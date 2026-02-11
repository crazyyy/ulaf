<?php

namespace Dev4Press\Plugin\SweepPress\Basic;

use Dev4Press\Plugin\SweepPress\Expand\REST;
use Dev4Press\Plugin\SweepPress\Meta\Tracker;
use Dev4Press\Plugin\SweepPress\Pro\Expand\Backup;
use Dev4Press\Plugin\SweepPress\Pro\Expand\CRON;
use Dev4Press\Plugin\SweepPress\Pro\Expand\Monitor;
use Dev4Press\Plugin\SweepPress\Pro\Expand\Scheduler;
use Dev4Press\v54\Core\Plugins\Core;
use Dev4Press\v54\Core\Quick\BBP;
use Dev4Press\v54\Core\Quick\Sanitize;
use Dev4Press\v54\Core\Quick\WPR;
use Dev4Press\v54\Core\Scope;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Plugin extends Core {
    public string $plugin = 'sweeppress';

    public string $svg_icon = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjE2IiBoZWlnaHQ9IjE2IiB2aWV3Qm94PSIwIDAgMTYgMTYiPgo8cGF0aCBmaWxsPSJibGFjayIgZD0iTTguNzUgOS4zMTNjMC0wLjE3MyAwLjE0LTAuMzEzIDAuMzEyLTAuMzEzaDMuMzc2YzAuMTcyIDAgMC4zMTIgMC4xNCAwLjMxMiAwLjMxM3YwLjkzN2gtNHYtMC45Mzd6Ij48L3BhdGg+CjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNNy41IDEwLjc1YzAgMC0wLjEyNyAxLjAwOC0wLjI1IDEuNS0wLjE2NyAwLjY2Ny0wLjc1IDIuNS0wLjc1IDIuNSAwIDAuMjc2IDAuMjI0IDAuNSAwLjUgMC41aDcuNWMwLjI3NiAwIDAuNS0wLjIyNCAwLjUtMC41IDAgMC0wLjU4My0xLjgzMy0wLjc1LTIuNS0wLjEyMy0wLjQ5Mi0wLjI1LTEuNS0wLjI1LTEuNSAwLTAuMjc2LTAuNDc0LTAuNS0wLjc1LTAuNWgtNWMtMC4yNzYgMC0wLjc1IDAuMjI0LTAuNzUgMC41ek0xMy4zOTMgMTAuOTI5YzAuMDM3IDAuMjc3IDAuMTQ2IDEuMDU0IDAuMjUxIDEuNDczIDAuMTI2IDAuNTAzIDAuNDg1IDEuNjYzIDAuNjYxIDIuMjIzaC03LjExYzAuMTc2LTAuNTYgMC41MzUtMS43MiAwLjY2MS0yLjIyMyAwLjEwNS0wLjQxOSAwLjIxNC0xLjE5NiAwLjI1MS0xLjQ3MyAwLjA0NC0wLjAyNCAwLjEwOC0wLjA1NCAwLjE0My0wLjA1NGg1YzAuMDM1IDAgMC4wOTkgMC4wMzAgMC4xNDMgMC4wNTR6Ij48L3BhdGg+CjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNMTAuNDM3IDEyLjI1bDAuMDAxIDIuNWMtMC4wMDEgMC4xNzIgMC4xNCAwLjMxMiAwLjMxMiAwLjMxMnMwLjMxMi0wLjE0IDAuMzEzLTAuMzEybC0wLjAwMS0yLjVjMC0wLjE3Mi0wLjE0LTAuMzEzLTAuMzEyLTAuMzEyLTAuMTcyIDAtMC4zMTMgMC4xNC0wLjMxMyAwLjMxMnoiPjwvcGF0aD4KPHBhdGggZmlsbD0iYmxhY2siIGQ9Ik04Ljk0NCAxMi4xODlsLTAuNSAyLjVjLTAuMDM0IDAuMTY5IDAuMDc2IDAuMzM0IDAuMjQ1IDAuMzY3czAuMzM0LTAuMDc2IDAuMzY3LTAuMjQ1bDAuNS0yLjVjMC4wMzQtMC4xNjktMC4wNzYtMC4zMzQtMC4yNDUtMC4zNjctMC4xNjktMC4wMzQtMC4zMzQgMC4wNzYtMC4zNjcgMC4yNDV6Ij48L3BhdGg+CjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNMTEuOTQ0IDEyLjMxMWwwLjUgMi41YzAuMDMzIDAuMTY5IDAuMTk4IDAuMjc5IDAuMzY3IDAuMjQ1IDAuMTY5LTAuMDMzIDAuMjc5LTAuMTk4IDAuMjQ1LTAuMzY3bC0wLjUtMi41Yy0wLjAzMy0wLjE2OS0wLjE5OC0wLjI3OS0wLjM2Ny0wLjI0NS0wLjE2OSAwLjAzMy0wLjI3OSAwLjE5OC0wLjI0NSAwLjM2N3oiPjwvcGF0aD4KPHBhdGggZmlsbD0iYmxhY2siIGQ9Ik0xMS43NSAxLjVjMC0wLjU1Mi0wLjQ0OC0xLTEtMXMtMSAwLjQ0OC0xIDF2Ny41aDJ2LTcuNXpNMTAuMzc1IDEuNWMwLTAuMjA3IDAuMTY4LTAuMzc1IDAuMzc1LTAuMzc1czAuMzc1IDAuMTY4IDAuMzc1IDAuMzc1djYuODc1YzAgMC0wLjc1IDAtMC43NSAwdi02Ljg3NXoiPjwvcGF0aD4KPHBhdGggZmlsbD0iYmxhY2siIGQ9Ik02Ljc2MSAxMS4yNTZjLTAuMzQzLTAuMzgzLTAuODM3LTAuNjM5LTEuMzk5LTAuNjczLTAuNTE4LTAuMDMyLTEuMDAyIDAuMTMtMS4zNzcgMC40Mi0wLjIwMi0wLjA4My0wLjQyMS0wLjEzNC0wLjY1Mi0wLjE0OC0wLjY5OC0wLjA0My0xLjMzMyAwLjI2Ni0xLjcyNyAwLjc2Ny0wLjU0NiAwLjI0NS0wLjk3NCAwLjcyNy0xLjExOCAxLjM0Mi0wLjI0NiAxLjA1NSAwLjQzMiAyLjExNCAxLjUyMyAyLjM1NiAwLjUwNiAwLjExMyAxLjAxMSAwLjAzMCAxLjQzMS0wLjE5OSAwLjE4NSAwLjExNCAwLjM5MyAwLjE5OSAwLjYxOSAwLjI1IDAuNjAxIDAuMTM0IDEuMTk5LTAuMDA5IDEuNjU1LTAuMzQxIDAuMTM5LTAuMTAxIDAuMTcxLTAuMjk2IDAuMDY5LTAuNDM2cy0wLjI5Ny0wLjE3LTAuNDM2LTAuMDY5Yy0wLjMxNyAwLjIzLTAuNzM1IDAuMzI5LTEuMTUyIDAuMjM2LTAuMjEtMC4wNDctMC4zOTgtMC4xMzctMC41NTYtMC4yNThsLTAuMTgxLTAuMTM5LTAuMTg3IDAuMTNjLTAuMzE0IDAuMjE3LTAuNzE5IDAuMzA4LTEuMTI1IDAuMjE3LTAuNzQ2LTAuMTY2LTEuMjE5LTAuODgzLTEuMDUwLTEuNjA0IDAuMTAzLTAuNDQxIDAuNDIzLTAuNzggMC44MjUtMC45NGwwLjA4Ni0wLjAzNCAwLjA1My0wLjA3NWMwLjI2OC0wLjM3NyAwLjcyNy0wLjYxIDEuMjMzLTAuNTc5IDAuMjE1IDAuMDEzIDAuNDE2IDAuMDczIDAuNTkzIDAuMTY5bDAuMjAyIDAuMTA4IDAuMTY0LTAuMTU5YzAuMjcyLTAuMjY0IDAuNjU2LTAuNDE2IDEuMDcxLTAuMzkxIDAuMzkgMC4wMjQgMC43MzMgMC4yIDAuOTcxIDAuNDY2IDAuMTE1IDAuMTI5IDAuMzEzIDAuMTQgMC40NDEgMC4wMjRzMC4xNC0wLjMxMiAwLjAyNS0wLjQ0MXoiPjwvcGF0aD4KPHBhdGggZmlsbD0iYmxhY2siIGQ9Ik0zLjU0NiAxMC4xODRjMC4wMzItMC4yMDYgMC4wMTgtMC40MjEtMC4wNDktMC42MzEtMC4yMzctMC43NDYtMS4wNTctMS4xNTktMS44MzUtMC45MTJzLTEuMjA5IDEuMDU4LTAuOTcyIDEuODA0YzAuMDkyIDAuMjg5IDAuMjcxIDAuNTI3IDAuNSAwLjY5NyAwLjEzOSAwLjEwMyAwLjMzNSAwLjA3MyAwLjQzNy0wLjA2NXMwLjA3NC0wLjMzNS0wLjA2NS0wLjQzN2MtMC4xMjctMC4wOTQtMC4yMjYtMC4yMjYtMC4yNzYtMC4zODUtMC4xMzUtMC40MjQgMC4xMjMtMC44NzkgMC41NjYtMS4wMTlzMC45MTUgMC4wODIgMS4wNTAgMC41MDZjMC4wMzcgMC4xMTUgMC4wNDQgMC4yMzMgMC4wMjYgMC4zNDctMC4wMjYgMC4xNyAwLjA5MSAwLjMzIDAuMjYyIDAuMzU2czAuMzMtMC4wOTEgMC4zNTYtMC4yNjF6Ij48L3BhdGg+CjxwYXRoIGZpbGw9ImJsYWNrIiBkPSJNNSA2LjM2OGMtMC44MjggMC0xLjUgMC43MDItMS41IDEuNTY2czAuNjcyIDEuNTY2IDEuNSAxLjU2NmMwLjgyOCAwIDEuNS0wLjcwMSAxLjUtMS41NjZzLTAuNjcyLTEuNTY2LTEuNS0xLjU2NnpNNSA2Ljk5M2MwLjQ5IDAgMC44NzUgMC40MjkgMC44NzUgMC45NDFzLTAuMzg1IDAuOTQxLTAuODc1IDAuOTQxYy0wLjQ5IDAtMC44NzUtMC40My0wLjg3NS0wLjk0MXMwLjM4NS0wLjk0MSAwLjg3NS0wLjk0MXoiPjwvcGF0aD4KPC9zdmc+Cg==';

    public bool $license = true;

    public bool $debugpress = false;

    public bool $active = false;

    protected int $_plugins_loaded_priority = 1;

    public function __construct() {
        $this->url = SWEEPPRESS_URL;
        $this->path = SWEEPPRESS_PATH;
        parent::__construct();
    }

    public function run() {
        do_action( 'sweeppress_load_settings' );
        do_action( 'sweeppress_plugin_init' );
        $this->active = true;
        Tracker::instance();
        if ( $this->is_cli_enabled() ) {
            add_action( 'cli_init', array($this, 'register_cli_command') );
        }
        if ( $this->is_rest_enabled() ) {
            REST::instance();
        }
        if ( BBP::is_active() ) {
            add_filter( 'sweeppress_db_query_revisions_post_statuses', array($this, 'bbpress_add_post_statuses') );
        }
        if ( $this->s()->get( 'expand_admin_bar' ) ) {
            AdminBar::instance();
            add_action( 'init', array($this, 'admin_bar_cmd_init') );
        }
        add_action( 'debugpress-tracker-plugins-call', array($this, 'debugpress') );
    }

    public function debugpress() {
        if ( function_exists( 'debugpress_store_for_plugin' ) ) {
            debugpress_store_for_plugin( SWEEPPRESS_FILE, array(
                'storage' => sweeppress_settings()->group_get( 'storage' ),
            ) );
        }
    }

    public function after_setup_theme() {
        if ( sweeppress_settings()->get( 'serialized_debugpress_view' ) && WPR::is_plugin_active( 'debugpress/debugpress.php' ) && function_exists( 'debugpress_rx' ) ) {
            $this->debugpress = true;
        }
    }

    public function s() {
        return sweeppress_settings();
    }

    public function f() {
        return null;
    }

    public function b() {
        return null;
    }

    public function l() {
        return License::instance();
    }

    public function fs() {
        return sweeppress_fs();
    }

    public function backup() : ?Backup {
        return ( class_exists( '\\Dev4Press\\Plugin\\SweepPress\\Pro\\Expand\\Backup' ) ? Backup::instance() : null );
    }

    public function log( string $msg ) {
        if ( $this->is_log_enabled() ) {
            sweeppress_log( $msg );
        }
    }

    public function admin_bar_cmd_init() {
        if ( isset( $_GET['sweep-action'] ) && isset( $_GET['sweep-nonce'] ) ) {
            $full = sweeppress()->data_quick_sweepers();
            $items = sweeppress_settings()->get( 'admin_bar_quick_sweepers' );
            $allowed = array('auto');
            foreach ( $items as $item ) {
                if ( isset( $full[$item] ) ) {
                    $allowed[] = 'quick-' . $item;
                }
            }
            $action = Sanitize::key( $_GET['sweep-action'] );
            if ( in_array( $action, $allowed ) && wp_verify_nonce( $_GET['sweep-nonce'], 'sweeppress-action-' . $action ) ) {
                if ( $action == 'auto' ) {
                    sweeppress_core()->auto_sweep( 'adminbar' );
                } else {
                    if ( str_starts_with( $action, 'quick' ) ) {
                        $sweeper = substr( $action, 6 );
                        sweeppress_core()->sweep( array(
                            $sweeper => true,
                        ), 'adminbar' );
                    }
                }
            }
            $url = remove_query_arg( array('sweep-nonce', 'sweep-action') );
            wp_redirect( $url );
            exit;
        }
    }

    public function register_cli_command() {
        \WP_CLI::add_command( 'sweeppress', '\\Dev4Press\\Plugin\\SweepPress\\Expand\\CLI' );
    }

    public function is_log_enabled() : bool {
        return sweeppress_settings()->get( 'log_removal_queries' ) === true;
    }

    public function is_backup_enabled() : bool {
        return sweeppress_settings()->get( 'expand_backup' ) === true && class_exists( '\\Dev4Press\\Plugin\\SweepPress\\Pro\\Expand\\Backup' );
    }

    public function is_estimates_cache_enabled() : bool {
        return sweeppress_settings()->get( 'estimated_cache', 'sweepers' ) === true;
    }

    public function is_cli_enabled() : bool {
        if ( defined( 'SWEEPPRESS_CLI_ENABLED' ) ) {
            return SWEEPPRESS_CLI_ENABLED === true;
        }
        return sweeppress_settings()->get( 'expand_cli' ) === true;
    }

    public function is_rest_enabled() : bool {
        if ( defined( 'SWEEPPRESS_REST_ENABLED' ) ) {
            return SWEEPPRESS_REST_ENABLED === true;
        }
        return sweeppress_settings()->get( 'expand_rest' ) === true;
    }

    public function bbpress_add_post_statuses( array $statuses ) : array {
        $statuses[] = bbp_get_closed_status_id();
        $statuses[] = bbp_get_spam_status_id();
        $statuses[] = bbp_get_trash_status_id();
        $statuses[] = bbp_get_hidden_status_id();
        $statuses[] = bbp_get_private_status_id();
        return $statuses;
    }

    public function data_quick_sweepers() : array {
        $list = array(
            'all-transients'        => __( 'All Transients', 'sweeppress' ),
            'expired-transients'    => __( 'Expired Transients', 'sweeppress' ),
            'rss-feeds'             => __( 'RSS Feeds', 'sweeppress' ),
            'posts-auto-draft'      => __( 'Posts Auto Drafts', 'sweeppress' ),
            'posts-draft-revisions' => __( 'Posts Draft Revisions', 'sweeppress' ),
            'postmeta-locks'        => __( 'Postmeta Locks', 'sweeppress' ),
            'postmeta-edits'        => __( 'Postmeta Edits', 'sweeppress' ),
            'postmeta-oembeds'      => __( 'Postmeta Oembeds', 'sweeppress' ),
        );
        if ( Scope::instance()->is_multisite() ) {
            $list['all-network-transients'] = __( 'All Network Transients', 'sweeppress' );
            $list['network-expired-transients'] = __( 'Network Network Transients', 'sweeppress' );
        }
        return $list;
    }

}
