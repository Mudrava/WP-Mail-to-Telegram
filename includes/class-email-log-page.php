<?php
/**
 * Email Log Page - List of all logged emails
 */

defined('ABSPATH') || exit;

class WPMTT_Email_Log_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'process_actions']);
    }

    /**
     * Process bulk actions
     */
    public function process_actions()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wpmtt-log') {
            return;
        }

        // Delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            check_admin_referer('wpmtt_delete_email');

            $id = intval($_GET['id']);
            wpmtt()->database->delete_emails($id);

            wp_redirect(admin_url('admin.php?page=wpmtt-log&deleted=1'));
            exit;
        }

        // Bulk delete
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['email_ids'])) {
            check_admin_referer('wpmtt_bulk_action');

            $ids = array_map('intval', $_POST['email_ids']);
            wpmtt()->database->delete_emails($ids);

            wp_redirect(admin_url('admin.php?page=wpmtt-log&deleted=' . count($ids)));
            exit;
        }
    }

    /**
     * Render the log page
     */
    public static function render()
    {
        $database = wpmtt()->database;

        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

        // Filters
        $args = [
            'per_page' => $per_page,
            'page' => $current_page,
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'search' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
        ];

        $emails = $database->get_emails($args);
        $total = $database->get_total_count($args);
        $total_pages = ceil($total / $per_page);

        // Stats
        $stats = $database->get_stats();
        ?>
        <div class="wrap wpmtt-wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-email-alt"></span>
                <?php _e('Email Log', 'wp-mail-to-telegram'); ?>
            </h1>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php printf(__('Deleted %d email(s)', 'wp-mail-to-telegram'), intval($_GET['deleted'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="wpmtt-stats-cards">
                <div class="wpmtt-stat-card">
                    <span class="wpmtt-stat-number"><?php echo number_format_i18n($stats['total']); ?></span>
                    <span class="wpmtt-stat-label"><?php _e('Total Emails', 'wp-mail-to-telegram'); ?></span>
                </div>
                <div class="wpmtt-stat-card wpmtt-stat-success">
                    <span class="wpmtt-stat-number"><?php echo number_format_i18n($stats['sent']); ?></span>
                    <span class="wpmtt-stat-label"><?php _e('Sent', 'wp-mail-to-telegram'); ?></span>
                </div>
                <div class="wpmtt-stat-card wpmtt-stat-error">
                    <span class="wpmtt-stat-number"><?php echo number_format_i18n($stats['failed']); ?></span>
                    <span class="wpmtt-stat-label"><?php _e('Failed', 'wp-mail-to-telegram'); ?></span>
                </div>
                <div class="wpmtt-stat-card wpmtt-stat-telegram">
                    <span class="wpmtt-stat-number"><?php echo number_format_i18n($stats['sent_to_tg']); ?></span>
                    <span class="wpmtt-stat-label"><?php _e('To Telegram', 'wp-mail-to-telegram'); ?></span>
                </div>
                <div class="wpmtt-stat-card">
                    <span class="wpmtt-stat-number"><?php echo number_format_i18n($stats['today']); ?></span>
                    <span class="wpmtt-stat-label"><?php _e('Today', 'wp-mail-to-telegram'); ?></span>
                </div>
            </div>

            <!-- Filters -->
            <div class="wpmtt-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wpmtt-log">

                    <div class="wpmtt-filter-group">
                        <select name="status">
                            <option value=""><?php _e('All Statuses', 'wp-mail-to-telegram'); ?></option>
                            <option value="sent" <?php selected($args['status'], 'sent'); ?>>
                                <?php _e('Sent', 'wp-mail-to-telegram'); ?></option>
                            <option value="failed" <?php selected($args['status'], 'failed'); ?>>
                                <?php _e('Failed', 'wp-mail-to-telegram'); ?></option>
                            <option value="pending" <?php selected($args['status'], 'pending'); ?>>
                                <?php _e('Pending', 'wp-mail-to-telegram'); ?></option>
                        </select>

                        <input type="search" name="s" value="<?php echo esc_attr($args['search']); ?>"
                            placeholder="<?php _e('Search...', 'wp-mail-to-telegram'); ?>">

                        <button type="submit" class="button"><?php _e('Filter', 'wp-mail-to-telegram'); ?></button>

                        <?php if (!empty($args['status']) || !empty($args['search'])): ?>
                            <a href="<?php echo admin_url('admin.php?page=wpmtt-log'); ?>"
                                class="button"><?php _e('Reset', 'wp-mail-to-telegram'); ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Email Table -->
            <form method="post" action="">
                <?php wp_nonce_field('wpmtt_bulk_action'); ?>

                <div class="wpmtt-table-actions">
                    <select name="action">
                        <option value=""><?php _e('Bulk Actions', 'wp-mail-to-telegram'); ?></option>
                        <option value="delete"><?php _e('Delete', 'wp-mail-to-telegram'); ?></option>
                    </select>
                    <button type="submit" class="button"
                        onclick="return confirm(wpmtt.strings.confirm_delete);"><?php _e('Apply', 'wp-mail-to-telegram'); ?></button>
                </div>

                <table class="wp-list-table widefat fixed striped wpmtt-email-table">
                    <thead>
                        <tr>
                            <th class="check-column"><input type="checkbox" id="wpmtt-select-all"></th>
                            <th class="column-date"><?php _e('Date', 'wp-mail-to-telegram'); ?></th>
                            <th class="column-to"><?php _e('To', 'wp-mail-to-telegram'); ?></th>
                            <th class="column-subject"><?php _e('Subject', 'wp-mail-to-telegram'); ?></th>
                            <th class="column-status"><?php _e('Email', 'wp-mail-to-telegram'); ?></th>
                            <th class="column-telegram"><?php _e('Telegram', 'wp-mail-to-telegram'); ?></th>
                            <th class="column-actions"><?php _e('Actions', 'wp-mail-to-telegram'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($emails)): ?>
                            <tr>
                                <td colspan="7" class="wpmtt-no-items">
                                    <span class="dashicons dashicons-email"></span>
                                    <p><?php _e('No emails found yet', 'wp-mail-to-telegram'); ?></p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($emails as $email): ?>
                                <tr>
                                    <td class="check-column">
                                        <input type="checkbox" name="email_ids[]" value="<?php echo esc_attr($email->id); ?>">
                                    </td>
                                    <td class="column-date">
                                        <span
                                            class="wpmtt-date"><?php echo esc_html(date_i18n('M j, Y', strtotime($email->created_at))); ?></span>
                                        <span
                                            class="wpmtt-time"><?php echo esc_html(date_i18n('g:i a', strtotime($email->created_at))); ?></span>
                                    </td>
                                    <td class="column-to">
                                        <span
                                            class="wpmtt-email-address"><?php echo esc_html(wp_trim_words($email->to_email, 5)); ?></span>
                                    </td>
                                    <td class="column-subject">
                                        <a href="<?php echo admin_url('admin.php?page=wpmtt-email-view&id=' . $email->id); ?>"
                                            class="wpmtt-subject-link">
                                            <?php echo esc_html($email->subject ?: __('(No Subject)', 'wp-mail-to-telegram')); ?>
                                        </a>
                                    </td>
                                    <td class="column-status">
                                        <?php self::render_status_badge($email->status); ?>
                                    </td>
                                    <td class="column-telegram">
                                        <?php self::render_telegram_status($email->sent_to_tg, $email->tg_error); ?>
                                    </td>
                                    <td class="column-actions">
                                        <a href="<?php echo admin_url('admin.php?page=wpmtt-email-view&id=' . $email->id); ?>"
                                            class="button button-small" title="<?php _e('View', 'wp-mail-to-telegram'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wpmtt-log&action=delete&id=' . $email->id), 'wpmtt_delete_email'); ?>"
                                            class="button button-small wpmtt-delete-btn"
                                            title="<?php _e('Delete', 'wp-mail-to-telegram'); ?>"
                                            onclick="return confirm(wpmtt.strings.confirm_delete);">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="wpmtt-pagination">
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'current' => $current_page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render status badge
     */
    private static function render_status_badge($status)
    {
        $badges = [
            'sent' => ['label' => __('Sent', 'wp-mail-to-telegram'), 'class' => 'success'],
            'failed' => ['label' => __('Failed', 'wp-mail-to-telegram'), 'class' => 'error'],
            'pending' => ['label' => __('Pending', 'wp-mail-to-telegram'), 'class' => 'warning'],
        ];

        $badge = isset($badges[$status]) ? $badges[$status] : ['label' => $status, 'class' => 'default'];

        echo '<span class="wpmtt-badge wpmtt-badge-' . esc_attr($badge['class']) . '">' . esc_html($badge['label']) . '</span>';
    }

    /**
     * Render Telegram status
     */
    private static function render_telegram_status($sent, $error)
    {
        if ($sent) {
            echo '<span class="wpmtt-tg-status wpmtt-tg-sent" title="' . __('Sent to Telegram', 'wp-mail-to-telegram') . '"><span class="dashicons dashicons-yes-alt"></span></span>';
        } elseif (!empty($error)) {
            echo '<span class="wpmtt-tg-status wpmtt-tg-error" title="' . esc_attr($error) . '"><span class="dashicons dashicons-warning"></span></span>';
        } else {
            echo '<span class="wpmtt-tg-status wpmtt-tg-pending"><span class="dashicons dashicons-minus"></span></span>';
        }
    }
}

// Register render callback
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'wpmtt-log') {
        add_action('wpmtt_render_log_page', ['WPMTT_Email_Log_Page', 'render']);
        add_action('load-toplevel_page_wpmtt-log', function () {
            // Add screen options, etc.
        });
    }
});

// Hook into the admin page render
add_action('toplevel_page_wpmtt-log', ['WPMTT_Email_Log_Page', 'render']);
