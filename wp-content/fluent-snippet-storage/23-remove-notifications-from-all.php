<?php
// <Internal Doc Start>
/*
*
* @description: Remove the plugin notifications from all the pages except the main WP Admin dashboard
* @tags: 
* @group: 
* @name: Remove notifications from all admin pages except dashboard
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:57:47
* @updated_at: 2026-02-13 23:57:57
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
add_action( 'current_screen', function() {
    
	$screen = get_current_screen();

	if ( $screen->id !== "dashboard" ) {
        add_action( 'admin_enqueue_scripts', function() {
            
            echo '<style>
                    div.wrap > .update-nag, 
                    div.wrap > .updated, 
                    div.wrap > .error, 
                    div.wrap > .is-dismissible 
                    { 
                        display: none !important; 
                    }
                 </style>';
        
        });
	}
});