"use strict";
(self["webpackChunkdebug_log_viewer"] = self["webpackChunkdebug_log_viewer"] || []).push([["front_assets_js_components_log-view_api-service_js"],{

/***/ "./front/assets/js/components/log-view/api-service.js":
/*!************************************************************!*\
  !*** ./front/assets/js/components/log-view/api-service.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   LogViewerApiService: () => (/* binding */ LogViewerApiService)
/* harmony export */ });
/* harmony import */ var _utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../utils.js */ "./front/assets/js/utils.js");
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
/* global jQuery, ajaxurl, dbg_lv_backend_data, confirm, location, URL, XMLHttpRequest */

var LogViewerApiService = /*#__PURE__*/function () {
  function LogViewerApiService(table) {
    _classCallCheck(this, LogViewerApiService);
    this.table = table;
  }
  return _createClass(LogViewerApiService, [{
    key: "fillInitialData",
    value: function () {
      var _fillInitialData = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var rawResponse, response;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              _context.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_run_live_updates',
                initial: true,
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              rawResponse = _context.sent;
              if (rawResponse) {
                _context.next = 6;
                break;
              }
              return _context.abrupt("return");
            case 6:
              response = JSON.parse(rawResponse);
              if (response.data.length > 0) {
                this.table.rows.add(response.data).draw();
              }
              _context.next = 13;
              break;
            case 10:
              _context.prev = 10;
              _context.t0 = _context["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context.t0, 'error');
            case 13:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[0, 10]]);
      }));
      function fillInitialData() {
        return _fillInitialData.apply(this, arguments);
      }
      return fillInitialData;
    }()
  }, {
    key: "updateLogs",
    value: function () {
      var _updateLogs = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(onBeforeUpdate) {
        var rawResponse, response;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.prev = 0;
              if (onBeforeUpdate) onBeforeUpdate();
              _context2.next = 4;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_run_live_updates',
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 4:
              rawResponse = _context2.sent;
              if (rawResponse) {
                _context2.next = 7;
                break;
              }
              return _context2.abrupt("return");
            case 7:
              response = JSON.parse(rawResponse);
              if (!(response.action === 'clear')) {
                _context2.next = 11;
                break;
              }
              this.table.clear().draw();
              return _context2.abrupt("return");
            case 11:
              if (response.data.length > 0) {
                this.table.rows.add(response.data).draw();
              }
              _context2.next = 17;
              break;
            case 14:
              _context2.prev = 14;
              _context2.t0 = _context2["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context2.t0, 'error');
            case 17:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this, [[0, 14]]);
      }));
      function updateLogs(_x) {
        return _updateLogs.apply(this, arguments);
      }
      return updateLogs;
    }()
  }, {
    key: "toggleDebugMode",
    value: function () {
      var _toggleDebugMode = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3(isChecked) {
        var response, data;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              _context3.prev = 0;
              _context3.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_toggle_debug_mode',
                state: +isChecked,
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              response = _context3.sent;
              data = response.data;
              if (response.success) {
                _context3.next = 7;
                break;
              }
              throw new Error(response.data);
            case 7:
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)("".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('debug_mode'), " ").concat(data.state), 'success');
              _context3.next = 14;
              break;
            case 10:
              _context3.prev = 10;
              _context3.t0 = _context3["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context3.t0, 'error');
              throw _context3.t0;
            case 14:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[0, 10]]);
      }));
      function toggleDebugMode(_x2) {
        return _toggleDebugMode.apply(this, arguments);
      }
      return toggleDebugMode;
    }()
  }, {
    key: "toggleDebugScripts",
    value: function () {
      var _toggleDebugScripts = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4(isChecked) {
        var response, data;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              _context4.prev = 0;
              _context4.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_toggle_debug_scripts',
                state: +isChecked,
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              response = _context4.sent;
              data = response.data;
              if (response.success) {
                _context4.next = 7;
                break;
              }
              throw new Error(response.data);
            case 7:
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)("".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('debug_scripts'), " ").concat(data.state), 'success');
              _context4.next = 14;
              break;
            case 10:
              _context4.prev = 10;
              _context4.t0 = _context4["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context4.t0, 'error');
              throw _context4.t0;
            case 14:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[0, 10]]);
      }));
      function toggleDebugScripts(_x3) {
        return _toggleDebugScripts.apply(this, arguments);
      }
      return toggleDebugScripts;
    }()
  }, {
    key: "toggleLogInFile",
    value: function () {
      var _toggleLogInFile = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5(isChecked) {
        var response, data;
        return _regeneratorRuntime().wrap(function _callee5$(_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              _context5.prev = 0;
              _context5.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_toggle_log_in_file',
                state: +isChecked,
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              response = _context5.sent;
              data = response.data;
              if (response.success) {
                _context5.next = 7;
                break;
              }
              throw new Error(response.data);
            case 7:
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)("".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('debug_log_scripts'), " ").concat(data.state), 'success');
              _context5.next = 14;
              break;
            case 10:
              _context5.prev = 10;
              _context5.t0 = _context5["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context5.t0, 'error');
              throw _context5.t0;
            case 14:
            case "end":
              return _context5.stop();
          }
        }, _callee5, null, [[0, 10]]);
      }));
      function toggleLogInFile(_x4) {
        return _toggleLogInFile.apply(this, arguments);
      }
      return toggleLogInFile;
    }()
  }, {
    key: "toggleDisplayErrors",
    value: function () {
      var _toggleDisplayErrors = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6(isChecked) {
        var response, data;
        return _regeneratorRuntime().wrap(function _callee6$(_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              _context6.prev = 0;
              _context6.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_toggle_display_errors',
                state: +isChecked,
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              response = _context6.sent;
              data = response.data;
              if (response.success) {
                _context6.next = 7;
                break;
              }
              throw new Error(response.data);
            case 7:
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)("".concat((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('display_errors'), " ").concat(data.state), 'success');
              _context6.next = 14;
              break;
            case 10:
              _context6.prev = 10;
              _context6.t0 = _context6["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context6.t0, 'error');
              throw _context6.t0;
            case 14:
            case "end":
              return _context6.stop();
          }
        }, _callee6, null, [[0, 10]]);
      }));
      function toggleDisplayErrors(_x5) {
        return _toggleDisplayErrors.apply(this, arguments);
      }
      return toggleDisplayErrors;
    }()
  }, {
    key: "isDebugLogPubliclyAccessible",
    value: function () {
      var _isDebugLogPubliclyAccessible = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7() {
        var response;
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              _context7.prev = 0;
              _context7.next = 3;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_is_debug_log_publicly_accessible',
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 3:
              response = _context7.sent;
              if (response.success) {
                _context7.next = 6;
                break;
              }
              throw new Error(response.data);
            case 6:
              return _context7.abrupt("return", response);
            case 9:
              _context7.prev = 9;
              _context7.t0 = _context7["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context7.t0, 'error');
              throw _context7.t0;
            case 13:
            case "end":
              return _context7.stop();
          }
        }, _callee7, null, [[0, 9]]);
      }));
      function isDebugLogPubliclyAccessible() {
        return _isDebugLogPubliclyAccessible.apply(this, arguments);
      }
      return isDebugLogPubliclyAccessible;
    }()
  }, {
    key: "enableLogging",
    value: function () {
      var _enableLogging = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee8() {
        var response;
        return _regeneratorRuntime().wrap(function _callee8$(_context8) {
          while (1) switch (_context8.prev = _context8.next) {
            case 0:
              _context8.next = 2;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_first_run_enable_logging',
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 2:
              response = _context8.sent;
              if (response.success) {
                _context8.next = 5;
                break;
              }
              throw new Error(response.data);
            case 5:
              return _context8.abrupt("return", response);
            case 6:
            case "end":
              return _context8.stop();
          }
        }, _callee8);
      }));
      function enableLogging() {
        return _enableLogging.apply(this, arguments);
      }
      return enableLogging;
    }()
  }, {
    key: "clearLog",
    value: function () {
      var _clearLog = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee9() {
        var response;
        return _regeneratorRuntime().wrap(function _callee9$(_context9) {
          while (1) switch (_context9.prev = _context9.next) {
            case 0:
              _context9.prev = 0;
              if (confirm((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('flush_log_confirmation'))) {
                _context9.next = 3;
                break;
              }
              return _context9.abrupt("return", null);
            case 3:
              _context9.next = 5;
              return jQuery.post(ajaxurl, {
                action: 'dbg_lv_log_viewer_clear_log',
                wp_nonce: dbg_lv_backend_data.ajax_nonce
              });
            case 5:
              response = _context9.sent;
              if (response.success) {
                _context9.next = 8;
                break;
              }
              throw new Error(response.data);
            case 8:
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)((0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.t)('log_was_cleared'), 'success');
              location.reload();
              _context9.next = 16;
              break;
            case 12:
              _context9.prev = 12;
              _context9.t0 = _context9["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context9.t0, 'error');
              throw _context9.t0;
            case 16:
            case "end":
              return _context9.stop();
          }
        }, _callee9, null, [[0, 12]]);
      }));
      function clearLog() {
        return _clearLog.apply(this, arguments);
      }
      return clearLog;
    }()
  }, {
    key: "downloadLog",
    value: function downloadLog() {
      jQuery.post({
        url: ajaxurl,
        data: {
          action: 'dbg_lv_log_viewer_download_log',
          wp_nonce: dbg_lv_backend_data.ajax_nonce
        },
        xhr: function xhr() {
          var xhr = new XMLHttpRequest();
          xhr.responseType = 'blob';
          return xhr;
        },
        success: function success(response) {
          var url = URL.createObjectURL(response);
          var link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'debug.log');
          document.body.append(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);
        },
        error: function error(xhr, status, _error) {
          (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_error, 'error');
        }
      });
    }
  }, {
    key: "changeLogsUpdateMode",
    value: function () {
      var _changeLogsUpdateMode = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee10(mode) {
        var response;
        return _regeneratorRuntime().wrap(function _callee10$(_context10) {
          while (1) switch (_context10.prev = _context10.next) {
            case 0:
              _context10.prev = 0;
              _context10.next = 3;
              return jQuery.post({
                url: ajaxurl,
                data: {
                  action: 'dbg_lv_change_logs_update_mode',
                  mode: mode,
                  wp_nonce: dbg_lv_backend_data.ajax_nonce
                }
              });
            case 3:
              response = _context10.sent;
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(response.data, 'success');
              return _context10.abrupt("return", response);
            case 8:
              _context10.prev = 8;
              _context10.t0 = _context10["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context10.t0, 'error');
              throw _context10.t0;
            case 12:
            case "end":
              return _context10.stop();
          }
        }, _callee10, null, [[0, 8]]);
      }));
      function changeLogsUpdateMode(_x6) {
        return _changeLogsUpdateMode.apply(this, arguments);
      }
      return changeLogsUpdateMode;
    }()
  }, {
    key: "changeDatetimeFormat",
    value: function () {
      var _changeDatetimeFormat = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee11(format) {
        var response;
        return _regeneratorRuntime().wrap(function _callee11$(_context11) {
          while (1) switch (_context11.prev = _context11.next) {
            case 0:
              _context11.prev = 0;
              _context11.next = 3;
              return jQuery.post({
                url: ajaxurl,
                data: {
                  action: 'dbg_lv_change_datetime_format',
                  format: format,
                  wp_nonce: dbg_lv_backend_data.ajax_nonce
                }
              });
            case 3:
              response = _context11.sent;
              if (response.success) {
                _context11.next = 6;
                break;
              }
              throw new Error(response.data);
            case 6:
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(response.data, 'success');
              _context11.next = 13;
              break;
            case 9:
              _context11.prev = 9;
              _context11.t0 = _context11["catch"](0);
              (0,_utils_js__WEBPACK_IMPORTED_MODULE_0__.showToast)(_context11.t0, 'error');
              throw _context11.t0;
            case 13:
            case "end":
              return _context11.stop();
          }
        }, _callee11, null, [[0, 9]]);
      }));
      function changeDatetimeFormat(_x7) {
        return _changeDatetimeFormat.apply(this, arguments);
      }
      return changeDatetimeFormat;
    }()
  }]);
}();

