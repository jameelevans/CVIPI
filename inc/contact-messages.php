<?php
/** Private inbox for the configured WPForms Contact form. */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/contact-notifications.php';

function cvipi_contact_config() {
  $config = get_option( 'cvipi_contact_form', array() );
  return is_array( $config ) ? $config : array();
}

function cvipi_contact_is_form( $form ) {
  $config = cvipi_contact_config();
  return ! empty( $config['id'] ) && (int) $config['id'] === (int) ( $form['id'] ?? 0 );
}

add_action( 'init', function () {
  $caps = array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'read' ), 'manage_options' );
  $caps['create_posts'] = 'do_not_allow';
  register_post_type( 'cvipi_message', array(
    'labels' => array( 'name' => 'Contact Messages', 'singular_name' => 'Contact Message', 'edit_item' => 'Review Contact Message', 'search_items' => 'Search Messages', 'not_found' => 'No messages found.', 'not_found_in_trash' => 'No messages in Trash.', 'all_items' => 'All Messages' ),
    'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true,
    'show_ui' => true, 'show_in_rest' => false, 'rewrite' => false, 'query_var' => false,
    'can_export' => false, 'delete_with_user' => false, 'supports' => false,
    'capabilities' => $caps, 'map_meta_cap' => false, 'menu_icon' => 'dashicons-email-alt', 'menu_position' => 26,
  ) );
} );

add_filter( 'wp_insert_post_data', function ( $data ) {
  if ( 'cvipi_message' === $data['post_type'] && 'trash' !== $data['post_status'] ) $data['post_status'] = 'private';
  return $data;
} );

function cvipi_contact_states() {
  return array( 'new' => 'New', 'in_progress' => 'In Progress', 'resolved' => 'Resolved' );
}

function cvipi_contact_fields( $fields ) {
  $config = cvipi_contact_config();
  $data = array();
  foreach ( array( 'name' => 120, 'email' => 254, 'organization' => 180, 'role' => 180, 'subject' => 250, 'message' => 20000 ) as $key => $max ) {
    $id = $config['fields'][$key] ?? null;
    if ( null === $id || ! isset( $fields[$id] ) ) return new WP_Error( 'mapping', 'The form configuration needs attention. Please email the CVIPI team directly.' );
    $value = $fields[$id]['value'] ?? '';
    if ( ! is_string( $value ) || mb_strlen( $value ) > $max ) return new WP_Error( 'length', 'Please shorten your ' . $key . ' to ' . number_format( $max ) . ' characters or fewer.' );
    $data[$key] = 'message' === $key ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
  }
  if ( ! is_email( $data['email'] ) || '' === $data['name'] || '' === $data['message'] ) return new WP_Error( 'required', 'Please enter your name, a valid email address, and a message.' );
  return $data;
}

function cvipi_contact_store( $data, $form_id ) {
  $fingerprint = hash_hmac( 'sha256', $form_id . wp_json_encode( $data ), wp_salt( 'nonce' ) );
  $cache = 'cvipi_contact_duplicate_' . $fingerprint;
  $lock = 'cvipi_contact_lock_' . $fingerprint;
  $started = (int) get_option( $lock );
  if ( $started && $started < time() - 60 ) delete_option( $lock );
  if ( ! add_option( $lock, time(), '', false ) ) return new WP_Error( 'busy', 'Your message is being saved. Please wait a moment before trying again.' );
  try {
    if ( ! wp_using_ext_object_cache() ) wp_cache_delete( '_transient_' . $cache, 'options' );
    $existing = (int) get_transient( $cache );
    if ( $existing && 'cvipi_message' === get_post_type( $existing ) ) return $existing;
    $id = wp_insert_post( wp_slash( array(
      'post_type' => 'cvipi_message', 'post_status' => 'private', 'post_author' => 0,
      'post_title' => ( $data['subject'] ?: 'General inquiry' ) . ' - ' . $data['name'],
      'post_content' => $data['message'],
      // The private excerpt makes sender and organization searchable without custom SQL.
      'post_excerpt' => implode( ' ', array( $data['name'], $data['email'], $data['organization'], $data['role'] ) ),
      'comment_status' => 'closed', 'ping_status' => 'closed',
      'meta_input' => array( '_cvipi_contact_data' => $data, '_cvipi_contact_form_id' => (int) $form_id, '_cvipi_contact_state' => 'new', '_cvipi_contact_unread' => '1' ),
    ) ), true );
    if ( is_wp_error( $id ) ) return new WP_Error( 'storage', 'We could not save your message. Please try again or email the CVIPI team directly.' );
    set_transient( $cache, $id, 5 * MINUTE_IN_SECONDS );
    return $id;
  } finally {
    delete_option( $lock );
  }
}

