class ContactFaqs {
  constructor() {
    this.accordion = document.querySelector('[data-contact-faqs]');

    if (!this.accordion) return;

    this.items = Array.from(this.accordion.querySelectorAll('.contact-faqs__item'));
    this.bindEvents();
  }

  bindEvents() {
    this.items.forEach(item => {
      const trigger = item.querySelector('[data-faq-trigger]');
      const panel = item.querySelector('[data-faq-panel]');

      if (!trigger) return;

      trigger.addEventListener('click', () => this.toggleItem(item));

      if (panel) {
        panel.addEventListener('transitionend', event => {
          if (event.propertyName !== 'height') return;

          if (item.classList.contains('contact-faqs__item--is-open')) {
            panel.style.height = 'auto';
          } else {
            panel.hidden = true;
          }
        });
      }
    });
  }

  toggleItem(item) {
    const isOpen = item.classList.contains('contact-faqs__item--is-open');

    this.items.forEach(currentItem => {
      this.closeItem(currentItem);
    });

    if (!isOpen) {
      this.openItem(item);
    }
  }

  openItem(item) {
    const trigger = item.querySelector('[data-faq-trigger]');
    const panel = item.querySelector('[data-faq-panel]');

    if (!trigger || !panel) return;

    item.classList.add('contact-faqs__item--is-open');
    trigger.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    panel.style.height = '0px';

    window.requestAnimationFrame(() => {
      panel.style.height = `${panel.scrollHeight}px`;
    });
  }

  closeItem(item) {
    const trigger = item.querySelector('[data-faq-trigger]');
    const panel = item.querySelector('[data-faq-panel]');

    if (!trigger || !panel || panel.hidden) return;

    item.classList.remove('contact-faqs__item--is-open');
    trigger.setAttribute('aria-expanded', 'false');
    panel.style.height = `${panel.scrollHeight}px`;

    window.requestAnimationFrame(() => {
      panel.style.height = '0px';
    });
  }
}

export default ContactFaqs;
