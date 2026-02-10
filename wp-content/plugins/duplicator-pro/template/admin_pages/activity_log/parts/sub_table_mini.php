<?php

/**
 * Activity Log list template
 *
 * @package   Duplicator
 * @copyright (c) 2024, Snap Creek LLC
 */

use Duplicator\Controllers\ActivityLogPageController;
use Duplicator\Models\ActivityLog\AbstractLogEvent;
use Duplicator\Models\ActivityLog\LogUtils;
use Duplicator\Libs\Snap\SnapString;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 */

$severityLevels = LogUtils::getSeverityLabels();
/** @var array<AbstractLogEvent> */
$logs = $tplMng->getDataValueArrayRequired('logs');
?>
<table class="widefat dup-table-list striped dup-activity-log-table small">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-date"><?php esc_html_e('Date', 'duplicator-pro'); ?></th>
            <th scope="col" class="manage-column column-severity"><?php esc_html_e('Type', 'duplicator-pro'); ?></th>
            <th scope="col" class="manage-column column-title"><?php esc_html_e('Title', 'duplicator-pro'); ?></th>
            <th scope="col" class="manage-column column-description"><?php esc_html_e('Description', 'duplicator-pro'); ?></th>
            <th scope="col" class="manage-column column-duration"><?php esc_html_e('Duration', 'duplicator-pro'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($logs)) : ?>
            <tr>
                <td colspan="5" class="no-items">
                    <?php esc_html_e('No activity logs found.', 'duplicator-pro'); ?>
                </td>
            </tr>
        <?php else : ?>
            <?php
            // Calculate durations between consecutive events
            $logCount = count($logs);
            for ($i = 0; $i < $logCount; $i++) :
                $currentLog = $logs[$i];
                $nextLog    = isset($logs[$i + 1]) ? $logs[$i + 1] : null;
                $startTime  = (float) strtotime($currentLog->getCreatedAt());

                if ($nextLog) {
                    $endTime       = (float) strtotime($nextLog->getCreatedAt());
                    $phaseDuration = max(0, $endTime - $startTime);
                } else {
                    // For last entry, since it just marks completion
                    $phaseDuration = 1;
                }

                $durationFormatted = SnapString::formatHumanReadableDuration($phaseDuration, 0);
                ?>
                <tr>
                    <td class="column-date">
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($currentLog->getCreatedAt()))); ?>
                    </td>
                    <td class="column-severity">
                        <span class="dup-log-severity <?php echo esc_attr(ActivityLogPageController::getSeverityClass($currentLog->getSeverity())); ?>">
                            <?php echo esc_html($severityLevels[$currentLog->getSeverity()] ?? __('Unknown', 'duplicator-pro')); ?>
                        </span>
                    </td>
                    <td class="column-title">
                        <?php echo esc_html($currentLog->getTitle()); ?>
                    </td>
                    <td class="column-description">
                        <?php echo esc_html($currentLog->getShortDescription()); ?>
                    </td>
                    <td class="column-duration">
                        <?php echo esc_html($durationFormatted); ?>
                    </td>
                </tr>
            <?php endfor; ?>
        <?php endif; ?>
    </tbody>
</table>
