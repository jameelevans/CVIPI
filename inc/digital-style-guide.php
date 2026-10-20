<?php
/** Unlisted digital brand guide, current theme palette, and approved checklist. */
defined( 'ABSPATH' ) || exit;

function cvipi_is_digital_style_guide() {
  return is_page_template( 'page-digital-style-guide.php' );
}

function cvipi_style_guide_palette() {
  return array(
    'deep' => array( 'name' => 'Deep', 'token' => '--color-deep', 'hex' => '#071820', 'use' => 'Darkest background / Dark sections' ),
    'midnight' => array( 'name' => 'Midnight Teal', 'token' => '--color-dark-midnight-teal', 'hex' => '#0D3344', 'use' => 'Primary / Navigation / Authority' ),
    'teal' => array( 'name' => 'Teal Mid', 'token' => '--color-dark-blue', 'hex' => '#174F6B' ),
    'azure' => array( 'name' => 'Azure Blue', 'token' => '--color-azure-blue', 'hex' => '#1A6A92' ),
    'blue' => array( 'name' => 'Light Blue', 'token' => '--color-light-blue', 'hex' => '#4C9FD6' ),
    'copper' => array( 'name' => 'Copper', 'token' => '--color-copper', 'hex' => '#BB722A', 'use' => 'Calls to action / Action / Warmth' ),
    'copper-light' => array( 'name' => 'Copper Light', 'token' => '--color-light-copper', 'hex' => '#BF7B3C' ),
    'carmine' => array( 'name' => 'Carmine', 'token' => '--color-carmine', 'hex' => '#EAC293' ),
    'sand' => array( 'name' => 'Sand', 'token' => '--color-sand', 'hex' => '#F4EBDA', 'use' => 'Page background / Editorial ground' ),
    'body' => array( 'name' => 'Body Text', 'token' => '--color-medium-blue', 'hex' => '#4A6472' ),
    'morning' => array( 'name' => 'Morning', 'token' => '--color-morning', 'hex' => '#F7F3EC' ),
    'white' => array( 'name' => 'White', 'token' => '--color-white', 'hex' => '#FFFFFF' ),
  );
}

function cvipi_style_guide_contrast( $foreground, $background ) {
  $luminance = function ( $hex ) {
    $rgb = sscanf( $hex, '#%02x%02x%02x' );
    if ( 3 !== count( $rgb ) || in_array( null, $rgb, true ) ) throw new InvalidArgumentException( 'Use a six-digit hex color.' );
    $linear = array_map( function ( $channel ) {
      $value = $channel / 255;
      return $value <= 0.04045 ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
    }, $rgb );
    return $linear[0] * 0.2126 + $linear[1] * 0.7152 + $linear[2] * 0.0722;
  };
  $a = $luminance( $foreground );
  $b = $luminance( $background );
  return ( max( $a, $b ) + 0.05 ) / ( min( $a, $b ) + 0.05 );
}

function cvipi_style_guide_checklist_path() {
  $directory = cvipi_grantee_template_directory();
  if ( ! $directory ) return false;
  $file = $directory . '/CVIPI-508-Compliance-Checklist.docx';
  return ! is_link( $file ) && is_file( $file ) && is_readable( $file ) ? $file : false;
}

function cvipi_style_guide_checklist_url() {
  return home_url( '/cvipi-digital-style-guide/CVIPI-508-Compliance-Checklist.docx' );
}

add_action( 'init', function () {
  add_rewrite_rule( '^cvipi-digital-style-guide/CVIPI-508-Compliance-Checklist\.docx/?$', 'index.php?pagename=cvipi-digital-style-guide&cvipi_guide_download=checklist', 'top' );
} );
add_filter( 'query_vars', function ( $vars ) { $vars[] = 'cvipi_guide_download'; return $vars; } );

add_action( 'template_redirect', function () {
  $download = get_query_var( 'cvipi_guide_download' );
  if ( ! cvipi_is_digital_style_guide() && ! $download ) return;
  header( 'X-Robots-Tag: noindex, nofollow', true );
  if ( ! $download ) return;
  $path = 'checklist' === $download && cvipi_is_digital_style_guide() && is_page( 'cvipi-digital-style-guide' ) && 'publish' === get_post_status( get_queried_object_id() ) ? cvipi_style_guide_checklist_path() : false;
  if ( ! $path ) wp_die( 'This checklist is currently unavailable. Please contact the CVIPI team.', 'Checklist unavailable', array( 'response' => 404 ) );
  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
    header( 'Allow: GET, HEAD' );
    wp_die( 'This download only accepts GET and HEAD requests.', '', array( 'response' => 405 ) );
  }
  nocache_headers();
  header( 'Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document' );
  header( 'X-Content-Type-Options: nosniff' );
  header( 'Content-Disposition: attachment; filename="CVIPI-508-Compliance-Checklist.docx"' );
  header( 'Content-Length: ' . filesize( $path ) );
  if ( 'GET' === $method ) readfile( $path );
  exit;
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
  if ( ! cvipi_is_digital_style_guide() ) return;
  // This standalone guide keeps consent handling, without site navigation or exit prompts.
  wp_dequeue_script( 'Bundled_js' );
  $base = get_template_directory();
  $reveals = '/assets/js/digital-style-guide-reveals.js';
  $bundle = '/assets/js/digital-style-guide-bundled.js';
  wp_enqueue_script( 'cvipi-guide-reveals', get_template_directory_uri() . $reveals, array(), filemtime( $base . $reveals ), false );
  wp_enqueue_script( 'cvipi-digital-style-guide', get_template_directory_uri() . $bundle, array(), filemtime( $base . $bundle ), array( 'strategy' => 'defer', 'in_footer' => true ) );
  wp_add_inline_script( 'cvipi-digital-style-guide', 'window.cvipiAnalyticsConfig = ' . wp_json_encode( cvipi_analytics_config(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
}, 110 );
