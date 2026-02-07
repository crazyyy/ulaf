<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);



splash_enqueue_modul_scripts_styles('stm_video_posts_list');

$query = new WP_Query(array(
	'post_type' => 'post',
	'post_status' => 'publish',
	'ignore_sticky_posts' => 1,
	'posts_per_page' => 1,
	'p' => $post_id,
	'tax_query' => array(
		array(
			'taxonomy' => 'post_format',
			'field' => 'slug',
			'terms' => 'post-format-video'
		)
	)
));

?>

<div class="stm_video_posts_list stm_single_video_post">
	<?php
	if($query->have_posts()):
		$col = 'col-md-12 col-sm-12 col-xs-12';
		$posts = $query->get_posts();
		?>
		<div class="row">
			<div class="<?php echo esc_attr($col); ?>">
				<?php
				$post = $posts[0];
				$post_id = $post->ID;
				$video_url = get_post_meta($post_id, 'video_url', true);
				?>
				<div class="big-img-wrap">
					<div class="img">
						<img src="<?php echo get_the_post_thumbnail_url($post_id, 'stm-1140-666', true); ?>" />
						<div id="play-video" class="video-btn" data-src="<?php echo esc_attr($video_url); ?>"></div>
						<div class="categ">
							<ul>
								<?php
								foreach( get_the_terms( $post_id, 'category' ) as $val) {
									$catColor = get_term_meta($val->term_id, '_category_color', true);
									echo '<li><a href="' . get_category_link($val->term_id) . '" class="normal_font" style="background-color: #' . $catColor . ';">' . $val->name . '</a></li>';
								}
								?>
							</ul>
						</div>
						<?php if(!empty($video_url)) : ?>
							<div class="video-frame">
								<?php
								$video_w = 570;
								$video_h = $video_w / 1.58;
								echo '<iframe id="video-post-frame" width="' . $video_w . '" height="'.$video_h.'" frameborder="0" allowfullscreen></iframe>';
								?>
							</div>
						<?php endif; ?>
					</div>
					<div id="play-video" class="title heading-font" style="margin-top: 20px">
						<?php echo get_the_title($post_id); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		wp_reset_postdata();
	endif; ?>
</div>
