<div class="container sponsors-block-slider">
  <?php $posts = get_field('our_partners'); if( $posts ): ?>

    <div class="row sponsors-block-slider--slider slider owl-carousel owl-theme">
      <?php foreach( $posts as $post): ?>
        <?php setup_postdata($post); ?>

        <div class="slide col-md-8 col-sm-12">
          <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>

            <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />

          <?php endif; ?>
        </div>

      <?php endforeach; ?>
    </div>
    <?php wp_reset_postdata();  ?>

  <?php endif; ?>
</div><!-- container -->
