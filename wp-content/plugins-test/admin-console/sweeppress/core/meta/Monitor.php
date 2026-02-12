<?php

namespace Dev4Press\Plugin\SweepPress\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Monitor {
	private bool $active;
	private array $meta_types = array(
		'post',
		'term',
		'user',
		'comment',
		'blog',
	);
	private array $temp_store = array();
	private array $file_sources = array();
	private array $plugin_sources = array();
	private array $skip_sources = array(
		'wp-includes/class-wp-hook.php',
		'wp-includes/class-wp-widget.php',
		'wp-includes/option.php',
	);

	public function __construct() {
		$_global  = defined( 'SWEEPPRESS_METADATA_MONITOR' ) && SWEEPPRESS_METADATA_MONITOR !== 'off';
		$_reading = false;
		$_update  = false;

		if ( $_global ) {
			$_reading = SWEEPPRESS_METADATA_MONITOR === 'full';
			$_update  = true;

			add_action( 'sweeppress_plugin_init', array( $this, 'store_from_temp' ) );
		} else {
			if ( sweeppress_settings()->get( 'meta_tracker_monitor' ) ) {
				$_reading = sweeppress_settings()->get( 'meta_tracker_monitor_get' );
				$_update  = sweeppress_settings()->get( 'meta_tracker_monitor_update' );
			}
		}

		$this->active = $_reading || $_update;

		foreach ( $this->meta_types as $meta_type ) {
			if ( $_reading ) {
				add_filter( "get_{$meta_type}_metadata", array( $this, 'get_metadata' ), 10, 5 );
			}

			if ( $_update ) {
				add_action( "update_{$meta_type}_metadata", array( $this, "updated_metadata_{$meta_type}" ), 10, 3 );
				add_action( "add_{$meta_type}_metadata", array( $this, "updated_metadata_{$meta_type}" ), 10, 3 );
			}
		}

		if ( $_reading ) {
			add_filter( 'pre_option', array( $this, 'pre_option' ), 10, 2 );
		}

		if ( $_update ) {
			if ( is_multisite() ) {
				add_action( 'update_site_option', array( $this, 'update_site_option' ) );
			}

			add_filter( 'pre_update_option', array( $this, 'pre_update_option' ), 10, 2 );
		}
	}

	public static function instance() : Monitor {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Monitor();
		}

