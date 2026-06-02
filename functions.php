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

  // Adds async loading to script URLs that include the #asyncload marker.
  add_filter( 'clean_url', 'async_scripts', 11, 1 ); 




// * * --------| Functions in order |-------- *

  // Enqueue the compiled JavaScript bundle and the root WordPress theme stylesheet.
  function theme_enqueue_scripts() {
  wp_enqueue_script( 'Bundled_js', get_template_directory_uri() . '/assets/js/scripts-bundled.js#asyncload', array(), '1.0.0', true );
  wp_localize_script(
    'Bundled_js',
    'cvipiAjax',
    array(
      'ajaxUrl' => admin_url( 'admin-ajax.php' ),
      'nonce'   => wp_create_nonce( 'cvipi_ajax_nonce' ),
    )
  );
  wp_enqueue_style('cvipi_main_styles', get_stylesheet_uri());
  }



  // Convert the #asyncload marker into an async attribute for front-end script tags.
  function async_scripts($url){
  if ( strpos( $url, '#asyncload') === false )
    return $url;
  else if ( is_admin() )
    return str_replace( '#asyncload', '', $url );
  else
    return str_replace( '#asyncload', '', $url )."' async='async";
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
function svg_icon($class, $icon) { ?>
  <svg class="<?php echo $class ?>" aria-hidden="true">
    <use
      xlink:href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/sprite.svg' ); ?>#icon-<?php echo $icon ?>">
    </use>
  </svg>
  <?php } 
  // .Display inline svg icon from sprite sheet with custom class

//* Resource category cards shown on the homepage.
function cvipi_resource_category_cards() {
  return array(
    'archived-events-webinars' => array(
      'icon'      => 'report1',
      'cta_label' => 'Browse recordings',
      'color'     => 'var(--color-dark-blue)',
    ),
    'toolkits' => array(
      'icon'      => 'file1',
      'cta_label' => 'Browse toolkits',
      'color'     => 'var(--color-dark-midnight-teal)',
    ),
    'research-reports' => array(
      'icon'      => 'play1',
      'cta_label' => 'Browse research',
      'color'     => 'var(--color-copper)',
    ),
    'other-resources' => array(
      'icon'      => 'gear',
      'cta_label' => 'Browse resources',
      'color'     => 'var(--color-medium-blue)',
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
    $query_args['date_query'] = array(
      array(
        'year' => $resource_year,
      ),
    );
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

function cvipi_render_resource_card( $post_id = null ) {
  $post_id       = $post_id ? $post_id : get_the_ID();
  $category_card = cvipi_get_resource_category_card_by_post( $post_id );
  $category      = $category_card['term'];
  $category_name = $category ? wp_specialchars_decode( $category->name, ENT_QUOTES ) : 'Resource';
  $published     = get_the_date( 'F Y', $post_id );
  ob_start();
  ?>
  <article class="resources-page__card" style="--resource-accent: <?php echo esc_attr( $category_card['color'] ); ?>;">
    <div class="resources-page__card-type">
      <?php echo svg_icon( 'resources-page__type-icon', $category_card['icon'] ); ?>
      <span><?php echo esc_html( $category_name ); ?></span>
    </div>

    <div class="resources-page__card-body">
      <?php if ( $category ) : ?>
        <p class="resources-page__category"><?php echo esc_html( $category_name ); ?></p>
      <?php endif; ?>

      <h2 class="resources-page__title">
        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="resources-page__title-link">
          <?php echo esc_html( get_the_title( $post_id ) ); ?>
        </a>
      </h2>

      <p class="resources-page__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 22 ) ); ?></p>

      <div class="resources-page__meta">
        <span><?php echo esc_html( $published ); ?></span>
      </div>

      <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="resources-page__cta">
        View Resource <?php echo svg_icon( 'resources-page__cta-icon', 'arrow-right' ); ?>
      </a>
    </div>
  </article>
  <?php
  return ob_get_clean();
}

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
      $data['image'] = is_array( $image ) && isset( $image['url'] ) ? $image['url'] : $image;
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
          'key'           => 'field_cvipi_posts_header_image',
          'label'         => 'Background Image',
          'name'          => 'posts_header_image',
          'type'          => 'image',
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
        ),
      ),
    )
  );
}
add_action( 'acf/init', 'cvipi_register_posts_header_acf_fields' );

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
  'rewrite' => array('slug' => 'events'),
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
  'taxonomies'  => array( 'category' ),
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
  'menu_position' => 8,
  'menu_icon' => 'dashicons-groups'
));


}
 
  add_action('init', 'custom_post_types');
  // . 8 Enable custom post types
