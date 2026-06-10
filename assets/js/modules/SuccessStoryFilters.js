class SuccessStoryFilters {
  constructor() {
    this.archive = document.querySelector('[data-success-stories-archive]');

    if (!this.archive || typeof cvipiAjax === 'undefined') {
      return;
    }

    this.grid = this.archive.querySelector('[data-success-stories-grid]');
    this.pastGrid = this.archive.querySelector('[data-success-stories-past-grid]');
    this.count = this.archive.querySelector('[data-success-stories-count]');
    this.pastCount = this.archive.querySelector('[data-success-stories-past-count]');
    this.filters = Array.from(this.archive.querySelectorAll('[data-story-filter]'));

    if (!this.grid || !this.filters.length) {
      return;
    }

    this.bindEvents();
  }

  bindEvents() {
    this.filters.forEach(filter => {
      filter.addEventListener('click', () => this.fetchStories(filter));
    });
  }

  getFormData(filter) {
    const formData = new FormData();
    formData.append('action', 'cvipi_filter_success_stories');
    formData.append('nonce', cvipiAjax.nonce);
    formData.append('story_tag', filter.dataset.storyFilter || '');

    return formData;
  }

  fetchStories(filter) {
    if (filter.classList.contains('is-active')) {
      return;
    }

    this.archive.classList.add('stories--is-loading');

    fetch(cvipiAjax.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: this.getFormData(filter)
    })
      .then(response => response.json())
      .then(response => {
        if (!response.success) {
          throw new Error('Success story filtering failed.');
        }

        this.grid.innerHTML = response.data.featured_html || response.data.html;

        if (this.pastGrid) {
          this.pastGrid.innerHTML = response.data.past_html || '';
        }

        if (this.count) {
          this.count.textContent = response.data.label;
        }

        if (this.pastCount) {
          this.pastCount.textContent = response.data.past_label;
        }

        this.updateFilters(filter);
        this.updateUrl(filter.dataset.storyFilter || '');
      })
      .catch(() => {
        this.grid.innerHTML = '<p class="stories__empty">Something went wrong while filtering success stories.</p>';

        if (this.pastGrid) {
          this.pastGrid.innerHTML = '';
        }

        if (this.pastCount) {
          this.pastCount.textContent = '0 stories';
        }
      })
      .finally(() => {
        window.requestAnimationFrame(() => {
          this.archive.classList.remove('stories--is-loading');
        });
      });
  }

  updateFilters(activeFilter) {
    this.filters.forEach(filter => {
      const isActive = filter === activeFilter;
      filter.classList.toggle('is-active', isActive);
      filter.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  updateUrl(storyTag) {
    const params = new URLSearchParams(window.location.search);

    if (storyTag) {
      params.set('story_tag', storyTag);
    } else {
      params.delete('story_tag');
    }

    const nextUrl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
    window.history.replaceState({}, '', nextUrl);
  }
}

export default SuccessStoryFilters;
