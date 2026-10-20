import { createIcons, ArrowRight, Download, ExternalLink, FileText, Mail, Menu, Plus, Presentation, X } from 'lucide';
import AnalyticsConsent from './AnalyticsConsent';

const analytics = new AnalyticsConsent();

class DigitalStyleGuide {
  constructor(root) {
    this.root = root;
    this.sections = [...root.querySelectorAll('main > section[id]')];
    this.links = [...root.querySelectorAll('[data-guide-section]')];
    this.progress = root.querySelector('[data-guide-progress]');
    this.menu = root.querySelector('[data-guide-menu]');
    this.drawer = root.querySelector('[data-guide-drawer]');
    this.compact = window.matchMedia('(max-width: 64em)');
    this.motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    this.queued = false;
    createIcons({ icons: { ArrowRight, Download, ExternalLink, FileText, Mail, Menu, Plus, Presentation, X }, nameAttr: 'data-guide-icon', attrs: { 'aria-hidden': 'true', focusable: 'false' } });
    this.bindNavigation();
    document.documentElement.classList.add('style-guide-enhanced');
    this.menu.hidden = false;
    this.resize = new ResizeObserver(() => this.queueUpdate());
    this.resize.observe(root.querySelector('main'));
    window.addEventListener('scroll', () => this.queueUpdate(), { passive: true });
    window.addEventListener('resize', () => this.queueUpdate(), { passive: true });
    this.updatePosition();
  }

  bindNavigation() {
    this.menu.addEventListener('click', () => {
      this.drawer.showModal();
      this.menu.setAttribute('aria-expanded', 'true');
      document.documentElement.classList.add('style-guide-menu-open');
    });
    this.drawer.querySelector('[data-guide-close]').addEventListener('click', () => this.drawer.close());
    // Consent lives outside the modal. Release its focus trap before showing it.
    this.drawer.querySelector('[data-analytics-preferences]')?.addEventListener('click', () => {
      if (!this.drawer.open) return;
      analytics.trigger = this.menu;
      this.afterDrawerFocus = analytics.banner?.querySelector('h2') || this.menu;
      this.drawer.close();
    });
    this.drawer.addEventListener('click', event => {
      const rect = this.drawer.getBoundingClientRect();
      if (event.target === this.drawer && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) this.drawer.close();
    });
    this.drawer.addEventListener('close', () => {
      document.documentElement.classList.remove('style-guide-menu-open');
      this.menu.setAttribute('aria-expanded', 'false');
      const fallback = this.compact.matches ? this.menu : this.root.querySelector('.style-guide__sidebar [aria-current]');
      (this.afterDrawerFocus || fallback)?.focus({ preventScroll: true });
      this.afterDrawerFocus = null;
    });
    this.compact.addEventListener('change', event => { if (!event.matches && this.drawer.open) this.drawer.close(); });
    this.root.querySelectorAll('a[href^="#"]').forEach(link => link.addEventListener('click', event => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      const target = document.getElementById(link.hash.slice(1));
      if (!target) return;
      event.preventDefault();
      if (this.drawer.open) {
        this.afterDrawerFocus = target;
        this.drawer.close();
      }
      if (window.location.hash !== link.hash) window.history.pushState(null, '', link.hash);
      target.focus({ preventScroll: true });
      target.scrollIntoView({ behavior: this.motion.matches ? 'instant' : 'smooth', block: 'start' });
    }));
  }

  queueUpdate() {
    if (this.queued) return;
    this.queued = true;
    requestAnimationFrame(() => { this.queued = false; this.updatePosition(); });
  }

  updatePosition() {
    let current = this.sections[0].id;
    for (const section of this.sections) {
      if (section.getBoundingClientRect().top <= window.innerHeight * 0.3) current = section.id;
      else break;
    }
    // The final section may be shorter than the viewport, so use the document end.
    const max = document.documentElement.scrollHeight - window.innerHeight;
    if (max > 0 && window.scrollY >= max - 2) current = this.sections.at(-1).id;
    this.links.forEach(link => {
      if (link.dataset.guideSection === current) link.setAttribute('aria-current', 'location');
      else link.removeAttribute('aria-current');
    });
    this.progress.style.transform = `scaleX(${max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0})`;
  }
}

document.querySelectorAll('[data-style-guide]').forEach(root => {
  try { new DigitalStyleGuide(root); }
  catch (error) {
    document.documentElement.classList.remove('style-guide-enhanced', 'style-guide-motion');
    console.error('CVIPI style guide navigation could not start.', error);
  }
});
