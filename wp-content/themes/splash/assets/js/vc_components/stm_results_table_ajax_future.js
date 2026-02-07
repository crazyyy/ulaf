(function ($) {
    $(document).ready(function () {

        var countPosts = $('#stm-events-league-ajax-content-future').data('posts');

        $('select[name="events_league_list_future"]').on('change', function () {
            var selObj = $(this).select2('data');
            setContentFuture(selObj[0].id, countPosts);
        });

        var selObjDef = $('select[name="events_league_list_future"]').select2('data');
        setContentFuture(selObjDef[0].id, countPosts);
    });

    function setContentFuture(id, countPosts) {
        $('#stm-events-league-ajax-content-future').empty();
        $('#stm-events-league-ajax-content-future').addClass('loading');
        $.ajax({
            url: ajaxurl,
            dataType: 'json',
            context: this,
            data: {
                action: 'stm_events_league_table_by_id',
                league_id: id,
                count: countPosts,
                results_type: 'future',
                security: stm_events_league_table_by_id
            },
            success: function (data) {
                $('#stm-events-league-ajax-content-future').removeClass('loading');
                if(data.table) {
                    $('#stm-events-league-ajax-content-future').html(data.table);
                    $('#stm-link-all-future').attr('href', data.link);
                }
            }
        });
    }
})(jQuery);
