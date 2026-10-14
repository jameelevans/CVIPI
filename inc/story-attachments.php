<?php
/** Optional submission files are private, never Media Library/public attachments. */
defined( 'ABSPATH' ) || exit;

function cvipi_story_attachment_limit() {
  return min( 5 * MB_IN_BYTES, wp_max_upload_size() );
}

function cvipi_story_attachment_types() {
  return array( 'docx' => 'Word (.docx)', 'pdf' => 'PDF', 'txt' => 'TXT' );
}

function cvipi_story_attachment_validate( $file ) {
  $invalid = new WP_Error( 'attachment', 'Choose a valid Word (.docx), PDF, or TXT file. Macro-enabled and embedded-file documents are not accepted.' );
  if ( ! is_array( $file ) || ! isset( $file['name'], $file['tmp_name'], $file['error'], $file['size'] ) || ! is_string( $file['name'] ) || ! is_string( $file['tmp_name'] ) || ! is_numeric( $file['error'] ) || ! is_numeric( $file['size'] ) ) return $invalid;
  if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) return null;
  if ( UPLOAD_ERR_OK !== (int) $file['error'] ) return new WP_Error( 'attachment', 'The file could not upload. Choose a file within the size limit and try again.' );
  $size = is_file( $file['tmp_name'] ) ? filesize( $file['tmp_name'] ) : 0;
  if ( ! $size || $size !== (int) $file['size'] || $size > cvipi_story_attachment_limit() ) return new WP_Error( 'attachment', 'Choose a nonempty file up to ' . size_format( cvipi_story_attachment_limit() ) . '.' );
  $name = sanitize_file_name( $file['name'] );
  $extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
  if ( ! isset( cvipi_story_attachment_types()[$extension] ) || ! class_exists( 'finfo' ) ) return $invalid;
  $mime = ( new finfo( FILEINFO_MIME_TYPE ) )->file( $file['tmp_name'] );
  $mimes = array( 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'pdf' => 'application/pdf', 'txt' => 'text/plain' );
  $checked = wp_check_filetype_and_ext( $file['tmp_name'], $name, $mimes );
  if ( $checked['ext'] !== $extension || $checked['type'] !== $mimes[$extension] ) return $invalid;
  if ( 'docx' === $extension ) {
    if ( ! in_array( $mime, array( $mimes['docx'], 'application/zip', 'application/octet-stream' ), true ) || ! cvipi_story_attachment_is_docx( $file['tmp_name'] ) ) return $invalid;
  } elseif ( 'pdf' === $extension ) {
    if ( 'application/pdf' !== $mime ) return $invalid;
    $stream = fopen( $file['tmp_name'], 'rb' );
    if ( ! $stream ) return $invalid;
    $header = fread( $stream, 8 );
    fseek( $stream, max( 0, $size - 2048 ) );
    $end = stream_get_contents( $stream );
    fclose( $stream );
    if ( ! preg_match( '/^%PDF-1\.[0-9]|^%PDF-2\.0/', $header ) || ! str_contains( $end, '%%EOF' ) ) return $invalid;
  } else {
    $text = file_get_contents( $file['tmp_name'] );
    if ( 'text/plain' !== $mime || false === $text || ! mb_check_encoding( $text, 'UTF-8' ) || preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $text ) ) return $invalid;
  }
  return array( 'name' => mb_substr( $name, 0, 180 ), 'type' => $extension, 'size' => $size );
}

