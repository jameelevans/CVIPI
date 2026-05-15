import $ from 'jquery';

/**
 * MobileNav (NSVSP)
 * ----------------
 * What this module does:
 * 1) Opens/closes the mobile nav panel (hamburger + X)
 * 2) Opens/closes slide-in submenus (Resources / Glossary / FAQs)
 * 3) Accessibility:
 *    - ESC closes submenu first, then closes the menu
 *    - Focus trap keeps Tab/Shift+Tab inside the open mobile panel
 *    - When a submenu is open:
 *        • the blurred main layer is removed from the tab order
 *        • CLOSED submenus are removed from the tab order (even though they remain in the DOM)
 *
 * IMPORTANT (animation):
 * - We DO NOT toggle the `hidden` attribute for submenu panels.
 *   `hidden` => display:none => transforms cannot animate.
 * - Instead, CSS should slide panels based on aria-hidden:
 *     .mobile-navigation__submenu { transform: translateX(100%); transition: ... }
 *     .mobile-navigation__submenu[aria-hidden="false"] { transform: translateX(0); }
 */

class MobileNav {
  constructor() {
    // ---------- Core elements ----------
    this.mobileMenu    = $('.mobile-navigation__menu');   // hamburger trigger
    this.mobileContent = $('.mobile-navigation__nav');    // nav panel
    this.closeButton   = $('.mobile-navigation__close');  // X button (inside panel)
    this.backButton    = $('.mobile-navigation__back');   // Back button (inside panel)
    this.bodyContainer = $('.container');
    this.$body         = $('body');

    // ---------- Submenu elements ----------
    this.submenuToggles   = $('.mobile-navigation__submenu-toggle'); // chevrons
    this.submenuContainer = $('.mobile-navigation__submenus');       // wrapper over main list
    this.submenus         = $('.mobile-navigation__submenu');        // each submenu <ul>

    // Track which submenu is open (null = main menu)
    this.activeSubmenuId = null;

    // Bail if markup isn't present
    if (!this.mobileMenu.length || !this.mobileContent.length) return;

    /**
     * Focusable selector
     * We will filter out:
     * - anything disabled via data-focus-disabled="true" (main layer)
     * - anything disabled via data-panel-focus-disabled="true" (submenu panels)
     */
    this.focusableSelector =
      'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

    // Cached focusables for focus trap
    this.$focusables = $();
    this.$firstFocusable = $();
    this.$lastFocusable  = $();

    // Bind handlers so add/remove works reliably
    this.onKeyup   = this.onKeyup.bind(this);
    this.onKeydown = this.onKeydown.bind(this);

    this.bindEvents();

    // Ensure predictable initial state
    this.resetSubmenus();
  }

  // =========================================================
  // Event wiring
  // =========================================================
  bindEvents() {
    // Hamburger toggles panel
    this.mobileMenu.on('click', (e) => this.toggleMenu(e));

    // Close (X)
    if (this.closeButton.length) {
      this.closeButton.on('click', (e) => {
        e.preventDefault();
        this.closeMenu();
      });
    }

    // Back (only meaningful when submenu open)
    if (this.backButton.length) {
      this.backButton.on('click', (e) => {
        e.preventDefault();
        this.closeSubmenu();
      });
    }

    // ESC behavior
    $(document).on('keyup', this.onKeyup);

    // Clicking ANY link inside mobile nav closes the whole menu
    this.mobileContent.on('click', 'a', () => {
      if (this.isOpen()) this.closeMenu();
    });

    // Chevron buttons open submenus (delegated)
    this.mobileContent.on('click', '.mobile-navigation__submenu-toggle', (e) => {
      e.preventDefault();
      const $btn = $(e.currentTarget);
      const submenuId = $btn.data('submenu');
      if (submenuId) this.openSubmenu(submenuId, $btn);
    });
  }

  // =========================================================
  // Menu open/close
  // =========================================================
  isOpen() {
    return this.mobileContent.hasClass('mobile-navigation__nav--is-visible');
  }

  openMenu() {
    this.mobileContent
      .addClass('mobile-navigation__nav--is-visible')
      .attr('aria-hidden', 'false');

    this.mobileMenu.attr('aria-expanded', 'true');

    // lock scroll
    this.bodyContainer.addClass('fixed-position');
    this.$body.addClass('no-scroll');

    // Always start at main menu level
    this.resetSubmenus();

    // Focus trap
    this.setupFocusables();
    if (this.$firstFocusable.length) {
      this.$firstFocusable.focus();
    } else {
      this.mobileMenu.focus();
    }

    $(document).on('keydown', this.onKeydown);
  }

