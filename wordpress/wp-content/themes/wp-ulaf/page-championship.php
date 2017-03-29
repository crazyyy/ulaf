<?php /* Template Name: Champioship Page */ get_header(); ?>




  <?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="container results">
        <div class="row">
        <select class="filters-select">
          <option value=".game-info-2017">Сезон 2017</option>
          <option value=".game-info-2016">Сезон 2016</option>
        </select>
        <h2 >Результати</h2>


        <div class="grid">
          <div class="col-md-12 game-info-2017 grid-item">
            <h4>Дивизион A</h4>

            <div class="col-md-1 game-date">
              16.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Pirates (Odessa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
            </div>
            <div class="col-md-2 game-score">
              <span>00-48</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
              <span>Lumberjacks (Uzhgorod)</span>
            </div>

        </a>
        <h4>Дивизион A</h4>

            <div class="col-md-1 game-date">
              16.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Pirates (Odessa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
            </div>
            <div class="col-md-2 game-score">
              <span>00-48</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
              <span>Lumberjacks (Uzhgorod)</span>
            </div>

        </a>
        <h4>Дивизион A</h4>

            <div class="col-md-1 game-date">
              16.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Pirates (Odessa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
            </div>
            <div class="col-md-2 game-score">
              <span>00-48</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
              <span>Lumberjacks (Uzhgorod)</span>
            </div>

        </a>
    </div>



<div class="col-md-12 game-info-2016 grid-item">
      <h4
>Дивизион C</h4
>

            <div class="col-md-1 game-date">
              26.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gepardes (Yuzhny)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
            </div>
            <div class="col-md-2 game-score">
              <span>06-08</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/vikings.png">
              <span>Vikings (Nikolaev)</span>
            </div>

        </a>
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
