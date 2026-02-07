(function ($) {
    $(document).ready(function() {
        $('.load-posts-more-style').on('click', function (e) {
            e.preventDefault();
            var viewStyle = $(this).data('view-style');
            var categs = ($(this).data('categs') != '') ? $(this).data('categs') : '';
            var offset = $(this).attr('data-offset');
            var limit = $(this).data('limit');
            var numColumns = $(this).data('num-columns');
            var generateClass = $(this).data('generate-class');
            console.log(offset);
            getPosts(viewStyle, categs, offset, limit, numColumns, generateClass);
        });
    });

    function getPosts(viewStyle, categs, offset, limit, numColumns, genClass) {
        $.ajax({
            url: ajaxurl,
            dataType: 'json',
            context: this,
	        method: 'get',
            data: {
                action: 'stm_posts_most_styles',
                viewStyle: viewStyle,
                categs: categs,
                offset: offset,
                limit: limit,
                numColumns: numColumns,
                security: stm_posts_most_styles
            },
            beforeSend: function(){
                $('.load-posts-more-style').addClass('loading');
            },
            success: function (data) {
                $('.load-posts-more-style').removeClass('loading');

                if(data.offset !== 'none') {
	                offset = $('body').find('.load-posts-more-style').attr('data-offset');
                    $('body').find('.load-posts-more-style').attr('data-offset', data.offset);
                } else {
	                $('body').find('.load-posts-more-style').remove();
                }

                if(data.posts) {
                    $('.' + genClass).append(data.posts);
                }
            }
        });
    }
})(jQuery);