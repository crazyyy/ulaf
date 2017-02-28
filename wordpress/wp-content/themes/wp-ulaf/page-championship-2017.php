<?php /* Template Name: Чемпионат 2017 */ get_header(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

<?php $posts = get_field('match_result'); if( $posts ): ?>
    <?php foreach( $posts as $post): // variable must be called $post (IMPORTANT) ?>
<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
            <?php the_field('date', $p->ID); ?>
            </div>
            <a href="<?php echo get_permalink( $p->ID ); ?>">
            <div class="col-md-4 team-game">

              <span><?php the_field('first_team', $p->ID); ?></span>

            <!-- <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?> -->
            </div>
            <div class="col-md-2 game-score">
              <span><?php the_field('score_first_team', $p->ID); ?>-<?php the_field('score_second_team', $p->ID); ?></span>
            </div>
            <div class="col-md-5 team-game">
            <!-- <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?> -->
              <span><?php the_field('opposing_team', $p->ID); ?></span>
            </div>
            </a>
      </div><!-- row championship -->
    </div><!-- game-info -->
<?php endforeach; ?>
<?php endif; ?>


    </article>
    <article>

      <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>


    </article>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
