const CONSENT_KEY = 'cvipi_analytics_consent_v1';
const CONSENT_AGE = 180 * 24 * 60 * 60 * 1000;
const FILE_TYPES = /\.(pdf|docx?|xlsx?|pptx?|rtf|txt|csv|tsv|epub|zip|rar|7z|gz|tar|exe|dmg|mp3|wav|m4a|ogg|mp4|m4v|mov|avi|wmv|webm|jpe?g|png|gif|webp|svg)$/i;

export function cleanAnalyticsUrl(value, base) {
  try {
    const url = new URL(value, base);
    if (!['https:', 'http:'].includes(url.protocol)) return '';
    const path = decodeURIComponent(url.pathname);
    if (/@|[\r\n]|(?:wp-admin|wp-login\.php|wp-json)(?:\/|$)/i.test(path)) return '';
    return `${url.origin}${url.pathname}`;
  } catch (_) { return ''; }
}

export function readAnalyticsChoice(raw, now = Date.now()) {
  try {
    const value = JSON.parse(raw);
    if (value?.version !== 1 || !['accepted', 'declined'].includes(value.choice) || !Number.isFinite(value.savedAt) || value.savedAt > now || now - value.savedAt >= CONSENT_AGE) return null;
    return value;
  } catch (_) { return null; }
}

class AnalyticsConsent {
  constructor(config = window.cvipiAnalyticsConfig) {
    this.banner = document.querySelector('[data-analytics-consent]');
    if (!this.banner || !config) return;
    this.config = config;
    this.status = document.querySelector('[data-analytics-status]');
    this.choiceText = this.banner.querySelector('[data-analytics-choice]');
    this.accept = this.banner.querySelector('[data-analytics-accept]');
    this.close = this.banner.querySelector('[data-analytics-close]');
    this.preferences = [...document.querySelectorAll('[data-analytics-preferences]')];
    this.choice = this.readChoice();
    this.active = false;
    this.loaded = false;
    this.tagReady = false;
    this.tagConfigured = false;
    this.pendingEvents = [];
    this.viewSent = false;
    this.scrollMarks = new Set();
    this.startedForms = new WeakSet();
    this.completedElements = new WeakSet();
    this.videoMarks = new WeakMap();
    this.banner.dataset.mode = config.mode;
    this.banner.querySelector('[data-analytics-decline]').addEventListener('click', () => this.choose('declined'));
    this.accept.addEventListener('click', () => this.choose('accepted'));
    this.close.addEventListener('click', () => this.hide(true));
    this.preferences.forEach(button => {
      button.hidden = false;
      button.addEventListener('click', () => { this.trigger = button; this.show(true); });
    });
    this.banner.addEventListener('keydown', event => {
      if (event.key === 'Escape' && this.choice) { event.preventDefault(); this.hide(true); }
    });
    this.resize = new ResizeObserver(() => this.reserveSpace());
    this.resize.observe(this.banner);
    window.addEventListener('storage', event => {
      if (event.key !== CONSENT_KEY && event.key !== null) return;
      this.choice = this.readChoice();
      this.reconcile();
    });
    window.addEventListener('pageshow', () => { this.choice = this.readChoice(); this.reconcile(); });
    this.bindEvents();
    this.reconcile();
    this.observeConfirmations();
  }

  readChoice() {
    try { return readAnalyticsChoice(localStorage.getItem(CONSENT_KEY)); }
    catch (_) { return this.choice || null; }
  }

  permitted() {
    return this.choice?.choice === 'accepted' && Date.now() - this.choice.savedAt < CONSENT_AGE && navigator.globalPrivacyControl !== true;
  }

  choose(choice) {
    if (choice === 'accepted' && navigator.globalPrivacyControl === true) return;
    this.choice = {version: 1, choice, savedAt: Date.now()};
    let persisted = true;
    try { localStorage.setItem(CONSENT_KEY, JSON.stringify(this.choice)); }
    catch (_) { persisted = false; }
    const restore = this.banner.contains(document.activeElement);
    this.reconcile();
    this.status.textContent = `${choice === 'accepted' ? 'Analytics accepted.' : 'Analytics is off.'}${persisted ? '' : ' Browser storage is unavailable; this choice applies to this page only.'}`;
    this.hide();
    if (restore) this.restoreFocus();
  }

