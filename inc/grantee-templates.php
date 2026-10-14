<?php
/** Unlisted, approved grantee templates with crawlable noindex downloads. */
defined( 'ABSPATH' ) || exit;

function cvipi_grantee_templates() {
  return array(
    'document' => array(
      'slug' => 'cvipi-document-template',
      'title' => 'CVIPI Document Template',
      'description' => 'Download the CVIPI Word template for your organization\'s reports and documents.',
      'format' => 'Microsoft Word',
      'filename' => 'CVIPI-Document-Template.docx',
      'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ),
    'presentation' => array(
      'slug' => 'cvipi-presentation-template',
      'title' => 'CVIPI Presentation Template',
      'description' => 'Download the CVIPI PowerPoint template for your organization\'s presentations.',
      'format' => 'Microsoft PowerPoint',
      'filename' => 'CVIPI-Presentation-Template.pptx',
      'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ),
  );
}

function cvipi_grantee_template( $id = 0 ) {
  $id = $id ?: get_queried_object_id();
  if ( 'page-grantee-template.php' !== get_post_meta( $id, '_wp_page_template', true ) ) return null;
  $key = get_post_meta( $id, '_cvipi_grantee_template', true );
  return is_string( $key ) ? ( cvipi_grantee_templates()[$key] ?? null ) : null;
}

function cvipi_grantee_template_directory( $create = false ) {
  $roots = array_filter( array( realpath( ABSPATH ), realpath( $_SERVER['DOCUMENT_ROOT'] ?? ABSPATH ) ) );
  // Staging can be inside the live public_html folder; store outside both.
  for ( $parent = realpath( ABSPATH ); $parent && dirname( $parent ) !== $parent; $parent = dirname( $parent ) ) {
    if ( in_array( basename( $parent ), array( 'public_html', 'htdocs', 'wwwroot', 'www', 'public' ), true ) ) $roots[] = $parent;
  }
  usort( $roots, function ( $a, $b ) { return strlen( $a ) <=> strlen( $b ); } );
  $path = defined( 'CVIPI_GRANTEE_TEMPLATE_DIR' ) ? CVIPI_GRANTEE_TEMPLATE_DIR : dirname( $roots[0] ) . '/.cvipi-grantee-templates';
  if ( ! is_string( $path ) || ! str_starts_with( $path, '/' ) || in_array( basename( $path ), array( '.', '..' ), true ) ) return false;
  $parent = realpath( dirname( $path ) );
  if ( ! $parent ) return false;
  $path = $parent . '/' . basename( $path );
  foreach ( $roots as $root ) {
    if ( str_starts_with( trailingslashit( $path ), trailingslashit( $root ) ) ) return false;
  }
  if ( is_link( $path ) || ( ! is_dir( $path ) && ( ! $create || ! wp_mkdir_p( $path ) ) ) ) return false;
  if ( $create && ! chmod( $path, 0700 ) ) return false;
  return $path;
}

function cvipi_grantee_template_path( $template ) {
  $directory = cvipi_grantee_template_directory();
  if ( ! $directory || ! $template ) return false;
  $path = $directory . '/' . $template['filename'];
  return ! is_link( $path ) && is_file( $path ) && is_readable( $path ) ? $path : false;
}

function cvipi_grantee_template_url( $template ) {
  return home_url( '/' . $template['slug'] . '/' . $template['filename'] );
}

add_action( 'init', function () {
  foreach ( cvipi_grantee_templates() as $key => $template ) {
    add_rewrite_rule( '^' . $template['slug'] . '/' . preg_quote( $template['filename'], '#' ) . '/?$', 'index.php?pagename=' . $template['slug'] . '&cvipi_template_download=' . $key, 'top' );
  }
} );
add_filter( 'query_vars', function ( $vars ) { $vars[] = 'cvipi_template_download'; return $vars; } );

add_action( 'template_redirect', function () {
  $download = get_query_var( 'cvipi_template_download' );
  $template = cvipi_grantee_template();
  if ( ! $template && ! $download ) return;
  header( 'X-Robots-Tag: noindex, nofollow', true );
  if ( ! $download ) return;
  $expected = is_string( $download ) ? ( cvipi_grantee_templates()[$download] ?? null ) : null;
  $path = $template && $template === $expected && is_page( $template['slug'] ) && 'publish' === get_post_status( get_queried_object_id() ) ? cvipi_grantee_template_path( $template ) : false;
  if ( ! $path ) wp_die( 'This template is currently unavailable. Please contact the CVIPI team.', 'Template unavailable', array( 'response' => 404 ) );
  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
    header( 'Allow: GET, HEAD' );
    wp_die( 'This download only accepts GET and HEAD requests.', '', array( 'response' => 405 ) );
  }
  nocache_headers();
  header( 'Content-Type: ' . $template['mime'] );
  header( 'X-Content-Type-Options: nosniff' );
  header( 'Content-Disposition: attachment; filename="' . $template['filename'] . '"' );
  header( 'Content-Length: ' . filesize( $path ) );
  if ( 'GET' === $method ) readfile( $path );
  exit;
}, 1 );

function cvipi_grantee_template_exclusion() {
  return array( 'relation' => 'OR', array( 'key' => '_wp_page_template', 'compare' => 'NOT EXISTS' ), array( 'key' => '_wp_page_template', 'value' => 'page-grantee-template.php', 'compare' => '!=' ) );
}

add_action( 'pre_get_posts', function ( $query ) {
  if ( is_admin() || ! $query->is_search() ) return;
  $existing = $query->get( 'meta_query' );
  $query->set( 'meta_query', array_filter( array( 'relation' => 'AND', $existing, cvipi_grantee_template_exclusion() ) ) );
} );
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
  if ( 'page' === $type ) $args['meta_query'] = array_filter( array( 'relation' => 'AND', $args['meta_query'] ?? array(), cvipi_grantee_template_exclusion() ) );
  return $args;
}, 20, 2 );
add_filter( 'get_pages', function ( $pages ) {
  if ( is_admin() ) return $pages;
  return array_values( array_filter( $pages, function ( $page ) { return 'page-grantee-template.php' !== get_post_meta( $page->ID, '_wp_page_template', true ); } ) );
} );
add_filter( 'wp_robots', function ( $robots ) {
  if ( is_page_template( 'page-grantee-template.php' ) ) {
    unset( $robots['index'], $robots['follow'] );
    $robots['noindex'] = true;
    $robots['nofollow'] = true;
  }
  return $robots;
}, 100 );
