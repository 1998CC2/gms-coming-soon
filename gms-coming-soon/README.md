# GMS - Coming Soon

A fast, lightweight and multilingual Coming Soon & Maintenance Mode plugin for WordPress.

## Features

- Enable/disable Coming Soon mode
- Custom headline and text
- Custom background image from the media library
- Multilingual (DE, ES, EN) with automatic language detection
- Admin bypass (admins see the normal site)
- SEO-friendly 503 status while in Coming Soon mode

## Development

This repository can be used as the source for deployment to the WordPress.org plugin directory.

### Build / Release

1. Update the version in:
   - `gms-coming-soon.php`
   - `readme.txt` (Stable tag + Changelog)
2. Tag the release in Git.
3. Use the GitHub Action workflow in `.github/workflows/deploy-to-wporg.yml` or the `deploy.sh` script for manual deployment.