		return $instance;
	}

	public function is_active() : bool {
		return $this->active;
	}

	public function get_metadata( $result, $object_id, $meta_key, $single, $meta_type ) {
		$hooks = array(
			"get_user_option"       => 0,
			"get_metadata"          => 2,
			"get_{$meta_type}_meta" => 1,
		);

		if ( $meta_type !== 'user' ) {
			unset( $hooks['get_user_option'] );
		}

		$this->process_backtrace( 'meta', $meta_type, $meta_key, 'get', $hooks );

		return $result;
	}

	public function updated_metadata( $meta_type, $meta_key ) {
		$hooks = array(
			"add_metadata"             => 2,
			"add_{$meta_type}_meta"    => 1,
			"update_metadata"          => 2,
			"update_{$meta_type}_meta" => 1,
		);

		$this->process_backtrace( 'meta', $meta_type, $meta_key, 'update', $hooks );
	}

	public function updated_metadata_post( $meta_id, $object_id, $meta_key ) {
		$this->updated_metadata( 'post', $meta_key );
	}

	public function updated_metadata_term( $meta_id, $object_id, $meta_key ) {
		$this->updated_metadata( 'term', $meta_key );
	}

	public function updated_metadata_user( $meta_id, $object_id, $meta_key ) {
		$this->updated_metadata( 'user', $meta_key );
	}

	public function updated_metadata_comment( $meta_id, $object_id, $meta_key ) {
		$this->updated_metadata( 'comment', $meta_key );
	}

	public function updated_metadata_blog( $meta_id, $object_id, $meta_key ) {
		$this->updated_metadata( 'blog', $meta_key );
	}

	public function update_site_option( $option ) {
		$hooks = array(
			"update_site_option" => 0,
		);

		$this->process_backtrace( 'option', 'site_option', $option, 'update', $hooks );
	}

	public function pre_update_option( $value, $option ) {
		$hooks = array(
			"update_site_option" => 0,
			"update_option"      => 0,
		);

		$this->process_backtrace( 'option', 'option', $option, 'update', $hooks );

		return $value;
	}

	public function pre_option( $pre, $option ) {
		$hooks = array(
			"get_site_option" => 0,
			"get_option"      => 0,
		);

		$this->process_backtrace( 'option', 'option', $option, 'get', $hooks );

		return $pre;
	}

	public function store_from_temp() {
		if ( ! empty( $this->temp_store ) ) {
			foreach ( $this->temp_store as $item ) {
				if ( Storage::instance()->is_wordpress_core( $item[2], $item[3] ) ) {
					continue;
				}

				sweeppress_settings()->add_monitor_value( $item[0], $item[1], $item[2], $item[3], $item[4], false );
			}

			sweeppress_settings()->save( 'storage' );
		}
	}

	private function process_backtrace( $scope, $meta_type, $meta_key, $method, $hooks ) {
		if ( Storage::instance()->is_wordpress_core( $meta_type, $meta_key ) ) {
			return;
		}

		if ( empty( $meta_key ) || empty( $meta_type ) ) {
			return;
		}

		$back_trace = debug_backtrace();
		$back_trace = array_reverse( $back_trace );

		$last_file = '';
		foreach ( $back_trace as $trace ) {
			$curr_file = wp_normalize_path( $trace['file'] ?? '' );

			if ( ! str_contains( $curr_file, '/d4plib/dev4press/' ) ) {
				$last_file = $curr_file;
			}

			if ( isset( $trace['function'] ) && is_string( $trace['function'] ) && isset( $hooks[ $trace['function'] ] ) ) {
				$key = $hooks[ $trace['function'] ];

				if ( isset( $trace['args'][ $key ] ) && $trace['args'][ $key ] === $meta_key ) {
					$source = $this->find_source( $curr_file, $last_file );

					if ( ! empty( $source ) ) {
						$this->add( $scope, $method, $meta_type, $meta_key, $source );
						break;
					}
				}
			}
		}
	}

	private function add( $scope, $method, $type, $key, $source ) {
		if ( function_exists( 'sweeppress' ) && sweeppress()->active ) {
			sweeppress_settings()->add_monitor_value( $scope, $method, $type, $key, $source );
		} else {
			$this->temp_store[] = array( $scope, $method, $type, $key, $source );
		}
	}

	private function find_source( $file, $prev_file ) : string {
		$this->init_sources();

		$found = '';
		$type  = '';

		if ( str_contains( $file, '/d4plib/dev4press/' ) ) {
			$file = $prev_file;
		}

		foreach ( $this->file_sources as $type => $path ) {
			if ( str_starts_with( $file, $path ) ) {
				$found = substr( $file, strlen( $path ) );
				break;
			}
		}

		if ( empty( $found ) ) {
			$type = 'unknown';
		}

		switch ( $type ) {
			case 'plugin':
				if ( str_ends_with( $file, 'coreactivity/core/log/Metas.php' ) ) {
					$name = '';
				} else {
					$parts = explode( '/', $found );
					$name  = $parts[0];

					if ( isset( $this->plugin_sources[ $name ] ) ) {
						$name = $this->plugin_sources[ $name ];
					}
				}
				break;
			case 'theme':
				$parts = explode( '/', $found );
				$name  = $parts[0];
				break;
			case 'mu_plugin':
			case 'content':
			case 'wordpress':
				$name = $found;
				break;
			default:
				$name = $file;
				break;
		}

		return empty( $name ) || in_array( $name, $this->skip_sources ) ? '' : strtolower( $type . '::' . $name );
	}

	private function init_sources() {
		if ( empty( $this->file_sources ) ) {
			$this->file_sources = array(
				'plugin'    => wp_normalize_path( WP_PLUGIN_DIR . '/' ),
				'mu_plugin' => wp_normalize_path( WPMU_PLUGIN_DIR . '/' ),
				'theme'     => wp_normalize_path( get_theme_root() . '/' ),
				'content'   => wp_normalize_path( WP_CONTENT_DIR . '/' ),
				'wordpress' => wp_normalize_path( ABSPATH ),
			);

			$plugins = get_plugins();

			foreach ( array_keys( $plugins ) as $code ) {
				$parts = explode( '/', $code );

				$this->plugin_sources[ $parts[0] ] = $code;
			}
		}
	}
}
