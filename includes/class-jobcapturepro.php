<?php
/**
 * Core plugin class for JobCapturePro.
 *
 * @package JobCapturePro
 * @since   1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This file defines the core plugin class
 *
 * @since 1.0.0
 */

class JobCaptureProPlugin
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * The base URL for our backend API
	 */
	protected $jcp_api_base_url;


	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct()
	{
		if (defined('JOBCAPTUREPRO_VERSION')) {
			$this->version = JOBCAPTUREPRO_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'jobcapturepro';
		$this->jcp_api_base_url = 'https://jcp-api--travel-app-eor5yc.us-central1.hosted.app/api/';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_shortcodes();
		$this->define_api_hooks();
		$this->register_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jobcapturepro-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-jobcapturepro-admin.php';

		/**
		 * The class responsible for generating HTML templates
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jobcapturepro-templates.php';

		/**
		 * The class responsible for defining all actions for public-facing shortcodes
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-jobcapturepro-shortcodes.php';

		/**
		 * The class responsible for defining the REST API
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jobcapturepro-api.php';

		/** 
		 * The class responsible for rendering templates
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-template-loader.php';

		// Create an instance of the loader which will be used to register the hooks with WordPress.
		$this->loader = new JobCaptureProLoader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 */
	private function define_admin_hooks(): void
	{

		// Create an instance of the admin class
		$plugin_admin = new JobCaptureProAdmin(plugin_name: $this->get_plugin_name(), version: $this->get_version());

		// Register the admin_init hook with the corresponding function in the admin class
		$this->loader->add_action(hook: 'admin_init', component: $plugin_admin, callback: 'jobcapturepro_settings_init');
	}

	/**
	 * Register all of the shortcodes related to the public-facing functionality of the plugin.
	 */
	private function define_shortcodes(): void
	{
		// Create an instance of the shortcodes class
		$plugin_shortcodes = new JobCaptureProShortcodes($this->get_plugin_name(), $this->get_version(), $this->get_jobcapturepro_api_base_url());

		// Register the new shortcodes with proper plugin prefix
		$this->loader->add_shortcode(shortcode: 'jobcapturepro_checkin', component: $plugin_shortcodes, callback: 'get_checkin');
		$this->loader->add_shortcode(shortcode: 'jobcapturepro_checkins', component: $plugin_shortcodes, callback: 'get_all_checkins');
		$this->loader->add_shortcode(shortcode: 'jobcapturepro_map', component: $plugin_shortcodes, callback: 'get_map');
		$this->loader->add_shortcode(shortcode: 'jobcapturepro_company_info', component: $plugin_shortcodes, callback: 'get_company_info');
		$this->loader->add_shortcode(shortcode: 'jobcapturepro_combined', component: $plugin_shortcodes, callback: 'get_combined_components');

		// Keep old shortcodes for backwards compatibility
		$this->loader->add_shortcode(shortcode: 'jcp_checkin', component: $plugin_shortcodes, callback: 'get_checkin');
		$this->loader->add_shortcode(shortcode: 'jcp_all_checkins', component: $plugin_shortcodes, callback: 'get_all_checkins');
		$this->loader->add_shortcode(shortcode: 'jcp_multimap', component: $plugin_shortcodes, callback: 'get_map');
		$this->loader->add_shortcode(shortcode: 'jcp_company_info', component: $plugin_shortcodes, callback: 'get_company_info');
		$this->loader->add_shortcode(shortcode: 'jcp_combined_components', component: $plugin_shortcodes, callback: 'get_combined_components');
	}

	/**
	 * Register all API endpoints
	 */
	private function define_api_hooks(): void
	{
		new JobCaptureProAPI($this->get_jobcapturepro_api_base_url());
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	/**
	 * Retrieve the base URL for the backend API.
	 */
	public function get_jobcapturepro_api_base_url()
	{
		return $this->jcp_api_base_url;
	}

	public function register_public_hooks(): void
	{
		$this->loader->add_action(hook: 'wp_enqueue_scripts', component: $this, callback: 'enqueue_assets');
	}

	/**
	 * Enqueue frontend CSS and JS
	 */
	public function enqueue_assets()
	{
		// Only enqueue on pages that might use shortcodes
		if (is_admin()) {
			return;
		}

		// Enqueue Tailwind CSS globally for frontend
		wp_enqueue_style(
			'jobcapturepro-tailwind',
			plugin_dir_url(__FILE__) . '../assets/css/tailwind.css',
			array(),
			$this->version,
			'all'
		);
	}
}
