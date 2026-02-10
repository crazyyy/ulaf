<?php

namespace SolidWP\Mail\Admin;

use SolidWP\Mail\Migration\MigrationVer210;

/**
 * Class Notice
 *
 * Handles the display and dismissal of admin notices for the Solid Mail plugin.
 *
 * @package SolidWP\Mail\Admin
 */
class Notice {

	/**
	 * Meta key to store the dismissed state of the migration error notice.
	 *
	 * @var string
	 */
	private string $migration_error_flag = 'solid_mail_notice_migration_error_dismissed';

	/**
	 * Meta key to store the dismissed state of the solid 2.0.0 to 2.1.0 error.
	 *
	 * @var string
	 */
	private string $migration_200_211_error_flag = 'solid_mail_notice_migration_200_211_error_dismissed';

	/**
	 * Handles the AJAX request to dismiss the admin notice.
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		$nonce = sanitize_text_field( $_POST['_wpnonce'] ?? '' );

		// Verify nonce and return an error if verification fails
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'dismiss_solid_mail_notice' ) ) {
			wp_send_json_error();
		}

		$flag = sanitize_text_field( $_POST['flag'] ?? '' );

		// make sure the flag is allowed before saving.
		if ( ! empty( $flag )
			&& in_array(
				$flag,
				[
					$this->new_ownership_flag,
					$this->migration_error_flag,
					$this->migration_200_211_error_flag,
				],
				true
			)
		) {
			update_user_meta( get_current_user_id(), $flag, true );
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Displays the admin notice for the migration issue in version 2.1.0.
	 *
	 * @return void
	 */
	public function maybe_display_notice_200_211_error() {
		// Only admin should see this.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get the stored migration error message.
		$flag = get_option( MigrationVer210::FLAG_NAME, '' );

		// Check if the user has dismissed the notice or if there is no error.
		if ( 'yes' !== $flag || get_user_meta( get_current_user_id(), $this->migration_200_211_error_flag, true ) ) {
			return;
		}

		$nonce = wp_create_nonce( 'dismiss_solid_mail_notice' );
		?>
		<div class="notice notice-error is-dismissible solid-mail-migration-error-notice">
			<p>
				<?php
				printf(
						/* translators: %s: URL to the legacy settings page */
					__( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'There was an issue in version 2.1.0 which affected a small subset of users by erroneously deactivating the Active Connection toggle on the Email Connections Page at Solid Mail → Solid Mail. Please ensure that your active connection is still correctly enabled. <a href="%s">Verify your settings</a>',
						'LION'
					), // phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscaped
					esc_url( admin_url( 'admin.php?page=solidwp-mail#/providers/edit/legacy_smtp_id' ) )
				)
				?>
			</p>
		</div>
		<script type="text/javascript">
			( function ( $ ) {
				$( document ).on( 'click', '.solid-mail-migration-error-notice .notice-dismiss', function () {
					$.post( ajaxurl, {
						action: 'dismiss_solid_mail_notice',
						_wpnonce: '<?= esc_html( $nonce ); ?>',
						flag: '<?= esc_html( $this->migration_200_211_error_flag ); ?>'
					} );
				} );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * Displays the admin notice if there's a migration error.
	 *
	 * Checks if the migration error notice has been dismissed by the current user. If not,
	 * it outputs the HTML for the notice and a script to handle its dismissal via AJAX.
	 *
	 * @return void
	 */
	public function display_notice_migration_error() {
		// Only admin should see this.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get the stored migration error message.
		$migration_error = get_option( 'solid_mail_migration_error', '' );

		// Check if the user has dismissed the notice or if there is no error.
		if ( empty( $migration_error ) || get_user_meta( get_current_user_id(), $this->migration_error_flag, true ) ) {
			return;
		}

		$nonce = wp_create_nonce( 'dismiss_solid_mail_notice' );
		?>
		<div class="notice notice-error is-dismissible solid-mail-migration-error-notice">
			<p><?php esc_html_e( 'There was an error during the migration process: ', 'LION' ); ?>
				<?php echo esc_html( $migration_error ); ?></p>
		</div>
		<script type="text/javascript">
			( function ( $ ) {
				$( document ).on( 'click', '.solid-mail-migration-error-notice .notice-dismiss', function () {
					$.post( ajaxurl, {
						action: 'dismiss_solid_mail_notice',
						_wpnonce: '<?= esc_html( $nonce ); ?>',
						flag: '<?= esc_html( $this->migration_error_flag ); ?>'
					} );
				} );
			} )( jQuery );
		</script>
		<?php
	}
}