  closeMenu() {
    // Reset submenu UI + restore all focusability
    this.resetSubmenus();

    // Hide panel
    this.mobileContent
      .removeClass('mobile-navigation__nav--is-visible')
      .attr('aria-hidden', 'true');

    this.mobileMenu.attr('aria-expanded', 'false');

    // unlock scroll
    this.bodyContainer.removeClass('fixed-position');
    this.$body.removeClass('no-scroll');

    // Stop focus trap
    $(document).off('keydown', this.onKeydown);

    // Return focus to hamburger
    this.mobileMenu.focus();
  }

  toggleMenu(e) {
    if (e) e.preventDefault();
    this.isOpen() ? this.closeMenu() : this.openMenu();
  }

  // =========================================================
  // Keyboard handling
  // =========================================================
  onKeyup(e) {
    if (e.key !== 'Escape') return;
    if (!this.isOpen()) return;

    // ESC closes submenu first, otherwise closes the whole menu
    if (this.activeSubmenuId) {
      this.closeSubmenu();
    } else {
      this.closeMenu();
    }
  }

  onKeydown(e) {
    if (!this.isOpen()) return;
    if (e.key !== 'Tab') return;

    // If nothing focusable, prevent focus escaping
    if (!this.$focusables.length) {
      e.preventDefault();
      this.mobileMenu.focus();
      return;
    }

    const $active = $(document.activeElement);

    // Shift+Tab on first => wrap to last
    if (e.shiftKey && $active.is(this.$firstFocusable)) {
      e.preventDefault();
      this.$lastFocusable.focus();
      return;
    }

    // Tab on last => wrap to first
    if (!e.shiftKey && $active.is(this.$lastFocusable)) {
      e.preventDefault();
      this.$firstFocusable.focus();
    }
  }

  /**
   * Build the list of focusables inside the open panel.
   * Filters out:
   * - main-layer disabled items (data-focus-disabled="true")
   * - panel disabled items (data-panel-focus-disabled="true")
   */
  setupFocusables() {
    this.$focusables = this.mobileContent
      .find(this.focusableSelector)
      .filter(':visible')
      .filter((_, el) => $(el).attr('data-focus-disabled') !== 'true')
      .filter((_, el) => $(el).attr('data-panel-focus-disabled') !== 'true');

    this.$firstFocusable = this.$focusables.first();
    this.$lastFocusable  = this.$focusables.last();
  }

  // =========================================================
  // Focus control helpers
  // =========================================================

  /**
   * Disable focus in MAIN (level 1) list while submenu is open.
   * This prevents Shift+Tab from falling into the blurred layer.
   */
  disableMainLayerFocus() {
    const $mainList = this.mobileContent.find('.mobile-navigation__list');
    const mainListEl = $mainList.length ? $mainList.get(0) : null;
    if (!mainListEl) return;

    // Prefer inert (modern)
    try {
      mainListEl.inert = true;
    } catch (err) {}

    // Fallback: remove from tab order + mark for our focus filtering
    const $focusables = $mainList.find(this.focusableSelector);
    $focusables.each((_, el) => {
      const $el = $(el);

      if ($el.attr('data-prev-tabindex') == null) {
        const prev = $el.attr('tabindex');
        $el.attr('data-prev-tabindex', prev != null ? prev : '');
      }

      $el.attr('data-focus-disabled', 'true');
      $el.attr('tabindex', '-1');
    });
  }

  /**
   * Restore focus in MAIN (level 1) list.
   */
  enableMainLayerFocus() {
    const $mainList = this.mobileContent.find('.mobile-navigation__list');
    const mainListEl = $mainList.length ? $mainList.get(0) : null;
    if (!mainListEl) return;

    try {
      mainListEl.inert = false;
    } catch (err) {}

    const $restore = $mainList.find('[data-focus-disabled="true"]');
    $restore.each((_, el) => {
      const $el = $(el);
      const prev = $el.attr('data-prev-tabindex');

      if (prev === '') {
        $el.removeAttr('tabindex');
      } else if (prev != null) {
        $el.attr('tabindex', prev);
      }

      $el.removeAttr('data-prev-tabindex');
      $el.removeAttr('data-focus-disabled');
    });
  }

  /**
   * Disable focus inside a submenu panel (or the submenu container).
   * This is what lets panels stay in the DOM (for animation) without being tabbable.
   */
  disablePanelFocus($panel) {
    if (!$panel || !$panel.length) return;

    const panelEl = $panel.get(0);
    if (panelEl) {
      try {
        panelEl.inert = true;
      } catch (err) {}
    }

    const $focusables = $panel.find(this.focusableSelector);
    $focusables.each((_, el) => {
      const $el = $(el);

      if ($el.attr('data-panel-prev-tabindex') == null) {
        const prev = $el.attr('tabindex');
        $el.attr('data-panel-prev-tabindex', prev != null ? prev : '');
      }

      $el.attr('data-panel-focus-disabled', 'true');
      $el.attr('tabindex', '-1');
    });
  }

