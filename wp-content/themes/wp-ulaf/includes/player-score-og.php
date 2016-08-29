
<div class="col-md-12 inner player-position-og">
    <h4>player stats as OG</h4>
    <table class="player-score player-score-og">
        <tr>
            <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>
                  <?php
                  // check if the repeater field has rows of data
                  if( have_rows('player_og') ):
                    // loop through the rows of data
                      while ( have_rows('player_og') ) : the_row(); ?>
            <th>
                  <?php  $posts = get_sub_field('opposing_team');
                        if( $posts ):
                      foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                  <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                        <?php endforeach; ?>
                        <?php endif; ?>
          </th>
                     <?php endwhile; endif; ?>
          <th>Общая</th>
        </tr>
        <tr class="player-work">
            <td>Ярдов (Бег)</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_og') ):
                  // loop through the rows of data
                    while ( have_rows('player_og') ) : the_row(); ?>
            <td>
                <?php the_sub_field('rush_yrds'); ?>
            </td>
                <?php $i = $i + get_sub_field('rush_yrds'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>Ударов Квотербека с его стороны</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_og') ):
                  // loop through the rows of data
                    while ( have_rows('player_og') ) : the_row(); ?>
            <td>
                <?php the_sub_field('hits_qb_from_his_side'); ?>
            </td>
                <?php $i = $i + get_sub_field('hits_qb_from_his_side'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>&nbsp; </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
          </tr>

      </table><!-- player-score -->
    </div>



