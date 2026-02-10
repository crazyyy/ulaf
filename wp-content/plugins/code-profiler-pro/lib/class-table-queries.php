<?php
/**
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

if (! defined('ABSPATH') ) { die('Forbidden'); }

// =====================================================================

class CodeProfilerPro_Table_Queries extends WP_List_Table {

	public  $id;
	public  $profile_path;
	public  $section;
	private $total_time;
	public  $is_empty;
	public  $hidden;
	private $nonce;
	private $show_paths;
	private $truncate_queries;
	private $display_name;
	private $truncate_name;
	private $hide_empty_value;
	private $table_max_rows;
	private $abspath;
	private $row_count;

	function __construct( $section, $id, $profile_path ) {

		$this->section = $section;
		$this->id = $id;
		$this->profile_path = $profile_path;

		$this->abspath = rtrim( ABSPATH, '/\\');

		$cp_options = get_option('code-profiler-pro');

		if ( empty( $cp_options['show_paths'] ) || ! in_array( $cp_options['show_paths'], ['absolute', 'relative'] ) ) {
			$this->show_paths = 'relative';
		} else {
			$this->show_paths = $cp_options['show_paths'];
		}
		if ( empty( $cp_options['display_name'] ) || ! in_array( $cp_options['display_name'], ['full', 'slug'] ) ) {
			$this->display_name = 'full';
		} else {
			$this->display_name = $cp_options['display_name'];
		}
		if ( empty( $cp_options['truncate_name'] ) || ! preg_match('/^\d+$/', ( $cp_options['truncate_name'] ) ) ) {
			$this->truncate_name = 30;
		} else {
			$this->truncate_name = $cp_options['truncate_name'];
		}
		if ( empty( $cp_options['truncate_queries'] ) || ! preg_match('/^\d+$/', ( $cp_options['truncate_queries'] ) ) ) {
			$this->truncate_queries = 500;
		} else {
			$this->truncate_queries = $cp_options['truncate_queries'];
		}
		if (! empty( $cp_options['hide_empty_value'] ) ) {
			$this->hide_empty_value = 1;
		} else {
			$this->hide_empty_value = 0;
		}
		if ( empty( $cp_options['table_max_rows'] ) || ! preg_match('/^\d+$/', ( $cp_options['table_max_rows'] ) ) ) {
			$this->table_max_rows = 30;
		} else {
			$this->table_max_rows = $cp_options['table_max_rows'];
		}

		$this->nonce = wp_create_nonce('code-profile-view-file');

		parent::__construct( array(
			'singular'  => esc_html__('query', 'code-profiler-pro'),
			'plural'    => esc_html__('queries', 'code-profiler-pro'),
			'ajax'      => false
		));
    }


	/**
	 * Empty list
	 */
	function no_items() {
		esc_html_e('No records were found.', 'code-profiler-pro');
		$this->is_empty = 1;
	}

	/**
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

	/**
	 * Sortable columns
	 */
	function get_sortable_columns() {
		return array(
			'query'    => array('query', true ),
			'time'     => array('time', true ),
			'order'    => array('order', true ),
			'name'     => array('name', true )
		);
	}

	/**
	 * Columns
	 */
	function get_columns(){
		return array(
			'query'    => esc_html__('Query', 'code-profiler-pro'),
			'time'	  => esc_html__('Time (sec)', 'code-profiler-pro'),
			'order'    => esc_html__('Order', 'code-profiler-pro'),
			'name'     => esc_html__('Component', 'code-profiler-pro')
		);
    }

	/**
	 * Sorting
	 */
	function usort_reorder( $a, $b ) {
		$orderby = (! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'time';
		$order = (! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$result = $this->cmp_num_or_string( $a[$orderby], $b[$orderby] );
		return ( $order === 'asc') ? $result : -$result;
	}

	/**
	 * Sort string and numeric values differently
	 */
	function cmp_num_or_string( $a, $b ) {
		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			return ($a-$b) ? ($a-$b) / abs($a-$b) : 0;
		} else {
			return strnatcmp( $a, $b );
		}
	}


	/**
	 * Build the table rows
	 *
	 */
	function single_row( $a_comment ) {

		// $a_comment data is already sanitized

		$this->row_count++;
		if ( $this->row_count % 2 == 1 ) {
			$bgcolor = "#F9F9F9";
		} else {
			$bgcolor = "#FFFFFF";
		}
		echo "<tr style='background-color:$bgcolor;'>";

		$columns		= $this->get_columns();

		// Prepare and echo each cell of the row
		$this->echo_query_cell( $a_comment, $columns['query'] );
		$this->echo_time_cell( $a_comment, $columns['time'] );
		$this->echo_order_cell( $a_comment, $columns['order'] );
		$this->echo_name_cell( $a_comment, $columns['name'] );
		echo '</tr>';

		// Backtrace's row
		echo '<tr id="callers-'. $this->row_count .'" style="display:none;background-color:'. $bgcolor .'"><td id="colspan-'. $this->row_count .'" colspan="3">';

		$stack_buffer = unserialize( $a_comment['stack'] );

		// Sorting order
		krsort( $stack_buffer );
		echo '<ol reversed>';

		foreach( $stack_buffer as $k => $v ) {

			if ( is_string( $v['n'] ) ) {
				echo "<li>". esc_html( $v['n'] ) ."</li>";

			} else {
				if ( $v['n']['l'] == 1 ) {
					$tooltip = esc_attr__('Click to view the script', 'code-profiler-pro');
				} else {
					$tooltip = esc_attr__('Click to view the function', 'code-profiler-pro');
				}

				// Absolute or relative paths
				if ( $this->show_paths == 'relative' ) {
					$v['n']['n'] = ltrim( str_replace( $this->abspath, '', $v['n']['n'] ), '\\/');
				}
				echo "<li><a style='cursor:pointer' onClick=\"cpjspro_file_view('".
						base64_encode( $v['n']['f'] ) ."','{$this->nonce}','','". (int) $v['n']['l'] ."')\" ".
						"title='". $tooltip ."'>". esc_html( $v['n']['n'] ) ."</a></li>";
			}
		}
		echo '</ol></td></tr>';

	}

	/**
	 * Create the "Order" cell
	 */
	function echo_order_cell( $a_comment, $name ) {

		echo "<td class='order column-order' data-colname='". $name ."'>". (int) $a_comment['order'] ."</td>";

	}

	/**
	 * Create the "Query" cell
	 */
	function echo_query_cell( $a_comment, $name ) {

		if ( strlen( $a_comment['query'] ) > $this->truncate_queries ) {
			$query = mb_substr( $a_comment['query'], 0, $this->truncate_queries, 'utf-8') . ' [...]';
		} else {
			$query = $a_comment['query'];
		}

		$actions['view'] = '<a style="cursor:pointer" onClick="cpjspro_toggle_caller(\''.
								$this->row_count .'\', 4)">'.
								esc_html( __('View backtrace', 'code-profiler-pro') ) .'</a>';

		printf(
			'<td class="query column-query column-primary" data-colname="'. $name .'">%1$s %2$s</td>',
			// Beware of invalid code unit sequences
			htmlspecialchars( $query, ENT_SUBSTITUTE ),
			$this->row_actions( $actions )
		);
	}


	/**
	 * Create the "Time" cell
	 */
	function echo_time_cell( $a_comment, $name ) {

		$pc = ceil ( ( $a_comment['time'] / $this->total_time ) * 100 );

		$time = number_format( $a_comment['time'], 6 );

		echo "<td class='time column-time' data-colname='". $name ."'><div class='cp-list-pc' style='cursor:help' title='".
			sprintf(
				esc_attr__('%s%% of all queries (plugins and theme)', 'code-profiler-pro'),
				$pc
			).	"'><div class='cp-list-pc-bar' style='width:{$pc}%'></div><center>". esc_html( $time ) ."</center></div></td>";

	}


	/**
	 * Create "Name" cell
	 */
	function echo_name_cell( $a_comment, $name ) {

		echo "<td class='name column-name' data-colname='". $name ."'>". esc_html( $a_comment['name'] ) ."</td>";

	}

	/**
	 * Prepare to display profiles
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Fetch our data
		$profile = $this->fetch_queries();
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
	 * Retrieve all queries
	 */
	function fetch_queries() {

		$buffer = [];
		$count = 0;

		if (! file_exists("{$this->profile_path}.queries.profile") ) {
			return $buffer;
		}
		$lines = file_get_contents( "{$this->profile_path}.queries.profile", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( $lines === false ) {
			wp_die( sprintf(
				esc_html__('Cannot open profile file: %s', 'code-profiler-pro'),
				"{$this->profile_path}.queries.profile"
			));
		}

		// Case sensitivity
		if (! empty( $_REQUEST['c'] ) ) {
			$search = 'strpos';
		} else {
			$search = 'stripos';
		}

		$queries = unserialize( $lines );

		foreach( $queries as $index => $query ) {

			// We don't display but count empty values
			if ( ( empty( $query[1] ) || $query[1] == 0 ) && $this->hide_empty_value == 1 ) {
				$this->hidden++; continue;
			}

			// Search query + plugin/theme name and slug
			if (! empty( $_REQUEST['s'] ) ) {
				if ( $search( $query[0] . $query[2] . $query[3] . $query[4], $_REQUEST['s'] ) === false ) {
					// We must get the total time for all records, not only the filtered ones
					$this->total_time += $query[1];
					continue;
				}
			}

			$buffer[ $count ]['order']	= $count + 1;
			$buffer[ $count]['query']	= $query[0];
			$buffer[ $count]['time']	= $query[1];
			$this->total_time          += $query[1];
			$buffer[ $count ]['stack']	= $query[2];

			// Display full name or slug
			if ( $this->display_name == 'full' ) {
				$buffer[ $count ]['name'] = $query[4];
			} else {
				$buffer[ $count ]['name'] = $query[3];
			}
			// Truncate names if needed
			if ( strlen( $buffer[ $count ]['name'] ) > $this->truncate_name ) {
				$buffer[ $count ]['name'] = mb_substr( $buffer[ $count ]['name'], 0, $this->truncate_name , 'utf-8') .'...';
			}

			$count++;
		}
		return $buffer;
	}

}

// =====================================================================
// EOF
