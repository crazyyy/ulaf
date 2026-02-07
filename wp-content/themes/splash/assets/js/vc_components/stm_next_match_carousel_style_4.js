(function ($) {
    $(document).ready(function () {
        $('.stm-next-match-carousel-wrap.style_4 .stm-next-match-carousel2').owlCarousel({
            items: 2,
            autoplay: false,
            margin: 0,
            nav: false,
            dots: false,
            loop: true,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 2
                }
            }
        });
    });
})(jQuery);