<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Helpers\Helper;
/**
 * Disable User Account class
 */
class Disable_User_Account {
	use Helper;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'bulk_actions-users', [ $this, 'bulk_disable_user_accounts' ] );
		add_action( 'restrict_manage_users', [ $this, 'add_nonce_field' ], 5 );
		add_filter( 'handle_bulk_actions-users', [ $this, 'handle_bulk_disable_user_accounts' ], 10, 3 );
		add_filter( 'manage_users_columns', [ $this, 'add_user_account_custom_columns' ] );
		add_action( 'manage_users_custom_column', [ $this, 'render_user_account_custom_columns' ], 10, 3 );
		add_filter( 'user_row_actions', [ $this, 'add_enable_disable_links' ], 10, 2 );
		add_action( 'admin_post_adminoptim-disable-user-account', [ $this, 'disable_user_account' ] );
		add_action( 'admin_post_adminoptim-enable-user-account', [ $this, 'enable_user_account' ] );
		add_filter( 'wp_authenticate_user', [ $this, 'maybe_disable_login' ], 1 );
	}

	/**
	 * Bulk options to enable/disable user accounts
	 *
	 * @param array $bulk_actions // Bulk actions array.
	 *
	 * @return array
	 */
	public function bulk_disable_user_accounts( $bulk_actions ) {
		$bulk_actions['adminoptim_enable_user_accounts']  = _x( 'Enable User Accounts', 'bulk action', 'admin-optimizer' );
		$bulk_actions['adminoptim_disable_user_accounts'] = _x( 'Disable User Accounts', 'bulk action', 'admin-optimizer' );

		return $bulk_actions;
	}

	/**
	 * Handler for bulk enabling/disabling user accounts
	 *
	 * @param string $redirect_url // Redirect url.
	 * @param string $action // Bulk action.
	 * @param array  $user_ids // User ids.
	 *
	 * @return string
	 */
	public function handle_bulk_disable_user_accounts( $redirect_url, $action, $user_ids ) {
		check_admin_referer( 'adminoptim-bulk-users', 'adminoptim-bulk-users-nonce' );

		if ( 'adminoptim_enable_user_accounts' === $action || 'adminoptim_disable_user_accounts' === $action ) {
			foreach ( $user_ids as $user_id ) {
				if ( $this->is_administrator( $user_id ) ) {
					continue;
				}
				$user_account_disabled = get_user_meta( $user_id, 'adminoptim_disable_account', true );
				switch ( $action ) {
					case 'adminoptim_disable_user_accounts':
						if ( empty( $user_account_disabled ) ) {
							update_user_meta( $user_id, 'adminoptim_disable_account', true );
						}
						break;
					case 'adminoptim_enable_user_accounts':
						if ( ! empty( $user_account_disabled ) && '1' === $user_account_disabled ) {
							delete_user_meta( $user_id, 'adminoptim_disable_account' );
						}
						break;
				}
			}
		}

		return $redirect_url;
	}

	/**
	 * Add custom columns to Users page.
	 *
	 * @param array $columns All columns.
	 *
	 * @return array
	 */
	public function add_user_account_custom_columns( $columns ) {
		if ( empty( $columns['adminoptim_account_status'] ) ) {
			$columns['adminoptim_account_status'] = __( 'User Account', 'admin-optimizer' );
		}

		return $columns;
	}

	/**
	 * Render Custom columns in User page
	 *
	 * @param string $value Custom column output.
	 * @param string $column_name Column name.
	 * @param int    $user_id ID of the currently-listed user.
	 *
	 * @return string
	 */
	public function render_user_account_custom_columns( $value, $column_name, $user_id ) {
		if ( 'adminoptim_account_status' === $column_name ) {
			$user_account_disabled = get_user_meta( $user_id, 'adminoptim_disable_account', true );
			$status                = __( 'Active', 'admin-optimizer' );
			if ( ! empty( $user_account_disabled ) && '1' === $user_account_disabled ) {
				$status = __( 'Disabled', 'admin-optimizer' );
			}
			return esc_html( $status );
		} else {
			return $value;
		}
	}

	/**
	 * Add nonce field to Users page
	 *
	 * @return void
	 */
	public function add_nonce_field() {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		wp_nonce_field( 'adminoptim-bulk-users', 'adminoptim-bulk-users-nonce' );
	}

	/**
	 * Add links to quick row
	 *
	 * @param array    $actions // Actions.
	 * @param \WP_User $user // User object.
	 *
	 * @return array
	 */
	public function add_enable_disable_links( $actions, $user ) {
		if ( ! isset( $actions['adminoptim_user_can_login'] ) ) {
			if ( ! $this->is_administrator( $user->ID ) ) {
				$user_account_disabled = get_user_meta( $user->ID, 'adminoptim_disable_account', true );
				if ( ! empty( $user_account_disabled ) && '1' === $user_account_disabled ) {
					// user account is already disabled, set the links to enable user account.
					$actions['adminoptim_user_can_login'] = sprintf(
						'<a href="%s">' . __( 'Enable account', 'admin-optimizer' ) . '</a>',
						$this->generate_link(
							[
								'action'  => 'adminoptim-enable-user-account',
								'user_id' => $user->ID,
								'nonce'   => wp_create_nonce( 'adminoptim-disable-user' . $user->ID . '-account' ),
							]
						)
					);
				} else {
					// user account is enabled, set the links to disable user account.
					$actions['adminoptim_user_can_login'] = sprintf(
						'<a href="%s">' . __( 'Disable account', 'admin-optimizer' ) . '</a>',
						$this->generate_link(
							[
								'action'  => 'adminoptim-disable-user-account',
								'user_id' => $user->ID,
								'nonce'   => wp_create_nonce( 'adminoptim-disable-user' . $user->ID . '-account' ),
							]
						)
					);
				}
			}
		}
		return $actions;
	}

	/**
	 * Generate links to output in quick row
	 *
	 * @param array $query // Link args.
	 *
	 * @return string
	 */
	private function generate_link( array $query ) {
		$default = [
			'action'  => 'adminoptim-disable-user-account',
			'user_id' => 0,
			'nonce'   => wp_create_nonce( 'adminoptimizer-disable-user-account' ),
		];
		$query   = shortcode_atts( $default, $query );
		return esc_url( add_query_arg( $query, admin_url( 'admin-post.php?' ) ), null, '&' );
	}

	/**
	 * Function to disable user account
	 *
	 * @return void
	 */
	public function disable_user_account() {
		$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		if ( empty( $user_id ) ) {
			wp_die( esc_html__( 'No user found.', 'admin-optimizer' ) );
		} elseif ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'adminoptim-disable-user' . $user_id . '-account' ) ) {
				wp_die( esc_html__( 'Unauthorized action.', 'admin-optimizer' ) );
		} else {
			if ( ! $this->is_administrator( $user_id ) ) {
				update_user_meta( $user_id, 'adminoptim_disable_account', true );
			}
			if ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
			} else {
				wp_safe_redirect( admin_url( 'users.php' ) );
			}
		}
	}

	/**
	 * Function to enable user account
	 *
	 * @return void
	 */
	public function enable_user_account() {
		$user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;
		if ( empty( $user_id ) ) {
			wp_die( esc_html__( 'No user found.', 'admin-optimizer' ) );
		} elseif ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'adminoptim-disable-user' . $user_id . '-account' ) ) {
				wp_die( esc_html__( 'Unauthorized action.', 'admin-optimizer' ) );
		} else {
			delete_user_meta( $user_id, 'adminoptim_disable_account' );
			if ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
			} else {
				wp_safe_redirect( admin_url( 'users.php' ) );
			}
		}
	}

	/**
	 * Authenticate user login and disable user account if necessary
	 *
	 * @param \WP_Error|\WP_User $user // User object.
	 *
	 * @return \WP_Error|\WP_User
	 */
	public function maybe_disable_login( $user ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		if ( isset( $user->ID ) ) {
			if ( ! $this->is_administrator( $user->ID ) ) {
				$is_account_disabled = get_user_meta( $user->ID, 'adminoptim_disable_account', true );
				if ( ! empty( $is_account_disabled ) && '1' === $is_account_disabled ) {
					// should return \WP_Error instead?
					wp_die( esc_html__( 'ERROR: This user account is disabled.', 'admin-optimizer' ) );
				}
			}
		}
		return $user;
	}
}
