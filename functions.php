<?php
/**
 * Theme functions and WordPress hooks.
 *
 * This file registers global theme behavior: front-end assets, theme support,
 * login-screen customization, SVG helper output, and Customizer settings.
 *      
 * @package cvipi
 */

// * * --------| Actions and filters in order |-------- *

  // Register front-end styles and scripts.
  add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts' );

  // Loads the compiled theme bundle without blocking document parsing.
  add_filter( 'script_loader_tag', 'cvipi_defer_theme_bundle', 10, 3 );




// * * --------| Functions in order |-------- *

  // Enqueue the compiled JavaScript bundle and the root WordPress theme stylesheet.
  function theme_enqueue_scripts() {
  $theme_version = wp_get_theme()->get( 'Version' );
  $script_path   = get_template_directory() . '/assets/js/scripts-bundled.js';
  $style_path    = get_stylesheet_directory() . '/style.css';
  $script_version = file_exists( $script_path ) ? filemtime( $script_path ) : $theme_version;
  $style_version  = file_exists( $style_path ) ? filemtime( $style_path ) : $theme_version;

  wp_enqueue_script( 'Bundled_js', get_template_directory_uri() . '/assets/js/scripts-bundled.js', array(), $script_version, true );
  wp_localize_script(
    'Bundled_js',
    'cvipiAjax',
    array(
      'ajaxUrl' => admin_url( 'admin-ajax.php' ),
      'nonce'   => wp_create_nonce( 'cvipi_ajax_nonce' ),
    )
  );
  wp_enqueue_style( 'cvipi_main_styles', get_stylesheet_uri(), array(), $style_version );
  }

  // Add defer to the compiled theme bundle using WordPress' script tag filter
  // instead of altering the script URL.
  function cvipi_defer_theme_bundle( $tag, $handle, $src ) {
  if ( 'Bundled_js' !== $handle ) {
    return $tag;
  }

  return '<script src="' . esc_url( $src ) . '" defer></script>';
  }

//* Register theme supports and custom image sizes.
function cvipi_custom_logo_setup() {
  $defaults = array(
      'height'      => 38,
      'width'       => 38,
      'flex-height' => true,
      'flex-width'  => true,
      'header-text' => array( 'CVIPI', 'The National Community Violence Intervention & Prevention Initiative' ),
  );
  add_theme_support( 'custom-logo', $defaults );

  // Custom image sizes available to templates and media functions.
  add_image_size( 'my-thumbnail', 300, 169, false);
  add_image_size( 'x-small', 450, 253, false);
  add_image_size( 'small', 600, 338, false);
  add_image_size( 'medium', 768, 432, false);
  add_image_size( 'regular', 1024, 576, false);
  add_image_size( 'large', 1200, 675, false);
  add_image_size( 'med-large', 1600, 901, false);
  add_image_size( 'x-large', 2000, 1125, false);
  add_image_size( 'xx-large', 3000, 1688, false);
  add_image_size( 'full-size', 3200, 1801, false);
  add_image_size('pageBanner', 1300, 700, true);
}
add_action( 'after_setup_theme', 'cvipi_custom_logo_setup' );
add_theme_support( 'post-thumbnails' );
// .Activate the ability to add custom logo in customizer
// .Enable support for Post Thumbnails on posts and pages


//* 5. Add site link to logo on login screen
function ourHeaderUrl() {
  return esc_url(site_url('/'));
}
add_filter('login_headerurl', 'ourHeaderUrl');
// .Add site link to logo on login screen





//* 4. Make css styles available to login screen
function cvipi_login_css() {
  wp_enqueue_style('cvipi_main_styles', get_stylesheet_uri());
  }
add_action('login_enqueue_scripts', 'cvipi_login_css');
// .Make css styles available to login screen

//* 5. Replace WP logo with site title name on login screen
function cvipi_login_title() {
  return get_bloginfo('name');
}
add_filter('login_headertitle', 'cvipi_login_title');
// .Replace WP logo with site title name on login screen


//* 7. Add theme title to login screen
function ourLoginTitle() {
  return get_bloginfo('name');
}
add_filter('login_headertitle', 'ourLoginTitle');
// .Add theme title to login screen

//* Rename the built-in Posts post type to Resources in the admin.
function cvipi_rename_posts_to_resources() {
  global $wp_post_types;

  $labels = &$wp_post_types['post']->labels;

  $labels->name = 'Resources';
  $labels->singular_name = 'Resource';
  $labels->add_new = 'Add New';
  $labels->add_new_item = 'Add New Resource';
  $labels->edit_item = 'Edit Resource';
  $labels->new_item = 'Resource';
  $labels->view_item = 'View Resource';
  $labels->view_items = 'View Resources';
  $labels->search_items = 'Search Resources';
  $labels->not_found = 'No resources found';
  $labels->not_found_in_trash = 'No resources found in Trash';
  $labels->all_items = 'All Resources';
  $labels->archives = 'Resource Archives';
  $labels->attributes = 'Resource Attributes';
  $labels->insert_into_item = 'Insert into resource';
  $labels->uploaded_to_this_item = 'Uploaded to this resource';
  $labels->filter_items_list = 'Filter resources list';
  $labels->items_list_navigation = 'Resources list navigation';
  $labels->items_list = 'Resources list';
  $labels->item_published = 'Resource published';
  $labels->item_published_privately = 'Resource published privately';
  $labels->item_reverted_to_draft = 'Resource reverted to draft';
  $labels->item_scheduled = 'Resource scheduled';
  $labels->item_updated = 'Resource updated';
  $labels->menu_name = 'Resources';
  $labels->name_admin_bar = 'Resource';
}
add_action( 'init', 'cvipi_rename_posts_to_resources' );
// .Rename built-in Posts labels.

//* Display an inline SVG icon from the theme sprite sheet.
// The $class argument lets templates apply BEM-specific icon styling.
function svg_icon( $class, $icon ) {
  static $symbols = null;

  if ( null === $symbols ) {
    $symbols     = array();
    $sprite_path = get_stylesheet_directory() . '/assets/img/sprite.svg';

    if ( file_exists( $sprite_path ) ) {
      $sprite = file_get_contents( $sprite_path );

      if ( preg_match_all( '/<symbol\\s+id="icon-([^"]+)"\\s+viewBox="([^"]+)"[^>]*>(.*?)<\\/symbol>/s', $sprite, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
          $symbols[ $match[1] ] = array(
            'viewBox' => $match[2],
            'markup'  => $match[3],
          );
        }
      }
    }
  }

  $symbol = isset( $symbols[ $icon ] ) ? $symbols[ $icon ] : null;
  ?>
  <svg class="<?php echo esc_attr( $class ); ?>" aria-hidden="true" <?php echo $symbol ? 'viewBox="' . esc_attr( $symbol['viewBox'] ) . '"' : ''; ?>>
    <?php if ( $symbol ) : ?>
      <?php echo wp_kses( $symbol['markup'], array( 'path' => array( 'd' => true ) ) ); ?>
    <?php else : ?>
      <use href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/sprite.svg' ); ?>#icon-<?php echo esc_attr( $icon ); ?>" xlink:href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/sprite.svg' ); ?>#icon-<?php echo esc_attr( $icon ); ?>"></use>
    <?php endif; ?>
  </svg>
  <?php
}
// .Display inline svg icon from sprite sheet with custom class

function cvipi_get_primary_nav_items() {
  return array(
    'home' => array(
      'label' => 'Home',
      'url'   => home_url( '/' ),
      'title' => 'Go to the Home page',
    ),
    'what-is-cvipi' => array(
      'label' => 'What is CVIPI?',
      'url'   => site_url( '/what-is-cvipi' ),
      'title' => 'Go to the What is CVIPI page',
    ),
    'success-stories' => array(
      'label' => 'Success Stories',
      'url'   => site_url( '/success-stories' ),
      'title' => 'Go to the Success Stories page',
    ),
    'resources' => array(
      'label' => 'Resources',
      'url'   => site_url( '/resources' ),
      'title' => 'Go to the Resources page',
    ),
    'events' => array(
      'label' => 'Events',
      'url'   => site_url( '/events' ),
      'title' => 'Go to the Events page',
    ),
    'contact' => array(
      'label' => 'Contact',
      'url'   => site_url( '/contact' ),
      'title' => 'Go to the Contact page',
    ),
  );
}

function cvipi_is_primary_nav_item_current( $item_key ) {
  switch ( $item_key ) {
    case 'home':
      return is_front_page() || is_home();
    case 'what-is-cvipi':
      return is_page( 'what-is-cvipi' );
    case 'success-stories':
      return is_page( 'success-stories' ) || is_singular( 'success_story' ) || is_post_type_archive( 'success_story' );
    case 'resources':
      return is_page( 'resources' ) || is_singular( 'post' ) || is_category() || is_tag() || is_search();
    case 'events':
      return is_page( 'events' ) || is_singular( 'event' ) || is_post_type_archive( 'event' ) || is_tax( array( 'event_type', 'event_topic' ) );
    case 'contact':
      return is_page( 'contact' );
    default:
      return false;
  }
}

function cvipi_render_primary_navigation( $mobile = false ) {
  $item_class = $mobile ? 'mobile-navigation__item' : 'navigation__item';
  $link_class = $mobile ? 'mobile-navigation__link' : 'navigation__link';

  foreach ( cvipi_get_primary_nav_items() as $item_key => $item ) {
    $is_current   = cvipi_is_primary_nav_item_current( $item_key );
    $item_classes = $item_class . ( $is_current ? ' ' . $item_class . '--current' : '' );
    ?>
    <li class="<?php echo esc_attr( $item_classes ); ?>">
      <a
        href="<?php echo esc_url( $item['url'] ); ?>"
        class="<?php echo esc_attr( $link_class ); ?>"
        <?php echo ! $mobile ? 'title="' . esc_attr( $item['title'] ) . '"' : ''; ?>
        <?php echo $is_current ? 'aria-current="page"' : ''; ?>
      ><?php echo esc_html( $item['label'] ); ?></a>
    </li>
    <?php
  }
}

//* Resource category cards shown on the homepage.
function cvipi_resource_category_cards() {
  return array(
    'archived-events-webinars' => array(
      'icon'      => 'play1',
      'cta_label' => 'Browse recordings',
      'color'     => 'var(--color-dark-blue)',
    ),
    'toolkits' => array(
      'icon'      => 'gear',
      'cta_label' => 'Browse toolkits',
      'color'     => 'var(--color-deep)',
    ),
    'research-reports' => array(
      'icon'      => 'report1',
      'cta_label' => 'Browse research',
      'color'     => 'var(--color-dark-orange)',
    ),
    'other-resources' => array(
      'icon'      => 'file1',
      'cta_label' => 'Browse resources',
      'color'     => 'var(--color-black)',
    ),
  );
}

function cvipi_get_resource_category_card_by_post( $post_id = null ) {
  $post_id       = $post_id ? $post_id : get_the_ID();
  $category_cards = cvipi_resource_category_cards();
  $categories    = get_the_category( $post_id );

  if ( empty( $categories ) ) {
    return array(
      'term'  => null,
      'icon'  => 'file1',
      'color' => 'var(--color-dark-midnight-teal)',
    );
  }

  foreach ( $categories as $category ) {
    if ( isset( $category_cards[ $category->slug ] ) ) {
      return array_merge(
        array(
          'term' => $category,
        ),
        $category_cards[ $category->slug ]
      );
    }
  }

  return array(
    'term'  => $categories[0],
    'icon'  => 'file1',
    'color' => 'var(--color-dark-midnight-teal)',
  );
}

