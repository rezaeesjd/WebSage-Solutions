# WebPublisherSystem

A small standalone PHP system for publishing SEO blog content from a public GitHub repository.

This is not WordPress. It is designed to be uploaded as a normal folder to PHP web hosting.

## Current scope
This version includes:
- public blog archive page shell at `/WebPublisherSystem/blog/`
- settings page without password
- public GitHub repository connection settings
- GitHub connection test
- ability to read the configured public repository content path
- local JSON settings storage

No images are included yet.

## Main structure
```text
WebPublisherSystem/
  README.md
  index.php                  # redirects to blog/
  settings.php               # redirects to platform/settings.php
  blog/                      # default public blog archive
    index.php
  platform/                  # runnable platform/admin/support files
    index.php
    settings.php
    functions.php
    github.php
    assets/
      style.css
    data/
      .gitkeep
  content-system/            # AI/Codex content instructions and generated tour content
    AGENTS.md
    tours/
      cinque-terre-full-day-tour-from-milan/
        brief.md
        keywords.md
        blog-post.md
        faq.md
        meta.json
        internal-links.md
        automation-notes.md
  settings/                  # reserved for future reusable setting definitions
    .gitkeep
  publishers/                # reserved for future publisher/sync modules
    .gitkeep
  templates/                 # reserved for future page/layout templates
    .gitkeep
  structures/                # reserved for future content/data structures
    .gitkeep
```

## Upload folder
Upload the full folder to your web space:

```text
WebPublisherSystem/
```

Then open the public blog archive:

```text
https://your-domain.com/WebPublisherSystem/blog/
```

Or open the settings page:

```text
https://your-domain.com/WebPublisherSystem/settings.php
```

The top-level `index.php` redirects to the blog archive. The top-level `settings.php` redirects to the platform settings page.

## Default GitHub settings
The system is prefilled for this public repository:

```text
Owner: rezaeesjd
Repo: WebSage-Solutions
Branch: main
Content path: WebPublisherSystem/content-system/tours
```

## Important
The `platform/data/` folder must be writable by PHP because settings are saved to:

```text
platform/data/settings.json
```

## Current limitation
This version connects to GitHub and tests the configured content path, but it does not yet create final public blog post pages from the markdown files.

Next phase can add:
- Sync from GitHub button
- local cached posts
- archive cards generated from `meta.json`
- individual post pages
- markdown rendering
