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
					</div><!-- .Header Nav -->
				</div><!-- .Header Inner -->
		</header><!-- .Header -->
		<div class="banner">
			<div class="banner__inner container">
				<div class="banner__left">
					<p class="banner__subheader">Community Violence Intervention</p>
					<h2 class="banner__heading"><span>It takes a</span> community to end violence</h2>
					<p class="banner__details">The National Community Violence Intervention & Prevention Initiative (CVIPI) was created to be a source of information and practical resources on community violence intervention. 
					This nationwide initiative brings together community residents, local government, law enforcement, hospitals, victim service providers, community-based organizations (CBOs), researchers, and other partners to help prevent and reduce violent crime to make our communities safer for generations to come.</p>
					<a href="#" class="btn__copper">Access Resources &amp; Information</a>
				</div>
				<div class="banner__right">
					<div class="banner__stats">
						<span class="banner__org">300+</span>
						<p class="banner__org-des">Grantee organizations<br /> supported nationwide</p>
					</div>
					<div class="banner__stats">
						<span class="banner__resources">1,200+</span>
						<p class="banner__resources-des">Resources, guides & toolkits<br /> in the library</p>
					</div>
					<div class="banner__stats">
						<span class="banner__webinars">50+</span>
						<p class="banner__webinars-des">Webinars and trainings<br /> delivered annually</p>
					</div>
				</div>
			</div>
		</div>