function cvipi_get_resource_query_args( $filters = array() ) {
  $allowed_category_slugs = array_keys( cvipi_resource_category_cards() );
  $category_slug          = isset( $filters['resource_category'] ) ? sanitize_title( $filters['resource_category'] ) : '';
  $topic_slug             = isset( $filters['resource_topic'] ) ? sanitize_title( $filters['resource_topic'] ) : '';
  $search_query           = isset( $filters['resource_search'] ) ? sanitize_text_field( $filters['resource_search'] ) : '';
  $resource_year          = isset( $filters['resource_year'] ) ? absint( $filters['resource_year'] ) : 0;
  $exclude_ids            = isset( $filters['exclude_ids'] ) ? array_map( 'absint', (array) $filters['exclude_ids'] ) : array();

  $query_args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => -1,
    'ignore_sticky_posts' => true,
    'post__not_in'        => array_filter( $exclude_ids ),
  );

  if ( $search_query ) {
    $query_args['s'] = $search_query;
  }

  if ( $resource_year ) {
    $resource_year_ids = get_posts(
      array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true,
        'fields'              => 'ids',
      )
    );

    $resource_year_ids = array_filter(
      $resource_year_ids,
      function ( $resource_year_post_id ) use ( $resource_year ) {
        return (int) cvipi_get_resource_display_year( $resource_year_post_id ) === $resource_year;
      }
    );

    $query_args['post__in'] = ! empty( $resource_year_ids ) ? array_map( 'absint', $resource_year_ids ) : array( 0 );
  }

  $tax_query = array();

  if ( $category_slug && in_array( $category_slug, $allowed_category_slugs, true ) ) {
    $tax_query[] = array(
      'taxonomy' => 'category',
      'field'    => 'slug',
      'terms'    => $category_slug,
    );
  } else {
    $tax_query[] = array(
      'taxonomy' => 'category',
      'field'    => 'slug',
      'terms'    => $allowed_category_slugs,
    );
  }

  if ( $topic_slug ) {
    $tax_query[] = array(
      'taxonomy' => 'post_tag',
      'field'    => 'slug',
      'terms'    => $topic_slug,
    );
  }

  if ( count( $tax_query ) > 1 ) {
    $tax_query['relation'] = 'AND';
  }

  $query_args['tax_query'] = $tax_query;

  return $query_args;
}

function cvipi_get_resource_meta_value( $post_id, $key ) {
  if ( function_exists( 'get_field' ) ) {
    $value = get_field( $key, $post_id );

    if ( $value ) {
      return $value;
    }
  }

  return get_post_meta( $post_id, $key, true );
}

function cvipi_get_resource_display_date( $post_id, $format = 'F Y' ) {
  $custom_date = cvipi_get_resource_meta_value( $post_id, 'resource_custom_date' );

  if ( $custom_date ) {
    $timestamp = is_numeric( $custom_date )
      ? DateTime::createFromFormat( 'Ymd', (string) $custom_date )
      : date_create( $custom_date );

    if ( $timestamp ) {
      return date_i18n( $format, $timestamp->getTimestamp() );
    }
  }

  return get_the_date( $format, $post_id );
}

function cvipi_get_resource_display_year( $post_id ) {
  return cvipi_get_resource_display_date( $post_id, 'Y' );
}

function cvipi_get_resource_link_data( $post_id ) {
  $youtube_link     = cvipi_get_resource_meta_value( $post_id, 'resource_youtube_link' );
  $resource_document = cvipi_get_resource_meta_value( $post_id, 'resource_document' );
  $custom_link_text = cvipi_get_resource_meta_value( $post_id, 'resource_link_text' );
  $document_url     = '';

  if ( is_array( $resource_document ) && ! empty( $resource_document['url'] ) ) {
    $document_url = $resource_document['url'];
  } elseif ( is_numeric( $resource_document ) ) {
    $document_url = wp_get_attachment_url( (int) $resource_document );
  } elseif ( is_string( $resource_document ) ) {
    $document_url = $resource_document;
  }

  if ( $youtube_link ) {
    return array(
      'url'    => esc_url_raw( $youtube_link ),
      'label'  => $custom_link_text ? $custom_link_text : 'Watch Resource',
      'target' => '_blank',
    );
  }

  if ( $document_url ) {
    return array(
      'url'    => esc_url_raw( $document_url ),
      'label'  => $custom_link_text ? $custom_link_text : 'Download Resource',
      'target' => '_blank',
    );
  }

  return array(
    'url'    => get_permalink( $post_id ),
    'label'  => $custom_link_text ? $custom_link_text : 'View Resource',
    'target' => '',
  );
}

function cvipi_get_resource_primary_topic_tag( $post_id ) {
  $tags = get_the_terms( $post_id, 'post_tag' );

  if ( is_wp_error( $tags ) || empty( $tags ) ) {
    return null;
  }

  $primary_topic_slugs = array(
    'violence-intervention',
    'community-violence-intervention',
    'data-evaluation',
    'program-design',
    'policy-advocacy',
    'impact',
    'youth-program',
  );

  foreach ( $primary_topic_slugs as $primary_topic_slug ) {
    foreach ( $tags as $tag ) {
      if ( $primary_topic_slug === $tag->slug ) {
        return $tag;
      }
    }
  }

  return $tags[0];
}

function cvipi_get_single_context( $post_id ) {
  $post_type = get_post_type( $post_id );
  $context   = array(
    'type'            => 'resource',
    'archive_label'   => 'Resources',
    'archive_url'     => cvipi_get_filtered_resources_url(),
    'details_heading' => 'Resource Details',
    'primary_term'    => null,
    'tag_terms'       => array(),
    'location'        => '',
    'details'         => array(),
    'aside_icon'      => '',
    'action'          => null,
  );

  if ( 'success_story' === $post_type ) {
    $story_categories = get_the_terms( $post_id, 'category' );
    $story_location   = ! is_wp_error( $story_categories ) && ! empty( $story_categories ) ? wp_specialchars_decode( $story_categories[0]->name, ENT_QUOTES ) : '';

    $context['type']            = 'story';
    $context['archive_label']   = 'Success Stories';
    $context['archive_url']     = home_url( '/success-stories/' );
    $context['details_heading'] = 'Success Story';
    $context['location']        = $story_location;
    $context['tag_terms']       = cvipi_get_post_tags_for_single( $post_id );
    $context['details']         = array_filter(
      array(
        array( 'label' => 'Location', 'value' => $story_location ),
        array( 'label' => 'Published', 'value' => get_the_date( 'F j, Y', $post_id ) ),
      ),
      function( $item ) {
        return ! empty( $item['value'] );
      }
    );

    return $context;
  }

  if ( 'event' === $post_type ) {
    $event_terms         = cvipi_get_event_card_terms( $post_id );
    $event_type          = ! empty( $event_terms[0] ) ? $event_terms[0] : null;
    $event_tags          = array_slice( $event_terms, 1 );
    $event_cta_url       = cvipi_get_event_field( 'event_cta_url', $post_id );
    $event_recording_url = cvipi_get_event_field( 'event_recording_url', $post_id );
    $event_label         = cvipi_get_event_field( 'event_cta_label', $post_id );
    $event_presented_by  = cvipi_get_event_field( 'event_presented_by', $post_id );

    $context['type']            = 'event';
    $context['archive_label']   = 'Events';
    $context['archive_url']     = cvipi_get_filtered_events_url();
    $context['details_heading'] = 'Event Details';
    $context['primary_term']    = $event_type;
    $context['tag_terms']       = $event_tags;
    $context['aside_icon']      = $event_recording_url ? 'play1' : ( 'upcoming' === cvipi_get_event_status( $post_id ) ? 'calendar' : '' );
    $context['details']         = array_filter(
      array(
        array( 'label' => 'Date', 'value' => cvipi_get_event_date_label( $post_id, false ) ),
        array( 'label' => 'Presented By', 'value' => $event_presented_by ),
        array( 'label' => 'Location', 'value' => cvipi_get_event_field( 'event_location', $post_id ) ),
        array( 'label' => 'Duration', 'value' => cvipi_get_event_field( 'event_duration', $post_id ) ),
      ),
      function( $item ) {
        return ! empty( $item['value'] );
      }
    );
    if ( $event_recording_url ) {
      $context['action'] = array(
        'url'    => $event_recording_url,
        'label'  => 'Play Recording',
        'target' => '',
        'video'  => true,
        'icon'   => 'aside-play',
      );
    } elseif ( $event_cta_url ) {
      $context['action'] = array(
        'url'    => $event_cta_url,
        'label'  => $event_label ? $event_label : 'RSVP',
        'target' => '_blank',
        'video'  => false,
        'icon'   => 'display',
      );
    }

    return $context;
  }

  $resource_category_card = cvipi_get_resource_category_card_by_post( $post_id );
  $resource_link          = cvipi_get_resource_link_data( $post_id );
  $resource_type          = cvipi_get_resource_meta_value( $post_id, 'resource_type' );
  $resource_length        = cvipi_get_resource_meta_value( $post_id, 'resource_length' );
  $resource_document      = cvipi_get_resource_meta_value( $post_id, 'resource_document' );

  $context['primary_term'] = $resource_category_card['term'] ? array(
    'name' => wp_specialchars_decode( $resource_category_card['term']->name, ENT_QUOTES ),
    'url'  => cvipi_get_filtered_resources_url( $resource_category_card['term']->slug ),
  ) : null;
  $context['tag_terms']    = cvipi_get_post_tags_for_single( $post_id );
  $context['details']      = array_filter(
    array(
      array( 'label' => 'Published', 'value' => cvipi_get_resource_display_date( $post_id, 'F j, Y' ) ),
      array( 'label' => 'Type', 'value' => $resource_type ),
      array( 'label' => 'Length', 'value' => $resource_length ),
    ),
    function( $item ) {
      return ! empty( $item['value'] );
    }
  );
  $context['aside_icon']   = $resource_document ? 'file1' : 'book';
  $context['action']       = array_merge(
    $resource_link,
    array(
      'icon' => 'file1',
    )
  );

  return $context;
}

function cvipi_get_single_share_label( $post_id ) {
  $post_type = get_post_type( $post_id );

  if ( 'success_story' === $post_type ) {
    return 'Share This Story';
  }

  if ( 'event' === $post_type ) {
    return 'Share Recording';
  }

  if ( 'post' === $post_type ) {
    $resource_document = cvipi_get_resource_meta_value( $post_id, 'resource_document' );

    return $resource_document ? 'Share Resource' : 'Share Article';
  }

  return 'Share This Page';
}

function cvipi_get_post_tags_for_single( $post_id ) {
  $tags = get_the_terms( $post_id, 'post_tag' );

  if ( is_wp_error( $tags ) || empty( $tags ) ) {
    return array();
  }

  return array_map(
    function( $tag ) {
      $tag_link = get_term_link( $tag );

      return array(
        'name' => wp_specialchars_decode( $tag->name, ENT_QUOTES ),
        'url'  => is_wp_error( $tag_link ) ? '' : $tag_link,
        'slug' => $tag->slug,
      );
    },
    $tags
  );
}

function cvipi_get_single_header_media( $post_id ) {
  $image = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'x-large' ) : '';
  $video = '';

  if ( function_exists( 'get_field' ) ) {
    $custom_image = get_field( 'single_header_image', $post_id );
    $custom_video = get_field( 'single_header_video', $post_id );

    if ( is_array( $custom_image ) && ! empty( $custom_image['url'] ) ) {
      $image = $custom_image['url'];
    } elseif ( is_numeric( $custom_image ) ) {
      $image = wp_get_attachment_url( (int) $custom_image );
    } elseif ( is_string( $custom_image ) && $custom_image ) {
      $image = $custom_image;
    }

    if ( is_array( $custom_video ) && ! empty( $custom_video['url'] ) ) {
      $video = $custom_video['url'];
    } elseif ( is_numeric( $custom_video ) ) {
      $video = wp_get_attachment_url( (int) $custom_video );
    } elseif ( is_string( $custom_video ) && $custom_video ) {
      $video = $custom_video;
    }
  }

  return array(
    'image'     => $image,
    'video'     => $video,
    'has_media' => (bool) ( $image || $video ),
  );
}

function cvipi_render_single_breadcrumbs( $context ) {
  ?>
  <nav class="single-breadcrumbs" aria-label="Breadcrumb">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
    <span aria-hidden="true">&rsaquo;</span>
    <a href="<?php echo esc_url( $context['archive_url'] ); ?>"><?php echo esc_html( $context['archive_label'] ); ?></a>
  </nav>
  <?php
}

function cvipi_render_single_aside_icon( $context ) {
  if ( empty( $context['aside_icon'] ) ) {
    return;
  }
  ?>
  <div class="single-hero__aside-icon" aria-hidden="true">
    <?php echo svg_icon( 'single-hero__aside-svg', $context['aside_icon'] ); ?>
  </div>
  <?php
}

