(() => {
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (motion.matches || !window.IntersectionObserver || !window.MutationObserver) return;

  const html = document.documentElement;
  const selector = '[data-calendar-reveal]';
  const observed = new WeakSet();
  let intersections;
  let mutations;
  const disable = () => {
    html.classList.remove('tta-calendar-motion');
    intersections?.disconnect();
    mutations?.disconnect();
  };
  const reveal = (element, immediate = false) => {
    if (element.classList.contains('is-calendar-visible')) return;
    if (immediate) element.classList.add('is-calendar-static');
    element.classList.add('is-calendar-visible');
    intersections.unobserve(element);
  };
  const observe = node => {
    if (node.nodeType !== 1) return;
    const elements = [...node.querySelectorAll(selector)];
    if (node.matches(selector)) elements.unshift(node);
    elements.forEach(element => {
      if (observed.has(element)) return;
      observed.add(element);
      element.addEventListener('focusin', () => reveal(element, true), { once: true });
      intersections.observe(element);
    });
  };

  try {
    intersections = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) reveal(entry.target);
      });
    }, { threshold: 0, rootMargin: `0px 0px -${Math.round(window.innerHeight * 0.18)}px 0px` });
    // Observe markup as it is parsed, without waiting for the deferred calendar bundle.
    mutations = new MutationObserver(records => {
      try {
        records.forEach(record => record.addedNodes.forEach(observe));
      } catch (error) {
        disable();
      }
    });
    mutations.observe(html, { childList: true, subtree: true });
    observe(html);
    document.addEventListener('DOMContentLoaded', () => {
      try {
        observe(html);
      } catch (error) {
        disable();
      }
      mutations.disconnect();
    }, { once: true });
    motion.addEventListener('change', disable, { once: true });
    // Set the CSS guard in the head, before any headings or calendar markup can paint.
    html.classList.add('tta-calendar-motion');
  } catch (error) {
    disable();
  }
})();
