<?php
/** Local integration test: CAPTCHA and email transports are mocked; QA posts go to Trash. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() ) throw new RuntimeException( 'Local CLI only.' );
if ( ! defined( 'DOING_AJAX' ) ) define( 'DOING_AJAX', true );
class CvipiSubmissionTypeTestResponse extends RuntimeException {}
$checks = 0;
$ids = array();
$mail = array();
$original_post = $_POST;
$original_settings = get_option( 'cvipi_story_notifications' );
$session_id = wp_generate_uuid4();
$ip = cvipi_story_ip_key();
$rate_key = 'cvipi_story_rate_' . $ip;
$old_rate = get_transient( $rate_key );
$assert = function ( $condition, $label ) use ( &$checks ) { if ( ! $condition ) throw new RuntimeException( $label ); $checks++; };
$die = function () { return function () { throw new CvipiSubmissionTypeTestResponse(); }; };
$mock_mail = function ( $value, $atts ) use ( &$mail ) { $mail[] = $atts; return true; };
$mock_http = function ( $value, $args, $url ) {
  if ( 'https://www.google.com/recaptcha/api/siteverify' === $url ) return array( 'response' => array( 'code' => 200 ), 'body' => '{"success":true,"hostname":"cvipi.local"}' );
  return $value;
};
$request = function ( $data ) {
  $_POST = wp_slash( $data );
  ob_start();
  try { cvipi_submit_story(); } catch ( CvipiSubmissionTypeTestResponse $e ) {}
  return json_decode( ob_get_clean(), true );
};
add_filter( 'wp_die_ajax_handler', $die );
add_filter( 'pre_wp_mail', $mock_mail, 10, 2 );
add_filter( 'pre_http_request', $mock_http, 10, 3 );
try {
  $settings = cvipi_story_notification_defaults();
  $settings['admin_recipients'] = 'team@example.org';
  update_option( 'cvipi_story_notifications', $settings );
  delete_transient( $rate_key );
  set_transient( 'cvipi_story_' . $session_id, array( 'created' => time() - 10, 'ip' => $ip ), HOUR_IN_SECONDS );
  $data = array( 'name' => 'Submission Type QA', 'email' => 'qa-' . wp_generate_uuid4() . '@example.org', 'organization' => 'QA', 'location' => 'Tucson, AZ', 'title' => '', 'story' => str_repeat( 'Local integration test content. ', 5 ), 'consent' => 'yes', 'nonce' => wp_create_nonce( 'cvipi_submit_story' ), 'session' => $session_id, 'captcha' => 'mock-test-token' );
  $assert( count( cvipi_story_submission_types() ) === 2, 'Exactly two options.' );
  foreach ( array( null, '', 'invalid', '<script>story</script>', array( 'story' ) ) as $type ) {
    $invalid = $data;
    if ( null !== $type ) $invalid['submission_type'] = $type;
    $response = $request( $invalid );
    $assert( false === $response['success'] && isset( $response['data']['fields']['submission_type'] ), 'Missing/forged type is rejected.' );
  }
  $assert( count( $mail ) === 0, 'Invalid choices send no notifications.' );
  foreach ( cvipi_story_submission_types() as $type => $label ) {
    $data['submission_type'] = $type;
    set_transient( 'cvipi_story_' . $session_id, array( 'created' => time() - 10, 'ip' => $ip ), HOUR_IN_SECONDS );
    $response = $request( $data );
    $posts = get_posts( array( 'post_type' => 'success_story', 'post_status' => 'pending', 'meta_key' => '_cvipi_submitter_email', 'meta_value' => $data['email'], 'fields' => 'ids', 'numberposts' => -1 ) );
    $ids = array_unique( array_merge( $ids, $posts ) );
    $assert( true === $response['success'], 'Valid choice accepted.' );
    $id = $posts[0];
    $assert( get_post_meta( $id, '_cvipi_submission_type', true ) === $type, 'Choice stored.' );
    $assert( cvipi_story_submission_type_label( $id ) === $label, 'Admin label correct.' );
    $assert( get_post_status( $id ) === 'pending' && get_post_meta( $id, '_cvipi_submission_queue', true ) === '1', 'Approval queue retained.' );
    $assert( get_the_title( $id ) === 'Tucson, AZ', 'Optional title fallback preserved.' );
    $assert( count( $mail ) >= 2 && strpos( end( $mail )['message'], esc_html( $label ) ) !== false, 'Team notification includes choice.' );
    $assert( strpos( $mail[count( $mail ) - 2]['message'], $data['email'] ) === false, 'Receipt still omits visitor content.' );
    $data['email'] = 'qa-' . wp_generate_uuid4() . '@example.org';
  }
  delete_post_meta( $ids[0], '_cvipi_submission_type' );
  $assert( cvipi_story_submission_type_label( $ids[0] ) === 'Share a story', 'Older submissions retain story classification.' );
  WP_CLI::success( $checks . ' submission-type checks passed; no external mail or CAPTCHA request sent.' );
} finally {
  $_POST = $original_post;
  remove_filter( 'wp_die_ajax_handler', $die );
  remove_filter( 'pre_wp_mail', $mock_mail, 10 );
  remove_filter( 'pre_http_request', $mock_http, 10 );
  update_option( 'cvipi_story_notifications', $original_settings );
  delete_transient( 'cvipi_story_' . $session_id );
  if ( false === $old_rate ) delete_transient( $rate_key );
  else set_transient( $rate_key, $old_rate, HOUR_IN_SECONDS );
  foreach ( $ids as $id ) wp_trash_post( $id );
}
