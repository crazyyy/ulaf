"use strict";
(self["webpackChunkdebug_log_viewer"] = self["webpackChunkdebug_log_viewer"] || []).push([["front_assets_js_components_log-view_index_js"],{

/***/ "./front/assets/js/components/help-scout.js":
/*!**************************************************!*\
  !*** ./front/assets/js/components/help-scout.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initHelpScoutBeacon: () => (/* binding */ initHelpScoutBeacon)
/* harmony export */ });
/* global window, document */

/**
 * Initialize Help Scout Beacon chat
 * @param {string} beaconId - The Help Scout Beacon ID
 * @param {Object} userData - Optional user data for identification
 */
function initHelpScoutBeacon(beaconId) {
  var userData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  // Initialize the Beacon script
  ;
  (function (w, d, n) {
    function a() {
      var s = d.getElementsByTagName('script')[0];
      var b = d.createElement('script');
      b.type = 'text/javascript';
      b.async = true;
      b.src = 'https://beacon-v2.helpscout.net';
      s.parentNode.insertBefore(b, s);
    }
    if (w.Beacon = n = function n(t, _n, a) {
      w.Beacon.readyQueue.push({
        method: t,
        options: _n,
        data: a
      });
    }, n.readyQueue = [], d.readyState === 'complete') {
      return a();
    }
    w.attachEvent ? w.attachEvent('onload', a) : w.addEventListener('load', a, false);
  })(window, document, window.Beacon || function () {});

  // Initialize with your Beacon ID
  window.Beacon('init', beaconId);
  window.Beacon('config', {
    display: {
      verticalOffset: 30,
      horizontalOffset: 25
    }
  });
  // If user data is provided, identify the user
  if (userData) {
    window.Beacon('identify', userData);
  }
}

/***/ }),

/***/ "./front/assets/js/components/log-popover-renderer.js":
/*!************************************************************!*\
  !*** ./front/assets/js/components/log-popover-renderer.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   LogPopoverRenderer: () => (/* binding */ LogPopoverRenderer)
/* harmony export */ });
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils.js */ "./front/assets/js/utils.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


var LogPopoverRenderer = /*#__PURE__*/function () {
  function LogPopoverRenderer() {
    _classCallCheck(this, LogPopoverRenderer);
  }
  return _createClass(LogPopoverRenderer, null, [{
    key: "setContainer",
    value: function setContainer(container) {
      this.container = container;
    }
  }, {
    key: "renderLogFileInfo",
    value: function renderLogFileInfo(data) {
      var items = [];
      if (data.fileSize) {
        items.push("<div class=\"d-flex align-items-center mb-2\">\n        <i class=\"fa-solid fa-hdd text-muted me-2\"></i>\n        <span><strong>".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.t)('file_size'), ":</strong> ").concat(data.fileSize, "</span>\n      </div>"));
      }
      if (data.lastModified) {
        items.push("<div class=\"d-flex align-items-center mb-2\">\n        <i class=\"fa-solid fa-clock text-muted me-2\"></i>\n        <span><strong>".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.t)('last_modified'), ":</strong> ").concat(data.lastModified, "</span>\n      </div>"));
      }
      if (data.permissions) {
        items.push("<div class=\"d-flex align-items-center mb-2\">\n        <i class=\"fa-solid fa-shield-halved text-muted me-2\"></i>\n        <span><strong>".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.t)('permissions'), ":</strong> ").concat(data.permissions, "</span>\n      </div>"));
      }
      return items.join('');
    }
  }, {
    key: "renderLogFileWarnings",
    value: function renderLogFileWarnings(warnings) {
      return warnings.map(function (warning) {
        var statusClass = warning.type === 'danger' ? 'text-danger' : 'text-warning';
        var dismissIcon = warning.type === 'danger' ? '<i class="fa-solid fa-clock text-muted ms-2" id="dbg_lv_dismiss_alert" title="Dismiss for a week"></i>' : '';
        return "<div class=\"d-flex align-items-start mb-2\">\n        <i class=\"fa-solid ".concat(warning.icon, " ").concat(statusClass, " me-2 mt-1\"></i>\n        <div class=\"flex-grow-1\">\n          <strong>").concat(warning.title).concat(dismissIcon, "</strong><br>\n          <small class=\"text-muted\">").concat(warning.message, "</small>\n        </div>\n      </div>");
      }).join('');
    }
  }, {
    key: "getWarningsSeverity",
    value: function getWarningsSeverity(warnings) {
      if (!Array.isArray(warnings) || warnings.length === 0) return 'none';
      return warnings.some(function (w) {
        return w.type === 'danger';
      }) ? 'danger' : 'warning';
    }
  }, {
    key: "refresh",
    value: function refresh(logData) {
      var _logData$warnings;
      if (!this.container) {
        console.warn('LogPopoverRenderer container not set');
        return;
      }
      this.cleanup();
      this.container.innerHTML = '';
      if (((_logData$warnings = logData.warnings) === null || _logData$warnings === void 0 ? void 0 : _logData$warnings.length) > 0) {
        var severity = this.getWarningsSeverity(logData.warnings);
        if (severity !== 'none') {
          this.container.appendChild(this.createTrigger("fa-solid fa-triangle-exclamation log-warning-trigger severity-".concat(severity), (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.t)('log_file_status_warnings'), this.renderLogFileWarnings(logData.warnings)));
        }
      }
      if (logData.fileInfo) {
        this.container.appendChild(this.createTrigger('fa-solid fa-circle-info log-info-trigger', (0,_utils_js__WEBPACK_IMPORTED_MODULE_1__.t)('log_file_information'), this.renderLogFileInfo(logData.fileInfo)));
      }
      this.initializePopovers();
    }
  }, {
    key: "createTrigger",
    value: function createTrigger(className, title, content) {
      var trigger = document.createElement('i');
      Object.assign(trigger, {
        className: className,
        tabIndex: 0
      });
      var attrs = {
        'data-bs-toggle': 'popover',
        'data-bs-placement': 'bottom',
        'data-bs-html': 'true',
        'data-bs-trigger': 'click',
        'data-bs-title': title,
        'data-bs-content': content
      };
      Object.entries(attrs).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          value = _ref2[1];
        return trigger.setAttribute(key, value);
      });
      return trigger;
    }
  }, {
    key: "initializePopovers",
    value: function initializePopovers() {
      var _this = this;
      var triggers = this.container.querySelectorAll('[data-bs-toggle="popover"]');
      this.popovers = Array.from(triggers).map(function (trigger) {
        var popover = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Popover(trigger, {
          html: true,
          placement: 'bottom',
          trigger: 'click'
        });
        trigger.addEventListener('shown.bs.popover', function () {
          _this.setupClickHandler(trigger, popover);
        });
        return popover;
      });
    }
  }, {
    key: "setupClickHandler",
    value: function setupClickHandler(trigger, popover) {
      var handler = function handler(event) {
        var popoverEl = document.querySelector('.popover');
        var isOutsideClick = popoverEl && !trigger.contains(event.target) && !popoverEl.contains(event.target);
        var isDismissClick = event.target.id === 'dbg_lv_dismiss_alert';
        if (isOutsideClick || isDismissClick) {
          popover.hide();
        }
      };
      setTimeout(function () {
        return document.addEventListener('click', handler);
      }, 100);
      trigger.addEventListener('hidden.bs.popover', function () {
        document.removeEventListener('click', handler);
      }, {
        once: true
      });
    }
  }, {
    key: "cleanup",
    value: function cleanup() {
      this.popovers.forEach(function (popover) {
        var _popover$dispose;
        return popover === null || popover === void 0 || (_popover$dispose = popover.dispose) === null || _popover$dispose === void 0 ? void 0 : _popover$dispose.call(popover);
      });
      this.popovers = [];
      document.querySelectorAll('.popover').forEach(function (el) {
        return el.remove();
      });
    }
  }]);
}();
_defineProperty(LogPopoverRenderer, "container", null);
_defineProperty(LogPopoverRenderer, "popovers", []);
window.LogPopoverRenderer = LogPopoverRenderer;

/***/ }),

/***/ "./front/assets/js/components/log-view/alert-handler.js":
/*!**************************************************************!*\
  !*** ./front/assets/js/components/log-view/alert-handler.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AlertHandler: () => (/* binding */ AlertHandler)
/* harmony export */ });
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global localStorage, dbg_lv_backend_data */

/**
 * Handles the alert dismissal functionality
 */
var AlertHandler = /*#__PURE__*/function () {
  function AlertHandler() {
    _classCallCheck(this, AlertHandler);
    this.dismissKey = 'dbg_lv_public_log_alert_dismissed';
    this.dismissSelector = '#dbg_lv_dismiss_alert';
    this.weekInMilliseconds = 7 * 24 * 60 * 60 * 1000; // 7 days in milliseconds
  }

  /**
   * Initialize the alert handler
   */
  return _createClass(AlertHandler, [{
    key: "init",
    value: function init() {
      // Add event delegation for dismiss buttons in dynamically created popovers
      $(document).off('click', this.dismissSelector).on('click', this.dismissSelector, function (e) {
        e.preventDefault();
        this.dismissForWeek();
        this.removePublicLogAlert();
      }.bind(this));
    }

    /**
     * Dismiss the alert for a week
     */
  }, {
    key: "dismissForWeek",
    value: function dismissForWeek() {
      // Store current timestamp in localStorage
      localStorage.setItem(this.dismissKey, new Date().getTime().toString());
    }
  }, {
    key: "removePublicLogAlert",
    value: function removePublicLogAlert() {
      dbg_lv_backend_data.log_popover_data.warnings = dbg_lv_backend_data.log_popover_data.warnings.filter(function (warning) {
        return warning.key !== 'dbg_lv_public_log_alert_dismissed';
      });
      if (window.LogPopoverRenderer) {
        window.LogPopoverRenderer.refresh(dbg_lv_backend_data.log_popover_data);
      }
    }

    /**
     * Check if the alert is currently dismissed
     * @return {boolean} True if the alert is currently dismissed
     */
  }, {
    key: "isCurrentlyDismissed",
    value: function isCurrentlyDismissed() {
      var dismissedTimestamp = localStorage.getItem(this.dismissKey);
      if (dismissedTimestamp) {
        var currentTime = new Date().getTime();
        var dismissedTime = parseInt(dismissedTimestamp, 10);

        // If less than a week has passed, the alert is considered dismissed
        return currentTime - dismissedTime < this.weekInMilliseconds;
      }
      return false;
    }
  }]);
}();

/***/ }),

/***/ "./front/assets/js/components/log-view/constants.js":
/*!**********************************************************!*\
  !*** ./front/assets/js/components/log-view/constants.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DATETIME_FORMAT_ABSOLUTE: () => (/* binding */ DATETIME_FORMAT_ABSOLUTE),
