const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { JSDOM } = require('jsdom');
const html = fs.readFileSync(__dirname + '/../inc/analytics-dashboard/view.php', 'utf8');
const script = fs.readFileSync(__dirname + '/../assets/admin/analytics-dashboard.js', 'utf8');
function fixture() {
  const reports = Object.fromEntries(['overview', 'trend', 'events', 'pages', 'downloads', 'channels', 'devices', 'links', 'forms'].map(key => [key, { rows: [], rowCount: 0 }]));
  reports.overview.rows = [{ dateRange: 'current', activeUsers: 10, sessions: 20, screenPageViews: 40, userEngagementDuration: 600, averageSessionDuration: 45, engagementRate: .5 }, { dateRange: 'previous', activeUsers: 5, sessions: 10 }];
  reports.trend.rows = [{ date: '20260928', activeUsers: 10, sessions: 20, screenPageViews: 40 }];
  reports.events.rows = [{ dateRange: 'current', eventName: 'file_download', eventCount: 7 }, { dateRange: 'previous', eventName: 'file_download', eventCount: 2 }];
  reports.downloads.rows = [{ fileName: '<img src=x onerror=alert(1)>', eventCount: 7, totalUsers: 5 }];
  reports.pages.rows = [{ pagePath: '/success-stories/test/', screenPageViews: 30, activeUsers: 8 }, { pagePath: '/', screenPageViews: 10, activeUsers: 5 }];
  reports.channels.rows = [{ sessionDefaultChannelGroup: 'Direct', sessions: 20 }];
  return { property: '555256472', site: 'https://cvipi.info', range: '28', timezone: 'America/Los_Angeles', fetchedAt: '2026-09-29T15:00:00Z', reports, warnings: [] };
}
async function mount(fetcher) {
  const dom = new JSDOM(html, { runScripts: 'outside-only', url: 'https://example.test/wp-admin/' });
  dom.window.cvipiDashboard = { url: '/ajax', nonce: 'test-only' };
  dom.window.fetch = fetcher;
  dom.window.eval(script);
  await new Promise(resolve => setImmediate(resolve));
  return dom;
}
test('real rendering path draws a nonblank accessible chart and keeps report text inert', async () => {
  const dom = await mount(async () => ({ ok: true, json: async () => ({ success: true, data: fixture() }) }));
  const document = dom.window.document;
  assert.equal(document.querySelector('#cvipi-da-results').hidden, false);
  assert.equal(document.querySelectorAll('.cvipi-da__metric').length, 6);
  assert.match(document.querySelector('#cvipi-chart-title').textContent, /Active visitors/);
  assert.equal(document.querySelector('.series').getAttribute('points').split(' ').length, 28);
  assert.ok(!document.querySelector('.series').getAttribute('points').includes('NaN'));
  assert.match(document.querySelector('#cvipi-da-downloads').textContent, /<img/);
  assert.equal(document.querySelector('#cvipi-da-downloads img'), null);
  assert.equal(document.querySelectorAll('#cvipi-da-daily tbody tr').length, 28);
  document.querySelector('[data-trend="sessions"]').click();
  assert.match(document.querySelector('#cvipi-chart-title').textContent, /Visits/);
  document.querySelector('[data-view="content"]').click();
  assert.equal(document.querySelector('[data-panel="content"]').hidden, false);
  assert.equal(document.querySelector('[data-panel="overview"]').hidden, true);
  document.querySelector('#cvipi-da-page-type').value = 'stories';
  document.querySelector('#cvipi-da-page-type').dispatchEvent(new dom.window.Event('change'));
  assert.equal(document.querySelectorAll('#cvipi-da-pages tbody tr').length, 1);
  dom.window.close();
});
test('unavailable response never presents fabricated zero metrics', async () => {
  const dom = await mount(async () => ({ ok: false, json: async () => ({ success: false, data: { message: 'Unavailable' } }) }));
  assert.equal(dom.window.document.querySelector('#cvipi-da-results').hidden, true);
  assert.match(dom.window.document.querySelector('#cvipi-da-notice').textContent, /Unavailable/);
  assert.equal(dom.window.document.querySelector('#cvipi-da-export').disabled, true);
  dom.window.close();
});
