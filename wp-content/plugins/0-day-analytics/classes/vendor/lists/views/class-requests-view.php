<?php
/**
 * Class: Responsible for Requests views and operations.
 *
 * Edit and add requests, attach screens.
 *
 * @package advanced-analytics
 *
 * @since 2.7.0
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

use ADVAN\Helpers\Settings;
use ADVAN\Lists\Requests_List;
use ADVAN\Entities_Global\Common_Table;
use ADVAN\Controllers\Api\Endpoints;
use ADVAN\Entities\Requests_Log_Entity;
use ADVAN\Lists\Views\Abstract_View;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Lists\Views\Requests_View' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 2.7.0
	 */
	class Requests_View extends Abstract_View {

		/**
		 * Displays the requests page.
		 *
		 * @return void
		 *
		 * @since 2.7.0
		 */
		public static function analytics_requests_page() {
			static::display_page( \__( 'You do not have permission to manage requests list.', '0-day-analytics' ) );
		}

		/**
		 * Render the specific page content.
		 *
		 * @return void
		 *
		 * @since latest
		 */
		protected static function render_page_content(): void {

			$action = ! empty( $_REQUEST['action'] )
			? sanitize_key( $_REQUEST['action'] )
			: '';

			$requests = new Requests_List( '' );
			$requests->prepare_items();

			// Get analytics data.
			$analytics_data = self::get_analytics_data();
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php \esc_html_e( 'Requests', '0-day-analytics' ); ?></h1>

				<hr class="wp-header-end">
				<?php
				if ( ! Settings::get_option( 'advana_requests_enable' ) ) {
					?>
				<div id="advana-status-error" class="notice notice-error">
					<?php
					printf(
						'<p>%1$s</p>',
						sprintf(
							/* translators: %s: Link to requests settings. */
							\esc_html__( 'The requests logging is disabled. To enable it go to : %s', '0-day-analytics' ),
							'<a href="' . \esc_url( \add_query_arg( array( 'page' => Settings::SETTINGS_MENU_SLUG ), \admin_url( 'admin.php' ) ) ) . '#aadvana-options-tab-request-list">' . esc_html__( 'settings', '0-day-analytics' ) . '</a>'
						)
					);
					?>
				</div>
					<?php
				} else {
					if ( Settings::get_option( 'advana_http_requests_disable' ) ) {
						?>
				<div id="advana-status-error" class="notice notice-error">
						<?php
						printf(
							'<p>%1$s</p>',
							sprintf(
							/* translators: %s: Link to requests settings. */
								\esc_html__( 'HTTP Requests monitoring is disabled', '0-day-analytics' ),
							)
						);
						?>
				</div>
						<?php
					}
					if ( Settings::get_option( 'advana_rest_requests_disable' ) ) {
						?>
				<div id="advana-status-error" class="notice notice-error">
						<?php
						printf(
							'<p>%1$s</p>',
							sprintf(
							/* translators: %s: Link to requests settings. */
								\esc_html__( 'REST API Requests monitoring is disabled', '0-day-analytics' ),
							)
						);
						?>
				</div>
						<?php
					}
				}

				// Render analytics dashboard.
				self::render_analytics_dashboard( $analytics_data );
				?>

				<form id="requests-filter" method="get">
				<?php

				$page  = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
				$paged = ( isset( $_GET['paged'] ) ) ? filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1;

				printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
				printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

				echo '<div style="clear:both; float:right">';
				$requests->search_box(
					__( 'Search', '0-day-analytics' ),
					strtolower( $requests::get_table_name() ) . '-find'
				);
				echo '</div>';
				$requests->display();

				?>
				</form>
			</div>
			<style>
				/* modal */
				.media-modal,
				.media-modal-backdrop {
					display: none;
				}

				.media-modal.open,
				.media-modal-backdrop.open {
					display: block;
				}

				#aadvana-modal.aadvana-modal .media-frame-title,
				#aadvana-modal.aadvana-modal .media-frame-content {
					left: 0;
				}

				.media-frame-router {
					left: 10px;
				}
				#aadvana-modal.aadvana-modal
				.media-frame-content {
					top: 48px;
					bottom: 0;
					overflow: auto;
				}

				.button-link.media-modal-close {
					cursor: pointer;
					text-decoration: none;
				}

				.aadvana-modal-buttons{
					position: absolute;
					top: 0;
					right: 0;
				}
				.aadvana-modal-buttons .media-modal-close{
					position: relative;
					width: auto;
					padding: 0 .5rem;
				}

				.media-modal-close.prev .media-modal-icon::before {
					content: "\f342";
				}

				.media-modal-close.next .media-modal-icon::before {
					content: "\f346";
				}

				.modal-content-wrap {
					padding: 16px;
				}

				/* tab and panel */
				.aadvana-modal .nav-tab-active{
					border-bottom: solid 1px white;
					background-color: white;
				}
				.aadvana-panel-active{
					display:block;
					margin: 1rem 0;
				}

				.wrapper {
					text-align: center;
				}
				.wrapper .box{
					text-align: left;
					background-color: #f4f5f6;
					padding: .5rem;
					border-radius: .5rem;
					margin-bottom: 1rem;
					display: inline-block;
					vertical-align: top;
					width: 48%;
					box-sizing: border-box;
				}
				html.aadvana-darkskin .wrapper .box {
					background-color: #1d456b !important;
					border: 1px solid #ccc;
				}
				html.aadvana-darkskin .media-frame-content {
					background-color: #1d456b !important;
				}
				@media screen and (max-width: 782px) {

					.wrapper .box{
						display: block;
						width: auto;
					}

				}

			</style>

			<div id="aadvana-modal" class="media-modal aadvana-modal">
				<div class="aadvana-modal-buttons">
					<button class="button-link media-modal-close"><span class="media-modal-icon"></span></button>
				</div>
				<div class="media-modal-content">
					<div class="media-frame">
						<div class="media-frame-title">
							<h1><?php \esc_html_e( 'Request details:', '0-day-analytics' ); ?></h1>
						</div>
						<div class="media-frame-content">
							<div class="modal-content-wrap">
								<p>
									<b><?php \esc_html_e( 'Request: ', '0-day-analytics' ); ?> </b><span class="http-request-type"></span> | <span class="http-request-status"></span> | <span class="http-request-runtime"></span> | <?php \esc_html_e( 'Domain: ', '0-day-analytics' ); ?><span class="http-request-domain"></span>
								</p>
								<p>
									<b><?php \esc_html_e( 'Page:', '0-day-analytics' ); ?>:</b> 
									<span class="http-request-page"></span><br>
									<b><?php \esc_html_e( 'Request URL:', '0-day-analytics' ); ?>:</b> <span class="http-request-url"></span>
								</p>
								<div class="aadvana-panel-wrapper">
									<div class="aadvana-request-response aadvana-panel-active wrapper">
										<div class="box">
											<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
												<div>
													<h3><?php \esc_html_e( 'Request:', '0-day-analytics' ); ?></h3>
												</div>
												<div class=""><span title="<?php echo esc_attr__( 'Copy to clipboard', '0-day-analytics' ); ?>" class="dashicons dashicons-clipboard" style="cursor:pointer;" aria-hidden="true"></span> <span title="<?php echo esc_attr__( 'Share', '0-day-analytics' ); ?>" class="dashicons dashicons-share" style="cursor:pointer;" aria-hidden="true"></span></div>
											</div>
											<div class="http-request-args aadvana-pre-300"></div>
										</div>
										<div class="box">
											<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
												<div>
													<h3><?php \esc_html_e( 'Response:', '0-day-analytics' ); ?></h3>
												</div>
												<div class=""><span title="<?php echo esc_attr__( 'Copy to clipboard', '0-day-analytics' ); ?>" class="dashicons dashicons-clipboard" style="cursor:pointer;" aria-hidden="true"></span> <span title="<?php echo esc_attr__( 'Share', '0-day-analytics' ); ?>" class="dashicons dashicons-share" style="cursor:pointer;" aria-hidden="true"></span></div>
											</div>
											<div class="http-response aadvana-pre-300"></div>
										</div>						
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="media-modal-backdrop"></div>

				<script>

					jQuery(document).on('click', '.aadvan-request-show-details', function( e ) {
						e.preventDefault();
						let id = jQuery( this ).data( 'details-id' );
						jQuery('.http-request-args').html( jQuery('#advana-request-details-' + id ).html() );
						jQuery('.http-response').html( jQuery('#advana-response-details-' + id ).html() );

						jQuery('.http-request-status').html( jQuery('#advana-request-request_status-' + id ).clone() );
						jQuery('.http-request-runtime').html( jQuery('#advana-request-runtime-' + id ).clone() );
						jQuery('.http-request-type').html( jQuery('#advana-request-type-' + id ).clone() );
						jQuery('.http-request-domain').html( jQuery('#advana-request-domain-' + id ).clone() );
						jQuery('.http-request-page').html( jQuery('#advana-request-page_url-' + id ).clone().html(jQuery('#advana-request-page_url-' + id ).attr('title') ) );
						jQuery('.http-request-url').html( jQuery('#advana-request-url-' + id ).clone().html(jQuery('#advana-request-url-' + id ).attr('title') ) );

						jQuery('.media-modal').addClass('open');
						jQuery('.media-modal-backdrop').addClass('open');
					});

					jQuery(document).on('click', '.media-modal-close', function () {
						jQuery('.media-modal .http-request-args').html('');
						jQuery('.media-modal .http-response').html('');
						jQuery('.media-modal').removeClass('open');
						jQuery('.media-modal-backdrop').removeClass('open');
					});

					jQuery( document ).on( 'click', '.dashicons.dashicons-clipboard', function( e ) {

						if ( jQuery(this).parent().parent().next('.aadvana-pre-300').children('pre').length ) {
							let selectedText = jQuery(this).parent().parent().next('.aadvana-pre-300').children('pre').html();

							selectedText = selectedText.replace(/<br\s*\/?>/gim, "\n");
							selectedText = jQuery.parseHTML(selectedText); //parseHTML return HTMLCollection
							selectedText = jQuery(selectedText).text();

							navigator.clipboard.writeText(selectedText);
						}

					});
				
				jQuery( document ).ready( function() {

					if ( navigator.share ) {

						jQuery( document ).on( 'click', '.dashicons.dashicons-share', function( e ) {

							if ( jQuery(this).parent().parent().next('.aadvana-pre-300').children('pre').length ) {
								let selectedText = jQuery(this).parent().parent().next('.aadvana-pre-300').children('pre').html();

								selectedText = selectedText.replace(/<br\s*\/?>/gim, "\n");
								selectedText = jQuery.parseHTML(selectedText); //parseHTML return HTMLCollection
								selectedText = jQuery(selectedText).text();

								const shareData = {
									text: selectedText + '\n\n' + <?php echo wp_json_encode( \get_site_url() ); ?>,
								};

								try {
									navigator.share(shareData);
								} catch (err) {
									jQuery(this).text( `Error: ${err}` );
								}

							}
						});
						
					} else {
						jQuery( '.dashicons.dashicons-share' ).remove();
					}
				});
				</script>
			<?php
		}

		/**
		 * Options Help
		 *
		 * Return help text for options screen
		 *
		 * @return string  Help Text
		 *
		 * @since 2.7.0
		 */
		public static function add_help_content_table() {

			$help_text  = '<p>' . \__( 'This screen allows you to see all the requests where your WordPress site is currently running.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . \__( 'You can specify how many rows to be shown, or filter and search for given value(s).', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . \__( 'You can delete rows - keep in mind that this operation is destructive and can not be undone - make a backup first.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . \__( 'Bulk operations are supported.', '0-day-analytics' ) . '</p>';

			return $help_text;
		}

		/**
		 * Options Help
		 *
		 * Return help text for options screen
		 *
		 * @return string  Help Text
		 *
		 * @since 2.7.0
		 */
		public static function add_config_content_table() {

			Common_Table::init( Requests_Log_Entity::get_table_name() );

			$table_info = Common_Table::get_table_status();
			$help_text  = '';
			if ( ! empty( $table_info ) && isset( $table_info[0] ) ) {

				\ob_start();

				if ( isset( $table_info[0]['Name'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Name: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Name'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Engine'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Engine: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Engine'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Version'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Version: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Version'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Create_time'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Create time: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Create_time'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Collation'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Collation: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Collation'] ); ?></span></div>
					<?php
				}
				?>
				<input type="button" name="truncate_action" id="truncate_table" class="button action" data-table-name="<?php echo \esc_attr( $table_info[0]['Name'] ); ?>" value="<?php \esc_html_e( 'Truncate Table', '0-day-analytics' ); ?>">

					<script>
						let action_truncate = document.getElementById("truncate_table");

						action_truncate.onclick = tableTruncate;

						async function tableTruncate(e) {

							if ( confirm( '<?php echo \esc_js( \__( 'You sure you want to truncate this table? That operation is destructive', '0-day-analytics' ) ); ?>' ) ) {
								let tableName = e.target.getAttribute('data-table-name');

								let attResp;

								try {
									attResp = await wp.apiFetch({
										path: '/<?php echo esc_js( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/truncate_table/' + tableName,
										method: 'DELETE',
										cache: 'no-cache'
									});

									if (attResp.success) {
										
										location.reload();
									} else if (attResp.message) {
										jQuery('#wp-admin-bar-aadvan-menu .ab-item').html('<b><i>' + attResp.message + '</i></b>');
									}

								} catch (error) {
									throw error;
								}
							}
						}

					</script>
					<?php

					if ( ! \in_array( $table_info[0]['Name'], Common_Table::get_wp_core_tables() ) ) {
						?>
					<input type="button" name="drop_action" id="drop_table" class="button action" data-table-name="<?php echo \esc_attr( $table_info[0]['Name'] ); ?>" value="<?php \esc_html_e( 'Drop Table', '0-day-analytics' ); ?>">

					<script>
						let action_drop = document.getElementById("drop_table");

						action_drop.onclick = tableDrop;

						async function tableDrop(e) {

							if ( confirm( '<?php echo \esc_js( \__( 'You sure you want to delete this table? That operation is destructive', '0-day-analytics' ) ); ?>' ) ) {
								let tableName = e.target.getAttribute('data-table-name');

								let attResp;

								try {
									attResp = await wp.apiFetch({
										path: '/<?php echo esc_js( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/drop_table/' + tableName,
										method: 'DELETE',
										cache: 'no-cache'
									});

									if (attResp.success) {
										
										location.reload();
									} else if (attResp.message) {
										jQuery('#wp-admin-bar-aadvan-menu .ab-item').html('<b><i>' + attResp.message + '</i></b>');
									}

								} catch (error) {
									throw error;
								}
							}
						}

					</script>
						<?php
					}

					$help_text = \ob_get_clean();
			}

			return $help_text;
		}

		/**
		 * Removes unnecessary arguments if present and reloads.
		 *
		 * @return void
		 *
		 * @since 2.7.0
		 */
		public static function page_load() {
			if ( ! empty( $_GET['_wp_http_referer'] ) ) {
				\wp_safe_redirect(
					\remove_query_arg( array( '_wp_http_referer', 'bulk_action' ), \wp_unslash( $_SERVER['REQUEST_URI'] ) )
				);
				exit;
			}
		}

		/**
		 * Responsible for filtering table by plugin.
		 *
		 * @return void
		 *
		 * @since 3.7.1
		 */
		public static function plugin_filter_action() {

			if ( isset( $_REQUEST['plugin_top'] ) || isset( $_REQUEST['plugin_filter_bottom'] ) ) {

				if ( \check_admin_referer( Requests_List::PLUGIN_FILTER_ACTION, Requests_List::PLUGIN_FILTER_ACTION . 'nonce' ) ) {
					$id = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin_top'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					\wp_safe_redirect(
						\remove_query_arg(
							array( 'deleted' ),
							\add_query_arg(
								array(
									'page'   => Requests_List::REQUESTS_MENU_SLUG,
									Requests_List::SEARCH_INPUT => Requests_List::escaped_search_input(),
									'plugin' => rawurlencode( $id ),
								),
								\admin_url( 'admin.php' )
							)
						)
					);
					exit;
				}
			}
		}

		/**
		 * Get analytics data for the requests dashboard.
		 *
		 * @return array
		 *
		 * @since 4.8.0
		 */
		public static function get_analytics_data() {
			global $wpdb;

			$table_name = Requests_Log_Entity::get_table_name();

			// Get total requests count.
			$total_requests = Requests_Log_Entity::get_var( "SELECT COUNT(*) FROM {$table_name}" );

			// Get requests by status.
			$status_counts = Requests_Log_Entity::get_results(
				"SELECT request_status as status, COUNT(*) as count FROM {$table_name} GROUP BY request_status",
				ARRAY_A
			);

			// Get requests by type.
			$type_counts = Requests_Log_Entity::get_results(
				"SELECT type as request_type, COUNT(*) as count FROM {$table_name} GROUP BY type",
				ARRAY_A
			);

			// Get average runtime.
			$avg_runtime = Requests_Log_Entity::get_var(
				"SELECT AVG(runtime) FROM {$table_name} WHERE runtime > 0"
			);

			// Get requests in last 24 hours.
			$last_24h = Requests_Log_Entity::get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE date_added >= %d",
					strtotime( '-24 hours' )
				)
			);

			// Get requests in last 7 days.
			$last_7d = Requests_Log_Entity::get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE date_added >= %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					strtotime( '-7 days' )
				)
			);

			// Get top domains.
			$top_domains = Requests_Log_Entity::get_results(
				"SELECT domain, COUNT(*) as count FROM {$table_name} WHERE domain != '' GROUP BY domain ORDER BY count DESC LIMIT 10",
				ARRAY_A
			);

			// Get error rate.
			$error_count = Requests_Log_Entity::get_var(
				"SELECT COUNT(*) FROM {$table_name} WHERE status = 'error'"
			);
			$error_rate  = $total_requests > 0 ? ( $error_count / $total_requests ) * 100 : 0;

			// Get performance data for last 30 days.
			$performance_data = Requests_Log_Entity::get_results(
				$wpdb->prepare(
					"SELECT DATE(FROM_UNIXTIME(date_added)) as date, AVG(runtime) as avg_runtime, COUNT(*) as request_count
					 FROM {$table_name}
					 WHERE date_added >= %d
					 GROUP BY DATE(FROM_UNIXTIME(date_added))
					 ORDER BY date_added DESC",
					strtotime( '-30 days' )
				),
				ARRAY_A
			);

			return array(
				'total_requests'   => (int) $total_requests,
				'status_counts'    => $status_counts,
				'type_counts'      => $type_counts,
				'avg_runtime'      => (float) $avg_runtime,
				'last_24h'         => (int) $last_24h,
				'last_7d'          => (int) $last_7d,
				'top_domains'      => $top_domains,
				'error_rate'       => round( $error_rate, 2 ),
				'performance_data' => $performance_data,
			);
		}

		/**
		 * Render the analytics dashboard.
		 *
		 * @param array $analytics_data The analytics data.
		 *
		 * @return void
		 *
		 * @since 4.8.0
		 */
		public static function render_analytics_dashboard( $analytics_data ) {
			?>
			<div class="advana-analytics-dashboard" style="margin: 20px 0; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
				<h2 style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;" onclick="jQuery('.advana-dashboard-content').slideToggle(); jQuery('.advana-dashboard-toggle').toggleClass('dashicons-arrow-up-alt2 dashicons-arrow-down-alt2');">
					<?php \esc_html_e( 'Requests Analytics Dashboard', '0-day-analytics' ); ?>
					<span class="dashicons dashicons-arrow-up-alt2 advana-dashboard-toggle" style="font-size: 20px;"></span>
				</h2>

				<div class="advana-dashboard-content">
				<div class="analytics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">

					<!-- Summary Cards -->
					<div class="analytics-card" style="padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1;">
						<h3 style="margin: 0 0 10px 0;"><?php \esc_html_e( 'Total Requests', '0-day-analytics' ); ?></h3>
						<div style="font-size: 24px; font-weight: bold; color: #007cba;"><?php echo \number_format( (float) $analytics_data['total_requests'] ); ?></div>
					</div>

					<div class="analytics-card" style="padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1;">
						<h3 style="margin: 0 0 10px 0;"><?php \esc_html_e( 'Last 24 Hours', '0-day-analytics' ); ?></h3>
						<div style="font-size: 24px; font-weight: bold; color: #28a745;"><?php echo \number_format( (float) $analytics_data['last_24h'] ); ?></div>
					</div>

					<div class="analytics-card" style="padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1;">
						<h3 style="margin: 0 0 10px 0;"><?php \esc_html_e( 'Last 7 Days', '0-day-analytics' ); ?></h3>
						<div style="font-size: 24px; font-weight: bold; color: #17a2b8;"><?php echo \number_format( $analytics_data['last_7d'] ); ?></div>
					</div>

					<div class="analytics-card" style="padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1;">
						<h3 style="margin: 0 0 10px 0;"><?php \esc_html_e( 'Avg Runtime', '0-day-analytics' ); ?></h3>
						<div style="font-size: 24px; font-weight: bold; color: #6f42c1;"><?php echo \number_format( (float) $analytics_data['avg_runtime'], 3 ); ?>s</div>
					</div>

					<div class="analytics-card" style="padding: 15px; border-radius: 4px; border: 1px solid #e1e1e1;">
						<h3 style="margin: 0 0 10px 0;"><?php \esc_html_e( 'Error Rate', '0-day-analytics' ); ?></h3>
						<div style="font-size: 24px; font-weight: bold; color: <?php echo $analytics_data['error_rate'] > 10 ? '#dc3545' : '#28a745'; ?>;"><?php echo \number_format( (float) $analytics_data['error_rate'], 1 ); ?>%</div>
					</div>
				</div>

				<div class="analytics-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">

					<!-- Status Breakdown -->
					<div class="analytics-section">
						<h3><?php \esc_html_e( 'Requests by Status', '0-day-analytics' ); ?></h3>
						<div style="border: 1px solid #e1e1e1; padding: 15px; border-radius: 4px;">
							<?php if ( ! empty( $analytics_data['status_counts'] ) ) : ?>
								<ul style="list-style: none; padding: 0; margin: 0;">
									<?php foreach ( $analytics_data['status_counts'] as $status ) : ?>
										<?php
										$filter_url = \add_query_arg(
											array(
												'page' => 'advan_requests',
												'status_filter' => $status['status'],
											),
											\admin_url( 'admin.php' )
										);
										?>
									<li style="padding: 5px 0; border-bottom: 1px solid #e1e1e1;">
										<a href="<?php echo \esc_url( $filter_url ); ?>" style="text-decoration: none; color: inherit; display: block;" title="<?php \esc_attr_e( 'Click to filter by this status', '0-day-analytics' ); ?>">
											<span style="font-weight: bold;"><?php echo \esc_html( \ucfirst( $status['status'] ) ); ?>:</span>
											<span style="float: right;"><?php echo \number_format( (float) $status['count'] ); ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p><?php \esc_html_e( 'No data available', '0-day-analytics' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Type Breakdown -->
					<div class="analytics-section">
						<h3><?php \esc_html_e( 'Requests by Type', '0-day-analytics' ); ?></h3>
						<div style="border: 1px solid #e1e1e1; padding: 15px; border-radius: 4px;">
							<?php if ( ! empty( $analytics_data['type_counts'] ) ) : ?>
								<ul style="list-style: none; padding: 0; margin: 0;">
									<?php foreach ( $analytics_data['type_counts'] as $type ) : ?>
										<?php
										$filter_url = \add_query_arg(
											array(
												'page' => 'advan_requests',
												'type_filter' => $type['request_type'],
											),
											\admin_url( 'admin.php' )
										);
										?>
									<li style="padding: 5px 0; border-bottom: 1px solid #e1e1e1;">
										<a href="<?php echo \esc_url( $filter_url ); ?>" style="text-decoration: none; color: inherit; display: block;" title="<?php \esc_attr_e( 'Click to filter by this type', '0-day-analytics' ); ?>">
											<span style="font-weight: bold;"><?php echo \esc_html( $type['request_type'] ); ?>:</span>
											<span style="float: right;"><?php echo \number_format( (float) $type['count'] ); ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p><?php \esc_html_e( 'No data available', '0-day-analytics' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Top Domains -->
					<div class="analytics-section">
						<h3><?php \esc_html_e( 'Top Domains', '0-day-analytics' ); ?></h3>
						<div style="border: 1px solid #e1e1e1; padding: 15px; border-radius: 4px;">
							<?php if ( ! empty( $analytics_data['top_domains'] ) ) : ?>
								<ul style="list-style: none; padding: 0; margin: 0;">
									<?php foreach ( $analytics_data['top_domains'] as $domain ) : ?>
										<?php
										$filter_url = \add_query_arg(
											array(
												'page' => 'advan_requests',
												'domain_filter' => $domain['domain'],
											),
											\admin_url( 'admin.php' )
										);
										?>
									<li style="padding: 5px 0; border-bottom: 1px solid #e1e1e1;">
										<a href="<?php echo \esc_url( $filter_url ); ?>" style="text-decoration: none; color: inherit; display: block;" title="<?php \esc_attr_e( 'Click to filter by this domain', '0-day-analytics' ); ?>">
											<span style="font-weight: bold;"><?php echo \esc_html( $domain['domain'] ); ?>:</span>
											<span style="float: right;"><?php echo \number_format( (float) $domain['count'] ); ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p><?php \esc_html_e( 'No data available', '0-day-analytics' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Performance Trends -->
					<div class="analytics-section">
						<h3><?php \esc_html_e( 'Performance Trends (Last 30 Days)', '0-day-analytics' ); ?></h3>
						<div style="border: 1px solid #e1e1e1; padding: 15px; border-radius: 4px;">
							<?php if ( ! empty( $analytics_data['performance_data'] ) ) : ?>
								<div style="max-height: 200px; overflow-y: auto;">
									<table style="width: 100%; border-collapse: collapse;">
										<thead>
											<tr style="border-bottom: 2px solid #e1e1e1;">
												<th style="text-align: left; padding: 5px;"><?php \esc_html_e( 'Date', '0-day-analytics' ); ?></th>
												<th style="text-align: right; padding: 5px;"><?php \esc_html_e( 'Requests', '0-day-analytics' ); ?></th>
												<th style="text-align: right; padding: 5px;"><?php \esc_html_e( 'Avg Runtime', '0-day-analytics' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( array_slice( $analytics_data['performance_data'], -10 ) as $data ) : ?>
												<tr style="border-bottom: 1px solid #e1e1e1;">
													<td style="padding: 5px;"><?php echo esc_html( $data['date'] ); ?></td>
													<td style="text-align: right; padding: 5px;"><?php echo number_format( (float) $data['request_count'] ); ?></td>
													<td style="text-align: right; padding: 5px;"><?php echo number_format( (float) $data['avg_runtime'], 3 ); ?>s</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php else : ?>
								<p><?php \esc_html_e( 'No data available', '0-day-analytics' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
				</div>
			</div>
			<style>
				/* Responsive analytics dashboard */
				@media screen and (max-width: 782px) {
					.advana-analytics-dashboard .analytics-details {
						grid-template-columns: 1fr !important;
					}
					.advana-analytics-dashboard .analytics-grid {
						grid-template-columns: 1fr !important;
					}
				}
				/* Hover effect for clickable analytics items */
				.analytics-section ul li a:hover {
					background-color: #d1dae3;
					transition: background-color 0.2s ease;
				}
				.aadvana-darkskin .analytics-section ul li a:hover {
					background-color: #d1dae31e;
					transition: background-color 0.2s ease;
				}
			</style>
			<?php
		}
	}
}
