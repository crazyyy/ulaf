<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables, not globals
defined( 'ABSPATH' ) || exit;

use AdminEase\Plugin;

$settings                               = Plugin::get_settings( 'security' );
$password_protect_site_auto_load_log = $settings['password_protect_site_auto_load_log'] ?? false;
?>
<div class="col-xs-12 p-t-20">
	<div id="password-protection-log-container">
		<p><strong><?php esc_html_e( 'Password Protection Access Log', 'adminease' ); ?></strong></p>

		<div class="actions m-b-10">
			<button type="button" class="button button-secondary button-small" id="refresh-password-protection-log">
				<?php esc_html_e( 'Refresh Log', 'adminease' ); ?>
			</button>

			<button type="button" class="button button-secondary button-small inline-flex justify-content-center align-items-center gap-5" id="download-password-protection-log">
				<?php esc_html_e( 'Download CSV', 'adminease' ); ?> <span class="dashicons dashicons-download"></span>
			</button>
		</div>

		<!-- Table wrapper with max height for scrolling -->
		<div class="password-protection-log-table-wrapper m-t-10" style="max-height: 600px; overflow-y: auto; border: 1px solid #ddd;">
			<table id="password-protection-log-table" class="adminease-table" style="margin: 0;">
				<thead style="position: sticky; top: 0; background: #f0f0f1; z-index: 1;">
				<tr>
					<th style="padding: 8px;"><?php esc_html_e( 'Timestamp', 'adminease' ); ?></th>
					<th style="padding: 8px;"><?php esc_html_e( 'IP Address', 'adminease' ); ?></th>
					<th style="padding: 8px;"><?php esc_html_e( 'Status', 'adminease' ); ?></th>
					<th style="padding: 8px;"><?php esc_html_e( 'User Agent', 'adminease' ); ?></th>
					<th style="padding: 8px;"><?php esc_html_e( 'Password Hash', 'adminease' ); ?></th>
				</tr>
				</thead>
				<tbody id="password-protection-log-table-body">
				<tr>
					<td colspan="5" style="text-align: center; padding: 20px;">
						<?php
						if( $password_protect_site_auto_load_log ) {
							esc_html_e( 'Loading...', 'adminease' );
						} else {
							esc_html_e( 'Click "Refresh Log" to view access attempts.', 'adminease' );
						}
						?>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<!-- Log count -->
		<div id="password-protection-log-count" class="m-t-10" style="color: #666; font-size: 12px;"></div>

		<!-- Hidden input to store auto-load setting -->
		<input type="hidden" id="password-protect-site-auto-load-log" value="<?php echo esc_attr( $password_protect_site_auto_load_log ? '1' : '0' ); ?>">
	</div>
</div>