/* harmony export */   DATETIME_FORMAT_RELATIVE: () => (/* binding */ DATETIME_FORMAT_RELATIVE)
/* harmony export */ });
// Datetime format constants
var DATETIME_FORMAT_ABSOLUTE = 'ABSOLUTE';
var DATETIME_FORMAT_RELATIVE = 'RELATIVE';

/***/ }),

/***/ "./front/assets/js/components/log-view/email-recipients-manager.js":
/*!*************************************************************************!*\
  !*** ./front/assets/js/components/log-view/email-recipients-manager.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   EmailRecipientsManager: () => (/* binding */ EmailRecipientsManager)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global jQuery */


/**
 * Handles multiple email inputs for alert notifications
 */
var EmailRecipientsManager = /*#__PURE__*/function () {
  function EmailRecipientsManager() {
    var containerSelector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '#email-recipients-container';
    _classCallCheck(this, EmailRecipientsManager);
    this.container = jQuery(containerSelector);
    this.maxEmails = 3;
    this.init();
  }

  /**
   * Initialize the email recipients manager
   */
  return _createClass(EmailRecipientsManager, [{
    key: "init",
    value: function init() {
      if (!this.container.length) {
        return;
      }
      this.bindEvents();
      this.updateButtonStates();
    }

    /**
     * Bind event listeners
     */
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      // Add email button click
      this.container.on('click', '.add-email-btn', function (e) {
        e.preventDefault();
        _this.addEmailInput();
      });

      // Remove email button click
      this.container.on('click', '.remove-email-btn', function (e) {
        e.preventDefault();
        _this.removeEmailInput(jQuery(e.currentTarget).closest('.email-input-row'));
      });

      // Update button states when inputs change
      this.container.on('input', 'input[type="email"]', function () {
        _this.updateButtonStates();
      });
    }

    /**
     * Add a new email input row
     */
  }, {
    key: "addEmailInput",
    value: function addEmailInput() {
      var currentRows = this.container.find('.email-input-row');
      if (currentRows.length >= this.maxEmails) {
        return;
      }
      var newIndex = currentRows.length;
      var newRow = this.createEmailRow(newIndex, '');
      this.container.append(newRow);
      this.updateButtonStates();

      // Focus the new input
      newRow.find('input[type="email"]').focus();
    }

    /**
     * Remove an email input row
     */
  }, {
    key: "removeEmailInput",
    value: function removeEmailInput(row) {
      var allRows = this.container.find('.email-input-row');

      // Don't remove if it's the only row
      if (allRows.length <= 1) {
        return;
      }
      row.remove();
      this.updateIndices();
      this.updateButtonStates();
    }

    /**
     * Create a new email input row
     */
  }, {
    key: "createEmailRow",
    value: function createEmailRow(index) {
      var email = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      var row = jQuery("\n      <div class=\"email-input-row\" data-index=\"".concat(index, "\">\n        <input type=\"email\" name=\"emails[]\" value=\"").concat(email, "\" />\n        <button type=\"button\" class=\"btn btn-outline-danger btn-sm remove-email-btn\" title=\"").concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('remove_this_email'), "\">-</button>\n      </div>\n    "));
      return row;
    }

    /**
     * Update button states based on current rows
     */
  }, {
    key: "updateButtonStates",
    value: function updateButtonStates() {
      var rows = this.container.find('.email-input-row');
      var totalRows = rows.length;

      // Remove all add buttons first
      rows.find('.add-email-btn').remove();

      // Add the + button to the first row only
      var firstRow = rows.first();
      if (firstRow.length) {
        var addButton = firstRow.find('.add-email-btn');
        if (addButton.length === 0) {
          addButton = jQuery("<button type=\"button\" class=\"btn btn-outline-primary btn-sm add-email-btn\" title=\"".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('add_another_email'), "\">+</button>"));
          firstRow.append(addButton);
        }

        // Disable add button if we've reached the max
        addButton.prop('disabled', totalRows >= this.maxEmails);
      }

      // Update remove buttons
      rows.each(function (index, row) {
        var $row = jQuery(row);
        var removeBtn = $row.find('.remove-email-btn');
        if (index === 0) {
          // First row: hide remove button if it's the only row
          removeBtn.toggle(totalRows > 1);
        } else {
          // Other rows: always show remove button
          removeBtn.show();
        }
      });
    }

    /**
     * Update data-index attributes after row removal
     */
  }, {
    key: "updateIndices",
    value: function updateIndices() {
      this.container.find('.email-input-row').each(function (index, row) {
        jQuery(row).attr('data-index', index);
      });
    }

    /**
     * Get all email values
     */
  }, {
    key: "getEmails",
    value: function getEmails() {
      return this.container.find('input[type="email"]').map(function (index, input) {
        return jQuery(input).val().trim();
      }).get().filter(function (email) {
        return email !== '';
      });
    }

    /**
     * Set email values
     */
  }, {
    key: "setEmails",
    value: function setEmails(emails) {
      var _this2 = this;
      // Clear existing rows
      this.container.empty();

      // Ensure we have at least one email input
      if (emails.length === 0) {
        emails = [''];
      }

      // Create rows for each email
      emails.forEach(function (email, index) {
        var row = _this2.createEmailRow(index, email);
        _this2.container.append(row);
      });
      this.updateButtonStates();
    }

    /**
     * Validate all email inputs
     */
  }, {
    key: "validate",
    value: function validate() {
      var _this3 = this;
      var emails = this.getEmails();
      var errors = [];
      if (emails.length === 0) {
        errors.push((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('at_least_one_email_required'));
      }
      emails.forEach(function (email, index) {
        if (!_this3.isValidEmail(email)) {
          errors.push((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('email_not_valid').replace('%d', index + 1));
        }
      });
      return {
        isValid: errors.length === 0,
        errors: errors
      };
    }

    /**
     * Simple email validation - ReDoS safe
     */
  }, {
    key: "isValidEmail",
    value: function isValidEmail(email) {
      // Prevent ReDoS attacks by using length check and safe regex
      if (!email || email.length > 254) {
        return false;
      }

      // Safe regex pattern without potential for catastrophic backtracking
      var emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
      return emailRegex.test(email);
    }

    /**
     * Enable/disable all email inputs
     */
  }, {
    key: "setEnabled",
    value: function setEnabled(enabled) {
      this.container.find('input[type="email"]').prop('disabled', !enabled);
      this.container.find('.add-email-btn, .remove-email-btn').prop('disabled', !enabled);
    }
  }]);
}();

/***/ }),

/***/ "./front/assets/js/components/log-view/event-handlers.js":
/*!***************************************************************!*\
  !*** ./front/assets/js/components/log-view/event-handlers.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   LogViewerEventHandlers: () => (/* binding */ LogViewerEventHandlers)
