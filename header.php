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
												
										<li class="navigation__item">
											<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navigation__link" title="Go to the Home page">Home</a>
										</li>

										<li class="navigation__item">
											<a href="<?php echo esc_url( site_url( '#' ) ); ?>" class="navigation__link" title="Go to the Contact page">What is CVIPI?</a>
										</li>
										<li class="navigation__item">
											<a href="<?php echo esc_url( site_url( '#' ) ); ?>" class="navigation__link" title="Go to the Contact page">Who We Serve</a>
										</li>
										<li class="navigation__item">
											<a href="<?php echo esc_url( site_url( '#' ) ); ?>" class="navigation__link" title="Go to the Contact page">Our Stories</a>
										</li>
										<li class="navigation__item">
											<a href="<?php echo esc_url( site_url( '#' ) ); ?>" class="navigation__link" title="Go to the Contact page">Resources</a>
										</li>
										<li class="navigation__item">
											<a href="<?php echo esc_url( site_url( '#' ) ); ?>" class="navigation__link" title="Go to the Contact page">Events</a>
										</li>
										<li class="navigation__item">
											<a href="<?php echo esc_url( site_url( '#' ) ); ?>" class="navigation__link" title="Go to the Contact page">Contacts</a>
										</li>
									</ul>
							</nav>
						</div><!-- .navigation -->

						<!-- Mobile navigation 
						<div class="mobile-navigation">
							 Hidden menu label for accessibility
							<span hidden="" id="mobile-menu">Main menu</span>

							<button class="mobile-navigation__menu" aria-controls="mobile-navigation" tabindex="0" aria-expanded="false" aria-labelledby="mobile-menu">

							navigation menu icon
								<i class="mobile-navigation__icon" alt="Menu icon" aria-hidden="true">&nbsp;</i>
							</button>


							<nav class="mobile-navigation__nav" aria-label="Mobile menu" aria-labelledby="mobile-menu" aria-hidden="true">
								
								<ul class="mobile-navigation__list">
									
									<li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										Home
										</a>
									</li>
									<li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										What is CVIPI?
										</a>
									</li><li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										Who We Serve
										</a>
									</li><li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										Our Stories
										</a>
									</li>
									<li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										Resources
										</a>
									</li>
									<li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										Events
										</a>
									</li>
									<li class="mobile-navigation__item">
										<a href="#"
										class="mobile-navigation__link"
										title="Go to Home page">
										Contacts
										</a>
									</li>

									
									</ul>

								
							</nav>
							
						</div> Nav end -->
					</div><!-- .Header Nav -->
				</div><!-- .Header Inner -->
		</header><!-- .Header -->
		<?php
		/*
		 * Homepage banner video data.
		 *
		 * ACF field group expected on the front page:
		 * - banner_videos: repeater
		 * - video: file field, returned as an array or URL
		 * - poster_image: optional image field, returned as an array or URL
		 *
		 * The front-end carousel reads the encoded data from data-banner-videos.
		 * If no videos are present, the SCSS fallback background image remains visible.
		 */
		$banner_videos = array();
		$banner_page_id = get_queried_object_id();

		if ( is_front_page() && function_exists( 'get_field' ) ) {
			$banner_video_rows = get_field( 'banner_videos', $banner_page_id );

			if ( $banner_video_rows ) {
				foreach ( $banner_video_rows as $banner_video_row ) {
					$banner_video = isset( $banner_video_row['video'] ) ? $banner_video_row['video'] : null;
					$banner_poster = isset( $banner_video_row['poster_image'] ) ? $banner_video_row['poster_image'] : null;
					$banner_video_url = is_array( $banner_video ) && isset( $banner_video['url'] ) ? $banner_video['url'] : $banner_video;
					$banner_poster_url = is_array( $banner_poster ) && isset( $banner_poster['url'] ) ? $banner_poster['url'] : '';

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
		?>
		<!-- Homepage banner: media layer sits behind the content and stats columns. -->
		<div class="banner<?php echo $banner_has_videos ? ' banner--has-video' : ''; ?>"<?php echo $banner_has_videos ? ' data-banner-videos="' . esc_attr( $banner_video_data ) . '"' : ''; ?>>
			<?php if ( $banner_has_videos ) : ?>
				<!-- Decorative autoplay video; hidden from assistive tech because the copy below carries the meaning. -->
				<div class="banner__media" aria-hidden="true">
					<video
						class="banner__video"
						src="<?php echo esc_url( $banner_first_video['src'] ); ?>"
						<?php echo ! empty( $banner_first_video['poster'] ) ? 'poster="' . esc_url( $banner_first_video['poster'] ) . '"' : ''; ?>
						muted
						playsinline
						autoplay
						preload="metadata"
					></video>
				</div>
			<?php endif; ?>
			<div class="banner__inner">
				<div class="banner__left">
					<div class="banner__container">
						<p class="banner__subheader">Community Violence Intervention</p>

						<h2 class="banner__heading">It takes a <br /><em>community to end violence</em></h2>
						<div class="banner__details">
							<p>The National Community Violence Intervention & Prevention Initiative (CVIPI) was created to be a source of information and practical resources on community violence intervention. </p>
							<p>This nationwide initiative brings together community residents, local government, law enforcement, hospitals, victim service providers, community-based organizations (CBOs), researchers, and other partners to help prevent and reduce violent crime to make our communities safer for generations to come.</p>
						</div>
						<a href="#" class="btn__copper">Access Resources &amp; Information</a>
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
