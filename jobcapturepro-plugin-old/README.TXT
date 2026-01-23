=== JobCapturePro ===
Contributors: jobcapturepro
Tags: service business, field service, job tracking
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WordPress website to JobCapturePro's comprehensive job management system with interactive maps and detailed job displays.

== Description ==

JobCapturePro is a powerful WordPress plugin that connects your website to the JobCapturePro service, a comprehensive job management platform designed for service-based businesses. Display your completed jobs, customer interactions, and service locations directly on your WordPress site with beautiful, interactive components.

**Key Features:**

* **Job Check-ins Display**: Show detailed job check-ins with customer feedback, service details, and images
* **Interactive Maps**: Display job locations on Google Maps with markers and clustering for multiple locations
* **Company Information**: Show business details on any page
* **Mobile-Responsive Design**: All components are fully responsive and mobile-friendly

**What is JobCapturePro?**

JobCapturePro is a Software-as-a-Service (SaaS) platform that helps service businesses convert real-word jobs into SEO-boosting web content!

This WordPress plugin serves as an interface to display your JobCapturePro data on your website, allowing you to showcase your work, service areas, and business activity to potential customers.

**External Service Requirements:**

This plugin connects to the JobCapturePro API service (https://api.jobcapturepro.com) to retrieve and display your business data. You must have an active JobCapturePro account to use this plugin. The plugin will make API calls to:

* Retrieve job check-in data
* Fetch company information and statistics  
* Get location data for mapping features

For more information about JobCapturePro's terms of service, visit: https://jobcapturepro.com/termsfeed/terms-and-conditions/

**Available Shortcodes:**

**New Shortcodes (Recommended):**
* `[jobcapturepro_checkin]` - Display a single job check-in with full details
* `[jobcapturepro_checkins]` - Show multiple check-ins in a responsive grid layout
* `[jobcapturepro_combined]` - Display comprehensive job and company information
* `[jobcapturepro_company_info]` - Show business information and statistics
* `[jobcapturepro_map]` - Interactive map with job location markers

**Legacy Shortcodes (Backwards Compatibility):**
* `[jcp_checkin]`, `[jcp_all_checkins]`, `[jcp_combined_components]`, `[jcp_company_info]`, `[jcp_multimap]`

All shortcodes support `companyid` and `checkinid` parameters for targeted displays.

== Installation ==

1. Upload the `jobcapturepro-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > General and configure your JobCapturePro API settings
4. Enter your JobCapturePro API key (obtain from your JobCapturePro account dashboard)
6. Use the provided shortcodes on any post or page to display your job data

== Frequently Asked Questions ==

= Do I need a JobCapturePro account to use this plugin? =

Yes, this plugin requires an active JobCapturePro service account. The plugin serves as an interface to display data from the JobCapturePro platform on your WordPress website.

= How do I get a JobCapturePro API key? =

Log into your JobCapturePro account dashboard and navigate to the API settings section. Your API key will be displayed there, or you can generate a new one if needed.

= Is my data secure? =

All communication with the JobCapturePro API uses secure HTTPS connections. Your API key should be kept confidential and only entered in your WordPress admin settings.

== Changelog ==

= 1.0.0 =
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

== Upgrade Notice ==

= 1.0.0 =
Initial release of JobCapturePro WordPress plugin. Connect your website to your JobCapturePro service account to display job information and interactive maps.

== Third-Party Services ==

This plugin relies on the following external services:

**JobCapturePro API** (https://api.jobcapturepro.com)
- Purpose: Retrieve job, customer, and business data
- Data transmitted: API key, company ID, check-in ID parameters
- Terms of Service: https://www.jobcapturepro.com/terms-of-service
- Privacy Policy: https://www.jobcapturepro.com/privacy-policy

== Developer Information ==

**Plugin Structure:**
* Main plugin file: `jobcapturepro.php`
* Admin functionality: `admin/class-jobcapturepro-admin.php`
* Shortcodes: `public/class-jobcapturepro-shortcodes.php`  
* Templates: `includes/class-jobcapturepro-templates.php`
* Core functionality: `includes/class-jobcapturepro.php`

**Hooks and Filters:**
* `jobcapturepro_api_response` - Filter API responses before processing
* `jobcapturepro_template_data` - Modify template data before rendering
* `jobcapturepro_map_options` - Customize map display options

**System Requirements:**
* WordPress 5.0 or higher
* PHP 7.4 or higher
* cURL extension for API communications
* Active internet connection for external service access