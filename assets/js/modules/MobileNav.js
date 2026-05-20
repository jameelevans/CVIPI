// Accessible mobile navigation controller.
// Keeps ARIA state, keyboard focus, and body scroll lock in sync with the menu.
class MobileNav {
  constructor() {
    this.menuButton = document.querySelector('.mobile-navigation__menu');
    this.closeButton = document.querySelector('.mobile-navigation__close');
    this.nav = document.querySelector('.mobile-navigation__nav');
    this.body = document.body;
    this.focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    if (!this.menuButton || !this.nav) {
      return;
    }

    this.handleKeydown = this.handleKeydown.bind(this);
    this.openMenu = this.openMenu.bind(this);
    this.closeMenu = this.closeMenu.bind(this);
    this.toggleMenu = this.toggleMenu.bind(this);

    this.bindEvents();
    this.setClosedState();
  }

  bindEvents() {
    this.menuButton.addEventListener('click', this.toggleMenu);

    if (this.closeButton) {
      this.closeButton.addEventListener('click', this.closeMenu);
    }

    this.nav.addEventListener('click', event => {
      if (event.target.closest('a')) {
        this.closeMenu();
      }
    });
  }

  isOpen() {
    return this.nav.classList.contains('mobile-navigation__nav--is-visible');
  }

  getFocusableItems() {
    return Array.from(this.nav.querySelectorAll(this.focusableSelector)).filter(item => {
      return item.offsetParent !== null || item === this.closeButton;
    });
  }

  setClosedState() {
    this.menuButton.setAttribute('aria-expanded', 'false');
    this.nav.setAttribute('aria-hidden', 'true');
    this.nav.setAttribute('inert', '');
  }

  openMenu() {
    this.nav.classList.add('mobile-navigation__nav--is-visible');
    this.menuButton.classList.add('mobile-navigation__menu--is-active');
    this.menuButton.setAttribute('aria-expanded', 'true');
    this.nav.setAttribute('aria-hidden', 'false');
    this.nav.removeAttribute('inert');
    this.body.classList.add('mobile-navigation-open');
    document.addEventListener('keydown', this.handleKeydown);

    const focusableItems = this.getFocusableItems();
    const firstFocusableItem = focusableItems[0] || this.closeButton;

    if (firstFocusableItem) {
      firstFocusableItem.focus();
    }
  }

  closeMenu() {
    this.nav.classList.remove('mobile-navigation__nav--is-visible');
    this.menuButton.classList.remove('mobile-navigation__menu--is-active');
    this.setClosedState();
    this.body.classList.remove('mobile-navigation-open');
    document.removeEventListener('keydown', this.handleKeydown);
    this.menuButton.focus();
  }

  toggleMenu() {
    if (this.isOpen()) {
      this.closeMenu();
      return;
    }

    this.openMenu();
  }

  handleKeydown(event) {
    if (event.key === 'Escape') {
      this.closeMenu();
      return;
    }

    if (event.key !== 'Tab') {
      return;
    }

    const focusableItems = this.getFocusableItems();

    if (!focusableItems.length) {
      event.preventDefault();
      return;
    }

    const firstFocusableItem = focusableItems[0];
    const lastFocusableItem = focusableItems[focusableItems.length - 1];

    if (event.shiftKey && document.activeElement === firstFocusableItem) {
      event.preventDefault();
      lastFocusableItem.focus();
      return;
    }

    if (!event.shiftKey && document.activeElement === lastFocusableItem) {
      event.preventDefault();
      firstFocusableItem.focus();
    }
  }
}

export default MobileNav;
