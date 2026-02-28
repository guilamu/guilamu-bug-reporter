<?php
/**
 * Form Handler Class
 *
 * Handles AJAX form submission and screenshot upload.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_Form_Handler
 */
class Guilamu_Bug_Reporter_Form_Handler
{

    /**
     * Initialize form handlers.
     */
    public static function init(): void
    {
        add_action('wp_ajax_guilamu_submit_bug_report', array(self::class, 'handle_submission'));
        add_action('wp_ajax_guilamu_upload_screenshot', array(self::class, 'handle_screenshot_upload'));
    }

    /**
     * Handle bug report submission.
     */
    public static function handle_submission(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        // Get form data
        $form_data = array(
            'plugin_slug' => isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '',
            'plugin_name' => isset($_POST['plugin_name']) ? sanitize_text_field(wp_unslash($_POST['plugin_name'])) : '',
            'description' => isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '',
            'expected' => isset($_POST['expected']) ? sanitize_textarea_field(wp_unslash($_POST['expected'])) : '',
            'steps' => isset($_POST['steps']) ? sanitize_textarea_field(wp_unslash($_POST['steps'])) : '',
            'severity' => isset($_POST['severity']) ? sanitize_text_field(wp_unslash($_POST['severity'])) : '',
            'email' => isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '',
            'screenshot' => isset($_POST['screenshot_url']) ? esc_url_raw(wp_unslash($_POST['screenshot_url'])) : '',
        );

        // Validate required fields
        if (empty($form_data['plugin_slug']) || empty($form_data['description'])) {
            wp_send_json_error(__('Please fill in all required fields.', 'guilamu-bug-reporter'));
        }

        // Get plugin info
        $plugin = Guilamu_Bug_Reporter::get_plugin($form_data['plugin_slug']);
        if (!$plugin) {
            wp_send_json_error(__('Invalid plugin.', 'guilamu-bug-reporter'));
        }

        $form_data['plugin_name'] = $plugin['name'];
        $form_data['plugin_version'] = $plugin['version'];

        // Collect system info
        $system_info = Guilamu_Bug_Reporter_System_Info::get_all($form_data['plugin_slug']);
        $system_info_text = Guilamu_Bug_Reporter_System_Info::format_for_prompt($system_info);

        // Extract relevant README context for AI
        $readme_context = Guilamu_Bug_Reporter_Readme_Extractor::extract_context($form_data['plugin_slug']);

        // Get AI response (if enabled and configured)
        $ai_response = '';
        if (Guilamu_Bug_Reporter_Settings::is_ai_enabled()) {
            $provider = Guilamu_Bug_Reporter_Settings::get_ai_provider();

            if ('gemini' === $provider) {
                $gemini_key = Guilamu_Bug_Reporter_Settings::get_gemini_key();
                $gemini_model = Guilamu_Bug_Reporter_Settings::get_gemini_model();

                if ($gemini_key && $gemini_model) {
                    $ai_response = Guilamu_Bug_Reporter_Gemini_API::get_bug_response(
                        $gemini_key,
                        $gemini_model,
                        $form_data,
                        $system_info_text,
                        $readme_context
                    );
                }
            } else {
                // Default: POE
                $poe_key = Guilamu_Bug_Reporter_Settings::get_poe_key();
                $poe_model = Guilamu_Bug_Reporter_Settings::get_poe_model();

                if ($poe_key && $poe_model) {
                    $ai_response = Guilamu_Bug_Reporter_POE_API::get_bug_response(
                        $poe_key,
                        $poe_model,
                        $form_data,
                        $system_info_text,
                        $readme_context
                    );
                }
            }
        }

        // Create GitHub issue
        $issue_body = Guilamu_Bug_Reporter_GitHub_API::format_issue_body(
            $form_data,
            $system_info,
            $ai_response ?: '',
            $form_data['screenshot']
        );

        $issue_title = sprintf(
            '[Bug] %s',
            wp_trim_words($form_data['description'], 10, '...')
        );

        $issue = Guilamu_Bug_Reporter_GitHub_API::create_issue(
            $plugin['github_repo'],
            $issue_title,
            $issue_body,
            array('bug', 'user-reported')
        );

        if (is_wp_error($issue)) {
            wp_send_json_error($issue->get_error_message());
        }

        // Store email privately for follow-up
        self::store_reporter_email($issue['number'], $form_data['email'], $plugin['github_repo']);

        wp_send_json_success(array(
            'message' => __('Your bug report has been submitted successfully!', 'guilamu-bug-reporter'),
            'issue_url' => $issue['url'],
            'ai_response' => $ai_response,
        ));
    }

    /**
     * Handle screenshot upload.
     */
    public static function handle_screenshot_upload(): void
    {
        check_ajax_referer('guilamu_bug_reporter', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied.', 'guilamu-bug-reporter'));
        }

        if (empty($_FILES['screenshot'])) {
            wp_send_json_error(__('No file uploaded.', 'guilamu-bug-reporter'));
        }

        // Include required files
        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Validate file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_type = wp_check_filetype(basename($_FILES['screenshot']['name']));

        if (!in_array($_FILES['screenshot']['type'], $allowed_types, true)) {
            wp_send_json_error(__('Invalid file type. Please upload an image.', 'guilamu-bug-reporter'));
        }

        // Check file size (5MB max)
        if ($_FILES['screenshot']['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(__('File too large. Maximum size is 5MB.', 'guilamu-bug-reporter'));
        }

        $attachment_id = media_handle_upload('screenshot', 0);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error($attachment_id->get_error_message());
        }

        $url = wp_get_attachment_url($attachment_id);

        wp_send_json_success(array(
            'url' => $url,
            'id' => $attachment_id,
        ));
    }

    /**
     * Store reporter email privately.
     *
     * @param int    $issue_number GitHub issue number.
     * @param string $email        Reporter email.
     * @param string $repo         Repository.
     */
    private static function store_reporter_email(int $issue_number, string $email, string $repo): void
    {
        if (empty($email)) {
            return;
        }

        $emails = get_option('guilamu_bug_reporter_emails', array());
        $key = $repo . '#' . $issue_number;

        $emails[$key] = array(
            'email' => $email,
            'timestamp' => current_time('mysql'),
        );

        update_option('guilamu_bug_reporter_emails', $emails);
    }
}
