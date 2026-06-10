<?php
/**
 * Success Stories landing page.
 *
 * @package cvipi
 */

get_header( 'posts' );

$active_story_tag   = isset( $_GET['story_tag'] ) ? sanitize_title( wp_unslash( $_GET['story_tag'] ) ) : '';
$success_story_tags = cvipi_get_success_story_tags();
$success_story_query = new WP_Query(
	cvipi_get_success_story_query_args( $active_story_tag )
);
$success_story_posts = $success_story_query->posts;
$featured_stories    = array_slice( $success_story_posts, 0, 4 );
$past_stories        = array_slice( $success_story_posts, 4 );
$past_story_count    = count( $past_stories );
?>

<main id="main-content">
	<section class="stories stories--landing" data-success-stories-archive>
		<div class="stories__container">
			<div class="stories__filters" aria-label="Filter success stories by tag">
				<button
					type="button"
					class="stories__filter <?php echo '' === $active_story_tag ? 'is-active' : ''; ?>"
					data-story-filter=""
					aria-pressed="<?php echo '' === $active_story_tag ? 'true' : 'false'; ?>"
				>
					All Stories
				</button>
				<?php if ( ! empty( $success_story_tags ) ) : ?>
					<?php foreach ( $success_story_tags as $success_story_tag ) : ?>
						<button
							type="button"
							class="stories__filter <?php echo $active_story_tag === $success_story_tag->slug ? 'is-active' : ''; ?>"
							data-story-filter="<?php echo esc_attr( $success_story_tag->slug ); ?>"
							aria-pressed="<?php echo $active_story_tag === $success_story_tag->slug ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $success_story_tag->name ); ?>
						</button>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<p class="stories__count" data-success-stories-count>
				<?php echo esc_html( cvipi_get_success_story_count_label( $success_story_query->found_posts ) ); ?>
			</p>

			<div class="stories__posts" data-success-stories-grid aria-live="polite">
				<?php echo cvipi_render_success_story_cards( $featured_stories ); ?>
			</div>

			<section class="stories__past" aria-labelledby="past-stories-heading">
				<div class="stories__past-header">
					<h2 class="stories__past-heading" id="past-stories-heading">Past Stories</h2>
					<p class="stories__past-count" data-success-stories-past-count>
						<?php echo esc_html( cvipi_get_success_story_count_label( $past_story_count ) ); ?>
					</p>
				</div>

				<div class="stories__past-list" data-success-stories-past-grid aria-live="polite">
					<?php echo cvipi_render_success_story_past_items( $past_stories ); ?>
				</div>
			</section>
		</div>
	</section>

	<section class="stories-impact">
		<div class="stories-impact__media" aria-hidden="true"></div>
		<div class="stories-impact__content">
			<p class="stories-impact__eyebrow">CVIPI Across The Nation</p>
			<h2 class="stories-impact__heading">Real people, real neighborhoods, <em>real impact.</em></h2>
			<p class="stories-impact__description">CVIPI works hand-in-hand with our grantee communities on the ground and in our neighborhoods. We help violence interventionists, outreach workers, and community leaders gain the resources they need to make lasting change.</p>
			<a href="<?php echo esc_url( home_url( '/what-is-cvipi/' ) ); ?>" class="stories-impact__button">
				The Communities We Support <?php echo svg_icon( 'stories-impact__button-icon', 'arrow-right' ); ?>
			</a>
		</div>
	</section>
</main>

<?php
get_footer();
