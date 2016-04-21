<?php get_header(); ?>

   <!-- slider -->

    <div id="fullcarousel-example" data-interval="6000" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <div class="item active">
                <img src="<?php echo get_template_directory_uri(); ?>/img/slider1.jpg">
            </div>
            <div class="item">
                <img src="<?php echo get_template_directory_uri(); ?>/img/slider2.jpg">
            </div>
            <div class="item">
                <img src="<?php echo get_template_directory_uri(); ?>/img/slider3.jpg">
            </div>
            <div class="item">
                <img src="<?php echo get_template_directory_uri(); ?>/img/slider4.jpg">
            </div>
        </div>
    </div>
    <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev"><i class="icon-prev fa fa-angle-left"></i></a>
    <a class="right carousel-control" href="#carousel-example-generic" data-slide="next"><i class="icon-next fa fa-angle-right"></i> </a>

    <div class="item">
        <div class="carousel-caption">
            <div class="slider_title">
                <h1>Новость, которую ждали все игроки и любители - Американского футбола в Украине!</h1>
            </div>

            <div class="descr">
                <p>Друзья! Новость, которую ждали все игроки и любители Американского футбола в Украине! ...</p>
            </div>
            <div class="slider_but">
                <a class="btn btn-primary">Присоединяйтесь</a>
                <a class="btn second">Подробнее</a>
            </div>
        </div>
    </div>
    <a class="left carousel-control" href="#fullcarousel-example" data-slide="prev"><i class="icon-prev fa fa-angle-left"></i></a>
    <a class="right carousel-control" href="#fullcarousel-example" data-slide="next"><i class="icon-next fa fa-angle-right"></i></a>
   <!-- slider -->


    <section class="section-news">
      <div class="container">

        <div class="row news_block">
          <h3>Последние Новости</h3>

          <!-- start news loop -->
          <div class="col-md-3">
            <a href="#" class="hover_image">
              <img src="<?php echo get_template_directory_uri(); ?>/img/content/news1.jpg">
              <span class="date">19.04.2016 16:25</span>
            </a>
            <h4><a href="#">Американский футбол в интересных фактах и цифрах</a></h4>
          </div>
          <div class="col-md-3">
            <a href="#" class="hover_image">
              <img src="<?php echo get_template_directory_uri(); ?>/img/content/news2.jpg">
              <span class="date">19.04.2016 16:25</span>
            </a>
            <h4><a href="#">Календаль-Сезон 2016 Ulaf Дивизион C</a></h4>
          </div>
          <div class="col-md-3">
            <a href="#" class="hover_image">
              <img src="<?php echo get_template_directory_uri(); ?>/img/content/news3.jpg">
              <span class="date">19.04.2016 16:25</span>
            </a>
            <h4><a href="#">Наш Друг и партнер</a></h4>
          </div>
          <div class="col-md-3">
            <a href="#" class="hover_image">
              <img src="<?php echo get_template_directory_uri(); ?>/img/content/news4.jpg">
              <span class="date">19.04.2016 16:25</span>
            </a>
            <h4><a href="#">Календаль-Сезон 2016 Ulaf Дивизион B</a></h4>
          </div>
          <!-- end news loop -->
        </div><!-- news_block -->

        <div class="row our-partners">
          <h3>Наши Партнеры</h3>

          <div class="col-md-12">
            <ol class="brands">
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand.jpg"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand2.png"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand3.png"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand4.png"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand5.png"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand6.png"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand7.png"></li>
              <li><img src="<?php echo get_template_directory_uri(); ?>/img/partners/brand7.jpg"></li>
            </ol>
          </div>
        </div><!-- our-partners -->

      </div><!-- container -->
    </section><!-- section-news -->

<?php get_footer(); ?>


