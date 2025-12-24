<?php

/**
 * Template loader functionality for JobCapturePro plugin.
 *
 * @package JobCapturePro
 * @since   1.0.0
 */

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Template loader class
 *
 * @since 1.0.0
 */
class JobCapturePro_Template
{
    /**
     * Locate a template file
     */
    public static function locate_template($template_name)
    {
        $plugin_template = plugin_dir_path(dirname(__FILE__)) . "/templates/{$template_name}.php";

        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return false;
    }

    /**
     * Render a template with variables
     */
    public static function render_template($template_name, $variables = [])
    {
        $template_path = self::locate_template($template_name);

        if (!$template_path) {
            return '';
        }

        // Extract variables into current scope
        extract($variables);

        // Start output buffering
        ob_start();

        // Include the template
        include $template_path;

        // Return the output
        return ob_get_clean();
    }
}
