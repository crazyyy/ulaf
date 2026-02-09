<?php
/**
 * Player Slider Card
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
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
	'grouptag' => 'h4',
	'size' => 'sportspress-crop-medium',
	'show_all_players_link' => false,
	'show_positions' => get_option( 'sportspress_player_show_positions', 'yes' ) == 'yes' ? true : false,
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'show_age' => get_option( 'sportspress_player_show_age', 'yes' ) == 'yes' ? true : false,
	'show_nationality' => get_option( 'sportspress_player_show_nationality', 'yes' ) == 'yes' ? true : false,
	'show_nationality_flags' => get_option( 'sportspress_player_show_flags', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

// SportsPress Custom Text Strings
$sp_text_strings      = get_option( 'sportspress_text', array() );
$sp_text_age          = ! empty( $sp_text_strings['Age'] ) ? $sp_text_strings['Age'] : esc_html__( 'Age', 'alchemists' );
$sp_text_nationality  = ! empty( $sp_text_strings['Nationality'] ) ? $sp_text_strings['Nationality'] : esc_html__( 'Nationality', 'alchemists' );

// Determine number of players to display
if ( -1 === $number ):
	$number = (int) get_post_meta( $id, 'sp_number', true );
	if ( $number <= 0 ) $number = -1;
endif;

$size = $size;

$selector = 'team-roster-' . $id;

$countries = SP()->countries->countries;

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

if ( intval( $number ) > 0 )
	$limit = $number;

$group = new stdClass();
$group->term_id = null;
$group->name = null;
$group->slug = null;
$groups = array( $group );

$j = 0;
foreach ( $groups as $group ):
	$i = 0;

	$carousel_nav = '';

	// echo '<pre style="background-color: #fff;">' . var_export($data, true ) . '</pre>';

	$carousel_nav .= '<div class="team-roster-nav js-team-roster-nav d-block">';

		foreach( $data as $player_id => $performance ): if ( empty( $group->term_id ) || has_term( $group->term_id, 'sp_position', $player_id ) ):

			if ( isset( $limit ) && $i >= $limit ) continue;

			$title = get_the_title( $player_id );
			$title = trim( $title );

			ob_start();

				sp_get_template( 'player-carousel-thumbs__nav-item.php', array(
					'id'         => $player_id,
					'title'      => $title,
					'size'       => $size,
					'link_posts' => $link_posts,
				) );

			$carousel_nav .= ob_get_clean();

			$i++;

		endif; endforeach;

	$carousel_nav .= '</div>';

	$j++;

	if ( $i === 0 ) continue;

	// carouse nav output
	echo wp_kses_post( $carousel_nav );

endforeach;



$j = 0;
foreach ( $groups as $group ):
	$i = 0;

	$carousel_slides = '';

	$carousel_slides .= '<div class="team-roster team-roster--card team-roster--boxed team-roster--slider-with-nav js-team-roster--slider-with-nav mb-0 pb-0">';

		foreach( $data as $player_id => $performance ): if ( empty( $group->term_id ) || has_term( $group->term_id, 'sp_position', $player_id ) ):

			if ( isset( $limit ) && $i >= $limit ) continue;

			$title = get_the_title( $player_id );
			$title = trim( $title );

			ob_start();

				sp_get_template( 'player-carousel-thumbs__slide-item.php', array(
					'id'         => $player_id,
					'title'      => $title,
					'size'       => $size,
					'link_posts' => $link_posts,
					'show_positions' => $show_positions,
					'show_age' => $show_age,
					'sp_text_age' => $sp_text_age,
					'sp_text_nationality' => $sp_text_nationality,
					'show_nationality' => $show_nationality,
					'show_nationality_flags' => $show_nationality_flags,
					'countries' => $countries,
				) );

			$carousel_slides .= ob_get_clean();

			$i++;

		endif; endforeach;

	$carousel_slides .= '</div>';

	$j++;

	if ( $i === 0 ) continue;

	echo wp_kses_post( $carousel_slides );

endforeach;
?>

<script>
	(function($){
		$(function() {
			var slick_team_roster_with_nav = $('.js-team-roster--slider-with-nav');
			var slick_team_roster_nav = $('.js-team-roster-nav');

			slick_team_roster_with_nav.slick({
				slidesToShow: 1,
				arrows: true,
				dots: false,
				speed: 600,
				rows: 0,
				rtl: <?php echo esc_js( $is_rtl ); ?>,
				cssEase: 'cubic-bezier(0.23, 1, 0.32, 1)',
				asNavFor: slick_team_roster_nav,
				responsive: [
					{
						breakpoint: 992,
						settings: {
							arrows: false,
						}
					}
				]
			});

			slick_team_roster_nav.slick({
				slidesToShow: 6,
				slidesToScroll: 1,
				asNavFor: slick_team_roster_with_nav,
				focusOnSelect: true,
				arrows: true,
				centerMode: true,
				centerPadding: '0px',
				rows: 0,
				rtl: <?php echo esc_js( $is_rtl ); ?>,
				responsive: [
					{
						breakpoint: 1200,
						settings: {
							slidesToShow: 5,
						}
					},
					{
						breakpoint: 992,
						settings: {
							slidesToShow: 4,
						}
					},
					{
						breakpoint: 768,
						settings: {
							slidesToShow: 3,
						}
					},
					{
						breakpoint: 540,
						settings: {
							slidesToShow: 2,
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
						}
					}
				]
			});

		});
	})(jQuery);
</script>
