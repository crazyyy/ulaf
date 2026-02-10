<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use AdminEase\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Manages the functionality of blocking visitors from specific countries based on geolocation mechanisms.
 * The class supports multiple geolocation services such as Cloudflare, MaxMindDB, and GeoIP.
 */
class BlockSpecificCountries {
	private array $settings;
	public static bool $is_cloudflare_enabled;
	public static bool $is_geoip_enabled;
	public static bool $is_blocking_countries_enabled;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		self::$is_cloudflare_enabled         = Utils::is_cloudflare_enabled();
		self::$is_geoip_enabled              = Utils::is_geoip_enabled();
		self::$is_blocking_countries_enabled = ( self::$is_cloudflare_enabled || self::$is_geoip_enabled );
		
		if( !self::$is_blocking_countries_enabled ) {
			return;
		}
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Generates and updates the settings fields for configuring security options.
	 * Specifically includes fields for blocking visitors from specific countries.
	 *
	 * @param array $fields An associative array of existing settings fields with their configurations.
	 *
	 * @return array The modified array of settings fields with the added configuration for blocking specific countries.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$countries            = Utils::get_countries_iso();
		$visitor_country_code = Utils::get_client_country();
		
		if( isset( $countries[ $visitor_country_code ] ) ) {
			unset( $countries[ $visitor_country_code ] );
		}
		
		$fields['security']['fields'][] = [
			'type'              => 'switch',
			'id'                => 'block-specific-countries-enabled',
			'name'              => 'adminease[security][block_specific_countries_enabled]',
			'value'             => $this->settings['block_specific_countries_enabled'] ?? false,
			'label_class'       => 'adminease-switch',
			'input_class'       => 'form-control toggle-field',
			'label'             => __( 'Enable country blocking', 'adminease' ),
			'field_description' => self::$is_blocking_countries_enabled ? __( 'Block visitors from specific countries based on geolocation.', 'adminease' ) : __( 'Unfortunately your server does not support this feature.', 'adminease' ),
			'attributes'        => [
				'disabled' => !self::$is_blocking_countries_enabled,
			],
			'child_fields'      => [
				[
					'type'          => 'select',
					'id'            => 'block-specific-countries',
					'name'          => 'adminease[security][block_specific_countries][]',
					'value'         => $this->settings['block_specific_countries'] ?? '',
					'options'       => $countries,
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control adminease-choices',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Block specific countries', 'adminease' ),
					'attributes'    => [
						'data-parent'           => 'block-specific-countries-enabled',
						'multiple'              => 'multiple',
						'data-allow_clear'      => true,
						'data-allow_select_all' => true,
						'disabled'              => !self::$is_blocking_countries_enabled,
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Processes and saves settings specific to blocking visitors from certain countries
	 * based on different compatibility modules (Cloudflare, MaxMind, GeoIP).
	 *
	 * @param array $sanitized_settings The array of settings, including the 'security' section which defines the countries to block.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$block_specific_bots_enabled = $sanitized_settings['security']['block_specific_countries_enabled'] ?? false;
		$block_specific_countries    = $sanitized_settings['security']['block_specific_countries'] ?? [];
		$code                        = '';
		
		if( $block_specific_bots_enabled ) {
			if( !empty( $block_specific_countries ) && self::$is_cloudflare_enabled ) {
				$code = "<IfModule mod_rewrite.c>\n";
				$code .= "\tRewriteEngine On\n\n";
				$code .= "\t# Cloudflare compatibility\n";
				
				// Use multiple RewriteCond lines for better reliability
				foreach( $block_specific_countries as $index => $country ) {
					$or_flag = ( $index < count( $block_specific_countries ) - 1 ) ? ' [OR]' : '';
					$code    .= "\tRewriteCond %{HTTP:CF-IPCountry} ^{$country}$" . $or_flag . "\n";
				}
				
				$code .= "\tRewriteRule ^(.*)$ - [F,L]\n";
				$code .= "</IfModule>\n";
			}
			
			if( !empty( $block_specific_countries ) && self::$is_geoip_enabled ) {
				$block_specific_countries = implode( '|', $block_specific_countries );
				
				$code .= "\n<IfModule mod_geoip.c>\n";
				$code .= "\tGeoIPEnable On\n";
				$code .= "\tSetEnvIf GEOIP_COUNTRY_CODE ^($block_specific_countries)$ BlockCountry\n";
				$code .= "\tOrder Allow,Deny\n";
				$code .= "\tAllow from all\n";
				$code .= "\tDeny from env=BlockCountry\n";
				$code .= "</IfModule>\n";
			}
		}
		
		$code = apply_filters( 'adminease_block_specific_countries_code', $code, $sanitized_settings );
		
		Plugin::$FileHandler->stack_htaccess_rule( 'BLOCK_SPECIFIC_COUNTRIES', $code );
	}
}