<?php
/**
 * * The template for displaying the header
 *
 * @package cvipi
 */
?>
	<!doctype html>
	<html <?php language_attributes(); ?>>

	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<link rel="profile" href="https://gmpg.org/xfn/11">
		<?php wp_head(); ?>
	</head>



	<body class="container front-page">
		<a class="screen-reader-shortcut" href="#main-content" tabindex="1">Skip to main content</a>
		
		<!-- Header -->
		<header id="top" class="header home-header"  role="banner"> 
				<div class="header__inner">
					<div class="header__logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/img/cvipi-logo-white.webp" alt="<?php bloginfo( 'name' ); ?> logo">
						</a>
					</div><!-- .Header Logo -->

					<div class="header__nav">
						<!-- navigation -->
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
					</div><!-- .Header Nav -->
				</div><!-- .Header Inner -->

		    
		
		</header><!-- .Header -->
