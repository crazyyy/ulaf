/* global $, document, location */

import { LogViewerApiService } from './log-view/api-service.js'

$(document).ready(function () {
  // Initialize API service
  const apiService = new LogViewerApiService()

  // Handle start debugging button hover effects
  $('#first_run_enable_logging').hover(
    function () {
      $(this).addClass('btn-hover-effect')
    },
    function () {
      $(this).removeClass('btn-hover-effect')
    }
  )

  // Handle start debugging button click with loading state
  $('#first_run_enable_logging').on('click', async function () {
    const $btn = $(this)
    const originalText = $btn.html()

    // Set loading state
    $btn.prop('disabled', true)
    $btn.html('<i class="fas fa-spinner fa-spin"></i> Enabling Debug Logging...')
    $btn.removeClass('btn-hover-effect')

    try {
      await apiService.enableLogging()

      // Show success state in button
      $btn.html('<i class="fas fa-check-circle"></i> Debug Logging Enabled!')
      $btn.removeClass('btn-primary').addClass('btn-success')

      // Reload page after short delay to show success message
      setTimeout(() => location.reload(), 100)
    } catch (error) {
      // Show error state in button
      $btn.html('<i class="fas fa-exclamation-triangle"></i> Failed to Enable')
      $btn.removeClass('btn-primary').addClass('btn-danger')

      // Reset button after showing error
      setTimeout(() => {
        $btn.prop('disabled', false)
        $btn.html(originalText)
        $btn.removeClass('btn-danger').addClass('btn-primary')
      }, 2000)
    }
  })

  // Handle manual instructions toggle with simple show/hide
  $('.manual-debugging-instructions').on('click', function (e) {
    e.preventDefault()
    const target = $(this).attr('href')
    $(target).toggle()

    // Toggle chevron icon
    const icon = $(this).find('.toggle-icon')
    icon.toggleClass('fa-chevron-down fa-chevron-up')
  })
})
