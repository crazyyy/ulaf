<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Assignments/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Assignment' ) ) {
	/**
	 * Class LP_Addon_Assignment
	 */
	class LP_Addon_Assignment extends LP_Addon {

		/**
		 * Addon version
		 *
		 * @var string
		 */
		public $version = LP_ADDON_ASSIGNMENT_VER;

		/**
		 * Require LP version
		 *
		 * @var string
		 */
		public $require_version = LP_ADDON_ASSIGNMENT_REQUIRE_VER;

		/**
		 * LP_Addon_Assignment constructor.
		 */
		public function __construct() {
			parent::__construct();

			if ( $this->_check_version() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 20 );
				add_action( 'wp_enqueue_scripts', array( $this, 'assignment_enqueue_scripts' ) );
				add_action( 'save_post', array( $this, 'learnpress_update_final_assignment' ), 20 );
				add_action( 'init', array( $this, 'init' ) );
				add_action( 'init', array( $this, 'learnpress_assignment_add_rewrite_rules' ), 1000, 0 );
				add_filter( 'learn-press/course-support-items', array( $this, 'put_type_here' ), 10, 2 );
				add_filter( 'learn-press/new-section-item-data', array( $this, 'new_assignment_item' ), 10, 3 );
				add_filter( 'learn-press/course-item-object-class', array( $this, 'assignment_object_class' ), 10, 4 );
				add_filter( 'learn-press/modal-search-items/exclude', array( $this, 'exclude_items' ), 10, 4 );

				// update assignment item in single course template
				add_filter( 'learn_press_locate_template', array( $this, 'update_assignment_template' ), 10, 2 );

				add_action( 'learn-press/course-section-item/before-lp_assignment-meta', array(
					$this,
					'learnpress_assignment_show_duration'
				), 10 );
				add_action( 'learn-press/course-section-item/before-lp_assignment-meta', array(
					$this,
					'learnpress_assignment_meta_final'
				), 15 );
				add_filter( 'learn-press/can-view-item', array( $this, 'learnpress_assignment_can_view_item' ), 10, 4 );
				add_filter( 'learn-press/evaluate_passed_conditions', array(
					$this,
					'learnpress_assignment_evaluate'
				), 10, 2 );
				add_filter( 'learn-press/get-course-item', array( $this, 'learnpress_assignment_get_item' ), 10, 3 );
				add_filter( 'learn-press/default-user-item-status', array(
					$this,
					'learnpress_assignment_default_user_item_status'
				), 10, 2 );
				add_filter( 'learn-press/user-item-object', array(
					$this,
					'learnpress_assignment_user_item_object'
				), 10, 2 );
				add_filter( 'learn-press/course-item-type', array(
					$this,
					'learnpress_assignment_course_item_type'
				), 10, 1 );
				add_filter( 'learn-press/block-course-item-types', array(
					$this,
					'learnpress_assignment_block_course_item_type'
				), 10, 1 );

				//get passing_conditional
				//add_filter('learn-press/course-passing-condition', array(), 10, 3);

				// count assignment in admin archive course
				//add_filter( 'learn-press/course-count-items', array( $this, 'count_course_assignment' ), 10, 2 );
				// add support final item
				add_filter( 'learn-press/post-types-support-assessment-by-final-item', array(
					$this,
					'add_final_type'
				) );
				// register page
				add_action( 'admin_menu', array( $this, 'register_pages' ) );

				// get grade
				add_filter( 'learn-press/user-item-grade', array( $this, 'learnpress_assignment_get_grade' ), 10, 4 );

				// add email group
				add_filter( 'learn-press/email-section-classes', array( $this, 'add_email_group' ) );

				// handle evaluate form actions
				add_action( 'admin_init', array( $this, 'evaluate_actions' ) );

				//count more evaluated assignments
				add_filter( 'learn-press/course-item/completed', array(
					$this,
					'learnpress_assignment_count_evaluated_item'
				), 10, 3 );

				//add passed or failed class when display item:
				add_filter( 'learn-press/course-item-status-class', array(
					$this,
					'learnpress_assignment_add_class_css'
				), 10, 3 );

				// add filter user access admin view assignment
				add_filter( 'learn-press/filter-user-access-types', array( $this, 'add_filter_access' ) );

				// add user profile page tabs
				add_filter( 'learn-press/profile-tabs', array( $this, 'add_profile_tabs' ) );

				// add std value assignment meta box fields
				add_filter( 'rwmb_field_meta', array( $this, 'assignment_field_meta' ), 10, 2 );

				// add profile setting publicity fields
				add_filter( 'learn-press/get-publicity-setting', array( $this, 'add_publicity_setting' ) );

				// check profile setting publicity fields
				add_filter( 'learn-press/check-publicity-setting', array( $this, 'check_publicity_setting' ), 10, 2 );

				// add user profile page setting publicity fields
				add_action( 'learn-press/end-profile-publicity-fields', array(
					$this,
					'add_profile_publicity_fields'
				) );

				// save evaluate admin notice
				add_action( 'learn-press/save-evaluate-form', array( $this, 'evaluate_admin_notice' ) );

				// LP assignment email setting
				$this->emails_setting();

				add_filter( 'wp_default_editor', array( $this, 'evaluate_default_editor' ) );

				add_action( 'learn-press/frontend-editor/enqueue', array( $this, 'frontend_editor_enqueue' ) );
			}
		}

		public function frontend_editor_enqueue() {
			wp_enqueue_style( 'learn-press-assignment-fe', plugins_url( '/assets/css/assignment-front-editor.css', LP_ADDON_ASSIGNMENT_FILE ) );
			wp_enqueue_script( 'learn-press-assignment', plugins_url( '/assets/js/assignment-front-editor.js', LP_ADDON_ASSIGNMENT_FILE ), array(
				'frontend-course-editor'
			) );
			wp_localize_script( 'learn-press-assignment', 'lpa_fe_object',
				array(
					'ajax_url'              => admin_url( 'admin-ajax.php' ),
					'resend_evaluated_mail' => __( 'Re-send email for student to notify assignment has been evaluated?', 'learnpress-assignments' ),
					'delete_submission'     => __( 'Allow delete user\'s assignment and user can send it again?', 'learnpress-assignments' ),
					'reset_result'          => __( 'Allow clear the result has evaluated?', 'learnpress-assignments' )
				)
			);
		}

		/**
		 * Active visual in wp editor evaluate page.
		 *
		 * @param $editor
		 *
		 * @return string
		 */
		public function evaluate_default_editor( $editor ) {

			if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'assignment-evaluate' ) {
				return 'tinymce';
			}

			return $editor;
		}

		public function learnpress_assignment_block_course_item_type( $types ) {
			$types[] = LP_ASSIGNMENT_CPT;

			return $types;
		}

		/**
		 * @param $types
		 *
		 * @return array
		 */
		public function add_filter_access( $types ) {
			$types[] = LP_ASSIGNMENT_CPT;

			return $types;
		}

		public function learnpress_assignment_add_class_css( $item_status, $item_grade, $item_type ) {
			$item_class = '';
			if ( $item_type == LP_ASSIGNMENT_CPT && $item_status == 'evaluated' ) {
				$item_class = $item_grade;
			}

			return $item_class;
		}

		/**
		 * @param $settings
		 *
		 * @return mixed
		 */
		public function add_publicity_setting( $settings ) {
			$settings['assignments'] = LP()->settings()->get( 'profile_publicity.assignments' );

			return $settings;
		}

		/**
		 * @param $publicities
		 * @param $profile LP_Profile
		 *
		 * @return mixed
		 */
		public function check_publicity_setting( $publicities, $profile ) {
			$publicities['view-tab-assignment'] = $profile->get_publicity( 'assignments' ) == 'yes';

			return $publicities;
		}

		/**
		 * @param $profile LP_Profile
		 */
		public function add_profile_publicity_fields( $profile ) {
			if ( LP()->settings()->get( 'profile_publicity.assignments' ) === 'yes' ) { ?>
                <li class="form-field">
                    <label for="my-assignments"><?php _e( 'My assignments', 'learnpress-assignments' ); ?></label>
                    <div class="form-field-input">
                        <input name="publicity[assignments]" value="yes" type="checkbox"
                               id="my-assignments" <?php checked( $profile->get_publicity( 'assignments' ), 'yes' ); ?>/>
                        <p class="description"><?php _e( 'Public your profile assignments', 'learnpress-assignments' ); ?></p>
                    </div>
                </li>
			<?php }
		}

		/**
		 * Add std value assignment meta box fields.
		 *
		 * @param $meta
		 * @param $field
		 *
		 * @return mixed
		 */
		public function assignment_field_meta( $meta, $field ) {
			if ( ! empty( $field['assignment-field'] ) ) {
				$meta = $field['std'];

			}

			return $meta;
		}

		/**
		 * Add user profile tabs.
		 *
		 * @param $tabs
		 *
		 * @return mixed
		 */
		public function add_profile_tabs( $tabs ) {

			$settings = LP()->settings;

			$tabs['assignment'] = array(
				'title'    => __( 'Assignments', 'learnpress-assignments' ),
				'slug'     => $settings->get( 'profile_endpoints.profile-assignments', 'assignments' ),
				'callback' => array( $this, 'tab_assignments' ),
				'priority' => 25
			);

			return $tabs;
		}

		public function tab_assignments() {
			learn_press_assignment_get_template( 'profile/tabs/assignments.php' );
		}

		/**
		 * @param $post_id
		 */
		public function learnpress_update_final_assignment( $post_id ) {
			if ( LP_COURSE_CPT === get_post_type( $post_id ) ) {
				$final_assignment = get_post_meta( $post_id, '_lp_final_assignment', true );//echo'<pre>';print_r($final_assignment);die;
				if ( $final_assignment ) {
					$passing_grade_final_assignment = LP_Request::get_string( '_lp_course_result_final_assignment_passing_condition' );
					$max_mark                       = get_post_meta( $final_assignment, '_lp_mark', true );
					if ( $passing_grade_final_assignment >= 0 && $passing_grade_final_assignment <= 100 ) {
						$passing_grade_new = ( $max_mark * $passing_grade_final_assignment ) / 100;
						update_post_meta( $final_assignment, '_lp_passing_grade', $passing_grade_new );
						update_post_meta( $post_id, '_lp_passing_condition', $passing_grade_final_assignment );
					}
				}
			}
		}

		public function learnpress_assignment_count_evaluated_item( $completed, $item, $item_status ) {
			if ( $item->get_item_type() == LP_ASSIGNMENT_CPT && $item_status == 'evaluated' ) {
				$completed ++;
			}

			return $completed;
		}

		/**
		 * Handle evaluate form actions.
		 */
		public function evaluate_actions() {

			$page          = LP_Request::get( 'page' );
			$assignment_id = LP_Request::get_int( 'assignment_id' );
			$user_id       = LP_Request::get_int( 'user_id' );

			if ( ! ( 'assignment-evaluate' === $page ) || ! $assignment_id || ! $user_id || 'post' !== strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
				return;
			}

			$action       = LP_Request::get( 'action' );
			$user_item_id = LP_Request::get( 'user_item_id' );
			$assignment   = LP_Assignment::get_assignment( $assignment_id );

			if ( ! $action || ! $user_item_id ) {
				return;
			}

			$mark = LP_Request::get( '_lp_evaluate_assignment_mark', 0 );

			if ( $action != 're-evaluate' ) {
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_mark', $mark );
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_instructor_note', LP_Request::get( '_lp_evaluate_assignment_instructor_note' ) );
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_evaluate_upload', LP_Request::get( '_lp_evaluate_assignment_document' ) );
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_evaluate_author', learn_press_get_current_user()->get_id() );
			}

			$user_curd = new LP_User_CURD();
			switch ( $action ) {
				case 'evaluate':
					learn_press_update_user_item_meta( $user_item_id, 'grade', $mark >= $assignment->get_data( 'passing_grade' ) ? 'passed' : 'failed' );
					$user_curd->update_user_item_status( $user_item_id, 'evaluated' );

					do_action( 'learn-press/instructor-evaluated-assignment', $assignment_id, $user_id );
					break;
				case 're-evaluate':
					$user_curd->update_user_item_status( $user_item_id, 'completed' );

					do_action( 'learn-press/instructor-re-evaluated-assignment', $assignment_id, $user_id );
					break;
				default:
					break;
			}

			do_action( 'learn-press/save-evaluate-form', $action );
		}

		/**
		 * Add evaluate admin notice.
		 *
		 * @param $action
		 */
		public function evaluate_admin_notice( $action ) {
			if ( $action != 're-evaluate' ) {
				ob_start(); ?>
                <div class="lpa-fe-evaluate-updated">
                    <p>
						<?php
						switch ( $action ) {
							case 'evaluate':
								echo esc_html__( 'Evaluated successful.', 'leanpress-assignments' );
								break;
							case 'save':
								echo esc_html__( 'Evaluation updated.', 'leanpress-assignments' );
								break;
							default:
								break;
						} ?>
                    </p>
                </div>
			<?php }
		}

		/**
		 * Add email setting group.
		 *
		 * @param $groups
		 *
		 * @return array
		 */
		public function add_email_group( $groups ) {
			$groups[] = include LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/settings/email-groups/class-lp-settings-submitted-assignment-emails.php';
			$groups[] = include LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/settings/email-groups/class-lp-settings-evaluated-assignment-emails.php';

			return $groups;
		}

		/**
		 * Add email setting class.
		 */
		public function emails_setting() {
			if ( ! class_exists( 'LP_Settings_Emails_Group' ) ) {
				include_once LP_PLUGIN_PATH . 'inc/admin/settings/email-groups/class-lp-settings-emails-group.php';
			}

			LP_Emails::instance()->emails['LP_Email_Assignment_Submitted_Admin'] = include( 'emails/class-lp-email-submitted-assignment-admin.php' );
			LP_Emails::instance()->emails['LP_Email_Assignment_Submitted_User']  = include( 'emails/class-lp-email-submitted-assignment-user.php' );
			LP_Emails::instance()->emails['LP_Email_Assignment_Evaluated_User']  = include( 'emails/class-lp-email-evaluated-assignment-user.php' );
			LP_Emails::instance()->emails['LP_Email_Assignment_Evaluated_Admin'] = include( 'emails/class-lp-email-evaluated-assignment-admin.php' );
		}

		public function learnpress_assignment_get_grade( $grade, $item_id, $user_id, $course_id ) {
			if ( LP_ASSIGNMENT_CPT == get_post_type( $item_id ) ) {
				$result = learn_press_assignment_get_result( $item_id, $user_id, $course_id );
				$grade  = isset( $result['grade'] ) ? $result['grade'] : false;
			}

			return $grade;
		}

		public function learnpress_assignment_add_rewrite_rules() {
			$course_type  = LP_COURSE_CPT;
			$post_types   = get_post_types( '', 'objects' );
			$slug         = preg_replace( '!^/!', '', $post_types[ $course_type ]->rewrite['slug'] );
			$has_category = false;
			if ( preg_match( '!(%?course_category%?)!', $slug ) ) {
				$slug         = preg_replace( '!(%?course_category%?)!', '(.+?)/([^/]+)', $slug );
				$has_category = true;
			}
			if ( $has_category ) {
				add_rewrite_rule(
					'^' . $slug . '(?:/' . $post_types[ LP_ASSIGNMENT_CPT ]->rewrite['slug'] . '/([^/]+))/?$',
					'index.php?' . $course_type . '=$matches[2]&course_category=$matches[1]&course-item=$matches[3]&item-type=' . LP_ASSIGNMENT_CPT,
					'top'
				);
			} else {
				add_rewrite_rule(
					'^' . $slug . '/([^/]+)(?:/' . $post_types[ LP_ASSIGNMENT_CPT ]->rewrite['slug'] . '/([^/]+))/?$',
					'index.php?' . $course_type . '=$matches[1]&course-item=$matches[2]&item-type=' . LP_ASSIGNMENT_CPT,
					'top'
				);
			}
		}

		/**
		 * Count number assignment in admin archive course page.
		 *
		 * @param $content
		 * @param $course_items
		 *
		 * @return string
		 */
		public function count_course_assignment( $content, $course_items ) {
			if ( $course_items ) {
				$number_assignment = 0;
				foreach ( $course_items as $item_id ) {
					if ( get_post_type( $item_id ) == LP_ASSIGNMENT_CPT ) {
						$number_assignment ++;
					}
				}

				return $number_assignment ? sprintf( ', ' . _n( '%d assignment', '%d assignments', $number_assignment, 'learnpress-assignments' ), $number_assignment ) : ', ' . __( '0 assignment', 'learnpress-assignments' );
			}

			return $content;
		}

		/**
		 * @param $types
		 *
		 * @return array
		 */
		public function add_final_type( $types ) {
			$types[] = LP_ASSIGNMENT_CPT;

			return $types;
		}

		/**
		 * Register assignment pages.
		 */
		public function register_pages() {
			add_submenu_page(
				'',
				__( 'Assignment Student', 'learnpress-assignments' ),
				__( 'Assignment Student', 'learnpress-assignments' ),
				'edit_published_lp_courses',
				'assignment-student',
				array( $this, 'student_page' )
			);

			add_submenu_page(
				'',
				__( 'Assignment Evaluate', 'learnpress-assignments' ),
				__( 'Assignment Evaluate', 'learnpress-assignments' ),
				'edit_published_lp_courses',
				'assignment-evaluate',
				array( $this, 'evaluate_page' )
			);
		}

		/**
		 * Assignment students page.
		 */
		public function student_page() {
			$assignment_id = ! empty( $_REQUEST['assignment_id'] ) ? $_REQUEST['assignment_id'] : 0;

			global $post;
			$post = get_post( $assignment_id );
			setup_postdata( $post );

			require_once LP_ADDON_ASSIGNMENTS_INC . 'admin/class-student-list-table.php';
			learn_press_assignment_admin_view( 'students.php' );

			wp_reset_postdata();
		}

		/**
		 * Assignment evaluate page.
		 */
		public function evaluate_page() {
			$assignment_id = ! empty( $_REQUEST['assignment_id'] ) ? $_REQUEST['assignment_id'] : 0;
			global $post;
			$post = get_post( $assignment_id );
			setup_postdata( $post );

			require_once LP_ADDON_ASSIGNMENTS_INC . 'admin/class-lp-assignment-evaluate.php';
			learn_press_assignment_admin_view( 'evaluate.php' );

			wp_reset_postdata();
		}

		public function learnpress_assignment_course_item_type( $item_types ) {
			$item_types[] = 'lp_assignment';

			return $item_types;
		}

		public function learnpress_assignment_user_item_object( $item, $data ) {
			if ( LP_ASSIGNMENT_CPT == get_post_type( $data['item_id'] ) ) {
				$item = new LP_User_Item( $data );
			}

			return $item;
		}

		public function learnpress_assignment_default_user_item_status( $status, $item_id ) {
			if ( get_post_type( $item_id ) === LP_ASSIGNMENT_CPT ) {
				$status = 'viewed';
			}

			return $status;
		}

		/**
		 * @param $item
		 * @param $item_type
		 * @param $item_id
		 *
		 * @return bool|LP_Assignment
		 */
		public function learnpress_assignment_get_item( $item, $item_type, $item_id ) {
			if ( LP_ASSIGNMENT_CPT === $item_type ) {
				$item = LP_Assignment::get_assignment( $item_id );
			}

			return $item;
		}

		/**
		 * @param $course_result
		 * @param $user_course
		 *
		 * @return array|bool|int|mixed
		 */
		public function learnpress_assignment_evaluate( $course_result, $user_course ) {
			if ( ! $user_course->is_enrolled() ) {
				return false;
			}
			switch ( $course_result ) {
				case 'evaluate_final_assignment':
					$results = _evaluate_course_by_final_assignment( $user_course );
					break;
				default:
					$results = 0;
					break;
			}

			return $results;
		}

		/**
		 * Define constants.
		 */
		protected function _define_constants() {
			define( 'LP_ADDON_ASSIGNMENTS_PATH', dirname( LP_ADDON_ASSIGNMENT_FILE ) );
			define( 'LP_ADDON_ASSIGNMENTS_INC', LP_ADDON_ASSIGNMENTS_PATH . '/inc/' );
			define( 'LP_ADDON_ASSIGNMENTS_TEMPLATE', LP_ADDON_ASSIGNMENTS_PATH . '/templates/' );
			define( 'LP_INVALID_ASSIGNMENT_OR_COURSE', 250 );
			define( 'LP_ASSIGNMENT_HAS_STARTED_OR_COMPLETED', 260 );
			define( 'LP_ASSIGNMENT_CPT', 'lp_assignment' );
		}

		/**
		 *
		 */
		public function assignment_enqueue_scripts() {
			if ( function_exists( 'learn_press_is_course' ) && learn_press_is_course() ) {
				wp_enqueue_style( 'learn-press-assignment', plugins_url( '/assets/css/assignment.css', LP_ADDON_ASSIGNMENT_FILE ) );
				wp_enqueue_script( 'learn-press-assignment', plugins_url( '/assets/js/assignment.js', LP_ADDON_ASSIGNMENT_FILE ), array(
					'jquery',
					'plupload-all'
				) );
				$scripts = learn_press_assets();
				$scripts->add_script_data( 'learn-press-assignment', learn_press_assignment_single_args() );
			}
		}

		public function learnpress_assignment_can_view_item( $return, $item_id, $userid, $course_id ) {
			if ( get_post_type( $item_id ) == 'lp_assignment' ) {

				$return = learn_press_assignment_can_view_assignment( $item_id, $course_id, $userid );
			}

			return $return;
		}

		/**
		 * @param $item
		 */
		public function learnpress_assignment_show_duration( $item ) {
			$duration      = get_post_meta( $item->get_id(), '_lp_duration', true );
			$format        = array(
				'day'    => __( 'day', 'learnpress-assignments' ),
				'hour'   => __( 'hour', 'learnpress-assignments' ),
				'minute' => __( 'min', 'learnpress-assignments' ),
				'second' => __( 'sec', 'learnpress-assignments' ),
			);
			$format_plural = array(
				'day'    => __( 'days', 'learnpress-assignments' ),
				'hour'   => __( 'hours', 'learnpress-assignments' ),
				'minute' => __( 'mins', 'learnpress-assignments' ),
				'second' => __( 'seconds', 'learnpress-assignments' ),
			);
			$text_dura     = explode( ' ', $duration );
			if ( ! empty( end( $text_dura ) ) ) {
				$last_text    = end( $text_dura );
				$replace_text = absint( $duration ) > 1 ? $format_plural[ $last_text ] : $format[ $last_text ];
				$duration     = isset( $format[ $last_text ] ) ? str_replace( $last_text, $replace_text, $duration ) : $duration;
			}
			if ( absint( $duration ) == 0 ) {
				$duration = __( 'Unlimited Time', 'learnpress-assignments' );
			}
			echo '<span class="item-meta duration">' . $duration . '</span>';
		}

		public function learnpress_assignment_meta_final( $item ) {
			$course = LP_Global::course();
			if ( get_post_meta( $course->get_id(), '_lp_final_assignment', true ) == $item->get_id() ) {
				echo '<span class="item-meta final-assignment">' . __( 'Final', 'learnpress-assignments' ) . '</span>';
			}

			return;
		}

		/**
		 * Update single course assignment template files.
		 *
		 * @param $located
		 * @param $template_name
		 *
		 * @return mixed|string
		 */
		public function update_assignment_template( $located, $template_name ) {
			if ( $template_name == 'single-course/section/item-assignment.php' ) {
				$located = learn_press_assignment_get_template_part( 'item', 'assignment' );
				$located = learn_press_assignment_locate_template( 'single-course/section/item-assignment.php' );
			} elseif ( $template_name == 'single-course/content-item-lp_assignment.php' ) {
				$located = learn_press_assignment_locate_template( 'single-course/content-item-lp_assignment.php' );
			}

			return $located;
		}

		/**
		 * @param        $exclude
		 * @param        $type
		 * @param string $context
		 * @param null $context_id
		 *
		 * @return array
		 */
		public function exclude_items( $exclude, $type, $context = '', $context_id = null ) {
			if ( $type != 'lp_assignment' ) {
				return $exclude;
			}
			global $wpdb;
			$used_items = array();
			$query      = $wpdb->prepare( "
						SELECT item_id
						FROM {$wpdb->prefix}learnpress_section_items si
						INNER JOIN {$wpdb->prefix}learnpress_sections s ON s.section_id = si.section_id
						INNER JOIN {$wpdb->posts} p ON p.ID = s.section_course_id
						WHERE %d
						AND p.post_type = %s
					", 1, LP_COURSE_CPT );
			$used_items = $wpdb->get_col( $query );
			if ( $used_items && $exclude ) {
				$exclude = array_merge( $exclude, $used_items );
			} else if ( $used_items ) {
				$exclude = $used_items;
			}

			return is_array( $exclude ) ? array_unique( $exclude ) : array();
		}

		/**
		 * @param $status
		 * @param $type
		 * @param $item_type
		 * @param $item_id
		 *
		 * @return string
		 */
		public function assignment_object_class( $status, $type, $item_type, $item_id ) {
			if ( $type == 'assignment' ) {
				return 'LP_Assignment';
			}
		}

		/**
		 * @param $item
		 * @param $args
		 *
		 * @return int|WP_Error
		 */
		public function new_assignment_item( $item, $args, $course_id ) {
			if ( $item['type'] == LP_ASSIGNMENT_CPT ) {
				$assigment_curd = new LP_Assignment_CURD();
				$item['id']     = $assigment_curd->create( $args );
			}

			return $item['id'];
		}

		/**
		 * @param $types
		 * @param $key
		 *
		 * @return array
		 */
		public function put_type_here( $types, $key ) {
			if ( $key ) {
				$types[] = 'lp_assignment';
			} else {
				$types['lp_assignment'] = __( 'Assignment', 'learnpress-assignments' );
			}

			return $types;
		}

		/**
		 *
		 */
		public function init() {
			$actions = array(
				'start-assignment'    => 'start_assignment',
				'retake-assignment'   => 'start_assignment',
				'controls-assignment' => 'process_assignment',
				'lpae-evaluate'       => 'lpae_evaluate'
			);
			foreach ( $actions as $action => $function ) {
				LP_Request_Handler::register_ajax( $action, array( __CLASS__, $function ) );
				LP_Request_Handler::register( "lp-{$action}", array( __CLASS__, $function ) );
			}
		}

		public static function lpae_evaluate() {
			$page          = LP_Request::get( 'evaluate-page' );
			$assignment_id = LP_Request::get_int( 'assignment_id' );
			$user_id       = LP_Request::get_int( 'user_id' );
			$evaluate_page = get_page_link( get_option( 'assignment_evaluate_page_id' ) );
			if ( ! ( $evaluate_page === $page ) || ! $assignment_id || ! $user_id || 'post' !== strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
				return;
			}

			$action       = LP_Request::get( 'action' );
			$user_item_id = LP_Request::get( 'user_item_id' );
			$assignment   = LP_Assignment::get_assignment( $assignment_id );

			if ( ! $action || ! $user_item_id ) {
				return;
			}

			$mark = LP_Request::get( '_lp_evaluate_assignment_mark', 0 );

			if ( $action != 're-evaluate' ) {
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_mark', $mark );
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_instructor_note', LP_Request::get( '_lp_evaluate_assignment_instructor_note' ) );
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_evaluate_upload', LP_Request::get( '_lp_evaluate_assignment_document' ) );
				learn_press_update_user_item_meta( $user_item_id, '_lp_assignment_evaluate_author', learn_press_get_current_user()->get_id() );
			}

			$user_curd = new LP_User_CURD();
			switch ( $action ) {
				case 'evaluate':
					learn_press_update_user_item_meta( $user_item_id, 'grade', $mark >= $assignment->get_data( 'passing_grade' ) ? 'passed' : 'failed' );
					$user_curd->update_user_item_status( $user_item_id, 'evaluated' );

					do_action( 'learn-press/instructor-evaluated-assignment', $assignment_id, $user_id );
					break;
				case 're-evaluate':
					$user_curd->update_user_item_status( $user_item_id, 'completed' );

					do_action( 'learn-press/instructor-re-evaluated-assignment', $assignment_id, $user_id );
					break;
				default:
					break;
			}

			do_action( 'learn-press/save-evaluate-form', $action );
		}

		public static function process_assignment() {
			$pressed_button = LP_Request::get_post( 'controls-button' );
			$user_note      = LP_Request::get_post( 'assignment-editor-frontend' );
			if ( ! $pressed_button ) {
				$pressed_button = 'Send';
				$user_note      = __( 'This is the result which was forced to send because of time up issue', 'leanrpress-assignments' ) . $user_note;
			}
			$course_id     = LP_Request::get_int( 'course-id' );
			$assignment_id = LP_Request::get_int( 'assignment-id' );
			$user          = learn_press_get_current_user();
			$result        = array(
				'message'  => '',
				'result'   => __( 'Success', 'learnpress-assignments' ),
				'redirect' => learn_press_get_current_url()
			);
			if ( '' == $user_note ) {
				$result['message'] = __( 'You should have answer first', 'learnpress-assignments' );
				$result['result']  = 'error';
			} else {
				$current_useritem_id = learn_press_get_user_item_id( $user->get_id(), $assignment_id, $course_id );
				if ( ! $current_useritem_id ) {
					$course_data         = $user->get_course_data( $course_id );
					$current_useritem_id = $course_data->get_item( $assignment_id )->get_user_item_id();
				}
				learn_press_update_user_item_meta( $current_useritem_id, '_lp_assignment_answer_note', $user_note );
				$result['message'] = __( 'Your progress was saved!', 'learnpress-assignments' );
				if ( isset( $_FILES['_lp_upload_file'] ) && $_FILES['_lp_upload_file']['name'][0] != '' ) {
					$allow_file_amount  = get_post_meta( $assignment_id, '_lp_upload_files', true );
					$uploaded_files     = learn_press_assignment_get_uploaded_files( $current_useritem_id );
					$total_files        = ( $uploaded_files ) ? count( $uploaded_files ) : 0;
					$uploadedfiles      = $_FILES['_lp_upload_file'];
					$file_extension     = get_post_meta( $assignment_id, '_lp_file_extension', true );
					$file_extension     = $file_extension ? preg_replace( '#\s#', '', $file_extension ) : '*';
					$file_extension_arr = explode( ',', $file_extension );
					$max_upload_size    = get_post_meta( $assignment_id, '_lp_upload_file_limit', true );
					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}
					$upload_overrides = array( 'test_form' => false );
					add_filter( 'upload_dir', array( __CLASS__, 'learnpress_assignment_upload_dir' ) );
					$file_uploaded = 0;
					foreach ( $uploadedfiles['name'] as $key => $value ) {
						if ( $total_files >= $allow_file_amount ) {
							$result['message'] .= __( ' Your uploaded files reach the maximum amount!', 'learnpress-assignments' );
							break;
						}
						if ( $uploadedfiles['name'][ $key ] ) {
							$file = array(
								'name'     => $uploadedfiles['name'][ $key ],
								'type'     => $uploadedfiles['type'][ $key ],
								'tmp_name' => $uploadedfiles['tmp_name'][ $key ],
								'error'    => $uploadedfiles['error'][ $key ],
								'size'     => $uploadedfiles['size'][ $key ]
							);
						}
						if ( $file['size'] > $max_upload_size * 1024 * 1024 ) {
							$result['message'] .= sprintf( __( " The size of your %s file is over %d Mb(s).\n", 'learnpress-assignments' ), $file['name'], $max_upload_size );
							continue;
						}
						if ( ! in_array( '*', $file_extension_arr ) ) {
							$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
							if ( $ext && ! in_array( strtolower( $ext ), $file_extension_arr ) ) {
								$result['message'] .= ' ' . $file['name'] . __( ' is not allowed!', 'learnpress-assignments' );
								continue;
							}
						}
						$movefile = wp_handle_upload( $file, $upload_overrides );
						if ( $movefile && ! isset( $movefile['error'] ) ) {
							$movefile['filename']                       = $uploadedfiles['name'][ $key ];
							$movefile['file']                           = str_replace( ABSPATH, '', $movefile['file'] );
							$movefile['url']                            = str_replace( get_site_url(), '', $movefile['url'] );
							$movefile['saved_time']                     = current_time( 'Y-m-d H:i:s' );
							$movefile['size']                           = $file['size'];
							$uploaded_files[ md5( $movefile['file'] ) ] = $movefile;
							$total_files ++;
							$file_uploaded ++;
						} else {
							$result['message'] .= ' ' . $movefile['error'] . sprintf( __( ' Please check file %s!', 'learnpress-assignments' ), $uploadedfiles['name'][ $key ] );
						}
					}
					remove_filter( 'upload_dir', array( __CLASS__, 'learnpress_assignment_upload_dir' ) );
					learn_press_update_user_item_meta( $current_useritem_id, '_lp_assignment_answer_upload', serialize( $uploaded_files ) );
					if ( $file_uploaded == count( $uploadedfiles['name'] ) ) {
						$result['message'] = __( 'The progress was save! Your file(s) were uploaded successfully!', 'learnpress-assignments' );
					} else {
						$result['result'] = __( 'error', 'learnpress-assignments' );
					}
				}
				learn_press_update_assignment_item( $assignment_id, $course_id, $user, 'doing', $current_useritem_id );
				if ( 'Send' == $pressed_button ) {
					$result['message'] = __( 'What you did was sent to the instructors, please wait the evaluated result!', 'learnpress-assignments' );
					do_action( 'learn-press/student-submitted-assignment', $assignment_id, $user->get_id() );
					$evaluate_author = learn_press_get_user_item_meta( $current_useritem_id, '_lp_assignment_evaluate_author', true );
					if ( ! $evaluate_author ) {
						learn_press_update_user_item_meta( $current_useritem_id, '_lp_assignment_evaluate_author', 0 );
					}
					learn_press_update_assignment_item( $assignment_id, $course_id, $user, 'completed', $current_useritem_id );
				}
			}

			learn_press_maybe_send_json( $result );

			if ( ! empty( $result['message'] ) ) {
				if ( $result['result'] == 'error' ) {
					$result['message'] = __( 'Opps, something wrong while processing! ', 'learnpress-assignments' ) . $result['message'];
				}
				learn_press_add_message( $result['message'], $result['result'] );
			}

			if ( ! empty( $result['redirect'] ) ) {
				wp_redirect( $result['redirect'] );
				exit();
			}
		}

		public static function learnpress_assignment_upload_dir( $dir ) {
			$assignment_id = LP_Request::get_int( 'assignment-id' );
			$user          = learn_press_get_current_user();
			$more_path     = '/assignments';
			if ( isset( $assignment_id ) && $assignment_id ) {
				$more_path .= '/' . $assignment_id;
			}
			if ( isset( $user ) && $user->get_id() ) {
				$more_path .= '/' . $user->get_id();
			}
			$dir['path']   = $dir['basedir'] . $more_path;
			$dir['url']    = $dir['baseurl'] . $more_path;
			$dir['subdir'] = $more_path;

			return $dir;
		}

		public static function start_assignment() {
			$course_id     = LP_Request::get_int( 'course-id' );
			$assignment_id = LP_Request::get_int( 'assignment-id' );
			$action        = LP_Request::get_post( 'lp-ajax' );
			$action        = ( $action != '' ) ? str_replace( '-assignment', '', $action ) : 'start';
			$user          = learn_press_get_current_user();
			//$assignment = LP_Assignment::get_assignment($assignment_id);
			$result = array( 'result' => 'success' );
			try {
				$data = learn_press_assignment_start( $user, $assignment_id, $course_id, $action, true );
				if ( is_wp_error( $data ) ) {
					throw new Exception( $data->get_error_message() );
				} else {
					$result['result']   = 'success';
					$result['redirect'] = learn_press_get_current_url();
				}
			} catch ( Exception $ex ) {
				$result['message']  = $ex->getMessage();
				$result['result']   = 'error';
				$result['redirect'] = learn_press_get_current_url();
			}

			learn_press_maybe_send_json( $result );

			if ( ! empty( $result['message'] ) ) {
				learn_press_add_message( $result['message'] );
			}

			if ( ! empty( $result['redirect'] ) ) {
				wp_redirect( $result['redirect'] );
				exit();
			}
		}

		/**
		 * Include files.
		 */
		protected function _includes() {
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . '/functions.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'custom-post-types' . DIRECTORY_SEPARATOR . 'assignment.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'class-lp-assignment-curd.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'class-lp-assignment.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'lp-assignment-template-functions.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'lp-assignment-template-hooks.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/class-lp-assignment-admin-ajax.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'user-item/class-lp-user-item-assignment.php';

			// compatible with lp addons
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/compatible/class-lp-assignment-co-instructor.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/compatible/class-lp-assignment-buddypress.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/compatible/class-lp-assignment-content-drip.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'admin/compatible/class-lp-assignment-gradebook.php';
			if ( ! is_admin() ) {
				$this->frontend_includes();
			}
		}

		public function frontend_includes() {
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'shortcodes/class-lp-assignments-abstract-shortcodes.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'shortcodes/class-lp-assignments-shortcode-students-answer.php';
			require_once LP_ADDON_ASSIGNMENT_INC_PATH . 'shortcodes/class-lp-assignments-shortcode-evaluate-form.php';
		}

		/**
		 * Admin asset and localize script.
		 */
		public function admin_assets() {
			# TODO: add css and js
			wp_enqueue_style( 'learn-press-assignment', plugins_url( '/assets/css/admin-assignment.css', LP_ADDON_ASSIGNMENT_FILE ) );
			wp_enqueue_script( 'learn-press-assignment', plugins_url( '/assets/js/admin-assignment.js', LP_ADDON_ASSIGNMENT_FILE ), array( 'jquery' ) );

			if ( LP_Request::get( 'page' ) == 'assignment-student' ) {
				wp_localize_script( 'learn-press-assignment', 'lp_assignment_students',
					array(
						'resend_evaluated_mail' => __( 'Re-send email for student to notify assignment has been evaluated?', 'learnpress-assignments' ),
						'delete_submission'     => __( 'Allow delete user\'s assignment and user can send it again?', 'learnpress-assignments' ),
						'reset_result'          => __( 'Allow clear the result has evaluated?', 'learnpress-assignments' )
					)
				);
			}
		}
	}
}