<?php get_header(); ?>
<div id="main">
      <div class="stm-default-page stm-default-page-grid stm-default-page-left">
        <div class="container">
          <div class="row">
            <div class="col-md-9 col-md-push-3 col-sm-12">
              <div class="sidebar-margin-top clearfix"></div>
              <div class="stm-small-title-box">
                <div class="stm-title-box-unit ">
                  <div class="stm-page-title">
                    <div class="container">
                      <div class="clearfix stm-title-box-title-wrapper">
                        <h3>Новости</h3>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row row-3 row-sm-2">
             <?php if (have_posts()): while (have_posts()) : the_post(); ?>
                <div class="col-md-4 col-sm-6">
                  <div class="stm-single-post-loop post-3186 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf category-other tag-wada">
                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                      <div class="image">
                        <div class="stm-plus"></div>
                        <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
                        <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
                        <?php endif; ?>
                      </div>
                      <div class="date heading-font">
                        <?php the_time('m-d-Y'); ?> </div>
                      <div class="title heading-font">
                        <?php the_title(); ?> </div>
                    </a>
                    <div class="content">
                      <?php wpeExcerpt('wpeExcerpt40'); ?>
                    </div>
                    <div class="post-meta heading-font">
                      <div class="comments-number">
                        <a href="#">
                          <i class="fa fa-commenting"></i>
                          <span><?php comments_number(); ?></span>
                        </a>
                      </div>
                      <div class="post_list_item_tags">
                        <a href="#">
                          <i class="fa fa-tag"></i> <?php the_tags(); ?> </a>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endwhile; endif; ?>
                <?php wp_reset_query(); ?>
              </div>

                 <?php get_template_part('pagination'); ?>
            </div>
            <!--Sidebar-->
            <div class="col-md-3 col-md-pull-9 hidden-sm hidden-xs">
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
                  <?php endwhile; else: ?>
                  <div class="row">
                    <div class="col-md-12">
                     <h2 class="title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
                    </div><!-- col-md-12 -->
                 </div><!-- /.row -->
                 <?php endif; ?>

                </aside>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--main-->
  </div>
  </div>
<?php get_footer(); ?>