function cvipi_render_single_pills( $post_id, $context ) {
  if ( empty( $context['primary_term'] ) && empty( $context['tag_terms'] ) ) {
    return;
  }
  ?>
  <div class="single-hero__pills" aria-label="Post categories and tags">
    <?php if ( ! empty( $context['primary_term'] ) ) : ?>
      <a class="single-hero__pill single-hero__pill--primary" href="<?php echo esc_url( $context['primary_term']['url'] ); ?>"><?php echo esc_html( $context['primary_term']['name'] ); ?></a>
    <?php endif; ?>
    <?php foreach ( $context['tag_terms'] as $tag_term ) : ?>
      <a class="single-hero__pill" href="<?php echo esc_url( $tag_term['url'] ); ?>"><?php echo esc_html( $tag_term['name'] ); ?></a>
    <?php endforeach; ?>
  </div>
  <?php
}

function cvipi_render_single_details( $post_id, $context ) {
  $share_label   = cvipi_get_single_share_label( $post_id );
  $share_classes = 'single-hero__action single-hero__share';

  if ( 'success_story' !== get_post_type( $post_id ) ) {
    $share_classes .= ' single-hero__share--subtle';
  }
  ?>
  <?php if ( ! empty( $context['details'] ) ) : ?>
    <dl class="single-hero__details">
      <?php foreach ( $context['details'] as $detail ) : ?>
        <div class="single-hero__detail">
          <dt><?php echo esc_html( $detail['label'] ); ?></dt>
          <dd><?php echo esc_html( $detail['value'] ); ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
  <?php endif; ?>
  <?php if ( ! empty( $context['action']['url'] ) && ! empty( $context['action']['video'] ) ) : ?>
    <button
      class="single-hero__action"
      type="button"
      data-video-lightbox-trigger
      data-video-url="<?php echo esc_url( $context['action']['url'] ); ?>"
      data-video-title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
    >
      <?php echo svg_icon( 'single-hero__action-icon', ! empty( $context['action']['icon'] ) ? $context['action']['icon'] : 'aside-play' ); ?>
      <?php echo esc_html( $context['action']['label'] ); ?>
    </button>
  <?php elseif ( ! empty( $context['action']['url'] ) ) : ?>
    <a class="single-hero__action" href="<?php echo esc_url( $context['action']['url'] ); ?>" <?php echo ! empty( $context['action']['target'] ) ? 'target="' . esc_attr( $context['action']['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
      <?php echo svg_icon( 'single-hero__action-icon', ! empty( $context['action']['icon'] ) ? $context['action']['icon'] : 'file1' ); ?>
      <?php echo esc_html( $context['action']['label'] ); ?>
    </a>
  <?php endif; ?>
  <button
    class="<?php echo esc_attr( $share_classes ); ?>"
    type="button"
    data-share-button
    aria-label="<?php echo esc_attr( $share_label . ': ' . get_the_title( $post_id ) ); ?>"
  >
    <?php echo svg_icon( 'single-hero__action-icon', 'aside-share' ); ?>
    <?php echo esc_html( $share_label ); ?>
  </button>
  <?php
}

function cvipi_get_single_video_url( $post_id ) {
  if ( 'event' === get_post_type( $post_id ) ) {
    return cvipi_get_event_field( 'event_recording_url', $post_id );
  }

  if ( 'post' === get_post_type( $post_id ) ) {
    return cvipi_get_resource_meta_value( $post_id, 'resource_youtube_link' );
  }

  return '';
}

function cvipi_get_single_video_label( $post_id ) {
  if ( 'event' === get_post_type( $post_id ) ) {
    return cvipi_get_event_field( 'event_duration', $post_id );
  }

  if ( 'post' === get_post_type( $post_id ) ) {
    return cvipi_get_resource_meta_value( $post_id, 'resource_length' );
  }

  return '';
}

function cvipi_get_youtube_video_id( $url ) {
  $parts = wp_parse_url( $url );

  if ( empty( $parts['host'] ) ) {
    return '';
  }

  $host = preg_replace( '/^www\./', '', $parts['host'] );

  if ( 'youtu.be' === $host && ! empty( $parts['path'] ) ) {
    return trim( $parts['path'], '/' );
  }

  if ( false !== strpos( $host, 'youtube.com' ) ) {
    if ( ! empty( $parts['query'] ) ) {
      parse_str( $parts['query'], $query_args );

      if ( ! empty( $query_args['v'] ) ) {
        return sanitize_text_field( $query_args['v'] );
      }
    }

    if ( ! empty( $parts['path'] ) ) {
      $path_parts = array_values( array_filter( explode( '/', trim( $parts['path'], '/' ) ) ) );
      return ! empty( $path_parts ) ? sanitize_text_field( end( $path_parts ) ) : '';
    }
  }

  return '';
}

function cvipi_get_single_video_thumbnail_url( $post_id, $video_url ) {
  $youtube_video_id = cvipi_get_youtube_video_id( $video_url );

  if ( $youtube_video_id ) {
    return 'https://i.ytimg.com/vi/' . rawurlencode( $youtube_video_id ) . '/maxresdefault.jpg';
  }

  return has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'large' ) : '';
}

function cvipi_render_single_resource_video( $post_id ) {
  $video_url = cvipi_get_single_video_url( $post_id );

  if ( ! $video_url ) {
    return;
  }

  $poster = cvipi_get_single_video_thumbnail_url( $post_id, $video_url );
  $label  = cvipi_get_single_video_label( $post_id );
  ?>
  <div class="single-content__video">
    <button
      class="single-content__video-trigger"
      type="button"
      data-video-lightbox-trigger
      data-video-url="<?php echo esc_url( $video_url ); ?>"
      data-video-title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
      <?php echo $poster ? 'style="--single-video-poster: url(\'' . esc_url( $poster ) . '\');"' : ''; ?>
    >
      <span class="single-content__video-screen" aria-hidden="true"></span>
      <span class="single-content__video-play">
        <?php echo svg_icon( 'single-content__video-icon', 'play1' ); ?>
      </span>
      <?php if ( $label ) : ?>
        <span class="single-content__video-label"><?php echo esc_html( $label ); ?></span>
      <?php endif; ?>
    </button>
  </div>
  <?php
}

function cvipi_get_related_resources( $post_id, $count = 4 ) {
  $tag_ids = wp_get_post_terms( $post_id, 'post_tag', array( 'fields' => 'ids' ) );
  $args    = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $count,
    'post__not_in'        => array( $post_id ),
    'ignore_sticky_posts' => true,
  );

  if ( ! is_wp_error( $tag_ids ) && ! empty( $tag_ids ) ) {
    $args['tax_query'] = array(
      array(
        'taxonomy' => 'post_tag',
        'field'    => 'term_id',
        'terms'    => $tag_ids,
      ),
    );
  }

  $related_query = new WP_Query( $args );

  if ( $related_query->have_posts() || empty( $args['tax_query'] ) ) {
    return $related_query;
  }

  wp_reset_postdata();

  unset( $args['tax_query'] );
  return new WP_Query( $args );
}

function cvipi_render_related_resource_list( $related_resources ) {
  if ( ! $related_resources->have_posts() ) {
    echo '<p class="single-related__empty">No resources are available yet.</p>';
    return;
  }
  ?>
  <div class="single-related__list">
    <?php
    while ( $related_resources->have_posts() ) :
      $related_resources->the_post();
      $resource_type = cvipi_get_resource_meta_value( get_the_ID(), 'resource_type' );
      $resource_type = $resource_type ? $resource_type : 'Resource';
      ?>
      <article class="single-related__item">
        <a href="<?php echo esc_url( get_permalink() ); ?>">
          <span class="single-related__type"><?php echo esc_html( $resource_type ); ?></span>
          <span class="single-related__title"><?php echo esc_html( get_the_title() ); ?></span>
          <span class="single-related__date"><?php echo esc_html( cvipi_get_resource_display_date( get_the_ID(), 'M Y' ) ); ?></span>
        </a>
      </article>
      <?php
    endwhile;
    wp_reset_postdata();
    ?>
  </div>
  <?php
}

function cvipi_render_more_success_stories( $post_id ) {
  $stories = new WP_Query(
    array(
      'post_type'           => 'success_story',
      'post_status'         => 'publish',
      'posts_per_page'      => 3,
      'post__not_in'        => array( $post_id ),
      'ignore_sticky_posts' => true,
    )
  );

  if ( ! $stories->have_posts() ) {
    return;
  }
  ?>
  <section class="single-more-stories">
    <h2 class="single-more-stories__heading">More Stories</h2>
    <div class="single-more-stories__list">
      <?php
      while ( $stories->have_posts() ) :
        $stories->the_post();
        ?>
        <article class="single-more-stories__item">
          <a href="<?php echo esc_url( get_permalink() ); ?>">
            <span class="single-more-stories__title"><?php echo esc_html( get_the_title() ); ?></span>
            <span class="single-more-stories__link">Read Story <?php echo svg_icon( 'single-more-stories__icon', 'arrow-right' ); ?></span>
          </a>
        </article>
        <?php
      endwhile;
      wp_reset_postdata();
      ?>
    </div>
  </section>
  <?php
}

