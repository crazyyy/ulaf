<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\Plugin\SweepPress\Meta\Options;
use Dev4Press\v54\Core\Quick\Sanitize;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class GetBack extends \Dev4Press\v54\Core\Admin\GetBack {
    protected function process() {
        parent::process();
        if ( $this->a()->panel == 'options' ) {
            if ( $this->a()->subpanel == 'quick' ) {
                $this->quick_task_option();
            } else {
                if ( $this->is_bulk_action() ) {
                    $this->options_bulk();
                } else {
                    if ( $this->is_single_action( 'delete-option' ) ) {
                        $this->option_delete();
                    } else {
                        if ( $this->is_single_action( 'autoload-disable' ) ) {
                            $this->option_autoload( 'disable' );
                        } else {
                            if ( $this->is_single_action( 'autoload-enable' ) ) {
                                $this->option_autoload( 'enable' );
                            }
                        }
                    }
                }
            }
        }
        if ( $this->a()->panel == 'tools' ) {
            if ( $this->is_single_action( 'purge-cache' ) ) {
                $this->tools_purge_cache();
            }
        }
        if ( !empty( $this->a()->panel ) ) {
            if ( $this->is_single_action( 'disable-backups-notice' ) ) {
                $this->disable_backups_notice();
            }
            if ( $this->is_single_action( 'disable-metas-notice' ) ) {
                $this->disable_metas_notice();
            }
            if ( $this->is_single_action( 'clear-estimation-cache' ) ) {
                $this->clear_estimation_cache();
            }
        }
        do_action( 'sweeppress_admin_getback_handler', $this->a()->panel, $this );
    }

    private function quick_task_option() {
        $action = $this->_get( 'single-action' );
        check_admin_referer( 'sweeppress-options-' . $action );
        $message = 'invalid';
        $tasks = Options::instance()->quick_tasks();
        $quick = substr( $action, 6 );
        if ( isset( $tasks[$quick] ) && !empty( $tasks[$quick]['items'] ) ) {
            $ids = $tasks[$quick]['items'];
            $ids = Sanitize::ids_list( $ids );
            if ( !empty( $ids ) ) {
                $records = Options::instance()->delete_ids( $ids, 'QUICK_TASK', '__quick_options' );
                $message = 'option-deleted&records=' . $records;
            }
        }
        wp_redirect( $this->a()->current_url() . '&message=' . $message );
        exit;
    }

    private function options_bulk() {
        check_admin_referer( 'bulk-options' );
        $action = $this->get_bulk_action();
        $message = 'invalid';
        if ( $action != '' ) {
            $ids = Sanitize::_get_ids( 'option' );
            if ( !empty( $ids ) ) {
                if ( $action == 'delete' ) {
                    $records = Options::instance()->delete_ids( $ids, 'MANAGEMENT', '__options_options' );
                    $message = 'option-deleted&records=' . $records;
                } else {
                    if ( $action == 'autoload-disable' ) {
                        $records = Options::instance()->autoload_ids( $ids, 'no' );
                        $message = 'option-autoload&records=' . $records;
                    } else {
                        if ( $action == 'autoload-enable' ) {
                            $records = Options::instance()->autoload_ids( $ids, 'yes' );
                            $message = 'option-autoload&records=' . $records;
                        }
                    }
                }
            }
            wp_redirect( $this->a()->current_url() . '&message=' . $message );
            exit;
        }
    }

    private function option_autoload( $action ) {
        $option = ( isset( $_REQUEST['option_id'] ) ? Sanitize::absint( $_REQUEST['option_id'] ) : '' );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        check_admin_referer( 'sweeppress-autoload-' . $action . '-' . $option );
        $message = 'invalid';
        if ( $option > 0 ) {
            $ids = array($option);
            $autoload = ( $action == 'disable' ? 'no' : 'yes' );
            $records = Options::instance()->autoload_ids( $ids, $autoload );
            $message = 'option-autoload&records=' . $records;
        }
        wp_redirect( $this->a()->current_url( false ) . '&message=' . $message );
        exit;
    }

    private function option_delete() {
        $option = ( isset( $_REQUEST['option_id'] ) ? Sanitize::absint( $_REQUEST['option_id'] ) : '' );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        check_admin_referer( 'sweeppress-delete-option-' . $option );
        $message = 'invalid';
        if ( $option > 0 ) {
            $ids = array($option);
            $records = Options::instance()->delete_ids( $ids, 'MANAGEMENT', '__options_options' );
            $message = 'option-deleted&records=' . $records;
        }
        wp_redirect( $this->a()->current_url( false ) . '&message=' . $message );
        exit;
    }

    private function tools_purge_cache() {
        check_admin_referer( 'sweeppress-purge-cache' );
        sweeppress_settings()->purge_sweeper_cache();
        wp_redirect( $this->a()->current_url( false ) . '&message=cache-purged' );
        exit;
    }

    private function disable_backups_notice() {
        $nonce = ( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '' );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        if ( wp_verify_nonce( $nonce, 'sweeppress-disable-backups-notice' ) ) {
            sweeppress_settings()->set(
                'hide_backup_notices_v6',
                true,
                'settings',
                true
            );
        }
        wp_redirect( $this->a()->current_url() );
        exit;
    }

    private function disable_metas_notice() {
        $nonce = ( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '' );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        if ( wp_verify_nonce( $nonce, 'sweeppress-disable-metas-notice' ) ) {
            sweeppress_settings()->set(
                'hide_meta_notices',
                true,
                'settings',
                true
            );
        }
        wp_redirect( $this->a()->current_url() );
        exit;
    }

    private function clear_estimation_cache() {
        $nonce = ( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '' );
        // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
        if ( wp_verify_nonce( $nonce, 'sweeppress-clear-estimation-cache' ) ) {
            sweeppress_settings()->purge_sweeper_cache();
        }
        wp_redirect( $this->a()->current_url() );
        exit;
    }

}
