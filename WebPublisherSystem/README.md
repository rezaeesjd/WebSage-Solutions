# WebPublisherSystem

A small standalone PHP system for publishing SEO blog content from a public GitHub repository.

This is not WordPress. It is designed to be uploaded as a normal folder to PHP web hosting.

## Current scope
This first version includes:
- public archive page shell
- settings page without password
- public GitHub repository connection settings
- GitHub connection test
- ability to read the configured public repository content path
- local JSON settings storage

No images are included yet.

## Upload folder
Upload this folder to your web space:

```text
WebPublisherSystem/
```

Then open:

```text
https://your-domain.com/WebPublisherSystem/settings.php
```

## Default GitHub settings
The system is prefilled for this public repository:

```text
Owner: rezaeesjd
Repo: WebSage-Solutions
Branch: main
Content path: seo-content-system/tours
```

## Files
```text
WebPublisherSystem/
  README.md
  index.php
  settings.php
  functions.php
  github.php
  assets/
    style.css
  data/
    .gitkeep
```

## Important
The `data/` folder must be writable by PHP because settings are saved to:

```text
data/settings.json
```

## Current limitation
This version connects to GitHub and tests the configured content path, but it does not yet create final public blog post pages from the markdown files.

Next phase can add:
- Sync from GitHub button
- local cached posts
- archive cards generated from `meta.json`
- individual post pages
- markdown rendering
