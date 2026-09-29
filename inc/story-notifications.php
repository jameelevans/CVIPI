<?php
/** Submission receipts and editor alerts use the site's configured mail transport. */
defined( 'ABSPATH' ) || exit;

function cvipi_story_notification_defaults() {
  return array(
    'submitter_enabled' => '1',
    'submitter_subject' => 'Thank you for your submission to {site_name}',
    'submitter_heading' => 'Thank you for sharing.',
    'submitter_message' => "Thank you for submitting your success story to {site_name}.\n\nOur team has received your submission and will review it before publication. We may contact you if we need more information.\n\nThank you for sharing your community's work.\n\nThe {site_name} team",
    'admin_enabled' => '1',
    'admin_recipients' => get_option( 'admin_email' ),
    'admin_subject' => '[{site_name}] New success story submission',
    'admin_heading' => 'A new story is ready for review.',
    'admin_message' => "A community member has shared a success story with {site_name}.\n\nReview their submission, add the remaining details, and publish when it is ready.",
  );
}

function cvipi_story_notification_settings() {
  $saved = get_option( 'cvipi_story_notifications', array() );
  return array_merge( cvipi_story_notification_defaults(), is_array( $saved ) ? $saved : array() );
}

function cvipi_story_sanitize_notifications( $input ) {
  $old = cvipi_story_notification_settings();
  if ( ! is_array( $input ) ) return $old;
  $clean = array();
  foreach ( array( 'submitter', 'admin' ) as $type ) {
    $clean[$type . '_enabled'] = isset( $input[$type . '_enabled'] ) && '1' === $input[$type . '_enabled'] ? '1' : '0';
    foreach ( array( 'subject' => 180, 'heading' => 100, 'message' => 10000 ) as $field => $limit ) {
      $key = $type . '_' . $field;
      $raw = isset( $input[$key] ) && is_string( $input[$key] ) ? $input[$key] : '';
      $clean[$key] = 'message' === $field ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
      if ( '' === $clean[$key] || mb_strlen( $raw ) > $limit ) {
        add_settings_error( 'cvipi_story_notifications', 'invalid-template', 'Both notifications need a subject (up to 180 characters), heading (up to 100), and message (up to 10,000). Changes were not saved.' );
        return $old;
      }
    }
  }
  $raw = isset( $input['admin_recipients'] ) && is_string( $input['admin_recipients'] ) ? $input['admin_recipients'] : '';
  $recipients = array_values( array_unique( preg_split( '/[\s,;]+/', trim( $raw ), -1, PREG_SPLIT_NO_EMPTY ) ) );
  if ( count( $recipients ) > 10 || ( '1' === $clean['admin_enabled'] && ! $recipients ) || count( array_filter( $recipients, 'is_email' ) ) !== count( $recipients ) ) {
    add_settings_error( 'cvipi_story_notifications', 'invalid-recipients', 'Enter up to 10 valid admin email addresses, separated by commas or new lines. Changes were not saved.' );
    return $old;
  }
  $clean['admin_recipients'] = implode( "\n", $recipients );
  return $clean;
}

add_action( 'admin_init', function () {
  register_setting( 'cvipi_story_notifications', 'cvipi_story_notifications', array( 'sanitize_callback' => 'cvipi_story_sanitize_notifications' ) );
} );
add_action( 'admin_menu', function () {
  add_submenu_page( 'edit.php?post_type=success_story', 'Story Notifications', 'Notifications', 'manage_options', 'cvipi-story-notifications', 'cvipi_story_notifications_page' );
} );

