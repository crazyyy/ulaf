<?php get_header(); ?>

    <div id="main">
      <!--SINGLE POST-->
      <div id="post-3147" class="post-3147 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf tag-general-meeting tag-ifaf">
        <div class="stm-single-post stm-default-page">
          <div class="container">
            <div class="row stm-format-">


  <?php if (have_posts()): while (have_posts()) : the_post(); ?>
              <div class="col-md-9 col-md-push-3 col-sm-12">
                <div class="sidebar-margin-top clearfix"></div>
                <div class="stm-small-title-box">
                  <div class="stm-title-box-unit ">
                    <div class="stm-page-title">
                      <div class="container">
                        <div class="clearfix stm-title-box-title-wrapper">
                          <h3><?php the_title(); ?></h3>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--Post thumbnail-->
                <div class="post-thumbnail">


      <?php if ( has_post_thumbnail()) :?>
        <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
          <?php the_post_thumbnail(); // Fullsize image for the single post ?>
        </a>
      <?php endif; ?><!-- /post thumbnail -->
                  </div>
                <div class="stm-single-post-meta clearfix heading-font">
                  <div class="stm-meta-left-part">
                    <div class="stm-date">
                      <i class="fa fa-calendar-o"></i><?php the_time('d F Y'); ?></div>
                    <div class="stm-author">
                      <i class="fa fa-user"></i><?php the_author(); ?></div>
                  </div>

                  <div class="stm-comments-num">
                    <a href="http://www.ifaf.info/ifaf/ifaf-annual-general-meeting-september-23rd-2017/#respond" class="stm-post-comments">
                      <i class="fa fa-commenting"></i><?php comments_number(); ?></a>
                  </div>

                </div>

                <div class="post-content">
                 <?php the_content(); ?>
                  <div class="clearfix"></div>
                </div>
                <div class="stm-post-meta-bottom heading-font clearfix">
                  <div class="stm_post_tags">
                    <?php the_tags(' <i class="fa fa-tag"></i>') ?></div>

                </div>
                <!--Comments-->
                 <?php comments_template(); ?>
              </div>
              <div class="col-md-3 col-md-pull-9 hidden-sm hidden-xs">
                <?php get_sidebar('left'); ?>

                <aside id="stm_recent_posts-2" class="widget widget-default widget_stm_recent_posts">
                  <div class="widget-title">
                    <h4>Latest news</h4></div>
                  <div class="widget_media clearfix">
                    <a href="http://www.ifaf.info/competitions/official-logo-beach-football-world-championship/">
                      <div class="image">
                        <img width="150" height="150" src="img/2017-beach-football-world-championship-logo-150x150.jpg" class="img-responsive wp-post-image" alt="beach football world championship" srcset="http://www.ifaf.info/wp-content/uploads/2017/02/2017-beach-football-world-championship-logo-150x150.jpg 150w, http://www.ifaf.info/wp-content/uploads/2017/02/2017-beach-football-world-championship-logo-200x200.jpg 200w" sizes="(max-width: 150px) 100vw, 150px"> </div>
                      <div class="stm-post-content">
                        <div class="date heading-font">
                          February 18, 2017 </div>
                        <span class="h5">The Official Logo of the Beach Football World Championship Revealed</span>
                      </div>
                    </a>
                  </div>
                  <div class="clearfix"></div>
                  <div class="widget_media clearfix">
                    <a href="http://www.ifaf.info/ifaf/ifaf-took-part-wadas-webinar/">
                      <div class="image">
                        <img width="150" height="150" src="img/wada-play-true1-150x150.jpg" class="img-responsive wp-post-image" alt="Webinar" srcset="http://www.ifaf.info/wp-content/uploads/2017/02/wada-play-true1-150x150.jpg 150w, http://www.ifaf.info/wp-content/uploads/2017/02/wada-play-true1-200x200.jpg 200w" sizes="(max-width: 150px) 100vw, 150px"> </div>
                      <div class="stm-post-content">
                        <div class="date heading-font">
                          February 1, 2017 </div>
                        <span class="h5">IFAF Took Part in WADA’s Webinar</span>
                      </div>
                    </a>
                  </div>
                  <div class="clearfix"></div>
                  <div class="widget_media clearfix">
                    <a href="http://www.ifaf.info/ifaf/2017-ifaf-beach-football-world-championship/">
                      <div class="image">
                        <img width="150" height="150" src="img/AlbahriFahadAsianBeachGamesDay1MVu_O23c-Krl-150x150.jpg" class="img-responsive wp-post-image" alt="IFAF Beach Football World Championship" srcset="http://www.ifaf.info/wp-content/uploads/2017/01/AlbahriFahadAsianBeachGamesDay1MVu_O23c-Krl-150x150.jpg 150w, http://www.ifaf.info/wp-content/uploads/2017/01/AlbahriFahadAsianBeachGamesDay1MVu_O23c-Krl-200x200.jpg 200w" sizes="(max-width: 150px) 100vw, 150px"> </div>
                      <div class="stm-post-content">


  <?php endwhile; endif; ?>


<?php get_footer(); ?>
