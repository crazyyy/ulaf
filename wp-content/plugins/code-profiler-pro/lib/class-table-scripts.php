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

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================

class CodeProfilerPro_Table_Scripts extends WP_List_Table {

	public  $id;
	public  $profile_path;
	public  $section;
	private $total_time;
	public  $is_empty;
	public  $hidden;

	private $nonce;
	private $show_paths;
	private $display_name;
	private $truncate_name;
	private $hide_empty_value;
	private $table_max_rows;
	private $abspath;


	/**
	 * Initialize.
	 */
	function __construct( $section, $id, $profile_path ) {

		$this->section = $section;
		$this->id = $id;
		$this->profile_path = $profile_path;

		$this->abspath = rtrim( ABSPATH, '/\\');

		$cp_options = get_option('code-profiler-pro');

		if ( empty( $cp_options['show_paths'] ) ||
			! in_array( $cp_options['show_paths'], ['absolute', 'relative'] )
		) {
			$this->show_paths = 'relative';
		} else {
			$this->show_paths = $cp_options['show_paths'];
		}
		if ( empty( $cp_options['display_name'] ) ||
			! in_array( $cp_options['display_name'], ['full', 'slug'] )
		) {
			$this->display_name = 'full';
		} else {
			$this->display_name = $cp_options['display_name'];
		}
		if ( empty( $cp_options['truncate_name'] ) ||
			! preg_match('/^\d+$/', ( $cp_options['truncate_name'] ) )
		) {
			$this->truncate_name = 30;
		} else {
			$this->truncate_name = $cp_options['truncate_name'];
		}
		if (! empty( $cp_options['hide_empty_value'] ) ) {
			$this->hide_empty_value = 1;
		} else {
			$this->hide_empty_value = 0;
		}
		if ( empty( $cp_options['table_max_rows'] ) ||
			! preg_match('/^\d+$/', ( $cp_options['table_max_rows'] ) )
		) {
			$this->table_max_rows = 30;
		} else {
			$this->table_max_rows = $cp_options['table_max_rows'];
		}

		$this->nonce = wp_create_nonce('code-profile-view-file');

		parent::__construct([
			'singular' => esc_html__('script', 'code-profiler-pro'),
			'plural'   => esc_html__('scripts', 'code-profiler-pro'),
			'ajax'     => false
		]);
	}


	/**
	 * Empty list.
	 */
	function no_items() {
		esc_html_e('No records were found.', 'code-profiler-pro');
		$this->is_empty = 1;
	}


	/**
	 * Default.
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'name':
			case 'script':
				return esc_html( $item[ $column_name ] );
				break;
			case 'time':
				$pc = ceil ( ( $item[ $column_name ] / $this->total_time ) * 100 );
				$item[ $column_name ] = "<div class='cp-list-pc' style='cursor:help' title='".
						sprintf(
							esc_html__('%s%% of all scripts (plugins and theme)', 'code-profiler-pro'),
							$pc
						).	"'><div class='cp-list-pc-bar' style='width:{$pc}%'></div><center>".
						esc_html( $item[ $column_name ] ) ."</center></div>";
				return $item[ $column_name ];
				break;
			default:
				return '';
		}
	}


	/**
	 * Search box.
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


	/**
	 * Sortable columns.
	 */
	function get_sortable_columns() {
		return [
			'script'	=> ['script', true ],
			'time'	=> ['time', true ],
			'name'	=> ['name', true ]
		];
	}


	/**
	 * Columns.
	 */
	function get_columns(){
		return [
			'script'	=> esc_html__('Script', 'code-profiler-pro'),
			'time'	=> esc_html__('Time (sec)', 'code-profiler-pro'),
			'name'	=> esc_html__('Component', 'code-profiler-pro')
		];
    }


