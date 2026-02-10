=== Authority Mailer SMTP – WordPress SMTP Plugin to Fix Emails, Spam & Deliverability ===
Contributors: authorityplugins
Tags: wordpress smtp, smtp plugin, email deliverability, email logs, wordpress email
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fix WordPress emails not sending or going to spam. Reliable SMTP plugin with Gmail, Outlook, SendGrid & email logs.

== Description ==

**Authority Mailer SMTP** is a **WordPress SMTP plugin** designed to fix **WordPress emails not sending**, improve email deliverability and spam issues.

By default, WordPress sends emails using PHP mail, which often fails or sends emails to spam. Authority Mailer SMTP replaces PHP mail with secure SMTP or API-based delivery, ensuring emails from WordPress, WooCommerce, contact forms, and membership plugins reach inboxes reliably.

Whether you run a WooCommerce store, membership site, or business website, Authority Mailer SMTP ensures critical emails are delivered reliably.

Authority Mailer SMTP is lightweight, focused, and avoids unnecessary bloat found in all-in-one email plugins.

==  WordPress SMTP Plugin ==

Authority Mailer SMTP solves these issues by routing all WordPress emails through trusted SMTP providers with proper authentication and delivery tracking.

== ✔ Fix WordPress Emails Not Sending ==
If your WordPress site is not sending emails, common causes include:
- Hosting servers blocking PHP mail
- Shared IP addresses being blacklisted
- Missing SPF, DKIM, or DMARC authentication
- No visibility into failed email delivery


== ✔ Improve Email Deliverability & Avoid Spam ==
Authority Mailer SMTP improves inbox placement by:
- Sending emails via authenticated SMTP or APIs
- Supporting DKIM, SPF, and DMARC alignment
- Using trusted email infrastructure instead of shared hosting IPs
- Providing clear delivery status through email logs

This dramatically reduces spam placement and silent email failures.

== ✔ Email Logging & Monitoring ==

Know exactly what happens to every email sent from your site:
- View sent and failed emails
- Inspect error messages for debugging
- Search and filter email history
- Export logs for audits or compliance

No guessing. No lost emails.

== 🚀 Supported SMTP & Email Providers ==

Authority Mailer SMTP supports all major email providers:

- **Gmail & Google Workspace (OAuth2)** - Use your Gmail account with OAuth2
- **Outlook / Microsoft 365** - Enterprise-grade email
- **SendGrid** - High-volume transactional email provider
- **Mailgun** - Powerful API with excellent documentation
- **Postmark** - Transactional email specialists
- **MailerSend** - European GDPR-compliant option
- **Elastic Email** - High-volume, low-cost solution
- **Mailjet** - Proven reliability and compliance
- **Mandrill** - Mailchimp's transactional service
- **Zoho Mail** - Integrated with Zoho Suite
- **SMTP2GO** - Simple, reliable relay service
- **SparkPost** - Industry standard with analytics
- **Custom SMTP Servers** - Use your own mail server

Choose the provider that fits your volume, budget, and reliability needs.

== Free vs Pro Version ==

Authority Mailer SMTP is fully functional in the free version and is suitable for most WordPress sites.

For advanced use cases, the **Pro version** adds additional features designed for businesses, agencies, and high-volume websites.

**Free Version Includes:**
- Secure SMTP and API-based email delivery
- Support for major email providers
- Easy setup wizard
- Test email functionality
- Basic email logging

**Authority Mailer SMTP Pro Adds:**
- Extended email logs and advanced diagnostics
- Email resend and retry functionality
- Multi-provider failover support
- Enhanced reporting and delivery insights
- Advanced configuration options for power users
- Webhook support for supported API providers
- Customization options for WordPress email templates
- Priority support

