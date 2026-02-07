<?php
$number = $title = '';
$atts   = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if ($media_style == 'style_5_3') {
	splash_enqueue_modul_scripts_styles('stm_media_tabs_style_5_3');
} else {
	splash_enqueue_modul_scripts_styles('stm_media_tabs_default');
}

if ( empty( $number ) || empty($disable_masonry) ) {
	if($media_style == "style_2_3" && $number > 6) {
		$number = 6;
	} else if($media_style == "style_3_3" && $number > 9 ) {
		$number = 9;
	} else if($media_style == "style_5_3" && $number > 11 ) {
		$number = 11;
	}
}

$disableMasonryClass = "";
if(!empty($disable_masonry)) {
	$disableMasonryClass = "stm-disable-masonry";
}

/*ALL MEDIA ARGS*/
$all_media_args = array(
	'post_type'      => 'media_gallery',
	'post_status'    => 'publish',
	'posts_per_page' => intval( $number ),
	'meta_key'       => '_thumbnail_id',
);
$media_args     = array(
	'orderby' => 'date'
);

$media_args     = array_merge( $all_media_args, $media_args );
$all_medias     = new WP_Query( $media_args );

/*ALL IMAGE ARGS*/
$image_args = array(
	'meta_query' => array(
		array(
			'key'     => 'media_type',
			'value'   => 'image',
			'compare' => '='
		),
		'relation' => 'AND'
	)
);
$image_args = array_merge( $all_media_args, $image_args );
$all_images = new WP_Query( $image_args );

/*ALL AUDIO ARGS*/
$audio_args = array(
	'meta_query' => array(
		array(
			'key'     => 'media_type',
			'value'   => 'audio',
			'compare' => '='
		),
		'relation' => 'AND'
	)
);
$audio_args = array_merge( $all_media_args, $audio_args );
$all_audios = new WP_Query( $audio_args );

/*ALL VIDEO ARGS*/
$video_args = array(
	'meta_query' => array(
		array(
			'key'     => 'media_type',
			'value'   => 'video',
			'compare' => '='
		),
		'relation' => 'AND'
	)
);
$video_args = array_merge( $all_media_args, $video_args );
$all_videos = new WP_Query( $video_args );
?>
<?php if ( $all_medias->have_posts() ): ?>
	<div class="stm-media-tabs _gallery <?php if(splash_is_layout("baseball")) echo "stm-media-baseball-tabs"; echo esc_attr($media_style); ?>">
		<?php if ($media_style == 'style_5_3'): ?>
		<div class="container stm-media-tabs-header">
		<?php else: ?>
		<div class="clearfix">
		<?php endif; ?>
			<?php if ( ! empty( $title )): ?>
				<div class="stm-title-left">
					<<?php echo esc_html(getHTag()); ?> class="stm-main-title-unit"><?php echo esc_attr( $title ); ?></<?php echo esc_html(getHTag()); ?>>
                    <?php if(splash_is_layout("baseball")) : ?>
                        <a class="button stm-bsb-medias_btn with_bg" href="<?php echo esc_url(get_site_url( get_current_blog_id(), "medias")); ?>"><?php echo esc_html__("See More Media", "splash"); ?></a>
                    <?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="stm-media-tabs-nav">
				<ul class="stm-list-duty heading-font" role="tablist">
					<li class="active">
						<a href="#all_medias" aria-controls="all_medias" role="tab" data-toggle="tab" <?php if(splash_is_layout('baseball')) :?>class="normal_font"<?php endif; ?>>
							<span><?php esc_html_e( 'All', 'splash' ); ?></span>
						</a>
					</li>
					<?php if ( $all_images->have_posts() ): ?>
						<li>
							<a href="#image_media" aria-controls="image_media" role="tab" data-toggle="tab" <?php if(splash_is_layout('baseball')) :?>class="normal_font"<?php endif; ?>>
								<span><?php esc_html_e( 'Images', 'splash' ); ?></span>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( $all_audios->have_posts() ): ?>
						<li>
							<a href="#audio_media" aria-controls="audio_media" role="tab" data-toggle="tab">
								<span><?php esc_html_e( 'Audio', 'splash' ); ?></span>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( $all_videos->have_posts() ): ?>
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
			<div role="tabpanel" class="tab-pane fade in active" id="all_medias">
				<div class="stm-medias-unit-wider <?php echo esc_attr($disableMasonryClass); ?>">
					<div class="stm-medias-unit clearfix">
						<?php if ( $all_medias->have_posts() ) {
							$post_position = 0;
							while ( $all_medias->have_posts() ) {
								$all_medias->the_post();
								$post_position ++;
								if($atts['media_style'] == "style_5_3") stm_single_media_output_grid( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								elseif($atts['media_style'] == "style_2_3") stm_single_media_output( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								else  stm_single_media_output_3x3( get_the_ID(), $post_position, 'style_4', 'none', $disable_masonry );
							}
						}; ?>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="image_media">
				<div class="stm-medias-unit-wider <?php echo esc_attr($disableMasonryClass); ?>">
					<div class="stm-medias-unit clearfix">
						<?php if ( $all_images->have_posts() ) {
							$post_position = 0;
							while ( $all_images->have_posts() ) {
								$all_images->the_post();
								$post_position ++;
								if($atts['media_style'] == "style_5_3") stm_single_media_output_grid( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								elseif($atts['media_style'] == "style_2_3") stm_single_media_output( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								else  stm_single_media_output_3x3( get_the_ID(), $post_position, 'style_4', 'none', $disable_masonry );
							}
						}; ?>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="audio_media">
				<div class="stm-medias-unit-wider <?php echo esc_attr($disableMasonryClass); ?>">
					<div class="stm-medias-unit clearfix">
						<?php if ( $all_audios->have_posts() ) {
							$post_position = 0;
							while ( $all_audios->have_posts() ) {
								$all_audios->the_post();
								$post_position ++;
								if($atts['media_style'] == "style_5_3") stm_single_media_output_grid( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								elseif($atts['media_style'] == "style_2_3") stm_single_media_output( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								else  stm_single_media_output_3x3( get_the_ID(), $post_position, 'style_4', 'none', $disable_masonry );
							}
						}; ?>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="video_media">
				<div class="stm-medias-unit-wider <?php echo esc_attr($disableMasonryClass); ?>">
					<div class="stm-medias-unit clearfix">
						<?php if ( $all_videos->have_posts() ) {
							$post_position = 0;
							while ( $all_videos->have_posts() ) {
								$all_videos->the_post();
								$post_position ++;
								if($atts['media_style'] == "style_5_3") stm_single_media_output_grid( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								elseif($atts['media_style'] == "style_2_3") stm_single_media_output( get_the_ID(), $post_position, 'style_1', 'none', $disable_masonry );
								else  stm_single_media_output_3x3( get_the_ID(), $post_position, 'style_4', 'none', $disable_masonry );
							}
						}; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php else: ?>
	<h4><?php esc_html_e( 'No Media found', 'splash' ); ?></h4>
<?php endif; ?>

<?php wp_reset_postdata(); ?>