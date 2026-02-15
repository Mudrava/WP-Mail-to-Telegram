<?php
/**
 * Email Detail Page - View single email with code/preview modes
 */

defined('ABSPATH') || exit;

class WPMTT_Email_Detail_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'maybe_render']);
    }

    /**
     * Maybe render the page
     */
    public function maybe_render()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wpmtt-email-view') {
            add_action('admin_menu', function () {
                add_action('wpmtt-email-view', [$this, 'render']);
            });
        }
    }

    /**
     * Render the detail page
     */
    public static function render()
    {
        if (!isset($_GET['id'])) {
            wp_die(__('Email ID not provided', 'wp-mail-to-telegram'));
        }

        $id = intval($_GET['id']);
        $email = wpmtt()->database->get_email($id);

        if (!$email) {
            wp_die(__('Email not found', 'wp-mail-to-telegram'));
        }

        // Parse headers
        $headers = $email->headers;
        if (!is_array($headers)) {
            $headers = json_decode($headers, true);
            if (!is_array($headers)) {
                $headers = explode("\n", $email->headers);
            }
        }

        // Parse attachments
        $attachments = $email->attachments;
        if (!is_array($attachments)) {
            $attachments = json_decode($attachments, true);
        }
        if (!is_array($attachments)) {
            $attachments = [];
        }

        // Current view mode
        $view_mode = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'preview';
        ?>
        <div class="wrap wpmtt-wrap wpmtt-email-detail">
            <div class="wpmtt-detail-header">
                <a href="<?php echo admin_url('admin.php?page=wpmtt-log'); ?>" class="wpmtt-back-link">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php _e('Back to Log', 'wp-mail-to-telegram'); ?>
                </a>

                <h1><?php echo esc_html($email->subject ?: __('(No Subject)', 'wp-mail-to-telegram')); ?></h1>
            </div>

            <!-- Email Meta -->
            <div class="wpmtt-email-meta">
                <div class="wpmtt-meta-grid">
                    <div class="wpmtt-meta-item">
                        <span class="wpmtt-meta-label"><?php _e('From:', 'wp-mail-to-telegram'); ?></span>
                        <span
                            class="wpmtt-meta-value"><?php echo esc_html($email->from_email ?: get_option('admin_email')); ?></span>
                    </div>
                    <div class="wpmtt-meta-item">
                        <span class="wpmtt-meta-label"><?php _e('To:', 'wp-mail-to-telegram'); ?></span>
                        <span class="wpmtt-meta-value"><?php echo esc_html($email->to_email); ?></span>
                    </div>
                    <div class="wpmtt-meta-item">
                        <span class="wpmtt-meta-label"><?php _e('Date:', 'wp-mail-to-telegram'); ?></span>
                        <span
                            class="wpmtt-meta-value"><?php echo esc_html(date_i18n('M j, Y g:i a', strtotime($email->created_at))); ?></span>
                    </div>
                    <div class="wpmtt-meta-item">
                        <span class="wpmtt-meta-label"><?php _e('Email Status:', 'wp-mail-to-telegram'); ?></span>
                        <span class="wpmtt-meta-value">
                            <?php self::render_status($email->status, $email->error_message); ?>
                        </span>
                    </div>
                    <div class="wpmtt-meta-item">
                        <span class="wpmtt-meta-label"><?php _e('Telegram:', 'wp-mail-to-telegram'); ?></span>
                        <span class="wpmtt-meta-value">
                            <?php self::render_telegram_status($email->sent_to_tg, $email->tg_error); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- View Mode Tabs -->
            <div class="wpmtt-view-tabs">
                <a href="<?php echo add_query_arg('view', 'preview'); ?>"
                    class="wpmtt-tab <?php echo $view_mode === 'preview' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php _e('Preview', 'wp-mail-to-telegram'); ?>
                </a>
                <a href="<?php echo add_query_arg('view', 'code'); ?>"
                    class="wpmtt-tab <?php echo $view_mode === 'code' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php _e('Source Code', 'wp-mail-to-telegram'); ?>
                </a>
                <a href="<?php echo add_query_arg('view', 'headers'); ?>"
                    class="wpmtt-tab <?php echo $view_mode === 'headers' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-info-outline"></span>
                    <?php _e('Headers', 'wp-mail-to-telegram'); ?>
                </a>
            </div>

            <!-- Content Area -->
            <div class="wpmtt-email-content-wrapper">
                <?php if ($view_mode === 'preview'): ?>
                    <!-- Preview Mode -->
                    <div class="wpmtt-email-preview">
                        <iframe id="wpmtt-preview-iframe" sandbox=""
                            srcdoc="<?php echo esc_attr(self::prepare_preview_html($email->message)); ?>"></iframe>
                    </div>

                <?php elseif ($view_mode === 'code'): ?>
                    <!-- Code Mode -->
                    <div class="wpmtt-email-code">
                        <div class="wpmtt-code-actions">
                            <button type="button" class="button wpmtt-copy-code" data-target="wpmtt-email-source">
                                <span class="dashicons dashicons-clipboard"></span>
                                <?php _e('Copy', 'wp-mail-to-telegram'); ?>
                            </button>
                        </div>
                        <pre id="wpmtt-email-source"><code><?php echo esc_html($email->message); ?></code></pre>
                    </div>

                <?php elseif ($view_mode === 'headers'): ?>
                    <!-- Headers Mode -->
                    <div class="wpmtt-email-headers">
                        <table class="widefat">
                            <tbody>
                                <?php if (is_array($headers) && !empty($headers)): ?>
                                    <?php foreach ($headers as $key => $value): ?>
                                        <tr>
                                            <th><?php echo esc_html(is_numeric($key) ? 'Header' : $key); ?></th>
                                            <td><?php echo esc_html(is_array($value) ? implode(', ', $value) : $value); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2"><?php _e('No headers found', 'wp-mail-to-telegram'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Attachments -->
            <?php if (!empty($attachments)): ?>
                <div class="wpmtt-attachments">
                    <h3>
                        <span class="dashicons dashicons-paperclip"></span>
                        <?php _e('Attachments', 'wp-mail-to-telegram'); ?> (<?php echo count($attachments); ?>)
                    </h3>
                    <ul class="wpmtt-attachment-list">
                        <?php foreach ($attachments as $attachment): ?>
                            <li class="wpmtt-attachment-item">
                                <span class="dashicons dashicons-media-default"></span>
                                <span class="wpmtt-attachment-name"><?php echo esc_html(basename($attachment)); ?></span>
                                <?php if (file_exists($attachment)): ?>
                                    <span class="wpmtt-attachment-size">(<?php echo size_format(filesize($attachment)); ?>)</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Error Info -->
            <?php if (!empty($email->error_message) || !empty($email->tg_error)): ?>
                <div class="wpmtt-error-info">
                    <h3>
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('Error Information', 'wp-mail-to-telegram'); ?>
                    </h3>
                    <?php if (!empty($email->error_message)): ?>
                        <div class="wpmtt-error-block">
                            <strong><?php _e('Email Error:', 'wp-mail-to-telegram'); ?></strong>
                            <p><?php echo esc_html($email->error_message); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($email->tg_error)): ?>
                        <div class="wpmtt-error-block">
                            <strong><?php _e('Telegram Error:', 'wp-mail-to-telegram'); ?></strong>
                            <p><?php echo esc_html($email->tg_error); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Prepare HTML for preview iframe
     */
    private static function prepare_preview_html($content)
    {
        // Add basic styling for email preview
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    font-size: 14px;
                    line-height: 1.6;
                    color: #333;
                    padding: 20px;
                    margin: 0;
                    background: #fff;
                }
                a { color: #0073aa; }
                img { max-width: 100%; height: auto; }
                table { border-collapse: collapse; }
                td, th { padding: 8px; }
            </style>
        </head>
        <body>' . $content . '</body>
        </html>';

        return $html;
    }

    /**
     * Render status
     */
    private static function render_status($status, $error = '')
    {
        $statuses = [
            'sent' => ['label' => __('Sent', 'wp-mail-to-telegram'), 'class' => 'success', 'icon' => 'yes-alt'],
            'failed' => ['label' => __('Failed', 'wp-mail-to-telegram'), 'class' => 'error', 'icon' => 'dismiss'],
            'pending' => ['label' => __('Pending', 'wp-mail-to-telegram'), 'class' => 'warning', 'icon' => 'clock'],
        ];

        $s = isset($statuses[$status]) ? $statuses[$status] : ['label' => $status, 'class' => 'default', 'icon' => 'marker'];

        echo '<span class="wpmtt-status wpmtt-status-' . esc_attr($s['class']) . '">';
        echo '<span class="dashicons dashicons-' . esc_attr($s['icon']) . '"></span> ';
        echo esc_html($s['label']);
        echo '</span>';
    }

    /**
     * Render Telegram status
     */
    private static function render_telegram_status($sent, $error)
    {
        if ($sent) {
            echo '<span class="wpmtt-status wpmtt-status-success">';
            echo '<span class="dashicons dashicons-yes-alt"></span> ';
            echo __('Sent', 'wp-mail-to-telegram');
            echo '</span>';
        } elseif (!empty($error)) {
            echo '<span class="wpmtt-status wpmtt-status-error" title="' . esc_attr($error) . '">';
            echo '<span class="dashicons dashicons-warning"></span> ';
            echo __('Failed', 'wp-mail-to-telegram');
            echo '</span>';
        } else {
            echo '<span class="wpmtt-status wpmtt-status-default">';
            echo '<span class="dashicons dashicons-minus"></span> ';
            echo __('Not Sent', 'wp-mail-to-telegram');
            echo '</span>';
        }
    }
}

// Hook into the admin page render
add_action('admin_menu', function () {
    global $submenu;
    // The page is already registered as hidden, we just add render callback
}, 999);

add_action('wpmtt-email-view', ['WPMTT_Email_Detail_Page', 'render']);
add_action('load-admin_page_wpmtt-email-view', function () {
    // Set up any screen options here
});

// Alternative render hook
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'wpmtt-email-view') {
        add_action('admin_page_wpmtt-email-view', ['WPMTT_Email_Detail_Page', 'render']);
    }
});
