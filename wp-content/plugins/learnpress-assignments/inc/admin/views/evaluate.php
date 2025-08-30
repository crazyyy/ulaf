<?php
/**
 * Admin View: Assignment evaluate page.
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();
?>

<?php
$assignment_id = LP_Request::get_int( 'assignment_id' );
$user_id       = LP_Request::get_int( 'user_id' );

if ( ! learn_press_assignment_verify_url( $assignment_id ) ) { ?>
	<div id="error-page">
		<p><?php _e( 'Sorry, you are not allowed to access this page.', 'learnpress-assignments' ); ?></p>
	</div>
	<?php
	return;
}

if ( ! $user_id ) {
	_e( 'Invalid student', 'learnpress-assignments' );

	return;
}

if ( ! $assignment = learn_press_get_assignment( $assignment_id ) ) {
	_e( 'Invalid course', 'learnpress-assignments' );

	return;
} ?>

<?php
$user      = learn_press_get_user( $user_id );
$course    = learn_press_get_item_courses( $assignment_id );
$lp_course = learn_press_get_course( $course[0]->ID );

$course_data = $user->get_course_data( $lp_course->get_id() );

$evaluated = $user->has_item_status( array( 'evaluated' ), $assignment_id, $lp_course->get_id() );

$user_item_id = $course_data->get_item( $assignment_id )->get_user_item_id();

$last_answer    = learn_press_get_user_item_meta( $user_item_id, '_lp_assignment_answer_note', true );
$uploaded_files = learn_press_assignment_get_uploaded_files( $user_item_id );
?>

<div class="wrap" id="learn-press-evaluate">
	<h2><?php esc_html_e( 'Evaluate Form', 'learnpress-assignments' ); ?></h2>
	<a href="<?php echo esc_attr( learn_press_assignment_students_url( $assignment_id ) ) ?>"><?php _e( 'Back to list students', 'learnpress-assignments' ); ?></a>

	<div id="poststuff" class="<?php echo $evaluated ? esc_attr( 'assignment-evaluated' ) : ''; ?>">
		<form method="post">
			<input type="hidden" name="user_item_id" value="<?php echo esc_attr( $user_item_id ); ?>">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<div id="submitdiv" class="postbox ">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="toggle-indicator" aria-hidden="true"></span></button>
							<h2 class="hndle ui-sortable-handle">
								<span><?php _e( 'Actions', 'learnpress-assignments' ); ?></span></h2>
							<div class="inside">
								<div class="submitbox" id="submitpost">
									<div id="minor-publishing">
										<div id="major-publishing-actions">
											<?php if ( ! $evaluated ) { ?>
												<input name="action" type="submit" class="button button-large"
												       value="<?php _e( 'save', 'learnpress-assignments' ); ?>">
											<?php } ?>
											<input name="action" type="submit"
											       class="button button-primary button-large evaluate"
											       value="<?php echo $evaluated ? esc_attr( 're-evaluate', 'learnpress-assignments' ) : esc_attr( 'evaluate', 'learnpress-assignments' ); ?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="postbox-container-2" class="postbox-container">
					<div class="inside">

						<h3 class="assignment-title">
							<a href="<?php echo $assignment->get_edit_link(); ?>"
							   target="_blank"><?php echo $assignment->get_title(); ?></a>
						</h3>
						<?php if ( $intro = get_post_meta( $assignment_id, '_lp_introduction', true ) ) { ?>
							<div class="assignment-content"><?php echo $intro; ?></div>
						<?php } ?>

						<div class="rwmb-field rwmb-heading-wrapper submission-heading">
							<h4><?php _e( 'Submission', 'learnpress-assignments' ); ?></h4>
							<p class="description"><?php _e( 'Include student assignment answer and attach files.', 'learnpress-assignments' ); ?></p>
						</div>
						<div class="rwmb-field answer-content">
							<div class="rwmb-label">
								<label for="user-answer"><?php _e( 'Answer', 'learnpress-assignments' ); ?></label>
							</div>
							<div class="rwmb-input">
								<?php wp_editor( $last_answer, 'assignment-editor-student-answer', array(
									'media_buttons' => false,
									'textarea_rows' => 10
								) ); ?>
								<i><?php _e( 'Instructor can not modify submission of student, every change has no effect.', 'learnpress-assignments' ); ?></i>
							</div>
						</div>
						<div class="rwmb-field answer-uploads">
							<div class="rwmb-label">
								<label for="user-uploads"><?php _e( 'Attach File', 'learnpress-assignments' ); ?></label>
							</div>
							<div class="rwmb-input">
								<?php if ( $uploaded_files ) { ?>
									<ul class="assignment-files assignment-uploaded list-group list-group-flush">
										<?php foreach ( $uploaded_files as $file ) { ?>
											<li class="list-group-item"><a
													href="<?php echo get_site_url() . $file['url']; ?>"
													target="_blank"><?php echo $file['filename']; ?></a></li>
										<?php } ?>
									</ul>
								<?php } else { ?>
									<i><?php _e( 'There is no assignments attach file(s).', 'learnpress-assignments' ); ?></i>
								<?php } ?>
							</div>
						</div>

						<div class="rwmb-field rwmb-heading-wrapper">
							<h4><?php _e( 'Evaluation', 'learnpress-assignments' ); ?></h4>
							<p class="description"><?php _e( 'Your evaluation about student submission.', 'learnpress-assignments' ); ?></p>
						</div>

						<?php LP_Assignment_Evaluate::instance( $assignment, $user_item_id, $evaluated )->display(); ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
