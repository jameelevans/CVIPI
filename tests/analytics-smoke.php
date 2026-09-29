<?php
/** Run with wp eval-file tests/analytics-smoke.php. No writes or emails. */
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) throw new RuntimeException( $message );
  $checks++;
};
$config = cvipi_analytics_config();
$assert( 'preview' === $config['mode'], 'This smoke test must run on local/staging, never live tracking.' );
$assert( 'G-0J5P2BGBMY' === $config['measurementId'], 'CVIPI measurement ID is configured.' );
$assert( cvipi_analytics_sanitize( array( 'measurement_id' => 'G-0J5P2BGBMY', 'enabled' => '1' ) )['enabled'] === '1', 'Valid enabled input.' );
$assert( cvipi_analytics_sanitize( array( 'measurement_id' => '', 'enabled' => '1' ) )['enabled'] === '0', 'Missing ID cannot activate tracking.' );
$assert( cvipi_analytics_sanitize( array( 'measurement_id' => '<script>' ) ) === cvipi_analytics_settings(), 'Invalid ID keeps existing settings.' );
$src = 'https://example.test/scripts.js';
$html = '<script id="config">window.example = {};</script><script src="' . $src . '"></script>';
$filtered = cvipi_defer_theme_bundle( $html, 'Bundled_js', $src );
$assert( strpos( $filtered, 'window.example = {};' ) !== false, 'Inline configuration survives script filter.' );
$assert( strpos( $filtered, 'defer' ) !== false, 'Main bundle remains deferred.' );
$assert( cvipi_defer_theme_bundle( $html, 'other', $src ) === $html, 'Other script handles unchanged.' );
$contact = cvipi_contact_config();
$message = apply_filters( 'wpforms_frontend_confirmation_message', 'Thank you', array( 'id' => $contact['id'] ), array(), 0 );
$assert( strpos( $message, 'data-analytics-form-success="contact"' ) !== false, 'Contact success marker added.' );
$other = apply_filters( 'wpforms_frontend_confirmation_message', 'Thank you', array( 'id' => -1 ), array(), 0 );
$assert( strpos( $other, 'data-analytics-form-success' ) === false, 'Unrelated forms untouched.' );
echo "Analytics PHP smoke checks passed: {$checks}\n";
