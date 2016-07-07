<?php get_header(); ?>
<section class="container container-content people-page">
  <div class="row">
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>
        <div class="row player-card">
          <div class="player-main-photo col-md-3">
            <?php if ( has_post_thumbnail()) :?>
              <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php the_post_thumbnail(); // Fullsize image for the single post ?>
              </a>
            <?php endif; ?><!-- /post thumbnail -->
          </div><!-- /.player-main-photo -->
          <div class="col-md-9 player-character"><!-- player-character  -->
            <ul class="description people">
                  <li><?php the_title(); ?></li>
                  <li><span>КОМАНДА :</span> <?php
                      $posts = get_field('team');
                      if( $posts ): ?>
                        <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                        <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      </li>
                  <li><span>ВОЗРАСТ :</span> <?php
                  // get raw date
                  $date = get_field('birthday', false, false);
                  // make date object
                  $date = new DateTime($date); ?>
                  <?php
                  ?>
                  <?php echo $date->format('j M Y');?>
                       <?php
                        $birthday = $date->format('Y');
                        $current_year = date("Y");
                        $age = $current_year - $birthday;
                        echo $age; ?></li>
                  <li><span>ПРИБЫЛ С :</span> <?php the_field('from');?></li>
                  <li><span>РОЛЬ В КОМАНДЕ :</span> <?php the_field('role');?></li>
                  <li><span>В КОМАНДЕ С :</span> <?php
                  // get raw date
                  $date = get_field('in_team', false, false);
                  // make date object
                  $date = new DateTime($date); ?>
                  <?php
                  ?>
                  <?php echo $date->format('j M Y');?>
                       <?php
                        $birthday = $date->format('Y');
                        $current_year = date("Y");
                        $age = $current_year - $birthday;
                        echo $age; ?>
                        </li>
                  <li><span>ПРЕДЫДУЩИЕ КОМАНДЫ :</span><?php the_field('early_teams');?> </li>
                  <li><span>КОНТАКТЫ:</span>
                    <a href="<?php the_field('link_vk');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/vkontakte.png" alt="VK"></a>
                    <a href="<?php the_field('link_fb');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/facebook.png" alt="Facebook"></a>
                    <a href="<?php the_field('link_inatagram');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/instagram.png" alt="Instagram"></a>
                  </li>
           </ul>
          </div><!--/ player-character -->
        </div><!-- /.row -->
    <div class="row">
    <div class="col-md-12 player-bio poeple">
            <?php the_content(); ?>
          </div><!-- /.col-md-9 player-bio -->
      </div><!-- /row -->

      <?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

    <div class="owl-people-slide">
      <?php  $images = get_field('people_gallery');
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

