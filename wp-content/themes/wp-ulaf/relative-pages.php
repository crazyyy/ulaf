<section class="section-photo">
  <div class="container">
    <div class="row">

      <div class="col-md-4">

      <?php wpb_related_pages(); ?>
      <?php if ( has_post_thumbnail()) :?>

                 <?php the_post_thumbnail('full'); // Fullsize image for the single post ?>

            <?php endif; ?><!-- /post thumbnail -->
      </div>


    </div><!-- row -->
  </div><!-- container -->
</section><!-- section-photo -->
