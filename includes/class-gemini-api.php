<?php
/**
 * Gemini API Class
 *
 * Handles AI responses via Google Gemini API (OpenAI-compatible endpoint).
 * Adapted from DistillPress Gemini API Service.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_Gemini_API
 */
class Guilamu_Bug_Reporter_Gemini_API
{

    /**
     * Gemini API base URL (OpenAI-compatible).
     */
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/openai';

    /**
     * Available Gemini models.
     */
    private const MODELS = array(
        array(
            'id'   => 'gemini-2.0-flash',
            'name' => 'Gemini 2.0 Flash (fast, recommended)',
        ),
        array(
            'id'   => 'gemini-2.0-flash-lite',
            'name' => 'Gemini 2.0 Flash Lite (fastest, cheapest)',
        ),
        array(
            'id'   => 'gemini-1.5-pro',
            'name' => 'Gemini 1.5 Pro (most capable)',
        ),
    );

    /**
     * Get available Gemini models.
     *
     * @return array Array of models with id and name keys.
     */
    public static function get_models(): array
    {
        return self::MODELS;
    }

    /**
     * Validate a Gemini API key by making a lightweight request.
     *
     * @param string $api_key Gemini API key.
     * @return bool|WP_Error True if valid, WP_Error otherwise.
     */
    public static function validate_key(string $api_key)
    {
        if (empty($api_key)) {
            return new WP_Error('missing_key', __('API key is required.', 'guilamu-bug-reporter'));
        }

        $response = wp_remote_get(
            self::API_URL . '/models',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'timeout' => 15,
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
                    __('Gemini API error: %d', 'guilamu-bug-reporter'),
                    $status_code
                )
            );
        }

        return true;
    }

    /**
     * Get AI response for a bug report.
     *
     * @param string $api_key        Gemini API key.
     * @param string $model          Model ID.
     * @param array  $form_data      Bug report form data.
     * @param string $system_info    System info as formatted string.
     * @param string $readme_context Extracted README sections for context.
     * @return string|null AI response or null on failure.
     */
    public static function get_bug_response(string $api_key, string $model, array $form_data, string $system_info = '', string $readme_context = ''): ?string
    {
        if (empty($api_key) || empty($model)) {
            return null;
        }

        $system_prompt = "You are a helpful WordPress plugin support assistant. A user has submitted a bug report with their complete environment information.

IMPORTANT: Do NOT ask for any additional information as we already have all the technical details below.

Provide a helpful response that includes:
1. Brief acknowledgment (1 sentence)
2. 2-3 immediate troubleshooting steps they can try based on their specific environment and the plugin documentation context (if provided)
3. Reassurance that a developer will review the issue

Keep your response:
- Concise (max 150 words)
- Friendly and professional
- In plain text without markdown formatting
- Do NOT use bullet points or numbered lists with special characters
- Use simple line breaks between paragraphs
- Do NOT promise any fixes or timelines";

        // Build documentation context section if available
        $doc_context_section = '';
        if (!empty($readme_context)) {
            $doc_context_section = "\n\nPLUGIN DOCUMENTATION CONTEXT:\n" . $readme_context;
        }

        $user_prompt = sprintf(
            "Bug Report for: %s (v%s)

ISSUE DESCRIPTION:
What happened: %s
What was expected: %s

STEPS TO REPRODUCE:
%s

SEVERITY: %s

ENVIRONMENT INFORMATION:
%s%s",
            $form_data['plugin_name'] ?? '',
            $form_data['plugin_version'] ?? '',
            $form_data['description'] ?? '',
            $form_data['expected'] ?? '',
            $form_data['steps'] ?? '',
            $form_data['severity'] ?? '',
            $system_info,
            $doc_context_section
        );

        $response = wp_remote_post(
            self::API_URL . '/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'model'       => $model,
                    'messages'    => array(
                        array('role' => 'system', 'content' => $system_prompt),
                        array('role' => 'user', 'content' => $user_prompt),
                    ),
                    'temperature' => 0.7,
                    'max_tokens'  => 500,
                )),
                'timeout'   => 60,
                'sslverify' => true,
            )
        );

        if (is_wp_error($response)) {
            return null;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if (200 !== $status_code) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Guilamu Bug Reporter Gemini API Error: ' . wp_remote_retrieve_body($response));
            }
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $body['choices'][0]['message']['content'] ?? null;
    }
}
