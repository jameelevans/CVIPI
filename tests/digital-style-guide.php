<?php
/** Read-only WP CLI checks for the unlisted brand guide and approved downloads. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
$checks = 0;
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) WP_CLI::error( $message );
  $checks++;
};
$page = get_page_by_path( 'cvipi-digital-style-guide' );
$assert( $page && 'publish' === $page->post_status, 'Guide is published for direct-link visitors' );
$assert( 'page-digital-style-guide.php' === get_page_template_slug( $page ), 'Guide uses the dedicated template' );
$assert( ! cvipi_seo_post_indexable( $page ), 'Guide is excluded from automatic SEO indexing' );
$assert( '1' === get_post_meta( $page->ID, '_cvipi_seo_noindex', true ), 'Guide has explicit noindex metadata' );
parse_str( wp_parse_url( cvipi_style_guide_image_url( 'advising.jpg' ), PHP_URL_QUERY ), $image_query );
$assert( (int) $image_query['ver'] === filemtime( get_theme_file_path( 'assets/img/style-guide/advising.jpg' ) ), 'Guide images use their file versions to refresh cached copies' );
$assert( abs( cvipi_style_guide_contrast( '#000000', '#FFFFFF' ) - 21 ) < 0.0001, 'Black on white has 21:1 contrast' );
$assert( 1.0 === cvipi_style_guide_contrast( '#071820', '#071820' ), 'Identical colors have 1:1 contrast' );
$assert( cvipi_style_guide_contrast( '#071820', '#F4EBDA' ) === cvipi_style_guide_contrast( '#F4EBDA', '#071820' ), 'Contrast calculation is symmetric' );
$palette = cvipi_style_guide_palette();
$assert( count( $palette ) === 13, 'Guide includes the current brand palette and Muted Text' );
foreach ( $palette as $color ) {
  $assert( preg_match( '/^#[0-9A-F]{6}$/', $color['hex'] ) && ( str_starts_with( $color['token'], '--color-' ) || '--text-3' === $color['token'] ), 'Every palette entry uses a theme color token and valid hex' );
}
$assert( cvipi_style_guide_contrast( $palette['muted']['hex'], $palette['sand']['hex'] ) < 3, 'Muted Text fails even the large-text threshold on sand' );
$white_ratio = cvipi_style_guide_contrast( $palette['muted']['hex'], $palette['white']['hex'] );
$assert( $white_ratio >= 3 && $white_ratio < 4.5, 'Muted Text is large-text-only on white, as the warnings state' );
$checklist = cvipi_style_guide_checklist_path();
$assert( $checklist && ! is_link( $checklist ), 'Approved checklist is present and not a symlink' );
$assert( ! str_starts_with( $checklist, realpath( ABSPATH ) . '/' ), 'Checklist is stored outside the public webroot' );
$assert( hash_file( 'sha256', $checklist ) === '5d36a4cce6b984c20f82be8e99ad0250fa4ef18b8ef9d709175a838e7e34bebd', 'Checklist matches the original Figma download' );
$search = new WP_Query( array( 's' => 'CVIPI', 'post_type' => 'page', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$assert( ! in_array( $page->ID, $search->posts, true ), 'Guide is absent from frontend search' );
$sitemap = new WP_Query( apply_filters( 'wp_sitemaps_posts_query_args', array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ), 'page' ) );
$assert( ! in_array( $page->ID, $sitemap->posts, true ), 'Guide is absent from the page sitemap' );
$assert( ! in_array( $page->ID, wp_list_pluck( get_pages(), 'ID' ), true ), 'Guide is absent from automatic page listings' );
foreach ( wp_get_nav_menus() as $menu ) {
  foreach ( wp_get_nav_menu_items( $menu->term_id ) as $item ) {
    $assert( 'page' !== $item->object || (int) $item->object_id !== $page->ID, 'Guide has not been added to public navigation' );
  }
}

// Simulate the live indexing policy in memory, without changing site settings.
add_filter( 'home_url', function () { return 'https://cvipi.info/'; } );
add_filter( 'pre_option_blog_public', function () { return '1'; } );
$GLOBALS['wp_query'] = new WP_Query( array( 'page_id' => $page->ID ) );
$assert( cvipi_seo_live() && cvipi_is_digital_style_guide(), 'Live policy recognizes the dedicated guide' );
$assert( cvipi_seo_noindex(), 'Guide remains noindex even when live is indexable' );
$robots = apply_filters( 'wp_robots', array( 'index' => true, 'follow' => true ) );
$assert( ! isset( $robots['index'] ) && ! isset( $robots['follow'] ) && ! empty( $robots['noindex'] ) && ! empty( $robots['nofollow'] ), 'Guide overrides index/follow directives' );
WP_CLI::success( $checks . ' digital style guide integration checks passed.' );
