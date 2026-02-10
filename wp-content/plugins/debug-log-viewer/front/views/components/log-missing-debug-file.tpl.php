<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>
<div class="log-not-found">
	<div class="welcome-header">
		<div class="welcome-content">
			<h2 class="welcome-title">
				<i class="fas fa-rocket welcome-icon"></i>
				<?php esc_html_e('Welcome to Debug Log Viewer!', 'debug-log-viewer'); ?>
			</h2>
			<p class="welcome-subtitle">
				<?php esc_html_e('It looks like this is your first time here. We couldn\'t find a log file to begin debugging.', 'debug-log-viewer'); ?>
			</p>
			<p class="welcome-description">
				<?php esc_html_e('Let\'s get you set up to start monitoring your WordPress debug logs in style!', 'debug-log-viewer'); ?>
			</p>
		</div>
	</div>

	<div class="action-section">
		<div class="primary-action">
			<button id="first_run_enable_logging" class="btn btn-lg btn-primary start-debugging-btn">
				<i class="fas fa-play-circle"></i>
				<?php esc_html_e('Start Debugging Now', 'debug-log-viewer'); ?>
			</button>
			<p class="action-description"><?php esc_html_e('Automatically configure WordPress debugging for you', 'debug-log-viewer'); ?></p>
		</div>
		
		<div class="divider-section">
			<div class="divider-line"></div>
			<span class="divider-text"><?php esc_html_e('OR', 'debug-log-viewer'); ?></span>
			<div class="divider-line"></div>
		</div>

		<div class="manual-setup-section">
			<a class="manual-debugging-instructions" href="#dbg_lv_debugging_instructions">
				<i class="fas fa-tools"></i>
				<?php esc_html_e('Manual Setup Instructions', 'debug-log-viewer'); ?>
				<i class="fas fa-chevron-down toggle-icon"></i>
			</a>
		</div>
	</div>
	<div class="instructions-section">
		<div id="dbg_lv_debugging_instructions" style="display: none;">
			<div class="instruction-card">
				<div class="card-header">
					<h4 class="instruction-title">
						<i class="fas fa-cog"></i>
						<?php esc_html_e('Manual Setup Guide', 'debug-log-viewer'); ?>
					</h4>
					<p class="instruction-subtitle"><?php esc_html_e('Follow these simple steps to enable WordPress debugging manually', 'debug-log-viewer'); ?></p>
				</div>
				<div class="card-body">
					<div class="instruction-steps">
						<div class="step-item">
							<div class="step-number">1</div>
							<div class="step-content">
								<h5><?php esc_html_e('Locate your wp-config.php file', 'debug-log-viewer'); ?></h5>
								<p><?php 
									printf(
										/* translators: %1$s and %2$s are <code> tags around wp-config.php */
										esc_html__('Find the %1$swp-config.php%2$s file in the root folder of your WordPress installation. You can access your site files through FTP, cPanel, or any File Manager plugin.', 'debug-log-viewer'),
										'<code>',
										'</code>'
									);
								?></p>
							</div>
						</div>
						
						<div class="step-item">
							<div class="step-number">2</div>
							<div class="step-content">
								<h5><?php esc_html_e('Look for existing debug settings', 'debug-log-viewer'); ?></h5>
								<p><?php 
									printf(
										/* translators: %1$s and %2$s are <code> tags around wp-config.php */
										esc_html__('Open %1$swp-config.php%2$s for editing and search for existing lines like:', 'debug-log-viewer'),
										'<code>',
										'</code>'
									);
								?></p>
								<div class="code-examples">
									<code>define( 'WP_DEBUG', false );</code>
									<code>define( 'WP_DEBUG_LOG', false );</code>
								</div>
							</div>
						</div>
						
						<div class="step-item">
							<div class="step-number">3</div>
							<div class="step-content">
								<h5><?php esc_html_e('Enable debugging', 'debug-log-viewer'); ?></h5>
								<p><?php 
									printf(
										/* translators: %1$s and %2$s are <code> tags around the word "true" */
										esc_html__('Change the values to %1$strue%2$s to enable debugging:', 'debug-log-viewer'),
										'<code>',
										'</code>'
									);
								?></p>
								<div class="code-examples">
									<code>define( 'WP_DEBUG', true );</code>
									<code>define( 'WP_DEBUG_LOG', true );</code>
								</div>
							</div>
						</div>
						
						<div class="step-item">
							<div class="step-number">4</div>
							<div class="step-content">
								<h5><?php esc_html_e('Add missing configuration (if needed)', 'debug-log-viewer'); ?></h5>
								<p><?php 
									printf(
										/* translators: %1$s and %2$s are <code> tags around the stop editing comment */
										esc_html__('If those lines don\'t exist, add them before the line %1$s/* That\'s all, stop editing! */%2$s:', 'debug-log-viewer'),
										'<code>',
										'</code>'
									);
								?></p>
								<div class="code-examples">
									<code>define( 'WP_DEBUG', true );</code>
									<code>define( 'WP_DEBUG_LOG', true );</code>
									<code>define( 'WP_DEBUG_DISPLAY', false );</code>
								</div>
							</div>
						</div>
						
						<div class="step-item">
							<div class="step-number">5</div>
							<div class="step-content">
								<h5><?php esc_html_e('Save and test', 'debug-log-viewer'); ?></h5>
								<p><?php 
									printf(
										/* translators: %1$s %2$s %3$s %4$s are <code> tags around file names */
										esc_html__('Save the %1$swp-config.php%2$s file. WordPress will now log errors to %3$s/wp-content/debug.log%4$s. Once errors are logged, refresh this page to see them displayed in a beautiful table format.', 'debug-log-viewer'),
										'<code>',
										'</code>',
										'<code>',
										'</code>'
									);
								?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
