<?php
defined( 'ABSPATH' ) || exit;

if( !current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="col-xs-12 p-t-20" data-parent="bulk-delete-posts-enabled">
	<div class="adminease-bulk-delete-wrapper">
		<h3><?php esc_html_e( 'Bulk deletion status', 'adminease' ); ?></h3>
		
		<div class="adminease-bulk-delete-preview-results" style="display: none; margin-bottom: 20px; padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
			<p class="adminease-preview-text" style="margin: 0; font-weight: 600;"></p>
		</div>
		
		<div style="margin-bottom: 20px;">
			<button type="button" class="button button-primary adminease-bulk-delete-start" style="background: #d63638; border-color: #d63638;" disabled>
				<?php esc_html_e( 'Start Bulk Delete', 'adminease' ); ?>
			</button>
		</div>
		
		<!-- Reusable Progress Bar Component -->
		<div class="adminease-progress-bar-container" style="display: none; margin-bottom: 20px;">
			<div class="adminease-progress-bar-wrapper" style="background: #f0f0f1; border-radius: 4px; overflow: hidden; height: 30px; position: relative;">
				<div class="adminease-progress-bar-fill" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
				<div class="adminease-progress-bar-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: 600; color: #000;">
					0 / 0 (0%)
				</div>
			</div>
		</div>
		
		<div class="adminease-bulk-delete-list-container" style="display: none;">
			<h4><?php esc_html_e( 'Deleted Posts:', 'adminease' ); ?></h4>
			
			<div class="adminease-bulk-delete-list" style="height: 400px; overflow-y: scroll; border: 1px solid #ddd; padding: 15px; background: #f9f9f9; font-family: monospace; font-size: 13px; line-height: 1.8;">
				<!-- Items will be appended here via JavaScript -->
			</div>
		</div>
		
		<div class="adminease-bulk-delete-complete" style="display: none; margin-top: 20px; padding: 15px; background: #d7f0db; border-left: 4px solid #00a32a; border-radius: 4px;">
			<p style="margin: 0; font-weight: 600; color: #00a32a;">
				✓ <span class="adminease-complete-message"></span>
			</p>
		</div>
	</div>
</div>