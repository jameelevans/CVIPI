<?php
/** Anonymous, read-only local HTTP checks. No form submissions or content changes. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
if ( wp_parse_url( home_url(), PHP_URL_HOST ) !== 'cvipi.local' ) WP_CLI::error( 'Run only on the local site.' );
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) WP_CLI::error( $message );
  $checks++;
};
$page = wp_remote_get( home_url( '/cvipi-digital-style-guide/' ), array( 'timeout' => 20 ) );
$assert( ! is_wp_error( $page ) && 200 === wp_remote_retrieve_response_code( $page ), 'Guide loads for anonymous visitors' );
$assert( str_contains( wp_remote_retrieve_header( $page, 'x-robots-tag' ), 'noindex' ), 'Guide sends a noindex HTTP header' );
$body = wp_remote_retrieve_body( $page );
$assert( preg_match( '/<meta name=[\'"]robots[\'"] content=[\'"][^\'"]*noindex/', $body ), 'Guide includes noindex metadata' );
$assert( str_contains( $body, 'digital-style-guide-bundled.js' ) && ! str_contains( $body, '/assets/js/scripts-bundled.js?' ), 'Guide uses its isolated navigation and consent bundle' );
$assert( ! str_contains( $body, 'class="header ' ) && ! str_contains( $body, 'class="footer ' ), 'Standard navigation and footer are not rendered' );
foreach ( array( 'overview', 'templates', 'voice', 'colors', 'typography', 'imagery', 'contact' ) as $id ) {
  $assert( str_contains( $body, '<section id="' . $id . '"' ), $id . ' section is rendered' );
}
foreach ( cvipi_grantee_templates() as $template ) {
  $assert( str_contains( $body, home_url( '/' . $template['slug'] . '/' ) ), 'Template card links to the existing approved download page' );
}
$url = cvipi_style_guide_checklist_url();
$response = wp_remote_get( $url, array( 'timeout' => 20, 'redirection' => 0 ) );
$assert( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ), 'Checklist download returns 200 without a redirect' );
$assert( hash( 'sha256', wp_remote_retrieve_body( $response ) ) === hash_file( 'sha256', cvipi_style_guide_checklist_path() ), 'Downloaded checklist matches the approved original' );
$assert( wp_remote_retrieve_header( $response, 'content-type' ) === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Checklist has the correct Office MIME type' );
$assert( wp_remote_retrieve_header( $response, 'x-content-type-options' ) === 'nosniff', 'Download prevents MIME sniffing' );
$assert( str_contains( wp_remote_retrieve_header( $response, 'content-disposition' ), 'attachment; filename="CVIPI-508-Compliance-Checklist.docx"' ), 'Download uses the approved filename' );
$assert( str_contains( wp_remote_retrieve_header( $response, 'x-robots-tag' ), 'noindex' ), 'Checklist is also excluded from indexing' );
$head = wp_remote_head( $url );
$assert( 200 === wp_remote_retrieve_response_code( $head ) && '' === wp_remote_retrieve_body( $head ), 'HEAD works without sending the file body' );
$assert( (int) wp_remote_retrieve_header( $head, 'content-length' ) === filesize( cvipi_style_guide_checklist_path() ), 'HEAD reports the correct file size' );
$assert( 405 === wp_remote_retrieve_response_code( wp_remote_post( $url ) ), 'POST downloads are rejected' );
foreach ( array( '/?cvipi_guide_download=unknown', '/?cvipi_guide_download%5B%5D=checklist', '/cvipi-document-template/?cvipi_guide_download=checklist' ) as $path ) {
  $assert( 404 === wp_remote_retrieve_response_code( wp_remote_get( home_url( $path ) ) ), 'Invalid checklist mapping is safely rejected' );
}
$home = wp_remote_retrieve_body( wp_remote_get( home_url( '/' ) ) );
$assert( ! str_contains( $home, 'digital-style-guide-bundled.js' ), 'Ordinary pages do not load the guide bundle' );
WP_CLI::success( $checks . ' anonymous digital style guide HTTP checks passed.' );
