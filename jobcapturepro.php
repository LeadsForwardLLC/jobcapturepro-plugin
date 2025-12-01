<?php

/**
 * Plugin Name:       JobCapturePro
 * Plugin URI:        https://www.jobcapturepro.com/wordpress-plugin/
 * Description:       Display job check-ins, company information, and interactive maps from your JobCapturePro account. Showcase completed work, customer reviews, and business locations with professional shortcodes.
 * Text Domain:       jobcapturepro
 * Version:           1.0.4
 * Requires at least: 5.0
 * Tested up to:      6.6
 * Requires PHP:      7.4
 * Author:            JobCapturePro Team
 * Author URI:        https://www.jobcapturepro.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package JobCapturePro
 * @since   1.0.0
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

// Define plugin constants
define('JOBCAPTUREPRO_VERSION', '1.0.4');
define('JOBCAPTUREPRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JOBCAPTUREPRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JOBCAPTUREPRO_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_jobcapturepro()
{
	// Flush rewrite rules to ensure shortcodes work properly
	flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_jobcapturepro()
{
	// Clean up rewrite rules
	flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'activate_jobcapturepro');
register_deactivation_hook(__FILE__, 'deactivate_jobcapturepro');

/**
 * The core plugin class that is used to define 
 * admin-specific and public-facing shortcode hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-jobcapturepro.php';

/**
 * Begins execution of the plugin.
 * 
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_jobcapturepro()
{
	$plugin = new JobCaptureProPlugin();
	$plugin->run();
}

run_jobcapturepro();
