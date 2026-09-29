<?php
/** Run with local wp eval-file only. Test posts are removed in finally. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || cvipi_seo_live() ) exit( 'Local-only test.' );
function cvipi_seo_check( $pass, $label ) { if ( ! $pass ) throw new RuntimeException( $label ); echo "PASS: $label\n"; }
cvipi_seo_check( ! cvipi_seo_live(), 'Local indexing is disabled' );
cvipi_seo_check( false === apply_filters( 'wp_sitemaps_enabled', true ), 'Local sitemap is disabled' );
cvipi_seo_check( str_contains( apply_filters( 'robots_txt', '', 1 ), 'Disallow: /' ), 'Local robots disallows crawling' );
cvipi_seo_check( current_theme_supports( 'title-tag' ), 'Document titles are enabled' );
cvipi_seo_check( 'Community safety' === cvipi_seo_text( 'Community &lt;em&gt;safety&lt;/em&gt;' ), 'Encoded heading markup is removed' );
add_filter( 'pre_option_home', function () { return 'https://cvipi.info'; } );
add_filter( 'pre_option_blog_public', function () { return '1'; } );
cvipi_seo_check( cvipi_seo_live(), 'Live root hostname is recognized' );
cvipi_seo_check( array( 'page', 'success_story' ) === cvipi_seo_types(), 'Internal and unreleased content excluded' );
$id = wp_insert_post( array( 'post_type' => 'success_story', 'post_status' => 'publish', 'post_title' => 'SEO TEST ONLY', 'post_content' => 'A community-led violence intervention story with practical support for local organizations.' ), true );
if ( is_wp_error( $id ) ) throw new RuntimeException( $id->get_error_message() );
try {
  $post = get_post( $id );
  cvipi_seo_check( cvipi_seo_post_indexable( $post ), 'Published stories are eligible' );
  $provider = new WP_Sitemaps_Posts();
  $find = function () use ( $provider, $id ) { return in_array( get_permalink( $id ), array_column( $provider->get_url_list( 1, 'success_story' ), 'loc' ), true ); };
  cvipi_seo_check( $find(), 'A new published story appears in the sitemap immediately' );
  update_post_meta( $id, '_cvipi_seo_noindex', '1' );
  cvipi_seo_check( ! $find() && ! cvipi_seo_post_indexable( get_post( $id ) ), 'Noindex stories disappear from sitemap' );
  delete_post_meta( $id, '_cvipi_seo_noindex' );
  wp_update_post( array( 'ID' => $id, 'post_status' => 'pending' ) );
  cvipi_seo_check( ! $find(), 'Pending submissions stay out of sitemap' );
  wp_update_post( array( 'ID' => $id, 'post_status' => 'publish', 'post_password' => 'test-private' ) );
  cvipi_seo_check( ! $find(), 'Password-protected content stays out of sitemap' );
  wp_update_post( array( 'ID' => $id, 'post_password' => '' ) );
  $GLOBALS['wp_query'] = new WP_Query( array( 'p' => $id, 'post_type' => 'success_story' ) );
  $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
  cvipi_seo_check( str_contains( cvipi_seo_title(), 'SEO TEST ONLY' ), 'Story title is generated automatically' );
  cvipi_seo_check( str_contains( cvipi_seo_description(), 'community-led' ), 'Story description follows content automatically' );
  ob_start(); cvipi_seo_head(); $html = ob_get_clean();
  $dom = new DOMDocument(); @$dom->loadHTML( $html ); $xpath = new DOMXPath( $dom );
  cvipi_seo_check( 1 === $xpath->query( '//link[@rel="canonical"]' )->length, 'Exactly one canonical is emitted' );
  $schema = json_decode( $xpath->query( '//script[@type="application/ld+json"]' )->item( 0 )->textContent, true, 512, JSON_THROW_ON_ERROR );
  cvipi_seo_check( in_array( 'Article', array_column( $schema['@graph'], '@type' ), true ), 'Story structured data is valid JSON' );
  cvipi_seo_check( str_contains( apply_filters( 'robots_txt', '', 1 ), 'Sitemap: https://cvipi.info/wp-sitemap.xml' ), 'Live robots advertises the native sitemap' );
  add_filter( 'pre_option_home', function () { return 'https://cvipi.info/staging/8175'; }, 20 );
  cvipi_seo_check( ! cvipi_seo_live() && cvipi_seo_noindex(), 'Staging cannot accidentally become indexable' );
} finally { wp_delete_post( $id, true ); }
