<?php
/**
 * Player Gallery Thumbnail
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     1.9.13
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'icontag' => 'dt',
	'captiontag' => 'dd',
	'caption' => null,
	'size' => 'sportspress-crop-medium',
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );
$positions = wp_get_post_terms($id, 'sp_position');
$position = false;
if ($positions) {
    $position = $positions[0]->name;
}

// Add player number to caption if available
$player_number = get_post_meta( $id, 'sp_number', true );
if ( $player_number )
	$caption = '<strong>' . $player_number . '</strong> ' . $caption;

// Add caption tag if has caption
if ( $captiontag && $caption )
	$caption = '<' . $captiontag . ' class="wp-caption-text gallery-caption small-3 columns' . ( $player_number ? ' has-number' : '' ) . '">' . wptexturize( $caption ) . '</' . $captiontag . '>';

if ( $link_posts )
	$caption = '<a href="' . get_permalink( $id ) . '">' . $caption . '</a>';

if ( has_post_thumbnail( $id ) )
	$thumbnail = get_the_post_thumbnail( $id, 'blog_list' );
else
	$thumbnail = '<img width="150" height="150" src="http://www.gravatar.com/avatar/?s=150&d=mm&f=y" class="attachment-thumbnail wp-post-image">';

echo "<li class='gallery-item'>";
echo "
	<div class='player_image gallery-icon portrait'>"
	. '<a href="' . get_permalink( $id ) . '">' . $thumbnail . '</a>'
	. "</div>";
?>
	<div class="stm-player-title-wrap">
	<h4><a href="<?php echo esc_url( get_the_permalink($id) ); ?>"><?php echo get_the_title($id); ?></a></h4>
	<div class="player_like">
		<?php $like = ( get_post_meta($id, 'stm_like', true) == '' ) ? 0 : get_post_meta($id, 'stm_like', true); ?>
		<a href="#" class="like_button" data-id="<?php echo esc_attr( $id ); ?>" onclick="stm_like(jQuery(this)); return false;">
			<i class="fa fa-heart-o"></i> <span><?php echo esc_html($like); ?></span>
		</a>
	</div>
	</div>
	<div class="player_info clearfix <?php if($player_number == "") echo "position-full-width"; ?>">
		<div class="number"><i class="icon-soccer_ico_tshirt"></i> <?php echo esc_html( $player_number ); ?></div>
		<div class="position heading-font"><?php echo esc_html( $position );?></div>
	</div>

<?php echo "</li>";
