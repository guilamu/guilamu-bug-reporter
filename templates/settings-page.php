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
$ai_enabled = Guilamu_Bug_Reporter_Settings::is_ai_enabled();
$ai_provider = Guilamu_Bug_Reporter_Settings::get_ai_provider();
$poe_key = Guilamu_Bug_Reporter_Settings::get_poe_key();
$poe_model = Guilamu_Bug_Reporter_Settings::get_poe_model();
$gemini_key = Guilamu_Bug_Reporter_Settings::get_gemini_key();
$gemini_model = Guilamu_Bug_Reporter_Settings::get_gemini_model();
$registered = Guilamu_Bug_Reporter::get_registered_plugins();
$gemini_models = Guilamu_Bug_Reporter_Gemini_API::get_models();
?>

<div class="wrap guilamu-bug-reporter-settings-wrap">
    <h1>
        <?php esc_html_e('Guilamu Bug Reporter', 'guilamu-bug-reporter'); ?>
    </h1>

    <?php if (!$is_setup_complete): ?>
        <!-- Setup Wizard -->
        <div class="guilamu-bug-reporter-setup-wizard">
            <h2>🐛
                <?php esc_html_e('Welcome to Guilamu Bug Reporter!', 'guilamu-bug-reporter'); ?>
            </h2>
            <p class="description">
                <?php esc_html_e('Bug reports will be submitted as GitHub Issues. You can optionally enable AI-powered instant responses.', 'guilamu-bug-reporter'); ?>
            </p>

            <h3>🤖
                <?php esc_html_e('AI-Powered Responses (Optional)', 'guilamu-bug-reporter'); ?>
            </h3>
            <p class="description">
                <?php esc_html_e('Enable AI to provide instant troubleshooting suggestions when users submit bug reports. Choose between POE or Google Gemini.', 'guilamu-bug-reporter'); ?>
            </p>

            <!-- Provider choice -->
            <div id="guilamu-wizard-provider-choice">
                <h4><?php esc_html_e('Choose an AI Provider', 'guilamu-bug-reporter'); ?></h4>

                <div class="guilamu-provider-cards">
                    <label class="guilamu-provider-card">
                        <input type="radio" name="wizard_provider" value="poe">
                        <div class="guilamu-provider-card-inner">
                            <strong>POE</strong>
                            <span><?php esc_html_e('Free tier available. Supports Claude, GPT, and more.', 'guilamu-bug-reporter'); ?></span>
                        </div>
                    </label>
                    <label class="guilamu-provider-card">
                        <input type="radio" name="wizard_provider" value="gemini">
                        <div class="guilamu-provider-card-inner">
                            <strong>Google Gemini</strong>
                            <span><?php esc_html_e('Free tier with generous limits. Fast and capable.', 'guilamu-bug-reporter'); ?></span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- POE Setup -->
            <div id="guilamu-wizard-poe" style="display: none;">
                <div class="highlight">
                    <strong>⏱️
                        <?php esc_html_e('This takes less than 1 minute and is completely FREE!', 'guilamu-bug-reporter'); ?>
                    </strong>
                </div>

                <ol>
                    <li><?php esc_html_e('Go to poe.com and create a free account', 'guilamu-bug-reporter'); ?></li>
                    <li><?php esc_html_e('Navigate to: Settings → API', 'guilamu-bug-reporter'); ?></li>
                    <li><?php esc_html_e('Click "Generate API key"', 'guilamu-bug-reporter'); ?></li>
                    <li><?php esc_html_e('Copy and paste it below', 'guilamu-bug-reporter'); ?></li>
                </ol>

                <div class="guilamu-bug-reporter-setup-links">
                    <a href="https://poe.com/login" target="_blank" class="button">
                        <?php esc_html_e('Create Free POE Account →', 'guilamu-bug-reporter'); ?>
                    </a>
                    <a href="https://poe.com/api_key" target="_blank" class="button button-primary">
                        <?php esc_html_e('Get Your API Key →', 'guilamu-bug-reporter'); ?>
                    </a>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wizard_poe_api_key"><?php esc_html_e('POE API Key', 'guilamu-bug-reporter'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="wizard_poe_api_key" class="regular-text" value=""
                                autocomplete="new-password"
                                placeholder="<?php esc_attr_e('Paste your API key here', 'guilamu-bug-reporter'); ?>">
                            <span id="poe_key_status"></span>
                        </td>
                    </tr>
                    <tr id="poe_model_row" style="display: none;">
                        <th scope="row">
                            <label for="wizard_poe_model"><?php esc_html_e('AI Model', 'guilamu-bug-reporter'); ?></label>
                        </th>
                        <td>
                            <select id="wizard_poe_model" class="regular-text">
                                <option value=""><?php esc_html_e('Enter API key first...', 'guilamu-bug-reporter'); ?></option>
                            </select>
                            <span id="poe_model_loading" style="display: none;">
                                <span class="spinner is-active"></span>
                            </span>
                            <p class="description"><?php esc_html_e('Auto-detected: Latest Claude-Sonnet', 'guilamu-bug-reporter'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Gemini Setup -->
            <div id="guilamu-wizard-gemini" style="display: none;">
                <div class="highlight">
                    <strong>⏱️
                        <?php esc_html_e('Google Gemini offers a generous free tier — no credit card required!', 'guilamu-bug-reporter'); ?>
                    </strong>
                </div>

                <ol>
                    <li><?php esc_html_e('Go to Google AI Studio', 'guilamu-bug-reporter'); ?></li>
                    <li><?php esc_html_e('Sign in with your Google account', 'guilamu-bug-reporter'); ?></li>
                    <li><?php esc_html_e('Click "Get API key" → "Create API key"', 'guilamu-bug-reporter'); ?></li>
                    <li><?php esc_html_e('Copy and paste it below', 'guilamu-bug-reporter'); ?></li>
                </ol>

                <div class="guilamu-bug-reporter-setup-links">
                    <a href="https://aistudio.google.com/apikey" target="_blank" class="button button-primary">
                        <?php esc_html_e('Get Your Gemini API Key →', 'guilamu-bug-reporter'); ?>
                    </a>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wizard_gemini_api_key"><?php esc_html_e('Gemini API Key', 'guilamu-bug-reporter'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="wizard_gemini_api_key" class="regular-text" value=""
                                autocomplete="new-password"
                                placeholder="<?php esc_attr_e('Paste your API key here', 'guilamu-bug-reporter'); ?>">
                            <span id="gemini_key_status"></span>
                        </td>
                    </tr>
                    <tr id="gemini_model_row" style="display: none;">
                        <th scope="row">
                            <label for="wizard_gemini_model"><?php esc_html_e('AI Model', 'guilamu-bug-reporter'); ?></label>
                        </th>
                        <td>
                            <select id="wizard_gemini_model" class="regular-text">
                                <?php foreach ($gemini_models as $gmodel): ?>
                                    <option value="<?php echo esc_attr($gmodel['id']); ?>"
                                        <?php selected($gmodel['id'], 'gemini-2.0-flash'); ?>>
                                        <?php echo esc_html($gmodel['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Gemini 2.0 Flash is recommended for most use cases.', 'guilamu-bug-reporter'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="submit" style="display: flex; gap: 12px; align-items: center;">
                <button type="button" class="button button-primary button-hero" id="complete_setup" disabled>
                    <?php esc_html_e('Complete Setup with AI →', 'guilamu-bug-reporter'); ?>
                </button>
                <button type="button" class="button button-hero" id="skip_ai_setup">
                    <?php esc_html_e('Skip AI Setup →', 'guilamu-bug-reporter'); ?>
                </button>
            </p>
            <p class="description">
                <?php esc_html_e('You can always enable or change AI settings later from this page.', 'guilamu-bug-reporter'); ?>
            </p>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                var debounceTimer;
                var selectedProvider = '';

                // Provider selection
                $('input[name="wizard_provider"]').on('change', function () {
                    selectedProvider = $(this).val();
                    $('#guilamu-wizard-poe, #guilamu-wizard-gemini').hide();
                    $('#complete_setup').prop('disabled', true);

                    if (selectedProvider === 'poe') {
                        $('#guilamu-wizard-poe').show();
                        // Re-check if POE key is already validated
                        if ($('#poe_model_row').is(':visible') && $('#wizard_poe_model').val()) {
                            $('#complete_setup').prop('disabled', false);
                        }
                    } else if (selectedProvider === 'gemini') {
                        $('#guilamu-wizard-gemini').show();
                        // Re-check if Gemini key is already validated
                        if ($('#gemini_model_row').is(':visible')) {
                            $('#complete_setup').prop('disabled', false);
                        }
                    }
                });

                // POE API key input
                $('#wizard_poe_api_key').on('input', function () {
                    var apiKey = $(this).val().trim();
                    clearTimeout(debounceTimer);

                    if (apiKey.length < 10) {
                        $('#poe_model_row').hide();
                        $('#complete_setup').prop('disabled', true);
                        return;
                    }

                    debounceTimer = setTimeout(function () {
                        $('#poe_model_loading').show();
                        $('#poe_key_status').text('');

                        $.post(ajaxurl, {
                            action: 'guilamu_fetch_poe_models',
                            nonce: '<?php echo esc_js(wp_create_nonce('guilamu_bug_reporter')); ?>',
                            api_key: apiKey
                        }, function (response) {
                            $('#poe_model_loading').hide();

                            if (response.success) {
                                $('#poe_key_status').html('<span style="color:green;">✓</span>');
                                var $select = $('#wizard_poe_model').empty();
                                response.data.models.forEach(function (model) {
                                    $select.append($('<option>', {
                                        value: model.id,
                                        text: model.name,
                                        selected: model.id === response.data.default_model
                                    }));
                                });
                                $('#poe_model_row').show();
                                $('#complete_setup').prop('disabled', false);
                            } else {
                                $('#poe_key_status').html('<span style="color:red;">✗ ' + response.data + '</span>');
                                $('#poe_model_row').hide();
                                $('#complete_setup').prop('disabled', true);
                            }
                        });
                    }, 500);
                });

                // Gemini API key input
                $('#wizard_gemini_api_key').on('input', function () {
                    var apiKey = $(this).val().trim();
                    clearTimeout(debounceTimer);

                    if (apiKey.length < 10) {
                        $('#gemini_model_row').hide();
                        $('#complete_setup').prop('disabled', true);
                        return;
                    }

                    debounceTimer = setTimeout(function () {
                        $('#gemini_key_status').html('<span class="spinner is-active" style="float:none;"></span>');

                        $.post(ajaxurl, {
                            action: 'guilamu_validate_gemini_key',
                            nonce: '<?php echo esc_js(wp_create_nonce('guilamu_bug_reporter')); ?>',
                            api_key: apiKey
                        }, function (response) {
                            if (response.success) {
                                $('#gemini_key_status').html('<span style="color:green;">✓</span>');
                                $('#gemini_model_row').show();
                                $('#complete_setup').prop('disabled', false);
                            } else {
                                $('#gemini_key_status').html('<span style="color:red;">✗ ' + response.data + '</span>');
                                $('#gemini_model_row').hide();
                                $('#complete_setup').prop('disabled', true);
                            }
                        });
                    }, 500);
                });

                // Complete setup with AI
                $('#complete_setup').on('click', function () {
                    var $btn = $(this);
                    $btn.prop('disabled', true).text('<?php echo esc_js(__('Saving...', 'guilamu-bug-reporter')); ?>');

                    var data = {
                        action: 'guilamu_save_ai_settings',
                        nonce: '<?php echo esc_js(wp_create_nonce('guilamu_bug_reporter')); ?>',
                        ai_enabled: '1',
                        ai_provider: selectedProvider
                    };

                    if (selectedProvider === 'poe') {
                        data.poe_api_key = $('#wizard_poe_api_key').val();
                        data.poe_model = $('#wizard_poe_model').val();
                    } else if (selectedProvider === 'gemini') {
                        data.gemini_api_key = $('#wizard_gemini_api_key').val();
                        data.gemini_model = $('#wizard_gemini_model').val();
                    }

                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data);
                            $btn.prop('disabled', false).text('<?php echo esc_js(__('Complete Setup with AI →', 'guilamu-bug-reporter')); ?>');
                        }
                    });
                });

                // Skip AI setup
                $('#skip_ai_setup').on('click', function () {
                    var $btn = $(this);
                    $btn.prop('disabled', true).text('<?php echo esc_js(__('Saving...', 'guilamu-bug-reporter')); ?>');

                    $.post(ajaxurl, {
                        action: 'guilamu_skip_ai_setup',
                        nonce: '<?php echo esc_js(wp_create_nonce('guilamu_bug_reporter')); ?>'
                    }, function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data);
                            $btn.prop('disabled', false).text('<?php echo esc_js(__('Skip AI Setup →', 'guilamu-bug-reporter')); ?>');
                        }
                    });
                });
            });
        </script>

    <?php else: ?>
        <!-- Settings (After Setup) -->
        <form method="post" action="options.php">
            <?php settings_fields('guilamu_bug_reporter'); ?>

            <h2><?php esc_html_e('AI Settings', 'guilamu-bug-reporter'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('AI Responses', 'guilamu-bug-reporter'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="guilamu_bug_reporter_ai_enabled" value="1"
                                <?php checked($ai_enabled); ?>
                                id="guilamu_ai_enabled_toggle">
                            <?php esc_html_e('Enable AI-powered instant responses to bug reports', 'guilamu-bug-reporter'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <div id="guilamu-ai-settings" style="<?php echo $ai_enabled ? '' : 'display:none;'; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('AI Provider', 'guilamu-bug-reporter'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label style="margin-right: 20px;">
                                    <input type="radio" name="guilamu_bug_reporter_ai_provider" value="poe"
                                        <?php checked($ai_provider, 'poe'); ?>
                                        class="guilamu-provider-radio">
                                    POE
                                </label>
                                <label>
                                    <input type="radio" name="guilamu_bug_reporter_ai_provider" value="gemini"
                                        <?php checked($ai_provider, 'gemini'); ?>
                                        class="guilamu-provider-radio">
                                    Google Gemini
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <!-- POE Settings -->
                <div id="guilamu-poe-settings" style="<?php echo 'poe' === $ai_provider ? '' : 'display:none;'; ?>">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="poe_api_key"><?php esc_html_e('POE API Key', 'guilamu-bug-reporter'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="guilamu_bug_reporter_poe_key" id="poe_api_key"
                                    class="regular-text" value="<?php echo esc_attr($poe_key); ?>">
                                <a href="https://poe.com/api_key" target="_blank" class="button">
                                    <?php esc_html_e('Get API Key', 'guilamu-bug-reporter'); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="poe_model"><?php esc_html_e('AI Model', 'guilamu-bug-reporter'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="guilamu_bug_reporter_poe_model" id="poe_model"
                                    class="regular-text" value="<?php echo esc_attr($poe_model); ?>" readonly>
                                <p class="description">
                                    <?php esc_html_e('Model is auto-selected when you update your API key.', 'guilamu-bug-reporter'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Gemini Settings -->
                <div id="guilamu-gemini-settings" style="<?php echo 'gemini' === $ai_provider ? '' : 'display:none;'; ?>">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="gemini_api_key"><?php esc_html_e('Gemini API Key', 'guilamu-bug-reporter'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="guilamu_bug_reporter_gemini_key" id="gemini_api_key"
                                    class="regular-text" value="<?php echo esc_attr($gemini_key); ?>">
                                <a href="https://aistudio.google.com/apikey" target="_blank" class="button">
                                    <?php esc_html_e('Get API Key', 'guilamu-bug-reporter'); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="gemini_model"><?php esc_html_e('AI Model', 'guilamu-bug-reporter'); ?></label>
                            </th>
                            <td>
                                <select name="guilamu_bug_reporter_gemini_model" id="gemini_model" class="regular-text">
                                    <?php foreach ($gemini_models as $gmodel): ?>
                                        <option value="<?php echo esc_attr($gmodel['id']); ?>"
                                            <?php selected($gemini_model, $gmodel['id']); ?>>
                                            <?php echo esc_html($gmodel['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Gemini 2.0 Flash is recommended for most use cases.', 'guilamu-bug-reporter'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>

        <script>
            jQuery(document).ready(function ($) {
                // Toggle AI settings visibility
                $('#guilamu_ai_enabled_toggle').on('change', function () {
                    $('#guilamu-ai-settings').toggle($(this).is(':checked'));
                });

                // Toggle provider-specific settings
                $('.guilamu-provider-radio').on('change', function () {
                    var provider = $(this).val();
                    $('#guilamu-poe-settings, #guilamu-gemini-settings').hide();
                    if (provider === 'poe') {
                        $('#guilamu-poe-settings').show();
                    } else if (provider === 'gemini') {
                        $('#guilamu-gemini-settings').show();
                    }
                });
            });
        </script>

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
                        <th><?php esc_html_e('Plugin', 'guilamu-bug-reporter'); ?></th>
                        <th><?php esc_html_e('Version', 'guilamu-bug-reporter'); ?></th>
                        <th><?php esc_html_e('GitHub Repo', 'guilamu-bug-reporter'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered as $plugin): ?>
                        <tr>
                            <td><?php echo esc_html($plugin['name']); ?></td>
                            <td><?php echo esc_html($plugin['version']); ?></td>
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
                <th><?php esc_html_e('AI Responses', 'guilamu-bug-reporter'); ?></th>
                <td>
                    <?php if ($ai_enabled): ?>
                        <span style="color: green;">✓
                            <?php
                            printf(
                                /* translators: %s: provider name */
                                esc_html__('Enabled (%s)', 'guilamu-bug-reporter'),
                                'poe' === $ai_provider ? 'POE' : 'Google Gemini'
                            );
                            ?>
                        </span>
                    <?php else: ?>
                        <span style="color: #646970;">—
                            <?php esc_html_e('Disabled', 'guilamu-bug-reporter'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($ai_enabled && 'poe' === $ai_provider): ?>
            <tr>
                <th><?php esc_html_e('POE API', 'guilamu-bug-reporter'); ?></th>
                <td>
                    <?php if ($poe_key): ?>
                        <span style="color: green;">✓ <?php esc_html_e('Configured', 'guilamu-bug-reporter'); ?></span>
                    <?php else: ?>
                        <span style="color: red;">✗ <?php esc_html_e('Not configured', 'guilamu-bug-reporter'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($ai_enabled && 'gemini' === $ai_provider): ?>
            <tr>
                <th><?php esc_html_e('Gemini API', 'guilamu-bug-reporter'); ?></th>
                <td>
                    <?php if ($gemini_key): ?>
                        <span style="color: green;">✓ <?php esc_html_e('Configured', 'guilamu-bug-reporter'); ?></span>
                    <?php else: ?>
                        <span style="color: red;">✗ <?php esc_html_e('Not configured', 'guilamu-bug-reporter'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php esc_html_e('GitHub API', 'guilamu-bug-reporter'); ?></th>
                <td>
                    <span style="color: green;">✓ <?php esc_html_e('Pre-configured', 'guilamu-bug-reporter'); ?></span>
                    <p class="description">
                        <?php esc_html_e('GitHub token is bundled with the plugin.', 'guilamu-bug-reporter'); ?>
                    </p>
                </td>
            </tr>
        </table>
    <?php endif; ?>
</div>