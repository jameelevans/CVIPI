const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const file = process.argv[2] || path.join(__dirname, '../assets/js/modules/AnalyticsConsent.js');
const source = fs.readFileSync(file, 'utf8').replace(/export function/g, 'function').replace('export default AnalyticsConsent;', 'this.exports = {AnalyticsConsent, cleanAnalyticsUrl, readAnalyticsChoice};');

function setup(mode = 'live', saved = null, gpc = false) {
  const scripts = [], logs = [], writes = [], handlers = {};
  const element = () => ({hidden: false, dataset: {}, textContent: '', addEventListener() {}, focus() {}, getBoundingClientRect: () => ({height: 120}), contains: () => false});
  const controls = new Map();
  const banner = element();
  banner.querySelector = key => { if (!controls.has(key)) controls.set(key, element()); return controls.get(key); };
  const status = element();
  let stored = saved;
  const document = {
    querySelector: key => key === '[data-analytics-consent]' ? banner : key === '[data-analytics-status]' ? status : null,
    querySelectorAll: () => [], addEventListener: (name, fn) => { handlers[name] = fn; },
    documentElement: {style: {setProperty() {}}, scrollHeight: 2000}, body: {}, referrer: 'https://example.org/path?email=private',
    head: {appendChild: script => scripts.push(script)}, createElement: () => ({remove() {}}),
  };
  Object.defineProperty(document, 'cookie', {get: () => '_ga=123; _ga_TEST=456; wordpress_logged_in=keep', set: value => writes.push(value)});
  const context = vm.createContext({document, URL, Date, Set, WeakSet, WeakMap, Number, JSON, Math, navigator: {globalPrivacyControl: gpc},
    location: {href: 'https://cvipi.info/contact/?email=private#name', origin: 'https://cvipi.info', hostname: 'cvipi.info', pathname: '/contact/', protocol: 'https:'},
    localStorage: {getItem: () => stored, setItem: (_, value) => { stored = value; }},
    ResizeObserver: class {observe() {}}, MutationObserver: class {observe() {}},
    console: {info: (...args) => logs.push(args)},
  });
  context.window = context;
  context.addEventListener = (name, fn) => { handlers[name] = fn; };
  context.setTimeout = () => 1;
  context.clearTimeout = () => {};
  context.requestAnimationFrame = fn => fn();
  context.innerHeight = 1000;
  context.scrollY = 920;
  vm.runInContext(source, context);
  const instance = new context.exports.AnalyticsConsent({mode, measurementId: 'G-0J5P2BGBMY', pageType: 'page', pageTitle: 'CVIPI | Page', contactFormId: 219});
  return {instance, context, scripts, logs, writes, banner, handlers, setStored: value => {stored = value;}, ...context.exports};
}
const saved = choice => JSON.stringify({version: 1, choice, savedAt: Date.now()});

