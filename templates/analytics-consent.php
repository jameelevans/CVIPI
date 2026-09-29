<?php defined( 'ABSPATH' ) || exit; ?>
<section class="analytics-consent" data-analytics-consent hidden role="region" aria-labelledby="analytics-consent-title" aria-describedby="analytics-consent-description">
  <div class="analytics-consent__inner">
    <div class="analytics-consent__copy">
      <h2 id="analytics-consent-title" tabindex="-1">Your privacy matters.</h2>
      <p id="analytics-consent-description">May we use Google Analytics to understand visits, downloads, and links? Analytics stays off unless you accept. No advertising tracking.</p>
      <details class="analytics-consent__details">
        <summary>Privacy details</summary>
        <p>With your permission, Google Analytics receives usage and browser information and uses analytics cookies. We do not send form answers, names, email addresses, or search terms. Your choice is saved on this browser for six months. Change or withdraw it anytime using Analytics preferences in the footer.</p>
        <p>Forms, security checks such as reCAPTCHA, and essential hosting logs operate separately from optional analytics. <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Google Privacy Policy<span class="sr-only"> (opens in a new tab)</span></a><?php if ( get_privacy_policy_url() ) : ?> &middot; <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Site Privacy Policy</a><?php endif; ?></p>
      </details>
      <p class="analytics-consent__state" data-analytics-choice></p>
    </div>
    <div class="analytics-consent__actions">
      <button type="button" data-analytics-accept>Accept</button>
      <button type="button" data-analytics-decline>Deny</button>
      <button class="analytics-consent__cancel" type="button" data-analytics-close hidden>Keep current choice</button>
    </div>
  </div>
</section>
<p class="sr-only" role="status" aria-live="polite" data-analytics-status></p>
