<?php
/**
 * Settings Page Template
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_setup_complete = Guilamu_Bug_Reporter_Settings::is_setup_complete();
$poe_key = Guilamu_Bug_Reporter_Settings::get_poe_key();
$poe_model = Guilamu_Bug_Reporter_Settings::get_poe_model();
$registered = Guilamu_Bug_Reporter::get_registered_plugins();
?>

<div class="wrap guilamu-bug-reporter-settings-wrap">
    <h1>
        <?php esc_html_e('Guilamu Bug Reporter', 'guilamu-bug-reporter'); ?>
    </h1>

    <?php if (!$is_setup_complete): ?>
        <!-- Setup Wizard -->
        <div class="guilamu-bug-reporter-setup-wizard">
            <h2>🤖
                <?php esc_html_e('Welcome to Guilamu Bug Reporter!', 'guilamu-bug-reporter'); ?>
            </h2>
            <p class="description">
                <?php esc_html_e('To provide instant AI-powered responses to bug reports, you need a POE API key.', 'guilamu-bug-reporter'); ?>
            </p>

            <div class="highlight">
                <strong>⏱️
                    <?php esc_html_e('This takes less than 1 minute and is completely FREE!', 'guilamu-bug-reporter'); ?>
                </strong>
            </div>

            <ol>
                <li>
                    <?php esc_html_e('Go to poe.com and create a free account', 'guilamu-bug-reporter'); ?>
                </li>
                <li>
                    <?php esc_html_e('Navigate to: Settings → API', 'guilamu-bug-reporter'); ?>
                </li>
                <li>
                    <?php esc_html_e('Click "Generate API key"', 'guilamu-bug-reporter'); ?>
                </li>
                <li>
                    <?php esc_html_e('Copy and paste it below', 'guilamu-bug-reporter'); ?>
                </li>
            </ol>

            <div class="guilamu-bug-reporter-setup-links">
                <a href="https://poe.com/login" target="_blank" class="button">
                    <?php esc_html_e('Create Free POE Account →', 'guilamu-bug-reporter'); ?>
                </a>
                <a href="https://poe.com/api_key" target="_blank" class="button button-primary">
                    <?php esc_html_e('Get Your API Key →', 'guilamu-bug-reporter'); ?>
                </a>
            </div>

            <form id="guilamu-setup-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="poe_api_key">
                                <?php esc_html_e('POE API Key', 'guilamu-bug-reporter'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="password" name="poe_api_key" id="poe_api_key" class="regular-text" value=""
                                autocomplete="new-password"
                                placeholder="<?php esc_attr_e('Paste your API key here', 'guilamu-bug-reporter'); ?>">
                            <span id="poe_key_status"></span>
                        </td>
                    </tr>
                    <tr id="model_row" style="display: none;">
                        <th scope="row">
                            <label for="poe_model">
                                <?php esc_html_e('AI Model', 'guilamu-bug-reporter'); ?>
                            </label>
                        </th>
                        <td>
                            <select name="poe_model" id="poe_model" class="regular-text">
                                <option value="">
                                    <?php esc_html_e('Enter API key first...', 'guilamu-bug-reporter'); ?>
                                </option>
                            </select>
                            <span id="model_loading" style="display: none;">
                                <span class="spinner is-active"></span>
                            </span>
                            <p class="description">
                                <?php esc_html_e('Auto-detected: Latest Claude-Sonnet', 'guilamu-bug-reporter'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary button-hero" id="complete_setup" disabled>
                        <?php esc_html_e('Complete Setup →', 'guilamu-bug-reporter'); ?>
                    </button>
                </p>
            </form>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                var debounceTimer;

                $('#poe_api_key').on('input', function () {
                    var apiKey = $(this).val().trim();

                    clearTimeout(debounceTimer);

                    if (apiKey.length < 10) {
                        $('#model_row').hide();
                        $('#complete_setup').prop('disabled', true);
                        return;
                    }

                    debounceTimer = setTimeout(function () {
                        $('#model_loading').show();
                        $('#poe_key_status').text('');

                        $.post(ajaxurl, {
                            action: 'guilamu_fetch_poe_models',
                            nonce: '<?php echo esc_js(wp_create_nonce('guilamu_bug_reporter')); ?>',
                            api_key: apiKey
                        }, function (response) {
                            $('#model_loading').hide();

                            if (response.success) {
                                $('#poe_key_status').html('');
                                var $select = $('#poe_model').empty();

                                response.data.models.forEach(function (model) {
                                    $select.append($('<option>', {
                                        value: model.id,
                                        text: model.name,
                                        selected: model.id === response.data.default_model
                                    }));
                                });

                                $('#model_row').show();
                                $('#complete_setup').prop('disabled', false);
                            } else {
                                $('#poe_key_status').html('<span style="color:red;">✗ ' + response.data + '</span>');
                                $('#model_row').hide();
                                $('#complete_setup').prop('disabled', true);
                            }
                        });
                    }, 500);
                });

                $('#guilamu-setup-form').on('submit', function (e) {
                    e.preventDefault();

                    var $btn = $('#complete_setup');
                    $btn.prop('disabled', true).text('<?php echo esc_js(__('Saving...', 'guilamu-bug-reporter')); ?>');

                    $.post(ajaxurl, {
                        action: 'guilamu_save_poe_key',
                        nonce: '<?php echo esc_js(wp_create_nonce('guilamu_bug_reporter')); ?>',
                        api_key: $('#poe_api_key').val(),
                        model: $('#poe_model').val()
                    }, function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data);
                            $btn.prop('disabled', false).text('<?php echo esc_js(__('Complete Setup →', 'guilamu-bug-reporter')); ?>');
                        }
                    });
                });
            });
        </script>

    <?php else: ?>
        <!-- Settings (After Setup) -->
        <form method="post" action="options.php">
            <?php settings_fields('guilamu_bug_reporter'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="poe_api_key">
                            <?php esc_html_e('POE API Key', 'guilamu-bug-reporter'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="password" name="guilamu_bug_reporter_poe_key" id="poe_api_key" class="regular-text"
                            value="<?php echo esc_attr($poe_key); ?>">
                        <a href="https://poe.com/api_key" target="_blank" class="button">
                            <?php esc_html_e('Get API Key', 'guilamu-bug-reporter'); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="poe_model">
                            <?php esc_html_e('AI Model', 'guilamu-bug-reporter'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="guilamu_bug_reporter_poe_model" id="poe_model" class="regular-text"
                            value="<?php echo esc_attr($poe_model); ?>" readonly>
                        <p class="description">
                            <?php esc_html_e('Model is auto-selected when you update your API key.', 'guilamu-bug-reporter'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <!-- Registered Plugins -->
        <h2>
            <?php esc_html_e('Registered Plugins', 'guilamu-bug-reporter'); ?>
        </h2>
        <?php if (empty($registered)): ?>
            <p class="description">
                <?php esc_html_e('No plugins have registered with Bug Reporter yet.', 'guilamu-bug-reporter'); ?>
            </p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>
                            <?php esc_html_e('Plugin', 'guilamu-bug-reporter'); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Version', 'guilamu-bug-reporter'); ?>
                        </th>
                        <th>
                            <?php esc_html_e('GitHub Repo', 'guilamu-bug-reporter'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered as $plugin): ?>
                        <tr>
                            <td>
                                <?php echo esc_html($plugin['name']); ?>
                            </td>
                            <td>
                                <?php echo esc_html($plugin['version']); ?>
                            </td>
                            <td>
                                <a href="https://github.com/<?php echo esc_attr($plugin['github_repo']); ?>" target="_blank">
                                    <?php echo esc_html($plugin['github_repo']); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>
            <?php esc_html_e('Status', 'guilamu-bug-reporter'); ?>
        </h2>
        <table class="form-table">
            <tr>
                <th>
                    <?php esc_html_e('POE API', 'guilamu-bug-reporter'); ?>
                </th>
                <td>
                    <?php if ($poe_key): ?>
                        <span style="color: green;">✓
                            <?php esc_html_e('Configured', 'guilamu-bug-reporter'); ?>
                        </span>
                    <?php else: ?>
                        <span style="color: red;">✗
                            <?php esc_html_e('Not configured', 'guilamu-bug-reporter'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php esc_html_e('GitHub API', 'guilamu-bug-reporter'); ?>
                </th>
                <td>
                    <span style="color: green;">✓
                        <?php esc_html_e('Pre-configured', 'guilamu-bug-reporter'); ?>
                    </span>
                    <p class="description">
                        <?php esc_html_e('GitHub token is bundled with the plugin.', 'guilamu-bug-reporter'); ?>
                    </p>
                </td>
            </tr>
        </table>
    <?php endif; ?>
</div>