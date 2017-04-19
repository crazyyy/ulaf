<?php /* Template Name: Games Calendar 2017 */ get_header(); ?>
  <?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="container results">
        <div class="row">
        <h2 ><?php the_title(); ?></h2>
        <div class="grid">
          <div class="col-md-12">
          <h3 class="calendar-division-title">Дивизион А</h3>
          <?php if( have_rows('calendar_a') ): while ( have_rows('calendar_a') ) : the_row();?>
              <div class="game-bar">
                <div class="col-md-5 team-game result-left-team">
                    <?php $posts = get_sub_field('home_team');
                     if( $posts ): ?>
                     <?php foreach( $posts as $p ):?>
                      <span><?php echo get_the_title($p->ID); ?></span>
                      <?php endforeach; endif; ?>
                     <?php $posts = get_sub_field('home_team');
                     if( $posts ): foreach( $posts as $p ):?>
                  <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>

                </div>
                <div class="col-md-1 game-date">
                    <?php $date = the_sub_field('game_date');?>
                </div>
               <div class="col-md-5 team-game">
                     <?php $posts = get_sub_field('away_team'); if( $posts ): foreach( $posts as $p ):?>
                  <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>
                      <?php $posts = get_sub_field('away_team');
                     if( $posts ): ?>
                     <?php foreach( $posts as $p ):?>
                      <span><?php echo get_the_title($p->ID); ?></span>
                      <?php endforeach; endif; ?>
              </div>
             </div>
<?php endwhile; endif; ?>

        </div>

 <div class="col-md-12">
      <h3 class="calendar-division-title">Дивизион B</h3>
          <?php if( have_rows('calendar_b') ): while ( have_rows('calendar_b') ) : the_row();?>
             <div class="game-bar">
                <div class="col-md-5 team-game result-left-team">
                    <?php $posts = get_sub_field('home_team');
                     if( $posts ): ?>
                     <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                      <span><?php echo get_the_title($p->ID); ?></span>
                      <?php endforeach; endif; ?>
                     <?php $posts = get_sub_field('home_team');
                     if( $posts ): foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                  <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>

                </div>
                <div class="col-md-1 game-date">
                    <?php
                    $date = the_sub_field('game_date');

                  ?>
                </div>

               <div class="col-md-5 team-game">

                     <?php $posts = get_sub_field('away_team'); if( $posts ): foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                  <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>
                      <?php $posts = get_sub_field('away_team');
                     if( $posts ): ?>
                     <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                      <span><?php echo get_the_title($p->ID); ?></span>
                      <?php endforeach; endif; ?>

              </div>
             </div>





<?php endwhile; endif; ?>

        </div>
         <div class="col-md-12">
          <h3 class="calendar-division-title">Дивизион С</h3>
          <?php

// check if the repeater field has rows of data
                  if( have_rows('calendar_c') ):

  // loop through the rows of data
                  while ( have_rows('calendar_c') ) : the_row();?>




                      <div class="game-bar">


                <div class="col-md-5 team-game result-left-team">
                    <?php $posts = get_sub_field('home_team');
                     if( $posts ): ?>
                     <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                      <span><?php echo get_the_title($p->ID); ?></span>
                      <?php endforeach; endif; ?>
                     <?php $posts = get_sub_field('home_team');
                     if( $posts ): foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                  <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>

                </div>
                <div class="col-md-1 game-date">
                    <?php
                    $date = the_sub_field('game_date');

                  ?>
                </div>

               <div class="col-md-5 team-game">

                     <?php $posts = get_sub_field('away_team'); if( $posts ): foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                  <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>
                      <?php $posts = get_sub_field('away_team');
                     if( $posts ): ?>
                     <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                      <span><?php echo get_the_title($p->ID); ?></span>
                      <?php endforeach; endif; ?>

              </div>
             </div>





<?php endwhile; endif; ?>

        </div>






    </div>
  </div>
</div>











    </article>
  <?php endwhile; else: // If 404 page error ?>
    <article>

      <h4
 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h4
>


    </article>
  <?php endif; ?>
  </div>
<?php get_footer(); ?>
