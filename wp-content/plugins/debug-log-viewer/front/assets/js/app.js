/* global $, location */
import 'bootstrap/js/dist/modal'
import 'bootstrap/js/dist/toast'
import 'datatables.net-buttons-bs5'
import 'datatables.net-buttons/js/buttons.colVis.mjs'
import 'datatables.net-buttons/js/buttons.html5.mjs'
import 'datatables.net-searchbuilder-bs5'

import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css'
import 'datatables.net-searchbuilder-bs5/css/searchBuilder.bootstrap5.min.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import '../scss/style.scss'

window.$ = $
window.jQuery = $

import('./components/review-banner.js')

try {
  const page = new URL(location.href).searchParams.get('page')
  if (page === 'debug-log-viewer') {
    import('./components/log-view/index.js')
    import('./components/tab-handler.js')
    import('./components/welcome-screen.js')
  }
} catch (error) {
  console.log(error)
}
