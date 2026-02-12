<?php

namespace Dev4Press\Plugin\SweepPress\Base;

use Dev4Press\Plugin\SweepPress\Meta\Storage;
use Dev4Press\Plugin\SweepPress\Meta\Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Options {
	protected string $scope = '';
	protected array $defaults = array();
	protected array $transients = array();
	protected array $cache = array();

	public function __construct() {
	}

	/** @return static */
	public static function instance() {
		static $instance = array();

		if ( ! isset( $instance[ static::class ] ) ) {
			$instance[ static::class ] = new static();
		}

		return $instance[ static::class ];
	}

	public function get_defaults() : array {
		return $this->defaults;
	}

	public function get_transients() : array {
		return $this->transients;
	}

	public function identify( string $name ) : array {
		if ( isset( $this->cache[ $this->scope ][ $name ] ) ) {
			return $this->cache[ $this->scope ][ $name ];
		}

		$items = array();
		if ( $this->scope == 'options' && str_starts_with( $name, 'theme_mods_' ) ) {
			$items[] = Storage::instance()->detection_theme_mods( $name );
		} else if ( $this->scope == 'options' && str_starts_with( $name, 'widget_' ) ) {
			$items[] = 'widget::' . substr( $name, 7 );
		} else {
			$items = Storage::instance()->detection_processor( $this->scope, $name );
			$items = array_merge( $items, Storage::instance()->registration_processor( $this->scope, $name ) );
		}

		$tracked = Tracker::instance()->from_tracker( $this->scope, $name );

		if ( ! empty( $tracked ) ) {
			$items[] = $tracked;
		}

		$items = array_merge( $items, Tracker::instance()->from_monitor( 'option', $this->scope, $name ) );
		$items = array_unique( $items );
		$items = array_filter( $items );

		if ( ! isset( $this->cache[ $this->scope ] ) ) {
			$this->cache[ $this->scope ] = array();
		}

		$this->cache[ $this->scope ][ $name ] = Storage::instance()->items_to_results( $items );

		return $this->cache[ $this->scope ][ $name ];
	}

	public function filtered( $options, $source = '', $status = '' ) : array {
		$identity = array();
		$filtered = array();

		foreach ( $options as $option ) {
			$identity[ $option ] = $this->identify( $option );
		}

		if ( $source == 'unknown' ) {
			$status = '';
		}

		foreach ( $identity as $key => $result ) {
			$use = true;

			if ( $source != '' && $source != 'all' ) {
				if ( $source == 'known' ) {
					$use = ! ( $result['type'] == 'unknown' );
				} else if ( $result['type'] != $source ) {
					$use = false;
				}
			}

			if ( $use && $status != '' && $status != 'all' ) {
				if ( $result['type'] != 'widget' && $result['type'] != 'component' && $result['type'] != 'cache' ) {
					$actual = $result['installed'] && $result['active'] ? 'active' : ( $result['installed'] ? 'installed' : 'missing' );

					if ( $actual != $status ) {
						$use = false;
					}
				} else if ( $result['type'] == 'widget' ) {
					$actual = $result['installed'] ? 'installed' : 'missing';

					if ( $actual != $status ) {
						$use = false;
					}
				} else {
					$use = false;
				}
			}

			if ( $use ) {
				$filtered[] = $key;
			}
		}

		return $filtered;
	}
}
