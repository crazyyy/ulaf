<?php
namespace AdminEase;

use WP_Roles;

defined( 'ABSPATH' ) || exit;

/**
 * Utility class providing helper methods for various functionalities.
 */
class Utils {
	/**
	 * Parses a memory limit value and converts it into megabytes.
	 * @param string $value The memory limit value, potentially including a unit (e.g., 'M', 'G').
	 * @return int The memory value converted to megabytes.
	 */
	public static function parse_memory_limit( string $value ): int {
		$value  = trim( $value );
		$unit   = strtoupper( substr( $value, -1 ) );
		$number = (int) $value;
		
		switch( $unit ) {
			case 'G':
				return $number * 1024; // Convert to MB
			default:
				return $number; // Assume MB if no unit
		}
	}
	
	/**
	 * Get available user roles as options for select fields.
	 * @return array Array of user roles with role name as key and display name as value.
	 */
	public static function get_user_roles_options(): array {
		global $wp_roles;
		
		if( !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		
		$roles = [];
		
		foreach( $wp_roles->roles as $role_key => $role_data ) {
			$roles[ $role_key ] = translate_user_role( $role_data['name'] );
		}
		
		return $roles;
	}
	
	/**
	 * Retrieves an array of post types based on the specified arguments.
	 * This method allows for filtering through various categories of post types,
	 * including base types (posts, pages), WooCommerce-specific types, media attachments,
	 * and other registered post types.
	 * @param array $args Optional. An array of arguments to filter post types. The following keys are supported:
	 *                    'base'         - Includes default WordPress post types (post, page).
	 *                    'woocommerce'  - Includes WooCommerce post types (product, shop_coupon, shop_order),
	 *                                     only if WooCommerce is active.
	 *                    'media'        - Includes media types (attachment).
	 *                    'others'       - Includes other public and UI-visible post types.
	 * @return array An associative array of post type keys and their corresponding labels.
	 */
	public static function get_post_types( array $args = [] ): array {
		$return = [];
		
		if( in_array( 'base', $args ) ) {
			$return['post'] = esc_html__( 'Posts', 'adminease' );
			$return['page'] = esc_html__( 'Pages', 'adminease' );
		}
		
		if( in_array( 'product', $args ) && class_exists( 'adminease' ) ) {
			$return['product'] = esc_html__( 'Products', 'adminease' );
		}
		
		if( in_array( 'woocommerce', $args ) && class_exists( 'adminease' ) ) {
			$return['product']     = esc_html__( 'Products', 'adminease' );
			$return['shop_coupon'] = esc_html__( 'Coupons', 'adminease' );
			$return['shop_order']  = esc_html__( 'Orders', 'adminease' );
		}
		
		if( in_array( 'media', $args ) ) {
			$return['attachment'] = esc_html__( 'Media', 'adminease' );
		}
		
		if( in_array( 'others', $args ) ) {
			$base_and_others = array_filter(
				wp_list_pluck( get_post_types( [ 'public' => true, 'show_ui' => true ], 'objects' ), 'label', 'name' ),
				function( $key ) {
					// Return TRUE to keep the item (if "wp_" is NOT found)
					return strpos( $key, 'wp_' ) === false;
				},
				ARRAY_FILTER_USE_KEY
			);
			
			unset( $base_and_others['attachment'] );
			
			$return = array_merge( $return, $base_and_others );
		}
		
		return $return;
	}
	
	/**
	 * Checks if the request is coming through Cloudflare.
	 * @return bool Returns true if Cloudflare headers are present, otherwise false.
	 */
	public static function is_cloudflare_enabled(): bool {
		return !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ||
		       !empty( $_SERVER['HTTP_CF_IPCountry'] ) ||
		       !empty( $_SERVER['HTTP:CF-IPCountry'] ) ||
		       !empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ||
		       !empty( $_SERVER['HTTP_CF_RAY'] );
	}
	
	/**
	 * Determines if GeoIP functionality is enabled and available.
	 * Checks for the presence of GeoIP-related environment variables, the GeoIP PHP extension,
	 * or valid GeoIP database files in standard or user-specified locations.
	 * @return bool True if GeoIP functionality is enabled and available, false otherwise.
	 */
	public static function is_geoip_enabled(): bool {
		$geoip_vars = [
			'GEOIP_COUNTRY_CODE',
			'GEOIP_COUNTRY_NAME',
			'HTTP_CF_IPCOUNTRY', // Cloudflare sets this
		];
		
		foreach( $geoip_vars as $var ) {
			if( !empty( $_SERVER[ $var ] ) || getenv( $var ) ) {
				return true;
			}
		}
		
		// Check if GeoIP PHP extension is available (PECL geoip)
		if( function_exists( 'geoip_country_code_by_name' ) ) {
			return true;
		}
		
		// Check for GeoIP database files in allowed paths
		$possible_paths = [
			dirname( ABSPATH ) . '/GeoIP/GeoIP.dat',
			WP_CONTENT_DIR . '/uploads/GeoIP/GeoIP.dat',
			WP_CONTENT_DIR . '/GeoIP/GeoIP.dat',
		];
		
		// Add standard paths but check open_basedir first
		$standard_paths = [
			'/var/lib/GeoIP/GeoIP.dat',
			'/usr/share/GeoIP/GeoIP.dat',
			'/usr/local/share/GeoIP/GeoIP.dat',
		];
		
		$open_basedir = ini_get( 'open_basedir' );
		if( empty( $open_basedir ) ) {
			// No restriction, add standard paths
			$possible_paths = array_merge( $possible_paths, $standard_paths );
		}
		else {
			// Check which standard paths are allowed
			$allowed_dirs = explode( PATH_SEPARATOR, $open_basedir );
			foreach( $standard_paths as $std_path ) {
				foreach( $allowed_dirs as $allowed_dir ) {
					if( strpos( $std_path, rtrim( $allowed_dir, '/' ) ) === 0 ) {
						$possible_paths[] = $std_path;
						break;
					}
				}
			}
		}
		
		// Check if any GeoIP database exists
		foreach( $possible_paths as $path ) {
			if( @file_exists( $path ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Retrieve the client's IP address, considering various configurations such as Cloudflare and GeoIP.
	 * This method checks if Cloudflare or GeoIP features are enabled and retrieves the IP address accordingly.
	 * Fallbacks to the remote address provided by the server if no specific configurations are detected.
	 * @return string The IP address of the client.
	 */
	public static function get_client_ip(): string {
		$ip = '';
		
		if( self::is_cloudflare_enabled() ) {
			// Only use CF_CONNECTING_IP as it's the only one that contains an actual IP
			if( !empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			}
		}
		
		// Check standard proxy headers as fallback
		if( empty( $ip ) && !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ip_list   = explode( ',', $forwarded );
			$ip        = trim( $ip_list[0] );
		}
		
		if( empty( $ip ) && !empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		}
		
		// Final fallback to REMOTE_ADDR
		if( empty( $ip ) && !empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		
		// Validate IP address
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}
	
	/**
	 * Retrieves the client's country code based on available server variables and services.
	 * The method attempts to determine the country of the client by checking Cloudflare headers,
	 * GeoIP server variables, and performing a GeoIP lookup if necessary. It returns a validated
	 * two-letter ISO country code or an empty string if the country cannot be determined.
	 * @return string Returns a two-letter ISO country code (uppercase) if successfully determined,
	 *                or an empty string if the country could not be identified.
	 */
	public static function get_client_country(): string {
		$country_code = '';
		
		// Check Cloudflare headers first
		if( self::is_cloudflare_enabled() ) {
			if( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
				$country_code = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) );
			}
			else if( !empty( $_SERVER['HTTP_CF_IPCountry'] ) ) {
				$country_code = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCountry'] ) );
			}
		}
		
		// Check GeoIP server variables
		if( empty( $country_code ) && self::is_geoip_enabled() ) {
			if( !empty( $_SERVER['GEOIP_COUNTRY_CODE'] ) ) {
				$country_code = sanitize_text_field( wp_unslash( $_SERVER['GEOIP_COUNTRY_CODE'] ) );
			}
			else if( !empty( $_SERVER['HTTP_X_FORWARDED_COUNTRY'] ) ) {
				$country_code = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_COUNTRY'] ) );
			}
		}
		
		// Fallback: Try GeoIP lookup by IP address
		if( empty( $country_code ) && function_exists( 'geoip_country_code_by_name' ) ) {
			$ip = self::get_client_ip();
			
			if( !empty( $ip ) ) {
				$country_code = geoip_country_code_by_name( $ip );
			}
		}
		
		// Validate country code format (2 uppercase letters)
		if( !empty( $country_code ) ) {
			// Validate it's a 2-letter code and exists in our country list
			if( strlen( $country_code ) === 2 && preg_match( '/^[A-Z]{2}$/', $country_code ) ) {
				$countries = self::get_countries_iso();
				
				if( isset( $countries[ $country_code ] ) ) {
					return $country_code;
				}
			}
			else if( strlen( $country_code ) > 2 ) {
				// Possibly a full country name, try to map it to ISO2 code
				$countries = array_values( self::get_countries_iso() );
				
				$index = array_search( $country_code, $countries, true );
				
				if( false !== $index ) {
					$iso_codes = array_keys( self::get_countries_iso() );
					
					return $iso_codes[ $index ];
				}
			}
		}
		
		return '';
	}
	
	/**
	 * Returns a list of countries with their ISO 3166-1 alpha-2 codes as keys and their corresponding country names as values.
	 * @return array An associative array where the keys are ISO 3166-1 alpha-2 country codes and the values are the respective country names.
	 */
	public static function get_countries_iso(): array {
		return [
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Sint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, Democratic Republic of the',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Côte d\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curaçao',
			'CY' => 'Cyprus',
			'CZ' => 'Czechia',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'VA' => 'Holy See',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea (Democratic People\'s Republic of)',
			'KR' => 'Korea (Republic of)',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia (Federated States of)',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MK' => 'North Macedonia',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestine, State of',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Réunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthélemy',
			'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French part)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten (Dutch part)',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania, United Republic of',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (U.S.)',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		];
	}
	
	/**
	 * Retrieves the ISO code of a country based on its name.
	 * @param string $country_name The name of the country to retrieve the ISO code for.
	 *                             The input is case-insensitive and will be trimmed and converted to lowercase.
	 * @return string Returns the ISO code of the country if found.
	 *                Returns an empty string if the country name does not match any in the list.
	 */
	public static function get_country_code_by_name( string $country_name ): string {
		$countries    = self::get_countries_iso();
		$country_name = trim( strtolower( $country_name ) );
		
		foreach( $countries as $iso => $name ) {
			if( strtolower( $name ) === $country_name ) {
				return $iso;
			}
		}
		
		return '';
	}
	
	/**
	 * Get list of common bots
	 * @return array
	 */
	public static function get_bots_list(): array {
		$categorized = self::get_categorized_bots();
		$optgroups   = [];
		
		foreach( $categorized as $category => $data ) {
			$optgroups[ $data['label'] ] = $data['bots'];
		}
		
		return $optgroups;
	}
	
	/**
	 * Get categorized bot lists
	 * @return array
	 */
	public static function get_categorized_bots(): array {
		return [
			'search_engines' => [
				'label' => 'Search Engines',
				'bots'  => [
					'Googlebot'   => 'Googlebot',
					'Bingbot'     => 'Bingbot',
					'YandexBot'   => 'YandexBot',
					'Baiduspider' => 'Baiduspider',
					'DuckDuckBot' => 'DuckDuckBot',
					'Slurp'       => 'Yahoo! Slurp',
					'Sogou'       => 'Sogou Spider',
					'Yeti'        => 'Naver Yeti',
					'Applebot'    => 'Applebot',
					'ia_archiver' => 'Alexa Crawler',
				],
			],
			'social_media'   => [
				'label' => 'Social Media',
				'bots'  => [
					'facebookexternalhit' => 'Facebook Bot',
					'Facebot'             => 'Facebook Facebot',
					'Twitterbot'          => 'Twitterbot',
					'LinkedInBot'         => 'LinkedIn Bot',
					'Pinterestbot'        => 'Pinterestbot',
					'WhatsApp'            => 'WhatsApp Bot',
					'TelegramBot'         => 'Telegram Bot',
					'SkypeUriPreview'     => 'Skype URI Preview',
					'Discordbot'          => 'Discord Bot',
					'redditbot'           => 'Reddit Bot',
					'Slackbot'            => 'Slack Bot',
					'InstagramBot'        => 'Instagram Bot',
					'snapchat-proxy'      => 'Snapchat Bot',
					'TikTokBot'           => 'TikTok Bot',
					'LinkedInFeedBot'     => 'LinkedIn Feed Bot',
					'TikTok'              => 'TikTok Crawler',
					'YelpBot'             => 'Yelp Bot',
					'MastodonBot'         => 'Mastodon Bot',
					'BlueSkyBot'          => 'BlueSky Bot',
					'ThreadsBot'          => 'Meta Threads Bot',
				],
			],
			'ai_research'    => [
				'label' => 'AI & Research',
				'bots'  => [
					'GPTBot'          => 'OpenAI GPTBot',
					'ChatGPT-User'    => 'ChatGPT User Agent',
					'CCBot'           => 'Common Crawl Bot',
					'anthropic-ai'    => 'Anthropic AI Bot',
					'ClaudeBot'       => 'Claude AI Bot',
					'Google-Extended' => 'Google Bard/Gemini Bot',
					'PerplexityBot'   => 'Perplexity AI Bot',
					'YouBot'          => 'You.com AI Bot',
					'AI2Bot'          => 'Allen Institute AI Bot',
					'FacebookBot'     => 'Meta AI Bot',
					'Bytespider'      => 'ByteDance AI Bot',
					'ImagesiftBot'    => 'ImageSift AI Bot',
					'Omgilibot'       => 'Omgili AI Bot',
					'Diffbot'         => 'Diffbot AI Crawler',
					'DataMinr'        => 'DataMinr AI Bot',
					'WebzBot'         => 'Webz.io AI Bot',
					'ChatGPT'         => 'ChatGPT Bot',
					'BingAI'          => 'Bing AI Chat Bot',
					'PaLM'            => 'Google PaLM Bot',
					'LaMDA'           => 'Google LaMDA Bot',
					'Cohere'          => 'Cohere AI Bot',
					'HuggingFace'     => 'Hugging Face Bot',
					'Anthropic'       => 'Anthropic Bot',
					'OpenAI'          => 'OpenAI Bot',
				],
			],
			'seo_tools'      => [
				'label' => 'SEO Tools',
				'bots'  => [
					'AhrefsBot'                 => 'AhrefsBot',
					'SemrushBot'                => 'SEMrushBot',
					'MJ12bot'                   => 'Majestic MJ12bot',
					'DotBot'                    => 'DotBot',
					'Rogerbot'                  => 'Moz Rogerbot (Deprecated)',
					'SerpstatBot'               => 'Serpstat Bot',
					'DataForSeoBot'             => 'DataForSEO Bot',
					'Screaming Frog SEO Spider' => 'Screaming Frog',
					'Sitebulb'                  => 'Sitebulb Crawler',
					'Lumar'                     => 'Lumar (DeepCrawl)',
					'DeepCrawl'                 => 'DeepCrawl',
					'OnCrawl'                   => 'OnCrawl',
					'Botify'                    => 'Botify',
					'JetOctopus'                => 'JetOctopus',
					'NetpeakSpider'             => 'Netpeak Spider',
					'ContentKing'               => 'ContentKing',
					'BLEXBot'                   => 'BLEXBot',
					'MegaIndex'                 => 'MegaIndex Bot',
					'CognitiveSEO'              => 'CognitiveSEO Bot',
					'BrandVerity'               => 'BrandVerity Bot',
					'LinkpadBot'                => 'Linkpad Bot',
					'PageFreezer'               => 'PageFreezer Bot',
				],
			],
			'monitoring'     => [
				'label' => 'Monitoring & Uptime',
				'bots'  => [
					'UptimeRobot'       => 'UptimeRobot',
					'StatusCake'        => 'StatusCake Bot',
					'Pingdom'           => 'Pingdom Bot',
					'Site24x7'          => 'Site24x7 Bot',
					'GTmetrix'          => 'GTmetrix Bot',
					'NodePing'          => 'NodePing Bot',
					'Monitis'           => 'Monitis Bot',
					'WebGazer'          => 'WebGazer Bot',
					'AlertSite'         => 'AlertSite Bot',
					'Keynote'           => 'Keynote Bot',
					'ThousandEyes'      => 'ThousandEyes Bot',
					'NewRelic'          => 'New Relic Bot',
					'DatadogSynthetics' => 'Datadog Synthetics',
					'AppDynamics'       => 'AppDynamics Bot',
					'Dynatrace'         => 'Dynatrace Bot',
					'SolarWinds'        => 'SolarWinds Bot',
					'LogicMonitor'      => 'LogicMonitor Bot',
					'Catchpoint'        => 'Catchpoint Bot',
				],
			],
			'ecommerce'      => [
				'label' => 'E-commerce',
				'bots'  => [
					'Shopify-Partner' => 'Shopify Partner Bot',
					'WooRank'         => 'WooRank Bot',
					'PriceGrabber'    => 'PriceGrabber Bot',
					'Shopping.com'    => 'Shopping.com Bot',
					'Nextag'          => 'Nextag Bot',
					'Bizrate'         => 'Bizrate Bot',
					'ShopWiki'        => 'ShopWiki Bot',
					'TheFind'         => 'TheFind Bot',
					'Amazon'          => 'Amazon Bot',
					'eBayBot'         => 'eBay Bot',
					'EtsyBot'         => 'Etsy Bot',
					'WalmartBot'      => 'Walmart Bot',
					'TargetBot'       => 'Target Bot',
					'BestBuyBot'      => 'Best Buy Bot',
					'HomeDepotBot'    => 'Home Depot Bot',
					'WayfairBot'      => 'Wayfair Bot',
					'AliExpressBot'   => 'AliExpress Bot',
					'WishBot'         => 'Wish Bot',
					'OverstockBot'    => 'Overstock Bot',
					'NeweggBot'       => 'Newegg Bot',
					'ShopifyBot'      => 'Shopify Bot',
					'BigCommerceBot'  => 'BigCommerce Bot',
					'WooCommerceBot'  => 'WooCommerce Bot',
					'MagentoBot'      => 'Magento Bot',
					'PrestaShopBot'   => 'PrestaShop Bot',
					'OpenCartBot'     => 'OpenCart Bot',
					'SquareBot'       => 'Square Bot',
					'StripeBot'       => 'Stripe Bot',
					'PayPalBot'       => 'PayPal Bot',
					'KlarnaBot'       => 'Klarna Bot',
					'AfterPayBot'     => 'AfterPay Bot',
					'ShipStationBot'  => 'ShipStation Bot',
				],
			],
			'news_media'     => [
				'label' => 'News & Media',
				'bots'  => [
					'Flipboard'        => 'Flipboard Bot',
					'Pocket'           => 'Pocket Bot',
					'NewsBlur'         => 'NewsBlur Bot',
					'Apple News'       => 'Apple News Bot',
					'Google News'      => 'Google News Bot',
					'Yahoo News'       => 'Yahoo News Bot',
					'MSN Bot'          => 'MSN News Bot',
					'Bing News'        => 'Bing News Bot',
					'Reuters'          => 'Reuters Bot',
					'Associated Press' => 'AP News Bot',
					'CNN'              => 'CNN Bot',
					'BBC'              => 'BBC Bot',
					'NPR'              => 'NPR Bot',
					'Medium'           => 'Medium Bot',
					'Substack'         => 'Substack Bot',
					'NewsAPI'          => 'News API Bot',
					'AllSides'         => 'AllSides Bot',
					'Ground News'      => 'Ground News Bot',
					'SmartNews'        => 'SmartNews Bot',
					'Inoreader'        => 'Inoreader Bot',
					'Feedly'           => 'Feedly Bot',
					'The Old Reader'   => 'The Old Reader Bot',
				],
			],
			'archive'        => [
				'label' => 'Archive & Backup',
				'bots'  => [
					'archive.org_bot' => 'Internet Archive Bot',
					'Wayback'         => 'Wayback Machine',
					'BacklinkCrawler' => 'Backlink Crawler',
				],
			],
		];
	}
	
	/**
	 * Checks if the Pro version of the plugin is active.
	 * @return bool Returns true if the Pro version of the plugin is active, otherwise false.
	 */
	public static function is_pro_plugin_active(): bool {
		return defined( 'ADMINEASE_PRO_VERSION' );
	}
}