<?php
/**
 * Social - Twitch
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$alchemists_insta_url = isset( $alchemists_data['alchemists__opt-social-insta-url'] ) ? $alchemists_data['alchemists__opt-social-insta-url'] : '';
?>

<div class="post-grid__item col-sm-6 col-lg-4">
	<!-- Instagram Counter -->
	<a href="<?php echo esc_url( $alchemists_insta_url ); ?>" class="btn-social-counter btn-social-counter--card btn-social-counter--twitch" target="_blank">
		<div class="btn-social-counter__name"><?php esc_html_e( 'Twitch', 'alchemists' ); ?></div>
		<footer class="btn-social-counter__footer">
			<h6 class="btn-social-counter__title"><?php esc_html_e( 'Follow Us on Twitch', 'alchemists' ); ?></h6>
			<span class="btn-social-counter__add-icon"></span>
		</footer>
	</a>
	<!-- Instagram Counter / End -->
</div>
