<?php
/**
 * Officials Dropdown
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( get_option( 'sportspress_officials_show_selector', 'yes' ) === 'no' ) {
	return;
}

if ( ! isset( $official_id ) ) {
	$official_id = get_the_ID();
}

$args = array(
	'post_type'      => 'sp_official',
	'numberposts'    => 100,
	'posts_per_page' => 100,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'tax_query'      => array(
		'relation' => 'AND',
	),
);

$officials = get_posts( $args );

$options = array();

if ( $officials && is_array( $officials ) ) :
	foreach ( $officials as $official ) :
		$options[] = '<option value="' . get_post_permalink( $official->ID ) . '" ' . selected( $official->ID, $official_id, false ) . '>' . $official->post_title . '</option>';
	endforeach;
endif;

if ( count( $options ) > 1 ) :
	?>
	<div class="sp-template sp-template-official-selector sp-template-profile-selector">
		<select class="sp-profile-selector sp-official-selector sp-selector-redirect">
			<?php
			echo wp_kses(
				implode( $options ),
				array(
					'option' => array(
						'value'    => array(),
						'selected' => array(),
					),
				)
			);
			?>
		</select>
	</div>
	<?php
endif;
