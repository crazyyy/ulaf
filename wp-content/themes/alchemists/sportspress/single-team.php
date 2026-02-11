<?php
/**
 * The template for displaying Single Team
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 */

get_header();

$current_team_page     = get_query_var( 'teampage' );

$alchemists_data = get_option( 'alchemists_data' );
$team_nav_type         = isset( $alchemists_data['alchemists__team-nav-type'] ) ? $alchemists_data['alchemists__team-nav-type'] : 'fullwidth';
$page_heading_overlay  = isset( $alchemists_data['alchemists__opt-page-title-overlay-on'] ) ? $alchemists_data['alchemists__opt-page-title-overlay-on'] : '';
$breadcrumbs           = isset( $alchemists_data['alchemists__opt-page-title-breadcrumbs'] ) ? $alchemists_data['alchemists__opt-page-title-breadcrumbs'] : '';
$page_title_on         = isset( $alchemists_data['alchemists__opt-page-title-display'] ) ? $alchemists_data['alchemists__opt-page-title-display'] : 1;
$page_title_tag        = isset( $alchemists_data['alchemists__opt-page-title-tag'] ) ? $alchemists_data['alchemists__opt-page-title-tag'] : 'h1';
$page_title_layout     = isset( $alchemists_data['alchemists__page-title-layout'] ) ? $alchemists_data['alchemists__page-title-layout'] : 1;
$page_title_duotone    = isset( $alchemists_data['alchemists__opt-page-title-duotone'] ) ? $alchemists_data['alchemists__opt-page-title-duotone'] : 1;
$page_duotone_color    = isset( $alchemists_data['alchemists__opt-page-title-duotone-color'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color'] : 'primary';
$page_duotone_color1   = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-1'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-1'] : '';
$page_duotone_color2   = isset( $alchemists_data['alchemists__opt-page-title-duotone-color-2'] ) ? $alchemists_data['alchemists__opt-page-title-duotone-color-2'] : '';

$label_subtitle_on     = isset( $alchemists_data['alchemists__team-subpages-label--subtitle'] ) ? $alchemists_data['alchemists__team-subpages-label--subtitle'] : 1;
$label_subtitle        = isset( $alchemists_data['alchemists__team-subpages-label--subtitle-custom'] ) && ! empty( $alchemists_data['alchemists__team-subpages-label--subtitle-custom'] ) ? $alchemists_data['alchemists__team-subpages-label--subtitle-custom'] : esc_html__( 'The Team', 'alchemists' );

if ( $page_heading_overlay == 0 ) {
	$page_heading_overlay = 'page-heading--no-bg';
} else {
	$page_heading_overlay = 'page-heading--has-bg';
}

$team_subpages         = isset( $alchemists_data['alchemists__team-subpages']['enabled'] ) ? $alchemists_data['alchemists__team-subpages']['enabled'] : array( 'roster' => esc_html__( 'Roster', 'alchemists' ), 'standings' => esc_html__( 'Standings', 'alchemists' ), 'results' => esc_html__( 'Latest Results', 'alchemists' ), 'schedule' => esc_html__( 'Schedule', 'alchemists' ), 'gallery' => esc_html__( 'Gallery', 'alchemists' ));

$overview_label        = isset( $alchemists_data['alchemists__team-subpages-label--overview'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-label--overview'] ) : esc_html__( 'Overview', 'alchemists' );
$roster_label          = isset( $alchemists_data['alchemists__team-subpages-label--roster'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-label--roster'] ) : esc_html__( 'Roster', 'alchemists' );
$standings_label       = isset( $alchemists_data['alchemists__team-subpages-label--standings'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-label--standings'] ) : esc_html__( 'Standings', 'alchemists' );
$results_label         = isset( $alchemists_data['alchemists__team-subpages-label--results'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-label--results'] ) : esc_html__( 'Latest Results', 'alchemists' );
$schedule_label        = isset( $alchemists_data['alchemists__team-subpages-label--schedule'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-label--schedule'] ) : esc_html__( 'Schedule', 'alchemists' );
$gallery_label         = isset( $alchemists_data['alchemists__team-subpages-label--gallery'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-label--gallery'] ) : esc_html__( 'Gallery', 'alchemists' );


$overview_slug         = isset( $alchemists_data['alchemists__team-subpages-slug--overview'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-slug--overview'] ) : 'overview';
$roster_slug           = isset( $alchemists_data['alchemists__team-subpages-slug--roster'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-slug--roster'] ) : 'roster';
$standings_slug        = isset( $alchemists_data['alchemists__team-subpages-slug--standings'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-slug--standings'] ) : 'standings';
$results_slug          = isset( $alchemists_data['alchemists__team-subpages-slug--results'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-slug--results'] ) : 'results';
$schedule_slug         = isset( $alchemists_data['alchemists__team-subpages-slug--schedule'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-slug--schedule'] ) : 'schedule';
$gallery_slug          = isset( $alchemists_data['alchemists__team-subpages-slug--gallery'] ) ? esc_html( $alchemists_data['alchemists__team-subpages-slug--gallery'] ) : 'gallery';

$content_classes = array();

// Page Options
$page_heading                = get_field('page_heading');
$page_content_top_padding    = get_field('page_content_top_padding');
$page_content_bottom_padding = get_field('page_content_bottom_padding');

// Custom Page Heading Options
$page_heading_customize      = get_field('page_heading_customize');
$page_heading_style          = array();
$page_heading_styles_output  = array();

if ( $page_heading_customize ) {
	// Page Heading Background Image
	$page_heading_custom_background_img = get_field('page_heading_custom_background_img');

	if ( $page_heading_custom_background_img ) {
		// if background image selected display it
		$page_heading_style[] = 'background-image: url(' . $page_heading_custom_background_img . ');';
	} else {
		// if not, remove the default one
		$page_heading_style[] = 'background-image: none;';
	}

	// Page Heading Background Color
	$page_heading_custom_background_color = get_field('page_heading_custom_background_color');
	if ( $page_heading_custom_background_color ) {
		$page_heading_style[] = 'background-color: ' . $page_heading_custom_background_color . ';';
	}

	// Overlay
	$page_heading_add_overlay_on = get_field('page_heading_add_overlay_on');
	// hide pseudoelement if overlay disabled
	if ( empty( $page_heading_add_overlay_on ) ) {
		$page_heading_overlay = 'page-heading--no-bg';
	}

	$page_heading_custom_overlay_color = get_field('page_heading_custom_overlay_color') ? get_field('page_heading_custom_overlay_color') : 'transparent';
	$page_heading_custom_overlay_opacity = get_field( 'page_heading_custom_overlay_opacity' );
	$page_heading_remove_overlay_pattern = get_field( 'page_heading_remove_overlay_pattern' );

	if ( $page_heading_add_overlay_on ) {
		echo '<style>';
			echo '.page-heading::before {';
				echo 'background-color: ' . $page_heading_custom_overlay_color . ';';
				echo 'opacity: ' . $page_heading_custom_overlay_opacity / 100 . ';';
				if ( $page_heading_remove_overlay_pattern ) {
					echo 'background-image: none;';
				}
			echo '}';
		echo '</style>';
	}
}

// combine all custom inline properties into one string
if ( $page_heading_style ) {
	$page_heading_styles_output[] = 'style="' . implode( ' ', $page_heading_style ). '"';
}

// Page Content Options
$page_content_top_padding_class = '';
if ( $page_content_top_padding == 'none' ) {
	$content_classes[] = 'pt-0';
}

$page_content_bottom_padding_class = '';
if ( $page_content_bottom_padding == 'none' ) {
	$content_classes[] = 'pb-0';
}

if ( ! isset( $id ) ) {
	$id = get_the_ID();
}

$data = array();

$terms = get_the_terms( $id, 'sp_league' );

// Page Heading
if ( $page_heading == 'page_hero' ) {

	get_template_part( 'template-parts/page-hero-unit');

} elseif ( $page_heading == 'page_hero_posts_slider' ) {

	get_template_part( 'template-parts/page-hero-posts-slider');

} elseif ( $page_heading == 'page_default' || !$page_heading ) {

	include( locate_template( 'template-parts/page-hero-title.php' ) );

}


// Don't show navigation if one subpage is selected (1s - placebo)
if ( sizeof( $team_subpages ) > 2 && ! post_password_required() ) :

	$content_filter_classes = array( 'content-filter' );
	if ( 'boxed' === $team_nav_type ) {
		array_push(
			$content_filter_classes,
			'content-filter--boxed',
			'content-filter--highlight-side',
			'content-filter--label-left'
		);
	}
?>
<!-- Team Pages Filter -->
<nav class="<?php echo esc_attr( implode( ' ', $content_filter_classes ) ); ?>">
	<div class="container">
		<div class="content-filter__inner">
			<a href="#" class="content-filter__toggle"></a>
			<ul class="content-filter__list">

				<?php
				$i = 0;

				if ( $team_subpages ) :
					foreach ( $team_subpages as $key => $label ) :

						// skip if placebo
						if ( 'placebo' === $key ) {
							continue;
						}

						if ( 0 === $i ) :
							// For the first subpage don't add slug
							?>
							<li class="content-filter__item <?php if ( ! $current_team_page ) { echo 'content-filter__item--active'; }; ?>">
								<a href="<?php echo esc_url( get_permalink() ); ?>" class="content-filter__link">
									<?php if ( $label_subtitle_on ) : ?>
										<small><?php echo esc_html( $label_subtitle ); ?></small>
									<?php endif; ?>
									<?php echo esc_html( ${$key . '_label'} ); ?>
								</a>
							</li>
						<?php else : ?>
							<li class="content-filter__item <?php if ( $current_team_page == $key) { echo 'content-filter__item--active'; }; ?>">
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
				endif;
				?>
			</ul>
		</div>
	</div>
</nav>
<!-- Team Pages Filter / End -->
<?php endif; ?>


<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content <?php echo implode( ' ', $content_classes ); ?>" id="content">
	<div class="container">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<?php

				if ( ! post_password_required() ) {
					$j = 0;

					if ( $team_subpages ) :
						foreach ( $team_subpages as $key => $label ) :

							// skip if placebo
							if ( 'placebo' === $key ) {
								continue;
							}

							if ( ! $current_team_page && 0 === $j ) {
								get_template_part( 'sportspress/single-team/content', $key );
							} elseif ( $current_team_page == $key ) {
								get_template_part( 'sportspress/single-team/content', $key );
							}

							$j++;
						endforeach;
					endif;

				} else {
					echo get_the_password_form();
				}
				?>

			</main>
		</div>
	</div>

</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php
get_footer();
