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
     * Option name for AI enabled flag.
     */
    private const OPTION_AI_ENABLED = 'guilamu_bug_reporter_ai_enabled';

    /**
     * Option name for AI provider (poe or gemini).
     */
    private const OPTION_AI_PROVIDER = 'guilamu_bug_reporter_ai_provider';

    /**
     * Option name for POE API key.
     */
    private const OPTION_POE_KEY = 'guilamu_bug_reporter_poe_key';

    /**
     * Option name for POE model.
     */
    private const OPTION_POE_MODEL = 'guilamu_bug_reporter_poe_model';

    /**
     * Option name for Gemini API key.
     */
    private const OPTION_GEMINI_KEY = 'guilamu_bug_reporter_gemini_key';

    /**
     * Option name for Gemini model.
     */
    private const OPTION_GEMINI_MODEL = 'guilamu_bug_reporter_gemini_model';

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
        add_action('wp_ajax_guilamu_fetch_poe_models', array(self::class, 'ajax_fetch_poe_models'));
        add_action('wp_ajax_guilamu_validate_gemini_key', array(self::class, 'ajax_validate_gemini_key'));
        add_action('wp_ajax_guilamu_save_ai_settings', array(self::class, 'ajax_save_ai_settings'));
        add_action('wp_ajax_guilamu_skip_ai_setup', array(self::class, 'ajax_skip_ai_setup'));
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
        register_setting('guilamu_bug_reporter', self::OPTION_AI_ENABLED, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('guilamu_bug_reporter', self::OPTION_AI_PROVIDER, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('guilamu_bug_reporter', self::OPTION_POE_KEY, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('guilamu_bug_reporter', self::OPTION_POE_MODEL, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('guilamu_bug_reporter', self::OPTION_GEMINI_KEY, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('guilamu_bug_reporter', self::OPTION_GEMINI_MODEL, array(
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
                    esc_html__('Guilamu Bug Reporter needs configuration. %s to get started.', 'guilamu-bug-reporter'),
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
     * Check if AI is enabled.
     *
     * @return bool
     */
    public static function is_ai_enabled(): bool
    {
        return '1' === get_option(self::OPTION_AI_ENABLED, '');
    }

    /**
     * Get selected AI provider.
     *
     * @return string 'poe' or 'gemini'.
     */
    public static function get_ai_provider(): string
    {
        return (string) get_option(self::OPTION_AI_PROVIDER, 'poe');
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
     * Get Gemini API key.
     *
     * @return string
     */
    public static function get_gemini_key(): string
    {
        return (string) get_option(self::OPTION_GEMINI_KEY, '');
    }

    /**
     * Get Gemini model.
     *
     * @return string
     */
    public static function get_gemini_model(): string
    {
        return (string) get_option(self::OPTION_GEMINI_MODEL, 'gemini-2.0-flash');
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
    public static function ajax_fetch_poe_models(): void
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
     * AJAX handler to validate Gemini API key.
     */
    public static function ajax_validate_gemini_key(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

        if (empty($api_key)) {
            wp_send_json_error(__('API key is required.', 'guilamu-bug-reporter'));
        }

        $result = Guilamu_Bug_Reporter_Gemini_API::validate_key($api_key);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        $models = Guilamu_Bug_Reporter_Gemini_API::get_models();

        wp_send_json_success(array(
            'models' => $models,
            'default_model' => 'gemini-2.0-flash',
        ));
    }

    /**
     * AJAX handler to save AI settings (used by both wizard and settings page).
     */
    public static function ajax_save_ai_settings(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        $ai_enabled = isset($_POST['ai_enabled']) ? sanitize_text_field(wp_unslash($_POST['ai_enabled'])) : '0';
        $provider = isset($_POST['ai_provider']) ? sanitize_text_field(wp_unslash($_POST['ai_provider'])) : 'poe';

        update_option(self::OPTION_AI_ENABLED, $ai_enabled);
        update_option(self::OPTION_AI_PROVIDER, $provider);

        if ('poe' === $provider) {
            $api_key = isset($_POST['poe_api_key']) ? sanitize_text_field(wp_unslash($_POST['poe_api_key'])) : '';
            $model = isset($_POST['poe_model']) ? sanitize_text_field(wp_unslash($_POST['poe_model'])) : '';
            update_option(self::OPTION_POE_KEY, $api_key);
            update_option(self::OPTION_POE_MODEL, $model);
        } elseif ('gemini' === $provider) {
            $api_key = isset($_POST['gemini_api_key']) ? sanitize_text_field(wp_unslash($_POST['gemini_api_key'])) : '';
            $model = isset($_POST['gemini_model']) ? sanitize_text_field(wp_unslash($_POST['gemini_model'])) : 'gemini-2.0-flash';
            update_option(self::OPTION_GEMINI_KEY, $api_key);
            update_option(self::OPTION_GEMINI_MODEL, $model);
        }

        update_option(self::OPTION_SETUP_COMPLETE, true);

        wp_send_json_success(__('Settings saved successfully.', 'guilamu-bug-reporter'));
    }

    /**
     * AJAX handler to skip AI setup.
     */
    public static function ajax_skip_ai_setup(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        update_option(self::OPTION_AI_ENABLED, '0');
        update_option(self::OPTION_SETUP_COMPLETE, true);

        wp_send_json_success(__('Setup complete. AI responses are disabled.', 'guilamu-bug-reporter'));
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
