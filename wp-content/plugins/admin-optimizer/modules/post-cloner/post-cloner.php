<?php
namespace Yipresser\AdminOptimizer\Modules\Post_Cloner;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Helpers\Helper;

/**
 * Post Cloner Class
 */
class Post_Cloner {
	use Helper;
	use Cloner;

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var Post_Cloner_Settings_Pro
	 */
	protected $settings;

	const OPTION_NAME = 'adminoptim_post_cloner';

	const MENU_SLUG = 'adminoptimizer-post-cloner';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'post-cloner/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'post-cloner/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		$this->settings = new Post_Cloner_Settings( $this->options );

		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_action( 'init', [ $this, 'add_row_actions' ] );
		add_action( 'admin_action_adminoptim-clone-post', [ $this, 'create_clone_post' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Post Cloning', 'admin-optimizer' ),
			__( 'Post Cloning', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Hooks to the row_actions filters
	 *
	 * @return void
	 */
	public function add_row_actions() {
		if ( is_admin() ) {
			add_filter( 'post_row_actions', [ $this, 'row_actions' ], 10, 2 );
			add_filter( 'page_row_actions', [ $this, 'row_actions' ], 10, 2 );
		}
	}

	/**
	 * Row actions in actions
	 *
	 * @param array    $actions Array of actions.
	 * @param \WP_Post $post Post object.
	 *
	 * @return mixed
	 */
	public function row_actions( $actions, $post ) {
		$has_user_permission = $this->is_administrator( get_current_user_id() );

		if ( $has_user_permission ) {
			if ( ! empty( $this->options['post_types'] ) && in_array( $post->post_type, $this->options['post_types'], true ) ) {
				$actions['clone_post'] = '<a href="' . wp_nonce_url( admin_url( 'admin.php?action=adminoptim-clone-post&post=' . $post->ID ), 'adminoptim-clone-' . $post->ID ) . '" title="'
						. esc_attr( __( 'Clone this post', 'admin-optimizer' ) )
						. '">' . __( 'Clone', 'admin-optimizer' ) . '</a>';
			}
		}

		return $actions;
	}

	/**
	 * Function to handle the cloning link in row action
	 *
	 * @return void
	 */
	public function create_clone_post() {
		$clone_parent_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : null;

		$has_permission = $this->is_administrator( get_current_user_id() );

		if ( check_admin_referer( 'adminoptim-clone-' . $clone_parent_id ) && $has_permission ) {
			if ( $clone_parent_id ) {
				$clone_id = $this->clone_post( $clone_parent_id );

				if ( 0 < $clone_id && ! is_wp_error( $clone_id ) ) {
					if ( isset( $this->options['after_cloning'] ) && 'edit' === $this->options['after_cloning'] ) {
						wp_safe_redirect( get_edit_post_link( $clone_id, '&' ) );
					} else {
						wp_safe_redirect( admin_url( 'edit.php?post_type=' . get_post_type( $clone_parent_id ) ) );
					}
					exit;
				}
			} else {
				wp_die( esc_html__( 'Post cloning failed', 'admin-optimizer' ) );
			}
		}

		// if we didn't redirect out, then we fail.
		wp_die( esc_html__( 'Invalid Post ID', 'admin-optimizer' ) );
	}
}
