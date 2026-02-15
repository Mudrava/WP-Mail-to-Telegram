<?php
/**
 * Developer API - Public functions for developers
 */

defined('ABSPATH') || exit;

class WPMTT_Developer_API
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register shortcode for testing
        add_shortcode('wpmtt_test', [$this, 'test_shortcode']);
    }

    /**
     * Test shortcode for debugging
     * Usage: [wpmtt_test]
     */
    public function test_shortcode($atts)
    {
        if (!current_user_can('manage_options')) {
            return '';
        }

        ob_start();
        ?>
        <div style="padding: 20px; background: #f0f0f0; border-radius: 8px; margin: 20px 0;">
            <h3><?php _e('WP Mail to Telegram - Status', 'wp-mail-to-telegram'); ?></h3>
            <p>
                <strong><?php _e('Version:', 'wp-mail-to-telegram'); ?></strong> <?php echo WPMTT_VERSION; ?><br>
                <strong><?php _e('Configured:', 'wp-mail-to-telegram'); ?></strong>
                <?php echo WP_Mail_To_Telegram::is_configured() ? __('Yes', 'wp-mail-to-telegram') : __('No', 'wp-mail-to-telegram'); ?><br>
                <strong><?php _e('Telegram Enabled:', 'wp-mail-to-telegram'); ?></strong>
                <?php echo WP_Mail_To_Telegram::is_telegram_enabled() ? __('Yes', 'wp-mail-to-telegram') : __('No', 'wp-mail-to-telegram'); ?><br>
                <strong><?php _e('API Connected:', 'wp-mail-to-telegram'); ?></strong>
                <?php echo !empty(WP_Mail_To_Telegram::get_option('api_secret', '')) ? __('Yes', 'wp-mail-to-telegram') : __('No', 'wp-mail-to-telegram'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Get plugin version
 * 
 * @return string
 */
function wpmtt_version()
{
    return WPMTT_VERSION;
}

/**
 * Check if plugin is configured
 * 
 * @return bool
 */
function wpmtt_is_configured()
{
    return WP_Mail_To_Telegram::is_configured();
}

/**
 * Check if Telegram sending is enabled
 * 
 * @return bool
 */
function wpmtt_is_enabled()
{
    return WP_Mail_To_Telegram::is_telegram_enabled();
}

/**
 * Get email log statistics
 * 
 * @return array
 */
function wpmtt_get_stats()
{
    return wpmtt()->database->get_stats();
}

/**
 * Get recent emails
 * 
 * @param int $limit Number of emails to retrieve
 * @return array
 */
function wpmtt_get_recent_emails($limit = 10)
{
    return wpmtt()->database->get_emails([
        'per_page' => $limit,
        'page' => 1,
    ]);
}
