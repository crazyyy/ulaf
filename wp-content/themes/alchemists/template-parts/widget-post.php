<?php
/**
 * Template part for displaying a post in custom widgets
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author 		Dan Fisher
 * @package 	Alchemists
 * @since     1.0.0
 * @version   2.1.0
 */

?>

<li class="posts__item <?php echo esc_attr( $post_class ); ?>">
  <div class="posts__inner">
    <?php alchemists_post_category_labels(); ?>
    <h6 class="posts__title" title="<?php the_title_attribute(); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
    <time datetime="<?php the_time('c'); ?>" class="posts__date">
      <?php the_time( get_option('date_format') ); ?>
    </time>
    <div class="posts__excerpt">
      <?php echo alchemists_string_limit_words( get_the_excerpt(), 20); ?>
    </div>
  </div>
</li>
