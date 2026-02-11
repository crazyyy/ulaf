(function ($) {
    toastr.options={
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "500",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    jserrlog = {
        ...jserrlog,
        switch_tab: function (tab) {
            let tabCount = 2;
            tab = parseInt(tab, 10);
            for (let i = 1; i <= tabCount; i++) {
                if (i !== tab) {
                    $("#js-err-log-switch-" + i).removeClass("nav-tab-active").blur();
                    $("#js-err-log-tab-" + i).hide();
                }
            }
            let tabNames = {'1': '', '2': 'settings'};
            $("#js-err-log-switch-" + tab).addClass("nav-tab-active").focus();
            $("#js-err-log-tab-" + tab).show();
            if (typeof (history.pushState) != "undefined") {
                let url = new URL(location);
                if (tab !== 1) {
                    url.searchParams.set('tab', tabNames[tab]);
                } else {
                    url.searchParams.delete('tab');
                }
                history.pushState({}, "", url);
            }
        },
        update_setting: function (optionName, value) {
            toastr.options.toastClass= 'toast '+optionName+"-toast";
            toastr.info(jserrlog.text.SettingsUpdating)
            if (optionName === 'max_widget_results') {
                if (parseInt(value, 10) < 1) {
                    value = 1;
                    $("#max_widget_results").val(1);
                }
            }
            $.ajax({
                type: "post",
                dataType: "json",
                url: ajaxurl,
                success: function (response) {
                    $("."+optionName+"-toast").remove();
                    if (response.success === true) {
                        if (jserrlog.settings[optionName].logRefresh === true) {
                            jserrlog.refresh_log();
                        }
                        if(jserrlog.settings[optionName].cache_warning === true && jserrlog.booleans.has_known_cache_plugin===true){
                            $(".js-err-log-cache-warning").show();
                        }
                        toastr.success(jserrlog.text.SettingsUpdateSuccess);
                    }else{
                        toastr.error(jserrlog.text.SettingsUpdateError);
                    }
                },
                data: {
                    action: "jserrlog_update_settings",
                    nonce: jserrlog.nonce,
                    setting: optionName,
                    value: value
                }
            })
        },
        refresh_log: function () {
            $.ajax({
                type: "post",
                dataType: "json",
                url: ajaxurl,
                success: function (response) {
                    if (response.success === true) {
                        $("#js-err-log-tab-1").html(response.data);
                    }
                },
                data: {
                    action: "jserrlog_refresh_log",
                    nonce: jserrlog.nonce
                }
            })
        },
        purge_log: function () {
            $.ajax({
                type: "post",
                dataType: "json",
                url: ajaxurl,
                success: function (response) {
                    if (response.success === true) {
                        jserrlog.refresh_log();
                    }
                },
                data: {
                    action: "jserrlog_purge_log",
                    nonce: jserrlog.nonce
                }
            })
        },
        set_accent: function (color) {
            document.documentElement.style.setProperty("--jserrlog-color", color);
            document.documentElement.style.setProperty("--jserrlog-bg-color", color + '85');
        },
        switch_setting_visibility: function(originalOptionName, targetOptionNames,value){
            targetOptionNames = targetOptionNames.split(",");
            targetOptionNames.forEach((targetOptionName) => {
                if (value === true || parseInt(value,10)>0) {
                    $(".hidden-" + targetOptionName).fadeIn();
                } else {
                    $(".hidden-" + targetOptionName).fadeOut();
                }
            })
        },
        init: function () {
            $(".js-err-log-settings-nav a").on("click", function (e) {
                e.preventDefault();
                jserrlog.switch_tab($(this).data("tab"));
            });

            window.addEventListener("popstate", function (e) {
                window.location.reload();
            });

            $(".js-err-log-switch input").on("change", function () {
                let value = false,
                    $this = $(this),
                    optionName = $this.attr("name"),
                    shows = $this.data("shows");
                if (this.checked) {
                    value = true;
                }
                if (shows) {
                    jserrlog.switch_setting_visibility(optionName, shows, value)
                }
                jserrlog.update_setting(optionName, value);
            });

            let multiselect = $('select[multiple]');
            if (multiselect.length) {
                multiselect.multiselect({
                    search: true,
                    texts: {
                        placeholder: jserrlog.text.SelectPostTypes,
                        search: jserrlog.text.SearchPostTypes,
                        selectedOptions: jserrlog.text.SelectedPostTypes
                    },
                    showAllPlaceholderOpts: true,
                    minHeight: 100,
                    maxWidth: '100%',
                    maxPlaceholderWidth: '100%',
                    checkboxAutoFit: true
                });
            }
            $(".js-err-log-select").on("change", function () {
                let select = $(this),
                    optionName = select.attr("name"),
                    value;
                if (optionName.includes("[]")){
                    optionName=optionName.slice(0, -2);
                    value=$("#" + optionName).val();
                }else {
                    value = $(".js-err-log-select[name=" + optionName + "] :selected").val();
                }
                jserrlog.update_setting(optionName, value);
            });

            $(document).on("click", ".js-err-log-save-button", function (e) {
                e.preventDefault();
                let option = $(this).data("option"),
                    value = $('#' + option).val();
                jserrlog.update_setting(option, value)
            }).on("click", ".js-err-log-action", function (e) {
                e.preventDefault();
                $(".js-err-log-refreshable").addClass("refreshing");
                let action = $(this).data("action");
                if (action === 'refresh') {
                    jserrlog.refresh_log();
                } else if (action === 'purge') {
                    jserrlog.purge_log();
                }
            }).on("click keydown", ".js-err-log-switch", function (e) {
                if (e.type==='keydown' && e.which!==13 && e.which!==32){
                    return;
                }
                if (e.target.tagName === 'SPAN' || e.type==='keydown') {
                    let checkbox = $(this).find("input");
                    checkbox.prop("checked", !checkbox.prop("checked"));
                    checkbox.trigger("change");
                }
            }).on("change", '#js-err-log-color', function () {
                jserrlog.update_setting('accent',this.value);
            }).on("input", '#js-err-log-color', function () {
                jserrlog.set_accent(this.value);
            });
        }
    }
    jserrlog.init();
})(jQuery);