/* harmony export */ });
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
/* harmony import */ var _notification_utils_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./notification-utils.js */ "./front/assets/js/components/log-view/notification-utils.js");
/* harmony import */ var _constants_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./constants.js */ "./front/assets/js/components/log-view/constants.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; var r = _regenerator(), e = r.m(_regeneratorRuntime), t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor; function n(r) { var e = "function" == typeof r && r.constructor; return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name)); } var o = { "throw": 1, "return": 2, "break": 3, "continue": 3 }; function a(r) { var e, t; return function (n) { e || (e = { stop: function stop() { return t(n.a, 2); }, "catch": function _catch() { return n.v; }, abrupt: function abrupt(r, e) { return t(n.a, o[r], e); }, delegateYield: function delegateYield(r, o, a) { return e.resultName = o, t(n.d, _regeneratorValues(r), a); }, finish: function finish(r) { return t(n.f, r); } }, t = function t(r, _t, o) { n.p = e.prev, n.n = e.next; try { return r(_t, o); } finally { e.next = n.n; } }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n; try { return r.call(this, e); } finally { n.p = e.prev, n.n = e.next; } }; } return (_regeneratorRuntime = function _regeneratorRuntime() { return { wrap: function wrap(e, t, n, o) { return r.w(a(e), t, n, o && o.reverse()); }, isGeneratorFunction: n, mark: r.m, awrap: function awrap(r, e) { return new _OverloadYield(r, e); }, AsyncIterator: _regeneratorAsyncIterator, async: function async(r, e, t, o, u) { return (n(e) ? _regeneratorAsyncGen : _regeneratorAsync)(a(r), e, t, o, u); }, keys: _regeneratorKeys, values: _regeneratorValues }; })(); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regeneratorKeys(e) { var n = Object(e), r = []; for (var t in n) r.unshift(t); return function e() { for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e; return e.done = !0, e; }; }
function _regeneratorAsync(n, e, r, t, o) { var a = _regeneratorAsyncGen(n, e, r, t, o); return a.next().then(function (n) { return n.done ? n.value : a.next(); }); }
function _regeneratorAsyncGen(r, e, t, o, n) { return new _regeneratorAsyncIterator(_regenerator().w(r, e, t, o), n || Promise); }
function _regeneratorAsyncIterator(t, e) { function n(r, o, i, f) { try { var c = t[r](o), u = c.value; return u instanceof _OverloadYield ? e.resolve(u.v).then(function (t) { n("next", t, i, f); }, function (t) { n("throw", t, i, f); }) : e.resolve(u).then(function (t) { c.value = t, i(c); }, function (t) { return n("throw", t, i, f); }); } catch (t) { f(t); } } var r; this.next || (_regeneratorDefine2(_regeneratorAsyncIterator.prototype), _regeneratorDefine2(_regeneratorAsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () { return this; })), _regeneratorDefine2(this, "_invoke", function (t, o, i) { function f() { return new e(function (e, r) { n(t, i, e, r); }); } return r = r ? r.then(f, f) : f(); }, !0); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function _OverloadYield(e, d) { this.v = e, this.k = d; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global $ */




/**
 * Event handlers for log viewer
 */
var LogViewerEventHandlers = /*#__PURE__*/function () {
  function LogViewerEventHandlers(table, apiService, uiUtils) {
    _classCallCheck(this, LogViewerEventHandlers);
    this.table = table;
    this.api = apiService;
    this.ui = uiUtils;
  }

  /**
   * Initialize event handlers
   */
  return _createClass(LogViewerEventHandlers, [{
    key: "init",
    value: function init() {
      this.bindDynamicEventHandlers();
      this.bindStaticEventHandlers();
    }

    /**
     * Bind event handlers for dynamically created elements
     */
  }, {
    key: "bindDynamicEventHandlers",
    value: function bindDynamicEventHandlers() {
      // SearchBuilder button click
      $('.search-builder-btn').on('click', function () {
        // Toggle searchBuilder visibility
        $('.dtsb-searchBuilder').toggle();

        // If there's only one child, trigger the button click
        if ($('.dtsb-group').children().length === 1) {
          $('.dtsb-group button').trigger('click');
        }
      });
    }

    /**
     * Bind event handlers for static elements
     */
  }, {
    key: "bindStaticEventHandlers",
    value: function bindStaticEventHandlers() {
      var self = this;

      // Refresh log button
      $('.refresh-log').on('click', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.next = 2;
              return self.api.updateLogs(function () {
                return self.ui.animateRefreshButton();
              });
            case 2:
            case "end":
              return _context.stop();
          }
        }, _callee);
      })));

      // Table XHR event
      $('#dbg_lv_log-table').on('xhr.dt', function (e, settings, json, xhr) {
        if (json.info) {
          var parent = $('.table-wrapper');
          var element = parent.find('.log-viewer-info');
          if (element.length > 0) {
            $(element).text(json.info);
          } else {
            $(parent).prepend("<div class=\"alert alert-warning log-viewer-info\" role=\"alert\">".concat(json.info, "</div>"));
          }
        }
      });

      // Toggle debug mode checkbox
      $('#dbg_lv_toggle_debug_mode').on('change', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.next = 2;
              return self.api.toggleDebugMode($(this).is(':checked'));
            case 2:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this);
      })));

      // Toggle debug scripts checkbox
      $('#dbg_lv_toggle_debug_scripts').on('change', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              _context3.next = 2;
              return self.api.toggleDebugScripts($(this).is(':checked'));
            case 2:
            case "end":
              return _context3.stop();
          }
        }, _callee3, this);
      })));

      // Toggle log in file checkbox
      $('#dbg_lv_toggle_log_in_file').on('change', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        var isChecked;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              isChecked = $(this).is(':checked');
              if (isChecked) {
                self.autoEnableDebugMode();
              }
              _context4.next = 4;
              return self.api.toggleLogInFile(isChecked);
            case 4:
            case "end":
              return _context4.stop();
          }
        }, _callee4, this);
      })));

      // Toggle display errors checkbox
      $('#dbg_lv_toggle_display_errors').on('change', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6() {
        var checkbox, isChecked, modalElement, modal;
        return _regeneratorRuntime().wrap(function _callee6$(_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              checkbox = $(this);
              isChecked = checkbox.is(':checked');
              if (!isChecked) {
                _context6.next = 9;
                break;
              }
              modalElement = $('#display-errors-warning-modal');
              modal = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Modal(modalElement[0]); // Use imported Modal class
              $('#cancel-display-errors').off('click').on('click', function () {
                checkbox.prop('checked', false);
                modal.hide();
              });
              $('#confirm-display-errors').off('click').on('click', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
                return _regeneratorRuntime().wrap(function _callee5$(_context5) {
                  while (1) switch (_context5.prev = _context5.next) {
                    case 0:
                      modal.hide();
                      self.autoEnableDebugMode();
                      _context5.next = 4;
                      return self.api.toggleDisplayErrors(true);
                    case 4:
                    case "end":
                      return _context5.stop();
                  }
                }, _callee5);
              })));
              modal.show();
              return _context6.abrupt("return", false);
            case 9:
              _context6.next = 11;
              return self.api.toggleDisplayErrors(false);
            case 11:
            case "end":
              return _context6.stop();
          }
        }, _callee6, this);
      })));

      // Clear log button
      $(document).on('click', '.clear-log', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7() {
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              _context7.next = 2;
              return self.api.clearLog();
            case 2:
            case "end":
              return _context7.stop();
          }
        }, _callee7);
      })));

      // Download log button
      $(document).on('click', '.download-log', function () {
        self.api.downloadLog();
      });

      // Email alerts form
      $('#dbg_lv_log_viewer_alerts_form').on('submit', /*#__PURE__*/function () {
        var _ref8 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee8(e) {
          var _form$find;
          var form, action;
          return _regeneratorRuntime().wrap(function _callee8$(_context8) {
            while (1) switch (_context8.prev = _context8.next) {
              case 0:
                e.preventDefault();
                form = $('#dbg_lv_log_viewer_alerts_form');
                action = 'dbg_lv_change_log_viewer_alerts_status';
                _context8.next = 5;
                return (0,_notification_utils_js__WEBPACK_IMPORTED_MODULE_1__.updateEmailNotifications)(form, action);
              case 5:
                if (((_form$find = form.find('input[type="submit"]')) === null || _form$find === void 0 ? void 0 : _form$find.val()) === 'Enable') {
                  self.autoEnableDebugMode();
                  self.autoEnableDebugLog();
                }
              case 6:
              case "end":
                return _context8.stop();
            }
          }, _callee8);
        }));
        return function (_x) {
          return _ref8.apply(this, arguments);
        };
      }());

      // Update mode radio buttons
      $('input[name="UpdatesModeRadioOptions"]').on('change', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee9() {
        var selectedMode;
        return _regeneratorRuntime().wrap(function _callee9$(_context9) {
          while (1) switch (_context9.prev = _context9.next) {
            case 0:
              selectedMode = $('input[name="UpdatesModeRadioOptions"]:checked').val();
              _context9.next = 3;
              return self.api.changeLogsUpdateMode(selectedMode);
            case 3:
              if (selectedMode === 'AUTO') {
                self.ui.stopLiveUpdateLogs(); // Reset old timer
                self.ui.startLiveUpdateLogs(function () {
                  return self.api.updateLogs();
                });
              } else {
                self.ui.stopLiveUpdateLogs();
              }
            case 4:
            case "end":
              return _context9.stop();
          }
        }, _callee9);
      })));

      // Datetime format radio buttons
      $('input[name="DatetimeFormatRadioOptions"]').on('change', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee10() {
        var selectedFormat;
        return _regeneratorRuntime().wrap(function _callee10$(_context10) {
          while (1) switch (_context10.prev = _context10.next) {
            case 0:
              selectedFormat = $('input[name="DatetimeFormatRadioOptions"]:checked').val();
              _context10.next = 3;
              return self.api.changeDatetimeFormat(selectedFormat);
            case 3:
              // Update the frontend setting for immediate UI updates
              if (window.dbg_lv_backend_data) {
                window.dbg_lv_backend_data.datetime_format = selectedFormat;
              }

              // Manage relative time updates based on the new format
              if (selectedFormat === _constants_js__WEBPACK_IMPORTED_MODULE_2__.DATETIME_FORMAT_RELATIVE) {
                self.ui.startRelativeTimeUpdates();
              } else {
                self.ui.stopRelativeTimeUpdates();
              }

              // Invalidate datetime column cache and redraw to show the new format
              self.table.cells(null, 2).invalidate().draw(false);
            case 6:
            case "end":
              return _context10.stop();
          }
        }, _callee10);
      })));
    }

    /**
     * Auto enable debug mode
     */
  }, {
    key: "autoEnableDebugMode",
    value: function autoEnableDebugMode() {
      var target = $('#dbg_lv_toggle_debug_mode');
      if (target.is(':checked')) {
        return;
      }

      // Simulate change event
      target.prop('checked', true).trigger('change');
    }

    /**
     * Auto enable debug log
     */
  }, {
    key: "autoEnableDebugLog",
    value: function autoEnableDebugLog() {
      var target = $('#dbg_lv_toggle_log_in_file');
      if (target.is(':checked')) {
        return;
      }

      // Simulate change event
      target.prop('checked', true).trigger('change');
    }
  }]);
}();

/***/ }),

/***/ "./front/assets/js/components/log-view/index.js":
/*!******************************************************!*\
  !*** ./front/assets/js/components/log-view/index.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* harmony import */ var _table_config_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./table-config.js */ "./front/assets/js/components/log-view/table-config.js");
