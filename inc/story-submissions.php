<?php
/** Public submissions stay pending until an editor publishes them. */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/story-notifications.php';

function cvipi_story_captcha_config() {
  $host = wp_parse_url( home_url(), PHP_URL_HOST );
  $local = 'local' === wp_get_environment_type() && ( 'cvipi.local' === $host || 'localhost' === $host );
  $keys = get_option( 'cvipi_story_captcha', array() );
  return array(
    'local' => $local,
    'site' => $local ? '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' : ( $keys['site'] ?? '' ),
    'secret' => $local ? '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe' : ( $keys['secret'] ?? '' ),
  );
}

function cvipi_story_ip_key() {
  return hash_hmac( 'sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', wp_salt( 'nonce' ) );
}

function cvipi_story_input( $key ) {
  return isset( $_POST[$key] ) && is_string( $_POST[$key] ) ? wp_unslash( $_POST[$key] ) : '';
}

function cvipi_story_submission_types() {
  return array(
    'content' => 'Content (Research, Events, or Work around the field)',
    'story' => 'Share a story',
  );
}

function cvipi_story_submission_type_label( $post_id ) {
  $type = get_post_meta( $post_id, '_cvipi_submission_type', true );
  // Submissions received before this choice was added were all stories.
  if ( '' === $type ) $type = 'story';
  return cvipi_story_submission_types()[$type] ?? 'Not specified';
}

function cvipi_story_normalize_location( $location ) {
  $parts = array_map( 'trim', explode( ',', sanitize_text_field( $location ) ) );
  if ( 2 !== count( $parts ) || '' === $parts[0] || '' === $parts[1] ) return '';
  $parts = array_map( function ( $part ) { return preg_replace( '/\s+/u', ' ', $part ); }, $parts );
  if ( preg_match( '/^[a-z]{2}$/i', $parts[1] ) ) $parts[1] = strtoupper( $parts[1] );
  return implode( ', ', $parts );
}

function cvipi_story_location_category( $location ) {
  $term = term_exists( $location, 'category' );
  if ( ! $term ) $term = wp_insert_term( $location, 'category' );
  // A concurrent submission may have created the same category first.
  if ( is_wp_error( $term ) && 'term_exists' === $term->get_error_code() ) return (int) $term->get_error_data();
  return is_wp_error( $term ) ? $term : (int) $term['term_id'];
}

function cvipi_story_form_session() {
  nocache_headers();
  $key = 'cvipi_story_open_' . cvipi_story_ip_key();
  $count = (int) get_transient( $key );
  if ( $count >= 60 ) wp_send_json_error( array( 'message' => 'Please wait a while before opening another submission.' ), 429 );
  set_transient( $key, $count + 1, HOUR_IN_SECONDS );
  $id = wp_generate_uuid4();
  set_transient( 'cvipi_story_' . $id, array( 'created' => time(), 'ip' => cvipi_story_ip_key() ), HOUR_IN_SECONDS );
  $config = cvipi_story_captcha_config();
  wp_send_json_success( array( 'session' => $id, 'nonce' => wp_create_nonce( 'cvipi_submit_story' ), 'siteKey' => $config['site'], 'ready' => (bool) ( $config['site'] && $config['secret'] ) ) );
}
add_action( 'wp_ajax_cvipi_story_session', 'cvipi_story_form_session' );
add_action( 'wp_ajax_nopriv_cvipi_story_session', 'cvipi_story_form_session' );

