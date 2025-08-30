<?php get_header(); ?>
  <main class="main-content" itemscope itemprop="mainContentOfPage">
    <section class="section">
      <div class="content-container">

      <?php if ( have_posts() ) { ?>
        <header class="post-header">
          <h1 class="post-title">
            <?php printf( esc_html__( 'Results for "%s"', 'wpeb' ),
              '<span class="page-description search-term">' . esc_html( get_search_query() ) . '</span>'
            ); ?>
          </h1>
        </header><!-- /.post-header -->

        <div class="search-result-count default-max-width">
          <?php
          printf(
            esc_html(
              _n(
                'We found %d result for your search.',
                'We found %d results for your search.',
                (int) $wp_query->found_posts,
                'wpeb'
              )
            ),
            (int) $wp_query->found_posts
          );
          ?>
        </div><!-- .search-result-count -->

        <div class="loop-container">
          <div class="container">
            <div class="grid">

              <?php get_template_part('template-parts/loop'); ?>

              <?php get_template_part('template-parts/pagination'); ?>

            </div>
            <!-- /.grid -->
          </div>
          <!-- /.container -->
        </div>
        <!-- /.loop-container -->

      <?php } else { ?>

        <header class="post-header col-12">
          <h1 class="post-title">
            <?php __( 'Nothing Found', 'wpeb' ); ?>
          </h1>
        </header><!-- .post-header -->

      <?php } ?>

      </div>
    </section>

    <?php get_sidebar(); ?>
    
  </main>
<?php get_footer(); ?>
