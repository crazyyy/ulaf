/* global $, jQuery */
import { Tab } from 'bootstrap';
/**
 * Tab handler for Debug Log Viewer
 * Handles tab switching without page reload while updating URL
 */
(function ($) {
  'use strict'

  $(document).ready(function () {
    // Handle tab button clicks
    $('.tab-button').on('click', function () {
      // Get the tab identifier from the button ID
      const tabId = $(this).attr('id')
      const tabName = tabId.replace('-tab-btn', '')

      // Update active class on buttons
      $('.tab-button').removeClass('active')
      $(this).addClass('active')

      // Update URL parameter without page reload
      const url = new URL(window.location.href)
      url.searchParams.set('tab', tabName)
      window.history.pushState({}, '', url)

      // Hide all tab panes, then show the selected one
      $('.tab-pane').removeClass('show active')
      $(`#${tabName}-content`).addClass('show active')
    })

    // Ensure correct tab is active on page load
    const url = new URL(window.location.href)
    const activeTabParam = url.searchParams.get('tab')

    if (activeTabParam) {
      const tabEl = document.getElementById(activeTabParam + '-tab')
      if (tabEl) {
        const tab = new Tab(tabEl)
        tab.show()
      }
    } else {
      // Default to log-view tab if no tab parameter
      const tabEl = document.getElementById('log-view-tab')
      if (tabEl) {
        const tab = new Tab(tabEl)
        tab.show()
      }
    }
  })
})(jQuery)
