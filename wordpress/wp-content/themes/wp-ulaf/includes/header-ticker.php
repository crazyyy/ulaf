<div class="stm-top-ticker-holder">
  <div class="heading-font stm-ticker-title"><span class="stm-red">Последние</span> новости</div>
  <ol class="stm-ticker">
    <?php query_posts("showposts=3"); ?>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
    <?php endwhile; endif; ?>
    <?php wp_reset_query(); ?>
  </ol>
</div>
