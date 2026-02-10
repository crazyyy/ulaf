(function ($) {
    jserrlog = {
        ...jserrlog,
        init_dialogs: function () {
            $(".js-err-log-dialog-content").dialog({
                autoOpen: false,
                position: {my: "top center", at: "top center", of: "#js-err-log_widget"},
                modal: true,
                closeText: jserrlog.strings.Close,
                width: 400,
                classes: {
                    "ui-dialog": "js-err-log-dialog"
                },
                buttons: [
                    {
                        text: jserrlog.strings.Close,
                        click: function () {
                            $(this).dialog("close");
                        }
                    }
                ]
            });
            $(".js-err-log-details").addClass("active");
        },
        populate_view_dialog: function (el) {
            let $el = $(el),
                urls = $el.data("urls"),
                time = $el.data("time"),
                position = $el.data("position"),
                err = $el.data("err"),
                agent = $el.data("agent"),
                a1,
            a2 = $("<a>", {href: urls[1], target: "_blank"}).text(urls[1]);
            if (urls[0]==='Inline script'){
                a1=jserrlog.strings.InlineScript;
            }else{
                a1=$("<a>", {href: urls[0], target: "_blank"}).text(urls[0]);
            }
            $(".js-err-log-dialog .js-err-log-view-date").html(time);
            $(".js-err-log-dialog .js-err-log-view-position").html(position);
            $(".js-err-log-dialog .js-err-log-view-error").html(err);
            $(".js-err-log-dialog .js-err-log-view-script-url").html(a1);
            $(".js-err-log-dialog .js-err-log-view-page-url").html(a2);
            $(".js-err-log-dialog .js-err-log-view-agent").html(agent);
        },
        unique_id: function (timestamp) {
            return Math.trunc(timestamp + 10000 * Math.random()).toString(36);
        },
        refresh_log:function()
        {
            $.ajax({
                type: "post",
                dataType: "json",
                url: ajaxurl,
                success: function (response) {
                    if (response.success === true) {
                        $("#js-err-log_widget .inside").html(response.data);
                    }
                },
                data: {
                    action: "jserrlog_refresh_dashboard_log",
                    nonce: jserrlog.nonce
                }
            })
        },
        init: function () {
            $(document)
                .on("click touchend keydown", ".js-err-log-details.active", function (e) {
                    if (e.type==='keydown' && e.which!==13 && e.which!==32){
                        return;
                    }
                    e.preventDefault();
                    jserrlog.populate_view_dialog(this);
                    $(".js-err-log-dialog-content.ui-dialog-content").dialog("open");
                }).on("click touchend", ".js-err-log-refresh-log", function (e) {
                    e.preventDefault();
                $("#js-err-log_widget .inside").html("<div class='js-err-log-loader-holder'><div class='js-err-log-loader'></div></div>");
                jserrlog.refresh_log();
            })
            $(function () {
                jserrlog.init_dialogs();
            });
        }
    };
    jserrlog.init();
})(jQuery);