<?php
/**
 * Social - Facebook
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$alchemists_fb_url = isset( $alchemists_data['alchemists__opt-social-fb-url'] ) ? esc_html( $alchemists_data['alchemists__opt-social-fb-url'] ) : '';
?>

<div class="post-grid__item col-sm-6 col-lg-4">
	<!-- Facebook Counter -->
	<a href="<?php echo esc_url( $alchemists_fb_url ); ?>" class="btn-social-counter btn-social-counter--card btn-social-counter--fb" target="_blank">
		<div class="btn-social-counter__name"><?php esc_html_e( 'Facebook', 'alchemists' ); ?></div>
		<footer class="btn-social-counter__footer">
			<h6 class="btn-social-counter__title"><?php esc_html_e( 'Like Our Facebook Page', 'alchemists' ); ?></h6>
			<span class="btn-social-counter__add-icon"></span>
		</footer>
	</a>
	<!-- Facebook Counter / End -->
</div>
