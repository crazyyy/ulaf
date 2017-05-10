
<?php get_header(); ?>
<section class="container container-content">
  <div class="row">
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>
        <div class="row player-card">
          <div class="col-md-3 player-picture">
            <div class="player-pic">
                <?php if ( has_post_thumbnail()) :?>
              <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php the_post_thumbnail(); // Fullsize image for the single post ?>
              </a>
            <?php endif; ?>
                <div class="socials">
                <a href="<?php the_field('link_vk');?>"><i class="fa fa-vk" aria-hidden="true"></i></a>
                  <a href="<?php the_field('link_instagram');?>"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                 <a href="<?php the_field('link_fb');?>"><i class="fa fa-facebook-official" aria-hidden="true"></i></a>
                 <a href="<?php the_field('link_twitter');?>"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                </div>
              </div>
          </div>
          <div class="col-md-3 description">
            <div class="player-name"><?php the_title(); ?></div>
            <div class="stats-bar">
              <div class="games">Игры : <?php the_field('number_of_games');?></div>
              <div class="tds">Тачдауны : <?php the_field('touchdowns');?></div>
              <div class="yards">Ярды : <?php the_field('yards');?></div>
            </div>
            <div class="biography">
              <div class="born">Родился:
                  <span><?php $date = get_field('player_age', false, false); $date = new DateTime($date); ?>
                  <?php ?>
                  <?php echo $date->format('j M Y');?> |
                  <?php $birthday = $date->format('Y'); $current_year = date("Y"); $age = $current_year - $birthday; echo $age; ?>
                  </span>
              </div>
              <div class="nationality">Играет с:
                <span>
                  <?php $date = get_field('experience', false, false); $date = new DateTime($date); ?>
                  <?php ?>
                  <?php echo $date->format('j M Y');?> |
                  <?php $birthday = $date->format('Y'); $current_year = date("Y"); $age = $current_year - $birthday; echo $age; ?>

                </span>
              </div>
              <div class="team">Игровой номер:
              <span><?php the_field('player_number');?></span>
              </div>
              <div class="height-weight">Рост и вес:
                <span><?php the_field('height');?> см | <?php the_field('weight');?> кг</span>
              </div>
              <div class="position">Позиция: <span><?php the_field('player_position');?></span></div>
            </div>
          </div>
          <div class="col-md-4 current-team">
            <?php $posts = get_field('player_team'); if( $posts ): ?>
              <?php foreach( $posts as $p ): ?>
                <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_post_thumbnail( $p->ID ); ?></a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div><!--/ player-character -->
        <h3>Offensive stats</h3>
        <div class="row full-offensive-stats">
          <div class="col-md-12">
            <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">Сезон</td>

                <td>Команда</td>

                <td colspan="2"></td>

                <td colspan="5">Пасом</td>

                <td colspan="5">Бегом</td>

                <td colspan="2">Фамблы</td>
              </tr>
              <tr>

                <td colspan="2"></td>
                <td colspan="4"></td>

                <td>Rec</td>

                <td>Yds</td>

                <td>Avg</td>

                <td>TD</td>

                <td>Att</td>

                <td>Yds</td>

                <td>Avg</td>

                <td>TD</td>

                <td>Cov</td>

                <td>Lost</td>
              </tr>
              <tr class="stats">
              <?php if( have_rows('offensive_stats') ): while ( have_rows('offensive_stats') ) : the_row();?>

                <td><?php the_sub_field('season'); ?></td>
                <td><?php the_sub_field('oposing_team'); ?></td>
                <td colspan="4"></td>

                <td><?php the_sub_field('pass_receptions'); ?></td>
                <td><?php the_sub_field('passing_yards'); ?></td>
                <td><?php the_sub_field('average_yards_by_pass'); ?></td>
                <td><?php the_sub_field('passing_tds'); ?></td>
                <td><?php the_sub_field('rush_attemps'); ?></td>
                <td><?php the_sub_field('rushing_yards'); ?></td>
                <td><?php the_sub_field('rushing_yards_average'); ?></td>
                <td><?php the_sub_field('rushing_tds'); ?></td>
                <td><?php the_sub_field('covered_fumbles'); ?></td>
                <td><?php the_sub_field('losted_fumbles'); ?></td>
                 <?php endwhile; endif; ?>
              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
          </div>
        </div>
        <h3>Defensive stats</h3>
        <div class="row full-defensive-stats">
          <div class="col-md-12">
            <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">Сезон</td>

                <td>Команда</td>

                <td colspan="2"></td>

                <td colspan="7">Теклы</td>

                <td colspan="3">Перехваты</td>

                <td colspan="2">Фамблы</td>
              </tr>
              <tr>
                <td colspan="2"></td>
                <td colspan="4"></td>

                <td>Comb  </td>

                <td>Total</td>

                <td>Ast</td>

                <td>Sacks</td>

                <td>SFTY</td>

                <td>PDef</td>

                <td>Int</td>

                 <td>TD</td>

                <td>Forced</td>
              </tr>
              <tr class="stats">
                <?php if( have_rows('defensive_stats') ): while ( have_rows('defensive_stats') ) : the_row();?>

                <td><?php the_sub_field('season'); ?></td>
                <td><?php the_sub_field('oposing_team'); ?></td>
                <td colspan="4"></td>

                <td><?php the_sub_field('combined_tackles'); ?></td>
                <td><?php the_sub_field('total_tackles'); ?></td>
                <td><?php the_sub_field('tackles_assists'); ?></td>
                <td><?php the_sub_field('sacks'); ?></td>
                <td><?php the_sub_field('safetys'); ?></td>
                <td><?php the_sub_field('passes_defended'); ?></td>
                <td><?php the_sub_field('interceptions'); ?></td>
                <td><?php the_sub_field('tds'); ?></td>
                <td><?php the_sub_field('forced_fumbles'); ?></td>

                 <?php endwhile; endif; ?>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
          </div>
        </div>
        <div class="short-bio">
        <h3>Биография</h3>
        <p><?php the_content(); ?></p>
        </div>
        <?php comments_template(); ?>
      </article>
  <?php endwhile; else: ?>
  <article class="col-md-12">
    <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
  </article>
 <?php endif; ?>


</div><!-- /.row -->
<div class="row">
 <div class="col-md-12 player-bio">

 </div><!-- /.col-md-9 player-bio -->
</div><!-- /row -->

<?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

<div class="owl-player-slide">
  <?php $images = get_field('player_gallery'); if( $images ): foreach( $images as $image ): ?>
    <div class="item-slide">
      <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
    </div>
  <?php endforeach; ?>
 <?php endif; ?>
</div>
</div>
</section>

<?php get_footer(); ?>
