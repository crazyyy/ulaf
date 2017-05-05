<?php get_header(); ?>
<section class="container container-content-game">
  <div class="row single-game">
    <h1 class="game-title"><?php the_title(); ?></h1>
    <hr>

    <div class="col-md-12 team-single-game">
      <div class="col-md-4">
          <?php  $posts = get_field('first_home_team'); if( $posts ):
             foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
            <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                <?php endforeach; ?>
                 <?php endif; ?>
                 <?php wp_reset_query(); ?>
      </div>

       <div class="col-md-4">
          <div class="logo-main" style="margin-top: 0px; margin-right: 9px;">
            <a class="bloglogo" href="<?php echo home_url(); ?>">
              <img src="<?php echo get_template_directory_uri(); ?>/img/logo1.png" title="Home" alt="Logo">
            </a>
          </div>
          <div class="game-date">
             <?php // get raw date
                $date = get_field('date', false, false);
                // make date object
                $date = new DateTime($date);
                ?>
                <?php
                ?>
                <?php echo $date->format('j.m.Y');?>
           </div>
          <div class="single-game-score">
             <span><?php the_field('score_first_team');?></span> : <span><?php the_field('score_second_team');?></span>

          </div>

       </div>



        <div class="col-md-4">
          <?php  $posts = get_field('opposing_team');  if( $posts ):
            foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
             <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
               <?php endforeach; ?>
               <?php endif; ?>
               <?php wp_reset_query(); ?>
        </div>


    </div>
    <table class="team-game-score">
      <tr>
        <td>Команды</td>
        <td>1st Quarter</td>
        <td>2nd Quarter</td>
        <td>3rd Quarter</td>
        <td>4th Quarter</td>
      </tr>
      <tr>
        <td class="gold-score"><?php  $posts = get_field('team');  if( $posts ):
            foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
             <?php echo get_the_title( $p->ID ); ?>
               <?php endforeach; ?>
               <?php endif; ?>
        </td>
        <?php // check if the repeater field has rows of data
                      if( have_rows('first_team_game') ):
                      // loop through the rows of data
                      while ( have_rows('first_team_game') ) : the_row();?>
        <td class="gold-score">
        <?php the_sub_field('1st_quarter'); ?>
      </td>
        <td class="gold-score"><?php the_sub_field('2nd_quarter'); ?></td>
        <td class="gold-score"><?php the_sub_field('3rd_quarter'); ?></td>
        <td class="gold-score"><?php the_sub_field('4th_quarter'); ?></td>
      <?php endwhile; endif; ?>
      </tr>
      <tr>
        <td class="gold-score"><?php  $posts = get_field('opposing_team');  if( $posts ):
            foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
             <?php echo get_the_title( $p->ID ); ?>
               <?php endforeach; ?>
               <?php endif; ?>
        </td>
        <?php // check if the repeater field has rows of data
                      if( have_rows('second_team') ):
                      // loop through the rows of data
                      while ( have_rows('second_team') ) : the_row();?>
        <td class="gold-score">
        <?php the_sub_field('1st_quarter'); ?>
      </td>
        <td class="gold-score"><?php the_sub_field('2nd_quarter'); ?></td>
        <td class="gold-score"><?php the_sub_field('3rd_quarter'); ?></td>
        <td class="gold-score"><?php the_sub_field('4th_quarter'); ?></td>
      <?php endwhile; endif; ?>
      </tr>
      </table>
<div class="total-yds col-md-12">
   <div class="home-team-total-yds col-md-9">
        <div>Всего ярдов:<?php the_field('total_game_yards') ?></div>
        <div>Ярдов пасом:<?php the_field('total_game_pass_yards') ?></div>
        <div>Ярдов бегом:<?php the_field('total_game_rush_yards') ?></div>
        <div>Результативные действия:</div>
        <div class="score-authors">
<?php
    if( have_rows('point_scorer') ):
       while ( have_rows('point_scorer') ) : the_row();?>
  <span class="score-type"><?php the_sub_field('score_type'); ?></span>
  <span class="score-player-number">#<?php the_sub_field('scorer_number'); ?></span>
  <div class="score-player-name">
  <?php
       $posts = get_field('score_author');
          if( $posts ): ?>
            <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
               <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
             <?php endforeach; ?>
         <?php endif; ?>
  </div>
<?php endwhile; endif; ?>
</div>

      </div>
      <div class="away-team-total-yds col-md-3">
        <div>Всего ярдов:<?php the_field('total_game_yards_away') ?></div>
        <div>Ярдов бегом:<?php the_field('total_game_pass_yards_away') ?></div>
        <div>Ярдов пасом:<?php the_field('total_game_rush_yards_away') ?></div>
        <div>Результативные действия:</div>
        <div class="score-authors">
<?php
    if( have_rows('point_scorer_away') ):
       while ( have_rows('point_scorer_away') ) : the_row();?>
  <span class="score-type"><?php the_sub_field('score_type_away'); ?></span>
  <span class="score-player-number">#<?php the_sub_field('scorer_number_away'); ?></span>
  <div class="score-player-name">
  <?php
       $posts = get_field('score_author_away');
          if( $posts ): ?>
            <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
               <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
             <?php endforeach; ?>
         <?php endif; ?>
  </div>
<?php endwhile; endif; ?>
</div>
      </div>
</div>
<hr>



    </div>
  </div>
</section><!-- container container-content -->

<?php get_footer(); ?>
