<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\v54\Core\Options\Element as EL;
use Dev4Press\v54\Core\Options\Settings as BaseSettings;
use Dev4Press\v54\Core\Options\Type;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Settings extends BaseSettings {
    public function admin() : Plugin {
        return Plugin::instance();
    }

    protected function value( $name, $group = 'settings', $default = null ) {
        return sweeppress_settings()->get( $name, $group, $default );
    }

    protected function init() {
        $this->settings = array(
            'expand'      => array(
                'expand_cli'       => array(
                    'name'     => __( 'WP_CLI', 'sweeppress' ),
                    'kb'       => array(
                        'url' => 'cleanup-with-the-wp-cli',
                    ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'expand_cli',
                            __( 'Status', 'sweeppress' ),
                            __( 'Integrate access to plugin sweepers into WP-CLI.', 'sweeppress' ) . '<br/>' . __( 'To get started with available commands, run the following CLI command:', 'sweeppress' ) . '<br/><code>wp help sweeppress</code>',
                            Type::BOOLEAN,
                            $this->value( 'expand_cli' )
                        )),
                    )),
                ),
                'expand_rest'      => array(
                    'name'     => __( 'REST API', 'sweeppress' ),
                    'kb'       => array(
                        'url' => 'cleanup-with-wp-rest-api',
                    ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'expand_rest',
                            __( 'Status', 'sweeppress' ),
                            __( 'Integrate access to plugin sweepers into WordPress REST API. To get data from endpoints or run DELETE operations, administrator account authentication is required!', 'sweeppress' ) . '<br/>' . __( 'The plugin adds new endpoint for all the sweeper controls:', 'sweeppress' ) . '<br/><code>/sweeppress/v1/</code>',
                            Type::BOOLEAN,
                            $this->value( 'expand_rest' )
                        )),
                    )),
                ),
                'expand_log'       => array(
                    'name'     => __( 'Log File', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'log_removal_queries',
                            __( 'Sweeper Queries', 'sweeppress' ),
                            __( 'All queries executed during the sweeper runs will be logged into a log file.', 'sweeppress' ) . '<br/><code>' . sweeppress_log_path() . '</code>',
                            Type::BOOLEAN,
                            $this->value( 'log_removal_queries' )
                        )->args( array(
                            'label' => __( 'Log Removals and Queries', 'sweeppress' ),
                        ) )),
                    )),
                ),
                'expand_bar'       => array(
                    'name'     => __( 'Admin Bar Menu', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'expand_admin_bar',
                            __( 'SweepPress Menu', 'sweeppress' ),
                            __( 'New menu will be added to the WordPress Admin Bar to quickly access SweepPress panels and features from anywhere on the website where Admin Bar is displayed.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'expand_admin_bar' )
                        )->args( array(
                            'label' => __( 'Add Menu to Admin Bar', 'sweeppress' ),
                        ) )->switch( array(
                            'role' => 'control',
                            'name' => 'expand-admin-bar',
                            'type' => 'section',
                        ) )),
                    ), array(
                        'label'    => __( 'Admin Bar Elements', 'sweeppress' ),
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'admin_bar_show_quick',
                            __( 'Show Quick Sweep', 'sweeppress' ),
                            '',
                            Type::BOOLEAN,
                            $this->value( 'admin_bar_show_quick' )
                        ), EL::l(
                            'settings',
                            'admin_bar_show_auto',
                            __( 'Show Auto Sweep', 'sweeppress' ),
                            '',
                            Type::BOOLEAN,
                            $this->value( 'admin_bar_show_auto' )
                        )),
                        'switch'   => array(
                            'role'  => 'value',
                            'name'  => 'expand-admin-bar',
                            'value' => ( $this->value( 'expand_admin_bar' ) ? 'on' : 'off' ),
                            'ref'   => 'on',
                        ),
                    )),
                ),
                'expand_dashboard' => array(
                    'name'     => __( 'Plugin Dashboard', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'sweepers',
                            'dashboard_auto_quick',
                            __( 'Auto and Quick Cleanup', 'sweeppress' ),
                            __( 'If you don\'t need to use Auto and Quick cleanup features via plugin Dashboard, you can disable them, and the box for Auto and Quick cleanup will be hidden from the Dashboard.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'dashboard_auto_quick', 'sweepers' )
                        )),
                    )),
                ),
            ),
            'sweepers'    => array(
                'sweepers_database'  => array(
                    'name'     => __( 'Database Optimization', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => __( 'Thresholds', 'sweeppress' ),
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'sweepers',
                            'db_table_optimize_threshold',
                            __( 'Minimal Fragmentation', 'sweeppress' ),
                            __( 'How much of free space database table takes compared to the data and index.', 'sweeppress' ),
                            Type::ABSINT,
                            $this->value( 'db_table_optimize_threshold', 'sweepers' )
                        )->args( array(
                            'max'        => 100,
                            'min'        => 10,
                            'label_unit' => '%',
                        ) ), EL::l(
                            'sweepers',
                            'db_table_optimize_min_size',
                            __( 'Minimal Table Size', 'sweeppress' ),
                            __( 'The size of table includes usable space (data and index) and free space. If table size is smaller than value specified here, it will be skipped.', 'sweeppress' ),
                            Type::ABSINT,
                            $this->value( 'db_table_optimize_min_size', 'sweepers' )
                        )->args( array(
                            'min'        => 2,
                            'label_unit' => 'MB',
                        ) )),
                    ), array(
                        'label'    => __( 'Optimization', 'sweeppress' ),
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'sweepers',
                            'db_table_optimize_method',
                            __( 'Method', 'sweeppress' ),
                            __( 'There are different optimization methods that may have different effects on the optimization results and statistics reported.', 'sweeppress' ),
                            Type::SELECT,
                            $this->value( 'db_table_optimize_method', 'sweepers' )
                        )->data( 'array', array(
                            'optimize' => "OPTIMIZE",
                            'alter'    => 'ALTER FORCE',
                            'both'     => 'OPTIMIZE + ALTER FORCE',
                        ) )),
                    )),
                ),
                'sweepers_keep_days' => array(
                    'name'     => __( 'Number of days to skip', 'sweeppress' ),
                    'sections' => array(
                        array(
                            'label'    => __( 'Posts', 'sweeppress' ),
                            'name'     => '',
                            'class'    => '',
                            'settings' => array(
                                EL::l(
                                    'sweepers',
                                    'keep_days_posts-auto-draft',
                                    __( 'Auto Drafts', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_posts-auto-draft', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_posts-spam',
                                    __( 'Spam Content', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_posts-spam', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_posts-trash',
                                    __( 'Trash Content', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_posts-trash', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_posts-revisions',
                                    __( 'Revisions', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_posts-revisions', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_posts-draft-revisions',
                                    __( 'Revisions for Draft Posts', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_posts-draft-revisions', 'sweepers' )
                                )
                            ),
                        ),
                        array(
                            'label'    => __( 'Comments', 'sweeppress' ),
                            'name'     => '',
                            'class'    => '',
                            'settings' => array(
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-spam',
                                    __( 'Spam Comments', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-spam', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-trash',
                                    __( 'Trash Comments', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-trash', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-unapproved',
                                    __( 'Unapproved Comments', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-unapproved', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-pingback',
                                    __( 'Pingbacks', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-pingback', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-trackback',
                                    __( 'Trackbacks', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-trackback', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-ua',
                                    __( 'User Agents', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-ua', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_comments-akismet',
                                    __( 'Akismet Meta', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_comments-akismet', 'sweepers' )
                                )
                            ),
                        ),
                        array(
                            'label'    => __( 'User Signups', 'sweeppress' ),
                            'name'     => '',
                            'class'    => '',
                            'settings' => array(EL::l(
                                'sweepers',
                                'keep_days_signups-inactive',
                                __( 'Inactive Signups', 'sweeppress' ),
                                '',
                                Type::ABSINT,
                                $this->value( 'keep_days_signups-inactive', 'sweepers' )
                            )),
                        ),
                        array(
                            'label'    => __( 'Elementor', 'sweeppress' ),
                            'name'     => '',
                            'class'    => '',
                            'settings' => array(EL::l(
                                'sweepers',
                                'keep_days_elementor_submissions-trash',
                                __( 'Trash Submissions', 'sweeppress' ),
                                '',
                                Type::ABSINT,
                                $this->value( 'keep_days_elementor_submissions-trash', 'sweepers' )
                            )),
                        ),
                        array(
                            'label'    => __( 'Action Scheduler', 'sweeppress' ),
                            'name'     => '',
                            'class'    => '',
                            'settings' => array(
                                EL::l(
                                    'sweepers',
                                    'keep_days_actionscheduler-log',
                                    __( 'Log Entries', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_actionscheduler-log', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_actionscheduler-failed',
                                    __( 'Failed Actions', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_actionscheduler-failed', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_actionscheduler-complete',
                                    __( 'Completed Actions', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_actionscheduler-complete', 'sweepers' )
                                ),
                                EL::l(
                                    'sweepers',
                                    'keep_days_actionscheduler-canceled',
                                    __( 'Canceled Actions', 'sweeppress' ),
                                    '',
                                    Type::ABSINT,
                                    $this->value( 'keep_days_actionscheduler-canceled', 'sweepers' )
                                )
                            ),
                        ),
                        array(
                            'label'    => __( 'GravityForms Entries', 'sweeppress' ),
                            'name'     => '',
                            'class'    => '',
                            'settings' => array(EL::l(
                                'sweepers',
                                'keep_days_gravityforms_entry-spam',
                                __( 'Spam Entries', 'sweeppress' ),
                                '',
                                Type::ABSINT,
                                $this->value( 'keep_days_gravityforms_entry-spam', 'sweepers' )
                            ), EL::l(
                                'sweepers',
                                'keep_days_gravityforms_entry-trash',
                                __( 'Trash Entries', 'sweeppress' ),
                                '',
                                Type::ABSINT,
                                $this->value( 'keep_days_gravityforms_entry-trash', 'sweepers' )
                            )),
                        )
                    ),
                ),
                'sweepers_panel'     => array(
                    'name'     => __( 'Panel Options', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'sweepers',
                            'show_keep_days_notices',
                            __( 'Days To Keep Notices', 'sweeppress' ),
                            __( 'Show notices about the items estimate and deletion related to the days to keep options.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'show_keep_days_notices', 'sweepers' )
                        )),
                    )),
                ),
                'sweepers_dupe'      => array(
                    'name'     => __( 'Duplicate Exceptions', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(
                            EL::l(
                                'sweepers',
                                'dupe_allow_postmeta',
                                __( 'Postmeta', 'sweeppress' ),
                                __( 'List of meta keys that will not be checked for duplicated meta records.', 'sweeppress' ),
                                Type::EXPANDABLE_TEXT,
                                $this->value( 'dupe_allow_postmeta', 'sweepers' )
                            ),
                            EL::l(
                                'sweepers',
                                'dupe_allow_commentmeta',
                                __( 'Commentmeta', 'sweeppress' ),
                                __( 'List of meta keys that will not be checked for duplicated meta records.', 'sweeppress' ),
                                Type::EXPANDABLE_TEXT,
                                $this->value( 'dupe_allow_commentmeta', 'sweepers' )
                            ),
                            EL::l(
                                'sweepers',
                                'dupe_allow_termmeta',
                                __( 'Postmeta', 'sweeppress' ),
                                __( 'List of meta keys that will not be checked for duplicated meta records.', 'sweeppress' ),
                                Type::EXPANDABLE_TEXT,
                                $this->value( 'dupe_allow_termmeta', 'sweepers' )
                            ),
                            EL::l(
                                'sweepers',
                                'dupe_allow_usermeta',
                                __( 'Usermeta', 'sweeppress' ),
                                __( 'List of meta keys that will not be checked for duplicated meta records.', 'sweeppress' ),
                                Type::EXPANDABLE_TEXT,
                                $this->value( 'dupe_allow_usermeta', 'sweepers' )
                            )
                        ),
                    )),
                ),
            ),
            'performance' => array(
                'performance_method'    => array(
                    'name'     => __( 'Estimation Method', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'sweepers',
                            'method',
                            __( 'Processing', 'sweeppress' ),
                            __( 'Depending on the database size, server type and server speed, some estimation queries can take a long time to finish. This option will control how the estimation is triggered, and for resources heavy queries, it can be skipped and run on demand only.', 'sweeppress' ),
                            Type::SELECT,
                            $this->value( 'method', 'sweepers' )
                        )->data( 'array', array(
                            'auto'    => __( 'Automatic based on the database size', 'sweeppress' ),
                            'normal'  => __( 'Normal Estimation process for all Sweepers', 'sweeppress' ),
                            'partial' => __( 'Estimate on request for some Sweepers', 'sweeppress' ),
                            'request' => __( 'Estimate on request for all Sweepers', 'sweeppress' ),
                        ) )),
                    )),
                ),
                'performance_estimates' => array(
                    'name'     => __( 'Estimates Calculations', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'sweepers',
                            'estimated_mode_full',
                            __( 'Full Mode', 'sweeppress' ),
                            __( 'Full mode will estimate data size along with the number of records. But, estimating size on the large database can increase the time needed for the estimates queries to run, and that can increase the time needed to show the overviews for the dashboard and the Sweep panel (and other relevant plugin features).', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'estimated_mode_full', 'sweepers' )
                        ), EL::l(
                            'sweepers',
                            'estimated_with_index',
                            __( 'Estimates with Index', 'sweeppress' ),
                            __( 'By default estimations are related to actual data size, it doesn\'t take into account the index space that data needs. With this option, all size estimates will include the factored correction for the size to include approximated index size of the affected data. This works only with Full Mode estimations enabled.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'estimated_with_index', 'sweepers' )
                        ), EL::l(
                            'sweepers',
                            'estimated_cache',
                            __( 'Cache Estimates', 'sweeppress' ),
                            __( 'Estimates will be cached to avoid running them too often. This cache will be stored for the period of 2 hours, or until the sweeper is used. For large database it is highly recommended to use this option.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'estimated_cache', 'sweepers' )
                        )),
                    )),
                ),
            ),
            'advanced'    => array(
                'advanced_notices' => array(
                    'name'     => __( 'Plugin Notices', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'hide_backup_notices_v6',
                            __( 'Backup Notice', 'sweeppress' ),
                            __( 'On every page where the sweeping is available, plugin will show notice about creating backup before running the sweeping process. If you understand the requirements of making backups, you can disable this notice.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'hide_backup_notices_v6' )
                        )->args( array(
                            'label' => __( 'Hide the Notice', 'sweeppress' ),
                        ) ), EL::l(
                            'settings',
                            'hide_meta_notices',
                            __( 'Meta Notice', 'sweeppress' ),
                            __( 'On every page related to Options and Metadata management, plugin will show notice about the management process and removal of options and meta records. If you understand how the process works, you can disable this notice.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'hide_meta_notices' )
                        )->args( array(
                            'label' => __( 'Hide the Notice', 'sweeppress' ),
                        ) )),
                    )),
                ),
            ),
            'meta'        => array(
                'meta_monitor' => array(
                    'name'     => __( 'Usage Monitoring', 'sweeppress' ),
                    'sections' => array(array(
                        'label'    => '',
                        'name'     => '',
                        'class'    => '',
                        'settings' => array(EL::l(
                            'settings',
                            'meta_tracker_monitor',
                            __( 'Status', 'sweeppress' ),
                            __( 'Plugin will monitor GET and UPDATE operations for all non WordPress meta and options data and log where they have been used.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'meta_tracker_monitor' )
                        )->more( array(__( 'This monitor can add some overhead to the page loading time, because it will intercept all meta and options GET and UPDATE calls to process the call for the source of the call.', 'sweeppress' ), __( 'Use it only for a limited time, and usually one or two days are enough to identify all the used metadata and options sources.', 'sweeppress' )) ), EL::l(
                            'settings',
                            'meta_tracker_monitor_update',
                            __( 'Monitor Updates', 'sweeppress' ),
                            __( 'Monitor when the values are updated. This is more precise method, it is less demanding, but it can take more time to get full results.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'meta_tracker_monitor_update' )
                        ), EL::l(
                            'settings',
                            'meta_tracker_monitor_get',
                            __( 'Monitor Reading', 'sweeppress' ),
                            __( 'Monitor when the values are read. This method can generate multiple source for every meta or options, it is more demanding, but it can much faster to confirm the meta or options usage.', 'sweeppress' ),
                            Type::BOOLEAN,
                            $this->value( 'meta_tracker_monitor_get' )
                        )),
                    )),
                ),
            ),
        );
        $this->settings['meta'] = $this->upsell( __( 'The Pro version includes additional options for data preview and deletion.', 'sweeppress' ) ) + $this->settings['meta'];
        $this->settings['expand'] = $this->upsell( __( 'The Pro version includes additional to control list of Sweepers in the admin bar menu for quick sweeping.', 'sweeppress' ) ) + $this->settings['expand'];
        $this->settings = apply_filters( 'sweeppress_admin_internal_settings', $this->settings );
        $this->init_license();
    }

    private function data_backup_compression() : array {
        $list = array(
            'none' => __( 'None', 'sweeppress' ),
        );
        if ( function_exists( 'gzopen' ) ) {
            $list['gzip'] = __( 'GZIP', 'sweeppress' );
        }
        if ( function_exists( 'bzopen' ) ) {
            $list['bzip2'] = __( 'BZIP2', 'sweeppress' );
        }
        return $list;
    }

}
