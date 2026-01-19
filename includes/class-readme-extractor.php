<?php
/**
 * README Extractor Class
 *
 * Extracts relevant sections from plugin README files for AI context.
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guilamu_Bug_Reporter_Readme_Extractor
 */
class Guilamu_Bug_Reporter_Readme_Extractor
{
    /**
     * Sections to extract from README (in priority order).
     */
    private const SECTIONS = array(
        'Requirements',
        'FAQ',
        'Known Issues',
        'Limitations',
        'Troubleshooting',
    );

    /**
     * Maximum words to include in context (~300 tokens).
     */
    private const MAX_WORDS = 150;

    /**
     * Extract relevant context from a plugin's README.
     *
     * @param string $plugin_slug Plugin slug (folder name).
     * @return string Formatted context for AI, or empty string if nothing useful.
     */
    public static function extract_context(string $plugin_slug): string
    {
        $readme_path = self::find_readme($plugin_slug);
        
        if (!$readme_path || !file_exists($readme_path)) {
            return '';
        }

        $content = file_get_contents($readme_path);
        if (empty($content)) {
            return '';
        }

        $extracted = array();

        foreach (self::SECTIONS as $section) {
            $section_content = self::extract_section($content, $section);
            if ($section_content) {
                $extracted[$section] = $section_content;
            }
        }

        if (empty($extracted)) {
            return '';
        }

        // Format and truncate
        return self::format_context($extracted);
    }

    /**
     * Find README file for a plugin.
     *
     * @param string $plugin_slug Plugin slug.
     * @return string|null Path to README or null if not found.
     */
    private static function find_readme(string $plugin_slug): ?string
    {
        // Handle nested plugin folders (e.g., plugin-name/plugin-name/)
        $possible_paths = array(
            WP_PLUGIN_DIR . '/' . $plugin_slug . '/README.md',
            WP_PLUGIN_DIR . '/' . $plugin_slug . '/readme.md',
            WP_PLUGIN_DIR . '/' . $plugin_slug . '/README.txt',
            WP_PLUGIN_DIR . '/' . $plugin_slug . '/readme.txt',
            WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_slug . '/README.md',
            WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_slug . '/readme.md',
        );

        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Extract content under a markdown heading.
     *
     * @param string $content  Full README content.
     * @param string $heading  Heading text to find (without ##).
     * @return string|null Section content or null if not found.
     */
    private static function extract_section(string $content, string $heading): ?string
    {
        // Match ## Heading or ### Heading (case-insensitive)
        $pattern = '/^#{2,3}\s*' . preg_quote($heading, '/') . '\s*$/im';
        
        if (!preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start_pos = $matches[0][1] + strlen($matches[0][0]);
        
        // Find next heading of same or higher level
        $remaining = substr($content, $start_pos);
        
        if (preg_match('/^#{1,3}\s+/m', $remaining, $next_match, PREG_OFFSET_CAPTURE)) {
            $section_content = substr($remaining, 0, $next_match[0][1]);
        } else {
            $section_content = $remaining;
        }

        $section_content = trim($section_content);
        
        // Skip if section is too short (likely empty or just whitespace)
        if (strlen($section_content) < 20) {
            return null;
        }

        return $section_content;
    }

    /**
     * Format extracted sections and apply word budget.
     *
     * @param array $sections Associative array of section => content.
     * @return string Formatted context string.
     */
    private static function format_context(array $sections): string
    {
        $output = array();
        $total_words = 0;

        foreach ($sections as $section => $content) {
            // Clean up content (remove excessive whitespace, code blocks)
            $content = self::clean_content($content);
            
            $words = str_word_count($content);
            
            // Check if adding this section exceeds budget
            if ($total_words + $words > self::MAX_WORDS) {
                // Truncate this section to fit
                $remaining_budget = self::MAX_WORDS - $total_words;
                if ($remaining_budget > 20) {
                    $content = self::truncate_to_words($content, $remaining_budget);
                    $output[] = "**{$section}:** {$content}...";
                }
                break;
            }

            $output[] = "**{$section}:** {$content}";
            $total_words += $words;
        }

        return implode("\n\n", $output);
    }

    /**
     * Clean content for AI consumption.
     *
     * @param string $content Raw markdown content.
     * @return string Cleaned content.
     */
    private static function clean_content(string $content): string
    {
        // Remove code blocks
        $content = preg_replace('/```[\s\S]*?```/', '[code example]', $content);
        
        // Remove inline code but keep text
        $content = preg_replace('/`([^`]+)`/', '$1', $content);
        
        // Remove markdown links but keep text
        $content = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $content);
        
        // Remove images
        $content = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $content);
        
        // Normalize whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }

    /**
     * Truncate text to a maximum number of words.
     *
     * @param string $text      Text to truncate.
     * @param int    $max_words Maximum words.
     * @return string Truncated text.
     */
    private static function truncate_to_words(string $text, int $max_words): string
    {
        $words = explode(' ', $text);
        
        if (count($words) <= $max_words) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $max_words));
    }
}
