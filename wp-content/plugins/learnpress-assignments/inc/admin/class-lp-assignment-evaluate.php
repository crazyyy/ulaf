<?php
/**
 * Class LP_Assignment_Evaluate.
 *
 * @author  ThimPress
 * @package LearnPress/Assignments/Classes
 * @version 3.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'LP_Assignment_Evaluate' ) ) {

	/**
	 * Class LP_Assignment_Evaluate
	 */
	class LP_Assignment_Evaluate {

		/**
		 * @var array
		 */
		protected static $_instance = array();

		/**
		 * @var null
		 */
		protected $assignment = null;

		/**
		 * @var null
		 */
		protected $user_item_id = null;

		/**
		 * @var null
		 */
		protected $evaluated = null;

		/**
		 * LP_Assignment_Evaluate constructor.
		 *
		 * @param $assignment LP_Assignment
		 * @param $user_item_id
		 * @param $evaluated
		 */
		public function __construct( $assignment, $user_item_id, $evaluated ) {
			if ( ! LP_Assignment::get_assignment( $assignment ) ) {
				return;
			}
			$this->assignment   = $assignment;
			$this->user_item_id = $user_item_id;
			$this->evaluated    = $evaluated;
		}

		/**
		 * Display.
		 */
		public function display() {
			$settings = $this->get_settings();
			LP_Meta_Box_Helper::render_fields( $settings );
		}

		/**
		 * @return mixed
		 */
		public function get_settings() {
			$prefix = '_lp_evaluate_assignment_';

			$mark   = learn_press_get_user_item_meta( $this->user_item_id, '_lp_assignment_mark', true );
			$note   = learn_press_get_user_item_meta( $this->user_item_id, '_lp_assignment_instructor_note', true );
			$upload = learn_press_get_user_item_meta( $this->user_item_id, '_lp_assignment_evaluate_upload', true );

			$settings = apply_filters(
				'learn-press/assignment-evaluate-options',
				array(
					array(
						'title'            => __( 'Mark', 'learnpress-assignments' ),
						'id'               => $prefix . 'mark',
						'std'              => $mark ? $mark : 0,
						'type'             => 'number',
						'desc'             => __( 'Mark for user answer.', 'learnpress-assignments' ),
						'min'              => 0,
						'max'              => $this->assignment ? $this->assignment->get_data( 'mark' ) : '',
						'assignment-field' => 'yes',
						'disabled'         => $this->evaluated ? true : false
					),
					array(
						'title'            => __( 'Instructor note', 'learnpress-assignments' ),
						'id'               => $prefix . 'instructor_note',
						'std'              => $note ? $note : '',
						'type'             => 'textarea',
						'placeholder'      => __( 'Note here...', 'learnpress-assignments' ),
						'desc'             => __( 'Note for send student.', 'learnpress-assignments' ),
						'assignment-field' => 'yes',
						'disabled'         => $this->evaluated ? true : false
					),
					array(
						'title'            => __( 'Document', 'learnpress-assignments' ),
						'std'              => $upload ? $upload : '',
						'id'               => $prefix . 'document',
						'type'             => 'file_advanced',
						'desc'             => __( 'Upload files for the right answers, reference, etc', 'learnpress-assignments' ),
						'assignment-field' => 'yes',
						'disabled'         => $this->evaluated ? true : false
					)
				)
			);

			return $settings;
		}

		/**
		 * @param $assignment
		 * @param $user_item_id
		 * @param $evaluated
		 *
		 * @return array|LP_Assignment_Evaluate
		 */
		public static function instance( $assignment, $user_item_id, $evaluated ) {
			if ( ! self::$_instance ) {
				self::$_instance = new self( $assignment, $user_item_id, $evaluated );
			}

			return self::$_instance;
		}
	}
}