/***/ }),

/***/ "./front/assets/js/utils.js":
/*!**********************************!*\
  !*** ./front/assets/js/utils.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initScrollToTopButton: () => (/* binding */ initScrollToTopButton),
/* harmony export */   showToast: () => (/* binding */ showToast),
/* harmony export */   t: () => (/* binding */ t),
/* harmony export */   updateFilterCount: () => (/* binding */ updateFilterCount)
/* harmony export */ });
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
/* global dbg_lv_backend_data, $ */


/**
 * Translation function
 * @param {string} key - The key to translate
 * @returns {string} - The translated string
 */
function t(key) {
  var _dbg_lv_backend_data = dbg_lv_backend_data,
    phrases = _dbg_lv_backend_data.phrases;
  if (phrases && phrases[key]) {
    return phrases[key];
  }
  console.warn("Phrase ".concat(key, " not found"));
  return key;
}
function showToast(message, level) {
  var $toast = $('#custom-toast');
  var $toastBody = $('#toast-body');
  if (!$toast.length) {
    return; // Prevent errors if the toast element is missing
  }

  // Handle Error objects
  var displayMessage = message instanceof Error ? message.message : message;

  // Set the message
  $toastBody.text(displayMessage);

  // Determine the level (success, error, warning)
  var toastClass = 'bg-success';
  if (level === 'error') {
    toastClass = 'bg-danger';
  } else if (level === 'warning') {
    toastClass = 'bg-warning';
  }

  // Remove previous level classes and add the new one to the toast container
  $toast.removeClass('bg-success bg-danger bg-warning').addClass(toastClass);

  // Initialize and show the toast
  var toastInstance = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Toast($toast[0]);
  toastInstance.show();
}

