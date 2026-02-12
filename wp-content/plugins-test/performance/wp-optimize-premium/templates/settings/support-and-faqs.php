<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<h3><?php esc_html_e('Support and feedback', 'wp-optimize'); ?></h3>
<div class="wpo-fieldgroup">
	<?php WP_Optimize()->include_template('settings/system-status.php'); ?>
	<ul>
		<li><?php $wp_optimize->wp_optimize_url('https://teamupdraft.com/documentation/wp-optimize/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=read-our-faqs&utm_creative_format=text', __('Read our FAQ here', 'wp-optimize')); ?></li>
		<li><?php $wp_optimize->wp_optimize_url('https://teamupdraft.com/support/?utm_source=wpo-plugin&utm_medium=referral&utm_campaign=paac&utm_content=support-is-available&utm_creative_format=text', __('Support is available here.', 'wp-optimize')); ?></li>
		<li>
			<?php echo esc_html__('If you like WP-Optimize,', 'wp-optimize') . ' ' . $wp_optimize->wp_optimize_url('https://www.trustpilot.com/review/getwpo.com', __('please give us a positive review, here.', 'wp-optimize'), '', '', true); // phpcs:ignore WordPress.Security.EscapeOutput -- Output is already escaped ?>
			<?php echo esc_html__('Or, if you did not like it,', 'wp-optimize') . ' ' . $wp_optimize->wp_optimize_url('https://docs.google.com/forms/d/e/1FAIpQLSfNBZuVxCiCYpEyhuTd_ctQLBrELuXP9a71IS9ezjlNZPEItQ/viewform', __('please tell us why at this link.', 'wp-optimize'), '', '', true); // phpcs:ignore WordPress.Security.EscapeOutput -- Output is already escaped ?>
		</li>
	</ul>
</div>