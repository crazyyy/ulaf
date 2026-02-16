<?php
// <Internal Doc Start>
/*
*
* @description: With the following snippet, you can remove the WordPress dashboard news feed, and replace it with any RSS feed.


* @tags: 
* @group: 
* @name: Replace the WordPress dashboard news feed with another RSS feed
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:52:47
* @updated_at: 2026-02-13 23:52:47
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: all
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
add_action('wp_dashboard_setup', 'custom_dashboard_widgets');

function custom_dashboard_widgets() {
     global $wp_meta_boxes;
     // remove unnecessary widgets
     // var_dump( $wp_meta_boxes['dashboard'] ); // use to get all the widget IDs
     unset(
          $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'],
          $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'],
          $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']
     );
     // add a custom dashboard widget
     wp_add_dashboard_widget( 
         'dashboard_custom_feed', 
         'News from 10up', 
         'dashboard_custom_feed_output' 
         ); //add new RSS feed output
}

function dashboard_custom_feed_output() {
     echo '<div class="rss-widget">';
     wp_widget_rss_output(array(
          'url' => 'http://www.get10up.com/feed',
          'title' => 'What\'s up at 10up',
          'items' => 2,
          'show_summary' => 1,
          'show_author' => 0,
          'show_date' => 1,
     ));
     echo "</div>";
}