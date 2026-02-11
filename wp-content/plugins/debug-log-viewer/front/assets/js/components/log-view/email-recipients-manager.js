/* global jQuery */
import { t } from '../../utils.js'

/**
 * Handles multiple email inputs for alert notifications
 */
export class EmailRecipientsManager {
  constructor (containerSelector = '#email-recipients-container') {
    this.container = jQuery(containerSelector)
    this.maxEmails = 3
    this.init()
  }

  /**
   * Initialize the email recipients manager
   */
  init () {
    if (!this.container.length) {
      return
    }

    this.bindEvents()
    this.updateButtonStates()
  }

  /**
   * Bind event listeners
   */
  bindEvents () {
    // Add email button click
    this.container.on('click', '.add-email-btn', (e) => {
      e.preventDefault()
      this.addEmailInput()
    })

    // Remove email button click
    this.container.on('click', '.remove-email-btn', (e) => {
      e.preventDefault()
      this.removeEmailInput(jQuery(e.currentTarget).closest('.email-input-row'))
    })

    // Update button states when inputs change
    this.container.on('input', 'input[type="email"]', () => {
      this.updateButtonStates()
    })
  }

  /**
   * Add a new email input row
   */
  addEmailInput () {
    const currentRows = this.container.find('.email-input-row')

    if (currentRows.length >= this.maxEmails) {
      return
    }

    const newIndex = currentRows.length
    const newRow = this.createEmailRow(newIndex, '')

    this.container.append(newRow)
    this.updateButtonStates()

    // Focus the new input
    newRow.find('input[type="email"]').focus()
  }

  /**
   * Remove an email input row
   */
  removeEmailInput (row) {
    const allRows = this.container.find('.email-input-row')

    // Don't remove if it's the only row
    if (allRows.length <= 1) {
      return
    }

    row.remove()
    this.updateIndices()
    this.updateButtonStates()
  }

  /**
   * Create a new email input row
   */
  createEmailRow (index, email = '') {
    const row = jQuery(`
      <div class="email-input-row" data-index="${index}">
        <input type="email" name="emails[]" value="${email}" />
        <button type="button" class="btn btn-outline-danger btn-sm remove-email-btn" title="${t('remove_this_email')}">-</button>
      </div>
    `)

    return row
  }

  /**
   * Update button states based on current rows
   */
  updateButtonStates () {
    const rows = this.container.find('.email-input-row')
    const totalRows = rows.length

    // Remove all add buttons first
    rows.find('.add-email-btn').remove()

    // Add the + button to the first row only
    const firstRow = rows.first()
    if (firstRow.length) {
      let addButton = firstRow.find('.add-email-btn')
      if (addButton.length === 0) {
        addButton = jQuery(`<button type="button" class="btn btn-outline-primary btn-sm add-email-btn" title="${t('add_another_email')}">+</button>`)
        firstRow.append(addButton)
      }

      // Disable add button if we've reached the max
      addButton.prop('disabled', totalRows >= this.maxEmails)
    }

    // Update remove buttons
    rows.each((index, row) => {
      const $row = jQuery(row)
      const removeBtn = $row.find('.remove-email-btn')

      if (index === 0) {
        // First row: hide remove button if it's the only row
        removeBtn.toggle(totalRows > 1)
      } else {
        // Other rows: always show remove button
        removeBtn.show()
      }
    })
  }

  /**
   * Update data-index attributes after row removal
   */
  updateIndices () {
    this.container.find('.email-input-row').each((index, row) => {
      jQuery(row).attr('data-index', index)
    })
  }

  /**
   * Get all email values
   */
  getEmails () {
    return this.container.find('input[type="email"]').map((index, input) => {
      return jQuery(input).val().trim()
    }).get().filter(email => email !== '')
  }

  /**
   * Set email values
   */
  setEmails (emails) {
    // Clear existing rows
    this.container.empty()

    // Ensure we have at least one email input
    if (emails.length === 0) {
      emails = ['']
    }

    // Create rows for each email
    emails.forEach((email, index) => {
      const row = this.createEmailRow(index, email)
      this.container.append(row)
    })

    this.updateButtonStates()
  }

  /**
   * Validate all email inputs
   */
  validate () {
    const emails = this.getEmails()
    const errors = []

    if (emails.length === 0) {
      errors.push(t('at_least_one_email_required'))
    }

    emails.forEach((email, index) => {
      if (!this.isValidEmail(email)) {
        errors.push(t('email_not_valid').replace('%d', index + 1))
      }
    })

    return {
      isValid: errors.length === 0,
      errors
    }
  }

  /**
   * Simple email validation - ReDoS safe
   */
  isValidEmail (email) {
    // Prevent ReDoS attacks by using length check and safe regex
    if (!email || email.length > 254) {
      return false
    }

    // Safe regex pattern without potential for catastrophic backtracking
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/
    return emailRegex.test(email)
  }

  /**
   * Enable/disable all email inputs
   */
  setEnabled (enabled) {
    this.container.find('input[type="email"]').prop('disabled', !enabled)
    this.container.find('.add-email-btn, .remove-email-btn').prop('disabled', !enabled)
  }
}
