(function ($) {
    $(document).ready(function () {

        var countPosts = $('#stm-events-league-ajax-content-publish').data('posts');

        $('select[name="events_league_list_publish"]').on('change', function () {
            var selObj = $(this).select2('data');
            setContent(selObj[0].id, countPosts);
        });

        var selObjDef = $('select[name="events_league_list_publish"]').select2('data');
        setContent(selObjDef[0].id, countPosts);
    });

    function setContent(id, countPosts) {
        $('#stm-events-league-ajax-content-publish').empty();
        $('#stm-events-league-ajax-content-publish').addClass('loading');

        $.ajax({
            url: ajaxurl,
            dataType: 'json',
            context: this,
            data: {
                action: 'stm_events_league_table_by_id',
                league_id: id,
                count: countPosts,
                results_type: 'publish',
                security: stm_events_league_table_by_id
            },
            success: function (data) {
                if(data.table) {
                    $('#stm-events-league-ajax-content-publish').removeClass('loading');
                    $('#stm-events-league-ajax-content-publish').html(data.table);
                    $('#stm-link-all-publish').attr('href', data.link);
                }
            }
        });
    }
})(jQuery);