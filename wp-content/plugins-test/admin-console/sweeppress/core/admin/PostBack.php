<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\Plugin\SweepPress\Pro\Admin\PostBack as ProPostBack;
use Dev4Press\v54\Core\Quick\WPR;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class PostBack extends \Dev4Press\v54\Core\Admin\PostBack {
    protected function process() {
        parent::process();
        do_action( 'sweeppress_admin_postback_handler', $this->p() );
    }

    protected function tools() {
        parent::tools();
        if ( $this->a()->subpanel == 'purge' ) {
            $this->purge();
        }
    }

    protected function remove() {
        $data = ( isset( $_POST['sweeppress-tools'] ) ? sweeppress_sanitize_keys_based_array( $_POST['sweeppress-tools'] ) : array() );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        $remove = ( isset( $data['remove'] ) ? (array) $data['remove'] : array() );
        $message = 'nothing-removed';
        if ( !empty( $remove ) ) {
            $groups = array(
                'settings',
                'sweepers',
                'statistics',
                'scheduler',
                'cache',
                'storage'
            );
            foreach ( $groups as $group ) {
                if ( isset( $remove[$group] ) && $remove[$group] == 'on' ) {
                    $this->a()->settings()->remove_plugin_settings_by_group( $group );
                }
            }
            if ( isset( $remove['storage'] ) ) {
                $settings = array(
                    'cron_detection',
                    'scan_plugins',
                    'scan_themes',
                    'monitor_meta_get',
                    'monitor_meta_update',
                    'monitor_option_get',
                    'monitor_option_update'
                );
                foreach ( $settings as $option ) {
                    if ( isset( $remove['storage'][$option] ) && $remove['storage'][$option] == 'on' ) {
                        $this->a()->settings()->set( $option, array(), 'storage' );
                    }
                }
                $this->a()->settings()->save( 'storage' );
            }
            if ( isset( $remove['scheduler'] ) && $remove['scheduler'] == 'on' ) {
                WPR::remove_cron( 'sweeppress_sweep_job' );
            }
            if ( isset( $remove['log'] ) && $remove['log'] == 'on' ) {
                $log = sweeppress_log_path();
                if ( file_exists( $log ) ) {
                    wp_delete_file( $log );
                }
            }
            if ( isset( $remove['disable'] ) && $remove['disable'] == 'on' ) {
                if ( isset( $remove['settings'] ) && $remove['settings'] == 'on' ) {
                    $this->a()->settings()->remove_plugin_settings_by_group( 'core' );
                }
                sweeppress()->deactivate();
                wp_redirect( admin_url( 'plugins.php' ) );
                exit;
            }
            $message = 'removed';
        }
        wp_redirect( $this->a()->current_url() . '&message=' . $message );
        exit;
    }

    private function purge() {
        $data = ( isset( $_POST['sweeppress-tools'] ) ? sweeppress_sanitize_keys_based_array( $_POST['sweeppress-tools'] ) : array() );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        $remove = ( isset( $data['purge'] ) ? (array) $data['purge'] : array() );
        $message = ( empty( $remove ) ? 'nothing-removed' : 'removed' );
        if ( isset( $remove['cache'] ) ) {
            if ( isset( $remove['cache']['sweepers'] ) && $remove['cache']['sweepers'] == 'on' ) {
                sweeppress_settings()->purge_sweeper_cache();
            }
        }
        if ( isset( $remove['storage'] ) ) {
            $settings = array(
                'cron_detection',
                'scan_plugins',
                'scan_themes',
                'monitor_meta_get',
                'monitor_meta_update',
                'monitor_option_get',
                'monitor_option_update'
            );
            foreach ( $settings as $option ) {
                if ( isset( $remove['storage'][$option] ) && $remove['storage'][$option] == 'on' ) {
                    $this->a()->settings()->set( $option, array(), 'storage' );
                }
            }
            $this->a()->settings()->save( 'storage' );
        }
        wp_redirect( $this->a()->current_url() . '&message=' . $message );
        exit;
    }

}
