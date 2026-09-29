(function () {
  'use strict';
  const number = value => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(value || 0);
  const duration = value => {
    const seconds = Math.max(0, Math.round(value || 0));
    return seconds >= 60 ? `${Math.floor(seconds / 60)}m ${seconds % 60}s` : `${seconds}s`;
  };
  const average = (total, count) => count > 0 ? total / count : 0;
  const comparison = (current, previous, today, formatter = number) => {
    if (today) return `Yesterday: ${formatter(previous)} (full day)`;
    if (!previous) return current ? 'No previous activity' : 'No activity in either period';
    const change = (current - previous) / previous * 100;
    return `${change > 0 ? '+' : ''}${change.toFixed(1)}% vs. previous period`;
  };
  const csvCell = value => {
    let text = String(value ?? '');
    if (/^[\s]*[=+@-]/.test(text) || /^[\t\r]/.test(text)) text = "'" + text;
    return '"' + text.replace(/"/g, '""') + '"';
  };
  const calendarDays = data => {
    const parts = Object.fromEntries(new Intl.DateTimeFormat('en-US', { timeZone: data.timezone, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date(data.fetchedAt)).map(part => [part.type, part.value]));
    const today = Date.UTC(Number(parts.year), Number(parts.month) - 1, Number(parts.day));
    const count = data.range === 'today' ? 1 : Number(data.range);
    return Array.from({ length: count }, (_, index) => new Date(today - (data.range === 'today' ? 0 : count - index) * 86400000).toISOString().slice(0, 10));
  };
  const dayLabel = day => new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', timeZone: 'UTC' }).format(new Date(day + 'T12:00:00Z'));
  const helpers = { number, duration, average, comparison, csvCell, calendarDays };
  if (typeof module !== 'undefined' && module.exports) module.exports = helpers;
  if (typeof document === 'undefined') return;
  const root = document.getElementById('cvipi-dashboard');
  if (!root) return;
  const $ = suffix => document.getElementById('cvipi-da-' + suffix);
  const element = (tag, text, className) => {
    const node = document.createElement(tag);
    if (text !== undefined) node.textContent = text;
    if (className) node.className = className;
    return node;
  };
  const empty = target => target.replaceChildren(element('p', 'No recorded activity in this period.', 'cvipi-da__empty'));
  let data, controller, sequence = 0, trendMetric = 'activeUsers';
  const rows = key => data.reports[key].rows;
  const periodRows = (key, period = 'current') => rows(key).filter(row => !row.dateRange || row.dateRange === period);
  const overview = period => periodRows('overview', period)[0] || {};
  const eventTotal = (name, period) => periodRows('events', period).filter(row => row.eventName === name).reduce((sum, row) => sum + row.eventCount, 0);
  function table(target, headers, records, total = records.length) {
    target.replaceChildren();
    if (!records.length) { empty(target); return; }
    const wrapper = element('div', undefined, 'cvipi-da__table-wrap');
    wrapper.tabIndex = 0;
    wrapper.setAttribute('role', 'region');
    wrapper.setAttribute('aria-label', headers.map(header => header[0]).join(', ') + ' table');
    const grid = element('table');
    const head = element('thead'), headRow = element('tr'), body = element('tbody');
    headers.forEach(([label, numeric]) => { const cell = element('th', label, numeric ? 'num' : ''); cell.scope = 'col'; headRow.append(cell); });
    head.append(headRow);
    records.forEach(record => {
      const row = element('tr');
      record.forEach((value, index) => { const cell = element('td', undefined, headers[index][1] ? 'num' : ''); cell.textContent = String(value); row.append(cell); });
      body.append(row);
    });
    grid.append(head, body); wrapper.append(grid); target.append(wrapper);
    if (total > records.length) target.append(element('p', `Showing ${records.length} of ${number(total)} rows, ranked by activity.`, 'cvipi-da__caption'));
  }
  function metrics() {
    const current = overview('current'), previous = overview('previous');
    const metricSpecs = [
      ['Active visitors', current.activeUsers, previous.activeUsers, number],
      ['Visits', current.sessions, previous.sessions, number],
      ['Page views', current.screenPageViews, previous.screenPageViews, number],
      ['Avg. engaged time / visit', average(current.userEngagementDuration, current.sessions), average(previous.userEngagementDuration, previous.sessions), duration],
      ['Download clicks', eventTotal('file_download', 'current'), eventTotal('file_download', 'previous'), number],
      ['Successful forms', eventTotal('form_success', 'current'), eventTotal('form_success', 'previous'), number],
    ];
    $('metrics').replaceChildren(...metricSpecs.map(([label, currentValue = 0, previousValue = 0, format]) => {
      const card = element('article', undefined, 'cvipi-da__metric');
      card.append(element('h3', label), element('strong', format(currentValue)), element('small', comparison(currentValue, previousValue, data.range === 'today', format)));
      return card;
    }));
    const facts = [['Average visit length', duration(current.averageSessionDuration)], ['Engaged visits', `${((current.engagementRate || 0) * 100).toFixed(1)}%`]];
    $('engagement').replaceChildren(...facts.map(([label, value]) => {
      const row = element('div'); row.append(element('dt', label), element('dd', value)); return row;
    }));
  }
  function chart() {
    const days = calendarDays(data);
    const daily = new Map(rows('trend').map(row => [row.date, row]));
    const values = days.map(day => ({ day, ...(daily.get(day.replace(/-/g, '')) || {}) }));
    table($('daily'), [['Date'], ['Visitors', true], ['Visits', true], ['Views', true]], values.map(row => [dayLabel(row.day), number(row.activeUsers), number(row.sessions), number(row.screenPageViews)]));
    if (!values.some(row => row[trendMetric] > 0)) { empty($('chart')); return; }
    const svgNode = (tag, attributes = {}, text) => {
      const node = document.createElementNS('http://www.w3.org/2000/svg', tag);
      Object.entries(attributes).forEach(([name, value]) => node.setAttribute(name, value));
      if (text !== undefined) node.textContent = text;
      return node;
    };
    const maximum = Math.max(1, ...values.map(row => row[trendMetric] || 0));
    const ceiling = Math.max(4, Math.ceil(maximum / 4) * 4);
    const svg = svgNode('svg', { viewBox: '0 0 720 260', role: 'img', 'aria-labelledby': 'cvipi-chart-title cvipi-chart-description' });
    const names = { activeUsers: 'Active visitors', sessions: 'Visits', screenPageViews: 'Page views' };
    svg.append(svgNode('title', { id: 'cvipi-chart-title' }, `${names[trendMetric]} by day`), svgNode('desc', { id: 'cvipi-chart-description' }, 'Daily counts for the selected reporting period. Exact values are available in Daily figures. Daily visitors are not additive across days.'));
    for (let step = 0; step <= 4; step++) {
      const y = 220 - step * 48;
      svg.append(svgNode('line', { x1: 58, y1: y, x2: 700, y2: y, class: 'grid' }), svgNode('text', { x: 48, y: y + 4, 'text-anchor': 'end' }, number(ceiling * step / 4)));
    }
    const points = values.map((row, index) => [values.length === 1 ? 379 : 58 + index / (values.length - 1) * 642, 220 - (row[trendMetric] || 0) / ceiling * 192]);
    svg.append(svgNode('polyline', { points: points.map(point => point.join(',')).join(' '), class: 'series' }));
    points.forEach(([x, y], index) => {
      if (values.length > 28 && index !== values.length - 1) return;
      const dot = svgNode('circle', { cx: x, cy: y, r: 4, class: 'point' });
      dot.append(svgNode('title', {}, `${dayLabel(values[index].day)}: ${number(values[index][trendMetric])}`)); svg.append(dot);
    });
    svg.append(svgNode('text', { x: 58, y: 248 }, dayLabel(days[0])), svgNode('text', { x: 700, y: 248, 'text-anchor': 'end' }, dayLabel(days[days.length - 1])));
    $('chart').replaceChildren(svg);
  }
  function bars(key, dimension) {
    const records = rows(key), total = records.reduce((sum, row) => sum + row.sessions, 0);
    if (!total) { empty($(key)); return; }
    const list = element('ul', undefined, 'cvipi-da__bars');
    records.forEach(row => {
      const item = element('li'), label = element('div', undefined, 'cvipi-da__bar-label');
      label.append(element('span', row[dimension]), element('strong', `${number(row.sessions)} visits`));
      const bar = element('div', undefined, 'cvipi-da__bar'), fill = element('span');
      bar.setAttribute('aria-hidden', 'true'); fill.style.width = `${row.sessions / total * 100}%`; bar.append(fill); item.append(label, bar); list.append(item);
    });
    $(key).replaceChildren(list);
  }
  function pages() {
    const all = rows('pages');
    const filtered = $('page-type').value === 'stories' ? all.filter(row => /^\/success-stories\/[^/]+/.test(row.pagePath)) : all;
    table($('pages'), [['Page'], ['Views', true], ['Visitors', true]], filtered.map(row => [row.pagePath, number(row.screenPageViews), number(row.activeUsers)]), $('page-type').value === 'stories' ? filtered.length : data.reports.pages.rowCount);
    if ($('page-type').value === 'stories' && data.reports.pages.rowCount > all.length) $('pages').append(element('p', 'Success stories within the top 100 pages.', 'cvipi-da__caption'));
  }
  function render() {
    const days = calendarDays(data);
    $('period').textContent = `${dayLabel(days[0])} - ${dayLabel(days[days.length - 1])}, ${days[days.length - 1].slice(0, 4)} | ${data.timezone}`;
    $('freshness').textContent = `${data.stale ? 'Last successful report' : 'Updated'} ${new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', timeZone: data.timezone }).format(new Date(data.fetchedAt))} | 15-minute cache`;
    const messages = [data.notice, ...data.warnings];
    if (!overview('current').sessions) messages.push('No processed activity for this period yet. New Analytics data can take 24-48 hours to appear.');
    if (data.range === 'today') messages.push('Today is partial and preliminary. Comparisons show yesterday\'s full-day total.');
    $('notice').textContent = messages.filter(Boolean).join(' ');
    metrics(); chart(); bars('channels', 'sessionDefaultChannelGroup'); bars('devices', 'deviceCategory'); pages();
    table($('downloads'), [['File'], ['Clicks', true], ['People', true]], rows('downloads').map(row => [row.fileName, number(row.eventCount), number(row.totalUsers)]), data.reports.downloads.rowCount);
    table($('events'), [['Action'], ['Count', true]], periodRows('events').map(row => [row.eventName.replace(/_/g, ' '), number(row.eventCount)]));
    table($('forms'), [['Form'], ['Submissions', true]], rows('forms').map(row => [row['customEvent:form_name'], number(row.eventCount)]), data.reports.forms.rowCount);
    if (data.reports.forms.unavailable) $('forms').replaceChildren(element('p', data.reports.forms.unavailable, 'cvipi-da__empty'));
    table($('links'), [['Destination'], ['Type'], ['Clicks', true]], rows('links').map(row => [row.linkUrl, row['customEvent:link_type'] || 'Not available yet', number(row.eventCount)]), data.reports.links.rowCount);
    $('results').hidden = false;
  }
  async function load(refresh = false) {
    if (controller) controller.abort();
    controller = new AbortController(); const requestId = ++sequence;
    const range = $('range').value;
    $('refresh').disabled = true; $('export').disabled = true; $('results').setAttribute('aria-busy', 'true');
    $('notice').classList.remove('cvipi-da__notice--error'); $('notice').textContent = 'Loading reports...';
    if (!data || data.range !== range) { $('results').hidden = true; $('period').textContent = ''; $('freshness').textContent = 'Connecting to Google Analytics'; }
    try {
      const response = await fetch(cvipiDashboard.url, { method: 'POST', credentials: 'same-origin', signal: controller.signal, headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ action: 'cvipi_dashboard_read', nonce: cvipiDashboard.nonce, range, refresh: refresh ? '1' : '' }) });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.data?.message || 'Reports are unavailable. Reload this page and try again.');
      if (requestId !== sequence) return;
      data = result.data; render(); $('export').disabled = false;
    } catch (error) {
      if (error.name === 'AbortError' || requestId !== sequence) return;
      $('notice').classList.add('cvipi-da__notice--error');
      $('notice').textContent = error.message + (data && data.range === range ? ' Showing the previously loaded report.' : '');
    } finally {
      if (requestId === sequence) { $('refresh').disabled = false; $('results').setAttribute('aria-busy', 'false'); }
    }
  }
  $('range').addEventListener('change', () => load());
  $('refresh').addEventListener('click', () => load(true));
  $('page-type').addEventListener('change', pages);
  root.querySelectorAll('[data-view]').forEach(button => button.addEventListener('click', () => {
    root.querySelectorAll('[data-view]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    root.querySelectorAll('[data-panel]').forEach(panel => { panel.hidden = panel.dataset.panel !== button.dataset.view; });
  }));
  root.querySelectorAll('[data-trend]').forEach(button => button.addEventListener('click', () => {
    trendMetric = button.dataset.trend;
    root.querySelectorAll('[data-trend]').forEach(item => item.setAttribute('aria-pressed', String(item === button))); chart();
  }));
  $('export').addEventListener('click', () => {
    if (!data) return;
    const lines = [['CVIPI Analytics', data.site], ['Property', data.property], ['Reporting dates', calendarDays(data)[0], calendarDays(data).at(-1)], ['Timezone', data.timezone], ['Fetched at', data.fetchedAt], ['Coverage', 'Consenting live-site visitors only; up to 100 rows per report; download clicks are not completed transfers.'], ['Warnings', ...(data.warnings || []), data.notice || '']];
    Object.entries(data.reports).forEach(([name, report]) => {
      lines.push([], [name, 'Total available rows', report.rowCount]);
      const headers = Object.keys(report.rows[0] || {});
      if (headers.length) { lines.push(headers); report.rows.forEach(row => lines.push(headers.map(key => row[key]))); }
      else lines.push([report.unavailable || 'No recorded activity']);
    });
    const url = URL.createObjectURL(new Blob(['\ufeff' + lines.map(line => line.map(csvCell).join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8' }));
    const link = element('a'); link.href = url; link.download = `cvipi-analytics-${data.range}-${data.fetchedAt.slice(0, 10)}.csv`; link.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
  });
  load();
}());
