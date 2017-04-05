<?php /* Template Name: Team ULAF */ get_header(); ?>




<?php if (have_posts()): while (have_posts()) : the_post(); ?>
 <!--     <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>> -->

<div class="our-team">
  <div class="container">
    <div class="row">

    <div class="col-md-5 left-bg-block ">
          <div class="team-sign">
            <?php the_title(); ?>
          </div>
          <?php

          $posts = get_field('team');

          if( $posts ): ?>
            <ul class="team-list" style="overflow-y:auto; height: 325px;">
          <?php foreach( $posts as $post): // variable must be called $post (IMPORTANT) ?>
          <?php setup_postdata($post); ?>
            <li><span name="tab1"><?php the_title(); ?></span></li>

          <?php endforeach; ?>
          </ul>

          <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
<?php endif; ?>
    </div>
    <div class="col-md-7 right-bg-block">
      <div class="person" id="tab1">
        <div class="person-image">
          <img src="<?php echo get_template_directory_uri(); ?>/img/landry1.jpg" alt="">
          <div class="person-info">
            <span class="person-name">Максим Шило</span>
            <span class="person-role">Игрок</span>
            <span class="person-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum repudiandae optio sequi voluptatibus quaerat veritatis cupiditate amet laborum praesentium illo labore provident vitae ipsum nobis, perferendis debitis, blanditiis eaque maiores explicabo ea dicta soluta eveniet facere minima? Dignissimos dolorem est, illo dolorum vero quam reprehenderit saepe animi dolor in! Nulla, commodi minus quidem eius nam debitis distinctio quo sit repellat impedit veritatis harum ducimus quasi nostrum deserunt quaerat exercitationem dignissimos corrupti. Id magnam earum placeat dignissimos explicabo quae libero aliquam reiciendis obcaecati in. Eos error repudiandae dolore voluptatibus nisi cum vero quas voluptatem adipisci odit similique saepe aliquam, commodi reiciendis.</span>
          </div>
        </div>
      </div>
      <div class="person" id="tab2">
        <div class="person-image">
          <img src="<?php echo get_template_directory_uri(); ?>/img/brady.jpg" alt="">
          <div class="person-info">
            <span class="person-name">Миша Гусак</span>
            <span class="person-role">Игрок</span>
            <span class="person-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda velit pariatur deserunt nam eveniet aliquid aliquam. Assumenda dolores voluptas dolorem, blanditiis rem eaque provident, porro facere impedit consectetur laudantium animi.</span>
          </div>
        </div>
      </div>
      <div class="person" id="tab3">
        <div class="person-image">
          <img src="<?php echo get_template_directory_uri(); ?>/img/mack.jpg" alt="">
          <div class="person-info">
            <span class="person-name">Коля Лесовой</span>
            <span class="person-role">Игрок</span>
            <span class="person-description">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda velit pariatur deserunt nam eveniet aliquid aliquam. Assumenda dolores voluptas dolorem, blanditiis rem eaque provident, porro facere impedit consectetur laudantium animi.</span>
          </div>
        </div>
      </div>
    </div>

    </div>
    </div>
  </div>

<?php endwhile; endif; ?>


</div>
</div>

<?php get_footer(); ?>

