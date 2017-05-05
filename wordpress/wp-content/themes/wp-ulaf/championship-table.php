<?php /* Template Name: Table-championship Page */ get_header(); ?>
<div class="wrapper table-background">

  <section class="container">

    <div class="row grid-2">
<select class="filters-select">
          <option value=".team-table-position-2017">Сезон 2017</option>
          <option value=".team-table-position-2016">Сезон 2016</option>
        </select>
      <div class="col-md-12 team-table-position-2017 grid-item-2">


         <h3>Дивизион A</h3>

<table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>Очки</th>
                        <th>ОЗ</th>
                        <th>ОП</th>
                        <th>+/-</th>
                    </tr>
            </thead>
              <?php $posts = get_field('table_2017_a'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                <?php echo get_the_post_thumbnail( $p->ID, array(100,100) ); ?>
                <?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins_2017', $p->ID); ?></td>
                  <td><?php the_field('losts_2017', $p->ID); ?></td>
                  <td><?php the_field('points_2017', $p->ID); ?></td>
                  <td><?php the_field('total_scores_2017', $p->ID); ?></td>
                  <td><?php the_field('total_conceded_2017', $p->ID); ?></td>
                  <td><?php the_field('difference_2017', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>

      <h3>Дивизион B</h3>
        <table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>Очки</th>
                        <th>ОЗ</th>
                        <th>ОП</th>
                        <th>+/-</th>

                    </tr>
            </thead>
              <?php $posts = get_field('table_2017_b'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?>
                <?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins_2017', $p->ID); ?></td>
                  <td><?php the_field('losts_2017', $p->ID); ?></td>
                  <td><?php the_field('points_2017', $p->ID); ?></td>
                  <td><?php the_field('total_scores_2017', $p->ID); ?></td>
                  <td><?php the_field('total_conceded_2017', $p->ID); ?></td>
                  <td><?php the_field('difference_2017', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>
<h3>Дивизион C</h3>
      <table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>Очки</th>
                        <th>ОЗ</th>
                        <th>ОП</th>
                        <th>+/-</th>
                    </tr>
            </thead>
              <?php $posts = get_field('table_2017_c'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?>
                <?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                 <td><?php the_field('wins_2017', $p->ID); ?></td>
                  <td><?php the_field('losts_2017', $p->ID); ?></td>
                  <td><?php the_field('points_2017', $p->ID); ?></td>
                  <td><?php the_field('total_scores_2017', $p->ID); ?></td>
                  <td><?php the_field('total_conceded_2017', $p->ID); ?></td>
                  <td><?php the_field('difference_2017', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>

      <!-- 2016 year -->


      </div>
      <div class="col-md-12 team-table-position-2016 grid-item-2">


<h3>Дивизион A</h3>

<table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>Очки</th>
                        <th>ОЗ</th>
                        <th>ОП</th>
                        <th>+/-</th>
                    </tr>
            </thead>
              <?php $posts = get_field('table_team_a'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?>
                <?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins', $p->ID); ?></td>
                  <td><?php the_field('losts', $p->ID); ?></td>
                  <td><?php the_field('points', $p->ID); ?></td>
                  <td><?php the_field('total_scores', $p->ID); ?></td>
                  <td><?php the_field('total_losts', $p->ID); ?></td>
                  <td><?php the_field('difference', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>
      <h3>Дивизион B</h3>
        <table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>Очки</th>
                        <th>ОЗ</th>
                        <th>ОП</th>
                        <th>+/-</th>

                    </tr>
            </thead>
              <?php $posts = get_field('table_team_b'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?>
                <?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins', $p->ID); ?></td>
                  <td><?php the_field('losts', $p->ID); ?></td>
                  <td><?php the_field('points', $p->ID); ?></td>
                  <td><?php the_field('total_scores', $p->ID); ?></td>
                  <td><?php the_field('total_losts', $p->ID); ?></td>
                  <td><?php the_field('difference', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>
<h3>Дивизион C</h3>
      <table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>Очки</th>
                        <th>ОЗ</th>
                        <th>ОП</th>
                        <th>+/-</th>
                    </tr>
            </thead>
              <?php $posts = get_field('table_team_c'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                <?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?>
                <?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins', $p->ID); ?></td>
                  <td><?php the_field('losts', $p->ID); ?></td>
                  <td><?php the_field('points', $p->ID); ?></td>
                  <td><?php the_field('total_scores', $p->ID); ?></td>
                  <td><?php the_field('total_losts', $p->ID); ?></td>
                  <td><?php the_field('difference', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>

      <!-- 2016 year -->


      </div>
    </div>
  </section>
</div>
</div>
<?php get_footer(); ?>
