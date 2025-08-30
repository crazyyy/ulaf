<?php
/**
 * Class LP_Email_Assignment_Submitted_User
 *
 * @author   ThimPress
 * @package  LearnPress/Assignments/Classes/Email
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Email_Assignment_Submitted_User' ) ) {
	/**
	 * Class LP_Email_Assignment_Submitted_User.
	 */
	class LP_Email_Assignment_Submitted_User extends LP_Email {

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
		 * LP_Email_Assignment_Submitted_User constructor.
		 */
		public function __construct() {
			$this->id          = 'submitted-assignment-user';
			$this->title       = __( 'User', 'learnpress-assignments' );
			$this->description = __( 'Send this email to user when they have submitted assignment.', 'learnpress-assignments' );

			$this->template_base  = LP_ADDON_ASSIGNMENTS_TEMPLATE;
			$this->template_html  = 'emails/submited-assignment-user.php';
			$this->template_plain = 'emails/plain/submitted-assignment-user.php';

			$this->default_subject = __( '[{{site_title}}] You just submitted assignment ({{assignment_name}})', 'learnpress-assignments' );
			$this->default_heading = __( 'New Submit Assignment', 'learnpress-assignments' );

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

			add_action( 'learn-press/student-submitted-assignment', array( $this, 'trigger' ), 99, 2 );
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
			$this->recipient = $user->get_email();

			$return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			return $return;
		}
	}
}

return new LP_Email_Assignment_Submitted_User();