  reconcile() {
    if (this.choice && !readAnalyticsChoice(JSON.stringify(this.choice))) this.choice = null;
    if (this.permitted()) this.start();
    else this.stop();
    this.banner.dataset.choice = this.permitted() ? 'accepted' : (this.choice ? 'declined' : 'unselected');
    this.accept.disabled = navigator.globalPrivacyControl === true;
    this.close.hidden = !this.choice;
    this.choiceText.textContent = navigator.globalPrivacyControl === true ? 'Your browser privacy preference keeps analytics off.' : (this.choice ? `Current choice: analytics ${this.permitted() ? 'accepted' : 'declined'}.` : '');
    if (!this.choice && navigator.globalPrivacyControl !== true) this.show();
    else this.hide();
    window.clearTimeout(this.expiryTimer);
    if (this.choice) this.expiryTimer = window.setTimeout(() => this.reconcile(), Math.min(2147483647, Math.max(1, this.choice.savedAt + CONSENT_AGE - Date.now())));
  }

  show(focus = false) {
    this.banner.hidden = false;
    this.close.hidden = !this.choice;
    this.reserveSpace();
    if (focus) this.banner.querySelector('h2').focus({preventScroll: true});
  }

  hide(restoreFocus = false) {
    const containedFocus = this.banner.contains(document.activeElement);
    this.banner.hidden = true;
    this.reserveSpace();
    if (!restoreFocus || !containedFocus) return;
    this.restoreFocus();
  }

  restoreFocus() {
    const target = this.trigger || document.querySelector('main');
    if (target) {
      if (!target.hasAttribute('tabindex') && target.tagName === 'MAIN') target.setAttribute('tabindex', '-1');
      target.focus({preventScroll: true});
    }
  }

  reserveSpace() {
    const height = this.banner.hidden ? 0 : Math.ceil(this.banner.getBoundingClientRect().height);
    document.documentElement.style.setProperty('--cvipi-consent-space', `${height}px`);
  }

