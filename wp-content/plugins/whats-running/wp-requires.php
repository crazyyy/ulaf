<?php
/*
Plugin Name: What's running
Version: 1.9.1
Description: Lists WordPress require() calls mainly for plugin code refactoring.
Plugin URI: https://wordpress.org/plugins/whats-running/
Author: Viktor Szépe
License: GNU General Public License (GPL) version 2
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: whats_running_direct_access '
        . addslashes( @$_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

add_action( 'shutdown', 'whats_running' );

function whats_running() {

    // DOING_AJAX is defined late on file uploads (async-upload.php).
    if ( ( defined('DOING_AJAX') && DOING_AJAX )
        || ( defined('DOING_CRON') && DOING_CRON )
        || ( @$_SERVER['SCRIPT_FILENAME'] === ABSPATH . 'wp-admin/async-upload.php' )
        || is_robots()
    ) {
        return;
    }

    // Do run on IFRAME_REQUEST-s.

    $abslen = strlen( ABSPATH );
    $total_size = 0;
    $highlight = defined( 'WHATS_RUNNING_HIGHLIGHT' ) ? WHATS_RUNNING_HIGHLIGHT : false;

    if ( function_exists( 'opcache_get_status' ) ) {
        $sizes = opcache_get_status( true );
    } else {
        $sizes = null;
    }

    print '<div id="whats-running" style="clear:both;"/><hr/>
        <pre style="display:block !important;padding-left:160px;font:14px/140% monospace;background:#FFF;">
        <ol style="list-style-position:inside;">';

    foreach ( get_included_files() as $i => $path ) {
        if ( $sizes
            && isset( $sizes['scripts'][ $path ] )
            && isset( $sizes['scripts'][ $path ]['memory_consumption'] )
        ) {
            $size = $sizes['scripts'][ $path ]['memory_consumption'];
            $size_pixel_factor = 1024;
            $size_bar_color = '#880088';
        } else {
            $size = filesize( $path );
            $size_pixel_factor = 512;
            $size_bar_color = '#FF00FF';
        }

        $total_size += $size;
        $color = 'red';

        if ( $highlight && false !== strpos( $path, $highlight ) ) {
            $background = 'background:yellow;';
        } else {
            $background = '';
        }

        if ( 0 === strpos( $path, WP_PLUGIN_DIR ) ) {
            $color = 'blue';
        } elseif ( 0 === strpos( $path, WP_CONTENT_DIR . '/themes' ) ) {
            $color = 'orange';
        }
        // Truncate path only after WP_CONTENT_DIR check.
        if ( 0 === strpos( $path, ABSPATH ) ) {
            $path = substr( $path, $abslen );
        }
        if ( 0 === strpos( $path, WPINC ) ) {
            $color = 'green';
        } elseif ( 0 === strpos($path, 'wp-admin' ) ) {
            $color = 'grey';
        }

        printf( '<li style="color:%s;%s">%s<span title="%s kB" style="padding-left:%spx;display:inline-block;
            background-color:%s;border-radius:5px;height:5px;margin-left:5px;"></span></li>',
            $color,
            $background,
            esc_html( $path ),
            number_format( $size / 1024, 0 ),
            round( $size / $size_pixel_factor + 1 ),
            $size_bar_color
        );
    }

    // Total
    printf( '<li style="color:black;font-weight:bold;list-style:none;">Total: %s bytes</li>',
        number_format( $total_size, 0, '.', ' ' ) );
    print '</ol></pre></div>';
}
