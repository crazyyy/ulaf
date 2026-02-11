<?php
/**
 * Event Video
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version   1.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! isset( $id ) )
	$id = get_the_ID();

$video_url = get_post_meta( $id, 'sp_video', true );
if ( $video_url ):
	?>
	<div class="card card--no-paddings sp-template sp-template-event-video sp-event-video">
		<div class="card__header">
			<h4 class="sp-table-caption"><?php esc_html_e( 'Video', 'alchemists' ); ?></h4>
		</div>
		<div class="card__content text-center">
			<div class="alc-embeded-player alc-embeded-player--container">
				<?php
				global $wp_embed;
				print_r( str_replace( 'frameborder="0"', '', $wp_embed->autoembed( $video_url ) ) );
				?>
			</div>
		</div>
	</div>
<?php endif; ?>

