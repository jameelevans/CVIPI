<?php
defined( 'ABSPATH' ) || exit;

function cvipi_dashboard_property() { return '555256472'; }

function cvipi_dashboard_ranges( $range ) {
  if ( 'today' === $range ) return array(
    array( 'startDate' => 'today', 'endDate' => 'today', 'name' => 'current' ),
    array( 'startDate' => 'yesterday', 'endDate' => 'yesterday', 'name' => 'previous' ),
  );
  $days = in_array( (string) $range, array( '7', '28', '90' ), true ) ? (int) $range : 28;
  return array(
    array( 'startDate' => $days . 'daysAgo', 'endDate' => 'yesterday', 'name' => 'current' ),
    array( 'startDate' => ( 2 * $days ) . 'daysAgo', 'endDate' => ( $days + 1 ) . 'daysAgo', 'name' => 'previous' ),
  );
}

function cvipi_dashboard_filter( $event = '' ) {
  $filters = array(
    array( 'filter' => array( 'fieldName' => 'hostName', 'stringFilter' => array( 'matchType' => 'EXACT', 'value' => 'cvipi.info' ) ) ),
    array( 'notExpression' => array( 'filter' => array( 'fieldName' => 'pagePath', 'stringFilter' => array( 'matchType' => 'BEGINS_WITH', 'value' => '/staging/' ) ) ) ),
  );
  if ( $event ) $filters[] = array( 'filter' => array( 'fieldName' => 'eventName', 'stringFilter' => array( 'matchType' => 'EXACT', 'value' => $event ) ) );
  return array( 'andGroup' => array( 'expressions' => $filters ) );
}

function cvipi_dashboard_queries( $range, $available = null ) {
  $ranges = cvipi_dashboard_ranges( $range );
  $specs = array(
    'overview' => array( array(), array( 'activeUsers', 'sessions', 'screenPageViews', 'userEngagementDuration', 'averageSessionDuration', 'engagementRate' ), '', true ),
    'trend' => array( array( 'date' ), array( 'activeUsers', 'sessions', 'screenPageViews' ), '', false ),
    'events' => array( array( 'eventName' ), array( 'eventCount' ), '', true ),
    'pages' => array( array( 'pagePath' ), array( 'screenPageViews', 'activeUsers', 'userEngagementDuration' ), '', false ),
    'downloads' => array( array( 'fileName' ), array( 'eventCount', 'totalUsers' ), 'file_download', false ),
    'channels' => array( array( 'sessionDefaultChannelGroup' ), array( 'sessions' ), '', false ),
    'devices' => array( array( 'deviceCategory' ), array( 'sessions' ), '', false ),
    'links' => array( array( 'linkUrl', 'customEvent:link_type' ), array( 'eventCount' ), 'link_click', false ),
    'forms' => array( array( 'customEvent:form_name' ), array( 'eventCount' ), 'form_success', false ),
  );
  $queries = array();
  foreach ( $specs as $key => $spec ) {
    if ( is_array( $available ) ) {
      $spec[0] = array_values( array_filter( $spec[0], function ( $name ) use ( $available ) { return ! str_starts_with( $name, 'customEvent:' ) || in_array( $name, $available, true ); } ) );
      if ( 'forms' === $key && ! $spec[0] ) continue;
    }
    $queries[$key] = array(
      'dateRanges' => $spec[3] ? $ranges : array( $ranges[0] ),
      'metrics' => array_map( function ( $name ) { return array( 'name' => $name ); }, $spec[1] ),
      'dimensionFilter' => cvipi_dashboard_filter( $spec[2] ), 'limit' => '100',
      'orderBys' => array( 'trend' === $key ? array( 'dimension' => array( 'dimensionName' => 'date' ) ) : array( 'metric' => array( 'metricName' => $spec[1][0] ), 'desc' => true ) ),
    );
    if ( $spec[0] ) $queries[$key]['dimensions'] = array_map( function ( $name ) { return array( 'name' => $name ); }, $spec[0] );
  }
  return $queries;
}

