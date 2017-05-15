<?php /* Template Name: Ratings */ get_header(); ?>
<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="container results">

        <div class="row grid-3">
        <div class="col-md-12 rating-division-a grid-item-3">
          <h2>Defense</h2>
          <h4><a href="http://ulafua.com/defensive-tackles.htm">Defensive Tackles</a></h4>
          <h4><a href="http://ulafua.com/defensive-ends.htm">Defensive Ends</a></h4>
          <h4><a href="http://ulafua.com/linebackers.htm">Linebackers</a></h4>
          <h4><a href="http://ulafua.com/safeties.htm">Safeties</a></h4>
          <h4><a href="http://ulafua.com/cornerbacks.htm">Cornerbacks</a></h4>
          <h2>Offence</h2>
          <h4><a href="http://ulafua.com/quarterbacks.htm">Quarterbacks</a></h4>
          <h4><a href="http://ulafua.com/offensive-linemans.htm">Offensive Linemans</a></h4>
          <h4><a href="http://ulafua.com/runningbacks.htm">Runningbacks</a></h4>
          <h4><a href="http://ulafua.com/wide-receivers.htm">Wide Receivers</a></h4>
          <h4><a href="http://ulafua.com/tight-ends.htm">Tight ends</a></h4>
          <h4><a href="http://ulafua.com/kickers.htm">Kickers</a></h4>
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