	/**
	 * Sorting.
	 */
	function usort_reorder( $a, $b ) {
		$orderby = (! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'time';
		$order = (! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$result = $this->cmp_num_or_string( $a[$orderby], $b[$orderby] );
		return ( $order === 'asc' ) ? $result : -$result;
	}


	/**
	 * Sort string and numeric values differently.
	 */
	function cmp_num_or_string( $a, $b ) {
		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			return ($a-$b) ? ($a-$b)/abs($a-$b) : 0;
		} else {
			return strcmp( $a, $b );
		}
	}


	/**
	 * Row action links.
	 */
	function column_script( $item ) {

		// Check if we have an absolute or relative path
		if ( strpos( $item['script'], $this->abspath ) === 0 ) {
			$file = $item['script'];
		} else {
			$file = $this->abspath ."/{$item['script']}";
		}
		// Make sure the item is a file and exists
		if ( is_file( $file ) ) {
			$oc = "style=\"cursor:pointer\" onClick=\"cpjspro_file_view('".
					base64_encode( $file ) ."','{$this->nonce}','','')\"";
			$actions = array(
				'view' => sprintf( '<a %s>%s</a>', $oc,
							esc_html__('View script', 'code-profiler-pro')
				)
			);
		} else {
			$actions = array(
				'view' => '<font style="color:#50575e">'.
							esc_html__('N/A', 'code-profiler-pro').
							'</font>'
			);
		}
		return sprintf('%1$s %2$s', $item['script'], $this->row_actions( $actions ) );

	}


	/**
	 * Prepare to display profiles.
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Fetch our data
		$profile = $this->fetch_scripts();
		if ( isset( $profile['error'] ) ) {
			return $profile['error'];
		}

		usort( $profile, array( &$this, 'usort_reorder') );

		$per_page = $this->table_max_rows;

		$current_page = $this->get_pagenum();
		$total_items = count( $profile );
		$this->items = array_slice( $profile,( ( $current_page-1 )* $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		));
	}


	/**
	 * Retrieve all profiles.
	 */
	function fetch_scripts() {

		$buffer = [];
		$count  = 0;

		$fh = fopen( "{$this->profile_path}.scripts.profile", 'rb' );
		if ( $fh === false ) {
			wp_die( sprintf(
				esc_html__('Cannot open profile file: %s', 'code-profiler-pro'),
				"{$this->profile_path}.scripts.profile"
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
			$line = rtrim( fgets( $fh ) );
			$tmp = explode( "\t", $line );

			if (! isset( $tmp[0] ) || ! isset( $tmp[1] ) || ! isset( $tmp[2] ) || ! isset( $tmp[3] ) ) {
				continue;
			}
			// We don't display but count empty values
			if ( (empty( $tmp[2] ) || $tmp[2] == 0) && $this->hide_empty_value == 1 ) {
				$this->hidden++; continue;
			}

			// Search query
			if (! empty( $_REQUEST['s'] ) ) {
				// Remove the ABSPATH, we don't include it in the search
				$line = ltrim( str_replace( $this->abspath, '', $line ), '\\/');
				if ( $search( $line, $_REQUEST['s'] ) === false ) {
					// We must get the total time for all records, not only the filtered ones
					$this->total_time += $tmp[2];
					continue;
				}
			}

			// Display full name or slug
			if ( $this->display_name == 'full' ) {
				$buffer[$count]['name'] = $tmp[3];
			} else {
				$buffer[$count]['name'] = $tmp[0];
			}
			// Truncate names if needed
			if ( strlen( $buffer[$count]['name'] ) > $this->truncate_name ) {
				$buffer[$count]['name'] = mb_substr( $buffer[$count]['name'], 0, $this->truncate_name , 'utf-8') .'...';
			}
			if ( $tmp[4] == 'mu-plugin' ) {
				$buffer[$count]['name'] .= ' (MU)';
			}

			// Absolute or relative paths
			if ( $this->show_paths == 'relative' ) {
				$buffer[$count]['script'] = ltrim( str_replace( $this->abspath, '', $tmp[1] ), '\\/');
			} else {
				$buffer[$count]['script'] = $tmp[1];
			}
			$buffer[$count]['time'] = $tmp[2];
			$this->total_time += $tmp[2];
			$count++;
		}
		fclose( $fh );
		return $buffer;
	}

}

// =====================================================================
// EOF
