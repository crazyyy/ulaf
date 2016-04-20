<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php wp_title( '' ); ?><?php if ( wp_title( '', false ) ) { echo ' :'; } ?> <?php bloginfo( 'name' ); ?></title>

<!-- icons -->
  <link href="<?php echo get_template_directory_uri(); ?>/favicon.ico" rel="shortcut icon">

  <!--[if lt IE 9]>
    <script type="text/javascript" img src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.js"></script>
    <script type="text/javascript" img src="<?php echo get_template_directory_uri(); ?>/js/selectivizr.js"></script>
    <script type="text/javascript" img src="<?php echo get_template_directory_uri(); ?>/js/respond.js"></script>
  <![endif]-->

  <?php wp_head(); ?>

    <script type="text/javascript" img src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script type="text/javascript" img src="http://netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>


  </head>
<body <?php body_class(); ?>>

  <div class="navbar">
    <div class="container">
      <div class="row">
        <div class="col-md-1">
          <a class="navbar-brand"><img alt="" img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></a>
        </div>
        <div class="col-md-11">
          <nav class="header-nav">
            <?php wpeHeadNav(); ?>
          </nav>
        </div>

      </div><!-- .row -->
    </div><!-- .container -->
  </div><!-- navbar -->

    <section class="section-teams">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-md-12-teams">
                    <ul class="clearfix">
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/lions.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/slavs.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/eagles.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/glad.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/mon.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/rockets.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/sharks.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/vikings.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/tigers.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/dolph.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/hummers.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/cossacs.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/bears.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/bizonu.png"></a>
                        </li>
                        <li>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/titans.png"></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section><!-- section-teams -->
   <!-- header end  -->
