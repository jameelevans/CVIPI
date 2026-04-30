<?php
/**
 * * The template for displaying the about page
 *
 * @package cvipi
 */

 get_header('general');

?>
	<main id="main-content">
    <section class="error">
      <img class="error__image" src="<?php echo esc_url(get_theme_file_uri('assets/img/404.png')); ?>" alt="404">
      <h1 class="error__header">Page not found</h1>
      <p class="error__p">Sorry the page you requested can not be found. Please go back to the home page.</p> 
      <a class="error__home"href="<?php echo esc_url( home_url('/')); ?>">Go Home</a>
    </section>
	</main>
<?php get_footer(); ?>
