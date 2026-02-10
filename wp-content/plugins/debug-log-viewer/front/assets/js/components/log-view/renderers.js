import { t } from '../../utils.js'
import { DATETIME_FORMAT_ABSOLUTE, DATETIME_FORMAT_RELATIVE } from './constants.js'

/**
 * Renders a badge for log type with appropriate styling
 */
export function renderLogTypeBadge (data) {
  const typeClasses = {
    Notice: 'notice-bg',
    Warning: 'warning-bg',
    Fatal: 'fatal-bg',
    Database: 'database-bg',
    Parse: 'parse-bg',
    Deprecated: 'deprecated-bg',
    Custom: 'custom-bg'
  }
  const className = typeClasses[data] || 'bg-secondary'
  return `<span class="badge ${className}">${data}</span>`
}

/**
 * Renders description with collapsible stack trace if available
 */
export function renderDescription (description) {
  const { text, stack_trace: stackTrace } = description

  if (!stackTrace) return text

  // Generate unique ID with only alphanumeric characters
  const collapseId = 'stack-' + Date.now() + '-' + Math.floor(Math.random() * 1000)

  return `
    <div class="error-description">${text}</div>
    <a class="stack-trace" data-target="#${collapseId}">
      <i class="fa-solid fa-code"></i>
      ${t('stack_trace')}
    </a>
    <div id="${collapseId}" class="dlv-stack-trace" style="display: none;">
      <div class="copy-stack-btn" title="Copy to clipboard">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
        </svg>
      </div>
      <pre>${stackTrace}</pre>
    </div>
  `
}

export function convertToTimestamp (datetimeString) {
  return new Date(datetimeString).getTime()
}

export function renderDatetime (data, type, row) {
  if (type === 'display') {
    const datetimeFormat = window.dbg_lv_backend_data?.datetime_format || DATETIME_FORMAT_ABSOLUTE

    if (datetimeFormat === DATETIME_FORMAT_RELATIVE) {
      return formatRelativeTime(data)
    }
    return data
  }
  return convertToTimestamp(data)
}

