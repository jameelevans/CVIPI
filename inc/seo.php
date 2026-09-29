<?php
/** CVIPI-owned search metadata and native WordPress sitemap policy. */
defined( 'ABSPATH' ) || exit;

function cvipi_seo_live() {
  $url = wp_parse_url( home_url( '/' ) );
  return in_array( $url['host'] ?? '', array( 'cvipi.info', 'www.cvipi.info' ), true ) && '/' === ( $url['path'] ?? '/' ) && '1' === (string) get_option( 'blog_public' );
}

function cvipi_seo_settings() {
  return wp_parse_args( get_option( 'cvipi_seo', array() ), array(
    'home_title' => 'CVIPI | Community Violence Intervention & Prevention',
    'home_description' => 'CVIPI equips organizations with technical assistance to reduce community violence, strengthen partnerships, and build resilient neighborhoods.',
    'index_resources_events' => false,
  ) );
}

function cvipi_seo_text( $text, $limit = 0 ) {
  $text = wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
  $text = trim( preg_replace( '/\s+/u', ' ', $text ) );
  if ( ! $limit || mb_strlen( $text, 'UTF-8' ) <= $limit ) return $text;
  $short = wp_html_excerpt( $text, max( 1, $limit - 3 ), '' );
  $boundary = preg_replace( '/\s+\S*$/u', '', $short );
  return ( $boundary ?: $short ) . '...';
}

function cvipi_seo_types() {
  return cvipi_seo_settings()['index_resources_events'] ? array( 'page', 'success_story', 'post', 'event' ) : array( 'page', 'success_story' );
}

function cvipi_seo_excluded_pages() {
  $slugs = array( 'home' );
  if ( ! cvipi_seo_settings()['index_resources_events'] ) $slugs = array_merge( $slugs, array( 'resources', 'events' ) );
  $ids = array();
  foreach ( $slugs as $slug ) {
    $page = get_page_by_path( $slug );
    if ( $page && (int) get_option( 'page_on_front' ) !== $page->ID ) $ids[] = $page->ID;
  }
  return $ids;
}

function cvipi_seo_post_indexable( $post ) {
  return $post instanceof WP_Post && 'publish' === $post->post_status && ! $post->post_password
    && in_array( $post->post_type, cvipi_seo_types(), true )
    && ! in_array( $post->ID, cvipi_seo_excluded_pages(), true )
    && '1' !== get_post_meta( $post->ID, '_cvipi_seo_noindex', true );
}

function cvipi_seo_noindex() {
  if ( ! cvipi_seo_live() || is_search() || is_404() || is_preview() || is_feed() || is_attachment() ) return true;
  if ( is_front_page() ) return false;
  if ( is_singular() ) return ! cvipi_seo_post_indexable( get_queried_object() );
  return true;
}

function cvipi_seo_title() {
  if ( is_front_page() ) return cvipi_seo_text( cvipi_seo_settings()['home_title'] );
  if ( is_singular() ) {
    $custom = get_post_meta( get_queried_object_id(), '_cvipi_seo_title', true );
    $title = $custom ?: cvipi_seo_text( get_the_title( get_queried_object_id() ) ) . ' | CVIPI';
    $page = max( (int) get_query_var( 'page' ), (int) get_query_var( 'paged' ) );
    return cvipi_seo_text( $title ) . ( $page > 1 ? ' | Page ' . $page : '' );
  }
  return '';
}

function cvipi_seo_description() {
  if ( is_front_page() ) return cvipi_seo_text( cvipi_seo_settings()['home_description'], 180 );
  if ( ! is_singular() ) return '';
  $post = get_queried_object();
  if ( ! $post instanceof WP_Post || $post->post_password ) return '';
  $custom = get_post_meta( $post->ID, '_cvipi_seo_description', true );
  if ( $custom ) return cvipi_seo_text( $custom, 180 );
  $defaults = array(
    'what-is-cvipi' => 'Learn how CVIPI supports community violence intervention through technical assistance, partnerships, and a nationwide network of grantee organizations.',
    'success-stories' => 'Explore stories from CVIPI partner organizations and the community-led programs working to prevent violence and build safer neighborhoods.',
    'contact' => 'Contact the CVIPI team about technical assistance, community violence intervention, and opportunities to connect with our national network.',
    'resources' => 'Find research, tools, and practical resources for community violence intervention and prevention.',
    'events' => 'Explore CVIPI events, training sessions, and recordings for community violence intervention practitioners.',
  );
  $content = $post->post_excerpt ?: ( $defaults[$post->post_name] ?? strip_shortcodes( $post->post_content ) );
  return cvipi_seo_text( $content, 160 );
}

