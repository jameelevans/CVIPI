# Automatic CVIPI SEO

## Ownership

CVIPI's theme owns metadata through `inc/seo.php`; Yoast was deactivated and removed from live on September 29, 2026. No new SEO admin screens are required. WordPress core generates XML, pagination and sitemap routing rather than a custom XML serializer.

Deployment scope: new `inc/seo.php`, one require in functions.php, and the existing homepage heading changed from h2 to h1 without changing its class or copy. Local source contains the same module. Live and local functions/header files were patched independently to preserve their unrelated differences.

## Automatic behavior

- HTML titles, canonical links, descriptions, Open Graph and Twitter previews.
- Organization, WebSite and page-specific schema; published success stories also receive Article schema with real publication/modification dates and their featured image.
- New stories and normal pages are included as soon as published; pending, draft, protected and noindex content is excluded.
- Descriptions use existing preserved custom metadata, excerpts or content; hand-coded main landing pages have concise, content-matched defaults. No fabricated awards, reviews or authors.
- XML sitemap: `https://cvipi.info/wp-sitemap.xml`.
- Previous `sitemap_index.xml` address redirects permanently to the native sitemap.
- Sitemaps include only intended public content, not internal map records, standalone FAQ records, provider-logo records, author listings, tag/category archives, admin, or staging.
- Resources and Events remain excluded while hidden for their later launch. Code option `cvipi_seo.index_resources_events` can enable their indexing when launched; content is not deleted.
- The duplicate `/home/` page redirects to `/`. WordPress handles www-to-primary-domain canonical redirection.
- Robots permits public crawling and advertises the native sitemap; admin, login and staging paths are excluded. Local/staging responses are noindex and their sitemaps are disabled by this module's environment check.
- Live detection requires root cvipi.info/www.cvipi.info and blog_public=1. Staging blog_public was explicitly set to 0.
- XML and robots responses use no-cache headers. Core dynamically queries current posts; no cron, static generation or repeated search-engine pings are needed.
- Landing-page lastmod accounts for relevant story/map/provider changes, not arbitrary request time.

## Verification

`tests/seo-integration.php` runs with local `wp eval-file`. It creates one test story locally and deletes it in finally. It tests publication/pending/password/noindex transitions, immediate sitemap inclusion, generated metadata, JSON-LD and environment isolation. Do not run on live.

`tools/crawl-seo.cjs` audits every sitemap URL for HTTP 200, exactly one canonical, an H1, title, description, indexability and valid structured data. Requires jsdom. `--origin` isolates the known origin from the CDN; origin success is not proof of public crawler access.

Initial origin audit passed all six intended URLs: home, What is CVIPI, Success Stories, Contact and two published stories. WordPress core checksums passed. A read-only database scan found no references to the unrelated domain previously chosen by Google's historical canonical report. That report is historical evidence, not proof of a current compromise.

## Search Console and CDN

The existing verified domain property `sc-domain:cvipi.info` is used. The new sitemap index was submitted and processed successfully, but both child sitemaps currently report "Couldn't fetch" and zero discovered URLs. Search Console's live homepage test can fetch the page, and Google accepted a homepage reindexing request on September 29, 2026. Manual Actions reports no issues detected. These results do not mean all pages are indexed.

The public CDN still challenges some automated XML requests, even though the origin returns valid 200 XML. Bluehost exposes only an on/off Cloudflare toggle, so targeted verified-crawler exceptions require Bluehost support. The support messenger returned "Session is read-only" and "Not sent"; no support case has been created. A targeted security exception is awaiting owner approval. Do not disable Cloudflare/WAF or allow arbitrary requests just because they claim a crawler user agent.

Search and AI retrieval eligibility are distinct from guaranteed indexing, rankings or AI citation. No `llms.txt` file or structured-data trick guarantees inclusion. Preserve security controls while permitting verified search retrieval of public content.

## Rollback

Private live backup: `/home1/rrgvvfmy/cvipi-seo-backup-20260929/`, directory mode 700. Contains the pre-change database and Yoast/theme archive. Existing Yoast database metadata was retained but the plugin files removed. Prefer restoring only affected theme/plugin files/settings, not overwriting a database that has received newer submissions or content.

To restore Yoast, restore the archived plugin directory and activate it; the CVIPI module yields metadata ownership while WPSEO_VERSION exists. Flush rewrite rules and clear affected caches afterward. Local never had Yoast installed.

References: WordPress core sitemap hooks; Google Search Central sitemap, canonicalization, AI features and Organization documentation; official OpenAI/Anthropic crawler documentation. No additional analytics tags or tracking are installed by this SEO work.
