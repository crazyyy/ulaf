(function ($) {
	$(window).load(function () {
		if($(window).width() > 1900){
			$('.stm-video-carousel').owlCarousel({
				items: 4,
				autoplay: false,
				slideBy: 3,
				nav:true,
				autoWidth: true
			});
		}
		else {
			$('.stm-video-carousel').owlCarousel({
				items: 4,
				autoplay: false,
				slideBy: 3,
				nav:true,
				loop: true,
				responsive:{
					0:{
						items:1,
						slideBy: 1,
					},
					768:{
						items:2,
						slideBy: 2,
					},
					900:{
						items:3,
						slideBy: 3,
					},
					1200:{
						items:3
					}
				}
			});
		}

	});
})(jQuery);