function cvipi_render_resource_card( $post_id = null, $hidden = false ) {
  $post_id       = $post_id ? $post_id : get_the_ID();
  $category_card = cvipi_get_resource_category_card_by_post( $post_id );
  $category      = $category_card['term'];
  $category_name = $category ? wp_specialchars_decode( $category->name, ENT_QUOTES ) : 'Resource';
  $topic_tag     = cvipi_get_resource_primary_topic_tag( $post_id );
  $topic_name    = $topic_tag ? wp_specialchars_decode( $topic_tag->name, ENT_QUOTES ) : '';
  $published     = cvipi_get_resource_display_date( $post_id );
  $resource_link = cvipi_get_resource_link_data( $post_id );
  $length        = cvipi_get_resource_meta_value( $post_id, 'resource_length' );
  $resource_type = cvipi_get_resource_meta_value( $post_id, 'resource_type' );
  ob_start();
  ?>
  <article class="resources-page__card" style="--resource-accent: <?php echo esc_attr( $category_card['color'] ); ?>;" <?php echo $hidden ? 'hidden' : ''; ?>>
    <a href="<?php echo esc_url( $resource_link['url'] ); ?>" class="resources-page__card-link" <?php echo $resource_link['target'] ? 'target="' . esc_attr( $resource_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
      <span class="resources-page__card-type">
        <?php echo svg_icon( 'resources-page__type-icon', $category_card['icon'] ); ?>
        <span><?php echo esc_html( $category_name ); ?></span>
      </span>

      <span class="resources-page__card-body">
        <?php if ( $topic_name ) : ?>
          <span class="resources-page__category"><?php echo esc_html( $topic_name ); ?></span>
        <?php endif; ?>

        <span class="resources-page__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>

        <span class="resources-page__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 22 ) ); ?></span>

        <span class="resources-page__meta">
          <span class="resources-page__meta-item"><?php echo esc_html( $published ); ?></span>
          <?php if ( $length ) : ?>
            <span class="resources-page__meta-separator" aria-hidden="true"></span>
            <span class="resources-page__meta-item"><?php echo esc_html( $length ); ?></span>
          <?php endif; ?>
          <?php if ( $resource_type ) : ?>
            <span class="resources-page__meta-separator" aria-hidden="true"></span>
            <span class="resources-page__meta-item"><?php echo esc_html( $resource_type ); ?></span>
          <?php endif; ?>
        </span>

        <span class="resources-page__cta"><?php echo esc_html( $resource_link['label'] ); ?></span>
      </span>
    </a>
  </article>
  <?php
  return ob_get_clean();
}

function cvipi_get_success_story_query_args( $story_tag = '' ) {
  $query_args = array(
    'post_type'           => 'success_story',
    'posts_per_page'      => -1,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'orderby'             => 'date',
    'order'               => 'DESC',
  );

  if ( $story_tag ) {
    $query_args['tax_query'] = array(
      array(
        'taxonomy' => 'post_tag',
        'field'    => 'slug',
        'terms'    => sanitize_title( $story_tag ),
      ),
    );
  }

  return $query_args;
}

function cvipi_get_success_story_tags() {
  $story_ids = get_posts(
    array(
      'post_type'              => 'success_story',
      'post_status'            => 'publish',
      'posts_per_page'         => -1,
      'fields'                 => 'ids',
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    )
  );

  if ( empty( $story_ids ) ) {
    return array();
  }

  $terms = get_terms(
    array(
      'taxonomy'   => 'post_tag',
      'hide_empty' => true,
      'object_ids' => $story_ids,
      'orderby'    => 'name',
      'order'      => 'ASC',
    )
  );

  return is_wp_error( $terms ) ? array() : $terms;
}

function cvipi_render_success_story_card( $post_id = null ) {
  $post_id          = $post_id ? $post_id : get_the_ID();
  $story_categories = get_the_terms( $post_id, 'category' );
  $story_category   = ! is_wp_error( $story_categories ) && ! empty( $story_categories ) ? $story_categories[0]->name : '';

  ob_start();
  ?>
  <article class="story story--feature">
    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
      <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="story__top" aria-label="<?php echo esc_attr( 'Read ' . get_the_title( $post_id ) ); ?>">
        <?php
        echo wp_get_attachment_image(
          get_post_thumbnail_id( $post_id ),
          'large',
          false,
          array(
            'class' => 'story__img',
            'sizes' => '(min-width: 900px) 48rem, 100vw',
          )
        );
        ?>
      </a>
    <?php endif; ?>

    <div class="story__bottom">
      <header class="story__header">
        <?php if ( $story_category ) : ?>
          <p class="story__category">
            <?php echo svg_icon( 'story__category-icon', 'location-2' ); ?>
            <span><?php echo esc_html( wp_specialchars_decode( $story_category, ENT_QUOTES ) ); ?></span>
          </p>
        <?php endif; ?>
        <h2 class="story__heading">
          <?php echo esc_html( get_the_title( $post_id ) ); ?>
        </h2>
      </header>

      <p class="story__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 26 ) ); ?></p>
      <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="story__button">Read Full Story <?php echo svg_icon( 'story__button-icon', 'arrow-right' ); ?></a>
    </div>
  </article>
  <?php
  return ob_get_clean();
}

function cvipi_get_success_story_count_label( $count ) {
  return sprintf(
    _n( '%s story', '%s stories', $count, 'cvipi' ),
    number_format_i18n( $count )
  );
}

function cvipi_render_success_story_cards( $stories ) {
  if ( empty( $stories ) ) {
    return '<p class="stories__empty">No success stories matched this filter.</p>';
  }

  $html = '';

  foreach ( $stories as $story ) {
    $html .= cvipi_render_success_story_card( $story->ID );
  }

  return $html;
}

function cvipi_render_success_story_past_item( $post_id ) {
  $story_categories = get_the_terms( $post_id, 'category' );
  $story_category   = ! is_wp_error( $story_categories ) && ! empty( $story_categories ) ? $story_categories[0]->name : '';
  $story_month      = get_the_date( 'M', $post_id );
  $story_year       = get_the_date( 'Y', $post_id );

  ob_start();
  ?>
  <article class="stories__past-item">
    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="stories__past-link">
      <span class="stories__past-date">
        <span class="stories__past-month"><?php echo esc_html( strtoupper( $story_month ) ); ?></span>
        <span class="stories__past-year"><?php echo esc_html( $story_year ); ?></span>
      </span>
      <span class="stories__past-content">
        <span class="stories__past-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
        <?php if ( $story_category ) : ?>
          <span class="stories__past-location"><?php echo esc_html( wp_specialchars_decode( $story_category, ENT_QUOTES ) ); ?></span>
        <?php endif; ?>
      </span>
      <?php echo svg_icon( 'stories__past-icon', 'arrow-right' ); ?>
    </a>
  </article>
  <?php
  return ob_get_clean();
}

function cvipi_render_success_story_past_items( $stories ) {
  if ( empty( $stories ) ) {
    return '';
  }

  $html = '';

  foreach ( $stories as $story ) {
    $html .= cvipi_render_success_story_past_item( $story->ID );
  }

  return $html;
}

function cvipi_ajax_filter_success_stories() {
  check_ajax_referer( 'cvipi_ajax_nonce', 'nonce' );

  $story_tag      = isset( $_POST['story_tag'] ) ? sanitize_title( wp_unslash( $_POST['story_tag'] ) ) : '';
  $story_query    = new WP_Query( cvipi_get_success_story_query_args( $story_tag ) );
  $stories        = $story_query->posts;
  $featured       = array_slice( $stories, 0, 4 );
  $past_stories   = array_slice( $stories, 4 );
  $featured_html  = cvipi_render_success_story_cards( $featured );
  $past_html      = cvipi_render_success_story_past_items( $past_stories );
  $total_count    = (int) $story_query->found_posts;
  $past_count     = count( $past_stories );

  wp_send_json_success(
    array(
      'html'          => $featured_html,
      'featured_html' => $featured_html,
      'past_html'     => $past_html,
      'count'         => $total_count,
      'label'         => cvipi_get_success_story_count_label( $total_count ),
      'past_count'    => $past_count,
      'past_label'    => cvipi_get_success_story_count_label( $past_count ),
    )
  );
}
add_action( 'wp_ajax_cvipi_filter_success_stories', 'cvipi_ajax_filter_success_stories' );
add_action( 'wp_ajax_nopriv_cvipi_filter_success_stories', 'cvipi_ajax_filter_success_stories' );

//* Count published Resources assigned to one category.
function cvipi_get_resource_count_by_category( $term_id ) {
  $resource_count_query = new WP_Query(
    array(
      'post_type'              => 'post',
      'post_status'            => 'publish',
      'posts_per_page'         => 1,
      'fields'                 => 'ids',
      'ignore_sticky_posts'    => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
      'tax_query'              => array(
        array(
          'taxonomy' => 'category',
          'field'    => 'term_id',
          'terms'    => $term_id,
        ),
      ),
    )
  );

  return (int) $resource_count_query->found_posts;
}

//* Build a Resources page URL with a category filter query string.
function cvipi_get_filtered_resources_url( $term_slug = '' ) {
  $resources_page = get_page_by_path( 'resources' );
  $resources_url  = $resources_page ? get_permalink( $resources_page ) : home_url( '/resources/' );

  if ( ! $term_slug ) {
    return $resources_url;
  }

  return add_query_arg( 'resource_category', $term_slug, $resources_url );
}
// .Homepage resource category helpers.

function cvipi_register_resource_acf_fields() {
  if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
  }

  acf_add_local_field_group(
    array(
      'key'      => 'group_cvipi_resource_details',
      'title'    => 'Resource Details',
      'fields'   => array(
        array(
          'key'           => 'field_cvipi_featured_resource',
          'label'         => 'Featured Resource',
          'name'          => 'featured_resource',
          'type'          => 'true_false',
          'instructions'  => 'Only one Resource can be featured at a time. Saving this as featured will unfeature the others.',
          'ui'            => 1,
          'default_value' => 0,
        ),
        array(
          'key'           => 'field_cvipi_resource_youtube_link',
          'label'         => 'YouTube Link',
          'name'          => 'resource_youtube_link',
          'type'          => 'url',
          'instructions'  => 'Optional external YouTube URL for videos, webinars, or recordings.',
        ),
        array(
          'key'           => 'field_cvipi_resource_document',
          'label'         => 'Document',
          'name'          => 'resource_document',
          'type'          => 'file',
          'instructions'  => 'Optional downloadable file for this resource.',
          'return_format' => 'url',
          'library'       => 'all',
        ),
        array(
          'key'          => 'field_cvipi_resource_length',
          'label'        => 'Length of Resource',
          'name'         => 'resource_length',
          'type'         => 'text',
          'instructions' => 'Short text shown in resource metadata, for example: 45 min, 12 pages, Toolkit.',
        ),
        array(
          'key'          => 'field_cvipi_resource_type',
          'label'        => 'Resource Type',
          'name'         => 'resource_type',
          'type'         => 'text',
          'instructions' => 'Short text shown in resource metadata, for example: PDF, Video, Report, or Toolkit.',
        ),
        array(
          'key'          => 'field_cvipi_resource_link_text',
          'label'        => 'Custom Link Text',
          'name'         => 'resource_link_text',
          'type'         => 'text',
          'instructions' => 'Overrides the card button text, for example: Watch Webinar or Download Toolkit.',
        ),
        array(
          'key'           => 'field_cvipi_resource_custom_date',
          'label'         => 'Custom Resource Date',
          'name'          => 'resource_custom_date',
          'type'          => 'date_picker',
          'instructions'  => 'Optional display date used in place of the regular post date.',
          'display_format' => 'F j, Y',
          'return_format' => 'Ymd',
          'first_day'     => 0,
        ),
      ),
      'location' => array(
        array(
          array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'post',
          ),
        ),
      ),
    )
  );
}
add_action( 'acf/init', 'cvipi_register_resource_acf_fields' );

function cvipi_register_single_header_acf_fields() {
  if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
  }

  acf_add_local_field_group(
    array(
      'key'      => 'group_cvipi_single_header_media',
      'title'    => 'Single Header Media',
      'fields'   => array(
        array(
          'key'           => 'field_cvipi_single_header_video',
          'label'         => 'Header Background Video',
          'name'          => 'single_header_video',
          'type'          => 'file',
          'instructions'  => 'Optional looping background video for this single page header.',
          'return_format' => 'url',
          'library'       => 'all',
          'mime_types'    => 'mp4,webm,mov',
        ),
        array(
          'key'           => 'field_cvipi_single_header_image',
          'label'         => 'Header Background Image',
          'name'          => 'single_header_image',
          'type'          => 'image',
          'instructions'  => 'Optional image used instead of the featured image for this single page header.',
          'return_format' => 'url',
          'preview_size'  => 'medium',
          'library'       => 'all',
        ),
      ),
      'location' => array(
        array(
          array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'post',
          ),
        ),
        array(
          array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'success_story',
          ),
        ),
        array(
          array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'event',
          ),
        ),
      ),
    )
  );
}
add_action( 'acf/init', 'cvipi_register_single_header_acf_fields' );

function cvipi_enforce_single_featured_resource( $post_id ) {
  if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
    return;
  }

  if ( 'post' !== get_post_type( $post_id ) ) {
    return;
  }

  $is_featured = get_post_meta( $post_id, 'featured_resource', true );

  if ( ! $is_featured ) {
    return;
  }

  $other_featured_resources = get_posts(
    array(
      'post_type'      => 'post',
      'post_status'    => 'any',
      'posts_per_page' => -1,
      'post__not_in'   => array( $post_id ),
      'fields'         => 'ids',
      'meta_query'     => array(
        array(
          'key'     => 'featured_resource',
          'value'   => '1',
          'compare' => '=',
        ),
      ),
    )
  );

  foreach ( $other_featured_resources as $other_resource_id ) {
    update_post_meta( $other_resource_id, 'featured_resource', 0 );
  }
}
add_action( 'acf/save_post', 'cvipi_enforce_single_featured_resource', 20 );
add_action( 'save_post', 'cvipi_enforce_single_featured_resource', 20 );

function cvipi_ajax_filter_resources() {
  check_ajax_referer( 'cvipi_ajax_nonce', 'nonce' );

  $filters = array(
    'resource_category' => isset( $_POST['resource_category'] ) ? sanitize_title( wp_unslash( $_POST['resource_category'] ) ) : '',
    'resource_topic'    => isset( $_POST['resource_topic'] ) ? sanitize_title( wp_unslash( $_POST['resource_topic'] ) ) : '',
    'resource_search'   => isset( $_POST['resource_search'] ) ? sanitize_text_field( wp_unslash( $_POST['resource_search'] ) ) : '',
    'resource_year'     => isset( $_POST['resource_year'] ) ? absint( $_POST['resource_year'] ) : 0,
    'exclude_ids'       => isset( $_POST['exclude_ids'] ) ? array_map( 'absint', (array) $_POST['exclude_ids'] ) : array(),
  );

  $resource_query = new WP_Query( cvipi_get_resource_query_args( $filters ) );
  $html           = '';

  if ( $resource_query->have_posts() ) {
    while ( $resource_query->have_posts() ) {
      $resource_query->the_post();
      $html .= cvipi_render_resource_card();
    }

    wp_reset_postdata();
  } else {
    $html = '<p class="resources-page__empty">No resources matched your filters.</p>';
  }

  wp_send_json_success(
    array(
      'html'  => $html,
      'count' => (int) $resource_query->found_posts,
      'label' => sprintf(
        _n( '%s resource found', '%s resources found', $resource_query->found_posts, 'cvipi' ),
        number_format_i18n( $resource_query->found_posts )
      ),
    )
  );
}
add_action( 'wp_ajax_cvipi_filter_resources', 'cvipi_ajax_filter_resources' );
add_action( 'wp_ajax_nopriv_cvipi_filter_resources', 'cvipi_ajax_filter_resources' );