function cvipi_submit_story() {
  nocache_headers();
  if ( ! wp_verify_nonce( cvipi_story_input( 'nonce' ), 'cvipi_submit_story' ) ) {
    wp_send_json_error( array( 'message' => 'Your form session expired. Close and reopen the form; your text will be kept.' ), 403 );
  }
  $session_id = cvipi_story_input( 'session' );
  if ( ! preg_match( '/^[a-f0-9-]{36}$/', $session_id ) ) wp_send_json_error( array( 'message' => 'Please reopen the form and try again.' ), 400 );
  $session = get_transient( 'cvipi_story_' . $session_id );
  if ( ! is_array( $session ) || ! hash_equals( $session['ip'], cvipi_story_ip_key() ) ) {
    wp_send_json_error( array( 'message' => 'Your form session expired. Close and reopen the form; your text will be kept.' ), 403 );
  }
  if ( ! empty( $session['submitted'] ) ) wp_send_json_success();
  $rate_key = 'cvipi_story_rate_' . cvipi_story_ip_key();
  $attempts = (int) get_transient( $rate_key );
  if ( $attempts >= 20 ) wp_send_json_error( array( 'message' => 'Too many attempts. Please try again in an hour.' ), 429 );
  set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );
  if ( cvipi_story_input( 'website' ) || time() - $session['created'] < 3 ) {
    wp_send_json_error( array( 'message' => 'We could not verify this submission. Please try again.' ), 400 );
  }
  $fields = array();
  $errors = array();
  foreach ( array( 'name' => 120, 'email' => 254, 'organization' => 180, 'location' => 180, 'title' => 180, 'story' => 20000 ) as $field => $max ) {
    $raw = trim( cvipi_story_input( $field ) );
    $fields[$field] = 'story' === $field ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
    if ( ( ! in_array( $field, array( 'organization', 'title' ), true ) && '' === $fields[$field] ) || mb_strlen( $raw ) > $max ) $errors[$field] = 'Please enter ' . $field . ' (up to ' . number_format( $max ) . ' characters).';
  }
  if ( ! is_email( $fields['email'] ) ) $errors['email'] = 'Enter a valid email address.';
  $fields['submission_type'] = cvipi_story_input( 'submission_type' );
  if ( ! array_key_exists( $fields['submission_type'], cvipi_story_submission_types() ) ) $errors['submission_type'] = 'Choose Content or Share a story.';
  $fields['location'] = cvipi_story_normalize_location( $fields['location'] );
  if ( '' === $fields['location'] ) $errors['location'] = 'Enter your location as City, State (for example, Tucson, AZ).';
  if ( mb_strlen( $fields['story'] ) < 80 ) $errors['story'] = 'Please share at least 80 characters about your story.';
  if ( 'yes' !== cvipi_story_input( 'consent' ) ) $errors['consent'] = 'Please confirm permission to review your story.';
  if ( $errors ) wp_send_json_error( array( 'message' => 'Please check the highlighted fields.', 'fields' => $errors ), 422 );
  if ( '' === $fields['title'] ) $fields['title'] = $fields['location'];
  $config = cvipi_story_captcha_config();
  $token = cvipi_story_input( 'captcha' );
  if ( ! $config['site'] || ! $config['secret'] || ! $token || strlen( $token ) > 4096 ) {
    wp_send_json_error( array( 'message' => 'Please complete the CAPTCHA verification.' ), 422 );
  }
  $verified = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
    'timeout' => 15,
    'body' => array( 'secret' => $config['secret'], 'response' => $token ),
  ) );
  $result = is_wp_error( $verified ) ? array() : json_decode( wp_remote_retrieve_body( $verified ), true );
  $host = wp_parse_url( home_url(), PHP_URL_HOST );
  if ( empty( $result['success'] ) || ( ! $config['local'] && ( $result['hostname'] ?? '' ) !== $host ) ) {
    wp_send_json_error( array( 'message' => 'CAPTCHA verification failed or expired. Please verify again.' ), 422 );
  }
  // An atomic option prevents double clicks/retries from creating two stories.
  $lock = 'cvipi_story_lock_' . $session_id;
  if ( ! add_option( $lock, time(), '', false ) ) wp_send_json_error( array( 'message' => 'Your submission is processing. Please wait before trying again.' ), 409 );
  // Another request may have completed while Google verified this token.
  if ( ! wp_using_ext_object_cache() ) wp_cache_delete( '_transient_cvipi_story_' . $session_id, 'options' );
  $latest_session = get_transient( 'cvipi_story_' . $session_id );
  if ( ! empty( $latest_session['submitted'] ) ) {
    delete_option( $lock );
    wp_send_json_success();
  }
  $category_id = cvipi_story_location_category( $fields['location'] );
  if ( is_wp_error( $category_id ) ) {
    delete_option( $lock );
    wp_send_json_error( array( 'message' => 'We could not save your location. Your text is still here; please try again.' ), 500 );
  }
  $paragraphs = preg_split( '/\n\s*\n/', $fields['story'] );
  $content = '';
  foreach ( $paragraphs as $paragraph ) $content .= '<!-- wp:paragraph --><p>' . nl2br( esc_html( trim( $paragraph ) ) ) . '</p><!-- /wp:paragraph -->' . "\n\n";
  $post_id = wp_insert_post( wp_slash( array(
    'post_type' => 'success_story', 'post_status' => 'pending', 'post_author' => 0,
    'post_title' => $fields['title'], 'post_content' => $content,
    'post_category' => array( $category_id ),
    'comment_status' => 'closed', 'ping_status' => 'closed',
    'meta_input' => array(
      '_cvipi_submission_queue' => '1', '_cvipi_submitted_at' => current_time( 'mysql', true ),
      '_cvipi_submitter_name' => $fields['name'], '_cvipi_submitter_email' => $fields['email'],
      '_cvipi_submitter_organization' => $fields['organization'], '_cvipi_story_location' => $fields['location'],
      '_cvipi_submission_consent' => 'review-and-publication-v1',
      '_cvipi_submission_type' => $fields['submission_type'],
    ),
  ) ), true );
  if ( is_wp_error( $post_id ) ) {
    delete_option( $lock );
    wp_send_json_error( array( 'message' => 'We could not save your story. Your text is still here; please try again.' ), 500 );
  }
  $session['submitted'] = true;
  set_transient( 'cvipi_story_' . $session_id, $session, HOUR_IN_SECONDS );
  delete_option( $lock );
  cvipi_story_send_notifications( $post_id );
  wp_send_json_success();
}
add_action( 'wp_ajax_cvipi_submit_story', 'cvipi_submit_story' );
add_action( 'wp_ajax_nopriv_cvipi_submit_story', 'cvipi_submit_story' );

