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
	? ' style="--posts-hero-image: url(\'' . esc_url( $posts_header_data['image'] ) . '\');"'
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

<body <?php body_class( array_filter( array( 'post-landing-page', is_page( 'success-stories' ) ? 'success-stories-page' : '' ) ) ); ?>>
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

	<section class="posts-hero">
		<div class="posts-hero__media"<?php echo $posts_header_style; ?> aria-hidden="true">
			<?php if ( ! empty( $posts_header_data['video'] ) ) : ?>
				<video
					class="posts-hero__video"
					src="<?php echo esc_url( $posts_header_data['video'] ); ?>"
					<?php echo ! empty( $posts_header_data['image'] ) ? 'poster="' . esc_url( $posts_header_data['image'] ) . '"' : ''; ?>
					autoplay
					muted
					loop
					playsinline
					preload="metadata"
				></video>
			<?php endif; ?>
		</div>
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
