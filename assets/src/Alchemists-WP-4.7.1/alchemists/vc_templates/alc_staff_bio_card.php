<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.7.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $staff_id
 * @var $link
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Staff_Bio_Card
 */

$title = $staff_id = $link = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'card card--clean alc-staff';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

if ( ! isset( $staff_id ) || $staff_id == 'default' ) {
	$staff_id = get_the_ID();
}

$defaults = array(
	'show_photo' => get_option( 'sportspress_staff_show_photo', 'yes' ) === 'yes' ? true : false,
	'show_nationality' => get_option( 'sportspress_staff_show_nationality', 'yes' ) == 'yes' ? true : false,
	'show_current_teams' => get_option( 'sportspress_staff_show_current_teams', 'yes' ) == 'yes' ? true : false,
	'show_past_teams' => get_option( 'sportspress_staff_show_past_teams', 'yes' ) == 'yes' ? true : false,
	'show_nationality_flags' => get_option( 'sportspress_staff_show_flags', 'yes' ) == 'yes' ? true : false,
	'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'abbreviate_teams' => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes' ? true : false,
	'link_phone' => get_option( 'sportspress_staff_link_phone', 'yes' ) == 'yes' ? true : false,
	'link_email' => get_option( 'sportspress_staff_link_email', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

// Get Staff featured image size
$staff_thumbnail_size = 'alchemists_thumbnail-player-lg';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$staff_thumbnail_size = $img_size;
	}
}

$countries = SP()->countries->countries;

$staff = new SP_Staff( $staff_id );

$nationalities = $staff->nationalities();
$current_teams = $staff->current_teams();
$past_teams = $staff->past_teams();
$name = $staff->post->post_title;
$roles = $staff->get_roles();
$excerpt = $staff->post->post_excerpt;
$phone = $staff->phone;
$email = $staff->email;

$data = array();

// Nationality
if ( $show_nationality && $nationalities && is_array( $nationalities ) ):
	$values = array();
	foreach ( $nationalities as $nationality ):
		if ( 2 == strlen( $nationality ) ):
			$legacy = SP()->countries->legacy;
			$nationality = strtolower( $nationality );
			$nationality = sp_array_value( $legacy, $nationality, null );
		endif;
		$country_name = sp_array_value( $countries, $nationality, null );
		$values[] = $country_name ? ( $show_nationality_flags ? '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" alt="' . $nationality . '"> ' : '' ) . $country_name : '&mdash;';
	endforeach;
	$data[ esc_html__( 'Nationality', 'alchemists' ) ] = implode( '<br>', $values );
endif;

// Current Teams
if ( $show_current_teams && $current_teams ):
	$teams = array();
	foreach ( $current_teams as $team ):
		$team_name = sp_get_team_name( $team, $abbreviate_teams );
		if ( $link_teams ) $team_name = '<a href="' . get_post_permalink( $team ) . '">' . $team_name . '</a>';
		$teams[] = $team_name;
	endforeach;
	$data[ esc_html__( 'Current Team', 'alchemists' ) ] = implode( ', ', $teams );
endif;


// Past Teams
if ( $show_past_teams && $past_teams ):
	$teams = array();
	foreach ( $past_teams as $team ):
		$team_name = sp_get_team_name( $team, $abbreviate_teams );
		if ( $link_teams ) $team_name = '<a href="' . get_post_permalink( $team ) . '">' . $team_name . '</a>';
		$teams[] = $team_name;
	endforeach;
	$data[ esc_html__( 'Past Teams', 'alchemists' ) ] = implode( ', ', $teams );
endif;

$data = apply_filters( 'sportspress_staff_details', $data, $staff_id );

if ( empty( $data ) ) {
	return;
}

// Phone
if ( $phone !== '' ):
	if ( $link_phone ) $phone = '<a href="tel:' . $phone . '">' . $phone . '</a>';
	$data[ esc_html__( 'Phone', 'alchemists' ) ] = $phone;
endif;

// Email
if ( $email !== '' ):
	if ( $link_email ) $email = '<a href="mailto:' . $email . '">' . $email . '</a>';
	$data[ esc_html__( 'Email', 'alchemists' ) ] = $email;
endif;

//parse link
$link = ( '||' === $link ) ? '' : $link;
$link = vc_build_link( $link );
$use_link = false;
if ( strlen( $link['url'] ) > 0 ) {
	$use_link = true;
	$a_href = $link['url'];
	$a_title = $link['title'];
	$a_target = $link['target'];
	$a_rel = $link['rel'];
}

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

if ( $use_link ) {
	$attributes[] = 'href="' . trim( $a_href ) . '"';
	$attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
	if ( ! empty( $a_target ) ) {
		$attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
	}
	if ( ! empty( $a_rel ) ) {
		$attributes[] = 'rel="' . esc_attr( trim( $a_rel ) ) . '"';
	}
}

$attributes = implode( ' ', $attributes );
?>


<!-- Staff Bio Card -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) { ?>
		<div class="card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>

			<?php if ( $use_link ) {
				$card_header_btn_classes = array(
					'btn',
					'btn-xs',
					'btn-default',
					'card-header__button',
				);
				// make it outline
				if ( alchemists_sp_preset( 'basketball' ) || alchemists_sp_preset( 'soccer' ) ) {
					$card_header_btn_classes[] = 'btn-outline';
				}
				echo '<a class="' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '" ' . $attributes . '>' . $a_title . '</a>';
			} ?>
		</div>
	<?php } ?>

	<div class="card__content">

		<div class="card">
			<div class="card__content">

				<div class="row">
					<?php if ( $show_photo && has_post_thumbnail( $staff_id ) ): ?>
						<div class="col-lg-6">
							<div class="alc-staff__photo">
								<?php echo get_the_post_thumbnail( $staff_id, $staff_thumbnail_size ); ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="col-lg-6">
						<div class="alc-staff-inner">

							<header class="alc-staff__header">
								<h1 class="alc-staff__header-name">
									<?php echo wp_kses_post( $name ); ?>
								</h1>
								<?php if ( $roles ) : ?>
								<span class="alc-staff__header-role">
									<?php
									foreach ( $roles as $role ) {
										echo '<span class="alc-staff__header-role-item">' . esc_html( $role->name ) . '</span>';
									}
									?>
								</span>
								<?php endif; ?>
							</header>

							<?php if ( $excerpt ) : ?>
							<!-- Excerpt -->
							<div class="alc-staff-excerpt">
								<?php echo wp_kses_post( $excerpt ); ?>
							</div>
							<!-- Excerpt / End -->
							<?php endif; ?>

							<!-- Details -->
							<dl class="alc-staff-details">
								<?php foreach ( $data as $label => $value ) : ?>
									<dt class="alc-staff-details__label"><?php echo esc_html( $label ); ?></dt>
									<dd class="alc-staff-details__value"><?php echo wp_kses_post( $value ); ?></dd>
								<?php endforeach; ?>
							</dl>
							<!-- Details / End -->

						</div>

					</div>
				</div>
			</div>
		</div>


	</div>

</div>
<!-- Staff Bio Card / End -->