function cvipi_story_is_queue() {
  return is_admin() && isset( $_GET['cvipi_submissions'] ) && '1' === $_GET['cvipi_submissions'];
}

add_action( 'pre_get_posts', function ( $query ) {
  global $pagenow;
  if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() || 'success_story' !== $query->get( 'post_type' ) ) return;
  $meta = (array) $query->get( 'meta_query' );
  $meta[] = array( 'key' => '_cvipi_submission_queue', 'compare' => cvipi_story_is_queue() ? 'EXISTS' : 'NOT EXISTS' );
  $query->set( 'meta_query', $meta );
} );

add_filter( 'post_type_labels_success_story', function ( $labels ) {
  if ( cvipi_story_is_queue() ) $labels->name = 'Submitted Stories';
  return $labels;
} );

add_action( 'transition_post_status', function ( $new, $old, $post ) {
  if ( 'success_story' === $post->post_type && 'publish' === $new ) delete_post_meta( $post->ID, '_cvipi_submission_queue' );
}, 10, 3 );

add_action( 'admin_menu', function () {
  add_submenu_page( 'edit.php?post_type=success_story', 'Submitted Stories', 'Submitted Stories', 'edit_others_posts', 'edit.php?post_type=success_story&cvipi_submissions=1' );
  add_submenu_page( 'edit.php?post_type=success_story', 'Submission Settings', 'Submission Settings', 'manage_options', 'cvipi-story-settings', 'cvipi_story_settings_page' );
} );
add_filter( 'views_edit-success_story', function ( $views ) {
  $url = admin_url( 'edit.php?post_type=success_story' );
  return array(
    'stories' => '<a href="' . esc_url( $url ) . '"' . ( cvipi_story_is_queue() ? '' : ' class="current" aria-current="page"' ) . '>Success Stories</a>',
    'submissions' => '<a href="' . esc_url( add_query_arg( 'cvipi_submissions', '1', $url ) ) . '"' . ( cvipi_story_is_queue() ? ' class="current" aria-current="page"' : '' ) . '>Submitted Stories</a>',
    'trash' => '<a href="' . esc_url( add_query_arg( array( 'post_status' => 'trash', 'cvipi_submissions' => cvipi_story_is_queue() ? '1' : '0' ), $url ) ) . '">Trash</a>',
  );
} );
add_action( 'restrict_manage_posts', function ( $type ) {
  if ( 'success_story' === $type && cvipi_story_is_queue() ) echo '<input type="hidden" name="cvipi_submissions" value="1">';
} );
add_filter( 'manage_success_story_posts_columns', function ( $columns ) {
  if ( cvipi_story_is_queue() ) { $columns['cvipi_submission_type'] = 'Submission type'; $columns['cvipi_location'] = 'Location'; $columns['cvipi_submitter'] = 'Submitted by'; }
  return $columns;
} );
add_action( 'manage_success_story_posts_custom_column', function ( $column, $id ) {
  if ( 'cvipi_submission_type' === $column ) echo esc_html( cvipi_story_submission_type_label( $id ) );
  if ( 'cvipi_location' === $column ) echo esc_html( get_post_meta( $id, '_cvipi_story_location', true ) );
  if ( 'cvipi_submitter' === $column && current_user_can( 'edit_post', $id ) ) echo esc_html( get_post_meta( $id, '_cvipi_submitter_name', true ) );
}, 10, 2 );

