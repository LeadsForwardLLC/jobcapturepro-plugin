=== JobCapturePro WordPress Plugin ===

Use JobCapturePro to capture job leads and manage your business.

== Installation ==

In your terminal, navigate to the parent directory from here:

`$ cd ../`

If there is an existing zip file from a previous install, you can remove it:

`$ rm jobcapturepro-plugin.zip`

or if there are no other zip files you want to keep in the parent directory, you can use this shorthand:

`$ rm *.zip`

Create the archive for distribution, being sure to exclude git version control metadata:

`$zip -r jobcapturepro-plugin.zip jobcapturepro-plugin -x '*.git*'`

In WordPress, navigate to the Plugins menu and click "Add New Plugin" at the top of the screen.

In most cases, there should be no issue if you have an active installation of the plugin. If desired, look for the plugin in the list of installed plugins, Deactivate, and Delete it.

Click "Upload Plugin" on the next screen and select the zip file created in the previous step.

Click "Install Now" to install the plugin if this is the first time installing it, and then click "Activate Plugin" on the next screen. Otherwise, choose to replace the existing plugin and it will be updated automatically.

Navigate to the Settings/General and make sure the API key is set.

== Developer Documentation ==

= Adding a Shortcode =

1. Add your shortcode code to `class-jobcapturepro-shortcodes.php`. Most of these functions will have a similar structure. The common scenario is to make an API call to our backend and then pass processing onto an HTML template function.
2. Add your HTML template code to `class-jobcapturepro-templates.php`. This should return HTML/CSS to the shortcode function. Consider separating styles into a separate function for maintainability.
3. Define the shortcode in `class-jobcapturepro.php`. This can be added to the function `JobCaptureProPlugin::define_shortcodes()`.
4. Make sure the backend is updated to support whatever API calls are necessary for the shortcode.

== Available Features ==

= Shortcodes =

* `[jcp_checkin]` - Display a specific check-in
* `[jcp_all_checkins]` - Display all check-ins
* `[jcp_map]` - Display a heatmap
* `[jcp_multimap]` - Display a map with multiple markers
* `[jcp_company_info]` - Display company information
* `[jcp_combined_components]` - Display combined components (check-ins + map)
* `[jcp_reviews]` - Display recent reviews
* `[jcp_nearby_checkins]` - Display nearby checkins based on current checkin's city

= Widgets =

* **JobCapturePro Reviews Widget** - Display recent reviews for a company
  - Configurable title
  - Optional company ID (can use URL parameter `companyId`)
  - Configurable number of reviews to display
  - Automatically checks for `checkinId` URL parameter to filter reviews
  - Displays star ratings, author names, review text, and dates
  - Shows "no reviews" message when no reviews are available

* **JobCapturePro Nearby Checkins Widget** - Display nearby checkins based on the current checkin's city
  - Configurable title
  - Configurable number of checkins to display
  - Option to exclude current checkin from results
  - Automatically gets city from URL parameter `checkinId`
  - Shows compact list of nearby checkins with images, descriptions, and dates
  - Shows "no nearby checkins" message when no data is available

= Reviews Widget Usage =

The Reviews Widget can be used in two ways:

1. **As a WordPress Widget**: Go to Appearance > Widgets in your WordPress admin and drag the "JobCapturePro Reviews Widget" to your desired widget area.

2. **As a Shortcode**: Use `[jcp_reviews]` with optional parameters:
   - `companyid` - Specific company ID
   - `limit` - Number of reviews to display (default: 5)
   - `title` - Widget title (default: "Recent Reviews")

   Example: `[jcp_reviews companyid="12345" limit="10" title="Customer Reviews"]`

= Nearby Checkins Widget Usage =

The Nearby Checkins Widget can be used in two ways:

1. **As a WordPress Widget**: Go to Appearance > Widgets in your WordPress admin and drag the "JobCapturePro Nearby Checkins Widget" to your desired widget area.

2. **As a Shortcode**: Use `[jcp_nearby_checkins]` with optional parameters:
   - `checkinid` - Specific checkin ID (if not provided, will use URL parameter)
   - `limit` - Number of nearby checkins to display (default: 10)
   - `title` - Widget title (default: "Nearby Checkins")
   - `exclude_current` - Whether to exclude the current checkin from results (default: "true")

   Example: `[jcp_nearby_checkins limit="5" title="More Jobs in This Area" exclude_current="false"]`

= URL Parameters =

The widget and shortcode automatically check for these URL parameters:
- `companyId` - Company ID for filtering reviews
- `checkinId` - Check-in ID for filtering reviews to a specific job
