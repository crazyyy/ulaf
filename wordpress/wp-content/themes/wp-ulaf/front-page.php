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
                    <div class="stm-media-tabs-nav">
                      <ul class="stm-list-duty heading-font" role="tablist">
                        <li class="active">
                          <a href="#ifaf" aria-controls="ifaf" role="tab" data-toggle="tab">
                            <span>Ifaf</span>
                          </a>
                        </li>
                        <li>
                          <a href="#federations" aria-controls="federations" role="tab" data-toggle="tab">
                            <span>Federations</span>
                          </a>
                        </li>
                        <li>
                          <a href="#competitions" aria-controls="competitions" role="tab" data-toggle="tab">
                            <span>Competitions</span>
                          </a>
                        </li>
                        <li>
                          <a href="#other" aria-controls="other" role="tab" data-toggle="tab">
                            <span>Other</span>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active" id="ifaf">
                      <div class="row row-3 row-sm-2">
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3186 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf category-other tag-wada">
                            <a href="http://www.ifaf.info/ifaf/ifaf-took-part-wadas-webinar/" title="IFAF Took Part in WADA’s Webinar">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/wada-play-true1-570x350.jpg" class="img-responsive wp-post-image" alt="Webinar" height="350" width="570"> </div>
                              <div class="date heading-font">
                                February 1, 2017 </div>
                              <div class="title heading-font">
                                IFAF Took Part in WADA’s Webinar </div>
                            </a>
                            <div class="content">
                              <p>The International Federation of American Football participated in a webinar organized by the World Anti-Doping Agency (WADA) on the topic “Compliance Monitoring Program“. The webinar was intended for the National Anti-Doping Organizations (NADO) and the International Federation (IF). During this 60-minute webinar, participants were provided with an overview of the main areas of WADA’s new…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/ifaf-took-part-wadas-webinar/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/wada/">
                                  <i class="fa fa-tag"></i> WADA </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3175 post type-post status-publish format-standard has-post-thumbnail hentry category-competitions category-ifaf tag-beach-football tag-costa-rica tag-fefacr tag-world-championship">
                            <a href="http://www.ifaf.info/ifaf/2017-ifaf-beach-football-world-championship/" title="The IFAF Beach Football World Championship from 2nd to 5th November 2017">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/AlbahriFahadAsianBeachGamesDay1MVu_O23c-Krl-570x350.jpg" class="img-responsive wp-post-image" alt="IFAF Beach Football World Championship" height="350" width="570"> </div>
                              <div class="date heading-font">
                                January 27, 2017 </div>
                              <div class="title heading-font">
                                The IFAF Beach Football World Championship from 2nd to 5th November 2017 </div>
                            </a>
                            <div class="content">
                              <p>As it was previously announced, the first ever Beach Football World Championship in 2017 will take place in Costa Rica. Now, preparations go on and it is known that the competition is going to be organized in the beginning of November in Tamarindo. The International Federation of American Football (IFAF) has signed contract with the…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/2017-ifaf-beach-football-world-championship/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/beach-football/">
                                  <i class="fa fa-tag"></i> Beach Football </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3147 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf tag-general-meeting tag-ifaf">
                            <a href="http://www.ifaf.info/ifaf/ifaf-annual-general-meeting-september-23rd-2017/" title="The IFAF Annual General Meeting on September 23rd 2017">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/ifaf-news-570x350.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                January 14, 2017 </div>
                              <div class="title heading-font">
                                The IFAF Annual General Meeting on September 23rd 2017 </div>
                            </a>
                            <div class="content">
                              <p>The IFAF Annual General Meeting will be held on 23rd of September 2017 and it will take place in the capital of France, Paris. The invitation was made by the IFAF Secretary in accordance with the IFAF statutes. All IFAF members are informed about the date and venue for the General Meeting by notice in…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/ifaf-annual-general-meeting-september-23rd-2017/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/general-meeting/">
                                  <i class="fa fa-tag"></i> General Meeting </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade " id="federations">
                      <div class="row row-3 row-sm-2">
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3135 post type-post status-publish format-standard has-post-thumbnail hentry category-competitions category-europe category-ifaf tag-american-football tag-ifaf tag-iwga tag-plfa tag-world-games tag-wroclaw">
                            <a href="http://www.ifaf.info/ifaf/american-football-tournament-2017-world-games/" title="American Football Tournament at 2017 World Games">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/World-Games-2017-570x350.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                January 9, 2017 </div>
                              <div class="title heading-font">
                                American Football Tournament at 2017 World Games </div>
                            </a>
                            <div class="content">
                              <p>American football is going to be one of four invitational sports in the World Games 2017 which will take place in Wrocław, Poland, from 20th to 30th of July. American football, speedway, kickboxing and indoor rowing are the disciplines approved by the authorities of the International World Games Association (IWGA), that awards the rights to…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/american-football-tournament-2017-world-games/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>1</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/american-football/">
                                  <i class="fa fa-tag"></i> American football </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3104 post type-post status-publish format-standard has-post-thumbnail hentry category-americas category-competitions category-ifaf tag-beach-flag-world-championship-2017 tag-costa-rica tag-fefacr tag-ifaf">
                            <a href="http://www.ifaf.info/competitions/costa-rica-host-ifaf-beach-flag-world-championship-2017/" title="Costa Rica to Host the IFAF Beach Flag World Championship 2017">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/Costa-Rica-to-Host-the-IFAF-Beach-Flag-World-Championship-20.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                December 15, 2016 </div>
                              <div class="title heading-font">
                                Costa Rica to Host the IFAF Beach Flag World Championship 2017 </div>
                            </a>
                            <div class="content">
                              <p>The International Federation of American Football has revealed that the country chosen to host the first ever Beach Flag World Championship in 2017 will be Costa Rica! The decision was made by the IFAF Managing Committee on Wednesday, 14th December, after bidding process which last from 1st to 15th November 2016. Beach flag football is…
                              </p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/competitions/costa-rica-host-ifaf-beach-flag-world-championship-2017/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>2</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/beach-flag-world-championship-2017/">
                                  <i class="fa fa-tag"></i> Beach Flag World Championship 2017 </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-2991 post type-post status-publish format-standard has-post-thumbnail hentry category-competitions category-europe category-ifaf tag-ejc tag-european-junior-championships tag-france tag-ifaf-europe">
                            <a href="http://www.ifaf.info/competitions/ejc-2017-tournament-lineup-is-set/" title="EJC 2017 tournament lineup is set">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/EJC-2017-tournament-lineup-is-set-Post-570x350.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                November 23, 2016 </div>
                              <div class="title heading-font">
                                EJC 2017 tournament lineup is set </div>
                            </a>
                            <div class="content">
                              <p>The European Junior Championships (EJC) 2017 will be decided in France: The four top junior national Teams in American football from the continent will battle for the title in the final tournament in Paris on July 14 (semifinals) and 16 (medal games). The hosts from France (two EJC titles in 2004 and 2006) and reigning…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/competitions/ejc-2017-tournament-lineup-is-set/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>2</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/ejc/">
                                  <i class="fa fa-tag"></i> EJC </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade " id="competitions">
                      <div class="row row-3 row-sm-2">
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3175 post type-post status-publish format-standard has-post-thumbnail hentry category-competitions category-ifaf tag-beach-football tag-costa-rica tag-fefacr tag-world-championship">
                            <a href="http://www.ifaf.info/ifaf/2017-ifaf-beach-football-world-championship/" title="The IFAF Beach Football World Championship from 2nd to 5th November 2017">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/AlbahriFahadAsianBeachGamesDay1MVu_O23c-Krl-570x350.jpg" class="img-responsive wp-post-image" alt="IFAF Beach Football World Championship" height="350" width="570"> </div>
                              <div class="date heading-font">
                                January 27, 2017 </div>
                              <div class="title heading-font">
                                The IFAF Beach Football World Championship from 2nd to 5th November 2017 </div>
                            </a>
                            <div class="content">
                              <p>As it was previously announced, the first ever Beach Football World Championship in 2017 will take place in Costa Rica. Now, preparations go on and it is known that the competition is going to be organized in the beginning of November in Tamarindo. The International Federation of American Football (IFAF) has signed contract with the…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/2017-ifaf-beach-football-world-championship/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/beach-football/">
                                  <i class="fa fa-tag"></i> Beach Football </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3135 post type-post status-publish format-standard has-post-thumbnail hentry category-competitions category-europe category-ifaf tag-american-football tag-ifaf tag-iwga tag-plfa tag-world-games tag-wroclaw">
                            <a href="http://www.ifaf.info/ifaf/american-football-tournament-2017-world-games/" title="American Football Tournament at 2017 World Games">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/World-Games-2017-570x350.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                January 9, 2017 </div>
                              <div class="title heading-font">
                                American Football Tournament at 2017 World Games </div>
                            </a>
                            <div class="content">
                              <p>American football is going to be one of four invitational sports in the World Games 2017 which will take place in Wrocław, Poland, from 20th to 30th of July. American football, speedway, kickboxing and indoor rowing are the disciplines approved by the authorities of the International World Games Association (IWGA), that awards the rights to…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/american-football-tournament-2017-world-games/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>1</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/american-football/">
                                  <i class="fa fa-tag"></i> American football </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3104 post type-post status-publish format-standard has-post-thumbnail hentry category-americas category-competitions category-ifaf tag-beach-flag-world-championship-2017 tag-costa-rica tag-fefacr tag-ifaf">
                            <a href="http://www.ifaf.info/competitions/costa-rica-host-ifaf-beach-flag-world-championship-2017/" title="Costa Rica to Host the IFAF Beach Flag World Championship 2017">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/Costa-Rica-to-Host-the-IFAF-Beach-Flag-World-Championship-20.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                December 15, 2016 </div>
                              <div class="title heading-font">
                                Costa Rica to Host the IFAF Beach Flag World Championship 2017 </div>
                            </a>
                            <div class="content">
                              <p>The International Federation of American Football has revealed that the country chosen to host the first ever Beach Flag World Championship in 2017 will be Costa Rica! The decision was made by the IFAF Managing Committee on Wednesday, 14th December, after bidding process which last from 1st to 15th November 2016. Beach flag football is…
                              </p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/competitions/costa-rica-host-ifaf-beach-flag-world-championship-2017/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>2</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/beach-flag-world-championship-2017/">
                                  <i class="fa fa-tag"></i> Beach Flag World Championship 2017 </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade " id="other">
                      <div class="row row-3 row-sm-2">
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3186 post type-post status-publish format-standard has-post-thumbnail hentry category-ifaf category-other tag-wada">
                            <a href="http://www.ifaf.info/ifaf/ifaf-took-part-wadas-webinar/" title="IFAF Took Part in WADA’s Webinar">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/wada-play-true1-570x350.jpg" class="img-responsive wp-post-image" alt="Webinar" height="350" width="570"> </div>
                              <div class="date heading-font">
                                February 1, 2017 </div>
                              <div class="title heading-font">
                                IFAF Took Part in WADA’s Webinar </div>
                            </a>
                            <div class="content">
                              <p>The International Federation of American Football participated in a webinar organized by the World Anti-Doping Agency (WADA) on the topic “Compliance Monitoring Program“. The webinar was intended for the National Anti-Doping Organizations (NADO) and the International Federation (IF). During this 60-minute webinar, participants were provided with an overview of the main areas of WADA’s new…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/ifaf/ifaf-took-part-wadas-webinar/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/wada/">
                                  <i class="fa fa-tag"></i> WADA </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3153 post type-post status-publish format-standard has-post-thumbnail hentry category-other tag-arisf tag-ioc tag-raffaele-chiulli tag-thomas-bach">
                            <a href="http://www.ifaf.info/other/arisf-president-raffaele-chiulli-meets-ioc-president-thomas-bach/" title="ARISF President Raffaele Chiulli meets with IOC President Thomas Bach">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/20170119_ARISF-IOC_Presidents-570x350.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                January 19, 2017 </div>
                              <div class="title heading-font">
                                ARISF President Raffaele Chiulli meets with IOC President Thomas Bach </div>
                            </a>
                            <div class="content">
                              <p>ARISF President Raffaele Chiulli held another very constructive meeting with the IOC President Thomas Bach at the IOC Headquarters in Lausanne to discuss the continued close cooperation between the two Organizations. Special focus was put on the appreciated contribution of ARISF in the recognition process of International Federations, good governance, ethics and the protection of…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/other/arisf-president-raffaele-chiulli-meets-ioc-president-thomas-bach/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/arisf/">
                                  <i class="fa fa-tag"></i> ARISF </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                          <div class="stm-single-post-loop post-3100 post type-post status-publish format-standard has-post-thumbnail hentry category-other tag-wada">
                            <a href="http://www.ifaf.info/other/wada-statement-regarding-conclusion-mclaren-investigation/" title="WADA Statement regarding conclusion of McLaren Investigation">
                              <div class="image">
                                <div class="stm-plus"></div>
                                <img src="<?php echo get_template_directory_uri(); ?>/img/say-no-to-doping-570x350.jpg" class="img-responsive wp-post-image" alt="" height="350" width="570"> </div>
                              <div class="date heading-font">
                                December 10, 2016 </div>
                              <div class="title heading-font">
                                WADA Statement regarding conclusion of McLaren Investigation </div>
                            </a>
                            <div class="content">
                              <p>The World Anti-Doping Agency (WADA) acknowledges the findings outlined by the McLaren Investigation Report Part II, which was released earlier today by Professor Richard H. McLaren during a press conference that he held in London, England.&nbsp; Report Part II reconfirms institutionalized manipulation of the doping control process in Russia, which was first exposed via Report…</p>
                            </div>
                            <div class="post-meta heading-font">
                              <div class="comments-number">
                                <a href="http://www.ifaf.info/other/wada-statement-regarding-conclusion-mclaren-investigation/#comments">
                                  <i class="fa fa-commenting"></i>
                                  <span>0</span>
                                </a>
                              </div>
                              <div class="post_list_item_tags">
                                <a href="http://www.ifaf.info/tag/wada/">
                                  <i class="fa fa-tag"></i> WADA </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="container clients-images">
        <div class="row">
          <div class="slider-img">
            <!-- Slide Start -->

            <div class="slide col-md-4">
              <div class="clients-item-img">
                <img src="<?php echo get_template_directory_uri(); ?>/img/FISU.png" alt="">
              </div>
            </div>

            <div class="slide col-md-4">
              <div class="clients-item-img">
                <img src="<?php echo get_template_directory_uri(); ?>/img/FISU.png" alt="">
              </div>
            </div>
             <div class="slide col-md-4">
              <div class="clients-item-img">
                <img src="<?php echo get_template_directory_uri(); ?>/img/FISU.png" alt="">
              </div>
            </div>

            <!-- Slide End -->
            <!-- Slide Start -->
            <!-- Slide End -->
            <!-- Slide Start -->
            <!-- Slide End -->
          </div>
        </div>
      </div>
      <script>
        $(document).ready(function(){
        $('.slider-img').owlCarousel({

    nav: true,
    items: 1,
    itemClass: '.slide',
    nav: true,
    loop:true,
    center:true
        });
});
      </script>
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
