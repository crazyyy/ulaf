<?php

namespace DebugLogViewer\Admin\Controllers;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}
class FreemiusController {
    public function init() {
        if ( !function_exists( 'dbg_lv' ) ) {
            function dbg_lv() {
                global $dbg_lv;
                if ( isset( $dbg_lv ) ) {
                    return $dbg_lv;
                }
                FreemiusController::include_sdk();
                $dbg_lv = fs_dynamic_init( [
                    'id'             => '17350',
                    'slug'           => 'debug-log-viewer',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_d456c712f16510d920c9f4ba4880a',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => [
                        'slug'       => 'debug-log-viewer',
                        'first-path' => 'admin.php?page=debug-log-viewer',
                    ],
                    'is_live'        => true,
                ] );
                return $dbg_lv;
            }

            // Initialize Freemius.
            dbg_lv();
            // Signal that the SDK was initiated.
            do_action( 'dbg_lv_loaded' );
        }
    }

    public static function include_sdk() {
        $freemius_path = __DIR__ . '/../../vendor/freemius/wordpress-sdk/start.php';
        if ( file_exists( $freemius_path ) ) {
            require_once $freemius_path;
        } else {
            $fallback_path = __DIR__ . '/../../freemius/wordpress-sdk/start.php';
            if ( file_exists( $fallback_path ) ) {
                require_once $fallback_path;
            } else {
                // Handle the error if neither path exists.
                wp_die( 'Freemius SDK not found. Please check the installation.' );
            }
        }
    }

}
