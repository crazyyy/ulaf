<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="d4p-content">
    <div class="d4p-group d4p-group-information">
        <h3><?php esc_html_e( 'Important', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
			<?php esc_html_e( 'These tools will remove all the data cached by the plugin. Right now that includes estimated sweeping results for every sweeper and results from the metadata scanner and monitoring.', 'sweeppress' ); ?>
        </div>
    </div>

    <div class="d4p-group d4p-group-tools">
        <h3><?php esc_html_e( 'Remove Sweepers Cache', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p>
				<?php esc_html_e( 'These results are cached for 2 hours only, and have auto purge implemented, but you can always run this purge manually, if you are doing some tests or making changes to the database that can impact results change.', 'sweeppress' ); ?>
            </p>
            <hr/>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][cache][sweepers]" value="on"/> <?php esc_html_e( 'Sweepers Estimation', 'sweeppress' ); ?>
            </label>
        </div>
    </div>

    <div class="d4p-group d4p-group-tools">
        <h3><?php esc_html_e( 'Remove Scanner Storage', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p>
				<?php esc_html_e( 'Scanner data is auto updated every week, or automatically when new plugin is activated. When removed, this data will be regenerated in the background over time.', 'sweeppress' ); ?>
            </p>
            <hr/>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][scan_plugins]" value="on"/> <?php esc_html_e( 'Plugins Options and Metas', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][scan_themes]" value="on"/> <?php esc_html_e( 'Themes Options and Metas', 'sweeppress' ); ?>
            </label>
        </div>
    </div>

    <div class="d4p-group d4p-group-tools">
        <h3><?php esc_html_e( 'Remove Monitor Storage', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p>
				<?php esc_html_e( 'Monitor data is result of the process that monitors updating and getting metadata and option values. Monitored data can take a long time to be generated and it is not recommended to remove if you plan to manage Metadata and Options o regular basis.', 'sweeppress' ); ?>
            </p>
            <hr/>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][monitor_meta_get]" value="on"/> <?php esc_html_e( 'Meta Monitor GET', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][monitor_meta_update]" value="on"/> <?php esc_html_e( 'Meta Monitor UPDATE', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][monitor_option_get]" value="on"/> <?php esc_html_e( 'Option Monitor GET', 'sweeppress' ); ?>
            </label>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][monitor_option_update]" value="on"/> <?php esc_html_e( 'Option Monitor UPDATE', 'sweeppress' ); ?>
            </label>
        </div>
    </div>

    <div class="d4p-group d4p-group-tools">
        <h3><?php esc_html_e( 'Remove Detection Storage', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p>
				<?php esc_html_e( 'Detected records are gathered via regular monitoring of the CRON registration process, and it is not recommended to remove it.', 'sweeppress' ); ?>
            </p>
            <hr/>
            <label>
                <input type="checkbox" class="widefat" name="sweeppress-tools[purge][storage][cron_detection]" value="on"/> <?php esc_html_e( 'CRON Detections', 'sweeppress' ); ?>
            </label>
        </div>
    </div>
</div>
