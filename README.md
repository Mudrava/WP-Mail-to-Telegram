# WP Mail to Telegram

WordPress plugin that sends copies of all outgoing emails to Telegram.

## Description

WP Mail to Telegram intercepts all emails sent through WordPress `wp_mail()` function and forwards them to a specified Telegram chat. This allows site administrators to receive instant notifications about form submissions, order alerts, system notifications, and any other email communication without relying solely on email delivery.

**Important:** This plugin does NOT replace email functionality. It only provides an additional notification channel for administrators. All emails are still sent to their original recipients as usual.

## Requirements

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Active Telegram account

## Installation

1. Upload the `wp-mail-to-telegram` folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins menu in WordPress
3. Complete the setup wizard that appears after activation

## Setup

### Step 1: Get a Verification Code

1. Open Telegram
2. Find the bot [@WPMailToTelegramBot](https://t.me/WPMailToTelegramBot)
3. Press Start
4. Send `/addsite`
5. Copy the 6-digit verification code

### Step 2: Enter the Code

Enter the verification code in the plugin setup wizard — done!

## Features

### Email Log

All emails sent from your WordPress site are logged with the following information:

* Sender and recipient addresses
* Subject line
* Full email content (HTML and plain text)
* Headers
* Attachments list
* Delivery status
* Telegram forwarding status

### Settings

* Enable/disable Telegram notifications
* Configure log retention period
* Test connection
* Send test emails
* Clear logs
* Reset all settings

### Developer API

Send custom messages to Telegram from your code:

```php
// Simple message
wpmtt_send_message( 'Your message text' );

// Message with HTML formatting
$message = '<b>Order Received</b>' . "\n";
$message .= 'Order ID: 12345' . "\n";
$message .= 'Total: $99.00';
wpmtt_send_message( $message );

// Check if plugin is configured
if ( wpmtt_is_configured() ) {
    // Plugin is ready to use
}

// Get email statistics
$stats = wpmtt_get_stats();
```

#### Available Functions

| Function | Returns | Description |
|----------|---------|-------------|
| `wpmtt_send_message( $message, $options )` | `bool` or `WP_Error` | Send custom message to Telegram |
| `wpmtt_is_configured()` | `bool` | Check if plugin setup is complete |
| `wpmtt_is_enabled()` | `bool` | Check if Telegram sending is enabled |
| `wpmtt_get_stats()` | `array` | Get email log statistics |
| `wpmtt_get_recent_emails( $limit )` | `array` | Get recent emails from log |
| `wpmtt_version()` | `string` | Get plugin version |

## Supported HTML Tags in Telegram

Telegram supports limited HTML formatting:

* `<b>` bold text
* `<i>` italic text
* `<u>` underlined text
* `<s>` strikethrough text
* `<code>` monospace text
* `<pre>` code block
* `<a href="...">` link

## File Structure

```
wp-mail-to-telegram/
├── wp-mail-to-telegram.php    Main plugin file
├── index.php                  Security file
├── README.md                  This file
├── includes/
│   ├── class-database.php         Database operations
│   ├── class-api-client.php       External API communication
│   ├── class-telegram-sender.php  Message formatting
│   ├── class-email-logger.php     Email interception
│   ├── class-admin-pages.php      Admin menu registration
│   ├── class-setup-wizard.php     Initial configuration
│   ├── class-email-log-page.php   Email log display
│   ├── class-email-detail-page.php Single email view
│   ├── class-settings-page.php    Settings management
│   ├── class-documentation-page.php In-app documentation
│   └── class-developer-api.php    Public API functions
└── assets/
    ├── css/
    │   └── admin.css              Admin interface styles
    └── js/
        └── admin.js               Admin interface scripts
```

## Database

The plugin creates one custom table:

**Table:** `{prefix}_wpmtt_email_log`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| to_email | VARCHAR(500) | Recipient address |
| from_email | VARCHAR(255) | Sender address |
| subject | TEXT | Email subject |
| message | LONGTEXT | Email body |
| headers | TEXT | Email headers |
| attachments | TEXT | Serialized attachments |
| status | VARCHAR(20) | sent, failed, pending |
| error_message | TEXT | Error details if failed |
| sent_to_tg | TINYINT | Telegram delivery flag |
| tg_message_id | VARCHAR(100) | Telegram message ID |
| tg_error | TEXT | Telegram error if any |
| created_at | DATETIME | Log timestamp |

## Uninstallation

When the plugin is deleted through WordPress admin:

* All plugin options are removed
* The email log table is dropped
* Scheduled cleanup tasks are cleared

## Support

For support and inquiries, contact us at [mudrava.com](https://mudrava.com)

## License

GPLv3 or later

## Author

[Mudrava](https://mudrava.com)
