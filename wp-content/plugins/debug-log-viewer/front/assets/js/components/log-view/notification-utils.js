/* global jQuery, ajaxurl, dbg_lv_backend_data */
import { showToast, t } from '../../utils.js'
import { NotificationScheduleManager } from './notification-schedule.js'
import { EmailRecipientsManager } from './email-recipients-manager.js'

const scheduleManager = new NotificationScheduleManager()
let emailRecipientsManager = null

/**
 * Updates email notification settings
 * @param {jQuery} form - The alerts form
 * @param {string} action - The AJAX action to perform
 * @returns {Promise<void>}
 */
export async function updateEmailNotifications (form, action) {
  const notificationStatus = form.attr('data-alerts-enabled')
  const recurrenceField = form.find('select[name="recurrence"]')
  const testEmailCheckbox = form.find('input[name="send_test_email"]')
  const submitButton = form.find('input[type="submit"]')
  const errorLevelCheckboxes = form.find('input[name="levels[]"]')

  // Validate emails using the manager
  const validation = emailRecipientsManager.validate()
  if (!validation.isValid) {
    showToast(validation.errors.join(', '), 'warning')
    return
  }

  const emails = emailRecipientsManager.getEmails()
  if (emails.length === 0) {
    showToast(t('email_is_not_specified'), 'warning')
    return
  }

  // Get selected error levels - only from enabled checkboxes (non-pro features)
  const selectedLevels = form.find('input[name="levels[]"]:checked:not(.pro-disabled)').map(function () {
    return jQuery(this).val()
  }).get()

  // Check if togglers are disabled for free users
  const hasProDisabledTogglers = form.find('.togglers-group.pro-disabled').length > 0

  // For free users with pro-disabled togglers, skip level validation
  // They'll get default error levels from the backend
  if (notificationStatus === 'false' && !hasProDisabledTogglers && selectedLevels.length === 0) {
    showToast(t('please_select_error_level'), 'warning')
    return
  }

  submitButton.attr('disabled', 'disabled')

  try {
    if (notificationStatus === 'true') {
      const response = await jQuery.post(ajaxurl, {
        action,
        status: 'disable',
        emails,
        levels: selectedLevels,
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!response.success) {
        throw new Error(response.data || t('request_error'))
      }

      form.attr('data-alerts-enabled', 'false')
      submitButton
        .removeClass('disable btn-danger')
        .addClass('enable btn-primary')
        .val('Enable')

      showToast(t('notifications_disabled'), 'success')

      // Hide schedule display
      scheduleManager.hide()

      const userEmailResponse = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_get_current_user_email',
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!userEmailResponse.success) {
        throw new Error(userEmailResponse.data || t('request_error'))
      }
      emailRecipientsManager.setEnabled(true)
      recurrenceField.prop('disabled', false)
      testEmailCheckbox.prop('disabled', false).closest('.form-check').show()

      // Only enable error level checkboxes if they're not pro-disabled
      errorLevelCheckboxes.each(function () {
        const checkbox = jQuery(this)
        const isProDisabled = checkbox.closest('.togglers-group').hasClass('pro-disabled')
        if (!isProDisabled) {
          checkbox.prop('disabled', false)
        }
      })
    } else {
      const response = await jQuery.post(ajaxurl, {
        action,
        status: 'enable',
        recurrence: recurrenceField?.val(),
        emails,
        levels: selectedLevels,
        send_test_email: +testEmailCheckbox?.is(':checked'),
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!response.success) {
        throw new Error(response.data || t('request_error'))
      }

      emailRecipientsManager.setEnabled(false)
      recurrenceField.prop('disabled', true)
      testEmailCheckbox.prop('disabled', true).closest('.form-check').hide()

      // Always disable error level checkboxes when notifications are enabled
      errorLevelCheckboxes.prop('disabled', true)

      submitButton
        .removeClass('enable btn-primary')
        .addClass('disable btn-danger')
        .val(t('disable'))

      form.attr('data-alerts-enabled', 'true')

      showToast(t('notifications_enabled'), 'success')

      // Show and update schedule display
      scheduleManager.show()
    }
  } catch (error) {
    showToast(error, 'error')
  }

  submitButton.prop('disabled', false)
}

/**
 * Initializes the email alerts form
 * @param {jQuery} form - The form to initialize
 * @returns {Promise<void>}
 */
export async function initEmailNotificationsForm (form) {
  if (!form || !form.length) {
    console.warn(t('email_alerts_form_not_found'))
    return
  }

  emailRecipientsManager = new EmailRecipientsManager()

  const recurrenceField = form.find('select[name="recurrence"]')
  const testEmailCheckbox = form.find('input[name="send_test_email"]')
  const submitButton = form.find('input[type="submit"]')
  const errorLevelCheckboxes = form.find('input[name="levels[]"]')

  // Elements are already disabled in HTML, just add loading visual state
  form.addClass('loading')

  try {
    if (form.attr('data-alerts-enabled') === 'true') {
      // Remove loading state but keep form disabled since alerts are enabled
      form.removeClass('loading')
      emailRecipientsManager.setEnabled(false)
      recurrenceField.prop('disabled', true)
      testEmailCheckbox.prop('disabled', true).closest('.form-check').hide()
      errorLevelCheckboxes.prop('disabled', true)
      submitButton
        .prop('disabled', false)
        .removeClass('enabled btn-primary')
        .addClass('disable btn-danger')
        .val(t('disable'))

      // Show schedule display for enabled alerts
      scheduleManager.show()
    } else {
      // For disabled alerts, check if we have existing emails from the template
      const existingEmails = emailRecipientsManager.getEmails()

      // If no existing emails, fetch the current user's email as default
      if (existingEmails.length === 0) {
        const response = await jQuery.post(ajaxurl, {
          action: 'dbg_lv_get_current_user_email',
          wp_nonce: dbg_lv_backend_data.ajax_nonce
        })

        if (!response.success) {
          throw new Error(response.data || t('request_error'))
        }
        // Set the current user's email as the default
        emailRecipientsManager.setEmails([response.data])
      }

      // Enable all form elements since alerts are disabled
      form.removeClass('loading')
      emailRecipientsManager.setEnabled(true)
      recurrenceField.prop('disabled', false)
      testEmailCheckbox.prop('disabled', false).closest('.form-check').show()

      // Only enable error level checkboxes if they're not pro-disabled
      errorLevelCheckboxes.each(function () {
        const checkbox = jQuery(this)
        const isProDisabled = checkbox.closest('.togglers-group').hasClass('pro-disabled')
        if (!isProDisabled) {
          checkbox.prop('disabled', false)
        }
      })

      submitButton
        .prop('disabled', false)
        .removeClass('disable btn-danger')
        .addClass('enable btn-primary').val(t('enable'))

      // Hide schedule display for disabled alerts
      scheduleManager.hide()
    }
  } catch (error) {
    // Remove loading state and enable basic form functionality on error
    form.removeClass('loading')
    submitButton.prop('disabled', false).val(t('enable'))
    showToast(error, 'error')
  }
}
