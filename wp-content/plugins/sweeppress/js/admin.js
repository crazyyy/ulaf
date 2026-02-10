/*jslint esversion: 6, regexp: true, nomen: true, undef: true, sloppy: true, eqeq: true, vars: true, white: true, plusplus: true, maxerr: 50, indent: 4 */
/*global d4plib_admin_data,d4plib_admin_dialogs,sweeppress_data*/

;(function($, window, document, undefined) {
    window.wp = window.wp || {};
    window.wp.sweeppress = window.wp.sweeppress || {};

    window.wp.sweeppress.helpers = {
        sizeFormat: (size) => {
            if (size === 0) {
                return "0 B";
            }

            const mod = Math.floor(Math.log(size) / Math.log(1024));

            return '<strong>' + (size / Math.pow(1024, mod)).toFixed(0) + '</strong> ' + ' KMGTP'.charAt(mod) + 'B';
        }
    };

    window.wp.sweeppress.admin = {
        init: () => {
            const p = d4plib_admin_data.page.panel;

            if (p !== "") {
                wp.sweeppress.admin.shared.controls();
            }

            if (p === "dashboard") {
                wp.sweeppress.admin.shared.init();
                wp.sweeppress.admin.dashboard.dialogs();
                wp.sweeppress.admin.dashboard.init();
            }

            if (p === "options") {
                wp.sweeppress.admin.options.dialogs();
                wp.sweeppress.admin.options.init();
                wp.sweeppress.admin.shared.options();
            }

            if (p === "sweep") {
                wp.sweeppress.admin.shared.init();
                wp.sweeppress.admin.sweep.init();
            }
        },
        dashboard: {
            init: () => {
                if ($(".sweeppress-sweeper-check").length > 0) {
                    $(".sweeppress-dashboard-check-all").show();
                }

                $(document).on("change", ".sweeppress-dashboard-check-all", (e) => {
                    const wrapper = $(e.currentTarget).closest(".sweeper-dashboard-overview"),
                        checked = $(e.currentTarget).is(":checked");

                    $(".sweeppress-sweeper-check", wrapper).prop("checked", checked).change();
                });

                $(document).on("change", ".sweeppress-task-check, .sweeppress-sweeper-check", () => {
                    const checked = $(".sweeppress-task-check:checked"),
                        total = checked.length;
                    let records = 0,
                        size = 0;

                    if (total > 0) {
                        checked.each((index, element) => {
                            records += $(element).data("records");
                            size += $(element).data("size");
                        });
                    }

                    $(".sweeppress-sweeper-counters strong").html(records);
                    $(".sweeppress-sweeper-counters span").html(wp.sweeppress.helpers.sizeFormat(size));

                    $(".sweeppress-sweeper-wrapper input[type=submit]").prop("disabled", total === 0);
                });

                $(document).on("click", "#sweeppress-information-auto .button-secondary", (e) => {
                    e.preventDefault();

                    $(".sweeper-dashboard-auto").hide();
                    $(".sweeper-dashboard-overview").show();
                });

                $(document).on("click", "#sweeppress-information-auto .button-primary", (e) => {
                    e.preventDefault();

                    $("#sweeppress-dialog-dashboard-auto").wpdialog("open");
                });

                $("form#sweeppress-form-quick").on("submit", (e) => {
                    e.preventDefault();

                    if ($(e.currentTarget).data("status") !== "working") {
                        $(e.currentTarget).slideUp("slow").data("status", "working");
                        $(".sweeppress-dashboard-check-all").hide();
                        $("#sweeppress-results-quick").slideDown("slow");
                        $(window).scrollTop(0);

                        $(e.currentTarget).ajaxSubmit({
                            dataType: "html",
                            type: "post",
                            timeout: 5 * 60 * 1000,
                            url: ajaxurl + "?action=sweeppress-run-quick",
                            success: (html) => {
                                $("#sweeppress-results-quick").html(html);
                            },
                            error: (request, status, error) => {
                                $("#sweeppress-results-quick").html(error);
                            }
                        });
                    }
                });
            },
            auto: () => {
                const nonce = $("#sweeppress-information-auto .button-primary").data("nonce");

                $("#sweeppress-information-auto").remove();
                $("#sweeppress-results-auto").slideDown("slow");
                $(window).scrollTop(0);

                $.ajax({
                    dataType: "html",
                    type: "post",
                    timeout: 5 * 60 * 1000,
                    url: ajaxurl + "?action=sweeppress-run-auto&_wpnonce=" + nonce,
                    success: (html) => {
                        $("#sweeppress-results-auto").html(html);
                    },
                    error: (request, status, error) => {
                        $("#sweeppress-results-auto").html(error);
                    }
                });
            },
            dialogs: () => {
                const dialog_run = $.extend({}, wp.dev4press.dialogs.default_dialog(), {
                    dialogClass: wp.dev4press.dialogs.classes("sweeppress-dialog-hide-x"),
                    buttons: [
                        {
                            id: "sweeppress-dialog-auto-run",
                            class: "sweeppress-dialog-auto-button-run",
                            text: d4plib_admin_dialogs.buttons.start,
                            data: {icon: "ok"},
                            click: () => {
                                $("#sweeppress-dialog-dashboard-auto").wpdialog("close");
                                wp.sweeppress.admin.dashboard.auto();
                            }
                        },
                        {
                            id: "sweeppress-dialog-auto-cancel",
                            class: "sweeppress-dialog-auto-button-cancel button-has-focus",
                            text: d4plib_admin_dialogs.buttons.cancel,
                            data: {icon: "cancel"},
                            autofocus: true,
                            click: () => {
                                $("#sweeppress-dialog-dashboard-auto").wpdialog("close");
                            }
                        }
                    ]
                });

                $("#sweeppress-dialog-dashboard-auto").wpdialog(dialog_run);

                wp.dev4press.dialogs.icons("#sweeppress-dialog-dashboard-auto");
            }
        },
        options: {
            init: () => {
                $(document).on("click", ".sweeppress-action-option-delete", (e) => {
                    e.preventDefault();

                    const dialog = $("#sweeppress-dialog-option-key-delete");

                    dialog.find("h4 strong").html($(e.currentTarget).data('option'));

                    wp.sweeppress.admin.storage.url = $(e.currentTarget).data("url");

                    dialog.wpdialog("open");
                });
            },
            dialogs: () => {
                const dialog_delete = $.extend({}, wp.dev4press.dialogs.default_dialog(), {
                    dialogClass: wp.dev4press.dialogs.classes("sweeppress-dialog-hide-x"),
                    buttons: [
                        {
                            id: "sweeppress-dialog-option-key-delete-delete",
                            class: "sweeppress-dialog-option-key-delete-button-delete",
                            text: d4plib_admin_dialogs.buttons.delete,
                            data: {icon: "delete"},
                            click: () => {
                                window.location.href = wp.sweeppress.admin.storage.url;
                            }
                        },
                        {
                            id: "sweeppress-dialog-option-key-delete-cancel",
                            class: "sweeppress-dialog-table-button-cancel button-has-focus",
                            text: d4plib_admin_dialogs.buttons.cancel,
                            data: {icon: "cancel"},
                            autofocus: true,
                            click: () => {
                                $("#sweeppress-dialog-option-key-delete").wpdialog("close");
                            }
                        }
                    ]
                }), dialog_bulk_delete = $.extend({}, wp.dev4press.dialogs.default_dialog(), {
                    dialogClass: wp.dev4press.dialogs.classes("sweeppress-dialog-hide-x"),
                    buttons: [
                        {
                            id: "sweeppress-dialog-option-bulk-delete-delete",
                            class: "sweeppress-dialog-option-bulk-delete-button-delete",
                            text: d4plib_admin_dialogs.buttons.delete,
                            data: {icon: "delete"},
                            click: () => {
                                $(".d4p-content form").submit();
                            }
                        },
                        {
                            id: "sweeppress-dialog-option-bulk-delete-cancel",
                            class: "sweeppress-dialog-option-bulk-delete-button-cancel button-has-focus",
                            text: d4plib_admin_dialogs.buttons.cancel,
                            data: {icon: "cancel"},
                            autofocus: true,
                            click: () => {
                                $("#sweeppress-dialog-option-bulk-delete").wpdialog("close");
                            }
                        }
                    ]
                });

                $("#sweeppress-dialog-option-key-delete").wpdialog(dialog_delete);
                $("#sweeppress-dialog-option-bulk-delete").wpdialog(dialog_bulk_delete);

                wp.dev4press.dialogs.icons("#sweeppress-dialog-option-key-delete");
                wp.dev4press.dialogs.icons("#sweeppress-dialog-option-bulk-delete");
            }
        },
        sweep: {
            init: () => {
                $(document).on("click", ".sweeper-item-refresh", (e) => {
                    e.preventDefault();

                    const button = $(e.currentTarget),
                        inside = $(e.currentTarget).closest(".sweeppress-item-inside"),
                        loader = inside.find(".sweeppress-item-run-loader");

                    inside.find("div:not(.sweeppress-item-run-loader)").hide();
                    loader.show();

                    wp.sweeppress.admin.sweep.reload(button, inside);
                });

                $(document).on("click", ".sweeppress-item-run button", (e) => {
                    e.preventDefault();

                    const button = $(e.currentTarget),
                        notice = $(e.currentTarget).parent().parent(),
                        loader = notice.next(),
                        inside = notice.parent();

                    notice.remove();
                    loader.show();

                    wp.sweeppress.admin.sweep.reload(button, inside);
                });

                $(document).on("click", "button.toggle-empty-tasks", (e) => {
                    e.preventDefault();

                    const status = $(e.currentTarget).attr("aria-expanded") === "true",
                        wrapper = $(e.currentTarget).closest(".sweeppress-sweepers-wrapper");

                    wrapper.find(".empty-sweeper").toggleClass("show-empty-sweeper");
                    wrapper.find(".empty-sweeper-category").toggleClass("show-empty-sweeper-category");

                    if (status) {
                        $(e.currentTarget).attr("aria-expanded", "false");
                    } else {
                        $(e.currentTarget).attr("aria-expanded", "true");
                    }
                });

                $(document).on("click", ".sweeppress-item-wrapper h5 button.toggle-empty", (e) => {
                    e.preventDefault();

                    const status = $(e.currentTarget).attr("aria-expanded") === "true";

                    $(e.currentTarget).closest(".sweeppress-item-wrapper").toggleClass("show-empty-tasks");

                    if (status) {
                        $(e.currentTarget).attr("aria-expanded", "false");
                    } else {
                        $(e.currentTarget).attr("aria-expanded", "true");
                    }
                });

                $(document).on("click", ".sweeppress-item-wrapper h5 button.toggle-section", (e) => {
                    e.preventDefault();

                    const id = "#" + $(e.currentTarget).attr("aria-controls"),
                        status = $(e.currentTarget).attr("aria-expanded") === "true";

                    if (status) {
                        $(e.currentTarget).attr("aria-expanded", "false");
                        $(id).prop("hidden", true);
                    } else {
                        $(e.currentTarget).attr("aria-expanded", "true");
                        $(id).prop("hidden", false);
                    }
                });

                $(document).on("change", ".sweeppress-task-check, .sweeppress-sweeper-check", () => {
                    const checked = $(".sweeppress-task-check:checked"),
                        total = checked.length;
                    let records = 0,
                        size = 0;

                    if (total > 0) {
                        checked.each((index, element) => {
                            records += $(element).data("records");
                            size += $(element).data("size");
                        });
                    }

                    $(".sweeppress-sweeper-counters .sweeppress-sweep-tasks").html(total);
                    $(".sweeppress-sweeper-counters .sweeppress-sweep-records").html(records);
                    $(".sweeppress-sweeper-counters .sweeppress-sweep-size").html(wp.sweeppress.helpers.sizeFormat(size));

                    $("#sweeppress-sweep-run").prop("disabled", total === 0);
                });

                $("form#sweeppress-form-sweep").on("submit", (e) => {
                    e.preventDefault();

                    if ($(e.currentTarget).data("status") !== "working") {
                        $(e.currentTarget).data("status", "working");

                        $("#sweeppress-results-wrapper").slideDown("slow");
                        $(".sweeppress-sweepers-wrapper").slideUp("slow", function() {
                            $(this).remove();
                            $("#sweeppress-sweep-run").remove();
                        });

                        $(window).scrollTop(0);

                        const r = $("#sweeppress-results-sweeper");

                        $(e.currentTarget).ajaxSubmit({
                            dataType: "html",
                            type: "post",
                            timeout: 5 * 60 * 1000,
                            url: ajaxurl + "?action=sweeppress-run-sweep",
                            success: (html) => {
                                r.html(html);
                            },
                            error: (request, status, error) => {
                                r.html(error);
                            }
                        });
                    }
                });
            },
            reload: (button, inside) => {
                $.ajax({
                    dataType: "html",
                    type: "post",
                    timeout: 5 * 60 * 1000,
                    url: ajaxurl + "?action=sweeppress-manual-estimate",
                    data: {
                        'sweeper': button.data("sweeper"),
                        'cache': button.data("cache"),
                        '_wpnonce': button.data("nonce")
                    },
                    success: (html) => {
                        inside.html(html);

                        if ($("input", inside).length > 0) {
                            inside.parent().find("h5 input").show();
                        }
                    },
                    error: (request, status, error) => {
                        inside.html(error);
                    }
                });
            }
        },
        shared: {
            init: () => {
                $(document).on("change", ".sweeppress-sweeper-check", (e) => {
                    const wrapper = $(e.currentTarget).closest(".sweeppress-item-wrapper"),
                        checked = $(e.currentTarget).is(":checked");

                    $(".sweeppress-task-check", wrapper).prop("checked", checked).change();
                });
            },
            options: () => {
                $(document).on("click", ".d4p-content #doaction, .d4p-content #doaction2", (e) => {
                    const action = $("#bulk-action-selector-top").val(),
                        p = d4plib_admin_data.page,
                        s = p.subpanel;

                    if (action === 'delete') {
                        e.preventDefault();

                        if (p.panel === "options" || p.panel === "sitemeta") {
                            $("#sweeppress-dialog-option-bulk-delete").wpdialog("open");
                        } else if (p.panel === 'backups') {
                            $("#sweeppress-dialog-backups-bulk-delete").wpdialog("open");
                        } else if (s.substring(0, 7) === 'preview') {
                            $("#sweeppress-dialog-preview-bulk-delete").wpdialog("open");
                        } else if (s.substring(0, 8) === 'metadata') {
                            $("#sweeppress-dialog-metadata-bulk-delete").wpdialog("open");
                        }
                    }
                });
            },
            controls: () => {
                $(document).on("click", ".sweeppress-confirm-url", (e) => {
                    e.preventDefault();

                    if (confirm(sweeppress_data.are_you_sure)) {
                        window.location.href = $(e.currentTarget).data("url");
                    }
                });
            }
        },
        storage: {
            url: '',
            table: ''
        }
    };

    $(document).ready(() => {
        wp.sweeppress.admin.init();
    });
})(jQuery, window, document);
