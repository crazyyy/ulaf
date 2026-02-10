<?php
/**
 * Social - YouTube
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.4.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$alchemists_youtube_url = isset( $alchemists_data['alchemists__opt-social-youtube-url'] ) ? esc_html( $alchemists_data['alchemists__opt-social-youtube-url'] ) : '';
?>

<div class="post-grid__item col-sm-6 col-lg-4">
	<!-- YouTube Counter -->
	<a href="<?php echo esc_url( $alchemists_youtube_url ); ?>" class="btn-social-counter btn-social-counter--card btn-social-counter--youtube" target="_blank">
		<div class="btn-social-counter__name"><?php esc_html_e( 'YouTube', 'alchemists' ); ?></div>
		<footer class="btn-social-counter__footer">
			<h6 class="btn-social-counter__title"><?php esc_html_e( 'Follow Us on YouTube', 'alchemists' ); ?></h6>
			<span class="btn-social-counter__add-icon"></span>
		</footer>
	</a>
	<!-- YouTube Counter / End -->
</div>
