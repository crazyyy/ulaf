<?php
/**
 * Class LP_Email_Assignment_Evaluated_Admin
 *
 * @author   ThimPress
 * @package  LearnPress/Assignments/Classes/Email
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Email_Assignment_Evaluated_Admin' ) ) {
	/**
	 * Class LP_Email_Assignment_Evaluated_Admin.
	 */
	class LP_Email_Assignment_Evaluated_Admin extends LP_Email {

		/**
		 * @var
		 */
		public $object;

		/**
		 * @var int
		 */
		public $course_id = 0;

		/**
		 * User ID
		 *
		 * @var int
		 */
		public $user_id = 0;

		/**
		 * @var int
		 */
		public $assignment_id = 0;

		/**
		 * LP_Email_Assignment_Evaluated_Admin constructor.
		 */
		public function __construct() {

			$this->id          = 'evaluated-assignment-admin';
			$this->title       = __( 'Admin', 'learnpress-assignments' );
			$this->description = __( 'Send this email to admin when they have evaluated assignment.', 'learnpress-assignments' );

			$this->template_base  = LP_ADDON_ASSIGNMENTS_TEMPLATE;
			$this->template_html  = 'emails/evaluated-assignment-admin.php';
			$this->template_plain = 'emails/plain/evaluated-assignment-admin.php';

			$this->default_subject = __( '[{{site_title}}] You just evaluated assignment ({{assignment_name}})', 'learnpress-assignments' );
			$this->default_heading = __( 'Evaluated Assignment', 'learnpress-assignments' );
			$this->recipient       = LP()->settings->get( 'emails_' . $this->id . '.recipients', $this->_get_admin_email() );

			parent::__construct();

			$this->support_variables = array_merge(
				$this->general_variables,
				array(
					'{{assignment_id}}',
					'{{assignment_name}}',
					'{{assignment_url}}',
					'{{course_id}}',
					'{{course_name}}',
					'{{course_url}}',
					'{{user_id}}',
					'{{user_name}}',
					'{{user_email}}'
				)
			);

			add_action( 'learn-press/instructor-evaluated-assignment', array( $this, 'trigger' ), 99, 2 );
		}

		/**
		 * @param $assignment_id
		 * @param $user_id
		 *
		 * @return bool
		 */
		public function trigger( $assignment_id, $user_id ) {
			if ( ! $this->enable ) {
				return false;
			}
			if ( ! ( $user = learn_press_get_user( $user_id ) ) ) {
				return false;
			}
			LP_Emails::instance()->set_current( $this->id );
			$format     = $this->email_format == 'plain_text' ? 'plain' : 'html';
			$assignment = get_post( $assignment_id );

			$courses = learn_press_get_item_courses( $assignment_id );
			$course  = get_post( $courses[0]->ID );

			$this->object    = $this->get_common_template_data(
				$format,
				array(
					'assignment_id'    => $assignment_id,
					'assignment_name'  => $assignment->post_title,
					'assignment_url'   => learn_press_get_course_item_permalink($course->ID, $assignment_id),
					'course_id'        => $course->ID,
					'course_name'      => $course->post_title,
					'course_url'       => get_the_permalink( $course->ID ),
					'user_id'          => $user_id,
					'user_name'        => learn_press_get_profile_display_name( $user ),
					'user_email'       => $user->get_email(),
					'user_profile_url' => learn_press_user_profile_link( $user_id )
				)
			);
			$this->variables = $this->data_to_variables( $this->object );

			$return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			return $return;
		}
	}
}

return new LP_Email_Assignment_Evaluated_Admin();