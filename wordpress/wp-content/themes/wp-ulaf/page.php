<?php get_header(); ?>
  <section class="section-history">
   <div class="container">
      <div class="row">

        <?php if (have_posts()): while (have_posts()) : the_post(); ?>

          <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>

            <h1 class="page-title"><?php the_title(); ?></h1>
            <?php the_content(); ?>

            <?php edit_post_link(); ?>


          </article>
        <?php endwhile; else: // If 404 page error ?>
          <article class="col-md-12">

            <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>

          </article>

        <?php endif; ?>

      </div><!-- row -->
    </div><!-- container -->
  </section><!-- section-history -->

<?php get_template_part('relative-pages'); ?>
<?php get_footer(); ?>
