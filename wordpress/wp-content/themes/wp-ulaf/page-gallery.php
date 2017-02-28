<?php /* Template Name: Gallery Page */ get_header(); ?>
<?php if (have_posts()): while (have_posts()) : the_post(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="section content">
      <div class="container">
        <div class="row">
          <!-- Gallery Page -->
            <div class="col-md-12 gallery">
              <?php $posts = get_field('gallery'); if( $posts ): ?>
                <ul>
                  <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                    <li>
                      <a href="<?php echo get_permalink( $p->ID ); ?>">
                        <?php echo get_the_title( $p->ID ); ?>
                        <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID, "medium" ) ); ?>" alt="">
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div> <!-- gallery -->
          </div>
        </div>
      </div>
    </article>
  <?php endwhile; endif; ?>
<?php get_footer(); ?>
