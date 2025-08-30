<?php
/**
 * Template for displaying attachment of assignment.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/single-assignment/attachment.php.
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
$list_attachments   = $current_assignment->get_attachments_assignment( $current_assignment );
?>

<?php if ( $list_attachments && count( $list_attachments ) ) { ?>
    <div class="learn_press_assignment_attachment">
        <h2><?php echo __( 'Attachment Files:', 'learnpress-assignments' ); ?></h2>
        <ul class="assignment-files assignment-documentations">
			<?php foreach ( $list_attachments as $att_id ) { ?>
                <li><?php echo wp_get_attachment_link( $att_id, $size = 'none' ) ?></li>
			<?php } ?>
        </ul>
    </div>
<?php } ?>