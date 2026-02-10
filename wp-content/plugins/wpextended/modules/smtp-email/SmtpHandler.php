<?php

namespace Wpextended\Modules\SmtpEmail;

use Wpextended\Includes\Utils;

class SmtpHandler
{
    /**
     * Initialize the SMTP handler
     */
    public function init()
    {
        // Override wp_mail using pre_wp_mail filter
        add_filter('pre_wp_mail', [$this, 'handleWpMail'], 10, 2);

        // Hook into phpmailer_init to ensure FROM address is set correctly after WordPress 6.9 changes
        // This runs after WordPress initializes PHPMailer but before sending
        add_action('phpmailer_init', [$this, 'ensureFromAddress'], 999);

        // Prevent WordPress 6.9 from overriding custom FROM addresses
        add_filter('wp_mail_from', [$this, 'filterFromEmail'], 999);
        add_filter('wp_mail_from_name', [$this, 'filterFromName'], 999);
    }

    /**
     * Handle wp_mail using pre_wp_mail filter
     */
    public function handleWpMail($null, $atts)
    {
        // Get settings using Utils::getSettings
        $settings = Utils::getSettings('smtp-email');

        // Validate required settings
        if (empty($settings['host']) || empty($settings['username']) || empty($settings['password'])) {
            $error = __('SMTP settings are incomplete. Please configure host, username and password.', WP_EXTENDED_TEXT_DOMAIN);
            $this->emailResult($atts['to'], $atts['subject'], 'Fail', $error);
            return false;
        }

        // Extract email data
        $to = $atts['to'];
        $subject = $atts['subject'];
        $message = $atts['message'];
        $headers = $atts['headers'];
        $attachments = $atts['attachments'];

        // Format recipients for logging
        $recipients = is_array($to) ? implode(', ', $to) : $to;

        try {
            // Get PHPMailer instance
            global $phpmailer;
            if (!($phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer)) {
                require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                $phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            }

            // Configure PHPMailer for SMTP
            $phpmailer->isSMTP();
            $phpmailer->SMTPAuth = true;
            $phpmailer->Host = $settings['host'] ?? '';
            $phpmailer->Port = $settings['port'] ?? '';

            // Set encryption based on port
            if ($settings['port'] == 465) {
                $phpmailer->SMTPSecure = 'ssl';
            } else {
                $phpmailer->SMTPSecure = 'tls';
            }

            $phpmailer->Username = $settings['username'] ?? '';
            $phpmailer->Password = $settings['password'] ?? '';
            $phpmailer->Timeout = 10;

            // Set UTF-8 encoding to properly handle international characters
            $phpmailer->CharSet = 'UTF-8';
            $phpmailer->Encoding = 'quoted-printable';

            // Parse headers first to check what user has set
            $userHeaders = $this->parseHeaders($headers);
            $processedHeaders = array();

            // Store original FROM values for WordPress 6.9 compatibility
            $userFromEmail = $userHeaders['From']['email'] ?? null;
            $userFromName = $userHeaders['From']['name'] ?? null;

            // Determine the FROM email address
            $finalFromEmail = null;
            $finalFromName = null;

            // Priority 1: Force settings (if enabled)
            if (($settings['force_from_email'] ?? false) && !empty($settings['from_email'])) {
                $finalFromEmail = $settings['from_email'];
            } elseif ($userFromEmail) {
                // Priority 2: User-provided FROM header (respect plugin headers)
                $finalFromEmail = $userFromEmail;
            } else {
                // Priority 3: Default to SMTP username or configured from_email
                $finalFromEmail = !empty($settings['from_email']) ? $settings['from_email'] : ($settings['username'] ?? '');
            }

            // Determine the FROM name
            if (($settings['force_from_name'] ?? false) && !empty($settings['from_name'])) {
                $finalFromName = $settings['from_name'];
            } elseif ($userFromName && !($settings['force_from_name'] ?? false)) {
                // User provided FromName and force_from_name is disabled, use user's name
                $finalFromName = $userFromName;
            } elseif (!empty($settings['from_name'])) {
                // Use configured from_name as fallback
                $finalFromName = $settings['from_name'];
            }

            // Set FROM address - do this early but we'll ensure it's correct after processing headers
            if (!empty($finalFromEmail)) {
                $phpmailer->From = sanitize_email($finalFromEmail);
            }
            if (!empty($finalFromName)) {
                $phpmailer->FromName = $finalFromName;
            }

            // Set custom X-Mailer header only if user hasn't set one
            $hasXMailer = false;
            foreach ($userHeaders as $key => $value) {
                if (strtolower($key) === 'x-mailer') {
                    $hasXMailer = true;
                    break;
                }
            }
            if (!$hasXMailer) {
                $phpmailer->XMailer = 'WP Extended v' . WP_EXTENDED_VERSION;
            }

            // Set recipients
            if (is_array($to)) {
                foreach ($to as $recipient) {
                    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception(sprintf(__('Invalid recipient email address: %s', WP_EXTENDED_TEXT_DOMAIN), $recipient));
                    }
                    $phpmailer->addAddress($recipient);
                }
            } else {
                if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception(sprintf(__('Invalid recipient email address: %s', WP_EXTENDED_TEXT_DOMAIN), $to));
                }
                $phpmailer->addAddress($to);
            }

