<?php
/**
 * * The template for displaying the footer
 *
 * @package cvipi
 */

?>
    <!-- Site footer: newsletter signup, footer navigation, legal copy, and back-to-top control. -->
    <footer class="footer">
       <div class="footer__container">
             <!-- Newsletter signup block. Form action is intentionally blank until an email provider is connected. -->
             <div class="footer__subscribe">
                <div class="footer__wrapper">
                    <header class="footer__header">
                        <p class="footer__subheading">Stay Connected</p>
                        <h2 class="footer__heading">Get updates on <em>what matters.</em></h2>
                        <p class="footer__description">Subscribe to the CVIPI newsletter for the latest resources, event announcements, and stories from the field.</p>
                    </header>
                    <form class="footer__form" action="">
                        <label class="sr-only" for="footer-email">Enter your email address</label>

                        <input class="footer__input" type="email" id="footer-email"  name="email"  placeholder="Enter your email address">

                        <button class="footer__btn" type="submit">Subscribe</button>
                    </form>

                    <p class="footer__privacy">We respect your privacy. Unsubscribe at any time.</p>
                </div>

            </div>
        </div>
        <!-- Primary footer content: brand summary, social links, navigation groups, and grant language. -->
        <div class="footer__main">
            <div class="footer__wrapper">
                <div class="footer__top">
                    <div class="footer__columns">
                        <header class="footer__header">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/cvipi-logo-white.webp" alt="<?php bloginfo( 'name' ); ?> logo" class="footer__logo">
                            <h3 class="footer__heading"><span class="sr-only">CVIPI </span>Your resource for building, managing, and sustaining community-led safety.</h3>
                            <ul class="social__list">
                                <li class="social__item">
                                    <a href="" class="social__link">
                                        <?php echo svg_icon('social__icon', 'x');?>
                                    </a>
                                </li>
                                <li class="social__item">
                                    <a href="" class="social__link">
                                        <?php echo svg_icon('social__icon', 'linkedin');?>
                                    </a>
                                </li>
                                <li class="social__item">
                                    <a href="" class="social__link">
                                        <?php echo svg_icon('social__icon', 'arrow-right');?>
                                    </a>
                                </li>
                            </ul>
                        </header>

                        <div class="footer__menu">
                            <h4 class="footer__cat-heading">Navigate</h4>
                            <nav class="footer__nav">
                                <ul class="footer__list">
                                    <li class="footer__item"><a href="" class="footer__link">Home</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">What is CVIPI?</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Resources</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Events</a></li>
                                </ul>
                            </nav>
                        </div>

                        <div class="footer__menu">
                            <h4 class="footer__cat-heading">Connect</h4>
                            <nav class="footer__nav">
                                <ul class="footer__list">
                                    <li class="footer__item"><a href="" class="footer__link">Our Stories</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Who We Serve</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Newsletter</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Contact Us</a></li>
                                </ul>
                            </nav>
                        </div>

                        <div class="footer__menu">
                            <h4 class="footer__cat-heading">legal</h4>
                            <nav class="footer__nav">
                                <ul class="footer__list">
                                    <li class="footer__item"><a href="" class="footer__link">Privacy Policy</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Terms of Use</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">Accessibility</a></li>
                                    <li class="footer__item"><a href="" class="footer__link">FOIA</a></li>
                                </ul>
                            </nav>
                        </div>


                    </div>
                </div>
                <div class="footer__bottom">
                <p class="footer__copyright">© 2026 Community Violence Intervention & Prevention Initiative. All rights
    reserved.</p>
                    <p class="footer__copyright">This project was supported by Grant No. 15PBJA-24-GK-04073-CVIP awarded by the Bureau of Justice Assistance. The Bureau of Justice Assistance is a component of the U.S. Department of Justice's Office of Justice Programs, which also
                    includes the Bureau of Justice Statistics, the National Institute of Justice, the Office of Juvenile Justice and Delinquency</p>
                </div> 
            </div>
        </div>
        <a href="#main-content" class="backtop" aria-label="Go back to the top"><?php echo svg_icon('backtop__icon', 'up');?><span class="sr-only">Back Top</span></a>
        
    </footer>
  


    <?php wp_footer(); ?>
</body>
</html>
