<?php

class WP_Data_Logger{

	protected static $instance;
	protected static $table_name;
	protected static $action = 'wp-data-logger--clear-log';

	public static function get_instance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct(){
		global $wpdb;

		add_action( 'admin_menu', function(){
			add_management_page(
				$page_title = 'WP Logger',
				$menu_title = 'WP Logger',
				$capability = 'manage_options',
				$menu_slug = 'logger',
				$func = array( $this, 'display_page' )
			);
		});

		self::$table_name = $wpdb->get_blog_prefix() . 'data_logger';

		add_action( 'upgrader_process_complete', array( $this, 'upgrader' ), 10, 2 );

		add_filter( 'plugin_action_links_' . WPDL_PLUGIN_NAME, array( $this, 'add_settings_link' ) );
		add_action( "wp_ajax_" . self::$action, array( $this, 'clear_log' ) );

		add_action( 'logger', array( $this, 'add' ), 0, 2 );

		//self::check_installation();
	}

	public function activation(){
		self::check_installation();
	}

	public function upgrader( $upgrader_object = null, $options = [] ){
		if ( ! empty( $upgrader_object ) &&
		     (
			     @$options['action'] != 'update'
			     || @$options['type'] != 'plugin'
			     || empty( $options['plugins'] )
			     || ! in_array( WPDL_PLUGIN_NAME, @$options['plugins'] )
		     )
		) return;

		self::check_installation();
		//self::remove_old_data();
	}

	public function check_installation( $upgrader_object = null, $options = [] ){
		self::$instance->db_delta();
	}

