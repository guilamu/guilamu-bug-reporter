/**
 * Guilamu Bug Reporter JavaScript
 */
(function ($) {
    'use strict';

    var BugReporter = {
        currentStep: 0,
        totalSteps: 6,
        formData: {},
        screenshotUrl: '',

        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            // Open modal
            $(document).on('click', '.guilamu-bug-report-btn', this.openModal.bind(this));

            // Close modal
            $(document).on('click', '.guilamu-bug-reporter-close', this.closeModal.bind(this));
            $(document).on('click', '.guilamu-bug-reporter-modal', function (e) {
                if ($(e.target).hasClass('guilamu-bug-reporter-modal')) {
                    BugReporter.closeModal();
                }
            });

            // Navigation
            $(document).on('click', '.guilamu-bug-reporter-next', this.nextStep.bind(this));
            $(document).on('click', '.guilamu-bug-reporter-back', this.prevStep.bind(this));
            $(document).on('click', '.guilamu-bug-reporter-submit', this.submitReport.bind(this));

            // Privacy acknowledgment
            $(document).on('change', '#guilamu-privacy-acknowledge', this.toggleNextButton.bind(this));

            // Screenshot upload
            $(document).on('click', '.guilamu-bug-reporter-upload', this.triggerUpload.bind(this));
            $(document).on('change', '#guilamu-screenshot-input', this.handleUpload.bind(this));

            // Escape key
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    BugReporter.closeModal();
                }
            });
        },

        openModal: function (e) {
            e.preventDefault();

            // Check if setup is complete
            if (!guilamuBugReporter.isSetupComplete) {
                if (confirm(guilamuBugReporter.i18n.setupRequired)) {
                    window.location.href = guilamuBugReporter.settingsUrl;
                }
                return;
            }

            var $btn = $(e.currentTarget);

            this.formData = {
                plugin_slug: $btn.data('plugin-slug'),
                plugin_name: $btn.data('plugin-name')
            };

            this.currentStep = 0;
            this.screenshotUrl = '';
            this.resetForm();
            this.showStep(0);
            this.updateProgress();

            $('.guilamu-bug-reporter-modal').addClass('active');
            $('body').css('overflow', 'hidden');
        },

        closeModal: function () {
            $('.guilamu-bug-reporter-modal').removeClass('active');
            $('body').css('overflow', '');
        },

        resetForm: function () {
            $('.guilamu-bug-reporter-form')[0].reset();
            $('.guilamu-bug-reporter-step').removeClass('active');
            $('.guilamu-bug-reporter-loading, .guilamu-bug-reporter-success').removeClass('active');
            $('.guilamu-bug-reporter-form-container').show();
            $('.guilamu-bug-reporter-upload').removeClass('has-file');
            $('.guilamu-bug-reporter-upload-preview').remove();
            $('.guilamu-bug-reporter-next').prop('disabled', true);
        },

        showStep: function (step) {
            $('.guilamu-bug-reporter-step').removeClass('active');
            $('.guilamu-bug-reporter-step[data-step="' + step + '"]').addClass('active');

            // Update footer buttons
            var $back = $('.guilamu-bug-reporter-back');
            var $next = $('.guilamu-bug-reporter-next');
            var $submit = $('.guilamu-bug-reporter-submit');

            $back.toggle(step > 0);
            $next.toggle(step < this.totalSteps - 1);
            $submit.toggle(step === this.totalSteps - 1);

            // Step 0 (privacy) requires acknowledgment
            if (step === 0) {
                $next.prop('disabled', !$('#guilamu-privacy-acknowledge').is(':checked'));
            } else {
                $next.prop('disabled', false);
            }

            // Step 4 (screenshot) is optional
            if (step === 4) {
                $next.prop('disabled', false);
            }
        },

        updateProgress: function () {
            var percent = ((this.currentStep + 1) / this.totalSteps) * 100;
            $('.guilamu-bug-reporter-progress-fill').css('width', percent + '%');
            $('.guilamu-bug-reporter-progress-text').text(
                guilamuBugReporter.i18n['step_' + (this.currentStep + 1) + '_title'] ||
                'Step ' + (this.currentStep + 1) + ' of ' + this.totalSteps
            );
        },

        nextStep: function () {
            if (!this.validateCurrentStep()) {
                return;
            }

            this.saveCurrentStepData();
            this.currentStep++;
            this.showStep(this.currentStep);
            this.updateProgress();
        },

        prevStep: function () {
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateProgress();
        },

        validateCurrentStep: function () {
            var $step = $('.guilamu-bug-reporter-step[data-step="' + this.currentStep + '"]');
            var valid = true;

            // Check required fields
            $step.find('[required]').each(function () {
                if (!$(this).val().trim()) {
                    $(this).addClass('error');
                    valid = false;
                } else {
                    $(this).removeClass('error');
                }
            });

            // Step 0: Privacy acknowledgment
            if (this.currentStep === 0 && !$('#guilamu-privacy-acknowledge').is(':checked')) {
                valid = false;
            }

            return valid;
        },

        saveCurrentStepData: function () {
            var $step = $('.guilamu-bug-reporter-step[data-step="' + this.currentStep + '"]');

            $step.find('input, textarea, select').each(function () {
                var $field = $(this);
                var name = $field.attr('name');

                if (name) {
                    if ($field.attr('type') === 'radio') {
                        if ($field.is(':checked')) {
                            BugReporter.formData[name] = $field.val();
                        }
                    } else {
                        BugReporter.formData[name] = $field.val();
                    }
                }
            });
        },

        toggleNextButton: function () {
            var checked = $('#guilamu-privacy-acknowledge').is(':checked');
            $('.guilamu-bug-reporter-next').prop('disabled', !checked);
        },

        triggerUpload: function (e) {
            e.preventDefault();
            $('#guilamu-screenshot-input').trigger('click');
        },

        handleUpload: function (e) {
            var file = e.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
                alert(guilamuBugReporter.i18n.error);
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Maximum size is 5MB.');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'guilamu_upload_screenshot');
            formData.append('nonce', guilamuBugReporter.nonce);
            formData.append('screenshot', file);

            var $upload = $('.guilamu-bug-reporter-upload');
            $upload.addClass('uploading');

            $.ajax({
                url: guilamuBugReporter.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $upload.removeClass('uploading');
                    if (response.success) {
                        BugReporter.screenshotUrl = response.data.url;
                        $upload.addClass('has-file');
                        $upload.find('.guilamu-bug-reporter-upload-preview').remove();
                        $upload.append('<img src="' + response.data.url + '" class="guilamu-bug-reporter-upload-preview">');
                    } else {
                        alert(response.data || guilamuBugReporter.i18n.error);
                    }
                },
                error: function () {
                    $upload.removeClass('uploading');
                    alert(guilamuBugReporter.i18n.error);
                }
            });
        },

        submitReport: function () {
            this.saveCurrentStepData();

            // Show loading
            $('.guilamu-bug-reporter-form-container').hide();
            $('.guilamu-bug-reporter-footer').hide();
            $('.guilamu-bug-reporter-loading').addClass('active');

            var data = $.extend({}, this.formData, {
                action: 'guilamu_submit_bug_report',
                nonce: guilamuBugReporter.nonce,
                screenshot_url: this.screenshotUrl
            });

            $.post(guilamuBugReporter.ajaxUrl, data, function (response) {
                $('.guilamu-bug-reporter-loading').removeClass('active');

                if (response.success) {
                    BugReporter.showSuccess(response.data);
                } else {
                    alert(response.data || guilamuBugReporter.i18n.error);
                    $('.guilamu-bug-reporter-form-container').show();
                    $('.guilamu-bug-reporter-footer').show();
                }
            }).fail(function () {
                $('.guilamu-bug-reporter-loading').removeClass('active');
                alert(guilamuBugReporter.i18n.error);
                $('.guilamu-bug-reporter-form-container').show();
                $('.guilamu-bug-reporter-footer').show();
            });
        },

        showSuccess: function (data) {
            var $success = $('.guilamu-bug-reporter-success');

            // Add AI response if available
            if (data.ai_response) {
                $success.find('.guilamu-bug-reporter-ai-response p').text(data.ai_response);
                $success.find('.guilamu-bug-reporter-ai-response').show();
            } else {
                $success.find('.guilamu-bug-reporter-ai-response').hide();
            }

            // Set issue link
            if (data.issue_url) {
                $success.find('.guilamu-bug-reporter-issue-link a').attr('href', data.issue_url);
            }

            $success.addClass('active');
            $('.guilamu-bug-reporter-footer').show();
            $('.guilamu-bug-reporter-footer .button').hide();
            $('<button type="button" class="button button-primary guilamu-bug-reporter-close">' +
                guilamuBugReporter.i18n.close + '</button>').appendTo('.guilamu-bug-reporter-footer');
        }
    };

    $(document).ready(function () {
        BugReporter.init();
    });

})(jQuery);
