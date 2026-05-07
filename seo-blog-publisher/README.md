# SEO Blog Publisher

A small standalone PHP blog archive shell for Milano Adventures.

This is **not WordPress** and does not use images yet. It is intended to be uploaded as a folder to normal PHP web hosting.

## Current phase
Phase 1 includes:
- public archive page
- settings page
- simple admin login/setup
- local JSON settings storage
- clean starting point for future GitHub sync

## Folder structure
```text
seo-blog-publisher/
  index.php
  settings.php
  functions.php
  logout.php
  assets/
    style.css
  data/
    .gitkeep
```

## Installation
1. Download this folder.
2. Upload `seo-blog-publisher` to your web space.
3. Open:

```text
https://your-domain.com/seo-blog-publisher/settings.php
```

4. On first visit, create the admin password.
5. Configure:
   - site name
   - archive title
   - archive path or URL
   - GitHub repository details for future sync
   - booking link placeholders
6. Open the archive page:

```text
https://your-domain.com/seo-blog-publisher/
```

## Important
The `data/` folder must be writable by PHP because settings are saved to:

```text
data/settings.json
```

## Current limitation
This first phase does not yet sync posts from GitHub. It only creates the installable shell and settings page.

Next phase can add:
- GitHub repository connection
- button to sync generated markdown files
- archive listing from synced posts
- individual post pages
- draft/publish controls
