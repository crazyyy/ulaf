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

  <!--[if lt IE 9]>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- css + javascript -->
  <?php wp_head(); ?>

</head>
<body <?php body_class(); ?>>
<!-- wrapper -->
<div class="wrapper">

  <!-- Navbar -->
  <header class="navbar">
    <div class="container">
      <div class="row">
        <div class="col-md-1 col-sm-2 col-xs-2">
          <a href="<?php echo home_url(); ?>" class="navbar-brand"><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></a>
            <span class="mobil-nav-btn">
              <img  src="<?php echo get_template_directory_uri(); ?>/img/mob-nav.png">
            </span>
        </div>
        <div class="col-md-11 col-sm-10 col-xs-10">
          <nav class="header-nav">
            <?php wpeHeadNav(); ?>
          </nav>
        </div>
      </div><!-- .row -->
    </div><!-- .container -->
  </header><!-- navbar -->

<!-- Teams logo -->
    <section class="section-teams">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-md-12-teams col-sm-12 col-xs-12">
                    <ul class="clearfix">
                      <ul class="all-review-page">
                          <?php
                          $temp = $wp_query; $wp_query= null; query_posts('post_type=team'.'&showposts=100&orderby=date&order=ASC'); while (have_posts()) : the_post();?>
                            <li>
                                <a rel="nofollow" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                <?php if ( has_post_thumbnail()) :
                                  the_post_thumbnail('medium');
                                else: ?>
                                  <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
                                <?php endif; ?>
                                </a><!-- /post thumbnail -->
                            </li>

                          <?php endwhile; ?>
                          <?php $wp_query = null; $wp_query = $temp;?>
                          <?php wp_reset_query(); ?>
                      </ul><!-- all-review-page -->
                    </ul>
                </div>
            </div>
        </div>
    </section><!-- section-teams -->
   <!-- header end  -->
