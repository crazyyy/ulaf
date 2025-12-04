<?php get_header(); ?>
  <main class="main-content col-12" itemscope itemprop="mainContentOfPage">
    <div class="row">

      <section class="section col-9">
        <div class="content-container">
          <?php if (have_posts()): while (have_posts()) : the_post(); ?>

            <header class="post-header">
              <h1 class="section-title">
                <?php the_title(); ?>
              </h1>
            </header><!-- /.post-header -->

            <article id="post-<?php the_ID(); ?>" <?php post_class('post-content'); ?>>

              <?php if ( has_post_thumbnail()) :?>
                <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                  <?php the_post_thumbnail(); // Filesize image for the single post ?>
                </a>
              <?php endif; ?>

              <?php the_content(); ?>

              <p><?php _e( 'Categorized in: ', 'wpeb' ); the_category(', '); // Separated by commas ?></p>

            </article><!-- /.post-content -->
          <?php endwhile; endif; ?>
        </div>
      </section>

      <?php get_sidebar(); ?>

    </div>
    <!-- /.row -->

  </main>
<?php get_footer(); ?>
