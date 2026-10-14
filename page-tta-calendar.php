<?php
/**
 * Template Name: TTA Calendar
 * @package cvipi
 */
get_header( 'posts' );
$zone = cvipi_calendar_timezone();
$today = new DateTimeImmutable( 'now', $zone );
$selected = cvipi_calendar_date( sanitize_text_field( $_GET['calendar_date'] ?? '' ), '', $zone ) ?: $today;
$month = $selected->modify( 'first day of this month' )->setTime( 0, 0 );
$fallback = cvipi_calendar_events( $month, $month->modify( '+1 month' ) );
$types = cvipi_get_event_type_terms();
$featured = cvipi_calendar_featured_events();
$config = array( 'feed' => rest_url( 'cvipi/v1/calendar' ), 'today' => $today->format( 'Y-m-d' ), 'date' => $selected->format( 'Y-m-d' ), 'timezone' => $zone->getName() );
?>
<main id="main-content" class="tta-calendar" data-tta-calendar>
  <script type="application/json" data-calendar-config><?php echo wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
  <section class="tta-calendar__schedule" aria-labelledby="calendar-heading">
    <div class="tta-calendar__inner">
      <header class="tta-calendar__intro" data-calendar-reveal>
        <p class="tta-calendar__eyebrow">Learn &amp; Connect</p>
        <h2 id="calendar-heading" class="tta-calendar__heading">Browse the <em>full schedule</em></h2>
        <p class="tta-calendar__description">Training, conversations, and connections that strengthen community-led safety.</p>
      </header>
      <div class="tta-calendar__frame" data-calendar-reveal>
        <div class="tta-calendar__toolbar" data-calendar-toolbar hidden>
          <div class="tta-calendar__navigation" aria-label="Calendar date navigation">
            <button type="button" class="tta-calendar__icon-button" data-calendar-prev aria-label="Previous month" title="Previous period"><i data-lucide="chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="tta-calendar__icon-button" data-calendar-next aria-label="Next month" title="Next period"><i data-lucide="chevron-right" aria-hidden="true"></i></button>
            <button type="button" class="tta-calendar__today" data-calendar-today>Today</button>
          </div>
          <h3 class="tta-calendar__period" data-calendar-title><?php echo esc_html( $month->format( 'F Y' ) ); ?></h3>
          <div class="tta-calendar__views" role="group" aria-label="Calendar view">
            <?php foreach ( array( 'dayGridMonth' => array( 'Month', 'calendar-days' ), 'timeGridWeek' => array( 'Week', 'columns-3' ), 'timeGridDay' => array( 'Day', 'calendar' ), 'listMonth' => array( 'List', 'list' ) ) as $view => $item ) : ?>
              <button type="button" data-calendar-view="<?php echo esc_attr( $view ); ?>" aria-pressed="<?php echo 'dayGridMonth' === $view ? 'true' : 'false'; ?>"><i data-lucide="<?php echo esc_attr( $item[1] ); ?>" aria-hidden="true"></i><?php echo esc_html( $item[0] ); ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="tta-calendar__viewport" data-calendar-viewport tabindex="0" role="region" aria-label="Scrollable TTA event calendar" hidden><div class="tta-calendar__canvas" data-calendar-canvas></div></div>
        <div class="tta-calendar__fallback" data-calendar-fallback>
          <h3><?php echo esc_html( $month->format( 'F Y' ) ); ?></h3>
          <?php if ( is_wp_error( $fallback ) || ! $fallback ) : ?><p>No events are scheduled for this month.</p><?php else : ?>
            <ul><?php foreach ( $fallback as $event ) : ?><li><a href="<?php echo esc_url( $event['url'] ); ?>"><?php echo esc_html( $event['title'] ); ?></a><span><?php echo esc_html( $event['extendedProps']['dateLabel'] . ' / ' . $event['extendedProps']['timeLabel'] ); ?></span></li><?php endforeach; ?></ul>
          <?php endif; ?>
        </div>
        <div class="tta-calendar__error" data-calendar-error hidden><p>We couldn't load the events. Please try again.</p><button type="button" class="tta-calendar__button" data-calendar-retry><i data-lucide="refresh-cw" aria-hidden="true"></i>Retry</button></div>
      </div>
      <p class="tta-calendar__status" data-calendar-status role="status" aria-live="polite" aria-atomic="true"></p>
      <div class="tta-calendar__summary"><p><i data-lucide="globe-2" aria-hidden="true"></i>Times shown in Eastern Time</p></div>
      <div class="tta-calendar__legend" aria-label="Event types"><?php $legend_names = array(); if ( ! is_wp_error( $types ) ) foreach ( $types as $term ) : if ( isset( $legend_names[$term->name] ) ) continue; $legend_names[$term->name] = true; ?><span data-calendar-legend="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></span><?php endforeach; ?></div>
    </div>
  </section>
  <section class="tta-calendar__featured" aria-labelledby="calendar-featured-heading">
    <div class="tta-calendar__inner">
      <header class="tta-calendar__featured-header" data-calendar-reveal>
        <div><p class="tta-calendar__eyebrow">Coming Soon</p><h2 id="calendar-featured-heading" class="tta-calendar__heading">Featured Events</h2></div>
        <a href="#calendar-heading" class="tta-calendar__text-link" data-calendar-view-all>View All Events <i data-lucide="arrow-right" aria-hidden="true"></i></a>
      </header>
      <?php if ( $featured ) : ?>
        <ul class="tta-calendar__featured-grid">
          <?php foreach ( $featured as $event ) :
            $details = $event['extendedProps'];
            $card_start = ( new DateTimeImmutable( $event['start'], $zone ) )->setTimezone( $zone );
            $card_date = $card_start->format( 'F j, Y' );
            if ( $event['allDay'] && $event['end'] ) {
              $last_day = ( new DateTimeImmutable( $event['end'], $zone ) )->modify( '-1 day' );
              if ( $last_day > $card_start ) $card_date .= ' - ' . $last_day->format( 'F j, Y' );
            }
            $card_time = str_replace( array( 'EDT', 'EST' ), 'ET', $details['timeLabel'] );
          ?>
            <li data-calendar-reveal>
              <a class="tta-calendar__featured-card" href="<?php echo esc_url( $event['url'] ); ?>" data-calendar-featured="<?php echo esc_attr( wp_json_encode( $event ) ); ?>" aria-labelledby="calendar-featured-<?php echo esc_attr( $event['id'] ); ?>">
                <p class="tta-calendar__featured-date"><?php echo esc_html( $card_date ); ?> &middot; <?php echo esc_html( $card_time ); ?></p>
                <h3 id="calendar-featured-<?php echo esc_attr( $event['id'] ); ?>" class="tta-calendar__featured-title"><?php echo esc_html( $event['title'] ); ?></h3>
                <?php if ( $details['description'] ) : ?><p class="tta-calendar__featured-description"><?php echo esc_html( wp_trim_words( $details['description'], 25 ) ); ?></p><?php endif; ?>
                <?php if ( $details['types'] ) : ?><div class="tta-calendar__tags"><?php foreach ( $details['types'] as $type ) : ?><span><?php echo esc_html( $type['name'] ); ?></span><?php endforeach; ?></div><?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else : ?>
        <p class="tta-calendar__description" data-calendar-reveal>New events are coming soon. Please check back for upcoming opportunities.</p>
      <?php endif; ?>
    </div>
  </section>
  <section class="tta-calendar__support" aria-labelledby="calendar-support-heading">
    <div class="tta-calendar__support-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/tta-community.webp' ) ); ?>" alt="Community members gathering for a conversation" loading="lazy" decoding="async" width="1200" height="800"></div>
    <div class="tta-calendar__support-content" data-calendar-reveal><p class="tta-calendar__eyebrow">Need Support?</p><h2 id="calendar-support-heading" class="tta-calendar__heading">Can't find what <em>you need?</em></h2><p class="tta-calendar__description">Connect with our team for training, webinars, and peer learning opportunities that support your work.</p><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="tta-calendar__button">Request TA <i data-lucide="arrow-up-right" aria-hidden="true"></i></a></div>
  </section>
  <dialog id="calendar-event-dialog" class="tta-calendar__dialog" data-calendar-dialog aria-labelledby="calendar-event-title">
    <button type="button" class="tta-calendar__icon-button tta-calendar__close" data-calendar-close aria-label="Close event details" title="Close"><i data-lucide="x" aria-hidden="true"></i></button>
    <img data-calendar-event-image alt="" class="tta-calendar__dialog-image" hidden>
    <div class="tta-calendar__dialog-body"><p class="tta-calendar__eyebrow" data-calendar-event-type>CVIPI Event</p><h2 id="calendar-event-title" data-calendar-event-title tabindex="-1"></h2>
      <dl class="tta-calendar__event-details"><div><dt><i data-lucide="calendar-days" aria-hidden="true"></i>Date</dt><dd data-calendar-event-date></dd></div><div><dt><i data-lucide="clock" aria-hidden="true"></i>Time</dt><dd data-calendar-event-time></dd></div><div data-calendar-location-row><dt><i data-lucide="map-pin" aria-hidden="true"></i>Location</dt><dd data-calendar-event-location></dd></div><div data-calendar-presenter-row><dt><i data-lucide="users" aria-hidden="true"></i>Presented By</dt><dd data-calendar-event-presenter></dd></div></dl>
      <p class="tta-calendar__description" data-calendar-event-description></p><div class="tta-calendar__tags" data-calendar-event-topics></div>
      <div class="tta-calendar__dialog-actions"><a class="tta-calendar__button" data-calendar-event-rsvp target="_blank" rel="noopener noreferrer" hidden><span></span><i data-lucide="arrow-up-right" aria-hidden="true"></i></a><a class="tta-calendar__text-link" data-calendar-event-url>Event Details <i data-lucide="arrow-right" aria-hidden="true"></i></a><a class="tta-calendar__text-link" data-calendar-event-download><i data-lucide="calendar-plus" aria-hidden="true"></i>Add to Calendar</a></div>
    </div>
  </dialog>
</main>
<?php get_footer(); ?>
