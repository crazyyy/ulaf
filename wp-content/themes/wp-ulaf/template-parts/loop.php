<?php if (have_posts()): while (have_posts()) : the_post(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class('loop-item col-12'); ?>>

    <a class="loop-thumbnail" href="<?php the_permalink(); ?>" rel="nofollow" title="<?php the_title(); ?>">
      <?php if ( has_post_thumbnail()) { ?>
        <img src="<?php echo the_post_thumbnail_url('medium'); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
      <?php } else { ?>
        <img src="<?php echo wpeb_catch_first_image(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
      <?php } ?>
    </a><!-- /.loop-thumbnail -->

    <h2 class="loop-title">
      <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
        <?php the_title(); ?>
      </a>
    </h2><!-- /.loop-title -->

    <div class="loop-date">
      <?php the_time('j F Y'); ?> <span><?php the_time('G:i'); ?></span>
    </div><!-- /.loop-date -->


    <?php wpeb_excerpt('wpeb_excerpt_40'); ?>

  </article><!-- /.loop-item -->
<?php endwhile; endif; ?>
