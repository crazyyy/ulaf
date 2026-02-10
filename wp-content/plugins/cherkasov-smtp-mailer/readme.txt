=== Cherkasov SMTP Mailer ===
Contributors: cherkasov1
Tags: smtp, email, gmail, telegram, log
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A reliable SMTP plugin for WordPress that ensures email delivery with logging and Telegram notifications.

== Description ==

The default `wp_mail()` function in WordPress can often be unreliable. Many hosting providers limit or poorly configure email sending, which leads to important notifications (like registrations, password resets, or WooCommerce orders) ending up in spam or not being delivered at all.

**Cherkasov SMTP Mailer** solves this problem by re-routing all your site's outgoing emails through a professional SMTP server (such as Gmail, SendGrid, or any other mail service). This significantly increases the reliability and deliverability of your emails.

The plugin features a modern, intuitive interface, an advanced email log, and the ability to receive instant notifications about sending statuses in your Telegram.

== Features ==

* **Reliable Delivery:** Send emails through any SMTP server to avoid spam filters.
* **Easy Configuration:** Quick presets for popular services (Gmail, Outlook, SendGrid) and flexible options for any other SMTP provider.
* **Email Log:** Keeps a detailed log of all sent emails. View content, status, headers, and error messages.
* **Log Management:** Search for emails, resend failed messages (one by one or all at once), and delete entries.
* **Telegram Notifications:** Get instant messages in Telegram about successfully sent or failed emails.
* **Testing Tool:** A built-in feature to send a test email to verify your settings and see a detailed debug log.
* **Security:** Passwords and tokens are not displayed in the form after being saved and are transmitted securely.
* **Modern UI:** A clean, responsive, and user-friendly interface built with modern web technologies.
* **Retention Policies:** Configure automatic deletion of old log entries to keep your database clean.

== Installation ==

= Method 1: Via WordPress Admin Panel (Recommended) =

1.  Navigate to `Plugins` > `Add New` in your WordPress dashboard.
2.  In the search field, type `Cherkasov SMTP Mailer`.
3.  Click "Install Now," and then "Activate."

= Method 2: Manual Upload =

1.  Download the plugin's ZIP archive.
2.  Go to `Plugins` > `Add New` and click the "Upload Plugin" button at the top.
3.  Select the downloaded ZIP file and click "Install Now."
4.  After installation, click "Activate."

== Configuration ==

After activating the plugin, go to **Settings > Cherkasov SMTP Mailer**.

= 1. Main SMTP Settings =

1.  **Mailer:**
    * **SMTP:** Choose this option to send emails via an external server.
    * **Default WP Mailer:** Use your host's standard mail function (not recommended).
2.  **Providers:** You can select a popular provider (Gmail, Outlook, SendGrid) to auto-fill the host and port fields.
3.  **SMTP Host, Port, and Encryption:** Enter the details provided by your email service provider.
4.  **Authentication:** In most cases, this toggle should be enabled.
5.  **SMTP Credentials:** Enter the username (usually your email address) and password for your email account.
    * **For Gmail/Google:** If you have two-factor authentication enabled, you must create an "App Password" in your Google account's security settings and use it here.
6.  **Save Changes.**

= 2. Sender Details =

* **From Name:** The name recipients will see.
* **From Email:** The address emails will be sent from.
* **Force Sender:** If enabled, all emails from your site (even from other plugins) will be sent using the specified From Name and From Email.

= 3. Telegram Notifications =

1.  Click the "Telegram Notifications" button.
2.  Enable the "Enable Telegram Notifications" option.
3.  **Bot Token:** Create a new bot by talking to [`@BotFather`](https://t.me/botfather) on Telegram. It will provide you with a unique token. Paste it here.
4.  **Chat ID:** Talk to the [`@userinfobot`](https://t.me/userinfobot) and click "Start." It will send you your unique ID. Paste it here.
5.  Choose which events you want to be notified about (failed and/or successful sends).
6.  Save the settings and click "Send Test" to verify.

== Frequently Asked Questions ==

**Q: My test emails are not being sent. What should I do?**
**A:** Please check the following:
1.  **Credentials:** Ensure the SMTP username and password are correct. For Gmail, use an "App Password."
2.  **Host and Port:** Double-check that the SMTP host, port, and encryption type (TLS/SSL) are correct.
3.  **Firewall:** Your hosting provider might be blocking outgoing connections on the selected port. Contact their support for assistance.
4.  **Debug Log:** After sending a test email, the plugin displays a detailed report. Read it carefully, as it often indicates the cause of the error.

**Q: Where can I get a Telegram Bot Token and Chat ID?**
**A:**
* **Token:** Talk to the [`@BotFather`](https://t.me/botfather) bot on Telegram, choose `/newbot`, and follow the instructions.
* **Chat ID:** Talk to the [`@userinfobot`](https://t.me/userinfobot), and it will immediately send you your ID.

**Q: Will this plugin work with WooCommerce?**
**A:** Yes, the plugin intercepts all calls to the `wp_mail()` function, so all emails from your site, including notifications from WooCommerce, will be sent through the configured SMTP server.

== Third-Party Services ==

This plugin uses the Telegram API to send notifications about the status of sent emails. This functionality is optional and only activated if you enable it in the settings and provide your own Telegram Bot Token and Chat ID.

* **Service:** Telegram Bot API
* **Data Sent:** When enabled, the plugin sends the recipient's email address, email subject, your site's URL, and the sending status (success or failure) to the Telegram API to be delivered to your specified chat.
* **Terms of Service:** https://telegram.org/tos
* **Privacy Policy:** https://telegram.org/privacy

== Changelog ==

= 1.0.0 =
* Initial release.
* **Reliable Delivery:** Configuration for any SMTP server.
* **Advanced UI:** Modern and intuitive settings page.
* **Email Logging:** Full logging of all outgoing emails with management features (resend, delete, view).
* **Telegram Notifications:** Instant alerts for email send status.
