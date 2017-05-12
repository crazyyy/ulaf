<?php /* Template Name: Ratings */ get_header(); ?>
<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="container results">
        <h2 >Рейтинги</h2>
        <div class="row grid-3">
        <div class="col-md-12 rating-division-a grid-item-3">
          <h4>Defensive Tackles</h4>
          <h4>Defensive Ends</h4>
          <h4>Linebackers</h4>
          <h4>Safeties</h4>
          <h4>Cornerbacks</h4>
          <h4>Quarterbacks</h4>
          <h4>Offensive Linemans</h4>
          <h4>Runningbacks</h4>
          <h4>Wide Receivers</h4>
          <h4>Tight ends</h4>
          <h4>Kickers</h4>
        </div>
      </div>
     </div>
    </article>
  <?php endwhile; else: // If 404 page error ?>
 <article>
<h4 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h4>
</article>
<?php endif; ?>
</div>
<?php get_footer(); ?>
