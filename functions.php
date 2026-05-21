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
  'menu_icon' => 'dashicons-groups'
));


}
 
  add_action('init', 'custom_post_types');
  // . 8 Enable custom post types
