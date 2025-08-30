<?php
/**
 * Template for displaying duration of current assignment user are doing.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/single-assignment/duration.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Assignments/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();
?>

<div class="assignment-status">
    <div class="progress-items">
        <div class="progress-item assignment-countdown">
            <span class="progress-number"><?php $duration->get() > 0 ? __(' --:--:-- ') : ''?></span>
            <span class="progress-label">
				<?php echo $duration->get() ? __( 'Time remaining', 'learnpress-assignments' ) : ( absint($duration_time) ? __('Time Up!', 'learnpress-assignments') : __( 'Unlimited Time', 'learnpress-assignments' ) ); ?>
            </span>
        </div>
    </div>
</div>