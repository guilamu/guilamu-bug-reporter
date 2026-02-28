# Guilamu Bug Reporter

Unified bug reporting for WordPress plugins with optional AI-powered instant responses and automatic GitHub issue creation.

## Bug Reporting Made Easy
- Conversational multi-step form collecting all relevant debugging info
- Collects full environment info (WordPress, PHP, plugins, theme)
- Automatically creates detailed GitHub issues
- Screenshot upload to WordPress Media Library

## Optional AI-Powered Responses
- Instant AI responses to help users troubleshoot issues
- Choose between **POE** or **Google Gemini** as your AI provider
- AI is fully optional — works great without it too

## Easy Integration
- Register any plugin with a single function call
- Add "Report a Bug" link to plugin pages
- Works with Gravity Forms and any WordPress plugin on [guilamu's GitHub](https://github.com/guilamu)
- Supports multiple plugins simultaneously

## Key Features
- **AI-Powered (Optional):** Get instant helpful responses using POE API or Google Gemini
- **Complete System Info:** Automatically captures WordPress, PHP, MySQL, plugins, and theme info
- **Multilingual:** Works with content in any language
- **Translation-Ready:** All strings are internationalized (French included)
- **Secure:** Nonce verification, capability checks, and sanitized inputs
- **GitHub Updates:** Automatic updates from GitHub releases

## Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- (Optional) POE API key for AI responses – [Get your key](https://poe.com/api_key)
- (Optional) Google Gemini API key (free) for AI responses – [Get your key](https://aistudio.google.com/apikey)

## Installation
1. Upload the `guilamu-bug-reporter` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings → Bug Reporter** to complete setup
4. Optionally configure an AI provider (POE or Gemini) for instant responses
5. Register your plugins using the API (see Integration section)

## Integration

### Register Your Plugin
```php
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'Guilamu_Bug_Reporter' ) ) {
        Guilamu_Bug_Reporter::register( array(
            'slug'        => 'your-plugin-slug',
            'name'        => 'Your Plugin Name',
            'version'     => '1.0.0',
            'github_repo' => 'your-username/your-plugin-repo',
        ) );
    }
} );
```

### Add Report Bug Button
```php
// Option A: Render button in your settings page
Guilamu_Bug_Reporter::render_button( 'your-plugin-slug' );

// Option B: Add to Plugins list
add_filter( 'plugin_row_meta', function( $links, $file ) {
    if ( $file === plugin_basename( __FILE__ ) ) {
        if ( class_exists( 'Guilamu_Bug_Reporter' ) ) {
            $links[] = '<a href="#" class="guilamu-bug-report-btn" data-plugin-slug="your-plugin-slug">🐛 Report a Bug</a>';
        }
    }
    return $links;
}, 10, 2 );
```

## FAQ

### Do I need an AI API key to use this plugin?
No! AI-powered responses are completely optional. The plugin works perfectly without AI — it will still collect all debugging information and create GitHub issues. You can enable AI later from the settings page.

### Which AI provider should I choose?
- **POE:** Offers access to Claude, GPT, and many other models. Create an account at [poe.com](https://poe.com).
- **Google Gemini:** Fast and capable with a generous free tier. Get a key at [Google AI Studio](https://aistudio.google.com/apikey).

### Do I need to configure GitHub?
No, the GitHub token is bundled with the plugin by the developer. You only need to optionally configure an AI provider for instant responses.

### What information is collected in bug reports?
The plugin collects: WordPress version, PHP version, MySQL version, active theme, active plugins, Gravity Forms version (if installed), server info, and user-provided bug description with optional screenshot.

### Can I use this with any WordPress plugin?
Yes! Any plugin can register with Bug Reporter using the `Guilamu_Bug_Reporter::register()` function. It works with Gravity Forms add-ons and any other WordPress plugins.

## Project Structure
```
.
├── guilamu-bug-reporter.php      # Main plugin file
├── README.md
├── assets
│   ├── css
│   │   └── bug-reporter.css      # Modal and form styles
│   └── js
│       └── bug-reporter.js       # Conversational form handler
├── includes
│   ├── class-bug-reporter.php    # Core registration and modal
│   ├── class-form-handler.php    # AJAX form submission
│   ├── class-gemini-api.php      # Google Gemini AI integration
│   ├── class-github-api.php      # GitHub issue creation
│   ├── class-github-updater.php  # GitHub auto-updates
│   ├── class-poe-api.php         # POE AI integration
│   ├── class-readme-extractor.php # README context for AI
│   ├── class-settings.php        # Settings page and setup wizard
│   └── class-system-info.php     # Environment info collector
├── languages
│   ├── guilamu-bug-reporter.pot  # Translation template
│   ├── guilamu-bug-reporter-en_US.po
│   └── guilamu-bug-reporter-fr_FR.po
└── templates
    ├── bug-report-form.php       # Modal form template
    └── settings-page.php         # Admin settings template
```

## Changelog

### 1.3.0
- **New:** Google Gemini API as an alternative AI provider
- **New:** AI is now fully optional — plugin works without any AI configuration
- **New:** Setup wizard allows skipping AI setup entirely
- **New:** Settings page with AI enable/disable toggle and provider selection (POE or Gemini)
- **Improved:** Bug report form now separates "What happened?" and "What did you expect?" into distinct steps
- **Improved:** GitHub issues include separate "What Happened" and "Expected Behavior" sections
- **Improved:** Privacy disclosure conditionally mentions AI only when enabled
- **Improved:** Setup notice no longer blocks bug reporting when AI is not configured

### 1.2.1
- **Security:** Upgraded to fine-grained GitHub token restricted to guilamu repositories only

### 1.2.0
- **New:** Smart README extraction for AI context (FAQ, Requirements, Known Issues)
- **Improved:** AI responses now leverage plugin documentation for better troubleshooting

### 1.1.3
- **Improved:** Removed extra padding from success container
- **Improved:** Added French translations for placeholder texts

### 1.1.2
- **Improved:** Removed green success heading for cleaner UI
- **Improved:** AI response takes more space with reduced padding
- **Improved:** Close button on left, GitHub link as blue button on right

### 1.1.1
- **Improved:** GitHub link moved to footer, aligned with Close button
- **Improved:** AI response formatting with proper line breaks
- **Improved:** Progress bar shows success message on completion
- **Improved:** Removed green checkmark icon from success screen

### 1.1.0
- **Improved:** AI prompt now includes full system info (WordPress, PHP, plugins, Gravity Forms)
- **Improved:** AI responses use plain text formatting for better display
- **Improved:** Removed validation checkmark from settings for cleaner UI
- **Fixed:** Browser autofill prevention on API key field

### 1.0.0
- Initial release
- AI-powered bug reporting with POE API integration
- Automatic GitHub issue creation
- Screenshot upload to Media Library
- Conversational form with privacy disclosure
- Complete system info collection
- Multilingual support with French translation
- GitHub auto-updates

## License
This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0) - see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  Made with love for the WordPress community
</p>
