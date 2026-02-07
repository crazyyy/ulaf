(function ($) {

    //Site background color
    wp.customize('site_bg_color', function (value) {
        value.bind(function (newval) {
            $('#wrapper').css('background-color', newval);
        });
    });

    //Text logo color
    wp.customize('logo_color', function (value) {
        value.bind(function (newval) {
            $('.stm-header .logo-main .blogname h1').css('color', newval);
        });
    });

    //Text body color
    wp.customize('typography_body_color', function (value) {
        value.bind(function (newval) {
            $('body, .normal_font').css('color', newval);
        });
    });

    //Top bar bg color
    wp.customize('top_bar_bg_color', function (value) {
        value.bind(function (newval) {
            $('#stm-top-bar').css('background-color', newval);
        });
    });

    wp.customize('top_bar_text_color', function (value) {
        value.bind(function (newval) {
            $('#stm-top-bar,#stm-top-bar a, #stm-top-bar .heading-font').css('color', newval);
        });
    });

    //Top bar bg color
    wp.customize('header_bg_color', function (value) {
        value.bind(function (newval) {
            $('.header-main').css('background-color', newval);
        });
    });

    //Footer COLOR
    wp.customize('footer_bg_color', function (value) {
        value.bind(function (newval) {
            $('.footer-widgets-wrapper').css('background-color', newval);
        });
    });

    wp.customize('footer_text_color', function (value) {
        value.bind(function (newval) {
            $('.footer-widgets-wrapper, .footer-widgets-wrapper a, .footer-widgets-wrapper li, .footer-widgets-wrapper li .text, .footer-widgets-wrapper caption').css('color', newval);
        });
    });
    //Footer COLOR END

    //Footer BOTTOM STYLES
    wp.customize('footer_bottom_bg_color', function (value) {
        value.bind(function (newval) {
            $('#stm-footer-bottom').css('background-color', newval);
        });
    });

    wp.customize('footer_bottom_text_color', function (value) {
        value.bind(function (newval) {
            $('#stm-footer-bottom, #stm-footer-bottom a, .footer-socials-title').css('color', newval);
        });
    });

    //Footer BOTTOM STYLES END

    //Text menu color
    wp.customize('typography_menu_color', function (value) {
        value.bind(function (newval) {
            $('.header-menu li a').css('color', newval);
        });
    });

    //Text heading color
    wp.customize('typography_heading_color', function (value) {
        value.bind(function (newval) {
            $('h1,.h1,h2,.h2,h3,.h3,h4,.h4,h5,.h5,h6,.h6,.heading-font,.button,.load-more-btn,.vc_tta-panel-title,.page-numbers li > a,.page-numbers li > span,.vc_tta-tabs .vc_tta-tabs-container .vc_tta-tabs-list .vc_tta-tab a span,.stm_auto_loan_calculator input').css('color', newval);
        });
    });

    //Slider jquery ui
    wp.customize('jqueryui_slider', function (value) {
        value.bind(function (newval) {
            $('p').css('font-size', newval + 'px');
        });
    });

})(jQuery);