// Avoid `console` errors in browsers that lack a console.
(function() {
  var method;
  var noop = function() {};
  var methods = ['assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error', 'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd', 'timeline', 'timelineEnd', 'timeStamp', 'trace', 'warn'];
  var length = methods.length;
  var console = (window.console = window.console || {});

  while (length--) {
    method = methods[length];

    // Only stub undefined methods.
    if (!console[method]) {
      console[method] = noop;
    }
  }
}());
if (typeof jQuery === 'undefined') {
  console.warn('jQuery hasn\'t loaded');
} else {
  console.log('jQuery has loaded');
}
// Place any jQuery/helper plugins in here.

(function($) {
  "use strict";

  /*Ready DOM scripts*/
  $(document).ready(function() {
    stm_sticky_footer();

    stmFullwidthRowJs();

    default_widgets_scripts();

    stm_sort_media();

  });

  /*Window load scripts*/

  $(window).load(function() {
    stm_sticky_footer();

    stmFullwidthRowJs();
  });

  /*Window resize scripts*/
  $(window).resize(function() {
    stm_sticky_footer();

    stmFullwidthRowJs();
  });


  /*CUSTOM FUNCTIONS*/
  /*Sticky footer*/
  function stm_sticky_footer() {
    var winH = $(window).outerHeight();
    var footerH = $('.stm-footer').outerHeight();
    var siteMinHeight = winH - footerH;

    $('#wrapper').css({
      'min-height': '100%'
      // 'min-height': siteMinHeight + 'px'
    });

    // $('body').css({
    //   'padding-bottom': footerH + 'px'
    // });

  }

  function stm_sort_media() {
    // init Isotope
    if ($('.stm-medias-unit').length) {
      if (typeof imagesLoaded == 'function') {
        $('.stm-medias-unit').imagesLoaded(function() {
          $('.stm-medias-unit').isotope({
            itemSelector: '.stm-media-single-unit',
            layoutMode: 'masonry'
          });
        });
      }
    }

    $('.stm-media-tabs-nav a').on('shown.bs.tab', function(e) {
      var tabId = $(this).attr('href');
      $(tabId + ' .stm-medias-unit').isotope('layout');
    })
  }

  function default_widgets_scripts() {
    var stm_menu_widget = $('.widget_nav_menu');
    var stm_categories_widget = $('.widget_categories');
    var stm_recent_entries = $('.widget_recent_entries');

    if (stm_menu_widget.length) {
      stm_menu_widget.each(function() {
        if ($(this).closest('.footer-widgets-wrapper').length == 0) {
          $(this).addClass('stm-widget-menu');
          $(this).find('a').each(function() {
            $(this).html('<span>' + $(this).text() + '</span>');
          });
        }
      });
    }

    if (stm_categories_widget.length) {
      stm_categories_widget.each(function() {
        $(this).find('a').each(function() {
          $(this).html('<span>' + $(this).text() + '</span>');
        });
      });
    }

    if (stm_recent_entries.length) {
      stm_recent_entries.each(function() {
        if (!$(this).find('.post-date').length) {
          $(this).addClass('no-date');
        }
      });
    }
  }

  function stmFullwidthRowJs() {
    var winW = $(window).outerWidth();
    var contW = $('.stm-fullwidth-row-js').find('.container').width();
    var contMargins = (winW - contW) / 2;
    $('.stm-fullwidth-row-js').css({
      'margin-left': -contMargins + 'px',
      'margin-right': -contMargins + 'px'
    })
  }

})(jQuery);

/*  header.js*/
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
/* // header.js*/

/* MIsha`s work */
//isotope results///////////////////////
$('.grid').isotope({
  // options
  itemSelector: '.grid-item',
  layoutMode: 'fitRows',
  filter: '.game-info-2017'
});

$('.filters-select').on('change', function() {
  // get filter value from option value
  var filterValue = this.value;
  $('.grid').isotope({
    filter: filterValue
  });
  $('.grid-item').show();
});

///////isotope table/////////////
$('.grid-2').isotope({
  // options
  itemSelector: '.grid-item-2',
  layoutMode: 'fitRows',
  filter: '.team-table-position-2017'
});

$('.filters-select').on('change', function() {
  // get filter value from option value
  var filterValue = this.value;
  $('.grid-2').isotope({
    filter: filterValue
  });
  $('.grid-item-2').show();
});
/////////////////////////////////////

// owlCarousel//////////////////////
(function() {
  $('.owl-carousel').owlCarousel({
    loop: true,
    nav: false,
    items: 3,
    autoplay: true,
    autoplayTimeout: 2000,
    responsive:{
        1200:{
          items:3
        },
        500:{
            items:1
        },
        400:{
            items:1
        },
        300:{
            items:1
        }
      }
  });
}());

////////////slide toggle/////////////
(function() {
  var slideDown = function() {
    var $trigger = $('.js-trigger'),
      $hidden = $('.js-hidden'),
      $trigger1 = $('.js-trigger-1'),
      $hidden1 = $('.js-hidden-1');

    $trigger.on('click', function() {
      $hidden.slideToggle();
    })
    $trigger1.on('click', function() {
      $hidden1.slideToggle();
    })

  }
  slideDown();
}());
////////////////////////////////////

/* // MIsha`s work */
