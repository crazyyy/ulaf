<?php
/**
 * Template for displaying content of assignment.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/single-assignment/content.php.
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

<div class="learn_press_assignment_content">
	<?php echo $current_assignment->get_content(); ?>
</div>