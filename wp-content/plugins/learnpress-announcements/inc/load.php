<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Announcements/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Announcements' ) ) {
	/**
	 * Class LP_Addon_Announcements.
	 */
	class LP_Addon_Announcements extends LP_Addon {

		/**
		 * @var string
		 */
		public $version = LP_ADDON_ANNOUNCEMENTS_VER;

		/**
		 * @var string
		 */
		public $require_version = LP_ADDON_ANNOUNCEMENTS_REQUIRE_VER;

		/**
		 * @var null
		 */
		protected static $_instance = null;

		/**
		 * LP_Addon_Announcements constructor.
		 */
		public function __construct() {
			parent::__construct();
			$this->add_announcements_emails();
		}

		/**
		 * Define Learnpress Announcement constants.
		 *
		 * @since 3.0.0
		 */
		protected function _define_constants() {
			define( 'LP_ANNOUNCEMENTS_PATH', dirname( LP_ADDON_ANNOUNCEMENTS_FILE ) );
			define( 'LP_ADDON_ANNOUNCEMENTS_URI', plugins_url( '/', LP_ADDON_ANNOUNCEMENTS_FILE ) );
			define( 'LP_ANNOUNCEMENTS_INC', LP_ANNOUNCEMENTS_PATH . '/inc/' );
			define( 'LP_ANNOUNCEMENTS_TEMPLATE', LP_ANNOUNCEMENTS_PATH . '/templates/' );
			define( 'LP_ANNOUNCEMENTS_CPT', 'lp_announcements' );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since 3.0.0
		 */
		protected function _includes() {
			include_once LP_ANNOUNCEMENTS_INC . 'functions.php';
			include_once LP_ANNOUNCEMENTS_INC . 'class-lp-announcements-post-type.php';
			include_once LP_ANNOUNCEMENTS_INC . 'lp-field.php';
		}

		/**
		 * Init hooks.
		 */
		protected function _init_hooks() {

			add_filter( 'learn-press/admin-course-tabs', array( $this, 'add_course_tab' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Loading list announcements
			add_action( 'wp_ajax_rwmb_lists_course', array( __CLASS__, 'ajax_lists_course' ) );
			add_action( 'wp_ajax_rwmb_lp_create_announcement', array( __CLASS__, 'ajax_create_announcement' ) );
			add_action( 'wp_ajax_rwmb_lp_remove_announcement', array( __CLASS__, 'ajax_remove_announcement' ) );

			/* Render Frontend */
			add_filter( 'learn-press/course-tabs', array( $this, 'add_single_course_announcements_tab' ), 5 );
			add_filter( 'comment_post_redirect', array( $this, 'announcement_comment_post_redirect' ), 100, 2 );
		}

		/**
		 * Enqueue scripts.
		 */
		public function enqueue_scripts() {
			global $post;

			$user      = learn_press_get_current_user();
			$user_data = get_userdata( $user->get_id() );
			$admin     = false;
			if ( $user_data && in_array( 'administrator', $user_data->roles ) ) {
				$admin = true;
			}

			if ( function_exists( 'learn_press_is_course' ) && learn_press_is_course() ) {
				if ( $admin || $user->has_course_status( $post->ID, array( 'enrolled', 'finished' ) ) ) {
					wp_enqueue_style( 'jquery-ui-accordion', $this->get_plugin_url( 'assets/css/jquery-ui-accordion.css' ), array(), LP_ADDON_ANNOUNCEMENTS_VER );
					wp_enqueue_style( 'lp-announcements-site-css', $this->get_plugin_url( 'assets/css/announcements.css' ), array(), LP_ADDON_ANNOUNCEMENTS_VER );
					wp_enqueue_script( 'lp-announcements-site-js', $this->get_plugin_url( 'assets/js/announcements.js' ), array(
						'jquery',
						'jquery-ui-accordion'
					), LP_ADDON_ANNOUNCEMENTS_VER, true );
				}
			}
		}

		/**
		 * Add Announcements tab in admin course.
		 *
		 * @param $tabs
		 *
		 * @return array
		 */
		public function add_course_tab( $tabs ) {
			$forum = array( 'course_announcements' => new RW_Meta_Box( self::course_announcements_meta_box() ) );

			return array_merge( $tabs, $forum );
		}


		/**
		 * Course Announcement meta box.
		 *
		 * @return mixed
		 */
		public function course_announcements_meta_box() {
			$meta_box = array(
				'title'      => __( 'Announcements', 'learnpress-announcements' ),
				'post_types' => LP_COURSE_CPT,
				'context'    => 'normal',
				'icon'       => 'dashicons-megaphone',
				'priority'   => 'high',
				'pages'      => array( LP_COURSE_CPT ),
				'fields'     => array(
					array(
						'name' => __( 'Announcements', 'learnpress-announcements' ),
						'id'   => '_lp_announcements_list_announcements',
						'desc' => __( 'Click the button "Send Mail" to send the new announcement for all students who were enrolled this course', 'learnpress-announcements' ),
						'type' => 'list_announcements',
						'std'  => ''
					),
					array(
						'name' => __( 'Display Comments', 'learnpress-announcements' ),
						'id'   => '_lp_announcements_display_comments',
						'desc' => __( 'Allow the users who is enrolled comment for the all announcements', 'learnpress-announcements' ),
						'type' => 'checkbox',
						'std'  => 'true'
					)
				)
			);

			return apply_filters( 'learn-press/course-announcement/settings-meta-box-args', $meta_box );
		}

		/**
		 * Lists course.
		 */
		public static function ajax_lists_course() {
			if ( ( isset( $_POST['action'] ) && $_POST['action'] === 'rwmb_lists_course' ) && ( isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) ) {

				$post = get_post( $_POST['post_id'] );
				$user = $post->post_author;

				if ( empty( $user ) ) {
					wp_die();
				}
				$lp_args = array(
					'post_type'      => LP_COURSE_CPT,
					'post_status '   => 'publish',
					'posts_per_page' => '-1',
					'author'         => $user
				);

				if ( isset( $_POST['post__not_in'] ) && ! empty( $_POST['post__not_in'] ) ) {
					$lp_args['post__not_in'] = explode( ',', $_POST['post__not_in'] );
				}

				$query = new WP_Query( $lp_args );

				ob_start();

				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						global $post;
						setup_postdata( $post );
						require( LP_ANNOUNCEMENTS_INC . 'admin/views/popup-loop-item.php' );
					}
					wp_reset_postdata();
				} else {
					require( LP_ANNOUNCEMENTS_INC . 'admin/views/popup-not-found.php' );
				}

				$result = ob_get_contents();
				ob_clean();
				echo $result;
			}
			wp_die();
		}

		/**
		 * Ajax create announcement.
		 */
		public static function ajax_create_announcement() {

			if ( isset( $_POST['action'] ) && $_POST['action'] === 'rwmb_lp_create_announcement'
			     && isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'lp-create-announcement' )
			     && ( ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) || ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) )
			     && isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) {

				$title     = urldecode( $_POST['title'] );
				$content   = urldecode( $_POST['content'] );
				$send_mail = false;

				if ( isset( $_POST['send_mail'] ) ) {
					$send_mail = $_POST['send_mail'];
				}

				$args = array(
					'post_status'  => 'publish',
					'post_type'    => LP_ANNOUNCEMENTS_CPT,
					'post_title'   => $title,
					'post_content' => $content
				);

				$post = get_post( $_POST['post_id'] );

				if ( ! empty( $post ) ) {
					$args['post_author'] = $post->post_author;
				}

				if ( isset( $_POST['display_comment'] ) ) {
					if ( $_POST['display_comment'] === 'true' ) {
						$args['comment_status'] = 'open';
					} else {
						$args['comment_status'] = 'close';
					}
				}

				$current_post = wp_insert_post( $args );

				/* Set multiple metadata for current announcement */
				if ( isset( $_POST['posts_id'] ) && ! empty( $_POST['posts_id'] ) ) {
					$posts_id = explode( ',', $_POST['posts_id'] );

					foreach ( $posts_id as $key => $post_id ) {
						add_post_meta( $current_post, '_lp_course_announcement', $post_id, false );
					}
				}

				$current_time = current_time( 'timestamp' );
				$post_time    = get_the_time( 'U', $current_post );

				if ( ( $current_time - $post_time ) < DAY_IN_SECONDS ) {
					$date = human_time_diff( $post_time, $current_time ) . __( ' ago', 'learnpress-announcement' );
				} else {
					$date = get_the_date( '', $current_post );
				}

				echo json_encode( array(
					'id'        => $current_post,
					'send_mail' => $send_mail,
					'title'     => get_the_title( $current_post ),
					'date'      => $date
				) );

				wp_die();
			}

			echo 'error';
			wp_die();
		}

		/**
		 * Ajax remove announcement.
		 */
		public static function ajax_remove_announcement() {

			if ( isset( $_POST['action'] ) && $_POST['action'] === 'rwmb_lp_remove_announcement'
			     && isset( $_POST['course_id'] ) && ! empty( $_POST['course_id'] )
			     && isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) {

				$course_id = $_POST['course_id'];
				$post_id   = $_POST['post_id'];

				delete_post_meta( $post_id, '_lp_course_announcement', $course_id );
			}

			wp_die();
		}

		/**
		 * @param $tabs
		 *
		 * @return mixed
		 */
		public function add_single_course_announcements_tab( $tabs ) {
			$user_id = get_current_user_id();

			if ( ! $user_id ) {
				return $tabs;
			}

			$course_id = get_the_ID();
			$user      = learn_press_get_current_user( $user_id );
			$user_data = get_userdata( $user->get_id() );

			/* Check permission of user is admin or enrolled */
			$roles = $user_data->roles[0];
			if ( $user->has_enrolled_course( $course_id ) || $roles === 'lp_teacher' || $roles === 'administrator' ) {
				$tabs['announcements'] = array(
					'title'    => __( 'Announcements', 'learnpres-announcements' ),
					'priority' => 30,
					'callback' => array( $this, 'single_course_announcements_tab_content' )
				);
			}

			return $tabs;
		}


		/**
		 * Announcements content in single course page.
		 */
		public function single_course_announcements_tab_content() {

			$args  = array(
				'post_type'      => LP_ANNOUNCEMENTS_CPT,
				'type'           => 'publish',
				'posts_per_page' => '-1',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => '_lp_course_announcement',
						'value'   => learn_press_get_course_id(),
						'compare' => '='
					),
				)
			);
			$query = new WP_Query( $args );

			learn_press_announcements_template( 'announcements.php', array( 'query' => $query ) );
		}

		/**
		 * @param $location
		 * @param $comment
		 *
		 * @return string
		 */
		public function announcement_comment_post_redirect( $location, $comment ) {
			if ( isset( $_REQUEST['lp_comment_announcement_from_course'] ) && ! empty( $_REQUEST['lp_comment_announcement_from_course'] ) ) {
				if ( isset( $_REQUEST['lp_comment_announcement_from_course_url'] ) && ! empty( $_REQUEST['lp_comment_announcement_from_course_url'] ) ) {
					return $_REQUEST['lp_comment_announcement_from_course_url'] . '?tab=announcements#comment-' . $comment->comment_ID;
				}
			}

			return $location;
		}

		/**
		 * Add email classes.
		 */
		public function add_announcements_emails() {
			LP_Emails::instance()->emails['LP_Email_Announcements'] = include( 'emails/class-lp-email-announcements.php' );
		}

		/**
		 * Instance.
		 *
		 * @return LP_Addon_Announcements|null
		 */
		public static function instance() {
			if ( ! self::$_instance ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

add_action( 'plugins_loaded', array( 'LP_Addon_Announcements', 'instance' ) );