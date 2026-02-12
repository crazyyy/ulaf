<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

// =====================================================================
// License page.

if (! empty( $_POST['save_license'] ) || ! empty( $_POST['check_license'] ) ) {

	// Verify the security nonce
	if ( empty( $_POST['cp_nonce'] ) ||
		! wp_verify_nonce($_POST['cp_nonce'], 'code_profiler_pro_license') ) {

		wp_nonce_ays('code_profiler_pro_license');
	}

	$res = code_profiler_pro_check_license();
	if (! empty( $res['error'] ) ) {
		printf(
			CODE_PROFILER_PRO_ERROR_NOTICE,
			$res['error']
		);
	} else {
		printf(
			CODE_PROFILER_PRO_UPDATE_NOTICE,
			$res['message']
		);
	}
	// Refresh options
	$cp_options = get_option( 'code-profiler-pro' );
}

$is_license = code_profiler_pro_is_license();

echo code_profiler_pro_display_tabs( 7 );
echo '<br />';

// No license yet
if ( $is_license == 0 ) {
	code_profiler_pro_license_input_form();

} else {
	code_profiler_pro_show_license( $is_license );
}


// =====================================================================
// Display the license, if any.

function code_profiler_pro_show_license( $is_license ) {

	$cp_options = get_option( 'code-profiler-pro' );

	// License will expire in less than 30 days
	if ( $is_license == 2 ) {
		echo '<h3 style="weight:bold;color:red">'.
		esc_html__('Your Code Profilder Pro license will expire soon.', 'code-profiler-pro').
		' '.
		sprintf(
			/* Translators: the two %s are the '<a href="....">' and '</a>' anchors */
			esc_html__('Please %sclick here%s to renew it.', 'code-profiler-pro'),
			'<a href="https://code-profiler.com/" target="_blank" rel="noopener noreferrer">', '</a>'
		). '</h3>';

	// License has expired
	} elseif ( $is_license == 3 ) {
		echo '<h3 style="weight:bold;color:red">'.
			esc_html__('Your Code Profilder Pro license has expired.', 'code-profiler-pro').
			' '.
			sprintf(
				/* Translators: the two %s are the '<a href="....">' and '</a>' anchors */
				esc_html__('Please %sclick here%s to renew it.', 'code-profiler-pro'),
				'<a href="https://code-profiler.com/" target="_blank" rel="noopener noreferrer">', '</a>'
		). '</h3>';


	// Invalid expiry date
	} elseif ( $is_license == 4 ) {
		echo '<h3 style="weight:bold;color:red">'.
		sprintf(
			esc_html__('Missing license expiration date. Please click the "%s" button below to fix the problem.', 'code-profiler-pro'),
			esc_html__('Check License', 'code-profiler-pro')
		). '</h3>';
		$cp_options['expire'] = '0000-00-00';

	} else {
		echo '<h3>'. esc_html__('Code Profiler Pro license', 'code-profiler-pro') .'</h3>';
	}

	?>
	<form method="post">
		<?php wp_nonce_field('code_profiler_pro_license', 'cp_nonce', 0); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e('License Number', 'code-profiler-pro') ?></th>
				<td>
					<p><input type="text" name="cppro-license" value="<?php esc_attr_e( $cp_options['license'] ) ?>" class="large-text" /></p>
					<p><input class="button-secondary" type="submit" name="check_license" value="<?php esc_attr_e('Check License', 'code-profiler-pro') ?>" /></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('Expiration date', 'code-profiler-pro') ?></th>
				<td><p><?php
				echo esc_html(
					date_i18n(
						get_option( 'date_format' ),
						strtotime(  $cp_options['expire'] )
					)
				) ?></p>
				</td>
			</tr>
		</table>
	</form>

	<div id="cp-footer-help" style="display:none">
	<?php
		$type = 'license';
		include 'help.php';
	?>
	</div>
	<br />
	<div class="alignleft actions bulkactions">
		<input type="button" class="button-primary" style="min-width:100px" value="<?php esc_attr_e('Help', 'code-profiler-pro' )?>" onclick="jQuery('#cp-footer-help').slideToggle(500);"/>
	</div>

<?php
}

// =====================================================================
// Display the license registration form.

function code_profiler_pro_license_input_form() {
	?>
	<p><?php esc_html_e('Enter your Code Profiler Pro license:', 'code-profiler-pro' ) ?></p>
	<form method="post">
		<?php wp_nonce_field('code_profiler_pro_license', 'cp_nonce', 0); ?>
		<input type="text" size="50" name="cppro-license" maxlength="64" value="" required autocomplete="off" autocapitalize="none" />&nbsp;&nbsp;<input type="submit" class="button-primary" name="save_license" value="<?php esc_attr_e('Save License', 'code-profiler-pro') ?>" />
	</form>
	<br />

<?php
}

// =====================================================================
// EOF