/* harmony import */ var _api_service_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./api-service.js */ "./front/assets/js/components/log-view/api-service.js");
/* harmony import */ var _ui_utils_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ui-utils.js */ "./front/assets/js/components/log-view/ui-utils.js");
/* harmony import */ var _event_handlers_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./event-handlers.js */ "./front/assets/js/components/log-view/event-handlers.js");
/* harmony import */ var _notification_utils_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./notification-utils.js */ "./front/assets/js/components/log-view/notification-utils.js");
/* harmony import */ var _help_scout_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../help-scout.js */ "./front/assets/js/components/help-scout.js");
/* harmony import */ var _alert_handler_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./alert-handler.js */ "./front/assets/js/components/log-view/alert-handler.js");
/* harmony import */ var _log_popover_renderer_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../log-popover-renderer.js */ "./front/assets/js/components/log-popover-renderer.js");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; var r = _regenerator(), e = r.m(_regeneratorRuntime), t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor; function n(r) { var e = "function" == typeof r && r.constructor; return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name)); } var o = { "throw": 1, "return": 2, "break": 3, "continue": 3 }; function a(r) { var e, t; return function (n) { e || (e = { stop: function stop() { return t(n.a, 2); }, "catch": function _catch() { return n.v; }, abrupt: function abrupt(r, e) { return t(n.a, o[r], e); }, delegateYield: function delegateYield(r, o, a) { return e.resultName = o, t(n.d, _regeneratorValues(r), a); }, finish: function finish(r) { return t(n.f, r); } }, t = function t(r, _t, o) { n.p = e.prev, n.n = e.next; try { return r(_t, o); } finally { e.next = n.n; } }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n; try { return r.call(this, e); } finally { n.p = e.prev, n.n = e.next; } }; } return (_regeneratorRuntime = function _regeneratorRuntime() { return { wrap: function wrap(e, t, n, o) { return r.w(a(e), t, n, o && o.reverse()); }, isGeneratorFunction: n, mark: r.m, awrap: function awrap(r, e) { return new _OverloadYield(r, e); }, AsyncIterator: _regeneratorAsyncIterator, async: function async(r, e, t, o, u) { return (n(e) ? _regeneratorAsyncGen : _regeneratorAsync)(a(r), e, t, o, u); }, keys: _regeneratorKeys, values: _regeneratorValues }; })(); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regeneratorKeys(e) { var n = Object(e), r = []; for (var t in n) r.unshift(t); return function e() { for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e; return e.done = !0, e; }; }
function _regeneratorAsync(n, e, r, t, o) { var a = _regeneratorAsyncGen(n, e, r, t, o); return a.next().then(function (n) { return n.done ? n.value : a.next(); }); }
function _regeneratorAsyncGen(r, e, t, o, n) { return new _regeneratorAsyncIterator(_regenerator().w(r, e, t, o), n || Promise); }
function _regeneratorAsyncIterator(t, e) { function n(r, o, i, f) { try { var c = t[r](o), u = c.value; return u instanceof _OverloadYield ? e.resolve(u.v).then(function (t) { n("next", t, i, f); }, function (t) { n("throw", t, i, f); }) : e.resolve(u).then(function (t) { c.value = t, i(c); }, function (t) { return n("throw", t, i, f); }); } catch (t) { f(t); } } var r; this.next || (_regeneratorDefine2(_regeneratorAsyncIterator.prototype), _regeneratorDefine2(_regeneratorAsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () { return this; })), _regeneratorDefine2(this, "_invoke", function (t, o, i) { function f() { return new e(function (e, r) { n(t, i, e, r); }); } return r = r ? r.then(f, f) : f(); }, !0); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function _OverloadYield(e, d) { this.v = e, this.k = d; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/* global jQuery, dbg_lv_backend_data, posthog */









(function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee($) {
    var dataTableConfig, table, apiService, uiUtils, eventHandlers, alertHandler, popoverContainer, result;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          (0,_help_scout_js__WEBPACK_IMPORTED_MODULE_6__.initHelpScoutBeacon)('055b8c26-4e0e-4352-a0f2-7e6f3e7c3336');
          !function (t, e) {
            var o, n, p, r;
            e.__SV || (window.posthog = e, e._i = [], e.init = function (i, s, a) {
              function g(t, e) {
                var o = e.split('.');
                o.length == 2 && (t = t[o[0]], e = o[1]), t[e] = function () {
                  t.push([e].concat(Array.prototype.slice.call(arguments, 0)));
                };
              }
              (p = t.createElement('script')).type = 'text/javascript', p.crossOrigin = 'anonymous', p.async = !0, p.src = s.api_host.replace('.i.posthog.com', '-assets.i.posthog.com') + '/static/array.js', (r = t.getElementsByTagName('script')[0]).parentNode.insertBefore(p, r);
              var u = e;
              for (void 0 !== a ? u = e[a] = [] : a = 'posthog', u.people = u.people || [], u.toString = function (t) {
                var e = 'posthog';
                return a !== 'posthog' && (e += '.' + a), t || (e += ' (stub)'), e;
              }, u.people.toString = function () {
                return u.toString(1) + '.people (stub)';
              }, o = 'init Ie Ts Ms Ee Es Rs capture Ge calculateEventProperties Os register register_once register_for_session unregister unregister_for_session js getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSurveysLoaded onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey canRenderSurveyAsync identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty Ds Fs createPersonProfile Ls Ps opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing Cs debug I As getPageViewId captureTraceFeedback captureTraceMetric'.split(' '), n = 0; n < o.length; n++) g(u, o[n]);
              e._i.push([i, s, a]);
            }, e.__SV = 1);
          }(document, window.posthog || []);
          posthog.init('phc_pYVBt9N6AieVgFXoQHj27wq3PwHLeujCZrNlXKSbYLv', {
            api_host: 'https://eu.i.posthog.com',
            defaults: '2025-05-24',
            person_profiles: 'always'
          });
          dataTableConfig = (0,_table_config_js__WEBPACK_IMPORTED_MODULE_1__.createDataTableConfig)();
          table = $('#dbg_lv_log-table').DataTable(dataTableConfig);
          $('.right-subheader .search-input').on('input', function () {
            var searchValue = $(this).val().trim();
            if (searchValue) {
              table.search(searchValue).draw();
            } else {
              table.search('').draw();
            }
          });
          apiService = new _api_service_js__WEBPACK_IMPORTED_MODULE_2__.LogViewerApiService(table);
          uiUtils = new _ui_utils_js__WEBPACK_IMPORTED_MODULE_3__.LogViewerUI(table);
          eventHandlers = new _event_handlers_js__WEBPACK_IMPORTED_MODULE_4__.LogViewerEventHandlers(table, apiService, uiUtils);
          alertHandler = new _alert_handler_js__WEBPACK_IMPORTED_MODULE_7__.AlertHandler();
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.initScrollToTopButton)();
          alertHandler.init();
          _context.next = 14;
          return apiService.fillInitialData();
        case 14:
          // Make LogPopoverRenderer available globally first
          window.LogPopoverRenderer = _log_popover_renderer_js__WEBPACK_IMPORTED_MODULE_8__.LogPopoverRenderer;

          // Initialize LogPopoverRenderer container
          popoverContainer = document.querySelector('.log-path-icons');
          if (popoverContainer) {
            window.LogPopoverRenderer.setContainer(popoverContainer);
            window.LogPopoverRenderer.refresh(dbg_lv_backend_data.log_popover_data);
          }
          $('#dbg_lv_log-table_processing').remove();

          // Only check if debug log is publicly accessible if the alert hasn't been dismissed
          if (alertHandler.isCurrentlyDismissed()) {
            _context.next = 23;
            break;
          }
          _context.next = 21;
          return apiService.isDebugLogPubliclyAccessible();
        case 21:
          result = _context.sent;
          if (result.success && result.data.is_public) {
            if (result.data.info) {
              dbg_lv_backend_data.log_popover_data.warnings.push(result.data.info);
              window.LogPopoverRenderer.refresh(dbg_lv_backend_data.log_popover_data);
            }
          }
        case 23:
          uiUtils.setupTimeFilter();
          eventHandlers.init();
          uiUtils.startRelativeTimeUpdates();
          _context.next = 28;
          return (0,_notification_utils_js__WEBPACK_IMPORTED_MODULE_5__.initEmailNotificationsForm)($('#dbg_lv_log_viewer_alerts_form'));
        case 28:
          if (dbg_lv_backend_data.log_updates_mode === 'AUTO') {
            uiUtils.startLiveUpdateLogs(function () {
              return apiService.updateLogs(function () {
                return uiUtils.animateRefreshButton();
              });
            });
          }
        case 29:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
})()(jQuery);

/***/ }),

