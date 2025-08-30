<?php
/**
 * Template for displaying introduction of assignment.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/single-assignment/intro.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Assignments/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit(); ?>

<?php
$current_assignment       = LP_Global::course_item();
$count              = $current_assignment->get_retake_count();
$intro_content      = $current_assignment->get_introduction();
?>

    <ul class="assignment-intro">
        <li>
            <label><?php _e( 'Attempts allowed', 'learnpress-assignments' ); ?></label>
            <span><?php echo ( null == $count || 0 > $count ) ? __( 'Unlimited', 'learnpress-assignments' ) : ( $count ? $count : __( 'No', 'learnpress-assignments' ) ); ?></span>
        </li>
        <li>
            <label><?php _e( 'Duration', 'learnpress-assignments' ); ?></label>
            <span><?php echo $current_assignment->get_duration_html(); ?></span>
        </li>
        <li>
            <label><?php _e( 'Passing grade', 'learnpress-assignments' ); ?></label>
            <span><?php echo sprintf( __( '%d point(s)', 'learnpress-assignments' ), $current_assignment->get_passing_grade() ); ?></span>
        </li>
    </ul>

<?php if ( $intro_content != '' ) { ?>
    <h2><?php echo __( 'Overview:', 'learnpress-assignments' ); ?></h2>

    <p><?php echo esc_html( $intro_content ); ?></p>
<?php }
