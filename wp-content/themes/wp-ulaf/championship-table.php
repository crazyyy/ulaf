<?php /* Template Name: Table-championship Page */ get_header(); ?>
<div class="wrapper table-background">
  <section class="container">
    <div class="row">
      <div class="col-md-12 team-table-position">
        <p>Дивизион A</p>
<table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>%</th>
                        <th>ОЗ</th>
                        <th>ОП</th>

                    </tr>
            </thead>
              <?php $posts = get_field('table_team_a'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins', $p->ID); ?></td>
                  <td><?php the_field('losts', $p->ID); ?></td>
                  <td><?php the_field('%', $p->ID); ?></td>
                  <td><?php the_field('total_scores', $p->ID); ?></td>
                  <td><?php the_field('total_losts', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>

      <p>Дивизион B</p>
        <table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>%</th>
                        <th>ОЗ</th>
                        <th>ОП</th>

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
                  <td><?php the_field('%', $p->ID); ?></td>
                  <td><?php the_field('total_scores', $p->ID); ?></td>
                  <td><?php the_field('total_losts', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>
<p>Дивизион C</p>
      <table class="championship-table">
            <thead>
                    <tr>
                        <th>Команды</th>
                        <th>В</th>
                        <th>П</th>
                        <th>%</th>
                        <th>ОЗ</th>
                        <th>ОП</th>

                    </tr>
            </thead>
              <?php $posts = get_field('table_team_c'); if( $posts ): ?>
            <tbody>
          <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <tr>
                  <td>
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
                      </td>
                  <td><?php the_field('wins', $p->ID); ?></td>
                  <td><?php the_field('losts', $p->ID); ?></td>
                  <td><?php the_field('%', $p->ID); ?></td>
                  <td><?php the_field('total_scores', $p->ID); ?></td>
                  <td><?php the_field('total_losts', $p->ID); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
      </table>
      </div>
    </div>
  </section>
</div>
  <?php get_template_part('relative-pages'); ?>
<?php get_footer(); ?>
