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

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================
// Display Profiles List tab.

echo code_profiler_pro_display_tabs( 2 );

// Look for actions
$section = 0;
$section_list = [
	1 => esc_html__('Plugins & Theme Performance', 'code-profiler-pro'),
	2 => esc_html__('Scripts Performance', 'code-profiler-pro'),
	3 => esc_html__('Methods & Functions Performance', 'code-profiler-pro'),
	4 => esc_html__('Database Queries Performance', 'code-profiler-pro'),
	5 => esc_html__('Remote Connections', 'code-profiler-pro'),
	6 => esc_html__('File I/O List', 'code-profiler-pro'),
	7 => esc_html__('File I/O Statistics', 'code-profiler-pro'),
	8 => esc_html__('Disk I/O Statistics', 'code-profiler-pro')
];
if (! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'view_profile' &&
	! empty( $_REQUEST['section'] ) && ! empty( $section_list[ $_REQUEST['section'] ] ) ) {
	// Make sure the profile exists and get its full path
	$profile_path = code_profiler_pro_get_profile_path( $_REQUEST['id'] );
	if ( $profile_path !== false ) {
		$section = (int) $_REQUEST['section'];
		$id = $_REQUEST['id'];
	}
}

// Search query: get rid of slashes added by WordPress
if (! empty( $_REQUEST['s'] ) ) {
	$_REQUEST['s'] = stripslashes( $_REQUEST['s'] );
}
$q = '';
if (! empty( $_SERVER['QUERY_STRING'] ) ) {
	$q = ' action="?'. esc_attr( $_SERVER['QUERY_STRING'] ) .'"';
}

if (! empty( $section ) ) {
	// View selected profile
	require 'menu_view_profile.php';

} else {
	// Show profiles list table

	// Load WP_List_Table class
	if (! class_exists('WP_List_Table') ) {
		 require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}
	require 'class-table-profiles.php';
	$CPTableProfiles = new CodeProfilerPro_Table_Profiles();

	// Profile(s) deletion
	if ( isset( $_REQUEST['action'] ) &&
		$_REQUEST['action'] == 'delete_profiles' &&
		! empty( $_REQUEST['profiles'] )
	) {
		// Verify security nonce
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-'. $CPTableProfiles->_args['plural'] ) === false ) {
			wp_nonce_ays('bulk-' . $CPTableProfiles->_args['plural'] );
		}

		$error = $CPTableProfiles->delete_profiles( $_REQUEST['profiles'] );
		if ( $error === true ) {
			printf(
				CODE_PROFILER_PRO_ERROR_NOTICE,
				esc_html__('Some errors occured when trying to delete the selected profiles.', 'code-profiler-pro')
			);
		} else {
			printf(
				CODE_PROFILER_PRO_UPDATE_NOTICE,
				esc_html__('The selected profiles were deleted.', 'code-profiler-pro')
			);
		}
	}
	?>
	<br />
	<form id="profile-form" method="post"<?php echo $q ?> onsubmit="return cpjspro_search_query();">
	<?php
		// Search query
		if (! empty( $_REQUEST['s'] ) ) {
			echo '<span class="subtitle">';
			printf( esc_html__('Filter: %s'), '<code>' . esc_html( $_REQUEST['s'] ) . '</code>');
			echo '</span>';
		}
		$CPTableProfiles->prepare_items();
		$CPTableProfiles->search_box( esc_attr__('Filter', 'code-profiler-pro'), 'search_id');
		$CPTableProfiles->display();
		?>
	</form>
	<!-- Help -->
	<div class="tablenav bottom">
		<div id="cp-footer-help" style="display:none">
			<?php
			 $type = 'profiles_list';
			 include 'help.php';
			 ?>
			<br />
		</div>

		<div class="alignleft actions bulkactions">
			<input type="button" class="button-primary" style="min-width:100px" value="<?php esc_attr_e('Help', 'code-profiler-pro')?>" onclick="jQuery('#cp-footer-help').slideToggle(500);"/>
		</div>

		<?php
		// Offer to download as CSV only if there's at least one result
		if (! $CPTableProfiles->is_empty ) {
		?>
			<div class="tablenav-pages">
				<a type="button" class="button-secondary" title="<?php esc_attr_e('Click to download as a CVS file', 'code-profiler-pro')?>" onclick='window.location="?page=code-profiler-pro&cp-download-csv=0000000000.0&cp-type=profiles&_wpnonce=<?php echo wp_create_nonce('code-profiler-download-csv') ?><?php
			if (! empty( $_REQUEST['s'] ) ) {
				echo '&s='. esc_attr( $_REQUEST['s'] );
			}
			?>"'/><span class="dashicons dashicons-download" style="display: inline-block;vertical-align: middle;"></span><?php esc_attr_e('Download as a CSV file', 'code-profiler-pro')?></a>
			</div>
		<?php
		}
		?>
	</div>
<?php
}
// =====================================================================
// EOF
