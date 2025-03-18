<?php

/**
 * This file defines admin-specific functionality of the plugin.
 */

class JobCaptureProAdmin {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Custom option and settings
	 */
	function jobcapturepro_settings_init() {

		// Register a new setting in the General Settings page.
		register_setting( 'general', 'jobcapturepro_options' );

		// Register a new section in the General Settings page.
		add_settings_section(
			'jobcapturepro_section_developers',
			__( 'JobCapturePro Settings', 'jobcapturepro' ),
			array($this, 'jobcapturepro_section_developers_callback'),
			'general'
		);

		// Register a new field in the "jobcapturepro_section_developers" section, inside the General Settings page.
		add_settings_field(
			'jobcapturepro_field_apikey',
			__( 'API Key', 'jobcapturepro' ),
			array($this, 'jobcapturepro_field_apikey_cb'),
			'general',
			'jobcapturepro_section_developers',
			array(
				'label_for'         => 'jobcapturepro_field_apikey',
				'class'             => 'jobcapturepro_row',
				'jobcapturepro_custom_data' => 'custom',
			)
		);

	}

	/**
	 * JobCapturePro admin section callback function.
	 */
	function jobcapturepro_section_developers_callback( $args ) {
		// Render the section introduction text
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>">
			<?php esc_html_e( 'Account settings can be found in the  ', 'jobcapturepro' ); ?>
			<a href="https://app.jobcapturepro.com/" target="_blank">JobCapturePro App Dashboard</a>
		</p>
		<?php
	}

	/**
	 * API Key field callback function.
	 */
	function jobcapturepro_field_apikey_cb( $args ) {

		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'jobcapturepro_options' );

		// Render the API Key input field
		?>
		<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="jobcapturepro_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			size="80" type="text"
			value="<?php echo esc_attr( $options[ $args['label_for'] ] ) ?>" />
		<?php
	}

}