<?php
/**
 * Documentation Page
 */

defined('ABSPATH') || exit;

class WPMTT_Documentation_Page
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Nothing to init
    }

    /**
     * Render documentation page
     */
    public static function render()
    {
        ?>
        <div class="wrap wpmtt-wrap wpmtt-docs-page">
            <h1>
                <span class="dashicons dashicons-book"></span>
                <?php _e('Documentation', 'wp-mail-to-telegram'); ?>
            </h1>

            <div class="wpmtt-docs-container">
                <!-- Sidebar Navigation -->
                <div class="wpmtt-docs-sidebar">
                    <nav class="wpmtt-docs-nav">
                        <a href="#about" class="wpmtt-docs-nav-item active"><?php _e('About', 'wp-mail-to-telegram'); ?></a>
                        <a href="#setup" class="wpmtt-docs-nav-item"><?php _e('Setup', 'wp-mail-to-telegram'); ?></a>
                        <a href="#features" class="wpmtt-docs-nav-item"><?php _e('Features', 'wp-mail-to-telegram'); ?></a>
                        <a href="#api" class="wpmtt-docs-nav-item"><?php _e('Developer API', 'wp-mail-to-telegram'); ?></a>
                        <a href="#faq" class="wpmtt-docs-nav-item"><?php _e('FAQ', 'wp-mail-to-telegram'); ?></a>
                        <a href="#support" class="wpmtt-docs-nav-item"><?php _e('Support', 'wp-mail-to-telegram'); ?></a>
                    </nav>
                </div>

                <!-- Content -->
                <div class="wpmtt-docs-content">

                    <!-- About Section -->
                    <section id="about" class="wpmtt-docs-section">
                        <h2><?php _e('About WP Mail to Telegram', 'wp-mail-to-telegram'); ?></h2>

                        <p class="wpmtt-docs-intro">
                            <?php _e('WP Mail to Telegram is a plugin that sends copies of all outgoing emails from your WordPress site directly to your Telegram. Perfect for receiving form submissions, order notifications, and system messages without having to constantly check your email inbox.', 'wp-mail-to-telegram'); ?>
                        </p>

                        <div class="wpmtt-docs-warning">
                            <span class="dashicons dashicons-warning"></span>
                            <div>
                                <strong><?php _e('Important to understand:', 'wp-mail-to-telegram'); ?></strong>
                                <p><?php _e('This plugin does NOT replace email. It only sends copies to the site administrator via Telegram. Your website users will still receive emails normally. This plugin is designed ONLY for notifying the site administrator.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>
                        </div>

                        <h3><?php _e('Perfect for:', 'wp-mail-to-telegram'); ?></h3>
                        <ul class="wpmtt-docs-list wpmtt-list-check">
                            <li><?php _e('Receiving contact form submissions', 'wp-mail-to-telegram'); ?></li>
                            <li><?php _e('Order notifications from e-commerce stores', 'wp-mail-to-telegram'); ?></li>
                            <li><?php _e('WordPress system notifications', 'wp-mail-to-telegram'); ?></li>
                            <li><?php _e('Monitoring user registrations', 'wp-mail-to-telegram'); ?></li>
                            <li><?php _e('Alerts from other plugins', 'wp-mail-to-telegram'); ?></li>
                        </ul>

                        <h3><?php _e('NOT suitable for:', 'wp-mail-to-telegram'); ?></h3>
                        <ul class="wpmtt-docs-list wpmtt-list-cross">
                            <li><?php _e('Replacing email newsletters to users', 'wp-mail-to-telegram'); ?></li>
                            <li><?php _e('Sending messages to customers via Telegram', 'wp-mail-to-telegram'); ?></li>
                            <li><?php _e('Mass email campaigns', 'wp-mail-to-telegram'); ?></li>
                        </ul>
                    </section>

                    <!-- Setup Section -->
                    <section id="setup" class="wpmtt-docs-section">
                        <h2><?php _e('Plugin Setup', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-docs-steps">
                            <div class="wpmtt-docs-step">
                                <span class="wpmtt-step-number">1</span>
                                <div class="wpmtt-step-content">
                                    <h4><?php _e('Get a Verification Code from Telegram', 'wp-mail-to-telegram'); ?></h4>
                                    <ol>
                                        <li><?php _e('Open Telegram', 'wp-mail-to-telegram'); ?></li>
                                        <li><?php _e('Find the bot', 'wp-mail-to-telegram'); ?> <a
                                                href="https://t.me/WPMailToTelegramBot" target="_blank">@WPMailToTelegramBot</a>
                                        </li>
                                        <li><?php _e('Press Start', 'wp-mail-to-telegram'); ?></li>
                                        <li><?php _e('Send the command', 'wp-mail-to-telegram'); ?> <code>/addsite</code></li>
                                        <li><?php _e('Copy the 6-digit verification code you receive', 'wp-mail-to-telegram'); ?></li>
                                    </ol>
                                </div>
                            </div>

                            <div class="wpmtt-docs-step">
                                <span class="wpmtt-step-number">2</span>
                                <div class="wpmtt-step-content">
                                    <h4><?php _e('Enter the Code in the Setup Wizard', 'wp-mail-to-telegram'); ?></h4>
                                    <p><?php _e('Enter the 6-digit verification code in the setup wizard and click Verify. That\'s it!', 'wp-mail-to-telegram'); ?>
                                    </p>
                                    <a href="<?php echo admin_url('admin.php?page=wpmtt-setup'); ?>"
                                        class="button button-primary"><?php _e('Run Setup Wizard', 'wp-mail-to-telegram'); ?></a>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Features Section -->
                    <section id="features" class="wpmtt-docs-section">
                        <h2><?php _e('Features', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-docs-features">
                            <div class="wpmtt-docs-feature">
                                <span class="dashicons dashicons-email-alt"></span>
                                <h4><?php _e('Email Log', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('All sent emails are saved in a log with the ability to view, search, and filter.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>
                            <div class="wpmtt-docs-feature">
                                <span class="dashicons dashicons-visibility"></span>
                                <h4><?php _e('Email Preview', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('View emails in preview or source code mode, including headers and attachments.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>
                            <div class="wpmtt-docs-feature">
                                <span class="dashicons dashicons-format-status"></span>
                                <h4><?php _e('Telegram Notifications', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('Instant notifications for every email directly in your Telegram with a link to the full version.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>
                            <div class="wpmtt-docs-feature">
                                <span class="dashicons dashicons-chart-bar"></span>
                                <h4><?php _e('Statistics', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('Track the number of sent emails, errors, and Telegram delivery status.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- API Section -->
                    <section id="api" class="wpmtt-docs-section">
                        <h2><?php _e('Developer API', 'wp-mail-to-telegram'); ?></h2>

                        <p><?php _e('The plugin provides a simple function to send custom messages to Telegram from your code.', 'wp-mail-to-telegram'); ?>
                        </p>

                        <h3><?php _e('Function wpmtt_send_message()', 'wp-mail-to-telegram'); ?></h3>

                        <div class="wpmtt-docs-code-block">
                            <div class="wpmtt-code-header">
                                <span><?php _e('Syntax', 'wp-mail-to-telegram'); ?></span>
                                <button type="button" class="wpmtt-copy-btn"
                                    data-target="api-syntax"><?php _e('Copy', 'wp-mail-to-telegram'); ?></button>
                            </div>
                            <pre
                                id="api-syntax"><code class="language-php">wpmtt_send_message( string $message, array $options = [] ): bool|WP_Error</code></pre>
                        </div>

                        <h4><?php _e('Parameters:', 'wp-mail-to-telegram'); ?></h4>
                        <table class="wpmtt-docs-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Parameter', 'wp-mail-to-telegram'); ?></th>
                                    <th><?php _e('Type', 'wp-mail-to-telegram'); ?></th>
                                    <th><?php _e('Description', 'wp-mail-to-telegram'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>$message</code></td>
                                    <td>string</td>
                                    <td><?php _e('The message text to send. Supports Telegram HTML formatting.', 'wp-mail-to-telegram'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>$options</code></td>
                                    <td>array</td>
                                    <td><?php _e('Additional options (optional).', 'wp-mail-to-telegram'); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <h4><?php _e('Options:', 'wp-mail-to-telegram'); ?></h4>
                        <table class="wpmtt-docs-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Key', 'wp-mail-to-telegram'); ?></th>
                                    <th><?php _e('Type', 'wp-mail-to-telegram'); ?></th>
                                    <th><?php _e('Default', 'wp-mail-to-telegram'); ?></th>
                                    <th><?php _e('Description', 'wp-mail-to-telegram'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>parse_mode</code></td>
                                    <td>string</td>
                                    <td>HTML</td>
                                    <td><?php _e('Parse mode: HTML or Markdown', 'wp-mail-to-telegram'); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <h4><?php _e('Returns:', 'wp-mail-to-telegram'); ?></h4>
                        <p><code>true</code> <?php _e('on success or', 'wp-mail-to-telegram'); ?> <code>WP_Error</code>
                            <?php _e('on failure.', 'wp-mail-to-telegram'); ?></p>

                        <h3><?php _e('Usage Examples', 'wp-mail-to-telegram'); ?></h3>

                        <div class="wpmtt-docs-code-block">
                            <div class="wpmtt-code-header">
                                <span><?php _e('Simple Message', 'wp-mail-to-telegram'); ?></span>
                                <button type="button" class="wpmtt-copy-btn"
                                    data-target="api-example-1"><?php _e('Copy', 'wp-mail-to-telegram'); ?></button>
                            </div>
                            <pre id="api-example-1"><code class="language-php">&lt;?php
                        // Send a simple message
                        wpmtt_send_message( 'Hello! This is a test message.' );
                        ?&gt;</code></pre>
                        </div>

                        <div class="wpmtt-docs-code-block">
                            <div class="wpmtt-code-header">
                                <span><?php _e('Formatted Message', 'wp-mail-to-telegram'); ?></span>
                                <button type="button" class="wpmtt-copy-btn"
                                    data-target="api-example-2"><?php _e('Copy', 'wp-mail-to-telegram'); ?></button>
                            </div>
                            <pre id="api-example-2"><code class="language-php">&lt;?php
                        // Message with HTML formatting
                        $message = "&lt;b&gt;New Inquiry!&lt;/b&gt;\n\n";
                        $message .= "Name: John Doe\n";
                        $message .= "Email: john@example.com\n";
                        $message .= "Phone: +1 234 567-8900";

                        wpmtt_send_message( $message );
                        ?&gt;</code></pre>
                        </div>

                        <div class="wpmtt-docs-code-block">
                            <div class="wpmtt-code-header">
                                <span><?php _e('WooCommerce Order Notification', 'wp-mail-to-telegram'); ?></span>
                                <button type="button" class="wpmtt-copy-btn"
                                    data-target="api-example-3"><?php _e('Copy', 'wp-mail-to-telegram'); ?></button>
                            </div>
                            <pre id="api-example-3"><code class="language-php">&lt;?php
                        // Notify on new WooCommerce order
                        add_action( 'woocommerce_new_order', function( $order_id ) {
                            $order = wc_get_order( $order_id );
    
                            $message = "&lt;b&gt;New Order #" . $order_id . "&lt;/b&gt;\n\n";
                            $message .= "Total: " . $order->get_formatted_order_total() . "\n";
                            $message .= "Customer: " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "\n";
                            $message .= "Email: " . $order->get_billing_email();
    
                            wpmtt_send_message( $message );
                        });
                        ?&gt;</code></pre>
                        </div>

                        <div class="wpmtt-docs-code-block">
                            <div class="wpmtt-code-header">
                                <span><?php _e('Error Handling', 'wp-mail-to-telegram'); ?></span>
                                <button type="button" class="wpmtt-copy-btn"
                                    data-target="api-example-4"><?php _e('Copy', 'wp-mail-to-telegram'); ?></button>
                            </div>
                            <pre id="api-example-4"><code class="language-php">&lt;?php
                        // With error handling
                        $result = wpmtt_send_message( 'My message' );

                        if ( is_wp_error( $result ) ) {
                            error_log( 'WP Mail to TG Error: ' . $result->get_error_message() );
                        } else {
                            // Message sent successfully
                        }
                        ?&gt;</code></pre>
                        </div>

                        <h3><?php _e('Supported Telegram HTML Tags', 'wp-mail-to-telegram'); ?></h3>
                        <ul class="wpmtt-docs-list">
                            <li><code>&lt;b&gt;</code> <?php _e('- bold text', 'wp-mail-to-telegram'); ?></li>
                            <li><code>&lt;i&gt;</code> <?php _e('- italic text', 'wp-mail-to-telegram'); ?></li>
                            <li><code>&lt;u&gt;</code> <?php _e('- underlined text', 'wp-mail-to-telegram'); ?></li>
                            <li><code>&lt;s&gt;</code> <?php _e('- strikethrough text', 'wp-mail-to-telegram'); ?></li>
                            <li><code>&lt;code&gt;</code> <?php _e('- monospace text', 'wp-mail-to-telegram'); ?></li>
                            <li><code>&lt;pre&gt;</code> <?php _e('- code block', 'wp-mail-to-telegram'); ?></li>
                            <li><code>&lt;a href="..."&gt;</code> <?php _e('- link', 'wp-mail-to-telegram'); ?></li>
                        </ul>
                    </section>

                    <!-- FAQ Section -->
                    <section id="faq" class="wpmtt-docs-section">
                        <h2><?php _e('Frequently Asked Questions', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-docs-faq">
                            <div class="wpmtt-faq-item">
                                <h4><?php _e('Emails are not arriving in Telegram. What should I do?', 'wp-mail-to-telegram'); ?>
                                </h4>
                                <p><?php _e('Check:', 'wp-mail-to-telegram'); ?></p>
                                <ol>
                                    <li><?php _e('Is Telegram sending enabled in the plugin settings?', 'wp-mail-to-telegram'); ?>
                                    </li>
                                    <li><?php _e('Is the plugin configured? (Settings page should show "Connected")', 'wp-mail-to-telegram'); ?>
                                    </li>
                                    <li><?php _e('Did you start the @WPMailToTelegramBot with the /start command?', 'wp-mail-to-telegram'); ?>
                                    </li>
                                    <li><?php _e('Send a test message from the plugin settings', 'wp-mail-to-telegram'); ?></li>
                                </ol>
                            </div>

                            <div class="wpmtt-faq-item">
                                <h4><?php _e('Can my customers receive emails in Telegram?', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('No. This plugin is designed only for the site administrator. Customers receive emails normally via email.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>

                            <div class="wpmtt-faq-item">
                                <h4><?php _e('Does the plugin replace email sending?', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('No. The plugin works alongside regular email sending. All emails are sent as usual, plus copies are forwarded to Telegram for the administrator.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>

                            <div class="wpmtt-faq-item">
                                <h4><?php _e('Is there a limit on the number of messages?', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('Telegram has a limit of 30 messages per second per bot. This is more than enough for a typical website.', 'wp-mail-to-telegram'); ?>
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Support Section -->
                    <section id="support" class="wpmtt-docs-section">
                        <h2><?php _e('Support', 'wp-mail-to-telegram'); ?></h2>

                        <div class="wpmtt-support-box">
                            <div class="wpmtt-support-item">
                                <span class="dashicons dashicons-admin-comments"></span>
                                <h4><?php _e('Telegram Chat', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('Ask a question in our Telegram support chat.', 'wp-mail-to-telegram'); ?></p>
                                <a href="https://t.me/mudrava_support" target="_blank"
                                    class="button"><?php _e('Open Chat', 'wp-mail-to-telegram'); ?></a>
                            </div>

                            <div class="wpmtt-support-item">
                                <span class="dashicons dashicons-email"></span>
                                <h4><?php _e('Email', 'wp-mail-to-telegram'); ?></h4>
                                <p><?php _e('Send us an email for assistance.', 'wp-mail-to-telegram'); ?></p>
                                <a href="mailto:support@mudrava.com"
                                    class="button"><?php _e('Send Email', 'wp-mail-to-telegram'); ?></a>
                            </div>
                        </div>

                        <div class="wpmtt-docs-info">
                            <p>
                                <strong><?php _e('Plugin Version:', 'wp-mail-to-telegram'); ?></strong>
                                <?php echo WPMTT_VERSION; ?><br>
                                <strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?><br>
                                <strong>PHP:</strong> <?php echo PHP_VERSION; ?>
                            </p>
                        </div>
                    </section>

                </div>
            </div>
        </div>
        <?php
    }
}

// Hook into the admin page render
add_action('wpmtt-docs', ['WPMTT_Documentation_Page', 'render']);
add_action('admin_page_wpmtt-docs', ['WPMTT_Documentation_Page', 'render']);

// Alternative hook
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'wpmtt-docs') {
        add_action('mail-to-tg_page_wpmtt-docs', ['WPMTT_Documentation_Page', 'render']);
    }
});
