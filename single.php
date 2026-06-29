<?php
/**
 * Shared single template for Resources, Success Stories, and Events.
 *
 * @package cvipi
 */

get_header( 'general' );
?>

<main id="single-page" class="single-page">
  <?php
  if ( have_posts() ) :
    while ( have_posts() ) :
      the_post();

      $post_id            = get_the_ID();
      $post_type          = get_post_type( $post_id );
      $single_context     = cvipi_get_single_context( $post_id );
      $header_media       = cvipi_get_single_header_media( $post_id );
      $related_resources  = cvipi_get_related_resources( $post_id, 4 );
      ?>

      <article <?php post_class( 'single-page__article single-page__article--' . $single_context['type'] ); ?>>
        <header class="single-hero<?php echo $header_media['has_media'] ? ' single-hero--has-media' : ''; ?>">
          <?php if ( $header_media['video'] ) : ?>
            <video class="single-hero__video" autoplay muted loop playsinline>
              <source src="<?php echo esc_url( $header_media['video'] ); ?>">
            </video>
          <?php elseif ( $header_media['image'] ) : ?>
            <div class="single-hero__image" style="background-image: url('<?php echo esc_url( $header_media['image'] ); ?>');"></div>
          <?php endif; ?>

          <div class="single-hero__screen" aria-hidden="true"></div>

          <div class="single-hero__inner container">
            <div class="single-hero__main">
              <?php cvipi_render_single_breadcrumbs( $single_context ); ?>

              <?php if ( 'success_story' === $post_type ) : ?>
                <?php if ( $single_context['location'] ) : ?>
                  <p class="single-hero__location">
                    <?php echo svg_icon( 'single-hero__location-icon', 'location-2' ); ?>
                    <span><?php echo esc_html( $single_context['location'] ); ?></span>
                  </p>
                <?php endif; ?>
              <?php else : ?>
                <?php cvipi_render_single_pills( $post_id, $single_context ); ?>
              <?php endif; ?>

              <h1 class="single-hero__title"><?php echo wp_kses_post( get_the_title() ); ?></h1>

              <?php if ( has_excerpt() ) : ?>
                <p class="single-hero__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
              <?php endif; ?>
            </div>

            <aside class="single-hero__aside" aria-label="<?php echo esc_attr( $single_context['details_heading'] ); ?>">
              <?php cvipi_render_single_aside_icon( $single_context ); ?>
              <?php cvipi_render_single_details( $post_id, $single_context ); ?>
            </aside>
          </div>
        </header>

        <div class="single-content container">
          <div class="single-content__main">
            <?php cvipi_render_single_resource_video( $post_id ); ?>

            <div class="single-content__body">
              <?php
              the_content();
              wp_link_pages(
                array(
                  'before' => '<div class="page-links">',
                  'after'  => '</div>',
                )
              );
              ?>
            </div>
          </div>

          <aside class="single-content__sidebar" aria-label="<?php echo 'success_story' === $post_type ? esc_attr__( 'More Stories and Related Resources', 'cvipi' ) : esc_attr__( 'Related Resources', 'cvipi' ); ?>">
            <?php if ( 'success_story' === $post_type ) : ?>
              <?php cvipi_render_more_success_stories( $post_id ); ?>
            <?php endif; ?>

            <section class="single-related">
              <h2 class="single-related__heading">Related Resources</h2>
              <?php cvipi_render_related_resource_list( $related_resources ); ?>
            </section>
          </aside>
        </div>
      </article>
      <?php
    endwhile;
  endif;
  ?>
</main>

<?php get_footer(); ?>