//* CVIPI map marker post type, fields, rendering, and AJAX helpers.
function cvipi_get_map_fiscal_years() {
  return array(
    '2022' => array(
      'label' => 'FY 2022',
      'color' => '#bb722a',
    ),
    '2023' => array(
      'label' => 'FY 2023',
      'color' => '#277ca8',
    ),
    '2024' => array(
      'label' => 'FY 2024',
      'color' => '#174f6b',
    ),
  );
}

function cvipi_register_map_marker_post_type() {
  register_post_type(
    'map_marker',
    array(
      'labels'       => array(
        'name'               => 'Map Markers',
        'singular_name'      => 'Map Marker',
        'add_new_item'       => 'Add New Map Marker',
        'edit_item'          => 'Edit Map Marker',
        'new_item'           => 'New Map Marker',
        'view_item'          => 'View Map Marker',
        'search_items'       => 'Search Map Markers',
        'not_found'          => 'No map markers found',
        'not_found_in_trash' => 'No map markers found in Trash',
        'all_items'          => 'All Map Markers',
        'menu_name'          => 'Map Markers',
      ),
      'public'       => true,
      'has_archive'  => false,
      'menu_icon'    => 'dashicons-location-alt',
      'show_in_rest' => true,
      'supports'     => array( 'title', 'editor', 'thumbnail' ),
      'rewrite'      => array( 'slug' => 'map-marker' ),
    )
  );
}
add_action( 'init', 'cvipi_register_map_marker_post_type' );

function cvipi_add_map_marker_meta_box() {
  add_meta_box(
    'cvipi_map_marker_details',
    'Map Marker Details',
    'cvipi_render_map_marker_meta_box',
    'map_marker',
    'normal',
    'high'
  );
}
add_action( 'add_meta_boxes', 'cvipi_add_map_marker_meta_box' );

function cvipi_render_map_marker_meta_box( $post ) {
  $fiscal_years = cvipi_get_map_fiscal_years();
  $fiscal_year  = get_post_meta( $post->ID, 'map_marker_fiscal_year', true );
  $award        = get_post_meta( $post->ID, 'map_marker_award', true );
  $location     = get_post_meta( $post->ID, 'map_marker_location_name', true );
  $x            = get_post_meta( $post->ID, 'map_marker_x', true );
  $y            = get_post_meta( $post->ID, 'map_marker_y', true );

  wp_nonce_field( 'cvipi_save_map_marker_details', 'cvipi_map_marker_details_nonce' );
  ?>
  <p>
    <label for="map_marker_fiscal_year"><strong>Fiscal Year</strong></label><br>
    <select id="map_marker_fiscal_year" name="map_marker_fiscal_year">
      <?php foreach ( $fiscal_years as $year => $year_data ) : ?>
        <option value="<?php echo esc_attr( $year ); ?>" <?php selected( $fiscal_year, $year ); ?>><?php echo esc_html( $year_data['label'] ); ?></option>
      <?php endforeach; ?>
    </select>
  </p>
  <p>
    <label for="map_marker_award"><strong>Award</strong></label><br>
    <input type="text" id="map_marker_award" name="map_marker_award" value="<?php echo esc_attr( $award ); ?>" class="widefat" placeholder="$2.0M">
  </p>
  <p>
    <label for="map_marker_location_name"><strong>Location Name</strong></label><br>
    <input type="text" id="map_marker_location_name" name="map_marker_location_name" value="<?php echo esc_attr( $location ); ?>" class="widefat" placeholder="Toledo, OH">
  </p>
  <p>
    <label for="map_marker_x"><strong>Map X Position</strong></label><br>
    <input type="number" id="map_marker_x" name="map_marker_x" value="<?php echo esc_attr( $x ); ?>" min="0" max="100" step="0.01">%
  </p>
  <p>
    <label for="map_marker_y"><strong>Map Y Position</strong></label><br>
    <input type="number" id="map_marker_y" name="map_marker_y" value="<?php echo esc_attr( $y ); ?>" min="0" max="100" step="0.01">%
  </p>
  <p class="description">Editors can also drag markers directly on the What is CVIPI page while logged in. Positions are stored as SVG percentage coordinates.</p>
  <?php
}

function cvipi_save_map_marker_meta( $post_id ) {
  if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'map_marker' !== get_post_type( $post_id ) ) {
    return;
  }

  if ( ! isset( $_POST['cvipi_map_marker_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cvipi_map_marker_details_nonce'] ) ), 'cvipi_save_map_marker_details' ) ) {
    return;
  }

  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  $fiscal_years = cvipi_get_map_fiscal_years();
  $fiscal_year  = isset( $_POST['map_marker_fiscal_year'] ) ? sanitize_text_field( wp_unslash( $_POST['map_marker_fiscal_year'] ) ) : '';
  $award        = isset( $_POST['map_marker_award'] ) ? sanitize_text_field( wp_unslash( $_POST['map_marker_award'] ) ) : '';
  $location     = isset( $_POST['map_marker_location_name'] ) ? sanitize_text_field( wp_unslash( $_POST['map_marker_location_name'] ) ) : '';
  $x            = isset( $_POST['map_marker_x'] ) ? min( 100, max( 0, (float) $_POST['map_marker_x'] ) ) : 50;
  $y            = isset( $_POST['map_marker_y'] ) ? min( 100, max( 0, (float) $_POST['map_marker_y'] ) ) : 50;

  update_post_meta( $post_id, 'map_marker_fiscal_year', isset( $fiscal_years[ $fiscal_year ] ) ? $fiscal_year : '2024' );
  update_post_meta( $post_id, 'map_marker_award', $award );
  update_post_meta( $post_id, 'map_marker_location_name', $location );
  update_post_meta( $post_id, 'map_marker_x', $x );
  update_post_meta( $post_id, 'map_marker_y', $y );
}
add_action( 'save_post_map_marker', 'cvipi_save_map_marker_meta' );

function cvipi_get_map_marker_query_args( $fiscal_year = '' ) {
  $query_args = array(
    'post_type'           => 'map_marker',
    'post_status'         => 'publish',
    'posts_per_page'      => -1,
    'orderby'             => 'title',
    'order'               => 'ASC',
    'ignore_sticky_posts' => true,
  );

  if ( $fiscal_year && 'all' !== $fiscal_year ) {
    $query_args['meta_query'] = array(
      array(
        'key'   => 'map_marker_fiscal_year',
        'value' => sanitize_text_field( $fiscal_year ),
      ),
    );
  }

  return $query_args;
}

function cvipi_can_edit_map_markers() {
  return is_user_logged_in() && current_user_can( 'edit_posts' );
}

function cvipi_get_map_marker_data( $fiscal_year = '' ) {
  $markers = array();
  $query   = new WP_Query( cvipi_get_map_marker_query_args( $fiscal_year ) );
  $can_edit_map = cvipi_can_edit_map_markers();

  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      $post_id     = get_the_ID();
      $marker_year = get_post_meta( $post_id, 'map_marker_fiscal_year', true );
      $years       = cvipi_get_map_fiscal_years();
      $year_data   = isset( $years[ $marker_year ] ) ? $years[ $marker_year ] : $years['2024'];

      $markers[] = array(
        'id'           => $post_id,
        'title'        => get_the_title(),
        'content'      => wp_trim_words( wp_strip_all_tags( get_the_content() ), 18 ),
        'fiscal_year'  => $marker_year ? $marker_year : '2024',
        'year_label'   => $year_data['label'],
        'color'        => $year_data['color'],
        'award'        => get_post_meta( $post_id, 'map_marker_award', true ),
        'location'     => get_post_meta( $post_id, 'map_marker_location_name', true ),
        'x'            => (float) get_post_meta( $post_id, 'map_marker_x', true ),
        'y'            => (float) get_post_meta( $post_id, 'map_marker_y', true ),
        'edit_link'    => get_edit_post_link( $post_id, '' ),
        'can_edit'     => $can_edit_map && current_user_can( 'edit_post', $post_id ),
      );
    }

    wp_reset_postdata();
  }

  return $markers;
}

function cvipi_get_map_marker_counts() {
  $counts = array();

  foreach ( cvipi_get_map_fiscal_years() as $year => $year_data ) {
    $query = new WP_Query( cvipi_get_map_marker_query_args( $year ) );
    $counts[ $year ] = (int) $query->found_posts;
    wp_reset_postdata();
  }

  return $counts;
}

function cvipi_render_map_marker_filters() {
  $fiscal_years = cvipi_get_map_fiscal_years();
  $counts       = cvipi_get_map_marker_counts();
  ob_start();
  ?>
  <div class="map-filters" data-map-filters>
    <button class="map-filters__button is-active" type="button" data-map-filter="all">
      <span class="map-filters__dot"></span>
      <span>All</span>
      <strong><?php echo esc_html( array_sum( $counts ) ); ?></strong>
    </button>
    <?php foreach ( $fiscal_years as $year => $year_data ) : ?>
      <button class="map-filters__button" type="button" data-map-filter="<?php echo esc_attr( $year ); ?>" style="--marker-color: <?php echo esc_attr( $year_data['color'] ); ?>;">
        <span class="map-filters__dot"></span>
        <span><?php echo esc_html( $year_data['label'] ); ?></span>
        <strong><?php echo esc_html( isset( $counts[ $year ] ) ? $counts[ $year ] : 0 ); ?></strong>
      </button>
    <?php endforeach; ?>
  </div>
  <?php
  return ob_get_clean();
}

function cvipi_render_marker_button( $marker ) {
  $x = $marker['x'] ? $marker['x'] : 50;
  $y = $marker['y'] ? $marker['y'] : 50;
  ob_start();
  ?>
  <button
    class="cvipi-map__marker"
    type="button"
    style="--marker-color: <?php echo esc_attr( $marker['color'] ); ?>; left: <?php echo esc_attr( $x ); ?>%; top: <?php echo esc_attr( $y ); ?>%;"
    data-marker-id="<?php echo esc_attr( $marker['id'] ); ?>"
    data-marker-title="<?php echo esc_attr( $marker['title'] ); ?>"
    data-marker-content="<?php echo esc_attr( $marker['content'] ); ?>"
    data-marker-year="<?php echo esc_attr( $marker['year_label'] ); ?>"
    data-marker-award="<?php echo esc_attr( $marker['award'] ); ?>"
    data-marker-location="<?php echo esc_attr( $marker['location'] ); ?>"
    data-can-edit="<?php echo $marker['can_edit'] ? 'true' : 'false'; ?>"
    aria-controls="cvipi-map-popup"
    aria-expanded="false"
    aria-label="<?php echo esc_attr( sprintf( 'View %s map marker', $marker['title'] ) ); ?>"
  >
    <span class="cvipi-map__marker-core"></span>
  </button>
  <?php
  return ob_get_clean();
}

function cvipi_render_map_markers( $fiscal_year = '' ) {
  $html = '';

  foreach ( cvipi_get_map_marker_data( $fiscal_year ) as $marker ) {
    $html .= cvipi_render_marker_button( $marker );
  }

  if ( ! $html ) {
    $html = '<p class="cvipi-map__empty">No map markers found for this fiscal year.</p>';
  }

  return $html;
}

