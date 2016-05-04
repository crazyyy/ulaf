<div class="col-md-12">
<div class="col-md-12 inner">
    <h4>player stats as P</h4>




      <table class="player-score">


        <tr>
          <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>


                <?php

                // check if the repeater field has rows of data
                if( have_rows('playplayer_punter') ):

                  // loop through the rows of data
                    while ( have_rows('playplayer_punter') ) : the_row();
                  ?>

          <th>

                <?php
                      $posts = get_sub_field('opposing_team');
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
            <td>ПАНТОВ</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_punter') ):

                  // loop through the rows of data
                    while ( have_rows('player_punter') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('punts'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('punts');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


         <tr>
            <td>НАИБОЛИЕ ДЛИННЫЙ ПАНТ</td>


                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_punter') ):

                  // loop through the rows of data
                    while ( have_rows('player_punter') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('longest_punt'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('longest_punt');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


          <tr class="player-work">
            <td>ЯРДОВ</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_punter') ):

                  // loop through the rows of data
                    while ( have_rows('player_punter') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('yds'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('yds');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
      </tr>



          <tr class="player-work">
            <td>123 </td>
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
</div>


