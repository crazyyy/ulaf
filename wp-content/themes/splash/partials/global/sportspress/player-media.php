<?php
splash_enqueue_modul_scripts_styles('stm_media_tabs_default');
$player_medias = unserialize( get_post_meta( get_the_ID(), 'stm_player_media', true ) );
$player_all    = array();
$player_medias_sorted = array(
	'image' => array(),
	'audio' => array(),
	'video' => array(),
);
if ( ! empty( $player_medias ) ) {
	foreach ( $player_medias as $player_media_type => $player_media ) {
		if ( ! empty( $player_media['text'] ) ) {
			foreach ( $player_media['text'] as $player_media_type_key => $player_media_type_value ) {
				$new_media         = array();
				$new_media['type'] = $player_media_type;
				$new_media['text'] = $player_media_type_value;
				if ( ! empty( $player_media['image_type_text'] )
				     and ! empty( $player_media['image_type_text'] )
				         and ! empty( $player_media['image_type_text'][ $player_media_type_key ] )
				) {
					$new_media['image'] = $player_media['image_type_text'][ $player_media_type_key ];
				}
				if($player_media_type !== 'image') {
					if ( ! empty( $player_media['url'] )
					     and ! empty( $player_media['url'] )
					         and ! empty( $player_media['url'][ $player_media_type_key ] )
					) {
						$new_media['url'] = $player_media['url'][ $player_media_type_key ];
					}
				}
				$player_all[] = $new_media;
				$player_medias_sorted[$player_media_type][] = $new_media;
			}
		}
	}
	shuffle( $player_all );
} ?>

<?php if ( ! empty( $player_all ) ): ?>
	<div class="stm-media-archive stm-media-archive-none">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="stm-media-tabs">
						<div class="clearfix">
							<div class="stm-title-left">
								<h1 class="stm-main-title-unit"><?php esc_html_e( 'Player Media', 'splash' ); ?></h1>
							</div>
							<div class="stm-media-tabs-nav">
								<ul class="stm-list-duty heading-font">
									<li class="active">
										<a href="#all_media" aria-controls="all_media" role="tab" data-toggle="tab">
											<span><?php esc_html_e( 'All', 'splash' ); ?></span>
										</a>
									</li>
									<?php if ( !empty($player_medias_sorted['image']) ): ?>
										<li>
											<a href="#image_media" aria-controls="image_media" role="tab" data-toggle="tab">
												<span><?php esc_html_e( 'Images', 'splash' ); ?></span>
											</a>
										</li>
									<?php endif; ?>
									<?php if ( !empty($player_medias_sorted['audio']) ): ?>
										<li>
											<a href="#audio_media" aria-controls="audio_media" role="tab" data-toggle="tab">
												<span><?php esc_html_e( 'Audio', 'splash' ); ?></span>
											</a>
										</li>
									<?php endif; ?>
									<?php if ( !empty($player_medias_sorted['video']) ): ?>
										<li>
											<a href="#video_media" aria-controls="video_media" role="tab" data-toggle="tab">
												<span><?php esc_html_e( 'Video', 'splash' ); ?></span>
											</a>
										</li>
									<?php endif; ?>
								</ul>
							</div>
						</div>
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane fade in active" id="all_media">
								<div class="stm-medias-unit-wider">
									<div class="stm-medias-unit clearfix">

										<?php if ( !empty($player_all) ) {
											$post_position = 0;
											$style         = 'style_' . rand( 1, 3 );
											foreach ( $player_all as $player_all_single ) {
												$post_position ++;
												if ( $post_position % 6 == 0 ) {
													$style = 'style_' . rand( 1, 3 );
												}
												stm_single_media_output( get_the_ID(), $post_position, $style, 'none', '', $player_all_single );
											}
										}; ?>

									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane fade" id="image_media">
								<div class="stm-medias-unit-wider">
									<div class="stm-medias-unit clearfix">
										<?php if ( !empty($player_medias_sorted['image']) ) {
											$post_position = 0;
											$style         = 'style_' . rand( 1, 3 );
											foreach ( $player_medias_sorted['image'] as $players_images ) {
												$post_position ++;
												if ( $post_position % 6 == 0 ) {
													$style = 'style_' . rand( 1, 3 );
												}
												stm_single_media_output( get_the_ID(), $post_position, $style, 'none', '', $players_images );
											}
										}; ?>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane fade" id="audio_media">
								<div class="stm-medias-unit-wider">
									<div class="stm-medias-unit clearfix">
										<?php if ( !empty($player_medias_sorted['audio']) ) {
											$post_position = 0;
											$style         = 'style_' . rand( 1, 3 );
											foreach ( $player_medias_sorted['audio'] as $players_audio ) {
												$post_position ++;
												if ( $post_position % 6 == 0 ) {
													$style = 'style_' . rand( 1, 3 );
												}
												stm_single_media_output( get_the_ID(), $post_position, $style, 'none', '', $players_audio );
											}
										}; ?>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane fade" id="video_media">
								<div class="stm-medias-unit-wider">
									<div class="stm-medias-unit clearfix">
										<?php if ( !empty($player_medias_sorted['video']) ) {
											$post_position = 0;
											$style         = 'style_' . rand( 1, 3 );
											foreach ( $player_medias_sorted['video'] as $players_video ) {
												$post_position ++;
												if ( $post_position % 6 == 0 ) {
													$style = 'style_' . rand( 1, 3 );
												}
												stm_single_media_output( get_the_ID(), $post_position, $style, 'none', '', $players_video );
											}
										}; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		var stm_player_id = <?php echo esc_js(get_the_ID()); ?>;
	</script>
<?php endif; ?>