function cvipi_render_marker_map() {
  $map_path = get_template_directory() . '/assets/img/map.svg';
  $map_svg  = file_exists( $map_path ) ? file_get_contents( $map_path ) : '';
  $can_edit_map = cvipi_can_edit_map_markers();
  ob_start();
  ?>
  <div class="we-serve__map cvipi-map" data-cvipi-map data-can-edit="<?php echo $can_edit_map ? 'true' : 'false'; ?>">
    <?php if ( $can_edit_map ) : ?>
      <p class="cvipi-map__editor-note">Drag a marker to update its saved map position.</p>
    <?php endif; ?>
    <p id="cvipi-map-instructions" class="sr-only">Use plus and minus keys to zoom the map, arrow keys to pan while zoomed, and Tab to move through map locations.</p>
    <div class="cvipi-map__viewport" data-map-viewport tabindex="0" aria-label="Interactive CVIPI map" aria-describedby="cvipi-map-instructions">
      <div class="cvipi-map__stage" data-map-stage>
        <div class="cvipi-map__base" aria-hidden="true">
          <?php echo $map_svg; ?>
        </div>
        <div class="cvipi-map__markers" data-map-markers>
          <?php echo cvipi_render_map_markers(); ?>
        </div>
      </div>
    </div>
    <article id="cvipi-map-popup" class="cvipi-map__popup" data-map-popup hidden tabindex="-1" role="dialog" aria-modal="false" aria-live="polite" aria-atomic="true" aria-labelledby="cvipi-map-popup-title">
      <button class="cvipi-map__popup-close" type="button" data-map-popup-close aria-label="Close marker details">&times;</button>
      <h3 id="cvipi-map-popup-title" class="cvipi-map__popup-title" data-map-popup-title></h3>
      <p class="cvipi-map__popup-content" data-map-popup-content></p>
      <dl class="cvipi-map__popup-details">
        <div class="cvipi-map__popup-detail">
          <dt class="cvipi-map__popup-term">Fiscal Year</dt>
          <dd class="cvipi-map__popup-value" data-map-popup-year></dd>
        </div>
        <div class="cvipi-map__popup-detail">
          <dt class="cvipi-map__popup-term">Award</dt>
          <dd class="cvipi-map__popup-value cvipi-map__popup-value--award" data-map-popup-award></dd>
        </div>
      </dl>
      <p class="cvipi-map__popup-location">
        <?php echo svg_icon( 'cvipi-map__popup-location-icon', 'location-2' ); ?>
        <span class="cvipi-map__popup-location-text" data-map-popup-location></span>
      </p>
    </article>
    <div class="cvipi-map__controls" aria-label="Map zoom controls">
      <button type="button" data-map-zoom-in aria-label="Zoom in">+</button>
      <button type="button" data-map-zoom-out aria-label="Zoom out">-</button>
      <button type="button" data-map-reset aria-label="Reset map">Reset</button>
    </div>
    <p class="cvipi-map__preview">Hover to Preview</p>

  </div>
  <?php
  return ob_get_clean();
}

function cvipi_ajax_filter_map_markers() {
  check_ajax_referer( 'cvipi_ajax_nonce', 'nonce' );

  $fiscal_year = isset( $_POST['fiscal_year'] ) ? sanitize_text_field( wp_unslash( $_POST['fiscal_year'] ) ) : '';

  wp_send_json_success(
    array(
      'html' => cvipi_render_map_markers( $fiscal_year ),
    )
  );
}
add_action( 'wp_ajax_cvipi_filter_map_markers', 'cvipi_ajax_filter_map_markers' );
add_action( 'wp_ajax_nopriv_cvipi_filter_map_markers', 'cvipi_ajax_filter_map_markers' );

function cvipi_ajax_update_map_marker_position() {
  check_ajax_referer( 'cvipi_ajax_nonce', 'nonce' );

  $post_id = isset( $_POST['marker_id'] ) ? absint( $_POST['marker_id'] ) : 0;

  if ( ! $post_id || 'map_marker' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
    wp_send_json_error( array( 'message' => 'You cannot edit this marker.' ), 403 );
  }

  $x = isset( $_POST['x'] ) ? min( 100, max( 0, (float) $_POST['x'] ) ) : 50;
  $y = isset( $_POST['y'] ) ? min( 100, max( 0, (float) $_POST['y'] ) ) : 50;

  update_post_meta( $post_id, 'map_marker_x', $x );
  update_post_meta( $post_id, 'map_marker_y', $y );

  wp_send_json_success(
    array(
      'x' => $x,
      'y' => $y,
    )
  );
}
add_action( 'wp_ajax_cvipi_update_map_marker_position', 'cvipi_ajax_update_map_marker_position' );
// .CVIPI map marker helpers.

//* Event field and taxonomy helpers.
function cvipi_get_event_field( $field_name, $post_id = null ) {
  $post_id = $post_id ? $post_id : get_the_ID();

  if ( function_exists( 'get_field' ) ) {
    $field_value = get_field( $field_name, $post_id );

    if ( '' !== $field_value && null !== $field_value ) {
      return $field_value;
    }
  }

  return get_post_meta( $post_id, $field_name, true );
}

function cvipi_get_event_timestamp( $post_id = null ) {
  $event_date = cvipi_get_event_field( 'event_date', $post_id );
  $event_time = cvipi_get_event_field( 'event_time', $post_id );

  if ( ! $event_date ) {
    return 0;
  }

  if ( is_numeric( $event_date ) && 8 === strlen( (string) $event_date ) ) {
    $event_date = substr( $event_date, 0, 4 ) . '-' . substr( $event_date, 4, 2 ) . '-' . substr( $event_date, 6, 2 );
  }

  $timestamp = strtotime( trim( $event_date . ' ' . $event_time ) );

  return $timestamp ? $timestamp : 0;
}

function cvipi_get_event_status( $post_id = null ) {
  $event_timestamp = cvipi_get_event_timestamp( $post_id );

  if ( ! $event_timestamp ) {
    return 'past';
  }

  return $event_timestamp >= current_time( 'timestamp' ) ? 'upcoming' : 'past';
}

function cvipi_get_event_datetime_attr( $post_id = null ) {
  $event_timestamp = cvipi_get_event_timestamp( $post_id );

  return $event_timestamp ? date( 'c', $event_timestamp ) : '';
}

function cvipi_get_event_date_label( $post_id = null, $include_prefix = true ) {
  $event_timestamp = cvipi_get_event_timestamp( $post_id );

  if ( ! $event_timestamp ) {
    return '';
  }

  $event_time     = cvipi_get_event_field( 'event_time', $post_id );
  $event_timezone = cvipi_get_event_field( 'event_timezone', $post_id );
  $date_label     = date_i18n( 'F j, Y', $event_timestamp );
  $time_label     = $event_time ? date_i18n( 'g:i A', $event_timestamp ) : '';
  $parts          = array();

  if ( $include_prefix ) {
    $parts[] = 'DATE ' . $date_label;
  } else {
    $parts[] = $date_label;
  }

  if ( $time_label ) {
    $parts[] = trim( $time_label . ' ' . $event_timezone );
  }

  return implode( ' · ', $parts );
}

function cvipi_get_event_short_date_parts( $post_id = null ) {
  $event_timestamp = cvipi_get_event_timestamp( $post_id );

  if ( ! $event_timestamp ) {
    return array(
      'month' => '',
      'day'   => '',
    );
  }

  return array(
    'month' => date_i18n( 'M', $event_timestamp ),
    'day'   => date_i18n( 'j', $event_timestamp ),
  );
}

function cvipi_get_filtered_events_url( $args = array() ) {
  $events_page = get_page_by_path( 'events' );
  $events_url  = $events_page ? get_permalink( $events_page ) : home_url( '/events/' );
  $query_args  = array_filter(
    $args,
    function( $value ) {
      return '' !== $value && null !== $value;
    }
  );

  return $query_args ? add_query_arg( $query_args, $events_url ) : $events_url;
}

function cvipi_get_event_type_terms() {
  return get_terms(
    array(
      'taxonomy'   => 'event_type',
      'hide_empty' => false,
      'orderby'    => 'name',
      'order'      => 'ASC',
    )
  );
}

function cvipi_get_event_topic_terms() {
  return get_terms(
    array(
      'taxonomy'   => 'event_topic',
      'hide_empty' => false,
      'orderby'    => 'name',
      'order'      => 'ASC',
    )
  );
}

function cvipi_get_event_card_terms( $post_id = null ) {
  $post_id = $post_id ? $post_id : get_the_ID();
  $terms   = array();

  $event_status = cvipi_get_event_status( $post_id );
  $terms[]      = array(
    'name' => 'upcoming' === $event_status ? 'Upcoming' : 'Past / Archived',
    'url'  => cvipi_get_filtered_events_url( array( 'event_status' => $event_status ) ),
  );

  $event_types = get_the_terms( $post_id, 'event_type' );

  if ( ! is_wp_error( $event_types ) && ! empty( $event_types ) ) {
    foreach ( $event_types as $event_type ) {
      $terms[] = array(
        'name' => $event_type->name,
        'url'  => cvipi_get_filtered_events_url( array( 'event_type' => $event_type->slug ) ),
      );
    }
  }

  return $terms;
}

function cvipi_get_event_query_args( $filters = array() ) {
  $event_search = ! empty( $filters['event_search'] ) ? sanitize_text_field( $filters['event_search'] ) : '';
  $event_type   = ! empty( $filters['event_type'] ) ? sanitize_title( $filters['event_type'] ) : '';
  $event_topic  = ! empty( $filters['event_topic'] ) ? sanitize_title( $filters['event_topic'] ) : '';
  $event_status = ! empty( $filters['event_status'] ) ? sanitize_key( $filters['event_status'] ) : '';
  $today_ymd    = current_time( 'Ymd' );

  if ( ! in_array( $event_status, array( 'upcoming', 'past' ), true ) ) {
    $event_status = '';
  }

  $query_args = array(
    'post_type'           => 'event',
    'posts_per_page'      => -1,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'meta_key'            => 'event_date',
    'orderby'             => 'meta_value_num',
    'order'               => 'DESC',
  );

  if ( $event_search ) {
    $query_args['s'] = $event_search;
  }

  $tax_query = array();

  if ( $event_type ) {
    $tax_query[] = array(
      'taxonomy' => 'event_type',
      'field'    => 'slug',
      'terms'    => $event_type,
    );
  }

  if ( $event_topic ) {
    $tax_query[] = array(
      'taxonomy' => 'event_topic',
      'field'    => 'slug',
      'terms'    => $event_topic,
    );
  }

  if ( count( $tax_query ) > 1 ) {
    $tax_query['relation'] = 'AND';
  }

  if ( ! empty( $tax_query ) ) {
    $query_args['tax_query'] = $tax_query;
  }

  if ( $event_status ) {
    $query_args['meta_query'] = array(
      array(
        'key'     => 'event_date',
        'value'   => $today_ymd,
        'compare' => 'upcoming' === $event_status ? '>=' : '<',
        'type'    => 'NUMERIC',
      ),
    );

    if ( 'upcoming' === $event_status ) {
      $query_args['order'] = 'ASC';
    }
  }

  return $query_args;
}

function cvipi_render_event_card( $post_id = null, $hidden = false ) {
  $post_id             = $post_id ? $post_id : get_the_ID();
  $event_terms         = cvipi_get_event_card_terms( $post_id );
  $event_location      = cvipi_get_event_field( 'event_location', $post_id );
  $event_recording_url = cvipi_get_event_field( 'event_recording_url', $post_id );
  $event_cta_url       = cvipi_get_event_field( 'event_cta_url', $post_id );
  $event_duration      = cvipi_get_event_field( 'event_duration', $post_id );
  $event_action_url    = get_permalink( $post_id );
  $event_action_label  = $event_recording_url ? 'View Recording' : 'View Event';
  $event_link_target   = '';
  $event_card_classes  = 'events-page__event-card' . ( $event_recording_url ? ' events-page__event-card--recording' : '' );

  ob_start();
  ?>
  <article class="<?php echo esc_attr( $event_card_classes ); ?>" <?php echo $hidden ? 'hidden' : ''; ?>>
    <a href="<?php echo esc_url( $event_action_url ); ?>" class="events-page__event-link" aria-label="<?php echo esc_attr( $event_action_label . ': ' . get_the_title( $post_id ) ); ?>" <?php echo $event_link_target ? 'target="' . esc_attr( $event_link_target ) . '" rel="noopener noreferrer"' : ''; ?>>
      <?php if ( $event_recording_url ) : ?>
        <span class="events-page__video" aria-hidden="true">
          <?php if ( has_post_thumbnail( $post_id ) ) : ?>
            <?php echo get_the_post_thumbnail( $post_id, 'regular', array( 'class' => 'events-page__video-img' ) ); ?>
          <?php endif; ?>
          <span class="events-page__play"><?php echo svg_icon( 'events-page__play-icon', 'play1' ); ?></span>
          <?php if ( $event_duration ) : ?>
            <span class="events-page__duration"><?php echo esc_html( $event_duration ); ?></span>
          <?php endif; ?>
        </span>
      <?php endif; ?>

      <span class="events-page__event-body">
        <?php if ( ! empty( $event_terms ) ) : ?>
          <span class="events-page__meta-pills" aria-label="Event categories">
            <?php foreach ( $event_terms as $event_term ) : ?>
              <span><?php echo esc_html( $event_term['name'] ); ?></span>
            <?php endforeach; ?>
          </span>
        <?php endif; ?>

        <span class="events-page__event-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
        <span class="events-page__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 20 ) ); ?></span>

        <span class="events-page__details">
          <?php if ( cvipi_get_event_date_label( $post_id, false ) ) : ?>
            <time datetime="<?php echo esc_attr( cvipi_get_event_datetime_attr( $post_id ) ); ?>"><?php echo esc_html( cvipi_get_event_date_label( $post_id, false ) ); ?></time>
          <?php endif; ?>
          <?php if ( $event_location ) : ?>
            <span><?php echo esc_html( $event_location ); ?></span>
          <?php endif; ?>
        </span>

        <span class="events-page__text-link">
          <?php echo esc_html( $event_action_label ); ?> <?php echo svg_icon( 'events-page__text-link-icon', 'arrow-right' ); ?>
        </span>
      </span>
    </a>
  </article>
  <?php

  return ob_get_clean();
}

