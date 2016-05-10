<?php get_header(); ?>
<section class="container container-content">
  <div class="row team-head">
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>

        <div class="row team-card">


          <div class="player-main-photo col-md-12">
            <span class="team_name">Vinnytsia Wolves</span>
            <ul class="description">
              <li><span>ГОРОД :</span> <?php the_field('team_city');?>
              </li><!-- //ГОРОД -->
              <li><span>ГОД ОСНОВАНИЯ:</span>
                 <?php
                // get raw date
                $date = get_field('date', false, false);
                // make date object
                $date = new DateTime($date);
                ?>

                <?php echo $date->format('j M Y'); ?>


              </li><!-- //ГОД ОСНОВАНИЯ -->
              <li><span>ЦВЕТА:</span>
                  <span class="team-colors">
                                  <?php
                    // check if the repeater field has rows of data
                    if( have_rows('colors') ):

                      // loop through the rows of data
                        while ( have_rows('colors') ) : the_row();
                    ?>
                  <span style="background-color:<?php the_sub_field('colors'); ?>">
                  </span>

                    <?php
                        endwhile;

                    else :

                    endif;
                    ?>
                  </span><!-- team-colors -->
              </li><!-- //ЦВЕТА -->

              <li><span>ГЛАВНЫЙ ТРЕНЕР:</span>
                    <?php

                    $posts = get_field('head_coach');

                    if( $posts ): ?>

                      <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>

                      <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
                      <?php endforeach; ?>

                    <?php endif; ?>
                </li><!-- //ГЛАВНЫЙ ТРЕНЕР -->
            </ul><!-- //description -->
          </div><!-- /.player-main-photo -->
        <div class="col-md-12 team-character">

          <span class="team-logo">
            <?php if ( has_post_thumbnail()) :?>

                 <?php the_post_thumbnail('full'); // Fullsize image for the single post ?>

            <?php endif; ?><!-- /post thumbnail -->
          </span><!-- team-logo -->





                <?php

            $image = get_field('main_image');

            if( !empty($image) ): ?>

            <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />

            <?php endif; ?>

        </div><!--/ team-character -->
          <div class="col-md-12">
            <div class="col-md-12 inner teams">
              <table class="player-score team-opossing">
                  <tr>
                      <th>Результат последних игр :</th>

                          <?php

                          // check if the repeater field has rows of data
                          if( have_rows('team_game') ):

                            // loop through the rows of data
                              while ( have_rows('team_game') ) : the_row();
                            ?>
                    <th>
                          <?php
                                $posts = get_sub_field('opposing_team');
                                if( $posts ):
                                  foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                          <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">

                                  <?php endforeach; ?>
                                <?php endif; ?>
                    </th>

                               <?php endwhile; endif; ?>
                </tr>

              <tr class="player-work">
                    <td>1st Quarter</td>
                          <?php
                          $i = 0;
                        // check if the repeater field has rows of data
                        if( have_rows('team_game') ):

                          // loop through the rows of data
                            while ( have_rows('team_game') ) : the_row();
                          ?>
                    <td>
                        <?php the_sub_field('1st_quarter'); ?>
                        </td>
                        <?php
                        $i = $i + get_sub_field('1st_quarter');
                         ?>
                        <?php endwhile; endif; ?>
                    <td>
                    <?php echo $i; ?>
                    </td>
            </tr>
            <tr>
                <td>2nd Quarter</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('team_game') ):

                  // loop through the rows of data
                    while ( have_rows('team_game') ) : the_row();
                  ?>
             <td>

                <?php the_sub_field('2nd_quarter'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('2nd_quarter');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>
           <tr>
            <td>3rd Quarter</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('team_game') ):

                  // loop through the rows of data
                    while ( have_rows('team_game') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('3rd_quarter'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('3rd_quarter');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>
          <tr>
            <td>4th Quarter</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('team_game') ):

                  // loop through the rows of data
                    while ( have_rows('team_game') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('4th_quarter'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('4th_quarter');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


       </table><!-- player-score team-opossing -->
      </div><!-- player-score -->
    </div>
  </div><!-- /.row -->




    <div class="row">
      <div class="col-md-12 people-desciption">
        <h4>Основатели и Тренера</h4>



          <div class="row">

               <?php

                    $posts = get_field('people');

                    if( $posts ): ?>

                      <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>


              <div class="col-md-3 people-desciption">


                        <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID, "medium" ) ); ?>" alt="">



                        <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>

              </div>

                      <?php endforeach; ?>

                      <?php endif; ?>

          </div><!-- /.row -->


        </div><!-- col-md-12 people-desciption -->

        <div class="col-md-12 player-bio">
            <?php the_content(); ?>
        </div><!-- /.col-md-9 player-bio -->
    </div><!-- /row -->

      <?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

          <?php

          $images = get_field('team_gallery');

          if( $images ): ?>


          <!-- Slider -->

        <style type="text/css">
        </style>
        <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/orbit.css">


        <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery-1.4.1.min.js">
        </script>
        <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery.orbit.min.js">
        </script>

      <script type="text/javascript">
        $(window).load(function() {
        $('#featured').orbit({
          'bullets': true,
          'timer' : true,
          'animation' : 'horizontal-slide'
            });
          });
      </script>
<div class="owl-team-slide">


                <?php

          $images = get_field('team_gallery');

          if( $images ):

 foreach( $images as $image ):



  ?>



     <div class="item-slide">

             <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />

        </div>


            <?php endforeach; ?>
            <?php endif; ?>



</div>
        <!-- //Slider -->
        <?php endif; ?>

        <?php comments_template(); ?>

</article>
        <?php endwhile; else: ?>
          <article class="col-md-12">

            <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>

          </article>
        <?php endif; ?>

        <h2><span class="team_achievements">Достижения команды</span></h2>
       <span class="text-achiv"><?php
      // check if the repeater field has rows of data
      if( have_rows('team_achievements') ):
        // loop through the rows of data
          while ( have_rows('team_achievements') ) : the_row();
              // display a sub field value
              the_sub_field('team_achievements');

          endwhile;

          else :

          // no rows found

          endif;

        ?></span>

    <div class="row">
      <div class="col-md-12 sponsors">
        <h4>Спонсоры команды</h4>
        <div class="row">

               <?php

                    $posts = get_field('sponsors');

                    if( $posts ): ?>

                      <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>


            <div class="col-md-1 people-desciption">


                        <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID, "medium" ) ); ?>" alt="">



                        <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>

            </div>

                      <?php endforeach; ?>

                      <?php endif; ?>

        </div><!-- /.row -->
    </div><!--people-desciption -->
  </div><!-- row -->

  </div>
</section>

<?php get_footer(); ?>
