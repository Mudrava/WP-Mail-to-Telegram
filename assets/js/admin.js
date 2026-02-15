/**
 * WP Mail to Telegram - Admin JavaScript
 */

(function ($) {
    'use strict';

    // ==========================================================================
    // Initialization
    // ==========================================================================
    $(document).ready(function () {
        WPMTT.init();
    });

    var WPMTT = window.WPMTT || {};

    WPMTT.init = function () {
        this.setupWizard.init();
        this.emailLog.init();
        this.settings.init();
        this.docs.init();
    };

    // ==========================================================================
    // Setup Wizard (2-step: Verification Code → Complete)
    // ==========================================================================
    WPMTT.setupWizard = {
        currentStep: 1,

        init: function () {
            var self = this;

            // Verification code input — validate on input
            $('#wpmtt-verification-code').on('input', function () {
                var val = $(this).val().replace(/\D/g, ''); // strip non-digits
                $(this).val(val);
                self.validateCode(val);
            });

            // Verify button
            $('.wpmtt-verify-btn').on('click', function (e) {
                e.preventDefault();
                self.verifyCode();
            });

            // Allow Enter key to submit
            $('#wpmtt-verification-code').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.verifyCode();
                }
            });

            // Skip button
            $('.wpmtt-skip-btn').on('click', function (e) {
                e.preventDefault();
                self.skipSetup();
            });
        },

        validateCode: function (value) {
            var $input = $('#wpmtt-verification-code');
            var $status = $input.siblings('.wpmtt-input-status');
            var $btn = $('.wpmtt-verify-btn');

            $input.removeClass('valid invalid');
            $status.removeClass('success error').text('');

            if (!value) {
                $btn.prop('disabled', true);
                return;
            }

            if (/^\d{6}$/.test(value)) {
                $input.addClass('valid');
                $status.addClass('success').text('OK');
                $btn.prop('disabled', false);
            } else if (value.length < 6) {
                // Still typing, don't mark as invalid yet
                $btn.prop('disabled', true);
            } else {
                $input.addClass('invalid');
                $status.addClass('error').text(wpmtt.strings.code_format || '6 digits required');
                $btn.prop('disabled', true);
            }
        },

        showStep: function (step) {
            this.currentStep = step;

            // Hide all steps
            $('.wpmtt-setup-step').removeClass('active');

            // Show current step
            $('.wpmtt-setup-step[data-step="' + step + '"]').addClass('active');

            // Update progress
            $('.wpmtt-progress-step').each(function () {
                var stepNum = $(this).data('step');
                $(this).toggleClass('active', stepNum <= step);
            });

            $('.wpmtt-progress-line').each(function (index) {
                $(this).toggleClass('active', index < step - 1);
            });
        },

        verifyCode: function () {
            var self = this;
            var $input = $('#wpmtt-verification-code');
            var $status = $input.siblings('.wpmtt-input-status');
            var $btn = $('.wpmtt-verify-btn');
            var code = $input.val().trim();

            if (!code) {
                $status.addClass('error').text(wpmtt.strings.enter_code || 'Please enter the verification code');
                return;
            }

            if (!/^\d{6}$/.test(code)) {
                $status.addClass('error').text(wpmtt.strings.code_format || '6 digits required');
                return;
            }

            // Disable button and show loading
            $btn.prop('disabled', true).text(wpmtt.strings.connecting || 'Connecting...');
            $status.removeClass('success error').text('');

            $.ajax({
                url: wpmtt.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpmtt_verify_code',
                    nonce: wpmtt.nonce,
                    code: code
                },
                success: function (response) {
                    if (response.success) {
                        $status.addClass('success').text(response.data.message);
                        // Go to step 2 (Complete)
                        setTimeout(function () {
                            self.showStep(2);
                        }, 1000);
                    } else {
                        $status.addClass('error').text(response.data.message);
                        $btn.prop('disabled', false).text(wpmtt.strings.connect || 'Connect');
                    }
                },
                error: function () {
                    $status.addClass('error').text(wpmtt.strings.error || 'Connection error');
                    $btn.prop('disabled', false).text(wpmtt.strings.connect || 'Connect');
                }
            });
        },

        skipSetup: function () {
            $.ajax({
                url: wpmtt.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpmtt_skip_setup',
                    nonce: wpmtt.nonce
                },
                success: function (response) {
                    if (response.success && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }
                }
            });
        }
    };

    // ==========================================================================
    // Email Log
    // ==========================================================================
    WPMTT.emailLog = {
        init: function () {
            // Select all checkbox
            $('#wpmtt-select-all').on('change', function () {
                var isChecked = $(this).prop('checked');
                $('input[name="email_ids[]"]').prop('checked', isChecked);
            });

            // Delete confirmation
            $('.wpmtt-delete-btn').on('click', function (e) {
                if (!confirm(wpmtt.strings.confirm_delete || 'Delete this email?')) {
                    e.preventDefault();
                }
            });
        }
    };

    // ==========================================================================
    // Settings
    // ==========================================================================
    WPMTT.settings = {
        init: function () {
            var self = this;

            // Test connection button
            $('#wpmtt-test-connection').on('click', function (e) {
                e.preventDefault();
                self.testConnection($(this));
            });

            // Send test email
            $('#wpmtt-send-test-email').on('click', function (e) {
                e.preventDefault();
                self.sendTestEmail($(this));
            });

            // Clear logs
            $('#wpmtt-clear-logs').on('click', function (e) {
                e.preventDefault();
                if (confirm(wpmtt.strings.confirm_clear || 'Are you sure you want to clear all logs?')) {
                    self.clearLogs($(this));
                }
            });

            // Reset settings
            $('#wpmtt-reset-settings').on('click', function (e) {
                e.preventDefault();
                if (confirm(wpmtt.strings.confirm_reset || 'Are you sure you want to reset all settings?')) {
                    self.resetSettings($(this));
                }
            });
        },

        testConnection: function ($btn) {
            var $result = $('#wpmtt-test-result');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> ' + (wpmtt.strings.sending || 'Sending...'));
            $result.removeClass('wpmtt-status-success wpmtt-status-error').html('');

            $.ajax({
                url: wpmtt.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpmtt_test_connection',
                    nonce: wpmtt.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $result.addClass('wpmtt-status-success').html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message);
                    } else {
                        $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + response.data.message);
                    }
                },
                error: function () {
                    $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + (wpmtt.strings.error || 'Error'));
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        sendTestEmail: function ($btn) {
            var $result = $('#wpmtt-test-email-result');
            var $emailTo = $('#wpmtt-test-email-to');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> ' + (wpmtt.strings.sending || 'Sending...'));
            $result.removeClass('wpmtt-status-success wpmtt-status-error').html('');

            $.ajax({
                url: wpmtt.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpmtt_send_test_email',
                    nonce: wpmtt.nonce,
                    to: $emailTo.val()
                },
                success: function (response) {
                    if (response.success) {
                        $result.addClass('wpmtt-status-success').html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message);
                    } else {
                        $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + response.data.message);
                    }
                },
                error: function () {
                    $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + (wpmtt.strings.error || 'Error'));
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        clearLogs: function ($btn) {
            var $result = $('#wpmtt-clear-result');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> ' + (wpmtt.strings.loading || 'Processing...'));
            $result.removeClass('wpmtt-status-success wpmtt-status-error').html('');

            $.ajax({
                url: wpmtt.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpmtt_clear_logs',
                    nonce: wpmtt.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $result.addClass('wpmtt-status-success').html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message);
                    } else {
                        $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + response.data.message);
                    }
                },
                error: function () {
                    $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + (wpmtt.strings.error || 'Error'));
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        resetSettings: function ($btn) {
            var $result = $('#wpmtt-reset-result');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html((wpmtt.strings.loading || 'Processing...'));
            $result.removeClass('wpmtt-status-success wpmtt-status-error').html('');

            $.ajax({
                url: wpmtt.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpmtt_reset_settings',
                    nonce: wpmtt.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $result.addClass('wpmtt-status-success').html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message);
                        if (response.data.redirect) {
                            setTimeout(function () {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                    } else {
                        $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + response.data.message);
                        $btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function () {
                    $result.addClass('wpmtt-status-error').html('<span class="dashicons dashicons-warning"></span> ' + (wpmtt.strings.error || 'Error'));
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        }
    };

    // ==========================================================================
    // Documentation
    // ==========================================================================
    WPMTT.docs = {
        init: function () {
            var self = this;

            // Smooth scroll for navigation
            $('.wpmtt-docs-nav-item').on('click', function (e) {
                e.preventDefault();
                var target = $(this).attr('href');
                self.scrollToSection(target);

                // Update active state
                $('.wpmtt-docs-nav-item').removeClass('active');
                $(this).addClass('active');
            });

            // Copy buttons
            $('.wpmtt-copy-btn, .wpmtt-copy-code').on('click', function () {
                var targetId = $(this).data('target');
                self.copyToClipboard(targetId, $(this));
            });

            // Scroll spy
            $(window).on('scroll', function () {
                self.updateActiveNav();
            });
        },

        scrollToSection: function (target) {
            var $target = $(target);
            if ($target.length) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - 50
                }, 300);
            }
        },

        updateActiveNav: function () {
            var scrollPos = $(window).scrollTop() + 100;

            $('.wpmtt-docs-section').each(function () {
                var $section = $(this);
                var sectionTop = $section.offset().top;
                var sectionBottom = sectionTop + $section.outerHeight();
                var sectionId = $section.attr('id');

                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    $('.wpmtt-docs-nav-item').removeClass('active');
                    $('.wpmtt-docs-nav-item[href="#' + sectionId + '"]').addClass('active');
                }
            });
        },

        copyToClipboard: function (targetId, $btn) {
            var $target = $('#' + targetId);
            var text = $target.text();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    var originalText = $btn.text();
                    $btn.text(wpmtt.strings.copied || 'Copied!');
                    setTimeout(function () {
                        $btn.text(originalText);
                    }, 2000);
                });
            } else {
                // Fallback for older browsers
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();

                var originalText = $btn.text();
                $btn.text(wpmtt.strings.copied || 'Copied!');
                setTimeout(function () {
                    $btn.text(originalText);
                }, 2000);
            }
        }
    };

    // ==========================================================================
    // Utility Functions
    // ==========================================================================

    // Spinning animation for dashicons
    $('<style>')
        .prop('type', 'text/css')
        .html('.dashicons.spinning { animation: wpmtt-spin 1s linear infinite; } @keyframes wpmtt-spin { 100% { transform: rotate(360deg); } }')
        .appendTo('head');

    // Export WPMTT
    window.WPMTT = WPMTT;

})(jQuery);
