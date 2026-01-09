<?php
/**
 * Settings Page Class
 *
 * Handles plugin settings and setup wizard.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_Settings
 */
class Guilamu_Bug_Reporter_Settings
{

    /**
     * Option name for POE API key.
     */
    private const OPTION_POE_KEY = 'guilamu_bug_reporter_poe_key';

    /**
     * Option name for POE model.
     */
    private const OPTION_POE_MODEL = 'guilamu_bug_reporter_poe_model';

    /**
     * Option name for setup complete flag.
     */
    private const OPTION_SETUP_COMPLETE = 'guilamu_bug_reporter_setup_complete';

    /**
     * Initialize settings.
     */
    public static function init(): void
    {
        add_action('admin_menu', array(self::class, 'add_menu_page'));
        add_action('admin_init', array(self::class, 'register_settings'));
        add_action('admin_notices', array(self::class, 'setup_notice'));
        add_action('wp_ajax_guilamu_fetch_poe_models', array(self::class, 'ajax_fetch_models'));
        add_action('wp_ajax_guilamu_save_poe_key', array(self::class, 'ajax_save_poe_key'));
    }

    /**
     * Add admin menu page.
     */
    public static function add_menu_page(): void
    {
        add_options_page(
            __('Guilamu Bug Reporter', 'guilamu-bug-reporter'),
            __('Bug Reporter', 'guilamu-bug-reporter'),
            'manage_options',
            'guilamu-bug-reporter',
            array(self::class, 'render_settings_page')
        );
    }

    /**
     * Register settings.
     */
    public static function register_settings(): void
    {
        register_setting('guilamu_bug_reporter', self::OPTION_POE_KEY, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('guilamu_bug_reporter', self::OPTION_POE_MODEL, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }

    /**
     * Show setup notice if not configured.
     */
    public static function setup_notice(): void
    {
        if (get_option(self::OPTION_SETUP_COMPLETE)) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && 'settings_page_guilamu-bug-reporter' === $screen->id) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php
                printf(
                    /* translators: %s: settings page URL */
                    esc_html__('Guilamu Bug Reporter needs configuration. %s to get started (takes less than 1 minute, completely free!)', 'guilamu-bug-reporter'),
                    '<a href="' . esc_url(admin_url('options-general.php?page=guilamu-bug-reporter')) . '">' . esc_html__('Click here', 'guilamu-bug-reporter') . '</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render settings page.
     */
    public static function render_settings_page(): void
    {
        include GUILAMU_BUG_REPORTER_PATH . 'templates/settings-page.php';
    }

    /**
     * Get POE API key.
     *
     * @return string
     */
    public static function get_poe_key(): string
    {
        return (string) get_option(self::OPTION_POE_KEY, '');
    }

    /**
     * Get POE model.
     *
     * @return string
     */
    public static function get_poe_model(): string
    {
        return (string) get_option(self::OPTION_POE_MODEL, '');
    }

    /**
     * Check if setup is complete.
     *
     * @return bool
     */
    public static function is_setup_complete(): bool
    {
        return (bool) get_option(self::OPTION_SETUP_COMPLETE, false);
    }

    /**
     * AJAX handler to fetch POE models.
     */
    public static function ajax_fetch_models(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

        if (empty($api_key)) {
            wp_send_json_error(__('API key is required.', 'guilamu-bug-reporter'));
        }

        $models = Guilamu_Bug_Reporter_POE_API::get_models($api_key);

        if (is_wp_error($models)) {
            wp_send_json_error($models->get_error_message());
        }

        $default_model = self::find_latest_claude_sonnet($models);

        wp_send_json_success(array(
            'models' => $models,
            'default_model' => $default_model,
        ));
    }

    /**
     * AJAX handler to save POE API key.
     */
    public static function ajax_save_poe_key(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        $model = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : '';

        update_option(self::OPTION_POE_KEY, $api_key);
        update_option(self::OPTION_POE_MODEL, $model);
        update_option(self::OPTION_SETUP_COMPLETE, true);

        wp_send_json_success(__('Settings saved successfully.', 'guilamu-bug-reporter'));
    }

    /**
     * Find the latest Claude Sonnet model.
     *
     * @param array $models Available models.
     * @return string|null Model ID or null.
     */
    private static function find_latest_claude_sonnet(array $models): ?string
    {
        $sonnet_models = array();

        foreach ($models as $model) {
            $name = strtolower($model['name'] ?? '');
            if (strpos($name, 'claude') !== false && strpos($name, 'sonnet') !== false) {
                preg_match('/(\d+\.?\d*)/', $model['name'], $matches);
                $version = floatval($matches[1] ?? 0);
                $sonnet_models[] = array(
                    'id' => $model['id'],
                    'version' => $version,
                );
            }
        }

        if (empty($sonnet_models)) {
            return null;
        }

        usort($sonnet_models, fn($a, $b) => $b['version'] <=> $a['version']);

        return $sonnet_models[0]['id'];
    }
}
