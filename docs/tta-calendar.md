# TTA Calendar

The local page is `/tta-calendar/`, using `page-tta-calendar.php`. It reuses the existing posts header and site footer. On another environment, create a page named TTA Calendar with the `tta-calendar` slug and select the TTA Calendar template. Navigation is unchanged.

Published, non-password-protected Events appear automatically using their existing date, time, type, topic, location, presenter, excerpt, RSVP link, and featured image. Drafts, pending submissions, private Events, and scheduled WordPress posts are excluded until published.

The Event Details editor adds optional End Date, End Time, and Calendar Time Zone fields. An all-day event has no start time; its end date includes the final day. A timed event needs an end time to set its duration. Existing ET/CT/MT/PT labels remain supported; explicit calendar time zones also support Arizona, Alaska, Hawaii, and UTC. Timed events display in Eastern Time with daylight-saving adjustments.

Featured Events appears below the calendar with up to three upcoming or ongoing published Events. The existing Feature on TTA Calendar setting prioritizes an Event; remaining spaces use upcoming Events in date order, comparing actual start times across time zones. Cards open the same accessible event dialog as the calendar and link to the Event page when JavaScript is unavailable. The section displays a coming-soon message when no upcoming Events exist. Its heading and cards use the existing scroll reveal and reduced-motion behavior. View All Events switches to monthly List view and scrolls to the schedule, without animated scrolling for reduced-motion visitors.

Month, Week, Day, and monthly List views display all published Events in their date range. Date and view persist in the URL. Clicking an event opens a keyboard-accessible native dialog with event details, registration when applicable, and an ICS download. The server-rendered monthly list remains usable if JavaScript is unavailable.

The public read-only feed is `/wp-json/cvipi/v1/calendar`, with required `start` and exclusive `end` dates (`YYYY-MM-DD`) and optional `search`, `type`, and `topic`. Requests are bounded to 370 days and at most 1,000 candidate Events. The calendar bundle loads only on this page.

Build with `npm run calendar` and `npx gulp devstyles`. Dependencies are pinned FullCalendar 6.1.20, Luxon 3.7.2, and Lucide 0.468.0; no calendar CDN is used.

Text blocks (hero, schedule intro, and support copy) fade upward as groups. The calendar frame reveals once when its top reaches the scroll trigger. The small `tta-calendar-reveals.js` controller is intentionally synchronous in the head: it establishes initial styles before first paint and observes markup during parsing, independent of the deferred FullCalendar bundle. Do not defer this controller. Missing JavaScript, unsupported observers, and reduced-motion preferences leave content visible. Keyboard focus reveals controls immediately. Run animation regression checks with `node tests/calendar-reveals.cjs`.

Run local integration tests with `wp eval-file wp-content/themes/cvipi/tests/event-calendar.php`. Tests are restricted to `cvipi.local` and create and remove only their own temporary Event fixtures.
Run read-only page, feed, fallback, public asset, and ICS download checks with `wp eval-file wp-content/themes/cvipi/tests/calendar-http.php` while the local site is running.
Run interaction, Retry, and cancelled-request regression checks with `node tests/calendar-interactions.cjs` after installing the development dependencies.

## Staging Checklist

- Deploy the calendar PHP module and page template together with its reveal controller, compiled calendar bundle, license file, image, and rebuilt site stylesheet. Include the calendar module require in `functions.php` and retain existing staging-specific code.
- Create the TTA Calendar page on staging and select its template; deploying theme files alone does not create the page. Keep navigation unchanged until approved.
- Do not copy the local database. Local Events marked `_cvipi_local_calendar_preview` (titles beginning `[Preview]`) are design samples, not confirmed CVIPI events, and must not be imported to stage or live without explicit approval.
- Confirm `mbstring` availability, public file permissions, page-cache refresh, the public calendar feed, and ICS downloads on staging after deployment. Do not change staging's noindex setting.
- Confirm real upcoming Event dates before review: the featured section intentionally displays a coming-soon message when there are no qualifying Events.
