# Guilamu Bug Reporter

Unified bug reporting for WordPress plugins with AI-powered instant responses and automatic GitHub issue creation.

## AI-Powered Bug Reporting
- Instant AI responses to help users troubleshoot issues
- Collects full environment info (WordPress, PHP, plugins, theme)
- Automatically creates detailed GitHub issues
- Screenshot upload to WordPress Media Library

## Easy Integration
- Register any plugin with a single function call
- Add "Report a Bug" link to plugin pages
- Works with Gravity Forms and any WordPress plugin on [guilamu's GitHub](https://github.com/guilamu)
- Supports multiple plugins simultaneously

## Key Features
- **AI-Powered:** Get instant helpful responses using POE API (Claude-Sonnet)
- **Complete System Info:** Automatically captures WordPress, PHP, MySQL, plugins, and theme info
- **Multilingual:** Works with content in any language
- **Translation-Ready:** All strings are internationalized (French included)
- **Secure:** Nonce verification, capability checks, and sanitized inputs
- **GitHub Updates:** Automatic updates from GitHub releases

## Requirements
- POE API key (free) for AI-powered responses – [Get your key](https://poe.com/api_key)
- WordPress 5.8 or higher
- PHP 7.4 or higher

## Installation
1. Upload the `guilamu-bug-reporter` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings → Bug Reporter** and enter your POE API key
4. Register your plugins using the API (see Integration section)

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

### How do I get a POE API key?
Go to [poe.com](https://poe.com), create a free account, then navigate to **Settings → API** and click "Generate API key". It takes less than 1 minute and is completely free.

### Do I need to configure GitHub?
No, the GitHub token is bundled with the plugin by the developer. You only need to configure your POE API key for AI-powered responses.

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
│   ├── class-github-api.php      # GitHub issue creation
│   ├── class-github-updater.php  # GitHub auto-updates
│   ├── class-poe-api.php         # AI response integration
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
