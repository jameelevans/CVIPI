export default class GuideContrastGroup {
  constructor(element, motion) {
    this.element = element;
    this.summary = element.querySelector('summary');
    this.content = element.querySelector('.style-guide__contrast-content');
    this.motion = motion;
    this.expanded = element.open;
    this.animation = null;
    if (!this.content || typeof this.content.animate !== 'function') return;
    element.classList.add('is-guide-animated');
    this.sync();
    this.summary.addEventListener('click', event => {
      event.preventDefault();
      this.setExpanded(!this.expanded);
    });
    element.addEventListener('toggle', () => { if (!this.animation) this.sync(); });
    motion.addEventListener('change', () => { if (motion.matches && this.animation) this.finish(); });
    window.addEventListener('resize', () => { if (this.animation) this.finish(); }, { passive: true });
  }

  sync() {
    this.expanded = this.element.open;
    this.element.classList.toggle('is-guide-expanded', this.expanded);
    // Native closed details handles focus; clearing inert preserves browser Find.
    this.content.inert = false;
  }

  setExpanded(expanded) {
    const start = this.element.open ? this.content.getBoundingClientRect().height : 0;
    this.stopAnimation();
    this.expanded = expanded;
    this.element.classList.toggle('is-guide-expanded', expanded);
    if (!expanded && this.content.contains(document.activeElement)) this.summary.focus({ preventScroll: true });
    this.content.inert = !expanded;
    if (this.motion.matches) {
      this.finish();
      return;
    }
    // Native details stays open until the closing animation has finished.
    this.element.open = true;
    const end = expanded ? this.content.firstElementChild.getBoundingClientRect().height : 0;
    this.content.style.height = `${start}px`;
    try {
      this.animation = this.content.animate([
        { height: `${start}px`, opacity: start > 0 ? 1 : 0 },
        { height: `${end}px`, opacity: expanded ? 1 : 0 },
      ], { duration: 320, easing: 'cubic-bezier(0.16, 1, 0.3, 1)', fill: 'both' });
      this.animation.onfinish = () => this.finish();
    } catch (_) {
      this.finish();
    }
  }

  stopAnimation() {
    if (!this.animation) return;
    this.animation.onfinish = null;
    this.animation.cancel();
    this.animation = null;
  }

  finish() {
    this.stopAnimation();
    this.element.open = this.expanded;
    // Release the fixed animation height so responsive content can reflow naturally.
    this.content.style.height = '';
    this.sync();
  }
}
