<?php get_header(); ?>
  <div class="section history">
     <div class="container">
        <div class="row">

        <?php if (have_posts()): while (have_posts()) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(col-md-12); ?>>

<h2><?php the_title(); ?></h2>
<?php the_content(); ?>
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

<div class="container container-comments">
        <div class="row">
        <div class="col-md-12">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>





      <span class="date"><?php the_time('d F Y'); ?> <?php the_time('H:i'); ?></span>

      <span class="comments"><?php comments_popup_link( __( 'Leave your thoughts', 'wpeasy' ), __( '1 Comment', 'wpeasy' ), __( '% Comments', 'wpeasy' )); ?></span><!-- /post details -->


      <span class="tags"><p><?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?></p></span>

      <span class="category"><p><?php _e( 'Categorised in: ', 'wpeasy' ); the_category(', '); // Separated by commas ?></p></span>

      <span class="post"><p><?php _e( 'This post was written by ', 'wpeasy' ); the_author(); ?></p></span>



      <?php comments_template(); ?>

    </article>
    </div>
  </div>
</div>



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



<?php get_footer(); ?>