  /**
   * Enable focus inside a submenu panel (or the submenu container).
   */
  enablePanelFocus($panel) {
    if (!$panel || !$panel.length) return;

    const panelEl = $panel.get(0);
    if (panelEl) {
      try {
        panelEl.inert = false;
      } catch (err) {}
    }

    const $restore = $panel.find('[data-panel-focus-disabled="true"]');
    $restore.each((_, el) => {
      const $el = $(el);
      const prev = $el.attr('data-panel-prev-tabindex');

      if (prev === '') {
        $el.removeAttr('tabindex');
      } else if (prev != null) {
        $el.attr('tabindex', prev);
      }

      $el.removeAttr('data-panel-prev-tabindex');
      $el.removeAttr('data-panel-focus-disabled');
    });
  }

  // =========================================================
  // Submenu open/close
  // =========================================================

  /**
   * Reset to MAIN level:
   * - aria-hidden all submenu panels
   * - disable focus inside those panels (so they can animate off-canvas safely)
   * - show Close, hide Back
   * - remove submenu-open helper class (used for blur/dim)
   * - restore main layer focusability
   */
  resetSubmenus() {
    this.activeSubmenuId = null;

    // Restore main layer focusability
    this.enableMainLayerFocus();

    // Hide & disable all submenu panels (NO `hidden` attribute toggling)
    this.submenus.each((_, el) => {
      const $submenu = $(el);
      $submenu.attr('aria-hidden', 'true');
      this.disablePanelFocus($submenu);
    });

    // Keep container in DOM, but mark hidden for AT + disable focus
    if (this.submenuContainer.length) {
      this.submenuContainer.attr('aria-hidden', 'true');
      this.disablePanelFocus(this.submenuContainer);
    }

    // Reset chevrons ARIA
    this.submenuToggles.attr('aria-expanded', 'false');

    // Controls: show Close, hide Back (CSS should visually toggle using aria-hidden)
    if (this.closeButton.length) {
      this.closeButton.attr('aria-hidden', 'false');
    }
    if (this.backButton.length) {
      this.backButton.attr('aria-hidden', 'true');
    }

    // Remove helper class used for blur/dim state
    this.mobileContent.removeClass('mobile-navigation__nav--submenu-open');

    // Update focus trap list
    this.setupFocusables();
  }

  /**
   * Open a submenu by ID (data-submenu target).
   * This is where we allow animation:
   * - No `hidden` toggling
   * - Only aria-hidden changes + focus enabling
   */
  openSubmenu(submenuId, $triggerBtn) {
    if (!this.isOpen()) return;

    // Start from a clean baseline
    this.resetSubmenus();

    this.activeSubmenuId = submenuId;

    // Prevent keyboard from entering blurred main list
    this.disableMainLayerFocus();

    // Make submenu container active/focusable
    if (this.submenuContainer.length) {
      this.submenuContainer.attr('aria-hidden', 'false');
      this.enablePanelFocus(this.submenuContainer);
    }

    // Activate requested panel (CSS slides it in via aria-hidden="false")
    const $submenu = this.mobileContent.find('#' + submenuId);
    $submenu.attr('aria-hidden', 'false');
    this.enablePanelFocus($submenu);

    // Mark trigger expanded
    if ($triggerBtn && $triggerBtn.length) {
      $triggerBtn.attr('aria-expanded', 'true');
    }

    // Swap controls: hide Close, show Back
    if (this.closeButton.length) {
      this.closeButton.attr('aria-hidden', 'true');
    }
    if (this.backButton.length) {
      this.backButton.attr('aria-hidden', 'false');
    }

    // Styling helper (blur/dim main layer)
    this.mobileContent.addClass('mobile-navigation__nav--submenu-open');

    // Recompute focusables for submenu level
    this.setupFocusables();

    // Move focus into submenu (first focusable)
    const $firstInSubmenu = $submenu
      .find(this.focusableSelector)
      .filter(':visible')
      .filter((_, el) => $(el).attr('data-panel-focus-disabled') !== 'true')
      .first();

    if ($firstInSubmenu.length) {
      $firstInSubmenu.focus();
    } else if (this.backButton.length) {
      this.backButton.focus();
    }
  }

  /**
   * Close current submenu and return to MAIN level.
   * Focus returns to the chevron button that opened the submenu.
   */
  closeSubmenu() {
    if (!this.activeSubmenuId) return;

    const currentId = this.activeSubmenuId;

    // Cache trigger chevron to restore focus
    const $triggerBtn = this.submenuToggles.filter(`[data-submenu="${currentId}"]`);

    this.resetSubmenus();

    if ($triggerBtn.length) $triggerBtn.focus();
  }
}

export default MobileNav;