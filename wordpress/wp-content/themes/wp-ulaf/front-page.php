<?php /* Template Name: Home Page */ get_header(); ?>

  <div class="owl-home-slide">
    <?php $images = get_field('slider_images'); if( $images ): foreach( $images as $image ): ?>

      <div class="item-slide">
        <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />

          <h1 class="slider_title"><?php the_field('slide_title');?></h1>
          <h5 class="descr"><?php the_field('slide_description');?></h5>

          <div class="slider_but">
            <a href="" class="btn btn-primary">Присоединяйтесь</a>
            <a href="" class="btn second">Подробнее</a>
          </div>

      </div><!-- item-slide -->

    <?php endforeach; endif; ?>
  </div><!-- /.owl-home-slide -->

  <section class="section-news">
    <div class="container">

      <div class="row news_block">

        <h3 class="col-md-12">Последние Новости</h3>

        <!-- start news loop -->
        <?php query_posts("showposts=4&cat=1"); ?>
          <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <div class="col-md-3 col-sm-6">
              <a href="<?php the_permalink(); ?>" class="hover_image">
                <?php if ( has_post_thumbnail()) : the_post_thumbnail('medium'); else: ?>
                  <img src="<?php echo catchFirstImage(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>" />
                <?php endif; ?>
                <span class="date"><?php the_time('j.m.Y'); ?> <?php the_time('H:i'); ?></span>
              </a>
              <h4><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
            </div>
          <?php endwhile; endif; ?>
        <?php wp_reset_query(); ?>
        <!-- end news loop -->

      </div><!-- news_block -->

      <div class="owl-footer-slide">

        <?php $images = get_field('sponsors_gallery'); if( $images ): foreach( $images as $image ): ?>

          <div class="item-slide sponsors-footer-images">
            <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
          </div><!-- sponsors-footer-images -->

        <?php endforeach; endif; ?>

      </div><!-- owl-footer-slide -->

    </div><!-- container -->
  </section><!-- section-news -->

<?php get_footer(); ?>