            // Set subject and message
            $phpmailer->Subject = $subject;
            $phpmailer->Body = $message;

            // Process headers
            $this->processHeaders($headers, $phpmailer, $processedHeaders);

            // If no Content-Type header was provided by user, set default with UTF-8
            if (empty($phpmailer->ContentType)) {
                $phpmailer->ContentType = 'text/html; charset=UTF-8';
            }

            // WordPress 6.9 compatibility: Ensure FROM address is set correctly after all header processing
            // This prevents WordPress from overriding custom FROM addresses set by plugins
            if (!empty($finalFromEmail) && filter_var($finalFromEmail, FILTER_VALIDATE_EMAIL)) {
                $phpmailer->From = sanitize_email($finalFromEmail);
            }
            if (!empty($finalFromName)) {
                $phpmailer->FromName = $finalFromName;
            }

            // WordPress 6.9 compatibility: Ensure Envelope-From (Sender) and Return-Path are set correctly
            // These must match the FROM address for proper mail delivery
            $envelopeFrom = !empty($finalFromEmail) ? $finalFromEmail : ($settings['username'] ?? '');
            if (!empty($envelopeFrom) && filter_var($envelopeFrom, FILTER_VALIDATE_EMAIL)) {
                $phpmailer->Sender = sanitize_email($envelopeFrom);
                $phpmailer->ReturnPath = sanitize_email($envelopeFrom);
            }

