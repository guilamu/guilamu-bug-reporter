<?php
/**
 * GitHub API Class
 *
 * Handles GitHub issue creation with bundled token.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_GitHub_API
 */
class Guilamu_Bug_Reporter_GitHub_API
{

    /**
     * Encoded token (injected at build time via GitHub Actions).
     * DO NOT commit real tokens to source control.
     */
    private const ENCODED_TOKEN = 'Z2hwX1BYcXY2YWt6ajdyd0FKV3ZjV0MycUdYa3daS254SjRmRXlPdw==';

    /**
     * GitHub API base URL.
     */
    private const API_URL = 'https://api.github.com';

    /**
     * Get the GitHub token.
     *
     * @return string Token or empty string.
     */
    private static function get_token(): string
    {
        // Try environment variable first
        $token = getenv('GUILAMU_GITHUB_TOKEN');
        if ($token) {
            return $token;
        }

        // Try wp-config constant
        if (defined('GUILAMU_GITHUB_TOKEN')) {
            return GUILAMU_GITHUB_TOKEN;
        }

        // Fallback to encoded token (build-time injected)
        if (!empty(self::ENCODED_TOKEN)) {
            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
            return base64_decode(self::ENCODED_TOKEN);
        }

        return '';
    }

    /**
     * Create a GitHub issue.
     *
     * @param string $repo        Repository in owner/repo format.
     * @param string $title       Issue title.
     * @param string $body        Issue body (markdown).
     * @param array  $labels      Optional labels.
     * @return array|WP_Error Issue data or error.
     */
    public static function create_issue(string $repo, string $title, string $body, array $labels = array())
    {
        $token = self::get_token();

        if (empty($token)) {
            return new WP_Error(
                'no_token',
                __('GitHub token is not configured.', 'guilamu-bug-reporter')
            );
        }

        $response = wp_remote_post(
            self::API_URL . '/repos/' . $repo . '/issues',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'Guilamu-Bug-Reporter/' . GUILAMU_BUG_REPORTER_VERSION,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'title' => $title,
                    'body' => $body,
                    'labels' => $labels,
                )),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if (201 !== $status_code) {
            $error_body = wp_remote_retrieve_body($response);
            return new WP_Error(
                'github_api_error',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __('GitHub API error: %d', 'guilamu-bug-reporter'),
                    $status_code
                )
            );
        }

        $issue = json_decode(wp_remote_retrieve_body($response), true);

        return array(
            'number' => $issue['number'] ?? 0,
            'url' => $issue['html_url'] ?? '',
            'title' => $issue['title'] ?? '',
        );
    }

    /**
     * Format bug report as GitHub issue body.
     *
     * @param array  $form_data   Form submission data.
     * @param array  $system_info System information.
     * @param string $ai_response Optional AI response.
     * @param string $screenshot_url Optional screenshot URL.
     * @return string Formatted markdown body.
     */
    public static function format_issue_body(array $form_data, array $system_info, string $ai_response = '', string $screenshot_url = ''): string
    {
        $body = "## Bug Report\n\n";
        $body .= '**Plugin:** ' . esc_html($form_data['plugin_name'] ?? '') . "\n";
        $body .= '**Severity:** ' . esc_html($form_data['severity'] ?? '') . "\n\n";

        $body .= "### Description\n";
        $body .= esc_html($form_data['description'] ?? '') . "\n\n";

        $body .= "### Steps to Reproduce\n";
        $body .= esc_html($form_data['steps'] ?? '') . "\n\n";

        if ($screenshot_url) {
            $body .= "### Screenshot\n";
            $body .= '![Screenshot](' . esc_url($screenshot_url) . ")\n\n";
        }

        $body .= "---\n\n";

        if ($ai_response) {
            $body .= "### 🤖 AI Initial Response\n";
            $body .= '> ' . str_replace("\n", "\n> ", esc_html($ai_response)) . "\n\n";
            $body .= "---\n\n";
        }

        $body .= "<details>\n<summary>System Information</summary>\n\n";
        $body .= Guilamu_Bug_Reporter_System_Info::format_as_markdown($system_info);
        $body .= "\n</details>\n";

        return $body;
    }
}
