<?php
/**
 * Class: System metrics - CPU and MEM.
 *
 * Helper class to determine the proper status of the request.
 *
 * @package advanced-analytics
 *
 * @since 3.9.3
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}
if ( ! class_exists( '\ADVAN\Helpers\System_Analytics' ) ) {
	/**
	 * System Analytics Helper
	 *
	 * Provides CPU and Memory usage readings in a WordPress-safe way.
	 * Namespace: ADVAN\Helpers
	 * Text Domain: 0-day-analytics
	 *
	 * @since 3.9.3
	 */
	class System_Analytics {

		public const PAGE_SLUG     = ADVAN_INNER_SLUG . '_page_advan_system_analytics';
		public const SYS_MENU_SLUG = 'advan_system_analytics';

		/**
		 * Initialize hooks.
		 *
		 * @return void
		 *
		 * @since 3.9.3
		 */
		public static function init(): void {
			\add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_menu' ), 100 );
			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
			\add_action(
				'wp_dashboard_setup',
				function () {
					\wp_add_dashboard_widget(
						'advan_system_status',
						__( 'Server Status', '0-day-analytics' ) . ' - ' . ADVAN_NAME,
						function () {
							echo System_Analytics::get_system_summary(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					);
				}
			);
		}

		/**
		 * Add system usage metrics to the admin bar.
		 *
		 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
		 *
		 * @return void
		 *
		 * @since 3.9.3
		 */
		public static function add_admin_bar_menu( $wp_admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$metrics = array(
				'cpu'  => 'CPU',
				'mem'  => 'MEM',
				'disk' => 'HDD',
			);

			if ( ! Settings::get_option( 'advana_server_info_cpu_enable' ) ) {
				unset( $metrics['cpu'] );
			}
			if ( ! Settings::get_option( 'advana_server_info_mem_enable' ) ) {
				unset( $metrics['mem'] );
			}
			if ( ! Settings::get_option( 'advana_server_info_hdd_enable' ) ) {
				unset( $metrics['disk'] );
			}
			foreach ( $metrics as $id => $label ) {
				$wp_admin_bar->add_node(
					array(
						'id'    => "advan-usage-$id",
						'title' => sprintf(
							'<div class="advan-usage-item" id="advan-usage-item-%1$s">
								
								<div class="advan-usage-bar">
									<div class="advan-usage-fill" style="height:0%%;position: absolute;"></div>
									<span class="advan-usage-label">%2$s</span><span class="advan-usage-value">0%%</span>
								</div>
								
							</div>',
							\esc_attr( $id ),
							\esc_html( $label )
						),
						'href'  => false,
						'meta'  => array(
							'title' => $label . \esc_attr__( ' Usage', '0-day-analytics' ),
							'class' => 'advan-usage-item-wrapper',
						),
					)
				);
			}
		}

		/**
		 * Render the admin page for server status.
		 *
		 * @return void
		 *
		 * @since 3.9.3
		 */
		public static function render_admin_page() {
			$nonce = \wp_create_nonce( 'wp_rest' );
			?>
			<div class="wrap" style="max-width:800px;">
				<h1 class="wp-heading-inline"><?php echo esc_html__( 'Server Status', '0-day-analytics' ); ?></h1>
				<p style="margin-bottom:20px;">
					<?php echo esc_html__( 'Real-time overview of your server performance.', '0-day-analytics' ); ?>
				</p>

				<div id="advan-system-dashboard">
					<?php echo self::get_system_summary( true );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<button id="advan-refresh-btn" class="button button-primary" style="margin-top:20px;">
					<?php echo esc_html__( 'Refresh Data', '0-day-analytics' ); ?>
				</button>

				<p style="margin-top:10px;">
					<?php echo esc_html__( 'Auto-refreshes every 10 seconds.', '0-day-analytics' ); ?>
				</p>

				<script type="text/javascript">
				(function($){
					const endpoint = '<?php echo esc_url( rest_url( '0-day/v1/system-status/get-status' ) ); ?>';
					const nonce = '<?php echo esc_js( $nonce ); ?>';

					function updateBar(selector, percent){
						let color = '#2ecc71';
						if(percent >= 55 && percent < 80) color = '#e67e22';
						if(percent >= 80) color = '#e74c3c';
						$(selector).css({width: percent + '%', background: color});
					}

					function refreshAnalytics(){
						fetch(endpoint, { headers: {'X-WP-Nonce': nonce} })
							.then(res => res.json())
							.then(data => {
								if(data.success){
									const cpu = data.data.cpu_usage ?? 0;
									const mem = data.data.memory_usage ?? 0;
									const disk = data.data.disk_usage ?? 0;
									$('#advan-cpu-value').text(cpu.toFixed(1) + '%');
									$('#advan-mem-value').text(mem.toFixed(1) + '%');
									$('#advan-disk-value').text(disk.toFixed(1) + '%');
									updateBar('#advan-cpu-bar', cpu);
									updateBar('#advan-mem-bar', mem);
									updateBar('#advan-disk-bar', disk);
									$('#advan-last-updated').text('<?php echo esc_js( __( 'Last updated:', '0-day-analytics' ) ); ?> ' + new Date().toLocaleTimeString());
								}
							})
							.catch(err => console.error('System analytics fetch failed:', err));
					}

					$('#advan-refresh-btn').on('click', refreshAnalytics);
					setInterval(refreshAnalytics, 10000);
				})(jQuery);
				</script>
			</div>
			<?php
		}

		/**
		 * REST API endpoint to get system status.
		 *
		 * @return \WP_REST_Response
		 *
		 * @since 3.9.3
		 */
		public static function rest_get_system_status() {
			$cpu  = self::get_cpu_usage();
			$mem  = self::get_memory_usage();
			$disk = self::get_disk_usage();

			$data = array(
				'cpu_usage'    => $cpu,
				'memory_usage' => $mem,
				'disk_usage'   => $disk,
				'timestamp'    => \current_time( 'mysql' ),
			);

			return new \WP_REST_Response(
				array(
					'success' => true,
					'data'    => $data,
				),
				200
			);
		}

		/**
		 * Get the current CPU usage.
		 *
		 * @return float|null
		 *
		 * @since 3.9.3
		 */
		public static function get_cpu_usage() {
			$os = strtolower( PHP_OS );

			if ( function_exists( 'sys_getloadavg' ) ) {
				$load = sys_getloadavg();
				if ( is_array( $load ) && isset( $load[0] ) ) {
					$cpu_count = 1;
					if ( @is_readable( '/proc/cpuinfo' ) ) {
						$cpu_count = (int) shell_exec( 'nproc 2>/dev/null' ) ?: 1;
					} elseif ( stripos( $os, 'win' ) === 0 ) {
						$cpu_count = (int) getenv( 'NUMBER_OF_PROCESSORS' ) ?: 1;
					}
					return round( ( $load[0] / $cpu_count ) * 100, 2 );
				}
			}

			if ( stripos( $os, 'linux' ) === 0 && @is_readable( '/proc/stat' ) ) {
				$get_cpu_stat = function () {
					$data = @file( '/proc/stat' );
					if ( ! $data || ! isset( $data[0] ) ) {
						return null;
					}
					$parts = preg_split( '/\s+/', trim( $data[0] ) );
					return array_slice( $parts, 1, 4 );
				};

				$stat1 = $get_cpu_stat();
				if ( null === $stat1 ) {
					return null;
				}
				usleep( 500000 );
				$stat2 = $get_cpu_stat();
				if ( null === $stat2 ) {
					return null;
				}

				$diff = array();
				for ( $i = 0; $i < 4; $i++ ) {
					$diff[ $i ] = $stat2[ $i ] - $stat1[ $i ];
				}
				$total = array_sum( $diff );
				if ( 0 == $total ) {
					return null;
				}

				$cpu_percent = ( $total - $diff[3] ) * 100 / $total;
				return round( $cpu_percent, 2 );
			}

			if ( stripos( $os, 'win' ) === 0 && function_exists( 'shell_exec' ) ) {
				$output = @shell_exec( 'wmic cpu get loadpercentage 2>NUL' );
				if ( $output ) {
					preg_match_all( '/\d+/', $output, $matches );
					if ( ! empty( $matches[0] ) ) {
						return round( array_sum( $matches[0] ) / count( $matches[0] ), 2 );
					}
				}
			}

			if ( function_exists( 'shell_exec' ) && stripos( $os, 'linux' ) === 0 ) {
				$output = @shell_exec( "top -bn1 | grep 'Cpu(s)' 2>/dev/null" );
				if ( $output && preg_match( '/(\d+\.\d+)\s*id/', $output, $matches ) ) {
					$idle = (float) $matches[1];
					return round( 100 - $idle, 2 );
				}
			}

			return null;
		}

		/**
		 * Get the current memory usage.
		 *
		 * @return float|null
		 *
		 * @since 3.9.3
		 */
		public static function get_memory_usage() {
			$os = strtolower( PHP_OS );

			if ( stripos( $os, 'linux' ) === 0 && @is_readable( '/proc/meminfo' ) ) {
				$data    = @file( '/proc/meminfo' );
				$meminfo = array();

				if ( false === $data ) {
					return null;
				}

				foreach ( $data as $line ) {
					list($key, $val) = explode( ':', $line );
					$meminfo[ $key ] = trim( $val );
				}
				$total = (int) filter_var( $meminfo['MemTotal'] ?? 0, FILTER_SANITIZE_NUMBER_INT );
				$free  = (int) filter_var( $meminfo['MemAvailable'] ?? 0, FILTER_SANITIZE_NUMBER_INT );
				if ( $total > 0 ) {
					$used_percent = 100 - ( ( $free / $total ) * 100 );
					return round( $used_percent, 2 );
				}
			}

			if ( stripos( $os, 'win' ) === 0 && function_exists( 'shell_exec' ) ) {
				$output = @shell_exec( 'wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value' );
				if ( $output && preg_match_all( '/(\d+)/', $output, $matches ) ) {
					if ( count( $matches[0] ) >= 2 ) {
						$free  = (int) $matches[0][0];
						$total = (int) $matches[0][1];
						if ( $total > 0 ) {
							$used_percent = 100 - ( ( $free / $total ) * 100 );
							return round( $used_percent, 2 );
						}
					}
				}
			}

			return null;
		}

		/**
		 * Check if a path is allowed by open_basedir restrictions.
		 *
		 * @param string $path The path to check.
		 *
		 * @return bool
		 *
		 * @since 4.2.0
		 */
		public static function is_allowed_by_open_base_dir( string $path ): bool {
			$ob = ini_get( 'open_basedir' );
			if ( ! $ob ) {
				return true; // no restrictions.
			}

			$allowed = explode( PATH_SEPARATOR, $ob );

			$real = @realpath( $path );
			if ( false === $real ) {
				return false;
			}

			foreach ( $allowed as $dir ) {
				$dir = rtrim( $dir, '/' );
				if ( strpos( $real, $dir ) === 0 ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Returns the disk usage
		 *
		 * @return int|null
		 *
		 * @since 3.9.3
		 */
		public static function get_disk_usage() {
			$total = @disk_total_space( '/' );
			if ( self::is_allowed_by_open_base_dir( '/' ) ) {
				$free = @disk_free_space( '/' );
			}
			if ( $total && $free ) {
				$used = 100 - ( ( $free / $total ) * 100 );
				return round( $used, 2 );
			}

			return null;
		}

		/**
		 * Get system summary HTML.
		 *
		 * @param bool $include_ids Whether to include element IDs for JS updates.
		 *
		 * @return string
		 *
		 * @since 3.9.3
		 */
		public static function get_system_summary( $include_ids = false ) {
			if ( ! Settings::get_option( 'advana_server_info_cpu_enable' ) ) {
				$cpu = 0;
			} else {
				$cpu = self::get_cpu_usage();
			}
			if ( ! Settings::get_option( 'advana_server_info_mem_enable' ) ) {
				$mem = 0;
			} else {
				$mem = self::get_memory_usage();
			}
			if ( ! Settings::get_option( 'advana_server_info_hdd_enable' ) ) {
				$disk = 0;
			} else {
				$disk = self::get_disk_usage();
			}

			// Additional system info
			$uptime = self::get_server_uptime();
			$load_avg = self::get_load_average();
			$php_version = PHP_VERSION;
			$wp_version = get_bloginfo( 'version' );
			$active_plugins = count( get_option( 'active_plugins', array() ) );
			$current_theme = wp_get_theme()->get( 'Name' );
			$mysql_version = self::get_mysql_version();
			$swap_usage = self::get_swap_usage();
			$php_memory_limits = self::get_php_memory_limits();
			$database_size = self::get_database_size();
			$server_timezone = self::get_server_timezone();
			$wordpress_memory_usage = self::get_wordpress_memory_usage();
			$php_extensions_count = self::get_php_extensions_count();
			$server_architecture = self::get_server_architecture();
			$total_users = self::get_total_users();

			$bar_color = function ( $percent ) {
				if ( $percent < 55 ) {
					return '#2ecc71';
				}
				if ( $percent < 80 ) {
					return '#e67e22';
				}
				return '#e74c3c';
			};

			ob_start();
			?>
			<script>
				if( 'undefined' != typeof localStorage ){
					var skin = localStorage.getItem('aadvana-backend-skin');
					if( skin == 'dark' ){

						var element = document.getElementsByTagName("html")[0];
						element.classList.add("aadvana-darkskin");
					}
				}
			</script>

			<div class="advan-system-summary" style="font-size:14px;max-width:600px;">
				<style>
					.advan-progress {
						position:relative;
						height:12px;
						background: #e6e1e1;
						border-radius:5px;
						overflow:hidden;
						margin-top:6px;
						margin-bottom:18px;
					}
					.advan-progress-bar {height:100%;transition:width 0.6s ease, background 0.3s ease;}
					.advan-stat-label {font-weight:600;}
					.advan-stat-value {font-size:16px;margin-top:3px;}
					.advan-info-section {margin-top:20px; padding:10px; border-radius:5px;}
					.advan-info-item {margin-bottom:5px;}
				</style>

				<div class="advan-stat">
					<span class="advan-stat-label"><?php echo esc_html__( 'CPU Usage', '0-day-analytics' ); ?>:</span>
					<div id="advan-cpu-value" class="advan-stat-value"><?php echo esc_html( $cpu ?? 0 ); ?>%</div>
					<div class="advan-progress">
						<div id="advan-cpu-bar" class="advan-progress-bar" style="width:<?php echo esc_attr( $cpu ?? 0 ); ?>%;background:<?php echo esc_attr( $bar_color( $cpu ?? 0 ) ); ?>;"></div>
					</div>
				</div>

				<div class="advan-stat">
					<span class="advan-stat-label"><?php echo esc_html__( 'Memory Usage', '0-day-analytics' ); ?>:</span>
					<div id="advan-mem-value" class="advan-stat-value"><?php echo esc_html( $mem ?? 0 ); ?>%</div>
					<div class="advan-progress">
						<div id="advan-mem-bar" class="advan-progress-bar" style="width:<?php echo esc_attr( $mem ?? 0 ); ?>%;background:<?php echo esc_attr( $bar_color( $mem ?? 0 ) ); ?>;"></div>
					</div>
				</div>

				<div class="advan-stat">
					<span class="advan-stat-label"><?php echo esc_html__( 'Disk Usage', '0-day-analytics' ); ?>:</span>
					<div id="advan-disk-value" class="advan-stat-value"><?php echo esc_html( $disk ?? 0 ); ?>%</div>
					<div class="advan-progress">
						<div id="advan-disk-bar" class="advan-progress-bar" style="width:<?php echo esc_attr( $disk ?? 0 ); ?>%;background:<?php echo esc_attr( $bar_color( $disk ?? 0 ) ); ?>;"></div>
					</div>
				</div>

				<div class="advan-info-section">
					<h4><?php echo esc_html__( 'System Information', '0-day-analytics' ); ?></h4>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Server Uptime', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $uptime ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Load Average', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( implode( ', ', $load_avg ) ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'PHP Version', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $php_version ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'WordPress Version', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $wp_version ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Active Plugins', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $active_plugins ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Current Theme', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $current_theme ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'MySQL Version', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $mysql_version ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Swap Usage', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $swap_usage ?? 'N/A' ); ?>%
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'PHP Memory Limits', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $php_memory_limits ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Database Size', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $database_size ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Server Timezone', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $server_timezone ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'WordPress Memory Usage', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $wordpress_memory_usage ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'PHP Extensions Count', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $php_extensions_count ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Server Architecture', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $server_architecture ); ?>
					</div>
					<div class="advan-info-item">
						<strong><?php echo esc_html__( 'Total Users', '0-day-analytics' ); ?>:</strong> <?php echo esc_html( $total_users ); ?>
					</div>
				</div>

				<div id="advan-last-updated" style="font-size:12px;">
					<?php echo esc_html__( 'Last updated:', '0-day-analytics' ) . ' ' . esc_html( date_i18n( 'H:i:s' ) ); ?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Inline CSS for admin bar styles.
		 *
		 * @return string
		 *
		 * @since 3.9.3
		 */
		public static function inline_css() {
			return <<<CSS
				
					/* #wp-admin-bar-advan-usage-cpu,
					#wp-admin-bar-advan-usage-mem,
					#wp-admin-bar-advan-usage-disk {
						padding: 0 6px !important;
					} */
					.advan-usage-item-wrapper .ab-item {
						padding: 0 1px !important;
					}
					.advan-usage-item {
						display: flex;
						flex-direction: column;
						align-items: center;
						justify-content: center;
						width: 40px !important;
						height: 28px !important;
						position: relative !important;
						text-align: center;
						color: #fff;
					}
					.advan-usage-label {
						color: white !important;
						/* bottom: 50%; */
						text-align: center;
						display: block;
						position: relative !important;
						font-weight: 600 !important;

						font-size: 10px !important;
						line-height: 1 !important;
						margin-bottom: 2px;
						color: #ddd;
					}
					.advan-usage-bar {
						width: 10px;
						height: 14px;
						/* background: #444; */
						border-radius: 2px;
						overflow: hidden;
						position: relative;
					}
					.advan-usage-fill {
						position: absolute;
						bottom: 0;
						left: 0;
						right: 0;
						height: 0%;
						background: #2ecc71;
						transition: height 0.5s ease, background 0.3s ease;
					}
					.advan-usage-value {
						text-align: center;
						display: block;
						position: relative !important;
						font-weight: 600 !important;
						font-size: 10px !important;
						line-height: 1 !important;
						text-shadow: 1px 1px 3px #000 !important;
						margin-bottom: 2px;
						color: #fff;
						font-size: 9px;
						margin-top: 2px;
						color: #ccc;
					}
				
			CSS;
		}

		/**
		 * Inline JavaScript for updating system usage metrics.
		 *
		 * @return string
		 *
		 * @since 3.9.3
		 */
		public static function inline_js() {
			$ajax  = \esc_url( \admin_url( 'admin-ajax.php' ) );
			$nonce = \wp_create_nonce( 'advan-system-usage' );
			$secs  = Settings::get_option( 'advana_server_info_admin_bar_refresh_interval' ) * 1000;
			return <<<JS
			(function($){
				function colorForPercent(val){
					if(val >= 80) return '#e74d3c86';
					if(val >= 55) return '#e67d227a';
					return '#05582890';
				}
				function updateMetric(id, val){
					const color = colorForPercent(val);
					const item = $('#advan-usage-item-' + id);
					item.find('.advan-usage-fill').css({
						height: val + '%',
						background: color
					});
					item.find('.advan-usage-value').text(val.toFixed(0) + '%');
				}
				function refreshMetrics(){
					$.getJSON('$ajax', {
						action: 'advan_get_system_usage',
						_ajax_nonce: '$nonce'
					}).done(function(response){
						if ( response.success ) {
							updateMetric('cpu', response.data.cpu ?? 0);
							updateMetric('mem', response.data.mem ?? 0);
							updateMetric('disk', response.data.disk ?? 0);
						}
					});
				}
				$(document).ready(function(){
					refreshMetrics();
					setInterval(refreshMetrics, $secs);
				});
			})(jQuery);
			JS;
		}

		/**
		 * Enqueue styles and scripts for admin bar.
		 *
		 * @return void
		 *
		 * @since 3.9.3
		 */
		public static function enqueue_styles() {
			if ( ! \is_admin_bar_showing() || ! \current_user_can( 'manage_options' ) ) {
				return;
			}
			\wp_add_inline_style( 'wp-admin', self::inline_css() );
			\wp_add_inline_script( 'jquery-core', self::inline_js(), 'after' );
		}

		/**
		 * Get server uptime.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_server_uptime() {
			if ( @is_readable( '/proc/uptime' ) ) {
				$uptime = @file_get_contents( '/proc/uptime' );
				if ( $uptime ) {
					$uptime = explode( ' ', $uptime )[0];
					return self::format_uptime( (int) $uptime );
				}
			}
			return __( 'N/A', '0-day-analytics' );
		}

		/**
		 * Format uptime seconds into readable string.
		 *
		 * @param int $seconds Uptime in seconds.
		 * @return string
		 *
		 * @since 4.4.1
		 */
		private static function format_uptime( $seconds ) {
			$days = floor( $seconds / 86400 );
			$hours = floor( ( $seconds % 86400 ) / 3600 );
			$minutes = floor( ( $seconds % 3600 ) / 60 );

			$parts = array();
			if ( $days > 0 ) {
				$parts[] = sprintf( _n( '%d day', '%d days', $days, '0-day-analytics' ), $days );
			}
			if ( $hours > 0 ) {
				$parts[] = sprintf( _n( '%d hour', '%d hours', $hours, '0-day-analytics' ), $hours );
			}
			if ( $minutes > 0 ) {
				$parts[] = sprintf( _n( '%d minute', '%d minutes', $minutes, '0-day-analytics' ), $minutes );
			}

			return implode( ', ', $parts );
		}

		/**
		 * Get load average.
		 *
		 * @return array
		 *
		 * @since 4.4.1
		 */
		public static function get_load_average() {
			if ( function_exists( 'sys_getloadavg' ) ) {
				$load = sys_getloadavg();
				if ( is_array( $load ) ) {
					return array_map( function( $val ) {
						return round( $val, 2 );
					}, $load );
				}
			}
			return array( 0, 0, 0 );
		}

		/**
		 * Get MySQL version.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_mysql_version() {
			global $wpdb;
			return $wpdb->get_var( "SELECT VERSION()" ) ?: __( 'N/A', '0-day-analytics' );
		}

		/**
		 * Get swap usage percentage.
		 *
		 * @return float|null
		 *
		 * @since 4.4.1
		 */
		public static function get_swap_usage() {
			if ( stripos( strtolower( PHP_OS ), 'linux' ) === 0 && @is_readable( '/proc/meminfo' ) ) {
				$data = @file( '/proc/meminfo' );
				if ( $data ) {
					$meminfo = array();
					foreach ( $data as $line ) {
						list( $key, $val ) = explode( ':', $line );
						$meminfo[ $key ] = trim( $val );
					}
					$total = (int) filter_var( $meminfo['SwapTotal'] ?? 0, FILTER_SANITIZE_NUMBER_INT );
					$free  = (int) filter_var( $meminfo['SwapFree'] ?? 0, FILTER_SANITIZE_NUMBER_INT );
					if ( $total > 0 ) {
						$used_percent = 100 - ( ( $free / $total ) * 100 );
						return round( $used_percent, 2 );
					}
				}
			}
			return null;
		}

		/**
		 * Get PHP memory limits.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_php_memory_limits() {
			$memory_limit = ini_get( 'memory_limit' );
			$post_max_size = ini_get( 'post_max_size' );
			$upload_max_filesize = ini_get( 'upload_max_filesize' );
			return sprintf(
				__( 'Memory: %s, Post: %s, Upload: %s', '0-day-analytics' ),
				$memory_limit,
				$post_max_size,
				$upload_max_filesize
			);
		}

		/**
		 * Get database size in MB.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_database_size() {
			global $wpdb;
			$size = $wpdb->get_var(
				"SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
				FROM information_schema.tables
				WHERE table_schema = DATABASE()"
			);
			return $size ? $size . ' MB' : __( 'N/A', '0-day-analytics' );
		}

		/**
		 * Get server timezone.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_server_timezone() {
			return date_default_timezone_get();
		}

		/**
		 * Get WordPress memory usage.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_wordpress_memory_usage() {
			$wp_memory_limit = WP_MEMORY_LIMIT;
			$wp_max_memory_limit = WP_MAX_MEMORY_LIMIT;
			return sprintf(
				__( 'WP Limit: %s, Max: %s', '0-day-analytics' ),
				$wp_memory_limit,
				$wp_max_memory_limit
			);
		}

		/**
		 * Get PHP extensions count.
		 *
		 * @return int
		 *
		 * @since 4.4.1
		 */
		public static function get_php_extensions_count() {
			return count( get_loaded_extensions() );
		}

		/**
		 * Get server architecture.
		 *
		 * @return string
		 *
		 * @since 4.4.1
		 */
		public static function get_server_architecture() {
			return php_uname( 'm' );
		}

		/**
		 * Get total users.
		 *
		 * @return int
		 *
		 * @since 4.4.1
		 */
		public static function get_total_users() {
			$user_count = count_users();
			return $user_count['total_users'];
		}

		/**
		 * AJAX handler to get system usage.
		 *
		 * @return void
		 *
		 * @since 3.9.3
		 */
		public static function ajax_get_system_usage() {
			// Verify nonce to mitigate CSRF on privileged AJAX action.
			if ( ! \check_ajax_referer( 'advan-system-usage', '_ajax_nonce', false ) ) {
				\wp_send_json_error( array( 'message' => __( 'Invalid request.', '0-day-analytics' ) ), 403 );
			}

			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_send_json_error();
			}

			$data = array(
				'cpu'  => self::get_cpu_usage(),
				'mem'  => self::get_memory_usage(),
				'disk' => self::get_disk_usage(),
			);
			\wp_send_json_success( $data );
		}
	}
}
