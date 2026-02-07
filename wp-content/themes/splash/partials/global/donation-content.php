<?php
	$sidebar_id = get_theme_mod('donation_sidebar', 'primary_sidebar');
	$sidebar_position = get_theme_mod('donation_sidebar_position', get_theme_mod("sidebar_position", "left"));
	$procent = splash_donors_text(get_the_ID(), true);

	if( !empty($sidebar_id) ) {
		$blog_sidebar = get_post( $sidebar_id );
	} else {
		$blog_sidebar = '';
	}

	if($sidebar_id == 'no_sidebar') {
		$sidebar_id = false;
	}

	$stm_sidebar_layout_mode = splash_sidebar_layout_mode($sidebar_position, $sidebar_id);

	$donation_subtitle = get_post_meta(get_the_id(), 'donor_subtitle', true);
	$donation_intro = get_post_meta(get_the_id(), 'donor_intro', true);

	$paypal_email = get_theme_mod('paypal_email', '');

?>

<div class="row">
	<?php echo wp_kses_post($stm_sidebar_layout_mode['content_before']); ?>
		<div class="stm-small-title-box">
			<?php if(!splash_is_layout('soccer_two') && !splash_is_layout('hockey')) get_template_part('partials/global/title-box'); ?>
		</div>

		<?php if(!empty($donation_subtitle)): ?>
			<div class="stm-donation-subtitle">
				<?php echo esc_attr($donation_subtitle); ?>
			</div>
		<?php endif; ?>

		<!--Post thumbnail-->
		<?php if ( has_post_thumbnail() ): ?>
			<div class="post-thumbnail">
				<?php the_post_thumbnail( 'stm-1170-650', array( 'class' => 'img-responsive' ) ); ?>
			</div>
		<?php endif; ?>



		<div class="clearfix">
			<div class="stm-donation-cash">
				<?php if(splash_is_af() || splash_is_layout("baseball")) : ?>
				<div class="stm-af-left">
					<span class="stm-donation-info">
						<?php echo esc_html($procent . "% " . esc_html__('donated of', 'splash') . ' ' . get_theme_mod( 'donation_currency', esc_html__( '$', 'splash' ) ) . get_post_meta( get_the_ID(), 'goal', true )); ?>
					</span>
				</div>
				<?php endif; ?>
				<div class="stm-af-right"><?php splash_donors_text(get_the_ID()); ?></div>
				<?php
				$procent_style = ($procent > 0 && $procent <= 100) ? 'style="width:' . $procent . '%;"' : 'style="width: 100%;"';
				if($procent >= 100) {
					$procent = esc_html__('Completed', 'splash');
				} else {
					$procent .= '%';
				}
				?>
				<?php if(splash_is_af() || splash_is_layout("baseball")) : ?>
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
							echo esc_attr("style='left: 99.4%;'");
						}
						?>></div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<div class="stm-donate">
				<a href="#" data-toggle="modal" data-target="#donationModal" class="button with_bg <?php if(splash_is_af()) echo "btn-md"; ?>"><?php esc_html_e('Donate now', 'splash'); ?></a>
			</div>

		</div>

		<?php if(!empty($donation_intro)): ?>
			<div class="stm-donation-intro">
				<?php echo esc_attr($donation_intro); ?>
			</div>
		<?php endif; ?>

		<div class="post-content">
			<?php the_content(); ?>
			<div class="clearfix"></div>
		</div>

		<?php splash_pages_pagination(); ?>

		<!--Comments-->
		<?php if ( comments_open() || get_comments_number() ) { ?>
			<div class="stm_post_comments">
				<?php comments_template(); ?>
			</div>
		<?php } ?>

	<?php echo wp_kses_post($stm_sidebar_layout_mode['content_after']); ?>

	<!--Sidebar-->
	<?php splash_display_sidebar(
		$sidebar_id,
		$stm_sidebar_layout_mode['sidebar_before'],
		$stm_sidebar_layout_mode['sidebar_after'],
		$blog_sidebar
	); ?>

</div>