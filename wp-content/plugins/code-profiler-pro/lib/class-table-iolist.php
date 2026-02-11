<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

// =====================================================================

class CodeProfilerPro_Table_IOList extends WP_List_Table {

	public  $id;
	public  $profile_path;
	public  $section;
	public  $is_empty;
	private $nonce;
	private $show_paths;
	private $table_max_rows;
	private $abspath;

	function __construct( $section, $id, $profile_path ) {

		$this->section = $section;
		$this->id = $id;
		$this->profile_path = $profile_path;

		$this->abspath = rtrim( ABSPATH, '/\\' );

		$cp_options = get_option( 'code-profiler-pro' );

		if ( empty( $cp_options['show_paths'] ) || ! in_array( $cp_options['show_paths'], ['absolute', 'relative' ] ) ) {
			$this->show_paths = 'relative';
		} else {
			$this->show_paths = $cp_options['show_paths'];
		}
		if ( empty( $cp_options['table_max_rows'] ) || ! preg_match( '/^\d+$/', ( $cp_options['table_max_rows'] ) ) ) {
			$this->table_max_rows = 30;
		} else {
			$this->table_max_rows = $cp_options['table_max_rows'];
		}

		$this->nonce = wp_create_nonce( 'code-profile-view-file' );

		parent::__construct( array(
			'singular'  => esc_html__( 'iolist', 'code-profiler-pro' ),
			'plural'    => esc_html__( 'iolists', 'code-profiler-pro' ),
			'ajax'      => false
		));
    }


	/*
	 * Empty list
	 */
	function no_items() {
		esc_html_e( 'No records were found.', 'code-profiler-pro' );
		$this->is_empty = 1;
	}

	/*
	 * Default
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'mode':
				return str_replace( [ '[', ']' ], [ '<code>', '</code>' ], $item[ $column_name ] );
			case 'order':
			case 'file':
				return $item[ $column_name ];
				break;
			default:
				return '';
		}
	}

	/*
	 * Search box
	 */
	public function search_box( $text, $input_id ) {
		if (! empty( $_REQUEST['c'] ) ) {
			$_REQUEST['c'] = 1;
		} else {
			$_REQUEST['c'] = 0;
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text ?>:</label>
			<input type="search" id="<?php echo $input_id ?>-search-input" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( esc_attr__('Filter', 'code-profiler-pro'), 'button', false, false, array('id' => 'search-submit') ); ?>
			<br />
			<label><input type="checkbox" id="case-search-input" name="c" value="1"<?php checked( $_REQUEST['c'], 1 ) ?> /> <?php esc_html_e('Case sensitive', 'code-profiler-pro') ?></label>
		</p>
		<?php
	}

	/*
	 * Sortable columns
	 */
	function get_sortable_columns() {
		return [
			'order'	=> array( 'order', true ),
			'file'	=> array( 'file', true ),
			'mode'	=> array( 'mode', true )
		];
	}

	/*
	 * Columns
	 */
	function get_columns(){
		return [
			'order'	=> esc_html__( 'Order', 'code-profiler-pro' ),
			'file'	=> esc_html__( 'File', 'code-profiler-pro' ),
			'mode'	=> esc_html__( 'Operation', 'code-profiler-pro' )
		];
    }

	/*
	 * Sorting
	 */
	function usort_reorder( $a, $b ) {
		$orderby = (! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'order';
		$order = (! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'asc';
		$result = $this->cmp_num_or_string( $a[$orderby], $b[$orderby] );
		return ( $order === 'asc' ) ? $result : -$result;
	}

	/*
	 * Sort string and numeric values differently
	 */
	function cmp_num_or_string( $a, $b ) {
		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			return ($a-$b) ? ($a-$b)/abs($a-$b) : 0;
		} else {
			return strcmp( $a, $b );
		}
	}


	/*
	 * Row action links
	 */
	function column_file( $item ) {

		// Check if its a rename operation
		if ( preg_match( '`^.+? => (.+?)$`', $item['file'], $match ) ) {
			$file = $match[1];
		} else {
			$file = $item['file'];
		}
		// Check if we have an absolute or relative path (but discard files
		// located outside the ABSPATH that have an absolute path, e.g. /tmp/foo.txt).
		// @is_file is used to silence PHP notices if there's a basedir restriction.
		if ( strpos( $file, $this->abspath ) !== 0 && ! @is_file( $file ) ) {
			$file = $this->abspath ."/$file";
		}
		// Make sure the item is a file and exists
		if ( is_file( $file ) ) {
			$oc = "style=\"cursor:pointer\" onClick=\"cpjspro_file_view('".
					base64_encode( $file ) ."','{$this->nonce}','','')\"";
			$actions = array(
				'view' => sprintf( '<a %s>%s</a>', $oc,
							esc_html__('View file', 'code-profiler-pro')
				)
			);
		} else {
			$actions = array(
				'view' => '<font style="color:#50575e">'.
							esc_html__('N/A', 'code-profiler-pro').
							'</font>'
			);
		}
		return sprintf( '%1$s %2$s', $item['file'], $this->row_actions( $actions ) );
	}

	/*
	 * Prepare to display profiles
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Fetch our data
		$profile = $this->fetch_iolist();

		usort( $profile, array( &$this, 'usort_reorder' ) );

		$per_page = $this->table_max_rows;

		$current_page = $this->get_pagenum();
		$total_items = count( $profile );
		$this->items = array_slice( $profile,( ( $current_page-1 )* $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		));
	}

	/*
	 * Retrieve all profiles
	 */
	function fetch_iolist() {

		$buffer = [];
		$count = 0;

		$fh = fopen( "{$this->profile_path}.iolist.profile", 'rb' );
		if ( $fh === false ) {
			wp_die( sprintf(
				esc_html__('Cannot open profile file: %s', 'code-profiler-pro'),
				"{$this->profile_path}.iolist.profile"
			));
		}
		// Case sensitivity
		if (! empty( $_REQUEST['c'] ) ) {
			$search = 'strpos';
		} else {
			$search = 'stripos';
		}
		while (! feof( $fh ) ) {

			$tmp = [];
			$line = fgets( $fh );
			// Search query
			if (! empty( $_REQUEST['s'] ) ) {
				// Remove the ABSPATH, we don't include it in the search
				$line = ltrim( str_replace( $this->abspath, '', $line ), '\\/');
				if ( $search( $line, $_REQUEST['s'] ) === false ) {
					continue;
				}
			}

			$tmp = explode( "\t", $line );

			if ( isset( $tmp[0] ) && isset( $tmp[1] ) && isset( $tmp[2] ) ) {
				$buffer[$count]['order'] = $tmp[0];

				// Absolute or relative paths (but don't modify files located
				// outside the ABSPATH that have an absolute path, e.g. /tmp/foo.txt)
				if ( $this->show_paths == 'relative' && strpos( $tmp[1], $this->abspath ) === 0 ) {
					$buffer[$count]['file'] = ltrim( str_replace( $this->abspath, '', $tmp[1] ), '\\/');
				} else {
					$buffer[$count]['file'] = $tmp[1];
				}

				$buffer[$count]['mode'] = $tmp[2];
				$count++;
			}
		}
		fclose( $fh );
		return $buffer;
	}

}

// =====================================================================
// EOF