function formatRelativeTime (datetimeString) {
  const logDate = new Date(datetimeString)
  const now = new Date()

  // Handle invalid dates
  if (isNaN(logDate.getTime())) {
    return datetimeString // Return original string if parsing fails
  }

  const diffMs = now - logDate

  // If the log entry is in the future, return original datetime
  if (diffMs < 0) {
    return datetimeString
  }

  const diffSeconds = Math.floor(diffMs / 1000)

  // Use the translation function for consistency
  const phrases = window.dbg_lv_backend_data?.phrases || {}

  // Less than 30 seconds = "just now"
  if (diffSeconds < 30) {
    return phrases.just_now || 'just now'
  }

  // 30 seconds to 1 minute = "1 minute ago"
  if (diffSeconds < 60) {
    return phrases.one_minute_ago || '1 minute ago'
  }

  // 1 to 2 minutes = "2 minutes ago"
  if (diffSeconds < 120) {
    return phrases.two_minutes_ago || '2 minutes ago'
  }

  // 2 to 5 minutes = "5 minutes ago"
  if (diffSeconds < 300) {
    return phrases.five_minutes_ago || '5 minutes ago'
  }

  // 5 to 10 minutes = "10 minutes ago"
  if (diffSeconds < 600) {
    return phrases.ten_minutes_ago || '10 minutes ago'
  }

  // 10 to 15 minutes = "15 minutes ago"
  if (diffSeconds < 900) {
    return phrases.fifteen_minutes_ago || '15 minutes ago'
  }

  // 15 to 30 minutes = "30 minutes ago"
  if (diffSeconds < 1800) {
    return phrases.thirty_minutes_ago || '30 minutes ago'
  }

  // 30 minutes to 1 hour = "1 hour ago"
  if (diffSeconds < 3600) {
    return phrases.one_hour_ago || '1 hour ago'
  }

  // 1 to 2 hours = "2 hours ago"
  if (diffSeconds < 7200) {
    return phrases.two_hours_ago || '2 hours ago'
  }

  // 2 to 3 hours = "3 hours ago"
  if (diffSeconds < 10800) {
    return phrases.three_hours_ago || '3 hours ago'
  }

  // 3 to 6 hours = "6 hours ago"
  if (diffSeconds < 21600) {
    return phrases.six_hours_ago || '6 hours ago'
  }

  // 6 to 12 hours = "12 hours ago"
  if (diffSeconds < 43200) {
    return phrases.twelve_hours_ago || '12 hours ago'
  }

  // 12 to 24 hours = "1 day ago"
  if (diffSeconds < 86400) {
    return phrases.one_day_ago || '1 day ago'
  }

  // 1 to 2 days = "2 days ago"
  if (diffSeconds < 172800) {
    return phrases.two_days_ago || '2 days ago'
  }

  // 2 to 3 days = "3 days ago"
  if (diffSeconds < 259200) {
    return phrases.three_days_ago || '3 days ago'
  }

  // 3 to 7 days = "1 week ago"
  if (diffSeconds < 604800) {
    return phrases.one_week_ago || '1 week ago'
  }

  // 1 to 2 weeks = "2 weeks ago"
  if (diffSeconds < 1209600) {
    return phrases.two_weeks_ago || '2 weeks ago'
  }

  // 2 weeks to 1 month = "1 month ago"
  if (diffSeconds < 2592000) {
    return phrases.one_month_ago || '1 month ago'
  }

  // 1 to 2 months = "2 months ago"
  if (diffSeconds < 5184000) {
    return phrases.two_months_ago || '2 months ago'
  }

  // 2 to 3 months = "3 months ago"
  if (diffSeconds < 7776000) {
    return phrases.three_months_ago || '3 months ago'
  }

  // 3 to 6 months = "6 months ago"
  if (diffSeconds < 15552000) {
    return phrases.six_months_ago || '6 months ago'
  }

  // 6 months to 1 year = "1 year ago"
  if (diffSeconds < 31536000) {
    return phrases.one_year_ago || '1 year ago'
  }

  // More than 1 year - calculate years
  const years = Math.floor(diffSeconds / 31536000)
  if (years === 1) {
    return phrases.one_year_ago || '1 year ago'
  } else {
    return (phrases.years_ago || '%d years ago').replace('%d', years)
  }
}

/**
 * Initialize jQuery animations for log view components
 * Call this function after log entries are rendered
 */

/* global $ */
$(document).ready(function ($) {
  // Handle stack trace toggle links
  $(document).on('click', '.stack-trace', function (e) {
    e.preventDefault()

    const $target = $($(this).data('target'))

    if ($target.is(':visible')) {
      $target.slideUp(300)
      $(this).html(`
        <i class="fa-solid fa-code"></i>
        ${t('stack_trace')}`)
    } else {
      $target.slideDown(300)
      $(this).html(t('hide_stack_trace'))
    }
  })

  // Generic copy to clipboard function
  function copyToClipboard ($button, text) {
    navigator.clipboard.writeText(text).then(() => {
      const originalSvg = $button.html()

      // Change to checkmark for feedback
      $button.html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5"></path></svg>')

      // Reset after a short delay
      setTimeout(() => {
        $button.html(originalSvg)
      }, 1500)
    })
  }

  // Handle copy to clipboard for stack traces
  $(document).on('click', '.copy-stack-btn', function (e) {
    e.preventDefault()
    e.stopPropagation()
    const stackTrace = $(this).siblings('pre').text()
    copyToClipboard($(this), stackTrace)
  })

  // Handle copy path to clipboard
  $(document).on('click', '.copy-path-btn', function (e) {
    e.preventDefault()
    e.stopPropagation()
    const path = $(this).siblings('.path-segment').text()
    copyToClipboard($(this), path)
  })

  // console.log('DataTable initialized')
  // $('.dtsb-clearAll').removeClass('btn-secondary').addClass('btn-sm btn-danger')
  // $('.dtsb-add').removeClass('btn-secondary').addClass('btn-sm btn-success')
  // $('.dtsb-delete').removeClass('btn-secondary').addClass('btn-sm btn-outline-danger')
})