// Runs after WPForms field, CAPTCHA and spam validation, before its final error check.
add_filter( 'wpforms_process_after_filter', 'cvipi_contact_capture', PHP_INT_MAX, 3 );
function cvipi_contact_capture( $fields, $entry, $form ) {
  if ( ! cvipi_contact_is_form( $form ) ) return $fields;
  unset( $GLOBALS['cvipi_contact_saved_id'] );
  $process = wpforms()->obj( 'process' );
  if ( ! empty( $process->errors[$form['id']] ) || ! empty( $form['spam_reason'] ) || ! empty( $process->spam_reason ) || ! empty( $process->spam_errors[$form['id']] ) ) return $fields;
  $data = cvipi_contact_fields( $fields );
  $id = is_wp_error( $data ) ? $data : cvipi_contact_store( $data, $form['id'] );
  if ( is_wp_error( $id ) ) {
    $process->errors[$form['id']]['footer'] = $id->get_error_message();
  } else {
    $GLOBALS['cvipi_contact_saved_id'] = $id;
  }
  return $fields;
}

add_filter( 'wpforms_entry_email', function ( $enabled, $fields, $entry, $form ) {
  return cvipi_contact_is_form( $form ) ? false : $enabled;
}, 10, 4 );
add_action( 'wpforms_process_complete', function ( $fields, $entry, $form ) {
  if ( cvipi_contact_is_form( $form ) && ! empty( $GLOBALS['cvipi_contact_saved_id'] ) ) cvipi_contact_send_notifications( $GLOBALS['cvipi_contact_saved_id'] );
}, 10, 3 );

add_filter( 'manage_cvipi_message_posts_columns', function () {
  return array( 'cb' => '<input type="checkbox">', 'title' => 'Subject', 'contact_sender' => 'Sender', 'contact_org' => 'Organization', 'contact_state' => 'Status', 'contact_unread' => 'Read Status', 'date' => 'Received' );
} );
add_action( 'manage_cvipi_message_posts_custom_column', function ( $column, $id ) {
  $data = get_post_meta( $id, '_cvipi_contact_data', true );
  if ( 'contact_sender' === $column ) echo esc_html( $data['name'] ?? '' ) . '<br>' . esc_html( $data['email'] ?? '' );
  if ( 'contact_org' === $column ) echo esc_html( $data['organization'] ?? '' );
  if ( 'contact_state' === $column ) echo esc_html( cvipi_contact_states()[get_post_meta( $id, '_cvipi_contact_state', true )] ?? 'New' );
  if ( 'contact_unread' === $column ) echo '1' === get_post_meta( $id, '_cvipi_contact_unread', true ) ? '<strong>Unread</strong>' : 'Read';
}, 10, 2 );
add_filter( 'post_row_actions', function ( $actions, $post ) {
  if ( 'cvipi_message' === $post->post_type ) unset( $actions['inline hide-if-no-js'], $actions['view'] );
  return $actions;
}, 10, 2 );

