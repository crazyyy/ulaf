<?php declare(strict_types=1);
/**
 * Language and locale collector.
 *
 * @package query-monitor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @extends QM_DataCollector<QM_Data_Languages>
 */
class QM_Collector_Languages extends QM_DataCollector {

	public $id = 'languages';

	public function get_storage(): QM_Data {
		return new QM_Data_Languages();
	}

	public function set_up(): void {

		parent::set_up();

		add_filter( 'load_textdomain_mofile', [ $this, 'log_mo_file_load' ], 9999, 2 );
		add_filter( 'load_translation_file', [ $this, 'log_translation_file_load' ], 9999, 3 );
		add_filter( 'load_script_translation_file', [ $this, 'log_script_file_load' ], 9999, 3 );

		// Locale collection must never run before init
		add_action( 'init', [ $this, 'collect_locale_data' ], 9999 );
	}

	public function tear_down(): void {

		remove_filter( 'load_textdomain_mofile', [ $this, 'log_mo_file_load' ], 9999 );
		remove_filter( 'load_translation_file', [ $this, 'log_translation_file_load' ], 9999 );
		remove_filter( 'load_script_translation_file', [ $this, 'log_script_file_load' ], 9999 );
		remove_action( 'init', [ $this, 'collect_locale_data' ], 9999 );

		parent::tear_down();
	}

	public function collect_locale_data(): void {

		if ( ! did_action( 'init' ) ) {
			return;
		}

		$this->data->locale              = get_locale();
		$this->data->user_locale         = get_user_locale();
		$this->data->determined_locale   = determine_locale();
		$this->data->language_attributes = get_language_attributes();

		if ( function_exists( '\Inpsyde\MultilingualPress\siteLanguageTag' ) ) {
			$this->data->mlp_language = \Inpsyde\MultilingualPress\siteLanguageTag();
		}

		if ( function_exists( 'pll_current_language' ) ) {
			$this->data->pll_language = pll_current_language();
		}
	}

	public function get_concerned_actions(): array {
		return [
			'load_textdomain',
			'unload_textdomain',
		];
	}

	public function get_concerned_filters(): array {
		return [
			'determine_locale',
			'gettext',
			'gettext_with_context',
			'lang_dir_for_domain',
			'language_attributes',
			'load_script_textdomain_relative_path',
			'load_script_translation_file',
			'load_script_translations',
			'load_textdomain_mofile',
			'load_translation_file',
			'locale',
			'ngettext',
			'ngettext_with_context',
			'override_load_textdomain',
			'override_unload_textdomain',
			'plugin_locale',
			'pre_determine_locale',
			'pre_get_language_files_from_path',
			'pre_load_script_translations',
			'pre_load_textdomain',
			'theme_locale',
			'translation_file_format',
		];
	}

	public function get_concerned_options(): array {
		return [ 'WPLANG' ];
	}

	public function get_concerned_constants(): array {
		return [ 'WPLANG' ];
	}

	public function process(): void {

		if ( empty( $this->data->languages ) ) {
			return;
		}

		$this->data->total_size = 0;

		ksort( $this->data->languages );

		foreach ( $this->data->languages as $mofiles ) {
			foreach ( $mofiles as $mofile ) {
				if ( ! empty( $mofile['found'] ) ) {
					$this->data->total_size += (int) $mofile['found'];
				}
			}
		}
	}

	public function log_mo_file_load( $file, string $domain ) {

		if ( class_exists( 'WP_Translation_Controller', false ) ) {
			return $file;
		}

		$found = is_string( $file ) && file_exists( $file );

		return $this->log_file_load( $file, $domain, $found );
	}

	public function log_translation_file_load( $file, string $domain, ?string $locale = null ) {

		if ( ! is_string( $file ) || ! class_exists( 'WP_Translation_Controller', false ) ) {
			return $file;
		}

		$controller = WP_Translation_Controller::get_instance();
		$loaded     = $controller->load_file( $file, $domain, $locale ?? determine_locale() );

		return $this->log_file_load( $file, $domain, $loaded );
	}

	public function log_file_load( $mofile, string $domain, $loaded ) {

		if ( 'query-monitor' === $domain && self::hide_qm() ) {
			return $mofile;
		}

		if ( is_string( $mofile ) && isset( $this->data->languages[ $domain ][ $mofile ] ) ) {
			return $mofile;
		}

		$trace = new QM_Backtrace( [
			'ignore_hook' => [ current_filter() => true ],
			'ignore_func' => [
				'_load_textdomain_just_in_time' => true,
				'get_translations_for_domain'   => true,
				'translate_with_gettext_context' => true,
			],
		] );

		$found = ( $loaded && is_string( $mofile ) && file_exists( $mofile ) )
			? filesize( $mofile )
			: false;

		$type = is_string( $mofile )
			? pathinfo( $mofile, PATHINFO_EXTENSION )
			: 'unknown';

		$this->data->languages[ $domain ][ $mofile ] = [
			'caller' => $trace->get_caller(),
			'domain' => $domain,
			'file'   => $mofile,
			'found'  => $found,
			'handle' => null,
			'type'   => $type,
		];

		return $mofile;
	}

	public function log_script_file_load( $file, string $handle, string $domain ) {

		$trace = new QM_Backtrace( [
			'ignore_hook' => [ current_filter() => true ],
		] );

		$found = ( is_string( $file ) && file_exists( $file ) )
			? filesize( $file )
			: false;

		$key = $file ?: uniqid( 'qm-lang-', true );

		$this->data->languages[ $domain ][ $key ] = [
			'caller' => $trace->get_caller(),
			'domain' => $domain,
			'file'   => $file,
			'found'  => $found,
			'handle' => $handle,
			'type'   => 'jed',
		];

		return $file;
	}
}

/**
 * IMPORTANT:
 * Register collector only after init to avoid WP 6.7+ notices
 */
add_action( 'init', static function () {
	QM_Collectors::add( new QM_Collector_Languages() );
}, 0 );
