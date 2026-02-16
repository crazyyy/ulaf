<?php
// <Internal Doc Start>
/*
*
* @description: Add a link to the plugins page to the admin top bar.
* @tags: 
* @group: Admin
* @name: Add "Plugins" item to the admin top bar.
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:56:51
* @updated_at: 2026-02-13 23:56:59
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: backend
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
add_action( 'admin_bar_menu', function($wp_admin_bar) {
    
    $wp_admin_bar->add_node( array(
        'id' => 'plugins',
        'title' => 'Plugins',
        'href' => esc_url( admin_url( 'plugins.php' ) ),
        'meta' => false
    ));
    
}, 999);