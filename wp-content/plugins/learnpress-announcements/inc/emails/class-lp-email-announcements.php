<?php

/**
 * Class LP_Email_Announcement
 *
 * @author   ThimPress
 * @package  LearnPress/Announcements/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Email_Announcement' ) ) {
	/**
	 * Class LP_Email_Announcement.
	 */
	class LP_Email_Announcements extends LP_Email {

		public $object;
		/**
		 * Course ID
		 *
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
		 * Announcement ID
		 *
		 * @var int
		 */
		public $announcement_id = 0;

		/**
		 * LP_Email_Announcement constructor.
		 */
		public function __construct() {
			parent::__construct();
			add_filter( 'learn_press_section_callback_emails_announcements', array(
				$this,
				'learn_press_section_callback_announcements'
			) );
			add_action( 'wp_ajax_rwmb_send_mail_announcements', array( __CLASS__, 'ajax_send_mail_announcements' ) );

			$this->id             = 'announcements';
			$this->title          = __( 'Announcement', 'learnpress-announcements' );
			$this->template_base  = LP_ANNOUNCEMENTS_TEMPLATE;
			$this->template_html  = 'emails/email-text-settings.php';
			$this->template_plain = 'emails/email-plain-settings.php';

			$this->default_subject   = __( '[{{site_title}}] You have a new announcement ({{announcement_name}})', 'learnpress-announcements' );
			$this->default_heading   = __( 'New Announcement', 'learnpress-announcements' );
			$this->support_variables = array_merge( $this->general_variables, array(
				'{{site_url}}',
				'{{site_title}}',
				'{{site_admin_email}}',
				'{{site_admin_name}}',
				'{{login_url}}',
				'{{header}}',
				'{{footer}}',
				'{{email_heading}}',
				'{{footer_text}}',
				'{{announcement_id}}',
				'{{announcement_name}}',
				'{{announcement_excerpt}}',
				'{{announcement_content}}',
				'{{course_id}}',
				'{{course_name}}',
				'{{course_url}}',
				'{{user_id}}',
				'{{user_name}}',
				'{{user_email}}',
				'{{user_profile_url}}'
			) );
		}

		/**
		 * Ajax send mail.
		 */
		public static function ajax_send_mail_announcements() {

			$error              = __( 'Error! Please try again!', 'learnpress-announcements' );
			$error_course       = __( 'Error! The course isn\'t created or is removed.', 'learnpress-announcements' );
			$error_announcement = __( 'Error! The announcement does not exist or is removed.', 'learnpress-announcements' );

			if ( isset( $_POST['action'] ) && $_POST['action'] === 'rwmb_send_mail_announcements'
			     && isset( $_POST['announcement_id'] ) && ! empty( $_POST['announcement_id'] )
			     && isset( $_POST['course_id'] ) && ! empty( $_POST['course_id'] )
			) {

				/* All user is enrolled the course */
				$curd     = new LP_Course_CURD();
				$all_user = $curd->get_user_enrolled( $_POST['course_id'] );

				$status_announcement = get_post_status( $_POST['announcement_id'] );
				$status_course       = get_post_status( $_POST['course_id'] );

				if ( empty( $status_course ) || $status_course == 'trash' ) {
					echo $error_course;
					wp_die();
				}

				if ( empty( $status_announcement ) || $status_announcement == 'trash' ) {
					echo $error_announcement;
					wp_die();
				}

				if ( count( $all_user ) ) {
					foreach ( $all_user as $user ) {
						$new_class = new LP_Email_Announcements();
						$new_class->trigger( $_POST['announcement_id'], $_POST['course_id'], $user->ID );
					}
				}

				echo 'Success';
				wp_die();
			}

			if ( ! isset( $_POST['course_id'] ) ) {
				echo $error_course;
				wp_die();
			}

			if ( ! isset( $_POST['announcement_id'] ) ) {
				echo $error_announcement;
				wp_die();
			}

			echo $error;
			wp_die();
		}

		/**
		 * @return array
		 */
		public function learn_press_section_callback_announcements() {
			return array( $this, 'output_section_announcements' );
		}

		/**
		 * Output section announcements.
		 */
		public function output_section_announcements() {
			if ( $email = $this->get_email_class( 'announcements' ) ) {
				$email->admin_options( $email );
			}
		}

		/**
		 * @param $id
		 *
		 * @return bool
		 */
		public function get_email_class( $id ) {
			$emails = LP_Emails::instance()->emails;
			if ( $emails ) {
				foreach ( $emails as $email ) {
					if ( $email->id == $id ) {
						return $email;
					}
				}
			}

			return false;
		}

		/**
		 * Get email template.
		 *
		 * @param string $format
		 *
		 * @return mixed
		 */
		public function get_template_data( $format = 'plain' ) {
			return $this->object;
		}

		/**
		 * @param string $object_id
		 * @param string $more
		 *
		 * @return array|object|void
		 */
		public function get_object( $object_id = '', $more = '' ) {

			$user   = learn_press_get_user( $this->user_id );
			$course = learn_press_get_course( $this->course_id );

			$object = array();

			if ( $course ) {
				$object = array_merge(
					$object,
					array(
						'course_id'   => $course->get_id(),
						'course_name' => $course->get_title(),
						'course_url'  => $course->get_permalink()
					)
				);
			}

			if ( $user ) {
				$object = array_merge(
					$object,
					array(
						'user_id'           => $user->get_id(),
						'user_name'         => $user->get_username(),
						'user_email'        => $user->get_email(),
						'user_display_name' => $user->get_display_name()
					)
				);
			}

			if ( $course_data = $user->get_course_data( $this->course_id ) ) {
				$object['course_start_date'] = $course_data->get_start_time();
			}

			$this->object = $this->get_common_template_data(
				$this->email_format,
				$object
			);

			$this->get_variable();
		}

		/**
		 * @param $announcement_id
		 * @param $course_id
		 * @param $user_id
		 *
		 * @return bool
		 */
		public function trigger( $announcement_id, $course_id, $user_id ) {
			if ( ! $this->enable ) {
				return false;
			}
			if ( ! ( $user = learn_press_get_user( $user_id ) ) ) {
				return false;
			}
			LP_Emails::instance()->set_current( $this->id );
			$format          = $this->email_format == 'plain_text' ? 'plain' : 'html';
			$announcement    = get_post( $announcement_id );
			$course          = get_post( $course_id );
			$this->object    = $this->get_common_template_data(
				$format,
				array(
					'announcement_id'      => $announcement_id,
					'announcement_name'    => $announcement->post_title,
					'announcement_excerpt' => $announcement->post_excerpt,
					'announcement_content' => $announcement->post_content,
					'course_id'            => $course_id,
					'course_name'          => $course->post_title,
					'course_url'           => get_the_permalink( $course_id ),
					'user_id'              => $user_id,
					'user_name'            => learn_press_get_profile_display_name( $user ),
					'user_email'           => $user->get_email(),
					'user_profile_url'     => learn_press_user_profile_link( $user_id )
				)
			);
			$this->variables = $this->data_to_variables( $this->object );

			$this->object['user'] = $user;
			$this->recipient      = $user->get_email();
			$return               = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			return $return;
		}

		/**
		 * @return string
		 */
		public function get_recipient() {
			if ( ! empty( $this->object['user'] ) ) {
				$this->recipient = $this->object['user']->get_email();
			}

			return parent::get_recipient();
		}
	}
}

return new LP_Email_Announcements();