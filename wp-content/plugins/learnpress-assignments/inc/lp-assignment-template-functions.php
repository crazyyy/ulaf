<?php
/**
 * All functions for LearnPress Assignment templates.
 *
 * @author  ThimPress
 * @package LearnPress/Assignments/Functions
 * @version 3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'learn_press_content_item_assignment_duration' ) ) {
	/**
	 * Assignment duration.
	 */
	function learn_press_content_item_assignment_duration() {
		$course          = LP_Global::course();
		$user            = learn_press_get_current_user();
		$assignment       = LP_Global::course_item();
		$assignment_data = $user->get_item_data( $assignment->get_id(), $course->get_id() );
		$status          = $user->get_item_status( $assignment->get_id(), $course->get_id() );
		$duration        = learn_press_assignment_get_time_remaining( $assignment_data );
		$duration_time   = get_post_meta( $assignment->get_id(), '_lp_duration', true );

		if ( in_array( $status, array( 'started', 'doing', 'completed' ) ) ) {
			learn_press_assignment_get_template( 'content-assignment/duration.php', array(
				'duration'      => $duration,
				'duration_time' => $duration_time
			) );
		}
	}
}

if ( ! function_exists( 'learn_press_content_item_assignment_title' ) ) {
	/**
	 * Assignment title.
	 */
	function learn_press_content_item_assignment_title() {
		learn_press_assignment_get_template( 'content-assignment/title.php' );
	}
}

if ( ! function_exists( 'learn_press_content_item_assignment_intro' ) ) {
	/**
	 * Assignment introduction.
	 */
	function learn_press_content_item_assignment_intro() {

		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		$status      = $user->get_item_status( $assignment->get_id(), $course->get_id() );

		if ( ! $status || $status == 'viewed' ) {
			learn_press_assignment_get_template( 'content-assignment/intro.php' );
		}
	}
}

if ( ! function_exists( 'learn_press_content_item_assignment_buttons' ) ) {
	/**
	 * Assignment buttons.
	 */
	function learn_press_content_item_assignment_buttons() {
		learn_press_assignment_get_template( 'content-assignment/buttons.php' );
	}
}

if ( ! function_exists( 'learn_press_content_item_assignment_content' ) ) {
	/**
	 * Assignment content.
	 */
	function learn_press_content_item_assignment_content() {
		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		if ( $user->has_course_status( $course->get_id(), array( 'finished' ) ) || $user->has_item_status( array(
				'started',
				'doing',
				'completed',
				'evaluated'
			), $assignment->get_id(), $course->get_id() )
		) {
			learn_press_assignment_get_template( 'content-assignment/content.php' );
		}
	}
}

if ( ! function_exists( 'learn_press_content_item_assignment_attachment' ) ) {
	/**
	 * Assignment attachment.
	 */
	function learn_press_content_item_assignment_attachment() {
		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		if ( $user->has_course_status( $course->get_id(), array( 'finished' ) ) || $user->has_item_status( array(
				'started',
				'doing',
				'completed',
				'evaluated'
			), $assignment->get_id(), $course->get_id() )
		) {
			learn_press_assignment_get_template( 'content-assignment/attachment.php' );
		}

	}
}

if ( ! function_exists( 'learn_press_assignment_start_button' ) ) {
	/**
	 * Start button.
	 */
	function learn_press_assignment_start_button() {
		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		if ( $user->has_course_status( $course->get_id(), array( 'finished' ) ) || ! $user->has_course_status( $course->get_id(), array( 'enrolled' ) ) || $user->has_item_status( array(
				'started',
				'doing',
				'completed',
				'evaluated'
			), $assignment->get_id(), $course->get_id() )
		) {
			return;
		}
		learn_press_assignment_get_template( 'content-assignment/buttons/start.php' );
	}
}


if ( ! function_exists( 'learn_press_assignment_nav_buttons' ) ) {
	/**
	 * Nav button.
	 */
	function learn_press_assignment_nav_buttons() {
		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		if ( ! $user->has_item_status( array(
			'started',
			'doing'
		), $assignment->get_id(), $course->get_id() ) ) {
			return;
		}

		learn_press_assignment_get_template( 'content-assignment/buttons/controls.php' );
	}
}


if ( ! function_exists( 'learn_press_assignment_after_sent' ) ) {
	/**
	 * Sent button.
	 */
	function learn_press_assignment_after_sent() {
		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		if ( ! $user->has_item_status( array(
			'completed'
		), $assignment->get_id(), $course->get_id() ) ) {
			return;
		}

		learn_press_assignment_get_template( 'content-assignment/buttons/sent.php' );
	}
}

