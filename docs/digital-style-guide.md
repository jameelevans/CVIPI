# CVIPI Digital Style Guide

Local URL: <http://cvipi.local/cvipi-digital-style-guide/>

The standalone page follows the CVIPI Brand Guide Figma Sites reference. It has its own sidebar, responsive navigation, seven content sections, scroll progress, and one-time text-block reveals. It deliberately does not render the main website header/footer or initialize the submission exit prompt. Analytics consent is retained; local and staging tracking exclusions are unchanged.

## Design And Assets

- Figma file: `AWNfePP0uJNnQncfXeSAPP`, CVIPI Brand Guide.
- Original Figma photography is copied into `assets/img/style-guide/` with descriptive alternative text where informative.
- The advising and neighborhood originals were re-transferred in full after detecting truncated JPEGs. The image regression check verifies complete JPEG end markers. Collage cells use fixed responsive geometry and absolutely positioned cover images.
- Fraunces, Syne, color variables, and the existing 10px desktop / 8px smaller-screen rem convention come from the theme. Color names/hex values reflect the current theme, rather than the slightly different draft palette.
- Contrast ratios are calculated from those current colors using unrounded thresholds. Labels distinguish body-text, large-text-only, and failed combinations. Demonstrations of failed pairings are intentionally low contrast and hidden from assistive technology; readable verdicts explain them.
- Native details/summary controls and a native mobile dialog support keyboard access, Escape, focus restoration, and a dark blurred backdrop. Without JavaScript, navigation remains visible and all content remains readable. Reduced motion disables reveals and smooth scrolling.
- Contrast groups animate open and closed with the Web Animations API. Repeated clicks reverse from the current height, resizing releases fixed heights, reduced motion changes finish immediately, and unsupported browsers keep native details behavior. Muted Text is included in the pairing matrix; bordered usage notes reflect the actual current contrast ratios.

## Hidden, Not Private

The guide is published for anyone holding its link. It sends `noindex, nofollow` in both robots metadata and an HTTP header and is excluded from frontend search, automatic page listings, and the WordPress page sitemap, including under the live indexing policy. No public menu link is created. This is not password protection or access control.

## Downloads And Installation

The two template cards link directly to the approved file-download routes, with a Contact link immediately below. The help band retains the original template page styling and aligns its text to the guide's responsive content gutter. The existing `/cvipi-document-template/` and `/cvipi-presentation-template/` landing pages and their approved originals remain available and unchanged.

The approved 508 checklist was copied byte-for-byte from the Figma reference, outside the public webroot, into the existing private grantee-template directory. Its SHA-256 is `5d36a4cce6b984c20f82be8e99ad0250fa4ef18b8ef9d709175a838e7e34bebd` (21,877 bytes). Keep the approved original available for future deployment; it is intentionally not committed as a public theme asset.

After deploying the theme to another environment, install that same checklist and create the page with:

```sh
wp eval-file wp-content/themes/cvipi/tools/install-digital-style-guide.php /absolute/path/CVIPI-508-Compliance-Checklist.docx
```

The installer validates the DOCX, rejects conflicting page slugs and symlink targets, preserves an existing guide page, writes the checklist with private permissions, sets noindex metadata, and refreshes rewrite rules. The existing `CVIPI_GRANTEE_TEMPLATE_DIR` setting is respected. The checklist URL only serves its fixed approved file over GET/HEAD with download, noindex, no-sniff, and no-cache headers.

## Build And Verify

```sh
npm run style-guide
npx gulp devstyles
node tests/digital-style-guide.cjs
node tests/guide-contrast-group.cjs
wp eval-file wp-content/themes/cvipi/tests/digital-style-guide.php
wp eval-file wp-content/themes/cvipi/tests/digital-style-guide-http.php
wp eval-file wp-content/themes/cvipi/tests/grantee-templates.php
wp eval-file wp-content/themes/cvipi/tests/grantee-template-http.php
```

Local WP CLI needs Local's running PHP/MySQL socket. HTTP tests refuse non-local hosts. The new page, private checklist, and compiled theme assets all need deployment; pushing PHP alone will not install page content or private files. No stage/live changes are made by adding these source files locally.
