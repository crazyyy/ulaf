<?php
/**
 * The template for displaying Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.3
 */

get_header();

if ( ! isset( $player_id ) ) {
	$player_id = get_the_ID();
}

$defaults = array(
	'show_number' => get_option( 'sportspress_player_show_number', 'no' ) == 'yes' ? true : false,
	'show_name' => get_option( 'sportspress_player_show_name', 'no' ) == 'yes' ? true : false,
	'show_nationality' => get_option( 'sportspress_player_show_nationality', 'yes' ) == 'yes' ? true : false,
	'show_positions' => get_option( 'sportspress_player_show_positions', 'yes' ) == 'yes' ? true : false,
	'show_current_teams' => get_option( 'sportspress_player_show_current_teams', 'yes' ) == 'yes' ? true : false,
	'show_past_teams' => get_option( 'sportspress_player_show_past_teams', 'yes' ) == 'yes' ? true : false,
	'show_leagues' => get_option( 'sportspress_player_show_leagues', 'no' ) == 'yes' ? true : false,
	'show_seasons' => get_option( 'sportspress_player_show_seasons', 'no' ) == 'yes' ? true : false,
	'show_nationality_flags' => get_option( 'sportspress_player_show_flags', 'yes' ) == 'yes' ? true : false,
	'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'show_age' => get_option( 'sportspress_player_show_age', 'yes' ) == 'yes' ? true : false,
	'show_player_birthday' => get_option( 'sportspress_player_show_birthday', 'no' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

// SportsPress Custom Text Strings
$sp_text_strings      = get_option( 'sportspress_text', array() );
$sp_text_age          = ! empty( $sp_text_strings['Age'] ) ? $sp_text_strings['Age'] : esc_html__( 'Age', 'alchemists' );
$sp_text_birthday     = ! empty( $sp_text_strings['Birthday'] ) ? $sp_text_strings['Birthday'] : esc_html__( 'Birthday', 'alchemists' );
$sp_text_team_current = ! empty( $sp_text_strings['Current Team'] ) ? $sp_text_strings['Current Team'] : esc_html__( 'Current Team', 'alchemists' );
$sp_text_teams_past   = ! empty( $sp_text_strings['Past Teams'] ) ? $sp_text_strings['Past Teams'] : esc_html__( 'Past Teams', 'alchemists' );
$sp_text_position     = ! empty( $sp_text_strings['Position'] ) ? $sp_text_strings['Position'] : esc_html__( 'Position', 'alchemists' );
$sp_text_nationality  = ! empty( $sp_text_strings['Nationality'] ) ? $sp_text_strings['Nationality'] : esc_html__( 'Nationality', 'alchemists' );

$current_player_page   = get_query_var('fpage');

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );
$page_heading_layout    = isset( $alchemists_data['alchemists__player-page-header-layout'] ) ? $alchemists_data['alchemists__player-page-header-layout'] : 'fullwidth';
$page_heading_overlay   = isset( $alchemists_data['alchemists__player-title-overlay-on'] ) ? $alchemists_data['alchemists__player-title-overlay-on'] : '';
$player_info_layout     = isset( $alchemists_data['alchemists__player-info-layout'] ) ? $alchemists_data['alchemists__player-info-layout'] : 'horizontal';
$player_info_layout_get = isset( $_GET['player_info_layout'] ) ? $_GET['player_info_layout'] : '';
$player_metrics_enable  = isset( $alchemists_data['alchemists__player-title-metrics'] ) ? $alchemists_data['alchemists__player-title-metrics'] : 0;
$player_metrics_custom  = isset( $alchemists_data['alchemists__player-title-metrics-custom'] ) ? $alchemists_data['alchemists__player-title-metrics-custom'] : array();
$team_logo              = isset( $alchemists_data['alchemists__player-team-logo'] ) ? $alchemists_data['alchemists__player-team-logo'] : 0;

$label_subtitle_on     = isset( $alchemists_data['alchemists__player-subpages-label--subtitle'] ) ? $alchemists_data['alchemists__player-subpages-label--subtitle'] : 1;
$label_subtitle        = isset( $alchemists_data['alchemists__player-subpages-label--subtitle-custom'] ) && ! empty( $alchemists_data['alchemists__player-subpages-label--subtitle-custom'] ) ? $alchemists_data['alchemists__player-subpages-label--subtitle-custom'] : esc_html__( 'Player', 'alchemists' );

$player_info_layout_class = 'horizontal';
if ( $player_info_layout === 'vertical' || $player_info_layout_get === 'vertical' ) {
	$player_info_layout_class = 'vertical';
}

// Player Header Advanced Stats (chart radar for Basketball and eSports, progress bars for Soccer)
$player_header_advanced_stats = get_field( 'player_page_heading_advanced_stats' );

$player_subpages       = isset( $alchemists_data['alchemists__player-subpages']['enabled'] ) ? $alchemists_data['alchemists__player-subpages']['enabled'] : array( 'stats' => esc_html__( 'Statistics', 'alchemists' ), 'bio' => esc_html__( 'Biography', 'alchemists' ), 'news' => esc_html__( 'Related News', 'alchemists' ), 'gallery' => esc_html__( 'Gallery', 'alchemists' ));

$overview_label        = isset( $alchemists_data['alchemists__player-subpages-label--overview'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-label--overview'] ) : esc_html__( 'Overview', 'alchemists' );
$stats_label           = isset( $alchemists_data['alchemists__player-subpages-label--stats'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-label--stats'] ) : esc_html__( 'Full Statistics', 'alchemists' );
$bio_label             = isset( $alchemists_data['alchemists__player-subpages-label--bio'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-label--bio'] ) : esc_html__( 'Biography', 'alchemists' );
$news_label            = isset( $alchemists_data['alchemists__player-subpages-label--news'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-label--news'] ) : esc_html__( 'Related News', 'alchemists' );
$gallery_label         = isset( $alchemists_data['alchemists__player-subpages-label--gallery'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-label--gallery'] ) : esc_html__( 'Gallery', 'alchemists' );

$overview_slug         = isset( $alchemists_data['alchemists__player-subpages-slug--overview'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-slug--overview'] ) : 'stats';
$stats_slug            = isset( $alchemists_data['alchemists__player-subpages-slug--stats'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-slug--stats'] ) : 'stats';
$bio_slug              = isset( $alchemists_data['alchemists__player-subpages-slug--bio'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-slug--bio'] ) : 'bio';
$news_slug             = isset( $alchemists_data['alchemists__player-subpages-slug--news'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-slug--news'] ) : 'news';
$gallery_slug          = isset( $alchemists_data['alchemists__player-subpages-slug--gallery'] ) ? esc_html( $alchemists_data['alchemists__player-subpages-slug--gallery'] ) : 'gallery';

if ( $page_heading_overlay == 0 ) {
	$page_heading_overlay = 'player-heading--no-bg';
} else {
	$page_heading_overlay = 'player-heading--has-bg';
}

$countries = SP()->countries->countries;

$player = new SP_Player( $player_id );

// Player Header Thumbnail
$player_image_head  = get_post_meta( $player_id, 'heading_player_photo', true );
$player_image_size  = 'alchemists_thumbnail-player';
if( $player_image_head ) {
	$player_image = wp_get_attachment_image( $player_image_head, $player_image_size );
} else {
	$player_image = '<img src="' . get_theme_file_uri( '/assets/images/player-single-370x400.png' ) . '" alt="" />';
}

// Get current teams
$current_teams = get_post_meta( $player_id, 'sp_current_team' );
$current_team_id = '';
if ( ! empty( $current_teams[0] ) ) {
	$current_team_id = $current_teams[0];
}

// get Team colors for player
$team_color_primary    = get_field( 'team_color_primary', $current_team_id );
$team_color_secondary  = get_field( 'team_color_secondary', $current_team_id );
$team_color_heading    = get_field( 'team_color_heading', $current_team_id );

// Player Metrics
// Get Player Metrics posts
$metrics = get_posts( array(
	'post_type'      => 'sp_metric',
	'posts_per_page' => 9999
));

// New array based on Player Statistics posts
$player_metrics_custom_array = array();
if ( $player_metrics_custom ) {
	foreach ( $player_metrics_custom as $player_metric_key => $player_metric_value) {
		$player_metrics_custom_array[ get_post_field( 'post_name', $player_metrics_custom[ $player_metric_key ] ) ] = get_post_field( 'post_title', $player_metrics_custom[ $player_metric_key ] );
	}
}

$player_metrics_data = (array)get_post_meta( $player_id, 'sp_metrics', true );

$metrics_array = array();
if( $metrics ){
	foreach( $metrics as $metric ){
		$metrics_array[ $metric->post_name ] = $metric->post_title;
	}
}

$player_metrics_default = array(
	'height',
	'weight'
);

$player_metrics = array();
if ( $player_metrics_enable ) {
	$player_metrics = $player_metrics_custom_array;
} else {
	$player_metrics = alchemists_sp_filter_array( $metrics_array, $player_metrics_default );
}


// Player Heading
if ( 'boxed' === $page_heading_layout ) {
	// Player - Boxed
	include( locate_template( 'sportspress/player/heading/player-heading-boxed.php' ) );

} elseif ( 'fullwidth' === $page_heading_layout ) {

	// Player - Fullwidth
	include( locate_template( 'sportspress/player/heading/player-heading-fullwidth.php' ) );

}


// Filter
// Don't show navigation if one subpage is selected (1s - placebo)
if ( sizeof( $player_subpages ) > 2 ) :

	$content_filter_classes = array( 'content-filter' );
	if ( 'boxed' === $page_heading_layout ) {
		array_push(
			$content_filter_classes,
			'content-filter--boxed',
			'content-filter--highlight-side',
			'content-filter--label-left',
			'mb-15'
		);
	}
	?>
	<!-- Player Pages Filter -->
	<nav class="<?php echo esc_attr( implode( ' ', $content_filter_classes ) ); ?>">
		<div class="container">
			<div class="content-filter__inner">
				<a href="#" class="content-filter__toggle"></a>
				<ul class="content-filter__list">

					<?php
					$i = 0;

					if ( $player_subpages ) :
						foreach ( $player_subpages as $key => $label ) :

							// skip if placebo
							if ( 'placebo' === $key ) {
								continue;
							}

							if ( 0 === $i ) :
								// For the first subpage don't add slug
								?>
								<li class="content-filter__item <?php if ( ! $current_player_page ) { echo 'content-filter__item--active'; }; ?>">
									<a href="<?php echo esc_url( get_permalink() ); ?>" class="content-filter__link">
										<?php if ( $label_subtitle_on ) : ?>
											<small><?php echo esc_html( $label_subtitle ); ?></small>
										<?php endif; ?>
										<?php echo esc_html( ${$key . '_label'} ); ?>
									</a>
								</li>
							<?php else : ?>
								<li class="content-filter__item <?php if ( $current_player_page == $key) { echo 'content-filter__item--active'; }; ?>">
									<a href="<?php echo esc_url( get_permalink() ); ?><?php echo strtolower( trim( ${$key . '_slug'} ) ); ?>/" class="content-filter__link">
										<?php if ( $label_subtitle_on ) : ?>
											<small><?php echo esc_html( $label_subtitle ); ?></small>
										<?php endif; ?>
										<?php echo esc_html( ${$key . '_label'} ); ?>
									</a>
								</li>
							<?php
							endif;

							$i++;

						endforeach;
					endif; ?>

				</ul>
			</div>
		</div>
	</nav>
	<!-- Player Pages Filter / End -->
<?php endif; ?>

<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content <?php echo esc_attr( 'boxed' === $page_heading_layout ? 'pt-0' : ''); ?>" id="content">
	<?php
	$j = 0;

	if ( $player_subpages ) :
		foreach ( $player_subpages as $key => $label ) :

			// skip if placebo
			if ( 'placebo' === $key ) {
				continue;
			}

			if ( ! $current_player_page && 0 === $j ) {
				get_template_part( 'sportspress/single-player/content', $key );
			} elseif ( $current_player_page == $key ) {
				get_template_part( 'sportspress/single-player/content', $key );
			}

			$j++;
		endforeach;
	endif;
	?>
</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php
get_footer();
