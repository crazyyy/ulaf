jQuery(document).ready( $ => {
    class SnippetsFilter {
        constructor() {
            this.descCookie = localStorage.getItem('showHideDescSnippets');
            this.oldTotal = $('.displaying-num').eq(0).text();
            this.calcTotal = $('#the-list tr').length - $('#the-list tr.plugin-update-tr').length;

            this.filterSelectors = [
                'td.column-name a.snippet-name',
                'td.column-description',
                'td.column-tags'
            ];

            this.init();
        }

        init() {
            $('div.tablenav.top div.alignleft.actions.bulkactions').after(ADTW.html);

            $('#hide-desc,#hide-active,#hide-inactive').click(e => e.preventDefault());
            $('#hide-desc').click(this.descAction.bind(this));
            $('#hide-active').click(this.swapClasses.bind(this));
            $('#hide-inactive').click(this.swapClasses.bind(this));

            $("#b5f-plugins-filter").on('focus', e => {
                if ($(e.target).val().length === 0) this.resetList();
            });
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
                this.resetList();
                this.updtTotals(); 
            });

            if (this.descCookie) $('#hide-desc').click();
        }

        updtTotals() {
            let nowTotal = $('#the-list tr:visible').length - $('#the-list tr.plugin-update-tr:visible').length;
            let theNum = nowTotal == this.calcTotal ? this.oldTotal : nowTotal;
            $('.displaying-num').text(theNum);
        }

        descAction(e) {
            if (!$(e.target).hasClass('active')) {
                $('div.plugin-description, div.row-actions').hide();
                localStorage.setItem('showHideDescSnippets', true);
                $(e.target).addClass('active b5f-active');
            } else {
                $('div.plugin-description, div.row-actions').show();
                localStorage.removeItem('showHideDescSnippets');
                $(e.target).removeClass('active b5f-active');
            }
        }

        allEData() {
            $('button.b5f-btn-status').each((i,v) => {
                $(v).attr('title', $(v).data('titleShow'));
            });
        }

        swapClasses(e) {
            let status = $(e.target).attr('id').includes('inactive') 
                ? 'active-snippet' : 'inactive-snippet';
            if (!$(e.target).hasClass('active')) {
                this.resetList();
                $('tr.'+status+', tr.plugin-update-tr').hide();
                $(e.target).addClass('active b5f-active');
                this.allEData();
                $(e.target).attr('title', $(e.target).data('titleHide'));
            } else {
                $('#the-list tr').show();
                $(e.target).removeClass('active b5f-active');
                $(e.target).attr('title', $(e.target).data('titleShow'));
            }
            this.updtTotals();
        }

        resetList() {
            $('tbody#the-list tr').show();
            $("#b5f-plugins-filter").val('');
            $('button.b5f-btn-status').removeClass('active b5f-active');
        }
    }

    new SnippetsFilter();
});