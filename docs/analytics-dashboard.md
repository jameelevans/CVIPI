# CVIPI Admin Analytics

## Access and source

- WordPress menu: Analytics (chart-line icon), requires `manage_options`.
- Reports read the live GA4 property `555256472`, hostname `cvipi.info`, excluding `/staging/`. Local/staging admin previews read that same property; they do not collect local/staging visits.
- Google service identity: `cvipi-analytics-dashboard@cvipi-503218.iam.gserviceaccount.com`. Property-only Viewer, no cost or revenue access, no Cloud IAM roles.
- Official `google/auth` SDK, locked Composer dependencies in `inc/analytics-dashboard/`. No client-side Google tokens or keys.
- `CVIPI_ANALYTICS_CREDENTIALS` in wp-config must point to a private JSON file outside ABSPATH, with file permissions 600 and containing directory 700. Never add this file to Git or theme backups.
- Live private path: `/home1/rrgvvfmy/.config/cvipi/analytics-reader.json`.
- Local private path: `/Users/jameelevans/.config/cvipi/analytics-reader.json`.

## Metrics and reporting

The Analytics Data API (`batchRunReports`) is the source of all figures, with metadata discovery to handle newly created custom dimensions. Native fields provide file names and link destinations. New form-name and link-type definitions are used automatically once Google's API exposes them.

- Active visitors: GA4 activeUsers, deduplicated across the complete period. Do not sum daily visitors.
- Visits: sessions. Views: screenPageViews.
- Average engaged time per visit: userEngagementDuration / sessions. Average visit length: averageSessionDuration. Engaged visits: engagementRate.
- Downloads: file_download eventCount, representing link clicks, not proof of completed downloads. Download people: totalUsers for that file/event.
- Successful forms: form_success eventCount, not form starts or validation failures.
- Standard periods: last 7, 28 or 90 completed days, compared with an immediately preceding equal-length period. Today is partial, and never compared to yesterday as percentage growth.
- Dates follow the GA property timezone returned by Google (currently America/Los_Angeles), not the WordPress or browser timezone.
- Detailed tables return the top 100 rows per report. Page filtering applies to the returned pages. CSV includes returned rows, limits and source timestamps.
- Tracking began September 29, 2026. No prior history is backfilled. Reports cover consenting live-site visitors; signed-in administrators are excluded by the existing collection layer.

Successful reports are cached for 15 minutes and retained for 24 hours for clearly labeled outage fallback. Forced refresh has a 60-second floor, outages have a 60-second cooldown, and refreshes use a short lock. No cron or tracking runs when administrators are not requesting reports. A missing connection produces an error, not zero metrics. Privacy thresholding, sampling and grouped rows are surfaced when Google reports them.

## Build and tests

- Compile `assets/admin/analytics-dashboard.scss` to its sibling CSS using Sass. Imports the existing theme variables and self-hosted Fraunces/Syne fonts. Does not rebuild public assets.
- `node --test tests/analytics-dashboard.test.cjs`
- `php tests/analytics-dashboard-test.php`
- DOM tests: `node --test tests/analytics-dashboard-dom.test.cjs` with jsdom 24 available. Fixtures exist only inside tests; the admin dashboard never shows sample data.
- Verify PHP lint, real API reports, admin/non-admin access, date/filter buttons, desktop/mobile overflow, fonts/logo, empty/error states and CSV.

## Scoped deployment and rollback

Deploy only `inc/analytics-dashboard.php`, `inc/analytics-dashboard/` (including locked vendor dependencies), `assets/admin/analytics-dashboard.{css,js,scss}`, and the single dashboard require in `functions.php`. Preserve the live functions.php rather than replacing it with the different local version. No public tracking, consent, content or submission files are changed by this deployment.

For rollback remove the dashboard require from functions.php or restore the pre-dashboard live functions backup. The private credential can remain unused, or revoke its Google service account key and remove its property access when retiring the dashboard. Rotate keys by provisioning the replacement, verifying report access, then revoking the previous key.
