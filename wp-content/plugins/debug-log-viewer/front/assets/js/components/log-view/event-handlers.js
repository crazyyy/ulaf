/* global $ */
import { Modal } from 'bootstrap'
import { updateEmailNotifications } from './notification-utils.js'
import { DATETIME_FORMAT_RELATIVE } from './constants.js'

/**
 * Event handlers for log viewer
 */
export class LogViewerEventHandlers {
  constructor (table, apiService, uiUtils) {
    this.table = table
    this.api = apiService
    this.ui = uiUtils
  }

  /**
   * Initialize event handlers
   */
  init () {
    this.bindDynamicEventHandlers()
    this.bindStaticEventHandlers()
  }

  /**
   * Bind event handlers for dynamically created elements
   */
  bindDynamicEventHandlers () {
    // SearchBuilder button click
    $('.search-builder-btn').on('click', function () {
      // Toggle searchBuilder visibility
      $('.dtsb-searchBuilder').toggle()

      // If there's only one child, trigger the button click
      if ($('.dtsb-group').children().length === 1) {
        $('.dtsb-group button').trigger('click')
      }
    })
  }

  /**
   * Bind event handlers for static elements
   */
  bindStaticEventHandlers () {
    const self = this

    // Refresh log button
    $('.refresh-log').on('click', async function () {
      await self.api.updateLogs(() => self.ui.animateRefreshButton())
    })

    // Table XHR event
    $('#dbg_lv_log-table').on('xhr.dt', function (e, settings, json, xhr) {
      if (json.info) {
        const parent = $('.table-wrapper')
        const element = parent.find('.log-viewer-info')

        if (element.length > 0) {
          $(element).text(json.info)
        } else {
          $(parent).prepend(`<div class="alert alert-warning log-viewer-info" role="alert">${json.info}</div>`)
        }
      }
    })

    // Toggle debug mode checkbox
    $('#dbg_lv_toggle_debug_mode').on('change', async function () {
      await self.api.toggleDebugMode($(this).is(':checked'))
    })

    // Toggle debug scripts checkbox
    $('#dbg_lv_toggle_debug_scripts').on('change', async function () {
      await self.api.toggleDebugScripts($(this).is(':checked'))
    })

    // Toggle log in file checkbox
    $('#dbg_lv_toggle_log_in_file').on('change', async function () {
      const isChecked = $(this).is(':checked')

      if (isChecked) {
        self.autoEnableDebugMode()
      }

      await self.api.toggleLogInFile(isChecked)
    })

    // Toggle display errors checkbox
    $('#dbg_lv_toggle_display_errors').on('change', async function () {
      const checkbox = $(this)
      const isChecked = checkbox.is(':checked')

      if (isChecked) {
        const modalElement = $('#display-errors-warning-modal')
        const modal = new Modal(modalElement[0]) // Use imported Modal class

        $('#cancel-display-errors').off('click').on('click', function () {
          checkbox.prop('checked', false)
          modal.hide()
        })

        $('#confirm-display-errors').off('click').on('click', async function () {
          modal.hide()
          self.autoEnableDebugMode()
          await self.api.toggleDisplayErrors(true)
        })

        modal.show()
        return false
      }

      await self.api.toggleDisplayErrors(false)
    })

    // Clear log button
    $(document).on('click', '.clear-log', async function () {
      await self.api.clearLog()
    })

    // Download log button
    $(document).on('click', '.download-log', function () {
      self.api.downloadLog()
    })

    // Email alerts form
    $('#dbg_lv_log_viewer_alerts_form').on('submit', async function (e) {
      e.preventDefault()

      const form = $('#dbg_lv_log_viewer_alerts_form')
      const action = 'dbg_lv_change_log_viewer_alerts_status'

      await updateEmailNotifications(form, action)

      if (form.find('input[type="submit"]')?.val() === 'Enable') {
        self.autoEnableDebugMode()
        self.autoEnableDebugLog()
      }
    })

    // Update mode radio buttons
    $('input[name="UpdatesModeRadioOptions"]').on('change', async function () {
      const selectedMode = $('input[name="UpdatesModeRadioOptions"]:checked').val()

      await self.api.changeLogsUpdateMode(selectedMode)

      if (selectedMode === 'AUTO') {
        self.ui.stopLiveUpdateLogs() // Reset old timer
        self.ui.startLiveUpdateLogs(() => self.api.updateLogs())
      } else {
        self.ui.stopLiveUpdateLogs()
      }
    })

    // Datetime format radio buttons
    $('input[name="DatetimeFormatRadioOptions"]').on('change', async function () {
      const selectedFormat = $('input[name="DatetimeFormatRadioOptions"]:checked').val()

      await self.api.changeDatetimeFormat(selectedFormat)

      // Update the frontend setting for immediate UI updates
      if (window.dbg_lv_backend_data) {
        window.dbg_lv_backend_data.datetime_format = selectedFormat
      }

      // Manage relative time updates based on the new format
      if (selectedFormat === DATETIME_FORMAT_RELATIVE) {
        self.ui.startRelativeTimeUpdates()
      } else {
        self.ui.stopRelativeTimeUpdates()
      }

      // Invalidate datetime column cache and redraw to show the new format
      self.table.cells(null, 2).invalidate().draw(false)
    })
  }

  /**
   * Auto enable debug mode
   */
  autoEnableDebugMode () {
    const target = $('#dbg_lv_toggle_debug_mode')

    if (target.is(':checked')) {
      return
    }

    // Simulate change event
    target.prop('checked', true).trigger('change')
  }

  /**
   * Auto enable debug log
   */
  autoEnableDebugLog () {
    const target = $('#dbg_lv_toggle_log_in_file')

    if (target.is(':checked')) {
      return
    }

    // Simulate change event
    target.prop('checked', true).trigger('change')
  }
}
