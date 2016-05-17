<?php get_header(); ?>
<section class="container container-content">
  <div class="row single-game">
    <h1><?php the_title(); ?></h1>
    <?php
                // get raw date
                $date = get_field('date', false, false);
                // make date object
                $date = new DateTime($date);
                ?>
                <?php
                ?>
                <?php echo $date->format('j M Y');?>
    <div class="col-md-12 team-single-game">

      <tr>


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
                <span class="single-game-score"><?php the_field('score_first_team');?></span>
                <span class="single-game-score"><?php the_field('score_second_team');?></span>
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
      </tr>
    </div>
    <div class="col-md-12">
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

<!-- <h2 style="font-size: 20px; color: #fff;">

<?php the_title(); ?>
</h2> -->

        <?php endwhile; else: ?>


          <div>
            <h2 class="title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
          </div><!-- /article -->
        <?php endif; ?>





        <?php $fivesdrafts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_status = 'Publish' AND post_type = 'player'");
        if ( $fivesdrafts ) {
          foreach ( $fivesdrafts as $post )
          {
            setup_postdata( $post );
            ?>
<!--             <h2>
  <a href="<?php the_permalink(); ?>" rel="bookmark" title="Permalink: <?php the_title(); ?>">
    <?php the_title(); ?>
  </a>
</h2> -->
            <?php
          }
        }
        else
        {
          ?>
          <h2>Not Found</h2>
          <?php
        }
        ?>


<?php

// args
$args = array(
  'numberposts' => -1,
  'post_type'   => 'player',
  'meta_query'  => array(
    'relation'    => 'OR',
    array(
      'key'   => 'player_position',
      'value'   => 'qb',
      'compare' => 'LIKE'
    ),
    array(
      'key'   => 'player_position',
      'value'   => 'wr',
      'compare' => 'LIKE'
    )
  )
);


// query
$the_query = new WP_Query( $args );

?>
<?php if( $the_query->have_posts() ): ?>
  <ul>
  <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
    <li>
      <a href="<?php the_permalink(); ?>">

        <?php the_title(); ?>
      </a>
    </li>
  <?php endwhile; ?>
  </ul>
<?php endif; ?>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>






    </div>



    <div class="col-md-12">

    </div>
    <div class="col-md-12">

    </div>

  </div><!-- row -->
</section><!-- container container-content -->

<?php get_footer(); ?>
