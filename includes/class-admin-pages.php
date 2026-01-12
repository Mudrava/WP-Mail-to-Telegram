<?php
/**
 * Admin pages registration
 */

defined('ABSPATH') || exit;

class WPMTT_Admin_Pages
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register admin menu
     */
    public function register_menu()
    {
        // Main menu
        add_menu_page(
            __('WP Mail to Telegram', 'wp-mail-to-telegram'),
            __('Mail to TG', 'wp-mail-to-telegram'),
            'manage_options',
            'wpmtt-log',
            [$this, 'render_log_page'],
            $this->get_menu_icon(),
            30
        );

        // Email log submenu
        add_submenu_page(
            'wpmtt-log',
            __('Email Log', 'wp-mail-to-telegram'),
            __('Email Log', 'wp-mail-to-telegram'),
            'manage_options',
            'wpmtt-log',
            [$this, 'render_log_page']
        );

        // Settings submenu
        add_submenu_page(
            'wpmtt-log',
            __('Settings', 'wp-mail-to-telegram'),
            __('Settings', 'wp-mail-to-telegram'),
            'manage_options',
            'wpmtt-settings',
            [$this, 'render_settings_page']
        );

        // Documentation submenu
        add_submenu_page(
            'wpmtt-log',
            __('Documentation', 'wp-mail-to-telegram'),
            __('Documentation', 'wp-mail-to-telegram'),
            'manage_options',
            'wpmtt-docs',
            [$this, 'render_docs_page']
        );

        // Hidden pages
        add_submenu_page(
            null,
            __('View Email', 'wp-mail-to-telegram'),
            '',
            'manage_options',
            'wpmtt-email-view',
            [$this, 'render_email_view_page']
        );

        add_submenu_page(
            null,
            __('Setup Wizard', 'wp-mail-to-telegram'),
            '',
            'manage_options',
            'wpmtt-setup',
            [$this, 'render_setup_page']
        );
    }

    /**
     * Get menu icon (Telegram-style)
     */
    private function get_menu_icon()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a7aaad"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.05-.2s-.16-.04-.23-.02c-.1.02-1.62 1.03-4.58 3.03-.43.3-.82.44-1.17.43-.39-.01-1.13-.22-1.68-.4-.68-.22-1.22-.34-1.17-.71.02-.2.31-.39.87-.59 3.41-1.48 5.68-2.46 6.82-2.94 3.25-1.35 3.92-1.59 4.36-1.6.1 0 .32.02.46.12.12.09.15.21.17.3-.01.06 0 .23-.02.36z"/></svg>');
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook)
    {
        // Only on our pages
        if (strpos($hook, 'wpmtt') === false) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'wpmtt-admin',
            WPMTT_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WPMTT_VERSION
        );

        // JS
        wp_enqueue_script(
            'wpmtt-admin',
            WPMTT_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WPMTT_VERSION,
            true
        );

        // Localize
        wp_localize_script('wpmtt-admin', 'wpmtt', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpmtt_nonce'),
            'strings' => [
                'confirm_delete' => __('Are you sure you want to delete the selected emails?', 'wp-mail-to-telegram'),
                'confirm_clear' => __('Are you sure you want to clear all logs? This action cannot be undone.', 'wp-mail-to-telegram'),
                'confirm_reset' => __('Are you sure you want to reset all settings? This action cannot be undone.', 'wp-mail-to-telegram'),
                'loading' => __('Loading...', 'wp-mail-to-telegram'),
                'error' => __('An error occurred', 'wp-mail-to-telegram'),
                'success' => __('Success!', 'wp-mail-to-telegram'),
                'validating' => __('Validating...', 'wp-mail-to-telegram'),
                'connecting' => __('Connecting...', 'wp-mail-to-telegram'),
                'sending' => __('Sending...', 'wp-mail-to-telegram'),
                'copied' => __('Copied!', 'wp-mail-to-telegram'),
                'saved' => __('Saved', 'wp-mail-to-telegram'),
            ],
        ]);
    }

    /**
     * Render log page (placeholder - actual rendering in separate class)
     */
    public function render_log_page()
    {
        // Rendered by WPMTT_Email_Log_Page
    }

    /**
     * Render settings page (placeholder)
     */
    public function render_settings_page()
    {
        // Rendered by WPMTT_Settings_Page
    }

    /**
     * Render docs page (placeholder)
     */
    public function render_docs_page()
    {
        // Rendered by WPMTT_Documentation_Page
    }

    /**
     * Render email view page (placeholder)
     */
    public function render_email_view_page()
    {
        // Rendered by WPMTT_Email_Detail_Page
    }

    /**
     * Render setup page (placeholder)
     */
    public function render_setup_page()
    {
        // Rendered by WPMTT_Setup_Wizard
    }
}
