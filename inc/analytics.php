<?php
/** Consent-first analytics. Local, staging and signed-in visits never reach GA. */
defined( 'ABSPATH' ) || exit;

function cvipi_analytics_settings() {
  $saved = get_option( 'cvipi_analytics', array() );
  return array_merge( array( 'measurement_id' => '', 'enabled' => '0' ), is_array( $saved ) ? $saved : array() );
}

function cvipi_analytics_sanitize( $input ) {
  $old = cvipi_analytics_settings();
  if ( ! is_array( $input ) ) return $old;
  $id = isset( $input['measurement_id'] ) && is_string( $input['measurement_id'] ) ? strtoupper( trim( $input['measurement_id'] ) ) : '';
  if ( $id && ! preg_match( '/^G-[A-Z0-9]{6,20}$/', $id ) ) {
    add_settings_error( 'cvipi_analytics', 'invalid-id', 'Enter a valid GA4 Measurement ID beginning with G-. Settings were not saved.' );
    return $old;
  }
  return array( 'measurement_id' => $id, 'enabled' => isset( $input['enabled'] ) && '1' === $input['enabled'] && $id ? '1' : '0' );
}

add_action( 'admin_init', function () {
  register_setting( 'cvipi_analytics', 'cvipi_analytics', array( 'sanitize_callback' => 'cvipi_analytics_sanitize' ) );
} );
add_action( 'admin_menu', function () {
  add_options_page( 'Analytics & Consent', 'Analytics & Consent', 'manage_options', 'cvipi-analytics', 'cvipi_analytics_settings_page' );
} );
function cvipi_analytics_settings_page() {
  if ( ! current_user_can( 'manage_options' ) ) return;
  $settings = cvipi_analytics_settings();
  ?>
  <div class="wrap"><h1>Analytics &amp; Consent</h1><?php settings_errors(); ?>
    <p><strong>Local, staging, and signed-in visits: preview only. No Analytics data leaves this site.</strong></p>
    <form action="options.php" method="post">
      <?php settings_fields( 'cvipi_analytics' ); ?>
      <table class="form-table" role="presentation">
        <tr><th scope="row"><label for="cvipi-ga-id">GA4 Measurement ID</label></th><td><input class="regular-text" id="cvipi-ga-id" name="cvipi_analytics[measurement_id]" value="<?php echo esc_attr( $settings['measurement_id'] ); ?>" placeholder="G-XXXXXXXXXX" pattern="G-[A-Za-z0-9]{6,20}"></td></tr>
        <tr><th scope="row">Production tracking</th><td><label><input type="checkbox" name="cvipi_analytics[enabled]" value="1" <?php checked( $settings['enabled'], '1' ); ?>> Enable on cvipi.info after visitor consent</label></td></tr>
      </table>
      <?php submit_button(); ?>
    </form>
    <p>Before production activation: disable GA Enhanced measurement to avoid duplicate events, confirm the privacy notice, and verify consent using the production tag. No advertising features are enabled.</p>
  </div>
  <?php
}

function cvipi_analytics_contact_form_id() {
  if ( function_exists( 'cvipi_contact_config' ) ) return (int) ( cvipi_contact_config()['id'] ?? 0 );
  return (int) get_option( 'cvipi_analytics_contact_form_id', 0 );
}

function cvipi_analytics_config() {
  $settings = cvipi_analytics_settings();
  $home = wp_parse_url( home_url( '/' ) );
  $production = 'production' === wp_get_environment_type() && 'https' === ( $home['scheme'] ?? '' ) && 'cvipi.info' === ( $home['host'] ?? '' ) && '/' === ( $home['path'] ?? '/' );
  $page_type = is_404() ? 'not_found' : ( is_front_page() ? 'home' : ( is_singular() ? get_post_type() : 'archive' ) );
  return array(
    'measurementId' => $settings['measurement_id'],
    'mode' => $production && ! is_user_logged_in() && '1' === $settings['enabled'] && preg_match( '/^G-[A-Z0-9]{6,20}$/', $settings['measurement_id'] ) ? 'live' : 'preview',
    'pageType' => sanitize_key( $page_type ),
    'pageTitle' => 'CVIPI | ' . ( is_404() ? 'Page not found' : ( is_front_page() ? 'Home' : ucwords( str_replace( '_', ' ', $page_type ) ) ) ),
    'contactFormId' => cvipi_analytics_contact_form_id(),
  );
}

add_action( 'wp_enqueue_scripts', function () {
  wp_add_inline_script( 'Bundled_js', 'window.cvipiAnalyticsConfig = ' . wp_json_encode( cvipi_analytics_config(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
  // Mailchimp's own unconditional impression counter is replaced by consented events.
  if ( wp_script_is( 'mailchimp_sf_main_js', 'enqueued' ) ) wp_add_inline_script( 'mailchimp_sf_main_js', 'if (window.mailchimpSF) { delete window.mailchimpSF.analytics_ajax_url; delete window.mailchimpSF.analytics_nonce; }', 'before' );
}, 100 );

add_action( 'wp_footer', function () {
  include get_template_directory() . '/templates/analytics-consent.php';
}, 15 );

// A success marker covers AJAX and normal WPForms confirmations without reading fields.
add_filter( 'wpforms_frontend_confirmation_message', function ( $message, $form ) {
  if ( cvipi_analytics_contact_form_id() && cvipi_analytics_contact_form_id() === (int) ( $form['id'] ?? 0 ) ) $message .= '<span hidden data-analytics-form-success="contact"></span>';
  return $message;
}, 10, 2 );
