<div class="col-md-12">
	<div class="stm-donations-content">


		<a href="<?php the_permalink(); ?>" class="title clearfix">
			<div class="title-inner h5"><?php the_title(); ?></div>
			<div class="stm-donation-cash">
				<?php splash_donors_text(get_the_ID()); ?>
			</div>
		</a>

		<div class="stm-donation-inner clearfix">

			<?php if(has_post_thumbnail()): ?>
				<div class="image">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail('thumbnail', array('class' => 'img-responsive')); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="stm-donation-meta">
				<?php
					$procent = splash_donors_text(get_the_ID(), true);
					$procent_style = 'style="width:' . $procent . '%;"';
					if($procent >= 100) {
						$procent = esc_html__('Completed', 'splash');
					} else {
						$procent .= '%';
					}
				?>
				<div class="stm-donation-procent">
					<div class="stm-label clearfix heading-font">
						<div class="left">
							<?php esc_html_e('Donated', 'splash'); ?>
						</div>
						<div class="right">
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
				<a href="<?php the_permalink(); ?>" class="button"><?php esc_html_e('Learn More', 'splash') ?></a>
			</div>

		</div>

	</div>
</div>