function cvipi_dashboard_rows( $report ) {
  $rows = array();
  foreach ( $report['rows'] ?? array() as $row ) {
    $item = array();
    foreach ( $report['dimensionHeaders'] ?? array() as $i => $header ) $item[$header['name']] = (string) ( $row['dimensionValues'][$i]['value'] ?? '' );
    foreach ( $report['metricHeaders'] ?? array() as $i => $header ) {
      $value = $row['metricValues'][$i]['value'] ?? null;
      if ( ! is_numeric( $value ) ) throw new RuntimeException( 'Invalid metric response.' );
      $item[$header['name']] = (float) $value;
    }
    $rows[] = $item;
  }
  return $rows;
}

function cvipi_dashboard_token() {
  $path = defined( 'CVIPI_ANALYTICS_CREDENTIALS' ) ? realpath( CVIPI_ANALYTICS_CREDENTIALS ) : false;
  $public = realpath( ABSPATH );
  if ( ! $path || ! is_readable( $path ) || ( $public && str_starts_with( $path, $public . DIRECTORY_SEPARATOR ) ) ) {
    throw new RuntimeException( 'The private Analytics connection is not configured.' );
  }
  require_once __DIR__ . '/vendor/autoload.php';
  $json = json_decode( file_get_contents( $path ), true, 16, JSON_THROW_ON_ERROR );
  // Fix the identity and endpoints; do not accept arbitrary credential configurations.
  if ( 'service_account' !== ( $json['type'] ?? '' ) || 'cvipi-analytics-dashboard@cvipi-503218.iam.gserviceaccount.com' !== ( $json['client_email'] ?? '' ) ) throw new RuntimeException( 'Unexpected Analytics identity.' );
  $json['token_uri'] = 'https://oauth2.googleapis.com/token';
  $credentials = new Google\Auth\Credentials\ServiceAccountCredentials( 'https://www.googleapis.com/auth/analytics.readonly', $json );
  $client = new GuzzleHttp\Client( array( 'timeout' => 15, 'connect_timeout' => 8 ) );
  $token = $credentials->fetchAuthToken( Google\Auth\HttpHandler\HttpHandlerFactory::build( $client ) );
  if ( empty( $token['access_token'] ) ) throw new RuntimeException( 'Analytics authentication failed.' );
  return $token['access_token'];
}

