<!DOCTYPE html>
<html lang="en">

<head>
  <title></title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="msapplication-TileImage" content="http://www.ifaf.info/wp-content/uploads/2016/10/cropped-IFAF-LOGO-512-1-270x270.png">
  <!--[if lte IE 9]><link rel="stylesheet" type="text/css" href="http://www.ifaf.info/wp-content/plugins/js_composer/assets/css/vc_lte_ie9.min.css" media="screen"><![endif]-->
  <!--[if IE  8]><link rel="stylesheet" type="text/css" href="http://www.ifaf.info/wp-content/plugins/js_composer/assets/css/vc-ie8.min.css" media="screen"><![endif]-->
  <link rel="icon" href="<?php echo get_template_directory_uri(); ?>favicon.ico" sizes="32x32">
  <link rel="icon" href="<?php echo get_template_directory_uri(); ?>favicon.ico" sizes="192x192">
  <link rel="apple-touch-icon-precomposed" href="<?php echo get_template_directory_uri(); ?>favicon.ico">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/main.css" type="text/css" media="all">
  <link  href="http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.css" rel="stylesheet">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

  <div id="wrapper">
    <div id="stm-top-bar">
      <div class="container">
        <div class="row">

          <div class="col-md-6 col-sm-6">
            <div class="stm-top-ticker-holder">
              <div class="heading-font stm-ticker-title"><span class="stm-red">Последние</span> новости</div>
              <ol class="stm-ticker">
                <?php query_posts("showposts=3"); ?>
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                <?php endwhile; endif; ?>
                <?php wp_reset_query(); ?>

              </ol>
            </div>
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
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="stm-header stm-transparent-header stm-header-fixed-mode">
      <div class="stm-header-inner">
        <div class="stm-header-background" style="background-image: url('http://www.ifaf.info/wp-content/uploads/2016/10/header-about-us.jpg')"></div>
        <div class="container stm-header-container">
          <!--Logo-->
          <div class="logo-main" style="margin-top: 0px; margin-right: 9px;">
            <a class="bloglogo" href="http://www.ulafua.com/">
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
          <a class="bloglogo" href="http://www.ulafua.com/">
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
                        <a href="#" target="_blank">
                          <i class="fa fa-facebook"></i>
                        </a>
                      </li>
                      <li>
                        <a href="#" target="_blank">
                          <i class="fa fa-twitter"></i>
                        </a>
                      </li>
                      <li>
                        <a href="#" target="_blank">
                          <i class="fa fa-instagram"></i>
                        </a>
                      </li>
                      <li>
                        <a href="#" target="_blank">
                          <i class="fa fa-linkedin"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <ul class="stm-mobile-menu-list heading-font">
             <?php wpeHeadNav(); ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
