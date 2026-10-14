# Submission Files

The submission choice changes title/body labels and supporting copy without clearing visitor input. The title remains optional, with location as the server-side fallback. One optional PDF, UTF-8 TXT, or macro-free Word DOCX file is accepted, up to 5 MB (or a lower PHP upload limit). Legacy DOC and macro-enabled Word files are intentionally excluded.

The browser uses multipart FormData, preserving the file rather than converting it to a string. Server validation independently checks extension, MIME, actual size, and file contents. DOCX ZIP packages have entry/expanded-size bounds; macros, ActiveX, embedded objects, path traversal, encrypted entries, and XML entities are rejected. Fileinfo and ZipArchive must be available for Word uploads.

Files are stored outside public web roots, with random server filenames, owner-only directories (0700) and files (0600). The default private directory is a site-specific `.cvipi-story-files-*` folder beside the hosting public root. A staging subfolder inside `public_html` still stores outside `public_html`. An absolute `CVIPI_STORY_UPLOAD_DIR` constant in wp-config.php can specify another persistent private directory; paths inside a detected web root are rejected. Confirm this directory is writable and covered by private backups on deployment. Files are not placed in the Media Library or embedded in published stories.

Editors download from Submission Details through an authenticated, nonce-protected admin-post endpoint requiring permission to edit that story. Downloads are forced attachments with nosniff/no-cache/noindex headers. Publishing keeps the review file private; permanent deletion of the story removes its own private file. Moving a story to Trash retains its file for restoration.

Notifications contain the attachment's type only, alongside the existing authenticated story-review link. Uploaded files, filenames, and public file URLs are not emailed. Receipts remain free of visitor-provided content. Validation is not antivirus scanning: documents remain untrusted, and editors should scan them before opening. A malware scanner/content-disarm service would require a separate hosting/service integration; none is configured by this change.

Run `wp eval-file tests/story-attachments.php` from the theme directory on cvipi.local. The 30 integration checks create/remove only their own fixtures and intercept notifications so no email is sent. Run `node tests/story-submission.cjs` for dynamic-label and multipart transport checks. Build with `npx gulp scripts` and `npx gulp devstyles`.
