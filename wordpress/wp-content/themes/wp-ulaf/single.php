<?php get_header(); ?>

    <div id="main">
      <!--SINGLE POST-->
      <div id="post-3147" class="post-3147 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf tag-general-meeting tag-ifaf">
        <div class="stm-single-post stm-default-page">
          <div class="container">
            <div class="row stm-format-">
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
                   <?php endif; ?>
               </div>
                <div class="stm-single-post-meta clearfix heading-font">
                  <div class="stm-meta-left-part">
                    <div class="stm-date">
                      <i class="fa fa-calendar-o"></i><?php the_time('d F Y'); ?> </div>
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
                    <?php the_tags(' <i class="fa fa-tag"></i>') ?> </div>

                </div>
                <!--Comments-->
                <div class="stm_post_comments">
                  <?php comments_template(); ?>
                </div>
              </div>
              <!--Sidebar-->
              <div class="col-md-3 col-md-pull-9 hidden-sm hidden-xs">
                <aside id="categories-2" class="widget widget-default widget_categories">
                  <?php get_sidebar('left'); ?>
                </aside>

                <aside id="stm_recent_posts-2" class="widget widget-default widget_stm_recent_posts">
                  <div class="widget-title">
                    <h4>Последние новости</h4></div>
                  <?php query_posts("showposts=3"); ?>
                  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                  <div class="widget_media clearfix">
                    <a href="<?php the_permalink(); ?>">
                      <div class="image">
                       <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
                        <img width="150" height="150" src="<?php echo catchFirstImage(); ?>" class="img-responsive wp-post-image" alt="<?php the_title(); ?>" sizes="(max-width: 150px) 100vw, 150px">
                        <?php endif; ?> </div>
                      <div class="stm-post-content">
                        <div class="date heading-font">
                          <?php the_time('m-d-Y'); ?> </div>
                        <span class="h5"><?php the_title(); ?></span>
                      </div>
                    </a>
                  </div>
                  <?php endwhile; endif; ?>
                  <?php wp_reset_query(); ?>

                </aside>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--main-->
  </div>

<?php get_footer(); ?>

