<?php
/** WP CLI: wp eval-file tools/install-digital-style-guide.php /absolute/checklist.docx */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) throw new RuntimeException( 'Run through WP CLI.' );
$source = isset( $args[0] ) ? realpath( $args[0] ) : false;
if ( ! $source || ! is_readable( $source ) || 'docx' !== strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) ) throw new RuntimeException( 'Provide the approved DOCX checklist.' );
$zip = new ZipArchive();
if ( true !== $zip->open( $source, ZipArchive::RDONLY ) || false === $zip->locateName( '[Content_Types].xml' ) || false === $zip->locateName( 'word/document.xml' ) || false !== $zip->locateName( 'word/vbaProject.bin' ) ) throw new RuntimeException( 'The checklist must be a valid, macro-free Word document.' );
$zip->close();
$page = get_page_by_path( 'cvipi-digital-style-guide', OBJECT, 'page' );
if ( $page && 'page-digital-style-guide.php' !== get_post_meta( $page->ID, '_wp_page_template', true ) ) throw new RuntimeException( 'This slug already belongs to another page.' );
$directory = cvipi_grantee_template_directory( true );
if ( ! $directory ) throw new RuntimeException( 'Approved private template storage is unavailable.' );
$target = $directory . '/CVIPI-508-Compliance-Checklist.docx';
if ( is_link( $target ) ) throw new RuntimeException( 'Refusing a symlink download target.' );
$hash = hash_file( 'sha256', $source );
if ( ! is_file( $target ) || hash_file( 'sha256', $target ) !== $hash ) {
  $temporary = $directory . '/.guide-checklist-' . wp_generate_uuid4() . '.tmp';
  if ( ! copy( $source, $temporary ) || ! chmod( $temporary, 0600 ) || hash_file( 'sha256', $temporary ) !== $hash || ! rename( $temporary, $target ) ) throw new RuntimeException( 'Checklist installation failed.' );
}
wp_get_theme()->cache_delete();
if ( ! $page ) {
  $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'CVIPI Digital Style Guide', 'post_name' => 'cvipi-digital-style-guide', 'post_content' => '', 'meta_input' => array( '_wp_page_template' => 'page-digital-style-guide.php' ) ), true );
  if ( is_wp_error( $id ) ) throw new RuntimeException( $id->get_error_message() );
  $page = get_post( $id );
}
update_post_meta( $page->ID, '_cvipi_seo_noindex', '1' );
update_post_meta( $page->ID, '_cvipi_seo_title', 'CVIPI Digital Style Guide' );
update_post_meta( $page->ID, '_cvipi_seo_description', 'CVIPI brand guidance, co-branded templates, voice, colors, typography, imagery, and accessibility resources for grantees.' );
flush_rewrite_rules( false );
WP_CLI::success( wp_json_encode( array( 'page_id' => $page->ID, 'url' => get_permalink( $page ), 'checklist_sha256' => $hash, 'checklist_bytes' => filesize( $target ) ), JSON_UNESCAPED_SLASHES ) );
