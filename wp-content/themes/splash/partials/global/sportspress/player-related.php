<?php

$current_id = get_the_ID();

$position_player = wp_get_post_terms($current_id, 'sp_position');


$data_args = array(
	'post_type' => 'sp_player',
	'post_status' => 'publish',
	'posts_per_page' => '8',
	'post__not_in' => array($current_id),
);

if(!empty($position_player) and !empty($position_player[0]) and !empty($position_player[0]->term_id)) {
	$data_args['tax_query'] = array(
		array(
			'taxonomy' => 'sp_position',
			'field' => 'id',
			'terms' => array($position_player[0]->term_id)
		)
	);
}
$data = new WP_Query($data_args);

$carousel_id = 'stm-players-'.rand(0,9999);

if($data->post_count > 0) :
?>

	<div class="stm-player-ids <?php echo esc_attr($carousel_id); ?>">
		<div class="clearfix">
			<div class="stm-title-left">
				<h2 class="stm-main-title-unit"><?php esc_html_e('Related Players', 'splash'); ?></h2>
			</div>
			<?php if($data->found_posts > 4): ?>
				<div class="stm-carousel-controls-right">
					<div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
					<div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
				</div>
			<?php endif; ?>
		</div>


		<div class="stm-player-list-wrapper">
			<div class="stm-players clearfix">
				<?php if($data->have_posts()) {
					while($data->have_posts()){
						$data->the_post();
						$player_id = get_the_id();
						if(!empty($player_id)):
							$player_number = get_post_meta( $player_id, 'sp_number', true );
							$positions = wp_get_post_terms($player_id,'sp_position');
							$position = false;
							if($positions) {
								$position = $positions[0]->name;
							}
							$image = splash_get_thumbnail_url($player_id, 0, 'stm-270-370');
							if(!empty($image)): ?>

								<div class="stm-list-single-player">
									<a href="<?php echo esc_url(get_the_permalink($player_id)); ?>" title="<?php echo esc_attr(get_the_title($player_id)); ?>">
										<?php if(splash_is_layout('hockey')) echo "<div class="."'over'>"; ?>
                                            <img src="<?php echo esc_url($image); ?>" />
                                        <?php if(splash_is_layout('hockey')) echo "</div>"; ?>
										<div class="stm-list-single-player-info">
											<div class="inner heading-font">
												<div class="player-number">
                                                    <?php if(splash_is_layout('hockey')) echo("<div class="."'before_'>"); ?>
                                                        <?php echo esc_attr($player_number); ?>
                                                    <?php if(splash_is_layout('hockey')) echo("</div>") ?>
                                                </div>
												<div class="player-title"><?php echo wp_kses_post(get_the_title($player_id)); ?></div>
												<div class="player-position"><?php echo esc_attr($position); ?></div>
											</div>
										</div>
									</a>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					<?php };
				}; ?>
			</div>
		</div>
	</div>


	<script type="text/javascript">
		(function($) {
			"use strict";

			var unique_class = "<?php echo esc_js($carousel_id); ?>";

			var owl = $('.' + unique_class + ' .stm-players');

			$(document).ready(function () {
				owl.owlCarousel({
					items: <?php echo (splash_is_layout('soccer_two') || splash_is_layout('esport')) ? '3' : '4'; ?>,
					dots: false,
					autoplay: false,
					slideBy: 1,
					loop: true,
					responsive:{
						0:{
							items:1,
						},
						440:{
							items:2,
						},
						768:{
							items:3,
						},
						992:{
							items:3,
						},
						1100: {
							items: <?php echo (splash_is_layout('soccer_two') || splash_is_layout('esport')) ? '3' : '4'; ?>,
						}
					}
				});

				$('.' + unique_class + ' .stm-carousel-control-prev').on('click', function(){
					owl.trigger('prev.owl.carousel');
				});

				$('.' + unique_class + ' .stm-carousel-control-next').on('click', function(){
					owl.trigger('next.owl.carousel');
				});
			});
		})(jQuery);
	</script>

<?php endif; ?>