<?php
/**
 * Plugin Name: Guilamu Bug Reporter
 * Plugin URI: https://github.com/guilamu/guilamu-bug-reporter
 * Description: Unified bug reporting for all Guilamu plugins with AI-powered instant responses.
 * Version: 1.3.1
 * Author: Guilamu
 * Author URI: https://github.com/guilamu
 * Text Domain: guilamu-bug-reporter
 * Domain Path: /languages
 * Update URI: https://github.com/guilamu/guilamu-bug-reporter/
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('GUILAMU_BUG_REPORTER_VERSION', '1.3.1');
define('GUILAMU_BUG_REPORTER_PATH', plugin_dir_path(__FILE__));
define('GUILAMU_BUG_REPORTER_URL', plugin_dir_url(__FILE__));
define('GUILAMU_BUG_REPORTER_BASENAME', plugin_basename(__FILE__));

/**
 * Load plugin text domain for translations.
 */
add_action('init', function () {
    load_plugin_textdomain(
        'guilamu-bug-reporter',
        false,
        dirname(GUILAMU_BUG_REPORTER_BASENAME) . '/languages'
    );
});

// Include required files
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-github-updater.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-bug-reporter.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-settings.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-system-info.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-github-api.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-poe-api.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-gemini-api.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-readme-extractor.php';
require_once GUILAMU_BUG_REPORTER_PATH . 'includes/class-form-handler.php';

// Initialize components
Guilamu_Bug_Reporter_GitHub_Updater::init();
Guilamu_Bug_Reporter::init();
Guilamu_Bug_Reporter_Settings::init();
Guilamu_Bug_Reporter_Form_Handler::init();

// Add "View details" link to plugin row meta
add_filter('plugin_row_meta', function ($links, $file) {
    if (GUILAMU_BUG_REPORTER_BASENAME === $file) {
        $links[] = sprintf(
            '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
            esc_url(self_admin_url(
                'plugin-install.php?tab=plugin-information&plugin=guilamu-bug-reporter'
                . '&TB_iframe=true&width=772&height=926'
            )),
            esc_attr__('More information about Guilamu Bug Reporter', 'guilamu-bug-reporter'),
            esc_attr__('Guilamu Bug Reporter', 'guilamu-bug-reporter'),
            esc_html__('View details', 'guilamu-bug-reporter')
        );
    }
    return $links;
}, 10, 2);
