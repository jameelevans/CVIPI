<?php
/** Public calendar feed and optional scheduling fields for existing Events. */
defined( 'ABSPATH' ) || exit;

function cvipi_calendar_timezone( $post_id = 0 ) {
  $zone = $post_id ? get_post_meta( $post_id, 'event_calendar_timezone', true ) : '';
  if ( $zone && in_array( $zone, timezone_identifiers_list(), true ) ) return new DateTimeZone( $zone );
  $label = $post_id ? trim( get_post_meta( $post_id, 'event_timezone', true ) ) : 'ET';
  $zones = array( 'ET' => 'America/New_York', 'EST' => 'America/New_York', 'EDT' => 'America/New_York', 'CT' => 'America/Chicago', 'CST' => 'America/Chicago', 'CDT' => 'America/Chicago', 'MT' => 'America/Denver', 'MST' => 'America/Denver', 'MDT' => 'America/Denver', 'PT' => 'America/Los_Angeles', 'PST' => 'America/Los_Angeles', 'PDT' => 'America/Los_Angeles', 'UTC' => 'UTC', 'GMT' => 'UTC' );
  if ( isset( $zones[strtoupper( $label )] ) ) return new DateTimeZone( $zones[strtoupper( $label )] );
  if ( in_array( $label, timezone_identifiers_list(), true ) ) return new DateTimeZone( $label );
  return $post_id ? wp_timezone() : new DateTimeZone( 'America/New_York' );
}

function cvipi_calendar_date( $value, $time, $zone ) {
  $value = (string) $value;
  $format = preg_match( '/^\d{8}$/', $value ) ? 'Ymd' : 'Y-m-d';
  $date = DateTimeImmutable::createFromFormat( '!' . $format, $value, $zone );
  if ( ! $date || $date->format( $format ) !== $value ) return null;
  if ( '' === $time ) return $date;
  if ( ! preg_match( '/^(\d{2}):(\d{2})(?::(\d{2}))?$/', (string) $time, $parts ) || (int) $parts[1] > 23 || (int) $parts[2] > 59 || (int) ( $parts[3] ?? 0 ) > 59 ) return null;
  $result = $date->setTime( (int) $parts[1], (int) $parts[2], (int) ( $parts[3] ?? 0 ) );
  // Reject nonexistent wall times at the spring DST transition.
  return $result->format( 'H:i' ) === substr( $time, 0, 5 ) ? $result : null;
}

