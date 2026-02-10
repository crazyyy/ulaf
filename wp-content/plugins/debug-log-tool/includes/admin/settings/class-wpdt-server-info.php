<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Server_Info' ) ) :

	final class WPDT_Server_Info {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {
		}

		/**
		 * Layout for server info page
		 *
		 * @return void
		 */
		public static function layout() {
			$tabs = array(
				'wp-config'  => 'WP Config',
				'htaccess'   => 'HTACCESS',
				'phpinfo'    => 'PHP Info',
				'database'   => 'Database',
				'cookies'    => 'Cookies',
				'transients' => 'Transients',
				'cron'       => 'Cron Jobs',
			);
			$nonce = wp_create_nonce( 'wpdt_server_info_nonce' );
			$active_tab = ( isset( $_GET['tab'] ) && check_admin_referer( 'wpdt_server_info_nonce' ) ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'wp-config';
			?>
			<div class="wpdt-settings-page">
				<?php WPDT_Admin::load_setting_header_html(); ?>
				<div class="wpdt-settings-container">
					<div class="wpdt-server-info-container">
						<h2 class="nav-tab-wrapper">
							<?php
							foreach ( $tabs as $tab => $name ) {
								$active = $active_tab === $tab ? 'nav-tab-active' : '';
								?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpdt-server-info&tab=' . $tab . '&_wpnonce=' . $nonce ) ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>"><?php echo esc_attr( $name ); ?></a>
								<?php
							}
							?>
						</h2>
						<div class="wpdt-server-info-content">
							<?php
							switch ( $active_tab ) {
								case 'wp-config':
									self::get_wp_config();
									break;
								case 'htaccess':
									self::get_htaccess();
									break;
								case 'phpinfo':
									self::get_phpinfo();
									break;
								case 'database':
									self::get_database_info();
									break;
								case 'cookies':
									self::get_cookies();
									break;
								case 'transients':
									self::get_transients();
									break;
								case 'cron':
									self::get_cron_jobs();
									break;
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Get WP Config
		 *
		 * @return void
		 */
		public static function get_wp_config() {
			$wp_config_path = WPDT_Config_Manager::wpdt_get_config_path();
			if ( file_exists( $wp_config_path ) ) {
				$wp_config = file_get_contents( $wp_config_path ); // phpcs:ignore
			} else {
				$wp_config = 'wp-config.php file not found';
			}
			?>
			<textarea readonly><?php echo esc_html( $wp_config ); ?></textarea>
			<?php
		}

		/**
		 * Get HTACCESS
		 *
		 * @return void
		 */
		public static function get_htaccess() {
			$htaccess_path = trailingslashit( get_home_path() ) . '.htaccess';
			if ( file_exists( $htaccess_path ) ) {
				$htaccess = file_get_contents( $htaccess_path ); // phpcs:ignore
			} else {
				$htaccess = '.htaccess file not found';
			}
			?>
			<textarea readonly style="width: 100%; font-family: monospace;"><?php echo esc_html( $htaccess ); ?></textarea>
			<?php
		}

		/**
		 * Get PHP Info
		 *
		 * @return void
		 */
		public static function get_phpinfo() {
			ob_start();
			phpinfo(); // phpcs:ignore
			$phpinfo = ob_get_clean();

			// Create an HTML file to store phpinfo output.
			$upload_dir = wp_upload_dir();
			$phpinfo_file = $upload_dir['basedir'] . '/wpdt-phpinfo.html';

			file_put_contents( $phpinfo_file, $phpinfo ); // phpcs:ignore

			// Get the URL to the file.
			$phpinfo_url = $upload_dir['baseurl'] . '/wpdt-phpinfo.html';

			echo '<iframe src="' . esc_url( $phpinfo_url ) . '" style="width: 100%; border: none;"></iframe>';
		}

		/**
		 * Get Database Info
		 *
		 * @return void
		 */
		public static function get_database_info() {
			global $wpdb;

			// Get all tables in the database.
			$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
			?>
			<div class="wpdt-database-info-container">
				<div class="wpdt-database-basic-info-container">
					<table class="wpdt-database-basic-info-tbl widefat striped">
						<tr>
							<td>Database Name</td>
							<td><?php echo esc_html( $wpdb->dbname ); ?></td>
						</tr>
						<tr>
							<td>Database Prefix</td>
							<td><?php echo esc_html( $wpdb->prefix ); ?></td>
						</tr>
						<tr>
							<td>Database Host</td>
							<td><?php echo esc_html( $wpdb->dbhost ); ?></td>
						</tr>
						<tr>
							<td>Database Charset</td>
							<td><?php echo esc_html( $wpdb->charset ); ?></td>
						</tr>
						<tr>
							<td>Database Collate</td>
							<td><?php echo esc_html( $wpdb->collate ); ?></td>
						</tr>
					</table>
				</div>
				<div class="wpdt-database-table-info-container">
					<h3>Database Overview</h3>
					<div class="wpdt-database-table-overview">
						<?php
						foreach ( $tables as $key => $table ) {
							$table_name = $table[0];
							$columns = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table_name ) );
							$total_rows = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table_name ) );

							// Get additional table info (Auto Increment & Size).
							$table_status = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table_name ) );
							$next_increment = $table_status->Auto_increment ?? 'N/A'; //phpcs:ignore
							$table_size = $table_status->Data_length + $table_status->Index_length; //phpcs:ignore
							$table_size = size_format( $table_size, 2 );
							?>
							<details style='margin-bottom: 10px;'>
								<summary>
									<strong><?php echo esc_attr( $table_name ); ?></strong>
									<span>
										(
										Rows: <?php echo esc_attr( $total_rows ); ?> |
										Increment: <?php echo esc_attr( $next_increment ); ?> |
										Size: <?php echo esc_attr( $table_size ); ?> |
										Columns: <?php echo esc_attr( count( $columns ) ); ?>
										)
									</span>
								</summary>
								<div class="wpdt-database-table-info-summary">
									<table class="widefat striped">
										<tr>
											<th>Column</th>
											<th>Type</th>
											<th>Null</th>
											<th>Key</th>
											<th>Default</th>
											<th>Extra</th>
										</tr>
										<?php
										foreach ( $columns as $column ) {
											?>
											<tr>
												<td><?php echo esc_html( $column->Field );  //phpcs:ignore?></td>
												<td><?php echo esc_html( $column->Type );  //phpcs:ignore?></td>
												<td><?php echo esc_html( $column->Null );  //phpcs:ignore?></td>
												<td><?php echo esc_html( $column->Key );  //phpcs:ignore?></td>
												<td><?php echo isset($column->Default) ? esc_html( $column->Default ) : ''; //phpcs:ignore ?></td>
												<td><?php echo esc_html( $column->Extra );  //phpcs:ignore?></td>
											</tr>
											<?php
										}
										?>
									</table>
								</div>
							</details>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Get Cookies
		 *
		 * @return void
		 */
		public static function get_cookies() {
			$cookies = $_COOKIE;
			?>
			<div class="wpdt-cookies-container">
				<table id="wpdt-cookies-table" class="wpdt-cookies-table widefat striped">
					<thead>
						<tr>
							<th>Cookie Name</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $cookies as $name => $value ) {
							?>
							<tr>
								<td><?php echo esc_html( $name ); ?></td>
								<td><?php echo esc_html( $value ); ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}

		/**
		 * Get Transients
		 *
		 * @return void
		 */
		public static function get_transients() {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT option_name, option_value
					FROM {$wpdb->options}
					WHERE option_name LIKE %s
					OR option_name LIKE %s
					",
					'_transient_%',
					'_site_transient_%'
				)
			);

			$transients = array();
			foreach ( $results as $row ) {

				if ( strpos( $row->option_name, '_transient_timeout_' ) !== false ||
					strpos( $row->option_name, '_site_transient_timeout_' ) !== false ) {
					continue;
				}

				$is_site_transient = strpos( $row->option_name, '_site_transient_' ) !== false;
				$base_name = str_replace( array( '_transient_', '_site_transient_' ), '', $row->option_name );
				$timeout_name = $is_site_transient ? '_site_transient_timeout_' . $base_name : '_transient_timeout_' . $base_name;
				$expiry = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $timeout_name ) );

				$transients[] = array(
					'name'   => esc_html( $base_name ),
					'value'  => maybe_unserialize( $row->option_value ),
					'expiry' => $expiry ? intval( $expiry ) : 0,
				);
			}
			?>
			<table id="wpdt-transients-table" class="widefat striped wpdt-table-fixed">
				<thead>
					<tr>
						<th>Name</th>
						<th>Value</th>
						<th>Expiry</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $transients as $t ) : ?>
						<tr>
							<td><?php echo esc_html( $t['name'] ); ?></td>
							<td>
								<?php
								$value = wp_json_encode( $t['value'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
								$is_long = strlen( $value ) > 300;
								?>
								<div class="wpdt-transient-value<?php echo $is_long ? '' : ' expanded'; ?>">
									<?php echo nl2br( esc_html( $value ) ); ?>
								</div>
								<?php if ( $is_long ) : ?>
									<div class="wpdt-view-toggle">View More</div>
								<?php endif; ?>
							</td>
							<td>
								<?php
								if ( $t['expiry'] ) {
									$expired = $t['expiry'] < time();
									echo '<span class="' . ( $expired ? 'wpdt-expired' : '' ) . '">' .
									esc_html( gmdate( 'Y-m-d H:i:s', $t['expiry'] ) ) . '</span>';
								} else {
									echo 'No Expiry';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		}

		/**
		 * Get Cron Jobs
		 *
		 * @return void
		 */
		public static function get_cron_jobs() {

			// Show cron status warning/info if needed.
			WPDT_Helper::maybe_show_cron_notice();

			$cron_jobs = get_option( 'cron' );
			if ( ! is_array( $cron_jobs ) ) {
				echo 'No cron jobs found.';
				return;
			}

			$schedules = wp_get_schedules();
			?>
			<table id="wp-cron-table" class="widefat striped wpdt-table-fixed" style="width:100%">
				<thead>
					<tr>
						<th>Hook</th>
						<th>Arguments</th>
						<th>Recurrence</th>
						<th>Next Run</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $cron_jobs as $timestamp => $cron_group ) {

					if ( ! is_array( $cron_group ) || empty( $cron_group ) ) {
						continue;
					}

					foreach ( $cron_group as $hook => $events ) {
						foreach ( $events as $key => $event ) {
							$args = ! empty( $event['args'] ) ? wp_json_encode( $event['args'] ) : '-';
							$recurrence = isset( $event['schedule'] ) ? ( $schedules[ $event['schedule'] ]['display'] ?? $event['schedule'] ) : 'One-time';
							$next_run = date_i18n( 'Y-m-d H:i:s', $timestamp );
							$action_example = '&lt;?php ' . esc_html( $hook ) . '();';

							echo '<tr>';
							echo '<td>' . esc_html( $hook ) . '</td>';
							echo '<td><code>' . esc_html( $args ) . '</code></td>';
							echo '<td>' . esc_html( $recurrence ) . '</td>';
							echo '<td>' . esc_html( $next_run ) . '</td>';
							echo '<td><code>' . esc_html( $action_example ) . '</code></td>';
							echo '</tr>';
						}
					}
				}
				?>
				</tbody>
			</table>
			<?php
		}
	}
endif;