function cvipi_dashboard_fetch( $range ) {
  try { $token = cvipi_dashboard_token(); }
  catch ( Throwable $e ) { return new WP_Error( 'connection', 'The private Google Analytics connection needs attention. Check the server credential and Viewer access.' ); }
  $metadata = wp_remote_get( 'https://analyticsdata.googleapis.com/v1beta/properties/' . cvipi_dashboard_property() . '/metadata', array( 'timeout' => 15, 'redirection' => 0, 'headers' => array( 'Authorization' => 'Bearer ' . $token ) ) );
  if ( is_wp_error( $metadata ) || 200 !== wp_remote_retrieve_response_code( $metadata ) ) return new WP_Error( 'metadata', 'Google report definitions are unavailable. Please try again shortly.' );
  $schema = json_decode( wp_remote_retrieve_body( $metadata ), true );
  if ( empty( $schema['dimensions'] ) ) return new WP_Error( 'metadata', 'Google returned incomplete report definitions.' );
  $available = array_column( $schema['dimensions'], 'apiName' );
  $queries = cvipi_dashboard_queries( $range, $available );
  $data = array( 'property' => cvipi_dashboard_property(), 'site' => 'https://cvipi.info', 'range' => $range, 'reports' => array(), 'warnings' => array() );
  if ( ! isset( $queries['forms'] ) ) {
    $data['reports']['forms'] = array( 'rows' => array(), 'rowCount' => 0, 'unavailable' => 'Google has not made the new form-name reporting field available yet. Submission totals remain in the key metrics.' );
    $data['warnings'][] = 'New custom reporting fields are still becoming available in Google Analytics.';
  }
  foreach ( array_chunk( $queries, 5, true ) as $chunk ) {
    $response = wp_remote_post( 'https://analyticsdata.googleapis.com/v1beta/properties/' . cvipi_dashboard_property() . ':batchRunReports', array(
      'timeout' => 25, 'redirection' => 0,
      'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
      'body' => wp_json_encode( array( 'requests' => array_values( $chunk ) ) ),
    ) );
    if ( is_wp_error( $response ) ) return new WP_Error( 'network', 'Google Analytics could not be reached. Please try again shortly.' );
    $status = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $status ) {
      $messages = array( 401 => 'Google authentication expired or was revoked.', 403 => 'Google denied report access. Check Viewer access and that the Analytics Data API is enabled.', 429 => 'Google Analytics is temporarily rate-limited. Please try again later.' );
      return new WP_Error( 'api', $messages[$status] ?? 'Google could not return the requested reports. Please try again later.' );
    }
    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $body ) || count( $body['reports'] ?? array() ) !== count( $chunk ) ) return new WP_Error( 'response', 'Google returned an incomplete report. No totals were substituted.' );
    foreach ( array_keys( $chunk ) as $i => $key ) {
      $report = $body['reports'][$i];
      if ( array_column( $report['metricHeaders'] ?? array(), 'name' ) !== array_column( $chunk[$key]['metrics'], 'name' ) ) return new WP_Error( 'response', 'Google returned an incomplete report. No totals were substituted.' );
      try { $rows = cvipi_dashboard_rows( $report ); }
      catch ( Throwable $e ) { return new WP_Error( 'response', 'Google returned an invalid report. Please try again later.' ); }
      $data['reports'][$key] = array( 'rows' => $rows, 'rowCount' => (int) ( $report['rowCount'] ?? 0 ) );
      $meta = $report['metadata'] ?? array();
      if ( ! empty( $meta['subjectToThresholding'] ) ) $data['warnings'][] = 'Google may withhold small counts for privacy.';
      if ( ! empty( $meta['dataLossFromOtherRow'] ) ) $data['warnings'][] = 'Some high-cardinality values are grouped by Google.';
      if ( ! empty( $meta['samplingMetadatas'] ) ) $data['warnings'][] = 'Google has sampled part of this report.';
      if ( ! empty( $meta['timeZone'] ) ) $data['timezone'] = $meta['timeZone'];
    }
  }
  $data['fetchedAt'] = gmdate( 'c' );
  $data['warnings'] = array_values( array_unique( $data['warnings'] ) );
  $data['timezone'] = $data['timezone'] ?? 'America/Los_Angeles';
  return $data;
}

function cvipi_dashboard_data( $range, $refresh = false ) {
  $key = 'cvipi_ga_dashboard_v1_' . cvipi_dashboard_property() . '_' . $range;
  $saved = get_transient( $key );
  $age = $saved ? time() - strtotime( $saved['fetchedAt'] ) : PHP_INT_MAX;
  if ( $saved && ( $age < 60 || ( ! $refresh && $age < 15 * MINUTE_IN_SECONDS ) ) ) return $saved;
  $failure = get_transient( $key . '_failure' );
  if ( $failure ) {
    if ( $saved ) { $saved['stale'] = true; $saved['notice'] = $failure . ' Showing the last successful report.'; return $saved; }
    return new WP_Error( 'cooldown', $failure );
  }
  $lock = $key . '_lock';
  $locked_at = (int) get_option( $lock );
  if ( $locked_at && $locked_at < time() - 90 ) delete_option( $lock );
  if ( ! add_option( $lock, time(), '', false ) ) {
    if ( $saved ) { $saved['stale'] = true; $saved['notice'] = 'A refresh is already in progress. Showing the last successful report.'; return $saved; }
    return new WP_Error( 'busy', 'Analytics is refreshing. Please try again in a moment.' );
  }
  try {
    $result = cvipi_dashboard_fetch( $range );
    if ( is_wp_error( $result ) ) {
      // Short cooldown avoids repeated API calls during an outage or revoked access.
      set_transient( $key . '_failure', $result->get_error_message(), MINUTE_IN_SECONDS );
      if ( $saved ) { $saved['stale'] = true; $saved['notice'] = $result->get_error_message() . ' Showing the last successful report.'; return $saved; }
      return $result;
    }
    set_transient( $key, $result, DAY_IN_SECONDS );
    return $result;
  } finally { delete_option( $lock ); }
}
