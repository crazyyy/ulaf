jQuery(document).ready( $ => {
    class UsersFilter {
        constructor() {
            this.oldTotal = $('.displaying-num').eq(0).text();
            this.totalLength = $('#the-list tr').length;

            this.filterSelectors = [
                'td.column-username > strong > a',
                'td.column-name',
                'td.column-email',
                'td.column-role'
            ];

            this.init();
        }

        init() {
            this.updtTotals();
            $('div.tablenav.top div.tablenav-pages.one-page').before(ADTW.html);

            $("#b5f-plugins-filter").on('input', (e) => {
                B5F_Common.daDelay( 
                    (param) => {
                        B5F_Common.daFilterByKeyword(param, document, this.filterSelectors);
                        this.updtTotals();
                    }, 
                    B5F_Common.daNormalizeStr($(e.target).val()) 
                );
            });

            $('.close-icon').click(() => { 
                $('tbody#the-list tr').show(); 
                this.updtTotals(); 
            });
        }

        updtTotals() {
            let nowTotal = $('#the-list tr:visible').length - $('#the-list tr.plugin-update-tr:visible').length;
            let show = nowTotal == this.totalLength ? this.oldTotal : nowTotal;
            $('.displaying-num').text(show);
        }
    }

    new UsersFilter();
});