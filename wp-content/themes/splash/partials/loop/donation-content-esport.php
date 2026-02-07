<div class="col-md-12 single-donation-wrap">
	<div class="stm-donations-content">

		<div class="stm-donation-inner clearfix">

			<?php if(has_post_thumbnail()): ?>
            <?php
                $image = get_post_thumbnail_id(get_the_ID());
                if(function_exists('wpb_getImageBySize')){
                    $post_thumbnail = wpb_getImageBySize( array(
                        'attach_id' => $image,
                        'thumb_size' => '180x170'
                    ) );
                }
                else {
                    $post_thumbnail = get_the_post_thumbnail(get_the_ID(), 'thumbnail', array('class' => 'img-responsive'));
                }
                ?>
				<div class="image">
					<a href="<?php the_permalink(); ?>">
                        <?php echo wp_kses_post($post_thumbnail['thumbnail']); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="stm-donation-meta">
                <a href="<?php the_permalink(); ?>" class="donate-title">
                    <div class="title-inner h5"><?php the_title(); ?></div>
                </a>
				<?php
					$procent = splash_donors_text(get_the_ID(), true);
                    $goal = get_post_meta( get_the_ID(), 'goal', true );
					$procent_style = 'style="width:' . $procent . '%;"';
					if($procent >= 100) {
						$procent = esc_html__('Completed', 'splash');
					} else {
						$procent .= esc_html__('% Donated of ', 'splash') . esc_html('$' . $goal);
					}
				?>
				<div class="stm-donation-procent">
					<div class="stm-label clearfix heading-font">
						<div class="left">
                            <?php echo esc_attr($procent); ?>
						</div>
					</div>
					<div class="stm-donation-outer-bar">
						<div class="stm-dontaion-inner-bar" <?php echo sanitize_text_field($procent_style); ?>></div>
					</div>
				</div>
				<div class="content">
					<?php the_excerpt(); ?>
				</div>
			</div>

		</div>

	</div>
</div>