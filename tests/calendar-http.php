<?php
/** Read-only anonymous HTTP checks against the running local WordPress site. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || home_url() !== 'http://cvipi.local' ) throw new RuntimeException( 'Local CVIPI CLI only.' );
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) throw new RuntimeException( $message );
  $checks++;
  WP_CLI::log( 'PASS: ' . $message );
};
$get = function ( $url, $method = 'GET' ) {
  $response = wp_remote_request( $url, array( 'method' => $method, 'timeout' => 15, 'redirection' => 0, 'cookies' => array() ) );
  if ( is_wp_error( $response ) ) throw new RuntimeException( $response->get_error_message() );
  return $response;
};
$page = $get( home_url( '/tta-calendar/' ) );
$assert( 200 === wp_remote_retrieve_response_code( $page ), 'Calendar page responds without authentication' );
$html = wp_remote_retrieve_body( $page );
$tags = new WP_HTML_Tag_Processor( $html );
$fallback = false;
$bundle = false;
$reveals = false;
while ( $tags->next_tag() ) {
  if ( null !== $tags->get_attribute( 'data-calendar-fallback' ) ) $fallback = null === $tags->get_attribute( 'hidden' );
  if ( 'SCRIPT' !== $tags->get_tag() ) continue;
  $src = $tags->get_attribute( 'src' ) ?: '';
  if ( str_contains( $src, '/tta-calendar-bundled.js' ) ) $bundle = true;
  if ( str_contains( $src, '/tta-calendar-reveals.js' ) ) $reveals = null === $tags->get_attribute( 'defer' ) && null === $tags->get_attribute( 'async' );
}
$assert( $fallback, 'Monthly event fallback is visible without JavaScript' );
$assert( $bundle && $reveals, 'Calendar bundle and pre-paint animation controller are included correctly' );
foreach ( array( 'style.css', 'assets/js/tta-calendar-bundled.js', 'assets/js/tta-calendar-reveals.js', 'assets/img/tta-community.webp' ) as $asset ) {
  $response = $get( get_theme_file_uri( $asset ) );
  $assert( 200 === wp_remote_retrieve_response_code( $response ) && strlen( wp_remote_retrieve_body( $response ) ) > 0, 'Public asset loads: ' . $asset );
}
$month = new DateTimeImmutable( 'first day of this month', cvipi_calendar_timezone() );
$feed_url = add_query_arg( array( 'start' => $month->format( 'Y-m-d' ), 'end' => $month->modify( '+1 month' )->format( 'Y-m-d' ) ), rest_url( 'cvipi/v1/calendar' ) );
$feed = $get( $feed_url );
$events = json_decode( wp_remote_retrieve_body( $feed ), true );
$assert( 200 === wp_remote_retrieve_response_code( $feed ) && is_array( $events ) && array_is_list( $events ), 'Anonymous calendar feed returns valid event JSON' );
$assert( str_contains( wp_remote_retrieve_header( $feed, 'cache-control' ), 'no-store' ), 'Calendar feed is not cached as stale event data' );
$invalid = $get( add_query_arg( array( 'start' => 'invalid', 'end' => '2026-12-01' ), rest_url( 'cvipi/v1/calendar' ) ) );
$assert( 400 === wp_remote_retrieve_response_code( $invalid ), 'Invalid feed dates return a controlled error' );
$malformed = $get( add_query_arg( array( 'calendar_date' => array( 'invalid' ) ), home_url( '/tta-calendar/' ) ) );
$assert( 200 === wp_remote_retrieve_response_code( $malformed ) && ! str_contains( wp_remote_retrieve_body( $malformed ), 'Fatal error' ), 'Malformed page dates do not crash the calendar' );
if ( $events ) {
  $event = $events[0];
  $download = $get( $event['extendedProps']['download'] );
  $assert( 200 === wp_remote_retrieve_response_code( $download ) && str_starts_with( wp_remote_retrieve_header( $download, 'content-type' ), 'text/calendar' ), 'Calendar download has the correct file type' );
  $assert( str_contains( wp_remote_retrieve_header( $download, 'content-disposition' ), 'attachment;' ) && 'nosniff' === wp_remote_retrieve_header( $download, 'x-content-type-options' ), 'Calendar download uses safe attachment headers' );
  $stable_lines = function ( $ics ) { return array_values( array_filter( explode( "\r\n", $ics ), function ( $line ) { return ! str_starts_with( $line, 'DTSTAMP:' ); } ) ); };
  $assert( $stable_lines( cvipi_calendar_ics( $event ) ) === $stable_lines( wp_remote_retrieve_body( $download ) ), 'Downloaded calendar file matches the event' );
  $head = $get( $event['extendedProps']['download'], 'HEAD' );
  $assert( 200 === wp_remote_retrieve_response_code( $head ) && '' === wp_remote_retrieve_body( $head ), 'Calendar download supports HEAD without a response body' );
}
$missing = $get( add_query_arg( array( 'action' => 'cvipi_calendar_download', 'event' => 0 ), admin_url( 'admin-post.php' ) ) );
$assert( 404 === wp_remote_retrieve_response_code( $missing ), 'Missing Events cannot be downloaded' );
WP_CLI::success( $checks . ' read-only calendar HTTP checks passed.' );
