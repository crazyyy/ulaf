!function(e){"use strict";function s(){if(!t.hasClass("stm-transparent-header")&&t.hasClass("stm-header-fixed-mode")){var e=t.find(".stm-header-inner").outerHeight();t.css("min-height",e+"px")}}function a(){if(t.hasClass("stm-transparent-header")&&t.hasClass("stm-header-fixed-mode")){var s=e(window).scrollTop(),a=t.offset().top;s-300>a?t.addClass("stm-header-fixed"):t.removeClass("stm-header-fixed"),s-400>a?t.addClass("stm-header-fixed-intermediate"):t.removeClass("stm-header-fixed-intermediate")}if(!t.hasClass("stm-transparent-header")&&t.hasClass("stm-header-fixed-mode")){var s=e(window).scrollTop(),a=t.offset().top;s-300>a?t.addClass("stm-header-fixed"):t.removeClass("stm-header-fixed"),s-400>a?t.addClass("stm-header-fixed-intermediate"):t.removeClass("stm-header-fixed-intermediate")}}var t=e(".stm-header");e(document).ready(function(){a(),s()}),e(window).load(function(){a(),s()}),e(window).resize(function(){a()}),e(window).scroll(function(){a()}),e(".grid").isotope({itemSelector:".grid-item",layoutMode:"fitRows"}),e(".filters-select").on("change",function(){var s=this.value;e(".grid").isotope({filter:s}),e(".grid-item").show()}),function(){e(".slider").owlCarousel({loop:!0,nav:!1,items:3,autoplay:!0,autoplayTimeout:2e3})}()}(jQuery);
//# sourceMappingURL=maps/header.js.map
