(function ($) {
    $(window).on('load', function () {
        $('.stm_latest_tweets.style_carousel .stm-latest-tweets .ctf-tweets').each(function () {
            var carousel = $(this);
            carousel.addClass('owl-carousel');
            setTimeout(function () {
                carousel.owlCarousel({
                    items: 1,
                    dots: true,
                    arrows: false
                });
            }, 550)
        });
    });
})(jQuery);
