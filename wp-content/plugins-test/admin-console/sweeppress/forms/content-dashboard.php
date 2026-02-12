<?php

use Dev4Press\Plugin\SweepPress\Meta\Tracker;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
Tracker::instance()->prepare_plugins();
Tracker::instance()->prepare_themes();
?>
<div class="d4p-content">
    <div class="d4p-cards-wrapper">
		<?php 
include SWEEPPRESS_PATH . 'forms/content-dashboard-status.php';
include SWEEPPRESS_PATH . 'forms/misc-notices-backup.php';
include SWEEPPRESS_PATH . 'forms/content-dashboard-pro.php';
include SWEEPPRESS_PATH . 'forms/content-dashboard-features.php';
include SWEEPPRESS_PATH . 'forms/content-dashboard-database.php';
if ( sweeppress_settings()->get( 'dashboard_auto_quick', 'sweepers' ) ) {
    include SWEEPPRESS_PATH . 'forms/content-dashboard-auto.php';
    include SWEEPPRESS_PATH . 'forms/content-dashboard-cleanup.php';
}
?>
    </div>
</div>