<?php get_header(); ?>
  <div class="section history">
     <div class="container">
        <div class="row">

        <?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(col-md-12); ?>>

<h2><?php the_title(); ?></h2>
<?php the_content(); ?>




    </article>
  <?php endwhile; else: // If 404 page error ?>
<article class="col-md-12">

      <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>

    </article>
  <?php endif; ?>


          <div class="">



        </div>
      </div>
    </div>
  </div>

                    <div class="section photo">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="<?php echo get_template_directory_uri(); ?>/img/teams/sbor.jpeg">
                                </div>
                                <div class="col-md-4">
                                  <img src="<?php echo get_template_directory_uri(); ?>/img/teams/sbor2.jpeg">
                                </div>
                                <div class="col-md-4"></div>
                            </div>
                        </div>
                    </div>

<?php get_footer(); ?>
