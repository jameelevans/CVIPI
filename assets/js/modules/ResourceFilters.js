class ResourceFilters {
  constructor() {
    this.archive = document.querySelector('[data-resources-archive]');

    if (!this.archive || typeof cvipiAjax === 'undefined') {
      return;
    }

    this.form = this.archive.querySelector('.resources-page__filters');
    this.grid = this.archive.querySelector('[data-resources-grid]');
    this.count = this.archive.querySelector('[data-resources-count]');
    this.pills = Array.from(this.archive.querySelectorAll('[data-resource-filter]'));
    this.loadMoreButton = this.archive.querySelector('[data-resources-load-more]');
    this.visibleCount = 6;
    this.batchSize = 6;
    this.debounceTimer = null;

    if (!this.form || !this.grid) {
      return;
    }

    this.bindEvents();
    this.syncVisibleCards();
  }

  bindEvents() {
    this.form.addEventListener('submit', event => {
      event.preventDefault();
      this.fetchResources();
    });

    this.form.addEventListener('input', event => {
      if (event.target.matches('input[type="search"]')) {
        window.clearTimeout(this.debounceTimer);
        this.debounceTimer = window.setTimeout(() => this.fetchResources(), 250);
      }
    });

    this.form.addEventListener('change', event => {
      if (event.target.matches('select')) {
        this.fetchResources();
      }
    });

    this.pills.forEach(pill => {
      pill.addEventListener('click', event => {
        event.preventDefault();
        const categoryField = this.form.querySelector('[name="resource_category"]');

        if (categoryField) {
          categoryField.value = pill.dataset.resourceFilter || '';
        }

        this.fetchResources();
      });
    });

    if (this.loadMoreButton) {
      this.loadMoreButton.addEventListener('click', () => {
        const previousVisibleCount = this.visibleCount;
        this.visibleCount += this.batchSize;
        this.syncVisibleCards();
        this.animateVisibleCards(previousVisibleCount);
      });
    }
  }

  getFormData() {
    const formData = new FormData(this.form);
    formData.append('action', 'cvipi_filter_resources');
    formData.append('nonce', cvipiAjax.nonce);

    if (this.archive.dataset.featuredResourceId) {
      formData.append('exclude_ids[]', this.archive.dataset.featuredResourceId);
    }

    return formData;
  }

  fetchResources() {
    this.archive.classList.add('resources-page--is-loading');

    fetch(cvipiAjax.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: this.getFormData()
    })
      .then(response => response.json())
      .then(response => {
        if (!response.success) {
          throw new Error('Resource filtering failed.');
        }

        this.grid.innerHTML = response.data.html;

        if (this.count) {
          this.count.textContent = response.data.label;
        }

        this.visibleCount = this.batchSize;
        this.updatePills();
        this.updateUrl();
        this.syncVisibleCards();
        this.animateVisibleCards();
      })
      .catch(() => {
        this.grid.innerHTML = '<p class="resources-page__empty">Something went wrong while filtering resources.</p>';
      })
      .finally(() => {
        this.archive.classList.remove('resources-page--is-loading');
      });
  }

  updatePills() {
    const categoryField = this.form.querySelector('[name="resource_category"]');
    const activeCategory = categoryField ? categoryField.value : '';

    this.pills.forEach(pill => {
      pill.classList.toggle('is-active', (pill.dataset.resourceFilter || '') === activeCategory);
    });
  }

  updateUrl() {
    const params = new URLSearchParams(new FormData(this.form));

    Array.from(params.keys()).forEach(key => {
      if (!params.get(key)) {
        params.delete(key);
      }
    });

    const nextUrl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
    window.history.replaceState({}, '', nextUrl);
  }

  syncVisibleCards() {
    const cards = Array.from(this.grid.querySelectorAll('.resources-page__card'));

    cards.forEach((card, index) => {
      card.hidden = index >= this.visibleCount;
    });

    if (this.loadMoreButton) {
      this.loadMoreButton.hidden = cards.length <= this.visibleCount;
    }
  }

  animateVisibleCards(startIndex = 0) {
    const cards = Array.from(this.grid.querySelectorAll('.resources-page__card:not([hidden])')).slice(startIndex);

    cards.forEach((card, index) => {
      card.classList.add('resources-page__card--fade-ready');
      card.classList.remove('resources-page__card--fade-in');

      window.setTimeout(() => {
        card.classList.add('resources-page__card--fade-in');
        card.classList.remove('resources-page__card--fade-ready');
      }, index * 45);
    });
  }

}

export default ResourceFilters;
