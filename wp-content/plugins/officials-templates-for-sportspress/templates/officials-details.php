<?php
/**
 * Official Details
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( get_option( 'sportspress_officials_show_details', 'yes' ) === 'no' ) {
	return;
}

if ( ! isset( $official_id ) ) {
	$official_id = get_the_ID();
}

$show_name              = get_option( 'otfs_officials_show_name', 'no' ) === 'yes' ? true : false;
$show_duties            = get_option( 'otfs_officials_show_duties', 'yes' ) === 'yes' ? true : false;
$show_nationality       = get_option( 'otfs_officials_show_nationality', 'yes' ) === 'yes' ? true : false;
$show_nationality_flags = get_option( 'otfs_officials_show_flags', 'yes' ) === 'yes' ? true : false;
$show_birthday          = get_option( 'otfs_officials_show_birthday', 'yes' ) === 'yes' ? true : false;
$show_age               = get_option( 'otfs_officials_show_age', 'yes' ) === 'yes' ? true : false;

$official      = new OTFS_Officials( $official_id );
$nationalities = $official->nationalities( $official_id );

$data = array();

if ( $show_name ) :
	$data[ esc_attr__( 'Name', 'sportspress' ) ] = get_the_title( $official_id );
endif;

if ( $show_nationality && $nationalities && is_array( $nationalities ) ) :
	$countries = SP()->countries->countries;
	$values    = array();
	foreach ( $nationalities as $nationality ) :
		if ( 2 === strlen( $nationality ) ) :
			$legacy      = SP()->countries->legacy;
			$nationality = strtolower( $nationality );
			$nationality = sp_array_value( $legacy, $nationality, null );
		endif;
		$country_name = sp_array_value( $countries, $nationality, null );
		$values[]     = $country_name ? ( $show_nationality_flags ? sp_flags( $nationality ) : '' ) . $country_name : '&mdash;';
	endforeach;
	$data[ esc_attr__( 'Nationality', 'sportspress' ) ] = implode( '<br>', $values );
endif;

if ( $show_duties ) :
	$duties = $official->duties( $official_id );
	if ( $duties && is_array( $duties ) ) :
		$duty_names = array();
		foreach ( $duties as $duty ) :
			$duty_names[] = $duty->name;
		endforeach;
		$data[ esc_attr__( 'Duty', 'sportspress' ) ] = implode( ', ', $duty_names );
	endif;
endif;

if ( $show_birthday ) {
	$data[ esc_attr__( 'Birthday', 'sportspress' ) ] = get_the_date( get_option( 'date_format' ), $official_id );
}

if ( $show_age ) {
	$sp_birthday_functions                      = new SportsPress_Birthdays();
	$data[ esc_attr__( 'Age', 'sportspress' ) ] = $sp_birthday_functions->get_age( get_the_date( 'm-d-Y', $official_id ) );
}

$data = apply_filters( 'otfs_officials_details', $data, $official_id );

if ( empty( $data ) ) {
	return;
}

$output = '<div class="sp-list-wrapper">' .
	'<dl class="sp-official-details">';

foreach ( $data as $label => $value ) :

	$output .= '<dt>' . $label . '</dt><dd>' . $value . '</dd>';

endforeach;

$output .= '</dl></div>';
?>
<div class="sp-template sp-template-official-details sp-template-details">
	<?php echo wp_kses_post( $output ); ?>
</div>
