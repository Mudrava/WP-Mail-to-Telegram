<?php
/**
 * Email Logger - Hooks into wp_mail to log all emails
 */

defined('ABSPATH') || exit;

class WPMTT_Email_Logger
{

    /**
     * Database instance
     */
    private $database;

    /**
     * Telegram sender instance
     */
    private $telegram;

    /**
     * Current email ID being processed
     */
    private $current_email_id = null;

    /**
     * Current email data
     */
    private $current_email_data = null;

    /**
     * Constructor
     */
    public function __construct($database, $telegram)
    {
        $this->database = $database;
        $this->telegram = $telegram;

        // Hook into wp_mail
        add_filter('wp_mail', [$this, 'log_email_before_send'], 10, 1);

        // Hook into mail success/failure
        add_action('wp_mail_succeeded', [$this, 'email_sent_success']);
        add_action('wp_mail_failed', [$this, 'email_sent_failed']);
    }

    /**
     * Log email before sending
     */
    public function log_email_before_send($args)
    {
        // Extract from email
        $from_email = '';
        if (!empty($args['headers'])) {
            $headers = is_array($args['headers']) ? $args['headers'] : explode("\n", $args['headers']);
            foreach ($headers as $header) {
                if (stripos($header, 'from:') !== false) {
                    preg_match('/from:\s*(.+)/i', $header, $matches);
                    if (!empty($matches[1])) {
                        $from_email = trim($matches[1]);
                    }
                }
            }
        }

        // Build email data
        $email_data = [
            'to_email' => is_array($args['to']) ? implode(', ', $args['to']) : $args['to'],
            'from_email' => $from_email ?: get_option('admin_email'),
            'subject' => isset($args['subject']) ? $args['subject'] : '',
            'message' => isset($args['message']) ? $args['message'] : '',
            'headers' => isset($args['headers']) ? $args['headers'] : '',
            'attachments' => isset($args['attachments']) ? $args['attachments'] : [],
            'status' => 'pending',
        ];

        // Log to database
        $this->current_email_id = $this->database->log_email($email_data);

        // Store for later
        $this->current_email_data = $email_data;

        return $args;
    }

    /**
     * Email sent successfully
     */
    public function email_sent_success($mail_data)
    {
        if (!$this->current_email_id) {
            return;
        }

        // Update status to sent
        $this->database->update_email($this->current_email_id, [
            'status' => 'sent',
        ]);

        // Send to Telegram
        $this->send_to_telegram();

        // Clear current
        $this->current_email_id = null;
        $this->current_email_data = null;
    }

    /**
     * Email send failed
     */
    public function email_sent_failed($wp_error)
    {
        if (!$this->current_email_id) {
            return;
        }

        $error_message = '';
        if (is_wp_error($wp_error)) {
            $error_message = $wp_error->get_error_message();
        } elseif (is_object($wp_error) && isset($wp_error->errors)) {
            $error = new WP_Error();
            $error->errors = $wp_error->errors;
            $error_message = $error->get_error_message();
        }

        // Update status to failed
        $this->database->update_email($this->current_email_id, [
            'status' => 'failed',
            'error_message' => $error_message,
        ]);

        // Send to Telegram even on failure
        $this->send_to_telegram(true);

        // Clear current
        $this->current_email_id = null;
        $this->current_email_data = null;
    }

    /**
     * Send notification to Telegram
     */
    private function send_to_telegram($is_failed = false)
    {
        if (!WP_Mail_To_Telegram::is_telegram_enabled()) {
            return;
        }

        if (!$this->current_email_id || !$this->current_email_data) {
            return;
        }

        $result = $this->telegram->send_email($this->current_email_data, $this->current_email_id);

        $update_data = [];

        if (is_wp_error($result)) {
            $update_data['sent_to_tg'] = 0;
            $update_data['tg_error'] = $result->get_error_message();
        } elseif (isset($result['success']) && $result['success']) {
            $update_data['sent_to_tg'] = 1;
            if (isset($result['message_id'])) {
                $update_data['tg_message_id'] = $result['message_id'];
            }
        } else {
            $update_data['sent_to_tg'] = 0;
            $update_data['tg_error'] = isset($result['message']) ? $result['message'] : __('Unknown error', 'wp-mail-to-telegram');
        }

        $this->database->update_email($this->current_email_id, $update_data);
    }
}
