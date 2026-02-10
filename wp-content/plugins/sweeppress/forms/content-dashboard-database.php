<?php

use Dev4Press\Plugin\SweepPress\Basic\Database;
use Dev4Press\v54\Core\Quick\File;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

sweeppress_settings()->set( 'database', Database::instance()->total_wp( 'estimate' ), 'statistics', true );

?>
<div class="d4p-group d4p-dashboard-card sweeper-dashboard-database">
    <h3>
		<?php esc_html_e( 'Database Status', 'sweeppress' ); ?>
    </h3>
    <div class="d4p-group-header">
        <ul>
            <li>
                <i class="d4p-icon d4p-icon-fw d4p-ui-database"></i>
                <strong><?php echo esc_html( Database::instance()->size() ); ?></strong>
                <span><?php esc_html_e( 'Estimated Database Size', 'sweeppress' ); ?></span>
            </li>
        </ul>
    </div>
    <div class="d4p-group-inner">
        <div class="d4p-ctrl-tabs d4p-tabs-have-icons d4p-tabs-in-group">
            <div role="tablist" aria-label="<?php esc_html_e( 'Database Tables Overview', 'sweeppress' ); ?>">
                <button type="button" id="sweeppress-db-tabs-wp-tab" aria-controls="sweeppress-db-tabs-wp" aria-selected="true" role="tab" data-tabname="wp" class="d4p-ctrl-tab d4p-ctrl-tab-sweeppress-db-tabs-wp d4p-ctrl-tab-is-active">
                    <i class="d4p-icon d4p-brand-wordpress" aria-hidden="true"></i> <span><?php esc_html_e( 'Core Tables', 'sweeppress' ); ?></span>
                </button>
                <button type="button" id="sweeppress-db-tabs-all-tab" aria-controls="sweeppress-db-tabs-all" aria-selected="false" role="tab" data-tabname="all" class="d4p-ctrl-tab d4p-ctrl-tab-sweeppress-db-tabs-all">
                    <i class="d4p-icon d4p-ui-database" aria-hidden="true"></i> <span><?php esc_html_e( 'All Tables', 'sweeppress' ); ?></span>
                </button>
            </div>
            <div id="sweeppress-db-tabs-wp" aria-hidden="false" role="tabpanel" aria-labelledby="sweeppress-db-tabs-wp-tab" class="d4p-ctrl-tabs-content d4p-ctrl-tab-sweeppress-db-tabs-wp d4p-ctrl-tabs-content-active">
                <div class="sweeppress-data-block">
                    <dl class="sweeppress-list">
                        <dt><?php esc_html_e( 'Total Tables', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( Database::instance()->total_wp( 'tables' ) ); ?></dd>
                        <dt><?php esc_html_e( 'Total Records', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( number_format_i18n( Database::instance()->total_wp( 'rows' ) ) ); ?></dd>
                        <dt><?php esc_html_e( 'Total Size', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total_wp( 'total' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Data Size', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total_wp( 'size' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Index Size', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total_wp( 'index' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Data Free', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total_wp( 'free' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Data Free (Percentage)', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( Database::instance()->total_wp( 'free_percentage' ) ); ?></dd>
                    </dl>
                </div>
            </div>
            <div id="sweeppress-db-tabs-all" aria-hidden="true" role="tabpanel" aria-labelledby="sweeppress-db-tabs-all-tab" class="d4p-ctrl-tabs-content d4p-ctrl-tab-sweeppress-db-tabs-all" hidden>
                <div class="sweeppress-data-block">
                    <dl class="sweeppress-list">
                        <dt><?php esc_html_e( 'Total Tables', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( Database::instance()->total( 'tables' ) ); ?></dd>
                        <dt><?php esc_html_e( 'Total Records', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( number_format_i18n( Database::instance()->total( 'rows' ) ) ); ?></dd>
                        <dt><?php esc_html_e( 'Total Size', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total( 'total' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Data Size', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total( 'size' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Index Size', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total( 'index' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Data Free', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( File::size_format( Database::instance()->total( 'free' ), 2, ' ', false ) ); ?></dd>
                        <dt><?php esc_html_e( 'Data Free (Percentage)', 'sweeppress' ); ?>:</dt>
                        <dd><?php echo esc_html( Database::instance()->total( 'free_percentage' ) ); ?></dd>
                    </dl>
                </div>
            </div>
        </div>


    </div>
    <div class="d4p-group-footer">
        <a href="<?php echo esc_url( panel()->a()->panel_url( 'database' ) ); ?>" class="button-primary"><?php esc_html_e( 'Database Panel', 'sweeppress' ); ?></a>
    </div>
</div>