function cvipi_calendar_event( $post_id ) {
  $post = get_post( $post_id );
  if ( ! $post || 'event' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password ) return null;
  $zone = cvipi_calendar_timezone( $post_id );
  $time = (string) get_post_meta( $post_id, 'event_time', true );
  $start = cvipi_calendar_date( get_post_meta( $post_id, 'event_date', true ), $time, $zone );
  if ( ! $start ) return null;
  $all_day = '' === $time;
  $end_date = get_post_meta( $post_id, 'event_end_date', true );
  $end_time = (string) get_post_meta( $post_id, 'event_end_time', true );
  $end = null;
  if ( $all_day ) {
    $last_day = $end_date ? cvipi_calendar_date( $end_date, '', $zone ) : $start;
    $end = $last_day && $last_day >= $start ? $last_day->modify( '+1 day' ) : $start->modify( '+1 day' );
  } elseif ( $end_time ) {
    $end = cvipi_calendar_date( $end_date ?: $start->format( 'Ymd' ), $end_time, $zone );
    if ( $end && $end <= $start ) $end = null;
  }
  $types = wp_get_post_terms( $post_id, 'event_type' );
  $topics = wp_get_post_terms( $post_id, 'event_topic' );
  $types = is_wp_error( $types ) ? array() : $types;
  $topics = is_wp_error( $topics ) ? array() : $topics;
  $display_start = $all_day ? $start : $start->setTimezone( cvipi_calendar_timezone() );
  $display_end = $end && ! $all_day ? $end->setTimezone( cvipi_calendar_timezone() ) : $end;
  $date_label = $display_start->format( 'l, F j, Y' );
  if ( $all_day && $end > $start->modify( '+1 day' ) ) $date_label .= ' - ' . $end->modify( '-1 day' )->format( 'F j, Y' );
  $time_label = $all_day ? 'All day' : $display_start->format( 'g:i A T' );
  if ( ! $all_day && $display_end ) $time_label .= ' - ' . $display_end->format( $display_end->format( 'Ymd' ) === $display_start->format( 'Ymd' ) ? 'g:i A T' : 'M j, g:i A T' );
  $text = function ( $value ) { return trim( html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' ) ); };
  $excerpt = $post->post_excerpt ?: strip_shortcodes( $post->post_content );
  return array(
    'id' => (string) $post_id, 'title' => $text( get_the_title( $post_id ) ),
    'start' => $start->format( $all_day ? 'Y-m-d' : DATE_ATOM ),
    'end' => $end ? $end->format( $all_day ? 'Y-m-d' : DATE_ATOM ) : null,
    'allDay' => $all_day, 'url' => get_permalink( $post_id ),
    'extendedProps' => array(
      'description' => $text( wp_trim_words( $excerpt, 65 ) ),
      'dateLabel' => $date_label, 'timeLabel' => $time_label, 'timezone' => $zone->getName(),
      'location' => $text( get_post_meta( $post_id, 'event_location', true ) ),
      'presenter' => $text( get_post_meta( $post_id, 'event_presented_by', true ) ),
      'types' => array_map( function ( $term ) { return array( 'name' => $term->name, 'slug' => $term->slug ); }, $types ),
      'topics' => array_map( function ( $term ) { return array( 'name' => $term->name, 'slug' => $term->slug ); }, $topics ),
      'rsvp' => esc_url_raw( get_post_meta( $post_id, 'event_cta_url', true ), array( 'http', 'https' ) ),
      'rsvpLabel' => $text( get_post_meta( $post_id, 'event_cta_label', true ) ) ?: 'Register',
      'recording' => (bool) get_post_meta( $post_id, 'event_recording_url', true ),
      'image' => get_the_post_thumbnail_url( $post_id, 'large' ) ?: '',
      'featured' => '1' === get_post_meta( $post_id, 'event_calendar_featured', true ),
      'download' => add_query_arg( array( 'action' => 'cvipi_calendar_download', 'event' => $post_id ), admin_url( 'admin-post.php' ) ),
    ),
  );
}

function cvipi_calendar_events( $start, $end, $filters = array() ) {
  // Two days of padding includes events whose local date crosses a feed boundary.
  $lower = $start->modify( '-2 days' )->format( 'Ymd' );
  $upper = $end->modify( '+2 days' )->format( 'Ymd' );
  $args = array(
    'post_type' => 'event', 'post_status' => 'publish', 'has_password' => false,
    'posts_per_page' => 1001, 'fields' => 'ids', 'no_found_rows' => true,
    'meta_key' => 'event_date', 'orderby' => 'meta_value_num', 'order' => 'ASC',
    'meta_query' => array(
      array( 'key' => 'event_date', 'value' => $upper, 'compare' => '<=', 'type' => 'NUMERIC' ),
      array( 'relation' => 'OR',
        array( 'key' => 'event_date', 'value' => $lower, 'compare' => '>=', 'type' => 'NUMERIC' ),
        array( 'key' => 'event_end_date', 'value' => $lower, 'compare' => '>=', 'type' => 'NUMERIC' ),
      ),
    ),
  );
  if ( ! empty( $filters['search'] ) ) $args['s'] = sanitize_text_field( $filters['search'] );
  foreach ( array( 'type' => 'event_type', 'topic' => 'event_topic' ) as $key => $taxonomy ) {
    if ( ! empty( $filters[$key] ) ) $args['tax_query'][] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => sanitize_title( $filters[$key] ) );
  }
  $ids = get_posts( $args );
  if ( count( $ids ) > 1000 ) return new WP_Error( 'calendar_limit', 'Please narrow your date range or filters.', array( 'status' => 422 ) );
  $events = array();
  foreach ( $ids as $id ) {
    $event = cvipi_calendar_event( $id );
    if ( ! $event ) continue;
    $range_zone = $event['allDay'] ? cvipi_calendar_timezone() : cvipi_calendar_timezone( $id );
    $event_start = new DateTimeImmutable( $event['start'], $range_zone );
    $event_end = $event['end'] ? new DateTimeImmutable( $event['end'], $range_zone ) : $event_start;
    if ( $event_start < $end && ( $event_end > $start || ( $event_start >= $start && $event_end === $event_start ) ) ) $events[] = $event;
  }
  return $events;
}

function cvipi_calendar_feed( WP_REST_Request $request ) {
  $zone = cvipi_calendar_timezone();
  $start = cvipi_calendar_date( $request['start'], '', $zone );
  $end = cvipi_calendar_date( $request['end'], '', $zone );
  if ( ! $start || ! $end || $end <= $start || $end > $start->modify( '+370 days' ) ) return new WP_Error( 'calendar_range', 'Choose a valid range of up to one year.', array( 'status' => 400 ) );
  $events = cvipi_calendar_events( $start, $end, array( 'search' => $request['search'], 'type' => $request['type'], 'topic' => $request['topic'] ) );
  if ( is_wp_error( $events ) ) return $events;
  $response = new WP_REST_Response( $events );
  $response->header( 'Cache-Control', 'no-store' );
  return $response;
}

add_action( 'rest_api_init', function () {
  $args = array();
  foreach ( array( 'start', 'end', 'search', 'type', 'topic' ) as $name ) $args[$name] = array( 'type' => 'string', 'required' => in_array( $name, array( 'start', 'end' ), true ), 'sanitize_callback' => 'sanitize_text_field' );
  register_rest_route( 'cvipi/v1', '/calendar', array( 'methods' => 'GET', 'callback' => 'cvipi_calendar_feed', 'permission_callback' => '__return_true', 'args' => $args ) );
} );

function cvipi_calendar_featured_events() {
  $zone = cvipi_calendar_timezone();
  $now = new DateTimeImmutable( 'now', $zone );
  $lower = $now->modify( '-1 day' )->format( 'Ymd' );
  $ids = get_posts( array(
    'post_type' => 'event', 'post_status' => 'publish', 'has_password' => false,
    'numberposts' => 100, 'fields' => 'ids', 'meta_key' => 'event_date', 'orderby' => 'meta_value_num', 'order' => 'ASC',
    'meta_query' => array( 'relation' => 'OR',
      array( 'key' => 'event_date', 'value' => $lower, 'compare' => '>=', 'type' => 'NUMERIC' ),
      array( 'key' => 'event_end_date', 'value' => $lower, 'compare' => '>=', 'type' => 'NUMERIC' ),
    ),
  ) );
  $events = array();
  foreach ( $ids as $id ) {
    $event = cvipi_calendar_event( $id );
    if ( $event && new DateTimeImmutable( $event['end'] ?: $event['start'], $event['allDay'] ? cvipi_calendar_timezone() : cvipi_calendar_timezone( $id ) ) >= $now ) $events[] = $event;
  }
  usort( $events, function ( $a, $b ) use ( $zone ) {
    return ( (int) $b['extendedProps']['featured'] <=> (int) $a['extendedProps']['featured'] ) ?: ( new DateTimeImmutable( $a['start'], $zone ) <=> new DateTimeImmutable( $b['start'], $zone ) );
  } );
  return array_slice( $events, 0, 3 );
}

function cvipi_calendar_ics( $event ) {
  $escape = function ( $text ) { return str_replace( array( '\\', "\r\n", "\r", "\n", ';', ',' ), array( '\\\\', '\\n', '\\n', '\\n', '\\;', '\\,' ), (string) $text ); };
  $lines = array( 'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//CVIPI//TTA Calendar//EN', 'CALSCALE:GREGORIAN', 'BEGIN:VEVENT', 'UID:cvipi-event-' . $event['id'] . '@' . wp_parse_url( home_url(), PHP_URL_HOST ), 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) );
  foreach ( array( 'start' => 'DTSTART', 'end' => 'DTEND' ) as $field => $property ) {
    if ( ! $event[$field] ) continue;
    $date = new DateTimeImmutable( $event[$field], new DateTimeZone( 'UTC' ) );
    $lines[] = $event['allDay'] ? $property . ';VALUE=DATE:' . $date->format( 'Ymd' ) : $property . ':' . $date->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
  }
  $lines[] = 'SUMMARY:' . $escape( $event['title'] );
  $lines[] = 'DESCRIPTION:' . $escape( $event['extendedProps']['description'] );
  $lines[] = 'LOCATION:' . $escape( $event['extendedProps']['location'] );
  $lines[] = 'URL:' . $escape( $event['url'] );
  $lines[] = 'END:VEVENT';
  $lines[] = 'END:VCALENDAR';
  // Fold by bytes without splitting UTF-8 characters (RFC 5545).
  foreach ( $lines as &$line ) {
    $folded = array();
    $limit = 75;
    while ( strlen( $line ) > $limit ) {
      $part = mb_strcut( $line, 0, $limit, 'UTF-8' );
      $folded[] = $part;
      $line = substr( $line, strlen( $part ) );
      $limit = 74;
    }
    $folded[] = $line;
    $line = implode( "\r\n ", $folded );
  }
  return implode( "\r\n", $lines ) . "\r\n";
}

