<?php
/** Local integration checks; only temporary test Events are created and removed. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_contains( home_url(), 'cvipi.local' ) ) throw new RuntimeException( 'Local CVIPI CLI only.' );
$ids = array();
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) throw new RuntimeException( $message );
  $checks++;
  echo 'PASS: ' . $message . PHP_EOL;
};
$create = function ( $meta, $data = array() ) use ( &$ids ) {
  $id = wp_insert_post( array_merge( array( 'post_type' => 'event', 'post_status' => 'publish', 'post_title' => 'Calendar integration QA ' . wp_generate_uuid4(), 'post_content' => 'Temporary calendar test content.', 'meta_input' => array_merge( array( 'event_date' => '20261008', 'event_time' => '10:00', 'event_timezone' => 'ET' ), $meta ) ), $data ), true );
  if ( is_wp_error( $id ) ) throw new RuntimeException( $id->get_error_message() );
  $ids[] = $id;
  return $id;
};
$range = function ( $start, $end, $filters = array() ) { return cvipi_calendar_events( new DateTimeImmutable( $start, cvipi_calendar_timezone() ), new DateTimeImmutable( $end, cvipi_calendar_timezone() ), $filters ); };
$contains = function ( $events, $id ) { return in_array( (string) $id, array_column( $events, 'id' ), true ); };
try {
  $zone = cvipi_calendar_timezone();
  $assert( null === cvipi_calendar_date( '20260230', '', $zone ), 'Invalid dates cannot roll into another month' );
  $assert( null !== cvipi_calendar_date( '20240229', '', $zone ), 'Leap-day dates are supported' );
  $assert( null === cvipi_calendar_date( '20260308', '02:30', $zone ), 'Nonexistent DST wall times are rejected' );
  $id = $create( array() );
  $event = cvipi_calendar_event( $id );
  $assert( '2026-10-08T10:00:00-04:00' === $event['start'], 'Existing ET labels use the correct daylight-saving offset' );
  $assert( null === $event['end'], 'Missing end times are not fabricated' );
  $assert( $contains( $range( '2026-10-08', '2026-10-09' ), $id ), 'Published Events appear on their actual date' );
  $assert( ! $contains( $range( '2026-10-09', '2026-10-10' ), $id ), 'Events do not leak into the next day' );
  $assert( str_contains( cvipi_calendar_ics( $event ), 'DTSTART:20261008T140000Z' ), 'Calendar downloads use UTC for timed Events' );
  $pacific = $create( array( 'event_calendar_timezone' => 'America/Los_Angeles', 'event_time' => '22:00' ) );
  $pacific_event = cvipi_calendar_event( $pacific );
  $assert( str_contains( $pacific_event['extendedProps']['dateLabel'], 'October 9' ) && '1:00 AM EDT' === $pacific_event['extendedProps']['timeLabel'], 'Pacific Events display on the correct Eastern day' );
  $assert( $contains( $range( '2026-10-09', '2026-10-10' ), $pacific ), 'Cross-zone Events remain in the requested day feed' );
  $overnight = $create( array( 'event_date' => '20261005', 'event_time' => '20:00', 'event_end_date' => '20261006', 'event_end_time' => '12:00' ) );
  $assert( $contains( $range( '2026-10-06', '2026-10-07' ), $overnight ), 'Multi-day timed Events are included when overlapping a view' );
  $assert( ! $contains( $range( '2026-10-07', '2026-10-08' ), $overnight ), 'Ended multi-day Events are excluded' );
  $all_day = $create( array( 'event_time' => '', 'event_date' => '20260401', 'event_end_date' => '20260403' ) );
  $all_day_event = cvipi_calendar_event( $all_day );
  $assert( $all_day_event['allDay'] && '2026-04-04' === $all_day_event['end'], 'All-day end dates are inclusive in the editor and exclusive in the feed' );
  $assert( str_contains( cvipi_calendar_ics( $all_day_event ), 'DTEND;VALUE=DATE:20260404' ), 'All-day downloads preserve the inclusive last day' );
  $pacific_all_day = $create( array( 'event_time' => '', 'event_calendar_timezone' => 'America/Los_Angeles' ) );
  $assert( ! $contains( $range( '2026-10-09', '2026-10-10' ), $pacific_all_day ), 'All-day dates do not spill into another day because of their time zone' );
  $assert( $contains( $range( '2026-10-08', '2026-10-09' ), $pacific_all_day ), 'All-day dates remain visible on their calendar date' );
  $_POST['acf'] = array( 'field_cvipi_event_date' => '20261008', 'field_cvipi_event_time' => '10:00' );
  $assert( true !== apply_filters( 'acf/validate_value/name=event_end_date', true, '20261007' ), 'Editor rejects an end date before the start' );
  $assert( true !== apply_filters( 'acf/validate_value/name=event_end_time', true, '09:00' ), 'Editor rejects a same-day end time before the start' );
  $_POST['acf']['field_cvipi_event_end_date'] = '20261009';
  $assert( true === apply_filters( 'acf/validate_value/name=event_end_time', true, '09:00' ), 'Editor permits an overnight end time on the following date' );
  unset( $_POST['acf'] );
  foreach ( array( 'draft', 'pending', 'private', 'future' ) as $status ) {
    $hidden = $create( array(), array( 'post_status' => $status, 'post_date' => '2099-01-01 00:00:00' ) );
    $assert( null === cvipi_calendar_event( $hidden ) && ! $contains( $range( '2026-10-01', '2026-11-01' ), $hidden ), $status . ' Events are never exposed' );
  }
  $protected = $create( array(), array( 'post_password' => 'calendar-test-password' ) );
  $assert( null === cvipi_calendar_event( $protected ) && ! $contains( $range( '2026-10-01', '2026-11-01' ), $protected ), 'Password-protected Events are never exposed' );
  $now = new DateTimeImmutable( 'now', $zone );
  $ongoing = $create( array( 'event_date' => $now->modify( '-3 days' )->format( 'Ymd' ), 'event_time' => '', 'event_end_date' => $now->modify( '+1 day' )->format( 'Ymd' ), 'event_calendar_featured' => '1' ) );
  $early_eastern = $create( array( 'event_date' => $now->modify( '+1 day' )->format( 'Ymd' ), 'event_time' => '02:00', 'event_calendar_timezone' => 'America/New_York', 'event_calendar_featured' => '1' ) );
  $later_pacific = $create( array( 'event_date' => $now->modify( '+1 day' )->format( 'Ymd' ), 'event_time' => '01:00', 'event_calendar_timezone' => 'America/Los_Angeles', 'event_calendar_featured' => '1' ) );
  $featured = cvipi_calendar_featured_events();
  $assert( $contains( $featured, $ongoing ), 'Featured Events include ongoing multi-day Events that started earlier' );
  $featured_ids = array_column( $featured, 'id' );
  $assert( false !== array_search( (string) $early_eastern, $featured_ids, true ) && false !== array_search( (string) $later_pacific, $featured_ids, true ) && array_search( (string) $early_eastern, $featured_ids, true ) < array_search( (string) $later_pacific, $featured_ids, true ), 'Featured Events sort by actual start time across time zones' );
  $assert( count( $featured ) <= 3, 'Featured Events never exceed three cards' );
  $term = get_term_by( 'slug', 'webinars', 'event_type' );
  wp_set_post_terms( $id, array( $term->term_id ), 'event_type' );
  $assert( $contains( $range( '2026-10-01', '2026-11-01', array( 'type' => 'webinars' ) ), $id ), 'Event-type filtering returns matching Events' );
  $assert( ! $contains( $range( '2026-10-01', '2026-11-01', array( 'type' => 'conferences' ) ), $id ), 'Event-type filtering excludes other types' );
  $assert( $contains( $range( '2026-10-01', '2026-11-01', array( 'search' => get_the_title( $id ) ) ), $id ), 'Search reads the existing Event title' );
  $request = new WP_REST_Request( 'GET', '/cvipi/v1/calendar' );
  $request->set_param( 'start', '2026-10-01' ); $request->set_param( 'end', '2026-11-01' );
  $response = rest_do_request( $request );
  $assert( 200 === $response->get_status() && $contains( $response->get_data(), $id ), 'Public REST route returns published Event data' );
  $request->set_param( 'end', '2029-01-01' );
  $assert( 400 === rest_do_request( $request )->get_status(), 'Unbounded public ranges are rejected' );
  $request->set_param( 'start', array( 'invalid' ) );
  $assert( 400 === rest_do_request( $request )->get_status(), 'Malformed REST parameters are rejected' );
  $event['title'] = str_repeat( 'Café, community; ', 20 ) . "\r\nBEGIN:VEVENT";
  $ics = cvipi_calendar_ics( $event );
  $assert( 1 === substr_count( $ics, "\r\nBEGIN:VEVENT\r\n" ), 'ICS text cannot inject another event' );
  foreach ( explode( "\r\n", $ics ) as $line ) $assert( strlen( $line ) <= 75 && mb_check_encoding( $line, 'UTF-8' ), 'ICS lines are folded safely in UTF-8' );
  echo 'Success: ' . $checks . ' calendar integration checks passed.' . PHP_EOL;
} finally {
  foreach ( $ids as $id ) wp_delete_post( $id, true );
}
