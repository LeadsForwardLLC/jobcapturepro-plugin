<?php

/**
 * Admin functionality for JobCapturePro plugin.
 *
 * @package JobCapturePro
 * @since   1.0.0
 */

// Prevent direct access.
if (! defined('ABSPATH')) {
	exit;
}

/**
 * This file defines admin-specific functionality of the plugin.
 *
 * @since 1.0.0
 */

class JobCaptureProAdmin
{
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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Custom option and settings
	 */
	function jobcapturepro_settings_init()
	{

		// Check if user has permission to manage options
		if (! current_user_can('manage_options')) {
			return;
		}

		// Register a new setting in the General Settings page with validation callback.
		register_setting('general', 'jobcapturepro_options', array(
			'sanitize_callback' => array($this, 'jobcapturepro_sanitize_options'),
		));

		// Register a new section in the General Settings page.
		add_settings_section(
			'jobcapturepro_section_developers',
			esc_html(__('JobCapturePro Settings', 'jobcapturepro')),
			array($this, 'jobcapturepro_section_developers_callback'),
			'general'
		);

		// Register a new field in the "jobcapturepro_section_developers" section, inside the General Settings page.
		add_settings_field(
			'jobcapturepro_field_apikey',
			esc_html(__('API Key', 'jobcapturepro')),
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
	function jobcapturepro_section_developers_callback($args)
	{
		// Check if user has permission to manage options
		if (! current_user_can('manage_options')) {
			wp_die(esc_html(__('You do not have sufficient permissions to access this page.', 'jobcapturepro')));
		}

		// Render the section introduction text
?>
		<p id="<?php echo esc_attr($args['id']); ?>">
			<?php esc_html_e('Account settings can be found in the  ', 'jobcapturepro'); ?>
			<a href="https://app.jobcapturepro.com/" target="_blank">JobCapturePro App Dashboard</a>
		</p>
	<?php
	}

	/**
	 * API Key field callback function.
	 */
	function jobcapturepro_field_apikey_cb($args)
	{

		// Check if user has permission to manage options
		if (! current_user_can('manage_options')) {
			echo '<p>' . esc_html(__('You do not have sufficient permissions to modify this setting.', 'jobcapturepro')) . '</p>';
			return;
		}

		// Get the value of the setting we've registered with register_setting()
		$options = get_option('jobcapturepro_options');

		// Render the API Key input field
	?>
		<input
			id="<?php echo esc_attr($args['label_for']); ?>"
			name="jobcapturepro_options[<?php echo esc_attr($args['label_for']); ?>]"
			size="80" type="text"
			value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>" />
		<p class="description">
			<?php esc_html_e('Enter your JobCapturePro API key from your account dashboard.', 'jobcapturepro'); ?>
		</p>
<?php
	}

	/**
	 * Sanitize and validate options before saving.
	 * 
	 * @param array $input The input options to sanitize.
	 * @return array The sanitized options.
	 */
	function jobcapturepro_sanitize_options($input)
	{

		// Verify nonce for additional security
		if (isset($_POST['_wpnonce'])) {
			$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
			if (! wp_verify_nonce($nonce, 'general-options')) {
				$existing_options = get_option('jobcapturepro_options', array());
				add_settings_error(
					'jobcapturepro_options',
					'nonce_verification_failed',
					esc_html(__('Security verification failed. Please try again.', 'jobcapturepro')),
					'error'
				);
				return $existing_options;
			}
		}

		// Check if user has permission to manage options
		if (! current_user_can('manage_options')) {
			// Return existing options without changes if user lacks permission
			$existing_options = get_option('jobcapturepro_options', array());
			add_settings_error(
				'jobcapturepro_options',
				'insufficient_permissions',
				esc_html(__('You do not have sufficient permissions to modify JobCapturePro settings.', 'jobcapturepro')),
				'error'
			);
			return $existing_options;
		}

		// Ensure input is an array
		if (! is_array($input)) {
			$existing_options = get_option('jobcapturepro_options', array());
			add_settings_error(
				'jobcapturepro_options',
				'invalid_input_format',
				esc_html(__('Invalid input format received.', 'jobcapturepro')),
				'error'
			);
			return $existing_options;
		}

		$sanitized_input = array();

		// Sanitize API key
		if (isset($input['jobcapturepro_field_apikey'])) {
			// Remove any whitespace and sanitize
			$api_key = sanitize_text_field(trim($input['jobcapturepro_field_apikey']));

			// Enhanced validation for API key format
			if (empty($api_key)) {
				add_settings_error(
					'jobcapturepro_options',
					'empty_api_key',
					esc_html(__('API Key cannot be empty.', 'jobcapturepro')),
					'error'
				);
			} elseif (strlen($api_key) < 10) {
				add_settings_error(
					'jobcapturepro_options',
					'invalid_api_key_length',
					esc_html(__('API Key must be at least 10 characters long.', 'jobcapturepro')),
					'error'
				);
			} elseif (strlen($api_key) > 200) {
				add_settings_error(
					'jobcapturepro_options',
					'invalid_api_key_length',
					esc_html(__('API Key is too long. Maximum 200 characters allowed.', 'jobcapturepro')),
					'error'
				);
			} elseif (! preg_match('/^[a-zA-Z0-9\-_]+$/', $api_key)) {
				add_settings_error(
					'jobcapturepro_options',
					'invalid_api_key_format',
					esc_html(__('API Key contains invalid characters. Only letters, numbers, hyphens, and underscores are allowed.', 'jobcapturepro')),
					'error'
				);
			} else {
				$sanitized_input['jobcapturepro_field_apikey'] = $api_key;
				add_settings_error(
					'jobcapturepro_options',
					'api_key_updated',
					esc_html(__('JobCapturePro API Key has been updated successfully.', 'jobcapturepro')),
					'updated'
				);
			}
		}

		return $sanitized_input;
	}

	/**
	 * Helper method to sanitize and validate ID parameters
	 * 
	 * @param string|null $id The ID to sanitize
	 * @param string $context Context for error messages ('checkin', 'company', etc.)
	 * @return string|null Sanitized ID or null if invalid
	 */
	public static function sanitize_id_parameter($id, $context = 'ID')
	{
		if (empty($id)) {
			return null;
		}

		// Sanitize the ID
		$sanitized_id = sanitize_text_field($id);

		// Validate ID format - only alphanumeric, hyphens, and underscores
		if (! preg_match('/^[a-zA-Z0-9\-_]+$/', $sanitized_id)) {
			error_log("JobCapturePro: Invalid {$context} ID format: " . $sanitized_id);
			return null;
		}

		// Check reasonable length limits
		if (strlen($sanitized_id) < 1 || strlen($sanitized_id) > 100) {
			error_log("JobCapturePro: Invalid {$context} ID length: " . strlen($sanitized_id));
			return null;
		}

		return $sanitized_id;
	}

	/**
	 * Helper method to sanitize API key from options
	 * 
	 * @return string|null Sanitized API key or null if invalid
	 */
	public static function get_sanitized_api_key()
	{
		$options = get_option('jobcapturepro_options', array());

		if (! isset($options['jobcapturepro_field_apikey'])) {
			return null;
		}

		$api_key = trim($options['jobcapturepro_field_apikey']);

		// Validate the stored API key
		if (empty($api_key) || strlen($api_key) < 10 || strlen($api_key) > 200) {
			return null;
		}

		if (! preg_match('/^[a-zA-Z0-9\-_]+$/', $api_key)) {
			return null;
		}

		return $api_key;
	}
}
