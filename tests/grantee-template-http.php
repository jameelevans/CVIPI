<?php
/** Read-only HTTP checks against local WordPress. No cookies or login. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
if ( wp_parse_url( home_url(), PHP_URL_HOST ) !== 'cvipi.local' ) WP_CLI::error( 'Run only on the local site.' );
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) WP_CLI::error( $message );
  $checks++;
};
foreach ( cvipi_grantee_templates() as $template ) {
  $url = cvipi_grantee_template_url( $template );
  $response = wp_remote_get( $url, array( 'timeout' => 20, 'redirection' => 0 ) );
  $assert( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ), 'Anonymous download returns 200' );
  $assert( hash( 'sha256', wp_remote_retrieve_body( $response ) ) === hash_file( 'sha256', cvipi_grantee_template_path( $template ) ), 'HTTP bytes match the stored original' );
  $assert( wp_remote_retrieve_header( $response, 'content-type' ) === $template['mime'], 'Office MIME type matches' );
  $assert( str_contains( wp_remote_retrieve_header( $response, 'content-disposition' ), 'attachment; filename="' . $template['filename'] . '"' ), 'Download filename matches' );
  $assert( str_contains( wp_remote_retrieve_header( $response, 'x-robots-tag' ), 'noindex' ), 'Downloaded file has noindex' );
  $head = wp_remote_head( $url );
  $assert( 200 === wp_remote_retrieve_response_code( $head ) && '' === wp_remote_retrieve_body( $head ), 'HEAD works without a body' );
  $assert( (int) wp_remote_retrieve_header( $head, 'content-length' ) === filesize( cvipi_grantee_template_path( $template ) ), 'HEAD size matches original' );
  $post = wp_remote_post( $url );
  $assert( 405 === wp_remote_retrieve_response_code( $post ), 'POST is rejected without changing anything' );
  $page = wp_remote_get( home_url( '/' . $template['slug'] . '/' ) );
  $assert( 200 === wp_remote_retrieve_response_code( $page ) && str_contains( wp_remote_retrieve_header( $page, 'x-robots-tag' ), 'noindex' ), 'Page is available with noindex header' );
  $body = wp_remote_retrieve_body( $page );
  $assert( preg_match( '/<meta name=[\'"]robots[\'"] content=[\'"][^\'"]*noindex/', $body ), 'Page includes noindex meta tag' );
}
$unknown = wp_remote_get( home_url( '/?cvipi_template_download=unknown' ) );
$assert( 404 === wp_remote_retrieve_response_code( $unknown ), 'Unknown download key returns 404' );
$array = wp_remote_get( home_url( '/?cvipi_template_download%5B%5D=document' ) );
$assert( 404 === wp_remote_retrieve_response_code( $array ), 'Array parameter is safely rejected' );
$mismatch = wp_remote_get( home_url( '/cvipi-document-template/?cvipi_template_download=presentation' ) );
$assert( 404 === wp_remote_retrieve_response_code( $mismatch ), 'Cross-template mapping returns 404' );
WP_CLI::success( $checks . ' anonymous grantee-template HTTP checks passed.' );
