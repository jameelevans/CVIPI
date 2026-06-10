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
    this.activeMarker = null;
    this.dragMoved = false;
    this.popupCloseTimer = null;
    this.popupDragStart = null;
    this.popupDragPointerId = null;
    this.lastFocusedElement = null;

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

    this.popup.addEventListener('pointerdown', event => this.startPopupDismissDrag(event));
    this.viewport.addEventListener('pointerdown', event => this.startPan(event));
    this.viewport.addEventListener('keydown', event => this.handleViewportKeydown(event));
    this.viewport.addEventListener('wheel', event => this.handleWheel(event), { passive: false });
    window.addEventListener('scroll', () => this.hidePopup({ immediate: true }), { passive: true });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && this.popup && !this.popup.hidden) {
        event.preventDefault();
        this.hidePopup();
      }
    });
    document.addEventListener('pointerdown', event => this.closePopupFromOutsideClick(event));

    document.addEventListener('pointermove', event => {
      this.moveMarker(event);
      this.movePan(event);
      this.movePopupDismissDrag(event);
    });
    document.addEventListener('pointerup', event => {
      this.endMarkerDrag(event);
      this.endPan();
      this.endPopupDismissDrag(event);
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
    this.popup.style.setProperty('--popup-marker-color', this.getMarkerColor(marker));
    this.setActiveMarker(marker);
    this.lastFocusedElement = document.activeElement;
    window.clearTimeout(this.popupCloseTimer);
    this.popup.classList.remove('cvipi-map__popup--is-dismissing');
    this.popup.hidden = false;
    this.popup.style.setProperty('--popup-drag-x', '0px');
    this.popup.style.setProperty('--popup-drag-y', '0px');
    this.positionPopup(markerLeft, markerTop, mapRect);

    window.requestAnimationFrame(() => {
      if (!this.popup || this.popup.hidden) {
        return;
      }

      this.map.classList.add('cvipi-map--popup-active');
      this.popup.classList.add('cvipi-map__popup--is-visible');
    });

    if (!this.usesHoverPopup()) {
      this.popup.setAttribute('aria-modal', 'true');
      this.popup.focus({ preventScroll: true });
    } else {
      this.popup.setAttribute('aria-modal', 'false');
    }
  }

  positionPopup(markerLeft, markerTop, mapRect) {
    if (!this.popup) {
      return;
    }

    if (!this.usesHoverPopup()) {
      this.popup.classList.remove(
        'cvipi-map__popup--placement-top',
        'cvipi-map__popup--placement-bottom',
        'cvipi-map__popup--placement-left',
        'cvipi-map__popup--placement-right'
      );
      this.popup.removeAttribute('style');
      this.popup.style.setProperty('--popup-drag-x', '0px');
      this.popup.style.setProperty('--popup-drag-y', '0px');
      return;
    }

    const gap = 18;
    const viewportPadding = 16;

    this.popup.classList.remove(
      'cvipi-map__popup--placement-top',
      'cvipi-map__popup--placement-bottom',
      'cvipi-map__popup--placement-left',
      'cvipi-map__popup--placement-right'
    );
    this.popup.classList.add('cvipi-map__popup--placement-top');
    this.popup.style.left = '0px';
    this.popup.style.top = '0px';

    const popupRect = this.popup.getBoundingClientRect();
    const popupWidth = popupRect.width;
    const popupHeight = popupRect.height;
    const viewportTopOffset = this.getFixedHeaderBottom() + viewportPadding;
    const viewportMinLeft = Math.max(0, viewportPadding - mapRect.left);
    const viewportMaxLeft = Math.min(mapRect.width - popupWidth, window.innerWidth - mapRect.left - popupWidth - viewportPadding);
    const viewportMinTop = Math.max(0, viewportTopOffset - mapRect.top);
    const viewportMaxTop = Math.min(mapRect.height - popupHeight, window.innerHeight - mapRect.top - popupHeight - viewportPadding);
    const canFitLeft = markerLeft - gap - popupWidth >= viewportMinLeft;
    const canFitRight = markerLeft + gap + popupWidth <= viewportMaxLeft + popupWidth;
    const wouldOverflowRight = markerLeft + (popupWidth / 2) > viewportMaxLeft;
    const wouldOverflowLeft = markerLeft - (popupWidth / 2) < viewportMinLeft;
    let placement = 'top';
    let left = markerLeft - (popupWidth / 2);
    let top = markerTop - popupHeight - gap;

    if (top < viewportMinTop) {
      placement = 'bottom';
      top = markerTop + gap;
    } else if (wouldOverflowRight && canFitLeft) {
      placement = 'left';
      left = markerLeft - popupWidth - gap;
      top = markerTop - (popupHeight / 2);
    } else if (wouldOverflowLeft && canFitRight) {
      placement = 'right';
      left = markerLeft + gap;
      top = markerTop - (popupHeight / 2);
    }

    left = this.clamp(left, viewportMinLeft, viewportMaxLeft);
    top = this.clamp(top, viewportMinTop, viewportMaxTop);

    this.popup.classList.remove('cvipi-map__popup--placement-top');
    this.popup.classList.add(`cvipi-map__popup--placement-${placement}`);
    this.popup.style.left = `${left}px`;
    this.popup.style.top = `${top}px`;
    this.popup.style.setProperty('--popup-arrow-left', `${this.clamp(markerLeft - left, 18, popupWidth - 18)}px`);
    this.popup.style.setProperty('--popup-arrow-top', `${this.clamp(markerTop - top, 18, popupHeight - 18)}px`);
  }

  clamp(value, min, max) {
    if (max < min) {
      return min;
    }

    return Math.min(Math.max(value, min), max);
  }

  getFixedHeaderBottom() {
    const header = document.querySelector('.header');

    if (!header) {
      return 0;
    }

    const headerStyles = window.getComputedStyle(header);

    if (headerStyles.position !== 'fixed' && headerStyles.position !== 'sticky') {
      return 0;
    }

    return Math.max(0, header.getBoundingClientRect().bottom);
  }

  getMarkerColor(marker) {
    const markerColor = marker.style.getPropertyValue('--marker-color').trim();

    if (markerColor) {
      return markerColor;
    }

    return window.getComputedStyle(marker).getPropertyValue('--marker-color').trim() || 'var(--color-light-blue)';
  }

  hidePopup(options = {}) {
    const shouldAnimate = !options.immediate && !this.usesHoverPopup();

    this.clearActiveMarker();
    this.map.classList.remove('cvipi-map--popup-active');

    if (this.popup) {
      this.popup.classList.remove('cvipi-map__popup--is-visible');
      this.popup.classList.remove('cvipi-map__popup--is-dragging');
      this.popup.setAttribute('aria-modal', 'false');

      window.clearTimeout(this.popupCloseTimer);

      if (shouldAnimate) {
        this.popup.classList.add('cvipi-map__popup--is-dismissing');
        this.popupCloseTimer = window.setTimeout(() => this.finishPopupClose(), 260);
      } else {
        this.finishPopupClose();
      }
    }

    this.restoreMarkerFocus();
  }

  finishPopupClose() {
    if (!this.popup) {
      return;
    }

    this.popup.hidden = true;
    this.popup.classList.remove('cvipi-map__popup--is-dismissing');
    this.popup.style.setProperty('--popup-drag-x', '0px');
    this.popup.style.setProperty('--popup-drag-y', '0px');
  }

  restoreMarkerFocus() {
    if (this.lastFocusedElement instanceof HTMLElement && document.contains(this.lastFocusedElement) && !this.usesHoverPopup()) {
      this.lastFocusedElement.focus({ preventScroll: true });
    }

    this.lastFocusedElement = null;
  }

  setActiveMarker(marker) {
    this.clearActiveMarker();
    this.activeMarker = marker;
    this.activeMarker.setAttribute('aria-expanded', 'true');
  }

  clearActiveMarker() {
    if (!this.activeMarker) {
      return;
    }

    this.activeMarker.setAttribute('aria-expanded', 'false');
    this.activeMarker = null;
  }

  closePopupFromOutsideClick(event) {
    if (!this.popup || this.popup.hidden || this.usesHoverPopup()) {
      return;
    }

    if (this.popup.contains(event.target) || event.target.closest('.cvipi-map__marker')) {
      return;
    }

    this.hidePopup();
  }

  startPopupDismissDrag(event) {
    if (this.usesHoverPopup() || (event.button !== undefined && event.button !== 0)) {
      return;
    }

    const interactiveElement = event.target.closest('button, a, input, select, textarea');

    if (interactiveElement && interactiveElement !== this.popup) {
      return;
    }

    this.popupDragStart = {
      x: event.clientX,
      y: event.clientY
    };
    this.popupDragPointerId = event.pointerId;
    this.popup.classList.add('cvipi-map__popup--is-dragging');
    this.popup.setPointerCapture(event.pointerId);
  }

  movePopupDismissDrag(event) {
    if (!this.popupDragStart || event.pointerId !== this.popupDragPointerId) {
      return;
    }

    const deltaX = event.clientX - this.popupDragStart.x;
    const deltaY = event.clientY - this.popupDragStart.y;

    this.popup.style.setProperty('--popup-drag-x', `${deltaX}px`);
    this.popup.style.setProperty('--popup-drag-y', `${deltaY}px`);
  }

  endPopupDismissDrag(event) {
    if (!this.popupDragStart || event.pointerId !== this.popupDragPointerId) {
      return;
    }

    const deltaX = event.clientX - this.popupDragStart.x;
    const deltaY = event.clientY - this.popupDragStart.y;
    const dismissDistance = Math.max(Math.abs(deltaX), Math.abs(deltaY));
    this.popup.releasePointerCapture(event.pointerId);
    this.popupDragStart = null;
    this.popupDragPointerId = null;
    this.popup.classList.remove('cvipi-map__popup--is-dragging');

    if (dismissDistance > 90) {
      this.popup.style.setProperty('--popup-drag-x', `${deltaX * 3}px`);
      this.popup.style.setProperty('--popup-drag-y', `${deltaY * 3}px`);
      this.hidePopup();
      return;
    }

    this.popup.style.setProperty('--popup-drag-x', '0px');
    this.popup.style.setProperty('--popup-drag-y', '0px');
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
    if (event.button !== undefined && event.button !== 0) {
      return;
    }

    if (event.target.closest('.cvipi-map__marker')) {
      return;
    }

    event.preventDefault();
    this.isPanning = true;
    this.panStart = {
      x: event.clientX - this.translate.x,
      y: event.clientY - this.translate.y
    };
    this.map.classList.add('cvipi-map--is-panning');
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
    this.map.classList.remove('cvipi-map--is-panning');
  }

  handleViewportKeydown(event) {
    if (event.target !== this.viewport) {
      return;
    }

    const panStep = event.shiftKey ? 60 : 28;
    const keyActions = {
      ArrowUp: () => this.panBy(0, panStep),
      ArrowDown: () => this.panBy(0, -panStep),
      ArrowLeft: () => this.panBy(panStep, 0),
      ArrowRight: () => this.panBy(-panStep, 0),
      '+': () => this.zoomBy(0.2),
      '=': () => this.zoomBy(0.2),
      '-': () => this.zoomBy(-0.2),
      '_': () => this.zoomBy(-0.2),
      Home: () => this.resetTransform(),
      '0': () => this.resetTransform()
    };

    const action = keyActions[event.key];

    if (!action) {
      return;
    }

    if (event.key.startsWith('Arrow') && this.scale <= 1) {
      return;
    }

    event.preventDefault();
    action();
  }

  panBy(x, y) {
    this.translate.x += x;
    this.translate.y += y;
    this.applyTransform();
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
    this.map.classList.toggle('cvipi-map--is-zoomed', this.scale > 1);
  }
}

export default CvipiMap;