function cvipi_ajax_filter_events() {
  check_ajax_referer( 'cvipi_ajax_nonce', 'nonce' );

  $filters = array(
    'event_search' => isset( $_POST['event_search'] ) ? sanitize_text_field( wp_unslash( $_POST['event_search'] ) ) : '',
    'event_type'   => isset( $_POST['event_type'] ) ? sanitize_title( wp_unslash( $_POST['event_type'] ) ) : '',
    'event_topic'  => isset( $_POST['event_topic'] ) ? sanitize_title( wp_unslash( $_POST['event_topic'] ) ) : '',
    'event_status' => isset( $_POST['event_status'] ) ? sanitize_key( wp_unslash( $_POST['event_status'] ) ) : '',
  );

  $events_query = new WP_Query( cvipi_get_event_query_args( $filters ) );
  $html         = '';

  if ( $events_query->have_posts() ) {
    while ( $events_query->have_posts() ) {
      $events_query->the_post();
      $html .= cvipi_render_event_card();
    }

    wp_reset_postdata();
  } else {
    $html = '<p class="events-page__empty">No events matched your filters.</p>';
  }

  wp_send_json_success(
    array(
      'html'  => $html,
      'count' => (int) $events_query->found_posts,
      'label' => sprintf(
        _n( '%s event found', '%s events found', $events_query->found_posts, 'cvipi' ),
        number_format_i18n( $events_query->found_posts )
      ),
    )
  );
}
add_action( 'wp_ajax_cvipi_filter_events', 'cvipi_ajax_filter_events' );
add_action( 'wp_ajax_nopriv_cvipi_filter_events', 'cvipi_ajax_filter_events' );
// .Event field and taxonomy helpers.

function cvipi_get_posts_header_defaults( $page_id = null ) {
  $page_id = $page_id ? $page_id : get_queried_object_id();
  $slug    = $page_id ? get_post_field( 'post_name', $page_id ) : '';
  $defaults = array(
    'success-stories' => array(
      'eyebrow'     => 'Community Impact',
      'title'       => 'Success <em>Stories</em>',
      'description' => 'Explore stories from partner organizations and programs working to transform community safety.',
    ),
    'events' => array(
      'eyebrow'     => 'Webinars, Trainings & Peer Learning',
      'title'       => 'Events <em>&amp; Webinars</em>',
      'description' => 'Join CVIPI events, trainings, and recordings designed to support community violence intervention practitioners.',
    ),
    'resources' => array(
      'eyebrow'     => 'Resource Library',
      'title'       => 'Resources',
      'description' => 'Browse tools, research, recordings, and practical guidance for community violence intervention work.',
    ),
  );

  return isset( $defaults[ $slug ] ) ? $defaults[ $slug ] : array(
    'eyebrow'     => get_bloginfo( 'name' ),
    'title'       => $page_id ? get_the_title( $page_id ) : '',
    'description' => '',
  );
}

function cvipi_get_posts_header_data( $page_id = null ) {
  $page_id  = $page_id ? $page_id : get_queried_object_id();
  $defaults = cvipi_get_posts_header_defaults( $page_id );
  $data     = $defaults;

  if ( function_exists( 'get_field' ) && $page_id ) {
    $eyebrow     = get_field( 'posts_header_eyebrow', $page_id );
    $title       = get_field( 'posts_header_title', $page_id );
    $description = get_field( 'posts_header_description', $page_id );
    $image       = get_field( 'posts_header_image', $page_id );
    $video       = get_field( 'posts_header_video', $page_id );

    if ( $eyebrow ) {
      $data['eyebrow'] = $eyebrow;
    }

    if ( $title ) {
      $data['title'] = $title;
    }

    if ( $description ) {
      $data['description'] = $description;
    }

    if ( $image ) {
      $data['image'] = cvipi_get_acf_media_url( $image );
    }

    if ( $video ) {
      $data['video'] = cvipi_get_acf_media_url( $video );
    }
  }

  if ( empty( $data['description'] ) && $page_id ) {
    $page_excerpt = get_the_excerpt( $page_id );
    $page_content = get_post_field( 'post_content', $page_id );

    if ( $page_excerpt ) {
      $data['description'] = $page_excerpt;
    } elseif ( $page_content ) {
      $data['description'] = wp_trim_words( wp_strip_all_tags( $page_content ), 28 );
    }
  }

  if ( empty( $data['image'] ) ) {
    $data['image'] = get_template_directory_uri() . '/assets/img/banner-img-1.webp';
  }

  return $data;
}

function cvipi_get_acf_media_url( $media ) {
  if ( is_array( $media ) && ! empty( $media['url'] ) ) {
    return $media['url'];
  }

  if ( is_numeric( $media ) ) {
    return wp_get_attachment_url( (int) $media );
  }

  return is_string( $media ) ? $media : '';
}

function cvipi_get_home_banner_page_id() {
  $page_id = get_queried_object_id();

  if ( $page_id ) {
    return $page_id;
  }

  $front_page_id = (int) get_option( 'page_on_front' );

  if ( $front_page_id ) {
    return $front_page_id;
  }

  $home_page = get_page_by_path( 'home' );

  return $home_page ? (int) $home_page->ID : 0;
}

function cvipi_get_home_banner_data( $page_id = null ) {
  $page_id = $page_id ? $page_id : cvipi_get_home_banner_page_id();
  $data    = array(
    'eyebrow'     => 'Community Violence Intervention',
    'title'       => wp_specialchars_decode( get_option( 'blogdescription' ), ENT_QUOTES ),
    'description' => "The National Community Violence Intervention & Prevention Initiative (CVIPI) was created to be a source of information and practical resources on community violence intervention.\n\nThis nationwide initiative brings together community residents, local government, law enforcement, hospitals, victim service providers, community-based organizations (CBOs), researchers, and other partners to help prevent and reduce violent crime to make our communities safer for generations to come.",
  );

  if ( function_exists( 'get_field' ) && $page_id ) {
    $eyebrow     = get_field( 'posts_header_eyebrow', $page_id );
    $title       = get_field( 'posts_header_title', $page_id );
    $description = get_field( 'posts_header_description', $page_id );

    if ( $eyebrow ) {
      $data['eyebrow'] = $eyebrow;
    }

    if ( $title ) {
      $data['title'] = $title;
    }

    if ( $description ) {
      $data['description'] = $description;
    }
  }

  return $data;
}

function cvipi_register_posts_header_acf_fields() {
  if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
  }

  acf_add_local_field_group(
    array(
      'key'      => 'group_cvipi_posts_header',
      'title'    => 'Landing Page Header',
      'fields'   => array(
        array(
          'key'   => 'field_cvipi_posts_header_eyebrow',
          'label' => 'Eyebrow',
          'name'  => 'posts_header_eyebrow',
          'type'  => 'text',
        ),
        array(
          'key'          => 'field_cvipi_posts_header_title',
          'label'        => 'Title',
          'name'         => 'posts_header_title',
          'type'         => 'textarea',
          'instructions' => 'Simple emphasis markup is allowed, for example: Events <em>&amp; Webinars</em>.',
          'rows'         => 2,
        ),
        array(
          'key'   => 'field_cvipi_posts_header_description',
          'label' => 'Description',
          'name'  => 'posts_header_description',
          'type'  => 'textarea',
          'rows'  => 3,
        ),
        array(
          'key'       => 'field_cvipi_posts_header_media_tab',
          'label'     => 'Background Media',
          'name'      => '',
          'type'      => 'tab',
          'placement' => 'top',
        ),
        array(
          'key'           => 'field_cvipi_posts_header_video',
          'label'         => 'Background Video',
          'name'          => 'posts_header_video',
          'type'          => 'file',
          'instructions'  => 'Optional looping background video. Upload one MP4/WebM/MOV file.',
          'return_format' => 'array',
          'library'       => 'all',
          'mime_types'    => 'mp4,webm,mov',
        ),
        array(
          'key'           => 'field_cvipi_posts_header_image',
          'label'         => 'Fallback Image',
          'name'          => 'posts_header_image',
          'type'          => 'image',
          'instructions'  => 'Used as the fallback/poster image when the video is loading or unavailable.',
          'return_format' => 'array',
          'preview_size'  => 'medium',
        ),
      ),
      'location' => array(
        array(
          array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'page',
          ),
          array(
            'param'    => 'page_type',
            'operator' => '!=',
            'value'    => 'front_page',
          ),
        ),
      ),
    )
  );
}
add_action( 'acf/init', 'cvipi_register_posts_header_acf_fields' );

/**
 * Prevent core footnotes meta from blocking page/editor saves.
 *
 * WordPress registers the `footnotes` post meta for REST saves on post types
 * that support the editor, custom fields, and revisions. This site does not use
 * the core Footnotes block, and a failed empty footnotes meta update can cause
 * the block editor to report "Updating failed" before ACF media fields save.
 */
function cvipi_unregister_core_footnotes_meta() {
  if ( ! function_exists( 'unregister_post_meta' ) ) {
    return;
  }

  $post_types = array( 'page', 'post', 'resource', 'event', 'success_story' );

  foreach ( $post_types as $post_type ) {
    unregister_post_meta( $post_type, 'footnotes' );
  }
}
add_action( 'init', 'cvipi_unregister_core_footnotes_meta', 21 );

function cvipi_register_home_banner_acf_fields() {
  if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
  }

  $home_banner_locations = array(
    array(
      array(
        'param'    => 'page_type',
        'operator' => '==',
        'value'    => 'front_page',
      ),
    ),
  );
  $home_page = get_page_by_path( 'home' );

  if ( $home_page ) {
    $home_banner_locations[] = array(
      array(
        'param'    => 'page',
        'operator' => '==',
        'value'    => (string) $home_page->ID,
      ),
    );
  }

  acf_add_local_field_group(
    array(
      'key'      => 'group_cvipi_home_banner',
      'title'    => 'Homepage Banner',
      'fields'   => array(
        array(
          'key'   => 'field_cvipi_home_banner_eyebrow',
          'label' => 'Eyebrow',
          'name'  => 'posts_header_eyebrow',
          'type'  => 'text',
        ),
        array(
          'key'          => 'field_cvipi_home_banner_title',
          'label'        => 'Title',
          'name'         => 'posts_header_title',
          'type'         => 'textarea',
          'instructions' => 'Simple emphasis markup is allowed, for example: Your Resource for <em>Community-Led Safety</em>.',
          'rows'         => 2,
        ),
        array(
          'key'   => 'field_cvipi_home_banner_description',
          'label' => 'Description',
          'name'  => 'posts_header_description',
          'type'  => 'textarea',
          'rows'  => 5,
        ),
        array(
          'key'          => 'field_6a0df09592f1e',
          'label'        => 'Background Videos',
          'name'         => 'banner_videos',
          'type'         => 'repeater',
          'instructions' => 'Add multiple videos to cycle through them on the homepage banner.',
          'layout'       => 'block',
          'button_label' => 'Add Video',
          'sub_fields'   => array(
            array(
              'key'           => 'field_6a0df0fa92f1f',
              'label'         => 'Video',
              'name'          => 'video',
              'type'          => 'file',
              'instructions'  => 'Upload one MP4/WebM/MOV file.',
              'return_format' => 'array',
              'library'       => 'all',
              'mime_types'    => 'mp4,webm,mov',
            ),
            array(
              'key'           => 'field_6a0df15b92f20',
              'label'         => 'Poster Image',
              'name'          => 'poster_image',
              'type'          => 'image',
              'instructions'  => 'Optional fallback image shown while the video loads.',
              'return_format' => 'array',
              'preview_size'  => 'medium',
            ),
          ),
        ),
      ),
      'location' => $home_banner_locations,
    )
  );
}
add_action( 'acf/init', 'cvipi_register_home_banner_acf_fields' );

