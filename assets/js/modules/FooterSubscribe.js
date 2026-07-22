class FooterSubscribe {
  constructor() {
    this.form = document.querySelector('[data-footer-subscribe]');

    if (!this.form) {
      return;
    }

    this.emailStep = this.form.querySelector('[data-footer-subscribe-email-step]');
    this.nameStep = this.form.querySelector('[data-footer-subscribe-name-step]');
    this.emailInput = this.form.querySelector('[data-footer-subscribe-email]');
    this.nameInputs = this.form.querySelectorAll('[data-footer-subscribe-name]');
    this.emailPreview = this.form.querySelector('[data-footer-subscribe-email-preview]');
    this.editEmailButton = this.form.querySelector('[data-footer-subscribe-edit-email]');
    this.submitButton = this.form.querySelector('[data-footer-subscribe-submit]');

    this.events();
  }

  events() {
    this.form.addEventListener('submit', (event) => this.handleSubmit(event), true);

    if (this.editEmailButton) {
      this.editEmailButton.addEventListener('click', () => this.showEmailStep());
    }
  }

  handleSubmit(event) {
    if (!this.form.classList.contains('footer__form--names-visible')) {
      event.preventDefault();
      event.stopImmediatePropagation();

      if (!this.emailInput.reportValidity()) {
        return;
      }

      this.showNameStep();
    }
  }

  showNameStep() {
    this.form.classList.add('footer__form--names-visible');
    this.emailStep.hidden = true;
    this.nameStep.hidden = false;
    this.emailPreview.textContent = this.emailInput.value;
    this.submitButton.textContent = 'Subscribe';

    this.nameInputs.forEach((input) => {
      input.required = true;
    });

    this.nameInputs[0].focus();
  }

  showEmailStep() {
    this.form.classList.remove('footer__form--names-visible');
    this.emailStep.hidden = false;
    this.nameStep.hidden = true;
    this.submitButton.textContent = 'Continue';

    this.nameInputs.forEach((input) => {
      input.required = false;
    });

    this.emailInput.focus();
  }
}

export default FooterSubscribe;
