<?php get_header(); ?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
  <section class="container container-content">
    <div class="row team-head">
      <?php if (have_posts()): while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>

          <div class="row team-card">

            <div class="player-main-photo col-md-12">
              <h2 class="team_name"><?php the_title(); ?></h2>

              <ul class="description">
                <li><span>ГОРОД :</span> <?php the_field('team_city');?></li>
                <li><span>ГОД ОСНОВАНИЯ:</span>
                  <?php
                    $date = get_field('date', false, false);
                    $date = new DateTime($date);
                    echo $date->format('j M Y');
                  ?>
                </li>
                <li><span>ЦВЕТА:</span>
                  <span class="team-colors">
                    <?php
                      if( have_rows('colors') ): while ( have_rows('colors') ) : the_row(); ?>
                      <span style="background-color:<?php the_sub_field('colors'); ?>"></span>
                    <?php endwhile; endif; ?>
                  </span><!-- team-colors -->
                </li><!-- //ЦВЕТА -->
                <li><span>ГЛАВНЫЙ ТРЕНЕР:</span>
                  <?php $posts = get_field('head_coach'); if( $posts ): foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                    <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
                    <li>
                      <span>ГИМН:</span> <?php the_field('hymn'); ?>
                    </li>
                    <li>
                      <span>ТРЕНИРОВКИ:</span><?php the_field('practice'); ?>
                    </li>
                    <li>
                      <span>КОНТАКТЫ:</span><a href="<?php the_field('link_vk');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/vkontakte.png" alt="VK"></a>
                    <a href="<?php the_field('link_fb');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/facebook.png" alt="Facebook"></a>
                    <a href="<?php the_field('link_inatagram');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/instagram.png" alt="Instagram"></a>
                    </li>
                  <?php endforeach; endif; ?>
                </li><!-- //ГЛАВНЫЙ ТРЕНЕР -->
              </ul><!-- //description -->

            </div><!-- /.player-main-photo -->

            <div class="col-md-12 team-character">

              <span class="team-logo">
                <?php if ( has_post_thumbnail()) :?>
                  <?php the_post_thumbnail('full'); // Fullsize image for the single post ?>
                <?php endif; ?><!-- /post thumbnail -->
              </span><!-- team-logo -->

              <?php $image = get_field('main_image'); if( !empty($image) ): ?>
                <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
              <?php endif; ?>

            </div><!--/ team-character -->

            <div class="col-md-12 inner teams">

      <table class="team-score team-opossing">
          <tr>
            <th>Результат последних игр :</th>
            <?php if( have_rows('team_game') ): while ( have_rows('team_game') ) : the_row(); ?>
              <th>
                  <?php $posts = get_sub_field('opposing_team'); if( $posts ): foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                  <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID ) ); ?>" alt="">
                      <?php endforeach; endif; ?>
              </th>
                     <?php endwhile; endif; ?>
          </tr>
          <tr class="player-work">
                <td>1st Quarter</td>

                    <?php $i = 0;// check if the repeater field has rows of data
                      if( have_rows('team_game') ):
                      // loop through the rows of data
                      while ( have_rows('team_game') ) : the_row();?>

                <td>
                      <?php the_sub_field('1st_quarter'); ?>
                </td>

                    <?php $i = $i + get_sub_field('1st_quarter'); ?>
                    <?php endwhile; endif; ?>

                <td>
                    <?php echo $i; ?>
                </td>
            </tr>
            <tr>
                <td>2nd Quarter</td>

                    <?php $i = 0;
                  // check if the repeater field has rows of data
                  if( have_rows('team_game') ):
                    // loop through the rows of data
                      while ( have_rows('team_game') ) : the_row();?>

                <td>
                    <?php the_sub_field('2nd_quarter'); ?>
                </td>
                    <?php $i = $i + get_sub_field('2nd_quarter');?>
                    <?php endwhile; endif; ?>
                <td>
                   <?php echo $i; ?>
                </td>
            </tr>
            <tr>
                <td>3rd Quarter</td>
                      <?php $i = 0;
                    // check if the repeater field has rows of data
                    if( have_rows('team_game') ):
                      // loop through the rows of data
                        while ( have_rows('team_game') ) : the_row();?>
                <td>
                    <?php the_sub_field('3rd_quarter'); ?>
                </td>
                    <?php
                    $i = $i + get_sub_field('3rd_quarter');?>
                    <?php endwhile; endif; ?>
                <td>
                <?php echo $i; ?>
                </td>
            </tr>
            <tr>
                <td>4th Quarter</td>
                      <?php $i = 0;
                    // check if the repeater field has rows of data
                    if( have_rows('team_game') ):
                      // loop through the rows of data
                        while ( have_rows('team_game') ) : the_row();?>
                <td>
                    <?php the_sub_field('4th_quarter'); ?>
                </td>
                    <?php $i = $i + get_sub_field('4th_quarter'); ?>
                    <?php endwhile; endif; ?>
                <td>
                <?php echo $i; ?>
                </td>
          </tr>

       </table><!-- player-score team-opossing -->
      </div><!-- player-score -->
    </div>
  </div><!-- /.row -->




    <div class="row">
      <div class="col-md-12 people-desciption">
        <h4>Основатели и Тренера</h4>

          <div class="row">
            <?php $posts = get_field('people'); if( $posts ): ?>
              <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>

                <div class="col-md-3 people-desciption">
                  <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank">
                    <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id( $p->ID, "medium" ) ); ?>" alt="">
                    <?php echo get_the_title( $p->ID ); ?>
                  </a>
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

        <div class="owl-team-slide">
          <?php $images = get_field('team_gallery'); if( $images ): foreach( $images as $image ): ?>

            <div class="item-slide">
              <img src="<?php echo $image['sizes']['large']; ?>" alt="<?php echo $image['alt']; ?>" />
            </div>

          <?php endforeach; endif; ?>
        </div><!-- owl-team-slide -->

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
