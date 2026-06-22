<?php
/**
 * Single post/page template placeholder.
 *
 * This template currently contains placeholder content. Keep this comment in
 * place so future developers know the visible text below is not production copy.
 *
 * @package cvipi
 */

get_header('general');
?>

<main id="single-page">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <section class="single-resource">
      <h1 class="h1__heading"><?php the_title(); ?></h1>

      <?php
      // Built-in WordPress Categories (text only, no links)
      $cats = get_the_terms( get_the_ID(), 'category' );
      if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
        $cat_names = implode( ', ', wp_list_pluck( $cats, 'name' ) );
        echo '<p class="single-resource__category">Category: ' . esc_html( $cat_names ) . '</p>';
      }
      ?>

      <p class="single-resource__date">Date Published: <?php echo esc_html( cvipi_get_resource_display_date( get_the_ID(), 'F j, Y' ) ); ?></p>

      <?php
      $resource_length = cvipi_get_resource_meta_value( get_the_ID(), 'resource_length' );
      $resource_link   = cvipi_get_resource_link_data( get_the_ID() );
      ?>

      <?php if ( $resource_length ) : ?>
        <p class="single-resource__length">Length: <?php echo esc_html( $resource_length ); ?></p>
      <?php endif; ?>

      <?php if ( ! empty( $resource_link['url'] ) ) : ?>
        <p class="single-resource__action">
          <a href="<?php echo esc_url( $resource_link['url'] ); ?>" <?php echo $resource_link['target'] ? 'target="' . esc_attr( $resource_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
            <?php echo esc_html( $resource_link['label'] ); ?>
          </a>
        </p>
      <?php endif; ?>

      <div class="single-resource__content">
        <?php
          the_content();
          wp_link_pages( [
            'before' => '<div class="page-links">',
            'after'  => '</div>',
          ] );
        ?>
      </div>

     

      

    </section><!-- closes .single-resource (removed the extra one) -->



  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