/***/ "./front/assets/js/components/log-view/notification-schedule.js":
/*!**********************************************************************!*\
  !*** ./front/assets/js/components/log-view/notification-schedule.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   NotificationScheduleManager: () => (/* binding */ NotificationScheduleManager)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; var r = _regenerator(), e = r.m(_regeneratorRuntime), t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor; function n(r) { var e = "function" == typeof r && r.constructor; return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name)); } var o = { "throw": 1, "return": 2, "break": 3, "continue": 3 }; function a(r) { var e, t; return function (n) { e || (e = { stop: function stop() { return t(n.a, 2); }, "catch": function _catch() { return n.v; }, abrupt: function abrupt(r, e) { return t(n.a, o[r], e); }, delegateYield: function delegateYield(r, o, a) { return e.resultName = o, t(n.d, _regeneratorValues(r), a); }, finish: function finish(r) { return t(n.f, r); } }, t = function t(r, _t, o) { n.p = e.prev, n.n = e.next; try { return r(_t, o); } finally { e.next = n.n; } }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n; try { return r.call(this, e); } finally { n.p = e.prev, n.n = e.next; } }; } return (_regeneratorRuntime = function _regeneratorRuntime() { return { wrap: function wrap(e, t, n, o) { return r.w(a(e), t, n, o && o.reverse()); }, isGeneratorFunction: n, mark: r.m, awrap: function awrap(r, e) { return new _OverloadYield(r, e); }, AsyncIterator: _regeneratorAsyncIterator, async: function async(r, e, t, o, u) { return (n(e) ? _regeneratorAsyncGen : _regeneratorAsync)(a(r), e, t, o, u); }, keys: _regeneratorKeys, values: _regeneratorValues }; })(); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regeneratorKeys(e) { var n = Object(e), r = []; for (var t in n) r.unshift(t); return function e() { for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e; return e.done = !0, e; }; }
function _regeneratorAsync(n, e, r, t, o) { var a = _regeneratorAsyncGen(n, e, r, t, o); return a.next().then(function (n) { return n.done ? n.value : a.next(); }); }
function _regeneratorAsyncGen(r, e, t, o, n) { return new _regeneratorAsyncIterator(_regenerator().w(r, e, t, o), n || Promise); }
function _regeneratorAsyncIterator(t, e) { function n(r, o, i, f) { try { var c = t[r](o), u = c.value; return u instanceof _OverloadYield ? e.resolve(u.v).then(function (t) { n("next", t, i, f); }, function (t) { n("throw", t, i, f); }) : e.resolve(u).then(function (t) { c.value = t, i(c); }, function (t) { return n("throw", t, i, f); }); } catch (t) { f(t); } } var r; this.next || (_regeneratorDefine2(_regeneratorAsyncIterator.prototype), _regeneratorDefine2(_regeneratorAsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () { return this; })), _regeneratorDefine2(this, "_invoke", function (t, o, i) { function f() { return new e(function (e, r) { n(t, i, e, r); }); } return r = r ? r.then(f, f) : f(); }, !0); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function _OverloadYield(e, d) { this.v = e, this.k = d; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global jQuery, ajaxurl, dbg_lv_backend_data */

var NotificationScheduleManager = /*#__PURE__*/function () {
  function NotificationScheduleManager() {
    _classCallCheck(this, NotificationScheduleManager);
    this.nextEmailInfo = $('#next-email-info');
    this.nextEmailTime = $('#next-email-time');
    this.nextEmailCountdown = $('#next-email-countdown');
    this.updateInterval = null;
  }
  return _createClass(NotificationScheduleManager, [{
    key: "updateScheduleDisplay",
    value: function () {
      var _updateScheduleDisplay = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var response;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              _context.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_get_alert_schedule',
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              response = _context.sent;
              if (response.success) {
                _context.next = 6;
                break;
              }
              throw new Error(response.data || (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('request_error'));
            case 6:
              if (response.scheduled) {
                this.nextEmailTime.text(response.formatted_time);
                this.nextEmailCountdown.text((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('in') + ' ' + response.time_until);
                this.nextEmailInfo.show();

                // Start countdown timer
                this.startCountdownTimer(response.timestamp);
              } else {
                this.nextEmailInfo.hide();
                this.stopCountdownTimer();
              }
              _context.next = 13;
              break;
            case 9:
              _context.prev = 9;
              _context.t0 = _context["catch"](0);
              console.error('Failed to update schedule display:', _context.t0);
              this.nextEmailInfo.hide();
            case 13:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[0, 9]]);
      }));
      function updateScheduleDisplay() {
        return _updateScheduleDisplay.apply(this, arguments);
      }
      return updateScheduleDisplay;
    }()
  }, {
    key: "startCountdownTimer",
    value: function startCountdownTimer(targetTimestamp) {
      var _this = this;
      this.stopCountdownTimer();
      this.updateInterval = setInterval(function () {
        var now = Math.floor(Date.now() / 1000);
        var timeDiff = targetTimestamp - now;
        if (timeDiff <= 0) {
          _this.stopCountdownTimer();
          return;
        }
        var timeUntil = _this.formatTimeRemaining(timeDiff);
        _this.nextEmailCountdown.text((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('in') + ' ' + timeUntil);
      }, 60000); // Update every minute
    }
  }, {
    key: "stopCountdownTimer",
    value: function stopCountdownTimer() {
      if (this.updateInterval) {
        clearInterval(this.updateInterval);
        this.updateInterval = null;
      }
    }
  }, {
    key: "formatTimeRemaining",
    value: function formatTimeRemaining(seconds) {
      var days = Math.floor(seconds / 86400);
      var hours = Math.floor(seconds % 86400 / 3600);
      var minutes = Math.floor(seconds % 3600 / 60);
      if (days > 0) {
        if (hours > 0) {
          return "".concat(days, " ").concat(days === 1 ? (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('day') : (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('days'), " ").concat(hours, " ").concat(hours === 1 ? (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('hour') : (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('hours'));
        } else {
          return "".concat(days, " ").concat(days === 1 ? (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('day') : (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('days'));
        }
      } else if (hours > 0) {
        return "".concat(hours, " ").concat(hours === 1 ? (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('hour') : (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('hours'));
      } else {
        return "".concat(minutes, " ").concat(minutes === 1 ? (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('minute') : (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('minutes'));
      }
    }
  }, {
    key: "hide",
    value: function hide() {
      this.nextEmailInfo.hide();
      this.stopCountdownTimer();
    }
  }, {
    key: "show",
    value: function show() {
      this.updateScheduleDisplay();
    }
  }]);
}();

/***/ }),

/***/ "./front/assets/js/components/log-view/notification-utils.js":
/*!*******************************************************************!*\
  !*** ./front/assets/js/components/log-view/notification-utils.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initEmailNotificationsForm: () => (/* binding */ initEmailNotificationsForm),
/* harmony export */   updateEmailNotifications: () => (/* binding */ updateEmailNotifications)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* harmony import */ var _notification_schedule_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./notification-schedule.js */ "./front/assets/js/components/log-view/notification-schedule.js");
/* harmony import */ var _email_recipients_manager_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./email-recipients-manager.js */ "./front/assets/js/components/log-view/email-recipients-manager.js");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; var r = _regenerator(), e = r.m(_regeneratorRuntime), t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor; function n(r) { var e = "function" == typeof r && r.constructor; return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name)); } var o = { "throw": 1, "return": 2, "break": 3, "continue": 3 }; function a(r) { var e, t; return function (n) { e || (e = { stop: function stop() { return t(n.a, 2); }, "catch": function _catch() { return n.v; }, abrupt: function abrupt(r, e) { return t(n.a, o[r], e); }, delegateYield: function delegateYield(r, o, a) { return e.resultName = o, t(n.d, _regeneratorValues(r), a); }, finish: function finish(r) { return t(n.f, r); } }, t = function t(r, _t, o) { n.p = e.prev, n.n = e.next; try { return r(_t, o); } finally { e.next = n.n; } }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n; try { return r.call(this, e); } finally { n.p = e.prev, n.n = e.next; } }; } return (_regeneratorRuntime = function _regeneratorRuntime() { return { wrap: function wrap(e, t, n, o) { return r.w(a(e), t, n, o && o.reverse()); }, isGeneratorFunction: n, mark: r.m, awrap: function awrap(r, e) { return new _OverloadYield(r, e); }, AsyncIterator: _regeneratorAsyncIterator, async: function async(r, e, t, o, u) { return (n(e) ? _regeneratorAsyncGen : _regeneratorAsync)(a(r), e, t, o, u); }, keys: _regeneratorKeys, values: _regeneratorValues }; })(); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regeneratorKeys(e) { var n = Object(e), r = []; for (var t in n) r.unshift(t); return function e() { for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e; return e.done = !0, e; }; }
function _regeneratorAsync(n, e, r, t, o) { var a = _regeneratorAsyncGen(n, e, r, t, o); return a.next().then(function (n) { return n.done ? n.value : a.next(); }); }
function _regeneratorAsyncGen(r, e, t, o, n) { return new _regeneratorAsyncIterator(_regenerator().w(r, e, t, o), n || Promise); }
function _regeneratorAsyncIterator(t, e) { function n(r, o, i, f) { try { var c = t[r](o), u = c.value; return u instanceof _OverloadYield ? e.resolve(u.v).then(function (t) { n("next", t, i, f); }, function (t) { n("throw", t, i, f); }) : e.resolve(u).then(function (t) { c.value = t, i(c); }, function (t) { return n("throw", t, i, f); }); } catch (t) { f(t); } } var r; this.next || (_regeneratorDefine2(_regeneratorAsyncIterator.prototype), _regeneratorDefine2(_regeneratorAsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () { return this; })), _regeneratorDefine2(this, "_invoke", function (t, o, i) { function f() { return new e(function (e, r) { n(t, i, e, r); }); } return r = r ? r.then(f, f) : f(); }, !0); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function _OverloadYield(e, d) { this.v = e, this.k = d; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/* global jQuery, ajaxurl, dbg_lv_backend_data */



var scheduleManager = new _notification_schedule_js__WEBPACK_IMPORTED_MODULE_1__.NotificationScheduleManager();
var emailRecipientsManager = null;

/**
 * Updates email notification settings
 * @param {jQuery} form - The alerts form
 * @param {string} action - The AJAX action to perform
 * @returns {Promise<void>}
 */
function updateEmailNotifications(_x, _x2) {
  return _updateEmailNotifications.apply(this, arguments);
}

/**
 * Initializes the email alerts form
 * @param {jQuery} form - The form to initialize
 * @returns {Promise<void>}
 */
function _updateEmailNotifications() {
  _updateEmailNotifications = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(form, action) {
    var notificationStatus, recurrenceField, testEmailCheckbox, submitButton, errorLevelCheckboxes, validation, emails, selectedLevels, hasProDisabledTogglers, response, userEmailResponse, _response;
    return _regeneratorRuntime().wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          notificationStatus = form.attr('data-alerts-enabled');
          recurrenceField = form.find('select[name="recurrence"]');
          testEmailCheckbox = form.find('input[name="send_test_email"]');
          submitButton = form.find('input[type="submit"]');
          errorLevelCheckboxes = form.find('input[name="levels[]"]'); // Validate emails using the manager
          validation = emailRecipientsManager.validate();
          if (validation.isValid) {
            _context.next = 9;
            break;
          }
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(validation.errors.join(', '), 'warning');
          return _context.abrupt("return");
        case 9:
          emails = emailRecipientsManager.getEmails();
          if (!(emails.length === 0)) {
            _context.next = 13;
            break;
          }
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('email_is_not_specified'), 'warning');
          return _context.abrupt("return");
        case 13:
          // Get selected error levels - only from enabled checkboxes (non-pro features)
          selectedLevels = form.find('input[name="levels[]"]:checked:not(.pro-disabled)').map(function () {
            return jQuery(this).val();
          }).get(); // Check if togglers are disabled for free users
          hasProDisabledTogglers = form.find('.togglers-group.pro-disabled').length > 0; // For free users with pro-disabled togglers, skip level validation
          // They'll get default error levels from the backend
          if (!(notificationStatus === 'false' && !hasProDisabledTogglers && selectedLevels.length === 0)) {
            _context.next = 18;
            break;
          }
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('please_select_error_level'), 'warning');
          return _context.abrupt("return");
        case 18:
          submitButton.attr('disabled', 'disabled');
          _context.prev = 19;
          if (!(notificationStatus === 'true')) {
            _context.next = 41;
            break;
          }
          _context.next = 23;
          return jQuery.post(ajaxurl, {
            action: action,
            status: 'disable',
            emails: emails,
            levels: selectedLevels,
            wp_nonce: dbg_lv_backend_data.ajax_nonce
          });
        case 23:
          response = _context.sent;
          if (response.success) {
            _context.next = 26;
            break;
          }
          throw new Error(response.data || (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('request_error'));
        case 26:
          form.attr('data-alerts-enabled', 'false');
          submitButton.removeClass('disable btn-danger').addClass('enable btn-primary').val('Enable');
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('notifications_disabled'), 'success');

          // Hide schedule display
          scheduleManager.hide();
          _context.next = 32;
          return jQuery.post(ajaxurl, {
            action: 'dbg_lv_get_current_user_email',
            wp_nonce: dbg_lv_backend_data.ajax_nonce
          });
        case 32:
          userEmailResponse = _context.sent;
          if (userEmailResponse.success) {
            _context.next = 35;
            break;
          }
          throw new Error(userEmailResponse.data || (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('request_error'));
        case 35:
          emailRecipientsManager.setEnabled(true);
          recurrenceField.prop('disabled', false);
          testEmailCheckbox.prop('disabled', false).closest('.form-check').show();

          // Only enable error level checkboxes if they're not pro-disabled
          errorLevelCheckboxes.each(function () {
            var checkbox = jQuery(this);
            var isProDisabled = checkbox.closest('.togglers-group').hasClass('pro-disabled');
            if (!isProDisabled) {
              checkbox.prop('disabled', false);
            }
          });
          _context.next = 54;
          break;
        case 41:
          _context.next = 43;
          return jQuery.post(ajaxurl, {
            action: action,
            status: 'enable',
            recurrence: recurrenceField === null || recurrenceField === void 0 ? void 0 : recurrenceField.val(),
            emails: emails,
            levels: selectedLevels,
            send_test_email: +(testEmailCheckbox === null || testEmailCheckbox === void 0 ? void 0 : testEmailCheckbox.is(':checked')),
            wp_nonce: dbg_lv_backend_data.ajax_nonce
          });
        case 43:
          _response = _context.sent;
          if (_response.success) {
            _context.next = 46;
            break;
          }
          throw new Error(_response.data || (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('request_error'));
        case 46:
          emailRecipientsManager.setEnabled(false);
          recurrenceField.prop('disabled', true);
          testEmailCheckbox.prop('disabled', true).closest('.form-check').hide();

          // Always disable error level checkboxes when notifications are enabled
          errorLevelCheckboxes.prop('disabled', true);
          submitButton.removeClass('enable btn-primary').addClass('disable btn-danger').val((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('disable'));
          form.attr('data-alerts-enabled', 'true');
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('notifications_enabled'), 'success');

          // Show and update schedule display
          scheduleManager.show();
        case 54:
          _context.next = 59;
          break;
        case 56:
          _context.prev = 56;
          _context.t0 = _context["catch"](19);
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context.t0, 'error');
        case 59:
          submitButton.prop('disabled', false);
        case 60:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[19, 56]]);
  }));
  return _updateEmailNotifications.apply(this, arguments);
}
function initEmailNotificationsForm(_x3) {
  return _initEmailNotificationsForm.apply(this, arguments);
}
function _initEmailNotificationsForm() {
  _initEmailNotificationsForm = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(form) {
    var recurrenceField, testEmailCheckbox, submitButton, errorLevelCheckboxes, existingEmails, response;
    return _regeneratorRuntime().wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          if (!(!form || !form.length)) {
            _context2.next = 3;
            break;
          }
          console.warn((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('email_alerts_form_not_found'));
          return _context2.abrupt("return");
        case 3:
          emailRecipientsManager = new _email_recipients_manager_js__WEBPACK_IMPORTED_MODULE_2__.EmailRecipientsManager();
          recurrenceField = form.find('select[name="recurrence"]');
          testEmailCheckbox = form.find('input[name="send_test_email"]');
          submitButton = form.find('input[type="submit"]');
          errorLevelCheckboxes = form.find('input[name="levels[]"]'); // Elements are already disabled in HTML, just add loading visual state
          form.addClass('loading');
          _context2.prev = 9;
          if (!(form.attr('data-alerts-enabled') === 'true')) {
            _context2.next = 20;
            break;
          }
          // Remove loading state but keep form disabled since alerts are enabled
          form.removeClass('loading');
          emailRecipientsManager.setEnabled(false);
          recurrenceField.prop('disabled', true);
          testEmailCheckbox.prop('disabled', true).closest('.form-check').hide();
          errorLevelCheckboxes.prop('disabled', true);
          submitButton.prop('disabled', false).removeClass('enabled btn-primary').addClass('disable btn-danger').val((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('disable'));

          // Show schedule display for enabled alerts
          scheduleManager.show();
          _context2.next = 35;
          break;
        case 20:
          // For disabled alerts, check if we have existing emails from the template
          existingEmails = emailRecipientsManager.getEmails(); // If no existing emails, fetch the current user's email as default
          if (!(existingEmails.length === 0)) {
            _context2.next = 28;
            break;
          }
          _context2.next = 24;
          return jQuery.post(ajaxurl, {
            action: 'dbg_lv_get_current_user_email',
            wp_nonce: dbg_lv_backend_data.ajax_nonce
          });
        case 24:
          response = _context2.sent;
          if (response.success) {
            _context2.next = 27;
            break;
          }
          throw new Error(response.data || (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('request_error'));
        case 27:
          // Set the current user's email as the default
          emailRecipientsManager.setEmails([response.data]);
        case 28:
          // Enable all form elements since alerts are disabled
          form.removeClass('loading');
          emailRecipientsManager.setEnabled(true);
          recurrenceField.prop('disabled', false);
          testEmailCheckbox.prop('disabled', false).closest('.form-check').show();

          // Only enable error level checkboxes if they're not pro-disabled
          errorLevelCheckboxes.each(function () {
            var checkbox = jQuery(this);
            var isProDisabled = checkbox.closest('.togglers-group').hasClass('pro-disabled');
            if (!isProDisabled) {
              checkbox.prop('disabled', false);
            }
          });
          submitButton.prop('disabled', false).removeClass('disable btn-danger').addClass('enable btn-primary').val((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('enable'));

          // Hide schedule display for disabled alerts
          scheduleManager.hide();
        case 35:
          _context2.next = 42;
          break;
        case 37:
          _context2.prev = 37;
          _context2.t0 = _context2["catch"](9);
          // Remove loading state and enable basic form functionality on error
          form.removeClass('loading');
          submitButton.prop('disabled', false).val((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('enable'));
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context2.t0, 'error');
        case 42:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[9, 37]]);
  }));
  return _initEmailNotificationsForm.apply(this, arguments);
}

/***/ }),

