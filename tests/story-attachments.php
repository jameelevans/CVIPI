<?php
/** Local-only fixtures; notifications are intercepted, never sent. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_contains( home_url(), 'cvipi.local' ) ) throw new RuntimeException( 'Local CVIPI CLI only.' );
define( 'CVIPI_STORY_UPLOAD_DIR', sys_get_temp_dir() . '/cvipi-private-file-test-' . wp_generate_uuid4() );
$files = array();
$post_id = 0;
$checks = 0;
$original_user = get_current_user_id();
$qa_email = 'attachment-qa-' . wp_generate_uuid4() . '@example.org';
$assert = function ( $condition, $message ) use ( &$checks ) {
  if ( ! $condition ) throw new RuntimeException( $message );
  $checks++;
  echo 'PASS: ' . $message . PHP_EOL;
};
$fixture = function ( $name, $contents ) use ( &$files ) {
  $path = tempnam( sys_get_temp_dir(), 'cvipi-file-' );
  file_put_contents( $path, $contents );
  $files[] = $path;
  return array( 'name' => $name, 'tmp_name' => $path, 'size' => filesize( $path ), 'error' => UPLOAD_ERR_OK );
};
$word = function ( $entries = array() ) use ( &$files ) {
  $path = tempnam( sys_get_temp_dir(), 'cvipi-word-' );
  $files[] = $path;
  $zip = new ZipArchive();
  $zip->open( $path, ZipArchive::CREATE | ZipArchive::OVERWRITE );
  $zip->addFromString( '[Content_Types].xml', '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>' );
  $zip->addFromString( 'word/document.xml', '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Local QA only</w:t></w:r></w:p></w:body></w:document>' );
  foreach ( $entries as $name => $value ) $zip->addFromString( $name, $value );
  $zip->close();
  return array( 'name' => 'test.docx', 'tmp_name' => $path, 'size' => filesize( $path ), 'error' => UPLOAD_ERR_OK );
};
$captured = array();
$capture_mail = function ( $return, $mail ) use ( &$captured ) { $captured[] = $mail; return true; };
add_filter( 'pre_wp_mail', $capture_mail, 10, 2 );
$settings = function () { return array_merge( cvipi_story_notification_defaults(), array( 'admin_recipients' => 'reviewer@example.org', 'admin_enabled' => '1', 'submitter_enabled' => '1' ) ); };
add_filter( 'pre_option_cvipi_story_notifications', $settings );
try {
  $assert( null === cvipi_story_attachment_validate( array( 'name' => '', 'tmp_name' => '', 'size' => 0, 'error' => UPLOAD_ERR_NO_FILE ) ), 'No file is optional' );
  $text = $fixture( 'test.txt', "CVIPI test document.\nThis is a private review attachment.\n" );
  $assert( 'txt' === cvipi_story_attachment_validate( $text )['type'], 'Plain UTF-8 TXT accepted' );
  $pdf = $fixture( 'test.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n" );
  $assert( 'pdf' === cvipi_story_attachment_validate( $pdf )['type'], 'PDF MIME and signatures accepted' );
  $assert( ! is_wp_error( cvipi_story_attachment_validate( $word() ) ), 'Macro-free DOCX package accepted' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'fake.pdf', 'Not actually a PDF document.' ) ) ), 'Renamed text is not a PDF' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'payload.txt', '<?php echo "unsafe"; ?>' ) ) ), 'PHP disguised as text rejected' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'payload.pdf.php', 'Sample text' ) ) ), 'Double-extension executable rejected' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'legacy.doc', 'Sample text' ) ) ), 'Legacy binary Word files rejected' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'empty.txt', '' ) ) ), 'Empty upload rejected' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'large.txt', str_repeat( 'A', cvipi_story_attachment_limit() + 1 ) ) ) ), 'Oversized uploads rejected' );
  $assert( is_wp_error( cvipi_story_attachment_validate( $fixture( 'binary.txt', "Text\0binary" ) ) ), 'Binary data disguised as TXT rejected' );
  $assert( is_wp_error( cvipi_story_attachment_validate( array( 'name' => array() ) ) ), 'Malformed/multiple-file arrays rejected' );
  foreach ( array( 'word/vbaProject.bin', 'word/embeddings/file.bin', '../payload.txt', 'word/activex/activeX1.xml' ) as $entry ) {
    $assert( is_wp_error( cvipi_story_attachment_validate( $word( array( $entry => 'untrusted' ) ) ) ), 'Unsafe DOCX package entry rejected: ' . $entry );
  }
  $assert( is_wp_error( cvipi_story_attachment_validate( $word( array( 'word/media/huge.bin' => str_repeat( '0', 26 * MB_IN_BYTES ) ) ) ) ), 'Compressed ZIP bomb rejected by expanded-size limits' );
  $directory = cvipi_story_attachment_directory( true );
  $assert( ! is_wp_error( $directory ) && ! str_starts_with( $directory, realpath( ABSPATH ) . '/' ), 'Storage is outside the website root' );
  $assert( 0700 === ( fileperms( $directory ) & 0777 ), 'Private directory has owner-only permissions' );
  $metadata = cvipi_story_attachment_validate( $text );
  $metadata['file'] = wp_generate_uuid4() . '.txt';
  $stored_path = $directory . '/' . $metadata['file'];
  file_put_contents( $stored_path, file_get_contents( $text['tmp_name'] ) );
  chmod( $stored_path, 0600 );
  $files[] = $stored_path;
  $post_id = wp_insert_post( array( 'post_type' => 'success_story', 'post_status' => 'pending', 'post_title' => 'Private attachment integration QA', 'meta_input' => array( '_cvipi_submitted_at' => current_time( 'mysql', true ), '_cvipi_submission_type' => 'content', '_cvipi_submitter_email' => $qa_email, '_cvipi_submission_attachment' => $metadata ) ) );
  $assert( $stored_path === cvipi_story_attachment_path( $metadata ), 'Review metadata resolves only the private file' );
  $assert( false === cvipi_story_attachment_path( array( 'file' => '../wp-config.php' ) ), 'Traversal cannot read unrelated files' );
  wp_set_current_user( 0 );
  $assert( ! cvipi_story_attachment_can_download( $post_id ), 'Anonymous visitors cannot download a file' );
  $subscriber = new WP_User( 0 );
  $subscriber->roles = array( 'subscriber' );
  $assert( ! $subscriber->has_cap( 'edit_post', $post_id ), 'Subscriber permission is insufficient' );
  $admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
  wp_set_current_user( $admins[0] );
  $assert( cvipi_story_attachment_can_download( $post_id ), 'An editor with post-edit permission can review the file' );
  cvipi_story_send_notifications( $post_id );
  $assert( 2 === count( $captured ), 'Both notifications generated without sending real email' );
  $admin_mail = array_values( array_filter( $captured, function ( $mail ) { return in_array( 'reviewer@example.org', (array) $mail['to'], true ); } ) )[0];
  $assert( str_contains( $admin_mail['message'], 'TXT attachment received' ), 'Team email names the attachment type' );
  foreach ( $captured as $mail ) $assert( empty( $mail['attachments'] ), 'No uploaded file attached to notification email' );
  $assert( ! str_contains( $admin_mail['message'], $metadata['file'] ) && ! str_contains( $admin_mail['message'], $directory ), 'Email contains no private filename/path or public file link' );
  wp_delete_post( $post_id, true );
  $post_id = 0;
  $assert( ! file_exists( $stored_path ), 'Deleting a submission permanently removes its own private file' );
  echo 'Success: ' . $checks . ' attachment integration checks passed.' . PHP_EOL;
} finally {
  if ( $post_id ) wp_delete_post( $post_id, true );
  foreach ( $files as $path ) if ( file_exists( $path ) ) unlink( $path );
  if ( is_dir( CVIPI_STORY_UPLOAD_DIR ) ) rmdir( CVIPI_STORY_UPLOAD_DIR );
  remove_filter( 'pre_wp_mail', $capture_mail, 10 );
  remove_filter( 'pre_option_cvipi_story_notifications', $settings );
  delete_transient( 'cvipi_receipt_' . hash_hmac( 'sha256', strtolower( $qa_email ), wp_salt( 'nonce' ) ) );
  wp_set_current_user( $original_user );
}
