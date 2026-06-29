<?php
/**
 * The template for displaying the site header.
 *
 * This file opens the HTML document, outputs the WordPress head hooks,
 * and renders the logo plus primary navigation used across the theme.
 *
 * @package cvipi
 */
?>
	<!doctype html>
	<html <?php language_attributes(); ?>>

	<head>
		<!-- Basic document metadata required by WordPress and responsive browsers. -->
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<link rel="profile" href="https://gmpg.org/xfn/11">

		<!-- Allows WordPress, plugins, and the theme to enqueue styles/scripts in the head. -->
		<?php wp_head(); ?>
	</head>



	<body class="front-page">
		<!-- Accessibility shortcut for keyboard and screen-reader users. -->
		<a class="screen-reader-shortcut" href="#main-content" tabindex="1">Skip to main content</a>
		
		<!-- Site header: contains the logo and primary navigation. -->
		<header id="top" class="header home-header"  role="banner"> 
				<div class="header__inner">
					<!-- Site logo linked back to the homepage. -->
					<div class="header__logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/img/cvipi-logo-white.webp" alt="<?php bloginfo( 'name' ); ?> logo">
						</a>
					</div><!-- .Header Logo -->

					<div class="header__nav">
						<!-- Primary navigation links. -->
						<div class="navigation">
								<nav class="navigation__nav" aria-controls="primary-navigation">
									<ul class="navigation__list">
										<?php cvipi_render_primary_navigation(); ?>
									</ul>
							</nav>
						</div><!-- .navigation -->

						<!-- Mobile navigation: hidden on desktop and controlled by MobileNav.js. -->
						<div class="mobile-navigation">
							<span class="sr-only" id="mobile-menu-label">Main menu</span>

							<button class="mobile-navigation__menu" type="button" aria-controls="mobile-navigation" aria-expanded="false" aria-labelledby="mobile-menu-label">
								<span class="mobile-navigation__icon" aria-hidden="true"></span>
							</button>

							<nav id="mobile-navigation" class="mobile-navigation__nav" aria-label="Mobile menu" aria-hidden="true">
								

								<ul class="mobile-navigation__list">
									<?php cvipi_render_primary_navigation( true ); ?>
								</ul>
							</nav>
						</div><!-- .mobile-navigation -->
					</div><!-- .Header Nav -->
				</div><!-- .Header Inner -->
		</header><!-- .Header -->
		<?php
		/*
		 * Homepage banner video data.
		 *
		 * ACF field group expected on the front page:
		 * - posts_header_eyebrow: banner eyebrow text
		 * - posts_header_title: banner title, allowing simple emphasis markup
		 * - posts_header_description: banner body copy
		 * - banner_videos: repeater
		 * - video: file field, returned as an array or URL
		 * - poster_image: optional image field, returned as an array or URL
		 *
		 * The front-end carousel reads the encoded data from data-banner-videos.
		 * If no videos are present, the SCSS fallback background image remains visible.
		 */
		$banner_videos = array();
		$banner_page_id = cvipi_get_home_banner_page_id();

		if ( is_front_page() && function_exists( 'get_field' ) ) {
			$banner_video_rows = get_field( 'banner_videos', $banner_page_id );

			if ( $banner_video_rows ) {
				foreach ( $banner_video_rows as $banner_video_row ) {
					$banner_video = isset( $banner_video_row['video'] ) ? $banner_video_row['video'] : null;
					$banner_poster = isset( $banner_video_row['poster_image'] ) ? $banner_video_row['poster_image'] : null;
					$banner_video_url = cvipi_get_acf_media_url( $banner_video );
					$banner_poster_url = cvipi_get_acf_media_url( $banner_poster );

					if ( $banner_video_url ) {
						$banner_videos[] = array(
							'src'    => esc_url_raw( $banner_video_url ),
							'poster' => esc_url_raw( $banner_poster_url ),
						);
					}
				}
			}
		}

		$banner_video_data = wp_json_encode( $banner_videos );
		$banner_has_videos = ! empty( $banner_videos );
		$banner_first_video = $banner_has_videos ? $banner_videos[0] : null;
		$banner_first_poster = $banner_first_video && ! empty( $banner_first_video['poster'] ) ? $banner_first_video['poster'] : '';
		$banner_data = cvipi_get_home_banner_data( $banner_page_id );
		$banner_cta = null;

		if ( is_front_page() && function_exists( 'get_field' ) ) {
			$banner_cta = get_field( 'banner_cta', $banner_page_id );
		}
		?>
		<!-- Homepage banner: media layer sits behind the content and stats columns. -->
		<div class="banner<?php echo $banner_has_videos ? ' banner--has-video' : ''; ?>"<?php echo $banner_has_videos ? ' data-banner-videos="' . esc_attr( $banner_video_data ) . '"' : ''; ?>>
			<?php if ( $banner_has_videos ) : ?>
				<!-- Decorative media; hidden from assistive tech because the copy below carries the meaning. -->
				<div class="banner__media"<?php echo $banner_first_poster ? ' style="background-image: url(' . esc_url( $banner_first_poster ) . ');"' : ''; ?> aria-hidden="true">
					<video
						class="banner__video"
						<?php echo ! empty( $banner_first_video['poster'] ) ? 'poster="' . esc_url( $banner_first_video['poster'] ) . '"' : ''; ?>
						muted
						playsinline
						preload="none"
					></video>
				</div>
			<?php endif; ?>
			<div class="banner__inner">
				<div class="banner__left">
					<div class="banner__container">
						<?php if ( ! empty( $banner_data['eyebrow'] ) ) : ?>
							<p class="banner__subheader"><?php echo esc_html( $banner_data['eyebrow'] ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $banner_data['title'] ) ) : ?>
							<h2 class="banner__heading"><?php echo wp_kses_post( $banner_data['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $banner_data['description'] ) ) : ?>
							<div class="banner__details">
								<?php echo wp_kses_post( wpautop( $banner_data['description'] ) ); ?>
							</div>
						<?php endif; ?>
						<?php if ( is_array( $banner_cta ) && ! empty( $banner_cta['url'] ) && ! empty( $banner_cta['title'] ) ) : ?>
							<a
								href="<?php echo esc_url( $banner_cta['url'] ); ?>"
								class="btn__copper banner__cta"
								<?php echo ! empty( $banner_cta['target'] ) ? 'target="' . esc_attr( $banner_cta['target'] ) . '"' : ''; ?>
								<?php echo isset( $banner_cta['target'] ) && '_blank' === $banner_cta['target'] ? 'rel="noopener noreferrer"' : ''; ?>
							>
								<?php echo esc_html( $banner_cta['title'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
				<div class="banner__right">
					<div class="banner__stats">
						<span class="banner__number">300+</span>
						<p class="banner__description">Grantee organizations<br /> supported nationwide</p>
					</div>
					<div class="banner__stats">
						<span class="banner__number">1,200+</span>
						<p class="banner__description">Resources, guides & toolkits<br /> in the library</p>
					</div>
					<div class="banner__stats">
						<span class="banner__number">50+</span>
						<p class="banner__description">Webinars and trainings<br /> delivered annually</p>
					</div>
				</div>
			</div>
		</div>
