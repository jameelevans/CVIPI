(() => {
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (motion.matches || !window.IntersectionObserver || !window.MutationObserver) return;
  const html = document.documentElement;
  const selector = '[data-guide-reveal]';
  const seen = new WeakSet();
  let intersections;
  let mutations;
  const disable = () => {
    html.classList.remove('style-guide-motion');
    intersections?.disconnect();
    mutations?.disconnect();
  };
  const reveal = element => {
    element.classList.add('is-guide-visible');
    intersections.unobserve(element);
  };
  const observe = node => {
    if (node.nodeType !== 1) return;
    const elements = [...node.querySelectorAll(selector)];
    if (node.matches(selector)) elements.unshift(node);
    elements.forEach(element => {
      if (seen.has(element)) return;
      seen.add(element);
      element.addEventListener('focusin', () => reveal(element), { once: true });
      intersections.observe(element);
    });
  };
  try {
    intersections = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) reveal(entry.target);
    }), { threshold: 0, rootMargin: '0px 0px -8% 0px' });
    mutations = new MutationObserver(records => {
      try { records.forEach(record => record.addedNodes.forEach(observe)); }
      catch (_) { disable(); }
    });
    mutations.observe(html, { childList: true, subtree: true });
    observe(html);
    document.addEventListener('DOMContentLoaded', () => {
      try { observe(html); } catch (_) { disable(); }
      mutations.disconnect();
    }, { once: true });
    motion.addEventListener('change', disable, { once: true });
    html.classList.add('style-guide-motion');
  } catch (_) { disable(); }
})();
