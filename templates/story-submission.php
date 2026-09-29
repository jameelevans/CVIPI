<?php defined( 'ABSPATH' ) || exit; ?>
<dialog id="story-dialog" class="story-dialog" aria-labelledby="story-dialog-title" data-story-dialog data-endpoint="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
  <button type="button" class="story-dialog__close" data-story-close aria-label="Close story submission" title="Close"><?php echo svg_icon( 'story-dialog__icon', 'close' ); ?></button>
  <div data-story-view="invite">
    <img class="story-dialog__image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/woman-with-group-smiling.webp' ); ?>" alt="" width="800" height="400">
    <div class="story-dialog__intro">
      <p class="story-dialog__eyebrow">Stories from the field</p>
      <h2 id="story-dialog-title" tabindex="-1">Create Your <em>Success Story</em></h2>
      <p>Share the work, research and events that are making a difference.</p>
      <button class="story-dialog__primary" type="button" data-story-create>Create Story <?php echo svg_icon( 'story-dialog__icon', 'arrow-right' ); ?></button>
      <button class="story-dialog__text-button" type="button" data-story-close>Not right now</button>
    </div>
  </div>
  <div class="story-dialog__form-view" data-story-view="form" hidden>
    <p class="story-dialog__eyebrow">Stories from the field</p>
    <h2 id="story-form-title" tabindex="-1">Create your <em>success story.</em></h2>
    <p class="story-dialog__summary">Share your community's progress. The CVIPI team will review your story before publication.</p>
    <form data-story-form>
      <div class="story-dialog__fields">
        <div><label for="story-name">Your name <span>(required)</span></label><input id="story-name" name="name" autocomplete="name" maxlength="120" required aria-describedby="story-name-error"><p class="story-dialog__field-error" id="story-name-error"></p></div>
        <div><label for="story-email">Email <span>(required)</span></label><input id="story-email" name="email" type="email" autocomplete="email" maxlength="254" required aria-describedby="story-email-error"><p class="story-dialog__field-error" id="story-email-error"></p></div>
        <div><label for="story-organization">Organization <span>(optional)</span></label><input id="story-organization" name="organization" autocomplete="organization" maxlength="180" aria-describedby="story-organization-error"><p class="story-dialog__field-error" id="story-organization-error"></p></div>
        <div><label for="story-location">Location <span>(required)</span></label><input id="story-location" name="location" placeholder="City, State" maxlength="180" required aria-describedby="story-location-error"><p class="story-dialog__field-error" id="story-location-error"></p></div>
        <fieldset class="story-dialog__wide story-dialog__submission-type" aria-describedby="story-submission_type-error">
          <legend>Identify what you would like to submit <span>(required)</span></legend>
          <?php foreach ( cvipi_story_submission_types() as $value => $label ) : ?>
            <label class="story-dialog__radio" for="story-type-<?php echo esc_attr( $value ); ?>"><input type="radio" id="story-type-<?php echo esc_attr( $value ); ?>" name="submission_type" value="<?php echo esc_attr( $value ); ?>" required aria-describedby="story-submission_type-error"><span><?php echo esc_html( $label ); ?></span></label>
          <?php endforeach; ?>
          <p class="story-dialog__field-error" id="story-submission_type-error"></p>
        </fieldset>
        <div class="story-dialog__wide"><label for="story-title">Story title <span>(optional)</span></label><input id="story-title" name="title" maxlength="180" aria-describedby="story-title-error"><p class="story-dialog__field-error" id="story-title-error"></p></div>
        <div class="story-dialog__wide"><label for="story-story">Your story <span>(required)</span></label><textarea id="story-story" name="story" rows="6" minlength="80" maxlength="20000" required aria-describedby="story-story-hint story-story-error"></textarea><div class="story-dialog__story-note"><p id="story-story-hint">Include the work, its impact, and lessons learned. Please omit sensitive personal information.</p><span data-story-count>0 / 20,000</span></div><p class="story-dialog__field-error" id="story-story-error"></p></div>
      </div>
      <div class="story-dialog__trap" aria-hidden="true"><label for="story-website">Website</label><input id="story-website" name="website" tabindex="-1" autocomplete="off"></div>
      <label class="story-dialog__consent" for="story-consent"><input id="story-consent" name="consent" type="checkbox" value="yes" required aria-describedby="story-consent-error"><span>I have permission to share this story and agree that CVIPI may contact me, edit it, and publish it following review.</span></label>
      <p class="story-dialog__field-error" id="story-consent-error"></p>
      <div class="story-dialog__verification"><div data-story-captcha></div><button class="story-dialog__text-button" type="button" data-story-retry hidden>Retry verification</button></div>
      <p class="story-dialog__status" data-story-status role="status" aria-live="polite" tabindex="-1"></p>
      <div class="story-dialog__actions"><p>Your email stays private.</p><button class="story-dialog__primary" type="submit" data-story-submit disabled>Submit Story <?php echo svg_icon( 'story-dialog__icon', 'arrow-right' ); ?></button></div>
    </form>
  </div>
  <div class="story-dialog__intro story-dialog__thanks" data-story-view="thanks" hidden>
    <p class="story-dialog__eyebrow">Story received</p>
    <h2 id="story-thanks-title" tabindex="-1">Thank you for <em>sharing.</em></h2>
    <p>Your story is with the CVIPI team for review. We may reach out using the email you provided.</p>
    <button class="story-dialog__primary" type="button" data-story-close>Done</button>
  </div>
</dialog>
