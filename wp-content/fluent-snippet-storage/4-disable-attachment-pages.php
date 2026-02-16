<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: Attachment
* @group: SEO
* @name: Disable Attachment Pages
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:00:09
* @updated_at: 2026-02-13 23:00:11
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: frontend
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
add_action(
	'template_redirect',
	function () {
		global $post;
		if ( ! is_attachment() || ! isset( $post->post_parent ) || ! is_numeric( $post->post_parent ) ) {
			return;
		}

		// Does the attachment have a parent post?
		// If the post is trashed, fallback to redirect to homepage.
		if ( 0 !== $post->post_parent && 'trash' !== get_post_status( $post->post_parent ) ) {
			// Redirect to the attachment parent.
			wp_safe_redirect( get_permalink( $post->post_parent ), 301 );
		} else {
			// For attachment without a parent redirect to homepage.
			wp_safe_redirect( get_bloginfo( 'wpurl' ), 302 );
		}
		exit;
	},
	1
);