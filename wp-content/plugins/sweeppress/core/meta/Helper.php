<?php

namespace Dev4Press\Plugin\SweepPress\Meta;

use Dev4Press\v54\Core\Quick\Str;

class Helper {
	public static function _find_prefixes( $options ) : array {
		$raw   = array();
		$marks = array();

		foreach ( $options as $option ) {
			$raw[ $option ] = preg_split( '/(?<=[_\-\s])/', $option, - 1, PREG_SPLIT_NO_EMPTY );

			$item   = '';
			$widget = false;
			for ( $i = 1; $i < count( $raw[ $option ] ); $i ++ ) {
				$idx  = $i - 1;
				$item .= $raw[ $option ][ $idx ];

				if ( ! $widget && $item != '_' && $item != '-' && $item != ' ' ) {
					$marks[ $item ][] = $option;
				}

				if ( $item == 'widget_' ) {
					$widget = true;
				}
			}
		}

		foreach ( $marks as $key => $list ) {
			if ( count( $list ) == 1 ) {
				unset( $marks[ $key ] );
			}
		}

		ksort( $marks );

		$keys     = array_keys( $marks );
		$id       = 1;
		$groups   = array();
		$prefixes = array();
		$core     = array();
		$skip     = array();

		for ( $i = 0; $i < count( $keys ); ++ $i ) {
			$idx = $keys[ $i ];
			$xdi = trim( $idx, '-_ ' );

			if ( preg_match( '/-|_|\s/', $xdi ) === 0 ) {
				$core[] = $idx;
			}

			if ( in_array( $idx, $skip ) ) {
				++ $id;
				continue;
			}

			$groups[ $idx ] = array( $idx );

			for ( $j = $id; $j < count( $keys ); ++ $j ) {
				if ( str_starts_with( $keys[ $j ], $idx ) ) {
					if ( $marks[ $idx ] === $marks[ $keys[ $j ] ] ) {
						$groups[ $idx ][] = $keys[ $j ];
						$skip[]           = $keys[ $j ];
					}
				} else {
					break;
				}
			}

			++ $id;
		}

		foreach ( $groups as $items ) {
			$key        = array_key_last( $items );
			$prefixes[] = $items[ $key ];
		}

		sort( $core );
		sort( $prefixes );

		return array(
			'core'     => $core,
			'prefixes' => $prefixes,
		);
	}

	public static function _render_value( $item ) : string {
		$value = $item->option_value ?? $item->meta_value;

		if ( is_null( $value ) ) {
			$render = '<span class="sweeppress-badge badge-empty">NULL</span>';
		} else if ( $value === '0' ) {
			$render = '0';
		} else if ( empty( $value ) ) {
			$render = '<span class="sweeppress-badge badge-empty">' . esc_html__( 'Empty', 'sweeppress' ) . '</span>';
		} else if ( is_numeric( $value ) ) {
			$render = esc_html( $value );
		} else if ( is_serialized( $value ) ) {
			if ( sweeppress()->debugpress ) {
				$render = debugpress_rx( maybe_unserialize( $value ), false );
			} else {
				$render = '<span class="sweeppress-badge badge-serialized">' . esc_html__( 'Serialized Content', 'sweeppress' ) . '</span>';
			}
		} else if ( Str::is_json( $value, false ) ) {
			if ( sweeppress()->debugpress ) {
				$render = debugpress_rx( json_decode( $value ), false );
			} else {
				$render = '<span class="sweeppress-badge badge-serialized">' . esc_html__( 'JSON Content', 'sweeppress' ) . '</span>';
			}
		} else {
			$length = strlen( $value );

			if ( $length > 128 && sweeppress()->debugpress ) {
				$render = debugpress_rx( $value, false );
			} else {
				$render = esc_html( substr( $value, 0, 128 ) );

				if ( $length > 128 ) {
					$render .= ' &hellip;';
				}
			}
		}

		return $render;
	}

	public static function _render_status( $item, $show_activity = true ) : string {
		$flags = array();

		if ( $item->result['type'] == 'theme' ) {
			$flags[] = '<span class="sweeppress-badge badge-theme">' . __( 'Theme', 'sweeppress' ) . '</span>';
		} else if ( $item->result['type'] == 'widget' ) {
			$flags[] = '<span class="sweeppress-badge badge-widget">' . __( 'Widget', 'sweeppress' ) . '</span>';
		} else if ( $item->result['type'] == 'wordpress' ) {
			$flags[] = '<span class="sweeppress-badge badge-wordpress">' . __( 'WordPress', 'sweeppress' ) . '</span>';
		} else if ( $item->result['type'] == 'plugin' ) {
			$flags[] = '<span class="sweeppress-badge badge-plugin">' . __( 'Plugin', 'sweeppress' ) . '</span>';
		} else if ( $item->result['type'] == 'component' || $item->result['type'] == 'cache' ) {
			$flags[] = '<span class="sweeppress-badge badge-component">' . ( $item->result['type'] == 'cache' ? __( 'Cache', 'sweeppress' ) : __( 'Component', 'sweeppress' ) ) . '</span>';
		} else {
			$flags[] = '<span class="sweeppress-badge badge-unknown">' . __( 'Unknown', 'sweeppress' ) . '</span>';
		}

		if ( $item->result['type'] == 'widget' ) {
			if ( $item->result['installed'] ) {
				$flags[] = '<span class="sweeppress-badge badge-installed">' . __( 'Registered', 'sweeppress' ) . '</span>';
			} else {
				$flags[] = '<span class="sweeppress-badge badge-missing">' . __( 'Not Registered', 'sweeppress' ) . '</span>';
			}
		}

		if ( $item->result['type'] == 'plugin' || $item->result['type'] == 'theme' ) {
			if ( $item->result['installed'] ) {
				$flags[] = '<span class="sweeppress-badge badge-installed">' . __( 'Installed', 'sweeppress' ) . '</span>';

				if ( $show_activity ) {
					if ( $item->result['active'] ) {
						$flags[] = '<span class="sweeppress-badge badge-active">' . __( 'Active', 'sweeppress' ) . '</span>';
					} else {
						$flags[] = '<span class="sweeppress-badge badge-inactive">' . __( 'Inactive', 'sweeppress' ) . '</span>';
					}
				}
			} else {
				$flags[] = '<span class="sweeppress-badge badge-missing">' . __( 'Missing', 'sweeppress' ) . '</span>';
			}
		}

		return '<div>' . join( '', $flags ) . '</div>';
	}

	public static function _render_source( $item ) : string {
		$render = array();

		if ( $item->result['type'] == 'unknown' ) {
			$render[] = __( 'Unknown', 'sweeppress' );
		} else {
			foreach ( $item->result['items'] as $item ) {
				$render[] = $item['name'] . ' <i class="d4p-icon d4p-ui-question-sqaure" title="' . $item['code'] . '"></i>';
			}
		}

		return '<ul class="_info"><li>' . join( '</li><li>', $render ) . '</li></ul>';
	}

	public static function _short_count( $number ) : string {
		$suffix = array( '', 'K', 'M' );

		for ( $i = 0; $i < count( $suffix ); $i ++ ) {
			$divide = $number / pow( 1000, $i );

			if ( $divide < 1000 ) {
				return round( $divide ) . $suffix[ $i ];
			}
		}

		return $number;
	}
}
