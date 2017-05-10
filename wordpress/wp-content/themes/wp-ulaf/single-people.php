
<?php get_header(); ?>
<section class="container container-content">
  <div class="row">
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>
        <div class="row player-card">
          <div class="col-md-3 player-picture">
            <div class="player-pic">
              <?php if ( has_post_thumbnail()) :?>
               <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php the_post_thumbnail(); // Fullsize image for the single post ?>
               </a>
                <?php endif; ?>
                <div class="socials">
                  <a href="<?php the_field('link_vk');?>"><i class="fa fa-vk" aria-hidden="true"></i></a>
                  <a href="<?php the_field('link_instagram');?>"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                  <a href="<?php the_field('link_fb');?>"><i class="fa fa-facebook-official" aria-hidden="true"></i></a>
                  <a href="<?php the_field('link_twitter');?>"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                </div>
              </div>
          </div>
          <div class="col-md-3 description">
            <div class="people-name"><?php the_title(); ?></div>
             <div class="biography">
              <div class="born">Родился: <span><?php $date = get_field('birthday', false, false); $date = new DateTime($date); ?>
                 <?php ?>
                  <?php echo $date->format('j M Y');?> |
                    <?php $birthday = $date->format('Y'); $current_year = date("Y"); $age = $current_year - $birthday;
                        echo $age; ?></span>
                </div>
              <div class="nationality">В команде с: <span>
               <?php $date = get_field('in_team', false, false); $date = new DateTime($date); ?>
                <?php ?>
                  <?php echo $date->format('j M Y');?> |
                   <?php $birthday = $date->format('Y'); $current_year = date("Y"); $age = $current_year - $birthday;
                      echo $age; ?></span>
               </div>

              <div class="height-weight">Рост и вес: <span><?php the_field('height');?> см | <?php the_field('weight');?> кг</span>
              </div>
              <div class="position">Должность в команде: <span><?php the_field('role');?></span>
              </div>
            </div>
          </div>
          <div class="col-md-4 current-team">
            <?php $posts = get_field('team'); if( $posts ): ?> <?php foreach( $posts as $p ): ?>
              <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_post_thumbnail( $p->ID ); ?></a>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div><!--/ player-character -->
      <div class="short-bio">
        <h3>Биография</h3>
        <p><?php the_content(); ?></p>
      </div>
      <?php comments_template(); ?>
</article>
<?php endwhile; else: ?>
<article class="col-md-12">
<h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
</article>
<?php endif; ?>


</div><!-- /.row -->
  <div class="row">
    <div class="col-md-12 player-bio">

    </div><!-- /.col-md-9 player-bio -->
  </div><!-- /row -->

  <?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

  <div class="owl-player-slide">
    <?php $images = get_field('player_gallery'); if( $images ): foreach( $images as $image ): ?>
      <div class="item-slide">
        <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
      </div>
    <?php endforeach; ?>
   <?php endif; ?>
  </div>
 </div>
</section>

<?php get_footer(); ?>
