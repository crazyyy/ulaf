<?php
/**
 * Class: Responsible for Hooks Capture views and operations.
 *
 * View and manage hooks capture logs.
 *
 * @package advanced-analytics
 *
 * @since 4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

use ADVAN\Helpers\Settings;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Lists\Hooks_Capture_List;
use ADVAN\Entities\Hooks_Capture_Entity;
use ADVAN\Controllers\Api\Endpoints;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Lists\Views\Hooks_Capture_View' ) ) {
	/**
	 * Responsible for Hooks Capture views.
	 *
	 * @since 4.5.0
	 */
	class Hooks_Capture_View {

		/**
		 * Displays the hooks capture page.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		public static function analytics_hooks_capture_page() {
			// Capability guard
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage hooks capture.', '0-day-analytics' ) );
			}

			\add_thickbox();

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
			self::list_hook_captures();
		}

		/**
		 * List hook captures.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		private static function list_hook_captures() {
			$hooks_capture = new Hooks_Capture_List( '' );
			$hooks_capture->prepare_items();

			\wp_enqueue_style( 'media-views' );
			\wp_enqueue_script( 'wp-api-fetch' );

			$table_name = Hooks_Capture_Entity::get_table_name();
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php \esc_html_e( 'Hooks Capture Log', '0-day-analytics' ); ?></h1>

				<hr class="wp-header-end">
				<?php
				if ( ! Settings::get_option( 'hooks_capture_module_enabled' ) ) {
					?>
				<div id="advana-status-error" class="notice notice-error">
					<?php
					printf(
						'<p>%1$s</p>',
						sprintf(
							/* translators: %s: Link to settings. */
							\esc_html__( 'The hooks capture module is disabled. To enable it go to: %s', '0-day-analytics' ),
							'<a href="' . \esc_url( \add_query_arg( array( 'page' => Settings::SETTINGS_MENU_SLUG ), \admin_url( 'admin.php' ) ) ) . '#aadvana-options-tab-hooks-capture">' . \esc_html__( 'settings', '0-day-analytics' ) . '</a>'
						)
					);
					?>
				</div>
					<?php
				}

				if ( isset( $_GET['deleted'] ) ) {
					?>
				<div id="message" class="updated notice is-dismissible">
					<p>
					<?php
					printf(
						/* translators: %d: Number of deleted items */
						\esc_html( _n( '%d hook capture deleted.', '%d hook captures deleted.', \absint( $_GET['deleted'] ), '0-day-analytics' ) ),
						\absint( $_GET['deleted'] )
					);
					?>
					</p>
				</div>
					<?php
				}
				?>

				<form id="hooks-capture-filter" method="get">
				<?php

				$page  = isset( $_GET['page'] ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
				$paged = isset( $_GET['paged'] ) ? \filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1;

				printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
				printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

				echo '<div style="clear:both; float:right">';
				$hooks_capture->search_box(
					\__( 'Search', '0-day-analytics' ),
					'hooks-capture-find'
				);
				echo '</div>';
				$hooks_capture->display();

				?>
				</form>
			</div>
			<style>
				.hook-status.enabled {
					color: #46b450;
					font-weight: bold;
				}
				.hook-status.disabled {
					color: #dc3232;
				}

				/* Modal styles - similar to fatals view */
				#TB_window {
					z-index: 160001 !important;
				}

				.media-modal,
				.media-modal-backdrop {
					display: none;
				}

				.media-modal.open,
				.media-modal-backdrop.open {
					display: block;
				}

				#aadvana-hook-modal.aadvana-modal .media-frame-title,
				#aadvana-hook-modal.aadvana-modal .media-frame-content {
					left: 0;
				}

				.media-frame-router {
					left: 10px;
				}

				#aadvana-hook-modal.aadvana-modal .media-frame-content {
					top: 48px;
					bottom: 0;
					overflow: auto;
				}

				.button-link.media-modal-close {
					cursor: pointer;
					text-decoration: none;
				}

				.aadvana-modal-buttons {
					position: absolute;
					top: 0;
					right: 0;
				}

				.aadvana-modal-buttons .media-modal-close {
					position: relative;
					width: auto;
					padding: 0 .5rem;
				}

				.modal-content-wrap {
					padding: 16px;
				}

				.wrapper {
					text-align: center;
				}

				.wrapper .box {
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

				.wrapper #hook-details {
					width: 99%;
				}

				@media screen and (max-width: 782px) {
					.wrapper .box {
						display: block;
						width: auto;
					}
				}
			</style>

			<!-- Modal popup for hook details -->
			<div id="aadvana-hook-modal" class="media-modal aadvana-modal">
				<div class="aadvana-modal-buttons">
					<button class="button-link media-modal-close"><span class="media-modal-icon"></span></button>
				</div>
				<div class="media-modal-content">
					<div class="media-frame">
						<div class="media-frame-title">
							<h1><?php \esc_html_e( 'Hook Capture Details', '0-day-analytics' ); ?></h1>
						</div>
						<div class="media-frame-content">
							<div class="modal-content-wrap">
								<p>
									<b><?php \esc_html_e( 'Table', '0-day-analytics' ); ?>:</b> 
									<span class="table-name"><?php echo \esc_html( $table_name ); ?></span><br>
								</p>
								<div class="aadvana-panel-wrapper">
									<div class="aadvana-request-response aadvana-panel-active wrapper">
										<div class="box" id="hook-details">
											<div class="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid justify-between">
												<div>
													<h3><?php \esc_html_e( 'Hook Data:', '0-day-analytics' ); ?></h3>
												</div>
												<div class=""><span title="<?php \esc_html_e( 'Copy to clipboard (as raw HTML)', '0-day-analytics' ); ?>" class="dashicons dashicons-clipboard" style="cursor:pointer;font-family: dashicons !important;" aria-hidden="true"></span> <span title="<?php \esc_html_e( 'Share', '0-day-analytics' ); ?>" class="dashicons dashicons-share" style="cursor:pointer;font-family: dashicons !important;" aria-hidden="true"></span></div>
											</div>
											<div class="hook-details-content aadvana-pre-300">
												<?php \esc_html_e( 'Loading please wait...', '0-day-analytics' ); ?>
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
				jQuery(document).on('click', '.aadvana-hook-view', function( e ) {
					e.preventDefault();
					let id = jQuery( this ).data( 'details-id' );
					let that = this;

					try {
						wp.apiFetch({
							path: '/<?php echo \esc_attr( \ADVAN\Controllers\Api\Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/get_hook_capture_record/<?php echo \esc_attr( $table_name ); ?>/' + id + '/',
							method: 'GET',
							cache: 'no-cache'
						}).then( ( response ) => {
							jQuery('.media-modal .hook-details-content').html(response.mail_body);
							jQuery('a.view-source').on('click', function(e) {
								this.href += '&width=' + ( window.innerWidth - 100 ) + '&height=' + ( window.innerHeight - 100 ) ;
							});
						} ).catch( ( error ) => {
							if (error.message) {
								jQuery(that).closest("tr").after('<tr><td style="overflow:hidden;" colspan="'+(jQuery(that).closest("tr").find("td").length+1)+'"><div class="error" style="background:#fff; color:#000;"> ' + error.message + '</div></td></tr>');
							}
						});
					} catch (error) {
						console.error('Error fetching hook details:', error);
					} finally {
						jQuery(that).css({
							"pointer-events": "",
							"cursor": ""
						});
					}

					jQuery('.media-modal').addClass('open');
					jQuery('.media-modal-backdrop').addClass('open');
				});

				jQuery(document).on('click', '.media-modal-close', function () {
					jQuery('.media-modal .hook-details-content').html('<?php \esc_html_e( 'Loading please wait...', '0-day-analytics' ); ?>');
					jQuery('.media-modal').removeClass('open');
					jQuery('.media-modal-backdrop').removeClass('open');
				});

				jQuery( document ).on( 'click', '.dashicons.dashicons-clipboard', function( e ) {
					if ( jQuery(this).parent().parent().next('.hook-details-content') ) {
						let selectedText = jQuery(this).parent().parent().next('.hook-details-content').html();
						if ( selectedText ) {
							navigator.clipboard.writeText(selectedText);
						}
					}
				});

				jQuery( document ).ready( function() {
					if ( navigator.share ) {
						jQuery( document ).on( 'click', '.dashicons.dashicons-share', function( e ) {
							if ( jQuery(this).parent().parent().next('.hook-details-content') ) {
								let selectedText = jQuery(this).parent().parent().next('.hook-details-content').html();
								const shareData = {
									text: selectedText + '\n\n' + "<?php echo \get_site_url(); ?>",
								};

								try {
									navigator.share(shareData);
								} catch (err) {
									console.error('Error sharing:', err);
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
	}
}
