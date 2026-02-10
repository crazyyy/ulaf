<?php
/**
 * Class: Responsible for Table views and operations.
 *
 * Edit and add table, attach screens.
 *
 * @package advanced-analytics
 *
 * @since 1.9.8.1
 */

declare(strict_types=1);

namespace ADVAN\Lists\Views;

use ADVAN\Lists\Table_List;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Helpers\Miscellaneous;
use ADVAN\Controllers\Api\Endpoints;
use ADVAN\Entities_Global\Common_Table;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Lists\Views\Table_View' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 1.9.8.1
	 */
	class Table_View {

		/**
		 * Displays the table page.
		 *
		 * @return void
		 *
		 * @since 1.7.0
		 */
		public static function analytics_table_page() {
			// Capability guard: only allow administrators (or users with equivalent capability).
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage tables.', '0-day-analytics' ) );
			}

			// Determine requested table early so we can process actions before any output.
			$table_name      = Common_Table::get_default_table();
			$requested_table = isset( $_REQUEST['show_table'] ) ? \sanitize_key( \wp_unslash( $_REQUEST['show_table'] ) ) : '';
			if ( $requested_table && \in_array( $requested_table, Common_Table::get_tables(), true ) ) {
				$table_name = $requested_table;
			}

			// Instantiate list table and process bulk actions BEFORE any output to avoid header issues.
			$table = new Table_List( $table_name );

			// Enqueue assets and render after potential redirects are processed.
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

			$action = ! empty( $_REQUEST['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? \sanitize_key( $_REQUEST['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: '';

			if ( ! empty( $action ) && ( 'add_table_data' === $action ) && WP_Helper::verify_admin_nonce( 'add-row' ) ) {

				$core_table = '';
				if ( in_array( $table_name, Common_Table::get_wp_core_tables(), true ) ) {
					$core_table = ' ( <span class="dashicons dashicons-wordpress" aria-hidden="true" style="vertical-align: middle;"></span> ) ';
				}
				Common_Table::init( $table_name );
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php \esc_html_e( 'Add New Row to Table: ', '0-day-analytics' ); ?><?php echo \wp_kses_post( $core_table ); ?><?php echo \esc_html( $table_name ); ?></h1>
					
					<hr class="wp-header-end">
					<form id="table-row-add" method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="<?php echo \esc_attr( Table_List::INSERT_ACTION ); ?>" />

				<?php

					$page  = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
					$paged = ( isset( $_GET['paged'] ) ) ? filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1;

					printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
					printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

					printf( '<input type="hidden" name="%s" value="%s" />', \esc_attr( Table_List::SEARCH_INPUT ), \esc_attr( Table_List::escaped_search_input() ) );

					printf( '<input type="hidden" name="show_table" value="%s" />', \esc_attr( $table_name ) );

					\wp_nonce_field( Table_List::NONCE_NAME );
					echo '<input type="hidden" name="table_name" value="' . \esc_attr( $table_name ) . '">';

					$columns = Common_Table::get_columns_info();
				?>
						<div id="advaa-status-notice" class="notice notice-info">
							<p>
								<?php
								\esc_html_e( 'Fill in the form below to add a new row to the table. Fields marked as auto-increment will be generated automatically.', '0-day-analytics' );
								?>
							</p>
						</div>

						<table class="form-table">
							<tbody>
								<?php

								foreach ( $columns as $column ) {
									$name  = \esc_attr( $column['Field'] );
									$type  = strtolower( $column['Type'] );
									$value = ''; // No pre-filled value for insert.
									$null  = 'YES' === $column['Null'];
									$extra = strtolower( $column['Extra'] );

									// Skip auto-increment primary key.
									if ( 'auto_increment' === $extra ) {
										continue;
									}

									$input = '';

									$required = ''; //'required';

									// Detect input type.
									if ( preg_match( '/int|decimal|float|double|real|bit|bool/i', $type ) ) {
										$input = "<input class='large-text' type='number' step='any' name='$name' value='$value' " . ( $null ? '' : $required ) . '>';
									} elseif ( preg_match( '/char|varchar/i', $type ) ) {
										$input = "<input class='large-text' type='text' name='$name' value='$value' maxlength='255' " . ( $null ? '' : $required ) . '>';
									} elseif ( preg_match( '/text|tinytext|mediumtext|longtext/i', $type ) ) {
										$input = "<textarea class='large-text' name='$name' rows='10' " . ( $null ? '' : $required ) . ">$value</textarea>";
									} elseif ( preg_match( '/date$/i', $type ) ) {
										$input = "<input type='date' name='$name' value='$value'>";
									} elseif ( preg_match( '/datetime|timestamp/i', $type ) ) {
										$input = "<input type='datetime-local' name='$name' value=''>";
									} elseif ( preg_match( '/time$/i', $type ) ) {
										$input = "<input type='time' name='$name' value='$value'>";
									} elseif ( preg_match( '/year/i', $type ) ) {
										$input = "<input type='number' name='$name' value='$value' min='1900' max='2100'>";
									} elseif ( preg_match( '/enum\((.+)\)/i', $type, $matches ) ) {
										// Extract ENUM options.
										$options = str_getcsv( $matches[1], ',', "'" );
										$input   = "<select name='$name'>";
										$input  .= "<option value=''>-- " . \esc_html__( 'Select', '0-day-analytics' ) . " --</option>";
										foreach ( $options as $option ) {
											$input .= "<option value='" . esc_attr( $option ) . "'>" . esc_html( $option ) . '</option>';
										}
										$input .= '</select>';
									} elseif ( preg_match( '/set\((.+)\)/i', $type, $matches ) ) {
										// Extract SET options.
										$options = str_getcsv( $matches[1], ',', "'" );
										foreach ( $options as $option ) {
											$input .= "<label><input type='checkbox' name='{$name}[]' value='" . \esc_attr( $option ) . "'> " . \esc_html( $option ) . '</label><br>';
										}
									} elseif ( preg_match( '/json/i', $type ) ) {
										$input = "<textarea class='large-text' name='$name' rows='10' placeholder='Enter valid JSON'></textarea>";
									} else {
										// Fallback for unrecognized types.
										$input = "<input class='large-text' type='text' name='$name' value='$value'>";
									}
									?>

									<tr>
										<th scope="row">
											<label for="<?php echo \esc_attr( $name ); ?>"><strong><?php echo esc_html( $name ); ?></strong></label>
										</th>
										<td><?php echo $input; ?></td>
									</tr>
									<?php
								}

								?>
							</tbody>
						</table>

						<p class="submit">
							<?php \submit_button( \__( 'Add Row', '0-day-analytics' ), 'primary', '', false ); ?>
						</p>
					</form>
				</div>
				<?php
			} elseif ( ! empty( $action ) && ( 'edit_table_data' === $action ) && WP_Helper::verify_admin_nonce( 'edit-row' ) ) {

				$core_table = '';
				if ( in_array( $table_name, Common_Table::get_wp_core_tables(), true ) ) {
					$core_table = ' ( <span class="dashicons dashicons-wordpress" aria-hidden="true" style="vertical-align: middle;"></span> ) ';
				}
				Common_Table::init( $table_name );
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php \esc_html_e( 'Edit Row in Table: ', '0-day-analytics' ); ?><?php echo \wp_kses_post( $core_table ); ?><?php echo \esc_html( $table_name ); ?></h1>
					
					<hr class="wp-header-end">
					<form id="table-row-edit" method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="<?php echo \esc_attr( Table_List::UPDATE_ACTION ); ?>" />

				<?php

					$page  = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
					$paged = ( isset( $_GET['paged'] ) ) ? filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1;

					printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
					printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

					printf( '<input type="hidden" name="%s" value="%s" />', \esc_attr( Table_List::SEARCH_INPUT ), \esc_attr( Table_List::escaped_search_input() ) );

					printf( '<input type="hidden" name="show_table" value="%s" />', \esc_attr( $table_name ) );

					$id = isset( $_GET['id'] ) ? \sanitize_text_field( \wp_unslash( $_GET['id'] ) ) : '';

					\wp_nonce_field( Table_List::NONCE_NAME );
					echo '<input type="hidden" name="record_id" value="' . \esc_attr( $id ) . '">';
					echo '<input type="hidden" name="table_name" value="' . \esc_attr( $table_name ) . '">';

					$record = Common_Table::load_row_data(
						$id
					);

					$columns = Common_Table::get_columns_info();
				?>
						<div id="advaa-status-notice" class="notice notice-warning">
							<p>
								<?php
								printf(
									/* translators: 1: opening anchor tag, 2: closing anchor tag */
									\esc_html__( 'Don\'t edit / save records that contain serialized data! You may lose your data - make sure you have a backup first!', '0-day-analytics' ),
									// '<a href="https://developer.wordpress.org/plugins/plugin-basics/serialization/" target="_blank" rel="noopener noreferrer">',
									// '</a>'
								);
								?>
							</p>
						</div>

						<table class="form-table">
							<tbody>
								<?php

								foreach ( $columns as $column ) {
									$name  = \esc_attr( $column['Field'] );
									$type  = strtolower( $column['Type'] );
									$value = isset( $record[ $name ] ) ? \esc_html( $record[ $name ] ) : '';
									$null  = 'YES' === $column['Null'];
									$extra = strtolower( $column['Extra'] );

									// Skip auto-increment primary key.
									if ( 'auto_increment' === $extra ) {
										continue;
									}

									$input = '';

									$required = ''; //'required';

									// Detect input type.
									if ( preg_match( '/int|decimal|float|double|real|bit|bool/i', $type ) ) {
										$input = "<input class='large-text' type='number' step='any' name='$name' value='$value' " . ( $null ? '' : $required ) . '>';
									} elseif ( preg_match( '/char|varchar/i', $type ) ) {
										$input = "<input class='large-text' type='text' name='$name' value='$value' maxlength='255' " . ( $null ? '' : $required ) . '>';
									} elseif ( preg_match( '/text|tinytext|mediumtext|longtext/i', $type ) ) {
										$input = "<textarea class='large-text' name='$name' rows='10' " . ( $null ? '' : $required ) . ">$value</textarea>";
									} elseif ( preg_match( '/date$/i', $type ) ) {
										$input = "<input type='date' name='$name' value='$value'>";
									} elseif ( preg_match( '/datetime|timestamp/i', $type ) ) {
										$input = "<input type='datetime-local' name='$name' value='" . esc_attr( str_replace( ' ', 'T', $value ) ) . "'>";
									} elseif ( preg_match( '/time$/i', $type ) ) {
										$input = "<input type='time' name='$name' value='$value'>";
									} elseif ( preg_match( '/year/i', $type ) ) {
										$input = "<input type='number' name='$name' value='$value' min='1900' max='2100'>";
									} elseif ( preg_match( '/enum\((.+)\)/i', $type, $matches ) ) {
										// Extract ENUM options.
										$options = str_getcsv( $matches[1], ',', "'" );
										$input   = "<select name='$name'>";
										foreach ( $options as $option ) {
											$selected = $value === $option ? 'selected' : '';
											$input   .= "<option value='" . esc_attr( $option ) . "' $selected>" . esc_html( $option ) . '</option>';
										}
										$input .= '</select>';
									} elseif ( preg_match( '/set\((.+)\)/i', $type, $matches ) ) {
										// Extract SET options.
										$options = str_getcsv( $matches[1], ',', "'" );
										$current = explode( ',', $value );
										foreach ( $options as $option ) {
												$checked = in_array( $option, $current, true ) ? 'checked' : '';
											$input      .= "<label><input type='checkbox' name='{$name}[]' value='" . \esc_attr( $option ) . "' $checked> " . \esc_html( $option ) . '</label><br>';
										}
									} elseif ( preg_match( '/json/i', $type ) ) {
										$input = "<textarea class='large-text' name='$name' rows='10' placeholder='Enter valid JSON'>" . \esc_textarea( $value ) . '</textarea>';
									} else {
										// Fallback for unrecognized types.
										$input = "<input class='large-text' type='text' name='$name' value='$value'>";
									}
									?>

									<tr>
										<th scope="row">
											<label for="<?php echo \esc_attr( $name ); ?>"><strong><?php echo esc_html( $name ); ?></strong></label>
										</th>
										<td><?php echo $input; ?></td>
									</tr>
									<?php
								}

								?>
							</tbody>
						</table>

						<p class="submit">
							<?php \submit_button( '', 'primary', '', false ); ?>
						</p>
					</form>
				</div>
				<?php
			} else {

				$table->prepare_items();
				$core_table = '';
				if ( in_array( $table_name, Common_Table::get_wp_core_tables(), true ) ) {
					$core_table = ' ( <span class="dashicons dashicons-wordpress" aria-hidden="true" style="vertical-align: middle;"></span> ) ';
				}
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php \esc_html_e( 'Table: ', '0-day-analytics' ); ?><?php echo \wp_kses_post( $core_table ); ?><?php echo \esc_html( $table_name ); ?></h1>
					<?php
					// Build Add New link with nonce.
					$add_new_url = \add_query_arg(
						array(
							'page'       => Table_List::TABLE_MENU_SLUG,
							'action'     => 'add_table_data',
							'show_table' => $table_name,
						),
						\admin_url( 'admin.php' )
					);
					$add_new_url = \wp_nonce_url( $add_new_url, 'add-row' );
					?>
					<a href="<?php echo \esc_url( $add_new_url ); ?>" class="page-title-action">
						<?php \esc_html_e( 'Add New', '0-day-analytics' ); ?>
					</a>
					
					<hr class="wp-header-end">
					<form id="table-filter" method="get">
				<?php

							$page  = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : 1;
							$paged = ( isset( $_GET['paged'] ) ) ? filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1;

							printf( '<input type="hidden" name="page" value="%s" />', \esc_attr( $page ) );
							printf( '<input type="hidden" name="paged" value="%d" />', \esc_attr( $paged ) );

							printf( '<input type="hidden" name="show_table" value="%s" />', \esc_attr( $table_name ) );

							echo '<div style="clear:both; float:right">';
							$table->search_box(
								__( 'Search', '0-day-analytics' ),
								strtolower( $table->get_table_name() ) . '-find'
							);
					echo '</div>';
					$table->display();

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
								<h1><?php \esc_html_e( 'Table row details:', '0-day-analytics' ); ?></h1>
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
													<div class=""><span title="<?php \esc_html_e( 'Copy to clipboard (as raw HTML)', '0-day-analytics' ); ?>" class="dashicons dashicons-clipboard" style="cursor:pointer;font-family: dashicons !important;" aria-hidden="true"></span> <span title="<?php esc_html_e( 'Share', '0-day-analytics' ); ?>" class="dashicons dashicons-share" style="cursor:pointer;font-family: dashicons !important;" aria-hidden="true"></span></div>
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
								path: '/<?php echo \esc_attr( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/get_table_record/<?php echo \esc_attr( $table_name ); ?>/' + id + '/',
								method: 'GET',
								cache: 'no-cache'
							}).then( ( attResp ) => {

								jQuery('.media-modal .http-request-args').html(attResp.mail_body);
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

							navigator.clipboard.writeText(selectedText);
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
		}

		/**
		 * Options Help
		 *
		 * Return help text for options screen
		 *
		 * @return string  Help Text
		 *
		 * @since 1.9.8.1
		 */
		public static function add_help_content_table() {

			$help_text  = '<p>' . __( 'This screen allows you to see all the tables in your Database where your WordPress site is currently running.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can specify how many rows to be shown, or filter and search for given value(s).', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'You can delete rows - keep in mind that this operation is destructive and can not be undone - make a backup first.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'Bulk operations are supported.', '0-day-analytics' ) . '</p>';
			$help_text .= '<p>' . __( 'Use the drop-down to select different table.', '0-day-analytics' ) . '</p>';

			return $help_text;
		}

		/**
		 * Options Help
		 *
		 * Return help text for options screen
		 *
		 * @return string  Help Text
		 *
		 * @since 2.4.1
		 */
		public static function add_config_content_table() {

			if ( '' === Common_Table::get_name() ) {
				$table_name = Common_Table::get_default_table();
				if ( isset( $_REQUEST['show_table'] ) ) {
					if ( \in_array( $_REQUEST['show_table'], Common_Table::get_tables() ) ) {
						$table_name = $_REQUEST['show_table']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					}
				}

				Common_Table::init( $table_name );
			}

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

				if ( isset( $table_info[0]['Row_format'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Row format: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Row_format'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Rows'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Rows: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Rows'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Avg_row_length'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Avg row length: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Avg_row_length'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Data_length'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Data length: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Data_length'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Index_length'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Index length: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Index_length'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Data_free'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Data free: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Data_free'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Auto_increment'] ) ) {
					?>
					<div> <b><?php \esc_html_e( 'Auto increment: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Auto_increment'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Create_time'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Create time: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Create_time'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Update_time'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Update time: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Update_time'] ); ?></span></div>
					<?php
				}

				if ( isset( $table_info[0]['Check_time'] ) ) {
					?>
					<div><b><?php \esc_html_e( 'Check time: ', '0-day-analytics' ); ?></b> <span class="italic"><?php echo \esc_attr( $table_info[0]['Check_time'] ); ?></span></div>
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
										path: '/<?php echo Endpoints::ENDPOINT_ROOT_NAME; ?>/v1/truncate_table/' + tableName,
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
										path: '/<?php echo \esc_attr( Endpoints::ENDPOINT_ROOT_NAME ); ?>/v1/drop_table/' + tableName,
										method: 'DELETE',
										cache: 'no-cache'
									});

									if (attResp.success) {
										
										location.href= '<?php echo \esc_url_raw( Miscellaneous::get_tables_page_link() ); ?>';
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
		 * Responsible for switching the table of the view.
		 *
		 * @return void
		 *
		 * @since 2.1.0
		 */
		public static function switch_action() {

			if ( isset( $_REQUEST['table_filter_top'] ) || isset( $_REQUEST['table_filter_bottom'] ) ) {

				if ( \check_admin_referer( Table_List::SWITCH_ACTION, Table_List::SWITCH_ACTION . 'nonce' ) && \in_array( $_REQUEST['table_filter_top'], Common_Table::get_tables(), true ) ) {
					$table = $_REQUEST['table_filter_top']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

					$context    = isset( $_REQUEST['context'] ) ? \sanitize_text_field( $_REQUEST['context'] ) : 'site';
					$is_network = ( $context === 'network' && \is_multisite() );

					\wp_safe_redirect(
						\remove_query_arg(
							array( 'deleted' ),
							\add_query_arg(
								array(
									'page'       => Table_List::TABLE_MENU_SLUG,
									Table_List::SEARCH_INPUT => Table_List::escaped_search_input(),
									'show_table' => rawurlencode( $table ),
								),
								( ( $is_network ) ? \network_admin_url( 'admin.php' ) : \admin_url( 'admin.php' ) )
							)
						)
					);
					exit;
				}
			}
		}

		/**
		 * Removes unnecessary arguments if present and reloads.
		 *
		 * @return void
		 *
		 * @since 2.3.0
		 */
		public static function page_load() {
			if ( ! empty( $_GET['_wp_http_referer'] ) ) {
				\wp_safe_redirect(
					\remove_query_arg( array( '_wp_http_referer' ), \wp_unslash( $_SERVER['REQUEST_URI'] ) )
				);
				exit;
			}
		}

		/**
		 * Collects all the data from the form and updates the table.
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function update_table() {
			// Capability guard: only allow administrators (or users with equivalent capability).
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage tables.', '0-day-analytics' ) );
			}

			// Bail if malformed Transient request.
			if ( empty( $_REQUEST['record_id'] ) || empty( $_REQUEST['show_table'] ) ) {
				return;
			}

			// Bail if nonce fails.
			if ( empty( $_REQUEST['_wpnonce'] ) || ! WP_Helper::verify_admin_nonce( Table_List::NONCE_NAME ) ) {
				return;
			}

			// Sanitize data.
			$record_id  = \sanitize_key( $_REQUEST['record_id'] );
			$table_name = \sanitize_key( $_REQUEST['show_table'] );

			if ( ! Common_Table::check_table_exists( $table_name ) ) {
				return new \WP_Error( 'table_not_found', 'Table not found.' );
			}

			Common_Table::init( $table_name );

			$columns = Common_Table::get_columns_info();

			$cols_data = array();

			$no_primary_key = true;

			foreach ( $columns as $column ) {
				$name  = \esc_attr( $column['Field'] );
				$extra = strtolower( $column['Extra'] );

				// Skip auto-increment primary key.
				if ( 'auto_increment' === $extra ) {
					$cols_data[ $name ] = $record_id;

					$no_primary_key = false;

					continue;
				}
				if ( isset( $_POST[ $name ] ) ) {
					$cols_data[ $name ] = \wp_unslash( $_POST[ $name ] );
				}
			}

			$where = null;

			if ( $no_primary_key ) {
				$record = Common_Table::load_row_data(
					$record_id
				);

				$where = array(
					Common_Table::get_real_id_name() => $record[ Common_Table::get_real_id_name() ],
				);
			}

			Common_Table::insert_row_record( $table_name, $cols_data, $where );

			$context    = isset( $_REQUEST['context'] ) ? \sanitize_text_field( $_REQUEST['context'] ) : 'site';
			$is_network = ( $context === 'network' && \is_multisite() );

			\wp_safe_redirect(
				\remove_query_arg(
					array( 'deleted' ),
					\add_query_arg(
						array(
							'page'                   => Table_List::TABLE_MENU_SLUG,
							'paged'                  => ( isset( $_POST['paged'] ) ) ? filter_input( INPUT_POST, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1,
							Table_List::SEARCH_INPUT => ( isset( $_POST[ Table_List::SEARCH_INPUT ] ) ) ? \sanitize_text_field( \wp_unslash( $_POST[ Table_List::SEARCH_INPUT ] ) ) : '',
							'updated'                => true,
							'show_table'             => $table_name,
							'event_type'             => ( isset( $_REQUEST['event_type'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['event_type'] ) ) : '' ),
						),
						( ( $is_network ) ? \network_admin_url( 'admin.php' ) : \admin_url( 'admin.php' ) )
					)
				)
			);
			exit;
		}

		/**
		 * Collects all the data from the form and inserts a new row.
		 *
		 * @return void|\WP_Error
		 *
		 * @since 4.4.0
		 */
		public static function insert_table() {
			// Capability guard: only allow administrators (or users with equivalent capability).
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage tables.', '0-day-analytics' ) );
			}

			// Bail if malformed request.
			if ( empty( $_REQUEST['show_table'] ) ) {
				\wp_die( \esc_html__( 'Missing table name.', '0-day-analytics' ) );
			}

			// Bail if nonce fails.
			if ( empty( $_REQUEST['_wpnonce'] ) || ! \wp_verify_nonce( \wp_unslash( $_REQUEST['_wpnonce'] ), Table_List::NONCE_NAME ) ) {
				\wp_die( \esc_html__( 'Security check failed.', '0-day-analytics' ) );
			}

			// Sanitize data.
			$table_name = \sanitize_key( $_REQUEST['show_table'] );

			if ( ! Common_Table::check_table_exists( $table_name ) ) {
				return new \WP_Error( 'table_not_found', 'Table not found.' );
			}

			Common_Table::init( $table_name );

			$columns = Common_Table::get_columns_info();

			$cols_data = array();

			foreach ( $columns as $column ) {
				$name  = \esc_attr( $column['Field'] );
				$extra = strtolower( $column['Extra'] );

				// Skip auto-increment primary key - it will be generated.
				if ( 'auto_increment' === $extra ) {
					continue;
				}

				// Only add data if field is present in POST.
				if ( isset( $_POST[ $name ] ) ) {
					$cols_data[ $name ] = \wp_unslash( $_POST[ $name ] );
				}
			}

			// Insert without $where parameter - this creates a new row.
			Common_Table::insert_row_record( $table_name, $cols_data );

			$context    = isset( $_REQUEST['context'] ) ? \sanitize_text_field( $_REQUEST['context'] ) : 'site';
			$is_network = ( 'network' === $context && \is_multisite() );

			\wp_safe_redirect(
				\remove_query_arg(
					array( 'deleted' ),
					\add_query_arg(
						array(
							'page'                   => Table_List::TABLE_MENU_SLUG,
							'paged'                  => ( isset( $_POST['paged'] ) ) ? filter_input( INPUT_POST, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1,
							Table_List::SEARCH_INPUT => ( isset( $_POST[ Table_List::SEARCH_INPUT ] ) ) ? \sanitize_text_field( \wp_unslash( $_POST[ Table_List::SEARCH_INPUT ] ) ) : '',
							'inserted'               => true,
							'show_table'             => $table_name,
							'event_type'             => ( isset( $_REQUEST['event_type'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['event_type'] ) ) : '' ),
						),
						( ( $is_network ) ? \network_admin_url( 'admin.php' ) : \admin_url( 'admin.php' ) )
					)
				)
			);
			exit;
		}
	}
}
