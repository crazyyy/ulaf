/**
	* @author    Dan Fisher
	* @package   Alchemists
	* @since     1.0.0
	* @version   4.4.16
	*
	* Template Scripts
*/

;(function($){
	"use strict";

	$(function() {
		$('#js-preloader').delay(0).fadeOut();
		$('#js-preloader-overlay').delay(200).fadeOut('slow');
	});

	$.fn.exists = function () {
		return this.length > 0;
	};

	/* ----------------------------------------------------------- */
	/*  Predefined Variables
	/* ----------------------------------------------------------- */
	var color_primary        = alchemistsData.color_primary;
	var main_nav             = $('.main-nav');
	var secondary_nav        = $('.secondary-nav');
	var circular_bar         = $('.circular__bar');
	var mp_single            = $('.mp_single-img');
	var mp_gallery           = $('.mp_gallery');
	var mp_iframe            = $('.mp_iframe');
	var post_fitRows         = $('.post-grid--fitRows');
	var post_masonry         = $('.post-grid--masonry');
	var post_masonry_filter  = $('.post-grid--masonry-filter');
	var team_album           = $('.album');
	var slick_player_info    = $('.player-info');
	var slick_product        = $('.product__slider');
	var content_filter       = $('.content-filter');
	var marquee              = $('.marquee');

	// Language direction
	var rtl = $('html').attr('dir') === 'rtl' ? true : false;

	var template_var = 'template-basketball';

	if ( $('body').hasClass('template-soccer') ) {
		template_var = 'template-soccer';
	} else if ( $('body').hasClass('template-football') ) {
		template_var = 'template-football';
	} else if ( $('body').hasClass('template-esports') ) {
		template_var = 'template-esports';
	} else {
		template_var = 'template-basketball';
	}

	var Core = {

		initialize: function() {

			this.SvgPolyfill();

			this.headerNav();

			this.headerSecondaryNav();

			this.circularBar();

			this.MagnificPopup();

			this.isotope();

			this.SlickCarousel();

			this.SlickDataInit();

			this.ContentFilter();

			this.miscScripts();

		},

		SvgPolyfill: function() {
			svg4everybody();
		},

		headerNav: function() {

			if ( main_nav.exists() ) {

				var top_nav             = $('.nav-account');
				var top_nav_li          = $('.nav-account > li');
				var social              = $('.social-links--main-nav');
				var info_nav_li_onclick = $('.js-info-block__item--onclick');
				var wrapper             = $('.site-wrapper');
				var nav_list            = $('.main-nav__list');
				var nav_list_li         = $('.main-nav__list > li');
				var toggle_btn          = $('#header-mobile__toggle');
				var pushy_btn           = $('.pushy-panel__toggle');
				var pushy_btn_mobile    = $('.header-mobile-pushy-panel__toggle');
				var header_banner       = $('.header-banner');

				// Clone Search Form
				var header_search_form = $('.header-search-form').clone();
				$('#header-mobile').append( header_search_form );

				// Clone Shopping Cart to Mobile Menu
				var shop_cart = $('.info-block__item--shopping-cart > .info-block__link-wrapper').clone();
				shop_cart.appendTo(nav_list).wrap('<li class="main-nav__item--shopping-cart"></li>');

				// Add arrow and class if Top Bar menu ite has submenu
				top_nav_li.has('.main-nav__sub-0').addClass('has-children');
				top_nav_li.has('.main-nav__sub-0').prepend('<span class="main-nav__toggle"></span>');

				// Clone Top Bar menu to Main Menu
				if ( top_nav.exists() ) {
					var children = top_nav.children().clone();
					nav_list.append(children);
				}

				// Clone Header Logo to Mobile Menu
				var logo_mobile = $('.header-mobile__logo').clone();
				nav_list.prepend(logo_mobile);
				logo_mobile.prepend('<span class="main-nav__back"></span>');

				// Clone Header Info to Mobile Menu
				var header_info1 = $('.info-block__item--contact-primary').clone();
				var header_info2 = $('.info-block__item--contact-secondary').clone();
				nav_list.append(header_info1).append(header_info2);

				// Add icon if Shopping Heading triggered on click
				if ( info_nav_li_onclick.exists() ){
					info_nav_li_onclick.prepend('<span class="info-block__menu-icon"></span>');
					info_nav_li_onclick.on('click', '.info-block__menu-icon', function() {
						$(this).toggleClass('active');
					});
				}

				// Clone Social Links to Main Menu
				if ( social.exists() ) {
					var social_li = social.children().clone();
					var social_li_new = social_li.contents().unwrap();
					social_li_new.appendTo(nav_list).wrapAll('<li class="main-nav__item--social-links"></li>');
				}

				// Clone Header Banner to Mobile Menu
				if ( header_banner.exists() ) {
					var header_banner_copy = header_banner.clone();
					nav_list.append( header_banner_copy );
				}

				// Mobile Menu Toggle
				toggle_btn.on('click', function(e){
					e.preventDefault();
					wrapper.toggleClass('site-wrapper--has-overlay');
				});

				$('.site-overlay, .main-nav__back').on('click', function(){
					wrapper.toggleClass('site-wrapper--has-overlay');
				});

				// Pushy Panel Toggle
				pushy_btn.on('click', function(e){
					e.preventDefault();
					wrapper.toggleClass('site-wrapper--has-overlay-pushy');
				});

				// Pushy Panel Toggle Mobile
				pushy_btn_mobile.on('click', function(e){
					e.preventDefault();
					wrapper.toggleClass('site-wrapper--has-overlay-pushy');
				});

				// Remove overlay state
				$('.site-overlay, .pushy-panel__back-btn').on('click', function(e){
					e.preventDefault();
					wrapper.removeClass('site-wrapper--has-overlay-pushy site-wrapper--has-overlay');
				});

				// Add toggle button and class if menu has submenu
				nav_list_li.has('.main-nav__sub-0').prepend('<span class="main-nav__toggle"></span>');
				nav_list_li.has('.main-nav__megamenu').prepend('<span class="main-nav__toggle"></span>');

				$('.main-nav__toggle').on('click', function(){
					$(this).toggleClass('main-nav__toggle--rotate')
					.parent().siblings().children().removeClass('main-nav__toggle--rotate');

					$(".main-nav__sub-0, .main-nav__megamenu").not($(this).siblings('.main-nav__sub-0, .main-nav__megamenu')).slideUp('normal');
					$(this).siblings('.main-nav__sub-0').slideToggle('normal');
					$(this).siblings('.main-nav__megamenu').slideToggle('normal');
				});

				// Add toggle button and class if submenu has sub-submenu
				$('.main-nav__list > li > ul > li').has('.main-nav__sub-1').prepend('<span class="main-nav__toggle-2"></span>');
				$('.main-nav__list > li > ul > li > ul > li').has('.main-nav__sub-2').prepend('<span class="main-nav__toggle-2"></span>');

				$('.main-nav__toggle-2').on('click', function(){
					$(this).toggleClass('main-nav__toggle--rotate');
					$(this).siblings('.main-nav__sub-1').slideToggle('normal');
					$(this).siblings('.main-nav__sub-2').slideToggle('normal');
				});

				// Mobile Search
				$('#header-mobile__search-icon').on('click', function(){
					$(this).toggleClass('header-mobile__search-icon--close');
					$('.header-mobile').toggleClass('header-mobile--expanded');
				});
			}
		},

		headerSecondaryNav: function() {

			if ( secondary_nav.exists() ) {

				var nav_sec_list        = $('.secondary-nav__list');
				var nav_sec_list_li     = $('.secondary-nav__list > li');
				var toggle_btn_sec      = $('#secondary-nav__toggle');

				// Mobile Menu Toggle
				toggle_btn_sec.on('click', function(e){
					e.preventDefault();
					$(this).toggleClass('secondary-nav__toggle--rotate');
					nav_sec_list.slideToggle('fast');
				});

				// Add toggle button and class if menu has submenu
				nav_sec_list_li.has('.main-nav__sub-0').prepend('<span class="main-nav__toggle"></span>');

				$('.secondary-nav__list .main-nav__toggle').on('click', function(){
					$(this).toggleClass('main-nav__toggle--rotate')
					.parent().siblings().children().removeClass('main-nav__toggle--rotate');

					$(".secondary-nav__list .main-nav__sub-0").not($(this).siblings('.main-nav__sub-0')).slideUp('normal');
					$(this).siblings('.main-nav__sub-0').slideToggle('normal');
				});

				// Add toggle button and class if submenu has sub-submenu
				$('.secondary-nav__list > li > ul > li').has('.main-nav__sub-1').prepend('<span class="main-nav__toggle-2"></span>');
				$('.secondary-nav__list > li > ul > li > ul > li').has('.main-nav__sub-2').prepend('<span class="main-nav__toggle-2"></span>');

				$('.secondary-nav__list .main-nav__toggle-2').on('click', function(){
					$(this).toggleClass('main-nav__toggle--rotate');
					$(this).siblings('.main-nav__sub-1').slideToggle('normal');
					$(this).siblings('.main-nav__sub-2').slideToggle('normal');
				});
			}
		},

		circularBar: function() {

			var track_color = alchemistsData.track_color;
			var track_line_cap = 'square';

			if ( template_var === 'template-esports' ) {
				track_line_cap = 'round';
			}

			if ( circular_bar.exists() ) {
				circular_bar.easyPieChart({
					barColor: color_primary,
					trackColor: track_color,
					lineCap: track_line_cap,
					lineWidth: 8,
					size: 90,
					scaleLength: 0
				});
			}

		},

		MagnificPopup: function(){

			if (mp_single.exists() ) {
				// Single Image
				$('.mp_single-img').magnificPopup({
					type:'image',
					removalDelay: 300,

					gallery:{
						enabled:false
					},
					mainClass: 'mfp-fade',
					autoFocusLast: false,

				});
			}

			if (mp_gallery.exists() ) {
				// Multiple Images (gallery)
				$('.mp_gallery').magnificPopup({
					type:'image',
					removalDelay: 300,

					gallery:{
						enabled:true
					},
					mainClass: 'mfp-fade',
					autoFocusLast: false,

				});
			}

			if (mp_iframe.exists() ) {
				// Iframe (video, maps)

				function extendMagnificIframe(){

					var $start = 0;
					var $iframe = {
							markup: '<div class="mfp-iframe-scaler">' +
											'<div class="mfp-close"></div>' +
											'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
											'</div>' +
											'<div class="mfp-bottom-bar">' +
											'<div class="mfp-title"></div>' +
											'</div>',
							patterns: {
								youtube: {
									index: 'youtu',
									id: function(url) {

										var m = url.match( /^.*(?:youtu.be\/|v\/|e\/|u\/\w+\/|embed\/|v=)([^#\&\?]*).*/ );
										if ( !m || !m[1] ) return null;
											if (url.indexOf('t=') != - 1){
												var $split = url.split('t=');
												var hms = $split[1].replace('h',':').replace('m',':').replace('s','');
												var a = hms.split(':');
												if (a.length == 1){
													$start = a[0];
												} else if (a.length == 2){
													$start = (+a[0]) * 60 + (+a[1]);
												} else if (a.length == 3){
													$start = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);
												}
											}

											var suffix = '?autoplay=1';
											if ( $start > 0 ){
												suffix = '?start=' + $start + '&autoplay=1';
											}

										return m[1] + suffix;
									},
									src: '//www.youtube.com/embed/%id%'
								},
								vimeo: {
									index: 'vimeo.com/',
									id: function(url) {
										var m = url.match(/(https?:\/\/)?(www.)?(player.)?vimeo.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/);
										if ( !m || !m[5] ) return null;
										return m[5];
									},
									src: '//player.vimeo.com/video/%id%?autoplay=1'
								},
								gmaps: {
									index: '//maps.google.',
									src: '%id%&output=embed'
								}
							}
					};

					return $iframe;

			}

				$('.mp_iframe').magnificPopup({
					type: 'iframe',
					removalDelay: 300,
					mainClass: 'mfp-fade',
					autoFocusLast: false,
					iframe: extendMagnificIframe()

				});
			}
		},


		isotope: function() {

			if ( post_fitRows.exists() ) {
				var $grid = post_fitRows.imagesLoaded( function() {
					// init Isotope after all images have loaded
					$grid.isotope({
						itemSelector: '.post-grid__item',
						layoutMode: 'fitRows',
						masonry: {
							columnWidth: '.post-grid__item'
						}
					});
				});
			}

			if ( post_masonry.exists() ) {
				var $masonry = post_masonry.imagesLoaded( function() {
					// init Isotope after all images have loaded
					$masonry.isotope({
						itemSelector: '.post-grid__item',
						layoutMode: 'masonry',
						percentPosition: true,
						masonry: {
							columnWidth: '.post-grid__item'
						}
					});
				});
			}

			if ( team_album.exists() ) {
				var masonry_album = team_album.imagesLoaded( function() {
					// init Isotope after all images have loaded
					masonry_album.isotope({
						itemSelector: '.album__item',
						layoutMode: 'masonry',
						percentPosition: true,
						masonry: {
							columnWidth: '.album__item'
						}
					});
				});
			}


			if ( post_masonry_filter.exists() ) {
				var $masonry_grid = post_masonry_filter.imagesLoaded( function() {

					var $filter = $('.js-category-filter');

					// init Isotope after all images have loaded
					$masonry_grid.isotope({
						filter      : '*',
						itemSelector: '.post-grid__item',
						layoutMode: 'masonry',
						masonry: {
							columnWidth: '.post-grid__item'
						}
					});

					// filter items on button click
					$filter.on( 'click', 'button', function() {
						var filterValue = $(this).attr('data-filter');
						$filter.find('button').removeClass('category-filter__link--active');
						$(this).addClass('category-filter__link--active');
						$masonry_grid.isotope({
							filter: filterValue
						});
					});
				});
			}

		},


		SlickCarousel: function() {

			// Player Info
			if ( slick_player_info.exists() ) {

				slick_player_info.slick({
					slidesToShow: 3,
					arrows: false,
					dots: false,
					infinite: false,
					variableWidth: true,
					rtl: rtl,
					rows: 0,
					responsive: [
						{
							breakpoint: 992,
							settings: {
								slidesToShow: 1,
								dots: true,
								variableWidth: false
							}
						}
					]
				});
			}


			// Products Slider
			if ( slick_product.exists() ) {

				slick_product.slick({
					slidesToShow: 1,
					arrows: false,
					dots: true,
					rtl: rtl,
					rows: 0,
				});
			}

		},


		SlickDataInit: function() {

			// Init for all slick carousels using data-slick attribute
			$('[data-slick]').slick();

		},


		ContentFilter: function() {

			if ( content_filter.exists() ) {
				$('.content-filter__toggle').on('click', function(e){
					e.preventDefault();
					$(this).toggleClass('content-filter__toggle--active');
					$('.content-filter__list').toggleClass('content-filter__list--expanded');
				});
			}

		},


		miscScripts: function() {
			// Tooltips
			$('[data-toggle="tooltip"]').tooltip();

			[].slice.call( document.querySelectorAll( 'select.cs-select' ) ).forEach( function(el) {
				new SelectFx(el);
			} );

			// Marquee
			if ( marquee.exists() ) {
				var speed = marquee.data('speed');
				var pauseOnHover = marquee.data('pauseOnHover');
				var startVisible = marquee.data('startVisible');

				marquee.marquee({
					allowCss3Support: true,
					speed: speed,
					pauseOnHover: pauseOnHover,
					startVisible: startVisible
				});
			}

			// Switch Toggle
			$('.widget-game-result').each(function() {
				var $this = $(this);

				if ( $this.find('.alc-switch-toggle').exists() ) {
					var txtHolder = $this.find('.switch__label-txt');
					var txtExpand = txtHolder.data('text-expand');
					var txtShrink = txtHolder.data('text-shrink');

					$this.on('change', function(){
						$this.find('.widget-game-result__extra-stats').toggleClass('active');
						txtHolder.text(function(i, text){
							return text === txtShrink ? txtExpand : txtShrink;
						});
					});
				}
			});

			// Add theme class to select element for Categories and Archives widgets
			$('.widget_categories .postform, .widget_archive select[name="archive-dropdown"]').addClass('form-control');


			$.fn.firstWord = function( elClass ) {
				var text = this.text().trim().split(" ");
				var first = text.shift();
				this.html((text.length > 0 ? "<span class='" + elClass + "'>" + first + "</span> " : first) + text.join(" "));
			};

			$.fn.lastWord = function( elClass ) {
				var text = this.text().trim().split(" ");
				var last = text.pop();
				this.html(text.join(" ") + "<span class='" + elClass + "'>" + last + "</span>");
			};

			// Highlight the last name on Single Player page
			$('.player-info__name').each(function () {
				if ($(this).find("span").length > 0) {
					$(this).find("span").addClass('player-info__last-name');
				} else {
					$(this).lastWord( 'player-info__last-name' );
				}
			});

			// Highlight the last name on Single Player page
			$('.team-roster__member-name').each(function(){
				if ($(this).find("span").length > 0) {
					$(this).find("span").addClass('team-roster__member-last-name');
				} else {
					$(this).lastWord( 'team-roster__member-last-name' );
				}
			});

			// Highlight the last name for Featured Player Widget
			$(".widget-player__name").each(function () {
				if ($(this).find("span").length > 0) {
					$(this).find("span").addClass("widget-player__last-name");
				} else {
					$(this).lastWord( 'widget-player__last-name' );
				}
			});

			// Highlight the last name on Roster Carousel
			$('.team-roster__player-name').each(function () {
				if ($(this).find("span").length > 0) {
					$(this).find("span").addClass('team-roster__player-last-name');
				} else {
					$(this).lastWord( 'team-roster__player-last-name' );
				}
			});

			// Highlight the last name of Staff member
			$('.alc-staff__header-name').each(function () {
				if ($(this).find("span").length > 0) {
					$(this).find("span").addClass('alc-staff__header-last-name');
				} else {
					$(this).lastWord( 'alc-staff__header-last-name' );
				}
			});

			// Highlight the first word on Single Player page circular bar
			$('.player-info-stats__item .circular__label').each(function () {
				$(this).firstWord( 'circular__label-first' );
			});

			$('[data-toggle="tooltip"]').on('click', function () {
				$(this).blur();
			});

			// Duotone effect
			$('.effect-duotone').prepend('<div class="effect-duotone__layer"><div class="effect-duotone__layer-inner"></div></div>');

		},

	};

	$(function(){
		Core.initialize();
	});

})(jQuery);