test('no choice or declined: no tag, events, or pending queue', () => {
  for (const choice of [null, saved('declined')]) {
    const s = setup('live', choice);
    s.instance.track('file_download');
    assert.equal(s.scripts.length, 0);
    assert.equal(s.instance.pendingEvents.length, 0);
    assert.equal(s.context.dataLayer, undefined);
  }
});
test('preview acceptance never loads Google, strips query and referrer path', () => {
  const s = setup('preview'); s.instance.choose('accepted');
  assert.equal(s.scripts.length, 0);
  assert.equal(s.logs.length, 1);
  assert.equal(s.logs[0][2].page_location, 'https://cvipi.info/contact/');
  assert.equal(s.logs[0][2].page_referrer, 'https://example.org');
});
test('accept loads one tag and one page view, advertising remains denied', () => {
  const s = setup(); s.instance.choose('accepted'); s.instance.start();
  assert.equal(s.scripts.length, 1);
  assert.equal(s.context.dataLayer.length, 0);
  s.scripts[0].onload();
  const calls = s.context.dataLayer.map(args => Array.from(args));
  assert.equal(calls.filter(args => args[0] === 'config').length, 1);
  assert.equal(calls.filter(args => args[1] === 'page_view').length, 1);
  assert.equal(calls[0][2].ad_storage, 'denied');
  assert.equal(calls.find(args => args[0] === 'config')[2].send_page_view, false);
});
test('withdraw during load discards queued events; onload cannot activate tracking', () => {
  const s = setup(); s.instance.choose('accepted'); s.instance.track('file_download');
  s.instance.choose('declined'); s.scripts[0].onload();
  assert.equal(s.context.dataLayer.length, 0);
  assert.equal(s.instance.pendingEvents.length, 0);
  assert.equal(s.context['ga-disable-G-0J5P2BGBMY'], true);
  s.instance.choose('accepted');
  assert.equal(s.context.dataLayer.filter(args => args[1] === 'file_download').length, 0);
  assert.equal(s.context.dataLayer.filter(args => args[1] === 'page_view').length, 1);
});
test('withdraw loaded tag blocks events and clears only analytics cookies', () => {
  const s = setup(); s.instance.choose('accepted'); s.scripts[0].onload();
  const count = s.context.dataLayer.length;
  s.instance.choose('declined'); s.instance.track('button_click');
  assert.equal(s.context.dataLayer.length, count);
  assert.equal(s.context['ga-disable-G-0J5P2BGBMY'], true);
  assert.ok(s.writes.some(value => value.startsWith('_ga=')));
  assert.ok(s.writes.every(value => !value.startsWith('wordpress')));
});
test('stored acceptance and cross-tab withdrawal', () => {
  const s = setup('live', saved('accepted')); assert.equal(s.scripts.length, 1);
  s.setStored(saved('declined')); s.handlers.storage({key: 'cvipi_analytics_consent_v1'});
  assert.equal(s.instance.active, false);
});
test('GPC overrides saved consent and prevents acceptance', () => {
  const s = setup('live', saved('accepted'), true); s.instance.choose('accepted');
  assert.equal(s.scripts.length, 0); assert.equal(s.instance.active, false);
});
test('invalid, expired, and future consent are rejected', () => {
  const {readAnalyticsChoice: read} = setup();
  for (const value of ['bad', '{}', JSON.stringify({version: 1, choice: 'accepted', savedAt: Date.now() - 181 * 86400000}), JSON.stringify({version: 1, choice: 'accepted', savedAt: Date.now() + 60000})]) assert.equal(read(value), null);
});
test('expired active consent is revoked before the next event', () => {
  const s = setup('preview', saved('accepted'));
  s.instance.choice.savedAt -= 181 * 86400000;
  s.instance.track('file_download'); assert.equal(s.instance.active, false); assert.equal(s.logs.length, 1);
});
test('storage failure retains only current-page choice', () => {
  const s = setup('preview'); s.context.localStorage.setItem = () => {throw new Error('blocked');};
  s.instance.choose('accepted'); assert.equal(s.instance.active, true);
  assert.match(s.instance.status.textContent, /this page only/);
});
test('sensitive URL components and nonweb schemes never reach analytics', () => {
  const {cleanAnalyticsUrl: clean} = setup();
  assert.equal(clean('/file.pdf?token=private#private', 'https://cvipi.info'), 'https://cvipi.info/file.pdf');
  for (const value of ['/wp-admin/post.php', '/wp-json/test', '/person%40example.com', 'mailto:private@example.org', 'javascript:alert(1)']) assert.equal(clean(value, 'https://cvipi.info'), '');
});
test('download creates one sanitized event; mail link omits address', () => {
  const s = setup('preview', saved('accepted'));
  const link = href => ({getAttribute: () => href, hasAttribute: () => false, closest: () => null});
  const target = item => ({closest: selector => selector === 'a[href]' ? item : null});
  s.instance.click({target: target(link('/file.pdf?token=secret'))});
  assert.equal(s.logs.at(-1)[1], 'file_download');
  assert.equal(s.logs.at(-1)[2].link_url, 'https://cvipi.info/file.pdf');
  s.instance.click({target: target(link('mailto:private@example.org'))});
  assert.equal(s.logs.at(-1)[2].link_type, 'mailto');
  assert.ok(!JSON.stringify(s.logs).includes('private@example.org'));
});
test('scroll thresholds are once per page and form success never reads fields', () => {
  const s = setup('preview', saved('accepted')); s.instance.scroll(); s.instance.scroll();
  assert.equal(s.logs.filter(log => log[1] === 'scroll_depth').length, 4);
  s.handlers['cvipi:form-success']({detail: {form: 'story', email: 'private@example.org', story: 'private'}});
  assert.equal(s.logs.at(-1)[1], 'form_success');
  assert.ok(!JSON.stringify(s.logs.at(-1)).includes('private'));
});
