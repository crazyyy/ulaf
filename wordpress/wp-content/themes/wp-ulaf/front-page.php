<?php /* Template Name: Home Page */ get_header(); ?>

<!-- Home Slider -->
  <!-- <div class="col-md-12 col-sm-12 col-xs-12 owl-home-slide">
        <?php if( have_rows('slider') ): ?>
        <?php while( have_rows('slider') ): the_row();
                      // vars
        $link = get_sub_field('slider_link');
        $link2 = get_sub_field('slider_link2');
        $image = get_sub_field('slider_image');
        $content = get_sub_field('slider_title');
        $description = get_sub_field('slider_description'); ?>
      <div class="item-slide">
            <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt'] ?>" />
            <h1 class="slider_title"><?php echo $content; ?></h1>
            <h5 class="descr"><?php echo $description; ?></h5>
        <div class="slider_but">
             <?php if( $link ): ?>
            <a href="<?php echo $link; ?>" class="btn btn-primary">Присоединяйтесь</a>
            <?php endif; ?>
            <?php if( $link2 ): ?>
            <a href="<?php echo $link2; ?>" class="btn second">Подробнее</a>
            <?php endif; ?>
        </div>
      </div>item-slide
                 <?php endwhile; ?>
                  <?php endif; ?>
    </div> -->
    <!-- /.owl-home-slide -->

    <!-- News -->
  <!-- <section class="section-news">

    <div class="container">
      <div class="row news_block">
        <h3 class="col-md-12 news-title">Последние новости</h3>
        start news loop
        <?php query_posts("showposts=4&cat=1"); ?>
          <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <div class="col-md-3 col-sm-6-news-posts">
              <a href="<?php the_permalink(); ?>" class="hover_image">
                <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
                  <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
                <?php endif; ?>
                <span class="date"><?php the_time('j.m.Y'); ?> <?php the_time('H:i'); ?></span>
              </a>
              <h4><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
            </div>
          <?php endwhile; endif; ?>
        <?php wp_reset_query(); ?>
        end news loop

      </div>news_block

      Sponsors Slider
      <div class="owl-footer-slide">
        <?php $images = get_field('sponsors_gallery'); if( $images ): foreach( $images as $image ): ?>
          <div class="item-slide sponsors-footer-images">
            <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
          </div>
        <?php endforeach; endif; ?>
      </div>owl-footer-slide
    </div>container
  </section>section-news -->
<div id="main">
      <div class="stm-title-box-unit transparent-header_on">
      </div>
      <div class="container">
        <div class="vc_row wpb_row vc_row-fluid">
          <div class="wpb_column vc_column_container vc_col-sm-12">
            <div class="vc_column-inner ">
              <div class="wpb_wrapper">
                <!-- PLACE FOR FULLPAGE SLIDER -->
                <?php putRevSlider('main'); ?>


              </div>
            </div>
          </div>
        </div>

        <div class="vc_row-full-width vc_clearfix"></div>
        <div data-vc-full-width="true" data-vc-full-width-init="true" class="vc_row wpb_row vc_row-fluid vc_custom_1462188886536 vc_row-has-fill">
          <div class="wpb_column vc_column_container vc_col-sm-12">
            <div class="vc_column-inner ">
              <div class="wpb_wrapper">
                <div class="stm-media-tabs stm-news-tabs-wrapper">
                  <div class="clearfix">
                    <div class="stm-title-left">
                      <h3 class="stm-main-title-unit">Новости</h3>
                    </div>
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

                                </div>
                              <div class="date heading-font">
                                <?php the_time('m-d-Y'); ?></div>
                              <div class="title heading-font">
                                <?php the_title(); ?> </div>
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

                                  <i class="fa fa-tag"></i> <?php the_tags(); ?>
                              </div>
                            </div>
                          </div>
                        </div>
                        <?php endwhile; endif; ?>
                        <?php wp_reset_query(); ?>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

<div class="container">
        <div class="row slider">
          <div class="slide col-md-12">
            <img src="<?php echo get_template_directory_uri(); ?>/img/FISU.png" alt="">
          </div>
          <div class="slide col-md-12">
            <img src="<?php echo get_template_directory_uri(); ?>/img/FISU.png" alt="">
          </div>
          <div class="slide col-md-12">
            <img src="<?php echo get_template_directory_uri(); ?>/img/ifaf-news.jpg" alt="">
          </div>
        </div>
      </div>
        <div class="vc_row-full-width vc_clearfix"></div>

        <div data-vc-full-width="true" data-vc-full-width-init="true" data-vc-parallax="2" data-vc-parallax-image="http://ulafua.com/wp-content/uploads/2017/03/paralax-1.jpg" class="vc_row wpb_row vc_row-fluid vc_row-has-fill vc_general vc_parallax vc_parallax-content-moving" style="background-image: url(http://ulafua.com/wp-content/uploads/2017/03/paralax-1.jpg); background-repeat: no-repeat;background-size: cover;">
          <div class="wpb_column vc_column_container vc_col-sm-12">
            <div class="vc_column-inner vc_custom_1476626466570">
              <div class="wpb_wrapper">
                <div class="wpb_text_column wpb_content_element ">
                  <div class="wpb_wrapper">
                    <h3 style="text-align: center;"><span style="color: #ffffff;">IFAF is composed of 103 members nations on six continents<br>
(North and South America, Europe, Asia, Africa and Oceania)<br>
all of which possess national federations dedicated solely to American football.</span></h3>
                  </div>
                </div>
                <div class="stm-call-to-action clearfix">
                  <a class="button btn-secondary btn-md btn-style-4" href="http://www.ifaf.info/wp-content/uploads/2016/11/IFAF-Membership-form.pdf" title="Download" target="_blank">Скачать</a>
                  <div class="stm-call-to-action-inner">
                    <h4 style="color:#ffffff">Download Questionnaire for applying for IFAF Membership!</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="vc_row-full-width vc_clearfix"></div>


        <div class="vc_row-full-width vc_clearfix"></div>
        <div class="clearfix">
        </div>
      </div>
    </div>
    <!--main-->
  </div>

  <div data-vc-full-width="true" data-vc-full-width-init="true" class="vc_row wpb_row vc_row-fluid stm-red-bg">
          <div class="wpb_column vc_column_container vc_col-sm-12">
            <div class="vc_column-inner ">
              <div class="wpb_wrapper">
                <div class="stm-call-to-action clearfix">
                  <a class="button btn-secondary btn-md btn-style-4" href="https://vk.com/ulafua" title="Перейти!">Перейти!</a>
                  <div class="stm-call-to-action-inner">
                    <h4 style="color:#ffffff">Тренерам,игрокам,функционерам команд.</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
<?php get_footer(); ?>
