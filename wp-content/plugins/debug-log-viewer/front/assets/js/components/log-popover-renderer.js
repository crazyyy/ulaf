import { Popover } from 'bootstrap'
import { t } from '../utils.js'

export class LogPopoverRenderer {
  static container = null
  static popovers = []

  static setContainer (container) {
    this.container = container
  }

  static renderLogFileInfo (data) {
    const items = []

    if (data.fileSize) {
      items.push(`<div class="d-flex align-items-center mb-2">
        <i class="fa-solid fa-hdd text-muted me-2"></i>
        <span><strong>${t('file_size')}:</strong> ${data.fileSize}</span>
      </div>`)
    }

    if (data.lastModified) {
      items.push(`<div class="d-flex align-items-center mb-2">
        <i class="fa-solid fa-clock text-muted me-2"></i>
        <span><strong>${t('last_modified')}:</strong> ${data.lastModified}</span>
      </div>`)
    }

    if (data.permissions) {
      items.push(`<div class="d-flex align-items-center mb-2">
        <i class="fa-solid fa-shield-halved text-muted me-2"></i>
        <span><strong>${t('permissions')}:</strong> ${data.permissions}</span>
      </div>`)
    }

    return items.join('')
  }

  static renderLogFileWarnings (warnings) {
    return warnings.map(warning => {
      const statusClass = warning.type === 'danger' ? 'text-danger' : 'text-warning'
      const dismissIcon = warning.type === 'danger'
        ? '<i class="fa-solid fa-clock text-muted ms-2" id="dbg_lv_dismiss_alert" title="Dismiss for a week"></i>'
        : ''

      return `<div class="d-flex align-items-start mb-2">
        <i class="fa-solid ${warning.icon} ${statusClass} me-2 mt-1"></i>
        <div class="flex-grow-1">
          <strong>${warning.title}${dismissIcon}</strong><br>
          <small class="text-muted">${warning.message}</small>
        </div>
      </div>`
    }).join('')
  }

  static getWarningsSeverity (warnings) {
    if (!Array.isArray(warnings) || warnings.length === 0) return 'none'
    return warnings.some(w => w.type === 'danger') ? 'danger' : 'warning'
  }

  static refresh (logData) {
    if (!this.container) {
      console.warn('LogPopoverRenderer container not set')
      return
    }

    this.cleanup()
    this.container.innerHTML = ''

    if (logData.warnings?.length > 0) {
      const severity = this.getWarningsSeverity(logData.warnings)
      if (severity !== 'none') {
        this.container.appendChild(this.createTrigger(
          `fa-solid fa-triangle-exclamation log-warning-trigger severity-${severity}`,
          t('log_file_status_warnings'),
          this.renderLogFileWarnings(logData.warnings)
        ))
      }
    }

    if (logData.fileInfo) {
      this.container.appendChild(this.createTrigger(
        'fa-solid fa-circle-info log-info-trigger',
        t('log_file_information'),
        this.renderLogFileInfo(logData.fileInfo)
      ))
    }

    this.initializePopovers()
  }

  static createTrigger (className, title, content) {
    const trigger = document.createElement('i')
    Object.assign(trigger, {
      className,
      tabIndex: 0
    })

    const attrs = {
      'data-bs-toggle': 'popover',
      'data-bs-placement': 'bottom',
      'data-bs-html': 'true',
      'data-bs-trigger': 'click',
      'data-bs-title': title,
      'data-bs-content': content
    }

    Object.entries(attrs).forEach(([key, value]) => trigger.setAttribute(key, value))
    return trigger
  }

  static initializePopovers () {
    const triggers = this.container.querySelectorAll('[data-bs-toggle="popover"]')

    this.popovers = Array.from(triggers).map(trigger => {
      const popover = new Popover(trigger, {
        html: true,
        placement: 'bottom',
        trigger: 'click'
      })

      trigger.addEventListener('shown.bs.popover', () => {
        this.setupClickHandler(trigger, popover)
      })

      return popover
    })
  }

  static setupClickHandler (trigger, popover) {
    const handler = (event) => {
      const popoverEl = document.querySelector('.popover')
      const isOutsideClick = popoverEl &&
        !trigger.contains(event.target) &&
        !popoverEl.contains(event.target)
      const isDismissClick = event.target.id === 'dbg_lv_dismiss_alert'

      if (isOutsideClick || isDismissClick) {
        popover.hide()
      }
    }

    setTimeout(() => document.addEventListener('click', handler), 100)
    trigger.addEventListener('hidden.bs.popover', () => {
      document.removeEventListener('click', handler)
    }, { once: true })
  }

  static cleanup () {
    this.popovers.forEach(popover => popover?.dispose?.())
    this.popovers = []
    document.querySelectorAll('.popover').forEach(el => el.remove())
  }
}

window.LogPopoverRenderer = LogPopoverRenderer
