<?php

namespace Yipresser\AdminOptimizer\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ADMINOPTIMIZER_ADMIN_URI' ) ) {
	define( 'ADMINOPTIMIZER_ADMIN_URI', ADMINOPTIMIZER_URI . 'admin/' );
}

if ( ! defined( 'ADMINOPTIMIZER_ADMIN_PATH' ) ) {
	define( 'ADMINOPTIMIZER_ADMIN_PATH', ADMINOPTIMIZER_PATH . 'admin/' );
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Admin_Settings class
 */
class Admin_Settings extends WP_Settings_API_Helper {
	/**
	 * A list of all the nav tabs
	 *
	 * @var array
	 */
	private $nav_tab = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ], 1 );
		add_action( 'update_option_' . MODULES_OPTION, [ $this, 'cleanup_as_schedule' ], 10, 3 );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => MODULES_OPTION,
				'option_name'  => MODULES_OPTION,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];
		$settings_sections      = apply_filters( 'adminoptimizer_settings_sections', [] );
		if ( ! empty( $settings_sections ) ) {
			$this->settings_sections = $settings_sections;
		}
		$nav_tab = [];
		$nav_tab = apply_filters( 'adminoptimizer_settings_navtab', $nav_tab );
		if ( ! empty( $nav_tab ) ) {
			$this->nav_tab = $nav_tab;
		}
		$this->setup();
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Admin Optimizer Modules', 'admin-optimizer' ),
			__( 'Modules', 'admin-optimizer' ),
			'manage_options',
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			[ $this, 'render_menu_page' ]
		);
	}

	/**
	 * Render menu page
	 *
	 * @return void
	 */
	public function render_menu_page() {
		?>
		<div class="wrap">
			<div id="adminoptim-header-wrap" class="sticky">
				<h1><?php esc_html_e( 'Admin Optimizer', 'admin-optimizer' ); ?></h1>
				<div>
					<button id="adminoptim-submit-btn" class="button button-primary adminoptim-submit-btn"><?php esc_html_e( 'Save Changes', 'admin-optimizer' ); ?></button>
					<a href="<?php echo esc_url( 'https://www.adminoptimizer.com/#pricing' ); ?>" class="button adminoptim-pro-btn" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'admin-optimizer' ); ?></a>
				</div>
			</div>
			<?php settings_errors(); ?>
			<?php $this->render_settings( MODULES_OPTION ); ?>
			<div class="adminoptim-pro-strip">
				<?php
					// translators: %s is the outgoing link to the Pro site.
					$promotion_message = sprintf( __( 'Get more optimization with %1$sAdmin Optimizer Pro%2$s', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' );
					echo wp_kses( $promotion_message, 'a' );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Settings page
	 *
	 * @param string $section Settings section.
	 *
	 * @return void
	 */
	public function render_settings( $section ) {
		?>
		<div class="adminoptim-content">
			<nav class="adminoptim-nav-wrapper" id="adminoptim-nav-wrapper">
				<div class="adminoptim-sticky">
				<?php
				$tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'content-management'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( ! array_key_exists( $tab, $this->nav_tab ) ) {
					$tab = 'content-management';
				}
				$i = 1;
				foreach ( $this->nav_tab as $key => $args ) :
					?>
					<div class="adminoptim-nav-tab">
						<button id="tab-btn-<?php echo esc_attr( $key ); ?>" class="adminoptim-tab-btn" aria-selected="<?php echo ( $tab === $key ) ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $key ); ?>" role="tab" type="button" tabindex="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $args['title'] ); ?></button>
					</div>
					<?php ++$i; ?>
				<?php endforeach; ?>
				</div>
			</nav>
			<div class="adminoptim-tab-content-wrap">
				<form id="adminoptim-modules-form" action="<?php echo esc_url( admin_url( 'options.php' ), null, '&' ); ?>" method="post">
					<?php
					settings_fields( $section );
					foreach ( $this->nav_tab as $key => $args ) :
						if ( $tab === $key ) {
							$content_visibility = 'section-visible';
						} else {
							$content_visibility = 'section-hidden';
						}
						?>
						<div class="tab-content <?php echo esc_attr( $content_visibility ); ?>" id="tab-content-<?php echo esc_html( $key ); ?>" aria-labelledby="<?php echo esc_attr( $key ); ?>" role="tabpanel">
						<?php do_settings_sections( $args['slug'] ); ?>
						</div>
					<?php endforeach; ?>
					<p class="submit">
						<button class="button button-primary adminoptim-submit-btn"><?php esc_html_e( 'Save Changes', 'admin-optimizer' ); ?></button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $options Setting options.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		if ( ! empty( $options ) ) {
			return array_map( 'absint', $options );
		} else {
			return [];
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix Enqueue script only on specific page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		wp_register_style( 'adminoptim-modules-pro-settings', ADMINOPTIMIZER_ADMIN_URI . 'assets/css/modules-pro-settings.min.css', [], filemtime( ADMINOPTIMIZER_ADMIN_PATH . 'assets/css/modules-pro-settings.min.css' ) );
		if ( 'toplevel_page_' . ADMINOPTIMIZER_MODULES_MENU_SLUG === $hook_suffix ) {
			wp_enqueue_script( 'adminoptim-modules-settings', ADMINOPTIMIZER_ADMIN_URI . 'assets/js/settings.min.js', [], filemtime( ADMINOPTIMIZER_ADMIN_PATH . 'assets/js/settings.min.js' ), true );
			wp_enqueue_style( 'adminoptim-modules-settings', ADMINOPTIMIZER_ADMIN_URI . 'assets/css/settings.min.css', [], filemtime( ADMINOPTIMIZER_ADMIN_PATH . 'assets/css/settings.min.css' ) );
		}
	}

	/**
	 * Disable Action Scheduler schedule if a module is deactivated
	 *
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 * @param string $option_name    Option name.
	 *
	 * @return void
	 */
	public function cleanup_as_schedule( $old_value, $value, $option_name ) {
		if ( MODULES_OPTION === $option_name && is_array( $value ) ) {
			// Check if Publish Missed Posts module has been deactivated.
			if ( empty( $value['enable_publish_missed_posts'] ) ) {
				if ( as_has_scheduled_action( 'adminoptim_publish_missed_post' ) ) {
					as_unschedule_action( 'adminoptim_publish_missed_post' );
				}
			}
			// Check if database cleaner module has been deactivated.
			if ( empty( $value['enable_db_cleaner'] ) ) {
				if ( as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
					as_unschedule_action( 'adminoptim_database_cleanup' );
				}
			}
		}
	}
}