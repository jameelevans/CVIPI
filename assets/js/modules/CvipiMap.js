class CvipiMap {
  constructor() {
    this.map = document.querySelector('[data-cvipi-map]');

    if (!this.map || typeof cvipiAjax === 'undefined') {
      return;
    }

    this.filters = document.querySelector('[data-map-filters]');
    this.viewport = this.map.querySelector('[data-map-viewport]');
    this.stage = this.map.querySelector('[data-map-stage]');
    this.markersLayer = this.map.querySelector('[data-map-markers]');
    this.popup = this.map.querySelector('[data-map-popup]');
    this.canEdit = this.map.dataset.canEdit === 'true';
    this.scale = 1;
    this.translate = { x: 0, y: 0 };
    this.isPanning = false;
    this.panStart = null;
    this.activeDragMarker = null;
    this.dragMoved = false;

    this.popupFields = {
      title: this.map.querySelector('[data-map-popup-title]'),
      content: this.map.querySelector('[data-map-popup-content]'),
      year: this.map.querySelector('[data-map-popup-year]'),
      award: this.map.querySelector('[data-map-popup-award]'),
      location: this.map.querySelector('[data-map-popup-location]')
    };

    this.bindEvents();
    this.prepareSvg();
  }

  bindEvents() {
    if (this.filters) {
      this.filters.addEventListener('click', event => {
        const button = event.target.closest('[data-map-filter]');

        if (!button) {
          return;
        }

        this.filterMarkers(button);
      });
    }

    if (this.canEdit) {
      this.markersLayer.addEventListener('pointerdown', event => this.startMarkerDrag(event));
    }

    this.markersLayer.addEventListener('pointerover', event => this.previewMarker(event));
    this.markersLayer.addEventListener('pointerout', event => this.closeMarkerPreview(event));
    this.markersLayer.addEventListener('focusin', event => this.previewMarker(event));
    this.markersLayer.addEventListener('focusout', event => this.closeMarkerPreview(event));
    this.markersLayer.addEventListener('click', event => this.openMarkerFromClick(event));

    this.viewport.addEventListener('pointerdown', event => this.startPan(event));
    this.viewport.addEventListener('wheel', event => this.handleWheel(event), { passive: false });
    window.addEventListener('scroll', () => this.hidePopup(), { passive: true });

    document.addEventListener('pointermove', event => {
      this.moveMarker(event);
      this.movePan(event);
    });
    document.addEventListener('pointerup', event => {
      this.endMarkerDrag(event);
      this.endPan();
    });

    const close = this.map.querySelector('[data-map-popup-close]');
    const zoomIn = this.map.querySelector('[data-map-zoom-in]');
    const zoomOut = this.map.querySelector('[data-map-zoom-out]');
    const reset = this.map.querySelector('[data-map-reset]');

    if (close) {
      close.addEventListener('click', () => this.hidePopup());
    }

    if (zoomIn) {
      zoomIn.addEventListener('click', () => this.zoomBy(0.2));
    }

    if (zoomOut) {
      zoomOut.addEventListener('click', () => this.zoomBy(-0.2));
    }

    if (reset) {
      reset.addEventListener('click', () => this.resetTransform());
    }
  }

  prepareSvg() {
    const paths = this.map.querySelectorAll('.cvipi-map__base svg path');

    paths.forEach(path => {
      path.classList.add('cvipi-map__state');
    });
  }

  filterMarkers(button) {
    const formData = new FormData();
    formData.append('action', 'cvipi_filter_map_markers');
    formData.append('nonce', cvipiAjax.nonce);
    formData.append('fiscal_year', button.dataset.mapFilter || 'all');

    this.map.classList.add('cvipi-map--is-loading');
    this.hidePopup();

    fetch(cvipiAjax.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(response => response.json())
      .then(response => {
        if (!response.success) {
          throw new Error('Map filtering failed.');
        }

        this.markersLayer.innerHTML = response.data.html;
        this.filters.querySelectorAll('[data-map-filter]').forEach(filter => {
          filter.classList.toggle('is-active', filter === button);
        });
      })
      .catch(() => {
        this.markersLayer.innerHTML = '<p class="cvipi-map__empty">Something went wrong while filtering markers.</p>';
      })
      .finally(() => {
        this.map.classList.remove('cvipi-map--is-loading');
      });
  }

  previewMarker(event) {
    const marker = event.target.closest('.cvipi-map__marker');

    if (!marker || !this.usesHoverPopup()) {
      return;
    }

    this.showPopup(marker);
  }

  closeMarkerPreview(event) {
    const marker = event.target.closest('.cvipi-map__marker');

    if (!marker || !this.usesHoverPopup()) {
      return;
    }

    if (event.relatedTarget && marker.contains(event.relatedTarget)) {
      return;
    }

    this.hidePopup();
  }

  openMarkerFromClick(event) {
    const marker = event.target.closest('.cvipi-map__marker');

    if (!marker || this.dragMoved || this.usesHoverPopup()) {
      return;
    }

    this.showPopup(marker);
  }

  usesHoverPopup() {
    return window.matchMedia('(min-width: 768px)').matches;
  }

  showPopup(marker) {
    const markerRect = marker.getBoundingClientRect();
    const mapRect = this.map.getBoundingClientRect();
    const markerLeft = markerRect.left + (markerRect.width / 2) - mapRect.left;
    const markerTop = markerRect.top + (markerRect.height / 2) - mapRect.top;

    this.popupFields.title.textContent = marker.dataset.markerTitle || '';
    this.popupFields.content.textContent = marker.dataset.markerContent || '';
    this.popupFields.year.textContent = marker.dataset.markerYear || '';
    this.popupFields.award.textContent = marker.dataset.markerAward || '';
    this.popupFields.location.textContent = marker.dataset.markerLocation || '';
    this.popup.hidden = false;
    this.popup.style.left = `${markerLeft}px`;
    this.popup.style.top = `${markerTop}px`;
    this.popup.classList.toggle('cvipi-map__popup--right', markerLeft < mapRect.width / 2);
  }

  hidePopup() {
    if (this.popup) {
      this.popup.hidden = true;
    }
  }

  startMarkerDrag(event) {
    const marker = event.target.closest('.cvipi-map__marker');

    if (!this.canEdit || !marker || marker.dataset.canEdit !== 'true') {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    this.activeDragMarker = marker;
    this.dragMoved = false;
    this.map.classList.add('cvipi-map--is-editing');
    marker.setPointerCapture(event.pointerId);
  }

  moveMarker(event) {
    if (!this.activeDragMarker) {
      return;
    }

    const position = this.getStagePosition(event.clientX, event.clientY);
    this.dragMoved = true;
    this.activeDragMarker.style.left = `${position.x}%`;
    this.activeDragMarker.style.top = `${position.y}%`;
    this.hidePopup();
  }

  endMarkerDrag(event) {
    if (!this.activeDragMarker) {
      return;
    }

    const marker = this.activeDragMarker;
    const position = this.getStagePosition(event.clientX, event.clientY);
    const formData = new FormData();
    formData.append('action', 'cvipi_update_map_marker_position');
    formData.append('nonce', cvipiAjax.nonce);
    formData.append('marker_id', marker.dataset.markerId);
    formData.append('x', position.x);
    formData.append('y', position.y);

    marker.releasePointerCapture(event.pointerId);
    this.activeDragMarker = null;
    this.map.classList.remove('cvipi-map--is-editing');

    fetch(cvipiAjax.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    }).catch(() => {
      marker.classList.add('cvipi-map__marker--save-error');
    });
  }

  getStagePosition(clientX, clientY) {
    const rect = this.stage.getBoundingClientRect();
    const x = Math.min(100, Math.max(0, ((clientX - rect.left) / rect.width) * 100));
    const y = Math.min(100, Math.max(0, ((clientY - rect.top) / rect.height) * 100));

    return {
      x: x.toFixed(2),
      y: y.toFixed(2)
    };
  }

  startPan(event) {
    if (event.target.closest('.cvipi-map__marker')) {
      return;
    }

    this.isPanning = true;
    this.panStart = {
      x: event.clientX - this.translate.x,
      y: event.clientY - this.translate.y
    };
  }

  movePan(event) {
    if (!this.isPanning || !this.panStart) {
      return;
    }

    this.translate.x = event.clientX - this.panStart.x;
    this.translate.y = event.clientY - this.panStart.y;
    this.applyTransform();
  }

  endPan() {
    this.isPanning = false;
    this.panStart = null;
  }

  handleWheel(event) {
    event.preventDefault();
    this.zoomBy(event.deltaY > 0 ? -0.12 : 0.12);
  }

  zoomBy(amount) {
    this.scale = Math.min(3, Math.max(1, this.scale + amount));

    if (this.scale === 1) {
      this.translate = { x: 0, y: 0 };
    }

    this.applyTransform();
  }

  resetTransform() {
    this.scale = 1;
    this.translate = { x: 0, y: 0 };
    this.applyTransform();
  }

  applyTransform() {
    this.stage.style.transform = `translate(-50%, -50%) translate(${this.translate.x}px, ${this.translate.y}px) scale(${this.scale})`;
  }
}

export default CvipiMap;
