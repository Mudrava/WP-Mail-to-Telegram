<?php
/**
 * Plugin Name:         WP Mail to Telegram
 * Plugin URI:          https://mudrava.com/wp-mail-to-telegram
 * Description:         Send copies of all WordPress emails to your Telegram. Perfect for receiving form submissions, order notifications, and system alerts without configuring SMTP. NOTE: This is NOT a replacement for email - it only notifies site administrators.
 * Version:             1.0.1
 * Author:              Mudrava
 * Author URI:          https://mudrava.com
 * License:             GPLv3 or later
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         wp-mail-to-telegram
 * Domain Path:         /languages
 * Requires PHP:        7.4
 * Requires at least:   5.0
 */

defined('ABSPATH') || exit;

// Plugin constants
define('WPMTT_VERSION', '1.0.1');
define('WPMTT_PLUGIN_FILE', __FILE__);
define('WPMTT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPMTT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMTT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPMTT_API_URL', 'https://tgb.mudrava.com/api/m/wpmailtt/webhook/cmkcsetk4002p7w0ii4czelcy');

// Minimum PHP version check
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        echo esc_html__('WP Mail to Telegram requires PHP 7.4 or higher.', 'wp-mail-to-telegram');
        echo '</p></div>';
    });
    return;
}

/**
 * Main plugin class
 */
final class WP_Mail_To_Telegram
{

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Plugin components
     */
    public $database;
    public $logger;
    public $telegram;
    public $api_client;

    /**
     * Get instance
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies()
    {
        require_once WPMTT_PLUGIN_DIR . 'includes/class-database.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-api-client.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-telegram-sender.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-email-logger.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-admin-pages.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-setup-wizard.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-email-log-page.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-email-detail-page.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-settings-page.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-documentation-page.php';
        require_once WPMTT_PLUGIN_DIR . 'includes/class-developer-api.php';

        // Initialize components
        $this->database = new WPMTT_Database();
        $this->api_client = new WPMTT_API_Client();
        $this->telegram = new WPMTT_Telegram_Sender($this->api_client);
        $this->logger = new WPMTT_Email_Logger($this->database, $this->telegram);
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Activation/Deactivation
        register_activation_hook(WPMTT_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(WPMTT_PLUGIN_FILE, [$this, 'deactivate']);

        // Uninstall hook
        register_uninstall_hook(WPMTT_PLUGIN_FILE, ['WP_Mail_To_Telegram', 'uninstall']);

        // Init
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_init', [$this, 'maybe_redirect_to_setup']);

        // Scheduled cleanup
        add_action('wpmtt_cleanup_logs', [$this, 'scheduled_cleanup']);

        // Admin
        if (is_admin()) {
            new WPMTT_Admin_Pages();
            new WPMTT_Setup_Wizard();
            new WPMTT_Email_Log_Page();
            new WPMTT_Email_Detail_Page();
            new WPMTT_Settings_Page();
            new WPMTT_Documentation_Page();
        }

        // Developer API
        new WPMTT_Developer_API();

        // Plugin action links
        add_filter('plugin_action_links_' . WPMTT_PLUGIN_BASENAME, [$this, 'add_action_links']);
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Create database tables
        $this->database->create_tables();

        // Set flag to redirect to setup wizard
        if (!get_option('wpmtt_setup_complete')) {
            set_transient('wpmtt_activation_redirect', true, 30);
        }

        // Schedule cleanup cron
        if (!wp_next_scheduled('wpmtt_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'wpmtt_cleanup_logs');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear scheduled hook
        wp_clear_scheduled_hook('wpmtt_cleanup_logs');
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall()
    {
        global $wpdb;

        // Delete options
        delete_option('wpmtt_settings');
        delete_option('wpmtt_setup_complete');
        delete_option('wpmtt_setup_skipped');
        delete_option('wpmtt_db_version');

        // Drop table
        $table_name = $wpdb->prefix . 'wpmtt_email_log';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

        // Clear scheduled hook
        wp_clear_scheduled_hook('wpmtt_cleanup_logs');
    }

    /**
     * Redirect to setup wizard after activation
     */
    public function maybe_redirect_to_setup()
    {
        if (get_transient('wpmtt_activation_redirect')) {
            delete_transient('wpmtt_activation_redirect');

            // Don't redirect on multisite bulk activation
            if (is_network_admin() || isset($_GET['activate-multi'])) {
                return;
            }

            // Don't redirect if setup is complete
            if (get_option('wpmtt_setup_complete')) {
                return;
            }

            wp_safe_redirect(admin_url('admin.php?page=wpmtt-setup'));
            exit;
        }
    }

    /**
     * Scheduled log cleanup
     */
    public function scheduled_cleanup()
    {
        $retention_days = self::get_option('log_retention_days', 30);
        if ($retention_days > 0) {
            $this->database->clear_old_logs($retention_days);
        }
    }

    /**
     * Load translations
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('wp-mail-to-telegram', false, dirname(WPMTT_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Add plugin action links
     */
    public function add_action_links($links)
    {
        $plugin_links = [
            '<a href="' . admin_url('admin.php?page=wpmtt-settings') . '">' . __('Settings', 'wp-mail-to-telegram') . '</a>',
            '<a href="' . admin_url('admin.php?page=wpmtt-docs') . '">' . __('Documentation', 'wp-mail-to-telegram') . '</a>',
        ];
        return array_merge($plugin_links, $links);
    }

    /**
     * Get option with default
     */
    public static function get_option($key, $default = '')
    {
        $options = get_option('wpmtt_settings', []);
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Update option
     */
    public static function update_option($key, $value)
    {
        $options = get_option('wpmtt_settings', []);
        $options[$key] = $value;
        update_option('wpmtt_settings', $options);
    }

    /**
     * Delete all options
     */
    public static function delete_all_options()
    {
        delete_option('wpmtt_settings');
        delete_option('wpmtt_setup_complete');
        delete_option('wpmtt_setup_skipped');
    }

    /**
     * Check if plugin is configured (setup complete AND api_secret present)
     */
    public static function is_configured()
    {
        return (bool) get_option('wpmtt_setup_complete', false)
            && !empty(self::get_option('api_secret', ''));
    }

    /**
     * Check if Telegram sending is enabled
     */
    public static function is_telegram_enabled()
    {
        return self::is_configured() && self::get_option('telegram_enabled', true);
    }
}

/**
 * Main function to get plugin instance
 */
function wpmtt()
{
    return WP_Mail_To_Telegram::instance();
}

/**
 * Developer API: Send custom message to Telegram
 * 
 * @param string $message Message to send
 * @param array  $options Optional settings
 * @return bool|WP_Error
 */
function wpmtt_send_message($message, $options = [])
{
    if (!WP_Mail_To_Telegram::is_configured()) {
        return new WP_Error('not_configured', __('WP Mail to Telegram is not configured', 'wp-mail-to-telegram'));
    }

    $telegram = wpmtt()->telegram;
    return $telegram->send_custom_message($message, $options);
}

// Initialize plugin
wpmtt();