/***/ "./front/assets/js/components/log-view/renderers.js":
/*!**********************************************************!*\
  !*** ./front/assets/js/components/log-view/renderers.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   convertToTimestamp: () => (/* binding */ convertToTimestamp),
/* harmony export */   renderDatetime: () => (/* binding */ renderDatetime),
/* harmony export */   renderDescription: () => (/* binding */ renderDescription),
/* harmony export */   renderLogTypeBadge: () => (/* binding */ renderLogTypeBadge)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* harmony import */ var _constants_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants.js */ "./front/assets/js/components/log-view/constants.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");



/**
 * Renders a badge for log type with appropriate styling
 */
function renderLogTypeBadge(data) {
  var typeClasses = {
    Notice: 'notice-bg',
    Warning: 'warning-bg',
    Fatal: 'fatal-bg',
    Database: 'database-bg',
    Parse: 'parse-bg',
    Deprecated: 'deprecated-bg',
    Custom: 'custom-bg'
  };
  var className = typeClasses[data] || 'bg-secondary';
  return "<span class=\"badge ".concat(className, "\">").concat(data, "</span>");
}

/**
 * Renders description with collapsible stack trace if available
 */
function renderDescription(description) {
  var text = description.text,
    stackTrace = description.stack_trace;
  if (!stackTrace) return text;

  // Generate unique ID with only alphanumeric characters
  var collapseId = 'stack-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
  return "\n    <div class=\"error-description\">".concat(text, "</div>\n    <a class=\"stack-trace\" data-target=\"#").concat(collapseId, "\">\n      <i class=\"fa-solid fa-code\"></i>\n      ").concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('stack_trace'), "\n    </a>\n    <div id=\"").concat(collapseId, "\" class=\"dlv-stack-trace\" style=\"display: none;\">\n      <div class=\"copy-stack-btn\" title=\"Copy to clipboard\">\n        <svg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\">\n          <path d=\"M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2\"></path>\n          <rect x=\"8\" y=\"2\" width=\"8\" height=\"4\" rx=\"1\" ry=\"1\"></rect>\n        </svg>\n      </div>\n      <pre>").concat(stackTrace, "</pre>\n    </div>\n  ");
}
function convertToTimestamp(datetimeString) {
  return new Date(datetimeString).getTime();
}
function renderDatetime(data, type, row) {
  if (type === 'display') {
    var _window$dbg_lv_backen;
    var datetimeFormat = ((_window$dbg_lv_backen = window.dbg_lv_backend_data) === null || _window$dbg_lv_backen === void 0 ? void 0 : _window$dbg_lv_backen.datetime_format) || _constants_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_FORMAT_ABSOLUTE;
    if (datetimeFormat === _constants_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_FORMAT_RELATIVE) {
      return formatRelativeTime(data);
    }
    return data;
  }
  return convertToTimestamp(data);
}
function formatRelativeTime(datetimeString) {
  var _window$dbg_lv_backen2;
  var logDate = new Date(datetimeString);
  var now = new Date();

  // Handle invalid dates
  if (isNaN(logDate.getTime())) {
    return datetimeString; // Return original string if parsing fails
  }
  var diffMs = now - logDate;

  // If the log entry is in the future, return original datetime
  if (diffMs < 0) {
    return datetimeString;
  }
  var diffSeconds = Math.floor(diffMs / 1000);

  // Use the translation function for consistency
  var phrases = ((_window$dbg_lv_backen2 = window.dbg_lv_backend_data) === null || _window$dbg_lv_backen2 === void 0 ? void 0 : _window$dbg_lv_backen2.phrases) || {};

  // Less than 30 seconds = "just now"
  if (diffSeconds < 30) {
    return phrases.just_now || 'just now';
  }

  // 30 seconds to 1 minute = "1 minute ago"
  if (diffSeconds < 60) {
    return phrases.one_minute_ago || '1 minute ago';
  }

  // 1 to 2 minutes = "2 minutes ago"
  if (diffSeconds < 120) {
    return phrases.two_minutes_ago || '2 minutes ago';
  }

  // 2 to 5 minutes = "5 minutes ago"
  if (diffSeconds < 300) {
    return phrases.five_minutes_ago || '5 minutes ago';
  }

  // 5 to 10 minutes = "10 minutes ago"
  if (diffSeconds < 600) {
    return phrases.ten_minutes_ago || '10 minutes ago';
  }

  // 10 to 15 minutes = "15 minutes ago"
  if (diffSeconds < 900) {
    return phrases.fifteen_minutes_ago || '15 minutes ago';
  }

  // 15 to 30 minutes = "30 minutes ago"
  if (diffSeconds < 1800) {
    return phrases.thirty_minutes_ago || '30 minutes ago';
  }

  // 30 minutes to 1 hour = "1 hour ago"
  if (diffSeconds < 3600) {
    return phrases.one_hour_ago || '1 hour ago';
  }

  // 1 to 2 hours = "2 hours ago"
  if (diffSeconds < 7200) {
    return phrases.two_hours_ago || '2 hours ago';
  }

  // 2 to 3 hours = "3 hours ago"
  if (diffSeconds < 10800) {
    return phrases.three_hours_ago || '3 hours ago';
  }

  // 3 to 6 hours = "6 hours ago"
  if (diffSeconds < 21600) {
    return phrases.six_hours_ago || '6 hours ago';
  }

  // 6 to 12 hours = "12 hours ago"
  if (diffSeconds < 43200) {
    return phrases.twelve_hours_ago || '12 hours ago';
  }

  // 12 to 24 hours = "1 day ago"
  if (diffSeconds < 86400) {
    return phrases.one_day_ago || '1 day ago';
  }

  // 1 to 2 days = "2 days ago"
  if (diffSeconds < 172800) {
    return phrases.two_days_ago || '2 days ago';
  }

  // 2 to 3 days = "3 days ago"
  if (diffSeconds < 259200) {
    return phrases.three_days_ago || '3 days ago';
  }

  // 3 to 7 days = "1 week ago"
  if (diffSeconds < 604800) {
    return phrases.one_week_ago || '1 week ago';
  }

  // 1 to 2 weeks = "2 weeks ago"
  if (diffSeconds < 1209600) {
    return phrases.two_weeks_ago || '2 weeks ago';
  }

  // 2 weeks to 1 month = "1 month ago"
  if (diffSeconds < 2592000) {
    return phrases.one_month_ago || '1 month ago';
  }

  // 1 to 2 months = "2 months ago"
  if (diffSeconds < 5184000) {
    return phrases.two_months_ago || '2 months ago';
  }

  // 2 to 3 months = "3 months ago"
  if (diffSeconds < 7776000) {
    return phrases.three_months_ago || '3 months ago';
  }

  // 3 to 6 months = "6 months ago"
  if (diffSeconds < 15552000) {
    return phrases.six_months_ago || '6 months ago';
  }

  // 6 months to 1 year = "1 year ago"
  if (diffSeconds < 31536000) {
    return phrases.one_year_ago || '1 year ago';
  }

  // More than 1 year - calculate years
  var years = Math.floor(diffSeconds / 31536000);
  if (years === 1) {
    return phrases.one_year_ago || '1 year ago';
  } else {
    return (phrases.years_ago || '%d years ago').replace('%d', years);
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
    e.preventDefault();
    var $target = $($(this).data('target'));
    if ($target.is(':visible')) {
      $target.slideUp(300);
      $(this).html("\n        <i class=\"fa-solid fa-code\"></i>\n        ".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('stack_trace')));
    } else {
      $target.slideDown(300);
      $(this).html((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('hide_stack_trace'));
    }
  });

  // Generic copy to clipboard function
  function copyToClipboard($button, text) {
    navigator.clipboard.writeText(text).then(function () {
      var originalSvg = $button.html();

      // Change to checkmark for feedback
      $button.html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5"></path></svg>');

      // Reset after a short delay
      setTimeout(function () {
        $button.html(originalSvg);
      }, 1500);
    });
  }

  // Handle copy to clipboard for stack traces
  $(document).on('click', '.copy-stack-btn', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var stackTrace = $(this).siblings('pre').text();
    copyToClipboard($(this), stackTrace);
  });

  // Handle copy path to clipboard
  $(document).on('click', '.copy-path-btn', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var path = $(this).siblings('.path-segment').text();
    copyToClipboard($(this), path);
  });

  // console.log('DataTable initialized')
  // $('.dtsb-clearAll').removeClass('btn-secondary').addClass('btn-sm btn-danger')
  // $('.dtsb-add').removeClass('btn-secondary').addClass('btn-sm btn-success')
  // $('.dtsb-delete').removeClass('btn-secondary').addClass('btn-sm btn-outline-danger')
});

