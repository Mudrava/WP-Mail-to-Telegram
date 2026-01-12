<?php
/**
 * Telegram message sender
 */

defined('ABSPATH') || exit;

class WPMTT_Telegram_Sender
{

    /**
     * API Client
     */
    private $api_client;

    /**
     * Max message length for Telegram
     */
    const MAX_MESSAGE_LENGTH = 4000;

    /**
     * Constructor
     */
    public function __construct($api_client)
    {
        $this->api_client = $api_client;
    }

    /**
     * Send email notification to Telegram
     */
    public function send_email($email_data, $email_id)
    {
        if (!WP_Mail_To_Telegram::is_telegram_enabled()) {
            return false;
        }

        // Format message
        $message = $this->format_email_message($email_data, $email_id);

        // Get view URL
        $view_url = admin_url('admin.php?page=wpmtt-email-view&id=' . $email_id);

        $data = [
            'email_id' => $email_id,
            'to' => is_array($email_data['to']) ? implode(', ', $email_data['to']) : $email_data['to'],
            'from' => $email_data['from'],
            'subject' => $email_data['subject'],
            'message' => $message,
            'view_url' => $view_url,
            'attachments' => $email_data['attachments'],
        ];

        $result = $this->api_client->send_email_notification($data);

        return $result;
    }

    /**
     * Send custom message
     */
    public function send_custom_message($message, $options = [])
    {
        if (!WP_Mail_To_Telegram::is_configured()) {
            return new WP_Error('not_configured', __('Plugin is not configured', 'wp-mail-to-telegram'));
        }

        // Truncate if too long
        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            $message = mb_substr($message, 0, self::MAX_MESSAGE_LENGTH - 50) . "\n\n[Message truncated...]";
        }

        return $this->api_client->send_custom_message($message, $options);
    }

    /**
     * Format email for Telegram
     */
    private function format_email_message($email_data, $email_id)
    {
        $site_name = get_bloginfo('name');
        $view_url = admin_url('admin.php?page=wpmtt-email-view&id=' . $email_id);

        $to = is_array($email_data['to']) ? implode(', ', $email_data['to']) : $email_data['to'];

        // Build message
        $lines = [];
        $lines[] = "<b>New Email from Website</b>";
        $lines[] = "Site: " . esc_html($site_name);
        $lines[] = "";
        $lines[] = "<b>To:</b> " . esc_html($to);

        if (!empty($email_data['from'])) {
            $lines[] = "<b>From:</b> " . esc_html($email_data['from']);
        }

        $lines[] = "<b>Subject:</b> " . esc_html($email_data['subject']);
        $lines[] = "";

        // Message preview
        $content = $this->strip_html_for_telegram($email_data['message']);
        $max_content_length = self::MAX_MESSAGE_LENGTH - 500; // Reserve space for metadata

        if (mb_strlen($content) > $max_content_length) {
            $content = mb_substr($content, 0, $max_content_length) . "...";
        }

        $lines[] = "<b>Content:</b>";
        $lines[] = $content;
        $lines[] = "";

        // Attachments
        if (!empty($email_data['attachments'])) {
            $count = is_array($email_data['attachments']) ? count($email_data['attachments']) : 1;
            $lines[] = "Attachments: " . $count;
            $lines[] = "";
        }

        $lines[] = "<a href=\"" . esc_url($view_url) . "\">View Full Email</a>";

        return implode("\n", $lines);
    }

    /**
     * Strip HTML for Telegram message
     */
    private function strip_html_for_telegram($html)
    {
        // Convert common tags
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        $html = preg_replace('/<\/div>/i', "\n", $html);
        $html = preg_replace('/<\/li>/i', "\n", $html);
        $html = preg_replace('/<li>/i', "- ", $html);

        // Convert links
        $html = preg_replace('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', '$2 ($1)', $html);

        // Strip remaining tags
        $text = strip_tags($html);

        // Clean up whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        // Escape HTML entities for Telegram
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        return $text;
    }
}
