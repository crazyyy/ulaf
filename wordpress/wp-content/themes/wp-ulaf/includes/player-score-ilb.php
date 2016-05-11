
<div class="col-md-12 inner">
    <h4>player stats as ILB</h4>

      <table class="player-score player-score-ilb">
        <tr>
          <th><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png"></th>
                <?php  // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
          <th>
                <?php   $posts = get_sub_field('opposing_team');
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
            <td>ТЕКЛОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
            <td>
                <?php the_sub_field('tackles'); ?>
            </td>
                <?php $i = $i + get_sub_field('tackles'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>ЗАБЛОКИРОВАНЫХ ПАСОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
            <td>
                <?php the_sub_field('block_pass'); ?>
            </td>
                <?php $i = $i + get_sub_field('block_pass');  ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>ПЕРЕХВАТОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row();  ?>
            <td>
                <?php the_sub_field('inerceptions'); ?>
            </td>
                <?php $i = $i + get_sub_field('inerceptions'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>ТАЧДАУНОВ</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
            <td>
                <?php the_sub_field('td'); ?>
            </td>
                <?php  $i = $i + get_sub_field('td');  ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>СЕКОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
            <td>
                <?php the_sub_field('sacks'); ?>
            </td>
                <?php $i = $i + get_sub_field('sacks'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
         <tr>
            <td>СЕЙФТИ</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
            <td>
                <?php the_sub_field('safety'); ?>
            </td>
                <?php  $i = $i + get_sub_field('safety'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr class="player-work">
            <td>ФОРСИРОВАНО ФАМБЛОВ</td>
                  <?php $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row(); ?>
            <td>
                <?php the_sub_field('forced_fumbles'); ?>
            </td>
                <?php $i = $i + get_sub_field('forced_fumbles'); ?>
                <?php endwhile; endif; ?>
            <td>
            <?php echo $i; ?>
            </td>
        </tr>
        <tr>
            <td>ФАМБЛОВ ПОДОБРАНО</td>
                  <?php  $i = 0;
                // check if the repeater field has rows of data
                if( have_rows('inside_linebacker') ):
                  // loop through the rows of data
                    while ( have_rows('inside_linebacker') ) : the_row();  ?>
            <td>
                <?php the_sub_field('fumble_recovery'); ?>
            </td>
                <?php $i = $i + get_sub_field('fumble_recovery');  ?>
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


