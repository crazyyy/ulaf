<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php wp_title( '' ); ?><?php if ( wp_title( '', false ) ) { echo ' :'; } ?> <?php bloginfo( 'name' ); ?></title>

  <link href="http://www.google-analytics.com/" rel="dns-prefetch"><!-- dns prefetch -->

  <!-- icons -->
  <link href="<?php echo get_template_directory_uri(); ?>/favicon.ico" rel="shortcut icon">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>css/ammap.css" type="text/css" media="all">
  <!--[if lt IE 9]>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!-- css + javascript -->
  <?php wp_head(); ?>

</head>
<body <?php body_class(); ?>>

  <div id="wrapper">
    <div id="stm-top-bar">
      <div class="container">
        <div class="row">

          <div class="col-md-6 col-sm-6">
            <?php get_template_part('includes/header-ticker'); ?>
          </div>

          <div class="col-md-6 col-sm-6">
            <div class="clearfix">
              <div class="stm-top-bar_right">
                <div class="clearfix">
                  <div class="stm-top-switcher-holder">
                  </div>
                  <div class="stm-top-cart-holder">
                  </div>
                </div>
              </div>
              <div class="stm-top-socials-holder">
                <ul class="top-bar-socials stm-list-duty">
                  <li>
                    <a href="https://www.facebook.com/ulaf.ua/" target="_blank">
                      <i class="fa fa-facebook"></i>
                    </a>
                  </li>
                  <li>
                    <a href="https://twitter.com/ulafcool" target="_blank">
                      <i class="fa fa-twitter"></i>
                    </a>
                  </li>
                  <li>
                    <a href="https://www.instagram.com/ulafcool/" target="_blank">
                      <i class="fa fa-instagram"></i>
                    </a>
                  </li>
                  <li>
                    <a href="https://vk.com/ulafua" target="_blank">
                      <i class="fa fa-vk"></i>
                    </a>
                  </li>
                  <li>
                    <a href="https://www.youtube.com/channel/UCX-eSs3MKCWTAnm3rQJ7WJQ" target="_blank">
                      <i class="fa fa-youtube"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if ( is_page(22) ){
        $header_nav_class = 'stm-header stm-transparent-header stm-header-fixed-mode';
      } else {
        $header_nav_class = 'stm-header stm-non-transparent-header stm-header-fixed-mode';
      } ?>

    <div class="<?php echo $header_nav_class; ?>">
      <div class="stm-header-inner">
        <div class="stm-header-background" style="background-image: url('<?php echo get_template_directory_uri(); ?>/img/header.jpg')"></div>
        <div class="container stm-header-container">
          <!--Logo-->
          <div class="logo-main" style="margin-top: 0px;">
            <a class="bloglogo" href="<?php echo home_url(); ?>">
              <img src="<?php echo get_template_directory_uri(); ?>/img/logo1.png" title="Home" alt="Logo">
            </a>
          </div>
          <div class="stm-main-menu">
            <div class="stm-main-menu-unit " style="margin-top: 30px;">
              <?php wpeHeadNav(); ?>
            </div>
          </div>
        </div>
      </div>
      <!--MOBILE HEADER-->
      <div class="stm-header-mobile clearfix">
        <div class="logo-main" style="margin-top: 0px;">
          <a class="bloglogo" href="<?php echo home_url(); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/img/logo1.png" title="Home" alt="Logo">
          </a>
        </div>
        <div class="stm-mobile-right">
          <div class="clearfix">
            <div class="stm-menu-toggle">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
        <div class="stm-mobile-menu-unit">
          <div class="inner">
            <div class="stm-top clearfix">
              <div class="stm-switcher pull-left">
              </div>
              <div class="stm-top-right">
                <div class="clearfix">
                  <div class="stm-top-search">
                  </div>
                  <div class="stm-top-socials">
                    <ul class="top-bar-socials stm-list-duty">
                      <li>
                        <a href="https://www.facebook.com/ulaf.ua/" target="_blank">
                          <i class="fa fa-facebook"></i>
                        </a>
                      </li>
                      <li>
                        <a href="https://twitter.com/ulafcool" target="_blank">
                          <i class="fa fa-twitter"></i>
                        </a>
                      </li>
                      <li>
                        <a href="https://www.instagram.com/ulafcool/" target="_blank">
                          <i class="fa fa-instagram"></i>
                        </a>
                      </li>
                      <li>
                        <a href="https://vk.com/ulafua" target="_blank">
                          <i class="fa fa-linkedin"></i>
                        </a>
                      </li>
                      <li>
                        <a href="#" target="_blank">
                          <i class="fa fa-youtube"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <?php wpeHeadNavMobile(); ?>
          </div>
        </div>
      </div>
    </div>
