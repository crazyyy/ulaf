<?php
/**
 * Player Slider Card
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$html5 = current_theme_supports( 'html5', 'gallery' );
$defaults = array(
	'id' => get_the_ID(),
	'title' => false,
	'number' => -1,
	'grouping' => null,
	'orderby' => 'default',
	'order' => 'ASC',
	'itemtag' => 'div',
	'icontag' => 'figure',
	'captiontag' => 'div',
	'grouptag' => 'h4',
	'columns' => 3,
	'size' => 'sportspress-crop-medium',
	'show_all_players_link' => false,
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

// Determine number of players to display
if ( -1 === $number ):
	$number = (int) get_post_meta( $id, 'sp_number', true );
	if ( $number <= 0 ) $number = -1;
endif;

// Autoplay
$roster_slider_autoplay = get_field( 'roster_slider_autoplay' );
// Custom Background
$roster_slider_bg       = get_field( 'roster_slider_background_image' );

if ( $roster_slider_bg ) {
	$roster_slider_bg = 'style="background-image:url(' . esc_url( $roster_slider_bg ). ')"';
}

if ( $roster_slider_autoplay ) {
	$roster_slider_autoplay = 'true';
} else {
	$roster_slider_autoplay = 'false';
}

$itemtag = tag_escape( $itemtag );
$captiontag = tag_escape( $captiontag );
$icontag = tag_escape( $icontag );
$valid_tags = wp_kses_allowed_html( 'post' );
if ( ! isset( $valid_tags[ $itemtag ] ) )
	$itemtag = 'div';
if ( ! isset( $valid_tags[ $captiontag ] ) )
	$captiontag = 'div';
if ( ! isset( $valid_tags[ $icontag ] ) )
	$icontag = 'div';

$columns = intval( $columns );
$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
$size = $size;
$float = is_rtl() ? 'right' : 'left';

$selector = 'sp-player-gallery-' . $id;

$list = new SP_Player_List( $id );
$data = $list->data();

// Remove the first row to leave us with the actual data
unset( $data[0] );

if ( $grouping === null || $grouping === 'default' ):
	$grouping = $list->grouping;
endif;

if ( $orderby == 'default' ):
	$orderby = $list->orderby;
	$order = $list->order;
elseif ( $orderby == 'rand' ):
	uasort( $data, 'sp_sort_random' );
else:
	$list->priorities = array(
		array(
			'key' => $orderby,
			'order' => $order,
		),
	);
	uasort( $data, array( $list, 'sort' ) );
endif;

$slider_style = $slider_div = '';
$size_class = sanitize_html_class( $size );
$slider_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";
echo apply_filters( 'gallery_style', $slider_style . "\n\t\t" );
?>
<?php echo wp_kses_post( $slider_div ); ?>
	<?php
	if ( intval( $number ) > 0 )
		$limit = $number;

	if ( $grouping === 'position' ):
		$groups = get_terms( 'sp_position', array( 'orderby' => 'slug' ) );
	else:
		$group = new stdClass();
		$group->term_id = null;
		$group->name = null;
		$group->slug = null;
		$groups = array( $group );
	endif;

	$j = 0;

	foreach ( $groups as $group ):
		$i = 0;

		$slider = '';

		if ( ! empty( $group->name ) ):
			$slider .= '<a name="group-' . $group->slug . '" id="group-' . $group->slug . '"></a>';
			$slider .= '<' . $grouptag . ' class="sp-gallery-group-name player-group-name player-gallery-group-name">' . $group->name . '</' . $grouptag . '>';
		endif;

		foreach( $data as $player_id => $performance ): if ( empty( $group->term_id ) || has_term( $group->term_id, 'sp_position', $player_id ) ):

			if ( isset( $limit ) && $i >= $limit ) continue;

			$caption = get_the_title( $player_id );
			$caption = trim( $caption );

			ob_start();

				sp_get_template( 'player-slider-card__slide.php', array(
					'id' => $player_id,
					'itemtag' => $itemtag,
					'icontag' => $icontag,
					'captiontag' => $captiontag,
					'caption' => $caption,
					'size' => $size,
					'link_posts' => $link_posts,
				) );

			$slider .= ob_get_clean();

			$i++;

		endif; endforeach;

		$j++;

		if ( $i === 0 ) continue;

		echo '<div class="sp-template sp-template-player-gallery sp-template-gallery">';

		echo '<div class="team-roster team-roster--card team-roster--card-slider sp-player-gallery-wrapper sp-gallery-wrapper" ' . $roster_slider_bg .'>';

		echo wp_kses_post( $slider );

		echo '</div>'; ?>

		<script>
			(function($){
				$(function() {
					var $slick_team_roster_card = $('.team-roster--card-slider');

					// Team Roster - Card Slider
					$slick_team_roster_card.slick({
						slidesToShow: 1,
						arrows: true,
						dots: false,
						rtl: <?php echo esc_js( $is_rtl ); ?>,
						rows: 0,
						responsive: [
							{
								breakpoint: 992,
								settings: {
									arrows: false,
								}
							}
						]
					});

				});
			})(jQuery);
		</script>

		<?php if ( $show_all_players_link && ( 'position' !== $grouping || $j == count( $groups ) ) ) {
			echo '<div class="sp-player-gallery-link sp-gallery-link sp-view-all-link"><a href="' . get_permalink( $id ) . '">' . esc_html__( 'View all players', 'alchemists' ) . '</a></div>';
		}

		echo '</div>';

	endforeach;

echo "</div>\n";
