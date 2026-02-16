<?php
// <Internal Doc Start>
/*
*
* @description: This is used to change the URL of the logo on the WordPress login page. By default, this logo links to the WordPress site.


* @tags: 
* @group: 
* @name: Replace the login header URL
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:51:46
* @updated_at: 2026-02-13 23:51:46
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
/**
 * Replaces the login header logo URL
 *
 * @param $url
 */

add_filter( 'login_headerurl', function ( $url ) {
    $url = home_url( '/' );
    return $url;
} );

