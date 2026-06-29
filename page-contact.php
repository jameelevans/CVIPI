<?php
/**
 * Contact page.
 *
 * @package cvipi
 */

get_header( 'general' );

$faq_query = new WP_Query(
	array(
		'post_type'              => 'faq',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
		),
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	)
);
?>

<main id="main-content" class="contact-page">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<section class="contact-intro" aria-labelledby="contact-heading">
				<div class="contact-intro__container container">
					<div class="contact-intro__content">
						<p class="contact-intro__eyebrow">How Can We Help?</p>
						<h1 class="contact-intro__heading" id="contact-heading">We&rsquo;d love to <em>hear from you.</em></h1>
						<p class="contact-intro__description">Whether you&rsquo;re a current grantee, a prospective applicant, a researcher, or a community stakeholder, our team is ready to connect you with the right people and resources.</p>

						<div class="contact-intro__cards">
							<article class="contact-intro__card">
								<div class="contact-intro__icon" aria-hidden="true">
									<?php echo svg_icon( 'contact-intro__svg', 'clock' ); ?>
								</div>
								<div class="contact-intro__card-content">
									<h2 class="contact-intro__card-heading">Response Time</h2>
									<p class="contact-intro__card-text">We typically respond within 2-3 business days. Urgent TA requests are prioritized and may receive a faster response.</p>
								</div>
							</article>

							<article class="contact-intro__card">
								<div class="contact-intro__icon" aria-hidden="true">
									<?php echo svg_icon( 'contact-intro__svg', 'users2' ); ?>
								</div>
								<div class="contact-intro__card-content">
									<h2 class="contact-intro__card-heading">Email Directly</h2>
									<p class="contact-intro__card-text">You can also reach us at <a href="mailto:cvipi@iir.com">cvipi@iir.com</a> for general inquiries.</p>
								</div>
							</article>
						</div>
					</div>

					<div class="contact-intro__form" aria-label="Contact form">
						<?php if ( trim( get_the_content() ) ) : ?>
							<?php the_content(); ?>
						<?php else : ?>
							<div class="contact-intro__form-empty">
								<p>Add a WPForms block or shortcode to this page to display the contact form.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
			<?php
		endwhile;
	endif;
	?>

	<section class="contact-faqs" aria-labelledby="contact-faqs-heading">
		<div class="contact-faqs__container container">
			<div class="contact-faqs__header">
				<p class="contact-faqs__eyebrow">Common Questions</p>
				<h2 class="contact-faqs__heading" id="contact-faqs-heading">Frequently Asked Questions</h2>
				<p class="contact-faqs__description">Quick answers to the questions we hear most often from grantees, partners, and stakeholders.</p>
			</div>

			<div class="contact-faqs__list" data-contact-faqs>
				<?php if ( $faq_query->have_posts() ) : ?>
					<?php
					$faq_index = 0;
					while ( $faq_query->have_posts() ) :
						$faq_query->the_post();
						$faq_id      = 'contact-faq-' . get_the_ID();
						$panel_id    = $faq_id . '-panel';
						$button_id   = $faq_id . '-button';
						$is_open     = 0 === $faq_index;
						?>
						<article class="contact-faqs__item<?php echo $is_open ? ' contact-faqs__item--is-open' : ''; ?>">
							<h3 class="contact-faqs__item-heading">
								<button
									class="contact-faqs__trigger"
									type="button"
									id="<?php echo esc_attr( $button_id ); ?>"
									aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
									aria-controls="<?php echo esc_attr( $panel_id ); ?>"
									data-faq-trigger
								>
									<span class="contact-faqs__question"><?php the_title(); ?></span>
									<span class="contact-faqs__plus" aria-hidden="true"></span>
								</button>
							</h3>
							<div
								class="contact-faqs__panel"
								id="<?php echo esc_attr( $panel_id ); ?>"
								role="region"
								aria-labelledby="<?php echo esc_attr( $button_id ); ?>"
								data-faq-panel
								<?php echo $is_open ? 'style="height: auto;"' : 'hidden'; ?>
							>
								<div class="contact-faqs__answer">
									<?php the_content(); ?>
								</div>
							</div>
						</article>
						<?php $faq_index++; ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p class="contact-faqs__empty">FAQs will appear here once they are published.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="stories-impact">
		<div class="stories-impact__content">
			<p class="stories-impact__eyebrow">What Is CVIPI?</p>
			<h2 class="stories-impact__heading">Your Resource for <em>safer communities</em></h2>
			<p class="stories-impact__description">The Community Violence Intervention and Prevention Initiative (CVIPI) is a national platform that equips organizations with the training, technical assistance, and evidence-based resources they need to reduce violence and build resilient neighborhoods.</p>
			<a href="<?php echo esc_url( home_url( '/what-is-cvipi/' ) ); ?>" class="stories-impact__button">
				Who We Are
			</a>
		</div>
		<div class="stories-impact__media" aria-hidden="true"></div>
	</section>
</main>

<?php
get_footer();
