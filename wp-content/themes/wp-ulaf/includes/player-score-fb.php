
<div class="col-md-12 inner">
    <h4>player stats as FB</h4>

    <table class="player-score player-score-fb">
      <tr>
          <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>

                <?php
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
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
          <td>НАБРАНЫХ ЯРДОВ (БЕГ)</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
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
            <td>НАБРАНЫХ ЯРДОВ ПАСОМ</td>
                <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('receiving_yds'); ?>
            </td>
                <?php  $i = $i + get_sub_field('receiving_yds'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>ТАЧДАУН БЕГОМ</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('rush_tds'); ?>
            </td>
                <?php  $i = $i + get_sub_field('rush_tds'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
         <tr>
            <td>ТАЧДАУН ПАСОМ</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('pass_tds'); ?>
            </td>
                <?php  $i = $i + get_sub_field('pass_tds');  ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
      </tr>
      <tr class="player-work">
            <td>КОЛИЧЕСТВО БЕГОВ</td>

                 <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('rush_att'); ?>
            </td>
                <?php $i = $i + get_sub_field('rush_att'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>ПОЙМАНЫХ БРОСКОВ</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row();  ?>
            <td>
                <?php the_sub_field('reception'); ?>
            </td>
                <?php  $i = $i + get_sub_field('reception'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>ФАМБЛОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('fumble'); ?>
            </td>
                <?php $i = $i + get_sub_field('fumble');  ?>
                <?php endwhile; endif; ?>
            <td>
              <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>ПОТЕРЬ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_rb_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_rb_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('lost'); ?>
            </td>
                <?php  $i = $i + get_sub_field('lost'); ?>
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