  start() {
    if (this.active) return;
    this.active = true;
    if (this.config.mode === 'live') {
      if (!/^G-[A-Z0-9]{6,20}$/.test(this.config.measurementId)) { this.active = false; return; }
      window[`ga-disable-${this.config.measurementId}`] = false;
      if (!this.loaded) {
        this.loaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        const script = document.createElement('script');
        script.id = 'cvipi-google-analytics';
        script.async = true;
        script.referrerPolicy = 'no-referrer';
        script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(this.config.measurementId)}`;
        script.onload = () => {
          this.tagReady = true;
          if (this.permitted() && this.active) this.configureTag();
        };
        script.onerror = () => { this.stop(); this.loaded = false; script.remove(); this.status.textContent = 'Analytics could not load. The site remains available.'; };
        document.head.appendChild(script);
      }
      if (this.tagReady) this.configureTag();
    }
    if (!this.viewSent) {
      this.viewSent = true;
      this.track('page_view');
      if (this.config.pageType === 'not_found') this.track('page_not_found');
    }
  }

  stop() {
    this.active = false;
    if (this.config.measurementId) window[`ga-disable-${this.config.measurementId}`] = true;
    this.pendingEvents = [];
    if (!this.tagConfigured && this.config.mode === 'live') this.viewSent = false;
    // Opt-out is set before cookie cleanup. No denied-consent pings are sent.
    this.clearCookies();
  }

  configureTag() {
    if (!this.tagConfigured) {
      this.tagConfigured = true;
      window.gtag('consent', 'default', {analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
      window.gtag('consent', 'update', {analytics_storage: 'granted'});
      window.gtag('set', 'ads_data_redaction', true);
      window.gtag('set', 'url_passthrough', false);
      window.gtag('js', new Date());
      window.gtag('config', this.config.measurementId, {
        send_page_view: false, allow_google_signals: false, allow_ad_personalization_signals: false,
        page_location: this.pageUrl(), page_referrer: this.referrer(), page_title: this.config.pageTitle,
        cookie_expires: CONSENT_AGE / 1000, cookie_update: false, cookie_flags: 'SameSite=Lax;Secure',
      });
    }
    const events = this.pendingEvents.splice(0);
    events.forEach(([name, payload]) => window.gtag('event', name, payload));
  }

  clearCookies() {
    const names = document.cookie.split(';').map(item => item.trim().split('=')[0]).filter(name => /^_ga(?:_|$)|^_gid$|^_gat(?:_|$)/.test(name));
    const hosts = location.hostname.split('.');
    const domains = [''];
    for (let i = 0; i < hosts.length - 1; i++) domains.push(hosts.slice(i).join('.'), `.${hosts.slice(i).join('.')}`);
    const parts = location.pathname.split('/').filter(Boolean);
    const paths = new Set(['/']);
    parts.forEach((_, i) => { paths.add(`/${parts.slice(0, i + 1).join('/')}`); paths.add(`/${parts.slice(0, i + 1).join('/')}/`); });
    names.forEach(name => domains.forEach(domain => paths.forEach(path => {
      document.cookie = `${name}=; Max-Age=0; Path=${path}; SameSite=Lax${domain ? `; Domain=${domain}` : ''}${location.protocol === 'https:' ? '; Secure' : ''}`;
    })));
  }

  pageUrl() {
    return this.config.pageType === 'not_found' ? `${location.origin}/404` : (cleanAnalyticsUrl(location.href, location.href) || `${location.origin}/`);
  }

  referrer() {
    try { return document.referrer ? new URL(document.referrer).origin : ''; } catch (_) { return ''; }
  }

  track(name, params = {}) {
    if (!this.active) return;
    if (!this.permitted()) { this.reconcile(); return; }
    const payload = {page_location: this.pageUrl(), page_referrer: this.referrer(), page_title: this.config.pageTitle, page_type: this.config.pageType, ...params};
    if (this.config.mode === 'preview') {
      console.info('[CVIPI analytics preview]', name, payload);
      return;
    }
    const data = {...payload, send_to: this.config.measurementId};
    if (!this.tagConfigured) { if (this.pendingEvents.length < 50) this.pendingEvents.push([name, data]); return; }
    window.gtag('event', name, data);
  }

  formName(form) {
    if (!form) return '';
    if (form.closest('[data-story-dialog]')) return 'story';
    if (form.matches('[data-footer-subscribe]')) return 'newsletter';
    if (form.id === `wpforms-form-${this.config.contactFormId}`) return 'contact';
    return '';
  }

  bindEvents() {
    document.addEventListener('click', event => this.click(event));
    document.addEventListener('auxclick', event => { if (event.button === 1) this.click(event); });
    document.addEventListener('focusin', event => {
      const form = event.target.closest('form');
      const name = this.formName(form);
      if (!this.active || !name || this.startedForms.has(form)) return;
      this.startedForms.add(form);
      this.track('form_start', {form_name: name});
    });
    document.addEventListener('cvipi:form-success', event => {
      if (event.detail?.form === 'story') this.track('form_success', {form_name: 'story'});
    });
    document.addEventListener('cvipi:form-error', event => {
      if (event.detail?.form === 'story') this.track('form_error', {form_name: 'story', error_type: 'submission'});
    });
    document.addEventListener('submit', event => {
      const name = this.formName(event.target);
      if (name === 'newsletter' && !event.target.classList.contains('footer__form--names-visible')) return;
      if (name) this.track('form_submit_attempt', {form_name: name});
    }, true);
    let invalidPending = false;
    document.addEventListener('invalid', event => {
      const name = this.formName(event.target.closest('form'));
      if (!name || invalidPending) return;
      invalidPending = true;
      this.track('form_error', {form_name: name, error_type: 'validation'});
      window.setTimeout(() => { invalidPending = false; }, 0);
    }, true);
    document.addEventListener('cvipi:map-action', event => {
      if (['marker_open', 'zoom', 'pan', 'filter', 'reset'].includes(event.detail?.action)) this.track('map_interaction', {action_name: event.detail.action});
    });
    document.addEventListener('cvipi:filter-results', event => {
      if (['resources', 'events', 'stories'].includes(event.detail?.section)) this.track('filter_results', {content_group: event.detail.section, search_used: event.detail.searchUsed === true});
    });
    let pending = false;
    window.addEventListener('scroll', () => {
      if (pending || !this.active) return;
      pending = true;
      window.requestAnimationFrame(() => { pending = false; this.scroll(); });
    }, {passive: true});
    document.addEventListener('play', event => {
      if (event.target.matches('video[controls]')) this.track('video_start', {video_provider: 'html5'});
    }, true);
    document.addEventListener('pause', event => {
      if (event.target.matches('video[controls]') && !event.target.ended) this.track('video_pause', {video_provider: 'html5'});
    }, true);
    document.addEventListener('ended', event => {
      if (event.target.matches('video[controls]')) this.track('video_complete', {video_provider: 'html5'});
    }, true);
    document.addEventListener('timeupdate', event => {
      const video = event.target;
      if (!this.active || !video.matches('video[controls]') || !Number.isFinite(video.duration) || video.duration <= 0) return;
      const marks = this.videoMarks.get(video) || new Set();
      [25, 50, 75].forEach(mark => {
        if (video.currentTime / video.duration * 100 >= mark && !marks.has(mark)) { marks.add(mark); this.track('video_progress', {video_provider: 'html5', video_percent: mark}); }
      });
      this.videoMarks.set(video, marks);
    }, true);
  }

  click(event) {
    if (!this.active || !event.target.closest || event.target.closest('[data-analytics-consent], [data-analytics-preferences], #wpadminbar')) return;
    const link = event.target.closest('a[href]');
    if (link) {
      const href = link.getAttribute('href');
      const area = link.closest('footer') ? 'footer' : link.closest('header, .navigation, .mobile-navigation') ? 'navigation' : 'content';
      if (/^(mailto|tel):/i.test(href)) { this.track('link_click', {link_type: href.split(':')[0].toLowerCase(), link_area: area}); return; }
      const clean = cleanAnalyticsUrl(href, location.href);
      if (!clean) return;
      const url = new URL(clean);
      const extension = url.pathname.match(FILE_TYPES)?.[1]?.toLowerCase();
      const download = link.hasAttribute('download') || !!extension || !!link.closest('.wp-block-file') || link.hasAttribute('data-download');
      const params = {link_url: clean, link_domain: url.hostname, link_type: href.startsWith('#') ? 'anchor' : url.origin === location.origin ? 'internal' : 'outbound', link_area: area};
      if (download) {
        params.file_extension = extension || 'other';
        params.file_name = url.pathname.split('/').pop().slice(0, 100);
      }
      this.track(download ? 'file_download' : 'link_click', params);
      return;
    }
    const button = event.target.closest('button, input[type="submit"], [role="button"]');
    if (!button) return;
    const actions = [
      ['[data-story-open]', 'story_invite'], ['[data-story-create]', 'story_create'],
      ['[data-faq-trigger]', 'faq_toggle'], ['[data-story-filter]', 'story_filter'],
      ['[data-resource-filter]', 'resource_filter'], ['[data-event-status-filter], [data-event-type-filter]', 'event_filter'],
      ['[data-resources-load-more], [data-events-load-more]', 'load_more'],
      ['.mobile-navigation__menu', 'mobile_menu'], ['[data-share-button]', 'share'],
    ];
    const action = actions.find(([selector]) => button.matches(selector));
    const key = button.id || [...button.classList].find(name => /^[a-z][a-z0-9_-]{0,79}$/i.test(name)) || 'unlabelled';
    this.track('button_click', {action_name: action?.[1] || 'other', button_key: /^[a-z][a-z0-9_-]{0,79}$/i.test(key) ? key : 'other'});
    if (button.matches('[data-video-lightbox-trigger]')) this.track('video_open', {video_provider: 'embedded'});
  }

  scroll() {
    const total = document.documentElement.scrollHeight - window.innerHeight;
    if (total <= 0) return;
    const percent = window.scrollY / total * 100;
    [25, 50, 75, 90].forEach(mark => {
      if (percent >= mark && !this.scrollMarks.has(mark)) {
        this.scrollMarks.add(mark);
        this.track('scroll_depth', {percent_scrolled: mark});
      }
    });
  }

  observeConfirmations() {
    const check = root => {
      const selector = '[data-analytics-form-success="contact"], [data-footer-subscribe] .mc_success_msg, .contact-intro__form .wpforms-error, [data-footer-subscribe] .mc_error_msg';
      const nodes = [...root.querySelectorAll(selector)];
      if (root.matches?.(selector)) nodes.unshift(root);
      nodes.forEach(node => {
        if (this.completedElements.has(node)) return;
        this.completedElements.add(node);
        const contact = node.matches('[data-analytics-form-success]') || !!node.closest('.contact-intro__form');
        const error = node.matches('.wpforms-error, .mc_error_msg');
        this.track(error ? 'form_error' : 'form_success', {form_name: contact ? 'contact' : 'newsletter', ...(error ? {error_type: 'validation_or_submission'} : {})});
      });
    };
    check(document);
    this.confirmations = new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
      if (node.nodeType === 1) check(node);
    })));
    this.confirmations.observe(document.body, {childList: true, subtree: true});
  }
}

export default AnalyticsConsent;
