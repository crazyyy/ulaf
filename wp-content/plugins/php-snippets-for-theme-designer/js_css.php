<?php

/**
 * Author: Fumito MIZUNO
 * License: GPL ver.2 or later
 */


function psftd_add_page() {
    $page = add_theme_page( __( 'Theme JavaScript/CSS Info', 'psftd' ), __( 'Theme JS/CSS', 'psftd' ), 'manage_options', 'jscss', 'psftd_do_page' );
}
add_action( 'admin_menu', 'psftd_add_page' );

function psftd_do_page() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'psftd' ) );
    }
    $theme = 'parent';
    if (is_child_theme()) {
        $theme = 'child';
    }
?>
    <div class="wrap">
        <h3><?php _e( 'JavaScript', 'psftd' );?></h3>
<?php
    $obj = new psftd_get_js($theme);
    $jsdata .= $obj->wrap_func();
    echo psftd_readonly_textarea($jsdata, 4+$obj->get_filelist_length(), 100 );
?>
        <h3><?php _e( 'CSS', 'psftd' );?></h3>
<?php
    $obj = new psftd_get_css($theme);
    $cssdata .= $obj->wrap_func();
    echo psftd_readonly_textarea($cssdata, 4+$obj->get_filelist_length(), 100 );
?>    </div>
<?php
}
