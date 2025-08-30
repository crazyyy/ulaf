<?php get_header(); ?>
  <main class="main-content" itemscope itemprop="mainContentOfPage">
    <section class="section">
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

          </article><!-- /.post-content -->
        <?php endwhile; endif; ?>
      </div>
    </section>

    <?php get_sidebar(); ?>

  </main>
<?php get_footer(); ?>
