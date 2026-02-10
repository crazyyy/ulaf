/* global jQuery, ajaxurl, dbg_lv_backend_data, confirm, location, URL, XMLHttpRequest */
import { showToast, t } from '../../utils.js'

export class LogViewerApiService {
  constructor(table) {
    this.table = table
  }

  async fillInitialData() {
    try {
      const rawResponse = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_run_live_updates',
        initial: true,
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!rawResponse) return

      const response = JSON.parse(rawResponse)

      if (response.data.length > 0) {
        this.table.rows.add(response.data).draw()
      }
    } catch (error) {
      showToast(error, 'error')
    }
  }

  async updateLogs(onBeforeUpdate) {
    try {
      if (onBeforeUpdate) onBeforeUpdate()

      const rawResponse = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_run_live_updates',
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!rawResponse) return

      const response = JSON.parse(rawResponse)

      if (response.action === 'clear') {
        this.table.clear().draw()
        return
      }

      if (response.data.length > 0) {
        this.table.rows.add(response.data).draw()
      }
    } catch (error) {
      showToast(error, 'error')
    }
  }

  async toggleDebugMode(isChecked) {
    try {
      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_toggle_debug_mode',
        state: +isChecked,
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      const { data } = response

      if (!response.success) {
        throw new Error(response.data)
      }

      showToast(`${t('debug_mode')} ${data.state}`, 'success')
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  async toggleDebugScripts(isChecked) {
    try {
      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_toggle_debug_scripts',
        state: +isChecked,
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      const { data } = response

      if (!response.success) {
        throw new Error(response.data)
      }

      showToast(`${t('debug_scripts')} ${data.state}`, 'success')
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  async toggleLogInFile(isChecked) {
    try {
      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_toggle_log_in_file',
        state: +isChecked,
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })
      const { data } = response

      if (!response.success) {
        throw new Error(response.data)
      }

      showToast(`${t('debug_log_scripts')} ${data.state}`, 'success')
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  async toggleDisplayErrors(isChecked) {
    try {
      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_toggle_display_errors',
        state: +isChecked,
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      const { data } = response

      if (!response.success) {
        throw new Error(response.data)
      }

      showToast(`${t('display_errors')} ${data.state}`, 'success')
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  async isDebugLogPubliclyAccessible() {
    try {
      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_is_debug_log_publicly_accessible',
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!response.success) {
        throw new Error(response.data)
      }

      return response
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  async enableLogging() {
    const response = await jQuery.post(ajaxurl, {
      action: 'dbg_lv_first_run_enable_logging',
      wp_nonce: dbg_lv_backend_data.ajax_nonce
    })

    if (!response.success) {
      throw new Error(response.data)
    }

    return response
  }

  async clearLog() {
    try {
      if (!confirm(t('flush_log_confirmation'))) {
        return null
      }

      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_log_viewer_clear_log',
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!response.success) {
        throw new Error(response.data)
      }

      showToast(t('log_was_cleared'), 'success')
      location.reload()
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  downloadLog() {
    jQuery.post({
      url: ajaxurl,
      data: {
        action: 'dbg_lv_log_viewer_download_log',
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      },
      xhr: function () {
        const xhr = new XMLHttpRequest()
        xhr.responseType = 'blob'
        return xhr
      },
      success: (response) => {
        const url = URL.createObjectURL(response)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'debug.log')

        document.body.append(link)
        link.click()
        document.body.removeChild(link)
        URL.revokeObjectURL(url)
      },
      error: (xhr, status, error) => {
        showToast(error, 'error')
      }
    })
  }

  async changeLogsUpdateMode(mode) {
    try {
      const response = await jQuery.post({
        url: ajaxurl,
        data: {
          action: 'dbg_lv_change_logs_update_mode',
          mode,
          wp_nonce: dbg_lv_backend_data.ajax_nonce
        }
      })

      showToast(response.data, 'success')
      return response
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }

  async changeDatetimeFormat(format) {
    try {
      const response = await jQuery.post({
        url: ajaxurl,
        data: {
          action: 'dbg_lv_change_datetime_format',
          format,
          wp_nonce: dbg_lv_backend_data.ajax_nonce
        }
      })

      if (!response.success) {
        throw new Error(response.data)
      }
      showToast(response.data, 'success')
    } catch (error) {
      showToast(error, 'error')
      throw error
    }
  }
}
