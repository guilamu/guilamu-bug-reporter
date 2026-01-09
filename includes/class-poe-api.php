<?php
/**
 * POE API Class
 *
 * Handles AI responses via POE API.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_POE_API
 */
class Guilamu_Bug_Reporter_POE_API
{

    /**
     * POE API base URL.
     */
    private const API_URL = 'https://api.poe.com';

    /**
     * Get available models from POE API.
     *
     * @param string $api_key POE API key.
     * @return array|WP_Error Array of models or error.
     */
    public static function get_models(string $api_key)
    {
        if (empty($api_key)) {
            return new WP_Error('missing_key', __('API key is required.', 'guilamu-bug-reporter'));
        }

        $response = wp_remote_get(
            self::API_URL . '/v1/models',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if (200 !== $status_code) {
            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __('POE API error: %d', 'guilamu-bug-reporter'),
                    $status_code
                )
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        $models = array();
        foreach ($body['data'] ?? array() as $model) {
            $models[] = array(
                'id' => $model['id'] ?? '',
                'name' => $model['metadata']['display_name'] ?? $model['id'] ?? '',
            );
        }

        // Sort alphabetically
        usort($models, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return $models;
    }

    /**
     * Get AI response for a bug report.
     *
     * @param string $api_key     POE API key.
     * @param string $model       Model ID.
     * @param array  $form_data   Bug report form data.
     * @param string $system_info System info as formatted string.
     * @return string|null AI response or null on failure.
     */
    public static function get_bug_response(string $api_key, string $model, array $form_data, string $system_info = ''): ?string
    {
        if (empty($api_key) || empty($model)) {
            return null;
        }

        $system_prompt = "You are a helpful WordPress plugin support assistant. A user has submitted a bug report with their complete environment information.

IMPORTANT: Do NOT ask for any additional information as we already have all the technical details below.

Provide a helpful response that includes:
1. Brief acknowledgment (1 sentence)
2. 2-3 immediate troubleshooting steps they can try based on their specific environment
3. Reassurance that a developer will review the issue

Keep your response:
- Concise (max 150 words)
- Friendly and professional
- In plain text without markdown formatting
- Do NOT use bullet points or numbered lists with special characters
- Use simple line breaks between paragraphs
- Do NOT promise any fixes or timelines";

        $user_prompt = sprintf(
            "Bug Report for: %s (v%s)

ISSUE DESCRIPTION:
%s

STEPS TO REPRODUCE:
%s

SEVERITY: %s

ENVIRONMENT INFORMATION:
%s",
            $form_data['plugin_name'] ?? '',
            $form_data['plugin_version'] ?? '',
            $form_data['description'] ?? '',
            $form_data['steps'] ?? '',
            $form_data['severity'] ?? '',
            $system_info
        );

        $response = wp_remote_post(
            self::API_URL . '/v1/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'model' => $model,
                    'messages' => array(
                        array('role' => 'system', 'content' => $system_prompt),
                        array('role' => 'user', 'content' => $user_prompt),
                    ),
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                )),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            return null;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if (200 !== $status_code) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $body['choices'][0]['message']['content'] ?? null;
    }
}
