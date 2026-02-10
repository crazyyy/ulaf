<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Export_Import_Settings class
 */
class Export_Import_Settings extends WP_Settings_API_Helper {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => Export_Import::OPTION_NAME,
				'option_name'  => Export_Import::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-export-import',
				'title'       => '',
				'description' => '',
				'menu_slug'   => Export_Import::OPTION_NAME,
				'option_name' => Export_Import::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Import Settings', 'admin-optimizer' ),
						'id'       => 'import-settings',
						'name'     => 'import_settings',
						'callback' => [ $this, 'render_import_field' ],
						'desc'     => __( 'Import settings from a JSON file. This will overwrite all current settings. Please make sure to backup your current settings before importing a new file.', 'admin-optimizer' ),
					],
					[
						'type'     => 'callback',
						'title'    => __( 'Export Settings', 'admin-optimizer' ),
						'id'       => 'export-settings',
						'name'     => 'export_settings',
						'callback' => [ $this, 'render_export_field' ],
						'desc'     => __( 'Export all your settings to a JSON file.', 'admin-optimizer' ),
					],
				],
			],
		];
		$this->setup();
	}

	/**
	 * Render export fields
	 *
	 * @param array $fields Setings fields.
	 */
	public function render_export_field( $fields ) {
		?>
		<button class="button button-primary" id="adminoptim-export-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'adminoptim-export-settings' ) ); ?>"><?php esc_html_e( 'Export to JSON', 'admin-optimizer' ); ?></button>
		<?php if ( ! empty( $fields['desc'] ) ) : ?>
			<p class="description"><?php echo esc_html( $fields['desc'] ); ?></p>
		<?php endif; ?>    
		<?php
	}

	/**
	 * Render import field
	 */
	public function render_import_field() {
		?>
		<input type="file" name="adminoptim_import_json" id="adminoptim_import_json" accept="application/json" class="import-field">
		<button class="button button-primary" id="adminoptim-import-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'adminoptim-import-settings' ) ); ?>"><?php esc_html_e( 'Import', 'admin-optimizer' ); ?></button>
		<div id="import-status"></div>
		<?php
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer Pro - Export/Import Settings', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Export_Import::OPTION_NAME, [ 'remove_submit_button' => true ] ); ?>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix Page hook.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Export_Import::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim-export-import', Export_Import::MODULE_URL . 'assets/js/export-import.min.js', [ 'jquery' ], filemtime( Export_Import::MODULE_PATH . 'assets/js/export-import.min.js' ), true );
			wp_enqueue_style( 'adminoptim-export-import', Export_Import::MODULE_URL . 'assets/css/export-import.min.css', [], filemtime( Export_Import::MODULE_PATH . 'assets/css/export-import.min.css' ) );
		}
	}
}