<?php
/**
 * General site header without a page hero.
 *
 * Used by templates that render their own main header, such as single pages,
 * search, 404, and other utility views.
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

<body <?php body_class( 'general-page' ); ?>>
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
							<?php cvipi_render_primary_navigation(); ?>
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
							<?php cvipi_render_primary_navigation( true ); ?>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</header>
