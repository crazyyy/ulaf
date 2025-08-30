<?php
/**
 * Template for displaying title of assignment.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/single-assignment/title.php.
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
?>

<h2><?php echo esc_html( $current_assignment->get_title() ); ?></h2>