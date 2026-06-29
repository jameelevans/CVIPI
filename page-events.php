<?php
/**
 * Events listing page.
 *
 * Displays upcoming events plus the full searchable/filterable event library.
 *
 * @package cvipi
 */

get_header( 'posts' );

$event_search = isset( $_GET['event_search'] ) ? sanitize_text_field( wp_unslash( $_GET['event_search'] ) ) : '';
$event_type   = isset( $_GET['event_type'] ) ? sanitize_title( wp_unslash( $_GET['event_type'] ) ) : '';
$event_topic  = isset( $_GET['event_topic'] ) ? sanitize_title( wp_unslash( $_GET['event_topic'] ) ) : '';
$event_status = isset( $_GET['event_status'] ) ? sanitize_key( wp_unslash( $_GET['event_status'] ) ) : '';

if ( ! in_array( $event_status, array( 'upcoming', 'past' ), true ) ) {
	$event_status = '';
}

$event_types  = cvipi_get_event_type_terms();
$event_topics = cvipi_get_event_topic_terms();

$today_ymd = current_time( 'Ymd' );

$upcoming_events_query = new WP_Query(
	array(
			'post_type'           => 'event',
			'posts_per_page'      => 3,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		'meta_key'            => 'event_date',
		'orderby'             => 'meta_value_num',
		'order'               => 'ASC',
		'meta_query'          => array(
			array(
				'key'     => 'event_date',
				'value'   => $today_ymd,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		),
	)
);

$events_query = new WP_Query(
	cvipi_get_event_query_args(
		array(
			'event_search' => $event_search,
			'event_type'   => $event_type,
			'event_topic'  => $event_topic,
			'event_status' => $event_status,
		)
	)
);
?>

<main id="main-content">
	<section class="events-page" data-events-archive>
		<div class="events-page__container">
			<section class="events-page__upcoming" aria-labelledby="upcoming-events-heading">
				<header class="events-page__section-header">
					<p class="events-page__eyebrow">Coming Up</p>
					<h2 id="upcoming-events-heading" class="events-page__heading">Upcoming <em>Events</em></h2>
					<p class="events-page__intro">Monthly webinars held via Zoom. Free and open to all CVIPI grantees and CVI practitioners.</p>
				</header>

				<div class="events-page__upcoming-grid">
					<?php if ( $upcoming_events_query->have_posts() ) : ?>
						<?php
						while ( $upcoming_events_query->have_posts() ) :
							$upcoming_events_query->the_post();
							$event_date_parts = cvipi_get_event_short_date_parts();
							$event_cta_url    = cvipi_get_event_field( 'event_cta_url' );
							$event_cta_label  = cvipi_get_event_field( 'event_cta_label' );
								$event_location   = cvipi_get_event_field( 'event_location' );
								$event_terms      = cvipi_get_event_card_terms();
								$event_url        = get_permalink();
								$event_timestamp  = cvipi_get_event_timestamp();
								?>
								<article class="events-page__upcoming-card">
									<a href="<?php echo esc_url( $event_url ); ?>" class="events-page__upcoming-link" aria-label="<?php echo esc_attr( 'View event: ' . get_the_title() ); ?>">
										<span class="events-page__upcoming-date">
											<span class="events-page__date-badge">
												<span><?php echo esc_html( $event_date_parts['month'] ); ?></span>
												<strong><?php echo esc_html( $event_date_parts['day'] ); ?></strong>
											</span>
											<span class="events-page__date-copy">
												<time class="events-page__date" datetime="<?php echo esc_attr( cvipi_get_event_datetime_attr() ); ?>">
													<?php echo esc_html( $event_timestamp ? date_i18n( 'F j, Y', $event_timestamp ) : cvipi_get_event_date_label( null, false ) ); ?>
												</time>
												<span class="events-page__date-meta">
													<?php if ( $event_timestamp ) : ?>
														<span><?php echo esc_html( date_i18n( 'l', $event_timestamp ) ); ?></span>
													<?php endif; ?>
													<?php if ( cvipi_get_event_field( 'event_time' ) ) : ?>
														<span class="events-page__meta-separator" aria-hidden="true"></span>
														<span><?php echo esc_html( date_i18n( 'g:i A', $event_timestamp ) . ' ' . cvipi_get_event_field( 'event_timezone' ) ); ?></span>
													<?php endif; ?>
												</span>
											</span>
										</span>
										<span class="events-page__upcoming-body">
											<span class="events-page__card-title"><?php echo esc_html( get_the_title() ); ?></span>
											<span class="events-page__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></span>

										<?php if ( ! empty( $event_terms ) || $event_location ) : ?>
											<span class="events-page__tags" aria-label="Event tags">
												<?php foreach ( $event_terms as $event_term ) : ?>
													<span><?php echo esc_html( $event_term['name'] ); ?></span>
												<?php endforeach; ?>
												<?php if ( $event_location ) : ?>
													<span><?php echo esc_html( $event_location ); ?></span>
												<?php endif; ?>
											</span>
										<?php endif; ?>

										<?php if ( $event_cta_url ) : ?>
											<span class="events-page__button">
												<?php echo svg_icon( 'events-page__button-icon', 'display' ); ?>
												<?php echo esc_html( $event_cta_label ? $event_cta_label : 'RSVP' ); ?>
											</span>
										<?php endif; ?>
									</span>
								</a>
							</article>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<p class="events-page__empty">No upcoming events are scheduled yet.</p>
					<?php endif; ?>
				</div>
			</section>

			<section class="events-page__library" aria-labelledby="events-library-heading">
				<header class="events-page__section-header">
					<p class="events-page__eyebrow">Full Library</p>
					<h2 id="events-library-heading" class="events-page__heading">All Events &amp; Recordings</h2>
				</header>

				<form class="events-page__filters" action="<?php echo esc_url( cvipi_get_filtered_events_url() ); ?>" method="get">
					<input type="hidden" name="event_status" value="<?php echo esc_attr( $event_status ); ?>" />

					<label class="events-page__search">
						<span>Search Events</span>
						<input type="search" name="event_search" value="<?php echo esc_attr( $event_search ); ?>" placeholder="Search by title, speaker, or topic..." />
					</label>

					<label>
						<span>Event Type</span>
						<select name="event_type">
							<option value="">All Types</option>
							<?php if ( ! is_wp_error( $event_types ) ) : ?>
								<?php foreach ( $event_types as $event_type_term ) : ?>
									<option value="<?php echo esc_attr( $event_type_term->slug ); ?>" <?php selected( $event_type, $event_type_term->slug ); ?>>
										<?php echo esc_html( $event_type_term->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</label>

					<label>
						<span>Topic</span>
						<select name="event_topic">
							<option value="">All Topics</option>
							<?php if ( ! is_wp_error( $event_topics ) ) : ?>
								<?php foreach ( $event_topics as $event_topic_term ) : ?>
									<option value="<?php echo esc_attr( $event_topic_term->slug ); ?>" <?php selected( $event_topic, $event_topic_term->slug ); ?>>
										<?php echo esc_html( $event_topic_term->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</label>

					<button type="submit">Search</button>
				</form>

				<nav class="events-page__pills" aria-label="Quick event filters">
					<a href="<?php echo esc_url( cvipi_get_filtered_events_url() ); ?>" data-event-status-filter="" class="events-page__pill--deep events-page__pill--separator <?php echo '' === $event_status ? 'is-active' : ''; ?>">All Events</a>
					<a href="<?php echo esc_url( cvipi_get_filtered_events_url( array( 'event_status' => 'upcoming' ) ) ); ?>" data-event-status-filter="upcoming" class="<?php echo 'upcoming' === $event_status ? 'is-active' : ''; ?>">Upcoming</a>
					<a href="<?php echo esc_url( cvipi_get_filtered_events_url( array( 'event_status' => 'past' ) ) ); ?>" data-event-status-filter="past" class="events-page__pill--separator <?php echo 'past' === $event_status ? 'is-active' : ''; ?>">Past / Archived</a>
					<?php if ( ! is_wp_error( $event_types ) ) : ?>
						<a href="<?php echo esc_url( cvipi_get_filtered_events_url( array( 'event_search' => $event_search, 'event_topic' => $event_topic, 'event_status' => $event_status ) ) ); ?>" data-event-type-filter="" class="<?php echo '' === $event_type ? 'is-active' : ''; ?>">All Types</a>
						<?php foreach ( $event_types as $event_type_term ) : ?>
							<a href="<?php echo esc_url( cvipi_get_filtered_events_url( array( 'event_type' => $event_type_term->slug ) ) ); ?>" data-event-type-filter="<?php echo esc_attr( $event_type_term->slug ); ?>" class="<?php echo $event_type === $event_type_term->slug ? 'is-active' : ''; ?>">
								<?php echo esc_html( $event_type_term->name ); ?>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</nav>

				<p class="events-page__count" data-events-count>
					<?php
					printf(
						esc_html( _n( '%s event found', '%s events found', $events_query->found_posts, 'cvipi' ) ),
						esc_html( number_format_i18n( $events_query->found_posts ) )
					);
					?>
				</p>

				<div class="events-page__grid" data-events-grid aria-live="polite">
					<?php if ( $events_query->have_posts() ) : ?>
						<?php
						$event_card_index = 0;

						while ( $events_query->have_posts() ) :
							$events_query->the_post();
							echo cvipi_render_event_card( get_the_ID(), $event_card_index >= 6 );
							$event_card_index++;
						endwhile;
						?>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<p class="events-page__empty">No events matched your filters.</p>
					<?php endif; ?>
				</div>

				<div class="events-page__load-more">
					<button type="button" data-events-load-more <?php echo $events_query->found_posts > 6 ? '' : 'hidden'; ?>>Load More Events</button>
				</div>
			</section>
		</div>
	</section>
</main>

<?php
get_footer();
