<?php
/**
 * The template for displaying Single Player Header - Boxed
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

// Custom Page Heading Options
$page_heading_customize      = get_field( 'player_page_heading_customize' );
$page_heading_style          = array();
$page_heading_styles_output  = array();

$custom_css_output = '';

// duotone
$duotone_effect = array( 'effect-duotone' );
if ( empty( $team_color_primary ) ) {
	$duotone_effect[] = 'effect-duotone--blue';
} else {
	$custom_css_output .= '.single-sp_player .effect-duotone .effect-duotone__layer-inner {';
		$custom_css_output .= 'background-color: ' . $team_color_primary . ';';
	$custom_css_output .= '}';
}

if ( $page_heading_customize ) {
	// Page Heading Background Image
	$page_heading_custom_background_img = get_field( 'player_page_heading_custom_background_img' );

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
	$page_heading_add_overlay_on         = get_field( 'player_page_heading_add_overlay_on' );
	if ( empty( $page_heading_add_overlay_on ) ) {
		unset( $duotone_effect[0] ); // removed effect-duotone class
	}

	$page_heading_custom_overlay_color   = get_field( 'player_page_heading_custom_overlay_color') ? get_field('player_page_heading_custom_overlay_color') : 'transparent';
	$page_heading_custom_overlay_opacity = get_field( 'player_page_heading_custom_overlay_opacity' );

	if ( $page_heading_add_overlay_on ) {
		$custom_css_output .= '.single-sp_player .effect-duotone .effect-duotone__layer-inner {';
			$custom_css_output .= 'background-color: ' . $page_heading_custom_overlay_color . ';';
			$custom_css_output .= 'opacity: ' . $page_heading_custom_overlay_opacity / 100 . ';';
		$custom_css_output .= '}';
	}
}

// combine all custom inline properties into one string
if ( $page_heading_style ) {
	$page_heading_styles_output[] = 'style="' . implode( ' ', $page_heading_style ). '"';
}

// output custom CSS only on this page
if ( ! empty( $custom_css_output ) ) {
	echo '<style>';
		echo wp_strip_all_tags( $custom_css_output );
	echo '</style>';
}

// Metrics
$player_metrics = array(
	'fname' => esc_html__( 'Name', 'alchemists' ),
	'lname' => esc_html__( 'Surname', 'alchemists' ),
);

// Social Links
$player_facebook = get_field( 'player_social_facebook' );
$player_twitter  = get_field( 'player_social_twitter' );
$player_twitch   = get_field( 'player_social_twitch' );


// Position
$player_terms = get_the_terms( $id, 'sp_position' );
$player_position_id = 0;
$player_position_name = '';
if ( is_array( $player_terms ) && ! empty( $player_terms ) ) {
	$player_position_id   = $player_terms[0]->term_id;
	$player_position_name = $player_terms[0]->name;
}
$player_position_icon = get_term_meta( $player_position_id, 'character_icon', true );
?>

<div class="container">
	<div class="section pb-0">
		<!-- Single Player -->
		<div class="team-roster team-roster--card team-roster--boxed mb-0 pb-0">

			<!-- Player -->
			<div class="team-roster__item card card--no-paddings">
				<div class="card__content">
					<div class="team-roster__content-wrapper">

						<!-- Player Photo -->
						<figure class="team-roster__player-img">
							<div class="team-roster__player-shape <?php echo esc_attr( implode( ' ', $duotone_effect ) ); ?> team-roster__player-shape--default">
								<div class="team-roster__player-shape-inner" <?php echo implode( ' ', $page_heading_styles_output ); ?>></div>
							</div>
							<?php
							if ( $player_image_head ) {
								echo wp_get_attachment_image( $player_image_head, 'alchemists_thumbnail-player-lg-fit' );
							} else {
								echo '<img src="' . get_theme_file_uri( '/assets/images/player-single-470x580.png' ) . '" alt="" />';
							}
							?>
						</figure>
						<!-- Player Photo / End-->

						<!-- Player Content -->
						<div class="team-roster__content">

							<!-- Player Details -->
							<div class="team-roster__player-details">
								<div class="team-roster__player-info">
									<?php if ( $show_name ) : ?>
									<h5 class="team-roster__player-realname">
									<?php
									foreach ( $player_metrics as $player_metric_key => $player_metric_value ) :
										if ( ! empty( $player_metrics_data[ $player_metric_key ] ) ) :
											echo esc_html( $player_metrics_data[ $player_metric_key ] ) . ' ';
										endif;
									endforeach;
									?>
									</h5>
									<?php endif; ?>

									<h1 class="team-roster__player-nickname"><?php echo wp_kses_post( $player->post->post_title ); ?></h1>

								</div>
							</div>
							<!-- Player Details / End -->

							<?php if ( has_excerpt() ) : ?>
							<!-- Player Excerpt -->
							<div class="team-roster__player-excerpt">
								<?php the_excerpt(); ?>
							</div>
							<!-- Player Excerpt / End -->
							<?php endif; ?>

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
										<div class="team-roster__player-metrics-item-value"><?php echo esc_html( alchemists_get_age( get_the_date( 'm-d-Y', $player_id ), true ) ); ?></div>
									</div>
									<?php endif; ?>

								</div>

								<?php
								// Social Networks
								if ( have_rows( 'player_social_networks' ) ) :
								?>
								<ul class="team-roster__player-social social-links social-links--circle-filled">

									<?php while ( have_rows( 'player_social_networks' ) ) : the_row(); ?>
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
									'data'                 => $player->data(0),
									'id'                   => $player_id,
									'team_color_primary'   => $team_color_primary,
									'team_color_secondary' => $team_color_secondary,
								) );

								// Player Statistics - Progress Bars
								if ( $player_header_advanced_stats || is_null( $player_header_advanced_stats ) ) :
									sp_get_template( 'player-statistics-bars-boxed.php', array(
										'data' => $player->data(0),
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
										<use xlink:href="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/esports/icons-esports.svg#' . esc_attr( $player_position_icon ); ?>"/>
									</svg>
								</div>
								<?php endif; ?>
								<div class="team-roster__meta-label"><?php echo esc_html( $player_position_name ); ?></div>
							</div>
							<?php endif; ?>

						</aside>
						<!-- Player Meta Info / End -->

					</div>
				</div>
			</div>
			<!-- Player / End -->

			<?php
			// Player Statistics - Cards
			sp_get_template( 'player-statistics-cards-boxed.php', array(
				'data'                 => $player->data(0),
				'team_color_primary'   => $team_color_primary,
				'team_color_secondary' => $team_color_secondary,
			) );
			?>

		</div>
		<!-- Single Player / End -->
	</div>
</div>
