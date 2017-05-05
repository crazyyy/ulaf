
<div class="stm-footer" >
    <div id="stm-footer-top">
      <div id="footer-main">
        <div class="footer-widgets-wrapper less_4">
          <div class="container">
            <div class="widgets stm-cols-4 clearfix">
              <aside id="text-2" class="widget widget_text">
                <div class="widget-wrapper">
                  <div class="widget-title">
                    <h6>О нас</h6></div>
                  <div class="textwidget">
                    <div class="stm-text-lighten">Миссия Украинской лиги Американского футбола - это Популяризация Американского футбола в Украине, оздоровление нации, популяризация здорового образа жизни, а также повышение рейтинга нашей страны на международной арене.</div>
                  </div>
                </div>
              </aside>
              <aside id="recent-posts-3" class="widget widget_recent_entries">
                <div class="widget-wrapper">
                  <div class="widget-title">
                    <h6>Последние новости</h6></div>
                  <ul>
                   <?php query_posts("showposts=2"); ?>
                   <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                    <li>
                      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                      <span class="post-date"><?php the_time('m-d-Y'); ?></span>
                    </li>
                    <?php endwhile; endif; ?>
                    <?php wp_reset_query(); ?>
                  </ul>
                </div>
              </aside>
              <aside id="nav_menu-2" class="widget widget_nav_menu">
                <div class="widget-wrapper">
                  <div class="widget-title">
                    <h6>Ссылки</h6></div>
                  <div class="menu-widget-menu-container">
                    <ul id="menu-widget-menu" class="menu">
                      <li id="menu-item-2703" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-84 current_page_item menu-item-2703"><a href="http://ulafua.com/">Главная</a></li>
                      <li id="menu-item-2710" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2710"><a href="http://ulafua.com/news">Новости</a></li>
                      <li id="menu-item-2707" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2707"><a href="<?php the_permalink(); ?>">Команды</a></li>
                      <li id="menu-item-2704" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2704"><a href="http://ulafua.com/druzya-i-partnery.htm">Друзья и партнеры</a></li>
                      <li id="menu-item-2709" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2709"><a href="http://ulafua.com/foto.htm">Медиа</a></li>
                      <li id="menu-item-2706" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2706"><a href="http://ulafua.com/sostav.htm">Сборная</a></li>
                      <li id="menu-item-2708" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2708"><a href="http://ulafua.com/istoriya-ulaf.htm">О нас</a></li>
                      <li id="menu-item-2705" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2705"><a href="http://ulafua.com/kontakty.htm">Контакты</a></li>
                    </ul>
                  </div>
                </div>
              </aside>
              <aside id="contacts-2" class="widget widget_contacts">
                <div class="widget-wrapper">
                  <div class="widget-title">
                    <h6>Наши контакты</h6></div>
                  <ul class="stm-list-duty heading-font">
                    <li class="widget_contacts_address">
                      <div class="icon"><i class="fa fa-map-marker"></i></div>
                      <div class="text">Ukraine, Kyiv — 04201, st. Kondratuka, 7, office 712<div>
                    </li>
                    <li class="widget_contacts_phone">
                      <div class="icon"><i class="fa fa-phone"></i></div>
                      <div class="text">+38 097 111 51 21</div>
                    </li>
                    <li>
                      <div class="icon"><i class="fa fa-fax"></i></div>
                      <div class="text">+33 (0) 1 43 11 14 71</div>
                    </li>
                    <li class="widget_contacts_mail">
                      <div class="icon"><i class="fa fa-envelope"></i></div>
                      <div class="text"><a href="mailto:info@ulafua.com">info@ulafua.com</a></div>
                    </li>
                  </ul>
                </div>
              </aside>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="stm-footer-bottom">
      <div class="container">
        <div class="clearfix">
          <div class="footer-bottom-left">
            <div class="footer-bottom-left-text">
              © 2017 Ulafua.com - Все права защищены. </div>
          </div>
          <div class="footer-bottom-right">
            <div class="clearfix">
              <div class="footer-bottom-right-text">
                Украинская Лига Американского Футбола</div>
              <div class="footer-socials-unit">
                <div class="h6 footer-socials-title">
                  Мы в соц.сетях: </div>
                <ul class="footer-bottom-socials stm-list-duty">
                  <li class="stm-social-facebook">
                    <a href="https://www.facebook.com/ulaf.ua/" target="_blank">
                      <i class="fa fa-facebook"></i>
                    </a>
                  </li>
                  <li class="stm-social-twitter">
                    <a href="https://twitter.com/ulafcool" target="_blank">
                      <i class="fa fa-twitter"></i>
                    </a>
                  </li>
                  <li class="stm-social-instagram">
                    <a href="https://www.instagram.com/ulafcool/" target="_blank">
                      <i class="fa fa-instagram"></i>
                    </a>
                  </li>
                  <li class="stm-social-linkedin">
                    <a href="https://vk.com/ulafua" target="_blank">
                      <i class="fa fa-vk"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php wp_footer(); ?>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery-migrate.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery_form.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/bootstrap.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/owl.carousel.min.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/imagesloaded.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/isotope.pkgd.min.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/smoothScroll.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/ticker.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/fotorama.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/skrollr.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/select2.js"></script>

  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/scripts.js"></script>

</body>
</html>
