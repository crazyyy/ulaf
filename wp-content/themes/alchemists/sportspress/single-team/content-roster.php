<?php
/**
 * The template for displaying Single Team
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

// get Team colors for player
$team_color_primary   = get_field( 'team_color_primary', $id );
$team_color_secondary = get_field( 'team_color_secondary', $id );

if ( $team_color_primary ) {
	$color_primary   = $team_color_primary;
} else {
	$color_primary   = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
}

$roster_type_get = isset( $_GET['roster_type'] ) ? $_GET['roster_type'] : '';
$roster_type     = get_field( 'gallery_roster_type' );

// to-do: add option to select columns on the team page
$columns = 3;

// to-do: add option to select number of players to display on the team page
$number = -1;

$roster = 'gallery';

if ( $roster_type_get ) {
	// Roster layout based on GET parameter
	if ( $roster_type_get === 'blocks' ) {
		$roster = 'blocks';
	} elseif ( $roster_type_get === 'cards' ) {
		$roster = 'cards';
	} elseif ( $roster_type_get === 'slider-card' ) {
		$roster = 'slider';
	} elseif ( $roster_type_get === 'carousel_thumbs' ) {
		$roster = 'carousel-thumbs';
	} elseif ( $roster_type_get === 'slider' ) {
		if ( alchemists_sp_preset( 'soccer') ) {
			$roster = 'slider-card';
		} elseif ( alchemists_sp_preset( 'football') ) {
			$roster = 'slider-carousel';
		} else {
			$roster = 'slider';
		}
	}

} else {

	// Roster layout based on selected field
	if ( $roster_type === 'blocks' ) {
		$roster = 'blocks';
	} elseif ( $roster_type === 'cards' ) {
		$roster = 'cards';
	} elseif ( $roster_type === 'slider-card' ) {
		$roster = 'slider';
	} elseif ( $roster_type === 'carousel_thumbs' ) {
		$roster = 'carousel-thumbs';
	} elseif ( $roster_type === 'slider' ) {
		if ( alchemists_sp_preset( 'soccer') ) {
			$roster = 'slider-card';
		} elseif ( alchemists_sp_preset( 'football') ) {
			$roster = 'slider-carousel';
		} else {
			$roster = 'slider';
		}
	}

}

// Gallery Roster
$gallery_roster_on   = get_field( 'gallery_roster_show' );
$gallery_roster      = get_field( 'gallery_roster' );
$gallery_roster_id   = '';

// List Roster
$list_roster_on       = get_field( 'list_roster_show' );
$list_roster          = get_field( 'list_roster' );
$list_roster_id       = '';

// Featured
$featured_player_on        = get_field( 'featured_player' );
$featured_player           = get_field( 'team_featured_player' );
$featured_player_shortcode = get_field( 'team_roster_featured_player' );
$featured_player_args      = array();

// Featured Player
$featured_player_args = array(
	'post_type'      => 'sp_player',
	'p'              => $featured_player,
	'posts_per_page' => 1,
);

if ( $featured_player_on && $featured_player ) {
	$list_roster_width = 'col-lg-8';
} else {
	$list_roster_width = 'col-lg-12';
}

// Staff
$staff_display  = get_field( 'team_staff' );
$staff_position = get_field( 'team_staff_position' );
$staff_type     = get_field( 'team_staff_display_type' );
$staff_heading  = get_field( 'team_staff_heading' );

// Select Staff template
$staff_template = 'event-staff.php';
if ( 'gallery' == $staff_type ) {
	$staff_template = 'single-team/staff/alc-team-roster-staff-gallery.php';
} elseif ( 'blocks' == $staff_type ) {
	$staff_template = 'single-team/staff/alc-team-roster-staff-blocks.php';
}

// Display Staff before Players
if ( $staff_display && $staff_position == 'before' ) {
	echo sp_get_template( $staff_template, array(
		'id' => $id,
		'index' => 0,
		'staff_heading' => $staff_heading
	) );
}

// Display Player Gallery Roster
if ( $gallery_roster_on && $gallery_roster ) {
	$gallery_roster_id = $gallery_roster->ID;
	sp_get_template( 'player-' . $roster . '.php', array(
		'id'      => $gallery_roster_id,
		'columns' => $columns,
		'number'  => $number,
	) );
}

// Display Staff after Players
if ( $staff_display && $staff_position == 'after' ) {
	echo sp_get_template( $staff_template, array(
		'id' => $id,
		'index' => 0,
		'staff_heading' => $staff_heading
	) );
}
?>

<div class="row">
	<div class="<?php echo esc_attr( $list_roster_width ); ?>">
		<?php // Display List Roster
		if ( $list_roster_on && $list_roster ) {

			foreach ( $list_roster as $list_roster_item ) {
				$list_roster_id = $list_roster_item->ID;

				sp_get_template( 'player-list.php', array(
					'id'      => $list_roster_id,
					'rows'    => 11,
				) );

			}
		} ?>
	</div>

	<?php if ( $featured_player_on ) : ?>
	<div class="col-lg-4">

		<?php
		if ( $featured_player_shortcode ) {

			// Featured Player displayed with shortcode
			echo do_shortcode( $featured_player_shortcode );

		} elseif ( $featured_player ) {

			// Featured Player Selected with ACF Field (old way)
			$sp_preset_name = '';
			if ( alchemists_sp_preset( 'football') ) {
				$sp_preset_name = 'football';
			} elseif ( alchemists_sp_preset( 'soccer') ) {
				$sp_preset_name = 'soccer';
			} else {
				$sp_preset_name = 'default';
			}

			$defaults = array(
				'current_season' => get_option( 'sportspress_season', '' ),
				'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
			);
			extract( $defaults, EXTR_SKIP );

			$performance_desc = esc_html__( 'In career', 'alchemists' );

			$loop = new WP_Query( $featured_player_args );
			while ( $loop->have_posts() ) : $loop->the_post();

				$player_id = $featured_player;

				$player = new SP_Player( $player_id );
				$data = $player->data( 0, false );
				unset( $data[0] );

				if ( ! empty( $current_season ) && ! $show_total ) {
					if ( isset( $data[ $current_season ] ) ) {
						$data = $data[ $current_season ];
						$performance_desc = esc_html__( 'In current season', 'alchemists' );
					}
				} else {
					if ( isset( $data[-1] )) {
						$data = $data[-1];
					}
				}

				// Player Image (Alt)
				$player_image_head  = get_field('heading_player_photo');
				$player_image_size  = 'alchemists_thumbnail-player-sm';
				if( $player_image_head ) {
					$image_url = wp_get_attachment_image( $player_image_head, $player_image_size );
				} else {
					$image_url = '<img src="' . get_theme_file_uri( '/assets/images/player-single-placeholder-189x198.png' ) . '" loading="lazy" alt="" />';
				}

				// Player Team Logo
				$sp_current_teams = get_post_meta($id,'sp_current_team');
				$sp_current_team = '';
				if( !empty($sp_current_teams[0]) ) {
					$sp_current_team = $sp_current_teams[0];
				}

				// Player Name
				$title      = get_the_title( $player_id );
				$player_url = get_the_permalink( $player_id );

				// Player Number
				$player_number = get_post_meta( $player_id, 'sp_number', true );

				// Player Position(s)
				$positions = wp_get_post_terms( $player_id, 'sp_position');
				$position = false;
				if( $positions ) {
					$position = $positions[0]->name;
				}

				// Output
				include( locate_template( 'sportspress/single-team/featured-player/content-player-' . $sp_preset_name . '.php' ) );

			endwhile;

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();
		}
		?>

	</div>
	<?php endif; ?>

</div>
