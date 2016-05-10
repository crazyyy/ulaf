<?php get_header(); ?>
<section class="container container-content">
  <div class="row">
    <div class="col-md-12">
      <tr>
          <td><?php the_title(); ?></td>
            <?php
                // get raw date
                $date = get_field('date', false, false);
                // make date object
                $date = new DateTime($date);
                ?>
                <?php
                ?>
                <?php echo $date->format('j M Y');?>
                <td>

                  <?php
                      $posts = get_field('team');
                      if( $posts ):
                        foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">

                        <?php endforeach; ?>

                    <?php endif; ?>
                </td>

          <td>

                <?php
                      $posts = get_field('opposing_team');
                      if( $posts ):
                        foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">

                        <?php endforeach; ?>

                    <?php endif; ?>

          </td>






          <td><?php the_field('score');?></td>

          <td>123123</td>
      </tr>
    </div>
    <div class="col-md-12">
            <?php
                // get raw date
                $searchdate = get_field('date', false, false);

                ?>


        <?php

        $args = array(
          'post_type' => 'player',

          'meta_query' => array(
            // 'key' => 'date',
            // 'value' => $date,
            // // 'compare' => 'BETWEEN',
            // 'type' => 'DATE',


            'meta_key' => 'date',
            'meta_value' => $searchdate


            )
          );



        query_posts( $args ); if (have_posts()): while (have_posts()) : the_post(); ?>

<h2 style="font-size: 20px; color: #fff;">



<?php the_title(); ?>
</h2>

        <?php endwhile; else: ?>




          <div>
            <h2 class="title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
          </div><!-- /article -->
        <?php endif; ?>








    </div>
    <div class="col-md-12">

    </div>
    <div class="col-md-12">

    </div>

  </div><!-- row -->
</section><!-- container container-content -->

<?php get_footer(); ?>