/***/ }),

/***/ "./front/assets/js/components/log-view/table-config.js":
/*!*************************************************************!*\
  !*** ./front/assets/js/components/log-view/table-config.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createDataTableConfig: () => (/* binding */ createDataTableConfig)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* harmony import */ var _renderers_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./renderers.js */ "./front/assets/js/components/log-view/renderers.js");
/* harmony import */ var _ui_utils_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ui-utils.js */ "./front/assets/js/components/log-view/ui-utils.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
/* global $, MutationObserver */




/**
 * Creates and returns the DataTable configuration object
 */
function createDataTableConfig(initCallbacks) {
  return {
    serverSide: false,
    stateSave: true,
    ordering: true,
    retrieve: true,
    bSort: true,
    order: [[0, 'desc']],
    // Sort by timestamp desc by default
    processing: true,
    language: {
      processing: "<div class=\"loader-overlay\"></div><i class=\"fa fa-spinner fa-spin fa-3x fa-fw\"></i><span class=\"mt-3\">".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('loading_in_process'), "</span>"),
      search: '_INPUT_',
      lengthMenu: '_MENU_',
      emptyTable: (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('empty_log_table'),
      zeroRecords: (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('no_matching_records')
    },
    pageLength: 10,
    layout: {
      topStart: null,
      // remove pageLength from top default position
      topEnd: null,
      // remove search from top default position
      top8Start: ['pageLength', {
        buttons: [{
          extend: 'colvis',
          className: 'column-visibility-btn',
          text: (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('show-hide')
        }, {
          extend: 'csv',
          className: 'csv-export-btn d-none',
          // Hidden, we'll trigger it from the dropdown
          filename: 'debug-log-export',
          exportOptions: {
            format: {
              body: function body(data) {
                // Clean up HTML tags from the data
                return $('<div>').html(data).text();
              }
            }
          }
        }]
      }, $("\n          <div class=\"time-filter\">\n            <div class=\"datetime-range-buttons btn-group\">\n              <button class=\"btn btn-sm btn-outline-primary\" value=\"5m\">5m</button>\n              <button class=\"btn btn-sm btn-outline-primary\" value=\"30m\">30m</button>\n              <button class=\"btn btn-sm btn-outline-primary\" value=\"1h\">1h</button>\n              <button class=\"btn btn-sm btn-outline-primary\" value=\"12h\">12h</button>\n              <button class=\"btn btn-sm btn-outline-primary active\" value=\"all\">All</button>\n              ".concat((0,_ui_utils_js__WEBPACK_IMPORTED_MODULE_2__.isPremium)() ? '<button class="btn btn-sm btn-outline-primary custom-range-btn" value="custom">Custom</button>' : "<div class=\"position-relative d-inline-block\">\n                    <button class=\"btn btn-sm btn-outline-primary custom-range-btn pro-disabled\" value=\"custom\" disabled>Custom</button>\n                    <span class=\"pro-badge freemius-checkout-trigger\" title=\"Upgrade to Pro\">Pro</span>\n                </div>", "\n            </div>\n          </div>")), $("<div class=\"d-flex ms-4 advanced-filter\">\n            <button class=\"btn btn-sm btn-outline-primary search-builder-btn\" style=\"height: 30px; border:none\">\n              <i class=\"fa fa-filter\"></i>\n              <span class=\"filter-text\">".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('filter'), "</span>\n              <span class=\"filter-counter\"></span>\n            </button>\n          </div>"))],
      top8End: [{
        search: {}
      }, $("<div class=\"dropdown more-dropdown\">\n          <button class=\"btn btn-sm btn-outline-primary dropdown-toggle\" type=\"button\" id=\"dropdownMenuButton\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">\n          <span>".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('more'), "</span>\n          </button>\n          <ul class=\"dropdown-menu more-menu\" aria-labelledby=\"dropdownMenuButton\">\n            ").concat((0,_ui_utils_js__WEBPACK_IMPORTED_MODULE_2__.createDropdownMenuItems)(), "\n          </ul>\n        </div>")), $("<button class=\"btn btn-primary refresh-log\" title=\"".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('refresh_log'), "\"><i class=\"fa fa-solid fa-arrows-rotate\"></i></button>"))],
      top7: 'searchBuilder'
    },
    columns: [{
      data: 'timestamp',
      visible: false,
      render: function render(data, type, row) {
        // If timestamp is missing, calculate it from datetime
        return data || (0,_renderers_js__WEBPACK_IMPORTED_MODULE_1__.convertToTimestamp)(row.datetime);
      }
    }, {
      data: 'type',
      render: _renderers_js__WEBPACK_IMPORTED_MODULE_1__.renderLogTypeBadge
    }, {
      data: 'datetime',
      className: 'datetime',
      width: '10%',
      render: _renderers_js__WEBPACK_IMPORTED_MODULE_1__.renderDatetime
    }, {
      data: 'description',
      className: 'description ph-no-capture',
      render: _renderers_js__WEBPACK_IMPORTED_MODULE_1__.renderDescription,
      width: '55%'
    }, {
      data: 'file',
      className: 'file ph-no-capture',
      width: '25%'
    }, {
      data: 'line',
      className: 'line'
    }],
    autoWidth: false,
    initComplete: function initComplete() {
      if (initCallbacks && typeof initCallbacks.onInitComplete === 'function') {
        initCallbacks.onInitComplete();
      }
      var table = this.api();
      $(document).on('click', '.export-csv-btn', function (e) {
        e.preventDefault();
        table.button('.csv-export-btn').trigger();
      });
      var updateFilterCountHandler = function updateFilterCountHandler() {
        return (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.updateFilterCount)(table);
      };
      table.on('searchBuilder-rebuild', updateFilterCountHandler);
      table.on('searchBuilder-redraw', updateFilterCountHandler);
      table.on('draw', updateFilterCountHandler);
      var searchBuilderObserver = new MutationObserver(function () {
        return (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.updateFilterCount)(table);
      });
      var searchBuilderContainer = document.querySelector('.dtsb-searchBuilder');
      if (searchBuilderContainer) {
        searchBuilderObserver.observe(searchBuilderContainer, {
          childList: true,
          subtree: true
        });
      }
      setTimeout(function () {
        return (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.updateFilterCount)(table);
      }, 500);
    }
  };
}

/***/ }),

/***/ "./front/assets/js/components/log-view/ui-utils.js":
/*!*********************************************************!*\
  !*** ./front/assets/js/components/log-view/ui-utils.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   LogViewerUI: () => (/* binding */ LogViewerUI),
/* harmony export */   createDropdownMenuItems: () => (/* binding */ createDropdownMenuItems),
/* harmony export */   getFreemiusCheckoutHandler: () => (/* binding */ getFreemiusCheckoutHandler),
/* harmony export */   isPremium: () => (/* binding */ isPremium),
/* harmony export */   openFreemiusCheckout: () => (/* binding */ openFreemiusCheckout),
/* harmony export */   openFreemiusPricing: () => (/* binding */ openFreemiusPricing),
/* harmony export */   showPricingModal: () => (/* binding */ showPricingModal)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
/* harmony import */ var _constants_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants.js */ "./front/assets/js/components/log-view/constants.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* global $, dbg_lv_backend_data, FS */


var isPremium = function isPremium() {
  var _window$dbg_lv_backen, _window$dbg_lv_freemi;
  return ((_window$dbg_lv_backen = window.dbg_lv_backend_data) === null || _window$dbg_lv_backen === void 0 ? void 0 : _window$dbg_lv_backen.is_premium) || ((_window$dbg_lv_freemi = window.dbg_lv_freemius_data) === null || _window$dbg_lv_freemi === void 0 ? void 0 : _window$dbg_lv_freemi.is_premium) || false;
};

/**
 * Initialize and return Freemius checkout handler
 */
var getFreemiusCheckoutHandler = function getFreemiusCheckoutHandler() {
  if (typeof FS === 'undefined' || !FS.Checkout) {
    console.warn('Freemius checkout not available');
    return null;
  }
  var freemiusData = window.dbg_lv_freemius_data || {};
  return new FS.Checkout({
    product_id: freemiusData.product_id || '17350',
    plan_id: freemiusData.plan_id || '29848',
    public_key: freemiusData.public_key || 'pk_d456c712f16510d920c9f4ba4880a',
    image: freemiusData.image || ''
  });
};

/**
 * Open Freemius checkout with enhanced options
 */
var openFreemiusCheckout = function openFreemiusCheckout() {
  var handler = getFreemiusCheckoutHandler();
  if (!handler) {
    var _window$dbg_lv_backen2, _window$dbg_lv_freemi2;
    // Fallback to direct URL if Freemius SDK is not available
    var checkoutUrl = ((_window$dbg_lv_backen2 = window.dbg_lv_backend_data) === null || _window$dbg_lv_backen2 === void 0 ? void 0 : _window$dbg_lv_backen2.checkout_url) || ((_window$dbg_lv_freemi2 = window.dbg_lv_freemius_data) === null || _window$dbg_lv_freemi2 === void 0 ? void 0 : _window$dbg_lv_freemi2.checkout_url) || '';
    if (checkoutUrl) {
      window.open(checkoutUrl, '_blank');
    }
    return;
  }
  handler.open({
    name: 'Debug Log Viewer Pro',
    licenses: 1,
    billing_cycle: 'annual',
    billing_cycle_selector: 'dropdown',
    show_reviews: true,
    show_refund_badge: true,
    currency: 'auto',
    language: 'auto'
  });
};

/**
 * Show the pricing comparison modal
 */
var showPricingModal = function showPricingModal() {
  var $modal = $('#dbg-lv-pricing-modal');
  if ($modal.length) {
    $modal.modal('show');
  } else {
    // Fallback: open checkout directly if modal not found
    openFreemiusCheckout();
  }
};

/**
 * Open Freemius pricing - shows custom modal first, then checkout
 */
