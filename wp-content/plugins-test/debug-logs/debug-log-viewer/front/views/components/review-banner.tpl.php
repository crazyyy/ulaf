<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// Ensure required variables are passed
if (!isset($nonce_url_do) || !isset($nonce_url_later)) {
	return;
}
?>

<div class="dbg-lv-review-banner">
	<div class="review-card">
		<div class="review-content">
			<div class="review-icon">
				<i class="fas fa-heart"></i>
			</div>
			<div class="review-message">
				<h4 class="review-title">
					<?php esc_html_e('Enjoying Debug Log Viewer?', 'debug-log-viewer'); ?>
				</h4>
				<p class="review-description">
					<?php esc_html_e('Your feedback helps us improve! If our plugin has been helpful, would you mind leaving a quick review?', 'debug-log-viewer'); ?>
				</p>
			</div>
		</div>
		
		<div class="review-actions">
			<a href="<?php echo esc_url($nonce_url_do); ?>" 
			   target="_blank"
			   data-rate-action="do-rate"
			   class="btn btn-primary review-btn">
				<i class="fas fa-star"></i>
				<?php esc_html_e('Rate Plugin', 'debug-log-viewer'); ?>
			</a>
			<a href="<?php echo esc_url($nonce_url_later); ?>" 
			   data-rate-action="later"
			   class="btn btn-outline-secondary review-btn">
				<i class="fas fa-clock"></i>
				<?php esc_html_e('Maybe Later', 'debug-log-viewer'); ?>
			</a>
		</div>
	</div>
</div>
