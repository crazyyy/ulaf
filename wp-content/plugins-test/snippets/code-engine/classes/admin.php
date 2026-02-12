<?php
class Meow_MWCODE_Admin extends MeowKit_MWCODE_Admin {

	public $core;

	public function __construct( $core ) {
		$this->core = $core;

		parent::__construct( MWCODE_PREFIX, MWCODE_ENTRY, MWCODE_DOMAIN, class_exists( 'MeowPro_MWCODE_Core' ) );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'app_menu' ) );

			// Load the scripts only if they are needed by the current screen
			// $page = isset( $_GET["page"] ) ? sanitize_text_field( $_GET["page"] ) : null;
			// $is_mwcode_screen = in_array( $page, [ 'mwcode_settings' ] );
			// $is_meowapps_dashboard = $page === 'meowapps-main-menu';


			// if ( $is_meowapps_dashboard || $is_mwcode_screen ) {
			// 	add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			// }

			$can_access_settings = $this->core->can_access_settings();
			$can_access_features = $this->core->can_access_features();

			if ( $can_access_settings || $can_access_features ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}
		}
	}

	function admin_enqueue_scripts() {

		// Load the scripts
		$physical_file = MWCODE_PATH . '/app/index.js';
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWCODE_VERSION;
		wp_register_script( 'mwcode_snippet_vault-vendor', MWCODE_URL . 'app/vendor.js',
			['wp-element', 'wp-i18n'], $cache_buster
		);
		wp_register_script( 'mwcode_snippet_vault', MWCODE_URL . 'app/index.js',
			['mwcode_snippet_vault-vendor', 'wp-i18n', 'wp-blocks', 'wp-block-editor', 'wp-components'], $cache_buster
		);
		wp_set_script_translations( 'mwcode_snippet_vault', 'code-engine' );
		wp_enqueue_script('mwcode_snippet_vault' );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', '//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		// Localize and options
		wp_localize_script( 'mwcode_snippet_vault', 'mwcode_snippet_vault', [
			'api_url' => rest_url( 'code-engine/v1' ),
			'rest_url' => rest_url(),
			'plugin_url' => MWCODE_URL,
			'prefix' => MWCODE_PREFIX,
			'domain' => MWCODE_DOMAIN,
			'is_pro' => class_exists( 'MeowPro_MWCODE_Core' ),
			'is_registered' => !!$this->is_registered(),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'options' => $this->core->get_all_options(),
		] );
	}

	function is_registered() {
		return apply_filters( MWCODE_PREFIX . '_meowapps_is_registered', false, MWCODE_PREFIX );
	}

	function app_menu() {
		add_submenu_page( 'meowapps-main-menu', 'Code Engine', 'Code Engine', 'manage_options',
			'mwcode_settings', array( $this, 'admin_settings' ) );
	}

	function admin_settings() {
		echo '<div id="mwcode-admin-settings"></div>';
	}
}

?>