<?php get_header(); ?>
<section class="container container-content">
  <div class="row">
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
                <?php
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
            <?php if ( has_post_thumbnail()) :?>

                 <?php the_post_thumbnail(); // Fullsize image for the single post ?>

            <?php endif; ?><!-- /post thumbnail -->



          </div><!--/ team-character -->
          <div class="col-md-12">
                <table class="player-score">
                  <tr>
                    <th>Результат последних игр :</th>
                    <th><img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png"></th>
                    <th><img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png"></th>
                    <th><img src="<?php echo get_template_directory_uri(); ?>/img/teams/eagles.png"></th>
                    <th><img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png"></th>
                    <th><img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png"></th>
                    <th><img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png"></th
                  </tr>

                  <tr class="player-work">
                      <td>1st Quarter</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                    </tr>
                    <tr>
                      <td>2nd Quarter</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                    </tr>
                    <tr class="player-work">
                      <td>3rd Quarter</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                    </tr>
                    <tr>
                      <td>4th Quarter</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                    </tr>
                    <tr class="player-work">
                      <td>Result</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                      <td>name</td>
                    </tr>

                    <tr class="player-work">
                      <td>123 </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>


                </table>
          </div><!-- player-score -->

        </div><!-- /.row -->
    <div class="row">
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
      <div id="featured">
            <?php foreach( $images as $image ): ?>
             <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
            <?php endforeach; ?>
      </div>

      <span class="orbit-caption" id="ezioCaption">This is an <em>awesome caption</em> for Ezio. <strong>Note:</strong> This whole image is linked</span>

      <span class="orbit-caption" id="marcusCaption">This is an <em>awesome caption</em> for Marcus with a <a href="http://www.zurb.com/playground" target="_blank" style="color: #fff">link</a></span>

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

  </div>
</section>

<?php get_footer(); ?>
