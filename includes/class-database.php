<?php
/**
 * Database handler for email logging
 */

defined('ABSPATH') || exit;

class WPMTT_Database
{

    /**
     * Table name
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpmtt_email_log';
    }

    /**
     * Get table name
     */
    public function get_table_name()
    {
        return $this->table_name;
    }

    /**
     * Create database tables
     */
    public function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            to_email VARCHAR(500) NOT NULL,
            from_email VARCHAR(255) DEFAULT '',
            subject VARCHAR(500) DEFAULT '',
            message LONGTEXT,
            headers TEXT,
            attachments TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            error_message TEXT,
            sent_to_tg TINYINT(1) DEFAULT 0,
            tg_message_id VARCHAR(100) DEFAULT '',
            tg_error TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at),
            KEY sent_to_tg (sent_to_tg)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Store DB version
        update_option('wpmtt_db_version', WPMTT_VERSION);
    }

    /**
     * Log an email
     */
    public function log_email($data)
    {
        global $wpdb;

        $defaults = [
            'to_email' => '',
            'from_email' => '',
            'subject' => '',
            'message' => '',
            'headers' => '',
            'attachments' => '',
            'status' => 'pending',
            'error_message' => '',
            'sent_to_tg' => 0,
            'tg_message_id' => '',
            'tg_error' => '',
            'created_at' => current_time('mysql'),
        ];

        $data = wp_parse_args($data, $defaults);

        // Serialize arrays
        if (is_array($data['attachments'])) {
            $data['attachments'] = json_encode($data['attachments']);
        }
        if (is_array($data['headers'])) {
            $data['headers'] = json_encode($data['headers']);
        }
        if (is_array($data['to_email'])) {
            $data['to_email'] = implode(', ', $data['to_email']);
        }

        $result = $wpdb->insert(
            $this->table_name,
            $data,
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update email log entry
     */
    public function update_email($id, $data)
    {
        global $wpdb;

        return $wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id],
            null,
            ['%d']
        );
    }

    /**
     * Get emails with pagination
     */
    public function get_emails($args = [])
    {
        global $wpdb;

        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'status' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(subject LIKE %s OR to_email LIKE %s OR message LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }

        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }

        $where_sql = implode(' AND ', $where);

        // Sanitize orderby
        $allowed_orderby = ['id', 'to_email', 'subject', 'status', 'created_at', 'sent_to_tg'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($args['page'] - 1) * $args['per_page'];

        $query = "SELECT * FROM {$this->table_name} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $values[] = $args['per_page'];
        $values[] = $offset;

        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get total count
     */
    public function get_total_count($args = [])
    {
        global $wpdb;

        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(subject LIKE %s OR to_email LIKE %s OR message LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        $where_sql = implode(' AND ', $where);

        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}";

        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }

        return (int) $wpdb->get_var($query);
    }

    /**
     * Get single email
     */
    public function get_email($id)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }

    /**
     * Delete emails
     */
    public function delete_emails($ids)
    {
        global $wpdb;

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)",
            $ids
        ));
    }

    /**
     * Delete all emails
     */
    public function delete_all_emails()
    {
        global $wpdb;
        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }

    /**
     * Clear old logs
     */
    public function clear_old_logs($days = 30)
    {
        global $wpdb;

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < %s",
            $date
        ));
    }

    /**
     * Get statistics
     */
    public function get_stats()
    {
        global $wpdb;

        $stats = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'sent_to_tg' => 0,
            'today' => 0,
        ];

        // Total
        $stats['total'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        // By status
        $stats['sent'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'sent'");
        $stats['failed'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'failed'");

        // Sent to TG
        $stats['sent_to_tg'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE sent_to_tg = 1");

        // Today
        $today = current_time('Y-m-d');
        $stats['today'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE DATE(created_at) = %s",
            $today
        ));

        return $stats;
    }
}
