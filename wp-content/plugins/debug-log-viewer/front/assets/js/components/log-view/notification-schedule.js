/* global jQuery, ajaxurl, dbg_lv_backend_data */
import { showToast, t } from '../../utils.js'

export class NotificationScheduleManager {
  constructor () {
    this.nextEmailInfo = $('#next-email-info')
    this.nextEmailTime = $('#next-email-time')
    this.nextEmailCountdown = $('#next-email-countdown')
    this.updateInterval = null
  }

  async updateScheduleDisplay () {
    try {
      const response = await jQuery.post(ajaxurl, {
        action: 'dbg_lv_get_alert_schedule',
        wp_nonce: dbg_lv_backend_data.ajax_nonce
      })

      if (!response.success) {
        throw new Error(response.data || t('request_error'))
      }

      if (response.scheduled) {
        this.nextEmailTime.text(response.formatted_time)
        this.nextEmailCountdown.text(t('in') + ' ' + response.time_until)
        this.nextEmailInfo.show()

        // Start countdown timer
        this.startCountdownTimer(response.timestamp)
      } else {
        this.nextEmailInfo.hide()
        this.stopCountdownTimer()
      }
    } catch (error) {
      console.error('Failed to update schedule display:', error)
      this.nextEmailInfo.hide()
    }
  }

  startCountdownTimer (targetTimestamp) {
    this.stopCountdownTimer()

    this.updateInterval = setInterval(() => {
      const now = Math.floor(Date.now() / 1000)
      const timeDiff = targetTimestamp - now

      if (timeDiff <= 0) {
        this.stopCountdownTimer()
        return
      }

      const timeUntil = this.formatTimeRemaining(timeDiff)
      this.nextEmailCountdown.text(t('in') + ' ' + timeUntil)
    }, 60000) // Update every minute
  }

  stopCountdownTimer () {
    if (this.updateInterval) {
      clearInterval(this.updateInterval)
      this.updateInterval = null
    }
  }

  formatTimeRemaining (seconds) {
    const days = Math.floor(seconds / 86400)
    const hours = Math.floor((seconds % 86400) / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)

    if (days > 0) {
      if (hours > 0) {
        return `${days} ${days === 1 ? t('day') : t('days')} ${hours} ${hours === 1 ? t('hour') : t('hours')}`
      } else {
        return `${days} ${days === 1 ? t('day') : t('days')}`
      }
    } else if (hours > 0) {
      return `${hours} ${hours === 1 ? t('hour') : t('hours')}`
    } else {
      return `${minutes} ${minutes === 1 ? t('minute') : t('minutes')}`
    }
  }

  hide () {
    this.nextEmailInfo.hide()
    this.stopCountdownTimer()
  }

  show () {
    this.updateScheduleDisplay()
  }
}
