(function ($) {
	$(document).ready(function () {

		if ($('.stm-players-tabs').hasClass('style_1')) {
			$('.player-carousel').owlCarousel({
				items: 4,
				autoplay: false,
				slideBy: 1,
				nav:true,
				loop: true,
				margin: 30,
				responsive:{
					0:{
						items:1
					},
					767:{
						items:2
					},
					900:{
						items:3
					},
					1200:{
						items:4
					}
				}
			});
			$('.stm-players-tabs .slider-navs .prev').on('click', function (e) {
				e.preventDefault();
				var count = $('.stm-players-tabs').attr('data-count');
				$('.player-carousel .owl-prev').trigger('click');
				setTimeout(function () {
					var currentSlide = $('.player-info-wrap.active').attr('data-slide');
					if(currentSlide == 1){
						$('.owl-item.active .player-slide-thumb-' + count).trigger('click');
					}
					else {
						$('.owl-item.active .player-slide-thumb-' + (parseInt(currentSlide) - 1)).trigger('click');
					}
				}, 50)

			});
			$('.stm-players-tabs .slider-navs .next').on('click', function (e) {
				e.preventDefault();
				var count = $('.stm-players-tabs').attr('data-count');
				$('.player-carousel .owl-next').trigger('click');
				setTimeout(function () {
					var currentSlide = $('.player-info-wrap.active').attr('data-slide');

					if(currentSlide == $('.player-info-wrap').length){
						$('.owl-item.active .player-slide-thumb-1').trigger('click');
					}
					else {
						$('.owl-item.active .player-slide-thumb-' + (parseInt(currentSlide) + 1)).trigger('click');
					}
				}, 50)

			});
		} else if ($('.stm-players-tabs').hasClass('style_2')) {
			var carouselClass = '.stm-players-tabs.style_2 .stm-players-info';
			function startStmPlayersTabsCarousel() {
				$(carouselClass).owlCarousel({
					items: 1,
					autoplay: false,
					nav: false,
					margin: 0
				});
			}
			function stopStmPlayersTabsCarousel() {
				var owl = $(carouselClass);
				owl.trigger('destroy.owl.carousel').removeClass('owl-carousel owl-loaded');
				owl.find('.owl-stage-outer').children().unwrap();
			}
			if ( $(window).width() < 1023 ) {
				startStmPlayersTabsCarousel();
			}

			$(window).resize(function() {
				if ( $(window).width() < 1023 ) {
					startStmPlayersTabsCarousel();
				} else {
					stopStmPlayersTabsCarousel();
				}
			});			
		}
		$('.player-carousel__item a').on('click', function (e) {
			e.preventDefault();
			$('.player-carousel__item a').removeClass('active');
			$(this).addClass('active');
			var activeSlide = $(this).attr('data-slide');
			$('.player-info-wrap').removeClass('active');
			$('.player-slide-' + activeSlide).addClass('active');
		});
	});
})(jQuery);