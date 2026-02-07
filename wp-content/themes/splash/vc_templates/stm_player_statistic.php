<?php
$title = $league = $season = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
if ( isset( $atts['items'] ) && strlen( $atts['items'] ) > 0 ) {
	$items = vc_param_group_parse_atts( $atts['items'] );
	if ( ! is_array( $items ) ) {
		$temp = explode( ',', $atts['items'] );
		$paramValues = array();
		foreach ( $temp as $value ) {
			$data = explode( '|', $value );
			$newLine = array();
			$newLine['title'] = isset( $data[0] ) ? $data[0] : 0;
			$newLine['sub_title'] = isset( $data[1] ) ? $data[1] : '';
			if ( isset( $data[1] ) && preg_match( '/^\d{1,3}\%$/', $data[1] ) ) {
				$colorIndex += 1;
				$newLine['title'] = (float) str_replace( '%', '', $data[1] );
				$newLine['sub_title'] = isset( $data[2] ) ? $data[2] : '';
			}
			$paramValues[] = $newLine;
		}
		$atts['items'] = urlencode( json_encode( $paramValues ) );
	}
}
?>
<div class="stm-media-tabs stm-statistic-tabs">

	<div class="clearfix">
		<?php if(!empty($title)): ?>
			<div class="stm-title-left">
				<<?php echo esc_html(getHTag()); ?> class="stm-main-title-unit white"><?php echo esc_attr($title); ?></<?php echo esc_html(getHTag()); ?>>
			</div>
		<?php endif; ?>
	</div>

	<div class="clearfix">
		<?php if(!empty($sub_title)): ?>
			<div class="stm-title-left">
				<h3 class="stm-main-title-unit sub-title white"><?php echo esc_attr($sub_title); ?></h3>
			</div>
		<?php endif; ?>

		<?php if(!empty($items)): ?>
			<div class="stm-media-tabs-nav">
				<ul class="stm-list-duty heading-font" role="tablist">
					<?php $counter = 0; ?>
					<?php foreach($items as $item): ?>
						<?php if(!empty($item['statistic']) and !empty($item['players'])):

							$counter++;

							if(!empty($item['statistic_title'])) {
								$item_title = $item['statistic_title'];
							} else {
								$item_title = get_the_title($item['statistic']);
							} ?>

							<li <?php if($counter == 1): ?>class="active"<?php endif; ?>>
								<a href="#stmStatistic<?php echo sanitize_file_name($item_title); ?>" aria-controls="stmStatistic<?php echo sanitize_file_name($item_title); ?>" role="tab" data-toggle="tab">
									<span><?php echo esc_attr($item_title); ?></span>
								</a>
							</li>
						<?php endif; ?>

					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>

	<div class="stm-tabs-wrapper">
		<?php if(!empty($images)): ?>
			<div class="stm-player-stat_bg" style="background-image: url(<?php echo esc_url(splash_get_thumbnail_url(0, $images, 'full')); ?>);"></div>
		<?php endif; ?>

		<div class="tab-content">
			<?php if(!empty($items)):
				$counter = 0;
				foreach($items as $item):
					if(!empty($item['statistic']) and !empty($item['players'])):
						$counter++;

						if(!empty($item['statistic_title'])) {
							$item_title = $item['statistic_title'];
						} else {
							$item_title = get_the_title($item['statistic']);
						}

						$posts = explode(', ', $item['players']);

						$field = get_post($item['statistic']);
						$field_key = ($field != null) ? $field->post_name : "";

					?>
						<div
							role="tabpanel"
							class="tab-pane fade <?php if($counter == 1){ ?>in active<?php } ?>"
							id="stmStatistic<?php echo sanitize_file_name($item_title); ?>"
						>
							<div class="stm-player-statistic-unit">
								<?php if(!empty($posts)): ?>
									<?php
									foreach($posts as $post_id):
										$player = new SP_Player( $post_id );
										$data = $player->data($league);
										unset( $data[0] );

										/*STAT*/
										$stat = 0;
										if($data and !empty($data[$season]) and !empty($data[$season][$field_key])) {
											$stat = $data[$season][$field_key];
										}

										/*IMAGE*/
										$player_image_id = get_post_meta($post_id, 'player_image', true);
										if(!empty($player_image_id)) {
											$image = splash_get_thumbnail_url( 0, $player_image_id, 'stm-540-500' );
										} else {
											$image = '';
										}

										/*TITLE*/
										$title = get_the_title($post_id);
										$player_url = get_the_permalink($post_id);

										/*POSITION*/
										$positions = wp_get_post_terms($post_id,'sp_position');
										$position = false;
										if($positions) {
											$position = $positions[0]->name;
										}

										/*NUMBER*/
										//$player_number = get_post_meta( $post_id, 'sp_number', true );
									?>
										<div class="stm-single-player-vc_stats clearfix">
											<?php if(!empty($image)): ?>
												<div class="image">
													<a href="<?php echo esc_url($player_url); ?>">
														<img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" />
													</a>
												</div>
											<?php endif; ?>
											<div class="stm-statistic-meta">
												<?php if(empty($stat)):
													$stat = '0';
												endif; ?>

												<div class="stat clearfix">
													<div class="stat-value"><?php echo esc_attr($stat); ?></div>
													<div class="stat-label heading-font">
														<span class="stm-red"><?php echo esc_attr($item_title); ?></span> <span><?php esc_html_e('per game', 'splash'); ?></span>
													</div>
												</div>

												<div class="player-meta-name_number heading-font">
													<?php if(!empty($title)): ?>
														<span class="title">
															<a href="<?php echo esc_url($player_url); ?>">
																<?php echo esc_attr($title); ?>
															</a>
														</span>
													<?php endif; ?>
													<?php /*if(!empty($player_number)): ?>
														<span class="number stm-red">
															#<?php echo esc_attr($player_number); ?>
														</span>
													<?php endif;*/ ?>
												</div>

												<?php if(!empty($position)): ?>
													<div class="position heading-font">
														<?php echo esc_attr($position); ?>
													</div>
												<?php endif; ?>

												<a href="<?php echo esc_url($player_url); ?>" class="button">
													<?php esc_html_e('View profile', 'splash'); ?>
												</a>

											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>

					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function($) {
		"use strict";

		var owl = $('.stm-statistic-tabs .tab-pane.active .stm-player-statistic-unit');

		$(document).ready(function () {
			owl.owlCarousel({
				items: 1,
				dots: false,
				autoplay: false,
				slideBy: 1,
				loop: false,
				navText: ''
			});
		});

		$('.stm-statistic-tabs .stm-media-tabs-nav ul li a').on('shown.bs.tab', function(){
			var tabId = $(this).attr('href');
			var owlTab = $(tabId + ' .stm-player-statistic-unit');

			owlTab.owlCarousel({
				items: 1,
				dots: false,
				autoplay: false,
				slideBy: 1,
				loop: false,
				navText: ''
			});

			owlTab.trigger('destroy.owl.carousel');
			owlTab.html(owlTab.find('.owl-stage-outer').html()).removeClass('owl-loaded');

			owlTab.owlCarousel({
				items: 1,
				dots: false,
				autoplay: false,
				slideBy: 1,
				loop: false,
				navText: ''
			});
		});

	})(jQuery);
</script>