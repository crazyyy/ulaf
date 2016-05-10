<section class="relative-pages">
  <div class="container">
    <div class="row">

      <?php query_posts("showposts=3&cat=1"); ?>
      <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div class="col-md-4 relative-posts">
          <a href="<?php the_permalink(); ?>" class="hover_image">
            <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
              <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
            <?php endif; ?>
            <span class="date"><?php the_time('j.m.Y'); ?> <?php the_time('H:i'); ?></span>
          </a>
        </div>

      <?php endwhile; endif; ?>
      <?php wp_reset_query(); ?>

    </div><!-- row -->
  </div><!-- container -->
</section><!-- relative-pages -->
