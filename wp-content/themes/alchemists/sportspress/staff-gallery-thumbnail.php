<?php
/**
 * Staff Gallery Thumbnail
 *
 * @author 		ThemeBoy
 * @package 	SportsPress_Staff_Directories
 * @version     2.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'row' => array(),
	'icontag' => 'figure',
	'captiontag' => 'figcaption',
	'caption' => null,
	'link_posts' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
	'link_phone' => get_option( 'sportspress_staff_link_phone', 'yes' ) == 'yes' ? true : false,
	'link_email' => get_option( 'sportspress_staff_link_email', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

$countries = SP()->countries->countries;

$staff = new SP_Staff( $id );

// Add staff role undder caption if available
$staff_role_name = '';
$roles = get_the_terms( $id, 'sp_role' );
if ( $roles && ! is_wp_error( $roles ) ) {
	$staff_role = array_shift( $roles );
	$staff_role_name = $staff_role->name;
}

$phone = $staff->phone;
$email = $staff->email;

$data = array();

if ( $phone !== '' ):
	if ( $link_phone ) $phone = '<a href="tel:' . $phone . '">' . $phone . '</a>';
	$data[ esc_html__( 'Phone:', 'alchemists' ) ] = $phone;
endif;

if ( $email !== '' ):
	if ( $link_email ) $email = '<a href="mailto:' . $email . '">' . $email . '</a>';
	$data[ esc_html__( 'Email:', 'alchemists' ) ] = $email;
endif;

// Add caption tag if has caption
if ( $captiontag && $caption ) {
	$caption = '<' . $captiontag . '>' . wptexturize( $caption ) . '</' . $captiontag . '>';
}

$caption_class = 'team-roster__member-name--no-link';

// Staff Thumbnail
if ( has_post_thumbnail( $id ) ) {
	$thumbnail = get_the_post_thumbnail( $id, 'alchemists_thumbnail-player-block' );
} else {
	$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/placeholder-140x210.jpg' ) . '" alt="">';
}

echo "<{$itemtag} class='team-roster__item'>";
	echo "<div class='team-roster__holder'>";
		echo "
			<{$icontag} class='team-roster__img'>"
				. '<a href="' . get_permalink( $id ) . '">' . $thumbnail
					. '<span class="btn-fab"></span>'
				. '</a>'
			. "</{$icontag}>";
		echo "
			<div class='team-roster__content'>"
				. "<header class='team-roster__member-header'>"
					. "<h2 class='team-roster__member-name " . esc_attr( $caption_class ) . "'>" . $caption . "</h2>"
				. "</header>";

				if ( $staff_role_name ) {
					echo "<div class='team-roster__member-subheader'>"
						. "<div class='team-roster__member-position'>" . $staff_role_name . "</div>"
					. "</div>";
				}

				echo "<ul class='team-roster__member-details list-unstyled'>";

					foreach( $data as $label => $value ):

						echo "<li class='team-roster__member-details-item'><span class='item-title'>" . $label. "</span> <span class='item-desc'>" . $value . "</span></li>";

					endforeach;

				echo "</ul>";

			echo "</div>";

			if ( $link_posts ) {
				echo "<a href='" . get_permalink( $id ) ."' class='btn-fab'></a>";
			}

	echo "</div>";
echo "</{$itemtag}>";
