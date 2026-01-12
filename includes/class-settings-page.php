<?php
/**
 * Settings Page
 */

defined('ABSPATH') || exit;

class WPMTT_Settings_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_wpmtt_test_connection', [$this, 'ajax_test_connection']);
        add_action('wp_ajax_wpmtt_send_test_email', [$this, 'ajax_send_test_email']);
        add_action('wp_ajax_wpmtt_clear_logs', [$this, 'ajax_clear_logs']);
        add_action('wp_ajax_wpmtt_reset_settings', [$this, 'ajax_reset_settings']);
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('wpmtt_settings_group', 'wpmtt_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings'],
        ]);
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = [];

        $sanitized['telegram_id'] = isset($input['telegram_id']) ? sanitize_text_field($input['telegram_id']) : '';
        $sanitized['telegram_enabled'] = isset($input['telegram_enabled']) ? (bool) $input['telegram_enabled'] : false;
        $sanitized['verification_code'] = isset($input['verification_code']) ? sanitize_text_field($input['verification_code']) : '';
        $sanitized['log_retention_days'] = isset($input['log_retention_days']) ? intval($input['log_retention_days']) : 30;

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public static function render()
    {
        $telegram_id = WP_Mail_To_Telegram::get_option('telegram_id', '');
        $telegram_enabled = WP_Mail_To_Telegram::get_option('telegram_enabled', true);
        $verification_code = WP_Mail_To_Telegram::get_option('verification_code', '');
        $log_retention = WP_Mail_To_Telegram::get_option('log_retention_days', 30);
        $is_configured = WP_Mail_To_Telegram::is_configured();
        ?>
        <div class="wrap wpmtt-wrap wpmtt-settings-page">
            <h1>
                <span class="dashicons dashicons-admin-generic"></span>
                <?php _e('Settings', 'wp-mail-to-telegram'); ?>
            </h1>

            <?php if (!$is_configured): ?>
                <div class="notice notice-warning">
                    <p>
                        <strong><?php _e('Plugin is not configured.', 'wp-mail-to-telegram'); ?></strong>
                        <a
                            href="<?php echo admin_url('admin.php?page=wpmtt-setup'); ?>"><?php _e('Run the setup wizard', 'wp-mail-to-telegram'); ?></a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Settings saved.', 'wp-mail-to-telegram'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php" class="wpmtt-settings-form">
                <?php settings_fields('wpmtt_settings_group'); ?>

                <!-- Telegram Settings -->
                <div class="wpmtt-settings-section">
                    <h2>
                        <span class="dashicons dashicons-format-status"></span>
                        <?php _e('Telegram', 'wp-mail-to-telegram'); ?>
                    </h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label
                                    for="wpmtt_telegram_enabled"><?php _e('Send to Telegram', 'wp-mail-to-telegram'); ?></label>
                            </th>
                            <td>
                                <label class="wpmtt-toggle">
                                    <input type="checkbox" id="wpmtt_telegram_enabled" name="wpmtt_settings[telegram_enabled]"
                                        value="1" <?php checked($telegram_enabled); ?>>
                                    <span class="wpmtt-toggle-slider"></span>
                                </label>
                                <p class="description">
                                    <?php _e('Enable/disable sending notifications to Telegram', 'wp-mail-to-telegram'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpmtt_telegram_id"><?php _e('Telegram ID', 'wp-mail-to-telegram'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="wpmtt_telegram_id" name="wpmtt_settings[telegram_id]"
                                    value="<?php echo esc_attr($telegram_id); ?>" class="regular-text">
                                <p class="description">
                                    <?php _e('Your numeric Telegram ID.', 'wp-mail-to-telegram'); ?>
                                    <a href="https://t.me/ShowMyTelegramIDBot"
                                        target="_blank"><?php _e('How to get it?', 'wp-mail-to-telegram'); ?></a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Verification Code', 'wp-mail-to-telegram'); ?></label>
                            </th>
                            <td>
                                <?php if ($verification_code): ?>
                                    <code class="wpmtt-verification-code"><?php echo esc_html($verification_code); ?></code>
                                    <span class="wpmtt-status wpmtt-status-success">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php _e('Connected', 'wp-mail-to-telegram'); ?>
                                    </span>
                                    <input type="hidden" name="wpmtt_settings[verification_code]"
                                        value="<?php echo esc_attr($verification_code); ?>">
                                <?php else: ?>
                                    <span class="wpmtt-status wpmtt-status-warning">
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php _e('Not configured', 'wp-mail-to-telegram'); ?>
                                    </span>
                                    <a href="<?php echo admin_url('admin.php?page=wpmtt-setup'); ?>"
                                        class="button button-secondary"><?php _e('Configure', 'wp-mail-to-telegram'); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Test Connection', 'wp-mail-to-telegram'); ?></th>
                            <td>
                                <button type="button" class="button button-secondary" id="wpmtt-test-connection" <?php disabled(!$is_configured); ?>>
                                    <span class="dashicons dashicons-update"></span>
                                    <?php _e('Send Test Message', 'wp-mail-to-telegram'); ?>
                                </button>
                                <span id="wpmtt-test-result"></span>
                                <p class="description">
                                    <?php _e('Send a test message to Telegram to verify the connection', 'wp-mail-to-telegram'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Log Settings -->
                <div class="wpmtt-settings-section">
                    <h2>
                        <span class="dashicons dashicons-list-view"></span>
                        <?php _e('Email Log', 'wp-mail-to-telegram'); ?>
                    </h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpmtt_log_retention"><?php _e('Log Retention', 'wp-mail-to-telegram'); ?></label>
                            </th>
                            <td>
                                <select id="wpmtt_log_retention" name="wpmtt_settings[log_retention_days]">
                                    <option value="7" <?php selected($log_retention, 7); ?>>
                                        <?php _e('7 days', 'wp-mail-to-telegram'); ?></option>
                                    <option value="14" <?php selected($log_retention, 14); ?>>
                                        <?php _e('14 days', 'wp-mail-to-telegram'); ?></option>
                                    <option value="30" <?php selected($log_retention, 30); ?>>
                                        <?php _e('30 days', 'wp-mail-to-telegram'); ?></option>
                                    <option value="60" <?php selected($log_retention, 60); ?>>
                                        <?php _e('60 days', 'wp-mail-to-telegram'); ?></option>
                                    <option value="90" <?php selected($log_retention, 90); ?>>
                                        <?php _e('90 days', 'wp-mail-to-telegram'); ?></option>
                                    <option value="0" <?php selected($log_retention, 0); ?>>
                                        <?php _e('Forever', 'wp-mail-to-telegram'); ?></option>
                                </select>
                                <p class="description">
                                    <?php _e('Automatically delete old log entries', 'wp-mail-to-telegram'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Clear Logs', 'wp-mail-to-telegram'); ?></th>
                            <td>
                                <button type="button" class="button button-secondary" id="wpmtt-clear-logs">
                                    <span class="dashicons dashicons-trash"></span>
                                    <?php _e('Clear All Logs', 'wp-mail-to-telegram'); ?>
                                </button>
                                <span id="wpmtt-clear-result"></span>
                                <p class="description">
                                    <?php _e('Delete all email log entries. This action cannot be undone.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Test Email -->
                <div class="wpmtt-settings-section">
                    <h2>
                        <span class="dashicons dashicons-email"></span>
                        <?php _e('Test Email', 'wp-mail-to-telegram'); ?>
                    </h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Send Test Email', 'wp-mail-to-telegram'); ?></th>
                            <td>
                                <input type="email" id="wpmtt-test-email-to"
                                    value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text"
                                    placeholder="email@example.com">
                                <button type="button" class="button button-secondary" id="wpmtt-send-test-email">
                                    <span class="dashicons dashicons-email-alt"></span>
                                    <?php _e('Send', 'wp-mail-to-telegram'); ?>
                                </button>
                                <span id="wpmtt-test-email-result"></span>
                                <p class="description">
                                    <?php _e('Send a test email to verify logging and Telegram forwarding', 'wp-mail-to-telegram'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(__('Save Settings', 'wp-mail-to-telegram')); ?>
            </form>

            <!-- Danger Zone -->
            <div class="wpmtt-settings-section wpmtt-danger-zone">
                <h2>
                    <span class="dashicons dashicons-warning"></span>
                    <?php _e('Danger Zone', 'wp-mail-to-telegram'); ?>
                </h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Reset Settings', 'wp-mail-to-telegram'); ?></th>
                        <td>
                            <button type="button" class="button button-link-delete" id="wpmtt-reset-settings">
                                <?php _e('Reset All Settings', 'wp-mail-to-telegram'); ?>
                            </button>
                            <span id="wpmtt-reset-result"></span>
                            <p class="description">
                                <?php _e('Delete all plugin settings and start fresh. This action cannot be undone.', 'wp-mail-to-telegram'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Test connection
     */
    public function ajax_test_connection()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        $result = wpmtt()->telegram->send_custom_message(
            "<b>Test Message</b>\n\n" .
            "Connection is working!\n\n" .
            "Site: " . get_bloginfo('name') . "\n" .
            home_url() . "\n\n" .
            current_time('M j, Y g:i a')
        );

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Test message sent!', 'wp-mail-to-telegram')]);
    }

    /**
     * AJAX: Send test email
     */
    public function ajax_send_test_email()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        $to = isset($_POST['to']) ? sanitize_email($_POST['to']) : get_option('admin_email');

        if (!is_email($to)) {
            wp_send_json_error(['message' => __('Invalid email address', 'wp-mail-to-telegram')]);
        }

        $subject = sprintf(__('[%s] Test Email from WP Mail to Telegram', 'wp-mail-to-telegram'), get_bloginfo('name'));
        $message = sprintf(
            __("Hello!\n\nThis is a test email from the WP Mail to Telegram plugin.\n\nIf you received this email, your WordPress email sending is working correctly.\n\nSite: %s\nURL: %s\nDate: %s\n\n--\nWP Mail to Telegram", 'wp-mail-to-telegram'),
            get_bloginfo('name'),
            home_url(),
            current_time('M j, Y g:i a')
        );

        $result = wp_mail($to, $subject, $message);

        if ($result) {
            wp_send_json_success(['message' => sprintf(__('Test email sent to %s', 'wp-mail-to-telegram'), $to)]);
        } else {
            wp_send_json_error(['message' => __('Failed to send email', 'wp-mail-to-telegram')]);
        }
    }

    /**
     * AJAX: Clear all logs
     */
    public function ajax_clear_logs()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        $result = wpmtt()->database->delete_all_emails();

        if ($result !== false) {
            wp_send_json_success(['message' => __('All logs cleared successfully', 'wp-mail-to-telegram')]);
        } else {
            wp_send_json_error(['message' => __('Failed to clear logs', 'wp-mail-to-telegram')]);
        }
    }

    /**
     * AJAX: Reset all settings
     */
    public function ajax_reset_settings()
    {
        check_ajax_referer('wpmtt_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'wp-mail-to-telegram')]);
        }

        // Delete all options
        WP_Mail_To_Telegram::delete_all_options();

        wp_send_json_success([
            'message' => __('Settings reset successfully. Redirecting to setup wizard...', 'wp-mail-to-telegram'),
            'redirect' => admin_url('admin.php?page=wpmtt-setup')
        ]);
    }
}

// Hook into the admin page render
add_action('wpmtt-settings', ['WPMTT_Settings_Page', 'render']);
add_action('admin_page_wpmtt-settings', ['WPMTT_Settings_Page', 'render']);

// Alternative hook
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'wpmtt-settings') {
        add_action('mail-to-tg_page_wpmtt-settings', ['WPMTT_Settings_Page', 'render']);
    }
});
