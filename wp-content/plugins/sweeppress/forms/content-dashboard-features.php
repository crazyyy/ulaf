<?php

use function Dev4Press\v54\Functions\panel;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="d4p-group d4p-dashboard-card d4p-dashboard-status">
    <h3><?php 
esc_html_e( 'Plugin Features', 'sweeppress' );
?></h3>
    <div class="d4p-group-header">
        <ul>
            <li>
                <i class="d4p-icon d4p-icon-fw d4p-ui-tags"></i>
                <strong><?php 
echo esc_html( sweeppress_core()->get_sweepers_count() );
?></strong>
                <span><?php 
esc_html_e( 'Enabled Sweepers', 'sweeppress' );
?></span>
            </li>
            <li>
                <i class="d4p-icon d4p-icon-fw d4p-ui-tags"></i>
                <strong><?php 
echo esc_html( sweeppress_core()->get_sweepers_count( 'disabled' ) );
?></strong>
                <span><?php 
esc_html_e( 'Disabled Sweepers', 'sweeppress' );
?></span>
            </li>
        </ul>
    </div>
    <div class="d4p-group-inner">
		<?php 
?>
            <div>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red d4p-badge-pro">
                    <?php 
esc_html_e( 'PRO', 'sweeppress' );
?>
                </span>
                <div class="d4p-status-message"><?php 
esc_html_e( 'Daily/Weekly Monitoring', 'sweeppress' );
?></div>
            </div>
            <div>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red d4p-badge-pro">
                    <?php 
esc_html_e( 'PRO', 'sweeppress' );
?>
                </span>
                <div class="d4p-status-message"><?php 
esc_html_e( 'Data Backup', 'sweeppress' );
?></div>
            </div>
            <div>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red d4p-badge-pro">
                    <?php 
esc_html_e( 'PRO', 'sweeppress' );
?>
                </span>
                <div class="d4p-status-message"><?php 
esc_html_e( 'Sweeper Jobs', 'sweeppress' );
?></div>
            </div>
            <hr/>
		<?php 
?>

		<?php 
?>
		<?php 
?>
        <div>
			<?php 
if ( sweeppress()->is_cli_enabled() ) {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-green">
                    <i class="d4p-icon d4p-ui-check"></i> <?php 
    esc_html_e( 'On', 'sweeppress' );
    ?>
                </span>
			<?php 
} else {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red">
                    <i class="d4p-icon d4p-ui-times"></i> <?php 
    esc_html_e( 'Off', 'sweeppress' );
    ?>
                </span>
			<?php 
}
?>
            <div class="d4p-status-message"><?php 
esc_html_e( 'WP CLI Support', 'sweeppress' );
?></div>
        </div>
        <div>
			<?php 
if ( sweeppress()->is_rest_enabled() ) {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-green">
                    <i class="d4p-icon d4p-ui-check"></i> <?php 
    esc_html_e( 'On', 'sweeppress' );
    ?>
                </span>
			<?php 
} else {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red">
                    <i class="d4p-icon d4p-ui-times"></i> <?php 
    esc_html_e( 'Off', 'sweeppress' );
    ?>
                </span>
			<?php 
}
?>
            <div class="d4p-status-message"><?php 
esc_html_e( 'WP REST API Support', 'sweeppress' );
?></div>
        </div>
        <div>
			<?php 
if ( sweeppress()->is_estimates_cache_enabled() ) {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-green">
                    <i class="d4p-icon d4p-ui-check"></i> <?php 
    esc_html_e( 'On', 'sweeppress' );
    ?>
                </span>
			<?php 
} else {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red">
                    <i class="d4p-icon d4p-ui-times"></i> <?php 
    esc_html_e( 'Off', 'sweeppress' );
    ?>
                </span>
			<?php 
}
?>
            <div class="d4p-status-message"><?php 
esc_html_e( 'Estimation Cache', 'sweeppress' );
?></div>
        </div>
        <div>
			<?php 
if ( sweeppress()->is_log_enabled() ) {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-green">
                    <i class="d4p-icon d4p-ui-check"></i> <?php 
    esc_html_e( 'On', 'sweeppress' );
    ?>
                </span>
			<?php 
} else {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red">
                    <i class="d4p-icon d4p-ui-times"></i> <?php 
    esc_html_e( 'Off', 'sweeppress' );
    ?>
                </span>
			<?php 
}
?>
            <div class="d4p-status-message"><?php 
esc_html_e( 'Log File', 'sweeppress' );
?></div>
        </div>
        <div>
			<?php 
if ( sweeppress_settings()->get( 'meta_tracker_monitor' ) ) {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-green">
                    <i class="d4p-icon d4p-ui-check"></i> <?php 
    esc_html_e( 'On', 'sweeppress' );
    ?>
                </span>
			<?php 
} else {
    ?>
                <span class="d4p-card-badge d4p-badge-right d4p-badge-red">
                    <i class="d4p-icon d4p-ui-times"></i> <?php 
    esc_html_e( 'Off', 'sweeppress' );
    ?>
                </span>
			<?php 
}
?>
            <div class="d4p-status-message"><?php 
esc_html_e( 'Metadata Monitor', 'sweeppress' );
?></div>
        </div>
		<?php 
?>
    </div>
    <div class="d4p-group-footer">
        <a href="<?php 
echo esc_url( panel()->a()->panel_url( 'settings' ) );
?>" class="button-primary"><?php 
esc_html_e( 'Plugin Settings', 'sweeppress' );
?></a>
    </div>
</div>
