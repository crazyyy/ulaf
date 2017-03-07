<div class="player-main-photo col-md-3">
            <?php if ( has_post_thumbnail()) :?>
              <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php the_post_thumbnail(); // Fullsize image for the single post ?>
              </a>
            <?php endif; ?><!-- /post thumbnail -->
          </div><!-- /.player-main-photo -->
          <div class="col-md-9 player-character">

            <span class="quarterback-image"><img src="<?php echo get_template_directory_uri(); ?>/img/teams/qb.png"></span>

            <!-- Player description -->
            <ul class="description single-player">
              <li><?php the_title(); ?></li>
              <li><span>КОМАНДА :</span>  <?php
                      $posts = get_field('player_team');
                      if( $posts ): ?>
                        <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
                        <a href="<?php echo get_permalink( $p->ID ); ?>" target="_blank"><?php echo get_the_title( $p->ID ); ?></a>
                        <?php endforeach; ?>
                      <?php endif; ?>
                </li>
                <li><span>ДАТА РОЖДЕНИЯ (ВОЗРАСТ): </span><?php
                  // get raw date
                  $date = get_field('player_age', false, false);
                  // make date object
                  $date = new DateTime($date); ?>
                  <?php
                  ?>
                  <?php echo $date->format('j M Y');?>
                       <?php
                        $birthday = $date->format('Y');
                        $current_year = date("Y");
                        $age = $current_year - $birthday;
                        echo $age; ?>
                  </li>
                  <li><span>РОСТ:</span> <?php the_field('height');?></li>
                  <li><span>ВЕС:</span> <?php the_field('weight');?></li>
                  <li><span>ИГРАЕТ С :</span> <?php the_field('start_to_play');?></li>
                  <li><span>ИГРОВОЙ НОМЕР:</span> <?php the_field('player_number');?></li>
                  <li><span>КОНТАКТЫ:</span>

                    <a href="<?php the_field('link_vk');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/vkontakte.png" alt="VK"></a>
                    <a href="<?php the_field('link_fb');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/facebook.png" alt="Facebook"></a>
                    <a href="<?php the_field('link_inatagram');?>" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/img/social/instagram.png" alt="Instagram"></a>
                  </li>
           </ul><!-- player description -->
         </div>