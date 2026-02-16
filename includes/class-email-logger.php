<?php
/**
 * Email Logger - Hooks into wp_mail to log all emails
 */

defined('ABSPATH') || exit;

class WPMTT_Email_Logger
{

    /**
     * Database instance
     *
     * @var WPMTT_Database
     */
    private $database;

    /**
     * Telegram sender instance
     *
     * @var WPMTT_Telegram_Sender
     */
    private $telegram;

    /**
     * Stack of email contexts for re-entrancy protection.
     * Each entry: ['id' => int, 'data' => array]
     *
     * @var array
     */
    private $email_stack = [];

    /**
     * Constructor
     *
     * @param WPMTT_Database        $database Database instance.
     * @param WPMTT_Telegram_Sender $telegram Telegram sender instance.
     */
    public function __construct($database, $telegram)
    {
        $this->database = $database;
        $this->telegram = $telegram;

        add_filter('wp_mail', [$this, 'log_email_before_send'], 10, 1);
        add_action('wp_mail_succeeded', [$this, 'email_sent_success']);
        add_action('wp_mail_failed', [$this, 'email_sent_failed']);
    }

    /**
     * Log email before sending (wp_mail filter).
     *
     * Pushes current context onto the stack so nested wp_mail() calls
     * don't clobber state.
     *
     * @param array $args wp_mail arguments.
     * @return array Unchanged $args.
     */
    public function log_email_before_send($args)
    {
        // Extract From header
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

        $email_data = [
            'to_email'    => is_array($args['to']) ? implode(', ', $args['to']) : $args['to'],
            'from_email'  => $from_email ?: get_option('admin_email'),
            'subject'     => isset($args['subject']) ? $args['subject'] : '',
            'message'     => isset($args['message']) ? $args['message'] : '',
            'headers'     => isset($args['headers']) ? $args['headers'] : '',
            'attachments' => isset($args['attachments']) ? $args['attachments'] : [],
            'status'      => 'pending',
        ];

        $email_id = $this->database->log_email($email_data);

        // Push onto the stack (supports nested wp_mail calls)
        $ctx = [
            'id'   => $email_id,
            'data' => $email_data,
        ];
        $this->email_stack[] = $ctx;

        // Send to Telegram immediately (don't wait for success)
        $this->send_to_telegram($ctx);

        return $args;
    }

    /**
     * Email sent successfully.
     *
     * @param array $mail_data Mail data from WordPress.
     */
    public function email_sent_success($mail_data)
    {
        $ctx = $this->pop_context();
        if (!$ctx) {
            return;
        }

        $this->database->update_email($ctx['id'], [
            'status' => 'sent',
        ]);

        // $this->send_to_telegram($ctx);
    }

    /**
     * Email send failed.
     *
     * @param WP_Error $wp_error Error object.
     */
    public function email_sent_failed($wp_error)
    {
        $ctx = $this->pop_context();
        if (!$ctx) {
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

        $this->database->update_email($ctx['id'], [
            'status'        => 'failed',
            'error_message' => $error_message,
        ]);

        // notify on failures too
        // $this->send_to_telegram($ctx, true);
    }

    /**
     * Pop the most recent email context from the stack.
     *
     * @return array|null Context with 'id' and 'data', or null if stack empty.
     */
    private function pop_context()
    {
        if (empty($this->email_stack)) {
            return null;
        }

        return array_pop($this->email_stack);
    }

    /**
     * Send notification to Telegram.
     *
     * @param array $ctx       Email context ('id' + 'data').
     * @param bool  $is_failed Whether the email delivery failed.
     */
    private function send_to_telegram($ctx, $is_failed = false)
    {
        if (!WP_Mail_To_Telegram::is_telegram_enabled()) {
            return;
        }

        $email_id   = $ctx['id'];
        $email_data = $ctx['data'];

        if (!$email_id || !$email_data) {
            return;
        }

        $result = $this->telegram->send_email($email_data, $email_id);

        $update_data = [];

        if (is_wp_error($result)) {
            $update_data['sent_to_tg'] = 0;
            $update_data['tg_error']   = $result->get_error_message();
        } elseif (isset($result['success']) && $result['success']) {
            $update_data['sent_to_tg'] = 1;
            if (isset($result['message_id'])) {
                $update_data['tg_message_id'] = $result['message_id'];
            }
        } else {
            $update_data['sent_to_tg'] = 0;
            $update_data['tg_error']   = isset($result['message']) ? $result['message'] : __('Unknown error', 'wp-mail-to-telegram');
        }

        $this->database->update_email($email_id, $update_data);
    }
}
