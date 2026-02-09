<?php
/**
 * Widget template
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 *
 * @var TP_Twitch_Stream $stream
 */

if ( ! isset( $streams ) || ! isset( $template_args ) )
	return;

$size = ( isset ( $template_args ) && ! empty( $template_args['size'] ) ) ? $template_args['size'] : 'large';
$preview = ( isset ( $template_args ) && ! empty( $template_args['preview'] ) ) ? $template_args['preview'] : 'image';

$stream_count = 0;
?>
<div class="twitch-streams">

	<?php foreach ( $streams as $stream ) { ?>
		<?php
		$stream_bg         = '';

		// Stream thumbnail
		$thumbnail_url     = isset( $stream->stream['thumbnail_url'] ) && ! empty( $stream->stream['thumbnail_url'] ) ? $stream->get_thumbnail_url( 380, 104 ) : '';

		// Offline background
		$offline_image_url = isset( $stream->stream['user']['offline_image_url'] ) && ! empty( $stream->stream['user']['offline_image_url'] ) ? $stream->stream['user']['offline_image_url'] : '';

		// set stream thumbnail (if live), otherwise channel offline image
		if ( $thumbnail_url ) {
			$stream_bg = $thumbnail_url;
		} elseif ( $offline_image_url ) {
			$stream_bg = $offline_image_url;
		}

		// output background
		if ( $stream_bg ) {
			$stream_bg = 'style="background-image: url(' . $stream_bg . ');"';
		}
		?>

		<?php $stream_count++; ?>
		<?php if ( 'large' === $size || ( 'large-first' === $size && 1 === $stream_count ) ) : ?>
		<div class="twitch-stream-wrapper">
			<div class="<?php $stream->the_classes( 'twitch-stream twitch-stream--featured twitch-stream--has-video card' ); ?>">
				<div class="twitch-stream__overlay effect-duotone effect-duotone--base" <?php echo wp_kses_post( $stream_bg ); ?>></div>
				<figure class="twitch-stream__img">
					<a href="<?php echo esc_url( $stream->get_user_url() ); ?>" target="_blank" rel="nofollow">
						<img class="twitch-stream__avatar" src="<?php echo esc_url( $stream->get_user_avatar_url( 150, 150 ) ); ?>" alt="<?php echo esc_attr( $stream->get_user_display_name() ); ?>" />
					</a>
				</figure>

				<div class="twitch-stream__body">
					<h5 class="twitch-stream__title">
						<?php
						if ( $stream->is_live() ){
							echo '<a href="' . $stream->get_url() . '" target="_blank" rel="nofollow">' . $stream->get_title() . '</a>';
						} else {
							esc_html_e( 'No Streams Found', 'alchemists' );
						}
						?>
					</h5>
					<div class="twitch-stream__info">
						<a href="<?php echo esc_url( $stream->get_user_url() ); ?>" target="_blank" rel="nofollow"><?php echo esc_html( $stream->get_user_display_name() ); ?></a><?php echo wp_kses_post( $stream->the_user_verified_icon() ); ?>
					</div>
					<div class="twitch-stream__footer">
						<div class="twitch-stream__status">
							<?php
							if ( $stream->get_viewer( true ) ) {
								esc_html_e( 'Live', 'alchemists' );
							} else {
								esc_html_e( 'Offline', 'alchemists' );
							}
							?>
						</div>

						<div class="twitch-stream__counters">
							<?php if ( $stream->get_viewer( true ) ) : ?>
							<div class="twitch-stream__viewers">
								<?php echo esc_html( $stream->get_viewer( true ) ); ?>
							</div>
							<?php endif; ?>
							<div class="twitch-stream__views">
								<?php echo esc_html( $stream->get_views( true ) ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="twitch-stream-video card">
				<div class="card__content">
					<?php if ( 'video' === $preview || ( 'video-first' === $preview && 1 === $stream_count ) ) : ?>
						<div class="twitch-stream__video">
							<div class="twitch-iframe-container">
								<iframe
									src="https://player.twitch.tv/?channel=<?php echo esc_attr( $stream->get_user_name( ) ); ?>&parent=<?php echo esc_attr( tp_twitch_get_site_host() ); ?>&muted=true"
									width="560"
									height="315"
									frameborder="0"
									scrolling="no"
									allowfullscreen="true">
								</iframe>
							</div>
						</div>

						<?php else : ?>

						<a class="twitch-stream__thumbnail-link" href="<?php echo esc_url( $stream->get_url() ); ?>" target="_blank" rel="nofollow">
							<img class="twitch-stream__thumbnail-img" src="<?php echo esc_url( $stream->get_thumbnail_url( 480, 270 ) ); ?>" alt="<?php echo esc_attr( $stream->get_thumbnail_alt() ); ?>" />
							<div class="twitch-stream__thumbnail-overlay">
								<i class="fa fa-play twitch-stream__thumbnail-icon"></i>
							</div>
						</a>

						<?php endif; ?>
				</div>
			</div>
		</div>

		<?php else : ?>
		<div class="twitch-stream-wrapper">
			<div class="<?php $stream->the_classes( 'twitch-stream card' ); ?>">
				<div class="twitch-stream__overlay effect-duotone effect-duotone--base" <?php echo wp_kses_post( $stream_bg ); ?>></div>
				<figure class="twitch-stream__img">
					<a href="<?php echo esc_url( $stream->get_user_url() ); ?>" target="_blank" rel="nofollow">
						<img class="twitch-stream__avatar" src="<?php echo esc_url( $stream->get_user_avatar_url( 150, 150 ) ); ?>" alt="<?php echo esc_attr( $stream->get_user_display_name() ); ?>" />
					</a>
				</figure>

				<div class="twitch-stream__body">
					<h5 class="twitch-stream__title">
						<?php
						if ( $stream->is_live() ){
							echo '<a href="' . $stream->get_url() . '" target="_blank" rel="nofollow">' . $stream->get_title() . '</a>';
						} else {
							esc_html_e( 'No Streams Found', 'alchemists' );
						}
						?>
					</h5>
					<div class="twitch-stream__info">
						<a href="<?php echo esc_url( $stream->get_user_url() ); ?>" target="_blank" rel="nofollow"><?php echo esc_html( $stream->get_user_display_name() ); ?></a><?php echo wp_kses_post( $stream->the_user_verified_icon() ); ?>
					</div>
					<div class="twitch-stream__footer">
						<div class="twitch-stream__status">
							<?php
							if ( $stream->get_viewer( true ) ) {
								esc_html_e( 'Live', 'alchemists' );
							} else {
								esc_html_e( 'Offline', 'alchemists' );
							}
							?>
						</div>

						<div class="twitch-stream__counters">
							<?php if ( $stream->get_viewer( true ) ) : ?>
							<div class="twitch-stream__viewers">
								<?php echo esc_html( $stream->get_viewer( true ) ); ?>
							</div>
							<?php endif; ?>
							<div class="twitch-stream__views">
								<?php echo esc_html( $stream->get_views( true ) ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php endif; ?>

	<?php } ?>

</div>
