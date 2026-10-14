<?php
/**
 * Template Name: Grantee Template Download (Unlisted)
 * @package cvipi
 */
$template = cvipi_grantee_template();
$path = cvipi_grantee_template_path( $template );
get_header( 'general' );
?>
<main id="main-content" class="grantee-template">
  <section class="grantee-template__intro" aria-labelledby="template-title">
    <div class="grantee-template__inner">
      <p class="grantee-template__eyebrow">CVIPI Grantee Templates</p>
      <h1 id="template-title"><?php the_title(); ?></h1>
      <?php if ( $template ) : ?>
        <p class="grantee-template__description"><?php echo esc_html( $template['description'] ); ?></p>
        <dl class="grantee-template__details">
          <div><dt>Format</dt><dd><?php echo esc_html( $template['format'] . ' (.' . pathinfo( $template['filename'], PATHINFO_EXTENSION ) . ')' ); ?></dd></div>
          <?php if ( $path ) : ?><div><dt>File size</dt><dd><?php echo esc_html( size_format( filesize( $path ), 0 ) ); ?></dd></div><?php endif; ?>
        </dl>
        <?php if ( $path ) : ?>
          <a class="grantee-template__download" href="<?php echo esc_url( cvipi_grantee_template_url( $template ) ); ?>" download="<?php echo esc_attr( $template['filename'] ); ?>" title="<?php echo esc_attr( 'Download ' . $template['title'] ); ?>"><i data-grantee-icon="download" aria-hidden="true"></i><span><?php echo esc_html( 'Download ' . ( 'Microsoft Word' === $template['format'] ? 'Word' : 'PowerPoint' ) . ' Template' ); ?></span></a>
        <?php else : ?><p class="grantee-template__description">This template is currently unavailable. Please contact the CVIPI team for a copy.</p><?php endif; ?>
      <?php else : ?><p class="grantee-template__description">Please contact the CVIPI team for this template.</p><?php endif; ?>
    </div>
  </section>
  <section class="grantee-template__help" aria-labelledby="template-help-title">
    <div class="grantee-template__inner">
      <h2 id="template-help-title">Questions about the template?</h2>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact the CVIPI team <i data-grantee-icon="arrow-up-right" aria-hidden="true"></i></a>
    </div>
  </section>
</main>
<?php get_footer(); ?>
