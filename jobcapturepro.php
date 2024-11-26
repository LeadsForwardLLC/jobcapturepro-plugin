<?php
    /*
     * Plugin Name:       JobCapturePro Plugin
     * Description:       Use JobCapturePro to capture job leads and manage your business.
     * Version:           1.0.0
     * Author:            JobCapturePro
     * Author URI:        https://www.jobcapturepro.com/
     */

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	/**
	 * Current plugin version.
	 * Uses SemVer - https://semver.org
	 */
	define( 'JOBCAPTUREPROPLUGIN_VERSION', '1.0.0' );

	/**
	 * The core plugin class that is used to define 
	 * admin-specific and public-facing shortcode hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-jobcapturepro.php';

	/**
	 * Begins execution of the plugin.
	 */
	function run_jobcapturepro() {

		$plugin = new JobCaptureProPlugin();
		$plugin->run();

	}

	run_jobcapturepro();
