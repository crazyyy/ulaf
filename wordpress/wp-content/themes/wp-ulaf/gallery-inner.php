<?php /* Template Name: Inner Photo page */ get_header(); ?>
<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

      <div class="section content">
        <div class="container">
          <div class="row">

          <!-- Gallery Page -->
            <div class="col-md-12 gallery">
              <h1 class="page-title"><?php the_title(); ?></h1>
              <?php $images = get_field('ulaf_gallery'); if( $images ): ?>
               <div class="fotorama" data-nav="thumbs" data-allowfullscreen="true" data-loop="true" data-width="100%">
              <?php $images = get_field('ulaf_gallery'); if( $images ): foreach( $images as $image ): ?>
              <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
             <?php endforeach; endif; ?>
             </div>

              <?php endif; ?>
            </div> <!-- gallery -->

          </div>
        </div>
      </div>
    </div>

    </article>
  <?php endwhile; endif; ?>
<?php get_footer(); ?>
