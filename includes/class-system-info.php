<?php
/**
 * System Info Collector
 *
 * Gathers WordPress environment and plugin information.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_System_Info
 */
class Guilamu_Bug_Reporter_System_Info
{

    /**
     * Get all system information.
     *
     * @param string $plugin_slug Optional plugin slug for specific info.
     * @return array System information.
     */
    public static function get_all(string $plugin_slug = ''): array
    {
        global $wpdb;

        $info = array(
            'WordPress Version' => get_bloginfo('version'),
            'PHP Version' => PHP_VERSION,
            'MySQL Version' => $wpdb->db_version(),
            'Server Software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown',
            'Memory Limit' => ini_get('memory_limit'),
            'Max Upload Size' => size_format(wp_max_upload_size()),
            'WP_DEBUG' => defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled',
            'WP_DEBUG_LOG' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Enabled' : 'Disabled',
            'Timezone' => wp_timezone_string(),
            'Active Theme' => self::get_theme_info(),
        );

        // Plugin-specific info
        if ($plugin_slug) {
            $plugin = Guilamu_Bug_Reporter::get_plugin($plugin_slug);
            if ($plugin) {
                $info['Reporting Plugin'] = $plugin['name'] . ' ' . $plugin['version'];
            }
        }

        // Gravity Forms info (if active)
        if (class_exists('GFForms')) {
            $info['Gravity Forms'] = GFForms::$version;
            $info['GF_DEBUG'] = defined('GF_DEBUG') && GF_DEBUG ? 'Enabled' : 'Disabled';
        }

        // Active plugins
        $info['Active Plugins'] = self::get_active_plugins_list();

        return $info;
    }

    /**
     * Get theme information.
     *
     * @return string Theme name and version.
     */
    private static function get_theme_info(): string
    {
        $theme = wp_get_theme();
        return $theme->get('Name') . ' ' . $theme->get('Version');
    }

    /**
     * Get list of active plugins.
     *
     * @return string Comma-separated list of plugins.
     */
    private static function get_active_plugins_list(): string
    {
        $active_plugins = get_option('active_plugins', array());
        $plugins = array();

        foreach ($active_plugins as $plugin_path) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path, false, false);
            if (!empty($plugin_data['Name'])) {
                $plugins[] = $plugin_data['Name'] . ' ' . ($plugin_data['Version'] ?? '');
            }
        }

        return implode(', ', $plugins);
    }

    /**
     * Format system info as markdown table.
     *
     * @param array $info System information array.
     * @return string Markdown table.
     */
    public static function format_as_markdown(array $info): string
    {
        $md = "| Setting | Value |\n|---------|-------|\n";

        foreach ($info as $key => $value) {
            // Truncate long values (like plugin lists)
            if (strlen($value) > 100) {
                $value = substr($value, 0, 100) . '...';
            }
            $md .= '| ' . esc_html($key) . ' | ' . esc_html($value) . " |\n";
        }

        return $md;
    }

    /**
     * Format system info as plain text for AI prompt.
     *
     * @param array $info System information array.
     * @return string Plain text format.
     */
    public static function format_for_prompt(array $info): string
    {
        $lines = array();

        foreach ($info as $key => $value) {
            // Truncate long values
            if (strlen($value) > 150) {
                $value = substr($value, 0, 150) . '...';
            }
            $lines[] = $key . ': ' . $value;
        }

        return implode("\n", $lines);
    }
}
