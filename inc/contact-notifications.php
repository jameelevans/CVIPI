<?php
/** Branded Contact messages sent through the existing WordPress mail transport. */
defined( 'ABSPATH' ) || exit;

function cvipi_contact_notification_defaults() {
  return array(
    'submitter_enabled' => '1', 'admin_enabled' => '1', 'admin_recipients' => get_option( 'admin_email' ),
    'submitter_subject' => 'Thank you for contacting {site_name}',
    'submitter_heading' => 'Thank you for reaching out.',
    'submitter_message' => "We have received your message. Our team will review your inquiry and connect you with the right people and resources.\n\nWe typically respond within 2-3 business days.\n\nThe {site_name} team",
    'admin_subject' => '[{site_name}] New contact message',
    'admin_heading' => 'A new message has arrived.',
    'admin_message' => "Someone has contacted {site_name}. Their message and contact details are below.\n\nOpen the message to review it and update its status.",
  );
}
function cvipi_contact_notification_settings() {
  $saved = get_option( 'cvipi_contact_notifications', array() );
  return array_merge( cvipi_contact_notification_defaults(), is_array( $saved ) ? $saved : array() );
}
function cvipi_contact_sanitize_notifications( $input ) {
  $old = cvipi_contact_notification_settings();
  if ( ! is_array( $input ) ) return $old;
  $clean = array();
  foreach ( array( 'submitter', 'admin' ) as $type ) {
    $clean[$type . '_enabled'] = isset( $input[$type . '_enabled'] ) && '1' === $input[$type . '_enabled'] ? '1' : '0';
    foreach ( array( 'subject' => 180, 'heading' => 100, 'message' => 10000 ) as $field => $limit ) {
      $key = $type . '_' . $field;
      $raw = isset( $input[$key] ) && is_string( $input[$key] ) ? $input[$key] : '';
      $clean[$key] = 'message' === $field ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
      if ( '' === $clean[$key] || mb_strlen( $raw ) > $limit ) {
        add_settings_error( 'cvipi_contact_notifications', 'invalid-template', 'Enter a subject (up to 180 characters), heading (up to 100), and message (up to 10,000) for each email. Changes were not saved.' );
        return $old;
      }
    }
  }
  $raw = isset( $input['admin_recipients'] ) && is_string( $input['admin_recipients'] ) ? $input['admin_recipients'] : '';
  $recipients = array_values( array_unique( preg_split( '/[\s,;]+/', trim( $raw ), -1, PREG_SPLIT_NO_EMPTY ) ) );
  if ( count( $recipients ) > 10 || ( '1' === $clean['admin_enabled'] && ! $recipients ) || count( array_filter( $recipients, 'is_email' ) ) !== count( $recipients ) ) {
    add_settings_error( 'cvipi_contact_notifications', 'invalid-recipients', 'Enter up to 10 valid team email addresses, separated by commas or new lines. Changes were not saved.' );
    return $old;
  }
  $clean['admin_recipients'] = implode( "\n", $recipients );
  return $clean;
}
add_action( 'admin_init', function () {
  register_setting( 'cvipi_contact_notifications', 'cvipi_contact_notifications', array( 'sanitize_callback' => 'cvipi_contact_sanitize_notifications' ) );
} );
add_action( 'admin_menu', function () {
  add_submenu_page( 'edit.php?post_type=cvipi_message', 'Contact Notifications', 'Notifications', 'manage_options', 'cvipi-contact-notifications', 'cvipi_contact_notifications_page' );
} );
function cvipi_contact_notifications_page() {
  if ( ! current_user_can( 'manage_options' ) ) return;
  $settings = cvipi_contact_notification_settings();
  ?>
  <div class="wrap">
    <h1>Contact Notifications</h1>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
      <?php settings_fields( 'cvipi_contact_notifications' ); ?>
      <?php foreach ( array( 'submitter' => 'Sender confirmation', 'admin' => 'Team notification' ) as $type => $label ) : ?>
        <h2><?php echo esc_html( $label ); ?></h2>
        <table class="form-table" role="presentation">
          <tr><th scope="row">Status</th><td><label><input type="checkbox" name="cvipi_contact_notifications[<?php echo esc_attr( $type ); ?>_enabled]" value="1" <?php checked( $settings[$type . '_enabled'], '1' ); ?>> Enable <?php echo esc_html( strtolower( $label ) ); ?></label></td></tr>
          <?php if ( 'admin' === $type ) : ?>
            <tr><th scope="row"><label for="contact-admin-recipients">Team recipients</label></th><td><textarea class="large-text" id="contact-admin-recipients" name="cvipi_contact_notifications[admin_recipients]" rows="3"><?php echo esc_textarea( $settings['admin_recipients'] ); ?></textarea></td></tr>
          <?php endif; ?>
          <?php foreach ( array( 'subject' => 'Subject', 'heading' => 'Email heading' ) as $field => $field_label ) : ?>
            <tr><th scope="row"><label for="contact-<?php echo esc_attr( $type . '-' . $field ); ?>"><?php echo esc_html( $field_label ); ?></label></th><td><input class="large-text" id="contact-<?php echo esc_attr( $type . '-' . $field ); ?>" name="cvipi_contact_notifications[<?php echo esc_attr( $type . '_' . $field ); ?>]" value="<?php echo esc_attr( $settings[$type . '_' . $field] ); ?>" maxlength="<?php echo 'subject' === $field ? '180' : '100'; ?>" required></td></tr>
          <?php endforeach; ?>
          <tr><th scope="row"><label for="contact-<?php echo esc_attr( $type ); ?>-message">Message</label></th><td><textarea class="large-text" id="contact-<?php echo esc_attr( $type ); ?>-message" name="cvipi_contact_notifications[<?php echo esc_attr( $type ); ?>_message]" rows="8" maxlength="10000" required><?php echo esc_textarea( $settings[$type . '_message'] ); ?></textarea><p class="description">Available field: <code>{site_name}</code></p></td></tr>
        </table>
      <?php endforeach; ?>
      <?php submit_button(); ?>
    </form>
    <h2>Email Previews</h2>
    <?php foreach ( array( 'submitter' => 'Preview sender confirmation', 'admin' => 'Preview team notification' ) as $type => $label ) : ?>
      <p><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cvipi_contact_preview&type=' . $type ), 'cvipi_contact_preview' ) ); ?>"><?php echo esc_html( $label ); ?></a></p>
    <?php endforeach; ?>
  </div>
  <?php
}

