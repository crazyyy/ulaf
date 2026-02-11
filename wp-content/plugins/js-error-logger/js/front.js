(function () {
    js_err_log = {
        ...js_err_log,
        errors: {},
        time_offset: js_err_log.time-Date.now(),
        error_count: 0,
        pattern: /[/\-\\^$*+?.()|[\]{}]/g,
        user_agent: (window.navigator.userAgent === undefined) ? '' : window.navigator.userAgent,
        do_request: function (params) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', js_err_log.ajax_url);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send(params);
        },
        is_match: function (string, ignored_strings) {
            let ajax = true;
            ignored_strings.every(function (el) {
                let matches = js_err_log.get_match(el, string);
                if (matches) {
                    ajax = false;
                    return false;
                }
                return true;
            })
            return ajax;
        },
        get_match: function (el, string) {
            let pattern = el.trim().toLowerCase().replace(js_err_log.pattern, function (match) {
                return '\\' + match;
            });
            return string.toLowerCase().match(pattern);
        },
        is_multi_match: function (err = ['', ''], ignored_strings) {
            let ajax = true;
            if (!err[0] || !err[1]) {
                return ajax;
            }
            ignored_strings.every(function (el) {
                let arr = el.split("||"),
                    err_match = js_err_log.get_match(arr[0], err[0]),
                    script_match = js_err_log.get_match(arr[1], err[1]);
                if (err_match && script_match) {
                    ajax = false;
                    return false;
                }
                return true;
            })
            return ajax;
        }
        ,
        init: function () {
            let loc = window.location.href ?? document.documentURI;
            if (!loc || loc.length === 0) {
                loc = 'N/A';
            }
            js_err_log.loc = loc;
            if (js_err_log.booleans.delay_send) {
                document.onvisibilitychange = function () {
                    if (document.visibilityState === "hidden") {
                        if (Object.keys(js_err_log.errors).length !== 0) {
                            let params = new URLSearchParams();
                            params.append('action', 'jserrlog_log_error');
                            params.append('nonce', js_err_log.nonce);
                            params.append('data', JSON.stringify(js_err_log.errors));
                            js_err_log.do_request(params.toString());
                            js_err_log.errors = {};
                        }
                    }
                };
            }
            window.onerror = function (msg, url, line, col = '', err = '') {
                if (!url || url.length === 0) {
                    url = 'N/A';
                }
                if (!js_err_log.booleans.third_party_scripts) {
                    if (msg.toLowerCase().trim() === 'script error.') {
                        return;
                    }
                }
                let max = Math.max(js_err_log.error_count, js_err_log.max_errors_per_page, 10);
                if (max === js_err_log.error_count) {
                    return;
                }
                js_err_log.error_count++;
                let full_error = JSON.stringify(err, Object.getOwnPropertyNames(err)).replace(/\\n/g, "\\n"),
                    ajax = true,
                    ignored_strings = [];
                if (js_err_log.ignored_data.agents.length) {
                    ignored_strings = js_err_log.ignored_data.agents;
                    ajax = js_err_log.is_match(js_err_log.user_agent, ignored_strings);
                }
                if (js_err_log.ignored_data.errors.length) {
                    if (ajax === true) {
                        ignored_strings = js_err_log.ignored_data.errors;
                        ajax = js_err_log.is_match(full_error, ignored_strings);
                    }
                }
                if (js_err_log.ignored_data.scripts.length) {
                    if (ajax === true) {
                        ignored_strings = js_err_log.ignored_data.scripts;
                        ajax = js_err_log.is_match(url, ignored_strings);
                    }
                }
                if (js_err_log.ignored_data.combined.length) {
                    if (ajax === true) {
                        ignored_strings = js_err_log.ignored_data.combined;
                        ajax = js_err_log.is_multi_match([full_error, url], ignored_strings);
                    }
                }
                if (ajax === false) {
                    return;
                }
                let time = Math.floor((Date.now()+js_err_log.time_offset) / 1000),
                    urls = JSON.stringify([url, js_err_log.loc]);
                if (!js_err_log.booleans.delay_send) {
                    let params = new URLSearchParams();
                    params.append('action', 'jserrlog_log_error');
                    params.append('nonce', js_err_log.nonce);
                    params.append('msg', msg.toString());
                    params.append('urls', urls);
                    params.append("line", line.toString());
                    params.append("time", time.toString());
                    params.append('agent', js_err_log.user_agent);
                    params.append('col', col.toString());
                    params.append('err', full_error);
                    params = params.toString().replace(/%20/g, '+');
                    js_err_log.do_request(params);
                } else {
                    js_err_log.errors[js_err_log.error_count] = {
                        "msg": msg,
                        "urls": urls,
                        "line": line,
                        "time": time,
                        "agent": js_err_log.user_agent,
                        "col": col,
                        "err": full_error
                    };
                }
            };
        }
    }
    js_err_log.init();
})();