"use strict";
(self["webpackChunkdebug_log_viewer"] = self["webpackChunkdebug_log_viewer"] || []).push([["front_assets_js_components_tab-handler_js"],{

/***/ "./front/assets/js/components/tab-handler.js":
/*!***************************************************!*\
  !*** ./front/assets/js/components/tab-handler.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "jquery");
/* global $, jQuery */

/**
 * Tab handler for Debug Log Viewer
 * Handles tab switching without page reload while updating URL
 */
(function ($) {
  'use strict';

  $(document).ready(function () {
    // Handle tab button clicks
    $('.tab-button').on('click', function () {
      // Get the tab identifier from the button ID
      var tabId = $(this).attr('id');
      var tabName = tabId.replace('-tab-btn', '');

      // Update active class on buttons
      $('.tab-button').removeClass('active');
      $(this).addClass('active');

      // Update URL parameter without page reload
      var url = new URL(window.location.href);
      url.searchParams.set('tab', tabName);
      window.history.pushState({}, '', url);

      // Hide all tab panes, then show the selected one
      $('.tab-pane').removeClass('show active');
      $("#".concat(tabName, "-content")).addClass('show active');
    });

    // Ensure correct tab is active on page load
    var url = new URL(window.location.href);
    var activeTabParam = url.searchParams.get('tab');
    if (activeTabParam) {
      var tabEl = document.getElementById(activeTabParam + '-tab');
      if (tabEl) {
        var tab = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Tab(tabEl);
        tab.show();
      }
    } else {
      // Default to log-view tab if no tab parameter
      var _tabEl = document.getElementById('log-view-tab');
      if (_tabEl) {
        var _tab = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Tab(_tabEl);
        _tab.show();
      }
    }
  });
})(jQuery);

/***/ })

}]);