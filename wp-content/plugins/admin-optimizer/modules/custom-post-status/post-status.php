<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post_Status class
 */
class Post_Status {

	const MENU_SLUG = 'adminoptimizer-custom-post-status';
	const TERM_KEY  = 'post_status';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'custom-post-status/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'custom-post-status/';

	/**
	 * User Settings
	 *
	 * @var Post_Status_Settings
	 */
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new Post_Status_Settings();
		add_action( 'init', [ $this, 'register_post_status' ] );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );

		// Add custom statuses to the post states.
		add_filter( 'display_post_states', [ $this, 'add_custom_post_states' ], 9, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_scripts' ] );
	}

	/**
	 * Register Post Status
	 *
	 * @return void
	 */
	public function register_post_status() {
		// Register 'post_status' taxonomy to store user created post statuses.
		if ( ! taxonomy_exists( self::TERM_KEY ) ) {
			$args = [
				'hierarchical' => false,
				'label'        => false,
				'query_var'    => false,
				'rewrite'      => false,
				'show_ui'      => false,
			];
			register_taxonomy( self::TERM_KEY, 'post', $args );
		}

		$custom_post_statuses = self::get_custom_post_statuses();

		if ( ! empty( $custom_post_statuses ) ) {
			foreach ( $custom_post_statuses as $custom_status ) {
				// translators: %1$s: custom status name %2$s post count.
				$singular = sprintf( __( '%1$s <span class="count">(%2$s)</span>', 'admin-optimizer' ), $custom_status->name, '%s' );
				// translators: %1$s: custom status name %2$s post count.
				$plural      = sprintf( __( '%1$s <span class="count">(%2$s)</span>', 'admin-optimizer' ), $custom_status->name, '%s' );
				$label_count = [
					0          => $singular,
					1          => $plural,
					'singular' => $singular,
					'plural'   => $plural,
					'context'  => null,
					'domain'   => 'admin-optimizer',
				];
				register_post_status(
					$custom_status->slug,
					[
						'label'       => $custom_status->name,
						'protected'   => true,
						'_builtin'    => false,
						'label_count' => $label_count,
					]
				);
			}
		}
	}

	/**
	 * Get user defined custom post status
	 *
	 * @param array $extra_args  Extra arguments.
	 *
	 * @return int[]|string|string[]|\WP_Error|\WP_Term[]
	 */
	public static function get_custom_post_statuses( $extra_args = [] ) {
		$default_args = [
			'taxonomy'   => self::TERM_KEY,
			'hide_empty' => false,
		];
		if ( ! empty( $extra_args ) ) {
			$args = array_unique( array_merge( $default_args, $extra_args ) );
		} else {
			$args = $default_args;
		}
		return get_terms( $args );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Custom Post Status', 'admin-optimizer' ),
			__( 'Custom Post Status', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Add custom post states
	 *
	 * @param array    $states Post States.
	 * @param \WP_Post $post Post object.
	 *
	 * @return mixed
	 */
	public function add_custom_post_states( $states, $post ) {
		/* Receive the post status object by post status name */
		$post_status_object = get_post_status_object( $post->post_status );

		/* Checks if the label exists */
		if ( in_array( $post_status_object->label, $states, true ) ) {
			return $states;
		}

		$queried_status = $_REQUEST['post_status'] ?? ''; //phpcs:ignore
		if ( $queried_status === $post_status_object->name ) {
			// No need to display the post status if a specific status was already requested.
			return $states;
		}

		$states[ $post_status_object->name ] = $post_status_object->label;

		return $states;
	}

	/**
	 * Enqueue scripts for block editor
	 *
	 * @return void
	 */
	public function enqueue_block_scripts() {
		global $pagenow;
		$screen = get_current_screen();

		if ( in_array( $pagenow, [ 'post.php', 'edit.php', 'post-new.php', 'page.php', 'edit-pages.php', 'page-new.php' ], true ) ) {
			if ( $screen->is_block_editor() ) {
				wp_enqueue_script(
					'adminoptimizer-block-custom-status',
					self::MODULE_URL . 'block/dist/index.js',
					[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ],
					filemtime( self::MODULE_PATH . 'block/dist/index.js' ),
					false
				);

				wp_set_script_translations(
					'adminoptimizer-block-custom-status',
					'admin-optimizer',
					ADMINOPTIMIZER_PATH . 'languages'
				);
			}
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		global $pagenow, $post;

		$screen = get_current_screen();

		if ( in_array( $pagenow, [ 'post.php', 'edit.php', 'post-new.php' ], true ) ) {
			if ( ! $screen->is_block_editor() ) {
				wp_enqueue_script( 'adminoptimizer-append-custom-status', self::MODULE_URL . 'assets/js/custom-status-classic.min.js', [ 'jquery' ], filemtime( self::MODULE_PATH . 'assets/js/custom-status-classic.min.js' ), true );
			}
			$custom_statuses       = [];
			$block_custom_statuses = [
				[
					'slug' => 'pending',
					'name' => 'Pending Review',
				],
				[
					'slug' => 'draft',
					'name' => 'Draft',
				],
			];
			$post_status           = $post->post_status ?? '';
			$current_post_status   = [];
			$custom_post_statuses  = self::get_custom_post_statuses();
			if ( ! empty( $custom_post_statuses ) ) {
				foreach ( $custom_post_statuses as $custom_status ) {
					$custom_status_obj       = [
						'slug' => $custom_status->slug,
						'name' => $custom_status->name,
					];
					$custom_statuses[]       = $custom_status_obj;
					$block_custom_statuses[] = $custom_status_obj;
					if ( $post_status === $custom_status->slug ) {
						$current_post_status['slug'] = $custom_status->slug;
						$current_post_status['name'] = $custom_status->name;
					}
				}
			}
			wp_localize_script( 'adminoptimizer-append-custom-status', 'adminoptimizerCustomStatuses', $custom_statuses );
			wp_localize_script( 'adminoptimizer-append-custom-status', 'adminoptimizerCurrentStatus', $current_post_status );
			wp_localize_script( 'adminoptimizer-block-custom-status', 'adminoptimizerCustomStatuses', $block_custom_statuses );

		}
	}
}
