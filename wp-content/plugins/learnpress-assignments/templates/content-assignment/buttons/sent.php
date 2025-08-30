<?php
/**
 * Template for displaying Assignment after sent.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/assignments/content-assignment/buttons/sent.php.
 *
 * @author  ThimPress
 * @package  Learnpress/Assignments/Templates
 * @version  3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

$course              = LP_Global::course();
$current_assignment       = LP_Global::course_item();
$user                = learn_press_get_current_user();
$current_useritem_id = learn_press_get_user_item_id( $user->get_id(), $current_assignment->get_id(), $course->get_id() );
if( ! $current_useritem_id ){
	$course_data  = $user->get_course_data( $course->get_id() );
	$current_useritem_id = $course_data->get_item( $current_assignment->get_id() )->get_user_item_id();
}
$last_answer    = learn_press_get_user_item_meta( $current_useritem_id, '_lp_assignment_answer_note', true );
$uploaded_files = learn_press_assignment_get_uploaded_files( $current_useritem_id );
?>

<div class="assignment-after-sent">
    <h3><?php _e( 'Your Answer:', 'learnpress-assignments' ); ?></h3>
    <blockquote><?php _e( $last_answer ); ?></blockquote>
	<?php if ( $uploaded_files && count( $uploaded_files ) ): ?>
        <h3><?php _e( 'Your Uploaded File(s):', 'learnpress-assignments' ); ?></h3>
        <div class="learn-press-assignment-uploaded">
            <ul class="assignment-files assignment-uploaded list-group list-group-flush">
				<?php foreach ( $uploaded_files as $file ) { ?>
                    <li class="list-group-item"><a href="<?php echo get_site_url() . $file['url']; ?>" target="_blank"><?php echo $file['filename']; ?></a></li>
				<?php } ?>
            </ul>
        </div>
	<?php endif; ?>
</div>