	function db_delta(){
		global $wpdb;
		$table = self::$table_name;

		$query =
			"CREATE TABLE {$table} (
			ID  int(11) unsigned NOT NULL auto_increment,
			status varchar(255) NOT NULL default '',
			date datetime NULL DEFAULT CURRENT_TIMESTAMP,
			content longtext NULL default '',
			PRIMARY KEY (ID)
		)
		DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate};";

		dbDelta( $query );
		return true;
	}

	public function remove_old_data(){
		delete_option( 'logger_u7' );
	}

	function display_page(){
		global $wpdb;

		$default_limit = 200;
		$limit = ( ! defined( 'WPDL_DISPLAY_LIMIT' ) || ! is_numeric( WPDL_DISPLAY_LIMIT ) || WPDL_DISPLAY_LIMIT < 1 ) ? $default_limit : WPDL_DISPLAY_LIMIT;

		echo	'<h1>Log</h1>';
		echo	"<p>For adding data to log use the hook: <code>do_action( 'logger', \$data, \$status = 'warning' );</code></p>";
		echo 	'<a class="button log_status_selector status_all" data-status="all">ALL</a>'.
		        '<a class="button log_status_selector status_info" data-status="info">INFO</a>'.
		        '<a class="button log_status_selector status_warning" data-status="warning">WARNING</a>'.
		        '<a class="button log_status_selector status_error" data-status="error">ERROR</a>'.
		        apply_filters( 'wp_logger_button_panel', '' );

		echo	'<a class="button clear_log button-link-delete" href="" data-status="info">Clear INFO</a>' .
		        '<a class="button clear_log button-link-delete" href="" data-status="warning">Clear WARNING</a>' .
		        '<a class="button clear_log button-link-delete" href="" data-status="error">Clear ERROR</a>' .

		        '<a class="button clear_log button-link-delete" href="" data-status="all">Clear Log</a>' ;

		$suppress = $wpdb->suppress_errors( true );
		$data = $wpdb->get_results( 'SELECT * FROM '. self::$table_name . ' ORDER BY ID DESC LIMIT ' . $limit, ARRAY_A );
		$wpdb->suppress_errors( $suppress );
		if ( empty( $data ) || ! is_array( $data ) ):
			echo '<p>There is no data in the log</p>';
			return;
		endif;

		$styles = apply_filters( 'wp_logger_inline_css', self::print_css() );
		echo apply_filters( 'wp_logger_inline_js', self::print_js() );
		?>
		<style>
			<?php echo $styles; ?>
		</style>
		<table border="1" width="100%" class="logger_table">
			<thead>
			<tr>
				<th>№</th>
				<th>Status</th>
				<th class="th_data">Data <span class="hide_data"><span class="hide_all">[Hide all]</span> <span class="show_all">[Show all]</span></span></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$i = 0;
			foreach( $data as $k => $item ):
				$i++;
				$status = 'all ' . @$item['status'];

				ob_start();
				var_dump( maybe_unserialize( @$item['content'] ) );
				$content = esc_html( ob_get_clean() );
				?>
				<tr class="data <?php echo $status;?>" id="loggerDataRow_<?php echo $i; ?>">
					<td width="100px" class="num_column dblClickToScroll" >
						<span><span class="prev">[prev]</span><b>[<?php echo $k + 1; ?>]</b><span class="next">[next]</span></span>
					</td>
					<td class="status_column" align="center"><span><?php echo @$item['status'];?></span></td>
					<td class="data_column">
						<span class="timestamp"><?php echo "[{$item['date']}]"; ?></span> <span class="hide_data">[<span class="is_visible">Hide this</span><span class="is_hidden">Show this</span>]</span>
						<pre class="data_content"><?php echo apply_filters( 'wp_data_logger_print_data', $content ); ?></pre>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	function add( $data = '', $status = 'info' ){
		global $wpdb;
		$suppress = $wpdb->suppress_errors( true );
		$i = 0;
		do{
			$result = $wpdb->insert(
				self::$table_name,
				array( 'status' => $status, 'content' => maybe_serialize( $data ) )
			);
		} while( empty( $result ) && $this->db_delta() && $i++ < 1 );
		$wpdb->suppress_errors( $suppress );
		if ( empty( $result) ) error_log( '[WP DATA LOGGER] Error while DB table creating' );
	}

	/**
	 * Add fast link in plugins list
	 */
	function add_settings_link( $links ){
		$settings_link = '<a href="tools.php?page=logger">View WP Logger</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function clear_log(){
		global $wpdb;

		check_ajax_referer( self::$action );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( json_encode( new \WP_Error( 'no_permission', 'You have no permission to do this', array( 'status' => 403 ) ) ) );
		}

		if ( $_POST['status'] == 'all' ) :
			$wpdb->query( 'TRUNCATE ' . self::$table_name );
		elseif ( ! empty( $_POST['status'] ) ) :
			$wpdb->delete( self::$table_name, array( 'status' => $_POST['status'] ) );
		endif;
		wp_die( json_encode( array( 'done' => true ) ) );
	}

	function update_message( $data, $response ) {
		if( isset( $data['upgrade_notice'] ) ) :
			printf(
				'<div class="update-message">%s</div>',
				wpautop( $data['upgrade_notice'] )
			);
		endif;

		if ( ! defined( 'WPDL_VERSION' ) || version_compare( WPDL_VERSION, '2.0', '<' ) ) :
			printf(
				'<div class="update-message">%s</div>',
				wpautop( 'During update will be lost all logged data. Please, make sure this won\'t hurt you.' )
			);
		endif;
	}

	function print_css(){
		$css = "
		.dblClickToScroll{ 
			vertical-align: top;
			text-align: center;
		}
		.dblClickToScroll .prev,
		.dblClickToScroll .next{
			cursor : pointer;
			font-size: 10px;
		}
		.logger_table{
		    margin-top: 6px;
		}
		.logger_table .status_column{
			font-weight: 900;
			vertical-align: top;
		}
		.logger_table .warning .status_column span{
			color: #803600;
		}
		.logger_table .error .status_column span{
			color : #F00;
		}
		.logger_table .num_column span{}
		.logger_table .data_column span.timestamp{
			font-size: 10px;
		}
		.logger_table .data_column .hide_data,
		.logger_table .th_data .hide_data{
			cursor: pointer;
			font-family: monospace;
			font-size: 10px;
		}
		.logger_table .data_column .hide_data .is_hidden{
			display : none;
		}
		.logger_table .data_column .hide_data.hide .is_hidden{
			display : initial;
		}
		.logger_table .data_column .hide_data.hide .is_visible{
			display : none;
		}
		.logger_table .data_column .hide_data.hide + pre{
			display : none;
		}
		.logger_table .data_column pre{
			margin: 2px 0;
		}

		a.button.log_status_selector,
		a.button.clear_log {
			margin: 0 0 0 4px;
		}
		a.button.clear_log{
			float : right;
		}
		a.delimiter {
			width: 1px;
			margin: 0 20px;
		}";
		return $css;

	}

	function print_js(){
		$js = "

		<script>
			jQuery( '.log_status_selector' ).click( function( eventObject ){
				eventObject.preventDefault();
				var status = jQuery( this ).data( 'status' );
				if ( status == 'all' ){
					jQuery( '.logger_table tbody tr' ).show()
				} else {
					jQuery( '.logger_table tbody tr' ).hide()
					jQuery( '.logger_table tbody tr.' + status ).show()
				}
			})
				
			jQuery( '.clear_log' ).click( function( eventObject ){
				eventObject.preventDefault();
				var btn = jQuery( this );
				if ( ! confirm( 'Do you wish really do this?' ) ) return;
				jQuery.post( 
					'". get_admin_url() ."/admin-ajax.php',
					{ 
                        action : '". self::$action ."', 
                        status : btn.data( 'status' ),
                        _ajax_nonce : '". wp_create_nonce( self::$action ) ."'
                    },
					function( response ){
						response = JSON.parse( response );
						if ( response.done == true ){
							jQuery( '.logger_table tr.data.' + btn.data( 'status' ) ).remove();
						}
					}
				);
			} )
			
			jQuery( '#wpbody-content' ).on( 'click', '.dblClickToScroll .prev', function(){
				var prevRow = jQuery( this ).closest( 'tr.data' ).prev( 'tr.data' );
				if ( prevRow.length )
				jQuery('body,html').animate({scrollTop: prevRow.offset().top - 60}, 600);
			} );
			
			jQuery( '#wpbody-content' ).on( 'click', '.dblClickToScroll .next', function(){
				var nextRow = jQuery( this ).closest( 'tr.data' ).next( 'tr.data' );
				if ( nextRow.length )
				jQuery('body,html').animate({scrollTop: nextRow.offset().top - 60}, 600);
			} );
			
			jQuery( '#wpbody-content' ).on( 'click', '.data_column .hide_data', function(){
				jQuery( this ).toggleClass( 'hide' );
			});
			
			jQuery( '#wpbody-content' ).on( 'click', '.th_data .hide_all', function(){
				jQuery( '.data_column .hide_data' ).addClass( 'hide' );
			});
			
			jQuery( '#wpbody-content' ).on( 'click', '.th_data .show_all', function(){
				jQuery( '.data_column .hide_data' ).removeClass( 'hide' );
			});
			
		</script>
		";

		return $js;
	}
	/**
	 * Add button to WP Logger plugin page
	 *
	 * @param string $name Button name
	 * @param callable $clb Callback function
	 * @param string $btnClass Button class. Default is empty (will be generated).
	 */
	public static function add_panel_button( string $name, callable $clb, string $btnClass = '' ) {
		// Define $action as a unique random action name
		$action = 'wpdl_' . md5( serialize( [ $name, $btnClass ] ) );

		$btnClass = $btnClass ?: $action;

		add_action( "wp_ajax_$action", function () use ( $name, $action, $clb ) {
			check_ajax_referer( $action );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( json_encode( new \WP_Error( 'no_permission', 'You have no permission to do this', array( 'status' => 403 ) ) ) );
			}

			do_action( 'logger', [ 'button-execution-started' => $name ]) ;

			try {
				call_user_func( $clb );
			} catch ( \Exception|\Error $e ) {
				do_action( 'logger', [ 'button' => $name, 'exception' => $e ], 'error' );

				throw $e;
			}

			do_action( 'logger', [ 'button-execution-finished' => $name ]) ;
		} );

		add_filter( 'wp_logger_button_panel', function() use ( $name, $btnClass, $action  ){
			return
				"
		<a class='button button-primary $btnClass' href=''>$name</a>
		<script>
			jQuery( 'a.$btnClass' ).click( function( eo ){
				eo.preventDefault();
				if ( ! confirm( 'Do $name start?' ) ) return;
				jQuery.post( 
					'". get_admin_url() ."/admin-ajax.php',
					{ 
                        action : '$action',
                        _ajax_nonce : '". wp_create_nonce( $action ) ."'
                    },
					function( response ){}
				);
			})
		</script>";
		} );
	}
}