<?php
/** Shared desktop sidebar and native mobile dialog navigation. */
defined( 'ABSPATH' ) || exit;
$sections = array( 'overview' => 'Overview', 'templates' => 'Templates', 'voice' => 'Voice', 'colors' => 'Colors', 'typography' => 'Typography', 'imagery' => 'Imagery', 'contact' => 'Contact' );
?>
<div class="style-guide__brand"><a href="#overview" aria-label="CVIPI Digital Style Guide, overview"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/cvipi-logo-white.webp' ) ); ?>" alt="CVIPI" width="3495" height="1842"><span>Design Direction 2026</span></a></div>
<div class="style-guide__sidebar-actions">
  <a href="mailto:CVIPICollab@icf.com"><i data-guide-icon="mail" aria-hidden="true"></i>Contact</a>
  <a href="#templates"><i data-guide-icon="download" aria-hidden="true"></i>Download Templates</a>
</div>
<nav class="style-guide__navigation" aria-label="Style guide sections">
  <p class="style-guide__nav-label">Foundation</p>
  <?php $number = 0; foreach ( $sections as $id => $label ) : $number++; ?>
    <a href="#<?php echo esc_attr( $id ); ?>" data-guide-section="<?php echo esc_attr( $id ); ?>"<?php if ( 'overview' === $id ) : ?> aria-current="location"<?php endif; ?>><span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $number ) ); ?></span><?php echo esc_html( $label ); ?></a>
  <?php endforeach; ?>
</nav>
<div class="style-guide__privacy"><button type="button" data-analytics-preferences hidden>Analytics preferences</button></div>