add_action( 'add_meta_boxes_success_story', function ( $post ) {
  if ( ! get_post_meta( $post->ID, '_cvipi_submitted_at', true ) ) return;
  add_meta_box( 'cvipi-submission-details', 'Submission Details', function ( $post ) {
    echo '<p><strong>Submission type:</strong> ' . esc_html( cvipi_story_submission_type_label( $post->ID ) ) . '</p>';
    foreach ( array( 'Name' => '_cvipi_submitter_name', 'Email' => '_cvipi_submitter_email', 'Organization' => '_cvipi_submitter_organization', 'Submitted (UTC)' => '_cvipi_submitted_at' ) as $label => $key ) {
      echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( get_post_meta( $post->ID, $key, true ) ) . '</p>';
    }
    wp_nonce_field( 'cvipi_edit_submission', 'cvipi_submission_nonce' );
    echo '<p><label for="cvipi-review-location"><strong>Location</strong></label><input class="widefat" id="cvipi-review-location" name="cvipi_story_location" maxlength="180" value="' . esc_attr( get_post_meta( $post->ID, '_cvipi_story_location', true ) ) . '"></p>';
    echo '<p>Permission to review and publish: received. Contact details are private. Review the story and complete its header fields before publishing.</p>';
    cvipi_story_notification_summary( $post->ID );
  }, 'success_story', 'side' );
} );

add_action( 'save_post_success_story', function ( $id ) {
  if ( wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || ! current_user_can( 'edit_post', $id ) ) return;
  if ( ! wp_verify_nonce( cvipi_story_input( 'cvipi_submission_nonce' ), 'cvipi_edit_submission' ) ) return;
  update_post_meta( $id, '_cvipi_story_location', sanitize_text_field( mb_substr( cvipi_story_input( 'cvipi_story_location' ), 0, 180 ) ) );
} );

add_action( 'admin_init', function () {
  register_setting( 'cvipi_story_settings', 'cvipi_story_captcha', array( 'sanitize_callback' => function ( $input ) {
    $old = get_option( 'cvipi_story_captcha', array() );
    if ( ! is_array( $input ) ) return $old;
    $site = sanitize_text_field( $input['site'] ?? '' );
    $secret = sanitize_text_field( $input['secret'] ?? '' );
    if ( empty( $secret ) ) $secret = $old['secret'] ?? '';
    if ( str_starts_with( $site, '6LeIxAcT' ) || str_starts_with( $secret, '6LeIxAcT' ) ) {
      add_settings_error( 'cvipi_story_captcha', 'test-keys', 'Use real keys here. Local test keys are applied automatically.' );
      return $old;
    }
    return array( 'site' => $site, 'secret' => $secret );
  } ) );
} );

function cvipi_story_settings_page() {
  if ( ! current_user_can( 'manage_options' ) ) return;
  $keys = get_option( 'cvipi_story_captcha', array() );
  ?>
  <div class="wrap"><h1>Story Submission Settings</h1>
    <?php settings_errors(); ?>
    <p>Use Google reCAPTCHA v2 checkbox keys. Keep domain verification enabled for cvipi.info. Local development uses Google's official test keys; stage and live require real keys.</p>
    <form method="post" action="options.php">
      <?php settings_fields( 'cvipi_story_settings' ); ?>
      <table class="form-table" role="presentation">
        <tr><th><label for="story-site-key">Site key</label></th><td><input class="regular-text" id="story-site-key" name="cvipi_story_captcha[site]" value="<?php echo esc_attr( $keys['site'] ?? '' ); ?>" autocomplete="off"></td></tr>
        <tr><th><label for="story-secret-key">Secret key</label></th><td><input class="regular-text" type="password" id="story-secret-key" name="cvipi_story_captcha[secret]" value="" autocomplete="new-password"><p class="description"><?php echo empty( $keys['secret'] ) ? 'No secret key saved.' : 'Secret key saved. Leave blank to keep it.'; ?></p></td></tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

add_action( 'wp_footer', function () { get_template_part( 'templates/story-submission' ); } );
