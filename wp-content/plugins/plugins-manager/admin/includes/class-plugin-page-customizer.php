<?php

namespace WTBP;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class that defines the plugin page customize functions
 *
 * @since      1.0.0
 */
class PluginPageCustomizer {

	/**
	 * @var Plugin
	 */
	public $plugin;
	#region Basic functions

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin = Plugin::instance();

		$this->define_admin();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 10, 1 );

		//ADD DEACTIVATE AND DELETE
		add_action( 'admin_notices', [ $this, 'bulk_action_admin_notice' ] );
		add_filter( 'bulk_actions-plugins', [ $this, 'register_bulk_action' ] );
		add_filter( 'bulk_actions-plugins-network', [ $this, 'register_bulk_action' ] );

		add_filter( 'handle_bulk_actions-plugins', [ $this, 'bulk_action_handler' ], 10, 3 );
		add_filter( 'handle_bulk_actions-plugins-network', [ $this, 'bulk_action_handler' ], 10, 3 );

		add_filter( 'plugin_action_links', [ $this, 'add_action_links' ], 10, 4 );
		add_action( 'current_screen', [ $this, 'plugin_screen_actions' ] );

		//ADD IMAGE COLUMN
		if ( $this->plugin->getOption( 'show_icons_column', 1 ) ) {
			add_filter( 'manage_plugins_columns', [ $this, 'add_plugins_column' ], 10, 1 );
			add_filter( 'manage_plugins-network_columns', [ $this, 'add_plugins_column' ], 10, 1 );
			add_action( 'manage_plugins_custom_column', [ $this, 'manage_plugins_column' ], 10, 3 );
		}

		//SORT
		add_action( 'manage_plugins_sortable_columns', [ $this, 'manage_plugins_sortable' ], 10, 1 );
		add_action( 'manage_plugins-network_sortable_columns', [ $this, 'manage_plugins_sortable' ], 10, 1 );
		add_filter( 'views_plugins', [ $this, 'add_filter_link' ], 10, 1 );
		add_filter( 'views_plugins-network', [ $this, 'add_filter_link' ], 10, 1 );

		//GIT
		if ( $this->plugin->getOption( 'show_git', 1 ) ) {
			add_filter( 'plugin_row_meta', [ $this, 'add_update_icons' ], 99999, 4 );
			add_filter( 'site_transient_update_plugins', [ $this, 'disable_plugin_updates_if_git' ], 10, 2 );
		}

		//autoupdate
		if ( $this->plugin->getOption( 'change_autoupdate', 1 ) ) {
			add_filter( 'plugin_auto_update_setting_html', [ $this, 'plugin_auto_update_html' ], 10, 3 );
		}

		//CHANGELOG
		if ( $this->plugin->getOption( 'show_changelog', 0 ) ) {
			$updates = get_site_transient( 'update_plugins' );
			if ( $updates && is_object( $updates ) && isset( $updates->response ) ) {
				foreach ( $updates->response as $update ) {
					if ( $update ) {
						add_action( "in_plugin_update_message-{$update->plugin}", [
							$this,
							'changelog_in_update_message',
						], 10, 2 );
					}
				}
			}
		}

	}

	/**
	 * Checks whether the git-repository is active in the plugin folder
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 */
	public function is_plugin_git( $plugin_file ) {
		$slug = explode( '/', $plugin_file );
		if ( isset( $slug[0] ) ) {
			$slug = $slug[0];
		} else {
			return false;
		}

		if ( file_exists( WP_PLUGIN_DIR . "/{$slug}/.git" ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the git-repository is active in the plugin folder
	 *
	 * @param $plugin_file
	 *
	 * @return string
	 */
	public function get_plugin_git_branch( $plugin_file ) {
		$slug = explode( '/', $plugin_file );
		if ( isset( $slug[0] ) ) {
			$slug = $slug[0];
		} else {
			return false;
		}

		$git_folder = WP_PLUGIN_DIR . "/{$slug}/.git";
		if ( file_exists( $git_folder ) ) {
			if ( file_exists( "{$git_folder}/HEAD" ) ) {
				$head = file_get_contents( "{$git_folder}/HEAD" );
				preg_match( '/ref:\s\D+\/\D+\/(.+)/', $head, $match );
				$branch = $match[1] ?? '';
			}
		}

		return $branch ?? '';
	}

	/**
	 * @param $meta
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $status
	 *
	 * @return mixed
	 */
	function add_update_icons( $meta, $plugin_file, $plugin_data, $status ) {

		if ( $this->is_plugin_git( $plugin_file ) ) {
			$meta[0] = "<span class='wtbp-git-version' title='" . __( 'This plugin is installed as a GIT repository!', 'bulk-plugins' ) . "'>{$meta[0]}</span>
                        <span class='wtbp-git-icon' title='" . __( 'This plugin is installed as a GIT repository!', 'bulk-plugins' ) . "'></span>";

			if ( $this->plugin->getOption( 'show_git_branch', 0 ) ) {
				$branch = $this->get_plugin_git_branch( $plugin_file );

				$meta[0] .= "<span class='wtbp-git-version-branch' title='{$branch}'>({$branch})</span>";
			}
		}

		if ( isset( $plugin_data['new_version'] ) && version_compare( $plugin_data['new_version'], $plugin_data['Version'], '>' ) && $this->is_plugin_git( $plugin_file ) ) {
			$update_href = wp_nonce_url( self_admin_url( "update.php?action=upgrade-plugin&plugin={$plugin_file}" ), "upgrade-plugin_{$plugin_file}" );
			$meta[]      = "<div class='update-message notice inline notice-warning notice-alt' style='display: inline-block;'><p style='margin: 0 !important;'><a href='{$update_href}'>Update to {$plugin_data['new_version']}</a></p></div>";
		}

		return $meta;
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function disable_plugin_updates_if_git( $value, $transient ) {
		if ( is_object( $value ) && isset( $value->response ) ) {
			foreach ( $value->response as $key => $item ) {
				if ( $this->is_plugin_git( $key ) ) {
					$value->no_update[ $key ] = $item;
					unset( $value->response[ $key ] );
				}
			}
		}

		return $value;
	}

	/**
	 * Admin styles and scripts
	 *
	 * @since     1.0.0
	 */
	public function admin_enqueue_scripts( $screen ) {
		if ( 'plugins.php' === $screen ) {
			wp_enqueue_style( WTBP_PLUGIN_PREFIX . 'admin-basic-style', WTBP_PLUGIN_URL . '/admin/assets/css/wtbp-admin.css', [], WTBP_VERSION );
			wp_enqueue_script( WTBP_PLUGIN_PREFIX . 'admin-basic-script', WTBP_PLUGIN_URL . '/admin/assets/js/wtbp-admin.js', [ 'jquery' ], WTBP_VERSION, false );
			wp_localize_script( WTBP_PLUGIN_PREFIX . 'admin-basic-script', 'wtbp_confirm', [
				'text' => __( 'Are you sure you want to deactivate and remove the plugin?', 'bulk-plugins' ),
			] );
		}

	}

	#endregion Basic functions

	#region Plugin Functions
	/**
	 * Settings page
	 */
	public function show_settings() {
		if ( isset( $_POST['wtbp_update_changelog'] ) && check_admin_referer( 'wtbp_save_settings' ) ) {
			update_option( WTBP_PLUGIN_PREFIX . 'update_changelog', (int) $_POST['wtbp_update_changelog'] );
			echo '<div id="message" class="updated"><p>' . __( 'Settings updated', 'bulk-plugins' ) . '</p></div>';
		}

		$options = [
			'update_changelog' => get_option( WTBP_PLUGIN_PREFIX . 'update_changelog', 1 ),
		];
		require_once WTBP_PLUGIN_PATH . 'admin/settings.php';
	}

	/**
	 * Register bulk option
	 *
	 * @return array(string)
	 */
	public function register_bulk_action( $bulk_actions ) {
		$bulk_actions['wtbp_deactivate_and_delete'] = __( 'Deactivate and delete', 'bulk-plugins' );

		return $bulk_actions;
	}

	/**
	 * Handler of bulk option
	 *
	 * @param $redirect_to
	 * @param $doaction
	 * @param $plugins
	 *
	 * @return string
	 */
	public function bulk_action_handler( $redirect_to, $doaction, $plugins ) {
		if ( $doaction !== 'wtbp_deactivate_and_delete' ) {
			return $redirect_to;
		}
		$dd_plugins = [];
		foreach ( $plugins as $plugin ) {
			if ( $this->deactivate_and_delete( $plugin ) ) {
				$dd_plugins[] = explode( '/', $plugin )[0] ?? '';
			}
		}

		$redirect_to = add_query_arg( [
			'wtbp_bulk_action' => $dd_plugins ? implode( ', ', $dd_plugins ) : __( 'You do not have enough permissions to remove or deactivate plugins', 'bulk-plugins' ),
		], $redirect_to );

		remove_all_filters( 'wp_redirect' );

		return $redirect_to;
	}

	/**
	 * Admin notice after bulk action
	 */
	public function bulk_action_admin_notice() {
		if ( empty( $_GET['wtbp_bulk_action'] ) ) {
			return;
		}

		$_SERVER['REQUEST_URI'] = remove_query_arg( [ 'wtbp_bulk_action' ], $_SERVER['REQUEST_URI'] );

		$data = htmlspecialchars( strip_tags( $_GET['wtbp_bulk_action'] ) );
		$msg  = '<b>' . __( 'Plugins deactivated and delete: ', 'bulk-plugins' ) . '</b>' . $data;
		echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
	}

	/**
	 * Admin notice after bulk action
	 */
	public function add_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		if ( is_plugin_active( $plugin_file ) ) {
			$actions[] = '<a href="' . add_query_arg( [
					'action' => 'deactivate_and_delete',
					'plugin' => rawurlencode( $plugin_file ),
				] ) . '" id="wtbp-delete-confirm">' . __( 'Delete', 'bulk-plugins' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Add Image column in plugins page
	 *
	 * @return array Columns
	 */
	public function add_plugins_column( $columns ) {
		//$first_column = reset($columns);
		$first_column          = array_shift( $columns );
		$first_column          = [ 'cb' => $first_column ];
		$image_column['image'] = __( 'Image', 'bulk-plugins' );

		return array_merge( $first_column, $image_column, $columns );
	}

	/**
	 * Manage Image column in plugins page
	 */
	public function manage_plugins_column( $column_name, $plugin_file, $plugin_data ) {
		if ( 'image' === $column_name ) {
			if ( isset( $plugin_data['icons'] ) && ! empty( $plugin_data['icons'] ) && is_array( $plugin_data['icons'] ) ) {
				$icon = $plugin_data['icons']['1x'] ?? ( $plugin_data['icons']['default'] ?? '' );
			}

			if ( ! empty( $icon ) ) {
				echo "<div class='wtbp-plugin-icon' style='background-image: url({$icon});'></div>";
			} else {
				$pluginChar = mb_substr( $plugin_data['Name'], 0, 1 );
				echo "<div class='wtbp-plugin-icon wtbp-plugin-icon-text'>{$pluginChar}</div>";
			}
		}
	}

	/**
	 * Add sortable column
	 */
	public function manage_plugins_sortable( $sortable_columns ) {
		$sortable_columns['name'] = [ 'Name', false ];

		// false = asc (по умолчанию), true  = desc

		return $sortable_columns;
	}

	/**
	 * Add filter on the Posts list tables.
	 */
	public function add_filter_link( $views ) {
		$views['plugins_filter'] = '<a href="#" class="wtbp_sort_plugins" data-sort="active">' . __( 'Sort plugins', 'bulk-plugins' ) . '</a>';

		return $views;

	}

	/**
	 * Deactivate and delete plugin
	 *
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function deactivate_and_delete( $plugin ) {
		if ( current_user_can( 'deactivate_plugin', $plugin ) ) {
			deactivate_plugins( $plugin, true );

			if ( current_user_can( 'delete_plugins' ) ) {
				delete_plugins( [ $plugin ] );

				return true;
			}
		}

		return false;
	}

	/**
	 * Hook action on plugins.php screen
	 */
	public function plugin_screen_actions( $screen ) {
		if ( 'plugins' == $screen->id || 'plugins-network' == $screen->id ) {
			if ( isset( $_GET['action'] ) && isset( $_GET['plugin'] ) && $_GET['action'] === 'deactivate_and_delete' ) {
				$pluginName = explode( '/', $_GET['plugin'] )[0] ?? '';
				$deleted    = $this->deactivate_and_delete( urldecode( $_GET['plugin'] ) );

				$_SERVER['REQUEST_URI'] = remove_query_arg( [ 'action', 'plugin' ], $_SERVER['REQUEST_URI'] );
				remove_all_filters( 'wp_redirect' );
				wp_safe_redirect( add_query_arg( [ 'wtbp_bulk_action' => $deleted ? $pluginName : __( 'You do not have enough permissions to remove or deactivate plugins', 'bulk-plugins' ) ] ) );
				exit;
			}
		}
	}

	/**
	 * @param $plugin_data
	 * @param $response
	 *
	 * @return void
	 */
	public function changelog_in_update_message( $plugin_data, $response ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		$cache_option_name = WTBP_PLUGIN_PREFIX . "_{$plugin_data['slug']}_{$plugin_data['new_version']}";
		$readme            = get_transient( $cache_option_name );

		if ( ! $readme ) {
			$api = plugins_api( 'plugin_information', [
				'slug' => wp_unslash( $plugin_data['slug'] ),
			] );
			if ( ! is_wp_error( $api ) ) {
				$matches = [];
				preg_match_all( '/^(<h1>|<h2>|<h3>|<h4>|<p>|<div>)' . $plugin_data['Version'] . '.*(<\/h1>|<\/h2>|<\/h3>|<\/h4>|<\/p>|<\/div>)$/m', $api->sections['changelog'], $matches );
				if ( isset( $matches[0][0] ) ) {
					$readme = substr( $api->sections['changelog'], 0, strpos( $api->sections['changelog'], $matches[0][0] ) );
				}
				set_transient( $cache_option_name, $readme, DAY_IN_SECONDS );
			}
		}
		echo "<div class='wtbp-update-message-changelog' style='display: block;'>{$readme}</div>";
	}


	/**
	 * @param $html
	 * @param $plugin_file
	 * @param $plugin_data
	 */
	public function plugin_auto_update_html( $html, $plugin_file, $plugin_data ) {
		$auto_updates = (array) get_site_option( 'auto_update_plugins', [] );
		$checked      = '';
		if ( in_array( $plugin_file, $auto_updates, true ) ) {
			$text    = __( 'Disable auto-updates' );
			$action  = 'disable';
			$checked = 'checked';
		} else {
			$text    = __( 'Enable auto-updates' );
			$action  = 'enable';
			$checked = '';
		}

		global $status, $page, $s, $totals;
		$query_args = [
			'action'        => "{$action}-auto-update",
			'plugin'        => $plugin_file,
			'paged'         => $page,
			'plugin_status' => $status,
		];

		$url = add_query_arg( $query_args, 'plugins.php' );

		$result[] = sprintf( '<div style="text-align: right;"><a href="%s" class="toggle-auto-update aria-button-if-js" data-wp-action="%s">', wp_nonce_url( $url, 'updates' ), $action );

		$result[] = '<span class="dashicons dashicons-update spin hidden" aria-hidden="true"></span>';
		$result[] = '</a>';
		$result[] = "<input type='checkbox' title='{$text}' class='wtb_autoupdate_checkbox' {$checked}></div>";
		$result   = implode( '', $result );

		return $result;
	}

	#endregion Plugin Functions
}