/**
 * Initialize the scroll to top button
 */
function initScrollToTopButton() {
  $('body').append("<div class=\"scroll-top\" id=\"dbg_lv_scrollToTopButton\" style=\"display: none;\">\n        <svg xmlns=\"http://www.w3.org/2000/svg\" height=\"1em\" viewBox=\"0 0 384 512\">\n            <path fill=\"#4f6df5\" d=\"M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z\"/>\n        </svg>\n    </div>");
  $(document).on('scroll', function () {
    if ($(this).scrollTop() > 100) {
      $('#dbg_lv_scrollToTopButton').fadeIn();
    } else {
      $('#dbg_lv_scrollToTopButton').fadeOut();
    }
  });
  $('#dbg_lv_scrollToTopButton').on('click', function () {
    $('html, body').animate({
      scrollTop: 0
    }, 500, 'linear');
    return false;
  });
}

/**
 * Updates the filter count badge on the filter button
 * @param {object} table - The DataTable instance
 */
function updateFilterCount(table) {
  var conditionsCount = 0;
  var filterButton = $('.search-builder-btn');
  var filterCount = filterButton.find('.filter-counter');
  var searchBuilderCriteria = table.searchBuilder.getDetails();
  var isTableFiltered = table.search() !== '' || table.rows({
    search: 'applied'
  }).count() !== table.rows().count();
  if (searchBuilderCriteria && searchBuilderCriteria.criteria && isTableFiltered) {
    conditionsCount = searchBuilderCriteria.criteria.filter(function (criteria) {
      // Check if value exists and is not empty
      if (!criteria.value) return false;

      // Check if this is just an empty condition row
      if (criteria.condition === undefined || criteria.condition === '') return false;
      if (Array.isArray(criteria.value)) {
        return criteria.value.length > 0 && criteria.value.some(function (v) {
          return v && String(v).trim() !== '';
        });
      }
      return String(criteria.value).trim() !== '';
    }).length;
  }
  if (conditionsCount > 0) {
    filterCount.text(" (".concat(conditionsCount, ")")).show();
  } else {
    filterCount.hide();
  }
}

/***/ })

}]);