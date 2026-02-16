<?php
// <Internal Doc Start>
/*
*
* @description: Change icon, link or text of the link on the sign-up page
* @tags: Login Page
* @group: 
* @name: Customize Login Page
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:45:35
* @updated_at: 2026-02-13 23:45:35
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
// Change the Logo
add_action( 'login_head', function () {
	echo '<style type="text/css">h1 a { background: url(' . get_bloginfo( 'template_directory' ) . '/images/logo-login.gif) 50% 50% no-repeat !important; }</style>';
} );

// Change the URL
add_filter( 'login_headerurl', function () {
	return site_url();
} );

// Change the title
add_filter( 'login_headertext', function () {
	return get_option( 'blogname' );
} );