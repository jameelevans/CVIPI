class VideoLightbox {
  constructor() {
    this.triggers = Array.from(document.querySelectorAll('[data-video-lightbox-trigger]'));

    if (!this.triggers.length) {
      return;
    }

    this.modal = null;
    this.frame = null;
    this.title = null;
    this.closeButton = null;
    this.fullscreenButton = null;
    this.lastFocusedElement = null;

    this.createModal();
    this.bindEvents();
  }

  createModal() {
    this.modal = document.createElement('div');
    this.modal.className = 'video-lightbox';
    this.modal.setAttribute('role', 'dialog');
    this.modal.setAttribute('aria-modal', 'true');
    this.modal.setAttribute('aria-labelledby', 'video-lightbox-title');
    this.modal.setAttribute('hidden', '');
    this.modal.innerHTML = `
      <div class="video-lightbox__backdrop" data-video-lightbox-close></div>
      <div class="video-lightbox__panel" role="document">
        <div class="video-lightbox__bar">
          <h2 id="video-lightbox-title" class="video-lightbox__title"></h2>
          <div class="video-lightbox__controls">
            <button class="video-lightbox__button" type="button" data-video-lightbox-fullscreen aria-label="Enter fullscreen">Fullscreen</button>
            <button class="video-lightbox__button video-lightbox__button--close" type="button" data-video-lightbox-close aria-label="Close video">Close</button>
          </div>
        </div>
        <div class="video-lightbox__stage" data-video-lightbox-stage>
          <iframe class="video-lightbox__frame" title="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>
    `;

    document.body.appendChild(this.modal);
    this.frame = this.modal.querySelector('.video-lightbox__frame');
    this.title = this.modal.querySelector('.video-lightbox__title');
    this.closeButton = this.modal.querySelector('[data-video-lightbox-close].video-lightbox__button');
    this.fullscreenButton = this.modal.querySelector('[data-video-lightbox-fullscreen]');
  }

  bindEvents() {
    this.triggers.forEach(trigger => {
      trigger.addEventListener('click', () => this.open(trigger));
    });

    this.modal.addEventListener('click', event => {
      if (event.target.closest('[data-video-lightbox-close]')) {
        this.close();
      }
    });

    this.fullscreenButton.addEventListener('click', () => this.enterFullscreen());

    document.addEventListener('keydown', event => {
      if (this.modal.hidden) {
        return;
      }

      if (event.key === 'Escape') {
        event.preventDefault();
        this.close();
      }

      if (event.key === 'Tab') {
        this.trapFocus(event);
      }
    });
  }

  open(trigger) {
    const videoUrl = trigger.dataset.videoUrl || '';
    const embedUrl = this.getEmbedUrl(videoUrl);

    if (!embedUrl) {
      window.open(videoUrl, '_blank', 'noopener');
      return;
    }

    const videoTitle = trigger.dataset.videoTitle || 'Video';
    this.lastFocusedElement = document.activeElement;
    this.title.textContent = videoTitle;
    this.frame.title = videoTitle;
    this.frame.src = embedUrl;
    this.modal.hidden = false;
    document.documentElement.classList.add('video-lightbox-open');

    window.requestAnimationFrame(() => {
      this.modal.classList.add('video-lightbox--is-visible');
      this.closeButton.focus({ preventScroll: true });
    });
  }

  close() {
    this.modal.classList.remove('video-lightbox--is-visible');
    document.documentElement.classList.remove('video-lightbox-open');
    this.frame.src = '';
    this.modal.hidden = true;

    if (this.lastFocusedElement instanceof HTMLElement && document.contains(this.lastFocusedElement)) {
      this.lastFocusedElement.focus({ preventScroll: true });
    }

    this.lastFocusedElement = null;
  }

  enterFullscreen() {
    const stage = this.modal.querySelector('[data-video-lightbox-stage]');

    if (stage && stage.requestFullscreen) {
      stage.requestFullscreen();
    }
  }

  trapFocus(event) {
    const focusable = Array.from(
      this.modal.querySelectorAll('button, [href], iframe, input, select, textarea, [tabindex]:not([tabindex="-1"])')
    ).filter(element => !element.disabled && element.offsetParent !== null);

    if (!focusable.length) {
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  getEmbedUrl(url) {
    try {
      const parsedUrl = new URL(url);
      const hostname = parsedUrl.hostname.replace(/^www\./, '');
      let videoId = '';

      if (hostname === 'youtu.be') {
        videoId = parsedUrl.pathname.replace('/', '');
      } else if (hostname.includes('youtube.com')) {
        videoId = parsedUrl.searchParams.get('v') || parsedUrl.pathname.split('/').filter(Boolean).pop();
      } else if (hostname.includes('vimeo.com')) {
        videoId = parsedUrl.pathname.split('/').filter(Boolean).pop();
        return videoId ? `https://player.vimeo.com/video/${videoId}?autoplay=1` : '';
      }

      return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0` : '';
    } catch (error) {
      return '';
    }
  }
}

export default VideoLightbox;
