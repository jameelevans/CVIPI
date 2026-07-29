<?php
/**
 * * The template for displaying the footer
 *
 * @package cvipi
 */

?>
    <!-- Site footer: newsletter signup, footer navigation, legal copy, and back-to-top control. -->
    <footer class="footer">
       <?php if ( ! is_page( 'what-is-cvipi' ) ) : ?>
       <div class="footer__container">
             <!-- Newsletter signup block. Form action is intentionally blank until an email provider is connected. -->
             <div class="footer__subscribe">
                <div class="footer__wrapper">
                    <header class="footer__header">
                        <p class="footer__subheading">Stay Connected</p>
                        <h2 class="footer__heading">Get updates on <em>what matters.</em></h2>
                        <p class="footer__description">Subscribe to the CVIPI bulletin for the latest stories from the field.</p>
                    </header>
                    <?php $footer_mailchimp_list_id = get_option( 'mc_list_id' ); ?>
                    <form class="footer__form mc_signup_form" action="#footer-subscribe" method="post" id="footer-subscribe" data-list-id="<?php echo esc_attr( $footer_mailchimp_list_id ); ?>" data-footer-subscribe>
                        <input type="hidden" class="mc_submit_type" name="mc_submit_type" value="html">
                        <input type="hidden" name="mcsf_action" value="mc_submit_signup_form">
                        <?php wp_nonce_field( 'mc_submit_signup_form', '_mc_submit_signup_form_nonce', false ); ?>
                        <?php
                        if ( function_exists( 'mailchimp_sf_honeypot_field' ) ) {
                            mailchimp_sf_honeypot_field();
                        }
                        ?>
                        <div class="mc_message_wrapper footer__message" aria-live="polite"></div>

                        <div class="footer__form-step footer__form-step--email" data-footer-subscribe-email-step>
                            <label class="sr-only" for="footer-email">Enter your email address</label>
                            <input class="footer__input" type="email" id="footer-email" name="mc_mv_EMAIL" placeholder="Enter your email address" autocomplete="email" required data-footer-subscribe-email>
                        </div>

                        <div class="footer__form-step footer__form-step--names" hidden data-footer-subscribe-name-step>
                            <p class="footer__email-preview" data-footer-subscribe-email-preview></p>

                            <label class="sr-only" for="footer-first-name">First name</label>
                            <input class="footer__input" type="text" id="footer-first-name" name="mc_mv_FNAME" placeholder="First name" autocomplete="given-name" data-footer-subscribe-name>

                            <label class="sr-only" for="footer-last-name">Last name</label>
                            <input class="footer__input" type="text" id="footer-last-name" name="mc_mv_LNAME" placeholder="Last name" autocomplete="family-name" data-footer-subscribe-name>

                            <button class="footer__edit-email" type="button" data-footer-subscribe-edit-email>Edit email</button>
                        </div>

                        <button class="footer__btn mc_signup_submit_button" type="submit" data-footer-subscribe-submit>Continue</button>
                    </form>

                    <p class="footer__privacy">We respect your privacy. Unsubscribe at any time.</p>
                </div>

            </div>
        </div>
        <?php endif; ?>
        <!-- Primary footer content: brand summary, social links, navigation groups, and grant language. -->
        <div class="footer__main">
            <div class="footer__wrapper">
                <div class="footer__top">
                    <div class="footer__columns">
                        <header class="footer__header">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/cvipi-logo-white.webp" alt="<?php bloginfo( 'name' ); ?> logo" class="footer__logo">
                            <h3 class="footer__heading"><span class="sr-only">CVIPI </span>Your resource for building, managing, and sustaining community-led safety.</h3>

                        </header>

                        <div class="footer__menu">
                            <h4 class="footer__cat-heading">Navigate</h4>
                            <nav class="footer__nav">
                                <ul class="footer__list">
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '' ) ); ?>" class="footer__link">Home</a></li>
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '/what-is-cvipi' ) ); ?>" class="footer__link">What is CVIPI?</a></li>
                                    <?php if ( false ) : // Temporarily hidden until Resources and Events return to navigation. ?>
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '/resources' ) ); ?>" class="footer__link">Resources</a></li>
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '/events' ) ); ?>" class="footer__link">Events</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>

                        <div class="footer__menu">
                            <h4 class="footer__cat-heading">Connect</h4>
                            <nav class="footer__nav">
                                <ul class="footer__list">
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '/success-stories' ) ); ?>" class="footer__link">Our Stories</a></li>
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '/what-is-cvipi#who-we-serve' ) ); ?>" class="footer__link">Who We Serve</a></li>
                                    <li class="footer__item"><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="footer__link">Contact Us</a></li>
                                </ul>
                            </nav>
                        </div>

                        <div class="footer__menu">
                            <h4 class="footer__cat-heading">legal</h4>
                            <nav class="footer__nav">
                                <ul class="footer__list">
                                    <li class="footer__item"><a href="https://digital.gov/topics/accessibility" class="footer__link" target="_blank">Accessibility</a></li>
                                    <li class="footer__item"><a href="https://www.foia.gov/" target="_blank" class="footer__link">FOIA</a></li>
                                </ul>
                            </nav>
                        </div>


                    </div>
                </div>
                <div class="footer__bottom">
                <p class="footer__copyright">© 2026 Community Violence Intervention & Prevention Initiative. All rights
    reserved.</p>
                    <p class="footer__copyright">The Bureau of Justice Assistance is a component of the U.S. Department of Justice's Office of Justice Programs, which also includes the Bureau of Justice Statistics, the National Institute of Justice, the Office of Juvenile Justice and Delinquency Prevention, the Office for Victims of Crime, and the SMART Office. Points of view or opinions in this on this website are those of the author and do not necessarily represent the official position or policies of the U.S. Department of Justice.</p>
                </div> 
            </div>
        </div>
        <a href="#main-content" class="backtop" aria-label="Go back to the top"><?php echo svg_icon('backtop__icon', 'up');?><span class="sr-only">Back Top</span></a>
        
    </footer>
  


    <?php wp_footer(); ?>
</body>
</html>
