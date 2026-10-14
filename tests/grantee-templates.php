<?php
/** Local WP CLI integration checks; no writes to content or site settings. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) WP_CLI::error( $message );
  $checks++;
};
$ids = array();
$hashes = array(
  'document' => 'bc9f346540716d9e664e391770bb585de08991faf44ec0c740df20cd824ce5e2',
  'presentation' => '0234c7681ca8f29a3c083bf05c79e02f0e103f85213625849f1cdfee00e0f416',
);
foreach ( cvipi_grantee_templates() as $key => $template ) {
  $page = get_page_by_path( $template['slug'] );
  $assert( $page && 'publish' === $page->post_status, $key . ' page is published' );
  $ids[] = $page->ID;
  $assert( $template === cvipi_grantee_template( $page->ID ), $key . ' uses the unlisted template' );
  $assert( '1' === get_post_meta( $page->ID, '_cvipi_seo_noindex', true ), $key . ' has SEO exclusion metadata' );
  $assert( ! cvipi_seo_post_indexable( $page ), $key . ' is excluded even on an indexable live site' );
  $path = cvipi_grantee_template_path( $template );
  $assert( $path && hash_file( 'sha256', $path ) === $hashes[$key], $key . ' bytes match the approved original' );
  $assert( ! str_starts_with( $path, realpath( ABSPATH ) . '/' ), $key . ' original is not in the webroot' );
  $assert( ! cvipi_grantee_template( get_option( 'page_on_front' ) ), 'Homepage has no download mapping' );
  $request = $template['slug'] . '/' . $template['filename'];
  $rules = $GLOBALS['wp_rewrite']->wp_rewrite_rules();
  $matched = false;
  foreach ( $rules as $pattern => $query ) {
    if ( preg_match( '#^' . $pattern . '#', $request ) ) {
      $matched = str_contains( $query, 'pagename=' . $template['slug'] ) && str_contains( $query, 'cvipi_template_download=' . $key );
      break;
    }
  }
  $assert( $matched, $key . ' download has a clean WordPress route' );
}
$search = new WP_Query( array( 's' => 'CVIPI', 'post_type' => 'page', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$assert( ! array_intersect( $ids, $search->posts ), 'Unlisted templates are absent from frontend search' );
$normal = get_page_by_path( 'what-is-cvipi' );
$assert( $normal && in_array( $normal->ID, $search->posts, true ), 'Ordinary matching pages remain searchable' );
$sitemap_args = apply_filters( 'wp_sitemaps_posts_query_args', array( 'post_type' => 'page', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1 ), 'page' );
$sitemap = new WP_Query( $sitemap_args );
$assert( ! array_intersect( $ids, $sitemap->posts ), 'Live page-sitemap query excludes both templates' );
$listing = wp_list_pluck( get_pages(), 'ID' );
$assert( ! array_intersect( $ids, $listing ), 'Automatic frontend page listings exclude templates' );
$assert( in_array( (int) get_option( 'page_on_front' ), $listing, true ), 'Automatic listings retain ordinary pages' );

// Exercise live policy in memory; no WordPress options or content are modified.
add_filter( 'home_url', function () { return 'https://cvipi.info/'; } );
add_filter( 'pre_option_blog_public', function () { return '1'; } );
$assert( cvipi_seo_live(), 'Live policy is enabled in the test context' );
$assert( cvipi_seo_post_indexable( $normal ), 'Normal published pages remain indexable on live' );
foreach ( $ids as $id ) {
  $GLOBALS['wp_query'] = new WP_Query( array( 'page_id' => $id ) );
  $assert( cvipi_seo_noindex(), 'Template remains noindex on the live domain' );
  $robots = apply_filters( 'wp_robots', array( 'index' => true, 'follow' => true ) );
  $assert( ! isset( $robots['index'] ) && ! isset( $robots['follow'] ) && ! empty( $robots['noindex'] ), 'Template robots directive overrides index/follow' );
}
WP_CLI::success( $checks . ' grantee-template integration checks passed.' );
