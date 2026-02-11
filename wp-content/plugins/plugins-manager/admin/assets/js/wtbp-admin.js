jQuery(document).ready(function ($) {
    //jQuery('.plugin-update.colspanchange').attr('colspan', 4);

    var tbl = jQuery('table.wp-list-table.plugins tbody tr');
    var btn_text = jQuery('.wtbp_sort_plugins').text();

    jQuery(document).on('click', '.wtbp_sort_plugins', function (e) {
        e.preventDefault();
        var btn = jQuery(this);
        if (btn.attr('data-sort') === 'active') {
            jQuery('table.wp-list-table.plugins tbody tr').sort(function (a, b) { // сортируем
                var act_class1 = jQuery(a).hasClass('active');
                var act_class2 = jQuery(b).hasClass('active');
                return +act_class2 - +act_class1;
            }).appendTo('table.wp-list-table.plugins tbody');// возвращаем в контейнер
            btn.attr('data-sort', 'deactive');
            btn.text(btn_text + ' (Active)');
        } else {
            jQuery('table.wp-list-table.plugins tbody tr').sort(function (a, b) { // сортируем
                var act_class1 = jQuery(a).hasClass('active');
                var act_class2 = jQuery(b).hasClass('active');
                return +act_class1 - +act_class2;
            }).appendTo('table.wp-list-table.plugins tbody');// возвращаем в контейнер
            btn.attr('data-sort', 'active');
            btn.text(btn_text + ' (Inactive)');
        }
    });

    jQuery(document).on('click', '#wtbp-delete-confirm', function (e) {
        if (!confirm(wtbp_confirm.text)) {
            e.preventDefault();
        }
    });
    //Auto-update
    jQuery(document).on('change', '.wtb_autoupdate_checkbox', function (e) {
        var self = jQuery(this);
        self.hide();
        self.prev('.toggle-auto-update').click();
        jQuery(document).on('wp-auto-update-setting-changed', function (event, data) {
            self.show();
        });
    });
});