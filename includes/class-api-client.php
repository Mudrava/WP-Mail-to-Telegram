<?php
/**
 * API Client for communication with Telegram bot server
 */

defined('ABSPATH') || exit;

class WPMTT_API_Client
{

    /**
     * API URL
     */
    private $api_url;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api_url = WPMTT_API_URL;
    }

    /**
     * Verify site with the bot
     */
    public function verify_site($code, $telegram_id)
    {
        $site_url = home_url();

        $response = $this->request('verify', [
            'code' => sanitize_text_field($code),
            'telegram_id' => sanitize_text_field($telegram_id),
            'site_url' => esc_url($site_url),
            'site_name' => get_bloginfo('name'),
        ]);

        return $response;
    }

    /**
     * Send email notification to Telegram
     */
    public function send_email_notification($data)
    {
        $verification_code = WP_Mail_To_Telegram::get_option('verification_code', '');

        if (empty($verification_code)) {
            return new WP_Error('no_verification', __('Verification code not found', 'wp-mail-to-telegram'));
        }

        $response = $this->request('send_email', [
            'code' => $verification_code,
            'site_url' => home_url(),
            'email_id' => $data['email_id'],
            'to' => $data['to'],
            'from' => $data['from'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'view_url' => $data['view_url'],
            'has_attachments' => !empty($data['attachments']),
        ]);

        return $response;
    }

    /**
     * Send custom message to Telegram
     */
    public function send_custom_message($message, $options = [])
    {
        $verification_code = WP_Mail_To_Telegram::get_option('verification_code', '');

        if (empty($verification_code)) {
            return new WP_Error('no_verification', __('Verification code not found', 'wp-mail-to-telegram'));
        }

        $response = $this->request('send_message', [
            'code' => $verification_code,
            'site_url' => home_url(),
            'message' => $message,
            'parse_mode' => isset($options['parse_mode']) ? $options['parse_mode'] : 'HTML',
        ]);

        return $response;
    }

    /**
     * Test connection
     */
    public function test_connection()
    {
        $verification_code = WP_Mail_To_Telegram::get_option('verification_code', '');

        if (empty($verification_code)) {
            return new WP_Error('no_verification', __('Verification code not found', 'wp-mail-to-telegram'));
        }

        return $this->request('test', [
            'code' => $verification_code,
            'site_url' => home_url(),
        ]);
    }

    /**
     * Make API request
     */
    private function request($endpoint, $data = [])
    {
        $url = trailingslashit($this->api_url) . $endpoint;

        $args = [
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => json_encode($data),
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code >= 400) {
            $message = isset($data['message']) ? $data['message'] : __('API Error', 'wp-mail-to-telegram');
            return new WP_Error('api_error', $message, ['status' => $code]);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Failed to parse response', 'wp-mail-to-telegram'));
        }

        return $data;
    }
}
