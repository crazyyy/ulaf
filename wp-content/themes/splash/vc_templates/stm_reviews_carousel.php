<?php
$title = $number = '';
$number = 3;
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );


if($atts['review_view_style'] == "review_style_two") {
	wp_enqueue_script( 'cloud_carousel' );
}

$review_args = array(
	'post_type'      => 'testimonial',
	'posts_per_page' => intval( $number ),
	'post_status'    => 'publish'
);

$reviews_query = new WP_Query($review_args);

$id = 'stm-reviews-'.rand(0,9999);

if($reviews_query->have_posts()): ?>
	<?php if ($review_view_style == 'review_style_6'): 
		splash_enqueue_modul_scripts_styles('stm_reviews_carousel_style_6');
		$visibleItems = 2;
		?>
		<div class="stm-reviews-carousel <?php echo esc_attr($review_view_style . " " . $id) ?>">

			<?php if(!empty($title)): ?>
			<h2 class="stm-reviews-carousel__title"><?php echo esc_html($title) ?></h2>
			<?php endif; ?>

			<div class="stm-reviews-carousel__carousel">
				<?php while($reviews_query->have_posts()): $reviews_query->the_post(); ?>
				<?php
					$image_url = '';
					$position = '';
					if(has_post_thumbnail()) {
						$image_url = splash_get_thumbnail_url(get_the_id(), 0, 'thumbnail' );
					}
					$position = get_post_meta(get_the_ID(), 'position_name')[0];
				?>
				<div class="stm-reviews-carousel__review">
					<div class="stm-reviews-carousel__review-wrapper">
						<div class="stm-reviews-carousel__review-photo">
							<img src="<?php echo esc_url($image_url) ?>" alt="<?php echo get_the_title() ?>" />
						</div>
						<div class="stm-reviews-carousel__review-author">
							<div class="stm-reviews-carousel__review-author-name heading-font"><?php the_title() ?></div>
							<div class="stm-reviews-carousel__review-author-position"><?php echo esc_html($position) ?></div>
						</div>
						<div class="stm-reviews-carousel__review-text"><?php the_content() ?></div>
					</div>
				</div>
				<?php endwhile; ?>
			</div>

			<script type="text/javascript">
				(function($) {
					"use strict";
					var unique_class = "";
					var owl = $('.<?php echo esc_js($id); ?> .stm-reviews-carousel__carousel');
					$(document).ready(function () {
						owl.owlCarousel({
							items: <?php echo esc_js($visibleItems); ?>,
							dots: true,
							margin: 40,
							loop: false,
							responsive: {
								0:{
									items: 1
								},
								768:{
									items: <?php echo esc_js($visibleItems); ?>,
								}
							}
						});
					});
				})(jQuery);
			</script>
		</div>
	<?php elseif ($review_view_style == 'review_style_5'): 
		splash_enqueue_modul_scripts_styles('stm_reviews_carousel_style_5')
		?>
		<div class="stm-reviews-carousel <?php echo esc_attr($review_view_style . " " . $id) ?>">
			<div class="stm-reviews-carousel__wrapper">
				<?php if(!empty($title)): ?>
				<div class="stm-reviews-carousel__header">
					<h2 class="stm-reviews-carousel__title"><?php echo esc_html($title) ?></h2>
				</div>
				<?php endif; ?>
				<div class="stm-reviews-carousel__content">
					<div class="stm-reviews-carousel__carousel">
						<?php while($reviews_query->have_posts()): $reviews_query->the_post(); ?>
						<?php
							$image_url = '';
							$position = '';
							if(has_post_thumbnail()) {
								$image_url = splash_get_thumbnail_url(get_the_id(), 0, 'thumbnail' );
							}
							$position = get_post_meta(get_the_ID(), 'position_name')[0];
						?>
						<div class="stm-reviews-carousel__review">
							<div class="stm-reviews-carousel__review-wrapper">
								<div class="stm-reviews-carousel__review-text"><?php the_content() ?></div>
								<div class="stm-reviews-carousel__review-author">
									<div class="stm-reviews-carousel__review-author-name heading-font"><?php the_title() ?></div>
									<div class="stm-reviews-carousel__review-author-position"><?php echo esc_html($position) ?></div>
								</div>
								<div class="stm-reviews-carousel__review-photo">
									<img src="<?php echo esc_url($image_url) ?>" alt="<?php echo get_the_title() ?>" />
								</div>
							</div>
						</div>
						<?php endwhile; ?>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				(function($) {
					"use strict";
					var unique_class = "<?php echo esc_js($id); ?>";
					var items = "<?php echo esc_js($number); ?>";
					var owl = $('.' + unique_class + ' .stm-reviews-carousel__carousel');
					$(document).ready(function () {
						owl.owlCarousel({
							center: true,
							items: items,
							dots: false,
							margin: 0,
							loop: true,
							slideBy: 1,
							onTranslated: function () {
								// var image = $('.' + unique_class + ' .owl-item.active .stm-review-single').data('image');
								// $('.' + unique_class + ' .stm-review-image').css('background-image', 'url("' + image + '")');
							},
							responsive: {
								320:{
									items: 1
								},
								768:{
									items: <?php echo esc_js($number); ?>,
								}
							}
						});
					});
				})(jQuery);
			</script>
		</div>
	<?php else: ?>
		<?php if($atts['review_view_style'] != 'review_style_three'): ?>
			<div class="container">
		<?php endif; ?>
			<div class="stm-reviews-main-wrapper <?php echo esc_attr($id); echo " " . esc_attr($atts['review_view_style']); ?>">
				<div class="clearfix">
					<?php if(!empty($title)): ?>
						<div class="stm-title-left">
							<<?php echo esc_html(getHTag()); ?> class="stm-main-title-unit"><?php echo esc_attr($title); ?></<?php echo esc_html(getHTag()); ?>>
						</div>
					<?php endif; ?>
					<?php if($review_view_style != 'review_style_two' && $review_view_style != 'review_style_four' && !splash_is_layout('esport')) : ?>
						<div class="stm-carousel-controls-right stm-reviews-controls">
							<div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
							<div class="stm-review-dots"></div>
							<div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
						</div>
					<?php endif; ?>
				</div>
				<?php if(!empty($reviews_query->posts[0]) and !empty($reviews_query->posts[0]->ID) && $atts['review_view_style'] == 'review_style_one'): ?>
					<?php $image_url = splash_get_thumbnail_url($reviews_query->posts[0]->ID, 0, 'full'); ?>
					<div class="stm-review-image" style="background-image: url('<?php echo esc_url($image_url); ?>')"></div>
				<?php endif; ?>
				<div class="stm-reviews-carosel-wrapper">
					<div id="carousel" class="stm-reviews">
						<?php while($reviews_query->have_posts()): $reviews_query->the_post(); ?>
							<?php
								$image_url = '';
								if(has_post_thumbnail()) {
									$image_url = splash_get_thumbnail_url(get_the_id(), 0, 'full' );
								}

								$color = get_post_meta(get_the_id(), 'text_color', true);
								$idSingle = 'stm-reviews-single-'.rand(0,9999);
							?>

							<div id="<?php echo esc_attr($idSingle); ?>" class="stm-review-single" data-image="<?php echo esc_url($image_url); ?>">
								<div class="stm-review-container">
									<?php if($review_view_style != "review_style_four"): ?>
										<div class="icon" <?php echo !empty(esc_attr($color)) ? 'style="color:' . esc_attr($color) . '"' : ''; ?>>
											<i class="icon-quote"></i>
										</div>
									<?php endif; ?>
									<?php if($review_view_style == 'review_style_one'): ?>
										<div class="title heading-font"><?php the_title(); ?></div>
										<div class="content normal_font" <?php echo !empty(esc_attr($color)) ? 'style="color: ' . esc_attr($color) . '"' : ''; ?>><?php the_content(); ?></div>
										<div class="line"></div>
									<?php elseif($review_view_style == 'review_style_two'): ?>
										<div class="content normal_font" <?php echo !empty(esc_attr($color)) ? 'style="color: ' . esc_attr($color) . '"' : ''; ?>><?php the_content(); ?></div>
										<div class="title heading-font"><?php the_title(); ?></div>
										<div class="sub-title normal_font">
											<?php
												$postMeta = get_post_meta(get_the_ID(), 'position_name');
												if($postMeta != null) {
													echo splash_sanitize_text_field($postMeta[0]);
												}
											?>
										</div>
										<img class="avatar" src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'thumb')) ?>" />
									<?php elseif($review_view_style == 'review_style_three'): ?>
										<div class="review_author_img">
											<img class="avatar" src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'thumb')) ?>" />
										</div>
										<div class="review_data">
											<div class="title heading-font"><?php the_title(); ?></div>
											<?php
											$position = get_post_meta(get_the_ID(), 'position_name', true);
											if(splash_is_layout('esport') && !empty($position)): ?>
											<div class="position">
												<?php echo esc_html($position); ?>
											</div>
											<?php endif; ?>
											<div class="content normal_font" <?php echo !empty(esc_attr($color)) ? 'style="color: ' . esc_attr($color) . '"' : ''; ?>><?php the_content(); ?></div>
										</div>
									<?php elseif($review_view_style == 'review_style_four'): ?>
										<div class="stm_review_bsb">
											<div class="review_author_img">
												<img class="avatar" src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'stm-200-200')) ?>" />
											</div>
											<div class="review_content_bg">
												<div class="content normal_font" <?php echo !empty(esc_attr($color)) ? 'style="color: ' . esc_attr($color) . '"' : ''; ?>><?php the_content(); ?></div>
												<div class="review_divider"></div>
												<div class="title heading-font"><?php the_title(); ?></div>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endwhile; ?>
					</div>
				</div>
				<?php if($review_view_style == "review_style_four"): ?>
					<div class="review_four_nav">
						<div class="stm-carousel-controls-right stm-reviews-controls">
							<div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
							<div class="stm-review-dots"></div>
							<div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php if($atts['review_view_style'] != 'review_style_three'): ?>
		</div>
		<?php endif; ?>
		<?php // carousel setting param
		$showDots = ($review_view_style == "review_style_one" || $review_view_style == "review_style_four") ? true : false;
		$margin = ($review_view_style != "review_style_four") ? 20 : 60;
		$visibleItems = ($review_view_style == "review_style_four") ? 1.3 : 2;
		if($review_view_style == "review_style_one") $visibleItems = 1;
		$slideBy = ($review_view_style == "review_style_one" || $review_view_style == "review_style_four") ? 1 : 2;

		?>


		<script type="text/javascript">
			(function($) {
				"use strict";

				var unique_class = "<?php echo esc_js($id); ?>";

				var owl = $('.' + unique_class + ' .stm-reviews');

				$(document).ready(function () {
					<?php if($atts['review_view_style'] == "review_style_one" || $atts['review_view_style'] == "review_style_three" || $atts['review_view_style'] == "review_style_four"): ?>
						owl.owlCarousel({
							<?php if($review_view_style == "review_style_four"): ?>
							center: true,
							<?php endif; ?>
							items: <?php echo esc_js($visibleItems); ?>,
							dots: true,
							margin: <?php echo esc_js($margin); ?>,
							loop: true,
							slideBy: <?php echo esc_js($slideBy); ?>,
							<?php if($showDots): ?>
							dotsContainer: '.' + unique_class + ' .stm-review-dots',
							<?php endif; ?>
							onTranslated: function () {
								var image = $('.' + unique_class + ' .owl-item.active .stm-review-single').data('image');
								$('.' + unique_class + ' .stm-review-image').css('background-image', 'url("' + image + '")');
							},

							responsive: {
							<?php if($atts['review_view_style'] == 'review_style_three'): ?>
								320:{
									items: 1,
									slideBy: 1
								},
								768:{
									items: 1,
									slideBy: 1
								},
								769:{
									items: 2,
									slideBy: 2
								}
								<?php elseif($atts['review_view_style'] == 'review_style_four'): ?>
								320:{
									items: 1,
									slideBy: 1
								},
								768:{
									items: <?php echo esc_js($visibleItems); ?>,
									slideBy: <?php echo esc_js($slideBy); ?>
								}
							<?php endif; ?>
							}

						});

						$('.' + unique_class + ' .stm-carousel-control-prev').on('click', function(){
							owl.trigger('prev.owl.carousel');
						});

						$('.' + unique_class + ' .stm-carousel-control-next').on('click', function(){
							owl.trigger('next.owl.carousel');
						});
					<?php else: ?>

						var xr = 470;

						if($("body").outerWidth() == 1024) {
							xr = 300;
						} else if($("body").outerWidth() == 768) {
							xr = 280;
						} else if($("body").outerWidth() < 650) {
							xr = 230;
						}

						$('#carousel').Cloud9Carousel({
							yOrigin: 0,
							yRadius: -17,
							xRadius: xr,
							itemClass: "stm-review-single",
							bringToFront: true,
							farScale: 0.8,
							onLoaded: function(showcase) {
								//console.log(showcase);
							},
							onAnimationFinished: function () {
								$(this).addClass("stm-active");
							},
							onRendered: function(carousel) {
								var index = carousel.nearestIndex();
								for(var q=0;q<(carousel.items.length);q++ ){
									if(q == index){
										var item = carousel.items[q];
										$('#' + item.element.id).css("opacity", "1");
									} else {
										var item = carousel.items[q];
										$('#' + item.element.id).css("opacity", "0.8");
									}
								}

							}
						});

					<?php endif;?>
				});
			})(jQuery);
		</script>

	<?php endif; ?>
	
	<?php wp_reset_postdata();
endif; ?>