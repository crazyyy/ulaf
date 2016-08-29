<section class="relative-pages">
  <div class="container">
    <div class="row">

      <?php $popularpost = new WP_Query( array( 'posts_per_page' => 3, 'post__not_in' =>array(get_the_ID()) , 'meta_key' => 'wpb_post_views_count', 'orderby' => 'wpb_post_views_count', 'order' => 'DESC'  ) ); while ( $popularpost->have_posts() ) : $popularpost->the_post(); ?>
        <div class="col-md-4 relative-posts">
          <a href="<?php the_permalink(); ?>" class="hover_image">
            <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
              <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
            <?php endif; ?>
            <span class="date"><?php the_time('j.m.Y'); ?> <?php the_time('H:i'); ?></span>
          </a>
        </div>
      <?php endwhile; ?>
      <?php wp_reset_query(); ?>

    </div><!-- row -->
  </div><!-- container -->
</section><!-- relative-pages -->



