<?php
/**
 * Header for post-type landing pages.
 *
 * Used by landing pages such as Events, Resources, and Success Stories. Single
 * post templates should use their own header pattern later.
 *
 * @package cvipi
 */

$posts_header_data = cvipi_get_posts_header_data();
$posts_header_style = ! empty( $posts_header_data['image'] )
	? ' style="background-image: linear-gradient(90deg, rgba(var(--color-deep-a), 0.82), rgba(var(--color-deep-a), 0.62)), url(' . esc_url( $posts_header_data['image'] ) . ');"'
	: '';
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

<body <?php body_class( 'post-landing-page' ); ?>>
	<a class="screen-reader-shortcut" href="#main-content" tabindex="1">Skip to main content</a>

	<header id="top" class="header home-header" role="banner">
		<div class="header__inner">
			<div class="header__logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/cvipi-logo-white.webp' ); ?>" alt="<?php bloginfo( 'name' ); ?> logo">
				</a>
			</div>

			<div class="header__nav">
				<div class="navigation">
					<nav class="navigation__nav" aria-controls="primary-navigation">
						<ul class="navigation__list">
							<li class="navigation__item">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navigation__link">Home</a>
							</li>
							<li class="navigation__item">
								<a href="<?php echo esc_url( site_url( '/what-is-cvipi' ) ); ?>" class="navigation__link">What is CVIPI?</a>
							</li>
							<li class="navigation__item">
								<a href="<?php echo esc_url( site_url( '/success-stories' ) ); ?>" class="navigation__link">Success Stories</a>
							</li>
							<li class="navigation__item">
								<a href="<?php echo esc_url( site_url( '/resources' ) ); ?>" class="navigation__link">Resources</a>
							</li>
							<li class="navigation__item">
								<a href="<?php echo esc_url( site_url( '/events' ) ); ?>" class="navigation__link">Events</a>
							</li>
							<li class="navigation__item">
								<a href="<?php echo esc_url( site_url( '/contact' ) ); ?>" class="navigation__link">Contact</a>
							</li>
						</ul>
					</nav>
				</div>

				<div class="mobile-navigation">
					<span class="sr-only" id="mobile-menu-label">Main menu</span>

					<button class="mobile-navigation__menu" type="button" aria-controls="mobile-navigation" aria-expanded="false" aria-labelledby="mobile-menu-label">
						<span class="mobile-navigation__icon" aria-hidden="true"></span>
					</button>

					<nav id="mobile-navigation" class="mobile-navigation__nav" aria-label="Mobile menu" aria-hidden="true">
						<ul class="mobile-navigation__list">
							<li class="mobile-navigation__item">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-navigation__link">Home</a>
							</li>
							<li class="mobile-navigation__item">
								<a href="<?php echo esc_url( site_url( '/what-is-cvipi' ) ); ?>" class="mobile-navigation__link">What is CVIPI?</a>
							</li>
							<li class="mobile-navigation__item">
								<a href="<?php echo esc_url( site_url( '/success-stories' ) ); ?>" class="mobile-navigation__link">Success Stories</a>
							</li>
							<li class="mobile-navigation__item">
								<a href="<?php echo esc_url( site_url( '/resources' ) ); ?>" class="mobile-navigation__link">Resources</a>
							</li>
							<li class="mobile-navigation__item">
								<a href="<?php echo esc_url( site_url( '/events' ) ); ?>" class="mobile-navigation__link">Events</a>
							</li>
							<li class="mobile-navigation__item">
								<a href="<?php echo esc_url( site_url( '/contact' ) ); ?>" class="mobile-navigation__link">Contact</a>
							</li>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</header>

	<section class="posts-hero">
		<div class="posts-hero__media"<?php echo $posts_header_style; ?> aria-hidden="true"></div>
		<div class="posts-hero__content">
			<?php if ( ! empty( $posts_header_data['eyebrow'] ) ) : ?>
				<p class="posts-hero__eyebrow"><?php echo esc_html( $posts_header_data['eyebrow'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $posts_header_data['title'] ) ) : ?>
				<h1 class="posts-hero__heading"><?php echo wp_kses_post( $posts_header_data['title'] ); ?></h1>
			<?php endif; ?>

			<?php if ( ! empty( $posts_header_data['description'] ) ) : ?>
				<p class="posts-hero__description"><?php echo esc_html( $posts_header_data['description'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
