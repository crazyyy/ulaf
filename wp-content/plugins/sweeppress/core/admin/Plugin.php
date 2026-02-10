<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\Plugin\SweepPress\Basic\Plugin as CorePlugin;
use Dev4Press\Plugin\SweepPress\Basic\Settings as CoreSettings;
use Dev4Press\v54\Core\Admin\Menu\Plugin as BasePlugin;
use Dev4Press\v54\Core\Scope;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Plugin extends BasePlugin {
    public string $plugin = 'sweeppress';

    public string $plugin_prefix = 'sweeppress';

    public string $plugin_menu = 'SweepPress';

    public string $plugin_title = 'SweepPress';

    public bool $has_metabox = true;

    public bool $auto_mod_interface_colors = true;

    public array $enqueue_wp = array(
        'dialog' => true,
    );

    public array $per_page_options = array(
        'sweeppress_options_rows_per_page',
        'sweeppress_sitemeta_rows_per_page',
        'sweeppress_metadata_rows_per_page',
        'sweeppress_preview_rows_per_page',
        'sweeppress_duplicates_rows_per_page',
        'sweeppress_backups_rows_per_page'
    );

    public function constructor() {
        $this->url = SWEEPPRESS_URL;
        $this->path = SWEEPPRESS_PATH;
        add_action( 'sweeppress_plugin_core_ready', array($this, 'ready') );
    }

    public function settings() : CoreSettings {
        return sweeppress_settings();
    }

    public function plugin() : CorePlugin {
        return CorePlugin::instance();
    }

    public function settings_definitions() : Settings {
        return Settings::instance();
    }

    public function ready() {
    }

    public function admin_menu_items() {
        $this->setup_items = array(
            'install' => array(
                'title' => __( 'Install', 'sweeppress' ),
                'icon'  => 'ui-traffic',
                'type'  => 'setup',
                'info'  => __( 'Before you continue, make sure plugin installation was successful.', 'sweeppress' ),
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Install',
            ),
            'update'  => array(
                'title' => __( 'Update', 'sweeppress' ),
                'icon'  => 'ui-traffic',
                'type'  => 'setup',
                'info'  => __( 'Before you continue, make sure plugin was successfully updated.', 'sweeppress' ),
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Update',
            ),
        );
        $this->menu_items = array(
            'dashboard'  => array(
                'title' => __( 'Overview', 'sweeppress' ),
                'icon'  => 'ui-home',
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Dashboard',
            ),
            'about'      => array(
                'title' => __( 'About', 'sweeppress' ),
                'icon'  => 'ui-info',
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\About',
            ),
            'settings'   => array(
                'title' => __( 'Settings', 'sweeppress' ),
                'icon'  => 'ui-cog',
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Settings',
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-settings/',
                ),
            ),
            'sweep'      => array(
                'title' => __( 'Sweep', 'sweeppress' ),
                'icon'  => 'plugin-sweeppress',
                'info'  => __( 'Detailed view for all the available Sweeper tools with additional information about each tool.', 'sweeppress' ),
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Sweep',
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/user-guide/sweeppress-website-cleaning-and-sweeping/',
                ),
            ),
            'options'    => array(
                'title' => __( 'Manage Options', 'sweeppress' ),
                'icon'  => 'ui-sliders-base',
                'info'  => __( 'Review all the records in the WordPress options table with ability to remove them.', 'sweeppress' ),
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Options',
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-manage-options/',
                ),
            ),
            'metadata'   => array(
                'title'  => __( 'Manage Metadata', 'sweeppress' ),
                'short'  => __( 'Manage Meta', 'sweeppress' ),
                'icon'   => 'ui-memo-pad',
                'info'   => __( 'Management of the various WordPress metadata tables with ability to remove them.', 'sweeppress' ),
                'class'  => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Metadata',
                'kb'     => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-manage-metadata/',
                ),
                'is_pro' => true,
            ),
            'sitemeta'   => array(
                'title'  => __( 'Manage Sitemeta', 'sweeppress' ),
                'icon'   => 'ui-network',
                'info'   => __( 'Review all the records in the WordPress Sitemeta table with ability to remove them.', 'sweeppress' ),
                'class'  => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Sitemeta',
                'kb'     => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-manage-sitemeta/',
                ),
                'is_pro' => true,
            ),
            'database'   => array(
                'title'  => __( 'Manage Database', 'sweeppress' ),
                'short'  => __( 'Manage DB', 'sweeppress' ),
                'icon'   => 'ui-database',
                'info'   => __( 'Management for the website database tables linked to the current WordPress instance.', 'sweeppress' ),
                'class'  => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Database',
                'kb'     => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-manage-database/',
                ),
                'is_pro' => true,
            ),
            'jobs'       => array(
                'title'  => __( 'Sweeper Jobs', 'sweeppress' ),
                'icon'   => 'ui-clock',
                'info'   => __( 'Manage list of scheduled sweep jobs.', 'sweeppress' ),
                'class'  => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Jobs',
                'kb'     => array(
                    'url' => 'https://www.dev4press.com/kb/article/schedule-sweeper-jobs/',
                ),
                'is_pro' => true,
            ),
            'crons'      => array(
                'title'  => __( 'CRON Control', 'sweeppress' ),
                'icon'   => 'ui-calendar',
                'info'   => __( 'List of all currently scheduled jobs in WordPress CRON.', 'sweeppress' ),
                'class'  => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Crons',
                'kb'     => array(
                    'url' => 'https://www.dev4press.com/kb/article/cron-control/',
                ),
                'is_pro' => true,
            ),
            'backups'    => array(
                'title'  => __( 'Sweep Backups', 'sweeppress' ),
                'icon'   => 'ui-archive',
                'info'   => __( 'Management for the SQL export file backups from sweeping and options and meta removal operations.', 'sweeppress' ),
                'class'  => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Backups',
                'kb'     => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-sweep-backups/',
                ),
                'is_pro' => true,
            ),
            'sweepers'   => array(
                'title' => __( 'Sweepers List', 'sweeppress' ),
                'icon'  => 'ui-list',
                'info'  => __( 'List of all current sweepers, and where they can be used from.', 'sweeppress' ),
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Sweepers',
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-sweepers-list/',
                ),
            ),
            'statistics' => array(
                'title' => __( 'Statistics', 'sweeppress' ),
                'icon'  => 'ui-chart-area',
                'info'  => __( 'Overview of the statistics gathered by the plugin with all time and monthly statistics.', 'sweeppress' ),
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Statistics',
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-panel-statistics/',
                ),
            ),
            'tools'      => array(
                'title' => __( 'Tools', 'sweeppress' ),
                'icon'  => 'ui-wrench',
                'class' => '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Panel\\Tools',
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-plugin-maintenance-and-removal/',
                ),
            ),
        );
        if ( !Scope::instance()->is_multisite() || !is_main_site() ) {
            unset($this->menu_items['sitemeta']);
        }
        $this->process_menu_items();
    }

    public function svg_icon() : string {
        return sweeppress()->svg_icon;
    }

    public function run_getback() {
        new GetBack($this);
    }

    public function run_postback() {
        new PostBack($this);
    }

    public function message_process( $code, $msg ) {
        switch ( $code ) {
            case 'metadata-deleted':
            case 'option-deleted':
                $records = ( isset( $_GET['records'] ) ? absint( $_GET['records'] ) : 0 );
                $msg['message'] = sprintf( __( 'The deletion has been completed. Records removed in the process: %s.', 'sweeppress' ), $records );
                break;
            case 'backups-deleted':
                $records = ( isset( $_GET['files'] ) ? absint( $_GET['files'] ) : 0 );
                $msg['message'] = sprintf( __( 'The backups deletion has been completed. Files removed in the process: %s.', 'sweeppress' ), $records );
                break;
            case 'optimize-completed':
                $msg['message'] = __( 'Table optimization has been completed.', 'sweeppress' );
                break;
            case 'backups-purged':
                $records = ( isset( $_GET['deleted-files'] ) ? absint( $_GET['deleted-files'] ) : 0 );
                $msg['message'] = sprintf( __( 'The backups removal has been completed. Files removed in the process: %s.', 'sweeppress' ), $records );
                break;
            case 'repair-completed':
                $msg['message'] = __( 'Table repair has been completed.', 'sweeppress' );
                break;
            case 'truncate-completed':
                $msg['message'] = __( 'Table truncate operation has been completed.', 'sweeppress' );
                break;
            case 'drop-completed':
                $msg['message'] = __( 'Table drop operation has been completed.', 'sweeppress' );
                break;
            case 'job-run':
                $msg['message'] = __( 'CRON job has been executed.', 'sweeppress' );
                break;
            case 'job-deleted':
                $msg['message'] = __( 'CRON job has been removed.', 'sweeppress' );
                break;
            case 'job-cleared':
                $msg['message'] = __( 'All instances of the CRON job have been removed.', 'sweeppress' );
                break;
            case 'cache-purged':
                $msg['message'] = __( 'Cached data has been purged.', 'sweeppress' );
                break;
        }
        return $msg;
    }

    public function register_scripts_and_styles() {
        $this->enqueue->register( 'css', 'sweeppress-admin', array(
            'path' => 'css/',
            'file' => 'admin',
            'ext'  => 'css',
            'min'  => true,
            'ver'  => sweeppress_settings()->file_version(),
            'src'  => 'plugin',
            'int'  => array('ctrl'),
        ) )->register( 'js', 'sweeppress-admin', array(
            'path' => 'js/',
            'file' => 'admin',
            'ext'  => 'js',
            'min'  => true,
            'ver'  => sweeppress_settings()->file_version(),
            'src'  => 'plugin',
            'int'  => array('ctrl'),
            'req'  => array('jquery', 'jquery-form'),
        ) );
    }

    public function help_tab_getting_help() {
        if ( !empty( $this->panel ) ) {
            Help::instance( $this );
        }
        parent::help_tab_getting_help();
    }

    public function wizard() {
        return null;
    }

    protected function extra_enqueue_scripts_plugin() {
        $this->enqueue->css( 'sweeppress-admin' )->js( 'sweeppress-admin' );
        wp_localize_script( $this->enqueue->prefix() . 'sweeppress-admin', 'sweeppress_data', array(
            'are_you_sure' => __( 'Are you sure? This operation is not reversible. Make sure you have the backup in case you need removed data later.', 'sweeppress' ),
        ) );
    }

}
