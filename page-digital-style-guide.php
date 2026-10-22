<?php
/**
 * Template Name: CVIPI Digital Style Guide (Unlisted)
 * Standalone Figma reference layout, using the theme's branding and download pages.
 * @package cvipi
 */
$palette = cvipi_style_guide_palette();
$checklist = cvipi_style_guide_checklist_path();
$alphabet = 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z';
$backgrounds = array(
  'sand' => array( 'label' => 'On Sand', 'description' => 'Our primary background. Most pages, cards and long-form content sit on sand.', 'note' => 'Watch out: Muted Text and Copper Light both fail on sand. On white they are limited to large text; check each dark-blue pairing before using them for body text.' ),
  'teal' => array( 'label' => 'On Teal Mid', 'description' => 'Our deep blue band. Voice, pull quotes and feature sections.', 'note' => 'Watch out: Copper, Copper Light, and Muted Text all fail on this background. Choose a higher-contrast pairing for text.' ),
  'midnight' => array( 'label' => 'On Midnight Teal', 'description' => 'Our darkest brand blue. Heroes, navigation and dark panels.', 'note' => 'Watch out: Copper, Copper Light, and Muted Text are for large text only here, not body copy.' ),
  'deep' => array( 'label' => 'On Ink', 'description' => 'Near-black. Full-bleed sections, imagery sections and photo overlays.', 'note' => 'Copper, Copper Light, and Muted Text pass for body text on Deep. Do not assume they also pass on the lighter blues.' ),
  'white' => array( 'label' => 'On White', 'description' => 'Used sparingly, mostly cards and tables sitting on top of sand.', 'note' => 'Watch out: Copper, Copper Light, and Muted Text meet the large-text threshold only. Choose a darker color for body text.' ),
);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class( 'digital-style-guide-page' ); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-shortcut" href="#main-content">Skip to main content</a>
<div class="style-guide" data-style-guide>
  <header class="style-guide__mobile-bar">
    <a class="style-guide__mobile-brand" href="#overview" aria-label="CVIPI Digital Style Guide, overview"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/cvipi-logo-white.webp' ) ); ?>" alt="CVIPI" width="3495" height="1842"></a>
    <button class="style-guide__menu" type="button" data-guide-menu aria-haspopup="dialog" aria-controls="style-guide-menu" aria-expanded="false" aria-label="Open style guide navigation" title="Open navigation" hidden><i data-guide-icon="menu" aria-hidden="true"></i></button>
  </header>
  <aside class="style-guide__sidebar"><?php get_template_part( 'templates/style-guide-navigation' ); ?></aside>
  <dialog id="style-guide-menu" class="style-guide__drawer" data-guide-drawer aria-label="Style guide navigation">
    <button type="button" class="style-guide__drawer-close" data-guide-close aria-label="Close style guide navigation" title="Close navigation"><i data-guide-icon="x" aria-hidden="true"></i></button>
    <?php get_template_part( 'templates/style-guide-navigation' ); ?>
  </dialog>
  <div class="style-guide__progress" aria-hidden="true"><span data-guide-progress></span></div>
  <main id="main-content" class="style-guide__main" tabindex="-1">
    <section id="overview" class="style-guide__overview" aria-labelledby="guide-overview-title" tabindex="-1">
      <img class="style-guide__overview-image" src="<?php echo esc_url( cvipi_style_guide_image_url( 'overview.jpg' ) ); ?>" alt="" fetchpriority="high" decoding="async" width="1400" height="787">
      <div class="style-guide__overview-content" data-guide-reveal>
        <p class="style-guide__eyebrow">CVIPI &middot; Design Direction 2026</p>
        <blockquote class="style-guide__opening-quote">
          <p>&ldquo;The Community-Based Violence Intervention and Prevention Initiative (CVIPI) exists to ensure that every community-based organization working to end community violence has access to the expertise, the tools, and the trust they need to succeed &mdash; from day one of their grant.&rdquo;</p>
          <cite>&mdash; CVIPI Design Brief &middot; EO 14338 &middot; America by Design Initiative</cite>
        </blockquote>
        <h1 id="guide-overview-title" class="style-guide__overview-title"><span>One Home.</span><span>Every</span><span><em>Resource.</em></span></h1>
        <p class="style-guide__overview-copy">Inspired by the editorial clarity of <em>America by Design</em>, its cinematic depth of documentary journalism, and the human warmth of our grant managers, this redesign of the CVIPI visual identity is authoritative, warm, and caring. It's deeply rooted in your stories. Preventing community-based violence inspires some of the hardest work in public safety. Use this guidance to enhance the CVIPI brand and demonstrate impact as you share your success.</p>
        <ul class="style-guide__overview-meta" aria-label="Brand summary">
          <li>Fraunces</li>
          <li>Syne</li>
          <li>#0D3344 &middot; Midnight Teal</li>
        </ul>
      </div>
    </section>

    <section id="templates" class="style-guide__section style-guide__templates" aria-labelledby="guide-templates-title" tabindex="-1">
      <header class="style-guide__section-header" data-guide-reveal><p class="style-guide__eyebrow">02 &mdash; Templates</p><h2 id="guide-templates-title" class="style-guide__heading">Co-Branded<br><em>Collateral.</em></h2></header>
      <div class="style-guide__template-intro" data-guide-reveal>
        <div><p class="style-guide__lede">The primary brand is that of <strong>each grantee.</strong> This guide is designed to support the inclusion and incorporation of the CVIPI brand elements in your organization's materials, as there is no common logo mark.</p><p class="style-guide__template-note">These pieces are designed to clearly showcase our partnership. These templates pair your organization's brand with ours so we can share messages, stories, and resources to show our common goals.</p></div>
        <div><h3 class="style-guide__benefit-heading">Benefits of sharing with<br><em>the CVIPI Collaborative</em></h3><ul class="style-guide__benefits"><li>Elevating your success and expertise with wider reach and increased engagement</li><li>Demonstrating the strengths of a national initiative creating local impact</li><li>Boosting CVIPI's recognition through community collaborations and partnerships</li></ul></div>
      </div>
      <div class="style-guide__downloads" data-guide-reveal>
        <h3>Download a template</h3><p>Start from a CVIPI-branded file so your materials read as co-branded from the first slide or page.</p>
        <div class="style-guide__download-grid">
          <?php foreach ( array( 'presentation' => 'Presentation Deck Template', 'document' => 'Report Template' ) as $key => $label ) : $template = cvipi_grantee_templates()[$key]; $file = cvipi_grantee_template_path( $template ); ?>
            <a class="style-guide__download-card" href="<?php echo esc_url( cvipi_grantee_template_url( $template ) ); ?>" download="<?php echo esc_attr( $template['filename'] ); ?>" title="<?php echo esc_attr( 'Download the CVIPI ' . $label ); ?>">
              <span class="style-guide__file-icon"><i data-guide-icon="<?php echo 'presentation' === $key ? 'presentation' : 'file-text'; ?>" aria-hidden="true"></i></span>
              <span class="style-guide__download-body"><strong><?php echo esc_html( $label ); ?></strong><span><?php echo esc_html( '.' . strtoupper( pathinfo( $template['filename'], PATHINFO_EXTENSION ) ) ); ?><?php if ( $file ) : ?> &middot; <?php echo esc_html( size_format( filesize( $file ), 1 ) ); ?><?php endif; ?></span></span>
              <i data-guide-icon="download" aria-hidden="true"></i>
            </a>
          <?php endforeach; ?>
        </div>
        <section class="style-guide__template-help" aria-labelledby="guide-template-help-title">
          <div class="style-guide__template-help-inner">
            <h3 id="guide-template-help-title">Questions about the template?</h3>
            <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact the CVIPI team <i data-guide-icon="arrow-up-right" aria-hidden="true"></i></a>
          </div>
        </section>
      </div>
    </section>

    <section id="voice" class="style-guide__section style-guide__voice" aria-labelledby="guide-voice-title" tabindex="-1">
      <div data-guide-reveal><p class="style-guide__eyebrow">03 &mdash; Voice</p><h2 id="guide-voice-title" class="style-guide__heading">How we <em>sound.</em></h2><p class="style-guide__voice-intro">The CVIPI brand is:</p><ul class="style-guide__voice-words"><?php foreach ( array( 'Encouraging', 'Intentional', 'Meaningful', 'Resilient', 'Creative', 'Inspirational' ) as $word ) : ?><li><?php echo esc_html( $word ); ?></li><?php endforeach; ?></ul></div>
    </section>

    <section id="colors" class="style-guide__section style-guide__colors" aria-labelledby="guide-colors-title" tabindex="-1">
      <header class="style-guide__section-header" data-guide-reveal><p class="style-guide__eyebrow">04 &mdash; Color</p><h2 id="guide-colors-title" class="style-guide__heading">A color palette that earns<br><em>trust without boundaries.</em></h2><p class="style-guide__description">Midnight Teal carries authority with more warmth than navy. Copper signals action and resilience &mdash; every CTA, every call to engage. Sand and bone replace clinical white with an editorial ground that feels human, and grounding.</p></header>
      <div data-guide-reveal>
        <ul class="style-guide__brand-palette">
          <?php foreach ( array( 'deep', 'midnight', 'copper', 'sand' ) as $key ) : $color = $palette[$key]; ?>
            <li class="style-guide__swatch"><div class="style-guide__swatch-color" style="background:var(<?php echo esc_attr( $color['token'] ); ?>)" aria-hidden="true"></div><dl class="style-guide__swatch-info"><dt><?php echo esc_html( $color['name'] ); ?></dt><dd class="style-guide__hex"><?php echo esc_html( $color['hex'] ); ?></dd><dd><?php echo esc_html( $color['use'] ); ?></dd></dl></li>
          <?php endforeach; ?>
        </ul>
        <h3 class="style-guide__label">Teal + Copper Scale</h3>
        <ul class="style-guide__color-scale"><?php foreach ( array( 'deep', 'midnight', 'teal', 'azure', 'blue', 'copper', 'carmine' ) as $key ) : $color = $palette[$key]; ?><li><span style="background:var(<?php echo esc_attr( $color['token'] ); ?>)" aria-hidden="true"></span><span class="style-guide__hex"><?php echo esc_html( $color['hex'] ); ?></span></li><?php endforeach; ?></ul>
      </div>

      <div class="style-guide__accessibility">
        <header class="style-guide__accessibility-header" data-guide-reveal>
          <div><h3>Every pairing,<br><em>checked for Section 508.</em></h3><p>CVIPI is federally funded, so our materials have to meet Section 508. In practice that means one thing: text has to stand out clearly from whatever sits behind it. Below, every text color is checked against the five backgrounds we actually build on &mdash; sand first, since that carries most of our pages.</p><ul class="style-guide__contrast-key"><li><span class="style-guide__verdict-dot style-guide__verdict-dot--pass"></span>Use freely &mdash; safe at any size</li><li><span class="style-guide__verdict-dot style-guide__verdict-dot--large"></span>Large text only &mdash; 24px, or 19px bold</li><li><span class="style-guide__verdict-dot style-guide__verdict-dot--fail"></span>Avoid for text</li></ul></div>
          <div class="style-guide__accessibility-actions">
            <?php if ( $checklist ) : ?><a class="style-guide__resource-button" href="<?php echo esc_url( cvipi_style_guide_checklist_url() ); ?>" download="CVIPI-508-Compliance-Checklist.docx"><i data-guide-icon="download" aria-hidden="true"></i>Download the 508 Checklist</a><span>.DOCX &middot; <?php echo esc_html( size_format( filesize( $checklist ), 0 ) ); ?></span><?php endif; ?>
            <a class="style-guide__resource-button style-guide__resource-button--outline" href="https://www.tpgi.com/color-contrast-checker/" target="_blank" rel="noopener noreferrer"><i data-guide-icon="external-link" aria-hidden="true"></i>Get the Contrast Checker<span class="sr-only"> (opens in a new tab)</span></a><span>Free &middot; Windows &amp; macOS</span>
          </div>
        </header>
        <?php foreach ( $backgrounds as $key => $group ) : $background = $palette[$key]; $light = in_array( $key, array( 'sand', 'white' ), true ); $foregrounds = $light ? array( 'deep', 'midnight', 'teal', 'body', 'copper', 'azure', 'blue', 'copper-light', 'muted' ) : array( 'white', 'sand', 'carmine', 'copper', 'copper-light', 'blue', 'body', 'azure', 'muted' ); ?>
          <details class="style-guide__contrast-group"<?php if ( 'sand' === $key ) : ?> open<?php endif; ?>>
            <summary><span class="style-guide__background-chip" style="background:var(<?php echo esc_attr( $background['token'] ); ?>)" aria-hidden="true"></span><span class="style-guide__group-copy"><strong><?php echo esc_html( $group['label'] ); ?> <span><?php echo esc_html( $background['hex'] ); ?></span></strong><span><?php echo esc_html( $group['description'] ); ?></span><span class="style-guide__group-note"><?php echo esc_html( $group['note'] ); ?></span></span><span class="style-guide__accordion-icon"><i data-guide-icon="plus" aria-hidden="true"></i></span></summary>
            <div class="style-guide__contrast-content"><div class="style-guide__pair-grid">
              <?php foreach ( $foregrounds as $foreground_key ) :
                $foreground = $palette[$foreground_key];
                $ratio = cvipi_style_guide_contrast( $foreground['hex'], $background['hex'] );
                $status = $ratio >= 4.5 ? 'pass' : ( $ratio >= 3 ? 'large' : 'fail' );
                $verdict = array( 'pass' => 'Use freely', 'large' => 'Large text only', 'fail' => 'Avoid for text' )[$status];
                $guidance = array( 'pass' => 'Suitable for body text and headings on this background.', 'large' => 'For headlines 24px or larger, or at least 19px bold. Not body text.', 'fail' => 'Choose a higher-contrast color for text on this background.' )[$status];
              ?>
                <div class="style-guide__pair" data-guide-contrast="<?php echo esc_attr( $status ); ?>">
                  <div class="style-guide__pair-demo" style="background:var(<?php echo esc_attr( $background['token'] ); ?>);color:var(<?php echo esc_attr( $foreground['token'] ); ?>)" aria-hidden="true"><span>Aa</span><span>Sample body text</span></div>
                  <div class="style-guide__pair-info"><h4><?php echo esc_html( $foreground['name'] ); ?><span><?php echo esc_html( $foreground['hex'] ); ?></span></h4><p class="style-guide__pair-verdict"><span class="style-guide__verdict-dot style-guide__verdict-dot--<?php echo esc_attr( $status ); ?>" aria-hidden="true"></span><?php echo esc_html( $verdict ); ?></p><p class="style-guide__pair-ratio"><?php echo esc_html( number_format( $ratio, 2 ) ); ?>:1 contrast</p><p><?php echo esc_html( $guidance ); ?></p></div>
                </div>
              <?php endforeach; ?>
            </div></div>
          </details>
        <?php endforeach; ?>
        <div class="style-guide__accessibility-rules" data-guide-reveal>
          <div><span>01</span><h4>Copper changes with its background</h4><p>Check the pairing before setting copper type. A color that works on a dark background may not be readable on sand or blue.</p></div>
          <div><span>02</span><h4>Color is never the only cue</h4><p>Links, alerts and required fields need a second signal &mdash; an underline, an icon or a label. Color-blind readers and black-and-white printouts depend on it.</p></div>
          <div><span>03</span><h4>Photos need a scrim</h4><p>Text over a photograph needs a dark gradient behind it, so it stays readable even where the image is at its lightest.</p></div>
        </div>
        <p class="style-guide__contrast-source">Ratios follow <a href="https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html" target="_blank" rel="noopener noreferrer">WCAG text contrast guidance<span class="sr-only"> (opens in a new tab)</span></a>. Color contrast is one part of accessibility, not a complete Section 508 review.</p>
      </div>
    </section>

    <section id="typography" class="style-guide__section style-guide__typography" aria-labelledby="guide-typography-title" tabindex="-1">
      <header class="style-guide__section-header" data-guide-reveal><p class="style-guide__eyebrow">05 &mdash; Typography</p><h2 id="guide-typography-title" class="style-guide__heading">Two voices,<br><em>one story.</em></h2><div class="style-guide__description"><p>Our typography and font choices are designed to emote and inform without feeling judgmental.</p><p>Fraunces gives us an optical personality in our serif style. It adds editorial weight to headlines that feels cinematic at display scale.</p><p>Syne, our sans-serif font, carries geometric clarity and confidence throughout our body text and design elements.</p></div></header>
      <div class="style-guide__typefaces" data-guide-reveal>
        <div class="style-guide__typeface"><p class="style-guide__label">Display / Headlines</p><h3>Fraunces</h3><p class="style-guide__type-role">Optical serif &middot; editorial cinematic presence</p><p class="style-guide__serif-specimen">Every<br>Resource.</p><p class="style-guide__italic-specimen">A trusted guide for every<br>grantee who needs it.</p><p class="style-guide__alphabet"><?php echo esc_html( $alphabet ); ?><br><?php echo esc_html( strtolower( $alphabet ) ); ?><br>0 1 2 3 4 5 6 7 8 9 &amp; @ # $ % ! ?</p></div>
        <div class="style-guide__typeface"><p class="style-guide__label">Body / UI</p><h3>Syne</h3><p class="style-guide__type-role">Angular geometric clarity</p><p class="style-guide__sans-specimen">Safer<br>Communities</p><p class="style-guide__type-copy">CVIPI provides direct technical assistance, training, and resources to CBO grant managers working in community violence intervention.</p><p class="style-guide__alphabet"><?php echo esc_html( $alphabet ); ?><br><?php echo esc_html( strtolower( $alphabet ) ); ?><br>0 1 2 3 4 5 6 7 8 9 &amp; @ # $ % ! ?</p></div>
      </div>
      <div class="style-guide__type-scales" data-guide-reveal>
        <div><h3 class="style-guide__label">Display Scale &mdash; Fraunces</h3>
          <?php foreach ( array( array( 'Display', '96px / .88', 'Resource', 'display' ), array( 'H1', '64px / .92', 'Our Mission', 'h1' ), array( 'H2', '44px / 1.0', 'TTA & Training', 'h2' ), array( 'H3', '30px / 1.15', 'Grant Reporting Hub', 'h3' ), array( 'H4', '20px / 1.3', 'Compliance Resources', 'h4' ), array( 'Italic Pull', '28px / 1.5', '"Every community deserves the resources to build lasting safety."', 'pull' ) ) as $sample ) : ?>
            <div class="style-guide__type-row"><p class="style-guide__type-spec"><?php echo esc_html( $sample[0] ); ?><span><?php echo esc_html( $sample[1] ); ?></span></p><p class="style-guide__scale-sample style-guide__scale-sample--<?php echo esc_attr( $sample[3] ); ?>"><?php echo esc_html( $sample[2] ); ?></p></div>
          <?php endforeach; ?>
        </div>
        <div><h3 class="style-guide__label">Body Scale &mdash; Syne</h3>
          <?php foreach ( array( array( 'Body LG', '18px / 1.75', 'We connect grantees to the expertise they need to succeed.', 'body-lg' ), array( 'Body MD', '15px / 1.72', 'Our TTA team delivers direct, hands-on support for CBO grant managers navigating BJA requirements and community-based programs.', 'body-md' ), array( 'Body SM', '13px / 1.6', 'Supporting violence intervention programs funded under EO 14338 and the America by Design initiative.', 'body-sm' ), array( 'Label', '11px / .06em', 'Resource Category', 'label' ), array( 'Eyebrow', '10px / .18em', '02 - Programs', 'eyebrow' ), array( 'Caption', '11px / 1.5', 'Community safety workers in a violence intervention training session, Washington D.C.', 'caption' ) ) as $sample ) : ?>
            <div class="style-guide__type-row"><p class="style-guide__type-spec"><?php echo esc_html( $sample[0] ); ?><span><?php echo esc_html( $sample[1] ); ?></span></p><p class="style-guide__scale-sample style-guide__scale-sample--<?php echo esc_attr( $sample[3] ); ?>"><?php echo esc_html( $sample[2] ); ?></p></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="imagery" class="style-guide__section style-guide__imagery" aria-labelledby="guide-imagery-title" tabindex="-1">
      <header class="style-guide__section-header" data-guide-reveal><p class="style-guide__eyebrow">06 &mdash; Imagery</p><h2 id="guide-imagery-title" class="style-guide__heading">Photography that<br><em>tells the story.</em></h2><p class="style-guide__description">To showcase CVIPI's mission and the people we serve, we feature authentic, documentary-style imagery of real community organizers in real neighborhoods &mdash; doing real work to bring a light into our communities. Always warm, desaturated and editorial, we try to avoid using stock or staged shots. Every image is treated to feel cinematic yet grounded, and definitely not cold and clinical.</p></header>
      <div class="style-guide__collage" data-guide-reveal>
        <?php $photos = array( array( 'community.jpg', 'Community gathering', 'Community / Gathering & connection', 1400, 787 ), array( 'collaboration.jpg', 'Collaborative work session', 'People / Working together', 1400, 933 ), array( 'advising.jpg', 'Professional advising', 'TTA / Advisor & grantee', 1335, 2000 ), array( 'training.jpg', 'Training session', 'Training / Capacity building', 1400, 933 ), array( 'neighborhood.jpg', 'Urban neighborhood', 'Place / Neighborhood & community', 1400, 933 ) ); foreach ( array( array_slice( $photos, 0, 2 ), array_slice( $photos, 2 ) ) as $row => $row_photos ) : ?>
        <div class="style-guide__collage-row style-guide__collage-row--<?php echo 0 === $row ? 'primary' : 'secondary'; ?>">
          <?php foreach ( $row_photos as $photo ) : ?>
          <figure><img src="<?php echo esc_url( cvipi_style_guide_image_url( $photo[0] ) ); ?>" alt="<?php echo esc_attr( $photo[1] ); ?>" width="<?php echo esc_attr( $photo[3] ); ?>" height="<?php echo esc_attr( $photo[4] ); ?>" loading="lazy" decoding="async"><figcaption><?php echo esc_html( $photo[2] ); ?></figcaption></figure>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <dl class="style-guide__image-notes" data-guide-reveal>
        <div><dt>Perspective</dt><dd>Eye level and intimate &mdash; human connection over institutional distance</dd></div>
        <div><dt>Treatment</dt><dd>Desaturate 40&ndash;55%, +5% contrast, teal veil overlay, subtle film grain</dd></div>
        <div><dt>Mood</dt><dd>Warm, editorial, grounded. Never clinical, posed, or stock-looking</dd></div>
        <div><dt>Avoid</dt><dd>Handshakes, suits, clich&eacute;d diversity shoots, white backgrounds, generic skylines</dd></div>
      </dl>
    </section>

    <section id="contact" class="style-guide__section style-guide__contact" aria-labelledby="guide-contact-title" tabindex="-1">
      <div data-guide-reveal><h2 id="guide-contact-title">Questions about<br><em>the brand?</em></h2><a href="mailto:CVIPICollab@icf.com">CVIPICollab@icf.com<i data-guide-icon="arrow-right" aria-hidden="true"></i></a></div>
    </section>
  </main>
</div>
<?php wp_footer(); ?>
</body>
</html>
