<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Sorting-Choice/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Sorting_Choice' ) ) {

	/**
	 * Class LP_Addon_Sorting_Choice
	 */
	class LP_Addon_Sorting_Choice extends LP_Addon {

		/**
		 * @var string
		 */
		public $version = LP_ADDON_SORTING_CHOICE_VER;

		/**
		 * @var string
		 */
		public $require_version = LP_ADDON_SORTING_CHOICE_REQUIRE_VER;

		/**
		 * LP_Addon_Sorting_Choice constructor.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Init plugin.
		 *
		 * @since 3.0.0
		 */
		public function init() {
			$this->_define_constants();
			$this->_includes();
			$this->_init_hooks();
		}

		/**
		 * Define Learnpress Sorting choice constants.
		 *
		 * @since 3.0.0
		 */
		protected function _define_constants() {
			if ( ! defined( 'LP_QUESTION_SORTING_CHOICE_PATH' ) ) {
				define( 'LP_QUESTION_SORTING_CHOICE_PATH', dirname( LP_ADDON_SORTING_CHOICE_FILE ) );
				define( 'LP_QUESTION_SORTING_CHOICE_ASSETS', LP_QUESTION_SORTING_CHOICE_PATH . '/assets/' );
				define( 'LP_QUESTION_SORTING_CHOICE_INC', LP_QUESTION_SORTING_CHOICE_PATH . '/inc/' );
				define( 'LP_QUESTION_SORTING_CHOICE_TEMPLATE', LP_QUESTION_SORTING_CHOICE_PATH . '/templates/' );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		protected function _includes() {
			include_once LP_QUESTION_SORTING_CHOICE_INC . 'class-lp-question-sorting-choice.php';
			include_once LP_QUESTION_SORTING_CHOICE_INC . 'functions.php';
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 3.0.0
		 */
		protected function _init_hooks() {
			add_filter( 'learn_press_question_types', array( __CLASS__, 'register_question' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 3.0.0
		 */
		public function enqueue_scripts() {
			if ( is_admin() ) {
				$assets = learn_press_admin_assets();
				$assets->enqueue_style( 'lp-sorting-choice-question-admin-css', $this->get_plugin_url( 'assets/css/admin.sorting-choice.css' ) );
			} else {
				
				if(learn_press_is_course()){
					$assets = learn_press_assets();
					$assets->enqueue_script( 'jquery-ui-touch-punch-js', 
							$this->get_plugin_url( 'assets/js/jquery.ui.touch-punch.min.js' ), 
							array(
								'jquery',
								'jquery-ui-core',
								'jquery-ui-sortable',
							));
					$assets->enqueue_script( 'lp-sorting-choice-question-js', $this->get_plugin_url( 'assets/js/sorting-choice.js' ) );
					$assets->enqueue_style( 'lp-sorting-choice-question-css', $this->get_plugin_url( 'assets/css/sorting-choice.css' ) );
				}
			}
		}

		/**
		 * Register question to Learnpress list question types.
		 *
		 * @since 3.0.0
		 *
		 * @param $types
		 *
		 * @return mixed
		 */
		public static function register_question( $types ) {
			$types['sorting_choice'] = __( 'Sorting Choice', 'learnpress-sorting-choice' );

			return $types;
		}
	}
}

add_action( 'plugins_loaded', array( 'LP_Addon_Sorting_Choice', 'instance' ) );