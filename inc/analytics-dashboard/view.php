<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap cvipi-da" id="cvipi-dashboard">
  <header class="cvipi-da__header">
    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/cvipi-logo-white.webp' ); ?>" alt="CVIPI" width="100" height="58">
    <div><p class="cvipi-da__eyebrow">CVIPI.INFO</p><h1>Analytics</h1></div>
    <a class="cvipi-da__google" href="https://analytics.google.com/analytics/web/#/p555256472/reports/reportinghub" target="_blank" rel="noopener noreferrer">Google Analytics <span class="dashicons dashicons-external" aria-hidden="true"></span><span class="screen-reader-text"> (opens in a new tab)</span></a>
  </header>
  <div class="cvipi-da__toolbar">
    <div><label for="cvipi-da-range">Reporting period</label><select id="cvipi-da-range"><option value="7">Last 7 complete days</option><option value="28" selected>Last 28 complete days</option><option value="90">Last 90 complete days</option><option value="today">Today (preliminary)</option></select></div>
    <p id="cvipi-da-period"></p>
    <div class="cvipi-da__tools">
      <button type="button" id="cvipi-da-export" class="cvipi-da__icon" aria-label="Export report as CSV" title="Export report as CSV" disabled><span class="dashicons dashicons-download" aria-hidden="true"></span></button>
      <button type="button" id="cvipi-da-refresh" class="cvipi-da__icon" aria-label="Refresh reports" title="Refresh reports"><span class="dashicons dashicons-update" aria-hidden="true"></span></button>
    </div>
  </div>
  <div class="cvipi-da__status"><span class="cvipi-da__source"><span aria-hidden="true"></span> Live site only</span><span id="cvipi-da-freshness">Connecting to Google Analytics</span></div>
  <p class="cvipi-da__notice" id="cvipi-da-notice" role="status" aria-live="polite">Loading reports...</p>
  <main id="cvipi-da-results" aria-busy="true" hidden>
    <section class="cvipi-da__metrics" id="cvipi-da-metrics" aria-label="Key metrics"></section>
    <nav class="cvipi-da__views" aria-label="Analytics views">
      <button type="button" data-view="overview" aria-pressed="true">Overview</button>
      <button type="button" data-view="content" aria-pressed="false">Content &amp; downloads</button>
      <button type="button" data-view="interactions" aria-pressed="false">Interactions</button>
    </nav>
    <section data-panel="overview">
      <div class="cvipi-da__split">
        <section class="cvipi-da__section cvipi-da__trend"><div class="cvipi-da__section-heading"><h2>Audience over time</h2><div class="cvipi-da__segmented" role="group" aria-label="Trend metric"><button type="button" data-trend="activeUsers" aria-pressed="true">Visitors</button><button type="button" data-trend="sessions" aria-pressed="false">Visits</button><button type="button" data-trend="screenPageViews" aria-pressed="false">Views</button></div></div><div id="cvipi-da-chart" class="cvipi-da__chart"></div><details class="cvipi-da__details"><summary>Daily figures</summary><div id="cvipi-da-daily"></div></details></section>
        <section class="cvipi-da__section"><h2>Traffic sources</h2><div id="cvipi-da-channels"></div></section>
      </div>
      <div class="cvipi-da__split cvipi-da__split--equal"><section class="cvipi-da__section"><h2>Reading &amp; engagement</h2><dl id="cvipi-da-engagement" class="cvipi-da__facts"></dl></section><section class="cvipi-da__section"><h2>Devices</h2><div id="cvipi-da-devices"></div></section></div>
    </section>
    <section data-panel="content" hidden>
      <section class="cvipi-da__section"><div class="cvipi-da__section-heading"><h2>Popular pages</h2><div><label for="cvipi-da-page-type" class="screen-reader-text">Page type</label><select id="cvipi-da-page-type"><option value="all">All pages</option><option value="stories">Success stories</option></select></div></div><div id="cvipi-da-pages"></div></section>
      <section class="cvipi-da__section"><h2>File downloads</h2><p class="cvipi-da__caption">Download-link clicks, not completed transfers.</p><div id="cvipi-da-downloads"></div></section>
    </section>
    <section data-panel="interactions" hidden>
      <div class="cvipi-da__split cvipi-da__split--equal"><section class="cvipi-da__section"><h2>Visitor actions</h2><div id="cvipi-da-events"></div></section><section class="cvipi-da__section"><h2>Successful form submissions</h2><div id="cvipi-da-forms"></div></section></div>
      <section class="cvipi-da__section"><h2>Link clicks</h2><div id="cvipi-da-links"></div></section>
    </section>
    <footer class="cvipi-da__footer"><p>Google Analytics 4 &middot; Consenting visitors only &middot; Tracking began Sep 29, 2026</p><p>Local, staging and signed-in admin visits are excluded. Recent data may take 24&ndash;48 hours to finish processing.</p></footer>
  </main>
  <noscript><p>JavaScript is required to load these reports.</p></noscript>
</div>
