(function ($) {
    $(document).ready(function () {
        var $stageP = (splash_slides.items >= 5) ? 230 : false;
        $('.stm_latest_results_carousel').owlCarousel({
            items: splash_slides.items,
            nav: false,
            dots: true,
            loop: true,
            autoplayHoverPause: false,
            paginationSpeed : 1000,
            slideSpeed : 5000,
            smartSpeed: 1000,
            singleItem:true,
            responsive: {
                0: {
                    items: 1
                },
                520: {
                    items: 2
                },
                1024: {
                    items: 3
                },
                1440: {
                    items: 4
                },
                1920: {
                    items: splash_slides.items,
                    stagePadding: $stageP,
                },
            }
        });
    });
})(jQuery);