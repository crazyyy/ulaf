<?php
/**
 * Centralized Strings for Authority Mailer SMTP
 *
 * All user-facing strings for onboarding, provider partials and testers are
 * centralized here. Do not hard-code UI or diagnostic text elsewhere.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $AUTHORITY_MAILER_STRINGS;

$AUTHORITY_MAILER_STRINGS = array(

	// ---- MENU ----
	'menu_title'                              => __( 'Authority Mailer', 'authority-mailer-smtp' ),
	'menu_dashboard_title'                    => __( 'Dashboard', 'authority-mailer-smtp' ),
	'menu_email_log'                          => __( 'Email Logs', 'authority-mailer-smtp' ),
	'menu_tools'                              => __( 'Domain Health Check', 'authority-mailer-smtp' ),
	'menu_setup'                              => __( 'Setup Assistant', 'authority-mailer-smtp' ),
	'menu_free_vs_pro'                        => __( 'Upgrade to Pro', 'authority-mailer-smtp' ),

	// ---- HERO / PAGE ----
	'hero_title'                              => __( 'Welcome to Authority Mailer SMTP', 'authority-mailer-smtp' ),
	'hero_sub'                                => __( "Let's get your WordPress emails sending reliably. Follow the simple steps.", 'authority-mailer-smtp' ),

	// ---- STEPS ----
	'step_labels'                             => array(
		__( 'Welcome', 'authority-mailer-smtp' ),
		__( 'Choose mailer', 'authority-mailer-smtp' ),
		__( 'Configure Settings', 'authority-mailer-smtp' ),
		__( 'Test & Finish', 'authority-mailer-smtp' ),
	),
	/* translators: %1$d is the current step number; %2$d is the total number of steps. */
	'step_meta'                               => __( 'Step %1$d of %2$d', 'authority-mailer-smtp' ),
	'step_follow_instructions'                => __( 'Follow the instructions to complete setup.', 'authority-mailer-smtp' ),

	// ---- WELCOME STEP ----
	'welcome_heading'                         => __( 'Get started', 'authority-mailer-smtp' ),
	'welcome_copy'                            => __( 'Follow these steps to configure outgoing email for your site.', 'authority-mailer-smtp' ),
	'btn_get_started'                         => __( "Let's Get Started →", 'authority-mailer-smtp' ),

	// ---- ONBOARDING WIZARD SPECIFIC ----
	'onboarding_step_1_meta'                  => __( 'STEP 1 OF 4', 'authority-mailer-smtp' ),
	'onboarding_get_started_heading'          => __( 'Get started', 'authority-mailer-smtp' ),
	'onboarding_get_started_subheading'       => __( 'Follow these steps to configure outgoing email for your site.', 'authority-mailer-smtp' ),
	'onboarding_intro_paragraph'              => __( 'Authority Mailer helps you send WordPress emails reliably through professional SMTP providers. No more emails landing in spam! This quick setup wizard will guide you through connecting your preferred email service in just a few minutes.', 'authority-mailer-smtp' ),
	'onboarding_what_happens_next'            => __( '✨ What happens next?', 'authority-mailer-smtp' ),
	'onboarding_step_choose_provider'         => __( '<strong>Choose your email provider</strong> from our list of 17+ supported services', 'authority-mailer-smtp' ),
	'onboarding_step_enter_credentials'       => __( '<strong>Enter your credentials</strong> (usually just an API key)', 'authority-mailer-smtp' ),
	'onboarding_step_send_test'               => __( '<strong>Send a test email</strong> to verify everything works', 'authority-mailer-smtp' ),
	'onboarding_step_done'                    => __( '<strong>You\'re done!</strong> All WordPress emails will now be sent reliably', 'authority-mailer-smtp' ),

	// ---- CHOOSE MAILER STEP ----
	'choose_mailer_heading'                   => __( 'Choose a mailer', 'authority-mailer-smtp' ),
	'choose_mailer_copy'                      => __( 'Pick the provider you want to use to send email.', 'authority-mailer-smtp' ),
	'smtp_provider_heading'                   => __( 'SMTP & Transactional Providers', 'authority-mailer-smtp' ),

	// ---- CONFIGURE / TEST STEP ----
	'configure_heading'                       => __( 'Configure provider settings', 'authority-mailer-smtp' ),
	'configure_copy'                          => __( 'Add the credentials and options required by your provider. Send a test to confirm delivery.', 'authority-mailer-smtp' ),
	'no_provider_selected_copy'               => __( 'Select a provider to configure.', 'authority-mailer-smtp' ),

	// ---- BUTTONS ----
	'btn_previous'                            => __( 'Previous', 'authority-mailer-smtp' ),
	'btn_save_continue'                       => __( 'Save and Continue', 'authority-mailer-smtp' ),
	'btn_connect_google'                      => __( 'Connect to Google', 'authority-mailer-smtp' ),
	'btn_copy'                                => __( 'Copy', 'authority-mailer-smtp' ),

	// New button for step 4 explicit send
	'btn_send_test'                           => __( 'Send Test Email', 'authority-mailer-smtp' ),

	// ---- LABELS ----
	'label_api_key'                           => __( 'API Key', 'authority-mailer-smtp' ),
	'label_client_id'                         => __( 'Client ID', 'authority-mailer-smtp' ),
	'label_client_secret'                     => __( 'Client Secret', 'authority-mailer-smtp' ),
	'label_redirect_uri'                      => __( 'Authorized Redirect URI', 'authority-mailer-smtp' ),
	'label_from_name'                         => __( 'From Name', 'authority-mailer-smtp' ),
	'label_from_email'                        => __( 'From Email', 'authority-mailer-smtp' ),
	'label_sending_domain'                    => __( 'Sending Domain', 'authority-mailer-smtp' ),
	'label_force_from_name'                   => __( 'Force From Name', 'authority-mailer-smtp' ),
	'label_force_from_email'                  => __( 'Force From Email', 'authority-mailer-smtp' ),
	'label_region'                            => __( 'Region', 'authority-mailer-smtp' ),
	'label_port'                              => __( 'SMTP Port', 'authority-mailer-smtp' ),
	'label_username'                          => __( 'SMTP Username', 'authority-mailer-smtp' ),
	'label_password'                          => __( 'SMTP Password', 'authority-mailer-smtp' ),
	'label_smtp_host'                         => __( 'SMTP Host', 'authority-mailer-smtp' ),
	'label_encryption'                        => __( 'Encryption', 'authority-mailer-smtp' ),
	'label_smtp_auth'                         => __( 'Enable Authentication', 'authority-mailer-smtp' ),
	'label_secret_key'                        => __( 'Secret Key', 'authority-mailer-smtp' ),
	'label_use_smtp_auth'                     => __( 'Use SMTP for Authentication', 'authority-mailer-smtp' ),

	// NEW: label for test recipient field
	'label_send_test_to'                      => __( 'Send Test Email To:', 'authority-mailer-smtp' ),

	// ---- EMAIL LOG related strings (new) ----
	'label_all_providers'                     => __( 'All providers', 'authority-mailer-smtp' ),
	'label_all_statuses'                      => __( 'All statuses', 'authority-mailer-smtp' ),
	'status_attempt'                          => __( 'Attempt', 'authority-mailer-smtp' ),
	'status_success'                          => __( 'Success', 'authority-mailer-smtp' ),
	'status_error'                            => __( 'Error', 'authority-mailer-smtp' ),
	'filter_button'                           => __( 'Filter', 'authority-mailer-smtp' ),
	'reset_button'                            => __( 'Reset', 'authority-mailer-smtp' ),
	'apply_button'                            => __( 'Apply', 'authority-mailer-smtp' ),
	'bulk_actions_label'                      => __( 'Bulk actions', 'authority-mailer-smtp' ),
	'bulk_action_delete'                      => __( 'Delete selected', 'authority-mailer-smtp' ),

	'btn_view'                                => __( 'View', 'authority-mailer-smtp' ),
	'btn_resend'                              => __( 'Resend', 'authority-mailer-smtp' ),
	'btn_delete'                              => __( 'Delete', 'authority-mailer-smtp' ),

	'no_log_entries'                          => __( 'No log entries found.', 'authority-mailer-smtp' ),
	/* translators: %1$d is the starting result number, %2$d is the ending result number, %3$d is the total number of results. */
	'showing_results'                         => __( 'Showing %1$d to %2$d of %3$d results', 'authority-mailer-smtp' ),
	/* translators: %1$s is the sender email address, %2$s is the recipient email address. */
	'label_date'                              => __( 'Date', 'authority-mailer-smtp' ),
	'label_provider'                          => __( 'Provider', 'authority-mailer-smtp' ),
	'label_to'                                => __( 'To', 'authority-mailer-smtp' ),
	'label_from'                              => __( 'From', 'authority-mailer-smtp' ),
	'label_subject'                           => __( 'Subject', 'authority-mailer-smtp' ),
	'label_status'                            => __( 'Status', 'authority-mailer-smtp' ),
	'label_actions'                           => __( 'Actions', 'authority-mailer-smtp' ),
	/* translators: %s is the response code from the email provider */
	'email_body_title'                        => __( 'Email Body', 'authority-mailer-smtp' ),
	'confirm_resend'                          => __( 'Resend this email via the same provider?', 'authority-mailer-smtp' ),
	'resend_attempted'                        => __( 'Resend attempted. New log entry created if successful.', 'authority-mailer-smtp' ),
	'resend_failed'                           => __( 'Resend failed', 'authority-mailer-smtp' ),
	'confirm_delete'                          => __( 'Delete this log entry? This action cannot be undone.', 'authority-mailer-smtp' ),
	'no_rows_selected'                        => __( 'No rows selected', 'authority-mailer-smtp' ),
	'select_bulk_action'                      => __( 'Select a bulk action first', 'authority-mailer-smtp' ),
	'bulk_delete_confirm'                     => __( 'Delete selected log entries? This action cannot be undone.', 'authority-mailer-smtp' ),
	'bulk_delete_failed'                      => __( 'Bulk delete failed', 'authority-mailer-smtp' ),
	'collapse'                                => __( 'Collapse', 'authority-mailer-smtp' ),
	'expand'                                  => __( 'Expand', 'authority-mailer-smtp' ),
	'unknown_bulk_action'                     => __( 'Unknown bulk action', 'authority-mailer-smtp' ),

	// Generic/utility labels
	'btn_get_started'                         => __( "Let's Get Started →", 'authority-mailer-smtp' ),
	'btn_previous'                            => __( 'Previous', 'authority-mailer-smtp' ),
	'btn_save_continue'                       => __( 'Save and Continue', 'authority-mailer-smtp' ),

	// Error/fallbacks used by AJAX handlers
	'invalid_table'                           => __( 'Invalid table', 'authority-mailer-smtp' ),
	'no_valid_ids'                            => __( 'No valid ids', 'authority-mailer-smtp' ),
	'provider_not_recorded'                   => __( 'Provider not recorded for this log entry.', 'authority-mailer-smtp' ),
	'resend_failed'                           => __( 'Resend failed', 'authority-mailer-smtp' ),
	'help_api_key'                            => __( 'Enter the API key for this provider.', 'authority-mailer-smtp' ),
	'help_api_key_mailgun'                    => __( 'Find your Mailgun API key in the Mailgun dashboard.', 'authority-mailer-smtp' ),
	'help_mailgun_domain'                     => __( 'Enter the Mailgun domain you configured for sending.', 'authority-mailer-smtp' ),
	'help_api_key_mailersend'                 => __( 'Follow instructions to get a MailerSend API key.', 'authority-mailer-smtp' ),
	'help_brevo_use_smtp'                     => __( 'When enabled, Brevo will send via SMTP. When disabled, Brevo API will be used (recommended).', 'authority-mailer-smtp' ),
	'help_brevo_smtp_username_desc'           => __( 'Your Brevo SMTP username from your Brevo account SMTP settings.', 'authority-mailer-smtp' ),
	'help_brevo_smtp_password_desc'           => __( 'Your Brevo SMTP password from your Brevo account SMTP settings.', 'authority-mailer-smtp' ),
	'help_from_name'                          => __( 'Name shown in the From field for outgoing messages.', 'authority-mailer-smtp' ),
	'help_from_email'                         => __( 'Address used as the From email.', 'authority-mailer-smtp' ),
	'help_force_from_name'                    => __( 'When enabled, the site will force this From name for outgoing mail.', 'authority-mailer-smtp' ),
	'help_force_from_email'                   => __( 'When enabled, the site will force this From email for outgoing mail.', 'authority-mailer-smtp' ),
	'help_force_authentication'               => __( 'When enabled, the site will force this to use SMTP Username and Password.', 'authority-mailer-smtp' ),
	'help_redirect_uri_google'                => __( 'Use this Redirect URI when creating a Google OAuth client. Copy it into your Google Console Authorized redirect URIs.', 'authority-mailer-smtp' ),
	'help_region_sparkpost'                   => __( 'Select your SparkPost account region (US or EU).', 'authority-mailer-smtp' ),

	// ---- FIELD DESCRIPTIONS FOR PROVIDER PARTIALS ----
	'help_from_name_sparkpost'                => __( 'The name that will appear in the "From" field of emails sent through SparkPost.', 'authority-mailer-smtp' ),
	'help_from_email_sparkpost'               => __( 'The email address that will be used as the sender for SparkPost emails.', 'authority-mailer-smtp' ),
	'help_from_name_smtp2go'                  => __( 'The name that will appear in the "From" field of emails sent through SMTP2GO.', 'authority-mailer-smtp' ),
	'help_from_email_smtp2go'                 => __( 'The email address that will be used as the sender for SMTP2GO emails.', 'authority-mailer-smtp' ),
	'help_from_name_sendgrid'                 => __( 'The name that will appear in the "From" field of emails sent through SendGrid.', 'authority-mailer-smtp' ),
	'help_from_email_sendgrid'                => __( 'The email address that will be used as the sender for SendGrid emails.', 'authority-mailer-smtp' ),
	'help_from_name_postmark'                 => __( 'The name that will appear in the "From" field of emails sent through Postmark.', 'authority-mailer-smtp' ),
	'help_from_email_postmark'                => __( 'The email address that will be used as the sender for Postmark emails.', 'authority-mailer-smtp' ),
	'help_from_name_mandrill'                 => __( 'The name that will appear in the "From" field of emails sent through Mandrill.', 'authority-mailer-smtp' ),
	'help_from_email_mandrill'                => __( 'The email address that will be used as the sender for Mandrill emails.', 'authority-mailer-smtp' ),

	// ---- GMAIL / GOOGLE SPECIFIC ----
	'gmail_section_from_address_note'         => __( 'You may use the following field if you do not wish to use default settings.', 'authority-mailer-smtp' ),
	'help_gmail_api'                          => __( 'Use the Gmail API for OAuth-based sending. You can connect via One-Click Setup or paste Client ID/Secret for a manual setup.', 'authority-mailer-smtp' ),
	'one_click_setup_note'                    => __( 'Enable an easy setup to connect with Google without creating an app manually.', 'authority-mailer-smtp' ),
	'connected_to_google'                     => __( 'Connected to Google', 'authority-mailer-smtp' ),
	'google_oauth_client_missing_detail'      => __( 'Provide OAuth Client ID and Client Secret for Google/Gmail (fields: client_id, client_secret).', 'authority-mailer-smtp' ),
	'help_client_id'                          => __( 'The OAuth Client ID from your Google Cloud Console. Paste it here so Authority Mailer can request authorization.', 'authority-mailer-smtp' ),
	'help_client_secret'                      => __( 'The OAuth Client Secret is used to exchange the authorization code for tokens. Keep it private.', 'authority-mailer-smtp' ),
	'provider_ctas'                           => array(
		'sendlayer'  => __( 'Get Started with SendLayer', 'authority-mailer-smtp' ),
		'smtpcom'    => __( 'Get Started with SMTP.com', 'authority-mailer-smtp' ),
		'brevo'      => __( 'Get Started with Brevo', 'authority-mailer-smtp' ),
		'elastic'    => __( 'Get Started with Elastic Email', 'authority-mailer-smtp' ),
		'mailersend' => __( 'Get Started with MailerSend', 'authority-mailer-smtp' ),
		'mailgun'    => __( 'Get Started with Mailgun', 'authority-mailer-smtp' ),
		'mailjet'    => __( 'Get Started with Mailjet', 'authority-mailer-smtp' ),
		'mandrill'   => __( 'Get Started with Mandrill', 'authority-mailer-smtp' ),
		'postmark'   => __( 'Get Started with Postmark', 'authority-mailer-smtp' ),
		'sendgrid'   => __( 'Get Started with SendGrid', 'authority-mailer-smtp' ),
		'smtp2go'    => __( 'Get Started with SMTP2GO', 'authority-mailer-smtp' ),
		'sparkpost'  => __( 'Get Started with SparkPost', 'authority-mailer-smtp' ),
		'zoho'       => __( 'Get Started with Zoho', 'authority-mailer-smtp' ),
		'aws'        => __( 'Get Started with AWS SES', 'authority-mailer-smtp' ),
		'office365'  => __( 'Get Started with Office 365', 'authority-mailer-smtp' ),
		'other'      => __( 'Open SMTP Docs', 'authority-mailer-smtp' ),
	),

	// ---- PROVIDER DESCRIPTIONS & DOC HELP (editable via filters) ----
	'provider_description_sendlayer'          => __( 'SendLayer is a simple and affordable SMTP provider focused on making transactional email delivery straightforward for WordPress sites.', 'authority-mailer-smtp' ),
	'provider_description_smtpcom'            => __( 'SMTP.com offers enterprise-grade SMTP relay, deliverability analytics and scaling for transactional email.', 'authority-mailer-smtp' ),
	'provider_description_brevo'              => __( 'Brevo (formerly Sendinblue) provides email and SMS services for transactional and marketing messages with an easy-to-use dashboard.', 'authority-mailer-smtp' ),
	'provider_description_elastic'            => __( 'Elastic Email provides an API and SMTP relay suited for both transactional and bulk messaging, with analytics and domain management.', 'authority-mailer-smtp' ),
	'provider_description_mailersend'         => __( 'MailerSend is a developer-friendly transactional email API and SMTP relay — great for notifications, receipts and transactional workflows.', 'authority-mailer-smtp' ),
	'provider_description_mailgun'            => __( 'Mailgun is an email API built for developers and teams. Use a verified sending domain for best deliverability and analytics.', 'authority-mailer-smtp' ),
	'provider_description_mailjet'            => __( 'Mailjet is an email API and SMTP relay platform that supports transactional and marketing emails with templates and analytics.', 'authority-mailer-smtp' ),
	'provider_description_mandrill'           => __( 'Mandrill (Mailchimp Transactional) is a reliable transactional email API designed for developers sending notifications and receipts.', 'authority-mailer-smtp' ),
	'provider_description_postmark'           => __( 'Postmark focuses on fast, reliable transactional email delivery and provides a simple API and message streams for transactional use.', 'authority-mailer-smtp' ),
	'provider_description_smtp2go'            => __( 'SMTP2GO provides a simple REST API for sending transactional email and an SMTP relay with global infrastructure for reliability.', 'authority-mailer-smtp' ),
	'provider_description_sparkpost'          => __( 'SparkPost provides a powerful email API and SMTP relay with features for deliverability and analytics.', 'authority-mailer-smtp' ),
	'provider_description_zoho'               => __( 'Zoho Mail is a secure email hosting service with SMTP support, ideal for businesses using Zoho Suite or requiring reliable email delivery.', 'authority-mailer-smtp' ),
	'provider_description_gmail'              => __( 'Send emails using your Gmail account with OAuth authentication for enhanced security.', 'authority-mailer-smtp' ),
	'provider_description_sendgrid'           => __( 'Leading email API platform with powerful features for transactional and marketing emails.', 'authority-mailer-smtp' ),
	'provider_description_aws'                => __( 'Amazon SES is a scalable cloud-based email service by AWS. Use SMTP credentials for reliable transactional email delivery with pay-as-you-go pricing.', 'authority-mailer-smtp' ),
	'provider_description_office365'          => __( 'Office 365 provides enterprise email services through Microsoft Exchange Online with SMTP relay support for WordPress integration.', 'authority-mailer-smtp' ),
	'provider_description_other'              => __( 'Use a custom SMTP service by providing host, port and credentials. This option works with most email providers and self-hosted SMTP servers.', 'authority-mailer-smtp' ),

	// ---- New small section headings used in partials (centralized) ----
	'section_subheading_sender'               => __( 'Sender', 'authority-mailer-smtp' ),
	'section_subheading_options'              => __( 'Options', 'authority-mailer-smtp' ),
	/* translators: %s is the provider name (e.g. "SendGrid", "Mailgun"). */
	'section_subheading_provider'             => __( '%s settings', 'authority-mailer-smtp' ), // sprintf provider name into this

	'help_read_setup_brevo'                   => __( 'Read how to set up Brevo', 'authority-mailer-smtp' ),
	'help_read_setup_mailersend'              => __( 'Read how to set up MailerSend', 'authority-mailer-smtp' ),
	'help_read_setup_mailgun'                 => __( 'Read how to set up Mailgun', 'authority-mailer-smtp' ),
	'help_read_setup_sendgrid'                => __( 'Read how to set up SendGrid', 'authority-mailer-smtp' ),
	'help_read_setup_mailjet'                 => __( 'Read how to set up Mailjet', 'authority-mailer-smtp' ),
	'help_read_setup_sendlayer'               => __( 'Read how to set up SendLayer', 'authority-mailer-smtp' ),
	'help_read_setup_smtp2go'                 => __( 'Read how to set up SMTP2GO', 'authority-mailer-smtp' ),
	'help_read_setup_smtpcom'                 => __( 'Read how to set up SMTP.com', 'authority-mailer-smtp' ),
	'help_read_setup_mandrill'                => __( 'Read how to set up Mandrill', 'authority-mailer-smtp' ),
	'help_read_setup_zoho'                    => __( 'Read how to set up Zoho Mail', 'authority-mailer-smtp' ),
	'help_read_setup_aws'                     => __( 'Read how to set up AWS SES', 'authority-mailer-smtp' ),
	'help_read_setup_office365'               => __( 'Read how to set up Office 365', 'authority-mailer-smtp' ),
	'help_read_setup_other'                   => __( 'Read general SMTP setup documentation', 'authority-mailer-smtp' ),
	'label_sender'                            => __( 'Sender', 'authority-mailer-smtp' ),

	// ---- VALIDATION / MESSAGES ----
	'i18n_select_mailer'                      => __( 'Please choose a mailer to continue.', 'authority-mailer-smtp' ),
	'i18n_save_error'                         => __( 'Unable to save selection.', 'authority-mailer-smtp' ),
	'i18n_request_failed'                     => __( 'Request failed', 'authority-mailer-smtp' ),
	'i18n_api_key_required'                   => __( 'Please enter your API Key.', 'authority-mailer-smtp' ),
	'i18n_name_required'                      => __( 'Please enter a name.', 'authority-mailer-smtp' ),
	'i18n_sender_name_required'               => __( 'Please enter a sender name.', 'authority-mailer-smtp' ),
	'i18n_email_invalid'                      => __( 'Please enter a valid email address.', 'authority-mailer-smtp' ),
	'i18n_saving_settings'                    => __( 'Saving settings...', 'authority-mailer-smtp' ),
	'i18n_settings_saved'                     => __( 'Settings saved.', 'authority-mailer-smtp' ),
	'i18n_google_client_id_required'          => __( 'Please enter a Client ID.', 'authority-mailer-smtp' ),
	'i18n_google_client_secret_required'      => __( 'Please enter a Client Secret.', 'authority-mailer-smtp' ),
	'i18n_sending_domain_required'            => __( 'Please input the sending domain/subdomain you configured.', 'authority-mailer-smtp' ),
	'i18n_sending_domain_invalid'             => __( 'Please enter a valid sending domain (e.g. mail.example.com).', 'authority-mailer-smtp' ),

	// New keys used by the saved-test UI
	'log_saving_settings'                     => __( 'Saving settings and starting test...', 'authority-mailer-smtp' ),
	'log_test_finished'                       => __( 'Test finished.', 'authority-mailer-smtp' ),

	'status_running_test'                     => __( 'Running test', 'authority-mailer-smtp' ),
	'status_re_running'                       => __( 'Re-running test', 'authority-mailer-smtp' ),
	'status_test_finished_issues'             => __( 'Test finished with issues', 'authority-mailer-smtp' ),
	'status_test_finished_success'            => __( 'Test finished successfully', 'authority-mailer-smtp' ),
	'status_test_finished_unknown'            => __( 'Test finished', 'authority-mailer-smtp' ),
	'status_no_provider_title'                => __( 'No provider', 'authority-mailer-smtp' ),
	'status_no_provider_message'              => __( 'Provider not found. Unable to retry.', 'authority-mailer-smtp' ),
	'status_test_failed'                      => __( 'Test failed', 'authority-mailer-smtp' ),

	// Postmark / others
	'i18n_postmark_token_required'            => __( 'Please enter your Postmark Server Token.', 'authority-mailer-smtp' ),
	'i18n_smtp_host_required'                 => __( 'Please enter the SMTP host.', 'authority-mailer-smtp' ),
	'i18n_smtp_port_required'                 => __( 'Please enter the SMTP port.', 'authority-mailer-smtp' ),
	'i18n_smtp_port_invalid'                  => __( 'Please enter a valid port (1-65535).', 'authority-mailer-smtp' ),
	'i18n_smtp_username_required'             => __( 'Please enter the SMTP username.', 'authority-mailer-smtp' ),
	'i18n_smtp_password_required'             => __( 'Please enter the SMTP password.', 'authority-mailer-smtp' ),

	// Accessibility / misc
	'onboarding_actions_label'                => __( 'Onboarding actions', 'authority-mailer-smtp' ),

	// ajax URL fallback (JS reads this)
	'ajax_url'                                => isset( $GLOBALS['ajaxurl'] ) ? $GLOBALS['ajaxurl'] : admin_url( 'admin-ajax.php' ),


	// ---- Mailgun tester strings ----
	'mailgun_diag_start'                      => __( 'Starting Mailgun diagnostics', 'authority-mailer-smtp' ),
	'mailgun_onboarding_keys'                 => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'mailgun_mm_opts_keys'                    => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'mailgun_final_settings'                  => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),

	// API key / domain detection
	'mailgun_api_key_missing'                 => __( 'API key not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'mailgun_api_key_missing_detail'          => __( 'Ensure the Mailgun API key is entered in the onboarding form (field: mailgun_api_key) or stored under authority_mailer_options[\'mailgun\'].', 'authority-mailer-smtp' ),
	'mailgun_api_detected'                    => __( 'API key detected', 'authority-mailer-smtp' ),

	'mailgun_domain_missing'                  => __( 'Mailgun sending domain not found in settings', 'authority-mailer-smtp' ),
	'mailgun_domain_missing_detail'           => __( 'Enter the Mailgun sending domain (e.g. mg.example.com) in the onboarding form (field: mailgun_domain) or under authority_mailer_options[\'mailgun\'].', 'authority-mailer-smtp' ),

	// DNS / host resolution
	/* translators: %s is the domain being resolved (e.g. mg.example.com). */
	'mailgun_resolving'                       => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is the host that was resolved; %2$s is the resolved address (IP or CNAME target). */
	'mailgun_resolved'                        => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is the domain that could not be resolved. */
	'mailgun_could_not_resolve'               => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is the configured endpoint_ip override value. */
	'mailgun_using_ip_override'               => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'mailgun_dns_failed'                      => __( 'DNS resolution failed for Mailgun hosts', 'authority-mailer-smtp' ),
	'mailgun_dns_failed_detail'               => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),

	// From / force toggles
	'mailgun_no_from_fallback'                => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'mailgun_using_test_recipient'            => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'mailgun_using_admin_email'               => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'mailgun_no_recipient'                    => __( 'No recipient available to send test email (admin email not set).', 'authority-mailer-smtp' ),

	// Transmission / POST
	/* translators: %s is the endpoint URL being POSTed to. */
	'mailgun_default_subject'                 => __( 'Authority Mailer Mailgun test', 'authority-mailer-smtp' ),
	/* NOTE: translatable strings should not include HTML wrappers. Removed <p> wrapper to satisfy i18n checks. */
	'mailgun_default_body'                    => __( 'Authority Mailer Mailgun test', 'authority-mailer-smtp' ),
	'mailgun_allow_insecure'                  => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),

	/* translators: %1$s is the HTTP status code; %2$s is the recipient email attempted. */
	'sendlayer_diag_start'                    => __( 'Starting SendLayer diagnostics', 'authority-mailer-smtp' ),
	'sendlayer_onboarding_keys'               => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'sendlayer_mm_opts_keys'                  => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'sendlayer_final_settings'                => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'sendlayer_api_key_missing'               => __( 'API key / token not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'sendlayer_api_key_missing_detail'        => __( 'Ensure the SendLayer API key is entered in the onboarding form (field: sendlayer_api_key).', 'authority-mailer-smtp' ),
	'sendlayer_api_detected'                  => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is the domain being resolved. */
	'sendlayer_resolving'                     => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is the host; %2$s is the resolved address. */
	'sendlayer_resolved'                      => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is the host that could not be resolved. */
	'sendlayer_could_not_resolve'             => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is the endpoint_ip override value. */
	'sendlayer_using_ip_override'             => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'sendlayer_dns_failed'                    => __( 'DNS resolution failed for SendLayer hosts', 'authority-mailer-smtp' ),
	'sendlayer_dns_failed_detail'             => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'sendlayer_no_from_fallback'              => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'sendlayer_using_test_recipient'          => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'sendlayer_using_admin_email'             => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'sendlayer_no_recipient'                  => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %1$s is the probe URL; %2$s is the Host header value. */
	'sendlayer_allow_insecure'                => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),
	/* translators: %s is the endpoint URL being POSTed to. */
	'sendlayer_attempting_post'               => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is the HTTP status code returned by POST. */
	'sendlayer_payload_preview'               => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	/* translators: %1$s is HTTP status; %2$s is recipient attempted. */
	'other_diag_start'                        => __( 'Starting generic SMTP diagnostics', 'authority-mailer-smtp' ),
	'other_missing_host'                      => __( 'SMTP host is not configured.', 'authority-mailer-smtp' ),
	'other_missing_port'                      => __( 'SMTP port is not configured.', 'authority-mailer-smtp' ),
	/* translators: %s is the SMTP host being resolved. */
	'other_resolving'                         => __( 'Resolving host: %s', 'authority-mailer-smtp' ),
	'other_resolve_failed'                    => __( 'Could not resolve SMTP host', 'authority-mailer-smtp' ),
	'other_resolved'                          => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host, %2$s is port, %3$s is protocol/encryption description. */
	'other_connecting'                        => __( 'Connecting to %1$s on port %2$s (%3$s)', 'authority-mailer-smtp' ),
	'other_connect_failed'                    => __( 'Failed to connect to SMTP host', 'authority-mailer-smtp' ),
	'other_smtp_greeting'                     => __( 'SMTP greeting received', 'authority-mailer-smtp' ),
	'other_smtp_ehlo'                         => __( 'Sent EHLO/HELO', 'authority-mailer-smtp' ),
	'other_smtp_ehlo_failed'                  => __( 'EHLO/HELO failed', 'authority-mailer-smtp' ),
	'other_smtp_starttls_attempt'             => __( 'Attempting STARTTLS', 'authority-mailer-smtp' ),
	'other_smtp_starttls_failed'              => __( 'STARTTLS negotiation failed', 'authority-mailer-smtp' ),
	'other_smtp_ehlo_after_tls'               => __( 'EHLO after STARTTLS', 'authority-mailer-smtp' ),
	'other_smtp_auth_start'                   => __( 'Starting SMTP AUTH', 'authority-mailer-smtp' ),
	'other_smtp_auth_user'                    => __( 'Sent SMTP auth username', 'authority-mailer-smtp' ),
	'other_smtp_auth_pass'                    => __( 'Sent SMTP auth password', 'authority-mailer-smtp' ),
	'other_smtp_auth_failed'                  => __( 'SMTP authentication failed', 'authority-mailer-smtp' ),
	'other_smtp_mail_from'                    => __( 'MAIL FROM response', 'authority-mailer-smtp' ),
	'other_smtp_mail_from_failed'             => __( 'MAIL FROM rejected by server', 'authority-mailer-smtp' ),
	'other_smtp_rcpt_to'                      => __( 'RCPT TO response', 'authority-mailer-smtp' ),
	'other_smtp_rcpt_failed'                  => __( 'RCPT TO rejected by server', 'authority-mailer-smtp' ),
	'other_no_recipient'                      => __( 'No recipient available for test email (admin email not set).', 'authority-mailer-smtp' ),
	'other_using_ip_override'                 => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'other_encryption_selected'               => __( 'Encryption selected', 'authority-mailer-smtp' ),
	'other_force_from_email_forced'           => __( 'Forcing from email using provider key', 'authority-mailer-smtp' ),
	'other_force_from_email_missing_fallback' => __( 'Force from email enabled but no provider address found — falling back to admin_email', 'authority-mailer-smtp' ),
	'other_force_from_name_forced'            => __( 'Forcing from name using provider key', 'authority-mailer-smtp' ),
	'other_force_from_name_missing_fallback'  => __( 'Force from name enabled but no provider name found — falling back to site name', 'authority-mailer-smtp' ),
	'other_no_smtp_username'                  => __( 'No SMTP username provided — attempting unauthenticated send if server permits', 'authority-mailer-smtp' ),


	// ---- Mandrill tester strings ----
	'mandrill_diag_start'                     => __( 'Starting Mandrill diagnostics', 'authority-mailer-smtp' ),
	'mandrill_onboarding_keys'                => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'mandrill_mm_opts_keys'                   => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'mandrill_final_settings'                 => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'mandrill_api_key_missing'                => __( 'API key / token not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'mandrill_api_key_missing_detail'         => __( 'Ensure the Mandrill API key is entered in the onboarding form (field: mandrill_api_key).', 'authority-mailer-smtp' ),
	'mandrill_api_detected'                   => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is the domain being resolved. */
	'mandrill_resolving'                      => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'mandrill_resolved'                       => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is the host that could not be resolved. */
	'mandrill_could_not_resolve'              => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'mandrill_using_ip_override'              => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'mandrill_dns_failed'                     => __( 'DNS resolution failed for Mandrill hosts', 'authority-mailer-smtp' ),
	'mandrill_dns_failed_detail'              => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'mandrill_no_from_fallback'               => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'mandrill_force_from_email_used'          => __( 'Force-from-email enabled; using mandrill_from_email', 'authority-mailer-smtp' ),
	'mandrill_force_from_email_empty'         => __( 'Force-from-email enabled but mandrill_from_email is empty', 'authority-mailer-smtp' ),
	'mandrill_force_from_name_used'           => __( 'Force-from-name enabled; using mandrill_from_name', 'authority-mailer-smtp' ),
	'mandrill_force_from_name_empty'          => __( 'Force-from-name enabled but mandrill_force_from_name is empty', 'authority-mailer-smtp' ),
	'mandrill_using_test_recipient'           => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'mandrill_using_admin_email'              => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'mandrill_no_recipient'                   => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %s is the endpoint URL. */
	'mandrill_attempting_post'                => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'mandrill_payload_preview'                => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'mandrill_allow_insecure'                 => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),
	'mandrill_default_subject'                => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'mandrill_default_body'                   => __( 'Authority Mailer test', 'authority-mailer-smtp' ),

	// ---- MailerSend tester strings ----
	'mailersend_diag_start'                   => __( 'Starting MailerSend diagnostics', 'authority-mailer-smtp' ),
	'mailersend_onboarding_keys'              => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'mailersend_mm_opts_keys'                 => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'mailersend_final_settings'               => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'mailersend_api_key_missing'              => __( 'API key / token not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'mailersend_api_key_missing_detail'       => __( 'Ensure the MailerSend API key is entered in the onboarding form (field: mailersend_api_key).', 'authority-mailer-smtp' ),
	'mailersend_api_detected'                 => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is the domain being resolved. */
	'mailersend_resolving'                    => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'mailersend_resolved'                     => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is the host that could not be resolved. */
	'mailersend_could_not_resolve'            => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'mailersend_using_ip_override'            => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'mailersend_dns_failed'                   => __( 'DNS resolution failed for MailerSend hosts', 'authority-mailer-smtp' ),
	'mailersend_dns_failed_detail'            => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'mailersend_no_from_fallback'             => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'mailersend_using_test_recipient'         => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'mailersend_using_admin_email'            => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'mailersend_no_recipient'                 => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %1$s is the probe URL; %2$s is Host header. */
	'mailersend_allow_insecure'               => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'mailersend_attempting_post'              => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'mailersend_payload_preview'              => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'mailersend_default_subject'              => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'mailersend_default_body'                 => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	'mailersend_sender_details'               => __( 'Final transmission addresses', 'authority-mailer-smtp' ),


	// shared generic keys (if missing)
	'provider_ignored_from_email'             => __( 'Provider defines a From Email but "Force From Email" is disabled — using site admin_email instead.', 'authority-mailer-smtp' ),
	'provider_ignored_from_name'              => __( 'Provider defines a From Name but "Force From Name" is disabled — using site name instead.', 'authority-mailer-smtp' ),
	'final_transmission_addresses'            => __( 'Final transmission addresses', 'authority-mailer-smtp' ),

	// ---- Elastic Email tester strings ----
	'elasticmail_diag_start'                  => __( 'Starting Elastic Email diagnostics', 'authority-mailer-smtp' ),
	'elasticmail_onboarding_keys'             => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'elasticmail_mm_opts_keys'                => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'elasticmail_final_settings'              => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'elasticmail_api_key_missing'             => __( 'API key / token not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'elasticmail_api_key_missing_detail'      => __( 'Ensure the Elastic Email API key is entered in the onboarding form (field: elasticmail_api_key).', 'authority-mailer-smtp' ),
	'elasticmail_api_detected'                => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is the domain being resolved. */
	'elasticmail_resolving'                   => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'elasticmail_resolved'                    => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is the host that could not be resolved. */
	'elasticmail_could_not_resolve'           => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'elasticmail_using_ip_override'           => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'elasticmail_dns_failed'                  => __( 'DNS resolution failed for Elastic Email hosts', 'authority-mailer-smtp' ),
	'elasticmail_dns_failed_detail'           => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'elasticmail_no_from_fallback'            => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'elasticmail_using_test_recipient'        => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'elasticmail_using_admin_email'           => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'elasticmail_no_recipient'                => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'elasticmail_attempting_post'             => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'elasticmail_payload_preview'             => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'elasticmail_default_subject'             => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'elasticmail_default_body'                => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	'elasticmail_allow_insecure'              => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),
	'help_read_setup_elastic'                 => __( 'Read how to set up Elastic Email', 'authority-mailer-smtp' ),
	'mailjet_diag_start'                      => __( 'Starting Mailjet diagnostics', 'authority-mailer-smtp' ),
	'mailjet_onboarding_keys'                 => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'mailjet_mm_opts_keys'                    => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'mailjet_final_settings'                  => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'mailjet_api_key_missing'                 => __( 'API key not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'mailjet_api_key_missing_detail'          => __( 'Ensure the Mailjet API key is entered in the onboarding form (field: mailjet_api_key).', 'authority-mailer-smtp' ),
	'mailjet_api_detected'                    => __( 'API credentials detected', 'authority-mailer-smtp' ),
	/* translators: %s is the host being resolved. */
	'mailjet_resolving'                       => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'mailjet_resolved'                        => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is host that could not be resolved. */
	'mailjet_could_not_resolve'               => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'mailjet_using_ip_override'               => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'mailjet_dns_failed'                      => __( 'DNS resolution failed for Mailjet hosts', 'authority-mailer-smtp' ),
	'mailjet_dns_failed_detail'               => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'mailjet_no_from_fallback'                => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'mailjet_using_test_recipient'            => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'mailjet_using_admin_email'               => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'mailjet_no_recipient'                    => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'mailjet_attempting_post'                 => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'mailjet_payload_preview'                 => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'mailjet_default_subject'                 => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'mailjet_default_body'                    => __( 'Authority Mailer test', 'authority-mailer-smtp' ),

	// ---- Postmark tester strings ----
	'postmark_diag_start'                     => __( 'Starting Postmark diagnostics', 'authority-mailer-smtp' ),
	'postmark_onboarding_keys'                => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'postmark_mm_opts_keys'                   => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'postmark_final_settings'                 => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'postmark_token_missing'                  => __( 'Postmark Server Token not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'postmark_token_missing_detail'           => __( 'Ensure the Postmark Server Token is entered in the onboarding form (field: postmark_server_token) or stored under authority_mailer_options[\'postmark\']', 'authority-mailer-smtp' ),
	'postmark_token_detected'                 => __( 'Server token detected', 'authority-mailer-smtp' ),
	/* translators: %s is the host being resolved. */
	'postmark_resolving'                      => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'postmark_resolved'                       => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is host that could not be resolved. */
	'postmark_could_not_resolve'              => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'postmark_using_ip_override'              => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'postmark_dns_failed'                     => __( 'DNS resolution failed for Postmark hosts', 'authority-mailer-smtp' ),
	'postmark_dns_failed_detail'              => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'postmark_no_from_fallback'               => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'postmark_using_test_recipient'           => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'postmark_using_admin_email'              => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'postmark_no_recipient'                   => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'postmark_attempting_post'                => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'postmark_payload_preview'                => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'postmark_default_subject'                => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'postmark_default_body'                   => __( 'Authority Mailer test', 'authority-mailer-smtp' ),
	'postmark_allow_insecure'                 => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),
	'help_read_setup_postmark'                => __( 'Read how to set up Postmark', 'authority-mailer-smtp' ),
	'label_postmark_server_api_token'         => __( 'Server Token', 'authority-mailer-smtp' ),
	'label_postmark_message_stream_id'        => __( 'Message Stream ID', 'authority-mailer-smtp' ),

	// ---- SMTP2GO tester strings ----
	'smtp2go_diag_start'                      => __( 'Starting SMTP2GO diagnostics', 'authority-mailer-smtp' ),
	'smtp2go_onboarding_keys'                 => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'smtp2go_mm_opts_keys'                    => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'smtp2go_final_settings'                  => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'smtp2go_api_key_missing'                 => __( 'API key not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'smtp2go_api_key_missing_detail'          => __( 'Ensure the SMTP2GO API key is entered in the onboarding form (field: smtp2go_api_key).', 'authority-mailer-smtp' ),
	'smtp2go_api_detected'                    => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is host being resolved. */
	'smtp2go_resolving'                       => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s host; %2$s resolved address. */
	'smtp2go_resolved'                        => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is host that could not be resolved. */
	'smtp2go_could_not_resolve'               => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'smtp2go_using_ip_override'               => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'smtp2go_dns_failed'                      => __( 'DNS resolution failed for SMTP2GO hosts', 'authority-mailer-smtp' ),
	'smtp2go_dns_failed_detail'               => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'smtp2go_using_test_recipient'            => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'smtp2go_using_admin_email'               => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'smtp2go_no_recipient'                    => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'smtp2go_attempting_post'                 => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'smtp2go_post_error_network'              => __( 'POST request returned an error: %s', 'authority-mailer-smtp' ),
	'smtp2go_payload_preview'                 => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'smtp2go_default_subject'                 => __( 'Authority Mailer SMTP2GO test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'smtp2go_default_body'                    => __( 'Authority Mailer SMTP2GO test', 'authority-mailer-smtp' ),
	'smtp2go_allow_insecure'                  => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),

	// SMTP2GO model validation guidance
	'sparkpost_diag_start'                    => __( 'Starting SparkPost diagnostics', 'authority-mailer-smtp' ),
	'sparkpost_onboarding_keys'               => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'sparkpost_mm_opts_keys'                  => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'sparkpost_final_settings'                => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'sparkpost_api_key_missing'               => __( 'API key not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'sparkpost_api_key_missing_detail'        => __( 'Ensure the SparkPost API key is entered in the onboarding form (field: sparkpost_api_key).', 'authority-mailer-smtp' ),
	'sparkpost_api_detected'                  => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is host being resolved. */
	'sparkpost_resolving'                     => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'sparkpost_resolved'                      => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is host that could not be resolved. */
	'sparkpost_could_not_resolve'             => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'sparkpost_using_ip_override'             => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'sparkpost_dns_failed'                    => __( 'DNS resolution failed for SparkPost hosts', 'authority-mailer-smtp' ),
	'sparkpost_dns_failed_detail'             => __( 'Check server DNS / firewall / outbound network (attempted hosts above).', 'authority-mailer-smtp' ),
	'sparkpost_no_from_fallback'              => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'sparkpost_using_test_recipient'          => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'sparkpost_using_admin_email'             => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'sparkpost_no_recipient'                  => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'sparkpost_attempting_post'               => __( 'Attempting POST %s', 'authority-mailer-smtp' ),
	/* translators: %s is HTTP status returned. */
	'sparkpost_payload_preview'               => __( 'Payload keys sent (preview)', 'authority-mailer-smtp' ),
	'sparkpost_default_subject'               => __( 'Authority Mailer SparkPost test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'sparkpost_default_body'                  => __( 'Authority Mailer SparkPost test', 'authority-mailer-smtp' ),
	'sparkpost_allow_insecure'                => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),

	// SparkPost model validation guidance
	'help_read_setup_sparkpost'               => __( 'Read how to set up SparkPost', 'authority-mailer-smtp' ),
	'help_region_sparkpost'                   => __( 'Select your SparkPost account region (US or EU).', 'authority-mailer-smtp' ),

	// ---- SENDGRID tester strings (added) ----
	'sendgrid_diag_start'                     => __( 'Starting SendGrid diagnostics', 'authority-mailer-smtp' ),
	'sendgrid_onboarding_keys'                => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'sendgrid_mm_opts_keys'                   => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'sendgrid_final_settings'                 => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),

	// API key / detection.
	'sendgrid_api_key_missing'                => __( 'API key not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'sendgrid_api_key_missing_detail'         => __( 'Ensure the SendGrid API key is entered in the onboarding form (field: sendgrid_api_key) or stored under authority_mailer_options[\'sendgrid\'] or a top-level sendgrid_api_key option.', 'authority-mailer-smtp' ),
	'sendgrid_api_detected'                   => __( 'API key detected', 'authority-mailer-smtp' ),

	// DNS / resolution.
	/* translators: %s is the domain/host being resolved. */
	'sendgrid_resolving'                      => __( 'Resolving DNS for %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is host; %2$s is resolved address. */
	'sendgrid_resolved'                       => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is host that could not be resolved. */
	'sendgrid_could_not_resolve'              => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'sendgrid_using_ip_override'              => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'sendgrid_dns_failed'                     => __( 'DNS resolution failed for SendGrid hosts', 'authority-mailer-smtp' ),
	'sendgrid_dns_failed_detail'              => __( 'Check server DNS, firewall, and outbound network. Ensure your host can reach api.sendgrid.com or the configured endpoint_host.', 'authority-mailer-smtp' ),

	// From / force toggles.
	'sendgrid_no_from_fallback'               => __( 'No from address in settings; falling back to admin_email', 'authority-mailer-smtp' ),
	'sendgrid_using_test_recipient'           => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'sendgrid_using_admin_email'              => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'sendgrid_no_recipient'                   => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),

	// Transmission / POST.
	/* translators: %s is endpoint being POSTed to. */
	'sendgrid_default_subject'                => __( 'Authority Mailer SendGrid test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'sendgrid_default_body'                   => __( 'Authority Mailer SendGrid test', 'authority-mailer-smtp' ),
	'sendgrid_allow_insecure'                 => __( 'allow_insecure is enabled — SSL verification disabled (debug only)', 'authority-mailer-smtp' ),

	/* translators: %1$s HTTP status; %2$s recipient attempted. */
	'brevo_diag_start'                        => __( 'Starting Brevo diagnostics', 'authority-mailer-smtp' ),
	'brevo_onboarding_keys'                   => __( 'Onboarding-provided settings summary', 'authority-mailer-smtp' ),
	'brevo_mm_opts_keys'                      => __( 'Stored authority_mailer_options inspected (summary)', 'authority-mailer-smtp' ),
	'brevo_final_settings'                    => __( 'Final settings used after merge (summary)', 'authority-mailer-smtp' ),
	'brevo_api_key_missing'                   => __( 'API key not found in onboarding settings or stored options', 'authority-mailer-smtp' ),
	'brevo_api_key_missing_detail'            => __( 'Ensure the Brevo (Sendinblue) API key is entered in the onboarding form (field: brevo_api_key or sendinblue_api_key).', 'authority-mailer-smtp' ),
	'brevo_api_detected'                      => __( 'API key detected', 'authority-mailer-smtp' ),
	/* translators: %s is the domain being resolved. */
	'brevo_resolved'                          => __( 'Resolved %1$s -> %2$s', 'authority-mailer-smtp' ),
	/* translators: %s is the host that could not be resolved. */
	'brevo_could_not_resolve'                 => __( 'Could not resolve %s', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint_ip override. */
	'brevo_using_ip_override'                 => __( 'Using endpoint_ip override: %s', 'authority-mailer-smtp' ),
	'brevo_dns_failed'                        => __( 'DNS resolution failed for Brevo hosts', 'authority-mailer-smtp' ),
	'brevo_using_test_recipient'              => __( 'Using test_recipient from settings', 'authority-mailer-smtp' ),
	'brevo_using_admin_email'                 => __( 'Using admin_email as test recipient', 'authority-mailer-smtp' ),
	'brevo_no_recipient'                      => __( 'No recipient available to send test email to (admin email not set).', 'authority-mailer-smtp' ),
	'brevo_attempting_test'                   => __( 'Sending test email through wp_mail() pipeline...', 'authority-mailer-smtp' ),
	/* translators: %s is endpoint being POSTed to. */
	'brevo_default_subject'                   => __( 'Authority Mailer Brevo test', 'authority-mailer-smtp' ),
	/* NOTE: removed HTML wrapper */
	'brevo_default_body'                      => __( 'Authority Mailer Brevo test', 'authority-mailer-smtp' ),
	'plugin_email_log_page_title'             => __( 'Authority Mailer Email Log', 'authority-mailer-smtp' ),
	'plugin_email_log_not_available'          => __( 'The Email Log admin page is not available. Please ensure the file includes/admin/email-log.php is present and loaded by the plugin.', 'authority-mailer-smtp' ),
	'dashboard_failed'                        => __( 'Failed', 'authority-mailer-smtp' ),
	'dashboard_delivered'                     => __( 'Delivered', 'authority-mailer-smtp' ),
	'dashboard_last_7_days'                   => __( 'Last 7 days', 'authority-mailer-smtp' ),
	'dashboard_recent_emails'                 => __( 'Recent Emails', 'authority-mailer-smtp' ),
	'dashboard_view_all'                      => __( 'View All', 'authority-mailer-smtp' ),
	'dashboard_to'                            => __( 'To', 'authority-mailer-smtp' ),
	'dashboard_subject'                       => __( 'Subject', 'authority-mailer-smtp' ),
	'dashboard_status'                        => __( 'Status', 'authority-mailer-smtp' ),
	'dashboard_date'                          => __( 'Date', 'authority-mailer-smtp' ),
	'dashboard_ago'                           => __( 'ago', 'authority-mailer-smtp' ),
	'dashboard_configure_provider'            => __( 'Configure Provider', 'authority-mailer-smtp' ),
	'dashboard_total_sent'                    => __( 'Total Sent', 'authority-mailer-smtp' ),
	'dashboard_view'                          => __( 'View', 'authority-mailer-smtp' ),
	'dashboard_delivery_health'               => __( 'Delivery Health', 'authority-mailer-smtp' ),
	'dashboard_delivery_rate'                 => __( 'Delivery Rate', 'authority-mailer-smtp' ),
	'dashboard_excellent'                     => __( 'Excellent', 'authority-mailer-smtp' ),
	'dashboard_good'                          => __( 'Good', 'authority-mailer-smtp' ),
	'dashboard_needs_attention'               => __( 'Needs Attention', 'authority-mailer-smtp' ),
	'dashboard_no_provider_configured'        => __( 'No Email Provider Configured', 'authority-mailer-smtp' ),
	'dashboard_no_provider_desc'              => __( 'To start sending emails, you need to configure an email service provider. Click below to get started with the setup wizard.', 'authority-mailer-smtp' ),
	'dashboard_provider_configured'           => __( 'Email Provider Active', 'authority-mailer-smtp' ),
	/* translators: %s is the email service provider name (e.g., "SendGrid", "Mailgun"). */
	'dashboard_provider_active_desc'          => __( 'Your site is configured to send emails using %s', 'authority-mailer-smtp' ),
	/* translators: %1$s is the provider name, %2$s is the last email timestamp */
	'dashboard_provider_active_with_time'     => __( 'Your site is using %1$s. Last email sent %2$s.', 'authority-mailer-smtp' ),
	'dashboard_all_systems_operational'       => __( 'All systems operational', 'authority-mailer-smtp' ),
	'dashboard_manage_settings'               => __( 'Manage Settings', 'authority-mailer-smtp' ),
	'dashboard_no_delivery_issues'            => __( 'No delivery issues detected', 'authority-mailer-smtp' ),
	'dashboard_no_failures_period'            => __( 'No failures in selected period', 'authority-mailer-smtp' ),
	'dashboard_pro_feature_badge'             => __( 'Pro feature', 'authority-mailer-smtp' ),
	'dashboard_no_email_activity'             => __( 'Not enough data yet', 'authority-mailer-smtp' ),
	'dashboard_chart_empty_message'           => __( 'Send a test email or wait for site activity to see analytics here.', 'authority-mailer-smtp' ),
	'dashboard_view_email_logs'               => __( 'View Email Logs', 'authority-mailer-smtp' ),
	'dashboard_unlock_analytics_title'        => __( 'Unlock Detailed Email Analytics!', 'authority-mailer-smtp' ),
	'dashboard_engagement_tracking'           => __( 'Engagement Tracking', 'authority-mailer-smtp' ),
	'dashboard_opens_clicks_analytics'        => __( '(Opens & Clicks analytics)', 'authority-mailer-smtp' ),
	'dashboard_advanced_testing'              => __( 'Advanced Email Testing', 'authority-mailer-smtp' ),
	'dashboard_realtime_tests'                => __( '(Real-time delivery tests)', 'authority-mailer-smtp' ),
	'dashboard_compliance_tools'              => __( 'Compliance Tools', 'authority-mailer-smtp' ),
	'dashboard_spam_score_check'              => __( '(Spam score check)', 'authority-mailer-smtp' ),
	'dashboard_unlock_pro_analytics'          => __( 'Unlock Pro Analytics →', 'authority-mailer-smtp' ),
	'dashboard_bounce_rate'                   => __( 'Bounce Rate', 'authority-mailer-smtp' ),
	'dashboard_bounce_tracking'               => __( 'Bounce Rate Tracking', 'authority-mailer-smtp' ),
	'dashboard_unlock_pro'                    => __( 'Available in Pro', 'authority-mailer-smtp' ),
	'dashboard_upgrade_to_premium'            => __( 'Upgrade to Premium', 'authority-mailer-smtp' ),
	'dashboard_daily'                         => __( 'Daily', 'authority-mailer-smtp' ),
	'dashboard_weekly'                        => __( 'Weekly', 'authority-mailer-smtp' ),
	'dashboard_monthly'                       => __( 'Monthly', 'authority-mailer-smtp' ),
	'dashboard_success'                       => __( 'Success', 'authority-mailer-smtp' ),
	'dashboard_pending'                       => __( 'Pending', 'authority-mailer-smtp' ),
	'dashboard_emails_sent'                   => __( 'Emails Sent', 'authority-mailer-smtp' ),
	'dashboard_email_analytics'               => __( 'Email Analytics', 'authority-mailer-smtp' ),
	'dashboard_total'                         => __( 'Total', 'authority-mailer-smtp' ),
	'dashboard_no_data_yet'                   => __( 'No Email Data Yet', 'authority-mailer-smtp' ),
	'dashboard_no_data_desc'                  => __( 'Start sending emails through Authority Mailer to see detailed analytics and trends here.', 'authority-mailer-smtp' ),
	'dashboard_send_test_email'               => __( 'Send Test Email', 'authority-mailer-smtp' ),
	'dashboard_bounce_tracking_pro'           => __( 'Bounce Tracking (Pro)', 'authority-mailer-smtp' ),
	'dashboard_upgrade_to_pro'                => __( 'Upgrade to Pro', 'authority-mailer-smtp' ),
	'dashboard_total_sent_tooltip'            => __( 'Total emails processed in the last 7 days', 'authority-mailer-smtp' ),
	'dashboard_delivered_tooltip'             => __( 'Successfully delivered emails (success + accepted status)', 'authority-mailer-smtp' ),
	'dashboard_failed_tooltip'                => __( 'Emails that failed to send due to errors', 'authority-mailer-smtp' ),
	'dashboard_chart_period_selector'         => __( 'Chart Period Selector', 'authority-mailer-smtp' ),
	'dashboard_skip_to_content'               => __( 'Skip to main content', 'authority-mailer-smtp' ),
	'email_log_title'                         => __( 'Email Logs', 'authority-mailer-smtp' ),
	'email_log_search_placeholder'            => __( 'Search emails...', 'authority-mailer-smtp' ),
	'email_log_from_date'                     => __( 'From date', 'authority-mailer-smtp' ),
	'email_log_to_date'                       => __( 'To date', 'authority-mailer-smtp' ),
	'email_log_per_page'                      => __( 'per page', 'authority-mailer-smtp' ),
	'email_log_no_emails_desc'                => __( 'Emails will appear here once your site starts sending them.', 'authority-mailer-smtp' ),
	'email_log_email_not_found'               => __( 'Email not found', 'authority-mailer-smtp' ),
	'email_log_back_to_log'                   => __( '← Back to Email Log', 'authority-mailer-smtp' ),
	'email_log_back_link'                     => __( 'Back to Email Log', 'authority-mailer-smtp' ),
	'email_log_email_details'                 => __( 'Email Details', 'authority-mailer-smtp' ),
	'email_log_response_code'                 => __( 'Response Code', 'authority-mailer-smtp' ),
	'email_log_error_message'                 => __( 'Error Message', 'authority-mailer-smtp' ),

	// ---- Email Log Messages/Alerts ----
	'email_log_entry_not_found'               => __( 'Email log entry not found.', 'authority-mailer-smtp' ),
	'onboarding_go_pro_today'                 => __( 'Go Pro Today', 'authority-mailer-smtp' ),
	'onboarding_reliable_delivery'            => __( 'Everything you need for reliable email delivery', 'authority-mailer-smtp' ),
	'email_logger_view_raw_payload'           => __( 'View raw payload (debug)', 'authority-mailer-smtp' ),
	'email_logger_view_raw_body'              => __( 'View raw body', 'authority-mailer-smtp' ),
	'email_logger_view_raw_headers'           => __( 'View raw headers', 'authority-mailer-smtp' ),

	// ---- Provider Settings Partials ----
	'settings_encryption_none'                => __( 'None', 'authority-mailer-smtp' ),
	'settings_encryption_ssl'                 => __( 'SSL', 'authority-mailer-smtp' ),
	'settings_encryption_tls'                 => __( 'TLS (STARTTLS)', 'authority-mailer-smtp' ),
	'help_from_name_desc'                     => __( 'The name that will appear in the "From" field of emails sent from your site.', 'authority-mailer-smtp' ),
	'help_from_email_desc'                    => __( 'The email address that will be used as the sender for all outgoing emails.', 'authority-mailer-smtp' ),
	'help_brevo_api_key_desc'                 => __( 'Your Brevo API key for authenticating and sending emails through their service.', 'authority-mailer-smtp' ),
	'help_elastic_api_key_desc'               => __( 'Your Elastic Email API key for authentication and sending emails.', 'authority-mailer-smtp' ),
	'region_us'                               => __( 'US', 'authority-mailer-smtp' ),
	'region_eu'                               => __( 'EU', 'authority-mailer-smtp' ),

	// ---- More Provider Help Text ----
	'help_sendlayer_api_key_desc'             => __( 'Your SendLayer API key for authenticating and sending emails through their service.', 'authority-mailer-smtp' ),
	'help_smtpcom_api_key_desc'               => __( 'Your SMTP.com API key for authenticating and sending emails through their service.', 'authority-mailer-smtp' ),
	'help_smtpcom_sender_name_desc'           => __( 'The sender name configured in your SMTP.com account (required by SMTP.com).', 'authority-mailer-smtp' ),
	'help_sendgrid_api_key'                   => __( 'Find your SendGrid API key in the SendGrid dashboard under Settings &gt; API Keys.', 'authority-mailer-smtp' ),
	'help_sendgrid_sending_domain'            => __( 'Enter the domain you\'ve verified in SendGrid for sending (e.g., yourdomain.com).', 'authority-mailer-smtp' ),
	'help_smtp2go_api_key'                    => __( 'Find your SMTP2GO API key in the SMTP2GO dashboard under Settings &gt; API Keys.', 'authority-mailer-smtp' ),
	'help_sparkpost_api_key'                  => __( 'Find your SparkPost API key in the SparkPost dashboard under Account &gt; API Keys.', 'authority-mailer-smtp' ),
	'help_postmark_server_token'              => __( 'Find your Postmark Server API Token in the Postmark dashboard under Servers &gt; API Tokens.', 'authority-mailer-smtp' ),
	'help_postmark_message_stream_id'         => __( 'Enter your Message Stream ID from Postmark (e.g., outbound). Find this in Settings &gt; Message Streams.', 'authority-mailer-smtp' ),
	'help_mandrill_api_key'                   => __( 'Find your Mandrill API key in the Mailchimp dashboard under Transactional &gt; Settings &gt; SMTP &amp; API Info.', 'authority-mailer-smtp' ),

	// ---- Mailjet Help Text ----
	'help_mailjet_api_key'                    => __( 'Find your Mailjet API key in the Mailjet dashboard under Account Settings &gt; REST API &gt; API Key Management.', 'authority-mailer-smtp' ),
	'help_mailjet_secret_key'                 => __( 'Find your Mailjet Secret Key (API Secret) in the Mailjet dashboard under Account Settings &gt; REST API &gt; API Key Management.', 'authority-mailer-smtp' ),

	// ---- SMTP (Other) Help Text ----
	'help_smtp_host_desc'                     => __( 'The hostname or IP address of your SMTP server (e.g., smtp.example.com).', 'authority-mailer-smtp' ),
	'help_smtp_port_desc'                     => __( 'The port number your SMTP server uses. Common ports: 25 (unencrypted), 465 (SSL), 587 (TLS), 2525 (alternate).', 'authority-mailer-smtp' ),
	'help_smtp_username_desc'                 => __( 'Your SMTP authentication username, typically your email address or account username.', 'authority-mailer-smtp' ),
	'help_smtp_password_desc'                 => __( 'Your SMTP authentication password or app-specific password.', 'authority-mailer-smtp' ),
	'help_smtp_encryption_desc'               => __( 'Choose the encryption method required by your SMTP server. SSL/TLS (port 465) or STARTTLS (port 587) recommended.', 'authority-mailer-smtp' ),

	// ---- Additional Email Log Detail View Strings ----
	'email_log_headers_title'                 => __( 'Email Headers', 'authority-mailer-smtp' ),
	'email_log_response_body_title'           => __( 'Response Body', 'authority-mailer-smtp' ),
	'delete_success'                          => __( 'Log entry deleted successfully.', 'authority-mailer-smtp' ),
	'delete_failed'                           => __( 'Failed to delete log entry.', 'authority-mailer-smtp' ),
	'no_permission'                           => __( 'You do not have permission to access this page.', 'authority-mailer-smtp' ),

	// ---- Zoho Help Text ----
	'help_from_name_zoho'                     => __( 'The name that will appear in the "From" field of emails sent through Zoho Mail.', 'authority-mailer-smtp' ),
	'help_from_email_zoho'                    => __( 'The email address from your Zoho Mail account that will be used as the sender.', 'authority-mailer-smtp' ),
	'help_zoho_smtp_host'                     => __( 'The Zoho SMTP server hostname (default: smtp.zoho.com for standard accounts, smtp.zoho.in for India, smtp.zoho.eu for Europe, smtp.zoho.com.cn for China).', 'authority-mailer-smtp' ),
	'help_zoho_smtp_port'                     => __( 'The port number for Zoho SMTP. Use 587 for TLS (recommended) or 465 for SSL.', 'authority-mailer-smtp' ),
	'help_zoho_smtp_encryption'               => __( 'Choose the encryption method. TLS (port 587) is recommended for Zoho Mail.', 'authority-mailer-smtp' ),
	'help_zoho_smtp_username'                 => __( 'Your full Zoho Mail email address (e.g., you@yourdomain.com).', 'authority-mailer-smtp' ),
	'help_zoho_smtp_password'                 => __( 'Your Zoho Mail password or app-specific password. We recommend using an app-specific password for security.', 'authority-mailer-smtp' ),

	// ---- Office 365 Help Text ----
	'help_from_name_office365'                => __( 'The name that will appear in the "From" field of emails sent through Office 365.', 'authority-mailer-smtp' ),
	'help_from_email_office365'               => __( 'The email address from your Office 365 account that will be used as the sender.', 'authority-mailer-smtp' ),
	'help_office365_smtp_host'                => __( 'The Office 365 SMTP server hostname (default: smtp.office365.com).', 'authority-mailer-smtp' ),
	'help_office365_smtp_port'                => __( 'The port number for Office 365 SMTP. Use 587 for TLS (recommended).', 'authority-mailer-smtp' ),
	'help_office365_smtp_encryption'          => __( 'Choose the encryption method. TLS (port 587) is required for Office 365.', 'authority-mailer-smtp' ),
	'help_office365_smtp_username'            => __( 'Your full Office 365 email address (e.g., you@yourdomain.com).', 'authority-mailer-smtp' ),
	'help_office365_smtp_password'            => __( 'Your Office 365 password or app password. For accounts with MFA enabled, you must use an app password.', 'authority-mailer-smtp' ),

	// ---- AWS SES Help Text ----
	'help_from_name_aws'                      => __( 'The name that will appear in the "From" field of emails sent through AWS SES.', 'authority-mailer-smtp' ),
	'help_from_email_aws'                     => __( 'The verified email address or domain in AWS SES that will be used as the sender.', 'authority-mailer-smtp' ),
	'help_aws_region'                         => __( 'Select the AWS region where your SES service is configured. This determines the SMTP endpoint.', 'authority-mailer-smtp' ),
	'help_aws_smtp_host'                      => __( 'The AWS SES SMTP endpoint for your region (e.g., email-smtp.us-east-1.amazonaws.com). This is automatically determined by your selected region.', 'authority-mailer-smtp' ),
	'help_aws_smtp_port'                      => __( 'The port number for AWS SES SMTP. Use 587 for TLS (recommended) or 465 for SSL.', 'authority-mailer-smtp' ),
	'help_aws_smtp_encryption'                => __( 'Choose the encryption method. TLS (port 587) is recommended for AWS SES.', 'authority-mailer-smtp' ),
	'help_aws_smtp_username'                  => __( 'Your AWS SES SMTP username. Generate this in the AWS SES console under SMTP Settings.', 'authority-mailer-smtp' ),
	'help_aws_smtp_password'                  => __( 'Your AWS SES SMTP password. Generate this in the AWS SES console under SMTP Settings. Note: This is different from your AWS IAM password.', 'authority-mailer-smtp' ),



	// ---- Admin Helpers / Common UI ----
	'common_free'                             => __( 'Free', 'authority-mailer-smtp' ),
	'common_by'                               => __( 'by', 'authority-mailer-smtp' ),
	'common_professional_email_delivery'      => __( 'Professional Email Delivery', 'authority-mailer-smtp' ),
	'common_send_test'                        => __( 'Send Test Email', 'authority-mailer-smtp' ),
	'common_upgrade_now'                      => __( 'Upgrade Now', 'authority-mailer-smtp' ),
	'pro_banner_title'                        => __( 'Additional Features Available in Authority Mailer SMTP Pro', 'authority-mailer-smtp' ),
	'pro_banner_subtitle'                     => __( 'The free version includes core SMTP functionality with support for 14 email providers. Pro version helps to track opens, clicks, delivery trends over time and AI Insights.', 'authority-mailer-smtp' ),
	'pro_banner_17_providers'                 => __( '17 Email Providers', 'authority-mailer-smtp' ),
	'pro_banner_unlimited_logs'               => __( 'Unlimited Email Logs', 'authority-mailer-smtp' ),
	'pro_banner_open_click_tracking'          => __( 'Open & Click Tracking', 'authority-mailer-smtp' ),
	'pro_banner_gdpr_compliance'              => __( 'GDPR Compliance Tools', 'authority-mailer-smtp' ),
	'pro_banner_ai_insights'                  => __( 'AI-Powered Insights', 'authority-mailer-smtp' ),
	'pro_banner_smart_failover'               => __( 'Smart Failover Routing', 'authority-mailer-smtp' ),
	'pro_banner_email_templates'              => __( 'Email Templates Library', 'authority-mailer-smtp' ),
	'pro_banner_priority_support'             => __( 'Priority Support', 'authority-mailer-smtp' ),
	'pro_banner_upgrade_cta'                  => __( 'See what’s included in Pro →', 'authority-mailer-smtp' ),
	'pro_banner_limited_offer'                => __( '🕐 Limited: 40% OFF', 'authority-mailer-smtp' ),

	// Legacy banner strings (keep for backward compatibility)
	'pro_banner_bounce_handling'              => __( 'Bounce & Complaint Handling', 'authority-mailer-smtp' ),
	'pro_banner_advanced_analytics'           => __( 'Advanced Analytics Dashboard', 'authority-mailer-smtp' ),
	'pro_banner_spam_checker'                 => __( 'Spam Score Checker', 'authority-mailer-smtp' ),
	'pro_banner_geographic_tracking'          => __( 'Geographic & Device Tracking', 'authority-mailer-smtp' ),
	'pro_banner_email_health'                 => __( 'Email Health Monitoring', 'authority-mailer-smtp' ),
	'pro_banner_real_time_notifications'      => __( 'Real-time Notifications', 'authority-mailer-smtp' ),

	// Free plan limits for sidebar
	'onboarding_upgrade_to_unlock'            => __( 'Upgrade to Premium to unlock this provider', 'authority-mailer-smtp' ),
	'white_glove_badge'                       => __( 'Optional Service', 'authority-mailer-smtp' ),
	'white_glove_headline'                    => __( 'Fix Deliverability Issues Without Technical Hassle', 'authority-mailer-smtp' ),
	'white_glove_subheadline'                 => __( 'Let our team configure everything for you', 'authority-mailer-smtp' ),
	// White-Glove Benefits
	'white_glove_benefit_1'                   => __( '24-hour setup guarantee', 'authority-mailer-smtp' ),
	'white_glove_benefit_2'                   => __( 'All email providers configured', 'authority-mailer-smtp' ),
	'white_glove_benefit_3'                   => __( 'SPF/DKIM optimization', 'authority-mailer-smtp' ),
	'white_glove_benefit_4'                   => __( 'Testing & verification', 'authority-mailer-smtp' ),
	'white_glove_benefit_5'                   => __( 'Zero technical knowledge needed', 'authority-mailer-smtp' ),
	// White-Glove Pricing
	'white_glove_price'                       => __( '$39', 'authority-mailer-smtp' ),
	'white_glove_original_price'              => __( '$69', 'authority-mailer-smtp' ),
	'white_glove_price_label'                 => __( 'One-time setup fee', 'authority-mailer-smtp' ),
	'white_glove_save_badge'                  => __( 'SAVE $30', 'authority-mailer-smtp' ),
	// White-Glove Trust Signals
	'white_glove_rating'                      => __( '4.9/5', 'authority-mailer-smtp' ),
	'white_glove_social_proof'                => __( '287+ successful setups', 'authority-mailer-smtp' ),
	'white_glove_guarantee'                   => __( 'Money-back guarantee', 'authority-mailer-smtp' ),
	'white_glove_turnaround'                  => __( 'Average setup: 37 minutes', 'authority-mailer-smtp' ),
	// White-Glove CTA
	'white_glove_cta_button'                  => __( 'Get Expert Setup →', 'authority-mailer-smtp' ),
	'white_glove_cta_secondary'               => __( 'or continue with DIY setup', 'authority-mailer-smtp' ),
	// White-Glove Step-specific messages (1-4)
	'white_glove_step_1_message'              => __( 'Starting from scratch? Let our experts handle the technical setup while you focus on your business.', 'authority-mailer-smtp' ),
	'white_glove_step_2_message'              => __( 'Not sure which provider to choose? Our team will help you select and configure the best one for your needs.', 'authority-mailer-smtp' ),
	'white_glove_step_3_message'              => __( 'Configuration can be tricky. Skip the headache—let our experts do it for you with zero errors.', 'authority-mailer-smtp' ),
	'white_glove_step_4_message'              => __( 'Want guaranteed success? Our team will set up, test, and verify everything works perfectly.', 'authority-mailer-smtp' ),
	// White-Glove Disclaimer
	'white_glove_disclaimer'                  => __( 'This is an optional professional service. Authority Mailer SMTP is fully functional without this service.', 'authority-mailer-smtp' ),
	// White-Glove Sidebar Label for Accessibility
	'onboarding_sidebar_label'                => __( 'Onboarding wizard sidebar', 'authority-mailer-smtp' ),
	'tools_sidebar_label'                     => __( 'Tools Sidebar', 'authority-mailer-smtp' ),
	'onboarding_actions_label'                => __( 'Wizard navigation actions', 'authority-mailer-smtp' ),

	// ---- REVIEW REQUEST SYSTEM ----
	// Tier 1: Admin Notice
	/* translators: %d: number of emails sent */
	'review_notice_title'                     => __( 'Awesome! You\'ve successfully sent %d emails with Authority Mailer SMTP.', 'authority-mailer-smtp' ),
	'review_notice_body'                      => __( 'Could you take 2 minutes to leave us a 5-star review? It helps others discover Authority Mailer!', 'authority-mailer-smtp' ),
	'review_btn_leave_review'                 => __( '⭐ Sure, I\'ll Leave a Review', 'authority-mailer-smtp' ),
	'review_btn_maybe_later'                  => __( 'Maybe Later', 'authority-mailer-smtp' ),
	'review_btn_already_did'                  => __( 'I Already Did', 'authority-mailer-smtp' ),

	// Tier 2: Settings Footer
	'review_footer_text'                      => __( 'Loving Authority Mailer SMTP?', 'authority-mailer-smtp' ),
	'review_footer_link'                      => __( 'Leave us a review!', 'authority-mailer-smtp' ),

	// Tier 3: Success Toast
	'review_toast_success'                    => __( 'Email sent successfully!', 'authority-mailer-smtp' ),
	'review_toast_prompt'                     => __( 'Enjoying Authority Mailer?', 'authority-mailer-smtp' ),
	'review_toast_link'                       => __( 'Leave a review', 'authority-mailer-smtp' ),

	// ---- FREE VS PRO PAGE ----
	'free_vs_pro_page_title'                  => __( 'Authority Mailer SMTP – Free vs Pro', 'authority-mailer-smtp' ),

	// Enhanced intro section
	'free_vs_pro_intro_heading'               => __( 'Choose the Right Plan for Your Needs', 'authority-mailer-smtp' ),
	'free_vs_pro_intro'                       => __( 'Authority Mailer SMTP Free provides everything most WordPress sites need for reliable email delivery. The free version includes professional SMTP support for 14 email providers, email logging, and delivery tracking—perfect for most websites.', 'authority-mailer-smtp' ),
	'free_vs_pro_intro_secondary'             => __( 'If you need advanced analytics, engagement tracking, or compliance tools, the Pro version adds powerful features for businesses with demanding email requirements.', 'authority-mailer-smtp' ),

	// Recommended badge and CTA
	'free_vs_pro_recommended_badge'           => __( 'Recommended', 'authority-mailer-smtp' ),
	'free_vs_pro_upgrade_cta'                 => __( 'Upgrade to Pro Today', 'authority-mailer-smtp' ),

	// Feature group headers
	'free_vs_pro_group_delivery'              => __( 'Email Delivery', 'authority-mailer-smtp' ),
	'free_vs_pro_group_analytics'             => __( 'Analytics & Tracking', 'authority-mailer-smtp' ),
	'free_vs_pro_group_reliability'           => __( 'Reliability & Compliance', 'authority-mailer-smtp' ),
	'free_vs_pro_group_support'               => __( 'Support & Services', 'authority-mailer-smtp' ),

	// Legacy strings (kept for backward compatibility)
	'free_vs_pro_footer_disclaimer'           => __( 'The free version provides reliable SMTP email delivery for WordPress. The Pro version adds advanced monitoring, analytics, and workflow tools for sites that require deeper visibility and control.', 'authority-mailer-smtp' ),
	'free_vs_pro_learn_more_cta'              => __( 'Learn more about Pro features', 'authority-mailer-smtp' ),

	// Table headers
	'free_vs_pro_feature_column'              => __( 'Feature', 'authority-mailer-smtp' ),
	'free_vs_pro_free_column'                 => __( 'Free', 'authority-mailer-smtp' ),
	'free_vs_pro_pro_column'                  => __( 'Pro', 'authority-mailer-smtp' ),

	// Table rows
	'free_vs_pro_core_smtp'                   => __( 'Core SMTP Email Sending', 'authority-mailer-smtp' ),
	'free_vs_pro_provider_support'            => __( 'Email Provider Support', 'authority-mailer-smtp' ),
	'free_vs_pro_14_providers'                => __( '14 Providers', 'authority-mailer-smtp' ),
	'free_vs_pro_17_providers'                => __( '17 Providers', 'authority-mailer-smtp' ),
	'free_vs_pro_gmail_outlook'               => __( 'Gmail / Outlook SMTP', 'authority-mailer-smtp' ),
	'free_vs_pro_custom_smtp'                 => __( 'Custom SMTP (Other SMTP)', 'authority-mailer-smtp' ),
	'free_vs_pro_email_logs'                  => __( 'Email Logs', 'authority-mailer-smtp' ),
	'free_vs_pro_basic'                       => __( 'Basic', 'authority-mailer-smtp' ),
	'free_vs_pro_unlimited'                   => __( 'Unlimited', 'authority-mailer-smtp' ),
	'free_vs_pro_delivery_status'             => __( 'Email Delivery Status', 'authority-mailer-smtp' ),
	'free_vs_pro_open_click_tracking'         => __( 'Open & Click Tracking', 'authority-mailer-smtp' ),
	'free_vs_pro_bounce_spam'                 => __( 'Bounce & Spam Handling', 'authority-mailer-smtp' ),
	'free_vs_pro_analytics_dashboard'         => __( 'Email Analytics Dashboard', 'authority-mailer-smtp' ),
	'free_vs_pro_delivery_trends'             => __( 'Delivery Trends Over Time', 'authority-mailer-smtp' ),
	'free_vs_pro_ai_insights'                 => __( 'AI-Powered Insights', 'authority-mailer-smtp' ),
	'free_vs_pro_webhook_receiver'            => __( 'Webhook Receiver', 'authority-mailer-smtp' ),
	'free_vs_pro_smart_failover'              => __( 'Smart Failover Routing', 'authority-mailer-smtp' ),
	'free_vs_pro_email_templates'             => __( 'Email Templates Library', 'authority-mailer-smtp' ),
	'free_vs_pro_gdpr_compliance'             => __( 'GDPR Compliance Tools', 'authority-mailer-smtp' ),
	'free_vs_pro_geographic_analytics'        => __( 'Geographic Analytics', 'authority-mailer-smtp' ),
	'free_vs_pro_priority_support'            => __( 'Priority Support', 'authority-mailer-smtp' ),

	// Feature descriptions (helper text for Pro features)
	'free_vs_pro_desc_open_click_tracking'    => __( 'Track email opens and link clicks for engagement insights', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_bounce_spam'            => __( 'Automatically handle bounces and spam complaints to maintain sender reputation', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_analytics_dashboard'    => __( 'Visual dashboard with delivery rates, trends, and engagement metrics', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_delivery_trends'        => __( 'Track email performance over time with detailed trend analysis', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_ai_insights'            => __( 'AI-powered recommendations to improve email deliverability', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_webhook_receiver'       => __( 'Receive real-time delivery events from email providers', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_smart_failover'         => __( 'Automatically switch to backup providers if primary fails', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_email_templates'        => __( 'Professional email templates for common WordPress notifications', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_gdpr_compliance'        => __( 'Tools to help you comply with GDPR email tracking requirements', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_geographic_analytics'   => __( 'See where your emails are being opened geographically', 'authority-mailer-smtp' ),
	'free_vs_pro_desc_priority_support'       => __( 'Get priority email and chat support from our expert team', 'authority-mailer-smtp' ),

	// ---- TOOLS PAGE ----
	'tools_page_title'                        => __( 'Email Deliverability Checker', 'authority-mailer-smtp' ),
	'tools_page_description'                  => __( 'Check your domain\'s email configuration and deliverability score', 'authority-mailer-smtp' ),
	'tools_domain_label'                      => __( 'Domain Name', 'authority-mailer-smtp' ),
	'tools_run_check'                         => __( 'Run Check', 'authority-mailer-smtp' ),
	'tools_run_again'                         => __( 'Run Again', 'authority-mailer-smtp' ),
	'tools_checking'                          => __( 'Checking...', 'authority-mailer-smtp' ),
	'tools_results_for'                       => __( 'Results for', 'authority-mailer-smtp' ),
	'tools_spf_record'                        => __( 'SPF Record', 'authority-mailer-smtp' ),
	'tools_spf_description'                   => __( 'SPF (Sender Policy Framework) helps prevent email spoofing by specifying which mail servers are authorized to send email on behalf of your domain.', 'authority-mailer-smtp' ),
	'tools_dkim_signature'                    => __( 'DKIM Signature', 'authority-mailer-smtp' ),
	'tools_dkim_description'                  => __( 'DKIM (DomainKeys Identified Mail) adds a digital signature to your emails to verify they haven\'t been altered in transit.', 'authority-mailer-smtp' ),
	'tools_dmarc_policy'                      => __( 'DMARC Policy', 'authority-mailer-smtp' ),
	'tools_dmarc_description'                 => __( 'DMARC (Domain-based Message Authentication) tells receiving servers how to handle emails that fail SPF or DKIM checks.', 'authority-mailer-smtp' ),
	'tools_mx_records'                        => __( 'MX Records', 'authority-mailer-smtp' ),
	'tools_mx_description'                    => __( 'MX (Mail Exchange) records specify which mail servers accept emails for your domain.', 'authority-mailer-smtp' ),
	'tools_reputation_score'                  => __( 'Reputation Score', 'authority-mailer-smtp' ),
	'tools_reputation_description'            => __( 'Your domain\'s sender reputation score (100 = excellent, 0 = poor). This affects email deliverability.', 'authority-mailer-smtp' ),
	'tools_blacklist_status'                  => __( 'Blacklist Status', 'authority-mailer-smtp' ),
	'tools_blacklist_description'             => __( 'Checks if your domain is listed on common email blacklists that could affect email delivery.', 'authority-mailer-smtp' ),
	'tools_error_empty_domain'                => __( 'Please enter a domain name', 'authority-mailer-smtp' ),
	'tools_error_invalid_domain'              => __( 'Please enter a valid domain name', 'authority-mailer-smtp' ),
	'tools_error_check_failed'                => __( 'Failed to check domain. Please try again.', 'authority-mailer-smtp' ),
	'tools_error_network'                     => __( 'An error occurred. Please check your connection and try again.', 'authority-mailer-smtp' ),
	'tools_reputation_excellent'              => __( 'Excellent reputation score: %d/100. Your domain is well-configured for email delivery.', 'authority-mailer-smtp' ),
	'tools_reputation_good'                   => __( 'Good reputation score: %d/100. Consider improving SPF, DKIM, and DMARC records.', 'authority-mailer-smtp' ),
	'tools_reputation_fair'                   => __( 'Fair reputation score: %d/100. Action recommended to improve email deliverability.', 'authority-mailer-smtp' ),
	'tools_reputation_poor'                   => __( 'Poor reputation score: %d/100. Immediate action required to fix email configuration.', 'authority-mailer-smtp' ),

	// ---- DELIVERABILITY CHECKER UX IMPROVEMENTS ----
	// Overall Summary Banner
	'tools_summary_success_title'             => __( 'Your Domain Is Well Configured', 'authority-mailer-smtp' ),
	'tools_summary_success_description'       => __( 'All critical email authentication checks have passed. Your domain is properly configured for reliable email delivery.', 'authority-mailer-smtp' ),
	'tools_summary_warning_title'             => __( 'Deliverability Needs Attention', 'authority-mailer-smtp' ),
	'tools_summary_warning_description'       => __( 'Some email authentication checks have failed. Review the recommendations below to improve your email deliverability.', 'authority-mailer-smtp' ),

	// Action Buttons
	'tools_action_view_guide'                 => __( 'View Setup Guide', 'authority-mailer-smtp' ),
	'tools_action_fix_for_me'                 => __( 'Fix It for Me', 'authority-mailer-smtp' ),

	// Pro Feature Nudges
	'tools_pro_nudge_monitoring'              => __( 'Want continuous monitoring & alerts? Unlock Pro', 'authority-mailer-smtp' ),

	// Social Proof
	'tools_social_proof'                      => __( '⭐ Trusted by 287+ successful email setups', 'authority-mailer-smtp' ),

	// DIY Section
	'tools_diy_section_title'                 => __( 'Fix it yourself', 'authority-mailer-smtp' ),
	'tools_diy_guide_link'                    => __( 'Complete Email Deliverability Guide', 'authority-mailer-smtp' ),
	'tools_diy_dns_link'                      => __( 'DNS Records Setup Tutorial', 'authority-mailer-smtp' ),
	'tools_diy_troubleshooting_link'          => __( 'Troubleshooting Common Issues', 'authority-mailer-smtp' ),
	'tools_diy_rerun_text'                    => __( '🔄 You can re-run this check anytime to verify your changes', 'authority-mailer-smtp' ),

);

/**
 * Allow developers to filter the strings array.
 *
 * @since 1.0.0
 *
 * @param array $AUTHORITY_MAILER_STRINGS The strings array.
 */
// phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Global constant convention.
$AUTHORITY_MAILER_STRINGS = apply_filters( 'authority_mailer_smtp_strings', $AUTHORITY_MAILER_STRINGS );