function cvipi_calendar_download() {
  $event = cvipi_calendar_event( absint( $_GET['event'] ?? 0 ) );
  if ( ! $event ) wp_die( 'Event not found.', 'Event not found', array( 'response' => 404 ) );
  nocache_headers();
  header( 'Content-Type: text/calendar; charset=utf-8' );
  header( 'Content-Disposition: attachment; filename="cvipi-event-' . $event['id'] . '.ics"' );
  header( 'X-Content-Type-Options: nosniff' );
  echo cvipi_calendar_ics( $event );
  exit;
}
add_action( 'admin_post_cvipi_calendar_download', 'cvipi_calendar_download' );
add_action( 'admin_post_nopriv_cvipi_calendar_download', 'cvipi_calendar_download' );

add_action( 'acf/init', function () {
  if ( ! function_exists( 'acf_add_local_field' ) ) return;
  $fields = array(
    array( 'key' => 'field_cvipi_event_end_date', 'name' => 'event_end_date', 'label' => 'End Date (optional)', 'type' => 'date_picker', 'display_format' => 'F j, Y', 'return_format' => 'Y-m-d', 'instructions' => 'For multi-day events. All-day end dates include this day.' ),
    array( 'key' => 'field_cvipi_event_end_time', 'name' => 'event_end_time', 'label' => 'End Time (optional)', 'type' => 'time_picker', 'display_format' => 'g:i A', 'return_format' => 'H:i', 'instructions' => 'For timed events. Leave blank if the end time is not confirmed.' ),
    array( 'key' => 'field_cvipi_event_calendar_timezone', 'name' => 'event_calendar_timezone', 'label' => 'Calendar Time Zone', 'type' => 'select', 'allow_null' => 1, 'choices' => array( 'America/New_York' => 'Eastern', 'America/Chicago' => 'Central', 'America/Denver' => 'Mountain', 'America/Phoenix' => 'Arizona', 'America/Los_Angeles' => 'Pacific', 'America/Anchorage' => 'Alaska', 'Pacific/Honolulu' => 'Hawaii', 'UTC' => 'UTC' ), 'instructions' => 'Leave blank to use the existing Timezone Label. The TTA Calendar displays timed events in Eastern Time.' ),
    array( 'key' => 'field_cvipi_event_calendar_featured', 'name' => 'event_calendar_featured', 'label' => 'Feature on TTA Calendar', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Prioritizes this published upcoming event in Featured Events.' ),
  );
  foreach ( $fields as $field ) acf_add_local_field( array_merge( $field, array( 'parent' => 'group_cvipi_event_details' ) ) );
}, 20 );

add_filter( 'acf/validate_value/name=event_end_date', function ( $valid, $value ) {
  if ( true !== $valid || ! $value ) return $valid;
  $start = sanitize_text_field( $_POST['acf']['field_cvipi_event_date'] ?? '' );
  return $start && str_replace( '-', '', $value ) < str_replace( '-', '', $start ) ? 'The end date must be on or after the event date.' : $valid;
}, 10, 2 );

add_filter( 'acf/validate_value/name=event_end_time', function ( $valid, $value ) {
  if ( true !== $valid || ! $value ) return $valid;
  $fields = $_POST['acf'] ?? array();
  $date = sanitize_text_field( $fields['field_cvipi_event_date'] ?? '' );
  $end = sanitize_text_field( $fields['field_cvipi_event_end_date'] ?? '' ) ?: $date;
  $time = sanitize_text_field( $fields['field_cvipi_event_time'] ?? '' );
  if ( ! $time ) return 'Add an event start time before setting an end time.';
  return $date === $end && $value <= $time ? 'The end time must be after the start time. For overnight events, set an end date.' : $valid;
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function () {
  if ( ! is_page_template( 'page-tta-calendar.php' ) && ! is_page( 'tta-calendar' ) ) return;
  $reveals = '/assets/js/tta-calendar-reveals.js';
  if ( is_file( get_template_directory() . $reveals ) ) {
    // Intentionally synchronous in the head: reveal styles must be ready before first paint.
    wp_enqueue_script( 'cvipi-tta-calendar-reveals', get_template_directory_uri() . $reveals, array(), filemtime( get_template_directory() . $reveals ), false );
  }
  $file = '/assets/js/tta-calendar-bundled.js';
  if ( ! is_file( get_template_directory() . $file ) ) return;
  wp_enqueue_script( 'cvipi-tta-calendar', get_template_directory_uri() . $file, array(), filemtime( get_template_directory() . $file ), array( 'strategy' => 'defer', 'in_footer' => true ) );
} );
