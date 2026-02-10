/* global $, MutationObserver */
import { t, updateFilterCount } from '../../utils.js'
import { renderLogTypeBadge, renderDescription, convertToTimestamp, renderDatetime } from './renderers.js'
import { createDropdownMenuItems, isPremium } from './ui-utils.js'

/**
 * Creates and returns the DataTable configuration object
 */
export function createDataTableConfig(initCallbacks) {
  return {
    serverSide: false,
    stateSave: true,
    ordering: true,
    retrieve: true,
    bSort: true,
    order: [[0, 'desc']], // Sort by timestamp desc by default
    processing: true,
    language: {
      processing: `<div class="loader-overlay"></div><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="mt-3">${t('loading_in_process')}</span>`,
      search: '_INPUT_',
      lengthMenu: '_MENU_',
      emptyTable: t('empty_log_table'),
      zeroRecords: t('no_matching_records')
    },
    pageLength: 10,

    layout: {
      topStart: null, // remove pageLength from top default position
      topEnd: null, // remove search from top default position
      top8Start: [
        'pageLength',
        {
          buttons: [
            {
              extend: 'colvis',
              className: 'column-visibility-btn',
              text: t('show-hide')
            },
            {
              extend: 'csv',
              className: 'csv-export-btn d-none', // Hidden, we'll trigger it from the dropdown
              filename: 'debug-log-export',
              exportOptions: {
                format: {
                  body: (data) => {
                    // Clean up HTML tags from the data
                    return $('<div>').html(data).text()
                  }
                }
              }
            }
          ]
        },
        $(`
          <div class="time-filter">
            <div class="datetime-range-buttons btn-group">
              <button class="btn btn-sm btn-outline-primary" value="5m">5m</button>
              <button class="btn btn-sm btn-outline-primary" value="30m">30m</button>
              <button class="btn btn-sm btn-outline-primary" value="1h">1h</button>
              <button class="btn btn-sm btn-outline-primary" value="12h">12h</button>
              <button class="btn btn-sm btn-outline-primary active" value="all">All</button>
              ${isPremium()
            ? '<button class="btn btn-sm btn-outline-primary custom-range-btn" value="custom">Custom</button>'
            : `<div class="position-relative d-inline-block">
                    <button class="btn btn-sm btn-outline-primary custom-range-btn pro-disabled" value="custom" disabled>Custom</button>
                    <span class="pro-badge freemius-checkout-trigger" title="Upgrade to Pro">Pro</span>
                </div>`
          }
            </div>
          </div>`),
        $(`<div class="d-flex ms-4 advanced-filter">
            <button class="btn btn-sm btn-outline-primary search-builder-btn" style="height: 30px; border:none">
              <i class="fa fa-filter"></i>
              <span class="filter-text">${t('filter')}</span>
              <span class="filter-counter"></span>
            </button>
          </div>`)
      ],
      top8End: [
        {
          search: {}
        },
        $(`<div class="dropdown more-dropdown">
          <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          <span>${t('more')}</span>
          </button>
          <ul class="dropdown-menu more-menu" aria-labelledby="dropdownMenuButton">
            ${createDropdownMenuItems()}
          </ul>
        </div>`),
        $(`<button class="btn btn-primary refresh-log" title="${t('refresh_log')}"><i class="fa fa-solid fa-arrows-rotate"></i></button>`)

      ],
      top7: 'searchBuilder'
    },

    columns: [
      {
        data: 'timestamp',
        visible: false,
        render: function (data, type, row) {
          // If timestamp is missing, calculate it from datetime
          return data || convertToTimestamp(row.datetime)
        }
      },
      {
        data: 'type',
        render: renderLogTypeBadge
      },
      { data: 'datetime', className: 'datetime', width: '10%', render: renderDatetime },
      { data: 'description', className: 'description ph-no-capture', render: renderDescription, width: '55%' },
      { data: 'file', className: 'file ph-no-capture', width: '25%' },
      { data: 'line', className: 'line' }
    ],
    autoWidth: false,
    initComplete: function () {
      if (initCallbacks && typeof initCallbacks.onInitComplete === 'function') {
        initCallbacks.onInitComplete()
      }

      const table = this.api()
      $(document).on('click', '.export-csv-btn', function (e) {
        e.preventDefault()
        table.button('.csv-export-btn').trigger()
      })

      const updateFilterCountHandler = () => updateFilterCount(table)

      table.on('searchBuilder-rebuild', updateFilterCountHandler)
      table.on('searchBuilder-redraw', updateFilterCountHandler)
      table.on('draw', updateFilterCountHandler)

      const searchBuilderObserver = new MutationObserver(() => updateFilterCount(table))
      const searchBuilderContainer = document.querySelector('.dtsb-searchBuilder')
      if (searchBuilderContainer) {
        searchBuilderObserver.observe(searchBuilderContainer, {
          childList: true,
          subtree: true
        })
      }

      setTimeout(() => updateFilterCount(table), 500)
    }
  }
}
