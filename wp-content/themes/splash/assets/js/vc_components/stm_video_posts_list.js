(function($) {
    $(document).ready(function () {
        $('body').on("click", '#play-video', function (e) {
            e.preventDefault();
            var id = $(this).attr('data-src');
            var src = 'https://www.youtube.com/embed/'+id;
            $("#video-post-frame").attr('src', src);
            $("#video-post-frame")[0].src += "?autoplay=1";
            $('.video-frame').addClass('played');

            return false;
        });
    });
})(jQuery);