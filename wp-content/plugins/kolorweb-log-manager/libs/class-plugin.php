<?php
/**
 * Plugin Class
 *
 * @package   kw-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 */

namespace KolorWeb\KWLogManager;

if ( ! defined( 'KWLOGMANAGER_BASE' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}


/**
 * Dependencies
 */
use KolorWeb\KWLogManager\Ajax;
use KolorWeb\KWLogManager\Characteristic\IsSingleton;
use KolorWeb\KWLogManager\Config;
use KolorWeb\KWLogManager\Log;
use KolorWeb\KWLogManager\Settings;


/**
 * Plugin class
 *
 * @since 1.0.0
 */
class Plugin {

	use IsSingleton;

	/**
	 * Check if current user has permission to access KolorWeb Log Manager.
	 *
	 * @var boolean Check if current user has permission to access KolorWeb Log Manager
	 *
	 * @since 1.0.1
	 */
	private $user_authorized = true;


	/**
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {

		register_activation_hook( KWLOGMANAGER_BASE . '/kw-log-manager.php', array( $this, 'plugin_activate' ) );
		register_deactivation_hook( KWLOGMANAGER_BASE . '/kw-log-manager.php', array( $this, 'plugin_deactivate' ) );

		// Register actions.
		add_action( 'plugins_loaded', array( $this, 'plugin_load_textdomain' ) );

		add_action( 'admin_init', array( $this, 'check_if_authorized' ), 1 );
		add_action( 'template_redirect', array( $this, 'add_dynamic_routes' ), 1, 1 );

		if ( \is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_navigation' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_css_and_js' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 900 );

			// Initialize ajax handler.
			Ajax::get_instance();
		}
	}

	/**
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	public function plugin_activate() {
		$config = Config::get_instance();
		$config->enable_debugging();
	}

	/**
	 * Deactivate plugin
	 *
	 * @since 1.0.0
	 */
	public function plugin_deactivate() {

		$config = Config::get_instance();
		$config->disable_debugging();
	}

	/**
	 * Load Text Domain.
	 *
	 * @since 1.0.1
	 */
	public function plugin_load_textdomain() {

		$path   = dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/';
		$result = load_plugin_textdomain( 'kolorweb-log-manager', false, $path );
	}

	/**
	 * Check if user has permission to access KolorWeb Log Manager
	 *
	 * @since 1.0.1
	 */
	public function check_if_authorized() {
		$authorized            = \current_user_can( 'manage_options' );
		$this->user_authorized = apply_filters( 'kwlm_user_authorized', $authorized );
	}

	/**
	 * Check if in customize preview mode.
	 */
	public function check_if_is_customize_preview() {
		if ( function_exists( 'is_customize_preview' ) ) {
			return is_customize_preview();
		} else {
			// Fallback for WordPress < 4.0 version.
			global $wp_customize;
			return ( $wp_customize instanceof WP_Customize_Manager ) && $wp_customize->is_preview();
		}
	}

	/**
	 * Enqueue css and js files when on plugin page
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_css_and_js() {

		if ( $this->user_authorized ) {

			$settings = Settings::get_instance();
			$log      = Log::get_instance();
			$config   = Config::get_instance();
			$user_id  = \get_current_user_id();
			$screen   = get_current_screen();

			$lang_settings = $this->get_lang_data();

			$localized = array(
				'api'              => admin_url( 'admin-ajax.php' ),
				'kwlm_nonce'       => wp_create_nonce( 'kwlm-nonce' ),
				'debug_enabled'    => $config->debug_enabled() ? 1 : 0,
				'debug_toggleable' => $config->is_debug_toggleable() ? 1 : 0,
				'current_page'     => is_object( $screen ) ? $screen->id : '',
				'plugin_url'       => admin_url( 'tools.php?page=kw-log-manager' ),
				'plugin_base_url'  => KWLOGMANAGER_URL,
				'settings'         => $settings->get_settings( $user_id ),
				'user_id'          => $user_id,
				'lang_settings'    => $lang_settings,
			);

			// In Customizer preview mode we do not need to enqueue CSS and JS files.
			if ( ! $this->check_if_is_customize_preview() ) {
				// Stylesheet files.
				wp_enqueue_style( 'kwlogmanager-css', KWLOGMANAGER_URL . 'assets/css/main.' . KWLOGMANAGER_VERSION . '.min.css', array(), KWLOGMANAGER_VERSION );

				// Javascript files.
				wp_enqueue_script( 'kwlogmanager-react-js', KWLOGMANAGER_URL . 'assets/js/react-framework.min.js', false, KWLOGMANAGER_VERSION, true );

				wp_enqueue_script( 'kwlogmanager-js', KWLOGMANAGER_URL . 'assets/js/main.' . KWLOGMANAGER_VERSION . '.min.js', array( 'kwlogmanager-react-js' ), KWLOGMANAGER_VERSION, true );

				// Localize variables.
				wp_localize_script( 'kwlogmanager-js', 'KWLOGMANAGER', $localized );
			}
		}
	}


	/**
	 * Add navigation entry
	 *
	 * @since 1.0.0
	 */
	public function add_navigation() {
		if ( $this->user_authorized ) {
			add_management_page( 'KolorWeb Log Manager', 'Log Manager', 'manage_options', 'kw-log-manager', array( $this, 'display_viewer_page' ) );
		}
	}


	/**
	 * Get main view
	 *
	 * @since 1.0.0
	 */
	public function display_viewer_page() {
		echo '<div id="kwlm-viewer-container" class="wrap"></div>';
	}


	/**
	 * Register dashboard widgets
	 *
	 * @since 1.0.0
	 */
	public function register_dashboard_widgets() {
		$show_dashboard_widget = apply_filters( 'kwlm_show_dashboard_widget', $this->user_authorized );

		if ( $show_dashboard_widget ) {
			\wp_add_dashboard_widget( 'kwlm-widget', 'KolorWeb Log Manager', array( $this, 'display_dashboard_widget' ) );
		}
	}


	/**
	 * Add dashboard widget
	 *
	 * @since 1.0.0
	 */
	public function display_dashboard_widget() {
		echo '<div id="kwlm-dashboard-widget-container"></div>';
	}


	/**
	 * Add admin bar menu.
	 *
	 * @param WP_Admin_Bar $admin_bar tha admin bar.
	 * @since 1.0.0
	 */
	public function add_admin_bar_menu( $admin_bar ) {

		$show_adminbar = apply_filters( 'kwlm_show_adminbar_widget', $this->user_authorized );

		if ( $show_adminbar ) {
			$admin_bar->add_node(
				array(
					'id'    => 'kwlm-menu',
					'title' => 'Log Manager',
					'href'  => admin_url( 'tools.php?page=kw-log-manager' ),
					'meta'  => array(
						'class' => 'kwlm-admin-bar-node',
					),
				)
			);
		}
	}


	/**
	 * Add dynamic routes
	 *
	 * @since 1.0.0
	 */
	public function add_dynamic_routes() {

		$log         = Log::get_instance();
		$settings    = Settings::get_instance();
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$url_path    = trailingslashit( explode( '?', $request_uri )[0] );

		if ( is_user_logged_in() ) {
			$user_id = \get_current_user_id();

			if ( '/debugging/download/log/' === $url_path ) {

				$can_download = apply_filters( 'kwlm_can_download_log', $this->user_authorized );

				if ( $can_download ) {

					header( 'Pragma: PUBLIC' );
					header( 'Content-Type: application/octet-stream; charset=utf-8' );
					header( 'Content-Disposition: attachment; filename="debug.log.html"' );
					header( 'HTTP/1.1 200 OK' );

					$config = $settings->get_settings( $user_id );

					if ( isset( $config['truncate_download'] ) && $config['truncate_download'] ) {

						$found   = array();
						$entries = $log->get_entries();

						foreach ( $entries as $entry ) {

							$key = md5( $entry['message'] );

							if ( ! isset( $found[ $key ] ) ) {

								$found[ $key ] = true;
								echo wp_kses_post( '[' . $entry['date'] . ' ' . $entry['time'] . ' ' . $entry['timezone'] . '] ' . $entry['message'] . '<br/>' );

							}
						}
					} else {

						echo wp_kses_post( trim( $log->get_contents() ) );

					}

					exit();

				}
			}
		}
	}


	/**
	 * Returns Languages Configuration
	 *
	 * @since 1.0.0
	 *
	 * @return Array $lang Languages String Data.
	 */
	public function get_lang_data() {

		// Lang Array Data.
		$lang = array(
			'loadEntries'  => __( 'Loading entries ...', 'kolorweb-log-manager' ),
			'enabled'      => __( 'Enabled', 'kolorweb-log-manager' ),
			'disabled'     => __( 'Disabled', 'kolorweb-log-manager' ),
			'goToDebug'    => __( 'Go to Log Viewer', 'kolorweb-log-manager' ),
			'simulating'   => __( 'Simulating', 'kolorweb-log-manager' ),
			'notFound'     => __( 'No entries found.', 'kolorweb-log-manager' ),
			'back'         => __( 'Back', 'kolorweb-log-manager' ),
			'close'        => __( 'Close', 'kolorweb-log-manager' ),
			'save'         => __( 'Save', 'kolorweb-log-manager' ),
			'update'       => __( 'Update', 'kolorweb-log-manager' ),
			'edit'         => __( 'Edit', 'kolorweb-log-manager' ),
			'delete'       => __( 'Delete', 'kolorweb-log-manager' ),
			'cancel'       => __( 'Cancel', 'kolorweb-log-manager' ),
			'disable'      => __( 'Disable', 'kolorweb-log-manager' ),
			'currentDate'  => __( 'Today', 'kolorweb-log-manager' ),
			'type'         => __( 'Type:', 'kolorweb-log-manager' ),
			'line'         => __( 'Line:', 'kolorweb-log-manager' ),
			'hideDetails'  => __( 'Hide details', 'kolorweb-log-manager' ),
			'moreDetails'  => __( 'More details', 'kolorweb-log-manager' ),
			'searchFor'    => __( 'Search for ...', 'kolorweb-log-manager' ),
			'searchingFor' => __( 'Searching for', 'kolorweb-log-manager' ),
			'seeHelp'      => __( 'See help', 'kolorweb-log-manager' ),
			'lastModified' => __( 'Last Modified', 'kolorweb-log-manager' ),
			'fileSize'     => __( 'Filesize', 'kolorweb-log-manager' ),
			'legend'       => __( 'Legend', 'kolorweb-log-manager' ),
			'legendInfo'   => __( 'Filter Errors by click on this labels...', 'kolorweb-log-manager' ),
			'othersErrors' => __( 'Other', 'kolorweb-log-manager' ),
			'loadWidget'   => __( 'Loading Widget Data ...', 'kolorweb-log-manager' ),
			'addCustomErr' => array(
				'name'    => __( 'Add Custom Error', 'kolorweb-log-manager' ),
				'label'   => __( 'Label', 'kolorweb-log-manager' ),
				'key'     => __( 'Error Key', 'kolorweb-log-manager' ),
				'color'   => __( 'Color', 'kolorweb-log-manager' ),
				'bgcolor' => __( 'Background', 'kolorweb-log-manager' ),
				'noErr'   => __( 'No custom error messages defined.', 'kolorweb-log-manager' ),
				'addNew'  => __( 'Add new', 'kolorweb-log-manager' ),
				'config'  => __( 'Feature must be configured.', 'kolorweb-log-manager' ),
			),
			'updCustomErr' => array(
				'name' => __( 'Edit Custom Error', 'kolorweb-log-manager' ),
			),
			'actions'      => array(
				'name'    => __( 'Actions', 'kolorweb-log-manager' ),
				'options' => array(
					'refresh'  => __( 'Refresh', 'kolorweb-log-manager' ),
					'clearLog' => __( 'Clear Log', 'kolorweb-log-manager' ),
					'download' => __( 'Download', 'kolorweb-log-manager' ),
				),
			),
			'sort'         => array(
				'name'    => __( 'Sort', 'kolorweb-log-manager' ),
				'options' => array(
					'newest' => __( 'By Newest', 'kolorweb-log-manager' ),
					'oldest' => __( 'By Oldest', 'kolorweb-log-manager' ),
				),
			),
			'view'         => array(
				'name'    => __( 'View as', 'kolorweb-log-manager' ),
				'options' => array(
					'group' => __( 'Group', 'kolorweb-log-manager' ),
					'list'  => __( 'List', 'kolorweb-log-manager' ),
				),
			),
			'notify'       => array(
				'notLoaded'      => __( 'Plugin could not be loaded.  Please try again.', 'kolorweb-log-manager' ),
				'debugStatus'    => __( 'Debbugging has been', 'kolorweb-log-manager' ),
				'logCleared'     => __( 'Log file successfully cleared', 'kolorweb-log-manager' ),
				'logClearedFail' => __( 'Failed to clear log file.  You might not have write permission', 'kolorweb-log-manager' ),
				'viewerUpdate'   => __( 'Viewer updated with new entries', 'kolorweb-log-manager' ),
				'noEntries'      => __( 'No new entries found', 'kolorweb-log-manager' ),
				'checkError'     => __( 'Checking for updates failed.', 'kolorweb-log-manager' ),
				'debugFileWarn'  => __( 'Debugging is enabled. However, the debug.log file does not exist or was not found.', 'kolorweb-log-manager' ),
				'debugDisabled'  => __( 'Debugging is currently disabled.', 'kolorweb-log-manager' ),
				'isDebug'        => __( 'Sorry, we could not detect if debugging is enabled or disabled.', 'kolorweb-log-manager' ),
				'howToDebug'     => __( 'How to Enable Debugging?', 'kolorweb-log-manager' ),
				'customErrorAdd' => __( 'Custom error successfully added', 'kolorweb-log-manager' ),
				'customErrorErr' => __( 'Please complete all required fields', 'kolorweb-log-manager' ),
				'customErrorUpd' => __( 'Custom error successfully updated', 'kolorweb-log-manager' ),
				'customErrorAsk' => __( 'Are you sure you want to delete', 'kolorweb-log-manager' ),
				'customErrorDel' => __( 'Custom error successfully deleted', 'kolorweb-log-manager' ),
				'customErrorDie' => __( 'Custom error could not be deleted', 'kolorweb-log-manager' ),
				'logLimitErr'    => __( 'Please insert only integer numbers values.', 'kolorweb-log-manager' ),
				'logLimitUpd'    => __( 'Log Limit was updated', 'kolorweb-log-manager' ),
			),
			'debugHelp'    => array(
				'intro'    => __( 'To turn on debugging, add the following to your wp-config.php file.', 'kolorweb-log-manager' ),
				'moreInfo' => __( 'For more information visit', 'kolorweb-log-manager' ),
			),
			'entry'        => __( 'entry', 'kolorweb-log-manager' ),
			'entries'      => __( 'entries', 'kolorweb-log-manager' ),
			'logEntries'   => __( 'Log Entries', 'kolorweb-log-manager' ),
			'settings'     => array(
				'name'        => __( 'Settings', 'kolorweb-log-manager' ),
				'enableDebug' => __( 'Enable Debug?', 'kolorweb-log-manager' ),
				'logLimit'    => __( 'Sets the maximum number of lines in the debug.log file to consider. Leave the value at zero to get them all.', 'kolorweb-log-manager' ),
				'fold'        => __( 'Fold sidebar to increase viewing area?', 'kolorweb-log-manager' ),
				'general'     => __( 'General', 'kolorweb-log-manager' ),
				'customError' => __( 'Custom Errors', 'kolorweb-log-manager' ),
			),
			'help'         => array(
				'name'      => __( 'Help', 'kolorweb-log-manager' ),
				'action'    => __( 'Action', 'kolorweb-log-manager' ),
				'learnMore' => __( 'Learn more here...', 'kolorweb-log-manager' ),
				'step'      => __( 'Step', 'kolorweb-log-manager' ),
				'sections'  => array(
					'toggleDebugging' => array(
						'question'     => __( 'How to toggle debugging status?', 'kolorweb-log-manager' ),
						'title'        => __( 'Toggle Debugging', 'kolorweb-log-manager' ),
						'p1'           => __( 'When configured, you can enable/disable WP_DEBUG with just one click.', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on "Settings", then click on the slider to enable/disable debugging', 'kolorweb-log-manager' ),
						'p2'           => __( 'If you do not see the debug toggle button, it means that the automatic WordPress debug activation procedure included in this Plugin has not been successful and you will have to proceed manually to edit the wp-config.php', 'kolorweb-log-manager' ),
					),
					'debugToggling'   => array(
						'question' => __( 'How to configure debug toggling?', 'kolorweb-log-manager' ),
						'title'    => __( 'Configure Debug Toggling', 'kolorweb-log-manager' ),
						'p1'       => __( 'When the plugin is activated, the system tries to automatically set all the DEBUG constants in your wp-config.php', 'kolorweb-log-manager' ),
						'p2'       => __( 'If toggle button does not appear in the settings means that your wp-config.php is not writable or not found in the default WordPress configuration path.', 'kolorweb-log-manager' ),
						'p3'       => __( 'If wp-config is not writable, you will have to manually set the values for DEBUG constants and you will not be able to use the one-click debug toggle function. The plugin will continue to work without problems, but without the magic of this feature.', 'kolorweb-log-manager' ),
						'step1_1'  => __( 'To manually enable WordPress debugging you can paste these lines of code into the wp-config.php file', 'kolorweb-log-manager' ),
						'step1_2'  => __( 'To manually disable WordPress debugging you can paste these lines of code into the wp-config.php file', 'kolorweb-log-manager' ),
						'step2_1'  => __( 'Refresh the page in the browser (reload page)', 'kolorweb-log-manager' ),
					),
					'foldSidebar'     => array(
						'question'     => __( 'How to fold sidebar to increase viewing space?', 'kolorweb-log-manager' ),
						'title'        => __( 'Fold Sidebar', 'kolorweb-log-manager' ),
						'p1'           => __( 'By default the sidebar will be folded when the log viewer is active. To disable, or toggle this behavior:', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on "Settings", then click on the slider to enable/disable sidebar folding', 'kolorweb-log-manager' ),
					),
					'addCustomErrors' => array(
						'question'     => __( 'How to add custom errors?', 'kolorweb-log-manager' ),
						'title'        => __( 'Manage Custom Errors', 'kolorweb-log-manager' ),
						'p1'           => __( 'Custom error messages allow you to create custom errors when testing, color code those errors in the viewer and filter the entries by those errors.', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'To add, edit or remove custom errors go to the "Settings" pane and click on the "Custom Errors" tab', 'kolorweb-log-manager' ),

					),
					'useCustomErrors' => array(
						'question' => __( 'How to use custom errors?', 'kolorweb-log-manager' ),
						'title'    => __( 'How to Use Custom Errors', 'kolorweb-log-manager' ),
						'p1_1'     => __( 'When you write an error to the log, you have to start the error message with a', 'kolorweb-log-manager' ),
						'p1_2'     => __( 'and the custom error key followed by a', 'kolorweb-log-manager' ),
						'p2'       => __( 'Example: If you defined a custom error with a key: my-custom-error', 'kolorweb-log-manager' ),
						'p3'       => __( 'In your code:', 'kolorweb-log-manager' ),
					),
					'sortEntries'     => array(
						'question'     => __( 'How to sort log entries?', 'kolorweb-log-manager' ),
						'title'        => __( 'Sort Entries', 'kolorweb-log-manager' ),
						'p1'           => __( 'Log entries can be sorted in descending or ascending order.', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on the A-Z icon.', 'kolorweb-log-manager' ),
						'action_li_02' => __( 'In the sidebar, click on the Z-A icon.', 'kolorweb-log-manager' ),
					),
					'helpView'        => array(
						'question'     => __( 'How to switch between group and list views?', 'kolorweb-log-manager' ),
						'title'        => __( 'Switch Between Group and List Views', 'kolorweb-log-manager' ),
						'p1'           => __( 'You can switch between Group and List views.', 'kolorweb-log-manager' ),
						'p2_h'         => __( 'Group View', 'kolorweb-log-manager' ),
						'p2'           => __( 'This view groups all similar entries and shows you just one entry with the latest timestamp for each error. It makes it much easier to analyze the log entries.', 'kolorweb-log-manager' ),
						'p3_h'         => __( 'List View', 'kolorweb-log-manager' ),
						'p3'           => __( 'This view lists every log entry which is similar to the standard log view.', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on the group icon.', 'kolorweb-log-manager' ),
						'action_li_02' => __( 'In the sidebar, click on the list icon.', 'kolorweb-log-manager' ),
					),
					'newLog'          => array(
						'question'     => __( 'How to check for new log entries?', 'kolorweb-log-manager' ),
						'title'        => __( 'Check For New Errors', 'kolorweb-log-manager' ),
						'p1_1'         => __( 'The plugin automatically check for new log errors every', 'kolorweb-log-manager' ),
						'p1_2'         => __( 'seconds and will update the view when new errors are found. There is no need to refresh the page.', 'kolorweb-log-manager' ),
						'p2'           => __( 'If you still want to manually check for new errors:', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on the refresh icon under "actions".', 'kolorweb-log-manager' ),
					),
					'clearLog'        => array(
						'question'     => __( 'How to clear the log file?', 'kolorweb-log-manager' ),
						'title'        => __( 'Clear Log', 'kolorweb-log-manager' ),
						'p1'           => __( 'If file permissions allow, the debug.log file will be truncated. If that fails, the file will be deleted. If the file cannot be truncated or deleted, an error will be displayed.', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on the clear icon under "actions".', 'kolorweb-log-manager' ),
					),
					'downloadLog'     => array(
						'question'     => __( 'How to download the log file?', 'kolorweb-log-manager' ),
						'title'        => __( 'Download Log', 'kolorweb-log-manager' ),
						'p1'           => __( 'When you click to download the log view, a smart log will be downloaded.  The smart log contains a unique entry for each error with the latest timestamp.  This helps make it much easier to review and can considerably reduce filesize.', 'kolorweb-log-manager' ),
						'action_li_01' => __( 'In the sidebar, click on the download icon under "actions".', 'kolorweb-log-manager' ),
					),

				),
			),
		);

		return $lang;
	}
}
