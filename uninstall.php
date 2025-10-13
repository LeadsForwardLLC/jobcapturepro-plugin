<?php
/**
 * Uninstall functionality for JobCapturePro plugin.
 *
 * Fired when the plugin is uninstalled.
 *
 * @package JobCapturePro
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
/**
 * Clean up plugin data on uninstall
 *
 * @since 1.0.0
 */
function jobcapturepro_uninstall() {
	// Check user permissions
	if ( ! current_user_can( 'delete_plugins' ) ) {
		return;
	}

	// Remove plugin options
	delete_option( 'jobcapturepro_options' );
	
	// Clear any cached data
	wp_cache_flush();
}

// Run the uninstall function
jobcapturepro_uninstall();