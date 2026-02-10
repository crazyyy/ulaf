<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Ads_Robots_Txt Settings class
 */
class Ads_Robots_Txt_Settings extends WP_Settings_API_Helper {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param array $options User options.
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
				'option_group' => Ads_Robots_Txt::OPTION_NAME,
				'option_name'  => Ads_Robots_Txt::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-robotstxt-section',
				'title'       => __( 'Robots.txt', 'admin-optimizer' ),
				// translators: %1$s is the anchor tag to the robots.txt resource. %2$s is the anchor closure tag.
				'description' => sprintf( __( 'A robots.txt file lists a website\'s preferences for bot behavior. It tells bots which webpages they should and should not access. Robots.txt files are most relevant for web crawlers. %1$sLearn more%2$s', 'admin-optimizer' ), '<a href="' . esc_url( 'https://developers.google.com/search/docs/crawling-indexing/robots/intro' ) . '" target="_blank">', '</a>' ),
				'menu_slug'   => Ads_Robots_Txt::OPTION_NAME,
				'option_name' => Ads_Robots_Txt::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'code-editor',
						'title' => __( 'Robots.txt Content', 'admin-optimizer' ),
						'id'    => 'robotstxt-content',
						'name'  => 'robotstxt_content',
						// translators: %1$s is the anchor tag to the robots.txt. %2$s is the anchor closure tag.
						'desc'  => sprintf( __( 'Leave it blank to disable it. %1$sView the robots.txt here%2$s.', 'admin-optimizer' ), '<a href="' . esc_url( home_url( 'robots.txt' ) ) . '" target="_blank">', '</a>' ),
					],
				],
			],
			[
				'id'          => 'adminoptimizer-adstxt-section',
				'title'       => __( 'Ads.txt', 'admin-optimizer' ),
				'description' => __( 'ads.txt is an IAB Tech Lab initiative that helps ensure that your digital ad inventory is only sold through sellers who you\'ve identified as authorized. Creating your own ads.txt file gives you more control over who\'s allowed to sell ads on your site and helps prevent counterfeit inventory from being presented to advertisers.', 'admin-optimizer' ),
				'menu_slug'   => Ads_Robots_Txt::OPTION_NAME,
				'option_name' => Ads_Robots_Txt::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'code-editor',
						'title' => __( 'Ads.txt Content', 'admin-optimizer' ),
						'id'    => 'adstxt-content',
						'name'  => 'adstxt_content',
						// translators: %1$s is the anchor tag to the app-ads.txt. %2$s is the anchor closure tag. %3$s is the anchor tag to the validator. %4$s is the anchor closure tag.
						'desc'  => sprintf( __( 'Leave it blank to disable it. %1$sView the ads.txt here%2$s. Validate with %3$sadstxt.guru%4$s', 'admin-optimizer' ), '<a href="' . esc_url( home_url( 'ads.txt' ) ) . '" target="_blank">', '</a>', '<a href="' . esc_url( 'https://adstxt.guru/validator/url/?url=' . rawurlencode( home_url( 'ads.txt' ) ) ) . '" target="_blank">', '</a>' ),
					],
				],
			],
			[
				'id'          => 'adminoptimizer-appadstxt-section',
				'title'       => __( 'App-ads.txt', 'admin-optimizer' ),
				'description' => __( 'The app-ads.txt file is a publicly accessible text file that app developers place on their official website to declare which ad networks and platforms are authorized to sell their app\'s ad inventory. This helps prevent ad fraud, ensuring that only legitimate sellers can monetize the app\'s ad space.', 'admin-optimizer' ),
				'menu_slug'   => Ads_Robots_Txt::OPTION_NAME,
				'option_name' => Ads_Robots_Txt::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'code-editor',
						'title' => __( 'App-ads.txt Content', 'admin-optimizer' ),
						'id'    => 'app-adstxt-content',
						'name'  => 'app_adstxt_content',
						// translators: %1$s is the anchor tag to the app-ads.txt. %2$s is the anchor closure tag. %3$s is the anchor tag to the validator. %4$s is the anchor closure tag.
						'desc'  => sprintf( __( 'Leave it blank to disable it. %1$sView the app-ads.txt here%2$s. Validate with %3$sadstxt.guru%4$s', 'admin-optimizer' ), '<a href="' . esc_url( home_url( 'app-ads.txt' ) ) . '" target="_blank">', '</a>', '<a href="' . esc_url( 'https://adstxt.guru/validator/url/?url=' . rawurlencode( home_url( 'app-ads.txt' ) ) ) . '" target="_blank">', '</a>' ),
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
			<h1><?php esc_html_e( 'Admin Optimizer Pro - Manage Robots.txt, Ads.txt, and App-ads.txt', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Ads_Robots_Txt::OPTION_NAME ); ?>
		</div>
		<?php
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
		if ( is_array( $options ) ) {
			if ( isset( $options['robotstxt_content'] ) ) {
				$sanitized_options['robotstxt_content'] = esc_textarea( $options['robotstxt_content'] );
			}
			if ( isset( $options['adstxt_content'] ) ) {
				$sanitized_options['adstxt_content'] = esc_textarea( $options['adstxt_content'] );
			}
			if ( isset( $options['app_adstxt_content'] ) ) {
				$sanitized_options['app_adstxt_content'] = esc_textarea( $options['app_adstxt_content'] );
			}
		}

		return $sanitized_options;
	}
}
