<?php
class Meow_MWCODE_Admin extends MeowCommon_Admin {

	public $core;

	public function __construct( $core ) {
		$this->core = $core;

		parent::__construct( MWCODE_PREFIX, MWCODE_ENTRY, MWCODE_DOMAIN, class_exists( 'MeowPro_MWCODE_Core' ) );

		if ( is_admin() ) {
			// Create the menu
			add_action( 'admin_menu', array( $this, 'app_menu' ) );

			// Handle admin notice actions
			add_action( 'admin_init', array( $this, 'handle_notice_action' ) );

			// Display the admin notice (if due)
			add_action( 'admin_notices', array( $this, 'maybe_show_notice' ) );

			// Load scripts only on relevant screens
			$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : null;
			$is_mwcode_screen      = in_array( $page, [ 'mwcode_settings' ] );
			$is_meowapps_dashboard = $page === 'meowapps-main-menu';

			if ( $is_meowapps_dashboard || $is_mwcode_screen ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}
		}
	}

	/**
	 * Handle “Do it later” action.
	 */
	public function handle_notice_action() {
		if ( isset( $_GET['mwcode_notice_action'] ) && $_GET['mwcode_notice_action'] === 'later' ) {
			if ( current_user_can( 'manage_options' ) ) {
				// Hide notice for 1 month
				update_user_meta( get_current_user_id(), 'mwcode_notice_next_show', strtotime( '+1 month' ) );
			}
			wp_safe_redirect( remove_query_arg( 'mwcode_notice_action' ) );
			exit;
		}
	}

	/**
	 * Show the notice if it's time.
	 */
	public function maybe_show_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return; // Show only to admins
		}

		$next_show = get_user_meta( get_current_user_id(), 'mwcode_notice_next_show', true );
		if ( empty( $next_show ) ) {
			$next_show = 0;
		}
		// If the stored timestamp hasn't passed, don't show the notice
		if ( time() < $next_show ) {
			return;
		}
		?>
		<div class="notice notice-warning" style="position:relative;">
			<p><strong>🎉 Snippet Vault becomes Code Engine!</strong></p>
			<p>
				The plugin <strong>Snippet Vault</strong> has been renamed and moved to <strong>Code Engine</strong> to better fit the Meow Apps suite.
				<b>Please install <a href="https://wordpress.org/plugins/code-engine/" target="_blank" rel="noopener noreferrer">Code Engine</a>.</b>
				Your snippets will remain intact even if you remove Snippet Vault. No further updates will be done on Snippet Vault.
			</p>
			<p>
				<!-- Opens Code Engine plugin page in a new tab -->
				<a 
					href="https://wordpress.org/plugins/code-engine/" 
					class="button button-primary" 
					target="_blank" 
					rel="noopener noreferrer"
				>
					Install Code Engine
				</a>
				&nbsp;
				<!-- Hides notice for 1 month -->
				<a 
					href="<?php echo esc_url( add_query_arg( 'mwcode_notice_action', 'later' ) ); ?>" 
					class="button"
				>
					Do it later
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts only on the plugin’s pages or the Meow Apps dashboard.
	 */
	public function admin_enqueue_scripts() {
		// Load the scripts
		$physical_file = MWCODE_PATH . '/app/index.js';
		$cache_buster  = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWCODE_VERSION;

		wp_register_script(
			'mwcode_snippet_vault-vendor',
			MWCODE_URL . 'app/vendor.js',
			[ 'wp-element', 'wp-i18n' ],
			$cache_buster
		);
		wp_register_script(
			'mwcode_snippet_vault',
			MWCODE_URL . 'app/index.js',
			[ 'mwcode_snippet_vault-vendor', 'wp-i18n' ],
			$cache_buster
		);

		wp_set_script_translations( 'mwcode_snippet_vault', 'code-engine' );
		wp_enqueue_script( 'mwcode_snippet_vault' );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', '//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap' );
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		// Localize and options
		wp_localize_script( 'mwcode_snippet_vault', 'mwcode_snippet_vault', [
			'api_url'       => rest_url( 'code-engine/v1' ),
			'rest_url'      => rest_url(),
			'plugin_url'    => MWCODE_URL,
			'prefix'        => MWCODE_PREFIX,
			'domain'        => MWCODE_DOMAIN,
			'is_pro'        => class_exists( 'MeowPro_MWCODE_Core' ),
			'is_registered' => !! $this->is_registered(),
			'rest_nonce'    => wp_create_nonce( 'wp_rest' ),
			'options'       => $this->core->get_all_options(),
		] );
	}

	/**
	 * Check if the plugin is registered (Pro version, etc.).
	 */
	public function is_registered() {
		return apply_filters( MWCODE_PREFIX . '_meowapps_is_registered', false, MWCODE_PREFIX );
	}

	/**
	 * Create the submenu in the WordPress admin under Meow Apps main menu.
	 */
	public function app_menu() {
		add_submenu_page(
			'meowapps-main-menu',
			'Code Engine',
			'Code Engine',
			'manage_options',
			'mwcode_settings',
			array( $this, 'admin_settings' )
		);
	}

	/**
	 * Render the settings page (React app container).
	 */
	public function admin_settings() {
		echo '<div id="mwcode-admin-settings"></div>';
	}
}
