!function(){for(var e,t=function(){},i=["assert","clear","count","debug","dir","dirxml","error","exception","group","groupCollapsed","groupEnd","info","log","markTimeline","profile","profileEnd","table","time","timeEnd","timeline","timelineEnd","timeStamp","trace","warn"],s=i.length,n=window.console=window.console||{};s--;)e=i[s],n[e]||(n[e]=t)}(),"undefined"==typeof jQuery?console.warn("jQuery hasn't loaded"):console.log("jQuery has loaded"),function(e){"use strict";function t(){e(window).outerHeight(),e(".stm-footer").outerHeight();e("#wrapper").css({"min-height":"100%"})}function i(){e(".stm-medias-unit").length&&"function"==typeof imagesLoaded&&e(".stm-medias-unit").imagesLoaded(function(){e(".stm-medias-unit").isotope({itemSelector:".stm-media-single-unit",layoutMode:"masonry"})}),e(".stm-media-tabs-nav a").on("shown.bs.tab",function(t){var i=e(this).attr("href");e(i+" .stm-medias-unit").isotope("layout")})}function s(){var t=e(".widget_nav_menu"),i=e(".widget_categories"),s=e(".widget_recent_entries");t.length&&t.each(function(){0==e(this).closest(".footer-widgets-wrapper").length&&(e(this).addClass("stm-widget-menu"),e(this).find("a").each(function(){e(this).html("<span>"+e(this).text()+"</span>")}))}),i.length&&i.each(function(){e(this).find("a").each(function(){e(this).html("<span>"+e(this).text()+"</span>")})}),s.length&&s.each(function(){e(this).find(".post-date").length||e(this).addClass("no-date")})}function n(){var t=e(window).outerWidth(),i=e(".stm-fullwidth-row-js").find(".container").width(),s=(t-i)/2;e(".stm-fullwidth-row-js").css({"margin-left":-s+"px","margin-right":-s+"px"})}e(document).ready(function(){t(),n(),s(),i()}),e(window).load(function(){t(),n()}),e(window).resize(function(){t(),n()})}(jQuery),function(e){"use strict";function t(){if(!s.hasClass("stm-transparent-header")&&s.hasClass("stm-header-fixed-mode")){var e=s.find(".stm-header-inner").outerHeight();s.css("min-height",e+"px")}}function i(){if(s.hasClass("stm-transparent-header")&&s.hasClass("stm-header-fixed-mode")){var t=e(window).scrollTop(),i=s.offset().top;t-300>i?s.addClass("stm-header-fixed"):s.removeClass("stm-header-fixed"),t-400>i?s.addClass("stm-header-fixed-intermediate"):s.removeClass("stm-header-fixed-intermediate")}if(!s.hasClass("stm-transparent-header")&&s.hasClass("stm-header-fixed-mode")){var t=e(window).scrollTop(),i=s.offset().top;t-300>i?s.addClass("stm-header-fixed"):s.removeClass("stm-header-fixed"),t-400>i?s.addClass("stm-header-fixed-intermediate"):s.removeClass("stm-header-fixed-intermediate")}}var s=e(".stm-header");e(document).ready(function(){i(),t()}),e(window).load(function(){i(),t()}),e(window).resize(function(){i()}),e(window).scroll(function(){i()})}(jQuery),$(".grid").isotope({itemSelector:".grid-item",layoutMode:"fitRows",filter:".game-info-2017"}),$(".filters-select").on("change",function(){var e=this.value;$(".grid").isotope({filter:e}),$(".grid-item").show()}),$(".grid-2").isotope({itemSelector:".grid-item-2",layoutMode:"fitRows",filter:".team-table-position-2017"}),$(".filters-select").on("change",function(){var e=this.value;$(".grid-2").isotope({filter:e}),$(".grid-item-2").show()}),function(){$(".owl-carousel").owlCarousel({loop:!0,nav:!1,items:3,autoplay:!0,autoplayTimeout:2e3,responsive:{1200:{items:3},500:{items:1},400:{items:1},300:{items:1}}})}(),function(){var e=function(){var e=$(".js-trigger"),t=$(".js-hidden"),i=$(".js-trigger-1"),s=$(".js-hidden-1");e.on("click",function(){t.slideToggle()}),i.on("click",function(){s.slideToggle()})};e()}(),function(){$(".tab-title>a").click(function(e){e.preventDefault();var t=$(this).parent().index();$(this).parent().addClass("active").siblings().removeClass("active").parent("ul.tabs").siblings(".tabs-content").children(".content").removeClass("active").eq(t).addClass("active")})}(),$(function(){$(window).scroll(function(){$(window).scrollTop()>100?$(".main_h").addClass("sticky"):$(".main_h").removeClass("sticky")})});
//# sourceMappingURL=maps/scripts.js.map
