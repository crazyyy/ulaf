<?php

use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="d4p-content">
    <div class="d4p-group d4p-group-information d4p-group-import">
        <h3><?php esc_html_e( 'Important Information', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p><?php esc_html_e( 'With this tool you can import all plugin settings from the export file made using the Export tool. If you made changes to the export file, the import will fail.', 'sweeppress' ); ?></p>
            <p><?php esc_html_e( 'For import tools to work correctly, you must import a file made with the export tool running the same versions of the plugin for both import and export!', 'sweeppress' ); ?></p>
        </div>
    </div>

    <div class="d4p-group d4p-group-information d4p-group-import d4p-group-tools">
        <h3><?php esc_html_e( 'What to import', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <label>
                <input type="checkbox" class="widefat" name="import_group[settings]" value="on" checked/> <?php esc_html_e( 'Main Plugin Settings', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="import_group[sweepers]" value="on" checked/> <?php esc_html_e( 'Sweepers Settings', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="import_group[statistics]" value="on" checked/> <?php esc_html_e( 'Collected Statistics', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="import_group[scheduler]" value="on" checked/> <?php esc_html_e( 'Sweeper Jobs (and all scheduled runs)', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="import_group[storage]" value="on" checked/> <?php esc_html_e( 'Scan and Monitor Storage', 'sweeppress' ); ?>
            </label>
        </div>
    </div>

    <div class="d4p-group d4p-group-information d4p-group-import">
        <h3><?php esc_html_e( 'Import from File', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p>
				<?php esc_html_e( 'Select file you want to import from', 'sweeppress' ); ?>:
            </p>
            <input type="file" name="import_file"/>
        </div>
    </div>

	<?php panel()->include_accessibility_control(); ?>
</div>
