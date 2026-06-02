<?php
/**
 * Resources listing page.
 *
 * Displays published Resources and optionally filters them by the
 * resource_category query string used by the homepage category cards.
 *
 * @package cvipi
 */

get_header( 'posts' );

$resource_category_cards = cvipi_resource_category_cards();
$allowed_category_slugs  = array_keys( $resource_category_cards );
$active_category_slug    = '';
$resource_search         = isset( $_GET['resource_search'] ) ? sanitize_text_field( wp_unslash( $_GET['resource_search'] ) ) : '';
$resource_year           = isset( $_GET['resource_year'] ) ? absint( $_GET['resource_year'] ) : 0;
$resource_topic          = isset( $_GET['resource_topic'] ) ? sanitize_title( wp_unslash( $_GET['resource_topic'] ) ) : '';

if ( isset( $_GET['resource_category'] ) ) {
	$requested_category_slug = sanitize_title( wp_unslash( $_GET['resource_category'] ) );

	if ( in_array( $requested_category_slug, $allowed_category_slugs, true ) ) {
		$active_category_slug = $requested_category_slug;
	}
}

$resource_category_terms = get_terms(
	array(
		'taxonomy'   => 'category',
		'slug'       => $allowed_category_slugs,
		'hide_empty' => false,
	)
);

$resource_category_terms_by_slug = array();

if ( ! is_wp_error( $resource_category_terms ) ) {
	foreach ( $resource_category_terms as $resource_category_term ) {
		$resource_category_terms_by_slug[ $resource_category_term->slug ] = $resource_category_term;
	}
}

$resource_topic_terms = get_terms(
	array(
		'taxonomy'   => 'post_tag',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

$featured_resource_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
		'meta_query'          => array(
			array(
				'key'     => 'featured_resource',
				'value'   => '1',
				'compare' => '=',
			),
		),
	)
);

$featured_resource_id = 0;

if ( $featured_resource_query->have_posts() ) {
	$featured_resource_query->the_post();
	$featured_resource_id = get_the_ID();
	wp_reset_postdata();
}

if ( ! $featured_resource_id ) {
	$fallback_featured_resource_query = new WP_Query(
		cvipi_get_resource_query_args(
			array(
				'resource_category' => '',
				'resource_search'   => '',
				'resource_year'     => 0,
			)
		)
	);

	if ( $fallback_featured_resource_query->have_posts() ) {
		$fallback_featured_resource_query->the_post();
		$featured_resource_id = get_the_ID();
		wp_reset_postdata();
	}
}

$resource_years = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array(
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $allowed_category_slugs,
			),
		),
	)
);

$resource_year_options = array();

foreach ( $resource_years as $resource_year_post_id ) {
	$resource_year_options[] = get_the_date( 'Y', $resource_year_post_id );
}

$resource_year_options = array_unique( $resource_year_options );
rsort( $resource_year_options );

$resource_query = new WP_Query(
	cvipi_get_resource_query_args(
		array(
				'resource_category' => $active_category_slug,
				'resource_search'   => $resource_search,
				'resource_year'     => $resource_year,
				'resource_topic'    => $resource_topic,
				'exclude_ids'       => array( $featured_resource_id ),
			)
	)
);
?>