function cvipi_story_attachment_is_docx( $path ) {
  if ( ! class_exists( 'ZipArchive' ) ) return false;
  $zip = new ZipArchive();
  if ( true !== $zip->open( $path ) ) return false;
  try {
    if ( $zip->numFiles > 1000 ) return false;
    $expanded = 0;
    for ( $index = 0; $index < $zip->numFiles; $index++ ) {
      $entry = $zip->statIndex( $index );
      if ( ! $entry || preg_match( '#(^/|\\\\|(^|/)\.\.(/|$)|vba|activex/|embeddings/|\.(exe|dll|js|vbs|ps1|docm)$)#i', $entry['name'] ) || ! empty( $entry['encryption_method'] ) ) return false;
      $expanded += $entry['size'];
      if ( $entry['size'] > 10 * MB_IN_BYTES || $expanded > 25 * MB_IN_BYTES ) return false;
    }
    $types = $zip->getFromName( '[Content_Types].xml', MB_IN_BYTES + 1 );
    $document = $zip->getFromName( 'word/document.xml', 10 * MB_IN_BYTES + 1 );
    if ( false === $types || false === $document || strlen( $types ) > MB_IN_BYTES || preg_match( '/<!DOCTYPE|<!ENTITY|macroEnabled|vbaProject/i', $types . $document ) ) return false;
    $previous = libxml_use_internal_errors( true );
    try {
      $xml = simplexml_load_string( $types, 'SimpleXMLElement', LIBXML_NONET );
      $body = simplexml_load_string( $document, 'SimpleXMLElement', LIBXML_NONET );
      if ( false === $xml || false === $body || 'document' !== $body->getName() ) return false;
      foreach ( $xml->children() as $type ) {
        if ( '/word/document.xml' === (string) $type['PartName'] && 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml' === (string) $type['ContentType'] ) return true;
      }
      return false;
    } finally {
      libxml_clear_errors();
      libxml_use_internal_errors( $previous );
    }
  } finally {
    $zip->close();
  }
}

function cvipi_story_attachment_directory( $create = false ) {
  $web_roots = array( realpath( ABSPATH ) );
  if ( ! empty( $_SERVER['DOCUMENT_ROOT'] ) && realpath( $_SERVER['DOCUMENT_ROOT'] ) ) $web_roots[] = realpath( $_SERVER['DOCUMENT_ROOT'] );
  // Include a hosting public root even when WordPress lives in a staging subfolder.
  for ( $parent = realpath( ABSPATH ); $parent && dirname( $parent ) !== $parent; $parent = dirname( $parent ) ) {
    if ( in_array( basename( $parent ), array( 'public_html', 'htdocs', 'wwwroot', 'www', 'public' ), true ) ) $web_roots[] = $parent;
  }
  usort( $web_roots, function ( $a, $b ) { return strlen( $a ) <=> strlen( $b ); } );
  $directory = defined( 'CVIPI_STORY_UPLOAD_DIR' ) ? CVIPI_STORY_UPLOAD_DIR : dirname( $web_roots[0] ) . '/.cvipi-story-files-' . substr( hash( 'sha256', home_url( '/' ) ), 0, 12 );
  if ( ! is_string( $directory ) || ! str_starts_with( $directory, '/' ) ) return new WP_Error( 'storage', 'Private file storage is unavailable. Please contact the CVIPI team.' );
  foreach ( $web_roots as $root ) {
    $normalized = trailingslashit( wp_normalize_path( $directory ) );
    if ( str_starts_with( $normalized, trailingslashit( wp_normalize_path( $root ) ) ) ) return new WP_Error( 'storage', 'Private file storage must be outside the public website folder.' );
  }
  if ( ! is_dir( $directory ) && ( ! $create || ! wp_mkdir_p( $directory ) ) ) return new WP_Error( 'storage', 'Private file storage is unavailable. Your text is still here; please try without a file or contact the team.' );
  $directory = realpath( $directory );
  foreach ( $web_roots as $root ) {
    if ( str_starts_with( trailingslashit( $directory ), trailingslashit( $root ) ) ) return new WP_Error( 'storage', 'Private file storage must be outside the public website folder.' );
  }
  if ( $create && ( ! chmod( $directory, 0700 ) || ! is_writable( $directory ) ) ) return new WP_Error( 'storage', 'Private file storage is unavailable. Please try without a file or contact the team.' );
  return $directory;
}

function cvipi_story_attachment_store( $file, $metadata, $post_id ) {
  $directory = cvipi_story_attachment_directory( true );
  if ( is_wp_error( $directory ) ) return $directory;
  $metadata['file'] = wp_generate_uuid4() . '.' . $metadata['type'];
  $path = $directory . '/' . $metadata['file'];
  if ( ! move_uploaded_file( $file['tmp_name'], $path ) ) return new WP_Error( 'storage', 'We could not save your file. Your text is still here; please try again.' );
  if ( ! chmod( $path, 0600 ) || ! update_post_meta( $post_id, '_cvipi_submission_attachment', $metadata ) ) {
    wp_delete_file( $path );
    return new WP_Error( 'storage', 'We could not save your file privately. Please try again.' );
  }
  return $metadata;
}

function cvipi_story_attachment_path( $metadata ) {
  if ( ! is_array( $metadata ) || ! preg_match( '/^[a-f0-9-]{36}\.(docx|pdf|txt)$/', $metadata['file'] ?? '' ) ) return false;
  $directory = cvipi_story_attachment_directory();
  if ( is_wp_error( $directory ) ) return false;
  $path = $directory . '/' . $metadata['file'];
  return ! is_link( $path ) && is_file( $path ) ? $path : false;
}

function cvipi_story_attachment_can_download( $id ) {
  return 'success_story' === get_post_type( $id ) && current_user_can( 'edit_post', $id );
}

add_action( 'admin_post_cvipi_story_attachment', function () {
  $id = absint( $_GET['post'] ?? 0 );
  if ( ! cvipi_story_attachment_can_download( $id ) ) wp_die( 'You cannot access this submission file.', '', array( 'response' => 403 ) );
  check_admin_referer( 'cvipi_story_attachment_' . $id );
  $metadata = get_post_meta( $id, '_cvipi_submission_attachment', true );
  $path = cvipi_story_attachment_path( $metadata );
  if ( ! $path || ! is_readable( $path ) ) wp_die( 'This submission file is unavailable.', '', array( 'response' => 404 ) );
  nocache_headers();
  header( 'Content-Type: application/octet-stream' );
  header( 'X-Content-Type-Options: nosniff' );
  header( 'X-Robots-Tag: noindex, nofollow' );
  header( "Content-Security-Policy: sandbox; default-src 'none'" );
  header( 'Content-Disposition: attachment; filename="submission.' . $metadata['type'] . '"; filename*=UTF-8\'\'' . rawurlencode( $metadata['name'] ) );
  header( 'Content-Length: ' . filesize( $path ) );
  readfile( $path );
  exit;
} );

function cvipi_story_attachment_admin_details( $id ) {
  $metadata = get_post_meta( $id, '_cvipi_submission_attachment', true );
  if ( ! is_array( $metadata ) || ! cvipi_story_attachment_can_download( $id ) ) return;
  $type = cvipi_story_attachment_types()[$metadata['type'] ?? ''] ?? 'File';
  $url = wp_nonce_url( add_query_arg( array( 'action' => 'cvipi_story_attachment', 'post' => $id ), admin_url( 'admin-post.php' ) ), 'cvipi_story_attachment_' . $id );
  echo '<p><strong>Attachment:</strong> ' . esc_html( $type . ' - ' . size_format( $metadata['size'] ) ) . '<br><a href="' . esc_url( $url ) . '">Download ' . esc_html( $metadata['name'] ) . '</a></p><p>Private review file. Not published with the story. Treat uploaded documents as untrusted and scan before opening; file validation is not an antivirus scan.</p>';
}

add_action( 'before_delete_post', function ( $id ) {
  if ( 'success_story' !== get_post_type( $id ) ) return;
  $path = cvipi_story_attachment_path( get_post_meta( $id, '_cvipi_submission_attachment', true ) );
  if ( $path ) wp_delete_file( $path );
} );
