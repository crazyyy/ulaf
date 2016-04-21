<?php get_header(); ?>
<section class="container container-content">
  <div class="row">
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class('col-md-12'); ?>>

        <div class="row player-card">


          <div class="player-main-photo col-md-3">
            <?php if ( has_post_thumbnail()) :?>
              <a class="single-thumb" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <?php the_post_thumbnail(); // Fullsize image for the single post ?>
              </a>
            <?php endif; ?><!-- /post thumbnail -->
          </div><!-- /.player-main-photo -->
          <div class="col-md-9 player-character">


          <ul class="description">
          <li>ВИКТОР ЯНЧАК</li>
              <li>ГОД РОЖДЕНИЯ:</li>
              <li>РОСТ:</li>
              <li>ВЕС:</li>
              <li>В КОМАНДЕ С: </li>
              <li>ИГРОВОЙ НОМЕР:</li>
          </ul>
          </div><!--/ player-character -->


        </div><!-- /.row -->
    <div class="row">
    <div class="col-md-12 player-bio">
            <?php the_content(); ?>
          </div><!-- /.col-md-9 player-bio -->
      </div><!-- /row -->

      <?php the_tags( __( 'Tags: ', 'wpeasy' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

      <p><?php _e( 'Categorised in: ', 'wpeasy' ); the_category(', '); // Separated by commas ?></p>

      <p><?php _e( 'This post was written by ', 'wpeasy' ); the_author(); ?></p>

      <?php edit_post_link(); ?>




<table>
  <tr>
    <td>team</td>
    <td>
    <?php
      $posts = get_field('player_team');

      if( $posts ): ?>
        <ul>
        <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
            <li>
              <a href="<?php echo get_permalink( $p->ID ); ?>"><?php echo get_the_title( $p->ID ); ?></a>
            </li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td>position</td>
    <td><?php the_field('player_position');?></td>
  </tr>
  <tr>
    <td>team jearsey number</td>
    <td><?php the_field('player_number');?></td>
  </tr>
</table>

<?php

$images = get_field('player_gallery');

if( $images ): ?>
    <ul>
        <?php foreach( $images as $image ): ?>
            <li>
                <a href="<?php echo $image['url']; ?>">
                     <img src="<?php echo $image['sizes']['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" />
                </a>
                <p><?php echo $image['caption']; ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>



<h2>player stats</h2>
<h3>player qb stats</h3>

<table>

<tr>
  <th>game date</th>
  <th>player team</th>
  <th>opposing team</th>
  <th>player yds</th>
  <th>pass compl</th>
  <th>yds compl</th>
</tr>

<?php

// check if the repeater field has rows of data
if( have_rows('player_qb_stats') ):

  // loop through the rows of data
    while ( have_rows('player_qb_stats') ) : the_row();

?>

 <tr>

<td>date</td>
<td>
    <?php
      $posts = get_sub_field('player_team');
      if( $posts ): ?>
        <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <a href="<?php echo get_permalink( $p->ID ); ?>"><?php echo get_the_title( $p->ID ); ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
</td>
<td>
      <?php
      $posts = get_sub_field('opposing_team');
      if( $posts ): ?>
        <?php foreach( $posts as $p ): // variable must NOT be called $post (IMPORTANT) ?>
              <a href="<?php echo get_permalink( $p->ID ); ?>"><?php echo get_the_title( $p->ID ); ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
</td>
<td>
  <?php the_sub_field('pass_yds');?>
</td>
<td>
  <?php the_sub_field('pass_compl');?>
</td>
<td>
  <?php the_sub_field('yds_compl');?>
</td>


 </tr>
<?php

    endwhile;

endif;

?>



</table>


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
