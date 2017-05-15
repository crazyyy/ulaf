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
//////////////////////////////////////
///Isotope Ratings
///////////////////
$('.grid-3').isotope({
  // options
  itemSelector: '.grid-item-3',
  layoutMode: 'fitRows',
  filter: '.rating-division-a'
});

$('.filters-select').on('change', function() {
  // get filter value from option value
  var filterValue = this.value;
  $('.grid-3').isotope({
    filter: filterValue
  });
  $('.grid-item-3').show();
});
/////////////////////////////////////

// owlCarousel//////////////////////
(function() {
  $('.sponsors-block-slider--slider').owlCarousel({
    loop: true,
    nav: false,
    items: 3,
    autoplay: true,
    autoplayTimeout: 2000,
    responsive: {
      1200: {
        items: 3
      },
      500: {
        items: 1
      },
      400: {
        items: 1
      },
      300: {
        items: 1
      }
    }
  });
  $('.sponsors-block-slider--slider .owl-item').each(function(index, el) {
    var width = $(this).width();
    $(this).height(width);

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
///////////Jqeury TABS////////////
(function() {
  $('.tab-title>a').click(function(e) {
    e.preventDefault();
    var index = $(this).parent().index();
    $(this).parent().addClass('active')
      .siblings().removeClass('active')
      .parent('ul.tabs').siblings('.tabs-content').children('.content').removeClass('active')
      .eq(index).addClass('active');
  });
}())
//////////////////////////////////
///Sticky Header//////////
$(function() {
    $(window).scroll(function() {
      var winTop = $(window).scrollTop();
      if (winTop >= 10) {
        $(".stm-transparent-header .stm-header-inner").css({
          'background-color': 'black',
          top: '-25px'
        });
        $(".stm-non-transparent-header .stm-header-inner").css({
          top: '-25px'
        });

      } else {
        $(".stm-transparent-header .stm-header-inner").css({
          'background-color': 'transparent',
          'top': '50px'
        });
        $(".stm-non-transparent-header .stm-header-inner").css({
          'top': '50px'
        });
      }
    })
  }) //ready func.
  ///
  ///
  ///JS MAP UKRAINE//////////////////
  ///////////////////////////////////
AmCharts.makeChart("mapdiv", {
  /**
   * this tells amCharts it's a map
   */
  "type": "map",

  /**
   * create data provider object
   * map property is usually the same as the name of the map file.
   * getAreasFromMap indicates that amMap should read all the areas available
   * in the map data and treat them as they are included in your data provider.
   * in case you don't set it to true, all the areas except listed in data
   * provider will be treated as unlisted.
   */
  "dataProvider": {
    "map": "ukraineLow",
    "getAreasFromMap": true,
    "showDescriptionOnHover": true,
    "images": [{
        "type": "circle",
        "label": "Lviv",
        "latitude": 49.83,
        "longitude": 24,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/lvy'>Lions Lviv</a> "
      }, {
        "type": "circle",
        "label": "Odessa",
        "latitude": 46.29,
        "longitude": 30.43,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/piraty'>Pirates Odesa</a>"
      }, {
        "type": "circle",
        "label": "Vinnytsia",
        "latitude": 49.14,
        "longitude": 28.28,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/vinnitskie-volki'>Wolves Vinnytsia</a>"
      }, {
        "type": "circle",
        "label": "Kyiv",
        "latitude": 50.4,
        "longitude": 30.61,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/bandity'>Bandits Kyiv</a> <br><a href='http://ulafua.com/team/slavyani'> Slavs Kyiv </a> <br><a href='http://ulafua.com/team/patrioty'> Patriots Kyiv </a><br><a href='http://ulafua.com/team/rebels'> Rebels Kyiv </a><br><a href='http://ulafua.com/team/dzhokery'> Jokers Kyiv </a><br> <a href='http://ulafua.com/team/buldogi'>Bulldogs Kyiv</a>"

      }, {
        "type": "circle",
        "label": "Uzhhorod",
        "latitude": 48.37,
        "longitude": 22.17,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/lesoruby'>Lumberjacks Uzhgorod</a> "

      }, {
        "type": "circle",
        "label": "Kharkiv",
        "latitude": 50,
        "longitude": 36.13,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/atlanty'>Atlantes Kharkiv</a> <br> <a href='http://ulafua.com/team/tigry'>Kharkiv Tigers</a>"

      }, {
        "type": "circle",
        "label": "Kamianets-Podilskyi",
        "latitude": 48.71,
        "longitude": 26.35,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/titany'>Titans-K-PNU Kamyanets-Podilsky</a>"

      }, {
        "type": "circle",
        "label": "Mariupol",
        "latitude": 47.05,
        "longitude": 37.32,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/delfiny'>Azov Dolphins Mariupol</a>"

      }, {
        "type": "circle",
        "label": "Mykolaiv",
        "latitude": 46.7,
        "longitude": 32,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/vikingi'>Vikings Mykolaiv</a> "

      }, {
        "type": "circle",
        "label": "Kherson",
        "latitude": 46.5,
        "longitude": 32.35,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/akuly'>Sharks Kherson</a>"

      }, {
        "type": "circle",
        "label": "Yuzhne",
        "latitude": 46.5,
        "longitude": 30.7,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/gepardy'>Gepards Yuzhny</a>"

      }, {
        "type": "circle",
        "label": "Dnipro",
        "latitude": 48.27,
        "longitude": 34.59,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/rakety'>Rockets Dnipro</a> <br><a href='http://ulafua.com/team/spartans'>Spartans Dnipro</a> "

      },

      {
        "type": "circle",
        "label": "Khmelnytskyi",
        "latitude": 49.35,
        "longitude": 27,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/gladiatory'>Gladiators Khmelnytskyi</a>"

      }, {
        "type": "circle",
        "label": "Zdolbuniv",
        "latitude": 50.30,
        "longitude": 26.15,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/orly'>Eagles Zdolbuniv</a> "

      }, {
        "type": "circle",
        "label": "Zaporizhzhia",
        "latitude": 47.50,
        "longitude": 35.08,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/kazaki'>Cossacks Zaporizhzhia</a>"

      }, {
        "type": "circle",
        "label": "Bila Tserkva",
        "latitude": 49.47,
        "longitude": 30.07,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/hawks'>Hawks Bila Tserkva</a>"

      }
    ]
  },

  /**
   * create areas settings
   * autoZoom set to true means that the map will zoom-in when clicked on the area
   * selectedColor indicates color of the clicked area.
   */
  "areasSettings": {
    "autoZoom": false,
    "selectedColor": "#CC0000",
    "color": "#999999",
    "accessibleLabel": ""
  },
  "zoomControl": {
    "zoomControlEnabled": false,
    "maxZoomLevel": 1
  },
  /**
   * let's say we want a small map to be displayed, so let's create it
   */
  "smallMap": {}
});
///////////////////////////////////
///LADIES TEAM MAP
///////////////////////////////////
AmCharts.makeChart("mapdiv2", {
  /**
   * this tells amCharts it's a map
   */
  "type": "map",

  /**
   * create data provider object
   * map property is usually the same as the name of the map file.
   * getAreasFromMap indicates that amMap should read all the areas available
   * in the map data and treat them as they are included in your data provider.
   * in case you don't set it to true, all the areas except listed in data
   * provider will be treated as unlisted.
   */
  "dataProvider": {
    "map": "ukraineLow",
    "getAreasFromMap": true,
    "showDescriptionOnHover": true,
    "images": [{
        "type": "circle",
        "label": "Vinnytsia",
        "latitude": 49.14,
        "longitude": 28.28,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/wolves-girls'>Wolves Vinnytsia</a>"
      }, {
        "type": "circle",
        "label": "Kyiv",
        "latitude": 50.4,
        "longitude": 30.61,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/rebels-girls'></a>Rebels Kyiv <br> <a href='http://ulafua.com/team/bulldogs-girls'>Bulldogs Kyiv</a>"

      },

      {
        "type": "circle",
        "label": "Kamianets-Podilskyi",
        "latitude": 48.71,
        "longitude": 26.35,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/titanides'>Titanides Kamyanets-Podilsky</a>"

      },

      {
        "type": "circle",
        "label": "Dnipro",
        "latitude": 48.27,
        "longitude": 34.59,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/valkyrias-girls'>Valkyrias Dnipro</a>"

      }


    ]
  },

  /**
   * create areas settings
   * autoZoom set to true means that the map will zoom-in when clicked on the area
   * selectedColor indicates color of the clicked area.
   */
  "areasSettings": {
    "autoZoom": false,
    "selectedColor": "#CC0000",
    "color": "#999999",
    "accessibleLabel": ""
  },
  "zoomControl": {
    "zoomControlEnabled": false,
    "maxZoomLevel": 1
  },
  /**
   * let's say we want a small map to be displayed, so let's create it
   */
  "smallMap": {}
});
//////////////////////////////////
///KIDS TEAMS MAP////////////
/////////////////////////////
AmCharts.makeChart("mapdiv3", {
  /**
   * this tells amCharts it's a map
   */
  "type": "map",

  /**
   * create data provider object
   * map property is usually the same as the name of the map file.
   * getAreasFromMap indicates that amMap should read all the areas available
   * in the map data and treat them as they are included in your data provider.
   * in case you don't set it to true, all the areas except listed in data
   * provider will be treated as unlisted.
   */
  "dataProvider": {
    "map": "ukraineLow",
    "getAreasFromMap": true,
    "showDescriptionOnHover": true,
    "images": [{
        "type": "circle",
        "label": "Lviv",
        "latitude": 49.83,
        "longitude": 24,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/lions-u-15'>Lions Lviv</a> "
      },

      {
        "type": "circle",
        "label": "Vinnytsia",
        "latitude": 49.14,
        "longitude": 28.28,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/wolves-u-15'>Wolves Vinnytsia</a>"
      }, {
        "type": "circle",
        "label": "Kyiv",
        "latitude": 50.4,
        "longitude": 30.61,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/patriots-u-15'>Patriots Kyiv</a>"

      }, {
        "type": "circle",
        "label": "Mariupol",
        "latitude": 47.05,
        "longitude": 37.32,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/azov-dolphins-u-15'>Azov Dolphins Mariupol</a>"

      }, {
        "type": "circle",
        "label": "Mykolaiv",
        "latitude": 46.7,
        "longitude": 32,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/vikings-u-15'>Vikings Mykolaiv</a> "

      }, {
        "type": "circle",
        "label": "Yuzhne",
        "latitude": 46.5,
        "longitude": 30.7,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/gepards-u-15'>Gepards Yuzhny</a>"

      }, {
        "type": "circle",
        "label": "Khmelnytskyi",
        "latitude": 49.35,
        "longitude": 27,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/silver-bullets-u-15'>Silver Bullets Khmelnytskyi</a>"

      }, {
        "type": "circle",
        "label": "Zdolbuniv",
        "latitude": 50.30,
        "longitude": 26.15,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/eagles-u-15'>Eagles Zdolbuniv</a> "

      }, {
        "type": "circle",
        "label": "Bila Tserkva",
        "latitude": 49.47,
        "longitude": 30.07,
        "labelColor": "#cc0000",
        "labelRollOverColor": "#000000",
        "title": "",
        "description": "<a href='http://ulafua.com/team/hawks-u-15'>Hawks Bila Tserkva</a>"

      }
    ]
  },

  /**
   * create areas settings
   * autoZoom set to true means that the map will zoom-in when clicked on the area
   * selectedColor indicates color of the clicked area.
   */
  "areasSettings": {
    "autoZoom": false,
    "selectedColor": "#CC0000",
    "color": "#999999",
    "accessibleLabel": ""
  },
  "zoomControl": {
    "zoomControlEnabled": false,
    "maxZoomLevel": 1
  },
  /**
   * let's say we want a small map to be displayed, so let's create it
   */
  "smallMap": {}
});
////////////////////////////
////////////////////////////
///TEAM MAP CHEERLEADERS////
/////////////////////////////
/////////////////////////////
AmCharts.makeChart("mapdiv4", {
  /**
   * this tells amCharts it's a map
   */
  "type": "map",

  /**
   * create data provider object
   * map property is usually the same as the name of the map file.
   * getAreasFromMap indicates that amMap should read all the areas available
   * in the map data and treat them as they are included in your data provider.
   * in case you don't set it to true, all the areas except listed in data
   * provider will be treated as unlisted.
   */
  "dataProvider": {
    "map": "ukraineLow",
    "getAreasFromMap": true,
    "showDescriptionOnHover": true,
    "images": [{
      "type": "circle",
      "label": "Lviv",
      "latitude": 49.83,
      "longitude": 24,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/cheerteam_lions'>Lions Lviv</a> "
    }, {
      "type": "circle",
      "label": "Odessa",
      "latitude": 46.29,
      "longitude": 30.43,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/odessapirates'>Pirates Odesa</a>"
    }, {
      "type": "circle",
      "label": "Vinnytsia",
      "latitude": 49.14,
      "longitude": 28.28,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/wolvescheerleadingteam'>Wolves Vinnytsia</a>"
    }, {
      "type": "circle",
      "label": "Kyiv",
      "latitude": 50.4,
      "longitude": 30.61,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/cheer_bandits'>Bandits Kyiv</a>  <br> <a href='https://vk.com/slavs_cheerleading_girls?_smt=groups_list%3A1'>Slavs Kyiv</a>  <br> <a href='https://vk.com/patriots_cheer'>Patriots Kyiv</a> <br> <a href='https://vk.com/jokers_sweets'>Jokers Kyiv</a> <br> <a href='https://vk.com/bulldogs_cheerleaders'>Bulldogs Kyiv</a>"

    }, {
      "type": "circle",
      "label": "Kamianets-Podilskyi",
      "latitude": 48.71,
      "longitude": 26.35,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/kp_af'>Titans-K-PNU Kamyanets-Podilsky</a>"

    }, {
      "type": "circle",
      "label": "Mariupol",
      "latitude": 47.05,
      "longitude": 37.32,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/azov_dolphins'>Azov Dolphins Mariupol</a>"

    }, {
      "type": "circle",
      "label": "Mykolaiv",
      "latitude": 46.7,
      "longitude": 32,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/cheerleading_niko'>Vikings Mykolaiv</a> "

    }, {
      "type": "circle",
      "label": "Dnipro",
      "latitude": 48.27,
      "longitude": 34.59,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/show_models_dnepr'>Rockets Dnipro</a>"

    }, {
      "type": "circle",
      "label": "Khmelnytskyi",
      "latitude": 49.35,
      "longitude": 27,
      "labelColor": "#cc0000",
      "labelRollOverColor": "#000000",
      "title": "",
      "description": "<a href='https://vk.com/club123202350'>Gladiators Khmelnytskyi</a>"

    }]
  },

  /**
   * create areas settings
   * autoZoom set to true means that the map will zoom-in when clicked on the area
   * selectedColor indicates color of the clicked area.
   */
  "areasSettings": {
    "autoZoom": false,
    "selectedColor": "#CC0000",
    "color": "#999999",
    "accessibleLabel": ""
  },
  "zoomControl": {
    "zoomControlEnabled": false,
    "maxZoomLevel": 1
  },
  /**
   * let's say we want a small map to be displayed, so let's create it
   */
  "smallMap": {}
});
////////////////////////////
/* // MIsha`s work */
