/* global dbg_lv_backend_data, $ */
import { Toast } from 'bootstrap'

/**
 * Translation function
 * @param {string} key - The key to translate
 * @returns {string} - The translated string
 */
export function t (key) {
  const { phrases } = dbg_lv_backend_data

  if (phrases && phrases[key]) {
    return phrases[key]
  }
  console.warn(`Phrase ${key} not found`)
  return key
}

export function showToast (message, level) {
  const $toast = $('#custom-toast')
  const $toastBody = $('#toast-body')

  if (!$toast.length) {
    return // Prevent errors if the toast element is missing
  }

  // Handle Error objects
  const displayMessage = message instanceof Error ? message.message : message

  // Set the message
  $toastBody.text(displayMessage)

  // Determine the level (success, error, warning)
  let toastClass = 'bg-success'
  if (level === 'error') {
    toastClass = 'bg-danger'
  } else if (level === 'warning') {
    toastClass = 'bg-warning'
  }

  // Remove previous level classes and add the new one to the toast container
  $toast.removeClass('bg-success bg-danger bg-warning').addClass(toastClass)

  // Initialize and show the toast
  const toastInstance = new Toast($toast[0])
  toastInstance.show()
}

/**
 * Initialize the scroll to top button
 */
export function initScrollToTopButton () {
  $('body').append(
    `<div class="scroll-top" id="dbg_lv_scrollToTopButton" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512">
            <path fill="#4f6df5" d="M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z"/>
        </svg>
    </div>`
  )

  $(document).on('scroll', function () {
    if ($(this).scrollTop() > 100) {
      $('#dbg_lv_scrollToTopButton').fadeIn()
    } else {
      $('#dbg_lv_scrollToTopButton').fadeOut()
    }
  })

  $('#dbg_lv_scrollToTopButton').on('click', function () {
    $('html, body').animate({ scrollTop: 0 }, 500, 'linear')
    return false
  })
}

/**
 * Updates the filter count badge on the filter button
 * @param {object} table - The DataTable instance
 */
export function updateFilterCount (table) {
  let conditionsCount = 0

  const filterButton = $('.search-builder-btn')
  const filterCount = filterButton.find('.filter-counter')

  const searchBuilderCriteria = table.searchBuilder.getDetails()
  const isTableFiltered = table.search() !== '' || table.rows({ search: 'applied' }).count() !== table.rows().count()

  if (searchBuilderCriteria && searchBuilderCriteria.criteria && isTableFiltered) {
    conditionsCount = searchBuilderCriteria.criteria.filter(criteria => {
      // Check if value exists and is not empty
      if (!criteria.value) return false

      // Check if this is just an empty condition row
      if (criteria.condition === undefined || criteria.condition === '') return false

      if (Array.isArray(criteria.value)) {
        return criteria.value.length > 0 && criteria.value.some(v => v && String(v).trim() !== '')
      }

      return String(criteria.value).trim() !== ''
    }).length
  }

  if (conditionsCount > 0) {
    filterCount.text(` (${conditionsCount})`).show()
  } else {
    filterCount.hide()
  }
}
