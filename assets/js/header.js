(function($) {
  "use strict";

  var $stm_header = $('.stm-header');

  $(document).ready(function() {
    stm_header_transparent();

    stm_header_height();
  });

  $(window).load(function() {
    stm_header_transparent();

    stm_header_height();
  });

  $(window).resize(function() {
    stm_header_transparent();
  });

  $(window).scroll(function() {
    stm_header_transparent();
  });

  function stm_header_height() {
    if (!$stm_header.hasClass('stm-transparent-header') && $stm_header.hasClass('stm-header-fixed-mode')) {
      var headerH = $stm_header.find('.stm-header-inner').outerHeight();

      $stm_header.css('min-height', headerH + 'px');
    }
  }
  //isotope///////////////////////
  $('.grid').isotope({

  // options
  itemSelector: '.grid-item',
  layoutMode: 'fitRows'
});
$('.filters-select').on( 'change', function() {
  // get filter value from option value
  var filterValue = this.value;
  $('.grid').isotope({ filter: filterValue });
  $('.grid-item').show();
});
 // owlCarousel//////////////////////
  (function() {
    $('.slider').owlCarousel({
    loop:true,
    nav: false,
    items: 3,
    autoplay:true,
    autoplayTimeout:2000
    });
  }());

  ////////////////////////////////////
  function stm_header_transparent() {
    /*HEADER TRANSPARENT FIXED*/
    if ($stm_header.hasClass('stm-transparent-header') && $stm_header.hasClass('stm-header-fixed-mode')) {
      var currentScrollPos = $(window).scrollTop();
      var headerPos = $stm_header.offset().top;

      if (currentScrollPos - 300 > headerPos) {
        $stm_header.addClass('stm-header-fixed');
      } else {
        $stm_header.removeClass('stm-header-fixed');
      }

      if (currentScrollPos - 400 > headerPos) {
        $stm_header.addClass('stm-header-fixed-intermediate');
      } else {
        $stm_header.removeClass('stm-header-fixed-intermediate');
      }
    }

    /*HEADER NON-TRANSPARENT FIXED*/
    if (!$stm_header.hasClass('stm-transparent-header') && $stm_header.hasClass('stm-header-fixed-mode')) {
      var currentScrollPos = $(window).scrollTop();
      var headerPos = $stm_header.offset().top;

      if (currentScrollPos - 300 > headerPos) {
        $stm_header.addClass('stm-header-fixed');
      } else {
        $stm_header.removeClass('stm-header-fixed');
      }

      if (currentScrollPos - 400 > headerPos) {
        $stm_header.addClass('stm-header-fixed-intermediate');
      } else {
        $stm_header.removeClass('stm-header-fixed-intermediate');
      }
    }
  }

})(jQuery);
