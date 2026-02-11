<?php

namespace Dev4Press\Plugin\SweepPress\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tracker {
	private array $plugins;
	private array $themes;
	private array $monitor = array();
	private int $offset = MONTH_IN_SECONDS;
	private int $max_time = 10;
	public array $map = array(
		'postmeta'    => 'post',
		'usermeta'    => 'user',
		'termmeta'    => 'term',
		'commentmeta' => 'comment',
		'blogmeta'    => 'blog',
		'logmeta'     => 'log',
		'options'     => 'option',
		'sitemeta'    => 'site_option',
	);

	public function __construct() {
		add_action( 'sweeppress_plugin_theme_scanner', array( $this, 'scanner' ) );
		add_action( 'activated_plugin', array( $this, 'activated_plugin' ) );

		$this->plugins = sweeppress_settings()->get( 'scan_plugins', 'storage' );
		$this->themes  = sweeppress_settings()->get( 'scan_themes', 'storage' );

		if ( is_admin() ) {
			$this->schedule( 10 );
		}

		Monitor::instance();
	}

	public static function instance() : Tracker {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new Tracker();
		}

		return $instance;
	}

	public function from_monitor( $group, $scope, $name ) : array {
		$this->init_monitor_data();
		$real = $this->map[ $scope ];

		$result = array();
		foreach ( $this->monitor[ $group ] as $list ) {

			if ( isset( $list[ $real ][ $name ] ) ) {
				$result = array_merge( $result, $list[ $real ][ $name ] );
			}
		}

		return $result;
	}

	public function init_monitor_data() {
		if ( empty( $this->monitor ) ) {
			$this->monitor = array(
				'meta'   => array(
					'update' => sweeppress_settings()->get( 'monitor_meta_update', 'storage' ),
					'get'    => sweeppress_settings()->get( 'monitor_meta_get', 'storage' ),
				),
				'option' => array(
					'update' => sweeppress_settings()->get( 'monitor_option_update', 'storage' ),
					'get'    => sweeppress_settings()->get( 'monitor_option_get', 'storage' ),
				),
			);
		}
	}

	public function from_tracker( $scope, $name ) : string {
		$name = strtolower( $name );

		foreach ( $this->plugins as $code => $plugin ) {
			$values = $plugin[ $scope ] ?? array();

			if ( ! empty( $values ) ) {
				foreach ( $values as $val ) {
					if ( $name === strtolower( $val ) ) {
						return 'plugin::' . $code;
					}
				}
			}
		}

		foreach ( $this->themes as $code => $theme ) {
			$values = $theme[ $scope ] ?? array();

			if ( ! empty( $values ) ) {
				foreach ( $values as $val ) {
					if ( $name === strtolower( $val ) ) {
						return 'theme::' . $code;
					}
				}
			}
		}

		return '';
	}

	public function schedule( $offset = 5 ) {
		if ( ! wp_next_scheduled( 'sweeppress_plugin_theme_scanner' ) ) {
			if ( $this->has_more() ) {
				wp_schedule_single_event( time() + $offset, 'sweeppress_plugin_theme_scanner' );
			}
		}
	}

	public function scanner() {
		$starting_time  = microtime( true );
		$this->max_time = max( floor( absint( ini_get( 'max_execution_time' ) ) / 2 ), $this->max_time );

		while ( microtime( true ) - $starting_time < $this->max_time && $this->has_more() ) {
			$this->scanner_single();
		}

		$this->schedule();
	}

	public function more_plugins() {
		foreach ( $this->plugins as $code => $plugin ) {
			if ( $plugin['scanned'] + $this->offset < time() ) {
				return $code;
			}
		}

		return false;
	}

	public function more_themes() {
		foreach ( $this->themes as $code => $theme ) {
			if ( $theme['scanned'] + $this->offset < time() ) {
				return $code;
			}
		}

		return false;
	}

	public function prepare_themes( $force = false ) {
		if ( empty( $this->themes ) || $force ) {
			$this->update_themes_scan_storage();
		}
	}

	public function prepare_plugins( $force = false ) {
		if ( empty( $this->plugins ) || $force ) {
			$this->update_plugins_scan_storage();
		}
	}

	public function activated_plugin( $plugin_file ) {
		$this->update_plugins_scan_storage();
	}

	private function has_more() : bool {
		return $this->more_plugins() !== false || $this->more_themes() !== false;
	}

	private function scanner_single() {
		$plugin = $this->more_plugins();

		if ( $plugin === false ) {
			$theme = $this->more_themes();

			if ( $theme !== false ) {
				$path = get_theme_root() . '/' . $theme . '/';
				$data = Scanner::instance()->scan( $path );

				$this->themes[ $theme ] = wp_parse_args( $data, $this->themes[ $theme ] );

				sweeppress_settings()->set( 'scan_themes', $this->themes, 'storage', true );
			}
		} else {
			$parts = explode( '/', $plugin );
			$path  = WP_PLUGIN_DIR . '/' . $parts[0] . '/';
			$data  = Scanner::instance()->scan( $path );

			$this->plugins[ $plugin ] = wp_parse_args( $data, $this->plugins[ $plugin ] );

			sweeppress_settings()->set( 'scan_plugins', $this->plugins, 'storage', true );
		}
	}

	private function update_themes_scan_storage() {
		$list = wp_get_themes();

		foreach ( $list as $code => $theme ) {
			$data = $this->_single_plugin_theme_scan_data( $theme['Name'], $theme['Version'] );

			if ( ! isset( $this->themes[ $code ] ) ) {
				$this->themes[ $code ] = $data;
			} else {
				if ( $this->themes[ $code ]['version'] != $data['version'] ) {
					foreach ( array( 'options', 'sitemeta', 'postmeta', 'termmeta', 'usermeta', 'commentmeta' ) as $key ) {
						$data[ $key ] = $this->themes[ $code ][ $key ];
					}

					$this->themes[ $code ] = $data;
				}
			}
		}

		sweeppress_settings()->set( 'scan_themes', $this->themes, 'storage', true );
	}

	private function update_plugins_scan_storage() {
		$list = get_plugins();

		foreach ( $list as $code => $plugin ) {
			$data = $this->_single_plugin_theme_scan_data( $plugin['Name'], $plugin['Version'] );

			if ( ! isset( $this->plugins[ $code ] ) ) {
				$this->plugins[ $code ] = $data;
			} else {
				if ( $this->plugins[ $code ]['version'] != $data['version'] ) {
					foreach ( array( 'options', 'sitemeta', 'postmeta', 'termmeta', 'usermeta', 'commentmeta' ) as $key ) {
						$data[ $key ] = $this->plugins[ $code ][ $key ];
					}

					$this->plugins[ $code ] = $data;
				}
			}
		}

		sweeppress_settings()->set( 'scan_plugins', $this->plugins, 'storage', true );
	}

	private function _single_plugin_theme_scan_data( $name, $version ) : array {
		return array(
			'name'        => $name,
			'version'     => $version,
			'options'     => array(),
			'sitemeta'    => array(),
			'postmeta'    => array(),
			'termmeta'    => array(),
			'usermeta'    => array(),
			'commentmeta' => array(),
			'scanned'     => 0,
			'timer'       => 0,
		);
	}
}
