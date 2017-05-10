<?php /* Template Name: Ratings */ get_header(); ?>
<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="container results">
        <div class="row">
        <select class="filters-select">
          <option value=".game-info-2017">Сезон 2017</option>
        </select>

        <h2 >Рейтинги</h2>
          <h4>Defensive Line</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                 <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Tackles</td>

                <td >Sacks</td>
                <td >Passes deflected</td>
                <td >Interceptions</td>
              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('dl_ratings') ):

  // loop through the rows of data
                  while ( have_rows('dl_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>
                <td><?php the_sub_field('rating_tackles'); ?></td>
                <td><?php the_sub_field('rating_sacks'); ?></td>
                <td><?php the_sub_field('rating_pd'); ?></td>
                <td><?php the_sub_field('rating_int'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
             <h4>Linebackers</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Tackles</td>

                <td >Sacks</td>
                <td >Passes deflected</td>
                <td >Interceptions</td>
              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('lb_ratings') ):

  // loop through the rows of data
                  while ( have_rows('lb_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>
                <td><?php the_sub_field('rating_tackles'); ?></td>
                <td><?php the_sub_field('rating_sacks'); ?></td>
                <td><?php the_sub_field('rating_pd'); ?></td>
                <td><?php the_sub_field('rating_int'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
            <h4>Safeties</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Tackles</td>

                <td >Sacks</td>
                <td >Passes deflected</td>
                <td >Interceptions</td>
              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('s_ratings') ):

  // loop through the rows of data
                  while ( have_rows('s_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>
                <td><?php the_sub_field('rating_tackles'); ?></td>
                <td><?php the_sub_field('rating_sacks'); ?></td>
                <td><?php the_sub_field('rating_pd'); ?></td>
                <td><?php the_sub_field('rating_int'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
            <h4>Cornerbacks</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Tackles</td>

                <td >Sacks</td>
                <td >Passes deflected</td>
                <td >Interceptions</td>
              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('cb_ratings') ):

  // loop through the rows of data
                  while ( have_rows('cb_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>
                <td><?php the_sub_field('rating_tackles'); ?></td>
                <td><?php the_sub_field('rating_sacks'); ?></td>
                <td><?php the_sub_field('rating_pd'); ?></td>
                <td><?php the_sub_field('rating_int'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
        <h4>Quarterbacks</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Pass Yards</td>

                <td >Rush Yards</td>
                <td>Pass TD's</td>
                <td >Interceptions</td>
                <td >Wins</td>
                <td >TD's</td>

              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('qb_ratings') ):

  // loop through the rows of data
                  while ( have_rows('qb_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>
                <td><?php the_sub_field('rating_pass_yds'); ?></td>
                <td><?php the_sub_field('rating_rush_yds'); ?></td>
                <td><?php the_sub_field('rating_pass_tds'); ?></td>
                <td><?php the_sub_field('rating_interceptions'); ?></td>
                 <td><?php the_sub_field('rating_wins'); ?></td>
                  <td><?php the_sub_field('rating_tds'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
            <h4>Offensive Linemans</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Rush yards</td>

                <td >QB sacks</td>

              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('ol_ratings') ):

  // loop through the rows of data
                  while ( have_rows('ol_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>

                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>

                <td><?php the_sub_field('rating_rush_yds'); ?></td>


                <td><?php the_sub_field('rating_qb_sacks'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
            <h4>Runningbacks</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>



                <td >Rush Yards</td>
                <td>Pass Yards</td>

                <td >TD's</td>

              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('rb_ratings') ):

  // loop through the rows of data
                  while ( have_rows('rb_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>
                <td><?php the_sub_field('rating_rush_yds'); ?></td>
                <td><?php the_sub_field('rating_pass_yds'); ?></td>


                <td><?php the_sub_field('rating_tds'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
            <h4>Wide Receivers</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Pass Yards</td>

                <td >TD's</td>

              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('wr_ratings') ):

  // loop through the rows of data
                  while ( have_rows('wr_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>

                <td><?php the_sub_field('rating_pass_yds'); ?></td>


                <td><?php the_sub_field('rating_tds'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
             <h4>Tight ends</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>Pass Yards</td>

                <td >TD's</td>

              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('te_ratings') ):

  // loop through the rows of data
                  while ( have_rows('te_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>

                <td><?php the_sub_field('rating_pass_yds'); ?></td>


                <td><?php the_sub_field('rating_tds'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
            <h4>Kickers</h4>
           <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">#</td>

                <td>Name</td>
                <td>Team</td>
                <td colspan="5"></td>

                <td >Games</td>

                <td>EP's</td>

                <td >FG's</td>

              </tr>
              <tr class="stats">
                <?php

// check if the repeater field has rows of data
                  if( have_rows('k_ratings') ):

  // loop through the rows of data
                  while ( have_rows('k_ratings') ) : the_row();?>



                <td><?php the_sub_field('rating'); ?></td>
                <td><?php the_sub_field('rating_name'); ?></td>
                  <td><?php the_sub_field('rating_team'); ?></td>
                <td colspan="5"></td>

                <td><?php the_sub_field('rating_games'); ?></td>

                <td><?php the_sub_field('rating_exp'); ?></td>


                <td><?php the_sub_field('rating_fgs'); ?></td>


                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>

      </div>
     </div>
    </article>
  <?php endwhile; else: // If 404 page error ?>
 <article>
<h4 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h4>
</article>
<?php endif; ?>
</div>
<?php get_footer(); ?>
