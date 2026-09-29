<?php
/** Private, read-only reporting for the live CVIPI GA4 property. */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/analytics-dashboard/reports.php';

add_action( 'admin_menu', function () {
  add_menu_page( 'CVIPI Analytics', 'Analytics', 'manage_options', 'cvipi-analytics-dashboard', 'cvipi_dashboard_page', 'dashicons-chart-line', 27 );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
  if ( 'toplevel_page_cvipi-analytics-dashboard' !== $hook || ! current_user_can( 'manage_options' ) ) return;
  $base = get_template_directory();
  $url = get_template_directory_uri();
  wp_enqueue_style( 'cvipi-dashboard', $url . '/assets/admin/analytics-dashboard.css', array( 'dashicons' ), filemtime( $base . '/assets/admin/analytics-dashboard.css' ) );
  wp_enqueue_script( 'cvipi-dashboard', $url . '/assets/admin/analytics-dashboard.js', array(), filemtime( $base . '/assets/admin/analytics-dashboard.js' ), true );
  wp_localize_script( 'cvipi-dashboard', 'cvipiDashboard', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'cvipi_dashboard_read' ) ) );
} );

add_action( 'wp_ajax_cvipi_dashboard_read', function () {
  if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'You do not have access to Analytics.' ), 403 );
  check_ajax_referer( 'cvipi_dashboard_read', 'nonce' );
  $range = isset( $_POST['range'] ) && is_string( $_POST['range'] ) ? sanitize_key( wp_unslash( $_POST['range'] ) ) : '28';
  if ( ! in_array( $range, array( 'today', '7', '28', '90' ), true ) ) wp_send_json_error( array( 'message' => 'Choose a valid reporting period.' ), 400 );
  nocache_headers();
  $result = cvipi_dashboard_data( $range, ! empty( $_POST['refresh'] ) );
  if ( is_wp_error( $result ) ) wp_send_json_error( array( 'message' => $result->get_error_message() ), 503 );
  wp_send_json_success( $result );
} );

function cvipi_dashboard_page() {
  if ( ! current_user_can( 'manage_options' ) ) return;
  require __DIR__ . '/analytics-dashboard/view.php';
}
