<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit();
}
//delete table wpvb_imlt_speed_tests
global $wpdb;
$imlt_table_name = $wpdb->prefix . "imlt_speed_tests";
$imlt_sql = "DROP TABLE IF EXISTS $imlt_table_name;";
$wpdb->query($imlt_sql);

// Delete custom options

$imlt_options = array('imtl_test_speed_enabled', 'imlt_track_user' );

foreach($imlt_options as $imlt_value) {
  delete_option($imlt_value);
}




?>
