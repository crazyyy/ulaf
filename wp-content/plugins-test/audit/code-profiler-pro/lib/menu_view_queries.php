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
// Display Classes/methods & functions stats.

// Load WP_List_Table class
if (! class_exists( 'WP_List_Table' ) ) {
	 require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require 'class-table-queries.php';
$CPTableQueries = new CodeProfilerPro_Table_Queries( $section, $id, $profile_path );

?>
<div class="colspan-test" id="colspan-test"></div>
<form class="queries" id="profile-form" method="post"<?php echo $q ?> onsubmit="return cpjspro_search_query();">
	<?php
	// Search query
	if (! empty( $_REQUEST['s'] ) ) {
		echo '<span class="subtitle">';
		printf( esc_html__( 'Filter: %s', 'code-profiler-pro' ), '<code>' . esc_html( $_REQUEST['s'] ) . '</code>' );
		echo '</span>';
	}
	$CPTableQueries->prepare_items();
	$CPTableQueries->search_box( esc_attr__('Filter', 'code-profiler-pro'), 'search_id' );
	$CPTableQueries->display();
	?>
</form>
<?php

// We don't offer to export CSV if there's no data to export
if ( empty( $CPTableQueries->is_empty ) ) {
	$save_csv = 1;
}
$type   = 'queries';
$hidden = $CPTableQueries->hidden;
// =====================================================================
// EOF