<main id="main-content">
	<section
		class="resources-page"
		data-resources-archive
		data-featured-resource-id="<?php echo esc_attr( $featured_resource_id ); ?>"
	>
		<div class="resources-page__container">
			<?php if ( $featured_resource_id ) : ?>
				<?php
				$featured_category_card = cvipi_get_resource_category_card_by_post( $featured_resource_id );
				$featured_category      = $featured_category_card['term'];
				$featured_image_url     = has_post_thumbnail( $featured_resource_id )
					? get_the_post_thumbnail_url( $featured_resource_id, 'large' )
					: get_template_directory_uri() . '/assets/img/post-1.webp';
				?>
				<article class="resources-page__featured" style="--resource-accent: <?php echo esc_attr( $featured_category_card['color'] ); ?>;">
					<a href="<?php echo esc_url( get_permalink( $featured_resource_id ) ); ?>" class="resources-page__featured-media">
						<img src="<?php echo esc_url( $featured_image_url ); ?>" alt="" class="resources-page__featured-img">
					</a>
					<div class="resources-page__featured-content">
						<?php if ( $featured_category ) : ?>
							<p class="resources-page__featured-kicker">
								<?php echo esc_html( wp_specialchars_decode( $featured_category->name, ENT_QUOTES ) ); ?> · Featured
							</p>
						<?php endif; ?>
						<h2 class="resources-page__featured-title">
							<a href="<?php echo esc_url( get_permalink( $featured_resource_id ) ); ?>">
								<?php echo esc_html( get_the_title( $featured_resource_id ) ); ?>
							</a>
						</h2>
						<p class="resources-page__featured-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $featured_resource_id ), 30 ) ); ?></p>
						<p class="resources-page__featured-meta"><?php echo esc_html( get_the_date( 'F Y', $featured_resource_id ) ); ?></p>
						<a href="<?php echo esc_url( get_permalink( $featured_resource_id ) ); ?>" class="resources-page__featured-button">
							View Featured Resource
						</a>
					</div>
				</article>
			<?php endif; ?>

			<form class="resources-page__filters" action="<?php echo esc_url( cvipi_get_filtered_resources_url() ); ?>" method="get">
				<label class="resources-page__search">
					<span>Search Resources</span>
					<input type="search" name="resource_search" value="<?php echo esc_attr( $resource_search ); ?>" placeholder="Search by keyword, title, or topic..." />
				</label>

				<label>
					<span>Type</span>
					<select name="resource_category">
						<option value="">All Types</option>
						<?php foreach ( $resource_category_cards as $resource_category_slug => $resource_category_card ) : ?>
							<?php
							if ( empty( $resource_category_terms_by_slug[ $resource_category_slug ] ) ) {
								continue;
							}

							$resource_category_term = $resource_category_terms_by_slug[ $resource_category_slug ];
							?>
							<option value="<?php echo esc_attr( $resource_category_term->slug ); ?>" <?php selected( $active_category_slug, $resource_category_term->slug ); ?>>
								<?php echo esc_html( wp_specialchars_decode( $resource_category_term->name, ENT_QUOTES ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					<span>Year</span>
					<select name="resource_year">
						<option value="">All Years</option>
						<?php foreach ( $resource_year_options as $resource_year_option ) : ?>
							<option value="<?php echo esc_attr( $resource_year_option ); ?>" <?php selected( $resource_year, (int) $resource_year_option ); ?>>
								<?php echo esc_html( $resource_year_option ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					<span>Topic</span>
					<select name="resource_topic">
						<option value="">All Topics</option>
						<?php if ( ! is_wp_error( $resource_topic_terms ) ) : ?>
							<?php foreach ( $resource_topic_terms as $resource_topic_term ) : ?>
								<option value="<?php echo esc_attr( $resource_topic_term->slug ); ?>" <?php selected( $resource_topic, $resource_topic_term->slug ); ?>>
									<?php echo esc_html( $resource_topic_term->name ); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</label>

				<button type="submit">Search</button>
			</form>

			<nav class="resources-page__pills" aria-label="Filter resources by category">
				<a
					href="<?php echo esc_url( cvipi_get_filtered_resources_url() ); ?>"
					data-resource-filter=""
					class="<?php echo $active_category_slug ? '' : 'is-active'; ?>"
				>
					All Resources
				</a>

				<?php foreach ( $resource_category_cards as $resource_category_slug => $resource_category_card ) : ?>
					<?php
					if ( empty( $resource_category_terms_by_slug[ $resource_category_slug ] ) ) {
						continue;
					}

					$resource_category_term = $resource_category_terms_by_slug[ $resource_category_slug ];
					?>
					<a
						href="<?php echo esc_url( cvipi_get_filtered_resources_url( $resource_category_term->slug ) ); ?>"
						data-resource-filter="<?php echo esc_attr( $resource_category_term->slug ); ?>"
						class="<?php echo $active_category_slug === $resource_category_term->slug ? 'is-active' : ''; ?>"
					>
						<?php echo esc_html( wp_specialchars_decode( $resource_category_term->name, ENT_QUOTES ) ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<p class="resources-page__count" data-resources-count>
				<?php
				printf(
					esc_html( _n( '%s resource found', '%s resources found', $resource_query->found_posts, 'cvipi' ) ),
					esc_html( number_format_i18n( $resource_query->found_posts ) )
				);
				?>
			</p>

			<div class="resources-page__grid" data-resources-grid aria-live="polite">
				<?php if ( $resource_query->have_posts() ) : ?>
					<?php
					while ( $resource_query->have_posts() ) :
						$resource_query->the_post();
						echo cvipi_render_resource_card();
					endwhile;
					?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p class="resources-page__empty">No resources matched your filters.</p>
				<?php endif; ?>
			</div>

			<div class="resources-page__load-more">
				<button type="button" data-resources-load-more <?php echo $resource_query->found_posts > 6 ? '' : 'hidden'; ?>>Load More Resources</button>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
