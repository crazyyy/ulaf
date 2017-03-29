<?php get_header(); ?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
  <section class="container container-content">
    <div class="row team-head">
      <?php if (have_posts()): while (have_posts()) : the_post(); ?>

      <div class="row team-card" >
       <div class="team-background-image" style="background-image: url(<?php echo get_template_directory_uri(); ?>/img/slider-4.jpg);background-size: cover;">
<div class="col-md-3 team-picture">
            <div class="player-pic">
                <?php if ( has_post_thumbnail()) :?>
              <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php the_post_thumbnail(); // Fullsize image for the single post ?>
              </a>
            <?php endif; ?>
                <div class="colors">
                <?php
                      if( have_rows('colors') ): while ( have_rows('colors') ) : the_row(); ?>
                      <span style="background-color:<?php the_sub_field('colors'); ?>"></span>
                    <?php endwhile; endif; ?>

                </div>
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
            <!-- <div class="stats-bar"> -->
              <div class="team-anthem">Гимн : <?php

                $file = get_field('hymn');

                if( $file ): ?>

                <audio controls>

                <source src="<?php echo $file['url']; ?>" type="audio/mpeg">
                </audio>

                <?php endif; ?>
                </div>

            <div class="biography">
              <div class="born">Дата основания: <span><?php
                    $date = get_field('date', false, false);
                    $date = new DateTime($date);
                    echo $date->format('j M Y');
                  ?></div>
              <div class="nationality">Президент: <span></span></div>
              <div class="team">Главный тренер: <span></span></div>

              <div class="position">Город: <span><?php the_field('team_city');?></span></div>
            </div>
          </div>


      </div>



      </div>
<div class="row full-offensive-stats">
          <div class="col-md-12">
            <table>
        <!-- Заголовок таблицы -->
              <tr>
                <td class="first-td">Сезон</td>

                <td>Противник</td>

                <td colspan="2"></td>

                <td colspan="5">Пасом</td>

                <td colspan="5">Бегом</td>

                <td colspan="2">Фамблы</td>
              </tr>
              <tr>
                <td colspan="2"></td>
                <td colspan="4">G</td>

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
                <td>2017</td>
                <td>Волки Винница</td>
                <td colspan="4">10</td>
                <td>56</td>
                <td>1120</td>
                <td>5.4</td>
                <td>15</td>
                <td>120</td>
                <td>1120</td>
                <td>11</td>
                <td>11</td>
                <td>11</td>
                <td>11</td>


              </tr>
        <!-- Конец Заголовока таблицы -->
            </table>
          </div>
        </div>



        <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>


  </div><!-- /.row -->




    <div class="row">
      <div class="col-md-12 people-desciption">
        <h4>Основатели и Тренера</h4>

          <div class="row">
            <?php $posts = get_field('people'); if( $posts ): ?>
              <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>

                <div class="col-md-3 people-desciption">

                    <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID, "medium" ) ); ?>" alt="">
                    <?php echo get_the_title( $p->ID ); ?>

                </div><!-- people-desciption -->

              <?php endforeach; ?>
            <?php endif; ?>
          </div><!-- /.row -->


        </div><!-- col-md-12 people-desciption -->

        <div class="col-md-12 player-bio">
          <h2 class="team_name">История команды "<?php the_title(); ?>"</h2>
          <?php the_content(); ?>
        </div><!-- /.col-md-9 player-bio -->
      </div><!-- /row -->
      <?php $images = get_field('team_gallery'); if( $images ): ?>
 <div class="fotorama"
     data-nav="thumbs" data-allowfullscreen="true" data-loop="true" data-width="100%"
     >
     <?php $images = get_field('team_gallery'); if( $images ): foreach( $images as $image ): ?>
  <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
<?php endforeach; endif; ?>
</div>

     <?php endif; ?>





      <?php if( have_rows('team_achievements') ) :  ?>
        <h2 class="team_achievements">Достижения команды</h2>
        <?php while ( have_rows('team_achievements') ) : the_row(); ?>
          <h5 class="text-achiv"><?php the_sub_field('team_achievements'); ?></h5>
        <?php endwhile;  ?>
      <?php endif; ?>

      <?php $posts = get_field('sponsors'); if( $posts ): ?>
        <div class="row">
          <div class="col-md-12 sponsors">
            <h4>Спонсоры команды</h4>
            <div class="row">

              <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>

                <div class="col-md-1 people-desciption">
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                    <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID, "medium" ) ); ?>" alt="">
                    <?php echo get_the_title( $p->ID ); ?>
                  </a>
                </div><!-- people-desciption -->

              <?php endforeach; ?>

          </div><!-- /.row -->
        </div><!--people-desciption -->
      </div><!-- row -->
    <?php endif; ?>

    <?php comments_template(); ?>

    </article>

  <?php endwhile; else: ?>
    <article class="col-md-12">
      <h2 class="page-title inner-title"><?php _e( 'Sorry, nothing to display.', 'wpeasy' ); ?></h2>
    </article>
  <?php endif; ?>

  </div>
</section>

<?php get_footer(); ?>
