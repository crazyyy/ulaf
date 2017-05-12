<?php /* Template Name: Ratings K */ get_header(); ?>

<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="container results">
        <div class="row grid-3">
        <div class="col-md-12 rating-division-a grid-item-3">
        <h2><?php the_title(); ?></h2>
        <h3>Дивизион А</h3>
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
            <h3>Дивизион В</h3>
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
                  if( have_rows('k_ratings_b') ):

  // loop through the rows of data
                  while ( have_rows('k_ratings_b') ) : the_row();?>



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
            <h3>Дивизион С</h3>
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
                  if( have_rows('k_ratings_c') ):

  // loop through the rows of data
                  while ( have_rows('k_ratings_c') ) : the_row();?>



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
      </div>
    </article>
    <?php endwhile; else: // If 404 page error ?>
 <article>
<h4 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h4>
</article>
<?php endif; ?>
</div>
<?php get_footer(); ?>
