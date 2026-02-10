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

class CodeProfilerPro_Table_Functions extends WP_List_Table {

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
	private $row_count;
	private $backtrace_enabled;
	private $callers_buffer = [];


	/********************************************************************
	 * Initialize.
	 */
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

		parent::__construct( [
			'singular'  => esc_html__('function', 'code-profiler-pro'),
			'plural'    => esc_html__('functions', 'code-profiler-pro'),
			'ajax'      => false
		] );
    }


	/********************************************************************
	 * Empty list
	 */
	function no_items() {
		esc_html_e('No records were found.', 'code-profiler-pro');
		$this->is_empty = 1;
	}

	/********************************************************************
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

	/********************************************************************
	 * Sortable columns
	 */
	function get_sortable_columns() {
		return array(
			'function' => array('function', true ),
			'caller'   => array('caller', true ),
			'time'     => array('time', true ),
			'script'   => array('script', true ),
			'name'     => array('name', true )
		);
	}

	/********************************************************************
	 * Columns
	 */
	function get_columns(){
		return [
			'function' => esc_html__('Function', 'code-profiler-pro'),
			'caller'	  => esc_html__('Caller', 'code-profiler-pro'),
			'time'	  => esc_html__('Time (sec)', 'code-profiler-pro'),
			'script'	  => esc_html__('Script', 'code-profiler-pro'),
			'name'     => esc_html__('Component', 'code-profiler-pro')
		];
    }

	/********************************************************************
	 * Sorting
	 */
	function usort_reorder( $a, $b ) {
		$orderby = (! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'time';
		$order = (! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$result = $this->cmp_num_or_string( $a[$orderby], $b[$orderby] );
		return ( $order === 'asc') ? $result : -$result;
	}

	/********************************************************************
	 * Sort string and numeric values differently
	 */
	function cmp_num_or_string( $a, $b ) {
		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			return ($a-$b) ? ($a-$b) / abs($a-$b) : 0;
		} else {
			return strnatcmp( $a, $b );
		}
	}

	/********************************************************************
	 * Build the table rows
	 *
	 */
	function single_row( $a_comment ) {

		// $a_comment data is already sanitized

		$this->callers_buffer = [];

		$this->row_count++;
		if ( $this->row_count % 2 == 1 ) {
			$bgcolor = "#F9F9F9";
		} else {
			$bgcolor = "#FFFFFF";
		}
		echo "<tr style='background-color:$bgcolor;'>";

		$columns		= $this->get_columns();

		// Prepare and echo each cell of the row
		$this->echo_function_cell( $a_comment, $columns['function'] );
		$this->echo_caller_cell( $a_comment, $columns['caller'] );
		$this->echo_time_cell( $a_comment, $columns['time'] );
		$this->echo_script_cell( $a_comment, $columns['script'] );
		$this->echo_name_cell( $a_comment, $columns['name'] );
		echo '</tr>';

		// Callers' row
		echo '<tr id="callers-'. $this->row_count .'" style="display:none;background-color:'. $bgcolor .'"><td id="colspan-'. $this->row_count .'" colspan="5"><ul>';

		$count = 0;

		foreach( $this->callers_buffer as $script => $calls ) {
			if ( preg_match('/^(.+?)@(\d+)$/', $script, $match ) ) {
				// Absolute or relative paths
				if ( $this->show_paths == 'relative') {
					$filename = ltrim( str_replace( $this->abspath, '', $match[1] ), '\\');
				} else {
					$filename = $match[1];
				}

				// Warn if the file has been deleted
				if (! file_exists( $match[1] ) ) {
					echo '<li style="color:#50575e">'. esc_html( $filename ) .
						' ('.	esc_html__('deleted file', 'code-profiler-pro') . ')</li>';

				} else {

					echo "<li><a style='cursor:pointer' onClick=\"cpjspro_file_view('".
							base64_encode( $match[1] ) ."','{$this->nonce}','','{$match[2]}')\" ".
							"title='". esc_attr__('Click to view', 'code-profiler-pro') ."'>".
							esc_html( $filename ) ."@{$match[2]}</a>";

					if (! empty( $calls[1] ) ) {

						$count++;
						$callsbacktrace = unserialize( $calls[1] );

						if (! empty( $callsbacktrace ) ) {
							echo " | <a style='cursor:pointer' title='". esc_attr__('Show backtrace', 'code-profiler-pro') .
								"' onClick='jQuery(\"#backtrace-". $this->row_count.$count."\").toggle()'>".
								esc_html__('View backtrace', 'code-profiler-pro') ."</a>";

							echo '<ol reversed id="backtrace-'. $this->row_count.$count .'" style="display:none"><br />';

							foreach( $callsbacktrace as $v ) {
								$cbfile = explode('@-@', $v);
								if ( empty( $cbfile[2] ) ) {
									continue;
								}

								$v_link = '';
								if (! preg_match('`^(/|[a-zA-Z]:)`', $cbfile[1] ) ) {
									$v_link = ABSPATH . $cbfile[1];
									if ( $this->show_paths == 'absolute') {
										$cbfile[1] = $v_link;
									}
								}

								if (! file_exists( $v_link ) ) {
									echo '<li style="color:#50575e">'. esc_html( $cbfile[1] ) .
									' ('.	esc_html__('deleted file', 'code-profiler-pro') . ')</li>';

								} else {
									echo "<li><a style='cursor:pointer;' onClick=\"cpjspro_file_view('".
											base64_encode( $v_link ) ."','{$this->nonce}','','{$cbfile[2]}')\" ".
											"title='". esc_attr__('Click to view', 'code-profiler-pro') ."'>".
											esc_html( "{$cbfile[1]}" ) ."@{$cbfile[2]}</a> -&gt; ". esc_html( $cbfile[0] );
								}
							}
							echo '</ol><br />';
						} else {
							if ( $this->backtrace_enabled ) {
								// Display only if there should be a backtrace
								echo " | <font style='color:#50575e' title='". esc_attr__('No backtrace available for this item.', 'code-profiler-pro') ."'>".
								esc_html__('N/A', 'code-profiler-pro') ."</font>";
							}
						}
					}
					echo "</li>";
				}
			}
		}
		echo '</ul></td></tr>';
	}


	/********************************************************************
	 * Create the "Function" cell
	 */
	function echo_function_cell( $a_comment, $name ) {

		// CP <=1.2 or no caller ('N/A')
		if (! is_serialized( $a_comment['caller'] ) ) {
			if ( $a_comment['caller'] != 'N/A' ) {
				// Trim is required for old profiles due to a bug (trailing space)
				$a_comment['caller'] = trim( $a_comment['caller'] );
				$this->callers_buffer[ $a_comment['caller'] ] = 0;
			}
		// CP >1.2
		} else {
			$this->callers_buffer = unserialize( $a_comment['caller'] );
		}

		// Check if we have an absolute or relative path
		if ( strpos( $a_comment['script'], $this->abspath ) === 0 ) {
			$file = $a_comment['script'];
		} else {
			$file = $this->abspath ."/{$a_comment['script']}";
		}
		$function = '';
		if ( in_array( $a_comment['function'], ['{main}', '{closure}'] ) ) {
			// We'll display the script
			$view = esc_html__('View script', 'code-profiler-pro');
		} else {
			$view = esc_html__('View function', 'code-profiler-pro');

			// We can have 'Class->method' or 'Class::method'
			if ( strpos( $a_comment['function'], '::') !== false ) {
				$tmp 			= explode( '::', $a_comment['function'] );
				$function	= $tmp[ count( $tmp ) -1 ];

			} elseif ( strpos($a_comment['function'], '->') !== false ) {
				$tmp			= explode( '->', $a_comment['function'] );
				$function	= $tmp[ count( $tmp ) -1 ];

			} else {
				$function	= $a_comment['function'];
			}
		}
		if ( is_file( $file ) ) {
			$oc = "style=\"cursor:pointer\" onClick=\"cpjspro_file_view('".
					base64_encode( $file ) ."','{$this->nonce}','".
					addslashes( $function ) ."','')\"";
			$actions = array(
				'view' => sprintf('<a %s>%s</a>', $oc, $view )
			);
		} else {
			$actions = array(
				'view' => '<font style="color:#50575e">'.
							esc_html__('N/A', 'code-profiler-pro').
							'</font>'
			);
		}
		$nc = count( $this->callers_buffer );
		if ( $nc == 0 ) {
			$actions['view2'] = esc_attr__('N/A', 'code-profiler-pro');
		} else {
			if ( $nc == 1 ) {
				$view2 = esc_html( __('View caller', 'code-profiler-pro') );
			} else {
				$view2 = esc_html( __('View callers', 'code-profiler-pro') );
			}
			$actions['view2'] = '<a style="cursor:pointer" onClick="cpjspro_toggle_caller(\''. $this->row_count .'\', 5)">'. $view2 .'</a>';
		}
		printf( '<td class="function column-function column-primary" data-colname="'. $name .'">%1$s %2$s</td>', esc_html( $a_comment['function'] ), $this->row_actions( $actions ) );

	}


	/********************************************************************
	 * Create the "Caller" cell
	 */
	function echo_caller_cell( $a_comment, $name ) {

		 echo "<td class='caller column-caller' data-colname='". $name ."'>". number_format( count( $this->callers_buffer ) ) ."</td>";

	}


	/**
	 * Create the "Time" cell
	 */
	function echo_time_cell( $a_comment, $name ) {

		$pc = ceil ( ( $a_comment['time'] / $this->total_time ) * 100 );
		echo "<td class='time column-time' data-colname='". $name ."'><div class='cp-list-pc' style='cursor:help' title='".
			sprintf(
			esc_attr__('%s%% of all functions (plugins and theme)', 'code-profiler-pro'),
			$pc
		).	"'><div class='cp-list-pc-bar' style='width:{$pc}%'></div><center>". esc_html( $a_comment['time'] ) ."</center></div></td>";

	}


	/********************************************************************
	 * Create the "Script" cell
	 */
	function echo_script_cell( $a_comment, $name ) {

		 echo "<td class='script column-script' data-colname='". $name ."'>". esc_html( $a_comment['script'] ) ."</td>";

	}


	/********************************************************************
	 * Create "Name" cell
	 */
	function echo_name_cell( $a_comment, $name ) {

		echo "<td class='name column-name' data-colname='". $name ."'>". esc_html( $a_comment['name'] ) ."</td>";

	}

	/********************************************************************
	 * Prepare to display profiles
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Fetch our data
		$profile = $this->fetch_functions();
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

	/********************************************************************
	 * Retrieve all profiles
	 */
	function fetch_functions() {

		$buffer = [];
		$count = 0;

		// Check if the backtrace was enabled
		if ( file_exists("{$this->profile_path}.summary.profile") ) {
			$summary = json_decode(
				file_get_contents("{$this->profile_path}.summary.profile"),
				true
			);
			if (! empty( $summary['backtrace'] ) ) {
				$this->backtrace_enabled = 1;
			}
		}

		// Read and parse the profile
		$fh = fopen( "{$this->profile_path}.functions.profile", 'rb');
		if ( $fh === false ) {
			wp_die( sprintf(
				esc_html__('Cannot open profile file: %s', 'code-profiler-pro'),
				"{$this->profile_path}.functions.profile"
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

			if (! isset( $tmp[1] ) || ! isset( $tmp[2] ) ||
				 ! isset( $tmp[4] ) || ! isset( $tmp[6] ) ) {

				continue;
			}

			// We don't display but count empty values
			if ( (empty( $tmp[2] ) || $tmp[2] == 0) && $this->hide_empty_value == 1 ) {
				$this->hidden++; continue;
			}

			// Search query
			if (! empty( $_REQUEST['s'] ) ) {
				// Remove the ABSPATH, we don't include it in the search
				$line = ltrim( str_replace( $this->abspath, '', $line ), '\\');
				if ( $search( $line, $_REQUEST['s'] ) === false ) {
					// We must get the total time for all records, not only the filtered ones
					$this->total_time += $tmp[2];
					continue;
				}
			}

			$buffer[$count]['function'] = $tmp[1];
			$buffer[$count]['time']     = $tmp[2];
			$this->total_time          += $tmp[2];

			// Absolute or relative paths
			if ( $this->show_paths == 'relative') {
				$buffer[$count]['script'] = ltrim( str_replace( $this->abspath, '', $tmp[4] ), '\\');
			} else {
				$buffer[$count]['script'] = $tmp[4];
			}

			$buffer[$count]['caller'] = $tmp[5];

			// Display full name or slug
			if ( $this->display_name == 'full') {
				$buffer[$count]['name'] = $tmp[6];
			} else {
				$buffer[$count]['name'] = $tmp[0];
			}
			// Truncate names if needed
			if ( strlen( $buffer[$count]['name'] ) > $this->truncate_name ) {
				$buffer[$count]['name'] = mb_substr( $buffer[$count]['name'], 0, $this->truncate_name , 'utf-8') .'...';
			}
			if ( isset( $tmp[7] ) && $tmp[7] == 'mu-plugin') {
				$buffer[$count]['name'] .= ' (MU)';
			}

			$count++;
		}
		fclose( $fh );
		return $buffer;
	}

}

// =====================================================================
// EOF
