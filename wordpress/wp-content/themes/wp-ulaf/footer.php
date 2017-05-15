<div class="stm-footer" >
  <div id="stm-footer-top">
    <div id="footer-main">
      <div class="footer-widgets-wrapper less_4">
        <div class="container">
          <div class="widgets stm-cols-4 clearfix">

            <?php if ( is_active_sidebar('widgetarea2') ) : ?>
              <?php dynamic_sidebar( 'widgetarea2' ); ?>
            <?php else : ?>
              <!-- If you want display static widget content - write code here
              RU: Здесь код вывода того, что необходимо для статического контента виджетов -->
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="stm-footer-bottom">
    <div class="container">
      <div class="clearfix">
        <div class="footer-bottom-left">
          <div class="footer-bottom-left-text">© 2017 Ulafua.com - Все права защищены. </div>
        </div>
        <div class="footer-bottom-right">
          <div class="clearfix">
            <div class="footer-bottom-right-text">Украинская Лига Американского Футбола</div>
            <div class="footer-socials-unit">
              <div class="h6 footer-socials-title">Мы в соц.сетях: </div>
              <ul class="footer-bottom-socials stm-list-duty">
                <li class="stm-social-facebook">
                  <a href="https://www.facebook.com/ulaf.ua/" rel="nofollow" target="_blank">
                    <i class="fa fa-facebook"></i>
                  </a>
                </li>
                <li class="stm-social-twitter">
                  <a href="https://twitter.com/ulafcool" rel="nofollow" target="_blank">
                    <i class="fa fa-twitter"></i>
                  </a>
                </li>
                <li class="stm-social-instagram">
                  <a href="https://www.instagram.com/ulafcool/" rel="nofollow" target="_blank">
                    <i class="fa fa-instagram"></i>
                  </a>
                </li>
                <li class="stm-social-vk">
                  <a href="https://vk.com/ulafua" rel="nofollow" target="_blank">
                    <i class="fa fa-vk"></i>
                  </a>
                </li>
                <li class="stm-social-vk">
                  <a href="https://www.youtube.com/channel/UCX-eSs3MKCWTAnm3rQJ7WJQ" rel="nofollow" target="_blank">
                    <i class="fa fa-youtube"></i>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div><!-- stm-footer -->

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
 <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/ammap.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/ukraineLow.js"></script>
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/scripts.js"></script>


</body>
</html>
