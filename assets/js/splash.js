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
      'min-height': siteMinHeight + 'px'
    });

    $('body').css({
      'padding-bottom': footerH + 'px'
    });

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
