class EventFilters {
  constructor() {
    this.archive = document.querySelector('[data-events-archive]');

    if (!this.archive || typeof cvipiAjax === 'undefined') {
      return;
    }

    this.form = this.archive.querySelector('.events-page__filters');
    this.grid = this.archive.querySelector('[data-events-grid]');
    this.count = this.archive.querySelector('[data-events-count]');
    this.statusPills = Array.from(this.archive.querySelectorAll('[data-event-status-filter]'));
    this.typePills = Array.from(this.archive.querySelectorAll('[data-event-type-filter]'));
    this.loadMoreButton = this.archive.querySelector('[data-events-load-more]');
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
      this.fetchEvents();
    });

    this.form.addEventListener('input', event => {
      if (event.target.matches('input[type="search"]')) {
        window.clearTimeout(this.debounceTimer);
        this.debounceTimer = window.setTimeout(() => this.fetchEvents(), 250);
      }
    });

    this.form.addEventListener('change', event => {
      if (event.target.matches('select')) {
        this.fetchEvents();
      }
    });

    this.statusPills.forEach(pill => {
      pill.addEventListener('click', event => {
        event.preventDefault();
        const statusField = this.form.querySelector('[name="event_status"]');

        if (statusField) {
          statusField.value = pill.dataset.eventStatusFilter || '';
        }

        this.fetchEvents();
      });
    });

    this.typePills.forEach(pill => {
      pill.addEventListener('click', event => {
        event.preventDefault();
        const typeField = this.form.querySelector('[name="event_type"]');

        if (typeField) {
          typeField.value = pill.dataset.eventTypeFilter || '';
        }

        this.fetchEvents();
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
    formData.append('action', 'cvipi_filter_events');
    formData.append('nonce', cvipiAjax.nonce);

    return formData;
  }

  fetchEvents() {
    this.archive.classList.add('events-page--is-loading');

    fetch(cvipiAjax.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: this.getFormData()
    })
      .then(response => response.json())
      .then(response => {
        if (!response.success) {
          throw new Error('Event filtering failed.');
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
        this.grid.innerHTML = '<p class="events-page__empty">Something went wrong while filtering events.</p>';
      })
      .finally(() => {
        this.archive.classList.remove('events-page--is-loading');
      });
  }

  updatePills() {
    const statusField = this.form.querySelector('[name="event_status"]');
    const typeField = this.form.querySelector('[name="event_type"]');
    const activeStatus = statusField ? statusField.value : '';
    const activeType = typeField ? typeField.value : '';

    this.statusPills.forEach(pill => {
      pill.classList.toggle('is-active', (pill.dataset.eventStatusFilter || '') === activeStatus);
    });

    this.typePills.forEach(pill => {
      pill.classList.toggle('is-active', (pill.dataset.eventTypeFilter || '') === activeType);
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
    const cards = Array.from(this.grid.querySelectorAll('.events-page__event-card'));

    cards.forEach((card, index) => {
      card.hidden = index >= this.visibleCount;
    });

    if (this.loadMoreButton) {
      this.loadMoreButton.hidden = cards.length <= this.visibleCount;
    }
  }

  animateVisibleCards(startIndex = 0) {
    const cards = Array.from(this.grid.querySelectorAll('.events-page__event-card:not([hidden])')).slice(startIndex);

    cards.forEach((card, index) => {
      card.classList.add('events-page__event-card--fade-ready');
      card.classList.remove('events-page__event-card--fade-in');

      window.setTimeout(() => {
        card.classList.add('events-page__event-card--fade-in');
        card.classList.remove('events-page__event-card--fade-ready');
      }, index * 45);
    });
  }
}

export default EventFilters;
