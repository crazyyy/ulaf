<?php /* Template Name: Champioship Page */ get_header(); ?>
  <?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1 class="page-title inner-title"><?php the_title(); ?></h1>
        <div class="row championship">

          <div class="col-md-1 game-date">
            24.04
          </div>
          <div class="col-md-4 team-game">
            <span>Бандити (Київ)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png">
          </div>
          <div class="col-md-2 game-score">
            <span>44-56</span>
          </div>
          <div class="col-md-4 team-game">
          <img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png">
            <span>Вовки (Вінниця)</span>
          </div>
          <div class="col-md-1 team-city">
            Вінниця
          </div>
      </div><!-- row championship -->


      <div class="row championship">

          <div class="col-md-1 game-date">
            15.05
          </div>
          <div class="col-md-4 team-game">
            <span>Бульдоги (Київ)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png">
          </div>
          <div class="col-md-2 game-score">
            <span>34-26</span>
          </div>
          <div class="col-md-4 team-game">
          <img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png">
            <span>Атланти (Харків)</span>
          </div>
          <div class="col-md-1 team-city">
            Харків
        </div><!-- row championship -->






    </article>
  <?php endwhile; else: // If 404 page error ?>
    <article>

      <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>


    </article>
  <?php endif; ?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
