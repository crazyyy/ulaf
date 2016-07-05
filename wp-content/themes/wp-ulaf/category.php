<?php get_header(); ?>
  <section class="section-news category">
    <div class="container">
      <div class="row category-news">


        <h1 class="cat-title inner-title col-md-12"><?php _e( 'Categories for', 'wpeasy' ); the_category(', '); ?></h1>

        <?php get_template_part('loop'); ?>

        <?php get_template_part('pagination'); ?>

      </div><!-- row -->
    </div><!-- container -->
  </section><!-- section-news -->

<?php get_footer(); ?>

