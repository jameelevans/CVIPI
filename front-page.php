<?php
/**
 * * The template for displaying the front page
 *
 * @package cvipi
 */

get_header();

?>
	<main id="main-content">
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
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/police-with-kids.webp" alt="Police woman sitting with kids" class="about__image">
				</div>
			</div>

			<div class="about__container">
				<div class="about__image-wrap">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/women-smiling-in-group.webp" alt="Polierferfwith kids" class="about__image">
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
		
		<section class="">

		</section>
		<section class="">
			<p class="about__subheader"></p>
		</section>
		<section class="">

		</section>
	</main>
<?php get_footer(); ?>
