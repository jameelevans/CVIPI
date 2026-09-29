class StorySubmission {
  constructor() {
    this.dialog = document.querySelector('[data-story-dialog]');
    if (!this.dialog || !this.dialog.showModal) return;
    this.form = this.dialog.querySelector('form');
    this.status = this.dialog.querySelector('[data-story-status]');
    this.submit = this.dialog.querySelector('[data-story-submit]');
    this.retry = this.dialog.querySelector('[data-story-retry]');
    this.widget = null;
    this.started = false;
    this.sending = false;
    this.visitedAt = Date.now();
    this.dismissed = this.readDismissed();
    this.backdrop = document.createElement('div');
    this.backdrop.className = 'story-dialog-backdrop';
    this.backdrop.hidden = true;
    this.backdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(this.backdrop);
    this.backdrop.addEventListener('click', () => this.dialog.close());
    this.dialog.setAttribute('aria-modal', 'true');
    this.dialog.addEventListener('keydown', event => this.handleKeys(event));
    this.form.addEventListener('submit', event => this.send(event));
    this.form.elements.story.addEventListener('input', () => {
      this.dialog.querySelector('[data-story-count]').textContent = `${this.form.elements.story.value.length.toLocaleString()} / 20,000`;
    });
    document.querySelectorAll('[data-story-open]').forEach(button => button.addEventListener('click', () => this.open(button)));
    this.dialog.querySelectorAll('[data-story-close]').forEach(button => button.addEventListener('click', () => this.dialog.close()));
    this.dialog.querySelector('[data-story-create]').addEventListener('click', () => { this.started = true; this.view('form'); this.prepare(); });
    this.retry.addEventListener('click', () => this.prepare());
    this.dialog.addEventListener('close', () => {
      this.backdrop.hidden = true;
      (this.background || []).forEach(([element, inert]) => { element.inert = inert; });
      document.documentElement.classList.remove('story-dialog-open');
      this.rememberDismissed();
      const target = this.trigger && this.trigger.getClientRects().length ? this.trigger : document.querySelector('.mobile-navigation__menu');
      if (target) target.focus();
    });
    this.dialog.addEventListener('click', event => {
      if (event.target !== this.dialog) return;
      const bounds = this.dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) this.dialog.close();
    });
    document.addEventListener('mouseout', event => {
      if (document.querySelector('[data-analytics-consent]:not([hidden])')) return;
      if (event.relatedTarget || event.clientY > 0 || Date.now() - this.visitedAt < 30000 || this.dismissed || this.dialog.open) return;
      if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches || document.querySelector('dialog[open], .video-lightbox--is-visible, .mobile-navigation-open')) return;
      if (document.activeElement && document.activeElement.matches('input, textarea, select, [contenteditable="true"]')) return;
      this.open(document.activeElement);
    });
  }

  readDismissed() {
    try { return sessionStorage.getItem('cvipi-story-prompt') === 'dismissed'; } catch (_) { return false; }
  }

  rememberDismissed() {
    this.dismissed = true;
    try { sessionStorage.setItem('cvipi-story-prompt', 'dismissed'); } catch (_) { /* Storage can be unavailable in private browsing. */ }
  }

  open(trigger) {
    if (this.dialog.open) return;
    this.trigger = trigger;
    // Google inserts its challenge iframe beside the dialog. Inert the site,
    // but leave Google's challenge available above our modal and focus trap.
    this.background = Array.from(document.body.children)
      .filter(element => element !== this.dialog && element !== this.backdrop && !element.querySelector('iframe[src*="/recaptcha/"][src*="/bframe"]'))
      .map(element => [element, element.inert]);
    this.background.forEach(([element]) => { element.inert = true; });
    this.backdrop.hidden = false;
    this.dialog.show();
    document.documentElement.classList.add('story-dialog-open');
    this.rememberDismissed();
    this.view(this.completed ? 'thanks' : this.started ? 'form' : 'invite');
    if (this.started && !this.completed && !this.sending) this.prepare();
  }

  view(name) {
    this.dialog.querySelectorAll('[data-story-view]').forEach(view => { view.hidden = view.dataset.storyView !== name; });
    this.dialog.classList.toggle('story-dialog--expanded', name === 'form');
    const id = name === 'form' ? 'story-form-title' : name === 'thanks' ? 'story-thanks-title' : 'story-dialog-title';
    this.dialog.setAttribute('aria-labelledby', id);
    document.getElementById(id).focus();
    this.dialog.scrollTop = 0;
  }

  handleKeys(event) {
    if (event.key === 'Escape') { event.preventDefault(); this.dialog.close(); return; }
    if (event.key !== 'Tab') return;
    const items = Array.from(this.dialog.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea, a[href], iframe, [tabindex="0"]'))
      .filter(element => element.tabIndex !== -1 && element.getClientRects().length && !element.closest('[aria-hidden="true"]'));
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && (document.activeElement === first || document.activeElement.matches('h2'))) { event.preventDefault(); last?.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
  }

  async request(data) {
    const response = await fetch(this.dialog.dataset.endpoint, {
      method: 'POST', credentials: 'same-origin', body: new URLSearchParams(data),
    });
    const json = await response.json();
    if (!json.success) { const error = new Error(json.data?.message || 'Unable to submit. Please try again.'); error.fields = json.data?.fields; throw error; }
    return json.data;
  }

  loadCaptcha() {
    if (window.grecaptcha?.render) return Promise.resolve();
    if (this.captchaLoading) return this.captchaLoading;
    this.captchaLoading = new Promise((resolve, reject) => {
      // WPForms owns the shared API on pages with a Contact form.
      if (document.getElementById('wpforms-recaptcha-js')) {
        const ready = () => { clearTimeout(timer); document.removeEventListener('wpformsRecaptchaLoaded', ready); resolve(); };
        const timer = setTimeout(() => {
          document.removeEventListener('wpformsRecaptchaLoaded', ready);
          reject(new Error('Verification could not load. Please reload the page and try again.'));
        }, 15000);
        document.addEventListener('wpformsRecaptchaLoaded', ready, {once: true});
        return;
      }
      const script = document.createElement('script');
      const timer = setTimeout(() => reject(new Error('Verification could not load. Please retry.')), 15000);
      window.cvipiStoryCaptchaReady = () => { clearTimeout(timer); resolve(); };
      script.src = 'https://www.google.com/recaptcha/api.js?onload=cvipiStoryCaptchaReady&render=explicit';
      script.async = true;
      script.onerror = () => { clearTimeout(timer); script.remove(); reject(new Error('Verification could not load. Please retry.')); };
      document.head.appendChild(script);
    }).catch(error => { this.captchaLoading = null; throw error; });
    return this.captchaLoading;
  }

  async prepare() {
    if (this.preparing) return;
    this.preparing = true;
    this.submit.disabled = true;
    this.retry.hidden = true;
    this.status.textContent = 'Loading verification...';
    try {
      this.session = await this.request({action: 'cvipi_story_session'});
      if (!this.session.ready) throw new Error('Story submissions are temporarily unavailable. Please contact the CVIPI team.');
      await this.loadCaptcha();
      const container = this.dialog.querySelector('[data-story-captcha]');
      if (this.widget === null) {
        this.widget = window.grecaptcha.render(container, {
          sitekey: this.session.siteKey,
          size: window.innerWidth < 380 ? 'compact' : 'normal',
          callback: () => { this.status.textContent = ''; },
          'expired-callback': () => { this.status.textContent = 'Verification expired. Please check the CAPTCHA again.'; },
          'error-callback': () => { this.status.textContent = 'Verification could not connect. Please retry.'; this.retry.hidden = false; },
        });
      } else window.grecaptcha.reset(this.widget);
      this.status.textContent = '';
      this.submit.disabled = false;
    } catch (error) { this.status.textContent = error.message; this.retry.hidden = false; }
    finally { this.preparing = false; }
  }

  async send(event) {
    event.preventDefault();
    if (this.sending || !this.session || !this.form.reportValidity()) return;
    this.form.querySelectorAll('[aria-invalid]').forEach(field => field.removeAttribute('aria-invalid'));
    this.form.querySelectorAll('.story-dialog__field-error').forEach(error => { error.textContent = ''; });
    const captcha = this.widget !== null ? window.grecaptcha.getResponse(this.widget) : '';
    if (!captcha) { document.dispatchEvent(new CustomEvent('cvipi:form-error', {detail: {form: 'story'}})); this.status.textContent = 'Please complete the CAPTCHA verification.'; this.status.focus(); return; }
    this.sending = true;
    this.submit.disabled = true;
    this.form.setAttribute('aria-busy', 'true');
    this.status.textContent = 'Sending your story...';
    try {
      await this.request({ ...Object.fromEntries(new FormData(this.form)), action: 'cvipi_submit_story', nonce: this.session.nonce, session: this.session.session, captcha });
      this.completed = true;
      document.dispatchEvent(new CustomEvent('cvipi:form-success', {detail: {form: 'story'}}));
      this.form.reset();
      this.view('thanks');
    } catch (error) {
      document.dispatchEvent(new CustomEvent('cvipi:form-error', {detail: {form: 'story'}}));
      this.status.textContent = error.message;
      Object.entries(error.fields || {}).forEach(([field, message]) => {
        const control = this.form.elements[field];
        const inputs = control instanceof RadioNodeList ? Array.from(control) : (control ? [control] : []);
        const hint = document.getElementById(`story-${field}-error`);
        if (inputs.length && hint) { inputs.forEach(input => input.setAttribute('aria-invalid', 'true')); hint.textContent = message; }
      });
      (this.form.querySelector('[aria-invalid]') || this.status).focus();
      if (this.widget !== null) window.grecaptcha.reset(this.widget);
    } finally {
      this.sending = false;
      this.submit.disabled = false;
      this.form.removeAttribute('aria-busy');
    }
  }
}

export default StorySubmission;
