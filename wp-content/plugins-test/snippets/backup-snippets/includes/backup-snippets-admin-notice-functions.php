<?php

/**
 * @package Backup_Snippets\Functions
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Admin Notices
if(!function_exists('backup_snippets_admin_notices')) {
    add_action( 'admin_notices', 'backup_snippets_admin_notices' );
    function backup_snippets_admin_notices(){
        if( get_transient( 'code-snippets-is-required' ) ) {
            ?>
            <div class='info notice is-dismissible'>
                <p><?php echo __("Sorry, it looks like you haven't installed or activated the Code Snippets plugin yet.", "backup-snippets"); ?></p>
            </div>
            <?php
            delete_transient( 'code-snippets-is-required' );
        }
    }
}