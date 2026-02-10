<?php
/**
 * Class: WP Transients Helper class
 *
 * Helper class to manipulate WP transients.
 *
 * @package advanced-analytics
 *
 * @since 1.7.0
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

use ADVAN\Lists\Transients_List;
use ADVAN\Entities_Global\Common_Table;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Helpers\Transients_Helper' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 1.7.0
	 */
	class Transients_Helper {

		public const WP_CORE_TRANSIENTS = array(
			'update_themes',
			'update_plugins',
			'update_core',
			'theme_roots',
			'poptags_',
			'doing_cron',
			'wp_theme_files_patterns-',
			'wp_plugin_dependencies_plugin_data',
			'wp_styles_for_blocks',
			'wp_core_block_css_files',
			'health-check-site-status-result',
		);

		/**
		 * Deletes a transient
		 *
		 * @param int $id - The hash of the transient to delete.
		 *
		 * @return bool|\WP_Error
		 *
		 * @since 1.7.0
		 */
		public static function delete_transient( int $id ) {

			if ( 0 < $id ) {
				global $wpdb;

				$esc_name = '%' . $wpdb->esc_like( '_transient_' ) . '%';
				$esc_time = '%' . $wpdb->esc_like( '_transient_timeout_' ) . '%';

				$sql = array( 'SELECT' );

				$sql[] = 'option_name';

				$sql[] = "FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s AND option_id = %d";

				$query = implode( ' ', $sql );

				// Prepare.
				$prepared = $wpdb->prepare( $query, $esc_name, $esc_time, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				$transient = $wpdb->get_var( $prepared, 0 ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			}

			// Bail if no Transient.
			if ( empty( $transient ) ) {
				return false;
			}

			$transient_name = self::get_transient_name( $transient );

			// Transient type.
			$retval = ( false !== self::is_site_wide( $transient ) )
			? \delete_site_transient( $transient_name )
			: \delete_transient( $transient_name );

			if ( false === $retval ) {
				return new \WP_Error(
					'transient_not_deleted',
					__( 'Transient is not / can not be deleted.', '0-day-analytics' )
				);
			}

			// Return.
			return $retval;
		}

		/**
		 * Removes all cron events for a specific hook
		 *
		 * @param string $hook The action hook name.
		 *
		 * @since 1.7.0
		 */
		public static function clear_events( $hook ) {
			\wp_clear_scheduled_hook( $hook );
		}

		/**
		 * Is a transient name site-wide?
		 *
		 * @param  string $transient_name - The transient name.
		 *
		 * @return boolean
		 *
		 * @since 1.7.0
		 */
		public static function is_site_wide( $transient_name = '' ): bool {
			return ( false !== strpos( (string) $transient_name, '_site_transient' ) );
		}

		/**
		 * Retrieve the transient name from the transient object
		 *
		 * @param  string $transient - The transient name.
		 *
		 * @return string
		 *
		 * @since 1.7.0
		 */
		public static function get_transient_name( $transient = false ): string {

			// Bail if no Transient.
			if ( empty( $transient ) ) {
				return '';
			}

			// Position.
			$pos = self::is_site_wide( $transient )
			? 16
			: 11;

			return substr( $transient, $pos, strlen( $transient ) );
		}

		/**
		 * Retrieve a transient by its ID
		 *
		 * @param \WP_REST_Request $request - The request object.
		 * @param int              $id - The ID of the transient to retrieve.
		 *
		 * @return array
		 *
		 * @since 1.8.5
		 * @since 3.8.0 - Added $request parameter.
		 */
		public static function get_transient_by_id( ?\WP_REST_Request $request = null, $id = 0 ) {
			global $wpdb;

			$id = \absint( $id );

			// Bail if empty ID.
			if ( empty( $id ) ) {
				if ( null !== $request ) {
					$id = $request->get_param( 'transient_id' );
				}
				if ( empty( $id ) ) {
					return false;
				}
			}

			// Prepare.
			$prepared = $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_id = %d", $id );

			// Query.
			$transient = $wpdb->get_row( $prepared, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( null === $request ) {
				return $transient;
			} elseif ( ! empty( $transient ) ) {
				\ob_start();

				$name = self::get_transient_name( $transient['option_name'] );

				if ( in_array( $name, self::WP_CORE_TRANSIENTS ) ) {
					?>
					<div id="advaa-status-notice" class="notice notice-warning">
						<p><?php esc_html_e( 'This is a WP core transient', '0-day-analytics' ); ?></p>
					</div>
					<?php
				} else {
					foreach ( self::WP_CORE_TRANSIENTS as $trans_name ) {
						if ( \str_starts_with( $name, $trans_name ) ) {
							?>
									<div id="advaa-status-notice" class="notice notice-warning">
										<p><?php esc_html_e( 'This is a WP core transient, even if you update it, the new value will be overridden by the core!', '0-day-analytics' ); ?></p>
									</div>
									<?php
									break;
						}
					}
				}
				?>
				<table class="widefat striped table-view-list" style="max-width:100%;table-layout: fixed;">
					<col width="20%" />
					<col width="80%" />
					<thead>
						<tr>
							<th>
								<?php echo \esc_html_e( 'Name', '0-day-analytics' ); ?>
							</th>
							<th>
								<?php echo \esc_html_e( 'Value', '0-day-analytics' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th><?php esc_html_e( 'Option ID', '0-day-analytics' ); ?></th>
							<td><?php echo esc_attr( $transient['option_id'] ); ?></td>
						</tr>
						<tr>
							<th><?php \esc_html_e( 'Name', '0-day-analytics' ); ?></th>
							<td><?php echo \esc_attr( self::clear_transient_name( $transient['option_name'] ) ); ?></td>
						</tr>
						<?php
						$expiration = self::get_transient_expiration_time( $transient['option_name'] );
						if ( 0 !== $expiration ) {

							$next_run_gmt        = gmdate( 'Y-m-d H:i:s', $expiration );
							$next_run_date_local = \get_date_from_gmt( $next_run_gmt, 'Y-m-d' );
							$next_run_time_local = \get_date_from_gmt( $next_run_gmt, 'H:i:s' );
						}
						if ( 0 !== $expiration ) {
							?>
						<tr>
							<th><?php \esc_html_e( 'Expiration', '0-day-analytics' ); ?></th>
							<td>
							<?php
								echo \esc_attr( $next_run_date_local ) . ' ' . \esc_attr( $next_run_time_local );
							?>
							</td>
						</tr>
							<?php
						} else {

						}

						?>
						<tr>
							<th><?php esc_html_e( 'Value', '0-day-analytics' ); ?></th>
							<td>
								<?php echo Common_Table::format_value_for_html( $transient['option_value'] ); ?>
							</td>
						</tr>
					</tbody>
				</table>
					<?php
					$message = \ob_get_clean();

					return rest_ensure_response(
						array(
							'success'    => true,
							'mail_body'  => $message,
							'transient_name' => $name,
						)
					);

			} else {
				return new \WP_Error(
					'empty_row',
					__( 'No record found.', '0-day-analytics' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Update an existing transient
		 *
		 * @param  array   $transient - The transient to update.
		 * @param  boolean $site_wide - Is the transient site-wide?.
		 *
		 * @return boolean
		 *
		 * @since 1.8.5
		 */
		public static function update_transient( $transient = '', $site_wide = false ) {

			// Bail if no Transient.
			if ( empty( $transient ) ) {
				return false;
			}

			if ( ! isset( $_POST['value'], $_REQUEST['cron_next_run_custom_date'], $_REQUEST['cron_next_run_custom_time'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return false;
			}

			// Values.
			$value = \stripslashes( $_POST['value'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$value = \maybe_unserialize( $value );

			/*
			// $expiration = \absint( \wp_unslash( $_POST['expires'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Subtract now.
			// $expiration = ( $expiration - time() );
			*/

			$current_time = time();

			$date = ( ( isset( $_REQUEST['cron_next_run_custom_date'] ) ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['cron_next_run_custom_date'] ) ) : '' );

			$time = ( ( isset( $_REQUEST['cron_next_run_custom_time'] ) ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['cron_next_run_custom_time'] ) ) : '' );

			$next_run_local = $date . ' ' . $time;

			$next_run_local = strtotime( $next_run_local, $current_time );

			if ( false === $next_run_local ) {
				return new \WP_Error(
					'invalid_timestamp',
					__( 'Invalid timestamp provided.', '0-day-analytics' )
				);
			}

			$expiration = (int) \get_gmt_from_date( \gmdate( 'Y-m-d H:i:s', $next_run_local ), 'U' );

			$expiration = ( $expiration - time() );

			$new_name = $_POST['name'] ?? '';

			if ( ! empty( $new_name ) && self::clear_transient_name( $new_name ) !== $transient ) {
				\delete_transient( $transient );
				$transient = $new_name;
			}

			// Transient type.
			$retval = ( false !== $site_wide )
			? \set_site_transient( $transient, $value, $expiration )
			: \set_transient( $transient, $value, $expiration );

			return $retval;
		}

		/**
		 * Creates transient using values in $_POST array
		 *
		 * @param string  $transient - The name of the transient.
		 * @param boolean $site_wide - Is this a site-wide transient or not.
		 *
		 * @return boolean
		 *
		 * @since 1.9.2
		 */
		public static function create_transient( $transient = '', $site_wide = false ) {
			return self::update_transient( $transient, $site_wide );
		}

		/**
		 * Retrieve the human-friendly transient value from the transient object
		 *
		 * @param  string $transient - The transient value.
		 *
		 * @return string/int
		 *
		 * @since 1.7.0
		 */
		public static function get_transient_value( $transient ) {

			// Get the value type.
			$type = self::get_transient_value_type( $transient );

			// Trim value to 100 chars.
			$value = substr( $transient, 0, 100 );

			// Escape & wrap in <code> tag.
			$value = '<code>' . \esc_html( $value ) . '</code>';

			// Return.
			return $value . '<br><span class="transient-type badge">' . esc_html( $type ) . '</span>';
		}

		/**
		 * Try to guess the type of value the Transient is
		 *
		 * @param  mixed $transient - The transient value.
		 *
		 * @return string
		 *
		 * @since 1.7.0
		 */
		private static function get_transient_value_type( $transient ): string {

			// Default type.
			$type = \esc_html__( 'unknown', '0-day-analytics' );

			// Try to unserialize.
			$value = \maybe_unserialize( $transient );

			// Array.
			if ( is_array( $value ) ) {
				$type = \esc_html__( 'array', '0-day-analytics' );

				// Object.
			} elseif ( is_object( $value ) ) {
				$type = \esc_html__( 'object', '0-day-analytics' );

				// Serialized array.
			} elseif ( \is_serialized( $value ) ) {
				$type = \esc_html__( 'serialized', '0-day-analytics' );

				// HTML.
			} elseif ( strip_tags( $value ) !== $value ) {
				$type = \esc_html__( 'html', '0-day-analytics' );

				// Scalar.
			} elseif ( is_scalar( $value ) ) {

				if ( is_numeric( $value ) ) {

					// Likely a timestamp.
					if ( 10 === strlen( $value ) ) {
						$type = \esc_html__( 'timestamp?', '0-day-analytics' );

						// Likely a boolean.
					} elseif ( in_array( $value, array( '0', '1' ), true ) ) {
						$type = \esc_html__( 'boolean?', '0-day-analytics' );

						// Any number.
					} else {
						$type = \esc_html__( 'numeric', '0-day-analytics' );
					}

					// JSON.
				} elseif ( is_string( $value ) && is_object( json_decode( $value ) ) ) {

					$type = \esc_html__( 'json', '0-day-analytics' );
				} elseif ( is_string( $value ) && in_array( $value, array( 'no', 'yes', 'false', 'true' ), true ) ) {
					$type = \esc_html__( 'boolean?', '0-day-analytics' );

					// Scalar.
				} else {
					$type = \esc_html__( 'scalar', '0-day-analytics' );
				}

				// Empty.
			} elseif ( empty( $value ) ) {
				$type = \esc_html__( 'empty', '0-day-analytics' );
			}

			// Return type.
			return $type;
		}

		/**
		 * Retrieve the expiration timestamp
		 *
		 * @param  string $transient - The transient name.
		 *
		 * @return int
		 *
		 * @since 1.7.0
		 */
		public static function get_transient_expiration_time( $transient ): int {

			// Get the same to use in the option key.
			$name = self::get_transient_name( $transient );

			// Get the value of the timeout.
			$time = self::is_site_wide( $transient )
			? \get_option( "_site_transient_timeout_{$name}" )
			: \get_option( "_transient_timeout_{$name}" );

			// Return the value.
			return (int) $time;
		}

		/**
		 * Collect error items.
		 *
		 * @param  array $args - Array with arguments to use.
		 *
		 * @return array|int
		 *
		 * @since 1.9.0
		 */
		public static function get_transient_items( $args = array() ) {

			global $wpdb;

			$timezone = WP_Helper::get_mysql_time_zone();
			$wpdb->query( $wpdb->prepare( "SET time_zone = %s", $timezone ) );

			// Parse arguments.
			$parsed_args = Transients_List::parse_args( $args );

			// Escape some LIKE parts.
			$esc_name      = '' . $wpdb->esc_like( '_transient_' ) . '%';
			$esc_site_name = '' . $wpdb->esc_like( '_site_transient_' ) . '%';
			$esc_time      = '%' . $wpdb->esc_like( '_transient_timeout_' ) . '%';

			// SELECT.
			$sql = array( 'SELECT' );

			// COUNT.
			if ( ! empty( $parsed_args['count'] ) ) {
				$sql[] = 'count(go.option_id)';
			} else {
				$sql[] = 'go.option_id, go.option_name, go.option_value, go.autoload, d.option_value AS schedule';
			}

			$sql[] = "FROM {$wpdb->options} as go ";

			// if ( empty( $parsed_args['count'] ) ) {

			// FROM.

			// old - ON d.option_name LIKE CONCAT('%_transient_timeout_', SUBSTRING_INDEX( go.option_name, '_transient_', -1 ), '%')

			$sql[] = "LEFT JOIN
				{$wpdb->options} d
				ON d.option_name = CONCAT(
					'_transient_timeout_', 
					SUBSTRING( go.option_name, LENGTH( '_transient_' ) + 1 )
				)
			WHERE ( go.option_name LIKE %s OR go.option_name LIKE %s ) AND go.option_name NOT LIKE %s";

			if ( ! empty( $parsed_args['type'] ) ) {
				if ( 'persistent' === $parsed_args['type'] ) {
					$sql[] = ' AND d.option_value IS NULL';
				}
				if ( 'with_expiration' === $parsed_args['type'] ) {
					$sql[] = ' AND d.option_value IS NOT NULL';
				}
				if ( 'expired' === $parsed_args['type'] ) {
					$sql[] = ' AND ( d.option_value IS NOT NULL AND d.option_value < UNIX_TIMESTAMP( CURRENT_TIMESTAMP ) )';
				}
				if ( 'core' === $parsed_args['type'] ) {
					$sql[]  = ' AND (';
					$in_sql = '';
					foreach ( self::WP_CORE_TRANSIENTS as $transient_name ) {
						$search  = '%' . $wpdb->esc_like( $transient_name ) . '%';
						$in_sql .= $wpdb->prepare( ' go.option_name LIKE %s OR', $search );
						// $in_sql .= ' go.option_name LIKE \'%' . $wpdb->esc_like( $transient_name ) . '%\' OR ';
					}

					$sql[] = \rtrim( $in_sql, ' OR ' );
					$sql[] = ')';
				}
			}
			// }

			// Search.
			if ( ! empty( $parsed_args['search'] ) ) {
				$search = '%' . $wpdb->esc_like( $parsed_args['search'] ) . '%';
				$sql[]  = $wpdb->prepare( 'AND go.option_name LIKE %s', $search );
			}

			// Limits.
			if ( empty( $parsed_args['count'] ) && empty( $parsed_args['all'] ) ) {
				$offset = absint( $parsed_args['offset'] );
				$per_page = absint( $parsed_args['per_page'] );

				// if ( ! empty( $parsed_args['orderby'] ) && \in_array( $parsed_args['orderby'], array( 'transient_name' ) ) ) {

				// $orderby = 'option_name';

				// $order = 'DESC';

				// if ( ! empty( $parsed_args['order'] ) && \in_array( $parsed_args['order'], array( 'ASC', 'DESC', 'asc', 'desc' ) ) ) {

				// $order = $parsed_args['order'];
				// }

				// $sql[] = $wpdb->prepare(
				// 'ORDER BY ' . \esc_sql( $orderby ) . ' ' . \esc_sql( $order ) . ' LIMIT %d, %d',
				// $offset,
				// $number
				// );
				// } else {
				$sql[] = $wpdb->prepare( 'ORDER BY option_id DESC LIMIT %d, %d', $offset, $per_page );
				// }
			}

			// Combine the SQL parts.
			$query = implode( ' ', $sql );

			// Prepare.
			$prepared = $wpdb->prepare( $query, $esc_name, $esc_site_name, $esc_time ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Query.
			$transients = empty( $parsed_args['count'] )
			? $wpdb->get_results( $prepared, \ARRAY_A ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: $wpdb->get_var( $prepared, 0 );    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( empty( $parsed_args['count'] ) && ! empty( $transients ) ) {
				$normalized_data = array();
				foreach ( $transients as $transient ) {
					$normalized_data[] = array(
						'transient_name' => self::get_transient_name( $transient['option_name'] ),
						'value'          => self::get_transient_value( $transient['option_value'] ),
						'schedule'       => (int) $transient['schedule'],
						'id'             => $transient['option_id'],
					);
				}
				$transients = $normalized_data;
			}

			// Return transients.
			return $transients;
		}

		/**
		 * Checks for _transient and _site_transient strings and removes them if present.
		 *
		 * @param string $transient - The name of the transient to clear.
		 *
		 * @return string
		 *
		 * @since 2.7.0
		 */
		public static function clear_transient_name( string $transient ): string {
			if ( \str_starts_with( $transient, '_site_transient_' ) ) {
				return str_replace( '_site_transient_', '', $transient );
			} elseif ( \str_starts_with( $transient, '_transient_' ) ) {
				return str_replace( '_transient_', '', $transient );
			}

			return $transient;
		}
	}
}
