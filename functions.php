<?php
/** 
 * Custom Functions
 *
 * ! What the custom functions do:
 * *    1. Enqueues all styles and scripts
 * *    2. Asynchronously load scripts for speed optimization
 *      
 */

// * * --------| Actions and filters in order |-------- *

  // Action to enque styles and scripts
  add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts' );

  // Asynchronously load scripts
  add_filter( 'clean_url', 'async_scripts', 11, 1 ); 




// * * --------| Functions in order |-------- *

  //Enqueuing styles and scripts
  function theme_enqueue_scripts() {
  wp_enqueue_script( 'Bundled_js', get_template_directory_uri() . '/assets/js/scripts-bundled.js#asyncload', array(), '1.0.0', true );
  wp_enqueue_style('allball_main_styles', get_stylesheet_uri());
  }



  // Asynchronously load scripts
  function async_scripts($url){
  if ( strpos( $url, '#asyncload') === false )
    return $url;
  else if ( is_admin() )
    return str_replace( '#asyncload', '', $url );
  else
    return str_replace( '#asyncload', '', $url )."' async='async";
  }

   //* 3. Activates the ability to add custom logo in customizer
function jameelevans_custom_logo_setup() {
  $defaults = array(
      'height'      => 38,
      'width'       => 38,
      'flex-height' => true,
      'flex-width'  => true,
      'header-text' => array( 'Jameel Evans', 'Wordpress Developer & Consultant' ),
  );
  add_theme_support( 'custom-logo', $defaults );

  //* 4. Enable support for custom sized Post Thumbnails on posts and pages
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
  add_image_size( 'staff-headshot', 304, 350, true);
  add_image_size('pageBanner', 1300, 700, true);
}
add_action( 'after_setup_theme', 'jameelevans_custom_logo_setup' );
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
function allball_login_css() {
  wp_enqueue_style('allball_main_styles', get_stylesheet_uri());
  }
add_action('login_enqueue_scripts', 'allball_login_css');
// .Make css styles available to login screen

//* 5. Replace WP logo with site title name on login screen
function allball_login_title() {
  return get_bloginfo('name');
}
add_filter('login_headertitle', 'allball_login_title');
// .Replace WP logo with site title name on login screen


//* 7. Add theme title to login screen
function ourLoginTitle() {
  return get_bloginfo('name');
}
add_filter('login_headertitle', 'ourLoginTitle');
// .Add theme title to login screen

 //* 7.  Display inline svg icon from sprite sheet with custom class
function svg_icon($class, $icon) { ?>
  <svg class="<?php echo $class ?>" aria-hidden="true">
    <use
      xlink:href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/sprite.svg' ); ?>#icon-<?php echo $icon ?>">
    </use>
  </svg>
  <?php } 
  // .Display inline svg icon from sprite sheet with custom class




 

// Add Footer Text Setting to Customizer
function allball_customize_register($wp_customize) {
  // Add Footer Section
  $wp_customize->add_section('allball_footer_section', array(
      'title'       => __('Footer Settings', 'allball'),
      'priority'    => 200,
      'description' => 'Customize the footer text',
  ));

  // Add Footer Text Setting
  $wp_customize->add_setting('allball_footer_text', array(
      'default'           => '', // Default is blank; admin must provide text
      'sanitize_callback' => 'wp_kses_post', // Allows safe HTML for formatting
      'transport'         => 'refresh',
  ));

  // Add Footer Text Control
  $wp_customize->add_control('allball_footer_text_control', array(
      'label'       => __('Footer Text', 'allball'),
      'section'     => 'allball_footer_section',
      'settings'    => 'allball_footer_text',
      'type'        => 'textarea', // Allows for longer text
  ));
}
add_action('customize_register', 'allball_customize_register');








