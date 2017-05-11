<?php /* Template Name: Home Page */ get_header(); ?>

  <div id="main">
    <div class="stm-title-box-unit transparent-header_on"></div>
      <div class="container">

        <div class="vc_row wpb_row vc_row-fluid">
          <div class="wpb_column vc_column_container vc_col-sm-12">
            <div class="vc_column-inner ">
              <div class="wpb_wrapper">
                <!-- PLACE FOR FULLPAGE SLIDER -->
                <?php putRevSlider('main'); ?>
              </div><!-- wpb_wrapper -->
            </div><!-- vc_column-inner -->
          </div><!-- wpb_column vc_column_container -->
        </div><!-- vc_row wpb_row vc_row-fluid -->

        <div class="vc_row-full-width vc_clearfix"></div>

        <div data-vc-full-width="true" data-vc-full-width-init="true" class="vc_row wpb_row vc_row-fluid vc_custom_1462188886536 vc_row-has-fill">
          <div class="wpb_column vc_column_container vc_col-sm-12">
            <div class="vc_column-inner ">
              <div class="wpb_wrapper">
                <div class="stm-media-tabs stm-news-tabs-wrapper">

                  <div class="stm-title-left">
                    <h6 class="stm-main-title-unit">Новости</h6>
                  </div>

                  <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active" id="ifaf">
                      <div class="row row-3 row-sm-2">

                      <?php query_posts("showposts=3"); ?>
                      <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3186 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf category-other tag-wada">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
                                 <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
                                <?php endif; ?>
                              </div><!-- image -->
                              <div class="date heading-font"><?php the_time('m-d-Y'); ?></div>
                              <div class="title heading-font"><?php the_title(); ?></div>
                            </a>
                            <div class="content">
                              <?php wpeExcerpt('wpeExcerpt40'); ?>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <i class="fa fa-commenting"></i>
                                <span><?php comments_number(); ?></span>
                              </div>
                              <div class="post_list_item_tags">
                                <i class="fa fa-tag"></i>
                                <?php the_tags(); ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endwhile; endif; ?>
                      <?php wp_reset_query(); ?>

                    </div>
                  </div><!-- tabpanel -->
                </div><!-- tab-content -->

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="container">
        <?php $posts = get_field('our_partners'); if( $posts ): ?>
          <div class="row slider owl-carousel owl-theme">
            <?php foreach( $posts as $post): ?>
              <?php setup_postdata($post); ?>
              <div class="slide col-md-8 col-sm-12">
                <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
                  <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <?php wp_reset_postdata();  ?>
        <?php endif; ?>
      </div><!-- container -->
      <div id="mapdiv" style="width: 100%; height: 600px;margin: 80px 0;"></div>
      <div class="vc_row-full-width vc_clearfix"></div>

      <div data-vc-full-width="true" data-vc-full-width-init="true" data-vc-parallax="2" data-vc-parallax-image="<?php echo get_template_directory_uri(); ?>/img/paralax-1.jpg" class="vc_row wpb_row vc_row-fluid vc_row-has-fill vc_general vc_parallax vc_parallax-content-moving" style="background-size: cover; background: url(<?php echo get_template_directory_uri(); ?>/img/paralax-1.jpg) fixed repeat 100%;">
        <div class="wpb_column vc_column_container vc_col-sm-12">
          <div class="vc_column-inner vc_custom_1476626466570">
            <div class="wpb_wrapper">
              <div class="wpb_text_column wpb_content_element ">
                <div class="wpb_wrapper">
                  <h3 style="text-align: center;">Презентация наших рекламных возможностей и медийных проявлений для партнеров и спонсоров.</h3>
                </div>
              </div>
              <div class="stm-call-to-action clearfix">
                <a class="button btn-secondary btn-md btn-style-4" href="https://drive.google.com/file/d/0B1o4CqfAm5ffTFhEQ05NRk55cHM/view" rel="nofollow" target="_blank">Скачать</a>
                <div class="stm-call-to-action-inner">

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div data-vc-full-width="true" data-vc-full-width-init="true" class="vc_row wpb_row vc_row-fluid stm-red-bg">
        <div class="wpb_column vc_column_container vc_col-sm-12">
          <div class="vc_column-inner ">
            <div class="wpb_wrapper">
              <div class="stm-call-to-action clearfix">
                <a class="button btn-secondary btn-md btn-style-4" href="http://ulaf.amfoot.net/" title="Перейти!">Перейти!</a>
                <div class="stm-call-to-action-inner">
                  <h4>Тренерам команд и судьям.</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="vc_row-full-width vc_clearfix"></div>

      <div class="vc_row-full-width vc_clearfix"></div>

      <div class="clearfix"></div>

    </div>
  </div><!--main-->

<?php get_footer(); ?>
