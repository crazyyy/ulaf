<?php
/**
 * Player Birthday
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.3
 * @version   4.4.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

if ( $link_posts ) {
	$caption = '<a href="' . get_permalink( $id ) . '">' . $caption . '</a>';
}

if ( has_post_thumbnail( $id ) ) {
	$thumbnail = get_the_post_thumbnail( $id, $size );
} else {
	$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-200x200.jpg' ) . '" alt="">';
}

echo '<div class="alc-birthdays__item-info">';
	echo '<figure class="alc-birthdays__item-img">';
		echo '<a href="' . get_permalink( $id ) . '">' . $thumbnail . '</a>';
	echo '</figure>';
	echo '<div class="alc-birthdays__item-inner">';
		echo '<h5 class="alc-birthdays__item-name">' . $caption . '</h5>';
	echo '</div>';
echo '</div>';
