const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(`${__dirname}/../assets/js/tta-calendar-reveals.js`, 'utf8');

function fixture({ reduced = false, supported = true, broken = false } = {}) {
  const classes = () => {
    const values = new Set();
    return { add: value => values.add(value), remove: value => values.delete(value), contains: value => values.has(value) };
  };
  const listeners = {};
  const html = { nodeType: 1, classList: classes(), matches: () => false, querySelectorAll: () => [] };
  const state = { html, listeners };
  const motion = { matches: reduced, addEventListener: (name, callback) => { state.motionChange = callback; } };
  class Intersections {
    constructor(callback, options) {
      if (broken) throw new Error('Unavailable observer');
      state.intersections = this;
      this.callback = callback;
      this.options = options;
      this.targets = new Set();
    }
    observe(target) { this.targets.add(target); }
    unobserve(target) { this.targets.delete(target); }
    disconnect() { this.targets.clear(); }
  }
  class Mutations {
    constructor(callback) { state.mutations = this; this.callback = callback; }
    observe() {}
    disconnect() { this.disconnected = true; }
  }
  vm.runInNewContext(source, {
    window: { matchMedia: () => motion, innerHeight: 900, IntersectionObserver: supported ? Intersections : undefined, MutationObserver: Mutations },
    document: { documentElement: html, addEventListener: (name, callback) => { listeners[name] = callback; } },
    IntersectionObserver: Intersections, MutationObserver: Mutations, WeakSet,
  });
  state.element = () => ({
    nodeType: 1, classList: classes(), matches: () => true, querySelectorAll: () => [],
    addEventListener(name, callback) { this[name] = callback; },
  });
  return state;
}

const active = fixture();
assert(active.html.classList.contains('tta-calendar-motion'), 'Initial state is enabled synchronously, before body parsing');
const frame = active.element();
active.mutations.callback([{ addedNodes: [frame] }]);
assert(active.intersections.targets.has(frame), 'Observe parsed markup before DOMContentLoaded or the deferred bundle');
assert.equal(active.intersections.options.threshold, 0, 'Tall calendars reveal when their top enters, not when a percentage is visible');
active.intersections.callback([{ target: frame, isIntersecting: false }]);
assert(!frame.classList.contains('is-calendar-visible'));
active.intersections.callback([{ target: frame, isIntersecting: true }]);
assert(frame.classList.contains('is-calendar-visible'));
assert(!active.intersections.targets.has(frame), 'Reveal once only');
active.mutations.callback([{ addedNodes: [frame] }]);
active.listeners.DOMContentLoaded();
assert(frame.classList.contains('is-calendar-visible'), 'Later parsing / bundle completion cannot re-hide content');
assert(active.mutations.disconnected, 'Stop watching the document after parsing');

const keyboard = fixture();
const focused = keyboard.element();
keyboard.mutations.callback([{ addedNodes: [focused] }]);
focused.focusin();
assert(focused.classList.contains('is-calendar-visible'));
assert(focused.classList.contains('is-calendar-static'), 'Keyboard focus makes controls visible immediately');
keyboard.motionChange();
assert(!keyboard.html.classList.contains('tta-calendar-motion'), 'Changing motion preference reveals all content');

for (const options of [{ reduced: true }, { supported: false }, { broken: true }]) {
  assert(!fixture(options).html.classList.contains('tta-calendar-motion'), 'Unsupported, reduced-motion, and failed setup remain visible');
}
const css = fs.readFileSync(`${__dirname}/../assets/css/pages/_tta-calendar.scss`, 'utf8');
assert(css.includes('@media (prefers-reduced-motion: no-preference)'));
assert(css.includes('.posts-hero__content'), 'Animate the whole hero text group');
const template = fs.readFileSync(`${__dirname}/../page-tta-calendar.php`, 'utf8');
assert(template.includes('class="tta-calendar__intro" data-calendar-reveal'));
assert(template.includes('class="tta-calendar__support-content" data-calendar-reveal'));
assert(!/<h2[^>]*data-calendar-reveal/.test(template), 'Text blocks, not individual headings, are reveal targets');
assert(!source.includes('site-animation--fade-up-ready'));
assert(!fs.readFileSync(`${__dirname}/../assets/js/modules/TtaCalendar.js`, 'utf8').includes('initReveals'));
console.log('Calendar reveal regression checks passed.');
