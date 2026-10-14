import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import luxonPlugin, { toLuxonDateTime } from '@fullcalendar/luxon3';
import { DateTime } from 'luxon';
import { createIcons, ChevronLeft, ChevronRight, CalendarDays, Columns3, Calendar as CalendarIcon, List, Globe2, RefreshCw, ArrowRight, ArrowUpRight, X, Clock, MapPin, Users, CalendarPlus } from 'lucide';

const views = { dayGridMonth: 'month', timeGridWeek: 'week', timeGridDay: 'day', listMonth: 'month' };
const categoryStyles = { webinars: 'webinar', webinar: 'webinar', 'peer-learning': 'peer', conferences: 'conference', conference: 'conference', panels: 'panel', training: 'training', trainings: 'training', workshops: 'training' };

class TtaCalendar {
  constructor(root) {
    this.root = root;
    this.config = JSON.parse(root.querySelector('[data-calendar-config]').textContent);
    this.canvas = root.querySelector('[data-calendar-canvas]');
    this.dialog = root.querySelector('[data-calendar-dialog]');
    this.status = root.querySelector('[data-calendar-status]');
    this.error = root.querySelector('[data-calendar-error]');
    this.viewport = root.querySelector('[data-calendar-viewport]');
    this.motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    this.mobile = window.matchMedia('(max-width: 37.4375em)');
    this.requestId = 0;
    this.query = new URL(window.location.href);
    const requestedView = this.query.searchParams.get('calendar_view');

    createIcons({ icons: { ChevronLeft, ChevronRight, CalendarDays, Columns3, Calendar: CalendarIcon, List, Globe2, RefreshCw, ArrowRight, ArrowUpRight, X, Clock, MapPin, Users, CalendarPlus }, attrs: { 'aria-hidden': 'true', focusable: 'false' } });
    root.querySelector('[data-calendar-toolbar]').hidden = false;
    this.viewport.hidden = false;
    root.querySelector('[data-calendar-fallback]').hidden = true;
    root.querySelectorAll('[data-calendar-legend]').forEach(el => el.classList.add(`tta-calendar__category--${categoryStyles[el.dataset.calendarLegend] || 'other'}`));

    this.calendar = new Calendar(this.canvas, {
      plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin, luxonPlugin],
      initialView: Object.hasOwn(views, requestedView) ? requestedView : 'dayGridMonth',
      initialDate: this.config.date,
      timeZone: this.config.timezone,
      locale: 'en', firstDay: 0, headerToolbar: false,
      height: 'auto', fixedWeekCount: false, dayMaxEvents: this.mobile.matches ? 1 : 3,
      eventInteractive: true, editable: false, navLinks: true,
      lazyFetching: false, expandRows: true, nowIndicator: true,
      slotDuration: '00:30:00', slotMinTime: '00:00:00', slotMaxTime: '24:00:00', scrollTime: '08:00:00',
      slotLabelFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
      eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
      noEventsContent: 'No events are scheduled for this month.',
      buttonText: { today: 'Today' },
      events: info => this.fetchEvents(info),
      datesSet: info => this.updatePeriod(info),
      loading: active => {
        this.canvas.setAttribute('aria-busy', String(active));
        this.root.classList.toggle('is-loading', active);
        if (active) this.status.textContent = 'Loading events...';
      },
      eventClassNames: info => [`tta-calendar__category--${categoryStyles[info.event.extendedProps.types?.[0]?.slug] || 'other'}`],
      eventContent: info => this.renderEvent(info),
      eventDidMount: info => {
        const label = `${info.event.title}. ${info.event.extendedProps.dateLabel}. ${info.event.extendedProps.timeLabel}. View event details.`;
        info.el.setAttribute('aria-label', label);
        info.el.setAttribute('title', label);
      },
      eventClick: info => {
        if (info.jsEvent.metaKey || info.jsEvent.ctrlKey || info.jsEvent.shiftKey || info.jsEvent.altKey) return;
        info.jsEvent.preventDefault();
        this.openEvent(info.event, info.el);
      },
      navLinkDayClick: date => this.calendar.changeView('timeGridDay', date),
      dateClick: info => this.calendar.changeView('timeGridDay', info.dateStr.slice(0, 10)),
    });
    this.bindControls();
    this.calendar.render();
  }

  async fetchEvents(info) {
    const id = ++this.requestId;
    this.abort?.abort();
    this.abort = new AbortController();
    const url = new URL(this.config.feed, window.location.origin);
    url.searchParams.set('start', info.startStr.slice(0, 10));
    url.searchParams.set('end', info.endStr.slice(0, 10));
    try {
      const response = await fetch(url, { signal: this.abort.signal, credentials: 'same-origin', cache: 'no-store' });
      const events = await response.json();
      if (!response.ok || !Array.isArray(events)) throw new Error('Calendar feed unavailable');
      if (id === this.requestId) {
        this.status.textContent = '';
        this.error.hidden = true;
        this.viewport.hidden = false;
      }
      return events;
    } catch (error) {
      // FullCalendar discards outdated fetches; cancelled requests are not failures.
      if (id !== this.requestId) return [];
      this.error.hidden = false;
      this.viewport.hidden = true;
      this.status.textContent = 'Events are temporarily unavailable.';
      throw error;
    }
  }

  renderEvent(info) {
    const content = document.createElement(info.view.type.startsWith('list') ? 'a' : 'span');
    if (content.tagName === 'A') content.href = info.event.url;
    content.className = 'tta-calendar__event-content';
    if (info.timeText && !info.event.allDay) {
      const time = document.createElement('span');
      time.className = 'tta-calendar__event-time';
      time.textContent = info.timeText;
      content.append(time);
    }
    const title = document.createElement('span');
    title.className = 'tta-calendar__event-name';
    title.textContent = info.event.title;
    content.append(title);
    return { domNodes: [content] };
  }

  updatePeriod(info) {
    const timedView = info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay';
    const height = timedView ? parseFloat(getComputedStyle(document.documentElement).fontSize) * 65 : 'auto';
    if (this.calendar.getOption('height') !== height) this.calendar.setOption('height', height);
    if (timedView) requestAnimationFrame(() => this.calendar.scrollToTime('08:00:00'));
    this.root.querySelector('[data-calendar-title]').textContent = info.view.title;
    this.root.dataset.calendarActiveView = info.view.type;
    this.root.querySelectorAll('[data-calendar-view]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.calendarView === info.view.type)));
    ['prev', 'next'].forEach(direction => this.root.querySelector(`[data-calendar-${direction}]`).setAttribute('aria-label', `${direction === 'prev' ? 'Previous' : 'Next'} ${views[info.view.type]}`));
    this.updateUrl();
    if (!this.motion.matches) this.canvas.animate([{ opacity: 0.4, transform: 'translateY(0.4rem)' }, { opacity: 1, transform: 'translateY(0)' }], { duration: 240, easing: 'ease-out' });
  }

  updateUrl() {
    ['search', 'type', 'topic'].forEach(key => this.query.searchParams.delete(`calendar_${key}`));
    this.query.searchParams.set('calendar_date', toLuxonDateTime(this.calendar.getDate(), this.calendar).toISODate());
    this.query.searchParams.set('calendar_view', this.calendar.view.type);
    window.history.replaceState(null, '', this.query);
  }

  bindControls() {
    this.root.querySelector('[data-calendar-prev]').addEventListener('click', () => this.calendar.prev());
    this.root.querySelector('[data-calendar-next]').addEventListener('click', () => this.calendar.next());
    this.root.querySelector('[data-calendar-today]').addEventListener('click', () => this.calendar.today());
    this.root.querySelectorAll('[data-calendar-view]').forEach(button => button.addEventListener('click', () => {
      if (this.calendar.view.type !== button.dataset.calendarView) this.calendar.changeView(button.dataset.calendarView);
    }));
    this.root.querySelector('[data-calendar-retry]').addEventListener('click', () => this.calendar.refetchEvents());
    this.root.querySelector('[data-calendar-view-all]').addEventListener('click', click => {
      if (click.metaKey || click.ctrlKey || click.shiftKey || click.altKey) return;
      if (this.calendar.view.type !== 'listMonth') this.calendar.changeView('listMonth');
    });
    this.root.querySelectorAll('[data-calendar-featured]').forEach(link => {
      const event = JSON.parse(link.dataset.calendarFeatured);
      event.start = DateTime.fromISO(event.start, { zone: this.config.timezone }).toJSDate();
      event.end = event.end ? DateTime.fromISO(event.end, { zone: this.config.timezone }).toJSDate() : null;
      link.setAttribute('aria-haspopup', 'dialog');
      link.setAttribute('aria-controls', this.dialog.id);
      link.addEventListener('click', click => {
        if (click.metaKey || click.ctrlKey || click.shiftKey || click.altKey) return;
        click.preventDefault();
        this.openEvent(event, link);
      });
    });
    this.dialog.querySelector('[data-calendar-close]').addEventListener('click', () => this.dialog.close());
    this.dialog.addEventListener('click', event => {
      const rect = this.dialog.getBoundingClientRect();
      if (event.target === this.dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) this.dialog.close();
    });
    this.dialog.addEventListener('close', () => {
      document.documentElement.classList.remove('tta-calendar-dialog-open');
      if (this.returnFocus?.isConnected) this.returnFocus.focus({ preventScroll: true });
    });
    this.mobile.addEventListener('change', event => this.calendar.setOption('dayMaxEvents', event.matches ? 1 : 3));
  }

  openEvent(event, element) {
    const details = event.extendedProps;
    this.returnFocus = element.matches('a, button, [tabindex]') ? element : element.querySelector('a[href]');
    const text = (key, value) => { this.dialog.querySelector(`[data-calendar-event-${key}]`).textContent = value || ''; };
    text('title', event.title);
    text('type', details.types?.map(type => type.name).join(' / ') || 'CVIPI Event');
    text('date', details.dateLabel);
    text('time', details.timeLabel);
    text('description', details.description);
    text('location', details.location);
    text('presenter', details.presenter);
    this.dialog.querySelector('[data-calendar-location-row]').hidden = !details.location;
    this.dialog.querySelector('[data-calendar-presenter-row]').hidden = !details.presenter;
    const topics = this.dialog.querySelector('[data-calendar-event-topics]');
    topics.replaceChildren(...(details.topics || []).map(topic => {
      const badge = document.createElement('span'); badge.textContent = topic.name; return badge;
    }));
    const image = this.dialog.querySelector('[data-calendar-event-image]');
    image.hidden = !details.image;
    if (details.image) image.src = details.image;
    else image.removeAttribute('src');
    const rsvp = this.dialog.querySelector('[data-calendar-event-rsvp]');
    const ended = (event.end || event.start) < new Date();
    rsvp.hidden = !details.rsvp || ended || details.recording;
    if (details.rsvp) rsvp.href = details.rsvp;
    rsvp.querySelector('span').textContent = details.rsvpLabel || 'Register';
    this.dialog.querySelector('[data-calendar-event-url]').href = event.url;
    this.dialog.querySelector('[data-calendar-event-download]').href = details.download;
    this.dialog.showModal();
    document.documentElement.classList.add('tta-calendar-dialog-open');
    this.dialog.querySelector('[data-calendar-event-title]').focus();
  }

}

document.querySelectorAll('[data-tta-calendar]').forEach(root => {
  try { new TtaCalendar(root); } catch (error) {
    root.querySelector('[data-calendar-fallback]').hidden = false;
    root.querySelector('[data-calendar-viewport]').hidden = true;
    root.querySelector('[data-calendar-toolbar]').hidden = true;
    console.error('CVIPI calendar initialization failed', error);
  }
});
