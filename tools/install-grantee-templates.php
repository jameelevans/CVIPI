<?php
/** Run with wp eval-file and the original DOCX/PPTX paths as two arguments. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
if ( count( $args ) !== 2 ) WP_CLI::error( 'Provide the original Word and PowerPoint file paths, in that order.' );
$templates = cvipi_grantee_templates();
$sources = array_combine( array_keys( $templates ), $args );
// Check both inputs and slug ownership before creating any pages or copying files.
foreach ( $templates as $key => $template ) {
  $source = $sources[$key];
  $extension = pathinfo( $template['filename'], PATHINFO_EXTENSION );
  if ( ! is_file( $source ) || ! is_readable( $source ) || strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) !== $extension ) WP_CLI::error( 'Provide a readable .' . $extension . ' file for ' . $key . '.' );
  $page = get_page_by_path( $template['slug'] );
  if ( $page && ( 'page-grantee-template.php' !== get_post_meta( $page->ID, '_wp_page_template', true ) || $key !== get_post_meta( $page->ID, '_cvipi_grantee_template', true ) ) ) WP_CLI::error( 'An unrelated page already owns /' . $template['slug'] . '/. Nothing was overwritten.' );
}
$directory = cvipi_grantee_template_directory( true );
if ( ! $directory || ! is_writable( $directory ) ) WP_CLI::error( 'Create writable storage outside the public root, or set CVIPI_GRANTEE_TEMPLATE_DIR to an existing parent path outside that root.' );
foreach ( $templates as $key => $template ) {
  $source = $sources[$key];
  $destination = $directory . '/' . $template['filename'];
  if ( is_link( $destination ) ) WP_CLI::error( 'Refusing a symbolic-link destination.' );
  $hash = hash_file( 'sha256', $source );
  if ( ! is_file( $destination ) || hash_file( 'sha256', $destination ) !== $hash ) {
    $temporary = tempnam( $directory, '.template-' );
    if ( ! $temporary || ! copy( $source, $temporary ) || ! chmod( $temporary, 0600 ) || hash_file( 'sha256', $temporary ) !== $hash || ! rename( $temporary, $destination ) ) {
      if ( $temporary ) unlink( $temporary );
      WP_CLI::error( 'Could not copy and verify ' . $template['filename'] . '.' );
    }
  }
  $page = get_page_by_path( $template['slug'] );
  if ( ! $page ) {
    $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $template['title'], 'post_name' => $template['slug'], 'post_excerpt' => $template['description'], 'comment_status' => 'closed', 'ping_status' => 'closed' ), true );
    if ( is_wp_error( $id ) ) WP_CLI::error( $id->get_error_message() );
  } else {
    $id = $page->ID;
  }
  update_post_meta( $id, '_wp_page_template', 'page-grantee-template.php' );
  update_post_meta( $id, '_cvipi_grantee_template', $key );
  update_post_meta( $id, '_cvipi_seo_noindex', '1' );
  update_post_meta( $id, '_cvipi_seo_description', $template['description'] );
  WP_CLI::log( get_permalink( $id ) . ' | SHA-256: ' . $hash );
}
flush_rewrite_rules( false );
WP_CLI::success( 'Unlisted template pages and original downloads installed. No navigation links were added.' );
