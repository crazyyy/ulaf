<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Custom_Login_Url_Settings class
 */
class Custom_Login_Url_Settings extends WP_Settings_API_Helper {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param array $options  User Options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		add_action( 'admin_init', [ $this, 'init' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => Custom_Login_Url::OPTION_NAME,
				'option_name'  => Custom_Login_Url::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-custom-login',
				'title'       => '',
				'description' => '',
				'menu_slug'   => Custom_Login_Url::OPTION_NAME,
				'option_name' => Custom_Login_Url::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Custom Login URl', 'admin-optimizer' ),
						'id'       => 'custom-url',
						'name'     => 'custom_slug',
						'desc'     => __( 'Change the default login URL and prevent public access to the login page.', 'admin-optimizer' ),
						'callback' => [ $this, 'render_custom_login_url_field' ],
					],
					[
						'type'     => 'callback',
						'title'    => __( 'Redirection Login URl', 'admin-optimizer' ),
						'id'       => 'redirection-url',
						'name'     => 'redirection_slug',
						'desc'     => __( 'Redirect URL when someone tries to access the wp-login.php page and the wp-admin directory while not logged in.', 'admin-optimizer' ),
						'callback' => [ $this, 'render_redirection_url_field' ],
					],
				],
			],
		];
		$this->setup();
	}

	/**
	 * Callback to render Custom Login URL field
	 *
	 * @return void
	 */
	public function render_custom_login_url_field() {
		$value = $this->options['custom_slug'] ?? 'login';
		?>
		<code><?php echo esc_url( home_url( '/' ) ); ?></code> <input id="custom-url" type="text" name="<?php echo esc_attr( Custom_Login_Url::OPTION_NAME ); ?>[custom_slug]" value="<?php echo esc_attr( $value ); ?>"> <code>/</code>
		<p class="description"><?php esc_html_e( 'Change the default login URL and prevent public access to the login page.', 'admin-optimizer' ); ?></p>
		<?php
	}

	/**
	 * Callback to render Redirection URL field
	 *
	 * @return void
	 */
	public function render_redirection_url_field() {
		$value = $this->options['redirection_slug'] ?? '404';
		?>
		<code><?php echo esc_url( home_url( '/' ) ); ?></code> <input id="redirection-url" type="text" name="<?php echo esc_attr( Custom_Login_Url::OPTION_NAME ); ?>[redirection_slug]" value="<?php echo esc_attr( $value ); ?>"> <code>/</code>
		<p class="description"><?php esc_html_e( 'Redirect URL when someone tries to access the wp-login.php page and the wp-admin directory while not logged in.', 'admin-optimizer' ); ?></p>
		<?php
	}

	/**
	 * Callback to sanitize user options
	 *
	 * @param array $options User options.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		if ( isset( $options['custom_slug'] ) ) {
			$options['custom_slug'] = sanitize_title_with_dashes( $options['custom_slug'] );
		}
		if ( isset( $options['redirection_slug'] ) ) {
			$options['redirection_slug'] = sanitize_title_with_dashes( $options['redirection_slug'] );
		}
		return $options;
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Custom Login URL', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Custom_Login_Url::OPTION_NAME ); ?>
		</div>
		<?php
	}
}