<?php get_header(); ?>
  <?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="stm-title-box-unit ">
        <div class="stm-page-title">
          <div class="container">
            <div class="clearfix stm-title-box-title-wrapper">
              <h3><?php the_title(); ?></h3>
            </div>
          </div>
        </div>
      </div>
      <div class="post-thumbnail">


      <?php if ( has_post_thumbnail()) :?>
        <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
          <?php the_post_thumbnail(); // Fullsize image for the single post ?>
        </a>
      <?php endif; ?><!-- /post thumbnail -->
      </div>
      <div id="main">

      <div class="container">

        <div class="vc_row wpb_row vc_row-fluid">
          <div class="wpb_column vc_column_container vc_col-sm-8">
           <?php the_content(); ?>
          </div>
          <div class="wpb_column vc_column_container vc_col-sm-4">
            <div class="vc_column-inner ">
              <?php get_sidebar('right'); ?>
            </div>
          </div>
        </div>
        <div class="clearfix">
        </div>
      </div>
    </div>
    <!--main-->
  </div>
  <!--wrapper-->
    </article>
  <?php endwhile; else: // If 404 page error ?>
    <article>

      <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>

    </article>
  <?php endif; ?>

<?php get_footer(); ?>
