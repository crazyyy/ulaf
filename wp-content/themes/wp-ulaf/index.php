<?php get_header(); ?>
  <main class="main-content" itemscope itemprop="mainContentOfPage">
    <section class="section">
      <div class="content-container">

        <header class="post-header">
          <?php if (is_home() && ! is_front_page() && ! empty( single_post_title('',false))) : ?>
            <h1 class="post-title"><?php single_post_title(); ?></h1>
          <?php else: ?>
            <h1 class="post-title"><?php _e('Latest Posts', 'wpeb'); ?></h1>
          <?php endif; ?>
        </header><!-- .post-header -->

        <div class="loop-container">
          <?php get_template_part('template-parts/loop'); ?>
        </div>
        <!-- /.loop-container -->

        <?php get_template_part('template-parts/pagination'); ?>

      </div>
    </section>

    <?php get_sidebar(); ?>
    
  </main>
<?php get_footer(); ?>
