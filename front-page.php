<?php
/**
 * * The template for displaying the front page
 *
 * @package cvipi
 */

get_header();

?>
	<main id="main-content">
		<!-- About section: two alternating feature rows introducing CVIPI and its national reach. -->
		<section class="about">
			<div class="about__container">
				<div class="about__content">
					<p class="about__subheader">What is CVIPI?</p>
					<h2 class="about__heading">Your Resource for <em>Community-Led Safety</em></h2>
					<p class="about__description">The Community Violence Intervention and Prevention Initiative CVIPI) is a national
					platform that equips organizations with the training, technical assistance, and evidencebased
					resources they need to reduce violence and build resilient neighborhoods.</p>
					<a href="" class="btn__outline-white">Who We Are</a>
				</div>
				<div class="about__image-wrap">
					<div class="about__image top">&nbsp;</div>
				</div>
			</div>

			<div class="about__container">
				<div class="about__image-wrap">
					<div class="about__image bottom">&nbsp;</div>
				</div>
				<div class="about__content">
					<p class="about__subheader">CVIPI Across the Nation</p>
					<h2 class="about__heading">Real people, real <em>neighborhoods, real impact.</em></h2>
					<p class="about__description">CVIPI works hand-in-hand with our grantee organizations throughout our country and our
					communities. We help our violence interrupters, outreach workers, and community
					leaders gain the resources they need to make lasting change.</p>
					<a href="" class="btn__outline-white">The Communities We Impact</a>
				</div>
			</div>
			
		</section>
		
		<!-- Highlights section: paired event and resource teasers for the homepage. -->
		<section class="highlights">
			<div class="highlights__section">

				<header class="highlights__header">
					<p class="highlights__spotlight">Browse by Category</p>
					<h2 class="highlights__heading">Find what <em>you need</em></h2>
					<p class="highlights__subheading">Explore our library organized by resource type, or search across everything below.</p>
				</header>

				<div class="highlights__container">

					<div class="highlights__resources">
						<div class="highlights__top">
							<h2 class="highlights__section-heading">Resources</h2>
							<a href="" class="highlights__link">Browse Library <?php echo svg_icon('highlights__icon', 'arrow-right');?></a>
						</div>
						<div class="resources">
							<a href="" class="resource">
								<article class="resource__container">
									
									<div class="resource__content">
										<header class="resource__header">
											<div class="resource__type">
												<?php echo svg_icon('resource__icon', 'report1');?>
											</div>
											<p class="resource__category">24 resources</p>
											<h3 class="resource__title">Archived Events &amp; Webinars</h3>
										</header>

										<p class="resource__details">
											Recordings from past CVIPI webinars, peer learning circles, conferences, and panel discussions.</p>
										<p class="resource__cta">Browse recordings <?php echo svg_icon('resource__cta-icon', 'arrow-right');?></p>
									</div>
									
								</article>
							</a>

							<a href="" class="resource">
								<article class="resource__container">
									
									<div class="resource__content">
										<header class="resource__header">
											<div class="resource__type">
										<?php echo svg_icon('resource__icon', 'file1');?>
									</div>
											<p class="resource__category">Toolkit</p>
											<h3 class="resource__title">Title Here</h3>
										</header>

										<p class="resource__details">
											Recordings from past CVIPI webinars, peer learning circles, conferences, and panel discussions.</p>
										<p class="resource__cta">Browse recordings <?php echo svg_icon('resource__cta-icon', 'arrow-right');?></p>
									</div>
									
								</article>
							</a>

							<a href="" class="resource">
								<article class="resource__container">
									
									<div class="resource__content">
										<header class="resource__header">
											<div class="resource__type">
										<?php echo svg_icon('resource__icon', 'play1');?>
									</div>
											<p class="resource__category">Toolkit</p>
											<h3 class="resource__title">Title Here</h3>
										</header>

										<p class="resource__details">
											Recordings from past CVIPI webinars, peer learning circles, conferences, and panel discussions.</p>
										<p class="resource__cta">Browse recordings <?php echo svg_icon('resource__cta-icon', 'arrow-right');?></p>
									</div>
									
								</article>
							</a>

							<a href="" class="resource">
								<article class="resource__container">
									
									<div class="resource__content">
										<header class="resource__header">
											<div class="resource__type">
										<?php echo svg_icon('resource__icon', 'gear');?>
									</div>
											<p class="resource__category">Toolkit</p>
											<h3 class="resource__title">Title Here</h3>
										</header>

										<p class="resource__details">
											Recordings from past CVIPI webinars, peer learning circles, conferences, and panel discussions.</p>
										<p class="resource__cta">Browse recordings <?php echo svg_icon('resource__cta-icon', 'arrow-right');?></p>
									</div>
									
								</article>
							</a>
						</div>
					</div>

					<div class="highlights__events">
						<div class="highlights__top">
							
							<h2 class="highlights__section-heading">Upcoming Events</h2>
							
							<a href="" class="highlights__link">View All <?php echo svg_icon('highlights__icon', 'arrow-right');?></a>
						</div>
						<div class="events">
							<a href="" class="event">
								<article class="event__container">
									<time
										class="event__date"
										datetime="2026-05-07T14:00:00-04:00"
										>
										DATE May 07, 2026 · 2:00 PM ET
									</time>
									<header class="event__header">
										<h3 class="event__heading">
											Editorial Titles
										</h3>
									</header>

									<p class="event__excerpt">
										Forem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit
										interdum, ac aliquet odio mattis.
									</p>

									<ul class="event__tags" aria-label="Event tags">
										<li class="event__tag">Webinar</li>
										<li class="event__tag">Free</li>
									</ul>
								</article>
							</a>

							<a href="" class="event">
								<article class="event__container">
									<time
										class="event__date"
										datetime="2026-05-07T14:00:00-04:00"
										>
										DATE May 07, 2026 · 2:00 PM ET
									</time>
									<header class="event__header">
										<h3 class="event__heading">
											Editorial Titles
										</h3>
									</header>

									<p class="event__excerpt">
										Forem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit
										interdum, ac aliquet odio mattis.
									</p>

									<ul class="event__tags" aria-label="Event tags">
										<li class="event__tag">Webinar</li>
										<li class="event__tag">Free</li>
									</ul>
								</article>
							</a>

							<a href="" class="event">
								<article class="event__container">
									<time
										class="event__date"
										datetime="2026-05-07T14:00:00-04:00"
										>
										DATE May 07, 2026 · 2:00 PM ET
									</time>
									<header class="event__header">
										<h3 class="event__heading">
											Editorial Titles
										</h3>
									</header>

									<p class="event__excerpt">
										Forem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit
										interdum, ac aliquet odio mattis.
									</p>

									<ul class="event__tags" aria-label="Event tags">
										<li class="event__tag">Webinar</li>
										<li class="event__tag">Free</li>
									</ul>
								</article>
							</a>

					
						</div>
					</div>

					
				</div>


				
			</div>

		</section>
		<!-- Stories section: spotlight cards for partner/community success stories. -->
		<section class="stories">
			<div class="stories__container">
				<div class="stories__top">
					<header class="stories__header">
						<p class="stories__spotlight">Spotlight</p>
						<h2 class="stories__heading">Success <em>Stories</em></h2>
						<p class="stories__subheading">These are some of our partner organizations and the programs they created to transform their communities.</p>
					</header>
					<div class="stories__cta">
						<a href="" class="stories__btn btn__outline-deep">View Our Stories</a>
					</div>
				</div>

				<div class="stories__posts">
					<?php
					$story_query = new WP_Query(
						array(
							'post_type'           => 'post',
							'posts_per_page'      => 3,
							'post_status'         => 'publish',
							'ignore_sticky_posts' => true,
							'no_found_rows'       => true,
						)
					);

					if ( $story_query->have_posts() ) :
						while ( $story_query->have_posts() ) :
							$story_query->the_post();
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
												'sizes' => '(min-width: 900px) 280px, (min-width: 600px) 33vw, 100vw',
											)
										);
										?>
									</div>
								<?php endif; ?>

								<div class="story__bottom">
									<header class="story__header">
										<?php if ( $story_category ) : ?>
											<p class="story__category"><?php echo esc_html( $story_category ); ?></p>
										<?php endif; ?>
										<h3 class="story__heading"><?php echo esc_html( get_the_title() ); ?></h3>
									</header>
									<p class="story__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>

									<a href="<?php echo esc_url( get_permalink() ); ?>" class="story__button">Read More</a>
								</div>
							</article>
							<?php
						endwhile;
						wp_reset_postdata();
					endif;
					?>
				</div>
			</div>
		</section>
		<!-- Providers section: duplicated provider lists create the continuous marquee effect. -->
		<section class="providers">
			<div class="providers__container">
				<header class="providers__top">
					<p class="providers__subheading">Our Network</p>
					<h2 class="providers__heading">CVIPI <em>TA Providers</em></h2>
				</header>

				<?php
				$provider_query = new WP_Query(
					array(
						'post_type'           => 'ta_provider',
						'posts_per_page'      => -1,
						'post_status'         => 'publish',
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
						'orderby'             => 'menu_order title',
						'order'               => 'ASC',
					)
				);
				$provider_items = '';

				if ( $provider_query->have_posts() ) {
					ob_start();

					while ( $provider_query->have_posts() ) {
						$provider_query->the_post();
						$provider_logo = function_exists( 'get_field' ) ? get_field( 'provider_logo' ) : null;
						$provider_logo_id = 0;
						$provider_logo_url = '';

						if ( is_array( $provider_logo ) ) {
							$provider_logo_id  = ! empty( $provider_logo['ID'] ) ? absint( $provider_logo['ID'] ) : 0;
							$provider_logo_url = ! empty( $provider_logo['url'] ) ? $provider_logo['url'] : '';
						} elseif ( is_numeric( $provider_logo ) ) {
							$provider_logo_id = absint( $provider_logo );
						} elseif ( is_string( $provider_logo ) ) {
							$provider_logo_url = $provider_logo;
						}

						if ( ! $provider_logo_id && ! $provider_logo_url ) {
							continue;
						}
						?>
						<li class="providers__item">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="providers__link">
								<?php
								if ( $provider_logo_id ) {
									echo wp_get_attachment_image(
										$provider_logo_id,
										'medium',
										false,
										array(
											'class' => 'providers__img',
											'alt'   => get_the_title(),
											'sizes' => '150px',
										)
									);
								} else {
									?>
									<img src="<?php echo esc_url( $provider_logo_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="providers__img">
									<?php
								}
								?>
							</a>
						</li>
						<?php
					}

					$provider_items = ob_get_clean();
					wp_reset_postdata();
				}
				?>

				<?php if ( $provider_items ) : ?>
					<div class="provider__container" aria-label="CVIPI TA providers">
					<ul class="providers__list">
						<?php echo $provider_items; ?>
					</ul>
					<ul class="providers__list" aria-hidden="true">
						<?php echo str_replace( 'class="providers__link"', 'class="providers__link" tabindex="-1"', $provider_items ); ?>
					</ul>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</main>
<?php get_footer(); ?>
