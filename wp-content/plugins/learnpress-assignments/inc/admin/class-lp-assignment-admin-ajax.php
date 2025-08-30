<?php
/**
 * Class LP_Assignment_Admin_Ajax
 *
 * @author  ThimPress
 * @package LearnPress/Assignments/Classes
 * @version 3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'LP_Assignment_Admin_Ajax' ) ) {
	/**
	 * Class LP_Assignment_Admin_Ajax
	 */
	class LP_Assignment_Admin_Ajax {

		/**
		 * Add action ajax.
		 */
		public static function init() {

			if ( ! is_user_logged_in() ) {
				return;
			}

			$actions = array(
				'send_evaluated_mail',
				'delete_submission',
				'reset_result'
			);

			foreach ( $actions as $action ) {
				add_action( 'wp_ajax_lp_assignment_' . $action, array( __CLASS__, $action ) );
			}
		}

		/**
		 * Resend evaluated mail.
		 */
		public static function send_evaluated_mail() {
			$user_id       = LP_Request::get_string( 'user_id' );
			$assignment_id = LP_Request::get_string( 'assignment_id' );

			if ( ! $user_id || ! $assignment_id ) {
				return;
			}

			$result = learn_press_send_evaluated_mail( $assignment_id, $user_id );

			if ( $result ) {
				learn_press_send_json( array(
					'status'  => 'success',
					'message' => __( 'Send mail to student successful!', 'learnpress-assignments' )
				) );
			} else {
				learn_press_send_json( array(
					'status'  => 'fail',
					'message' => __( 'Send mail to student fail!', 'learnpress-assignments' )
				) );
			}
			wp_die();
		}

		/**
		 * Delete user's assignment and user can send it again.
		 */
		public static function delete_submission() {
			$user_id       = LP_Request::get_string( 'user_id' );
			$assignment_id = LP_Request::get_string( 'assignment_id' );

			if ( ! $user_id || ! $assignment_id ) {
				return;
			}

			$user_curd = new LP_User_CURD();
			$result    = $user_curd->delete_user_item( array( 'item_id' => $assignment_id, 'user_id' => $user_id ) );

			if ( $result ) {
				learn_press_send_json( array(
					'status'  => 'success',
					'message' => __( 'Delete user\'s assignment successful!', 'learnpress-assignments' )
				) );
			} else {
				learn_press_send_json( array(
					'status'  => 'fail',
					'message' => __( 'Delete user\'s assignment fail!', 'learnpress-assignments' )
				) );
			}
			wp_die();
		}

		/**
		 * Clear the result has evaluated.
		 */
		public static function reset_result() {
			$user_item_id = LP_Request::get_string( 'user_item_id' );

			if ( ! $user_item_id ) {
				return;
			}

			learn_press_delete_user_item_meta( $user_item_id, 'grade' );
			learn_press_delete_user_item_meta( $user_item_id, '_lp_assignment_mark' );
			learn_press_delete_user_item_meta( $user_item_id, '_lp_assignment_instructor_note' );
			learn_press_delete_user_item_meta( $user_item_id, '_lp_assignment_evaluate_upload' );
			learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_evaluate_author', 0 );

			$user_curd = new LP_User_CURD();
			$result    = $user_curd->update_user_item_status( $user_item_id, 'completed' );

			if ( $result ) {
				learn_press_send_json( array(
					'status'  => 'success',
					'message' => __( 'Clear the result has evaluated successful!', 'learnpress-assignments' )
				) );
			} else {
				learn_press_send_json( array(
					'status'  => 'fail',
					'message' => __( 'Clear the result has evaluated fail!', 'learnpress-assignments' )
				) );
			}

			wp_die();
		}

	}
}

add_action( 'admin_init', array( 'LP_Assignment_Admin_Ajax', 'init' ) );