# JobCapturePro WordPress Plugin

Connect your WordPress website to JobCapturePro's comprehensive job management system with interactive maps and detailed job displays.

| | |
| --- | --- |
| Stable tag | 1.1.8 |
| Requires at least | WordPress 5.0 |
| Tested up to | WordPress 7.1 |
| Requires PHP | 7.4 |
| License | [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) |
| Source | https://github.com/LeadsForwardLLC/jobcapturepro-plugin |

## Description

JobCapturePro is a powerful WordPress plugin that connects your website to the JobCapturePro service, a comprehensive job management platform designed for service-based businesses. Display your completed jobs, customer interactions, and service locations directly on your WordPress site with beautiful, interactive components.

### Key Features

* **Job Check-ins Display**: Show detailed job check-ins with customer feedback, service details, and images
* **Interactive Maps**: Display job locations on Google Maps with markers and clustering for multiple locations
* **Company Information**: Show business details on any page
* **Mobile-Responsive Design**: All components are fully responsive and mobile-friendly

### What is JobCapturePro?

JobCapturePro is a Software-as-a-Service (SaaS) platform that helps service businesses convert real-world jobs into SEO-boosting web content!

This WordPress plugin serves as an interface to display your JobCapturePro data on your website, allowing you to showcase your work, service areas, and business activity to potential customers.

### External Service Requirements

This plugin connects to the JobCapturePro API service (https://api.jobcapturepro.com) to retrieve and display your business data. You must have an active JobCapturePro account to use this plugin. The plugin will make API calls to:

* Retrieve job check-in data
* Fetch company information and statistics
* Get location data for mapping features

For more information about JobCapturePro's terms of service, visit: https://jobcapturepro.com/termsfeed/terms-and-conditions/

### Available Shortcodes

**New Shortcodes (Recommended):**

* `[jobcapturepro_checkin]` - Display a single job check-in with full details
* `[jobcapturepro_checkins]` - Show multiple check-ins in a responsive grid layout
* `[jobcapturepro_combined]` - Display the interactive map and check-ins slider together (no company header)
* `[jobcapturepro_full]` - Display the company information header, interactive map, and check-ins slider together
* `[jobcapturepro_company_info]` - Show business information and statistics
* `[jobcapturepro_map]` - Interactive map with job location markers

**Legacy Shortcodes (Backwards Compatibility):**

* `[jcp_checkin]`, `[jcp_all_checkins]`, `[jcp_combined_components]`, `[jcp_company_info]`, `[jcp_multimap]`

All shortcodes support `companyid` and `checkinid` parameters for targeted displays.

## Installation

1. Upload the `jobcapturepro-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > General and configure your JobCapturePro API settings
4. Enter your JobCapturePro API key (obtain from your JobCapturePro account dashboard)
5. Use the provided shortcodes on any post or page to display your job data

## Frequently Asked Questions

**Do I need a JobCapturePro account to use this plugin?**

Yes, this plugin requires an active JobCapturePro service account. The plugin serves as an interface to display data from the JobCapturePro platform on your WordPress website.

**How do I get a JobCapturePro API key?**

Log into your JobCapturePro account dashboard and navigate to the API settings section. Your API key will be displayed there, or you can generate a new one if needed.

**Is my data secure?**

All communication with the JobCapturePro API uses secure HTTPS connections. Your API key should be kept confidential and only entered in your WordPress admin settings.

**Where is the source code?**

Full source code available at: https://github.com/LeadsForwardLLC/jobcapturepro-plugin

## Development

### Prerequisites

- Node.js 18+
- npm
- Docker (optional, for the local WordPress environment)

### Getting Started

- `git clone https://github.com/LeadsForwardLLC/jobcapturepro-plugin`
- `cd jobcapturepro-plugin`
- `npm install` - Install dependencies
- `npm run build` - Build for production
- `npm run dev` - Development build with watch

### Local WordPress environment

`docker-compose.yml` starts MariaDB, the latest WordPress image with this repository mounted as the plugin, and a WP-CLI service that shares the same install.

- `docker compose up -d` - start the stack, then browse to http://localhost:8888 to finish the WordPress install
- `docker compose run --rm cli wp plugin activate jobcapturepro` - run any WP-CLI command against the site
- `docker compose down -v` - start fresh (also needed after pulling a newer `wordpress` image, since core lives in a volume)

### Plugin Structure

* Main plugin file: `jobcapturepro.php`
* Admin functionality: `admin/class-jobcapturepro-admin.php`
* Shortcodes: `public/class-jobcapturepro-shortcodes.php`
* Templates: `includes/class-jobcapturepro-templates.php` and `templates/`
* Core functionality: `includes/class-jobcapturepro.php`
* Front-end sources: `src/js/` and `src/css/`, built to `dist/` by webpack
### Releasing

`scripts/release.sh <version> [base-branch]` builds `dist/`, creates `release/<version>` from the base branch (default `main`), bumps the version in `jobcapturepro.php` and the **Stable tag** row above, and strips development files so only the production plugin files remain. Review the branch, then push it with `git push -u origin release/<version>`.

## Third-Party Services

This plugin relies on the following external services:

**JobCapturePro API** (https://api.jobcapturepro.com)
- Purpose: Retrieve job, customer, and business data
- Data transmitted: API key, company ID, check-in ID parameters
- Terms of Service: https://www.jobcapturepro.com/terms-of-service
- Privacy Policy: https://www.jobcapturepro.com/privacy-policy

**Google Maps API** (https://maps.googleapis.com)
- Purpose: Displaying interactive maps with location markers
- Data transmitted: Search terms, IP addresses, latitude/longitude coordinates, map interactions (as per Google Maps Platform Terms Section 4.4)
- Terms of Service: https://cloud.google.com/maps-platform/terms
- Privacy Policy: https://policies.google.com/privacy

## System Requirements

* WordPress 5.0 or higher
* PHP 7.4 or higher
* cURL extension for API communications

## Changelog

### 1.0.0

* Initial release
* Job check-ins display functionality
* Interactive Google Maps integration
* Heat map visualization features
* Company information components
* Responsive design for all components
* Feature toggle system for customizable displays
* Comprehensive shortcode system
* Error handling for service unavailability
* Security enhancements and data sanitization

## Upgrade Notice

### 1.0.0

Initial release of JobCapturePro WordPress plugin. Connect your website to your JobCapturePro service account to display job information and interactive maps.
