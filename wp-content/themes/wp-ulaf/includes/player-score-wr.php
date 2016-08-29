
<div class="col-md-12 inner player-score-container-wr">
  <h4>player stats as WR</h4>
    <table class="player-score player-score-wr">
        <tr>
            <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>
                  <?php
                  // check if the repeater field has rows of data
                  if( have_rows('player_wr_stats') ):
                    // loop through the rows of data
                      while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <th>
                  <?php $posts = get_sub_field('opposing_team');
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
            <td>ПОЙМАНЫХ МЯЧЕЙ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('reception'); ?>
            </td>
                <?php $i = $i + get_sub_field('reception'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>НАБРАНЫХ ЯРДОВ ПАСОМ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('yds'); ?>
            </td>
                <?php $i = $i + get_sub_field('yds'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>НАБРАНО ЯРДОВ (БЕГ)</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
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
            <td>ДРОПОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('drops'); ?>
            </td>
                <?php $i = $i + get_sub_field('drops'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>ТАЧДАУНОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('td'); ?>
            </td>
                <?php  $i = $i + get_sub_field('td'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>ФАМБЛОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('fumble'); ?>
            </td>
                <?php $i = $i + get_sub_field('fumble'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>ПОТЕРЬ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('player_wr_stats') ):
                  // loop through the rows of data
                    while ( have_rows('player_wr_stats') ) : the_row(); ?>
            <td>
                <?php the_sub_field('lost'); ?>
            </td>
                <?php $i = $i + get_sub_field('lost'); ?>
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



