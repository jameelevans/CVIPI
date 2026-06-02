<?php
/**
 * Success Stories landing page.
 *
 * @package cvipi
 */

get_header( 'posts' );

$success_story_query = new WP_Query(
	array(
		'post_type'           => 'success_story',
		'posts_per_page'      => -1,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	)
);
?>

<main id="main-content">
	<section class="stories stories--landing">
		<div class="stories__container">
			<div class="stories__posts">
				<?php if ( $success_story_query->have_posts() ) : ?>
					<?php
					while ( $success_story_query->have_posts() ) :
						$success_story_query->the_post();
						$story_categories = get_the_category();
						$story_category   = ! empty( $story_categories ) ? $story_categories[0]->name : '';
						?>
						<article class="story">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="story__top">
									<?php
									echo wp_get_attachment_image(
										get_post_thumbnail_id(),
										'regular',
										false,
										array(
											'class' => 'story__img',
											'sizes' => '(min-width: 900px) 360px, (min-width: 600px) 33vw, 100vw',
										)
									);
									?>
								</div>
							<?php endif; ?>

							<div class="story__bottom">
								<header class="story__header">
									<?php if ( $story_category ) : ?>
										<p class="story__category"><?php echo esc_html( wp_specialchars_decode( $story_category, ENT_QUOTES ) ); ?></p>
									<?php endif; ?>
									<h2 class="story__heading"><?php echo esc_html( get_the_title() ); ?></h2>
								</header>

								<p class="story__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
								<a href="<?php echo esc_url( get_permalink() ); ?>" class="story__button">Read More</a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p class="stories__subheading">No success stories are published yet.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
