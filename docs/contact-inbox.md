# Contact Inbox

The existing WPForms Contact form remains the public interface. `inc/contact-messages.php`
saves sanitized entries after WPForms validation and before its final error check. Storage
failure blocks the success response. Spam and CAPTCHA failures are not saved. The default
WPForms notification is suppressed only for the configured Contact form; its successful
completion hook sends the two custom notifications through `wp_mail()` / WP Mail SMTP.

## Configuration

The per-site `cvipi_contact_form` option contains the WPForms form ID and field IDs:

```json
{"id":219,"fields":{"name":1,"email":4,"organization":5,"role":6,"subject":7,"message":3}}
```

Local uses form 219. Staging uses form 220, with field IDs name=1, email=2,
organization=4, role=5, subject=6, message=3. Do not copy local database settings to staging
or live. If rebuilding a form or deleting/recreating fields, update this mapping first.

`cvipi_contact_notifications` stores team recipients, individual enabled flags, and editable
subject/heading/message templates. These are managed under Contact Messages > Notifications.
Existing notification recipients are preserved during initial setup. Sender confirmations
never reflect visitor input, and are limited to three per recipient per hour. Team messages
include the submitted details and a private admin link; replies go to the sender.

Messages use the private `cvipi_message` post type. Only users with `manage_options` can
access the inbox or settings. There are no public, REST, or standard export routes. Original
submission details are read-only; notes and workflow status are stored separately. Opening a
message marks it read. Identical submissions within five minutes reuse the existing record.
Notification attempt markers prevent retries and admin edits from sending duplicate email.
Transport acceptance is shown separately from delivery; failed emails never delete entries.

WordPress Trash and its normal retention policy apply. Message data remains in the database
if the theme changes, but this theme must be active for capture and the inbox UI to function.
Existing emails are not automatically imported. Staff should agree a retention schedule and
include this stored contact data in the site's privacy policy and backup access controls.

## Verification

Run on a local environment with WordPress loaded:

```sh
wp eval-file wp-content/themes/cvipi/tests/contact-inbox.php
```

The suite intercepts outgoing mail, restores notification settings, and moves its test records
to Trash. It checks validation, deduplication, private access, notes, toggles, mail failure, and
the existing Story email defaults. Complete a separate browser test to verify the actual
WPForms submission, Google CAPTCHA, and final mail transport. Do not send QA messages to the
company inbox without approval. No credentials belong in the theme or its test files.
