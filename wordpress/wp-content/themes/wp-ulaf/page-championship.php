<?php /* Template Name: Champioship Page */ get_header(); ?>
  <?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="container results">
       <div class="row">
        <select class="filters-select">
          <option value=".game-info-2017">Сезон 2017</option>
        </select>
        <h2 >Результаты</h2>


        <div class="grid">
          <div class="col-md-12 game-info-2017 grid-item">
          <?php if( have_rows('game_result') ): while ( have_rows('game_result') ) : the_row();?>
          <h3><?php the_sub_field('division'); ?></h3>
           <?php $posts = get_sub_field('full_game_result_link');if( $posts ): ?>
           <?php foreach( $posts as $p ): ?>
          <a href="<?php echo get_permalink( $p->ID ); ?>">
            <div class="game-bar">
              <div class="col-md-2 game-date">
                 <?php $date = the_sub_field('game_date');?>
              </div>

              <div class="col-md-4 team-game result-left-team">
               <?php $posts = get_sub_field('home_team_name'); if( $posts ): ?>
               <?php foreach( $posts as $p ): ?>
               <span><?php echo get_the_title($p->ID); ?></span>
               <?php endforeach; endif; ?>
               <?php $posts = get_sub_field('home_team_logo'); if( $posts ): foreach( $posts as $p ): ?>
               <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
               <?php endforeach; endif; ?>

              </div>
              <div class="col-md-2 game-score">
                 <span><?php the_sub_field('game_score_result'); ?></span>
              </div>
              <div class="col-md-4 team-game">
               <?php $posts = get_sub_field('away_team_logo'); if( $posts ): foreach( $posts as $p ): ?>
               <img class="result-pic" src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
               <?php endforeach; endif; ?>
               <?php $posts = get_sub_field('away_team_name'); if( $posts ): ?>
               <?php foreach( $posts as $p ): ?>
               <span><?php echo get_the_title($p->ID); ?></span>
               <?php endforeach; endif; ?>
              </div>
             </div>
            </a>
          <?php endforeach; endif; ?>
        <?php endwhile; endif; ?>
     </div>
    <div class="col-md-12 game-info-2016 grid-item">

    </div>
    </div>
  </div>
</div>
</article>
<?php endwhile; else: // If 404 page error ?>
<article>
<h4 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h4>
</article>
<?php endif; ?>
</div>
<?php get_footer(); ?>
