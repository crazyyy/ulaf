<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use AdminEase\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class BlockSpecificBots
 * Handles functionality for blocking specific bots from accessing the site.
 * Provides administrative settings for customization and generates
 * corresponding .htaccess rules.
 */
class BlockSpecificBots {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ], 20 );
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Modifies and returns an array of settings fields to include bot blocking configuration options.
	 *
	 * @param array $fields The current fields array to be modified with additional settings.
	 *
	 * @return array The modified fields array including new bot blocking configuration options.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'              => 'switch',
			'id'                => 'block-specific-bots-enabled',
			'name'              => 'adminease[security][block_specific_bots_enabled]',
			'value'             => $this->settings['block_specific_bots_enabled'] ?? '',
			'label_class'       => 'adminease-switch',
			'input_class'       => 'form-control toggle-field',
			'label'             => __( 'Enable bots blocking', 'adminease' ),
			'description'       => __( "Block malicious or unwanted bots from accessing your site. <strong>Protect your server resources</strong> from aggressive crawlers, prevent content scraping, reduce bandwidth waste, and <strong>improve site performance</strong> by filtering out automated traffic that doesn't serve your business goals. Control which bots can access your content with precision.", 'adminease' ),
			'field_description' => __( 'Select common bots to block from accessing your site', 'adminease' ),
			'child_fields'      => [
				[
					'type'              => 'select',
					'id'                => 'block-specific-bots',
					'name'              => 'adminease[security][block_specific_bots][]',
					'value'             => $this->settings['block_specific_bots'] ?? [],
					'options'           => Utils::get_bots_list(),
					'has_optgroups'     => true,
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control adminease-choices',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Block common bots', 'adminease' ),
					'field_description' => __( 'Select common bots to block from accessing your site', 'adminease' ),
					'attributes'        => [
						'data-parent'           => 'block-specific-bots-enabled',
						'multiple'              => 'multiple',
						'data-allow_clear'      => true,
						'data-allow_select_all' => true,
					],
				],
				[
					'type'              => 'textarea',
					'id'                => 'block-specific-bots-custom',
					'name'              => 'adminease[security][block_specific_bots_custom]',
					'value'             => str_replace( ' ', PHP_EOL, ( $this->settings['block_specific_bots_custom'] ?? '' ) ),
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Block specific bots', 'adminease' ),
					'field_description' => __( 'List of custom bot user agents to block, one per line. You can use the Network Viewer Log feature to view bot traffic.<br>Example: <br><code>BadBot</code><br><code>SpamBot3000</code><br><code>EvilScraper</code>', 'adminease' ),
					'attributes'        => [
						'data-parent' => 'block-specific-bots-enabled',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Generate .htaccess rules for blocking bots
	 *
	 * @param array $bots Array of bot user agents to block
	 *
	 * @return string
	 */
	private function generate_bot_blocking_rules( array $bots ): string {
		$code = "<IfModule mod_rewrite.c>\n";
		$code .= "\tRewriteEngine On\n\n";
		$code .= "\t# Block specific bots\n";
		
		// Create pattern for all bots to block
		$bot_pattern = implode( '|', array_map( function( $bot ) {
			return preg_quote( $bot, '/' );
		}, $bots ) );
		
		$code .= "\tRewriteCond %{HTTP_USER_AGENT} ({$bot_pattern}) [NC]\n";
		$code .= "\tRewriteRule ^(.*)$ - [F,L]\n";
		$code .= "</IfModule>\n";
		
		return $code;
	}
	
	/**
	 * Get combined list of blocked bots from common and custom selections
	 *
	 * @param array  $common_bots
	 * @param string $custom_bots
	 *
	 * @return array
	 */
	private function get_blocked_bots( array $common_bots, string $custom_bots ): array {
		$blocked_bots = [];
		
		// Add selected common bots
		if( !empty( $common_bots ) ) {
			$blocked_bots = array_merge( $blocked_bots, $common_bots );
		}
		
		// Add custom bots with validation
		if( !empty( $custom_bots ) ) {
			$custom_bot_list = preg_split( '/[\s\n\r]+/', trim( $custom_bots ) );
			
			$custom_bot_list = array_filter( array_map( function( $bot ) {
				// Sanitize and validate each bot name
				$bot = trim( $bot );
				// Only allow alphanumeric, spaces, hyphens, underscores, dots
				$bot = preg_replace( '/[^a-zA-Z0-9\s\-_.\/]/', '', $bot );
				// Limit length to prevent abuse
				if( strlen( $bot ) > 100 ) {
					$bot = substr( $bot, 0, 100 );
				}
				
				return $bot;
			}, $custom_bot_list ) );
			
			$blocked_bots = array_merge( $blocked_bots, $custom_bot_list );
		}
		
		$blocked_bots = array_unique( array_filter( $blocked_bots ) );
		
		// Limit total number of bots to prevent performance issues
		if( count( $blocked_bots ) > 200 ) {
			$blocked_bots = array_slice( $blocked_bots, 0, 200 );
		}
		
		return $blocked_bots;
	}
	
	/**
	 * Process settings after save
	 *
	 * @param array $settings
	 */
	public function adminease_settings_saved( array $settings ): void {
		if( !current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$block_specific_bots_enabled = $settings['security']['block_specific_bots_enabled'] ?? false;
		$block_specific_bots         = $settings['security']['block_specific_bots'] ?? [];
		$block_specific_bots_custom  = $settings['security']['block_specific_bots_custom'] ?? [];
		$code                        = '';
		$blocked_bots                = [];
		
		if( $block_specific_bots_enabled ) {
			$blocked_bots = $this->get_blocked_bots( $block_specific_bots, $block_specific_bots_custom );
		}
		
		if( !empty( $blocked_bots ) ) {
			$code        = "<IfModule mod_rewrite.c>\n";
			$code        .= "\tRewriteEngine On\n\n";
			$code        .= "\t# Block specific bots\n";
			$bot_pattern = implode( '|', array_map( function( $bot ) {
				return preg_quote( $bot, '/' );
			}, $blocked_bots ) );
			$code        .= "\tRewriteCond %{HTTP_USER_AGENT} ({$bot_pattern}) [NC]\n";
			$code        .= "\tRewriteRule ^(.*)$ - [F,L]\n";
			$code        .= "</IfModule>\n";
		}

		Plugin::$FileHandler->stack_htaccess_rule( 'BLOCK_SPECIFIC_BOTS', $code );
	}
}