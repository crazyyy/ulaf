<?php
/**
 * Template for the Image Sitemap administration page.
 *
 * @package ImgSEO
 * @since   1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify user capability
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'imgseo-ai-alt-text-generator' ) );
}

$imgseo_sitemap_enabled_option_name = 'imgseo_sitemap_enabled';
$imgseo_sitemap_url                 = home_url( '/imgseo-sitemap.xml' );
$imgseo_sitemap_enabled_default     = true; // Default to true for new installations (also handled in activate())
$imgseo_sitemap_enabled             = get_option( $imgseo_sitemap_enabled_option_name, $imgseo_sitemap_enabled_default );
$imgseo_auto_refresh_enabled        = get_option( 'imgseo_sitemap_auto_refresh', false );
$imgseo_auto_refresh_interval       = get_option( 'imgseo_sitemap_auto_refresh_interval', 'daily' );
$imgseo_sitemap_needs_update        = get_option( 'imgseo_sitemap_needs_update', false );

// Auto-regenerate sitemap if needed when visiting the admin page
if ( $imgseo_sitemap_enabled && $imgseo_sitemap_needs_update ) {
	$imgseo_sitemap_generator = ImgSEO_Image_Sitemap_Generator::get_instance();
	$imgseo_generate_result = $imgseo_sitemap_generator->generate_sitemap_file();

	if ( $imgseo_generate_result['status'] === 'success' ) {
		update_option( 'imgseo_sitemap_needs_update', false );
		$imgseo_sitemap_needs_update = false; // Update local variable
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo '<strong>' . esc_html__( 'Sitemap Auto-Updated', 'imgseo-ai-alt-text-generator' ) . '</strong> ';
		echo esc_html__( 'The sitemap has been automatically regenerated to include recent changes.', 'imgseo-ai-alt-text-generator' );
		echo '</p></div>';
	}
}

// Gestione della programmazione/deprogrammazione del cron job
if (isset($_POST['imgseo_sitemap_auto_refresh'])) {
	$imgseo_sitemap_generator = ImgSEO_Image_Sitemap_Generator::get_instance();
	if (sanitize_text_field($_POST['imgseo_sitemap_auto_refresh']) === '1') {
		// Programma il cron job
		$imgseo_interval = sanitize_text_field($_POST['imgseo_sitemap_auto_refresh_interval']);
		$imgseo_sitemap_generator->schedule_auto_refresh($interval);
	} else {
		// Remove the cron job
		$imgseo_sitemap_generator->unschedule_auto_refresh();
	}
}

// Retrieve sitemap information passed from the class
$imgseo_template_data = get_query_var( 'imgseo_sitemap_data', array() );
$imgseo_sitemap_exists = !empty($imgseo_template_data['sitemap_exists']);
$imgseo_sitemap_static_url = !empty($imgseo_template_data['sitemap_url']) ? $imgseo_template_data['sitemap_url'] : $imgseo_sitemap_url;
$imgseo_last_generated = !empty($imgseo_template_data['last_generated']) ? $imgseo_template_data['last_generated'] : 0;

// Handle sitemap settings save
if ( isset( $_POST['imgseo_save_sitemap_settings_nonce'] ) &&
     wp_verify_nonce( sanitize_key( $_POST['imgseo_save_sitemap_settings_nonce'] ), 'imgseo_save_sitemap_settings_action' ) ) {

	if ( isset( $_POST['imgseo_save_sitemap_settings'] ) ) {
		$imgseo_current_sitemap_status_on_db = (bool) get_option( $imgseo_sitemap_enabled_option_name, $imgseo_sitemap_enabled_default );
		$imgseo_new_sitemap_status_from_form = isset( $_POST[ $imgseo_sitemap_enabled_option_name ] );
		$imgseo_auto_refresh_enabled = isset( $_POST['imgseo_sitemap_auto_refresh'] );
		$imgseo_auto_refresh_interval = sanitize_text_field( $_POST['imgseo_sitemap_auto_refresh_interval'] ?? 'daily' );

		// Update options in database
		update_option( $imgseo_sitemap_enabled_option_name, $imgseo_new_sitemap_status_from_form );
		update_option( 'imgseo_sitemap_auto_refresh', $imgseo_auto_refresh_enabled );
		update_option( 'imgseo_sitemap_auto_refresh_interval', $imgseo_auto_refresh_interval );
		$imgseo_sitemap_enabled = $imgseo_new_sitemap_status_from_form; // Update local variable for page rendering

		// Schedule or unschedule auto refresh
		if ( $imgseo_auto_refresh_enabled && $imgseo_new_sitemap_status_from_form ) {
			// Schedule auto refresh
			wp_clear_scheduled_hook( 'imgseo_auto_refresh_sitemap' );
			wp_schedule_event( time(), $imgseo_auto_refresh_interval, 'imgseo_auto_refresh_sitemap' );
		} else {
			// Unschedule auto refresh
			wp_clear_scheduled_hook( 'imgseo_auto_refresh_sitemap' );
		}

		// Determine message and whether to flush
		if ( $imgseo_new_sitemap_status_from_form ) {
			// Sitemap is now enabled
			flush_rewrite_rules(); // Always flush when enabling or saving as enabled
			if ( $imgseo_new_sitemap_status_from_form != $imgseo_current_sitemap_status_on_db ) {
				// Was disabled, now enabled
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Image sitemap has been enabled and rewrite rules flushed.', 'imgseo-ai-alt-text-generator' ) . '</p></div>';
			} else {
				// Was already enabled and saved
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Image sitemap settings saved and rewrite rules flushed.', 'imgseo-ai-alt-text-generator' ) . '</p></div>';
			}
		} else {
			// Sitemap is now disabled
			if ( $imgseo_new_sitemap_status_from_form != $imgseo_current_sitemap_status_on_db ) {
				// Was enabled, now disabled
				flush_rewrite_rules(); // Flush also when disabling to remove the rule
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Image sitemap has been disabled and rewrite rules flushed.', 'imgseo-ai-alt-text-generator' ) . '</p></div>';
			} else {
				// Was already disabled and saved
				echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Image sitemap settings saved. Sitemap remains disabled.', 'imgseo-ai-alt-text-generator' ) . '</p></div>';
			}
		}
	}
}

// Check if manual rewrite rules flush was requested
if ( isset( $_POST['imgseo_force_flush_rules_nonce'] ) &&
     wp_verify_nonce( sanitize_key( $_POST['imgseo_force_flush_rules_nonce'] ), 'imgseo_force_flush_rules_action' ) ) {

    if ( isset( $_POST['imgseo_force_flush_rules'] ) ) {
        // Force manual flush of rewrite rules
        flush_rewrite_rules();
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Rewrite rules have been manually flushed. Try accessing your sitemap now.', 'imgseo-ai-alt-text-generator' ) . '</p></div>';
    }
}
?>
<div class="wrap imgseo-admin-page">
	<h1><?php esc_html_e( 'Image Sitemap', 'imgseo-ai-alt-text-generator' ); ?></h1>

	<p>
		<?php esc_html_e( 'Your image sitemap helps search engines discover and index the images on your site.', 'imgseo-ai-alt-text-generator' ); ?>
	</p>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">

					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Sitemap Settings', 'imgseo-ai-alt-text-generator' ); ?></span></h2>
						<div class="inside">
							<form method="post" action="">
								<?php wp_nonce_field( 'imgseo_save_sitemap_settings_action', 'imgseo_save_sitemap_settings_nonce' ); ?>
								<table class="form-table">
									<tr valign="top">
										<th scope="row"><?php esc_html_e( 'Enable Image Sitemap', 'imgseo-ai-alt-text-generator' ); ?></th>
										<td>
											<label for="<?php echo esc_attr( $imgseo_sitemap_enabled_option_name ); ?>">
												<input type="checkbox" id="<?php echo esc_attr( $imgseo_sitemap_enabled_option_name ); ?>" name="<?php echo esc_attr( $imgseo_sitemap_enabled_option_name ); ?>" value="1" <?php checked( $imgseo_sitemap_enabled, true ); ?> />
												<?php esc_html_e( 'Generate an XML sitemap for images.', 'imgseo-ai-alt-text-generator' ); ?>
											</label>
											<p class="description">
												<?php
												if ( $imgseo_sitemap_enabled ) {
													printf(
												/* translators: %s: sitemap URL */
												esc_html__( 'Image sitemap is currently enabled and available at %s.', 'imgseo-ai-alt-text-generator' ),
												'<a href="' . esc_url( $imgseo_sitemap_static_url ) . '" target="_blank">' . esc_url( $imgseo_sitemap_static_url ) . '</a>'
											);
												} else {
													esc_html_e( 'Image sitemap is currently disabled. Enable to make it available.', 'imgseo-ai-alt-text-generator' );
												}
												?>
											</p>
											<p class="description">
												<?php esc_html_e( 'Submit the sitemap URL to search engines like Google Search Console once enabled.', 'imgseo-ai-alt-text-generator' ); ?>
											</p>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php esc_html_e( 'Auto Refresh', 'imgseo-ai-alt-text-generator' ); ?></th>
										<td>
											<label for="imgseo_sitemap_auto_refresh">
												<input type="checkbox" id="imgseo_sitemap_auto_refresh" name="imgseo_sitemap_auto_refresh" value="1" <?php checked( $imgseo_auto_refresh_enabled, true ); ?> />
												<?php esc_html_e( 'Automatically refresh sitemap periodically.', 'imgseo-ai-alt-text-generator' ); ?>
											</label>
											<p class="description">
												<?php esc_html_e( 'When enabled, the sitemap will be automatically updated at the specified interval.', 'imgseo-ai-alt-text-generator' ); ?>
											</p>
											<select name="imgseo_sitemap_auto_refresh_interval" id="imgseo_sitemap_auto_refresh_interval">
												<option value="hourly" <?php selected( $imgseo_auto_refresh_interval, 'hourly' ); ?>><?php esc_html_e( 'Every Hour', 'imgseo-ai-alt-text-generator' ); ?></option>
												<option value="daily" <?php selected( $imgseo_auto_refresh_interval, 'daily' ); ?>><?php esc_html_e( 'Daily', 'imgseo-ai-alt-text-generator' ); ?></option>
												<option value="weekly" <?php selected( $imgseo_auto_refresh_interval, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'imgseo-ai-alt-text-generator' ); ?></option>
											</select>
										</td>
									</tr>
								</table>
								<p class="submit">
									<button type="submit" name="imgseo_save_sitemap_settings" class="button btn-custom-primary">
										<?php esc_html_e( 'Save Settings', 'imgseo-ai-alt-text-generator' ); ?>
									</button>
									<p class="description" style="padding-top: 15px !important;">
										<?php esc_html_e( 'Saving will also regenerate URL rules if needed.', 'imgseo-ai-alt-text-generator' ); ?>
                                            </p>
								</p>
							</form>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Sitemap URL', 'imgseo-ai-alt-text-generator' ); ?></span></h2>
						<div class="inside">
							<?php if ( $imgseo_sitemap_enabled ) : ?>
								<p>
									<?php esc_html_e( 'Your image sitemap is available at the following URL:', 'imgseo-ai-alt-text-generator' ); ?>
								</p>
								<p>
									<a href="<?php echo esc_url( $imgseo_sitemap_static_url ); ?>" target="_blank"><?php echo esc_url( $imgseo_sitemap_static_url ); ?></a>
								</p>
								<p>
									<em><?php esc_html_e( 'Submit this URL to search engines like Google Search Console.', 'imgseo-ai-alt-text-generator' ); ?></em>
								</p>
							<?php else : ?>
								<p>
									<?php esc_html_e( 'The image sitemap is currently disabled. Enable it in the "Sitemap Settings" section above to get the URL.', 'imgseo-ai-alt-text-generator' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Sitemap Management', 'imgseo-ai-alt-text-generator' ); ?></span></h2>
						<div class="inside">
							<?php if ( $imgseo_sitemap_enabled ) : ?>
								<?php if ( $imgseo_sitemap_needs_update ) : ?>
									<div class="notice notice-info inline">
										<p><strong><?php esc_html_e( 'Note', 'imgseo-ai-alt-text-generator' ); ?></strong></p>
										<p><?php esc_html_e( 'Sitemap will be automatically updated when needed. You can also manually refresh it below.', 'imgseo-ai-alt-text-generator' ); ?></p>
									</div>
								<?php else : ?>
									<div class="notice notice-success inline">
										<p><strong><?php esc_html_e( 'Sitemap Up-to-Date', 'imgseo-ai-alt-text-generator' ); ?></strong></p>
										<p><?php esc_html_e( 'Your sitemap is current and includes all published images.', 'imgseo-ai-alt-text-generator' ); ?></p>
									</div>
								<?php endif; ?>

								<div class="refresh-button" style="margin-bottom: 20px;">
									<?php if ( !$imgseo_sitemap_exists ) : ?>
										<form method="post" action="" style="display: inline-block; margin-right: 10px;">
											<?php wp_nonce_field( 'imgseo_activate_sitemap_action', 'imgseo_activate_sitemap_nonce' ); ?>
											<button type="submit" name="imgseo_activate_sitemap" class="button btn-custom-primary button-large">
												<?php esc_html_e( 'ACTIVATE', 'imgseo-ai-alt-text-generator' ); ?>
											</button>
										</form>
										<p class="description" style="display: inline-block; margin-left: 10px; vertical-align: top; margin-top: 8px;">
											<?php esc_html_e( 'Create the sitemap file and activate URL rules.', 'imgseo-ai-alt-text-generator' ); ?>
										</p>
									<?php else : ?>
										<form method="post" action="" style="display: inline-block; margin-right: 10px;">
											<?php wp_nonce_field( 'imgseo_refresh_sitemap_action', 'imgseo_refresh_sitemap_nonce' ); ?>
											<button type="submit" name="imgseo_refresh_sitemap" class="button btn-custom-primary button-full-width">
												<?php esc_html_e( 'Refresh', 'imgseo-ai-alt-text-generator' ); ?>
											</button>
										</form>
										<p class="description" style="display: inline-block; margin-left: 10px; vertical-align: top; margin-top: 8px;">
											<?php esc_html_e( 'Update sitemap content and refresh URL rules.', 'imgseo-ai-alt-text-generator' ); ?>
										</p>
									<?php endif; ?>
								</div>

								<?php if ( $imgseo_last_generated > 0 ) : ?>
									<p class="description">
										<strong><?php esc_html_e( 'Status:', 'imgseo-ai-alt-text-generator' ); ?></strong>
										<?php
										printf(
											/* translators: %s: formatted date and time */
										esc_html__( 'Last updated: %s', 'imgseo-ai-alt-text-generator' ),
											esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $imgseo_last_generated ) )
									);
										?>
										<?php if ( $imgseo_auto_refresh_enabled ) : ?>
											<br><em><?php
											// translators: %s is the auto-refresh interval (e.g., daily, weekly)
											printf( esc_html__( 'Auto-refresh: %s', 'imgseo-ai-alt-text-generator' ), esc_html( ucfirst( $imgseo_auto_refresh_interval ) ) ); ?></em>
										<?php endif; ?>
									</p>
								<?php endif; ?>

								<div style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
									<p style="margin: 0 0 10px 0; font-weight: 600;">
										<span class="dashicons dashicons-info" style="color: #2271b1;"></span>
										<?php esc_html_e( 'How automatic updates work:', 'imgseo-ai-alt-text-generator' ); ?>
									</p>
									<ul style="margin: 0; padding-left: 20px; font-size: 13px; line-height: 1.6;">
										<li><?php esc_html_e( 'Sitemap regenerates automatically when you visit this page and changes are detected', 'imgseo-ai-alt-text-generator' ); ?></li>
										<li><?php esc_html_e( 'Triggered by: new image uploads, image deletions, or image renaming', 'imgseo-ai-alt-text-generator' ); ?></li>
										<li><?php esc_html_e( 'No manual intervention required - always stays current', 'imgseo-ai-alt-text-generator' ); ?></li>
										<?php if ( $imgseo_auto_refresh_enabled ) : ?>
											<?php /* translators: %s: refresh interval (e.g., "daily", "weekly") */ ?>
											<li><?php printf( esc_html__( 'Scheduled refresh: %s via WordPress cron', 'imgseo-ai-alt-text-generator' ), '<strong>' . esc_html( $imgseo_auto_refresh_interval ) . '</strong>' ); ?></li>
										<?php endif; ?>
									</ul>
								</div>
							<?php else : ?>
								<p>
									<?php esc_html_e( 'To manage the sitemap, first enable the "Enable Image Sitemap" option in the settings section above.', 'imgseo-ai-alt-text-generator' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