<!-- <div class="container game-info">
function openName(evt, personName) {
  var i, x, tablinks;
  x = document.getElementsByClassName("person");
  for (i = 0; i < x.length; i++) {
     x[i].style.display = "none";
  }

  document.getElementById(personName).style.display = "block";
  evt.currentTarget.className;

      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              16.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Pirates (Odessa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
            </div>
            <div class="col-md-2 game-score">
              <span>00-48</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
              <span>Lumberjacks (Uzhgorod)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              10.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Bandits (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png">
            </div>
            <div class="col-md-2 game-score">
              <span>46-15</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png">
              <span>Bulldogs (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              10.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Rockets (Dnepr)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/rockets.png">
            </div>
            <div class="col-md-2 game-score">
              <span>26-00</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
              <span>Gepardes (Yuzhny)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              09.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Аtlantes (Kharkov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png">
            </div>
            <div class="col-md-2 game-score">
              <span>27-14</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/patrioty.png">
              <span>Patriots (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              03.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Wolves (Vinnitsa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png">
            </div>
            <div class="col-md-2 game-score">
              <span>36-12</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/slavs.png">
              <span>Slavs (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>


    <div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              03.07
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gladiators (Hmelnitskiy)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/glad.png">
            </div>
            <div class="col-md-2 game-score">
              <span>66-06</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/mon.png">
              <span>Monarchs (Rovno)</span>
            </div>
      </div>row championship
        </a>
    </div>


<div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              26.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gepardes (Yuzhny)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
            </div>
            <div class="col-md-2 game-score">
              <span>06-08</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/vikings.png">
              <span>Vikings (Nikolaev)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              26.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Monarchs (Rovno)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/mon.png">
            </div>
            <div class="col-md-2 game-score">
              <span>40-00</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/eagles.png">
              <span>Eagles (Zdolbunov)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              26.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Bulldogs (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png">
            </div>
            <div class="col-md-2 game-score">
              <span>35-14</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png">
              <span>Аtlantes (Kharkov)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              25.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Dolphins (Mariupol)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dolph.png">
            </div>
            <div class="col-md-2 game-score">
              <span>48-00</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/tigers.png">
              <span>Tigers (Kharkov)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              25.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Lions (Lvov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/lions.png">
            </div>
            <div class="col-md-2 game-score">
              <span>00-52</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png">
              <span>Bandits (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              25.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Lumberjacks (Uzhgorod)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
            </div>
            <div class="col-md-2 game-score">
              <span>38-07</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/patrioty.png">
              <span>Patriots (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              12.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gepardes (Yuzhny)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
            </div>
            <div class="col-md-2 game-score">
              <span>22-06</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/tigers.png">
              <span>Tigers (Kharkov)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              12.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gladiators (Hmelnitskiy)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/glad.png">
            </div>
            <div class="col-md-2 game-score">
              <span>8-44</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/slavs.png">
              <span>Slavs (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              12.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Jokers (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dzhokery.png">
            </div>
            <div class="col-md-2 game-score">
              <span>06-60</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png">
              <span>Wolves (Vinnitsa)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              12.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Patriots (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/patrioty.png">
            </div>
            <div class="col-md-2 game-score">
              <span>28-00</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
              <span>Pirates (Odessa)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион А</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              11.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Bandits (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png">
            </div>
            <div class="col-md-2 game-score">
              <span>16-18</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/zubr.jpg">
              <span>Bisons(Минск)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              05.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Rockets (Dnepr)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/rockets.png">
            </div>
            <div class="col-md-2 game-score">
              <span>54-00</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dolph.png">
              <span>Dolphins (Mariupol)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              04.06
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Bulldogs (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png">
            </div>
            <div class="col-md-2 game-score">
              <span>28-34</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/zubr.jpg">
              <span>Bisons(Minsk)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              29.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Slavs (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/slavs.png">
            </div>
            <div class="col-md-2 game-score">
              <span>52-8</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/eagles.png">
              <span>Eagles (Zdolbunov)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              29.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gladiators (Hmelnitskiy)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/glad.png">
            </div>
            <div class="col-md-2 game-score">
              <span>0-90</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png">
              <span>Wolves (Vinnitsa)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              29.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Jokers (Kiev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dzhokery.png">
            </div>
            <div class="col-md-2 game-score">
              <span>34-6</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/mon.png">
              <span>Monarchs (Rovno)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              29.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Аtlantes (Kharkov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png">
            </div>
            <div class="col-md-2 game-score">
              <span>0-22</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png">
              <span>Bandits (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

<div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              29.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Lumberjacks (Uzhgorod)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
            </div>
            <div class="col-md-2 game-score">
              <span>38-7</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/lions.png">
              <span>Lions (Lvov)</span>
            </div>
      </div>row championship
        </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              22.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Vikings (Nikolaev)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/vikings.png">
            </div>
            <div class="col-md-2 game-score">
              <span>12-20</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/rockets.png">
              <span>Rockets (Dnepr)</span>
            </div>
      </div>row championship
        </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              22.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Tigers (Kharkov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/tigers.png">
            </div>
            <div class="col-md-2 game-score">
              <span>6-22</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
              <span>Gepardes (Yuzhny)</span>
            </div>
      </div>row championship
        </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              22.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Pirates (Odessa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
            </div>
            <div class="col-md-2 game-score">
              <span>10-40</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png">
              <span>Bulldogs (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>


    <div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              15.05
            </div>
            <a href="http://ulaf.pp.ua/games/monra">
            <div class="col-md-4 team-game">
              <span>Wolves (Vinnitsa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/wolves.png">
            </div>
            <div class="col-md-2 game-score">
              <span>98-13</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/mon.png">
              <span>Monarchs (Rovno)</span>
            </div>
      </div>row championship
        </a>
    </div>


    <div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              15.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Eagles (Zdolbunov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/eagles.png">
            </div>
            <div class="col-md-2 game-score">
              <span>24-0</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dzhokery.png">
              <span>Jokers (Kiev)</span>
            </div>
      </div>row championship
        </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион А</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              14.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Аtlantes (Kharkov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png">
            </div>
            <div class="col-md-2 game-score">
              <span>28-41</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/zubr.jpg">
              <span>Bisons(Minsk)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              08.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Rockets (Dnepr)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/rockets.png">
            </div>
            <div class="col-md-2 game-score">
              <span>58-0</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/tigers.png">
              <span>Tigers (Kharkov)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              07.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gepardes (Yuzhny)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
            </div>
            <div class="col-md-2 game-score">
              <span>12-20</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dolph.png">
              <span>Dolphins (Mariupol)</span>
            </div>
      </div>row championship
    </a>
    </div>

        <div class="container game-info">
      <h2>Дивизион А</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              08.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Pirates (Odessa)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
            </div>
            <div class="col-md-2 game-score">
              <span>17-13</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/lions.png">
              <span>Lions (Lvov)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион А</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              08.05
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Lumberjacks (Uzhgorod)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/les.png">
            </div>
            <div class="col-md-2 game-score">
              <span>39-38</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bandits.png">
              <span>Bandits (Kiev)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              24.04
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Monarchs (Rovno)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/mon.png">
            </div>
            <div class="col-md-2 game-score">
              <span>6-22</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/dzhokery.png">
              <span>Jokers (Kiev)</span>
            </div>
      </div>row championship
    </a>
    </div>

      <div class="container game-info">
      <h2>Дивизион B</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              24.04
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Eagles (Zdolbunov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/eagles.png">
            </div>
            <div class="col-md-2 game-score">
              <span>8-50</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/glad.png">
              <span>Gladiators (Хмельницк.)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
    <h2>Дивизион A</h2>
      <div class="row championship">
          <div class="col-md-1 game-date">
              24.04
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Bulldogs (Kiev)</span>
              <img src="<?php echo get_template_directory_uri(); ?>/img/teams/bulgogs.png">
            </div>
            <div class="col-md-2 game-score">
              <span>27-0</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/lions.png">
              <span>Lions (Lvov)</span>
            </div>
    </div>row championship
  </a>
  </div>



    <div class="container game-info">
    <h2>Дивизион A</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              23.04
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Patriots (Kiev)</span>
              <img src="<?php echo get_template_directory_uri(); ?>/img/teams/patrioty.png">
            </div>
            <div class="col-md-2 game-score">
              <span>20-42</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/zubr.jpg">
              <span>Bisons(Minsk)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
    <h2>Дивизион C</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              24.04
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Gepardes (Yuzhny)</span>
              <img src="<?php echo get_template_directory_uri(); ?>/img/teams/jeps.png">
            </div>
            <div class="col-md-2 game-score">
              <span>6-14</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/vikings.png">
              <span>Vikings (Nikolaev)</span>
            </div>
      </div>row championship
    </a>
    </div>

    <div class="container game-info">
    <h2>Дивизион А</h2>
        <div class="row championship">
            <div class="col-md-1 game-date">
              17.04
            </div>
            <a href="#">
            <div class="col-md-4 team-game">
              <span>Аtlantes (Kharkov)</span>
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/atlants.png">
            </div>
            <div class="col-md-2 game-score">
            <span>26</span><span>-29</span>
            </div>
            <div class="col-md-5 team-game">
            <img src="<?php echo get_template_directory_uri(); ?>/img/teams/pirates.png">
              <span>Pirates (Odessa) </span>
            </div>
      </div>row championship
    </a>
    </div>







    </article> -->
