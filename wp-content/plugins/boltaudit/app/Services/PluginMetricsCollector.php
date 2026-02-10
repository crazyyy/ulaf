<?php

namespace BoltAudit\App\Services;

use ReflectionFunction;
use ReflectionMethod;

/**
 * Collects performance metrics for individual plugins.
 */
class PluginMetricsCollector {
	protected string $slug;

	public function run( string $slug ): array {
		$this->slug = $slug;

		return $this->collect_now();
	}

	protected function collect_now(): array {
		$scripts = $this->merge_registered_and_enqueued(
			$this->get_scripts_by_plugin() ?? [],
			$this->get_enqueued_scripts_by_plugin() ?? []
		);

		$styles = $this->merge_registered_and_enqueued(
			$this->get_styles_by_plugin() ?? [],
			$this->get_enqueued_styles_by_plugin() ?? []
		);

		$enqueued = array_filter(
			array_merge( $scripts ?? [], $styles ?? [] ),
			fn( $asset ) => ! empty( $asset['enqueued'] )
		);

		return [
			'scripts'        => $scripts,
			'styles'         => $styles,
			'scripts_count'  => count( $scripts ),
			'styles_count'   => count( $styles ),
			'enqueued_count' => count( $enqueued ),
			'hooks'          => $this->get_hooks_by_plugin(),
		];
	}

	protected function merge_registered_and_enqueued( array $registered, array $enqueued ): array {
		$map = [];
		foreach ( $registered as $item ) {
			$map[$item['handle']] = $item;
		}

		foreach ( $enqueued as $item ) {
			if ( isset( $map[$item['handle']] ) ) {
				$map[$item['handle']]['enqueued'] = true;
			} else {
				$map[$item['handle']] = [
					'handle'   => $item['handle'],
					'src'      => $item['src'],
					'enqueued' => true,
				];
			}
		}

		return array_values( $map );
	}

	protected function belongs_to_plugin( string $path ): bool {
		if ( empty( $path ) ) {
			return false;
		}
		$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR . '/' . $this->slug );

		return strpos( wp_normalize_path( $path ), $plugin_dir ) === 0;
	}

	protected function get_callback_file( $callback ) {
		try {
			if ( is_string( $callback ) ) {
				if ( strpos( $callback, '::' ) !== false ) {
					list( $class, $method ) = explode( '::', $callback );
					if ( class_exists( $class ) ) {
						return ( new ReflectionMethod( $class, $method ) )->getFileName();
					}
				} elseif ( function_exists( $callback ) ) {
					return ( new ReflectionFunction( $callback ) )->getFileName();
				}
			} elseif ( is_array( $callback ) ) {
				if ( is_object( $callback[0] ) || class_exists( $callback[0] ) ) {
					return ( new ReflectionMethod( $callback[0], $callback[1] ) )->getFileName();
				}
			} elseif ( $callback instanceof \Closure ) {
				return ( new ReflectionFunction( $callback ) )->getFileName();
			}
		} catch ( \ReflectionException $e ) {}

		return;
	}

	public function get_hooks_by_plugin(): int {
		global $wp_filter;
		$count = 0;
		foreach ( $wp_filter as $hook ) {
			foreach ( $hook->callbacks ?? [] as $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$file = $this->get_callback_file( $callback['function'] ) ?? '';
					if ( $this->belongs_to_plugin( $file ) ) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	public function get_scripts_by_plugin(): array {
		global $wp_scripts;
		$found = [];
                $registered_scripts = $wp_scripts && isset( $wp_scripts->registered ) ? $wp_scripts->registered : [];
                foreach ( $registered_scripts as $handle => $data ) {
			if ( isset( $data->src ) && strpos( wp_normalize_path( $data->src ), "/{$this->slug}/" ) !== false ) {
				$found[] = ['handle' => $handle, 'src' => $data->src];
			}
		}

		return $this->enrich_asset_metrics( $found );
	}

	public function get_enqueued_scripts_by_plugin(): array {
		global $wp_scripts;
		$found = [];
                $queued_scripts = $wp_scripts && isset( $wp_scripts->queue ) ? $wp_scripts->queue : [];
                foreach ( $queued_scripts as $handle ) {
                        $data = $wp_scripts && isset( $wp_scripts->registered[$handle] ) ? $wp_scripts->registered[$handle] : null;
			if ( $data && isset( $data->src ) && strpos( wp_normalize_path( $data->src ), "/{$this->slug}/" ) !== false ) {
				$found[] = ['handle' => $handle, 'src' => $data->src];
			}
		}

		return $found;
	}

	public function get_styles_by_plugin(): array {
		global $wp_styles;
		$found = [];
                $registered_styles = $wp_styles && isset( $wp_styles->registered ) ? $wp_styles->registered : [];
                foreach ( $registered_styles as $handle => $data ) {
			if ( isset( $data->src ) && strpos( wp_normalize_path( $data->src ), "/{$this->slug}/" ) !== false ) {
				$found[] = ['handle' => $handle, 'src' => $data->src];
			}
		}

		return $this->enrich_asset_metrics( $found );
	}

	public function get_enqueued_styles_by_plugin(): array {
		global $wp_styles;
		$found = [];
                $queued_styles = $wp_styles && isset( $wp_styles->queue ) ? $wp_styles->queue : [];
                foreach ( $queued_styles as $handle ) {
                        $data = $wp_styles && isset( $wp_styles->registered[$handle] ) ? $wp_styles->registered[$handle] : null;
			if ( $data && isset( $data->src ) && strpos( wp_normalize_path( $data->src ), "/{$this->slug}/" ) !== false ) {
				$found[] = ['handle' => $handle, 'src' => $data->src];
			}
		}

		return $found;
	}

	protected function enrich_asset_metrics( array $assets ): array {
		foreach ( $assets as &$asset ) {
			$full_url = $asset['src'];

			// Only handle full URLs
			if ( ! filter_var( $full_url, FILTER_VALIDATE_URL ) ) {
				$asset['load_time_ms'] = null;
				$asset['size_kb']      = null;
				continue;
			}

			$start    = microtime( true );
			$response = wp_remote_get( $full_url );
			$elapsed  = ( microtime( true ) - $start ) * 1000;

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				$asset['load_time_ms'] = null;
				$asset['size_kb']      = null;
				continue;
			}

			$asset['load_time_ms'] = round( $elapsed, 2 );
			$asset_body            = wp_remote_retrieve_body( $response );
			$size_bytes            = strlen( $asset_body );
			$asset['size_kb']      = round( $size_bytes / 1024, 2 ); // Convert to kilobytes
		}

		return $assets;
	}

}
