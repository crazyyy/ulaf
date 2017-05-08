<?php /* Template Name: Team ULAF */ get_header(); ?>

  <?php if (have_posts()): while (have_posts()) : the_post(); ?>

    <div class="our-team">
      <div class="container">
        <div class="row">

          <h2 class="col-lg-12 col-md-12"><?php the_title(); ?> </h2>

          <div class="vertical-tabs">

            <?php $posts = get_field('team'); if( $posts ): ?>

              <ul class="tabs vertical" data-tab="">
                <?php $i = 1; foreach( $posts as $post): ?>
                  <?php setup_postdata($post); ?>

                  <?php if($i == 1) { $arguments = 'aria-selected="true" tabindex="0"'; } else { $arguments = 'aria-selected="false" tabindex="-1"'; } ?>

                  <li class="tab-title"><a href="#panela<?php echo $i; ?>" <?php echo $arguments; ?>><?php the_title(); ?></a></li>

                <?php $i++; endforeach; ?>
              </ul>
              <?php wp_reset_postdata(); ?>

            <?php endif; ?>

            <div class="tabs-content">

              <?php $posts = get_field('team'); if( $posts ): ?>
                <?php $i = 1; foreach( $posts as $post): ?>
                  <?php setup_postdata($post); ?>

                  <?php if($i == 1) { $class = 'content active'; } else { $class = 'content'; } ?>
                  <div class="<?php echo $class; ?>" id="panela<?php echo $i; ?>" <?php echo $arguments; ?>>
                    <div class="person-image">
                      <?php the_post_thumbnail('large'); ?>
                      <div class="person-info">
                        <span class="person-info--name"><?php the_title(); ?></span>
                        <a class="person-info--link" href="<?php the_permalink(); ?>"><i class="fa fa-user" aria-hidden="true"></i></a>
                      </div><!-- person-info -->
                    </div><!-- person-image -->
                  </div><!-- content -->

                <?php $i++; endforeach; ?>
                <?php wp_reset_postdata(); ?>

              <?php endif; ?>

            </div><!-- tabs-content -->

          </div><!-- vertical-tabs -->

        </div><!-- row -->
      </div><!-- container -->
    </div><!-- our-team -->

  <?php endwhile; endif; ?>

<?php get_footer(); ?>
