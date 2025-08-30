<?php get_header(); ?>
  <main class="main-content" itemscope itemprop="mainContentOfPage">
    <section class="section">
      <div class="content-container">

        <header class="post-header">
          <h1 class="post-title">
            <?php the_category(', '); ?>
          </h1>
        </header><!-- .post-header -->

        <div class="loop-container">
          <?php get_template_part('template-parts/loop'); ?>

          <?php get_template_part('template-parts/pagination'); ?>
        </div>
        <!-- /.loop-container -->

      </div>
    </section>

    <?php get_sidebar(); ?>

  </main>
<?php get_footer(); ?>
