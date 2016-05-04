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

if( $posts ): ?>
    <ul>
    <?php foreach( $posts as $post): // variable must be called $post (IMPORTANT) ?>
        <?php setup_postdata($post); ?>
        <li>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </li>
    <?php endforeach; ?>
    </ul>
    <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
<?php endif; ?>
                </td>

          <td>

                <?php
                      $posts = get_sub_field('opposing_team');
                      if( $posts ):
                        foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">

                        <?php endforeach; ?>
                      <?php endif; ?>

          </td>


                     <?php endwhile; endif; ?>

          <td><?php the_field('score');?></td>

          <td>123123</td>
      </tr>
    </div>
    <div class="col-md-12">

    </div>
    <div class="col-md-12">

    </div>
    <div class="col-md-12">

    </div>

  </div>
</section>

<?php get_footer(); ?>