function cvipi_story_notifications_page() {
  if ( ! current_user_can( 'manage_options' ) ) return;
  $settings = cvipi_story_notification_settings();
  ?>
  <div class="wrap">
    <h1>Story Notifications</h1>
    <?php settings_errors(); ?>
    <p>Emails use the site's configured WordPress mail service. Local captures messages in Mailpit; inbox delivery must be verified separately on stage/live.</p>
    <form method="post" action="options.php">
      <?php settings_fields( 'cvipi_story_notifications' ); ?>
      <?php foreach ( array( 'submitter' => 'Submitter thank-you', 'admin' => 'Admin new-submission alert' ) as $type => $label ) : ?>
        <h2><?php echo esc_html( $label ); ?></h2>
        <table class="form-table" role="presentation">
          <tr><th scope="row">Status</th><td><label><input type="checkbox" name="cvipi_story_notifications[<?php echo esc_attr( $type ); ?>_enabled]" value="1" <?php checked( $settings[$type . '_enabled'], '1' ); ?>> Enable <?php echo esc_html( strtolower( $label ) ); ?> emails</label></td></tr>
          <?php if ( 'admin' === $type ) : ?>
            <tr><th scope="row"><label for="story-admin-recipients">Recipients</label></th><td><textarea class="large-text" id="story-admin-recipients" name="cvipi_story_notifications[admin_recipients]" rows="3" aria-describedby="story-recipients-help"><?php echo esc_textarea( $settings['admin_recipients'] ); ?></textarea><p class="description" id="story-recipients-help">Up to 10 addresses, separated by commas or new lines. Defaults to the WordPress Administration Email Address.</p></td></tr>
          <?php endif; ?>
          <tr><th scope="row"><label for="story-<?php echo esc_attr( $type ); ?>-subject">Subject</label></th><td><input class="large-text" id="story-<?php echo esc_attr( $type ); ?>-subject" name="cvipi_story_notifications[<?php echo esc_attr( $type ); ?>_subject]" value="<?php echo esc_attr( $settings[$type . '_subject'] ); ?>" maxlength="180" required aria-describedby="story-<?php echo esc_attr( $type ); ?>-tokens"></td></tr>
          <tr><th scope="row"><label for="story-<?php echo esc_attr( $type ); ?>-heading">Email heading</label></th><td><input class="large-text" id="story-<?php echo esc_attr( $type ); ?>-heading" name="cvipi_story_notifications[<?php echo esc_attr( $type ); ?>_heading]" value="<?php echo esc_attr( $settings[$type . '_heading'] ); ?>" maxlength="100" required aria-describedby="story-<?php echo esc_attr( $type ); ?>-tokens"></td></tr>
          <tr><th scope="row"><label for="story-<?php echo esc_attr( $type ); ?>-message">Message</label></th><td><textarea class="large-text" id="story-<?php echo esc_attr( $type ); ?>-message" name="cvipi_story_notifications[<?php echo esc_attr( $type ); ?>_message]" rows="9" maxlength="10000" required aria-describedby="story-<?php echo esc_attr( $type ); ?>-tokens"><?php echo esc_textarea( $settings[$type . '_message'] ); ?></textarea><p class="description" id="story-<?php echo esc_attr( $type ); ?>-tokens"><?php echo 'admin' === $type ? 'Plain text. Available fields: {site_name}, {name}, {email}, {organization}, {title}, {location}, {review_url}.' : 'Plain text. Available field: {site_name}. Visitor-provided content is not included in receipts to prevent email abuse.'; ?></p></td></tr>
        </table>
      <?php endforeach; ?>
      <?php submit_button(); ?>
    </form>
    <h2>Email Previews</h2>
    <?php foreach ( array( 'submitter' => 'Preview saved thank-you email', 'admin' => 'Preview saved admin email' ) as $type => $label ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" target="_blank" rel="noopener">
        <input type="hidden" name="action" value="cvipi_story_notification_preview"><input type="hidden" name="notification" value="<?php echo esc_attr( $type ); ?>">
        <?php wp_nonce_field( 'cvipi_story_notification_preview', 'cvipi_preview_nonce_' . $type, false ); submit_button( $label, 'secondary', 'preview_' . $type, false ); ?>
      </form><br>
    <?php endforeach; ?>
  </div>
  <?php
}

function cvipi_story_send_notifications( $post_id ) {
  if ( 'success_story' !== get_post_type( $post_id ) || ! get_post_meta( $post_id, '_cvipi_submitted_at', true ) ) return;
  // A form retry or later editor save must not send the receipt again.
  if ( ! add_post_meta( $post_id, '_cvipi_notification_attempted', current_time( 'mysql', true ), true ) ) return;
  $settings = cvipi_story_notification_settings();
  $site_tokens = array( '{site_name}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
  $admin_tokens = $site_tokens + array(
    '{name}' => get_post_meta( $post_id, '_cvipi_submitter_name', true ),
    '{email}' => get_post_meta( $post_id, '_cvipi_submitter_email', true ),
    '{organization}' => get_post_meta( $post_id, '_cvipi_submitter_organization', true ),
    '{location}' => get_post_meta( $post_id, '_cvipi_story_location', true ),
    '{submission_type}' => cvipi_story_submission_type_label( $post_id ),
    '{title}' => get_post_field( 'post_title', $post_id, 'raw' ),
    '{review_url}' => admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' ),
  );
  foreach ( array( 'submitter', 'admin' ) as $type ) {
    $status_key = '_cvipi_notification_' . $type;
    if ( '1' !== $settings[$type . '_enabled'] ) { update_post_meta( $post_id, $status_key, 'disabled' ); continue; }
    $recipients = 'submitter' === $type ? array( $admin_tokens['{email}'] ) : preg_split( '/[\s,;]+/', $settings['admin_recipients'], -1, PREG_SPLIT_NO_EMPTY );
    if ( ! $recipients || count( array_filter( $recipients, 'is_email' ) ) !== count( $recipients ) ) { update_post_meta( $post_id, $status_key, 'failed' ); continue; }
    if ( 'submitter' === $type ) {
      $rate_key = 'cvipi_receipt_' . hash_hmac( 'sha256', strtolower( $recipients[0] ), wp_salt( 'nonce' ) );
      $count = (int) get_transient( $rate_key );
      if ( $count >= 3 ) { update_post_meta( $post_id, $status_key, 'rate_limited' ); continue; }
      set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );
    }
    $tokens = 'admin' === $type ? $admin_tokens : $site_tokens;
    $subject = sanitize_text_field( strtr( $settings[$type . '_subject'], $tokens ) );
    $message = strtr( $settings[$type . '_message'], $tokens );
    $heading = strtr( $settings[$type . '_heading'], $tokens );
    $details = 'admin' === $type ? array( 'Submission type' => $admin_tokens['{submission_type}'], 'Story' => $admin_tokens['{title}'], 'Location' => $admin_tokens['{location}'], 'Submitted by' => $admin_tokens['{name}'], 'Email' => $admin_tokens['{email}'], 'Organization' => $admin_tokens['{organization}'] ) : array();
    $review_url = 'admin' === $type ? $admin_tokens['{review_url}'] : '';
    $plain = $heading . "\n\n" . $message;
    foreach ( $details as $label => $value ) if ( '' !== $value ) $plain .= "\n\n" . $label . ': ' . $value;
    if ( $review_url ) $plain .= "\n\nReview story (WordPress sign-in required): " . $review_url;
    $html = cvipi_story_email_html( $subject, $heading, $message, $details, $review_url, true );
    $configure_mail = function ( $mailer ) use ( $plain ) {
      $mailer->AltBody = $plain;
      $logo = get_template_directory() . '/assets/img/cvipi-logo-email.png';
      if ( is_readable( $logo ) ) $mailer->addEmbeddedImage( $logo, 'cvipi-story-logo', 'cvipi.png', 'base64', 'image/png' );
    };
    $sender_name = function () use ( $site_tokens ) { return sanitize_text_field( $site_tokens['{site_name}'] ); };
    update_post_meta( $post_id, $status_key, 'sending' );
    add_action( 'phpmailer_init', $configure_mail );
    add_filter( 'wp_mail_from_name', $sender_name );
    try {
      $sent = wp_mail( $recipients, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
    } catch ( Throwable $error ) {
      $sent = false;
    } finally {
      remove_action( 'phpmailer_init', $configure_mail );
      remove_filter( 'wp_mail_from_name', $sender_name );
      // WordPress reuses PHPMailer; do not leak this plain-text message into another email.
      global $phpmailer;
      if ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) $phpmailer->AltBody = '';
    }
    // Transport acceptance is not a delivery receipt. Never discard a saved story on mail failure.
    update_post_meta( $post_id, $status_key, $sent ? 'accepted' : 'failed' );
  }
}

function cvipi_story_email_html( $subject, $heading, $message, $details = array(), $review_url = '', $embed_logo = false, $presentation = array() ) {
  // Inline email-safe equivalents of the theme's deep, dark-blue and carmine tokens.
  $brand = array( 'deep' => '#071820', 'blue' => '#174f6b', 'carmine' => '#eac293', 'white' => '#ffffff' );
  $logo = $embed_logo ? 'cid:cvipi-story-logo' : get_template_directory_uri() . '/assets/img/cvipi-logo-email.png';
  $eyebrow = $presentation['eyebrow'] ?? 'STORIES FROM THE FIELD';
  $button_label = $presentation['button_label'] ?? 'Review Story';
  ob_start();
  include get_template_directory() . '/templates/story-email.php';
  return ob_get_clean();
}

add_action( 'admin_post_cvipi_story_notification_preview', function () {
  if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You cannot preview these notifications.', '', array( 'response' => 403 ) );
  $type = 'admin' === cvipi_story_input( 'notification' ) ? 'admin' : 'submitter';
  check_admin_referer( 'cvipi_story_notification_preview', 'cvipi_preview_nonce_' . $type );
  $settings = cvipi_story_notification_settings();
  $tokens = array( '{site_name}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
  $url = admin_url( 'edit.php?post_type=success_story&cvipi_submissions=1' );
  if ( 'admin' === $type ) $tokens += array( '{name}' => 'Community Partner', '{email}' => 'partner@example.org', '{organization}' => 'Community Organization', '{title}' => 'Tucson, AZ', '{location}' => 'Tucson, AZ', '{submission_type}' => 'Share a story', '{review_url}' => $url );
  $details = 'admin' === $type ? array( 'Submission type' => 'Share a story', 'Story' => 'Tucson, AZ', 'Location' => 'Tucson, AZ', 'Submitted by' => 'Community Partner', 'Email' => 'partner@example.org', 'Organization' => 'Community Organization' ) : array();
  nocache_headers();
  header( 'Content-Type: text/html; charset=UTF-8' );
  header( 'X-Robots-Tag: noindex, nofollow' );
  echo cvipi_story_email_html( strtr( $settings[$type . '_subject'], $tokens ), strtr( $settings[$type . '_heading'], $tokens ), strtr( $settings[$type . '_message'], $tokens ), $details, 'admin' === $type ? $url : '' );
  exit;
} );

function cvipi_story_notification_summary( $post_id ) {
  $labels = array( 'accepted' => 'Accepted by mail service (delivery not confirmed)', 'failed' => 'Sending failed; check mail configuration', 'disabled' => 'Disabled at submission', 'rate_limited' => 'Suppressed by email rate limit', 'sending' => 'Attempt started; outcome not recorded' );
  foreach ( array( 'submitter' => 'Submitter email', 'admin' => 'Admin email' ) as $type => $label ) {
    $status = get_post_meta( $post_id, '_cvipi_notification_' . $type, true );
    echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $labels[$status] ?? 'No notification recorded' ) . '</p>';
  }
}
