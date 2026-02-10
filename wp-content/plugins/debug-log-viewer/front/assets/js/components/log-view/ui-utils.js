/* global $, dbg_lv_backend_data, FS */
import { t, showToast } from '../../utils.js'
import { DATETIME_FORMAT_RELATIVE } from './constants.js'

export const isPremium = () => window.dbg_lv_backend_data?.is_premium || window.dbg_lv_freemius_data?.is_premium || false

/**
 * Initialize and return Freemius checkout handler
 */
export const getFreemiusCheckoutHandler = () => {
  if (typeof FS === 'undefined' || !FS.Checkout) {
    console.warn('Freemius checkout not available')
    return null
  }

  const freemiusData = window.dbg_lv_freemius_data || {}

  return new FS.Checkout({
    product_id: freemiusData.product_id || '17350',
    plan_id: freemiusData.plan_id || '29848',
    public_key: freemiusData.public_key || 'pk_d456c712f16510d920c9f4ba4880a',
    image: freemiusData.image || ''
  })
}

export const openFreemiusCheckout = () => {
  const handler = getFreemiusCheckoutHandler()

  if (!handler) {
    // Fallback to direct URL if Freemius SDK is not available
    const checkoutUrl = window.dbg_lv_backend_data?.checkout_url || window.dbg_lv_freemius_data?.checkout_url || ''
    if (checkoutUrl) {
      window.open(checkoutUrl, '_blank')
    }
    return
  }

  // Open with options to show Freemius's native pricing selector
  handler.open({
    name: 'Debug Log Viewer',
    // Don't specify licenses/plan_id to let Freemius show all plans
    billing_cycle_selector: 'dropdown', // Show billing cycle options
    show_reviews: true, // Show customer reviews
    show_refund_badge: true, // Show money-back guarantee
    success: (response) => {
      console.log('Purchase completed:', response)
    }
  })
}

/**
 * Show quick features modal before checkout
 */
export const showFeaturesModal = () => {
  const $modal = $('#dbg-lv-features-modal')
  if ($modal.length) {
    $modal.modal('show')
  } else {
    // Fallback if modal not available - go directly to checkout
    openFreemiusCheckout()
  }
}

/**
 * Open Freemius pricing/upgrade flow
 */
export const openFreemiusPricing = () => {
  showFeaturesModal()
}

/**
 * Creates the dropdown menu items based on available features
 */
export function createDropdownMenuItems () {
  let menuItems = `
    <li><a class="dropdown-item download-btn download-log" href="#">
      <i class="fa fa-download"></i>${t('download_log')}</a></li>
    <li><a class="dropdown-item clear-log-btn clear-log" href="#">
    <i class="fa fa-trash"></i>${t('clear_log')}</a></li>`

  if (isPremium()) {
    menuItems += `
      <li><a class="dropdown-item export-csv-btn" href="#">
        <i class="fa fa-file-csv"></i>${t('export_csv')}
      </a></li>`
  } else {
    /* <fs_free_only> */

    menuItems +=
    `<li>
      <a class="dropdown-item pro-feature freemius-checkout-trigger" href="#" title="${t('upgrade_to_pro')}">
        <i class="fa fa-file-csv"></i>${t('export_csv')} 
        <span class="pro-badge">Pro</span>
      </a>
    </li>`
    /* </fs_free_only> */
  }

  return menuItems
}

// /**
//  * UI utilities for log viewer
//  */
export class LogViewerUI {
  constructor (table) {
    this.table = table
    this.logsUpdatesIcon = $('.refresh-log i')
    this.logsUpdateInterval = null
  }

  /**
     * Animate refresh button
     */
  animateRefreshButton () {
    const icon = this.logsUpdatesIcon[0]

    // Remove existing class and force reflow to restart animation
    this.logsUpdatesIcon.removeClass('rotate-animation')
    this.logsUpdatesIcon.addClass('rotate-animation')

    // Clean up after animation completes
    icon?.addEventListener('animationend', () => {
      this.logsUpdatesIcon.removeClass('rotate-animation')
    }, { once: true })
  }

