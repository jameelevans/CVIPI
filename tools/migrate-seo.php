<?php
/** Run once using wp eval-file before removing Yoast. Keeps its source data intact. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
if ( get_option( 'cvipi_seo_migrated' ) ) { WP_CLI::success( 'SEO migration already completed.' ); return; }
$count = 0;
$posts = get_posts( array( 'post_type' => array( 'page', 'post', 'success_story', 'event' ), 'post_status' => 'any', 'numberposts' => -1 ) );
foreach ( $posts as $post ) {
  foreach ( array( 'title' => '_yoast_wpseo_title', 'description' => '_yoast_wpseo_metadesc' ) as $field => $source ) {
    $value = get_post_meta( $post->ID, $source, true );
    if ( ! $value || get_post_meta( $post->ID, '_cvipi_seo_' . $field, true ) ) continue;
    if ( function_exists( 'wpseo_replace_vars' ) ) $value = wpseo_replace_vars( $value, $post );
    if ( str_contains( $value, '%%' ) ) continue;
    update_post_meta( $post->ID, '_cvipi_seo_' . $field, cvipi_seo_text( $value, 'title' === $field ? 120 : 180 ) );
    $count++;
  }
  if ( '1' === get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true ) ) update_post_meta( $post->ID, '_cvipi_seo_noindex', '1' );
}
update_option( 'cvipi_seo_migrated', gmdate( 'c' ), false );
WP_CLI::success( 'Preserved ' . $count . ' existing custom SEO values. Original Yoast metadata retained for rollback.' );
