<?php
/**
 * Official Photo
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( get_option( 'sportspress_officials_show_photo', 'yes' ) === 'no' ) {
	return;
}

if ( ! isset( $official_id ) ) {
	$official_id = get_the_ID();
}

if ( has_post_thumbnail( $official_id ) ) :
	?>
	<div class="sp-template sp-template-officials-photo sp-template-photo sp-officials-photo">
		<?php echo get_the_post_thumbnail( $official_id, 'sportspress-fit-medium' ); ?>
	</div>
	<?php
endif;
