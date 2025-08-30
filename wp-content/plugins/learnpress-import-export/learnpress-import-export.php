<?php
/**
 * Plugin Name: LearnPress - Export/Import Courses
 * Plugin URI: http://thimpress.com/learnpress
 * Description: Export and Import your courses with all lesson and quiz in easiest way.
 * Author: ThimPress
 * Version: 4.0.8
 * Author URI: http://thimpress.com
 * Tags: learnpress, lms, add-on, prerequisites courses
 * Text Domain: learnpress-import-export
 * Domain Path: /languages/
 * Require_LP_Version: 4.2.7.7
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package learnpress-import-export
 */

defined( 'ABSPATH' ) || exit;

use LPImportExport\Migration\Controllers\EnqueueScriptsController;
use LPImportExport\Migration\Controllers\AdminMenuController;
use LPImportExport\Migration\Controllers\MigrationPopupController;
use LPImportExport\Migration\Controllers\TutorMigrationController;
use LPImportExport\Migration\Helpers\Plugin;

const LP_ADDON_IMPORT_EXPORT_FILE = __FILE__;

/**
 * Class LP_Addon_Import_Export_Preload
 */
class LP_Addon_Import_Export_Preload {
	/**
	 * @var string[] Addon info
	 */
	public static $addon_info = array();
	/**
	 * @var LP_Addon_Import_Export $addon
	 */
	public static $addon;

	/**
	 * Singleton.
	 *
	 * @return LP_Addon_Import_Export_Preload
	 */
	public static function instance(): LP_Addon_Import_Export_Preload {
		static $instance;
		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * LP_Addon_Import_Export_Preload constructor.
	 */
	protected function __construct() {
		$can_load = true;
		// Set Base name plugin.
		define( 'LP_ADDON_IMPORT_EXPORT_BASENAME', plugin_basename( LP_ADDON_IMPORT_EXPORT_FILE ) );

		// Set version addon for LP check .
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		self::$addon_info = get_file_data(
			LP_ADDON_IMPORT_EXPORT_FILE,
			array(
				'Name'               => 'Plugin Name',
				'Require_LP_Version' => 'Require_LP_Version',
				'Version'            => 'Version',
			)
		);

		define( 'LP_ADDON_IMPORT_EXPORT_VER', self::$addon_info['Version'] );
		define( 'LP_ADDON_IMPORT_EXPORT_REQUIRE_VER', self::$addon_info['Require_LP_Version'] );
		//Dirs and Urls
		define( 'LP_ADDON_IMPORT_EXPORT_URL', plugin_dir_url( __FILE__ ) );
		define( 'LP_ADDON_IMPORT_EXPORT_DIR', plugin_dir_path( __FILE__ ) );
		define( 'LP_ADDON_IMPORT_EXPORT_CONFIG_DIR', LP_ADDON_IMPORT_EXPORT_DIR . 'config/' );
		define( 'LP_ADDON_IMPORT_EXPORT_VIEWS', LP_ADDON_IMPORT_EXPORT_DIR . 'views/' );
		define( 'LP_ADDON_IMPORT_EXPORT_ASSETS_URL', LP_ADDON_IMPORT_EXPORT_URL . 'assets/' );
		define( 'LP_ADDON_IMPORT_EXPORT_PATH', dirname( LP_ADDON_IMPORT_EXPORT_FILE ) );
		define( 'LP_ADDON_IMPORT_EXPORT_INC', LP_ADDON_IMPORT_EXPORT_PATH . '/inc/' );
		define(
			'LP_ADDON_IMPORT_EXPORT_FOLDER_ROOT_NAME',
			str_replace(
				array( '/', basename( __FILE__ ) ),
				'',
				plugin_basename( __FILE__ )
			)
		);

		//Pages
		define( 'LP_ADDON_IMPORT_EXPORT_MIGRATION_PAGE', 'migration_page' );

		//Keys
		define( 'LP_ADDON_IMPORT_EXPORT_TUTOR_COURSE_CPT', 'courses' );
		define( 'LP_ADDON_IMPORT_EXPORT_TUTOR_COURSE_ENROLLED', 'tutor_enrolled' );
		define( 'LP_ADDON_IMPORT_EXPORT_TUTOR_TOPIC_CPT', 'topics' );
		define( 'LP_ADDON_IMPORT_EXPORT_TUTOR_LESSON_CPT', 'lesson' );
		define( 'LP_ADDON_IMPORT_EXPORT_TUTOR_QUIZ_CPT', 'tutor_quiz' );
		define( 'LP_ADDON_IMPORT_EXPORT_TUTOR_ASSIGNMENT_CPT', 'tutor_assignments' );

		// Check LP activated .
		if ( ! is_plugin_active( 'learnpress/learnpress.php' ) ) {
			$can_load = false;
		} elseif ( version_compare( LP_ADDON_IMPORT_EXPORT_REQUIRE_VER, get_option( 'learnpress_version', '3.0.0' ), '>' ) ) {
			$can_load = false;
		}

		if ( ! $can_load ) {
			add_action( 'admin_notices', array( $this, 'show_note_errors_require_lp' ) );
			deactivate_plugins( LP_ADDON_IMPORT_EXPORT_BASENAME );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return;
		}

		// Sure LP loaded.
		add_action( 'learn-press/ready', array( $this, 'load' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		include_once 'inc/load.php';
		self::$addon = LP_Addon_Import_Export::instance();

		require_once LP_ADDON_IMPORT_EXPORT_DIR . 'vendor/autoload.php';

		include_once LP_PLUGIN_PATH . 'inc/admin/editor/class-lp-admin-editor.php';
		include_once LP_PLUGIN_PATH . 'inc/curds/class-lp-course-curd.php';
		include_once LP_PLUGIN_PATH . 'inc/curds/class-lp-section-curd.php';
		include_once LP_PLUGIN_PATH . 'inc/curds/class-lp-question-curd.php';

		new EnqueueScriptsController();
		new AdminMenuController();

		if ( Plugin::is_tutor_active() ) {
			new TutorMigrationController();
			new MigrationPopupController();
		}
	}

	public function show_note_errors_require_lp() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					'Please active <strong>LearnPress version %1$s or later</strong> before active <strong>%2$s</strong>',
					LP_ADDON_IMPORT_EXPORT_REQUIRE_VER,
					self::$addon_info['Name']
				);
				?>
			</p>
		</div>
		<?php
	}
}

LP_Addon_Import_Export_Preload::instance();

