<?php
/**
 * Social - Twitter
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$alchemists_tw_url = isset( $alchemists_data['alchemists__opt-social-tw-url'] ) ? $alchemists_data['alchemists__opt-social-tw-url'] : '';
?>

<div class="post-grid__item col-sm-6 col-lg-4">
	<!-- Twitter Counter -->
	<a href="<?php echo esc_url( $alchemists_tw_url ); ?>" class="btn-social-counter btn-social-counter--card btn-social-counter--twitter" target="_blank">
		<div class="btn-social-counter__name"><?php esc_html_e( 'Twitter', 'alchemists' ); ?></div>
		<footer class="btn-social-counter__footer">
			<h6 class="btn-social-counter__title"><?php esc_html_e( 'Follow Us on Twitter', 'alchemists' ); ?></h6>
			<span class="btn-social-counter__add-icon"></span>
		</footer>
	</a>
	<!-- Twitter Counter / End -->
</div>
