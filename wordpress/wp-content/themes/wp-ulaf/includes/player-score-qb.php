<div class="col-md-12">
<div class="col-md-12 inner player-position-qb">
    <h4>player stats as QB</h4>




      <table class="player-score player-score-qb">


        <tr>
          <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>


                <?php

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
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
            <td>НАБРАНЫХ ЯРДОВ (БЕГ)</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('rush_yrds'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('rush_yrds');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>



         <tr>
            <td>НАБРАНЫХ ЯРДОВ ПАСОМ</td>


                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('pass_yds'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('pass_yds');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


          <tr class="player-work">
            <td>ЯРДОВ (БЕГ)</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                   while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('rushing_yards'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('rushing_yards');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
      </tr>

          <tr class="player-work">
            <td>УСПЕШНЫХ ПАСОВ</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('pass_compl'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('pass_compl');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


         <tr>
            <td>ТАЧДАУН ПАСОМ</td>


                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('touchdown'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('touchdown');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
          </tr>


          <tr class="player-work">
            <td>ПЕРЕХВАТОВ</td>

                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('interceptions'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('interceptions');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
      </tr>


          <tr>
            <td>ФАМБЛОВ</td>


                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('fumble'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('fumble');

                 ?>

                <?php endwhile; endif; ?>

            <td>


            <?php echo $i; ?>

            </td>
      </tr>




          <tr>
            <td>ПОТЕРЬ</td>


                  <?php

                  $i = 0;

                // check if the repeater field has rows of data
                if( have_rows('player_qb_stats') ):

                  // loop through the rows of data
                    while ( have_rows('player_qb_stats') ) : the_row();
                  ?>
            <td>

                <?php the_sub_field('lost'); ?>

                </td>
                <?php
                $i = $i + get_sub_field('lost');

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


