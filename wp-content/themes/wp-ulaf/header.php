<?php
  /**
   * Displays the site header.
   * @package WordPress
   * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
   */

  $wrapper_classes  = 'site-header';
  $wrapper_classes .= has_custom_logo() ? ' has-logo' : '';
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">

  <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>

  <link href="//www.google-analytics.com/" rel="dns-prefetch">
  <link href="//fonts.googleapis.com" rel="dns-prefetch">
  <link href="//cdnjs.cloudflare.com" rel="dns-prefetch">
  <link href="//cdn.jsdelivr.net" rel="dns-prefetch">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <!-- icons -->
  <link href="<?php echo get_template_directory_uri(); ?>/img/favicon/favicon.ico" rel="shortcut icon">

  <!--[if lt IE 9]>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> itemscope itemtype="https://schema.org/WebPage">
  <?php wp_body_open(); ?>

    <header id="masthead" class="<?php echo esc_attr($wrapper_classes); ?>" role="banner" itemscope itemtype="https://schema.org/WPHeader">  
        <!-- Navigation -->
        <div class="navbar" role="navigation">
            <div class="nav-container">
                <?php $logo_id = get_theme_mod( 'dark_logo_setting' );
                  if ( $logo_id ) {
                    $logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
                    echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '">';
                  } elseif (function_exists('the_custom_logo')) {
                    the_custom_logo();
                  }
                ?>
                <nav class="header-nav" role="navigation">   
                  <?php wp_nav_menu(array('theme_location' => 'header-menu')); ?>
                </nav>
                <button class="mobile-menu-toggle">☰</button>
            </div>
        </div>
    </header>