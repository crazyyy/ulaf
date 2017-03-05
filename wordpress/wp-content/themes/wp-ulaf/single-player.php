
<?php get_header(); ?>
<section class="container container-content">
  <div class="row">
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>






        <div class="row player-card">

          <div class="col-md-3 player-picture">
            <div class="player-pic">
                <img src="img/logo.png" alt="">
              </div>
          </div>
          <div class="col-md-3 description">
            <div class="player-name">Antonio Brown</div>
            <div class="stats-bar">
              <div class="games">Игры : 15</div>
              <div class="tds">Тачдауны : 1</div>
              <div class="yards">Ярды : 1203</div>
            </div>
          </div>
        </div><!--/ player-character -->

  <?php
  $allpositions = get_field('player_position');
  if( $allpositions ):
    foreach( $allpositions as $position ):

      if ($position =='qb') {
         get_template_part('includes/player-score-qb');
      } else if ($position =='wr' ) {
         get_template_part('includes/player-score-wr');
      } else if ($position =='c' ) {
         get_template_part('includes/player-score-c');
      } else if ($position =='cb' ) {
        get_template_part('includes/player-score-cb');
      } else if ($position =='de' ) {
        get_template_part('includes/player-score-de');
     } else if ($position =='dt' ) {
     get_template_part('includes/player-score-dt');

     } else if ($position =='fb' ) {
     get_template_part('includes/player-score-fb');

     } else if ($position =='fs' ) {
     get_template_part('includes/player-score-fs');

     } else if ($position =='ilb' ) {
     get_template_part('includes/player-score-ilb');

     } else if ($position =='k' ) {
     get_template_part('includes/player-score-k');

     } else if ($position =='mlb' ) {
     get_template_part('includes/player-score-mlb');

     } else if ($position =='olb' ) {
     get_template_part('includes/player-score-olb');

     } else if ($position =='p' ) {
     get_template_part('includes/player-score-p');

     } else if ($position =='ss' ) {
     get_template_part('includes/player-score-ss');

     } else if ($position =='te' ) {
     get_template_part('includes/player-score-te');

     } else if ($position =='rb' ) {
     get_template_part('includes/player-score-rb');



      } else {
        // nothing
      }

    endforeach;
    endif; ?>
</div><!-- /.row -->
    <div class="row">
    <div class="col-md-12 player-bio">
            <?php the_content(); ?>
          </div><!-- /.col-md-9 player-bio -->
      </div><!-- /row -->

      <?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

  <div class="owl-player-slide">
          <?php $images = get_field('player_gallery');
              if( $images ):
        foreach( $images as $image ): ?>
        <div class="item-slide">
             <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
        </div>
            <?php endforeach; ?>
            <?php endif; ?>
  </div>

      <?php comments_template(); ?>
</article>
  <?php endwhile; else: ?>
  <article class="col-md-12">

      <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
  </article>
    <?php endif; ?>

  </div>
</section>

<?php get_footer(); ?>
