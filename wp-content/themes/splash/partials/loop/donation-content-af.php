<?php
$procent = splash_donors_text(get_the_ID(), true);
?>

<div class="col-md-12">
	<div class="stm-af-donations-content">		
		<div class="stm-donation-inner clearfix">
			
			<?php if(has_post_thumbnail()): ?>
				<div class="image">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail('full', array('class' => 'img-responsive')); ?>
					</a>
				</div>
			<?php endif; ?>
			
			<div class="stm-donation-meta">

				<a href="<?php the_permalink(); ?>" class="title clearfix">
					<div class="title-inner h5"><?php the_title(); ?></div>
					<div class="stm-donation-cash">
						<?php echo esc_html($procent . "% " . esc_html__('donated of', 'splash') . ' ' . get_theme_mod( 'donation_currency', esc_html__( '$', 'splash' ) ) . get_post_meta( get_the_ID(), 'goal', true )); ?>
					</div>
				</a>
				<?php
				$procent_style = ($procent > 0 && $procent <= 100) ? 'style="width:' . $procent . '%;"' : 'style="width: 100%;"';
				if($procent >= 100) {
					$procent = esc_html__('Completed', 'splash');
				} else {
					$procent .= '%';
				}
				?>
				<div class="stm-donation-procent">
					<div class="stm-range-wrapp">
						<div class="stm-donation-outer-bar">
							<div class="stm-dontaion-inner-bar" <?php echo sanitize_text_field($procent_style); ?>></div>
						</div>
						<div class="stm-range-control" <?php
						if(($procent > 0)){
							echo esc_attr("style='left: " . $procent . ";'");
						}
						else {
							echo esc_attr("style='left: 99.1%;'");
						}
						?>></div>
					</div>
				</div>
				<div class="content">
					<?php the_excerpt(); ?>
				</div>
				<a href="<?php the_permalink(); ?>" class="<?php echo (splash_is_layout("baseball")) ? "normal_font" : "button"; ?>"><?php esc_html_e('More Info', 'splash') ?> <i class="fa fa-arrow-right" aria-hidden="true"></i> </a>
			</div>
		
		</div>
	
	</div>
</div>