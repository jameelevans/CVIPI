class FrontPage {
  constructor() {
    this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    this.initBannerVideoCarousel();
    this.initBannerNumberCounts();
    this.initAboutSectionAnimations();
  }

  initBannerVideoCarousel() {
    const banner = document.querySelector('.banner[data-banner-videos]');
    const video = banner ? banner.querySelector('.banner__video') : null;
    const desktopMedia = window.matchMedia('(min-width: 50em)');

    if (!banner || !video) {
      return;
    }

    let videos = [];

    try {
      // The data attribute is JSON encoded in header.php.
      videos = JSON.parse(banner.dataset.bannerVideos);
    } catch (error) {
      return;
    }

    if (!Array.isArray(videos) || videos.length === 0) {
      return;
    }

    video.muted = true;
    video.playsInline = true;

    let currentVideo = 0;
    let hasEndedListener = false;

    const unloadVideo = () => {
      video.pause();
      video.removeAttribute('src');
      video.load();
    };

    const playVideo = (index) => {
      const nextVideo = videos[index];

      if (!nextVideo || !nextVideo.src) {
        return;
      }

      video.src = nextVideo.src;

      // Poster images are optional per ACF repeater row.
      if (nextVideo.poster) {
        video.poster = nextVideo.poster;
      } else {
        video.removeAttribute('poster');
      }

      video.load();
      video.play().catch(() => {});
    };

    const enableDesktopVideo = () => {
      if (videos.length === 1) {
        // Native looping is enough when editors upload only one banner video.
        video.loop = true;
      } else if (!hasEndedListener) {
        video.addEventListener('ended', () => {
          // Move forward one item and wrap back to the first video forever.
          currentVideo = (currentVideo + 1) % videos.length;
          playVideo(currentVideo);
        });

        hasEndedListener = true;
      }

      playVideo(currentVideo);
    };

    const syncVideoToViewport = () => {
      if (desktopMedia.matches) {
        enableDesktopVideo();
        return;
      }

      unloadVideo();
    };

    syncVideoToViewport();

    if (desktopMedia.addEventListener) {
      desktopMedia.addEventListener('change', syncVideoToViewport);
    } else if (desktopMedia.addListener) {
      desktopMedia.addListener(syncVideoToViewport);
    }
  }

  initBannerNumberCounts() {
    const numbers = Array.from(document.querySelectorAll('.banner__number'));

    if (!numbers.length) {
      return;
    }

    const animateNumber = (number) => {
      const numberData = this.getNumberData(number.textContent);

      if (!numberData) {
        return;
      }

      if (this.reducedMotion.matches) {
        number.textContent = numberData.original;
        return;
      }

      const duration = 1600;
      const startTime = performance.now();

      const tick = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = 1 - Math.pow(1 - progress, 3);
        const currentValue = numberData.endValue * easedProgress;

        number.textContent = this.formatNumber(currentValue, numberData);

        if (progress < 1) {
          window.requestAnimationFrame(tick);
          return;
        }

        number.textContent = numberData.original;
      };

      number.textContent = this.formatNumber(0, numberData);
      window.requestAnimationFrame(tick);
    };

    if (!('IntersectionObserver' in window)) {
      numbers.forEach(animateNumber);
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        animateNumber(entry.target);
        observer.unobserve(entry.target);
      });
    }, {
      threshold: 0.35
    });

    numbers.forEach((number) => observer.observe(number));
  }

  getNumberData(rawNumber) {
    const original = rawNumber.trim();
    const match = original.match(/^([^0-9-]*)(-?[\d,]+(?:\.\d+)?)(.*)$/);

    if (!match) {
      return null;
    }

    const numericText = match[2].replace(/,/g, '');
    const endValue = Number(numericText);

    if (!Number.isFinite(endValue)) {
      return null;
    }

    return {
      original,
      prefix: match[1],
      suffix: match[3],
      endValue,
      hasDecimal: numericText.includes('.')
    };
  }

  formatNumber(value, numberData) {
    const formattedValue = numberData.hasDecimal
      ? value.toLocaleString(undefined, { maximumFractionDigits: 1 })
      : Math.round(value).toLocaleString();

    return `${numberData.prefix}${formattedValue}${numberData.suffix}`;
  }

  initAboutSectionAnimations() {
    const aboutRows = Array.from(document.querySelectorAll('.about__container'));

    if (!aboutRows.length) {
      return;
    }

    // JS adds this setup class so the about content is never hidden when
    // JavaScript is unavailable or the animation cannot run.
    aboutRows.forEach((row) => {
      row.classList.add('about__container--ready');
    });

    if (this.reducedMotion.matches) {
      aboutRows.forEach((row) => {
        row.classList.add('about__container--is-visible');
      });
      return;
    }

    const revealRow = (row) => {
      row.classList.add('about__container--is-visible');
    };

    // A bottom root margin of -80px means each row reveals only after the
    // bottom of the viewport has passed 80px beyond the row's top edge.
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          revealRow(entry.target);
          observer.unobserve(entry.target);
        });
      }, {
        rootMargin: '0px 0px -80px 0px',
        threshold: 0
      });

      aboutRows.forEach((row) => observer.observe(row));
      return;
    }

    const revealRowsOnScroll = () => {
      aboutRows.forEach((row) => {
        if (row.classList.contains('about__container--is-visible')) {
          return;
        }

        if (row.getBoundingClientRect().top <= window.innerHeight - 80) {
          revealRow(row);
        }
      });
    };

    revealRowsOnScroll();
    window.addEventListener('scroll', revealRowsOnScroll, { passive: true });
  }
}

export default FrontPage;