            // Handle attachments
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (!file_exists($attachment)) {
                        throw new \Exception(sprintf(__('Attachment file not found: %s', WP_EXTENDED_TEXT_DOMAIN), $attachment));
                    }
                    $phpmailer->addAttachment($attachment);
                }
            }

            // Send the email
            $result = $phpmailer->send();

            // Log the email if logging is enabled
            $this->emailResult($recipients, $subject, 'Success', '');

            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            // Log the failure if logging is enabled
            $error = sprintf(
                __('%s (Code: %s)', WP_EXTENDED_TEXT_DOMAIN),
                $e->getMessage(),
                $e->getCode()
            );
            $this->emailResult($recipients, $subject, 'Fail', $error);

            return false;
        } catch (\Exception $e) {
            // Log the failure if logging is enabled
            $error = sprintf(
                __('General Error: %s', WP_EXTENDED_TEXT_DOMAIN),
                $e->getMessage()
            );
            $this->emailResult($recipients, $subject, 'Fail', $error);

            return false;
        }
    }

    /**
     * Process email headers and apply them to PHPMailer
     *
     * @param array|string $headers Headers from wp_mail
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @param array $processedHeaders Array to track processed headers
     * @return void
     */
    private function processHeaders($headers, $phpmailer, &$processedHeaders)
    {
        if (empty($headers)) {
            return;
        }

        if (is_array($headers)) {
            $this->processHeadersArray($headers, $phpmailer, $processedHeaders);
        } else {
            $this->processSingleHeader($headers, $phpmailer);
        }
    }

    /**
     * Process headers array
     *
     * @param array $headers Array of header strings
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @param array $processedHeaders Array to track processed headers
     * @return void
     */
    private function processHeadersArray($headers, $phpmailer, &$processedHeaders)
    {
        foreach ($headers as $header) {
            $this->processHeader($header, $phpmailer, $processedHeaders);
        }
    }

    /**
     * Process a single header string
     *
     * @param string $header Header string
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @param array $processedHeaders Array to track processed headers
     * @return void
     */
    private function processHeader($header, $phpmailer, &$processedHeaders)
    {
        if (empty($header) || !is_string($header)) {
            return;
        }

        $headerLower = strtolower(trim($header));

        if ($this->isContentTypeHeader($headerLower)) {
            $this->processContentTypeHeader($header, $phpmailer, $processedHeaders);
            return;
        }

        if ($this->isFromHeader($headerLower)) {
            $processedHeaders['from'] = true;
            return;
        }

        if ($this->isReplyToHeader($headerLower)) {
            $this->processReplyToHeader($header, $phpmailer);
            $processedHeaders['reply-to'] = true;
            return;
        }

        if ($this->isCcHeader($headerLower)) {
            $this->processCcHeader($header, $phpmailer);
            $processedHeaders['cc'] = true;
            return;
        }

        if ($this->isBccHeader($headerLower)) {
            $this->processBccHeader($header, $phpmailer);
            $processedHeaders['bcc'] = true;
            return;
        }

        if ($this->isXMailerHeader($headerLower)) {
            $processedHeaders['x-mailer'] = true;
            return;
        }

        // Add other custom headers
        $phpmailer->addCustomHeader($header);
    }

    /**
     * Process single header string (non-array format)
     *
     * @param string $headers Single header string
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @return void
     */
    private function processSingleHeader($headers, $phpmailer)
    {
        $headerLower = strtolower(trim($headers));

        if ($this->isContentTypeHeader($headerLower)) {
            $contentType = $this->normalizeContentType($headers);
            $phpmailer->ContentType = $contentType;
            return;
        }

        $phpmailer->addCustomHeader($headers);
    }

    /**
     * Check if header is Content-Type
     *
     * @param string $headerLower Lowercase header string
     * @return bool
     */
    private function isContentTypeHeader($headerLower)
    {
        return strpos($headerLower, 'content-type:') === 0;
    }

    /**
     * Check if header is From
     *
     * @param string $headerLower Lowercase header string
     * @return bool
     */
    private function isFromHeader($headerLower)
    {
        return strpos($headerLower, 'from:') === 0;
    }

    /**
     * Check if header is Reply-To
     *
     * @param string $headerLower Lowercase header string
     * @return bool
     */
    private function isReplyToHeader($headerLower)
    {
        return strpos($headerLower, 'reply-to:') === 0;
    }

    /**
     * Check if header is CC
     *
     * @param string $headerLower Lowercase header string
     * @return bool
     */
    private function isCcHeader($headerLower)
    {
        return strpos($headerLower, 'cc:') === 0;
    }

    /**
     * Check if header is BCC
     *
     * @param string $headerLower Lowercase header string
     * @return bool
     */
    private function isBccHeader($headerLower)
    {
        return strpos($headerLower, 'bcc:') === 0;
    }

    /**
     * Check if header is X-Mailer
     *
     * @param string $headerLower Lowercase header string
     * @return bool
     */
    private function isXMailerHeader($headerLower)
    {
        return strpos($headerLower, 'x-mailer:') === 0;
    }

    /**
     * Process Content-Type header
     *
     * @param string $header Header string
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @param array $processedHeaders Array to track processed headers
     * @return void
     */
    private function processContentTypeHeader($header, $phpmailer, &$processedHeaders)
    {
        if (isset($processedHeaders['content-type'])) {
            return;
        }

        $contentType = $this->normalizeContentType($header);
        $phpmailer->ContentType = $contentType;
        $processedHeaders['content-type'] = true;
    }

    /**
     * Normalize Content-Type header to ensure charset=UTF-8
     *
     * @param string $header Header string
     * @return string Normalized Content-Type value
     */
    private function normalizeContentType($header)
    {
        $contentType = preg_replace('/^content-type:\s*/i', '', $header);

        if (stripos($contentType, 'charset=') === false) {
            $contentType .= '; charset=UTF-8';
        } else {
            $contentType = preg_replace('/charset=[^;]+/i', 'charset=UTF-8', $contentType);
        }

        return $contentType;
    }

    /**
     * Process Reply-To header
     *
     * @param string $header Header string
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @return void
     */
    private function processReplyToHeader($header, $phpmailer)
    {
        $replyTo = preg_replace('/^reply-to:\s*/i', '', $header);
        $this->parseEmailAddress($replyTo, $phpmailer, 'addReplyTo');
    }

    /**
     * Process CC header
     *
     * @param string $header Header string
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @return void
     */
    private function processCcHeader($header, $phpmailer)
    {
        $cc = preg_replace('/^cc:\s*/i', '', $header);
        $this->parseEmailAddress($cc, $phpmailer, 'addCC');
    }

    /**
     * Process BCC header
     *
     * @param string $header Header string
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @return void
     */
    private function processBccHeader($header, $phpmailer)
    {
        $bcc = preg_replace('/^bcc:\s*/i', '', $header);
        $this->parseEmailAddress($bcc, $phpmailer, 'addBCC');
    }

    /**
     * Parse headers array to extract header values
     *
     * @param array|string $headers Headers from wp_mail
     * @return array Parsed headers
     */
    private function parseHeaders($headers)
    {
        $parsed = array();

        if (empty($headers)) {
            return $parsed;
        }

        $headersArray = is_array($headers) ? $headers : array($headers);

        foreach ($headersArray as $header) {
            if (empty($header) || !is_string($header)) {
                continue;
            }

            $header = trim($header);
            $colonPos = strpos($header, ':');
            if ($colonPos === false) {
                continue;
            }

            $headerName = trim(substr($header, 0, $colonPos));
            $headerValue = trim(substr($header, $colonPos + 1));
            $headerNameLower = strtolower($headerName);

            // Parse From header
            if ($headerNameLower === 'from') {
                $parsed['From'] = $this->parseEmailHeaderValue($headerValue);
            } else {
                // Store other headers
                $parsed[$headerName] = $headerValue;
            }
        }

        return $parsed;
    }

    /**
     * Parse email header value (e.g., "Name <email@example.com>" or "email@example.com")
     *
     * @param string $value Header value
     * @return array Array with 'email' and 'name' keys
     */
    private function parseEmailHeaderValue($value)
    {
        $result = array('email' => null, 'name' => null);

        // Check if it's in format "Name <email@example.com>"
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $value, $matches)) {
            $result['name'] = trim($matches[1], ' "\'');
            $result['email'] = trim($matches[2]);
        } else {
            // Just email address
            $result['email'] = trim($value);
        }

        return $result;
    }

    /**
     * Parse email address(es) and add to PHPMailer using specified method
     * RFC 2822 compliant parser that handles quoted display names with commas
     *
     * @param string $addresses Comma-separated email addresses
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     * @param string $method Method name to call (addReplyTo, addCC, addBCC)
     * @return void
     */
    private function parseEmailAddress($addresses, $phpmailer, $method)
    {
        if (empty($addresses) || !method_exists($phpmailer, $method)) {
            return;
        }

        // Split addresses using RFC 2822 compliant parsing
        $emailList = $this->splitEmailAddresses($addresses);

        foreach ($emailList as $email) {
            $email = trim($email);
            if (empty($email)) {
                continue;
            }

            $parsed = $this->parseEmailHeaderValue($email);
            if (!empty($parsed['email']) && filter_var($parsed['email'], FILTER_VALIDATE_EMAIL)) {
                if (!empty($parsed['name'])) {
                    $phpmailer->$method($parsed['email'], $parsed['name']);
                } else {
                    $phpmailer->$method($parsed['email']);
                }
            }
        }
    }

    /**
     * Split comma-separated email addresses respecting RFC 2822 rules
     * Handles quoted display names and angle brackets properly
     *
     * @param string $addresses Comma-separated email addresses string
     * @return array Array of individual email address strings
     */
    private function splitEmailAddresses($addresses)
    {
        $result = array();
        $length = strlen($addresses);
        $current = '';
        $inQuotes = false;
        $inAngleBrackets = false;
        $quoteChar = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $addresses[$i];
            $prevChar = ($i > 0) ? $addresses[$i - 1] : '';

            // Handle quote characters (single or double)
            if (($char === '"' || $char === "'") && $prevChar !== '\\') {
                if (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                }
                $current .= $char;
                continue;
            }

            // Track angle brackets (for email addresses like <email@example.com>)
            if ($char === '<' && !$inQuotes) {
                $inAngleBrackets = true;
                $current .= $char;
                continue;
            }

            if ($char === '>' && !$inQuotes) {
                $inAngleBrackets = false;
                $current .= $char;
                continue;
            }

            // Handle comma - only split if not inside quotes or angle brackets
            if ($char === ',' && !$inQuotes && !$inAngleBrackets) {
                $trimmed = trim($current);
                if (!empty($trimmed)) {
                    $result[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        // Add the last address
        $trimmed = trim($current);
        if (!empty($trimmed)) {
            $result[] = $trimmed;
        }

        return $result;
    }

    /**
     * Send SMTP test email
     */
    public function sendTestEmail($email, $errorReason = '')
    {
        $subject = __('Test email from WP Extended SMTP Module', WP_EXTENDED_TEXT_DOMAIN);
        // Validate email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->emailResult($email, $subject, 'Fail', __('Invalid test email address', WP_EXTENDED_TEXT_DOMAIN));
            return false;
        }

        // Get email content (HTML template)
        $message = $this->getTestEmailContent();

        // Set headers for HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Add error reason to message if provided
        if (!empty($errorReason)) {
            $message .= "<br><br><strong>Error Message:</strong> " . $errorReason;
        }

        if (empty($email)) {
            $email = get_option('admin_email');
        }

        wp_mail($email, $subject, $message, $headers);

        global $phpmailer;

        $has_error = isset($phpmailer->ErrorInfo) && !empty($phpmailer->ErrorInfo);

        return $has_error ? $phpmailer->ErrorInfo : __('Test email sent successfully.', WP_EXTENDED_TEXT_DOMAIN);
    }

    /**
     * Get test email HTML content
     */
    public function getTestEmailContent()
    {
        $user = wp_get_current_user();
        $username = $user->user_login;
        $displayName = $user->display_name;
        $name = $user->first_name != '' ? $user->first_name : $displayName;
        $websiteUrl = get_bloginfo('url');
        $testTimestamp = current_time('mysql');
        $emailAddress = $user->user_email;

        // Get the email template
        $template = file_get_contents(__DIR__ . '/TestEmail.php');

        // Replace placeholders
        $replacements = array(
            '{{logo_url}}' => WP_EXTENDED_URL . 'admin/assets/images/wpe-logo-horizontal.png',
            '{{name}}' => $name,
            '{{username}}' => $username,
            '{{email_address}}' => $emailAddress,
            '{{website_url}}' => $websiteUrl,
            '{{test_timestamp}}' => $testTimestamp,
        );

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Trigger the email result
     *
     * @param string $to The email address of the recipient
     * @param string $subject The subject of the email
     * @param string $status The status of the email
     * @param string $errorReason The reason for the email failure
     * @return void
     */
    public function emailResult($to, $subject, $status, $errorReason = '')
    {
        do_action('wpextended/smtp-email/email_sent', $to, $subject, $status, $errorReason, $this);

        if ($status === 'Fail') {
            do_action('wpextended/smtp-email/email_fail', $to, $subject, $status, $errorReason, $this);
        } else {
            do_action('wpextended/smtp-email/email_success', $to, $subject, $status, $errorReason, $this);
        }
    }

    /**
     * Ensure FROM address is set correctly after WordPress 6.9 initialization
     * This runs on phpmailer_init to prevent WordPress from overriding custom FROM addresses
     *
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer The PHPMailer instance
     * @return void
     */
    public function ensureFromAddress($phpmailer)
    {
        // Only process if this is an SMTP email (not handled by pre_wp_mail)
        // Check Mailer property directly since isSMTP() is a setter, not a getter
        if ($phpmailer->Mailer !== 'smtp') {
            return;
        }

        $settings = Utils::getSettings('smtp-email');

        // Determine the FROM email address
        $fromEmail = null;
        if (($settings['force_from_email'] ?? false) && !empty($settings['from_email'])) {
            $fromEmail = $settings['from_email'];
            $phpmailer->From = sanitize_email($fromEmail);
        } elseif (empty($phpmailer->From) || $phpmailer->From === 'wordpress@' . parse_url(home_url(), PHP_URL_HOST)) {
            // WordPress 6.9 default - replace with SMTP username or configured from_email
            $fromEmail = !empty($settings['from_email']) ? $settings['from_email'] : ($settings['username'] ?? '');
            if (!empty($fromEmail)) {
                $phpmailer->From = sanitize_email($fromEmail);
            }
        } else {
            // Use existing From address
            $fromEmail = $phpmailer->From;
        }

        // WordPress 6.9 compatibility: Set Envelope-From (Sender) and Return-Path
        // Only set if not already set or if WordPress 6.9 default needs to be replaced
        if (!empty($fromEmail) && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $sanitizedEmail = sanitize_email($fromEmail);
            // Only update if not already set correctly or if it's the WordPress default
            $wpDefault = 'wordpress@' . parse_url(home_url(), PHP_URL_HOST);
            if (empty($phpmailer->Sender) || $phpmailer->Sender === $wpDefault || $phpmailer->Sender !== $sanitizedEmail) {
                $phpmailer->Sender = $sanitizedEmail;
            }
            if (empty($phpmailer->ReturnPath) || $phpmailer->ReturnPath === $wpDefault || $phpmailer->ReturnPath !== $sanitizedEmail) {
                $phpmailer->ReturnPath = $sanitizedEmail;
            }
        }

        // If force_from_name is enabled, ensure it's set
        if (($settings['force_from_name'] ?? false) && !empty($settings['from_name'])) {
            $phpmailer->FromName = $settings['from_name'];
        } elseif (empty($phpmailer->FromName) && !empty($settings['from_name'])) {
            $phpmailer->FromName = $settings['from_name'];
        }
    }

    /**
     * Filter FROM email to prevent WordPress 6.9 from overriding custom addresses
     *
     * @param string $from_email The FROM email address
     * @return string The filtered FROM email address
     */
    public function filterFromEmail($from_email)
    {
        $settings = Utils::getSettings('smtp-email');

        // If force_from_email is enabled, use our setting
        if (($settings['force_from_email'] ?? false) && !empty($settings['from_email'])) {
            return sanitize_email($settings['from_email']);
        }

        // If WordPress 6.9 default is detected and we have a configured from_email, use it
        $wpDefault = 'wordpress@' . parse_url(home_url(), PHP_URL_HOST);
        if ($from_email === $wpDefault && !empty($settings['from_email'])) {
            return sanitize_email($settings['from_email']);
        }

        return $from_email;
    }

    /**
     * Filter FROM name to prevent WordPress 6.9 from overriding custom names
     *
     * @param string $from_name The FROM name
     * @return string The filtered FROM name
     */
    public function filterFromName($from_name)
    {
        $settings = Utils::getSettings('smtp-email');

        // If force_from_name is enabled, use our setting
        if (($settings['force_from_name'] ?? false) && !empty($settings['from_name'])) {
            return $settings['from_name'];
        }

        // If no name is set and we have a configured from_name, use it
        if (empty($from_name) && !empty($settings['from_name'])) {
            return $settings['from_name'];
        }

        return $from_name;
    }
}
