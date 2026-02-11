<?php
/**
 * Player Slider Thumbnail
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id'             => null,
	'link_posts'     => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

$identifier = uniqid( 'team-roster__item-' );

// Custom Page Heading Options
$page_heading_customize      = get_field( 'player_page_heading_customize', $id );
$page_heading_style          = array();
$page_heading_styles_output  = '';

$custom_css_output = '';

// Get current teams
$current_teams = get_post_meta( $id, 'sp_current_team' );
$current_team_id = '';
if ( ! empty( $current_teams[0] ) ) {
	$current_team_id = $current_teams[0];
}

// get Team colors for player
$team_color_primary    = get_field( 'team_color_primary', $current_team_id );
$team_color_secondary  = get_field( 'team_color_secondary', $current_team_id );

// duotone
$duotone_effect = array( 'effect-duotone' );
if ( empty( $team_color_primary ) ) {
	$duotone_effect[] = 'effect-duotone--blue';
} else {
	$custom_css_output .= '#' . $identifier . ' .effect-duotone .effect-duotone__layer-inner {';
		$custom_css_output .= 'background-color: ' . $team_color_primary . ';';
	$custom_css_output .= '}';
}

if ( $page_heading_customize ) {
	// Page Heading Background Image
	$page_heading_custom_background_img = get_field( 'player_page_heading_custom_background_img', $id );

	if ( $page_heading_custom_background_img ) {
		// if background image selected display it
		$page_heading_style[] = 'background-image: url(' . $page_heading_custom_background_img . ');';
	} else {
		// if not, remove the default one
		$page_heading_style[] = 'background-image: none;';
	}

	// Page Heading Background Color
	$page_heading_custom_background_color = get_field( 'player_page_heading_custom_background_color' );
	if ( $page_heading_custom_background_color ) {
		$page_heading_style[] = 'background-color: ' . $page_heading_custom_background_color . ';';
	}

	// Overlay
	$page_heading_add_overlay_on         = get_field( 'player_page_heading_add_overlay_on', $id );
	if ( empty( $page_heading_add_overlay_on ) ) {
		unset( $duotone_effect[0] ); // removed effect-duotone class
	}

	$page_heading_custom_overlay_color   = get_field( 'player_page_heading_custom_overlay_color', $id ) ? get_field( 'player_page_heading_custom_overlay_color', $id ) : 'transparent';
	$page_heading_custom_overlay_opacity = get_field( 'player_page_heading_custom_overlay_opacity', $id );

	if ( $page_heading_add_overlay_on ) {
		$custom_css_output .= '#' . $identifier . ' .effect-duotone .effect-duotone__layer-inner {';
			$custom_css_output .= 'background-color: ' . $page_heading_custom_overlay_color . ';';
			$custom_css_output .= 'opacity: ' . $page_heading_custom_overlay_opacity / 100 . ';';
		$custom_css_output .= '}';
	}
}

// combine all custom inline properties into one string
if ( $page_heading_style ) {
	$page_heading_styles_output = implode( ' ', $page_heading_style );
}

$player = new SP_Player( $id );
$player_data = $player->data(0);

unset( $player_data[0] );

if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $player_data[ $current_season ] ) ) {
		$player_data = $player_data[ $current_season ];
	}
} else {
	if ( isset( $player_data[-1] )) {
		$player_data = $player_data[-1];
	}
}

// Player Header Advanced Stats (chart radar for Basketball and eSports, progress bars for Soccer)
$player_header_advanced_stats = get_field( 'player_page_heading_advanced_stats', $id );

// Player Name
$player_metrics_data = (array)get_post_meta( $id, 'sp_metrics', true );
$player_fname = isset( $player_metrics_data['fname'] ) ? $player_metrics_data['fname'] : ''; // get first name based on player metrics
$player_lname = isset( $player_metrics_data['lname'] ) ? $player_metrics_data['lname'] : ''; // get last name based on player metrics

// Position (Character)
$player_terms = get_the_terms( $id, 'sp_position' );
$player_position_id = 0;
$player_position_name = '';
if ( is_array( $player_terms ) && ! empty( $player_terms ) ) {
	$player_position_id   = $player_terms[0]->term_id;
	$player_position_name = $player_terms[0]->name;
}
$player_position_icon = get_term_meta( $player_position_id, 'character_icon', true );

// echo '<pre style="background-color: #fff;">' . var_export($id, true ) . '</pre>';

// Player Background Image
if ( has_post_thumbnail( $id ) ) {
	$player_thumbnail = 'style="background-image:url('. get_the_post_thumbnail_url( $id, 'alchemists_thumbnail-player-lg-fit' ) .')"';
} else {
	$player_thumbnail = '';
}

// Player Image (Alt)
$player_image_head  = get_post_meta( $id, 'heading_player_photo', true );
$player_image_size  = 'alchemists_thumbnail-player-lg-fit';
if( $player_image_head ) {
	$image_url = wp_get_attachment_image( $player_image_head, $player_image_size );
} else {
	$image_url = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-470x578.png' ) . '" alt="" />';
}

// Player Excerpt
$player_excerpt = get_the_excerpt( $id );

// Player Bars
$shpercent   = isset( $player_data['shpercent'] ) ? $player_data['shpercent'] : '';
$passpercent = isset( $player_data['passpercent'] ) ? $player_data['passpercent'] : '';
$performance = isset( $player_data['perf'] ) ? $player_data['perf'] : '';

// Player Aside Stats
$goals   = isset( $player_data['goals'] ) ? $player_data['goals'] : esc_html__( 'n/a', 'alchemists' );
$gmp     = isset( $player_data['appearances'] ) ? $player_data['appearances'] : esc_html__( 'n/a', 'alchemists' );
$assists = isset( $player_data['assists'] ) ? $player_data['assists'] : esc_html__( 'n/a', 'alchemists' );
$drb     = isset( $player_data['drb'] ) ? $player_data['drb'] : esc_html__( 'n/a', 'alchemists' );
?>

<div id="<?php echo esc_attr( $identifier ); ?>" class="team-roster__item card card--no-paddings mb-0">
	<div class="card__content">
		<div class="team-roster__content-wrapper">

			<!-- Player Photo -->
			<figure class="team-roster__player-img">
				<div class="team-roster__player-shape <?php echo esc_attr( implode( ' ', $duotone_effect ) ); ?> team-roster__player-shape--default">
					<div class="team-roster__player-shape-inner" <?php if ( ! empty( $page_heading_styles_output ) ) { echo 'style="' . esc_attr( $page_heading_styles_output ) . '"'; } ?>></div>
				</div>
				<?php echo wp_kses_post( $image_url ); ?>
			</figure>
			<!-- Player Photo / End-->

			<!-- Player Content -->
			<div class="team-roster__content">

				<!-- Player Details -->
				<div class="team-roster__player-details">
					<div class="team-roster__player-info">
						<?php if ( ! empty( $player_fname ) || ! empty( $player_lname ) ) : ?>
							<h5 class="team-roster__player-realname"><?php echo esc_html( $player_fname ) . ' ' . esc_html( $player_lname ); ?></h5>
							<h3 class="team-roster__player-nickname"><?php echo esc_html( $title ); ?></h3>
						<?php endif; ?>
					</div>
				</div>
				<!-- Player Details / End -->

				<?php if ( has_excerpt( $id ) ) : ?>
					<!-- Player Excerpt -->
					<div class="team-roster__player-excerpt">
						<?php echo get_the_excerpt( $id ); ?>
					</div>
				<?php endif; ?>
				<!-- Player Excerpt / End -->

				<!-- Player Details - Common -->
				<div class="team-roster__player-details-common">
					<div class="team-roster__player-metrics">
						<?php
						if ( $show_nationality ):
							echo '<div class="team-roster__player-metrics-item team-roster__player-metrics-item--nationality">';
							echo '<h6 class="team-roster__player-metrics-item-label">' . $sp_text_nationality . '</h6>';

							$nationalities = $player->nationalities();
							if ( $nationalities && is_array( $nationalities ) ) {
								$values = array();
								foreach ( $nationalities as $nationality ):
									$country_name = sp_array_value( $countries, $nationality, null );
									$values[] = $country_name ? ( $show_nationality_flags ? '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" class="player-info-details__flag" alt="' . $nationality . '"> ' : '' ) . $country_name : '&mdash;';
								endforeach;
								$country_names_string = implode( ', ', $values );
								echo '<div class="team-roster__player-metrics-item-value">' . $country_names_string . '</div>';
							} else {
								echo '<div class="team-roster__player-metrics-item-value">' . esc_html__( 'n/a', 'alchemists' ) . '</div>';
							}

							echo '</div>';
						endif;
						?>

						<?php if ( $show_age ) : ?>
							<div class="team-roster__player-metrics-item">
								<h6 class="team-roster__player-metrics-item-label"><?php echo esc_html( $sp_text_age ); ?></h6>
								<div class="team-roster__player-metrics-item-value"><?php echo esc_html( alchemists_get_age( get_the_date( 'm-d-Y', $id ), true ) ); ?></div>
							</div>
						<?php endif; ?>

					</div>

					<?php if ( have_rows( 'player_social_networks', $id ) ) : ?>
						<ul class="team-roster__player-social social-links social-links--circle-filled">
							<?php while ( have_rows( 'player_social_networks', $id ) ) : the_row(); ?>
								<li class="social-links__item">
									<a href="<?php echo esc_url( the_sub_field( 'player_social_networks__link' ) ); ?>" class="social-links__link social-link-url" target="_blank"></a>
								</li>
							<?php endwhile; ?>
						</ul>
					<?php endif; ?>

				</div>
				<!-- Player Details - Common / End -->

				<!-- Player Stats -->
				<div class="team-roster__player-stats">

				<?php
				// Player Statistics - Circular Bars
				sp_get_template( 'player-statistics-circular-boxed.php', array(
					'data'                 => $player_data,
					'id'                   => $id,
					'team_color_primary'   => $team_color_primary,
					'team_color_secondary' => $team_color_secondary,
				) );

				// Player Statistics - Progress Bars
				if ( $player_header_advanced_stats || is_null( $player_header_advanced_stats ) ) :
					sp_get_template( 'player-statistics-bars-boxed.php', array(
						'data' => $player_data,
						'id' => $id,
						'team_color_primary'   => $team_color_primary,
						'team_color_secondary' => $team_color_secondary,
					) );
				endif;
				?>

				</div>
				<!-- Player Stats / End -->

			</div>
			<!-- Player Content / End -->

			<!-- Player Meta Info -->
			<aside class="team-roster__meta">

				<?php if ( $show_positions ) : ?>
					<div class="team-roster__meta-item">
						<?php if ( alchemists_sp_preset( 'esports' ) && $player_position_icon ) : ?>
							<div class="team-roster__meta-icon">
								<svg role="img" class="df-icon df-icon--<?php echo esc_attr( $player_position_icon ); ?>">
									<use xlink:href="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/esports/icons-esports.svg#' . esc_attr( $player_position_icon )	; ?>"/>
								</svg>
							</div>
						<?php endif; ?>
						<div class="team-roster__meta-label"><?php echo esc_html( $player_position_name ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( $link_posts ) : ?>
					<div class="team-roster__meta-item">
						<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
							<div class="team-roster__meta-icon team-roster__meta-icon--more">
								<i class="fa fa-ellipsis-h"></i>
							</div>
							<div class="team-roster__meta-label"><?php esc_html_e( 'More Info', 'alchemists' ); ?></div>
						</a>
					</div>
				<?php endif; ?>

			</aside>
			<!-- Player Meta Info / End -->

		</div>
	</div>

	<?php
	// output custom CSS only on this page
	if ( ! empty( $custom_css_output ) ) {
		echo '<style>';
			echo wp_strip_all_tags( $custom_css_output );
		echo '</style>';
	}
	?>
</div>
