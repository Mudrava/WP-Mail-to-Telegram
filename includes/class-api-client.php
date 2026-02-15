<?php
/**
 * API Client for communication with Telegram bot server.
 *
 * Authentication model:
 *  - verify_site() uses a one-time 6-digit code (no Bearer token required).
 *  - All subsequent requests use a 256-bit API secret returned from verify,
 *    sent as `Authorization: Bearer {secret}` with an `X-WP-Timestamp` header
 *    to prevent replay attacks (server rejects timestamps older than 5 min).
 */

defined('ABSPATH') || exit;

class WPMTT_API_Client
{

    /** @var string */
    private $api_url;

    public function __construct()
    {
        $this->api_url = WPMTT_API_URL;
    }

    // ------------------------------------------------------------------
    //  Public API methods
    // ------------------------------------------------------------------

    /**
     * One-time site verification using 6-digit code from the Telegram bot.
     *
     * On success the server returns `api_secret` – a 256-bit hex token that
     * MUST be persisted and used for all future requests.
     *
     * @param string $code 6-digit verification code
     * @return array|WP_Error
     */
    public function verify_site($code)
    {
        // Verify does NOT require Bearer auth – it's the bootstrap handshake.
        return $this->request('verify', [
            'action'   => 'verify',
            'code'     => sanitize_text_field($code),
            'site_url' => home_url(),
        ], false);
    }

    /**
     * Send email notification to Telegram.
     *
     * @param array $data {email_id, to, from, subject, message, view_url, attachments_count}
     * @return array|WP_Error
     */
    public function send_email_notification($data)
    {
        $request_data = [
            'action'            => 'send_email',
            'site_url'          => home_url(),
            'to'                => $data['to'],
            'from'              => $data['from'],
            'subject'           => $data['subject'],
            'message'           => $data['message'],
            'attachments_count' => isset($data['attachments_count']) ? (int) $data['attachments_count'] : 0,
        ];

        if (!empty($data['email_id'])) {
            $request_data['email_id'] = (int) $data['email_id'];
        }

        if (!empty($data['view_url'])) {
            $request_data['view_url'] = $data['view_url'];
        }

        return $this->request('send_email', $request_data);
    }

    /**
     * Send a custom message to Telegram.
     *
     * @param string $message
     * @param array  $options {parse_mode: HTML|Markdown}
     * @return array|WP_Error
     */
    public function send_custom_message($message, $options = [])
    {
        $request_data = [
            'action'   => 'send_message',
            'site_url' => home_url(),
            'message'  => $message,
        ];

        if (!empty($options['parse_mode'])) {
            $request_data['parse_mode'] = $options['parse_mode'];
        }

        return $this->request('send_message', $request_data);
    }

    /**
     * Test connection to the bot server.
     *
     * @return array|WP_Error
     */
    public function test_connection()
    {
        return $this->request('test', [
            'action'   => 'test',
            'site_url' => home_url(),
        ]);
    }

    /**
     * Unregister this site from the bot.
     *
     * @return array|WP_Error
     */
    public function unregister_site()
    {
        return $this->request('unregister', [
            'action'   => 'unregister',
            'site_url' => home_url(),
        ]);
    }

    // ------------------------------------------------------------------
    //  HTTP transport
    // ------------------------------------------------------------------

    /**
     * Make an authenticated API request.
     *
     * @param string $endpoint  API endpoint name (appended to base URL)
     * @param array  $data      Request body (JSON-encoded)
     * @param bool   $with_auth Whether to attach Bearer + timestamp headers (default true)
     * @return array|WP_Error
     */
    private function request($endpoint, $data = [], $with_auth = true)
    {
        $url = trailingslashit($this->api_url) . $endpoint;

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if ($with_auth) {
            $api_secret = WP_Mail_To_Telegram::get_option('api_secret', '');

            if (empty($api_secret)) {
                return new WP_Error(
                    'no_api_secret',
                    __('API secret not found. Please reconnect the site.', 'wp-mail-to-telegram')
                );
            }

            $headers['Authorization']  = 'Bearer ' . $api_secret;
            $headers['X-WP-Timestamp'] = (string) time();
        }

        $response = wp_remote_post($url, [
            'timeout' => 30,
            'headers' => $headers,
            'body'    => wp_json_encode($data),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $decoded     = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Failed to parse API response', 'wp-mail-to-telegram'));
        }

        if ($status_code >= 400) {
            $message    = isset($decoded['message'])    ? $decoded['message']    : __('API Error', 'wp-mail-to-telegram');
            $error_code = isset($decoded['error_code']) ? $decoded['error_code'] : 'api_error';
            return new WP_Error($error_code, $message, ['status' => $status_code]);
        }

        return $decoded;
    }
}
