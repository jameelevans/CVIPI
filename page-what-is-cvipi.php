<?php
/**
 * CVIPI landing page.
 *
 * @package cvipi
 */

get_header( 'cvipi' ); ?>

<main id="main-content" class="cvipi">
	<div class="cvipi__container">
		<section class="our-story">
			<div class="our-story__container">
				<div class="our-story__content">
					<header class="our-story__header">
						<p class="our-story__eyebrow">Our Story</p>
						<h2 class="our-story__heading">Launched in FY 2022 as a <em>historic investment</em></h2>
					</header>
					<p class="our-story__p">CVIPI launched in FY 2022 as a historic federal investment in community violence intervention, aimed at preventing and reducing violent crime through comprehensive, evidence-based community programs.</p>
					<p class="our-story__p">Several DOJ components collaborate on the initiative—BJA, NIJ, OJJDP, and OVC—to ensure jurisdictions have access to expertise and resources for addressing community violence.</p>
					<p class="our-story__p">This initiative builds partnerships among community residents, local government, law enforcement, hospitals, victim service providers, community-based organizations,researchers, and other stakeholders to <a href="#">help prevent and reduce violent crime in our communities</a>.</p>
				</div>
				<div class="our-story__media">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/img/post-3.webp" alt="" class="our-story__img">
				</div>
			</div>
		</section>
		<section class="mission">
			<h2 class="mission__header">Mission</h2>
			<p class="mission__statement">CVIPI is an evidence-informed investment that <em>funds and supports local ecosystem</em> to prevent community violence through proven violent crime reduction strategies.</p>
		</section>
		<section class="we-serve">
			<header class="we-serve__header">
				<p class="we-serve__eyebrow">CVIPI Across the Nation</p>
				<h2 class="we-serve__heading">Who <em>we serve.</em></h2>
				<p class="we-serve__description">In FY 2022 and FY 2023, OJP awarded nearly $200 million in CVIPI grants, partially funded
				by the Bipartisan Safer Communities Act. This historic level of funding reflects the federal
				government’s commitment to evidence-based, community-driven approaches to
				reducing violence. Click any marker to learn more about the organization.</p>
			</header>
			<div class="we-serve__details">
				<div class="we-serve__stats">
					<span class="we-serve__number">76<em>+</em></span>
					<p class="we-serve__number-details">Communities</p>
				</div>
				<div class="we-serve__stats">
					<span class="we-serve__number">$200<em>M</em></span>
					<p class="we-serve__number-details">Federal Investment</p>
				</div>
				<div class="we-serve__stats">
					<span class="we-serve__number">6</span>
					<p class="we-serve__number-details">Grantee Types</p>
				</div>
			</div>
			<div class="we-serve__filters">
				<?php echo cvipi_render_map_marker_filters(); ?>
			</div>

			<?php echo cvipi_render_marker_map(); ?>

		
		</section>
	</div>

	<section class="doj">
		<div class="doj__container">
				<header class="doj__header">
					<p class="doj__eyebrow">Collaborative Leadership</p>
					<h2 class="doj__heading">DOJ Components <em>Working Together</em></h2>
					<p class="doj__subheading">Several DOJ components collaborate on the initiative to ensure jurisdictions have access to expertise and resources for addressing community violence.</p>
				</header>
				<div class="doj__content">
					<a class="doj__components" href="https://bja.ojp.gov/" target="_blank">
						<h2 class="doj__heading">BJA</h2>
						<p class="doj__name">Bureau of Justice Assistance</p>
					</a>
					<a class="doj__components" href="https://nij.ojp.gov/" target="_blank">
						<h2 class="doj__heading">NIJ</h2>
						<p class="doj__name">National Institute of Justice</p>
					</a>
					<a class="doj__components" href="https://ojjdp.ojp.gov/" target="_blank">
						<h2 class="doj__heading">OJJDP</h2>
						<p class="doj__name">Office of Juvenile Justice &amp; Delinquency Prevention</p>
					</a>
					<a class="doj__components" href="https://ovc.ojp.gov/" target="_blank">
						<h2 class="doj__heading">OVC</h2>
						<p class="doj__name">Office for Victims of Crime</p>
					</a>
				</div>
			</div>

	</section>
</main>

<?php
get_footer();
