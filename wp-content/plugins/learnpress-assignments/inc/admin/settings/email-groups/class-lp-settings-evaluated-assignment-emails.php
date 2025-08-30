<?php
/**
 * Class LP_Settings_Evaluated_Assignment_Emails
 *
 * @author   ThimPress
 * @package  LearnPress/Assignments/Classes/Email
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Settings_Evaluated_Assignment_Emails' ) ) {
	/**
	 * Class LP_Settings_Evaluated_Assignment_Emails
	 */
	class LP_Settings_Evaluated_Assignment_Emails extends LP_Settings_Emails_Group {

		/**
		 * LP_Settings_Evaluated_Assignment_Emails constructor.
		 */
		public function __construct() {
			$this->group_id = 'evaluated-assignment-emails';
			$this->items    = array(
				'evaluated-assignment-admin',
				'evaluated-assignment-user'
			);

			parent::__construct();
		}

		/**
		 * @return string
		 */
		public function __toString() {
			return __( 'Evaluated Assignment', 'learnpress-assignments' );
		}
	}
}

return new LP_Settings_Evaluated_Assignment_Emails();