if ( ! function_exists( 'learn_press_assignment_result' ) ) {
	/**
	 * Result button.
	 */
	function learn_press_assignment_result() {
		$course      = LP_Global::course();
		$user        = LP_Global::user();
		$assignment       = LP_Global::course_item();
		if ( ! $user->has_item_status( array(
			'evaluated'
		), $assignment->get_id(), $course->get_id() ) ) {
			return;
		}

		learn_press_assignment_get_template( 'content-assignment/buttons/result.php' );
	}
}

if ( ! function_exists( 'learn_press_assignment_retake' ) ) {
	/**
	 * Retake button.
	 */
	function learn_press_assignment_retake() {
		$course       = LP_Global::course();
		$user         = LP_Global::user();
		$assignment       = LP_Global::course_item();
		$retake_count = $assignment->get_data( 'retake_count' );
		if ( ! $retake_count ) {

			return;
		}
		$user_item_id = learn_press_get_user_item_id( $user->get_id(), $assignment->get_id(), $course->get_id() );
		if ( ! $user_item_id ) {
			$course_data  = $user->get_course_data( $course->get_id() );
			if ( false !== $assignment_item = $course_data->get_item( $assignment->get_id() ) ) {
				$user_item_id = $assignment_item->get_user_item_id();
			} else {
				$user_item_id = 0;
			}
		}
		$redo_time    = learn_press_get_user_item_meta( $user_item_id, '_lp_assignment_retaken', true );
		$redo_time    = ( $redo_time ) ? $redo_time : 0;
		if ( ! $user->has_item_status( array(
				'completed',
				'evaluated'
			), $assignment->get_id(), $course->get_id() ) || $retake_count <= $redo_time ) {
			return;
		}

		learn_press_assignment_get_template( 'content-assignment/buttons/retake.php' );
	}
}

add_filter( 'learn-press/can-view-assignment', 'learn_press_assignment_filter_can_view_item', 10, 4 );

function learn_press_assignment_filter_can_view_item( $view, $assignment_id, $user_id, $course_id ) {
	$user           = learn_press_get_user( $user_id );
	$_lp_submission = get_post_meta( $course_id, '_lp_submission', true );
	if ( $_lp_submission === 'yes' ) {
		if ( ! $user->is_logged_in() ) {
			return 'not-logged-in';
		} else if ( ! $user->has_enrolled_course( $course_id ) ) {
			return 'not-enrolled';
		}
	}

	return $view;
}

if ( ! function_exists( 'learn_press_assignment_fe_setting' ) ) {
	function learn_press_assignment_fe_setting() {
		learn_press_assignment_get_template( 'frontend-editor/item-settings.php' );
	}
}

if ( ! function_exists( 'learn_press_assignment_fe_fields' ) ) {
	function learn_press_assignment_fe_fields() {
		learn_press_assignment_get_template( 'frontend-editor/form-fields.php' );
	}
}

if ( ! function_exists( 'learn_press_assignment_fe_manager_link' ) ) {
	function learn_press_assignment_fe_manager_link() {
		$manager_page = get_option('assignment_students_man_page_id');
		if ( $manager_page ) {
			$url = get_page_link( $manager_page );
			$args = array(
				'manager_page' => $manager_page,
				'page_url'      => $url
			);
			learn_press_assignment_get_template( 'frontend-editor/manager-link.php', $args );
		} else {
			return;
		}
	}
}

if ( ! function_exists( 'lp_assignments_setup_shortcode_page_content' ) ) {

	function lp_assignments_setup_shortcode_page_content( $content ) {
		global $post;

		$page_id = $post->ID;

		if ( ! $page_id ) {
			return $content;
		}

		if ( get_option('assignment_students_man_page_id') == $page_id ) {
			$current_content = get_post( $page_id )->post_content;
			if ( strpos( $current_content, '[assignment_students_manager' ) === false ) {
				$content = '[' . apply_filters( 'assignment_students_manager_shortcode_tag', 'assignment_students_manager' ) . ']';
			}
		} elseif ( get_option('assignment_evaluate_page_id') == $page_id ) {
			$current_content = get_post( $page_id )->post_content;
			if ( strpos( $current_content, '[assignment_evaluate_form' ) === false ) {
				$content = '[' . apply_filters( 'assignment_students_evaluate_shortcode_tag', 'assignment_evaluate_form' ) . ']';
			}
		}

		return do_shortcode( $content );
	}

}