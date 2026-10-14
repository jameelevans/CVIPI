# Unlisted Grantee Templates

Two WordPress pages use `page-grantee-template.php`:

- `/cvipi-document-template/` (Word)
- `/cvipi-presentation-template/` (PowerPoint)

Each page has branded download content and normal share metadata. Neither is added to the site's navigation, frontend page listings, search results, or XML sitemap. Both the HTML response and the original file download send `X-Robots-Tag: noindex, nofollow`; the page also has a robots meta tag. This is discoverability control, not authentication: anyone with a link can download. On live, do not block these paths in robots.txt, because crawlers need to fetch them to observe `noindex`.

## Storage and Deployment

Approved originals live **outside the webroot**, not in the public Media Library or theme. They are unchanged; only the download filenames are normalized:

- `CVIPI-Document-Template.docx`
- `CVIPI-Presentation-Template.pptx`

Default storage is `.cvipi-grantee-templates` beside the outermost recognized webroot. Local uses `app/.cvipi-grantee-templates`; Bluehost uses the folder beside `public_html`, including for staging installations inside `public_html`. This default folder can be shared by stage and live. PHP must have read access. Alternatively set `CVIPI_GRANTEE_TEMPLATE_DIR` in each environment's wp-config.php to an absolute folder outside all public roots; the parent must already exist.

**A theme/Git push alone does not deploy the documents or create the pages.** Transfer the two approved original files privately to the target host, then run from the theme directory:

```sh
wp --path=/path/to/wordpress eval-file tools/install-grantee-templates.php /private/path/approved.docx /private/path/approved.pptx
```

The installer validates page-slug ownership first, copies and verifies the original bytes, creates missing pages, sets the template/SEO flags, and flushes rewrite rules once. Re-running preserves existing page titles, status, and content. Replacing the source documents updates the downloads while the share URLs stay the same. No rewrite flush runs on normal page views. Back up the non-public storage folder along with the database and theme.

Download URLs are `/cvipi-document-template/CVIPI-Document-Template.docx` and `/cvipi-presentation-template/CVIPI-Presentation-Template.pptx`. They support GET and HEAD, return attachment disposition and the Office MIME type, and reject unknown template mappings and unpublished pages. No nonce/login is required for these approved public-to-link templates. The existing consent-controlled analytics recognizes their actual file extensions and download attributes; local/stage and signed-in users remain excluded.

The client's Digital Style Guide buttons can link to the live page URLs (or the direct download URLs) once deployed. The Figma guide itself is not published or modified by this implementation.

## Checks

```sh
wp --path=/path/to/local/wordpress eval-file tests/grantee-templates.php
wp --path=/path/to/local/wordpress eval-file tests/grantee-template-http.php
```

This read-only local test checks installed pages, approved-file hashes, storage, routing, search/listing exclusions, and the sitemap query. HTTP checks should also verify download content hashes, MIME types, disposition, `noindex`, and 404/405 behavior. Test anonymous downloads and desktop/mobile layout before sharing live links.