function cvipi_seo_canonical() {
  if ( is_404() || is_search() || is_preview() ) return '';
  if ( is_front_page() ) return home_url( '/' );
  if ( is_singular() ) return wp_get_canonical_url( get_queried_object_id() ) ?: get_permalink( get_queried_object_id() );
  return '';
}

function cvipi_seo_image() {
  $id = is_singular() ? get_post_thumbnail_id( get_queried_object_id() ) : 0;
  if ( $id ) {
    $image = wp_get_attachment_image_src( $id, 'large' );
    if ( $image ) return array( 'url' => $image[0], 'width' => $image[1], 'height' => $image[2], 'alt' => cvipi_seo_text( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );
  }
  $logo = get_template_directory_uri() . '/assets/img/cvipi-logo-email.png';
  if ( is_file( get_template_directory() . '/assets/img/cvipi-logo-email.png' ) ) return array( 'url' => $logo, 'alt' => 'CVIPI' );
  $icon = get_site_icon_url( 512 );
  return $icon ? array( 'url' => $icon, 'alt' => 'CVIPI' ) : array();
}

function cvipi_seo_head() {
  if ( is_feed() ) return;
  $title = cvipi_seo_text( wp_get_document_title() ); $description = cvipi_seo_description(); $url = cvipi_seo_canonical(); $image = cvipi_seo_image();
  if ( $description ) echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
  if ( $url ) echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
  $article = is_singular( array( 'success_story', 'post' ) ) && ! cvipi_seo_noindex();
  $meta = array( 'og:locale' => str_replace( '-', '_', get_bloginfo( 'language' ) ), 'og:type' => $article ? 'article' : 'website', 'og:site_name' => 'CVIPI', 'og:title' => $title, 'og:description' => $description, 'og:url' => $url, 'og:image' => $image['url'] ?? '', 'og:image:alt' => $image['alt'] ?? '' );
  foreach ( array( 'width', 'height' ) as $dimension ) if ( ! empty( $image[$dimension] ) ) $meta['og:image:' . $dimension] = $image[$dimension];
  if ( $article ) {
    $meta['article:published_time'] = get_post_time( 'c', true, get_queried_object_id() );
    $meta['article:modified_time'] = get_post_modified_time( 'c', true, get_queried_object_id() );
  }
  foreach ( $meta as $name => $value ) if ( '' !== (string) $value ) echo '<meta property="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '">' . "\n";
  foreach ( array( 'twitter:card' => ! empty( $image['width'] ) ? 'summary_large_image' : 'summary', 'twitter:title' => $title, 'twitter:description' => $description, 'twitter:image' => $image['url'] ?? '' ) as $name => $value ) if ( $value ) echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '">' . "\n";
  if ( cvipi_seo_noindex() || ! $url ) return;
  $home = home_url( '/' );
  $organization = array( '@type' => 'Organization', '@id' => $home . '#organization', 'name' => 'Community Violence Intervention and Prevention Initiative', 'alternateName' => 'CVIPI', 'url' => $home );
  $logo = get_site_icon_url( 512 );
  if ( $logo ) $organization['logo'] = array( '@type' => 'ImageObject', 'url' => $logo );
  $website = array( '@type' => 'WebSite', '@id' => $home . '#website', 'url' => $home, 'name' => 'CVIPI', 'alternateName' => 'Community Violence Intervention and Prevention Initiative', 'publisher' => array( '@id' => $home . '#organization' ), 'inLanguage' => get_bloginfo( 'language' ) );
  $page_type = is_page( 'contact' ) ? 'ContactPage' : ( is_page( 'what-is-cvipi' ) ? 'AboutPage' : ( is_page( 'success-stories' ) ? 'CollectionPage' : 'WebPage' ) );
  $page = array( '@type' => $page_type, '@id' => $url . '#webpage', 'url' => $url, 'name' => $title, 'description' => $description, 'isPartOf' => array( '@id' => $home . '#website' ), 'inLanguage' => get_bloginfo( 'language' ) );
  $graph = array( $organization, $website, $page );
  if ( $article ) {
    $story = array( '@type' => 'Article', '@id' => $url . '#article', 'headline' => cvipi_seo_text( get_the_title( get_queried_object_id() ) ), 'description' => $description, 'mainEntityOfPage' => array( '@id' => $url . '#webpage' ), 'publisher' => array( '@id' => $home . '#organization' ), 'datePublished' => $meta['article:published_time'], 'dateModified' => $meta['article:modified_time'], 'inLanguage' => get_bloginfo( 'language' ) );
    if ( ! empty( $image['url'] ) ) $story['image'] = array( $image['url'] );
    $graph[] = $story;
  }
  echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

function cvipi_seo_listing_modified( $entry, $types, $page = null ) {
  $latest = get_posts( array( 'post_type' => $types, 'post_status' => 'publish', 'numberposts' => 1, 'orderby' => 'modified', 'order' => 'DESC', 'has_password' => false ) );
  $times = array_filter( array( $entry['lastmod'] ?? '', $page ? get_post_modified_time( DATE_W3C, true, $page ) : '', $latest ? get_post_modified_time( DATE_W3C, true, $latest[0] ) : '' ) );
  if ( $times ) { usort( $times, function ( $a, $b ) { return strtotime( $b ) <=> strtotime( $a ); } ); $entry['lastmod'] = $times[0]; }
  return $entry;
}

// During migration, Yoast retains ownership until it is deactivated.
if ( ! defined( 'WPSEO_VERSION' ) ) {
  add_action( 'after_setup_theme', function () { add_theme_support( 'title-tag' ); } );
  add_filter( 'pre_get_document_title', function () { return esc_html( cvipi_seo_title() ); } );
  add_action( 'wp', function () { remove_action( 'wp_head', 'rel_canonical' ); } );
  add_action( 'wp_head', 'cvipi_seo_head', 3 );
  add_filter( 'wp_robots', function ( $robots ) {
    if ( cvipi_seo_noindex() ) { unset( $robots['index'] ); $robots['noindex'] = true; }
    else { $robots['max-image-preview'] = 'large'; $robots['max-snippet'] = '-1'; $robots['max-video-preview'] = '-1'; }
    return $robots;
  }, 99 );
  add_filter( 'wp_sitemaps_enabled', function () { return cvipi_seo_live(); } );
  add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) { return in_array( $name, array( 'users', 'taxonomies' ), true ) ? false : $provider; }, 10, 2 );
  add_filter( 'wp_sitemaps_post_types', function ( $types ) { return array_intersect_key( $types, array_flip( cvipi_seo_types() ) ); } );
  add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
    $args['has_password'] = false;
    $args['post__not_in'] = array_unique( array_merge( $args['post__not_in'] ?? array(), cvipi_seo_excluded_pages() ) );
    $args['meta_query'][] = array( 'relation' => 'OR', array( 'key' => '_cvipi_seo_noindex', 'compare' => 'NOT EXISTS' ), array( 'key' => '_cvipi_seo_noindex', 'value' => '1', 'compare' => '!=' ) );
    return $args;
  }, 10, 2 );
  add_filter( 'wp_sitemaps_posts_entry', function ( $entry, $post ) {
    $entry['lastmod'] = get_post_modified_time( DATE_W3C, true, $post );
    if ( 'page' === $post->post_type && 'success-stories' === $post->post_name ) return cvipi_seo_listing_modified( $entry, array( 'success_story' ), $post );
    if ( 'page' === $post->post_type && 'what-is-cvipi' === $post->post_name ) return cvipi_seo_listing_modified( $entry, array( 'map_marker', 'ta_provider' ), $post );
    return $entry;
  }, 10, 2 );
  add_filter( 'wp_sitemaps_posts_show_on_front_entry', function ( $entry ) {
    unset( $entry['lastmod'] );
    return cvipi_seo_listing_modified( $entry, array( 'success_story', 'ta_provider' ), get_page_by_path( 'home' ) );
  } );
  add_filter( 'robots_txt', function () {
    if ( ! cvipi_seo_live() ) return "User-agent: *\nDisallow: /\n";
    return "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nDisallow: /wp-login.php\nDisallow: /staging/\n\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
  }, 99 );
  add_action( 'template_redirect', function () {
    if ( get_query_var( 'sitemap' ) || is_robots() ) nocache_headers();
    if ( ! cvipi_seo_live() && ! is_admin() ) header( 'X-Robots-Tag: noindex, nofollow', true );
    $path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
    $base = rtrim( wp_parse_url( home_url(), PHP_URL_PATH ) ?? '', '/' );
    if ( $path === $base . '/sitemap_index.xml' ) { wp_safe_redirect( home_url( '/wp-sitemap.xml' ), 301, 'CVIPI SEO' ); exit; }
    $home_page = get_page_by_path( 'home' );
    if ( ! is_preview() && is_page( 'home' ) && $home_page && ! is_front_page() ) { wp_safe_redirect( home_url( '/' ), 301, 'CVIPI SEO' ); exit; }
  }, 0 );
}