add_action( 'restrict_manage_posts', function ( $type ) {
  if ( 'cvipi_message' !== $type ) return;
  echo '<label class="screen-reader-text" for="contact-state-filter">Message status</label><select id="contact-state-filter" name="contact_state"><option value="">All statuses</option>';
  foreach ( cvipi_contact_states() as $key => $label ) echo '<option value="' . esc_attr( $key ) . '"' . selected( $_GET['contact_state'] ?? '', $key, false ) . '>' . esc_html( $label ) . '</option>';
  echo '</select><label class="screen-reader-text" for="contact-unread-filter">Read status</label><select id="contact-unread-filter" name="contact_unread"><option value="">All messages</option><option value="1"' . selected( $_GET['contact_unread'] ?? '', '1', false ) . '>Unread</option><option value="0"' . selected( $_GET['contact_unread'] ?? '', '0', false ) . '>Read</option></select>';
} );
add_action( 'pre_get_posts', function ( $query ) {
  if ( ! is_admin() || ! $query->is_main_query() || 'cvipi_message' !== $query->get( 'post_type' ) ) return;
  $meta = (array) $query->get( 'meta_query' );
  $state = isset( $_GET['contact_state'] ) && is_string( $_GET['contact_state'] ) ? sanitize_key( $_GET['contact_state'] ) : '';
  if ( isset( cvipi_contact_states()[$state] ) ) $meta[] = array( 'key' => '_cvipi_contact_state', 'value' => $state );
  if ( isset( $_GET['contact_unread'] ) && in_array( $_GET['contact_unread'], array( '0', '1' ), true ) ) $meta[] = array( 'key' => '_cvipi_contact_unread', 'value' => $_GET['contact_unread'] );
  $query->set( 'meta_query', $meta );
} );
add_action( 'admin_menu', function () {
  if ( ! current_user_can( 'manage_options' ) ) return;
  $unread = new WP_Query( array( 'post_type' => 'cvipi_message', 'post_status' => 'private', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_cvipi_contact_unread', 'meta_value' => '1' ) );
  global $menu;
  foreach ( $menu as &$item ) {
    if ( 'edit.php?post_type=cvipi_message' === $item[2] && $unread->found_posts ) $item[0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $unread->found_posts . '</span><span class="screen-reader-text"> unread messages</span></span>';
  }
  unset( $item );
}, 99 );

add_action( 'load-post.php', function () {
  $id = absint( $_GET['post'] ?? 0 );
  if ( 'edit' === ( $_GET['action'] ?? '' ) && ! isset( $_GET['message'] ) && 'cvipi_message' === get_post_type( $id ) && current_user_can( 'manage_options' ) ) update_post_meta( $id, '_cvipi_contact_unread', '0' );
} );
add_action( 'add_meta_boxes_cvipi_message', function () {
  remove_meta_box( 'submitdiv', 'cvipi_message', 'side' );
  add_meta_box( 'cvipi-contact-details', 'Message', 'cvipi_contact_details_box', 'cvipi_message', 'normal', 'high' );
  add_meta_box( 'cvipi-contact-review', 'Team Review', 'cvipi_contact_review_box', 'cvipi_message', 'side', 'high' );
} );
function cvipi_contact_details_box( $post ) {
  $data = get_post_meta( $post->ID, '_cvipi_contact_data', true );
  echo '<table class="widefat striped"><tbody>';
  foreach ( array( 'name' => 'Name', 'email' => 'Email', 'organization' => 'Organization', 'role' => 'Role / Title', 'subject' => 'Subject' ) as $key => $label ) {
    echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td style="overflow-wrap:anywhere">' . esc_html( $data[$key] ?? '' ) . '</td></tr>';
  }
  echo '<tr><th scope="row">Received</th><td>' . esc_html( get_the_date( 'F j, Y g:i a', $post ) ) . '</td></tr></tbody></table>';
  echo '<div style="white-space:pre-wrap;overflow-wrap:anywhere;margin-top:1.5rem">' . esc_html( $data['message'] ?? '' ) . '</div>';
  if ( ! empty( $data['email'] ) && is_email( $data['email'] ) ) echo '<p><a class="button" href="' . esc_url( 'mailto:' . $data['email'] ) . '">Reply by Email</a></p>';
}
function cvipi_contact_review_box( $post ) {
  wp_nonce_field( 'cvipi_contact_review_' . $post->ID, 'cvipi_contact_review_nonce' );
  echo '<p><label for="contact-state"><strong>Status</strong></label></p><select id="contact-state" name="cvipi_contact_state" class="widefat">';
  foreach ( cvipi_contact_states() as $key => $label ) echo '<option value="' . esc_attr( $key ) . '"' . selected( get_post_meta( $post->ID, '_cvipi_contact_state', true ), $key, false ) . '>' . esc_html( $label ) . '</option>';
  echo '</select><p><label for="contact-notes"><strong>Internal Notes</strong></label></p><textarea id="contact-notes" name="cvipi_contact_notes" rows="8" class="widefat" maxlength="10000">' . esc_textarea( get_post_meta( $post->ID, '_cvipi_contact_notes', true ) ) . '</textarea>';
  echo '<p><label><input type="checkbox" name="cvipi_contact_unread" value="1"' . checked( get_post_meta( $post->ID, '_cvipi_contact_unread', true ), '1', false ) . '> Mark unread</label></p>';
  submit_button( 'Save Review', 'primary', 'save', false );
  echo '<p><a class="submitdelete" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Move to Trash</a></p><hr>';
  cvipi_story_notification_summary( $post->ID );
}
add_action( 'save_post_cvipi_message', function ( $id ) {
  if ( ! current_user_can( 'manage_options' ) || wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) ) return;
  $nonce = $_POST['cvipi_contact_review_nonce'] ?? '';
  if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'cvipi_contact_review_' . $id ) ) return;
  $state = isset( $_POST['cvipi_contact_state'] ) && is_string( $_POST['cvipi_contact_state'] ) ? sanitize_key( $_POST['cvipi_contact_state'] ) : '';
  if ( isset( cvipi_contact_states()[$state] ) ) update_post_meta( $id, '_cvipi_contact_state', $state );
  $notes = isset( $_POST['cvipi_contact_notes'] ) && is_string( $_POST['cvipi_contact_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cvipi_contact_notes'] ) ) : '';
  update_post_meta( $id, '_cvipi_contact_notes', wp_slash( mb_substr( $notes, 0, 10000 ) ) );
  update_post_meta( $id, '_cvipi_contact_unread', isset( $_POST['cvipi_contact_unread'] ) && '1' === $_POST['cvipi_contact_unread'] ? '1' : '0' );
} );
add_filter( 'bulk_actions-edit-cvipi_message', function ( $actions ) {
  unset( $actions['edit'] );
  return $actions + array( 'cvipi_read' => 'Mark read', 'cvipi_unread' => 'Mark unread', 'cvipi_resolved' => 'Mark resolved' );
} );
add_filter( 'handle_bulk_actions-edit-cvipi_message', function ( $redirect, $action, $ids ) {
  if ( ! current_user_can( 'manage_options' ) || ! in_array( $action, array( 'cvipi_read', 'cvipi_unread', 'cvipi_resolved' ), true ) ) return $redirect;
  check_admin_referer( 'bulk-posts' );
  foreach ( $ids as $id ) {
    if ( 'cvipi_message' !== get_post_type( $id ) ) continue;
    update_post_meta( $id, 'cvipi_resolved' === $action ? '_cvipi_contact_state' : '_cvipi_contact_unread', 'cvipi_resolved' === $action ? 'resolved' : ( 'cvipi_unread' === $action ? '1' : '0' ) );
  }
  return $redirect;
}, 10, 3 );
