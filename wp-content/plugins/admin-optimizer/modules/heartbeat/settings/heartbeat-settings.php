<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Heartbeat_Settings class
 */
class Heartbeat_Settings extends WP_Settings_API_Helper {
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
				'option_group' => Heartbeat_Control::OPTION_NAME,
				'option_name'  => Heartbeat_Control::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-hearbeat-admin',
				'title'       => __( 'On Admin Pages', 'admin-optimizer' ),
				'description' => '',
				'menu_slug'   => Heartbeat_Control::OPTION_NAME,
				'option_name' => Heartbeat_Control::OPTION_NAME,
				'fields'      => [
					[
						'type'    => 'radio',
						'title'   => __( 'Heartbeat Behavior', 'admin-optimizer' ),
						'choices' => [
							'default' => __( 'Keep to default settings', 'admin-optimizer' ),
							'modify'  => __( 'Modify the heartbeat frequency', 'admin-optimizer' ),
							'disable' => __( 'Disable the heartbeat', 'admin-optimizer' ),
						],
						'default' => 'default',
						'id'      => 'admin',
						'name'    => 'admin',
						'desc'    => '',
					],
					[
						'type'     => 'select',
						'title'    => __( 'Heartbeat Frequency', 'admin-optimizer' ),
						'choices'  => [
							'15'  => __( '15 seconds', 'admin-optimizer' ),
							'30'  => __( '30 seconds', 'admin-optimizer' ),
							'45'  => __( '45 seconds', 'admin-optimizer' ),
							'60'  => __( '1 minute', 'admin-optimizer' ),
							'120' => __( '2 minutes', 'admin-optimizer' ),
							'180' => __( '3 minutes', 'admin-optimizer' ),
							'240' => __( '4 minutes', 'admin-optimizer' ),
							'300' => __( '5 minutes', 'admin-optimizer' ),
						],
						'default'  => '60',
						'id'       => 'admin-freq',
						'name'     => 'admin_freq',
						'disabled' => true,
						'desc'     => __( 'Change the heartbeat frequency. Default is 1 minute.', 'admin-optimizer' ),
					],
				],
			],
			[
				'id'          => 'adminoptimizer-heartbeat-frontend',
				'title'       => __( 'On Frontend', 'admin-optimizer' ),
				'description' => '',
				'menu_slug'   => Heartbeat_Control::OPTION_NAME,
				'option_name' => Heartbeat_Control::OPTION_NAME,
				'fields'      => [
					[
						'type'    => 'radio',
						'title'   => __( 'Heartbeat Behavior', 'admin-optimizer' ),
						'choices' => [
							'default' => __( 'Keep to default settings', 'admin-optimizer' ),
							'modify'  => __( 'Modify the heartbeat frequency', 'admin-optimizer' ),
							'disable' => __( 'Disable the heartbeat', 'admin-optimizer' ),
						],
						'default' => 'default',
						'id'      => 'frontend',
						'name'    => 'frontend',
						'desc'    => '',
					],
					[
						'type'     => 'select',
						'title'    => __( 'Heartbeat Frequency', 'admin-optimizer' ),
						'choices'  => [
							'15'  => __( '15 seconds', 'admin-optimizer' ),
							'30'  => __( '30 seconds', 'admin-optimizer' ),
							'45'  => __( '45 seconds', 'admin-optimizer' ),
							'60'  => __( '1 minute', 'admin-optimizer' ),
							'120' => __( '2 minutes', 'admin-optimizer' ),
							'180' => __( '3 minutes', 'admin-optimizer' ),
							'240' => __( '4 minutes', 'admin-optimizer' ),
							'300' => __( '5 minutes', 'admin-optimizer' ),
						],
						'default'  => '60',
						'id'       => 'frontend-freq',
						'name'     => 'frontend_freq',
						'disabled' => true,
						'desc'     => __( 'Change the heartbeat frequency. Default is 1 minute.', 'admin-optimizer' ),
					],
				],
			],
			[
				'id'          => 'adminoptimizer-heartbeat-editor',
				'title'       => __( 'On Post Editor', 'admin-optimizer' ),
				'description' => '',
				'menu_slug'   => Heartbeat_Control::OPTION_NAME,
				'option_name' => Heartbeat_Control::OPTION_NAME,
				'fields'      => [
					[
						'type'    => 'radio',
						'title'   => __( 'Heartbeat Behavior', 'admin-optimizer' ),
						'choices' => [
							'default' => __( 'Keep to default settings', 'admin-optimizer' ),
							'modify'  => __( 'Modify the heartbeat frequency', 'admin-optimizer' ),
							'disable' => __( 'Disable the heartbeat', 'admin-optimizer' ),
						],
						'default' => 'default',
						'id'      => 'editor',
						'name'    => 'editor',
						'desc'    => '',
					],
					[
						'type'     => 'select',
						'title'    => __( 'Heartbeat Frequency', 'admin-optimizer' ),
						'choices'  => [
							'15'  => __( '15 seconds', 'admin-optimizer' ),
							'30'  => __( '30 seconds', 'admin-optimizer' ),
							'45'  => __( '45 seconds', 'admin-optimizer' ),
							'60'  => __( '1 minute', 'admin-optimizer' ),
							'120' => __( '2 minutes', 'admin-optimizer' ),
							'180' => __( '3 minutes', 'admin-optimizer' ),
							'240' => __( '4 minutes', 'admin-optimizer' ),
							'300' => __( '5 minutes', 'admin-optimizer' ),
						],
						'default'  => '60',
						'id'       => 'editor-freq',
						'name'     => 'editor_freq',
						'disabled' => true,
						'desc'     => __( 'Change the heartbeat frequency. Default is 1 minute.', 'admin-optimizer' ),
					],
				],
			],
		];
		$this->setup();
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Heartbeat Control', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Heartbeat_Control::OPTION_NAME ); ?>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix  Check if we are on the right page before enqueueing script.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Heartbeat_Control::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim-heartbeat-settings', Heartbeat_Control::MODULE_URL . 'assets/js/heartbeat-settings.min.js', [], filemtime( Heartbeat_Control::MODULE_PATH . 'assets/js/heartbeat-settings.min.js' ), true );
		}
	}

	/**
	 * Callback function to sanitize user options
	 *
	 * @param array $options User options.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		$sanitized_options = [];
		$frequency         = [ '15', '30', '45', '60', '120', '180', '240', '300' ];
		if ( is_array( $options ) ) {
			if ( isset( $options['admin'] ) ) {
				switch ( $options['admin'] ) {
					case 'default':
					case 'disable':
						$sanitized_options['admin'] = sanitize_text_field( $options['admin'] );
						break;
					case 'modify':
						$sanitized_options['admin'] = 'modify';
						if ( isset( $options['admin_freq'] ) ) {
							$sanitized_options['admin_freq'] = in_array( $options['admin_freq'], $frequency, true ) ? sanitize_text_field( $options['admin_freq'] ) : '60';
						}
						break;

				}
			}
			if ( isset( $options['frontend'] ) ) {
				switch ( $options['frontend'] ) {
					case 'default':
					case 'disable':
						$sanitized_options['frontend'] = sanitize_text_field( $options['frontend'] );
						break;
					case 'modify':
						$sanitized_options['frontend'] = 'modify';
						if ( isset( $options['frontend_freq'] ) ) {
							$sanitized_options['frontend_freq'] = in_array( $options['frontend_freq'], $frequency, true ) ? sanitize_text_field( $options['frontend_freq'] ) : '60';
						}
						break;

				}
			}
			if ( isset( $options['editor'] ) ) {
				switch ( $options['editor'] ) {
					case 'default':
					case 'disable':
						$sanitized_options['editor'] = sanitize_text_field( $options['editor'] );
						break;
					case 'modify':
						$sanitized_options['editor'] = 'modify';
						if ( isset( $options['editor_freq'] ) ) {
							$sanitized_options['editor_freq'] = in_array( $options['editor_freq'], $frequency, true ) ? sanitize_text_field( $options['editor_freq'] ) : '60';
						}
						break;

				}
			}
		}
		return $sanitized_options;
	}
}