<?php

namespace Dev4Press\Plugin\SweepPress\Admin\Panel;

use Dev4Press\v54\Core\UI\Admin\PanelSettings;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Settings extends PanelSettings {
    public $settings_class = '\\Dev4Press\\Plugin\\SweepPress\\Admin\\Settings';

    public function __construct( $admin ) {
        parent::__construct( $admin );
        if ( !is_null( sweeppress()->l() ) && sweeppress()->l()->is_freemius() === false ) {
            $this->subpanels = $this->subpanels + array(
                'license' => array(
                    'title'      => __( 'License', 'sweeppress' ),
                    'icon'       => 'ui-ribbon',
                    'break'      => __( 'Dev4Press', 'sweeppress' ),
                    'break-icon' => 'logo-dev4press',
                    'info'       => __( 'Activate your plugin license on this website by entering the license code.', 'sweeppress' ),
                    'kb'         => array(
                        'url' => 'https://www.dev4press.com/kb/article/sweeppress-setup-license-code/',
                    ),
                ),
            );
        }
        $is_pro = false;
        $this->subpanels = $this->subpanels + array(
            'sweepers'    => array(
                'title'      => __( 'Sweepers', 'sweeppress' ),
                'icon'       => 'ui-trash',
                'break'      => __( 'Global Settings', 'sweeppress' ),
                'break-icon' => 'plugin-sweeppress',
                'info'       => __( 'Options to control some of the plugin sweepers.', 'sweeppress' ),
            ),
            'performance' => array(
                'title' => __( 'Performance', 'sweeppress' ),
                'icon'  => 'ui-sun',
                'info'  => __( 'Options to various performance related options.', 'sweeppress' ),
            ),
            'advanced'    => array(
                'title' => __( 'Advanced', 'sweeppress' ),
                'icon'  => 'ui-toolbox',
                'info'  => __( 'Additional options for more control over the plugin.', 'sweeppress' ),
            ),
            'expand'      => array(
                'title'      => __( 'Expand', 'sweeppress' ),
                'icon'       => 'ui-puzzle',
                'break'      => __( 'Features', 'sweeppress' ),
                'break-icon' => 'ui-cabinet',
                'info'       => __( 'Expand the plugin with additional and optional features.', 'sweeppress' ),
            ),
            'meta'        => array(
                'title' => __( 'Meta & Options', 'sweeppress' ),
                'icon'  => 'ui-memo-pad',
                'info'  => __( 'Tracking of the metadata and options usage to identify what metadata and options can be removed.', 'sweeppress' ),
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/user-guide/sweeppress-feature-options-sitemeta-and-metadata-management/',
                ),
            ),
            'monitor'     => array(
                'title' => __( 'Monitor', 'sweeppress' ),
                'icon'  => 'ui-clock',
                'info'  => __( 'Controls for the daily and weekly sweeper monitoring.', 'sweeppress' ),
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeper-monitor/',
                ),
                'modd'  => ( $is_pro ? 'regular' : 'premium' ),
            ),
            'database'    => array(
                'title' => __( 'Database', 'sweeppress' ),
                'icon'  => 'ui-database',
                'info'  => __( 'Options for the Database panel for additional features and actions that can be performed.', 'sweeppress' ),
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-feature-database-management/',
                ),
                'modd'  => ( $is_pro ? 'regular' : 'premium' ),
            ),
            'backup'      => array(
                'title' => __( 'Backup Data', 'sweeppress' ),
                'icon'  => 'ui-archive',
                'info'  => __( 'Control backup of the data that has been removed by the Sweepers and from Management panels.', 'sweeppress' ),
                'kb'    => array(
                    'url' => 'https://www.dev4press.com/kb/article/sweeppress-feature-backup-data-before-removal/',
                ),
                'modd'  => ( $is_pro ? 'regular' : 'premium' ),
            ),
        );
        $this->subpanels = apply_filters( 'sweeppress_admin_settings_panels', $this->subpanels );
    }

}
