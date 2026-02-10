<?php
/**
 * Class: Responsible for Fatals views and operations.
 *
 * Edit and add fatals, attach screens.
 *
 * @package advanced-analytics
 *
 * @since 2.7.0
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

use ADVAN\Lists\Fatals_List;
use ADVAN\Entities_Global\Common_Table;
use ADVAN\Controllers\Api\Endpoints;
use ADVAN\Entities\WP_Fatals_Entity;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Lists\Views\Fatals_View' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 2.7.0
	 */
	class Fatals_View {

		/**
		 * Displays the fatals page.
		 *
		 * @return void
		 *
		 * @since 2.7.0
		 */
		public static function analytics_fatals_page() {
			// Capability guard: only allow administrators (or users with equivalent capability).
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage fatals.', '0-day-analytics' ) );
			}

			\add_thickbox();
			\wp_enqueue_style( 'media-views' );
			\wp_enqueue_script( 'wp-api-fetch' );
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
			<?php

			$fatals = new Fatals_List( '' );
			$fatals->prepare_items();

			$table_name = WP_Fatals_Entity::get_table_name();
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php \esc_html_e( 'PHP errors', '0-day-analytics' ); ?></h1>

				<hr class="wp-header-end">

				<form id="fatals-filter" method="get">
				<?php

				$page  = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
				$paged = ( isset( $_GET['paged'] ) ) ? \absint( filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) ) : 1;
				if ( $paged < 1 ) {
					$paged = 1;
				}

				printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
				printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

				echo '<div style="clear:both; float:right">';
				$fatals->search_box(
					__( 'Search', '0-day-analytics' ),
					strtolower( $fatals::get_table_name() ) . '-find'
				);
				echo '</div>';
				$fatals->display();

				?>
				</form>
			</div>
			<style>
				#TB_window {
					z-index: 160001 !important;
				}
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
					box-sizing: border-box;
				}
				html.aadvana-darkskin .wrapper .box {
					background-color: #1d456b !important;
					border: 1px solid #ccc;
				}
				html.aadvana-darkskin .media-frame-content {
					background-color: #1d456b !important;
				}
				.wrapper #mail-body {
					width: 99%;
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
							<h1><?php \esc_html_e( 'PHP error log details:', '0-day-analytics' ); ?></h1>
						</div>
						<div class="media-frame-content">
							<div class="modal-content-wrap">
								<p>
									<b><?php \esc_html_e( 'Table', '0-day-analytics' ); ?>:</b> 
									<span class="table-name"><?php echo \esc_html( $table_name ); ?></span><br>
								</p>
								<div class="aadvana-panel-wrapper">
									<div class="aadvana-request-response aadvana-panel-active wrapper">
										<div class="box" id="mail-body">
											<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
												<div>
													<h3><?php \esc_html_e( 'Row data:', '0-day-analytics' ); ?></h3>
												</div>
												<div class=""><span title="<?php \esc_html_e( 'Copy to clipboard (as raw HTML)', '0-day-analytics' ); ?>" class="dashicons dashicons-clipboard" style="cursor:pointer;font-family: dashicons !important;" aria-hidden="true"></span> <span title="<?php \esc_html_e( 'Share', '0-day-analytics' ); ?>" class="dashicons dashicons-share" style="cursor:pointer;font-family: dashicons !important;" aria-hidden="true"></span></div>
											</div>
											<div class="http-request-args aadvana-pre-300">
												<?php
												\esc_html_e( 'Loading please wait...', '0-day-analytics' );
												?>
													
											</div>
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

				jQuery(document).on('click', '.aadvana-tablerow-view', function( e ) {
					e.preventDefault();
					let id = jQuery( this ).data( 'details-id' );
					let that = this;
					var encodedValue = jQuery('<div />').text(id).html();
					try {
						attResp = wp.apiFetch({
							path: '/<?php echo \esc_attr( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/get_fatals_record/<?php echo \esc_attr( $table_name ); ?>/' + id + '/',
							method: 'GET',
							cache: 'no-cache'
						}).then( ( attResp ) => {

							jQuery('.media-modal .http-request-args').html(attResp.mail_body);
							jQuery('a.view-source').on('click', function(e) {
								this.href += '&width=' + ( window.innerWidth - 100 ) + '&height=' + ( window.innerHeight - 100 ) ;
							});
							//jQuery('.media-modal .table-name').html(attResp.table_name);

						} ).catch(
							( error ) => {
								if (error.message) {
									jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + error.message + '</div></td></tr>');
								}
							}
						);
					} catch (error) {
						throw error;
					} finally {
						jQuery(that).css({
							"pointer-events": "",
							"cursor": ""
						})
					}

					jQuery('.media-modal').addClass('open');
					jQuery('.media-modal-backdrop').addClass('open');
				});

				jQuery(document).on('click', '.media-modal-close', function () {
					jQuery('.media-modal .http-request-args').html('<?php \esc_html_e( 'Loading please wait...', '0-day-analytics' ); ?>');
					//jQuery('.media-modal .table-name').html('');
					jQuery('.media-modal').removeClass('open');
					jQuery('.media-modal-backdrop').removeClass('open');
				});

				jQuery( document ).on( 'click', '.dashicons.dashicons-clipboard', function( e ) {

					if ( jQuery(this).parent().parent().next('.aadvana-pre-300') ) {
						let selectedText = jQuery(this).parent().parent().next('.aadvana-pre-300').html();

						// selectedText = selectedText.replace(/<br\s*\/?>/gim, "\n");
						// selectedText = jQuery.parseHTML(selectedText); //parseHTML return HTMLCollection
						// selectedText = jQuery(selectedText).text();

						if ( selectedText ) {
							navigator.clipboard.writeText(selectedText);
					} else {
						let selectedText = jQuery(this).parent().parent().next().closest('.error_message').text();

						// if ( jQuery(this).parent().parent().next().next().next('.log_details_show').children('pre').length ) {
						// 	selectedText = selectedText + '\n\n' + 'Trace:' + '\n' + jQuery(this).parent().parent().next().next().next('.log_details_show').children('pre').html();

						// 	selectedText = selectedText.replace(/<br\s*\/?>/gim, "\n");
						// 	selectedText = jQuery.parseHTML(selectedText); //parseHTML return HTMLCollection
						// 	selectedText = jQuery(selectedText).text();
						// }

						navigator.clipboard.writeText(selectedText);
				
					}
				}

				});
			
				jQuery( document ).ready( function() {

					if ( navigator.share ) {

						jQuery( document ).on( 'click', '.dashicons.dashicons-share', function( e ) {

							if ( jQuery(this).parent().parent().next('.aadvana-pre-300') ) {
								let selectedText = jQuery(this).parent().parent().next('.aadvana-pre-300').html();

								// selectedText = selectedText.replace(/<br\s*\/?>/gim, "\n");
								// selectedText = jQuery.parseHTML(selectedText); //parseHTML return HTMLCollection
								// selectedText = jQuery(selectedText).text();

								const shareData = {
									text: selectedText + '\n\n' + "<?php echo \get_site_url(); ?>",
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

			$help_text  = '<p>' . __( 'This screen allows you to see all the fatals where your WordPress site is currently running.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can specify how many rows to be shown, or filter and search for given value(s).', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can delete rows - keep in mind that this operation is destructive and can not be undone - make a backup first.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'Bulk operations are supported.', '0-day-analytics' ) . '</p>';

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

			Common_Table::init( WP_Fatals_Entity::get_table_name() );

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

							if ( confirm( '<?php echo \esc_html__( 'You sure you want to truncate this table? That operation is destructive', '0-day-analytics' ); ?>' ) ) {
								let tableName = e.target.getAttribute('data-table-name');

								let attResp;

								try {
									attResp = await wp.apiFetch({
										path: '/<?php echo \esc_attr( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/truncate_table/' + tableName,
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

							if ( confirm( '<?php echo \esc_html__( 'You sure you want to delete this table? That operation is destructive', '0-day-analytics' ); ?>' ) ) {
								let tableName = e.target.getAttribute('data-table-name');

								let attResp;

								try {
									attResp = await wp.apiFetch({
										path: '/<?php echo esc_attr( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/drop_table/' + tableName,
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

				if ( \check_admin_referer( Fatals_List::PLUGIN_FILTER_ACTION, Fatals_List::PLUGIN_FILTER_ACTION . 'nonce' ) ) {
					$id = \sanitize_text_field( \wp_unslash( $_REQUEST['plugin_top'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					$context    = isset( $_REQUEST['context'] ) ? \sanitize_text_field( $_REQUEST['context'] ) : 'site';
					$is_network = ( $context === 'network' && \is_multisite() );

					\wp_safe_redirect(
						\remove_query_arg(
							array( 'deleted' ),
							\add_query_arg(
								array(
									'page'   => Fatals_List::FATALS_MENU_SLUG,
									Fatals_List::SEARCH_INPUT => Fatals_List::escaped_search_input(),
									'plugin' => rawurlencode( $id ),
								),
								( ( $is_network ) ? \network_admin_url( 'admin.php' ) : \admin_url( 'admin.php' ) )
							)
						)
					);
					exit;
				}
			}
		}
	}
}
