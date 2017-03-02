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


  <?php endwhile; endif; ?>


<?php get_footer(); ?>
