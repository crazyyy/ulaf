<div class="col-md-12">
<div class="col-md-12 inner player-position-k">
    <h4>player stats as K</h4>




      <table class="player-score player-score-k">


        <tr>
          <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>


                <?php

                // check if the repeater field has rows of data
                if( have_rows('player_kicker') ):

                  // loop through the rows of data
                    while ( have_rows('player_kicker') ) : the_row();
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
            <td>ФИЛДГОЛОВ</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_kicker') ):

                  // loop through the rows of data
                    while ( have_rows('player_kicker') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('fg'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('fg');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


         <tr>
            <td>НАИБОЛИЕ ДЛИННЫЙ ФИЛДГОЛ</td>


                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_kicker') ):

                  // loop through the rows of data
                    while ( have_rows('player_kicker') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('longest'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('longest');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


          <tr class="player-work">
            <td>НАИБОЛИЕ ДЛИННЫЙ КИКОФФ</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_kicker') ):

                  // loop through the rows of data
                    while ( have_rows('player_kicker') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('longest_kickoff'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('longest_kickoff');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
      </tr>



          <tr class="player-work">
            <td>&nbsp;</td>
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


