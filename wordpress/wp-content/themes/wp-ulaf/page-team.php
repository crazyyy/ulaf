<?php /* Template Name: Team ULAF */ get_header(); ?>

  <?php if (have_posts()): while (have_posts()) : the_post(); ?>


    <div class="our-team">
      <div class="container">
        <div class="row">

          <h2><?php the_title(); ?> </h2>

          <div class="vertical-tabs">

            <?php $posts = get_field('team'); if( $posts ): ?>

              <ul class="tabs vertical" data-tab="">
                <?php $i = 1; foreach( $posts as $post): ?>
                  <?php setup_postdata($post); ?>

                  <?php if($i == 1) { $arguments = 'aria-selected="true" tabindex="0"'; } else { $arguments = 'aria-selected="false" tabindex="-1"'; } ?>

                  <li class="tab-title"><a href="#panela<?php echo $i; ?>" <?php echo $arguments; ?>><?php the_title(); ?></a></li>

                <?php $i++; endforeach; ?>
              </ul>
              <?php wp_reset_postdata(); ?>

            <?php endif; ?>


          <div class="tabs-content">


            <?php $posts = get_field('team'); if( $posts ): ?>
              <?php $i = 1; foreach( $posts as $post): ?>
                <?php setup_postdata($post); ?>

                <?php if($i == 1) {
                    $class = 'content active';
                  } else {
                    $class = 'content';
                } ?>

                  <div class="<?php echo $class; ?>" id="panela<?php echo $i; ?>" <?php echo $arguments; ?>>

                    <div class="person-image">
                    <?php the_post_thumbnail('large'); ?>

                      <div class="person-info">
                        <span class="person-name"><?php the_title(); ?></span>
                        <button><a href="<?php echo get_permalink( $post->ID ); ?>">Профиль</a></button>
                        <span class="person-role"><?php the_field('person_role_tabs'); ?></span>
                        <span class="person-description"><?php the_field('person_bio_short'); ?></span>
                      </div>
                    </div>

                  </div>

                <?php $i++; endforeach; ?>

              <?php wp_reset_postdata(); ?>

            <?php endif; ?>



          </div>

        </div>

      </div>

    </div>
  </div>

<?php endwhile; endif; ?>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>


<?php get_footer(); ?>





    <!-- <div class="col-md-5 left-bg-block ">
          <div class="team-sign">
            <?php the_title(); ?>
          </div>
          <?php

          $posts = get_field('team');

          if( $posts ): ?>
            <ul class="team-list" style="overflow-y:auto; height: 325px;">
          <?php foreach( $posts as $post): // variable must be called $post (IMPORTANT) ?>
          <?php setup_postdata($post); ?>
            <li><span name="tab1"><?php the_title(); ?></span></li>

          <?php endforeach; ?>
          </ul>

          <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
    <?php endif; ?>
    </div>
    <div class="col-md-7 right-bg-block">
      <div class="person" id="tab1">
        <div class="person-image">
          <img src="<?php echo get_template_directory_uri(); ?>/img/landry1.jpg" alt="">
          <div class="person-info">
            <span class="person-name">Максим Шило</span>
            <span class="person-role">Игрок</span>
            <span class="person-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum repudiandae optio sequi voluptatibus quaerat veritatis cupiditate amet laborum praesentium illo labore provident vitae ipsum nobis, perferendis debitis, blanditiis eaque maiores explicabo ea dicta soluta eveniet facere minima? Dignissimos dolorem est, illo dolorum vero quam reprehenderit saepe animi dolor in! Nulla, commodi minus quidem eius nam debitis distinctio quo sit repellat impedit veritatis harum ducimus quasi nostrum deserunt quaerat exercitationem dignissimos corrupti. Id magnam earum placeat dignissimos explicabo quae libero aliquam reiciendis obcaecati in. Eos error repudiandae dolore voluptatibus nisi cum vero quas voluptatem adipisci odit similique saepe aliquam, commodi reiciendis.</span>
          </div>
        </div>
      </div>
      <div class="person" id="tab2">
        <div class="person-image">
          <img src="<?php echo get_template_directory_uri(); ?>/img/brady.jpg" alt="">
          <div class="person-info">
            <span class="person-name">Миша Гусак</span>
            <span class="person-role">Игрок</span>
            <span class="person-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda velit pariatur deserunt nam eveniet aliquid aliquam. Assumenda dolores voluptas dolorem, blanditiis rem eaque provident, porro facere impedit consectetur laudantium animi.</span>
          </div>
        </div>
      </div>
      <div class="person" id="tab3">
        <div class="person-image">
          <img src="<?php echo get_template_directory_uri(); ?>/img/mack.jpg" alt="">
          <div class="person-info">
            <span class="person-name">Коля Лесовой</span>
            <span class="person-role">Игрок</span>
            <span class="person-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda velit pariatur deserunt nam eveniet aliquid aliquam. Assumenda dolores voluptas dolorem, blanditiis rem eaque provident, porro facere impedit consectetur laudantium animi.</span>
          </div>
        </div>
      </div>
    </div> -->
