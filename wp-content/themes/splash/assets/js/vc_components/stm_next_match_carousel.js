(function ($) {
	$(document).ready(function () {
		if($(window).width() > 1900){
			$('.stm-next-match-carousel').owlCarousel({
				items: 4,
				autoplay: false,
				slideBy: 3,
				nav:true,
				autoWidth: true
			});
		}
		else {
			$('.stm-next-match-carousel').owlCarousel({
				items: 5,
				autoplay: false,
				slideBy: 1,
				nav:true,
				autoWidth: true,
				loop: true,
				responsive:{
					0:{
						items:2
					},
					900:{
						items:3
					},
					1200:{
						items:5
					}
				}
			});
		}

	});
})(jQuery);