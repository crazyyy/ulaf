(function ($) {
    $(document).ready(function () {

        $('select[name="league_list"]').on('change', function () {
            var selObj = $(this).select2('data');
            setContent(selObj[0].id);
        });

        var selObjDef = $('select[name="league_list"]').select2('data');
        setContent(selObjDef[0].id);

    });

    function setContent(id) {
        $('#stm-league-ajax-content').empty();
        $('#stm-league-ajax-content').addClass('loading');
	    var count = $('body').find('#stm-league-ajax-content').attr('data-count');
        console.log(count);
        $.ajax({
            url: ajaxurl,
            dataType: 'json',
            context: this,
            data: {
                action: 'stm_league_table_by_id',
                league_id: id,
                count: count,
                security: stm_league_table_by_id
            },
            success: function (data) {
                if(data.table) {
                    $('#stm-league-ajax-content').removeClass('loading');
                    $('#stm-league-ajax-content').html(data.table);
                    $('.stm-link-all-league').attr('href', data.link);

                    if (document.querySelector('#stm-league-ajax-content .sp-table-wrapper') !== null) {
                        new PerfectScrollbar('#stm-league-ajax-content .sp-table-wrapper');
                    }
                }
            }
        });
    }
})(jQuery);
