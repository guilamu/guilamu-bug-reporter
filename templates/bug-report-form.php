<?php
/**
 * Bug Report Form Template
 *
 * @package Guilamu_Bug_Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Bug Report Modal -->
<div class="guilamu-bug-reporter-modal">
    <div class="guilamu-bug-reporter-modal-content">
        <div class="guilamu-bug-reporter-header">
            <h2>
                <?php esc_html_e('🐛 Report a Bug', 'guilamu-bug-reporter'); ?>
            </h2>
            <button type="button" class="guilamu-bug-reporter-close"
                aria-label="<?php esc_attr_e('Close', 'guilamu-bug-reporter'); ?>">×</button>
        </div>

        <div class="guilamu-bug-reporter-progress">
            <div class="guilamu-bug-reporter-progress-bar">
                <div class="guilamu-bug-reporter-progress-fill"></div>
            </div>
            <div class="guilamu-bug-reporter-progress-text"></div>
        </div>

        <div class="guilamu-bug-reporter-body">
            <!-- Loading State -->
            <div class="guilamu-bug-reporter-loading">
                <div class="guilamu-bug-reporter-spinner"></div>
                <p>
                    <?php esc_html_e('Submitting your report...', 'guilamu-bug-reporter'); ?>
                </p>
            </div>

            <!-- Success State -->
            <div class="guilamu-bug-reporter-success">



                <div class="guilamu-bug-reporter-ai-response" style="display: none;">
                    <h4>🤖
                        <?php esc_html_e('AI Response', 'guilamu-bug-reporter'); ?>
                    </h4>
                    <p></p>
                </div>


            </div>

            <!-- Form -->
            <div class="guilamu-bug-reporter-form-container">
                <form class="guilamu-bug-reporter-form">
                    <!-- Step 0: Privacy -->
                    <div class="guilamu-bug-reporter-step active" data-step="0">
                        <h3>
                            <?php esc_html_e('Privacy & Data Disclosure', 'guilamu-bug-reporter'); ?>
                        </h3>

                        <div class="guilamu-bug-reporter-privacy">
                            <h4>
                                <?php esc_html_e('What we collect:', 'guilamu-bug-reporter'); ?>
                            </h4>
                            <ul>
                                <li>
                                    <?php esc_html_e('Your answers to the questions below', 'guilamu-bug-reporter'); ?>
                                </li>
                                <li>
                                    <?php esc_html_e('System information (WordPress version, PHP version, active plugins, etc.)', 'guilamu-bug-reporter'); ?>
                                </li>
                                <li>
                                    <?php esc_html_e('If you upload a screenshot, it will be stored in your WordPress Media Library', 'guilamu-bug-reporter'); ?>
                                </li>
                            </ul>

                            <h4>
                                <?php esc_html_e('Where it goes:', 'guilamu-bug-reporter'); ?>
                            </h4>
                            <ul>
                                <li>
                                    <?php esc_html_e('A public GitHub Issue will be created in the plugin\'s repository', 'guilamu-bug-reporter'); ?>
                                </li>
                                <li>
                                    <?php esc_html_e('An AI will analyze your report and provide immediate suggestions', 'guilamu-bug-reporter'); ?>
                                </li>
                                <li>
                                    <?php esc_html_e('Your email will NOT be published (stored privately for follow-up only)', 'guilamu-bug-reporter'); ?>
                                </li>
                            </ul>
                        </div>

                        <div class="guilamu-bug-reporter-acknowledge">
                            <input type="checkbox" id="guilamu-privacy-acknowledge">
                            <label for="guilamu-privacy-acknowledge">
                                <?php esc_html_e('I understand and wish to proceed', 'guilamu-bug-reporter'); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Step 1: Description -->
                    <div class="guilamu-bug-reporter-step" data-step="1">
                        <h3>
                            <?php esc_html_e('What happened vs. what you expected?', 'guilamu-bug-reporter'); ?>
                        </h3>
                        <p>
                            <?php esc_html_e('Describe the issue you encountered and what you expected to happen instead.', 'guilamu-bug-reporter'); ?>
                        </p>

                        <div class="guilamu-bug-reporter-field">
                            <textarea name="description" rows="5" required
                                placeholder="<?php esc_attr_e('Example: When I try to save the form, the page refreshes but the changes are not saved. I expected my changes to be saved and see a success message.', 'guilamu-bug-reporter'); ?>"></textarea>
                        </div>
                    </div>

                    <!-- Step 2: Steps to Reproduce -->
                    <div class="guilamu-bug-reporter-step" data-step="2">
                        <h3>
                            <?php esc_html_e('Steps to Reproduce', 'guilamu-bug-reporter'); ?>
                        </h3>
                        <p>
                            <?php esc_html_e('List the exact steps to recreate this issue.', 'guilamu-bug-reporter'); ?>
                        </p>

                        <div class="guilamu-bug-reporter-field">
                            <textarea name="steps" rows="5" required
                                placeholder="<?php esc_attr_e("1. Go to Form Settings\n2. Change the field label\n3. Click 'Save'\n4. Page refreshes but changes are lost", 'guilamu-bug-reporter'); ?>"></textarea>
                        </div>
                    </div>

                    <!-- Step 3: Severity -->
                    <div class="guilamu-bug-reporter-step" data-step="3">
                        <h3>
                            <?php esc_html_e('How severe is this issue?', 'guilamu-bug-reporter'); ?>
                        </h3>
                        <p>
                            <?php esc_html_e('Help us prioritize by indicating how this affects your work.', 'guilamu-bug-reporter'); ?>
                        </p>

                        <div class="guilamu-bug-reporter-severity">
                            <label>
                                <input type="radio" name="severity" value="blocking" required>
                                <span>🔴
                                    <?php esc_html_e('Blocking', 'guilamu-bug-reporter'); ?>
                                </span>
                            </label>
                            <label>
                                <input type="radio" name="severity" value="annoying">
                                <span>🟡
                                    <?php esc_html_e('Annoying', 'guilamu-bug-reporter'); ?>
                                </span>
                            </label>
                            <label>
                                <input type="radio" name="severity" value="minor">
                                <span>🟢
                                    <?php esc_html_e('Minor', 'guilamu-bug-reporter'); ?>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 4: Screenshot -->
                    <div class="guilamu-bug-reporter-step" data-step="4">
                        <h3>
                            <?php esc_html_e('Screenshot (Optional)', 'guilamu-bug-reporter'); ?>
                        </h3>
                        <p>
                            <?php esc_html_e('A screenshot can help us understand the issue better.', 'guilamu-bug-reporter'); ?>
                        </p>

                        <div class="guilamu-bug-reporter-upload">
                            <p>📷
                                <?php esc_html_e('Click to upload a screenshot', 'guilamu-bug-reporter'); ?>
                            </p>
                            <p class="description">
                                <?php esc_html_e('Max 5MB. JPG, PNG, GIF, or WebP.', 'guilamu-bug-reporter'); ?>
                            </p>
                        </div>
                        <input type="file" id="guilamu-screenshot-input" accept="image/*" style="display: none;">
                    </div>

                    <!-- Step 5: Email -->
                    <div class="guilamu-bug-reporter-step" data-step="5">
                        <h3>
                            <?php esc_html_e('Contact Email', 'guilamu-bug-reporter'); ?>
                        </h3>
                        <p>
                            <?php esc_html_e('We\'ll only use this to follow up if needed. It will NOT appear publicly.', 'guilamu-bug-reporter'); ?>
                        </p>

                        <div class="guilamu-bug-reporter-field">
                            <input type="email" name="email" required
                                value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>"
                                placeholder="<?php esc_attr_e('your@email.com', 'guilamu-bug-reporter'); ?>">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="guilamu-bug-reporter-footer">
            <div class="guilamu-bug-reporter-footer-left">
                <button type="button" class="button guilamu-bug-reporter-back" style="display: none;">
                    <?php esc_html_e('← Back', 'guilamu-bug-reporter'); ?>
                </button>
            </div>
            <div class="guilamu-bug-reporter-footer-right">
                <a href="#" target="_blank" class="button button-primary guilamu-bug-reporter-issue-link"
                    style="display: none;">
                    <?php esc_html_e('View Issue on GitHub', 'guilamu-bug-reporter'); ?>
                </a>
                <button type="button" class="button button-primary guilamu-bug-reporter-next" disabled>
                    <?php esc_html_e('Continue →', 'guilamu-bug-reporter'); ?>
                </button>
                <button type="button" class="button button-primary guilamu-bug-reporter-submit" style="display: none;">
                    <?php esc_html_e('Submit Report', 'guilamu-bug-reporter'); ?>
                </button>
            </div>
        </div>
    </div>
</div>