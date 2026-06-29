class SiteAnimations {
  constructor() {
    this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    this.parallaxItems = [];
    this.pendingRevealElements = new WeakSet();
    this.scrollTicking = false;

    document.documentElement.classList.add('site-animations-ready');

    this.initFadeUpReveals();
    this.initSplitReveals();
    this.initNumberCounts();
    this.initMissionHeaderParallax();
    this.initAboutSectionAnimations();
  }

  initFadeUpReveals() {
    this.initPageLoadReveal(['.cvipi-hero__content'], 'site-animation--fade-up-ready');
    this.initToggleReveal(['.mission__statement'], 'site-animation--fade-up-ready');
  }

  initSplitReveals() {
    const storyContent = Array.from(document.querySelectorAll('.our-story__content'));
    const storyMedia = Array.from(document.querySelectorAll('.our-story__media'));

    if (!storyContent.length && !storyMedia.length) {
      return;
    }

    storyContent.forEach((element) => {
      element.classList.add('site-animation--from-left');
    });

    storyMedia.forEach((element) => {
      element.classList.add('site-animation--from-right');
    });

    this.initPageLoadSplitReveal([...storyContent, ...storyMedia]);
  }

  initPageLoadSplitReveal(elements) {
    elements.forEach((element) => {
      element.classList.add('site-animation--slide-ready');
    });

    if (this.reducedMotion.matches) {
      elements.forEach((element) => {
        element.classList.add('site-animation--is-visible');
      });
      return;
    }

    window.setTimeout(() => {
      elements.forEach((element) => this.showRevealElement(element));
    }, 220);
  }

  initPageLoadReveal(selectors, readyClass) {
    const elements = Array.from(document.querySelectorAll(selectors.join(',')));

    if (!elements.length) {
      return;
    }

    elements.forEach((element) => {
      element.classList.add(readyClass);
    });

    if (this.reducedMotion.matches) {
      elements.forEach((element) => {
        element.classList.add('site-animation--is-visible');
      });
      return;
    }

    window.setTimeout(() => {
      elements.forEach((element) => this.showRevealElement(element));
    }, 120);
  }

  initStoryRevealElements(elements) {
    elements.forEach((element) => {
      element.classList.add('site-animation--slide-ready');
    });

    if (this.reducedMotion.matches) {
      elements.forEach((element) => {
        element.classList.add('site-animation--is-visible');
      });
      return;
    }

    window.setTimeout(() => {
      this.revealVisibleElements(elements);
    }, 360);

    window.setTimeout(() => {
      this.watchRevealElements(elements, '0px 0px -8% 0px');
    }, 1000);
  }

  initRevealElements(elements, readyClass) {
    elements.forEach((element) => {
      element.classList.add(readyClass);
    });

    if (this.reducedMotion.matches) {
      elements.forEach((element) => {
        element.classList.add('site-animation--is-visible');
      });
      return;
    }

    if ('IntersectionObserver' in window) {
      this.watchRevealElements(elements, '0px 0px -8% 0px');
      this.revealVisibleElements(elements);
      return;
    }

    const revealElementsOnScroll = () => {
      elements.forEach((element) => {
        const rect = element.getBoundingClientRect();
        const isVisible = rect.top <= window.innerHeight * 0.92 && rect.bottom >= 0;

        if (isVisible) {
          this.showRevealElement(element);
          return;
        }

        this.hideRevealElement(element);
      });
    };

    revealElementsOnScroll();
    window.addEventListener('scroll', revealElementsOnScroll, { passive: true });
  }

  watchRevealElements(elements, rootMargin) {
    if (!('IntersectionObserver' in window)) {
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          this.showRevealElement(entry.target);
          return;
        }

        this.hideRevealElement(entry.target);
      });
    }, {
      rootMargin,
      threshold: 0
    });

    elements.forEach((element) => observer.observe(element));
  }

  initToggleReveal(selectors, readyClass) {
    const elements = Array.from(document.querySelectorAll(selectors.join(',')));

    if (!elements.length) {
      return;
    }

    elements.forEach((element) => {
      element.classList.add(readyClass);
    });

    if (this.reducedMotion.matches) {
      elements.forEach((element) => {
        element.classList.add('site-animation--is-visible');
      });
      return;
    }

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            this.showRevealElement(entry.target);
            return;
          }

          this.hideRevealElement(entry.target);
        });
      }, {
        rootMargin: '0px 0px 0px 0px',
        threshold: 0
      });

      elements.forEach((element) => observer.observe(element));
      this.revealVisibleElements(elements);
      return;
    }

    const revealOnScroll = () => {
      elements.forEach((element) => {
        const rect = element.getBoundingClientRect();
        const isVisible = rect.top <= window.innerHeight * 0.82 && rect.bottom >= window.innerHeight * 0.12;

        if (isVisible) {
          this.showRevealElement(element);
          return;
        }

        this.hideRevealElement(element);
      });
    };

    revealOnScroll();
    window.addEventListener('scroll', revealOnScroll, { passive: true });
  }

  revealVisibleElements(elements) {
    elements.forEach((element) => {
      const rect = element.getBoundingClientRect();
      const isVisible = rect.top <= window.innerHeight && rect.bottom >= 0;

      if (isVisible) {
        this.showRevealElement(element);
      }
    });
  }

  showRevealElement(element) {
    if (element.classList.contains('site-animation--is-visible') || this.pendingRevealElements.has(element)) {
      return;
    }

    this.pendingRevealElements.add(element);

    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        element.classList.add('site-animation--is-visible');
        this.pendingRevealElements.delete(element);
      });
    });
  }

  hideRevealElement(element) {
    if (this.pendingRevealElements.has(element)) {
      return;
    }

    element.classList.remove('site-animation--is-visible');
  }

  initNumberCounts() {
    const numbers = Array.from(document.querySelectorAll('.banner__number, .we-serve__number'));

    if (!numbers.length) {
      return;
    }

    const animateNumber = (number) => {
      if (number.dataset.siteAnimationCounted === 'true') {
        return;
      }

      const numberData = this.getNumberData(number.textContent, number.innerHTML);

      if (!numberData) {
        return;
      }

      number.dataset.siteAnimationCounted = 'true';

      if (this.reducedMotion.matches) {
        number.innerHTML = numberData.originalHtml;
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

        number.innerHTML = numberData.originalHtml;
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
      rootMargin: '0px 0px -10% 0px',
      threshold: 0.35
    });

    numbers.forEach((number) => observer.observe(number));
  }

  getNumberData(numberText, numberHtml) {
    const original = numberText.trim();
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
      originalHtml: numberHtml,
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

  initMissionHeaderParallax() {
    const header = document.querySelector('.mission__header');
    const mission = header ? header.closest('.mission') : null;

    if (!header || !mission) {
      return;
    }

    header.classList.add('site-animation--parallax-ready');

    if (this.reducedMotion.matches) {
      return;
    }

    this.parallaxItems.push({ header, mission });
    this.updateParallaxItems();

    window.addEventListener('scroll', () => this.requestParallaxUpdate(), { passive: true });
    window.addEventListener('resize', () => this.requestParallaxUpdate());
  }

  requestParallaxUpdate() {
    if (this.scrollTicking) {
      return;
    }

    this.scrollTicking = true;
    window.requestAnimationFrame(() => {
      this.updateParallaxItems();
      this.scrollTicking = false;
    });
  }

  updateParallaxItems() {
    this.parallaxItems.forEach(({ header, mission }) => {
      const rect = mission.getBoundingClientRect();
      const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
      const range = viewportHeight + rect.height;
      const rawProgress = (viewportHeight - rect.top) / range;
      const progress = Math.min(Math.max(rawProgress, 0), 1);
      const translateY = -32 * progress;
      const opacity = Math.max(0, 1 - (progress * 1.45));

      header.style.opacity = opacity.toFixed(3);
      header.style.transform = `translate3d(-50%, ${translateY.toFixed(2)}px, 0)`;
    });
  }

  initAboutSectionAnimations() {
    const aboutRows = Array.from(document.querySelectorAll('.about__container'));

    if (!aboutRows.length) {
      return;
    }

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

export default SiteAnimations;
