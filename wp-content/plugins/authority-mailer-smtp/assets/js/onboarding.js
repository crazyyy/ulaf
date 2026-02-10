/*!
 * onboarding.js — final validation + modal + localization-aware script
 *
 * Full onboarding client script with robust, provider-aware validation.
 * Preserves modal/popup handlers and card divider injection behavior.
 *
 * This revision ensures UI strings used by the step-4 flow are read from the
 * localized object (authorityMailerOnboarding / AUTHORITY_MAILEROnboarding / authority_mailer_onboarding) via t('key').
 */
(function (window, document, $) {
  'use strict';

  if (typeof $ === 'undefined' || !$.fn) {
    console.error('onboarding.js: jQuery not available.');
    return;
  }

  // Safe accessor for localized strings (supports authorityMailerOnboarding, AUTHORITY_MAILEROnboarding or authority_mailer_onboarding)
  function t(key) {
    if (!key) return '';
    var obj = window.authorityMailerOnboarding || window.AUTHORITY_MAILEROnboarding || window.authority_mailer_onboarding || {};
    // top-level keys are available directly
    // Also support nested strings object: strings.key
    if (obj.hasOwnProperty(key)) return obj[key];
    if (obj.strings && obj.strings.hasOwnProperty(key)) return obj.strings[key];
    return '';
  }

  function ajaxUrl() {
    var obj = window.authorityMailerOnboarding || window.AUTHORITY_MAILEROnboarding || window.authority_mailer_onboarding || {};
    return obj.ajax_url || window.ajaxurl || '/wp-admin/admin-ajax.php';
  }

  function ajaxNonce() {
    var obj = window.authorityMailerOnboarding || window.AUTHORITY_MAILEROnboarding || window.authority_mailer_onboarding || {};
    return obj.nonce || '';
  }

  function isValidEmail(email) {
    if (!email) return false;
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
  }

  // Domain validation helper
  function isValidDomain(domain) {
    if (!domain || typeof domain !== 'string') return false;
    domain = domain.trim();

    // allow localhost / single-label for local/dev environments (optional)
    if (domain === 'localhost') return true;

    // Basic checks: contains at least one dot and only allowed characters
    // - allows subdomains, letters, digits, hyphens and dots
    // - TLD must be alphabetic and at least 2 chars (simple, practical check)
    // - prevents leading/trailing hyphens in each label and empty labels
    var parts = domain.split('.');
    // Accept single-label if it contains only letters/digits/hyphen (useful for some local setups)
    if (parts.length < 2) {
      // treat single-label as invalid except for localhost handled above
      return false;
    }
    var labelRe = /^[a-zA-Z0-9-]+$/;
    for (var i = 0; i < parts.length; i++) {
      var label = parts[i];
      if (!label || label.length === 0) return false;
      if (label[0] === '-' || label[label.length - 1] === '-') return false;
      if (!labelRe.test(label)) return false;
    }
    var tld = parts[parts.length - 1];
    if (!/^[a-zA-Z]{2,}$/.test(tld)) return false;
    return true;
  }

  // Error node helpers
  function ensureErrorNode($field) {
    if (!$field || !$field.length) return $();
    var id = $field.attr('id') || '';
    var $err;
    if (id) {
      $err = $('#' + id + '-error');
      if (!$err.length) {
        $err = $('<div/>', {
          id: id + '-error',
          'class': 'wpmsl-input-error',
          'aria-live': 'polite'
        }).insertAfter($field);
      }
    } else {
      $err = $field.siblings('.wpmsl-input-error').first();
      if (!$err.length) {
        $err = $('<div/>', { 'class': 'wpmsl-input-error', 'aria-live': 'polite' }).insertAfter($field);
      }
    }
    return $err;
  }

  function showFieldError($field, msgKey, fallback) {
    var msg = t(msgKey) || (fallback || '');
    var $err = ensureErrorNode($field);
    if (!$err.length) return;

    if ($err.text().trim() !== msg.trim()) {
      $err.text(msg).addClass('is-visible').show();
    } else {
      $err.addClass('is-visible').show();
    }

    try {
      $err.css({ 'color': '#c53030', 'font-weight': '500' });
    } catch (e) {}

    if ($field && $field.length) {
      $field.addClass('wpmsl-input-invalid').attr('aria-invalid', 'true');
      try { $field.css('border-color', '#c53030'); } catch (e) {}
    }
  }

  function clearFieldError($field) {
    if (!$field || !$field.length) return;
    var id = $field.attr('id');
    var $err = id ? $('#' + id + '-error') : $field.siblings('.wpmsl-input-error').first();
    if ($err && $err.length) {
      $err.text('').removeClass('is-visible').hide();
      try { $err.css({ 'color': '', 'font-weight': '' }); } catch (e) {}
    }
    $field.removeClass('wpmsl-input-invalid').removeAttr('aria-invalid');
    try { $field.css('border-color', ''); } catch (e) {}
  }

  function setFormStatus($form, msgKeyOrText, isError) {
    var msg = (typeof msgKeyOrText === 'string' && msgKeyOrText.indexOf('i18n_') === 0) ? t(msgKeyOrText) : msgKeyOrText || '';
    var $status = $form.find('#wpmsl-mailer-form-status');
    if (!$status.length) {
      $status = $('<div/>', {
        id: 'wpmsl-mailer-form-status',
        'class': 'wpmsl-form-status',
        'aria-live': 'polite'
      }).appendTo($form);
    }
    $status.toggleClass('error', !!isError).toggleClass('success', !isError && msg).text(msg).show();

    try {
      if (isError) {
        $status.css({ 'color': '#c53030', 'background': '#fee', 'border-color': '#f5c6cb', 'padding': '10px', 'border-radius': '4px', 'border': '1px solid #f5c6cb' });
      } else if (msg) {
        // Success styling - green/teal color scheme
        $status.css({ 'color': '#047857', 'background': '#d1fae5', 'border-color': '#6ee7b7', 'padding': '10px', 'border-radius': '4px', 'border': '1px solid #6ee7b7', 'font-weight': '500' });
      } else {
        $status.css({ 'color': '', 'background': '', 'border-color': '', 'padding': '', 'border-radius': '', 'border': '', 'font-weight': '' });
      }
    } catch (e) {}
  }

  // Helpers for provider detection
  function getProviderFromForm($form) {
    if ($form && $form.length) return ($form.data('mailer-form') || '').toString().toLowerCase();
    try {
      var u = new URL(window.location.href);
      return (u.searchParams.get('provider') || '').toLowerCase();
    } catch (e) {
      return '';
    }
  }

  function getCurrentProvider() {
    var p = $('.wpmsl-onboarding-wrap').data('current-provider');
    if (p) return p.toString().toLowerCase();
    try {
      var u = new URL(window.location.href);
      return (u.searchParams.get('provider') || '').toLowerCase();
    } catch (e) {
      return '';
    }
  }

  function isForceFromEmailEnabled($form) {
    if (!$form || !$form.length) return false;
    var $force = $form.find('input[name$="_force_from_email"], input[id$="_force_from_email"]').first();
    return $force.length ? !!$force.prop('checked') : false;
  }

  // Provider friendly names (used in status)
  function providerFriendlyName(key) {
    var map = {
      'sendlayer': 'SendLayer',
      'smtpcom': 'SMTP.com',
      'brevo': 'Brevo',
      'aws': 'Amazon SES',
      'elastic': 'Elastic Email',
      'gmail': 'Google / Gmail',
      'mailersend': 'MailerSend',
      'mailgun': 'Mailgun',
      'mailjet': 'Mailjet',
      'mandrill': 'Mandrill',
      'office365': '365 / Outlook',
      'postmark': 'Postmark',
      'sendgrid': 'SendGrid',
      'smtp2go': 'SMTP2GO',
      'sparkpost': 'SparkPost',
      'zoho': 'Zoho Mail',
      'other': 'Other SMTP',
      'smtp': 'Other SMTP'
    };
    return map[key] || (key ? key.charAt(0).toUpperCase() + key.slice(1) : 'Provider');
  }

  // validateProviderForm: provider-aware validation
  function validateProviderForm($form) {
    var result = { valid: true, focus: null };
    if (!$form || !$form.length) return result;

    var provider = getProviderFromForm($form);

    // API key explicit
    var $api = $form.find('input[name$="_api_key"], input[name*="api_key"], input[id*="api_key"]').filter(':visible').first();
    if ($api.length) {
      var v = $.trim($api.val() || '');
      if (v === '') {
        showFieldError($api, 'i18n_api_key_required', 'Please enter your API Key.');
        result.valid = false;
        result.focus = result.focus || $api;
      } else {
        clearFieldError($api);
      }
    }

    // secret-like detection
    var $secretLike = $form.find('input[name*="secret"], input[id*="secret"], input[name*="client_secret"]').filter(':visible').first();
    if ($secretLike.length) {
      var sv = $.trim($secretLike.val() || '');
      if (sv === '') {
        showFieldError($secretLike, 'i18n_secret_required', 'Please enter your Secret Key.');
        result.valid = false;
        result.focus = result.focus || $secretLike;
      } else {
        clearFieldError($secretLike);
      }
    }

    // Google/Gmail specific
    var $clientId = $form.find('input[name*="google_client_id"], input[id*="google_client_id"], input[name*="client_id"]').filter(':visible').first();
    var $clientSecret = $form.find('input[name*="google_client_secret"], input[id*="google_client_secret"], input[name*="client_secret"]').filter(':visible').first();
    if ($clientId.length) {
      var cid = $.trim($clientId.val() || '');
      if (cid === '') {
        showFieldError($clientId, 'i18n_google_client_id_required', 'Please enter a Client ID.');
        result.valid = false;
        result.focus = result.focus || $clientId;
      } else {
        clearFieldError($clientId);
      }
    }
    if ($clientSecret.length) {
      var cs = $.trim($clientSecret.val() || '');
      if (cs === '') {
        showFieldError($clientSecret, 'i18n_google_client_secret_required', 'Please enter a Client Secret.');
        result.valid = false;
        result.focus = result.focus || $clientSecret;
      } else {
        clearFieldError($clientSecret);
      }
    }

    // Postmark detection
    var $postmarkToken = $form.find('input[name*="postmark"], input[id*="postmark"], input[name*="server_api_token"]').filter(':visible').first();
    if ($postmarkToken.length) {
      var pt = $.trim($postmarkToken.val() || '');
      if (pt === '') {
        showFieldError($postmarkToken, 'i18n_postmark_token_required', 'Please enter your Server API Token.');
        result.valid = false;
        result.focus = result.focus || $postmarkToken;
      } else {
        clearFieldError($postmarkToken);
      }
    }

    // SparkPost / other specific keys
    var $sparkKey = $form.find('input[name="sparkpost_api_key"], input[id="sparkpost_api_key"]').filter(':visible').first();
    if ($sparkKey.length) {
      if ($.trim($sparkKey.val() || '') === '') {
        showFieldError($sparkKey, 'i18n_api_key_required', 'Please enter your SparkPost API Key.');
        result.valid = false;
        result.focus = result.focus || $sparkKey;
      } else {
        clearFieldError($sparkKey);
      }
    }

    // Brevo-specific validation: check if SMTP mode is enabled
    var $brevoUseSmtp = $form.find('input[name="brevo_use_smtp"], input[id="brevo_use_smtp"]').filter(':visible').first();
    var brevoSmtpEnabled = $brevoUseSmtp.length ? !!$brevoUseSmtp.prop('checked') : false;
    
    if (brevoSmtpEnabled) {
      // SMTP mode - validate SMTP credentials
      var $brevoSmtpUser = $form.find('input[name="brevo_smtp_username"], input[id="brevo_smtp_username"]').filter(':visible').first();
      var $brevoSmtpPass = $form.find('input[name="brevo_smtp_password"], input[id="brevo_smtp_password"]').filter(':visible').first();
      
      if ($brevoSmtpUser.length) {
        if ($.trim($brevoSmtpUser.val() || '') === '') {
          showFieldError($brevoSmtpUser, 'i18n_smtp_username_required', 'Please enter the SMTP username.');
          result.valid = false;
          result.focus = result.focus || $brevoSmtpUser;
        } else {
          clearFieldError($brevoSmtpUser);
        }
      }
      
      if ($brevoSmtpPass.length) {
        if ($.trim($brevoSmtpPass.val() || '') === '') {
          showFieldError($brevoSmtpPass, 'i18n_smtp_password_required', 'Please enter the SMTP password.');
          result.valid = false;
          result.focus = result.focus || $brevoSmtpPass;
        } else {
          clearFieldError($brevoSmtpPass);
        }
      }
    } else if (provider === 'brevo') {
      // API mode - validate API key (only if provider is brevo)
      var $brevoApi = $form.find('input[name="brevo_api_key"], input[id="brevo_api_key"]').filter(':visible').first();
      if ($brevoApi.length) {
        if ($.trim($brevoApi.val() || '') === '') {
          showFieldError($brevoApi, 'i18n_api_key_required', 'Please enter your Brevo API Key.');
          result.valid = false;
          result.focus = result.focus || $brevoApi;
        } else {
          clearFieldError($brevoApi);
        }
      }
    }

    // Other SMTP specific validation: host, port, auth
    var $otherHost = $form.find('input[name="other_smtp_host"], input[id="other_smtp_host"]').filter(':visible').first();
    if (!$otherHost.length) {
      $otherHost = $form.find('input[name="smtp_host"], input[id="smtp_host"]').filter(':visible').first();
    }
    if ($otherHost.length) {
      var hostVal = $.trim($otherHost.val() || '');
      if (hostVal === '') {
        showFieldError($otherHost, 'i18n_smtp_host_required', 'Please enter the SMTP host.');
        result.valid = false;
        result.focus = result.focus || $otherHost;
      } else {
        clearFieldError($otherHost);
      }
    }

    var $otherPort = $form.find('input[name="other_smtp_port"], input[id="other_smtp_port"]').filter(':visible').first();
    if (!$otherPort.length) {
      $otherPort = $form.find('input[name="smtp_port"], input[id="smtp_port"]').filter(':visible').first();
    }
    if ($otherPort.length) {
      var portVal = $.trim($otherPort.val() || '');
      if (portVal === '') {
        showFieldError($otherPort, 'i18n_smtp_port_required', 'Please enter the SMTP port.');
        result.valid = false;
        result.focus = result.focus || $otherPort;
      } else {
        var num = parseInt(portVal, 10);
        if (isNaN(num) || num < 1 || num > 65535) {
          showFieldError($otherPort, 'i18n_smtp_port_invalid', 'Please enter a valid port (1-65535).');
          result.valid = false;
          result.focus = result.focus || $otherPort;
        } else {
          clearFieldError($otherPort);
        }
      }
    }

    var $otherAuthCheckbox = $form.find('input[name="other_smtp_auth"], input[id="other_smtp_auth"]').filter(':visible').first();
    var authEnabled = $otherAuthCheckbox.length ? !!$otherAuthCheckbox.prop('checked') : false;
    if (authEnabled) {
      var $otherUser = $form.find('input[name="other_smtp_username"], input[id="other_smtp_username"], input[name="smtp_username"], input[id="smtp_username"]').filter(':visible').first();
      var $otherPass = $form.find('input[name="other_smtp_password"], input[id="other_smtp_password"], input[name="smtp_password"], input[id="smtp_password"]').filter(':visible').first();

      if ($otherUser.length) {
        if ($.trim($otherUser.val() || '') === '') {
          showFieldError($otherUser, 'i18n_smtp_username_required', 'Please enter the SMTP username.');
          result.valid = false;
          result.focus = result.focus || $otherUser;
        } else {
          clearFieldError($otherUser);
        }
      }

      if ($otherPass.length) {
        if ($.trim($otherPass.val() || '') === '') {
          showFieldError($otherPass, 'i18n_smtp_password_required', 'Please enter the SMTP password.');
          result.valid = false;
          result.focus = result.focus || $otherPass;
        } else {
          clearFieldError($otherPass);
        }
      }
    }

    // From name / sender name
    var $fromField = $form.find('input[name$="_from_name"]').first();
    var $senderField = $form.find('input[name$="_sender_name"]').first();
    if (!$fromField.length) {
      $fromField = $form.find('input[name="sendlayer_from_name"], input[name="brevo_from_name"], input[id$="_from_name"]').first();
    }
    if (!$senderField.length) {
      $senderField = $form.find('input[name="smtpcom_sender_name"], input[id$="_sender_name"]').first();
    }

    var fromVal = $fromField.length ? $.trim($fromField.val() || '') : '';
    var senderVal = $senderField.length ? $.trim($senderField.val() || '') : '';

    if (provider === 'smtpcom') {
      if ($senderField.length && senderVal === '') {
        showFieldError($senderField, 'i18n_sender_name_required', 'Please enter a sender name.');
        result.valid = false;
        result.focus = result.focus || $senderField;
      } else if ($senderField.length) {
        clearFieldError($senderField);
      }

      if ($fromField.length && fromVal === '') {
        showFieldError($fromField, 'i18n_name_required', 'Please enter a name.');
        result.valid = false;
        result.focus = result.focus || $fromField;
      } else if ($fromField.length) {
        clearFieldError($fromField);
      }
    } else {
      if ($fromField.length) {
        if (fromVal === '') {
          showFieldError($fromField, 'i18n_name_required', 'Please enter a name.');
          result.valid = false;
          result.focus = result.focus || $fromField;
        } else {
          clearFieldError($fromField);
        }
      }
      if ($senderField.length) {
        if (senderVal === '') {
          showFieldError($senderField, 'i18n_sender_name_required', 'Please enter a sender name.');
          result.valid = false;
          result.focus = result.focus || $senderField;
        } else {
          clearFieldError($senderField);
        }
      }
    }

    // Sending domain
    var $sendingDomain = $form.find('input[name*="sending_domain"], input[id*="sending_domain"]').first();
    if ($sendingDomain.length) {
      var sdVal = $.trim($sendingDomain.val() || '');
      if (sdVal === '') {
        var fallbackRequired = t('i18n_sending_domain_required') || 'Please input the sending domain/subdomain you configured in your provider dashboard.';
        showFieldError($sendingDomain, 'i18n_sending_domain_required', fallbackRequired);
        result.valid = false;
        result.focus = result.focus || $sendingDomain;
      } else if (!isValidDomain(sdVal)) {
        var fallbackInvalid = t('i18n_sending_domain_invalid') || 'Please enter a valid sending domain (e.g. mail.example.com).';
        showFieldError($sendingDomain, 'i18n_sending_domain_invalid', fallbackInvalid);
        result.valid = false;
        result.focus = result.focus || $sendingDomain;
      } else {
        clearFieldError($sendingDomain);
      }
    }

    // From email validation
    var $email = $form.find('input[type="email"]').first();
    if ($email.length) {
      var emailVal = $.trim($email.val() || '');
      var requiredAttr = !!$email.prop('required');
      var forceEnabled = isForceFromEmailEnabled($form);

      var mustValidateEmail = requiredAttr || forceEnabled || emailVal !== '';

      if (mustValidateEmail) {
        if (!isValidEmail(emailVal)) {
          showFieldError($email, 'i18n_email_invalid', 'Please enter a valid email address.');
          result.valid = false;
          result.focus = result.focus || $email;
        } else {
          clearFieldError($email);
        }
      } else {
        clearFieldError($email);
      }
    }

    return result;
  }

  // Stepper UI builder
  function renderStepper(labels) {
    if ($('.wpmsl-simple-stepper').length) return;
    var total = labels.length || 4;
    var $wrap = $('<div class="wpmsl-simple-stepper" />');
    $wrap.append('<div class="track"><div class="fill"></div></div>');
    var $steps = $('<div class="steps" />');
    for (var i = 0; i < total; i++) {
      var label = labels[i] || '';
      var $btn = $('<button type="button" class="step" data-step="' + i + '" aria-label="' + label + '"></button>');
      $btn.append('<div class="dot">' + (i + 1) + '</div>');
      $btn.append('<div class="label">' + label + '</div>');
      $steps.append($('<div class="step" />').attr('data-step', i).append($btn));
    }
    $wrap.append($steps);
    $('.wpmsl-onboarding-hero').after($wrap);
  }

  function updateStepper(step) {
    // Select only the outer step containers (avoid counting inner button.step duplicates)
    var $steps = $('.wpmsl-simple-stepper .steps > .step');
    $steps.removeClass('active completed');
    $steps.each(function () {
      var s = parseInt($(this).data('step'), 10);
      if (s < step) $(this).addClass('completed');
      if (s === step) $(this).addClass('active');
    });

    var total = $steps.length || 1;
    // Map step indices 0..(total-1) to 1..total so step 0 shows the first segment filled.
    var pct = ((step + 1) / Math.max(1, total)) * 100;
    $('.wpmsl-simple-stepper .track .fill').css('width', pct + '%');

    $('.wpmsl-step').hide().attr('aria-hidden', 'true');
    $('.wpmsl-step[data-step="' + step + '"]').show().attr('aria-hidden', 'false');

    if (parseInt(step, 10) === 0) {
      // Hide navigation actions on welcome step, but keep the welcome-actions container visible
      $('.wpmsl-step[data-step="0"] .wpmsl-card-actions').hide();
      // Only hide prev/next buttons that are inside card-actions containers, not the welcome button
      $('.wpmsl-step[data-step="0"] .wpmsl-card-actions .wpmsl-prev, .wpmsl-step[data-step="0"] .wpmsl-card-actions .wpmsl-next').hide();
    } else {
      $('.wpmsl-step[data-step="' + step + '"] .wpmsl-card-actions').show();
      $('.wpmsl-card .wpmsl-prev, .wpmsl-card .wpmsl-next').show();
    }

    try {
      var u = new URL(window.location.href);
      u.searchParams.set('step', step);
      if (!u.searchParams.get('page')) u.searchParams.set('page', 'authority-mailer-smtp-onboarding');
      history.replaceState(null, '', u.toString());
    } catch (e) {}
  }

  function replaceUrlWithStep(step) {
    try {
      var base = window.location.origin + window.location.pathname;
      var newUrl = base + '?page=authority-mailer-smtp-onboarding&step=' + encodeURIComponent(step);
      history.replaceState(null, '', newUrl);
    } catch (e) {}
  }

  // Utility: append to the configure-step log area (if present)
  function appendConfigureLog(line, type) {
    var $log = $('#wpmsl-config-test-log');
    if (!$log.length) return;
    var time = new Date().toLocaleTimeString();
    var prefix = type ? '[' + type.toUpperCase() + '] ' : '';
    $log.val($log.val() + '[' + time + '] ' + prefix + line + "\n");
    $log.scrollTop($log[0].scrollHeight);
  }

  function clearConfigureLog() {
    var $log = $('#wpmsl-config-test-log');
    if ($log.length) $log.val('');
  }

  // Ensure a writable test log exists on the results step (data-step="3") or visible card.
  function ensureConfigTestLogExists() {
    var $log = $('#wpmsl-config-test-log');
    if ($log.length) return $log;

    // Prefer placing inside step 3 if it exists in DOM
    var $step3 = $('.wpmsl-step[data-step="3"]').first();
    var $targetCard;
    if ($step3.length) {
      $targetCard = $step3.find('.wpmsl-card').first();
    } else {
      // fallback to visible step/card
      $targetCard = $('.wpmsl-step:visible .wpmsl-card').first();
    }
    if (!$targetCard || !$targetCard.length) {
      // last fallback: append to main container
      $targetCard = $('.wpmsl-onboarding-hero').first().parent();
    }

    // Create container area and textarea if not present
    var $area = $targetCard.find('.wpmsl-config-test-area').first();
    if (!$area.length) {
      $area = $('<div class="wpmsl-config-test-area" aria-live="polite" style="margin-top:12px;"></div>');
      // Place it above card actions if possible
      var $actions = $targetCard.find('.wpmsl-card-actions').first();
      if ($actions.length) $area.insertBefore($actions);
      else $targetCard.append($area);
    }
    var $textarea = $area.find('#wpmsl-config-test-log').first();
    if (!$textarea.length) {
      $textarea = $('<textarea id="wpmsl-config-test-log" class="wpmsl-config-test-log" readonly style="width:100%;height:220px;padding:10px;box-sizing:border-box;font-family:monospace;" aria-label="Configuration test log"></textarea>');
      $area.append($textarea);
    }

    // Ensure a status container exists beneath the textarea (only one)
    var $statusWrap = $area.find('#wpmsl-config-test-status-wrap').first();
    if (!$statusWrap.length) {
      $statusWrap = $('<div id="wpmsl-config-test-status-wrap" style="margin-top:12px;"></div>');
      $area.append($statusWrap);
    }

    return $textarea;
  }

  // Helper to set the status box beneath the test log (provider-specific hints)
  function setConfigTestStatus(state, title, message, provider) {
    // state: 'success' | 'error' | 'unknown'
    var $area = $('#wpmsl-config-test-log').closest('.wpmsl-config-test-area');
    if (!$area.length) {
      ensureConfigTestLogExists();
      $area = $('#wpmsl-config-test-log').closest('.wpmsl-config-test-area');
    }
    var $wrap = $area.find('#wpmsl-config-test-status-wrap').first();
    if (!$wrap.length) {
      $wrap = $('<div id="wpmsl-config-test-status-wrap" style="margin-top:12px;"></div>');
      $area.append($wrap);
    }

    var color = '#6b7280'; // gray
    var pillText = 'UNKNOWN';
    if (state === 'success') { color = '#2d8c59'; pillText = 'SUCCESSFUL'; }
    if (state === 'error')   { color = '#c53030'; pillText = 'UNSUCCESSFUL'; }

    // Provider-specific suggestions
    var providerKey = (provider || getCurrentProvider() || 'other').toLowerCase();
    var suggestions = {
      smtp: 'Check host, port, encryption and authentication. If using TLS use port 587; for SSL use 465.',
      other: 'Check host, port, encryption and authentication. Verify firewall allows outbound SMTP.',
      smtpcom: 'Check SMTP credentials and API settings. For API use your SMTP.com API or SMTP credentials.',
      sendgrid: 'If using API, verify your API Key permissions. For SMTP, ensure username/password are correct.',
      mailgun: 'Ensure domain is verified and API key has permissions. If using SMTP, ensure credentials correct.',
      mailersend: 'Verify API key or SMTP credentials and sending domain.',
      brevo: 'Verify API key (Brevo) or SMTP credentials (if using SMTP). Check sending domain in Brevo dashboard.',
      sendlayer: 'Check SendLayer API key, or SMTP host/credentials if using SMTP.',
      smtp2go: 'Verify SMTP2GO API or SMTP credentials and region settings.',
      sparkpost: 'Verify SparkPost API key (transmissions) or SPF/DKIM settings.',
      postmark: 'Ensure Server token is correct and server exists in Postmark dashboard.',
      gmail: 'If using OAuth ensure token present. For SMTP use app password (if 2FA) and correct port/encryption.',
      office365: 'Prefer OAuth for Office365. If using SMTP, check credentials and port (587 TLS).',
      zoho: 'Zoho prefers OAuth; if using SMTP ensure credentials and port match Zoho settings.',
      aws: 'For Amazon SES prefer API/SDK. If using SMTP, ensure SMTP credentials and region match SES settings.'
    };
    var hint = suggestions[providerKey] || suggestions['other'];

    var html = ''
      + '<div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">'
      +   '<div style="min-width:140px;padding:10px;border-radius:8px;display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e6eef1;">'
      +     '<span style="display:inline-block;width:12px;height:12px;background:' + color + ';border-radius:50%;"></span>'
      +     '<strong style="font-size:14px;">STATUS: ' + pillText + '</strong>'
      +   '</div>'
      +   '<div style="flex:1;min-width:220px;">'
      +     '<div style="font-size:13px;color:#374151;font-weight:600;">' + (title || providerFriendlyName(providerKey) + ' — Test result') + '</div>'
      +     '<div style="font-size:13px;color:#6b7280;margin-top:6px;">' + (message || '') + '</div>'
      +     '<div style="font-size:13px;color:#6b7280;margin-top:8px;"><strong>Suggested actions:</strong> ' + hint + '</div>'
      +   '</div>'
      +   '<div style="white-space:nowrap;">'
      +     '<button id="wpmsl-config-test-retry" class="wpmsl-btn-primary" type="button" style="margin-right:8px;">Retry</button>'
      +   '</div>'
      + '</div>';

    $wrap.html(html);

    // Attach retry handler
    $(document).off('click', '#wpmsl-config-test-retry');
    $(document).on('click', '#wpmsl-config-test-retry', function (e) {
      e.preventDefault();
      var providerToRun = provider || getCurrentProvider();
      if (!providerToRun) {
        setConfigTestStatus('error', t('status_no_provider_title') || 'No provider', t('status_no_provider_message') || 'Provider not found. Unable to retry.', providerToRun);
        return;
      }
      clearConfigureLog();
      setConfigTestStatus('unknown', t('status_re_running') || 'Re-running test', 'Running saved-test again…', providerToRun);
      runSavedTestPlayback(providerToRun);
    });
  }

  // Saved-test runner + playback into the log textarea
  function runSavedTestPlayback(provider, opts) {
    opts = opts || {};
    if (!provider) {
      appendConfigureLog('No provider specified for saved test.', 'error');
      setConfigTestStatus('error', t('status_no_provider_title') || 'No provider', 'No provider was specified for the saved test. Please select a provider and try again.', provider);
      if (typeof opts.onDone === 'function') opts.onDone(new Error('no provider'));
      return;
    }

    // Ensure the log exists and is visible
    var $log = ensureConfigTestLogExists();
    if ($log && $log.length) $log.show();
    clearConfigureLog();
    // clear earlier status when starting
    setConfigTestStatus('unknown', t('status_running_test') || 'Running test', 'Starting saved-test playback…', provider);
    appendConfigureLog(t('log_saving_settings') || 'Saving settings and starting test...', 'info');

    var payload = { action: 'authority_mailer_smtp_run_saved_test', nonce: ajaxNonce(), provider: provider };
    if (opts.test_recipient) payload.test_recipient = opts.test_recipient;

    $.post(ajaxUrl(), payload)
      .done(function (resp) {
        try { console.debug('authority_mailer_smtp_run_saved_test response', resp); } catch (e) {}
        if (resp && resp.success && resp.data && Array.isArray(resp.data.steps)) {
          var steps = resp.data.steps;
          var i = 0;
          var hadError = false;
          var hadSuccess = false;

          (function playNext() {
            if (i >= steps.length) {
              // Determine overall result
              if (hadError) {
                setConfigTestStatus('error', t('i18n_test_finished_issues') || t('status_test_finished_issues') || 'Test finished with issues', t('i18n_review_suggestions') || 'Some diagnostic steps failed. Review the log above and follow the suggestions.', provider);
              } else if (hadSuccess) {
                setConfigTestStatus('success', t('i18n_test_finished_success') || t('status_test_finished_success') || 'Test finished successfully', t('i18n_test_completed_ok') || 'The saved test completed without errors. If you received the test email, your setup is OK.', provider);
              } else {
                setConfigTestStatus('unknown', t('i18n_test_finished_unknown') || t('status_test_finished_unknown') || 'Test finished', t('i18n_no_clear_result') || 'No clear success or error was detected. Review the log for details.', provider);
              }

              appendConfigureLog(t('log_test_finished') || 'Test finished.', 'result');
              if (typeof opts.onDone === 'function') opts.onDone(null, steps);
              return;
            }

            var s = steps[i];
            // Check for error statuses - check multiple values for comprehensive error detection across all providers
            // Current backend uses: 'error'. Future-proofed for: 'failed', 'failure'
            if (s && (s.status === 'error' || s.status === 'failed' || s.status === 'failure')) hadError = true;
            // Check for success statuses - check multiple values to handle queued/accepted states
            // Current backend uses: 'success', 'accepted'. Future-proofed for: 'sent'
            if (s && (s.status === 'success' || s.status === 'accepted' || s.status === 'sent')) hadSuccess = true;

            appendConfigureLog(s.message || '(no message)', s.status || 'info');
            if (s.details) {
              if (typeof s.details === 'string') appendConfigureLog(s.details, 'detail');
              else if (Array.isArray(s.details)) s.details.forEach(function (d) { appendConfigureLog(typeof d === 'string' ? d : JSON.stringify(d), 'detail'); });
              else appendConfigureLog(JSON.stringify(s.details), 'detail');
            }
            i++;
            setTimeout(playNext, 350);
          })();
        } else {
          var msg = (resp && resp.data && resp.data.message) ? resp.data.message : (t('i18n_request_failed') || t('status_test_failed') || 'Test failed');
          appendConfigureLog(msg, 'error');
          setConfigTestStatus('error', t('status_test_failed') || 'Test failed', msg, provider);
          if (typeof opts.onDone === 'function') opts.onDone(new Error(msg));
        }
      })
      .fail(function (jqXHR) {
        var errText = (t('i18n_request_failed') || 'Request failed') + ' (' + (jqXHR && jqXHR.status ? jqXHR.status : t('i18n_unknown_error') || 'unknown') + ')';
        appendConfigureLog(errText, 'error');
        setConfigTestStatus('error', 'Request failed', errText + '. Check your network or server and try again.', provider);
        try { console.debug('authority_mailer_smtp_run_saved_test failed', jqXHR); } catch (e) {}
        if (typeof opts.onDone === 'function') opts.onDone(new Error('ajax fail'));
      });
  }

  // DOM-ready wiring
  $(function () {
    var labels = [];
    $('.wpmsl-steps ol li small').each(function () { labels.push($(this).text().trim()); });
    if (!labels.length) {
      var localizedLabels = (window.authorityMailerOnboarding && window.authorityMailerOnboarding.step_labels) ? window.authorityMailerOnboarding.step_labels : (window.AUTHORITY_MAILEROnboarding && window.AUTHORITY_MAILEROnboarding.step_labels) ? window.AUTHORITY_MAILEROnboarding.step_labels : (window.authority_mailer_onboarding && window.authority_mailer_onboarding.step_labels ? window.authority_mailer_onboarding.step_labels : []);
      if (Array.isArray(localizedLabels) && localizedLabels.length) labels = localizedLabels;
    }

    renderStepper(labels);

    var initial = (function () {
      // 1) Prefer server-rendered state on wrapper
      try {
        var container = $('.wpmsl-onboarding-wrap').first();
        if (container && container.length) {
          var ds = container.data('current-step');
          if (typeof ds !== 'undefined' && ds !== null) {
            var di = parseInt(ds, 10);
            if (!isNaN(di)) return di;
          }
        }
      } catch (e) { /* ignore */ }

      // 2) fallback to nav active li
      var $li = $('.wpmsl-steps li.active').first();
      if ($li.length) {
        var s = parseInt($li.data('step'), 10);
        if (!isNaN(s)) return s;
      }

      // 3) fallback to URL param
      try {
        var u = new URL(window.location.href);
        var v = u.searchParams.get('step');
        var n = parseInt(v, 10);
        return isNaN(n) ? 0 : n;
      } catch (e) { return 0; }
    })();

    updateStepper(initial);

    // Accessibility keyboard interactions
    $('.wpmsl-simple-stepper .step button, .wpmsl-mailer-tile').attr('tabindex', 0).on('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32) { e.preventDefault(); $(this).trigger('click'); }
    });

    // Tile click selects radio
    $(document).on('click', '.wpmsl-mailer-tile', function (e) {
      var $target = $(e.target);
      var $tile = $(this);

      // Allow clicks on the tile itself (which is a label) but prevent if clicking on nested interactive elements
      // Don't block if target is the tile label itself or if clicking on non-interactive children
      if ($target.is('input[type="radio"],a,button,.wpmsl-toggle, .wpmsl-toggle *')) return;

      // If clicking directly on a nested label (not the tile label), return
      if ($target.is('label') && !$target.is('.wpmsl-mailer-tile')) return;

      var $radio = $tile.find('input[type="radio"]');
      if ($radio.length && !$radio.prop('disabled')) {
        $radio.prop('checked', true).trigger('change');
      }
    });

    // Visual selection
    $(document).on('change', '.wpmsl-mailer-tile input[type="radio"]', function () {
      var $tile = $(this).closest('.wpmsl-mailer-tile');
      $tile.closest('.wpmsl-mailers-grid').find('.wpmsl-mailer-tile').removeClass('selected');
      if ($(this).prop('checked')) $tile.addClass('selected');
    });

    // Prev/Next handlers
    $(document).on('click', '.wpmsl-card .wpmsl-prev', function (e) {
      e.preventDefault();
      if ($(this).prop('disabled')) return;
      var cur = parseInt($('.wpmsl-step:visible').attr('data-step'), 10) || 0;
      var prev = Math.max(0, cur - 1);
      updateStepper(prev);
      replaceUrlWithStep(prev);
    });
    $(document).on('click', '.wpmsl-card .wpmsl-next', function (e) {
      e.preventDefault();
      if ($(this).prop('disabled')) return;
      var cur = parseInt($('.wpmsl-step:visible').attr('data-step'), 10) || 0;
      updateStepper(Math.min($('.wpmsl-step').length - 1, cur + 1));
    });

    // Clear inline errors while typing
    $(document).on('input change', '.wpmsl-form-input, input[name*="api"], input[id*="api"], input[name*="token"], input[id*="token"], input[name*="secret"], input[id*="secret"], input[name*="postmark"], input[id*="postmark"], input[name*="message_stream"], input[id*="message_stream"], input[name*="client_id"], input[id*="client_id"], input[name*="client_secret"], input[id*="client_secret"], input[id="sparkpost_api_key"], input[name="sparkpost_api_key"], input[name="other_smtp_host"], input[id="other_smtp_host"], input[name="other_smtp_port"], input[id="other_smtp_port"], input[name="other_smtp_username"], input[id="other_smtp_username"], input[name="other_smtp_password"], input[id="other_smtp_password"]', function () {
      clearFieldError($(this));
      var id = $(this).attr('id');
      if (id) $('#' + id + '-error').hide().text('');
    });

    // Save chosen mailer (choose step)
    $(document).on('click', '#authority-mailer-choose-save', function (e) {
      e.preventDefault();
      var provider = $('input[name="authority_mailer_smtp_choice_full"]:checked').val() || $('input[name="authority_mailer_smtp_choice"]:checked').val();
      if (!provider) {
        alert(t('i18n_select_mailer') || 'Please select a mailer to continue.');
        return;
      }
      var nonce = ajaxNonce();
      var url = ajaxUrl();
      $.post(url, { action: 'authority_mailer_smtp_set_selected_mailer', nonce: nonce, provider: provider })
        .done(function (resp) {
          if (resp && resp.success) {
            try {
              var redirect = new URL(window.location.href);
              redirect.searchParams.set('page', 'authority-mailer-smtp-onboarding');
              redirect.searchParams.set('step', 2);
              redirect.searchParams.set('provider', provider);
              redirect.hash = '';
              window.location.href = redirect.toString();
            } catch (err) {
              window.location.href = window.location.origin + window.location.pathname + '?page=authority-mailer-smtp-onboarding&step=2&provider=' + encodeURIComponent(provider);
            }
          } else {
            alert((resp && resp.data && resp.data.message) ? resp.data.message : (t('i18n_save_error') || ''));
          }
        })
        .fail(function () {
          alert(t('i18n_request_failed') || '');
        });
    });

    // Unified Save & Continue (configure step -> provider form)
    $(document).on('click', '.wpmsl-btn-primary.authority-mailer-save, .wpmsl-onboarding-save-and-test', function (e) {
      e.preventDefault();
      var $settingsForm = $('.wpmsl-mailer-form[data-mailer-form]').first();

      if ($settingsForm.length && $settingsForm.is(':visible')) {
        var validation = validateProviderForm($settingsForm);
        if (!validation.valid) {
          if (validation.focus && validation.focus.length) validation.focus.focus();
          setFormStatus($settingsForm, 'i18n_request_failed', true);
          return;
        }

        var ajaxUrlToUse = $settingsForm.attr('action') || ajaxUrl();
        var formData = $settingsForm.serialize();
        setFormStatus($settingsForm, 'i18n_saving_settings', false);
        var isSaveAndTest = $(this).hasClass('wpmsl-onboarding-save-and-test');

        $.post(ajaxUrlToUse, formData)
          .done(function (resp) {
            if (resp && resp.success) {
              setFormStatus($settingsForm, 'i18n_settings_saved', false);
              try {
                // Redirect to results step (3) so saved-test or follow-up instructions are shown.
                var redirect = new URL(window.location.href);
                redirect.searchParams.set('page', 'authority-mailer-smtp-onboarding');
                redirect.searchParams.set('step', 3);
                var provider = $settingsForm.data('mailer-form') || '';
                if (provider) redirect.searchParams.set('provider', provider);
                redirect.hash = '';
                window.location.href = redirect.toString();
              } catch (err) {
                window.location.href = window.location.origin + window.location.pathname + '?page=authority-mailer-smtp-onboarding&step=3&provider=' + encodeURIComponent($settingsForm.data('mailer-form') || '');
              }
            } else {
              var msg = (resp && resp.data && resp.data.message) ? resp.data.message : (t('i18n_save_error') || '');
              setFormStatus($settingsForm, '', true);
              if (msg) $('#wpmsl-mailer-form-status').text(msg).show();
            }
          })
          .fail(function (jqXHR) {
            setFormStatus($settingsForm, 'i18n_request_failed', true);
            console.error('Save settings AJAX failed:', jqXHR);
          });

        return;
      }

      // Fallback: save chosen provider (same as choose handler)
      var $selected = $('.wpmsl-mailers-grid input[type="radio"]:checked');
      if ($selected.length === 0) {
        var $grid = $('.wpmsl-mailers-grid');
        if ($grid.length) {
          $grid.addClass('wpmsl-validation-error');
          setTimeout(function () { $grid.removeClass('wpmsl-validation-error'); }, 1400);
          var $first = $grid.find('.wpmsl-mailer-tile').first();
          if ($first.length) $first.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        alert(t('i18n_select_mailer') || 'Please select a mailer to continue.');
        return;
      }

      var provider = $selected.val() || $selected.closest('.wpmsl-mailer-tile').data('mailer');
      var nonce = ajaxNonce();
      var url = ajaxUrl();
      $.post(url, { action: 'authority_mailer_smtp_set_selected_mailer', nonce: nonce, provider: provider })
        .done(function (resp) {
          if (resp && resp.success) {
            try {
              var redirect = new URL(window.location.href);
              redirect.searchParams.set('page', 'authority-mailer-smtp-onboarding');
              redirect.searchParams.set('step', 2);
              redirect.searchParams.set('provider', provider);
              redirect.hash = '';
              window.location.href = redirect.toString();
            } catch (err) {
              window.location.href = window.location.origin + window.location.pathname + '?page=authority-mailer-smtp-onboarding&step=2&provider=' + encodeURIComponent(provider);
            }
          } else {
            alert((resp && resp.data && resp.data.message) ? resp.data.message : (t('i18n_save_error') || ''));
          }
        })
        .fail(function (jqXHR) {
          console.error('authority_mailer_smtp_set_selected_mailer failed', jqXHR);
          try {
            var fallback = new URL(window.location.href);
            fallback.searchParams.set('page', 'authority-mailer-smtp-onboarding');
            fallback.searchParams.set('step', 2);
            fallback.searchParams.set('provider', provider);
            fallback.hash = '';
            window.location.href = fallback.toString();
          } catch (err) {
            window.location.href = window.location.origin + window.location.pathname + '?page=authority-mailer-smtp-onboarding&step=2&provider=' + encodeURIComponent(provider);
          }
        });
    });

    // Send Test Email button on Step 3: collect recipient, validate and run saved-test with recipient override
    $(document).on('click', '#authority-mailer-send-test-button', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var provider = getCurrentProvider() || (document.querySelector('input[name="authority_mailer_smtp_choice_full"]:checked') || {}).value || '';
      if (!provider) {
        // Try provider param in URL
        try {
          var u = new URL(window.location.href);
          provider = (u.searchParams.get('provider') || '').toLowerCase();
        } catch (err) { provider = ''; }
      }
      if (!provider) {
        appendConfigureLog('Provider not found. Cannot send test email.', 'error');
        return;
      }

      var $recipientField = $('#authority-mailer-test-recipient').first();
      var recipient = $recipientField.length ? $.trim($recipientField.val() || '') : '';
      if (!recipient || !isValidEmail(recipient)) {
        showFieldError($recipientField, 'i18n_email_invalid', 'Please enter a valid email address.');
        $recipientField.focus();
        return;
      } else {
        clearFieldError($recipientField);
      }

      $btn.prop('disabled', true);
      clearConfigureLog();
      setConfigTestStatus('unknown', 'Running test', 'Sending test email to ' + recipient + '…', provider);

      runSavedTestPlayback(provider, {
        test_recipient: recipient,
        onDone: function (err, steps) {
          $btn.prop('disabled', false);
        }
      });
    });

    // Other SMTP auth toggle behavior (keeps existing behavior)
    (function() {
      var $authToggle = $('#other_smtp_auth');
      var $authCreds  = $('#other-auth-creds');
      var $authPass   = $('#other-auth-pass');

      if ($authToggle.length) {
        var initial = $authToggle.is(':checked');
        $authCreds.toggle(initial);
        $authPass.toggle(initial);

        $(document).on('change', '#other_smtp_auth', function() {
          var on = $(this).is(':checked');
          $authCreds.toggle(on);
          $authPass.toggle(on);
        });
      }
    })();

    // Hide any existing error nodes
    $('.wpmsl-input-error').hide();

    // Initialize pre-selected provider tiles with the 'selected' class
    // This ensures that when returning to step 2 with a saved provider, the tile is visually highlighted
    $('.wpmsl-mailer-tile input[type="radio"]:checked').each(function() {
      $(this).closest('.wpmsl-mailer-tile').addClass('selected');
    });

    // After initialization, if URL has step=3 and provider param, prepare the log area but DO NOT auto-run the test.
    try {
      var u = new URL(window.location.href);
      var stepParam = parseInt(u.searchParams.get('step') || '', 10);
      var providerParam = (u.searchParams.get('provider') || '') || '';
      if (!isNaN(stepParam) && stepParam === 3 && providerParam) {
        ensureConfigTestLogExists();
        $('#wpmsl-config-test-log').show();
        // Note: test is run explicitly by clicking "Send Test Email" or Retry
      }
    } catch (e) {
      // ignore URL parsing errors
    }

    console.info('onboarding.js loaded and initialized');
  });

})(window, document, jQuery);
