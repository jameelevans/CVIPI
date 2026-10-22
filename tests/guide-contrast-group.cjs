const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(`${__dirname}/../assets/js/modules/GuideContrastGroup.js`, 'utf8');

function fixture({ open = false, reduced = false, supported = true, broken = false } = {}) {
  const state = { resize: null, animations: [], currentHeight: null, active: null };
  const values = new Set();
  const classes = { add: value => values.add(value), toggle: (value, enabled) => enabled ? values.add(value) : values.delete(value), contains: value => values.has(value) };
  const summary = { listeners: {}, addEventListener(name, callback) { this.listeners[name] = callback; }, focus() { state.focused = true; } };
  const motion = { matches: reduced, addEventListener(name, callback) { this[name] = callback; } };
  const content = {
    style: {}, inert: false,
    firstElementChild: { getBoundingClientRect: () => ({ height: 600 }) },
    getBoundingClientRect: () => ({ height: state.currentHeight ?? (element.open ? 600 : 0) }),
    contains: target => !!target && target === state.active,
    animate: supported ? (frames, options) => {
      if (broken) throw new Error('Animation unavailable');
      const animation = { frames, options, cancelled: false, cancel() { this.cancelled = true; } };
      state.animations.push(animation);
      return animation;
    } : undefined,
  };
  const element = {
    open, classList: classes, listeners: {},
    querySelector: selector => selector === 'summary' ? summary : content,
    addEventListener(name, callback) { this.listeners[name] = callback; },
  };
  const context = { window: { addEventListener(name, callback) { state[name] = callback; } }, document: { activeElement: null } };
  vm.createContext(context);
  vm.runInContext(`${source.replace('export default class', 'class')}; this.Contrast = GuideContrastGroup;`, context);
  const group = new context.Contrast(element, motion);
  return Object.assign(state, { element, summary, motion, content, group, context, values });
}
const normal = fixture();
assert(!normal.content.inert, 'Native closed details manages focus without disabling browser Find');
let prevented = false;
normal.summary.listeners.click({ preventDefault() { prevented = true; } });
assert(prevented && normal.element.open && normal.group.expanded, 'Open the native details before animating its panel');
assert(!normal.content.inert);
assert.equal(normal.animations[0].frames[0].height, '0px');
assert.equal(normal.animations[0].frames[1].height, '600px');
normal.animations[0].onfinish();
assert.equal(normal.content.style.height, '', 'Completed panels regain a fluid responsive height');
assert(normal.element.open && !normal.group.animation);
normal.context.document.activeElement = normal.active = {};
normal.group.setExpanded(false);
assert(normal.focused, 'Closing returns focus from the panel to its summary');
assert(normal.element.open, 'Keep content rendered until closing has finished');
assert(normal.content.inert);
normal.animations.at(-1).onfinish();
assert(!normal.element.open && !normal.group.expanded);

const rapid = fixture({ open: true });
rapid.group.setExpanded(false);
const cancelled = rapid.animations[0];
rapid.currentHeight = 240;
rapid.group.setExpanded(true);
assert(cancelled.cancelled && cancelled.onfinish === null, 'A repeated click cannot run a stale close callback');
assert.equal(rapid.animations[1].frames[0].height, '240px', 'Reverse smoothly from the current animated height');
rapid.animations[1].onfinish();
assert(rapid.element.open && rapid.element.classList.contains('is-guide-expanded'));

const reduced = fixture({ open: true, reduced: true });
reduced.group.setExpanded(false);
assert(!reduced.element.open && reduced.animations.length === 0, 'Reduced motion uses immediate native states');
const changing = fixture();
changing.group.setExpanded(true);
changing.motion.matches = true;
changing.motion.change();
assert(changing.element.open && !changing.group.animation && changing.content.style.height === '');

const resizing = fixture();
resizing.group.setExpanded(true);
resizing.resize();
assert(resizing.element.open && !resizing.group.animation, 'Resizing releases the animation height without clipping');
const failed = fixture({ broken: true });
failed.group.setExpanded(true);
assert(failed.element.open && failed.content.style.height === '' && !failed.group.animation, 'Animation errors leave the requested content readable');
const native = fixture({ supported: false });
assert(!native.summary.listeners.click && !native.values.has('is-guide-animated'), 'Unsupported browsers retain native details behavior');
const external = fixture();
external.element.open = true;
external.element.listeners.toggle();
assert(external.group.expanded && !external.content.inert, 'External/native toggles keep the enhanced state in sync');
console.log('Guide contrast-group open, close, reversal, focus, fallback, and reduced-motion checks passed.');
