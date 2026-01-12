<?php
/**
 * Setup Wizard - Premium onboarding experience
 */

defined('ABSPATH') || exit;

class WPMTT_Setup_Wizard
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'maybe_show_wizard']);
        add_action('wp_ajax_wpmtt_validate_telegram_id', [$this, 'ajax_validate_telegram_id']);
        add_action('wp_ajax_wpmtt_verify_code', [$this, 'ajax_verify_code']);
        add_action('wp_ajax_wpmtt_complete_setup', [$this, 'ajax_complete_setup']);
        add_action('wp_ajax_wpmtt_skip_setup', [$this, 'ajax_skip_setup']);
    }

    /**
     * Check if wizard should be shown
     */
    public function maybe_show_wizard()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if on setup page
        if (isset($_GET['page']) && $_GET['page'] === 'wpmtt-setup') {
            add_action('admin_footer', [$this, 'render_wizard']);
        }
    }

    /**
     * Render the setup wizard
     */
    public function render_wizard()
    {
        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        $telegram_id = WP_Mail_To_Telegram::get_option('telegram_id', '');
        ?>
        <div class="wpmtt-setup-overlay">
            <div class="wpmtt-setup-wizard">
                <!-- Header -->
                <div class="wpmtt-setup-header">
                    <div class="wpmtt-setup-logo">
                        <svg viewBox="0 0 240 240" width="48" height="48">
                            <defs>
                                <linearGradient id="tg-gradient" x1="120" y1="240" x2="120" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#1d93d2" />
                                    <stop offset="1" stop-color="#38b0e3" />
                                </linearGradient>
                            </defs>
                            <circle cx="120" cy="120" r="120" fill="url(#tg-gradient)" />
                            <path
                                d="M81.229,128.772l14.237,39.406s1.78,3.687,3.686,3.687,30.255-29.492,30.255-29.492l31.525-60.89L81.737,118.6Z"
                                fill="#c8daea" />
                            <path d="M100.106,138.878l-2.733,29.046s-1.144,8.9,7.754,0,17.415-15.763,17.415-15.763"
                                fill="#a9c6d8" />
                            <path
                                d="M81.486,130.178,52.2,120.636s-3.5-1.42-2.373-4.64c.232-.664.7-1.229,2.1-2.2,6.489-4.523,120.106-45.36,120.106-45.36s3.208-1.081,5.1-.362a2.766,2.766,0,0,1,1.885,2.055,9.357,9.357,0,0,1,.254,2.585c-.009.752-.1,1.449-.169,2.542-.692,11.165-21.4,94.493-21.4,94.493s-1.239,4.876-5.678,5.043A8.13,8.13,0,0,1,146.1,172.5c-8.711-7.493-38.819-27.727-45.472-32.177a1.27,1.27,0,0,1-.546-.9c-.093-.469.417-1.05.417-1.05s52.426-46.6,53.821-51.492c.108-.379-.3-.566-.848-.4-3.482,1.281-63.844,39.4-70.506,43.607A3.21,3.21,0,0,1,81.486,130.178Z"
                                fill="#fff" />
                        </svg>
                    </div>
                    <h1><?php _e('Welcome to WP Mail to Telegram', 'wp-mail-to-telegram'); ?></h1>
                    <p class="wpmtt-setup-subtitle">
                        <?php _e('Set up email notifications to Telegram in just a few minutes', 'wp-mail-to-telegram'); ?>
                    </p>
                </div>

                <!-- Progress -->
                <div class="wpmtt-setup-progress">
                    <div class="wpmtt-progress-step <?php echo $current_step >= 1 ? 'active' : ''; ?>" data-step="1">
                        <span class="step-number">1</span>
                        <span class="step-label"><?php _e('Telegram ID', 'wp-mail-to-telegram'); ?></span>
                    </div>
                    <div class="wpmtt-progress-line <?php echo $current_step >= 2 ? 'active' : ''; ?>"></div>
                    <div class="wpmtt-progress-step <?php echo $current_step >= 2 ? 'active' : ''; ?>" data-step="2">
                        <span class="step-number">2</span>
                        <span class="step-label"><?php _e('Connect Bot', 'wp-mail-to-telegram'); ?></span>
                    </div>
                    <div class="wpmtt-progress-line <?php echo $current_step >= 3 ? 'active' : ''; ?>"></div>
                    <div class="wpmtt-progress-step <?php echo $current_step >= 3 ? 'active' : ''; ?>" data-step="3">
                        <span class="step-number">3</span>
                        <span class="step-label"><?php _e('Complete', 'wp-mail-to-telegram'); ?></span>
                    </div>
                </div>

                <!-- Steps Content -->
                <div class="wpmtt-setup-content">

                    <!-- Step 1: Telegram ID -->
                    <div class="wpmtt-setup-step <?php echo $current_step === 1 ? 'active' : ''; ?>" data-step="1">
                        <div class="wpmtt-step-icon">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                        <h2><?php _e('Get Your Telegram ID', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-info-box">
                            <h4><?php _e('How to get your Telegram ID:', 'wp-mail-to-telegram'); ?></h4>
                            <ol>
                                <li><?php _e('Open Telegram and find the bot', 'wp-mail-to-telegram'); ?> <a
                                        href="https://t.me/ShowMyTelegramIDBot" target="_blank">@ShowMyTelegramIDBot</a></li>
                                <li><?php _e('Press the <strong>Start</strong> button or send the command', 'wp-mail-to-telegram'); ?>
                                    <code>/start</code>
                                </li>
                                <li><?php _e('Send the command', 'wp-mail-to-telegram'); ?> <code>/chatid</code></li>
                                <li><?php _e('Copy the numeric ID you receive', 'wp-mail-to-telegram'); ?></li>
                            </ol>
                        </div>

                        <div class="wpmtt-input-group">
                            <label for="wpmtt-telegram-id"><?php _e('Your Telegram ID', 'wp-mail-to-telegram'); ?></label>
                            <input type="text" id="wpmtt-telegram-id" value="<?php echo esc_attr($telegram_id); ?>"
                                placeholder="123456789" autocomplete="off">
                            <span class="wpmtt-input-status"></span>
                        </div>

                        <div class="wpmtt-step-actions">
                            <button type="button"
                                class="button button-secondary wpmtt-skip-btn"><?php _e('Set Up Later', 'wp-mail-to-telegram'); ?></button>
                            <button type="button" class="button button-primary button-hero wpmtt-next-btn" data-next="2"
                                disabled><?php _e('Next', 'wp-mail-to-telegram'); ?> →</button>
                        </div>
                    </div>

                    <!-- Step 2: Bot Connection -->
                    <div class="wpmtt-setup-step <?php echo $current_step === 2 ? 'active' : ''; ?>" data-step="2">
                        <div class="wpmtt-step-icon">
                            <span class="dashicons dashicons-admin-plugins"></span>
                        </div>
                        <h2><?php _e('Connect the Bot to Your Website', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-info-box">
                            <h4><?php _e('Follow these steps:', 'wp-mail-to-telegram'); ?></h4>
                            <ol>
                                <li><?php _e('Open Telegram and find the bot', 'wp-mail-to-telegram'); ?> <a
                                        href="https://t.me/WPMailToTelegramBot" target="_blank">@WPMailToTelegramBot</a></li>
                                <li><?php _e('Press <strong>Start</strong> to launch the bot', 'wp-mail-to-telegram'); ?></li>
                                <li><?php _e('Send the command', 'wp-mail-to-telegram'); ?> <code>/addsite</code></li>
                                <li><?php _e('The bot will give you a verification code - copy it', 'wp-mail-to-telegram'); ?>
                                </li>
                            </ol>
                        </div>

                        <div class="wpmtt-input-group">
                            <label
                                for="wpmtt-verification-code"><?php _e('Verification Code from Bot', 'wp-mail-to-telegram'); ?></label>
                            <input type="text" id="wpmtt-verification-code" placeholder="XXXX-XXXX-XXXX" autocomplete="off">
                            <span class="wpmtt-input-status"></span>
                        </div>

                        <div class="wpmtt-step-actions">
                            <button type="button" class="button button-secondary wpmtt-back-btn" data-back="1">←
                                <?php _e('Back', 'wp-mail-to-telegram'); ?></button>
                            <button type="button"
                                class="button button-primary button-hero wpmtt-verify-btn"><?php _e('Connect', 'wp-mail-to-telegram'); ?></button>
                        </div>
                    </div>

                    <!-- Step 3: Complete -->
                    <div class="wpmtt-setup-step <?php echo $current_step === 3 ? 'active' : ''; ?>" data-step="3">
                        <div class="wpmtt-step-icon wpmtt-success-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <h2><?php _e('Awesome! All Set Up!', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-success-box">
                            <p><?php _e('All email notifications from your website will now be automatically sent to your Telegram.', 'wp-mail-to-telegram'); ?>
                            </p>
                            <ul>
                                <li><span class="dashicons dashicons-yes"></span>
                                    <?php _e('Contact form submissions', 'wp-mail-to-telegram'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span>
                                    <?php _e('Order notifications', 'wp-mail-to-telegram'); ?></li>
                                <li><span class="dashicons dashicons-yes"></span>
                                    <?php _e('WordPress system notifications', 'wp-mail-to-telegram'); ?></li>
                            </ul>
                        </div>

                        <div class="wpmtt-quick-links">
                            <a href="<?php echo admin_url('admin.php?page=wpmtt-log'); ?>" class="wpmtt-quick-link">
                                <span class="dashicons dashicons-email-alt"></span>
                                <span><?php _e('Email Log', 'wp-mail-to-telegram'); ?></span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=wpmtt-settings'); ?>" class="wpmtt-quick-link">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <span><?php _e('Settings', 'wp-mail-to-telegram'); ?></span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=wpmtt-docs'); ?>" class="wpmtt-quick-link">
                                <span class="dashicons dashicons-book"></span>
                                <span><?php _e('Documentation', 'wp-mail-to-telegram'); ?></span>
                            </a>
                        </div>

                        <div class="wpmtt-step-actions">
                            <a href="<?php echo admin_url('admin.php?page=wpmtt-log'); ?>"
                                class="button button-primary button-hero"><?php _e('Go to Email Log', 'wp-mail-to-telegram'); ?></a>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="wpmtt-setup-footer">
                    <p>
                        <strong><?php _e('Important:', 'wp-mail-to-telegram'); ?></strong>
                        <?php _e('This plugin does NOT replace email. It only sends copies to the administrator via Telegram.', 'wp-mail-to-telegram'); ?>
                    </p>
                </div>
            </div>
        </div>

        <style>
            /* Hide default WordPress admin content on setup page */
            #wpbody-content>* {
                display: none !important;
            }

            #wpbody-content>.wpmtt-setup-overlay {
                display: flex !important;
            }

            /* Critical mobile fixes for setup wizard */
            @media screen and (max-height: 800px) {
                .wpmtt-setup-overlay {
                    align-items: flex-start !important;
                    padding-top: 0 !important;
                    padding-bottom: 20px !important;
                }

                .wpmtt-setup-wizard {
                    margin-top: 0 !important;
                }
            }

            @media screen and (max-width: 600px) {
                .wpmtt-setup-overlay {
                    padding: 0 !important;
                }

                .wpmtt-setup-wizard {
                    border-radius: 0 !important;
                    margin: 0 !important;
                }

                .wpmtt-setup-header h1 {
                    font-size: 16px !important;
                }

                .wpmtt-setup-logo svg {
                    width: 36px !important;
                    height: 36px !important;
                }
            }
        </style>
        <?php
    }

    /**
     * AJAX: Validate Telegram ID
     */
    public function ajax_validate_telegram_id()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        $telegram_id = isset($_POST['telegram_id']) ? sanitize_text_field($_POST['telegram_id']) : '';

        // Validate - must be numeric
        if (empty($telegram_id) || !preg_match('/^-?\d+$/', $telegram_id)) {
            wp_send_json_error(['message' => __('Telegram ID must be a number', 'wp-mail-to-telegram')]);
        }

        // Save
        WP_Mail_To_Telegram::update_option('telegram_id', $telegram_id);

        wp_send_json_success(['message' => __('Telegram ID saved', 'wp-mail-to-telegram')]);
    }

    /**
     * AJAX: Verify code with API
     */
    public function ajax_verify_code()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $telegram_id = WP_Mail_To_Telegram::get_option('telegram_id', '');

        if (empty($code)) {
            wp_send_json_error(['message' => __('Please enter the verification code', 'wp-mail-to-telegram')]);
        }

        if (empty($telegram_id)) {
            wp_send_json_error(['message' => __('Please enter your Telegram ID first', 'wp-mail-to-telegram')]);
        }

        // Call API
        $api_client = wpmtt()->api_client;
        $result = $api_client->verify_site($code, $telegram_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        if (isset($result['success']) && $result['success']) {
            // Save verification code
            WP_Mail_To_Telegram::update_option('verification_code', $code);
            WP_Mail_To_Telegram::update_option('telegram_enabled', true);
            update_option('wpmtt_setup_complete', true);

            wp_send_json_success(['message' => __('Bot connected successfully!', 'wp-mail-to-telegram')]);
        } else {
            $message = isset($result['message']) ? $result['message'] : __('Verification failed', 'wp-mail-to-telegram');
            wp_send_json_error(['message' => $message]);
        }
    }

    /**
     * AJAX: Complete setup
     */
    public function ajax_complete_setup()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        update_option('wpmtt_setup_complete', true);

        wp_send_json_success(['redirect' => admin_url('admin.php?page=wpmtt-log')]);
    }

    /**
     * AJAX: Skip setup
     */
    public function ajax_skip_setup()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        // Mark as skipped (but not complete)
        update_option('wpmtt_setup_skipped', true);

        wp_send_json_success(['redirect' => admin_url('admin.php?page=wpmtt-settings')]);
    }
}
