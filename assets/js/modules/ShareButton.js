class ShareButton {
  constructor() {
    this.buttons = document.querySelectorAll('[data-share-button]');
    this.toast = null;
    this.toastTimer = null;

    this.bindEvents();
  }

  bindEvents() {
    if (!this.buttons.length) return;

    this.buttons.forEach((button) => {
      button.addEventListener('click', () => this.shareCurrentPage());
    });
  }

  shareCurrentPage() {
    const shareData = {
      title: document.title,
      url: window.location.href
    };

    if (navigator.share) {
      navigator.share(shareData).catch((error) => {
        if (error && error.name === 'AbortError') return;

        this.copyUrlToClipboard(shareData.url);
      });
      return;
    }

    this.copyUrlToClipboard(shareData.url);
  }

  copyUrlToClipboard(url) {
    if (!navigator.clipboard || !navigator.clipboard.writeText) {
      this.copyUrlWithTextarea(url);
      return;
    }

    navigator.clipboard.writeText(url)
      .then(() => this.showToast())
      .catch(() => this.copyUrlWithTextarea(url));
  }

  copyUrlWithTextarea(url) {
    const textArea = document.createElement('textarea');
    textArea.value = url;
    textArea.setAttribute('readonly', '');
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '-9999px';
    textArea.style.opacity = '0';

    document.body.appendChild(textArea);
    textArea.select();
    textArea.setSelectionRange(0, textArea.value.length);

    try {
      const didCopy = document.execCommand('copy');

      if (didCopy) {
        this.showToast();
      }
    } catch (error) {
      // Fail silently when browser copy support is unavailable.
    }

    document.body.removeChild(textArea);
  }

  showToast() {
    if (!this.toast) {
      this.toast = document.createElement('div');
      this.toast.className = 'share-toast';
      this.toast.setAttribute('role', 'status');
      this.toast.setAttribute('aria-live', 'polite');
      this.toast.textContent = 'Link copied to clipboard.';

      const toastRoot = document.querySelector('#single-page') || document.body;
      toastRoot.appendChild(this.toast);
    }

    window.clearTimeout(this.toastTimer);
    this.toast.classList.add('share-toast--is-visible');

    this.toastTimer = window.setTimeout(() => {
      this.toast.classList.remove('share-toast--is-visible');
    }, 3000);
  }
}

export default ShareButton;