  /**
     * Start live update logs
     */
  startLiveUpdateLogs (updateLogsCallback) {
    const timeout = dbg_lv_backend_data.log_updates_interval * 1000
    this.logsUpdateInterval = setInterval(updateLogsCallback, timeout)
  }

  /**
     * Stop live update logs
     */
  stopLiveUpdateLogs () {
    if (this.logsUpdateInterval) {
      clearInterval(this.logsUpdateInterval)
    }
  }

  /**
   * Setup time filter for DataTable
   */
  setupTimeFilter () {
    const table = this.table

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      const filter = $('.datetime-range-buttons .btn.active').val() // Get the active filter

      if (filter === 'all') {
        return true // Show all rows
      }

      const now = new Date()
      const timestamp = new Date(Number(data[0])) // Assuming the first column is the timestamp
      if (isNaN(timestamp)) {
        // If timestamp parsing fails, exclude the row
        return false
      }

      const timeDiff = now - timestamp // Time difference in milliseconds

      if (filter === 'custom') {
        const fromDate = $('#custom-date-from').val()
        const toDate = $('#custom-date-to').val()

        if (!fromDate || !toDate) {
          return true // Show all if dates not set
        }

        const from = new Date(fromDate).getTime()
        const to = new Date(toDate).getTime()
        const timestampMs = timestamp.getTime()

        return timestampMs >= from && timestampMs <= to
      }

      return (filter === '5m' && timeDiff <= 5 * 60 * 1000) ||
             (filter === '30m' && timeDiff <= 30 * 60 * 1000) ||
             (filter === '1h' && timeDiff <= 60 * 60 * 1000) ||
             (filter === '12h' && timeDiff <= 12 * 60 * 60 * 1000)
    })

    // Filter Button Click Event
    $('html').on('click', '.datetime-range-buttons .btn', function () {
      // Check if this is a pro feature and user doesn't have premium
      if ($(this).hasClass('pro-disabled') && !window.dbg_lv_backend_data?.is_premium) {
        // Show upgrade message or redirect to upgrade
        openFreemiusPricing()
        return false
      }

      const filter = $(this).val()

      $('.datetime-range-buttons .btn').removeClass('active')
      $(this).addClass('active')

      if (filter === 'custom') {
        // Show custom date range inputs without relying on Bootstrap popover
        let $customContainer = $('.custom-date-range-dropdown')

        if ($customContainer.length === 0) {
          // Create the custom date range dropdown if it doesn't exist
          $customContainer = $(`
            <div class="custom-date-range-dropdown">
              <div class="date-range-content">
                <div class="date-input-group">
                  <label for="custom-date-from" class="form-label">From:</label>
                  <input type="datetime-local" id="custom-date-from" class="form-control form-control-sm" />
                </div>
                <div class="date-input-group">
                  <label for="custom-date-to" class="form-label">To:</label>
                  <input type="datetime-local" id="custom-date-to" class="form-control form-control-sm" />
                </div>
                <div class="date-actions">
                  <button type="button" class="btn btn-sm btn-primary apply-custom-range">Apply</button>
                  <button type="button" class="btn btn-sm btn-secondary cancel-custom-range">Cancel</button>
                </div>
              </div>
            </div>
          `)

          // Insert after the time filter buttons
          $('.time-filter').append($customContainer)
        }

        // Show the custom container
        $customContainer.show()

        // Always update max datetime to current time (timezone-aware)
        const now = new Date()
        const timezoneOffset = now.getTimezoneOffset() * 60000 // Convert to milliseconds
        const localNow = new Date(now.getTime() - timezoneOffset)
        const maxDateTime = localNow.toISOString().slice(0, 16)

        // Set default values if not set and apply timezone-aware max datetime
        const $fromInput = $('#custom-date-from')
        const $toInput = $('#custom-date-to')

        // Always update max attribute to prevent future date selection
        $fromInput.attr('max', maxDateTime)
        $toInput.attr('max', maxDateTime)

        if (!$fromInput.val() || !$toInput.val()) {
          const oneHourAgo = new Date(localNow.getTime() - 60 * 60 * 1000)

          $fromInput.val(oneHourAgo.toISOString().slice(0, 16))
          $toInput.val(maxDateTime)
        }
      } else {
        // Hide custom container if visible
        $('.custom-date-range-dropdown').hide()
        table.draw()
      }
    })

    // Custom date range apply button
    $('html').on('click', '.apply-custom-range', function () {
      const fromDate = $('#custom-date-from').val()
      const toDate = $('#custom-date-to').val()

      if (!fromDate || !toDate) {
        showToast(t('select_both_dates') || 'Please select both from and to dates.', 'error')
        return
      }

      const fromDateTime = new Date(fromDate)
      const toDateTime = new Date(toDate)
      const now = new Date()

      if (fromDateTime > toDateTime) {
        showToast(t('invalid_date_range') || 'From date cannot be later than to date.', 'error')
        return
      }

      // Check if any selected date is in the future
      if (fromDateTime > now || toDateTime > now) {
        showToast(t('future_date_not_allowed') || 'Future dates are not allowed. Please select dates up to the current time.', 'error')
        return
      }

      // Hide the custom dropdown
      $('.custom-date-range-dropdown').hide()

      // Keep the custom button active
      $('.datetime-range-buttons .btn').removeClass('active')
      $('.datetime-range-buttons .btn[value="custom"]').addClass('active')

      table.draw()
    })

    // Custom date range cancel button
    $('html').on('click', '.cancel-custom-range', function () {
      // Hide the custom dropdown
      $('.custom-date-range-dropdown').hide()

      // Reset to "All" filter
      $('.datetime-range-buttons .btn').removeClass('active')
      $('.datetime-range-buttons .btn[value="all"]').addClass('active')
      table.draw()
    })

    // Hide custom dropdown when clicking outside
    $(document).on('click', function (e) {
      const $customContainer = $('.custom-date-range-dropdown')
      const $customBtn = $('.custom-range-btn')

      if ($customContainer.is(':visible') &&
          !$customBtn.is(e.target) &&
          !$customContainer.is(e.target) &&
          $customContainer.has(e.target).length === 0) {
        $customContainer.hide()
        // Reset to "All" filter
        $('.datetime-range-buttons .btn').removeClass('active')
        $('.datetime-range-buttons .btn[value="all"]').addClass('active')
        table.draw()
      }
    })
  }

  startRelativeTimeUpdates () {
    this.stopRelativeTimeUpdates()

    if (window.dbg_lv_backend_data?.datetime_format === DATETIME_FORMAT_RELATIVE) {
      this.relativeTimeInterval = setInterval(() => {
        if (window.dbg_lv_backend_data?.datetime_format === DATETIME_FORMAT_RELATIVE) {
          this.table.cells(null, 2).invalidate().draw(false)
        }
      }, 60000)
    }
  }

  stopRelativeTimeUpdates () {
    if (this.relativeTimeInterval) {
      clearInterval(this.relativeTimeInterval)
      this.relativeTimeInterval = null
    }
  }
}

$(document).ready(function () {
  $(document).on('click', '.freemius-checkout-trigger', function (e) {
    e.preventDefault()
    e.stopPropagation()
    openFreemiusPricing()
  })

  // Handle upgrade button in features modal
  $(document).on('click', '#features-modal-upgrade-btn', function (e) {
    e.preventDefault()
    $('#dbg-lv-features-modal').modal('hide')
    // Small delay to let modal close animation finish
    setTimeout(() => openFreemiusCheckout(), 300)
  })
})