[You can learn more about the Pro version here:](https://www.authorityplugins.com/products/authority-mailer-smtp)

== Key Features ==

**⚙️ Easy Setup Wizard**
- Step-by-step configuration with visual guidance
- No technical knowledge required
- Provider-specific authentication helpers
- Automatic credential validation

**📊 Email Logging & Monitoring**
- Track every email sent from your WordPress site
- Monitor delivery status in real-time
- Search and filter email history
- Debug failed deliveries with error logs
- Export email logs for compliance and auditing

**✉️ Test Email Functionality**
- Send test emails before going live
- Verify provider configuration instantly
- Test with different email templates
- Confirm OAuth2 connections are working

**🔒 Enterprise Security**
- OAuth2 support for Gmail and Microsoft 365
- Secure credential storage in WordPress database
- API keys never exposed in logs or error messages
- HTTPS/TLS encryption for all connections
- No data sent to third-party servers except your chosen provider
- Designed to work with GDPR-compliant email providers


**🎯 Advanced Configuration**
- Custom SMTP server support
- Multiple provider configuration
- Sender name and email customization
- From address aliasing
- Header manipulation support

== How Authority Mailer SMTP Works ==

1. **Install & Activate** - Upload the plugin and activate it
2. **Choose Your Provider** - Select from 17+ email services or use custom SMTP
3. **Configure Credentials** - Enter API keys or authentication details
4. **Send Test Email** - Verify your setup before production
5. **Emails Route Automatically** - All WordPress emails now send via your provider

== Email Delivery Problems It Solves ==

**Problem:  Lost Emails**
- WordPress emails disappear without notification
- *Solution: * Professional SMTP ensures delivery confirmation and tracking

**Problem: Spam Folder Delivery**
- Your legitimate emails get marked as spam
- *Solution:* Professional providers have established sender reputations and DKIM/SPF/DMARC authentication

**Problem: No Delivery Insight**
- You don't know if emails actually sent
- *Solution:* Email logging shows exact delivery status

**Problem: High Bounce Rates**
- Invalid email formats cause bounces
- *Solution:* Professional providers validate and handle invalid addresses

**Problem: Server Blacklisting**
- Your shared server gets blacklisted for spam
- *Solution:* Use enterprise infrastructure not shared with spammers

== Perfect For ==

✅ **E-commerce Sites** - Order confirmations, shipping notifications, refund alerts
✅ **Membership Platforms** - Welcome emails, password resets, membership notifications
✅ **WordPress Communities** - User registration, comment notifications, admin alerts
✅ **Agency Websites** - Client notifications, project updates, delivery reports
✅ **Online Courses** - Lesson notifications, enrollment confirmations, certificate delivery
✅ **SaaS Platforms** - User onboarding, subscription alerts, usage reports
✅ **Real Estate Sites** - Property inquiries, showings alerts, offer notifications
✅ **Healthcare Portals** - Patient notifications (HIPAA compliant with SMTP)
✅ **Nonprofit Organizations** - Donation receipts, campaign updates, volunteer communications
✅ **WooCommerce Stores** - Product alerts, customer service messages, review requests

== Security & Privacy ==

Authority Mailer SMTP is designed with privacy and security in mind:

**🔐 Credential Protection**
- API keys and passwords stored encrypted in WordPress database
- Credentials never transmitted to external servers
- Secure credential hashing with WordPress functions

**🌐 Data Transmission**
- All connections use HTTPS/TLS encryption
- Email content only sent to your chosen provider
- No data logging on Authority Mailer servers
- Direct provider communication without intermediaries

**✅ Compliance**
- Designed to work with GDPR- and SOC 2–compliant email providers.

**📋 Permission Model**
- Explicit OAuth2 authorization for Gmail/Office 365
- Never stores passwords for sensitive accounts
- Transparent permission requesting
- Easy revocation through provider settings

== SMTP Configuration Guide ==

SMTP (Simple Mail Transfer Protocol) is the standard for sending emails. Here's what you need to know:

**SMTP vs API Methods:**
- **SMTP:** Traditional protocol, works everywhere, slightly slower
- **API:** Faster, more reliable, provider-specific
- Most providers support both; Authority Mailer uses the faster API when available

**Essential SMTP Settings:**
- **Host:** Server address (e.g., smtp.sendgrid.net)
- **Port:** Usually 587 (TLS) or 465 (SSL)
- **Username:** Usually email or account ID
- **Password:** API key or account password
- **Encryption:** TLS (port 587) preferred over SSL

**Troubleshooting Connection Issues:**
1. Verify credentials are correct
2. Check firewall allows outbound SMTP traffic
3. Ensure port 587 or 465 is not blocked
4. Test with Authority Mailer's test email feature
5. Check email logs for specific error messages

== Email Delivery Best Practices ==

Authority Mailer SMTP makes email delivery easy, but following best practices ensures maximum inbox placement:

**Sender Reputation**
- Send only to opted-in recipients
- Honor unsubscribe requests promptly
- Monitor bounce and complaint rates
- Use consistent "From" address

**Authentication Protocols**
- Enable DKIM signing (provider-managed)
- Configure SPF records in DNS
- Implement DMARC policy
- Use domain alignment for authentication

**Email Content**
- Avoid spam trigger words
- Balance text and images
- Include physical address (CAN-SPAM compliance)
- Provide clear unsubscribe link

**List Management**
- Remove hard bounces immediately
- Suppress repeat complainers
- Segment by engagement level
- Clean lists before migrations

== Frequently Asked Questions ==

= Which email provider should I choose? =
Choose based on your needs:
- **Budget:** Gmail / Google Mail
- **Reliability:** Postmark or SendGrid
- **Flexibility:** Mailgun
- **Volume:** Amazon SES
- **Enterprise:** SendGrid or Office 365

= Is my email data secure? =
Yes, completely. Your data is:
- Encrypted in transit (HTTPS/TLS)
- Encrypted at rest in your WordPress database
- Never shared with third parties
- Only sent to your chosen provider
- Never logged by Authority Mailer

If you’re looking for a simpler alternative to complex SMTP plugins, Authority Mailer SMTP focuses on reliability and clarity without aggressive upsells.

= Can I switch providers later? =
Yes!  Authority Mailer lets you change providers anytime:
1. Go to Settings
2. Choose new provider
3. Enter new credentials
4. Future emails use the new provider

= Will this work with existing WordPress plugins? =
Yes!  Authority Mailer hooks into WordPress's `wp_mail()` function, so all plugins automatically use your configured provider.

= What about WooCommerce emails? =
Perfect! WooCommerce emails use `wp_mail()`, so they'll automatically route through your SMTP provider.

= Can I use multiple email addresses? =
Yes, configure multiple "From" addresses in settings. Authority Mailer supports:
- Noreply emails
- Support team emails
- Billing emails
- Admin notifications

= What if emails still go to spam? =
This is usually a sender reputation issue, not Authority Mailer. Solutions:
1. Set up DKIM/SPF/DMARC on your domain
2. Monitor bounce rates (keep below 5%)
3. Monitor complaint rates (keep below 0.1%)
4. Warm up new sending IP addresses
5. Use provider's authentication tools

= Can I see which emails failed?
Yes! Email Logging shows:
- Delivery status (sent, failed, bounced, complained)
- Exact error messages
- Recipient address
- Subject line
- Timestamp
- Provider response

= Is there a free version? =
Authority Mailer SMTP is available in Free and Premium editions with different feature sets.

= How many emails can I send?
Depends on your provider:
- **Gmail:** Daily limit based on account type
- **Mailgun, SendGrid, etc.:** Based on your plan

= Does it log all emails? =
Yes, you can enable logging for all emails or filter by:
- Date range
- Recipient
- Status
- Provider
- Subject keywords

= Can I delete logged emails? =
Yes, bulk delete options available:
- Delete by date range
- Delete by status
- Delete all logs (with confirmation)


== Installation ==

= Automatic Installation =
1. Log in to WordPress admin panel
2. Go to Plugins > Add New
3. Search "Authority Mailer SMTP"
4. Click Install Now
5. Click Activate

= Manual Installation =
1. Download the plugin ZIP file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the downloaded ZIP file
4. Click Install Now
5. Click Activate

= First Steps After Installation =
1. Go to **Authority Mailer SMTP** in WordPress admin menu
2. Click **Setup Wizard**
3. Choose your email provider
4. Enter authentication credentials
5. Click **Send Test Email** to verify
6. You're done!  Emails now route through your provider

= Activation Troubleshooting =
If plugin doesn't appear in menu:
- Ensure PHP 7.4+ (check with hosting)
- Check plugin error logs
- Try deactivating conflicting plugins
- Contact support with error details

== External Services ==

Authority Mailer SMTP connects to external email services to deliver emails from your WordPress site. We respect your privacy and are transparent about data usage.

**Important:** Data is ONLY sent to the provider you explicitly choose during setup. Other providers receive no data.

= SendLayer =
- **Purpose:** Email delivery via SendLayer SMTP service
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND SendLayer is configured
- **Provider:** SendLayer
- **Privacy:** [https://sendlayer.com/privacy-policy](https://sendlayer.com/privacy-policy)
- **Terms:** [https://sendlayer.com/terms-of-service](https://sendlayer.com/terms-of-service)

= Elastic Email =
- **Purpose:** Transactional email delivery
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND Elastic Email is configured
- **Provider:** Elastic Email Inc.
- **Privacy:** [https://elasticemail.com/resources/usage-policies/privacy-policy](https://elasticemail.com/resources/usage-policies/privacy-policy)
- **Terms:** [https://elasticemail.com/resources/usage-policies/terms-of-use](https://elasticemail.com/resources/usage-policies/terms-of-use)

= Google Gmail API =
- **Purpose:** Send emails through Gmail account
- **Data:** Email content, headers, recipients, OAuth2 tokens
- **When:** Only when sending AND Gmail is configured
- **Provider:** Google LLC
- **Privacy:** [https://policies.google.com/privacy](https://policies.google.com/privacy)
- **Terms:** [https://policies.google.com/terms](https://policies.google.com/terms)
- **Authentication:** Secure OAuth2 (passwords never stored)
- **Scopes:** gmail.send, userinfo.email

= MailerSend =
- **Purpose:** Transactional email delivery
- **Data:** Email content, headers, recipient addresses, sender info, API token
- **When:** Only when sending AND MailerSend is configured
- **Provider:** MailerSend (UAB "Mailerlite")
- **Privacy:** [https://www.mailersend.com/legal/privacy-policy](https://www.mailersend.com/legal/privacy-policy)
- **Terms:** [https://www.mailersend.com/legal/terms-of-service](https://www.mailersend.com/legal/terms-of-service)

= Mailgun =
- **Purpose:** Email delivery and email validation
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND Mailgun is configured
- **Provider:** Mailgun Technologies, Inc.  (Sinch)
- **Privacy:** [https://www.mailgun.com/legal/privacy-policy/](https://www.mailgun.com/legal/privacy-policy/)
- **Terms:** [https://www.mailgun.com/legal/terms/](https://www.mailgun.com/legal/terms/)
- **API:** REST API with webhook support for bounces/complaints

= Mailjet =
- **Purpose:** Email delivery platform
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND Mailjet is configured
- **Provider:** Mailjet SAS (Sinch)
- **Privacy:** [https://www.mailjet.com/legal/privacy-policy/](https://www.mailjet.com/legal/privacy-policy/)
- **Terms:** [https://www.mailjet.com/legal/terms-and-conditions/](https://www.mailjet.com/legal/terms-and-conditions/)

= Mandrill (Mailchimp Transactional) =
- **Purpose:** Mailchimp's transactional email service
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND Mandrill is configured
- **Provider:** The Rocket Science Group LLC Intuit Mailchimp
- **Privacy:** [https://www.intuit.com/privacy/statement/](https://www.intuit.com/privacy/statement/)
- **Terms:** [https://mailchimp.com/legal/terms/](https://mailchimp.com/legal/terms/)

= Microsoft 365 / Outlook =
- **Purpose:** Send emails via Office 365 account
- **Data:** Email content, headers, recipients, SMTP credentials
- **When:** Only when sending AND Office 365 is configured
- **Provider:** Microsoft Corporation
- **Privacy:** [https://privacy.microsoft.com/en-us/privacystatement](https://privacy.microsoft.com/en-us/privacystatement)
- **Terms:** [https://www.microsoft.com/en-us/servicesagreement](https://www.microsoft.com/en-us/servicesagreement)

= Postmark =
- **Purpose:** Transactional email specialist service
- **Data:** Email content, headers, recipient addresses, sender info, API token
- **When:** Only when sending AND Postmark is configured
- **Provider:** Wildbit LLC (ActiveCampaign)
- **Privacy:** [https://postmarkapp.com/privacy-policy](https://postmarkapp.com/privacy-policy)
- **Terms:** [https://postmarkapp.com/terms-of-service](https://postmarkapp.com/terms-of-service)

= SendGrid =
- **Purpose:** Global leader in email delivery
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND SendGrid is configured
- **Provider:** Twilio SendGrid
- **Privacy:** [https://www.twilio.com/legal/privacy](https://www.twilio.com/legal/privacy)
- **Terms:** [https://www.twilio.com/legal/privacy](https://www.twilio.com/legal/tos)

= SMTP2GO =
- **Purpose:** Reliable email delivery service
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND SMTP2GO is configured
- **Provider:** SMTP2GO
- **Privacy:** [https://www.smtp2go.com/privacy/](https://www.smtp2go.com/privacy/)
- **Terms:** [https://www.smtp2go.com/terms/](https://www.smtp2go.com/terms/)

= SparkPost =
- **Purpose:** Email delivery platform with analytics
- **Data:** Email content, headers, recipient addresses, sender info, API key
- **When:** Only when sending AND SparkPost is configured
- **Provider:** SparkPost (Message Systems)
- **Privacy:** [https://www.sparkpost.com/policies/privacy/]](https://www.sparkpost.com/policies/privacy/)
- **Terms:** [https://www.sparkpost.com/policies/tou/](https://www.sparkpost.com/policies/tou/)

= Zoho Mail =
- **Purpose:** Zoho's email service
- **Data:** Email content, headers, recipient addresses, sender info, SMTP credentials
- **When:** Only when sending AND Zoho Mail is configured
- **Provider:** Zoho Corporation
- **Privacy:** [https://www.zoho.com/privacy.html](https://www.zoho.com/privacy.html)
- **Terms:** [https://www.zoho.com/terms.html](https://www.zoho.com/terms.html)

= Custom SMTP Server =
- **Purpose:** Use your own SMTP server
- **Data:** Email content, headers, recipient addresses, sender info, SMTP credentials
- **When:** Only when sending AND custom SMTP is configured
- **Provider:** Your configured server
- **Security:** Ensure your SMTP server uses TLS encryption

== Changelog ==

= 1.0.3 =
* Security: Completed comprehensive security audit of all plugin files
* Security: Enhanced input sanitization across AJAX handlers and form submissions
* Security: Improved nonce verification in all admin actions
* Security: Strengthened SQL injection protection with consistent use of prepared statements
* Security: Added additional capability checks for sensitive operations
* Security: Enhanced error handling to prevent information disclosure
* Standards: Improved WordPress coding standards compliance
* Standards: Enhanced PHPDoc blocks for better code documentation
* Standards: Optimized database query patterns for better performance
* Standards: Improved output escaping consistency
* Code Quality: Refactored DNS resolution error handling
* Code Quality: Enhanced debug logging with WP_DEBUG checks
* Code Quality: Improved external API timeout handling
* Code Quality: Better error messages for troubleshooting
* Performance: Optimized transient caching for DNS lookups
* Performance: Reduced duplicate database queries in email logging
* Compatibility: Tested with WordPress 6.9
* Compatibility: Verified PHP 8.1+ compatibility

= 1.0.2 =
* Added German translation
* Added French translation
* Added Japanese translation
* Added Dutch translation
* Added Portuguese translation
* Added Russian translation

= 1.0.1 =
* Initial release of Authority Mailer SMTP
* Support for 14+ email providers
* Secure onboarding wizard
* Email logging and monitoring system
* Gmail OAuth2 integration
* Custom SMTP server support
* Email test functionality
* Comprehensive email provider documentation

== Upgrade Notice ==

= 1.0.3 =
Security and code quality improvements. Enhanced WordPress coding standards compliance and improved error handling. Recommended update for all users.

= 1.0.0 =

Initial release of Authority Mailer SMTP - Transform your WordPress email delivery with professional SMTP providers and advanced logging.

== Resources & Support ==

**Getting Started:**
- Visit the Setup Wizard after activation
- Choose your email provider
- Follow provider-specific configuration steps
- Send test email to verify setup

**Common Configurations:**
- SendGrid [https://sendgrid.com/docs/ui/account-settings/api-keys/](https://sendgrid.com/docs/ui/account-settings/api-keys/)
- Mailgun [https://documentation.mailgun.com/](https://documentation.mailgun.com/)
- Gmail [https://developers.google.com/gmail/imap](https://developers.google.com/gmail/imap)
- Office 365 [https://support.microsoft.com/en-us/office](https://support.microsoft.com/en-us/office)

**Additional Help:**
- Check email logs for error details
- Review provider-specific documentation
- Verify DKIM/SPF records in DNS
- Contact hosting provider for port/firewall issues