function cvipi_register_event_taxonomies() {
  register_taxonomy(
    'event_type',
    array( 'event' ),
    array(
      'show_in_rest'      => true,
      'hierarchical'      => true,
      'labels'            => array(
        'name'          => 'Event Types',
        'singular_name' => 'Event Type',
        'search_items'  => 'Search Event Types',
        'all_items'     => 'All Event Types',
        'edit_item'     => 'Edit Event Type',
        'update_item'   => 'Update Event Type',
        'add_new_item'  => 'Add New Event Type',
        'new_item_name' => 'New Event Type',
        'menu_name'     => 'Event Types',
      ),
      'rewrite'           => array( 'slug' => 'event-type' ),
      'show_admin_column' => true,
    )
  );

  register_taxonomy(
    'event_topic',
    array( 'event' ),
    array(
      'show_in_rest'      => true,
      'hierarchical'      => true,
      'labels'            => array(
        'name'          => 'Event Topics',
        'singular_name' => 'Event Topic',
        'search_items'  => 'Search Event Topics',
        'all_items'     => 'All Event Topics',
        'edit_item'     => 'Edit Event Topic',
        'update_item'   => 'Update Event Topic',
        'add_new_item'  => 'Add New Event Topic',
        'new_item_name' => 'New Event Topic',
        'menu_name'     => 'Event Topics',
      ),
      'rewrite'           => array( 'slug' => 'event-topic' ),
      'show_admin_column' => true,
    )
  );
}
add_action( 'init', 'cvipi_register_event_taxonomies' );

function cvipi_create_default_event_types() {
  $default_event_types = array(
    'Webinars',
    'Peer Learning',
    'Conferences',
    'Panels',
  );

  foreach ( $default_event_types as $default_event_type ) {
    if ( ! term_exists( $default_event_type, 'event_type' ) ) {
      wp_insert_term( $default_event_type, 'event_type' );
    }
  }
}
add_action( 'init', 'cvipi_create_default_event_types', 20 );

function cvipi_register_event_acf_fields() {
  if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
  }

  acf_add_local_field_group(
    array(
      'key'      => 'group_cvipi_event_details',
      'title'    => 'Event Details',
      'fields'   => array(
        array(
          'key'           => 'field_cvipi_event_date',
          'label'         => 'Event Date',
          'name'          => 'event_date',
          'type'          => 'date_picker',
          'required'      => 1,
          'display_format'=> 'F j, Y',
          'return_format' => 'Y-m-d',
        ),
        array(
          'key'           => 'field_cvipi_event_time',
          'label'         => 'Event Time',
          'name'          => 'event_time',
          'type'          => 'time_picker',
          'display_format'=> 'g:i A',
          'return_format' => 'H:i',
        ),
        array(
          'key'           => 'field_cvipi_event_timezone',
          'label'         => 'Timezone Label',
          'name'          => 'event_timezone',
          'type'          => 'text',
          'default_value' => 'ET',
        ),
        array(
          'key'   => 'field_cvipi_event_location',
          'label' => 'Location / Platform',
          'name'  => 'event_location',
          'type'  => 'text',
        ),
        array(
          'key'   => 'field_cvipi_event_presented_by',
          'label' => 'Presented By',
          'name'  => 'event_presented_by',
          'type'  => 'text',
        ),
        array(
          'key'   => 'field_cvipi_event_cta_url',
          'label' => 'RSVP URL',
          'name'  => 'event_cta_url',
          'type'  => 'url',
        ),
        array(
          'key'           => 'field_cvipi_event_cta_label',
          'label'         => 'RSVP Label',
          'name'          => 'event_cta_label',
          'type'          => 'text',
          'default_value' => 'RSVP',
        ),
        array(
          'key'   => 'field_cvipi_event_recording_url',
          'label' => 'Recording URL',
          'name'  => 'event_recording_url',
          'type'  => 'url',
        ),
        array(
          'key'   => 'field_cvipi_event_duration',
          'label' => 'Recording Duration',
          'name'  => 'event_duration',
          'type'  => 'text',
        ),
      ),
      'location' => array(
        array(
          array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'event',
          ),
        ),
      ),
    )
  );
}
add_action( 'acf/init', 'cvipi_register_event_acf_fields' );




 

// Add Footer Text Setting to Customizer
function cvipi_customize_register($wp_customize) {
  // Add Footer Section
  $wp_customize->add_section('cvipi_footer_section', array(
      'title'       => __('Footer Settings', 'cvipi'),
      'priority'    => 200,
      'description' => 'Customize the footer text',
  ));

  // Add Footer Text Setting
  $wp_customize->add_setting('cvipi_footer_text', array(
      'default'           => '', // Default is blank; admin must provide text
      'sanitize_callback' => 'wp_kses_post', // Allows safe HTML for formatting
      'transport'         => 'refresh',
  ));

  // Add Footer Text Control
  $wp_customize->add_control('cvipi_footer_text_control', array(
      'label'       => __('Footer Text', 'cvipi'),
      'section'     => 'cvipi_footer_section',
      'settings'    => 'cvipi_footer_text',
      'type'        => 'textarea', // Allows for longer text
  ));
}
add_action('customize_register', 'cvipi_customize_register');



 //* 8.  Enable custom post types
function custom_post_types() {

// Events Post Type
register_post_type('event', array(
  'show_in_rest' => true,
  'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
  'rewrite' => array('slug' => 'event'),
  'taxonomies'  => array( 'event_type', 'event_topic' ),
  'public' => true,
  'labels' => array(
    'name' => 'Events',
    'add_new_item' => 'Add New Event',
    'edit_item' => 'Edit Event',
    'all_items' => 'All Events',
    'singular_name' => 'Event'
  ),
  'menu_position' => 6,
  'menu_icon' => 'dashicons-calendar-alt'
));

// Success Stories Post Type
register_post_type('success_story', array(
  'show_in_rest' => true,
  'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
  'rewrite' => array('slug' => 'success-stories'),
  'taxonomies'  => array( 'category', 'post_tag' ),
  'public' => true,
  'labels' => array(
    'name' => 'Success Stories',
    'add_new_item' => 'Add New Success Story',
    'edit_item' => 'Edit Success Story',
    'all_items' => 'All Success Stories',
    'singular_name' => 'Success Story'
  ),
  'menu_position' => 7,
  'menu_icon' => 'dashicons-star-filled'
));

// FAQs Post Type
register_post_type('faq', array(
  'show_in_rest' => true,
  'supports' => array('title', 'editor', 'page-attributes'),
  'rewrite' => array('slug' => 'faqs'),
  'public' => true,
  'labels' => array(
    'name' => 'FAQs',
    'add_new_item' => 'Add New FAQ',
    'edit_item' => 'Edit FAQ',
    'all_items' => 'All FAQs',
    'singular_name' => 'FAQ'
  ),
  'menu_position' => 8,
  'menu_icon' => 'dashicons-editor-help'
));

// TA Providers Post Type
register_post_type('ta_provider', array(
  'show_in_rest' => true,
  'supports' => array('title', 'editor', 'page-attributes'),
  'rewrite' => array('slug' => 'ta-providers'),
  'taxonomies'  => array( 'category' ),
  'public' => true,
  'labels' => array(
    'name' => 'TA Providers',
    'add_new_item' => 'Add New TA Provider',
    'edit_item' => 'Edit TA Provider',
    'all_items' => 'All TA Providers',
    'singular_name' => 'TA Provider'
  ),
  'menu_position' => 9,
  'menu_icon' => 'dashicons-groups'
));


}
 
add_action('init', 'custom_post_types');
  // . 8 Enable custom post types

function cvipi_seed_contact_faqs() {
  $seed_version = '2026-06-29-contact-faqs-v3';

  if ( get_option( 'cvipi_contact_faqs_seeded' ) === $seed_version ) {
    return;
  }

  $legacy_faq_titles = array(
    'Who should use the contact form?',
    'How quickly will I receive a response?',
    'Can I request training or technical assistance?',
    'Where can I find CVIPI resources?',
    'Can I share a success story or event?',
  );

  foreach ( $legacy_faq_titles as $legacy_faq_title ) {
    $legacy_faqs = get_posts(
      array(
        'post_type'      => 'faq',
        'post_status'    => 'any',
        'title'          => $legacy_faq_title,
        'posts_per_page' => -1,
        'fields'         => 'ids',
      )
    );

    foreach ( $legacy_faqs as $legacy_faq_id ) {
      wp_delete_post( $legacy_faq_id, true );
    }
  }

  $faqs = array(
    array(
      'title'   => 'What is the Community Violence Intervention and Prevention Initiative?',
      'content' => 'CVIPI is a historic federal investment in community violence intervention programs, administered through the U.S. Department of Justice\'s Office of Justice Programs. The initiative provides funding, training, and technical assistance to community-based organizations, local and state governments, research partners, and capacity-building intermediaries working to reduce violence in the communities most impacted by gun violence.',
    ),
    array(
      'title'   => 'How do I request technical assistance?',
      'content' => 'Use the contact form and include a short description of your organization, location, and the support you are looking for so the CVIPI team can route your request appropriately.',
    ),
    array(
      'title'   => 'Who is eligible for CVIPI funding?',
      'content' => 'Eligibility can vary by funding opportunity. Review the active solicitation details and related guidance from the Bureau of Justice Assistance or other Office of Justice Programs components.',
    ),
    array(
      'title'   => 'What types of programs does CVIPI support?',
      'content' => 'CVIPI supports evidence-informed and community-centered violence intervention strategies, including street outreach, hospital-based violence intervention, place-based approaches, and ecosystem-building work.',
    ),
    array(
      'title'   => 'How can I access CVIPI resources and webinar recordings?',
      'content' => 'Visit the Resources page to browse toolkits, reports, webinar recordings, and other materials designed for community violence intervention practitioners and partners.',
    ),
    array(
      'title'   => 'I\'m a current grantee. Who is my point of contact?',
      'content' => 'Current grantees should use their assigned grant or technical assistance contacts when available. If you are not sure who to reach, send a message through the contact form.',
    ),
    array(
      'title'   => 'How can my organization partner with CVIPI?',
      'content' => 'Share a brief description of your organization, the communities you serve, and the type of partnership you are interested in through the contact form.',
    ),
  );

  foreach ( $faqs as $index => $faq ) {
    $existing_faqs = get_posts(
      array(
        'post_type'      => 'faq',
        'post_status'    => 'any',
        'title'          => $faq['title'],
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
      )
    );

    if ( ! empty( $existing_faqs ) ) {
      $primary_faq = array_shift( $existing_faqs );

      wp_update_post(
        array(
          'ID'           => $primary_faq->ID,
          'post_status'  => 'publish',
          'post_content' => $faq['content'],
          'menu_order'   => $index,
        )
      );

      foreach ( $existing_faqs as $duplicate_faq ) {
        wp_delete_post( $duplicate_faq->ID, true );
      }

      continue;
    }

    wp_insert_post(
      array(
        'post_type'    => 'faq',
        'post_status'  => 'publish',
        'post_title'   => $faq['title'],
        'post_content' => $faq['content'],
        'menu_order'   => $index,
      )
    );
  }

  update_option( 'cvipi_contact_faqs_seeded', $seed_version );
}
add_action( 'init', 'cvipi_seed_contact_faqs', 25 );

function cvipi_seed_contact_page() {
  if ( get_page_by_path( 'contact' ) ) {
    return;
  }

  wp_insert_post(
    array(
      'post_type'    => 'page',
      'post_status'  => 'publish',
      'post_title'   => 'Contact',
      'post_name'    => 'contact',
      'post_content' => '',
    )
  );
}
add_action( 'init', 'cvipi_seed_contact_page', 25 );

function cvipi_maybe_flush_event_rewrite_rules() {
  $rewrite_version = '2026-06-26-event-single-slug';

  if ( get_option( 'cvipi_rewrite_rules_version' ) === $rewrite_version ) {
    return;
  }

  flush_rewrite_rules();
  update_option( 'cvipi_rewrite_rules_version', $rewrite_version );
}
add_action( 'init', 'cvipi_maybe_flush_event_rewrite_rules', 30 );