function cvipi_contact_email_content( $type, $data, $url = '', $embed = false ) {
  $settings = cvipi_contact_notification_settings();
  $tokens = array( '{site_name}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
  $subject = sanitize_text_field( strtr( $settings[$type . '_subject'], $tokens ) );
  $heading = strtr( $settings[$type . '_heading'], $tokens );
  $message = strtr( $settings[$type . '_message'], $tokens );
  $details = 'admin' === $type ? array( 'Name' => $data['name'], 'Email' => $data['email'], 'Organization' => $data['organization'], 'Role / Title' => $data['role'], 'Subject' => $data['subject'], 'Message' => $data['message'] ) : array();
  $url = 'admin' === $type ? $url : '';
  $plain = $heading . "\n\n" . $message;
  foreach ( $details as $label => $value ) if ( '' !== $value ) $plain .= "\n\n" . $label . ': ' . $value;
  if ( $url ) $plain .= "\n\nView message (WordPress sign-in required): " . $url;
  $html = cvipi_story_email_html( $subject, $heading, $message, $details, $url, $embed, array( 'eyebrow' => 'CONTACT CVIPI', 'button_label' => 'View Message' ) );
  return compact( 'subject', 'html', 'plain' );
}

function cvipi_contact_send_notifications( $id ) {
  if ( 'cvipi_message' !== get_post_type( $id ) ) return;
  $lock = 'cvipi_contact_mail_lock_' . (int) $id;
  $started = (int) get_option( $lock );
  if ( $started && $started < time() - 5 * MINUTE_IN_SECONDS ) delete_option( $lock );
  if ( ! add_option( $lock, time(), '', false ) ) return;
  try {
    cvipi_contact_deliver_notifications( $id );
  } finally {
    delete_option( $lock );
  }
}

function cvipi_contact_deliver_notifications( $id ) {
  $data = get_post_meta( $id, '_cvipi_contact_data', true );
  if ( ! is_array( $data ) || ! is_email( $data['email'] ?? '' ) ) return;
  // Retries and admin edits never send a second notification for this message.
  if ( ! add_post_meta( $id, '_cvipi_notification_attempted', current_time( 'mysql', true ), true ) ) return;
  $settings = cvipi_contact_notification_settings();
  $team = preg_split( '/[\s,;]+/', $settings['admin_recipients'], -1, PREG_SPLIT_NO_EMPTY );
  foreach ( array( 'submitter', 'admin' ) as $type ) {
    $status = '_cvipi_notification_' . $type;
    if ( '1' !== $settings[$type . '_enabled'] ) { update_post_meta( $id, $status, 'disabled' ); continue; }
    $recipients = 'admin' === $type ? $team : array( $data['email'] );
    if ( ! $recipients || count( array_filter( $recipients, 'is_email' ) ) !== count( $recipients ) ) { update_post_meta( $id, $status, 'failed' ); continue; }
    if ( 'submitter' === $type ) {
      $key = 'cvipi_contact_receipt_' . hash_hmac( 'sha256', strtolower( $data['email'] ), wp_salt( 'nonce' ) );
      $count = (int) get_transient( $key );
      if ( $count >= 3 ) { update_post_meta( $id, $status, 'rate_limited' ); continue; }
      set_transient( $key, $count + 1, HOUR_IN_SECONDS );
    }
    $email = cvipi_contact_email_content( $type, $data, admin_url( 'post.php?post=' . (int) $id . '&action=edit' ), true );
    $configure = function ( $mailer ) use ( $email ) {
      $mailer->AltBody = $email['plain'];
      $logo = get_template_directory() . '/assets/img/cvipi-logo-email.png';
      if ( is_readable( $logo ) ) $mailer->addEmbeddedImage( $logo, 'cvipi-story-logo', 'cvipi.png', 'base64', 'image/png' );
    };
    $name = function () { return sanitize_text_field( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ); };
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    $reply_to = 'admin' === $type ? $data['email'] : ( $team[0] ?? '' );
    if ( is_email( $reply_to ) ) $headers[] = 'Reply-To: ' . $reply_to;
    update_post_meta( $id, $status, 'sending' );
    add_action( 'phpmailer_init', $configure );
    add_filter( 'wp_mail_from_name', $name );
    try {
      $sent = wp_mail( $recipients, $email['subject'], $email['html'], $headers );
    } catch ( Throwable $error ) {
      $sent = false;
    } finally {
      remove_action( 'phpmailer_init', $configure );
      remove_filter( 'wp_mail_from_name', $name );
      global $phpmailer;
      if ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) $phpmailer->AltBody = '';
    }
    // Mail failure must not discard the saved message; accepted is not delivered.
    update_post_meta( $id, $status, $sent ? 'accepted' : 'failed' );
  }
}

add_action( 'admin_post_cvipi_contact_preview', function () {
  if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You cannot preview these notifications.', '', array( 'response' => 403 ) );
  check_admin_referer( 'cvipi_contact_preview' );
  $type = 'admin' === ( $_GET['type'] ?? '' ) ? 'admin' : 'submitter';
  $data = array( 'name' => 'Community Partner', 'email' => 'partner@example.org', 'organization' => 'Community Organization', 'role' => 'Program Coordinator', 'subject' => 'General Inquiry', 'message' => "Hello CVIPI team,\n\nWe would like to learn more about technical assistance for our community. Thank you for helping us connect with the right resources." );
  $email = cvipi_contact_email_content( $type, $data, admin_url( 'edit.php?post_type=cvipi_message' ) );
  nocache_headers();
  header( 'Content-Type: text/html; charset=UTF-8' );
  header( 'X-Robots-Tag: noindex, nofollow' );
  echo $email['html'];
  exit;
} );
