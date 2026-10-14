const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { parse } = require('acorn');
const source = fs.readFileSync(`${__dirname}/../assets/js/modules/TtaCalendar.js`, 'utf8');
const definition = parse(source, { ecmaVersion: 'latest', sourceType: 'module' }).body.find(node => node.type === 'ClassDeclaration' && node.id.name === 'TtaCalendar');
let respond;
const TtaCalendar = vm.runInNewContext(`${source.slice(definition.start, definition.end)}; TtaCalendar`, {
  URL, AbortController,
  window: { location: { origin: 'http://cvipi.local' } },
  fetch: (...args) => respond(...args),
});
const element = () => ({
  hidden: true, listeners: {}, textContent: '',
  addEventListener(name, handler) { this.listeners[name] = handler; },
});
function fixture() {
  const controls = new Map();
  const instance = Object.create(TtaCalendar.prototype);
  instance.root = {
    querySelector(selector) {
      if (!controls.has(selector)) controls.set(selector, element());
      return controls.get(selector);
    },
    querySelectorAll: () => [],
  };
  instance.dialog = { ...element(), querySelector: () => element() };
  instance.mobile = element();
  instance.error = element();
  instance.viewport = element();
  instance.status = element();
  instance.config = { feed: 'http://cvipi.local/wp-json/cvipi/v1/calendar' };
  instance.requestId = 0;
  instance.changes = [];
  instance.retries = 0;
  instance.calendar = {
    view: { type: 'dayGridMonth' },
    changeView(type) { this.view.type = type; instance.changes.push(type); },
    refetchEvents() { instance.retries++; },
  };
  instance.bindControls();
  return instance;
}
(async () => {
  const controls = fixture();
  const viewAll = controls.root.querySelector('[data-calendar-view-all]').listeners.click;
  viewAll({});
  assert.deepEqual(controls.changes, ['listMonth'], 'View All Events switches to List');
  viewAll({});
  assert.equal(controls.changes.length, 1, 'An already selected List view is not reloaded');
  controls.calendar.view.type = 'dayGridMonth';
  for (const key of ['metaKey', 'ctrlKey', 'shiftKey', 'altKey']) viewAll({ [key]: true });
  assert.equal(controls.changes.length, 1, 'Modified link clicks keep their normal browser behavior');
  controls.root.querySelector('[data-calendar-retry]').listeners.click();
  assert.equal(controls.retries, 1, 'Retry requests the event feed again');

  const feed = fixture();
  const range = { startStr: '2026-10-01T00:00:00-04:00', endStr: '2026-11-01T00:00:00-04:00' };
  respond = async url => {
    assert.equal(url.searchParams.get('start'), '2026-10-01');
    assert.equal(url.searchParams.get('end'), '2026-11-01');
    return { ok: true, json: async () => [{ id: 'example' }] };
  };
  const events = await feed.fetchEvents(range);
  assert.equal(events[0].id, 'example');
  assert.equal(feed.error.hidden, true);
  assert.equal(feed.viewport.hidden, false);

  respond = async () => ({ ok: false, json: async () => ({ message: 'Unavailable' }) });
  await assert.rejects(feed.fetchEvents(range), /unavailable/);
  assert.equal(feed.error.hidden, false, 'Feed errors expose Retry');
  assert.equal(feed.viewport.hidden, true, 'Failed feeds do not show an apparently empty calendar');
  assert.equal(feed.status.textContent, 'Events are temporarily unavailable.');

  respond = async () => ({ ok: true, json: async () => [] });
  await feed.fetchEvents(range);
  assert.equal(feed.error.hidden, true, 'A successful retry clears the error');
  assert.equal(feed.viewport.hidden, false);
  assert.equal(feed.status.textContent, '');

  let finishOld;
  respond = () => new Promise(resolve => { finishOld = resolve; });
  const oldRequest = feed.fetchEvents(range);
  respond = async () => ({ ok: true, json: async () => [] });
  await feed.fetchEvents(range);
  finishOld({ ok: false, json: async () => ({}) });
  assert.equal((await oldRequest).length, 0, 'Outdated failures are discarded without warnings');
  assert.equal(feed.error.hidden, true, 'An outdated request cannot overwrite a newer successful result');

  respond = (url, { signal }) => new Promise((resolve, reject) => {
    signal.addEventListener('abort', () => reject(Object.assign(new Error('Cancelled'), { name: 'AbortError' })), { once: true });
  });
  const cancelledRequest = feed.fetchEvents(range);
  respond = async () => ({ ok: true, json: async () => [] });
  await feed.fetchEvents(range);
  assert.equal((await cancelledRequest).length, 0, 'Rapid view changes quietly cancel outdated requests');
  assert.equal(feed.error.hidden, true);
  console.log('Calendar interaction and feed recovery regression checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
