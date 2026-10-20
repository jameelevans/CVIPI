const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const reveals = fs.readFileSync(`${__dirname}/../assets/js/digital-style-guide-reveals.js`, 'utf8');
function fixture({ reduced = false, supported = true, broken = false } = {}) {
  const classes = () => {
    const values = new Set();
    return { add: value => values.add(value), remove: value => values.delete(value), contains: value => values.has(value) };
  };
  const state = { listeners: {} };
  const html = { nodeType: 1, classList: classes(), querySelectorAll: () => [], matches: () => false };
  state.html = html;
  const motion = { matches: reduced, addEventListener: (_, callback) => { state.motionChange = callback; } };
  class Intersections {
    constructor(callback, options) {
      if (broken) throw new Error('Observer failed');
      Object.assign(this, { callback, options, targets: new Set() });
      state.intersections = this;
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
  vm.runInNewContext(reveals, {
    window: { matchMedia: () => motion, IntersectionObserver: supported ? Intersections : undefined, MutationObserver: Mutations },
    document: { documentElement: html, addEventListener: (name, callback) => { state.listeners[name] = callback; } },
    IntersectionObserver: Intersections, MutationObserver: Mutations, WeakSet,
  });
  state.element = () => ({ nodeType: 1, classList: classes(), querySelectorAll: () => [], matches: () => true, addEventListener(name, callback) { this[name] = callback; } });
  return state;
}
const active = fixture();
assert(active.html.classList.contains('style-guide-motion'), 'Prepare the animation before the initial paint');
const element = active.element();
active.mutations.callback([{ addedNodes: [element] }]);
assert(active.intersections.targets.has(element));
assert.equal(active.intersections.options.threshold, 0, 'Tall text blocks reveal on entry without waiting for a percentage');
active.intersections.callback([{ target: element, isIntersecting: false }]);
assert(!element.classList.contains('is-guide-visible'));
active.intersections.callback([{ target: element, isIntersecting: true }]);
assert(element.classList.contains('is-guide-visible'));
assert(!active.intersections.targets.has(element), 'Reveal only once');
active.listeners.DOMContentLoaded();
assert(active.mutations.disconnected);
assert(element.classList.contains('is-guide-visible'), 'Loading the bundle never re-hides a visible block');
const keyboard = fixture();
const focused = keyboard.element();
keyboard.mutations.callback([{ addedNodes: [focused] }]);
focused.focusin();
assert(focused.classList.contains('is-guide-visible'), 'Keyboard users can always see the focused content');
keyboard.motionChange();
assert(!keyboard.html.classList.contains('style-guide-motion'));
for (const options of [{ reduced: true }, { supported: false }, { broken: true }]) {
  assert(!fixture(options).html.classList.contains('style-guide-motion'), 'Reduced motion and setup failures leave the guide visible');
}

// Exercise active navigation and progress without needing a browser or live Analytics.
const source = fs.readFileSync(`${__dirname}/../assets/js/modules/DigitalStyleGuide.js`, 'utf8');
const classSource = source.slice(source.indexOf('class DigitalStyleGuide'), source.indexOf("document.querySelectorAll('[data-style-guide]')"));
const context = { window: { innerHeight: 900, scrollY: 0 }, document: { documentElement: { scrollHeight: 5000 } } };
vm.createContext(context);
vm.runInContext(`${classSource}; this.Guide = DigitalStyleGuide;`, context);
const guide = Object.create(context.Guide.prototype);
guide.sections = ['overview', 'templates', 'contact'].map((id, index) => ({ id, getBoundingClientRect: () => ({ top: index * 2000 - context.window.scrollY }) }));
guide.links = guide.sections.flatMap(section => [0, 1].map(() => ({ dataset: { guideSection: section.id }, attrs: {}, setAttribute(name, value) { this.attrs[name] = value; }, removeAttribute(name) { delete this.attrs[name]; } })));
guide.progress = { style: {} };
guide.updatePosition();
assert.equal(guide.links.filter(link => link.attrs['aria-current']).length, 2, 'Both navigation copies track the active section');
assert.equal(guide.links[0].attrs['aria-current'], 'location');
assert.equal(guide.progress.style.transform, 'scaleX(0)');
context.window.scrollY = 2000;
guide.updatePosition();
assert.equal(guide.links[2].attrs['aria-current'], 'location');
assert(!guide.links[0].attrs['aria-current']);
context.window.scrollY = 4100;
guide.updatePosition();
assert.equal(guide.links[4].attrs['aria-current'], 'location', 'Short final sections can become active at the end of the page');
assert.equal(guide.progress.style.transform, 'scaleX(1)');
const css = fs.readFileSync(`${__dirname}/../assets/css/pages/_digital-style-guide.scss`, 'utf8');
assert(css.includes('@media (prefers-reduced-motion: reduce)'));
assert(css.includes('&__drawer::backdrop') && css.includes('backdrop-filter: blur('));
assert(!/font-size:[^;\n]*vw/.test(css), 'Font sizing follows the theme rem conventions, not viewport scaling');
assert(!source.includes('StorySubmission'), 'The standalone guide does not initialize site exit prompts');
console.log('Digital style guide animation and navigation checks passed.');
