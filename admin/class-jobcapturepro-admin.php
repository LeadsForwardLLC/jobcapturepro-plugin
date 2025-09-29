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

		// Check if user has permission to manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Register a new setting in the General Settings page with validation callback.
		register_setting( 'general', 'jobcapturepro_options', array(
			'sanitize_callback' => array( $this, 'jobcapturepro_sanitize_options' ),
		) );

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
		// Check if user has permission to manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'jobcapturepro' ) );
		}

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

		// Check if user has permission to manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<p>' . esc_html__( 'You do not have sufficient permissions to modify this setting.', 'jobcapturepro' ) . '</p>';
			return;
		}

		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'jobcapturepro_options' );

		// Render the API Key input field
		?>
		<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="jobcapturepro_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			size="80" type="text"
			value="<?php echo esc_attr( $options[ $args['label_for'] ] ?? '' ); ?>" />
		<p class="description">
			<?php esc_html_e( 'Enter your JobCapturePro API key from your account dashboard.', 'jobcapturepro' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize and validate options before saving.
	 * 
	 * @param array $input The input options to sanitize.
	 * @return array The sanitized options.
	 */
	function jobcapturepro_sanitize_options( $input ) {

		// Check if user has permission to manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			// Return existing options without changes if user lacks permission
			$existing_options = get_option( 'jobcapturepro_options', array() );
			add_settings_error(
				'jobcapturepro_options',
				'insufficient_permissions',
				__( 'You do not have sufficient permissions to modify JobCapturePro settings.', 'jobcapturepro' ),
				'error'
			);
			return $existing_options;
		}

		$sanitized_input = array();

		// Sanitize API key
		if ( isset( $input['jobcapturepro_field_apikey'] ) ) {
			$api_key = sanitize_text_field( $input['jobcapturepro_field_apikey'] );
			
			// Basic validation for API key format (adjust as needed for your API key format)
			if ( empty( $api_key ) ) {
				add_settings_error(
					'jobcapturepro_options',
					'empty_api_key',
					__( 'API Key cannot be empty.', 'jobcapturepro' ),
					'error'
				);
			} elseif ( strlen( $api_key ) < 10 ) { // Adjust minimum length as needed
				add_settings_error(
					'jobcapturepro_options',
					'invalid_api_key',
					__( 'API Key appears to be invalid. Please check your API key.', 'jobcapturepro' ),
					'error'
				);
			} else {
				$sanitized_input['jobcapturepro_field_apikey'] = $api_key;
				add_settings_error(
					'jobcapturepro_options',
					'api_key_updated',
					__( 'JobCapturePro API Key has been updated successfully.', 'jobcapturepro' ),
					'updated'
				);
			}
		}

		return $sanitized_input;
	}

}