<?php
/**
 * Bug Reporter Core Class
 *
 * Handles plugin registration and button rendering.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter
 *
 * Main class for plugin registration and rendering.
 */
class Guilamu_Bug_Reporter
{

    /**
     * Registered plugins.
     *
     * @var array
     */
    private static $plugins = array();

    /**
     * Initialize the bug reporter.
     */
    public static function init(): void
    {
        add_action('admin_enqueue_scripts', array(self::class, 'enqueue_assets'));
        add_action('admin_footer', array(self::class, 'render_modal'));
    }

    /**
     * Register a plugin with the bug reporter.
     *
     * @param array $args {
     *     Plugin registration arguments.
     *
     *     @type string $slug        Plugin slug (required).
     *     @type string $name        Plugin display name (required).
     *     @type string $version     Plugin version (required).
     *     @type string $github_repo GitHub repo in owner/repo format (required).
     *     @type string $settings_page Optional settings page hook for auto-injection.
     * }
     * @return bool True if registered successfully.
     */
    public static function register(array $args): bool
    {
        $defaults = array(
            'slug' => '',
            'name' => '',
            'version' => '',
            'github_repo' => '',
            'settings_page' => '',
        );

        $plugin = wp_parse_args($args, $defaults);

        if (empty($plugin['slug']) || empty($plugin['github_repo'])) {
            return false;
        }

        self::$plugins[$plugin['slug']] = $plugin;

        return true;
    }

    /**
     * Get all registered plugins.
     *
     * @return array Registered plugins.
     */
    public static function get_registered_plugins(): array
    {
        return self::$plugins;
    }

    /**
     * Get a registered plugin by slug.
     *
     * @param string $slug Plugin slug.
     * @return array|null Plugin data or null if not found.
     */
    public static function get_plugin(string $slug): ?array
    {
        return self::$plugins[$slug] ?? null;
    }

    /**
     * Render the bug report button.
     *
     * @param string $slug Plugin slug.
     */
    public static function render_button(string $slug): void
    {
        if (!isset(self::$plugins[$slug])) {
            return;
        }

        $plugin = self::$plugins[$slug];
        ?>
        <button type="button" class="button guilamu-bug-report-btn" data-plugin-slug="<?php echo esc_attr($slug); ?>"
            data-plugin-name="<?php echo esc_attr($plugin['name']); ?>">
            <?php echo esc_html__('🐛 Report a Bug', 'guilamu-bug-reporter'); ?>
        </button>
        <?php
    }

    /**
     * Enqueue admin assets.
     */
    public static function enqueue_assets(): void
    {
        // Only load on admin pages
        if (!is_admin()) {
            return;
        }

        wp_enqueue_style(
            'guilamu-bug-reporter',
            GUILAMU_BUG_REPORTER_URL . 'assets/css/bug-reporter.css',
            array(),
            GUILAMU_BUG_REPORTER_VERSION
        );

        wp_enqueue_script(
            'guilamu-bug-reporter',
            GUILAMU_BUG_REPORTER_URL . 'assets/js/bug-reporter.js',
            array('jquery'),
            GUILAMU_BUG_REPORTER_VERSION,
            true
        );

        wp_localize_script(
            'guilamu-bug-reporter',
            'guilamuBugReporter',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('guilamu_bug_reporter'),
                'settingsUrl' => admin_url('options-general.php?page=guilamu-bug-reporter'),
                'isSetupComplete' => Guilamu_Bug_Reporter_Settings::is_setup_complete(),
                'i18n' => array(
                    'loading' => __('Processing...', 'guilamu-bug-reporter'),
                    'success' => __('Report submitted successfully!', 'guilamu-bug-reporter'),
                    'error' => __('An error occurred. Please try again.', 'guilamu-bug-reporter'),
                    'setupRequired' => __('Please configure Bug Reporter settings first.', 'guilamu-bug-reporter'),
                    'step_1_title' => __('Privacy & Data Disclosure', 'guilamu-bug-reporter'),
                    'step_2_title' => __('What Happened?', 'guilamu-bug-reporter'),
                    'step_3_title' => __('Expected Behavior', 'guilamu-bug-reporter'),
                    'step_4_title' => __('Steps to Reproduce', 'guilamu-bug-reporter'),
                    'step_5_title' => __('Severity', 'guilamu-bug-reporter'),
                    'step_6_title' => __('Screenshot (Optional)', 'guilamu-bug-reporter'),
                    'step_7_title' => __('Contact Email', 'guilamu-bug-reporter'),
                    'next' => __('Continue', 'guilamu-bug-reporter'),
                    'back' => __('Back', 'guilamu-bug-reporter'),
                    'submit' => __('Submit Report', 'guilamu-bug-reporter'),
                    'close' => __('Close', 'guilamu-bug-reporter'),
                ),
            )
        );
    }

    /**
     * Render the bug report modal in admin footer.
     */
    public static function render_modal(): void
    {
        // Only if we have registered plugins
        if (empty(self::$plugins)) {
            return;
        }

        include GUILAMU_BUG_REPORTER_PATH . 'templates/bug-report-form.php';
    }
}
