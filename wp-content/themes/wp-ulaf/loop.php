<?php if (have_posts()): while (have_posts()) : the_post(); ?>

  <div class="row">
    <div class="col-md-4">
      <a rel="nofollow" class="feature-img" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
        <?php if ( has_post_thumbnail()) :
          the_post_thumbnail('medium');
        else: ?>
          <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
        <?php endif; ?>
      </a><!-- /post thumbnail -->
    </div>

    <div class="col-md-8">
      <h2 class="inner-title">
        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
      </h2><!-- /post title -->
      <p class="date"><?php the_time('j.m.Y'); ?></p>

      <?php wpeExcerpt('wpeExcerpt20'); ?>

      <a href="<?php the_permalink(); ?>" class="read-more">Читать полностью...</a>
    </div>

  </div><!-- /.row -->

  <?php endwhile; else: ?>

    <div class="row">
      <div class="col-md-12">
        <h2 class="title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
      </div><!-- col-md-12 -->
    </div><!-- /.row -->

<?php endif; ?>