var openFreemiusPricing = function openFreemiusPricing() {
  showPricingModal();
};

/**
 * Creates the dropdown menu items based on available features
 */
function createDropdownMenuItems() {
  var menuItems = "\n    <li><a class=\"dropdown-item download-btn download-log\" href=\"#\">\n      <i class=\"fa fa-download\"></i>".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('download_log'), "</a></li>\n    <li><a class=\"dropdown-item clear-log-btn clear-log\" href=\"#\">\n    <i class=\"fa fa-trash\"></i>").concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('clear_log'), "</a></li>");
  if (isPremium()) {
    menuItems += "\n      <li><a class=\"dropdown-item export-csv-btn\" href=\"#\">\n        <i class=\"fa fa-file-csv\"></i>".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('export_csv'), "\n      </a></li>");
  } else {
    /* <fs_free_only> */

    menuItems += "<li>\n      <a class=\"dropdown-item pro-feature freemius-checkout-trigger\" href=\"#\" title=\"".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('upgrade_to_pro'), "\">\n        <i class=\"fa fa-file-csv\"></i>").concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('export_csv'), " \n        <span class=\"pro-badge\">Pro</span>\n      </a>\n    </li>");
    /* </fs_free_only> */
  }
  return menuItems;
}

// /**
//  * UI utilities for log viewer
//  */
var LogViewerUI = /*#__PURE__*/function () {
  function LogViewerUI(table) {
    _classCallCheck(this, LogViewerUI);
    this.table = table;
    this.logsUpdatesIcon = $('.refresh-log i');
    this.logsUpdateInterval = null;
  }

  /**
     * Animate refresh button
     */
  return _createClass(LogViewerUI, [{
    key: "animateRefreshButton",
    value: function animateRefreshButton() {
      var _this = this;
      var icon = this.logsUpdatesIcon[0];

      // Remove existing class and force reflow to restart animation
      this.logsUpdatesIcon.removeClass('rotate-animation');
      this.logsUpdatesIcon.addClass('rotate-animation');

      // Clean up after animation completes
      icon === null || icon === void 0 || icon.addEventListener('animationend', function () {
        _this.logsUpdatesIcon.removeClass('rotate-animation');
      }, {
        once: true
      });
    }

    /**
       * Start live update logs
       */
  }, {
    key: "startLiveUpdateLogs",
    value: function startLiveUpdateLogs(updateLogsCallback) {
      var timeout = dbg_lv_backend_data.log_updates_interval * 1000;
      this.logsUpdateInterval = setInterval(updateLogsCallback, timeout);
    }

    /**
       * Stop live update logs
       */
  }, {
    key: "stopLiveUpdateLogs",
    value: function stopLiveUpdateLogs() {
      if (this.logsUpdateInterval) {
        clearInterval(this.logsUpdateInterval);
      }
    }

    /**
     * Setup time filter for DataTable
     */
  }, {
    key: "setupTimeFilter",
    value: function setupTimeFilter() {
      var table = this.table;
      $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var filter = $('.datetime-range-buttons .btn.active').val(); // Get the active filter

        if (filter === 'all') {
          return true; // Show all rows
        }
        var now = new Date();
        var timestamp = new Date(Number(data[0])); // Assuming the first column is the timestamp
        if (isNaN(timestamp)) {
          // If timestamp parsing fails, exclude the row
          return false;
        }
        var timeDiff = now - timestamp; // Time difference in milliseconds

        if (filter === 'custom') {
          var fromDate = $('#custom-date-from').val();
          var toDate = $('#custom-date-to').val();
          if (!fromDate || !toDate) {
            return true; // Show all if dates not set
          }
          var from = new Date(fromDate).getTime();
          var to = new Date(toDate).getTime();
          var timestampMs = timestamp.getTime();
          return timestampMs >= from && timestampMs <= to;
        }
        return filter === '5m' && timeDiff <= 5 * 60 * 1000 || filter === '30m' && timeDiff <= 30 * 60 * 1000 || filter === '1h' && timeDiff <= 60 * 60 * 1000 || filter === '12h' && timeDiff <= 12 * 60 * 60 * 1000;
      });

      // Filter Button Click Event
      $('html').on('click', '.datetime-range-buttons .btn', function () {
        var _window$dbg_lv_backen3;
        // Check if this is a pro feature and user doesn't have premium
        if ($(this).hasClass('pro-disabled') && !((_window$dbg_lv_backen3 = window.dbg_lv_backend_data) !== null && _window$dbg_lv_backen3 !== void 0 && _window$dbg_lv_backen3.is_premium)) {
          // Show upgrade message or redirect to upgrade
          openFreemiusPricing();
          return false;
        }
        var filter = $(this).val();
        $('.datetime-range-buttons .btn').removeClass('active');
        $(this).addClass('active');
        if (filter === 'custom') {
          // Show custom date range inputs without relying on Bootstrap popover
          var $customContainer = $('.custom-date-range-dropdown');
          if ($customContainer.length === 0) {
            // Create the custom date range dropdown if it doesn't exist
            $customContainer = $("\n            <div class=\"custom-date-range-dropdown\">\n              <div class=\"date-range-content\">\n                <div class=\"date-input-group\">\n                  <label for=\"custom-date-from\" class=\"form-label\">From:</label>\n                  <input type=\"datetime-local\" id=\"custom-date-from\" class=\"form-control form-control-sm\" />\n                </div>\n                <div class=\"date-input-group\">\n                  <label for=\"custom-date-to\" class=\"form-label\">To:</label>\n                  <input type=\"datetime-local\" id=\"custom-date-to\" class=\"form-control form-control-sm\" />\n                </div>\n                <div class=\"date-actions\">\n                  <button type=\"button\" class=\"btn btn-sm btn-primary apply-custom-range\">Apply</button>\n                  <button type=\"button\" class=\"btn btn-sm btn-secondary cancel-custom-range\">Cancel</button>\n                </div>\n              </div>\n            </div>\n          ");

            // Insert after the time filter buttons
            $('.time-filter').append($customContainer);
          }

          // Show the custom container
          $customContainer.show();

          // Always update max datetime to current time (timezone-aware)
          var now = new Date();
          var timezoneOffset = now.getTimezoneOffset() * 60000; // Convert to milliseconds
          var localNow = new Date(now.getTime() - timezoneOffset);
          var maxDateTime = localNow.toISOString().slice(0, 16);

          // Set default values if not set and apply timezone-aware max datetime
          var $fromInput = $('#custom-date-from');
          var $toInput = $('#custom-date-to');

          // Always update max attribute to prevent future date selection
          $fromInput.attr('max', maxDateTime);
          $toInput.attr('max', maxDateTime);
          if (!$fromInput.val() || !$toInput.val()) {
            var oneHourAgo = new Date(localNow.getTime() - 60 * 60 * 1000);
            $fromInput.val(oneHourAgo.toISOString().slice(0, 16));
            $toInput.val(maxDateTime);
          }
        } else {
          // Hide custom container if visible
          $('.custom-date-range-dropdown').hide();
          table.draw();
        }
      });

      // Custom date range apply button
      $('html').on('click', '.apply-custom-range', function () {
        var fromDate = $('#custom-date-from').val();
        var toDate = $('#custom-date-to').val();
        if (!fromDate || !toDate) {
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('select_both_dates') || 'Please select both from and to dates.', 'error');
          return;
        }
        var fromDateTime = new Date(fromDate);
        var toDateTime = new Date(toDate);
        var now = new Date();
        if (fromDateTime > toDateTime) {
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('invalid_date_range') || 'From date cannot be later than to date.', 'error');
          return;
        }

        // Check if any selected date is in the future
        if (fromDateTime > now || toDateTime > now) {
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('future_date_not_allowed') || 'Future dates are not allowed. Please select dates up to the current time.', 'error');
          return;
        }

        // Hide the custom dropdown
        $('.custom-date-range-dropdown').hide();

        // Keep the custom button active
        $('.datetime-range-buttons .btn').removeClass('active');
        $('.datetime-range-buttons .btn[value="custom"]').addClass('active');
        table.draw();
      });

      // Custom date range cancel button
      $('html').on('click', '.cancel-custom-range', function () {
        // Hide the custom dropdown
        $('.custom-date-range-dropdown').hide();

        // Reset to "All" filter
        $('.datetime-range-buttons .btn').removeClass('active');
        $('.datetime-range-buttons .btn[value="all"]').addClass('active');
        table.draw();
      });

      // Hide custom dropdown when clicking outside
      $(document).on('click', function (e) {
        var $customContainer = $('.custom-date-range-dropdown');
        var $customBtn = $('.custom-range-btn');
        if ($customContainer.is(':visible') && !$customBtn.is(e.target) && !$customContainer.is(e.target) && $customContainer.has(e.target).length === 0) {
          $customContainer.hide();
          // Reset to "All" filter
          $('.datetime-range-buttons .btn').removeClass('active');
          $('.datetime-range-buttons .btn[value="all"]').addClass('active');
          table.draw();
        }
      });
    }
  }, {
    key: "startRelativeTimeUpdates",
    value: function startRelativeTimeUpdates() {
      var _window$dbg_lv_backen4,
        _this2 = this;
      this.stopRelativeTimeUpdates();
      if (((_window$dbg_lv_backen4 = window.dbg_lv_backend_data) === null || _window$dbg_lv_backen4 === void 0 ? void 0 : _window$dbg_lv_backen4.datetime_format) === _constants_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_FORMAT_RELATIVE) {
        this.relativeTimeInterval = setInterval(function () {
          var _window$dbg_lv_backen5;
          if (((_window$dbg_lv_backen5 = window.dbg_lv_backend_data) === null || _window$dbg_lv_backen5 === void 0 ? void 0 : _window$dbg_lv_backen5.datetime_format) === _constants_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_FORMAT_RELATIVE) {
            _this2.table.cells(null, 2).invalidate().draw(false);
          }
        }, 60000);
      }
    }
  }, {
    key: "stopRelativeTimeUpdates",
    value: function stopRelativeTimeUpdates() {
      if (this.relativeTimeInterval) {
        clearInterval(this.relativeTimeInterval);
        this.relativeTimeInterval = null;
      }
    }
  }]);
}();
$(document).ready(function () {
  // Handle clicks on upgrade/checkout trigger elements - show pricing modal
  $(document).on('click', '.freemius-checkout-trigger', function (e) {
    e.preventDefault();
    e.stopPropagation();
    openFreemiusPricing();
  });

  // Handle upgrade button click in pricing modal - open Freemius checkout
  $(document).on('click', '#pricing-modal-upgrade-btn', function () {
    $('#dbg-lv-pricing-modal').modal('hide');
    // Small delay to let modal close animation complete
    setTimeout(function () {
      openFreemiusCheckout();
    }, 300);
  });
});

/***/ })

}]);