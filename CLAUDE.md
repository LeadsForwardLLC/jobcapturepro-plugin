# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

JobCapturePro is a WordPress plugin (PHP + JavaScript) that integrates with the JobCapturePro SaaS API to display job checkins, company info, and interactive Google Maps on WordPress sites via shortcodes. Current version: 1.0.7.

Requirements: PHP 7.4+, Node.js 18+, WordPress 5.0+.

## Build Commands

```bash
npm install        # Install Node dependencies
npm run build      # Webpack production build (minified output to /dist/)
npm run dev        # Webpack development build with file watching
```

No automated tests or linting are configured.

## Local Development

```bash
npm install && npm run dev       # Start asset watcher
docker-compose up                # WordPress at http://localhost:8888
```

After starting, configure the API key in WordPress Settings > General > "API Key".

## Architecture

### Plugin Initialization Flow

`jobcapturepro.php` → `JobCaptureProPlugin` (main class in `includes/class-jobcapturepro.php`) → loads dependencies, registers admin hooks, shortcodes, REST API routes, and enqueues assets via `JobCaptureProLoader`.

### Key Components

- **Admin** (`admin/class-jobcapturepro-admin.php`): Settings page for API key configuration under WordPress General Settings.
- **API Proxy** (`includes/class-jobcapturepro-api.php`): REST routes at `/wp-json/jobcapturepro/v1/` that proxy requests to the external JobCapturePro API with Bearer token auth.
- **Shortcodes** (`public/class-jobcapturepro-shortcodes.php`): Six shortcodes (`jobcapturepro_checkin`, `jobcapturepro_checkins`, `jobcapturepro_map`, `jobcapturepro_company_info`, `jobcapturepro_combined`) plus five legacy aliases (`jcp_*`) for backwards compatibility.
- **Template System** (`includes/class-template-loader.php`, `templates/`): PHP template rendering with variable injection and output escaping helpers in `class-jobcapturepro-templates.php`.

### Frontend Assets

Webpack bundles 9 entry points (4 JS, 5 CSS) from `src/` to `dist/`:
- **map.js**: Google Maps Advanced Markers API with clustering (`@googlemaps/js-api-loader`, `@googlemaps/markerclusterer`)
- **gallery.js**: Image carousel in map info windows
- **checkins/load-more.js**: AJAX pagination for checkins grid
- **checkins/masonry-grid.js**: Responsive card layout
- CSS uses Tailwind CSS 4 + PostCSS (autoprefixer, cssnano)

### External API

Base URL is defined in `class-jobcapturepro.php`. Auth via `Authorization: Bearer {apikey}` header. SSL verification is enforced.

## Conventions

- All shortcode attributes are sanitized with `sanitize_text_field()`. IDs use custom `sanitize_id_parameter()`.
- Template output uses WordPress escaping functions (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`).
- Asset versioning uses the `JOBCAPTUREPRO_VERSION` constant for cache busting.
## Git Workflow

1. Create a branch from `main` with a prefix matching the change type: `feature/*`, `bugfix/*`, etc.
2. Merge the branch into `dev` for testing.
3. Once verified, open a PR from the feature/bugfix branch into `main` for review and merge.
