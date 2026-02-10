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

class CodeProfilerPro_Export {

	private $file;
	private $type;
	private $id;
	private $downloaded_file;
	private $buffer = [];
	// CSV header line
	private $headers = [
		['Slug', 'Execution time', 'Name', 'Type'],
		['Slug', 'Script', 'Execution time', 'Name', 'Type'],
		['Slug', 'Function', 'Execution time', 'Caller', 'Script', 'Caller script', 'Name', 'Type'],
		['Slug', 'Order', 'Query', 'Execution time', 'Backtrace', 'Name'],
		['Slug', 'URL', 'Execution time', 'Code', 'Message', 'Backtrace', 'Name'],
		['Order', 'Script', 'Operation'],
		['Operation', 'Called'],
		['I/O Read bytes', 'I/O Write bytes']
	];


	/**
	 * Initialize, and verify the request
	 */
	public function __construct() {

		// Security nonce
		if ( wp_verify_nonce(
			$_REQUEST['_wpnonce'], 'code-profiler-download-csv') === false
		) {

			wp_nonce_ays('code-profiler-download-csv');
		}

		// Verify that the profile is valid
		$this->file = code_profiler_pro_get_profile_path(
			$_REQUEST['cp-download-csv'],
			$_REQUEST['cp-type']
		);

		if ( $this->file === false ) {
			$err = sprintf(
				esc_html__('Cannot download CSV, profile not found: %s', 'code-profiler-pro'),
				$_REQUEST['cp-download-csv']
			);
			code_profiler_pro_log_error( $err );
			wp_die( esc_html( $err ) );
		}

		$this->type	= $_REQUEST['cp-type'];
		$this->id	= $_REQUEST['cp-download-csv'];

		$this->export();

	}


	/**
	 * Export data in CSV format
	 */
	private function export() {

		if ( preg_match('`/\d{10}\.\d+?\.(.+)$`', $this->file, $match ) ) {
			$this->downloaded_file = sanitize_file_name( $match[1] ) ."_{$this->type}.csv";
		} else {
			$this->downloaded_file = "{$this->id}_{$this->type}.csv";
		}

		if ( $this->type == 'queries') {
			$this->read_serialized_file();

		} elseif ( $this->type == 'connections') {
			$this->export_connections_file();

		} else {
			$this->read_csv_file();
		}

		// Make sure we have some data
		if ( empty( $this->buffer ) ) {
			$err = sprintf(
				esc_html__('Profile file is empty or corrupted: %s.', 'code-profiler-pro'),
				$this->file
			);
			code_profiler_pro_log_error( $err );
			wp_die( esc_html( $err ) );
		}

		if (! empty( $_REQUEST['s'] ) ) {
			// Add the "Filter" header" to show we're exporting
			// the filtered data only
			$this->headers[1][] = 'Filter'; // Scripts
			$this->headers[2][] = 'Filter'; // Functions
			$this->headers[3][] = 'Filter'; // Queries
			$this->headers[4][] = 'Filter'; // Connection
			$this->buffer[0][]  = preg_replace('/^([-=+@])/', '', $_REQUEST['s'] );
		}
		// Insert the right header
		if ( $this->type == 'slugs')      { array_unshift( $this->buffer, $this->headers[0] ); }
		if ( $this->type == 'scripts')    { array_unshift( $this->buffer, $this->headers[1] ); }
		if ( $this->type == 'functions')  { array_unshift( $this->buffer, $this->headers[2] ); }
		if ( $this->type == 'queries')    { array_unshift( $this->buffer, $this->headers[3] ); }
		if ( $this->type == 'connections'){ array_unshift( $this->buffer, $this->headers[4] ); }
		if ( $this->type == 'iolist')     { array_unshift( $this->buffer, $this->headers[5] ); }
		if ( $this->type == 'iostats')    { array_unshift( $this->buffer, $this->headers[6] ); }
		if ( $this->type == 'diskio')     { array_unshift( $this->buffer, $this->headers[7] ); }

		$out = fopen('php://output', 'w');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="'. $this->downloaded_file .'"');
		foreach( $this->buffer as $fields ) {
			// Get rid of the empty field created by fgetcsv
			if ( empty( $fields ) ) {
				continue;
			}

			// Callers' function: CP >1.2 and non-empty caller
			if ( $this->type == 'functions') {
				if ( isset( $fields[5] ) && is_serialized( $fields[5] ) ) {
					$callers_buffer = [];
					$callers_string = '';
					$callers_buffer = unserialize( $fields[5] );
					foreach( $callers_buffer as $script => $calls ) {
						$callers_string .= "$script, ";
					}
					$fields[3] = count( $callers_buffer );
					$fields[5] = rtrim( $callers_string, ' ,' );
				}
			}
			fputcsv( $out, $fields );
		}
		fclose( $out );

		exit;
	}


	/**
	 * Read CSV file
	 */
	private function read_csv_file() {

		$fh = fopen( "{$this->file}.{$this->type}.profile", 'rb');
		if ( $fh === false ) {
			$err = sprintf(
				esc_html__('Cannot open source file: %s', 'code-profiler-pro'),
				$this->file
			);
			code_profiler_pro_log_error( $err );
			wp_die( esc_html( $err ) );
		}

		// Fetch its content, and filter if needed
		while (! feof( $fh ) ) {
			$line = fgets( $fh );

			// Search query
			if (! empty( $_REQUEST['s'] ) && $this->search( $line ) === false ) {
				continue;
			}

			$this->buffer[] = explode( "\t", rtrim( $line ) );
		}
		fclose( $fh );
	}


	/**
	 * Read serialized file
	 */
	private function read_serialized_file() {

		$queries	= unserialize(
			file_get_contents(
				"{$this->file}.{$this->type}.profile",
				FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
		) );
		$count	= 0;

		foreach( $queries as $index => $query ) {
			// Search query
			if (! empty( $_REQUEST['s'] ) &&
				$this->search(
					$query[0] .
					$query[2] .
					$query[3] .
					$query[4]
				) === false
			) {
				continue;
			}

			// We need to unserialize and output the function names only, not the files
			$stack_string = '';
			$stack = unserialize( $query[2] );
			foreach( $stack as $k => $v ) {
				if ( is_string( $v['n'] ) ) {
					$stack_string .= "{$v['n']}, ";
				} else {
					$stack_string .= "{$v['n']['n']}, ";
				}
			}
			$count++;
			$this->buffer[] = [
				$query[3],
				$count,
				$query[0],
				number_format( $query[1], 6 ),
				rtrim( $stack_string, ' ,' ),
				$query[4]
			];
		}
	}


	/**
	 * Read connections file.
	 */
	private function export_connections_file() {

		$connections = json_decode(
			file_get_contents(
				"{$this->file}.{$this->type}.profile",
				FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
		), true );

		foreach( $connections as $index => $connection ) {
			// Search query
			if (! empty( $_REQUEST['s'] ) &&
				$this->search(
					$connection['url'] .
					$connection['msg'] .
					$connection['code'] .
					$connection['function'] .
					$connection['slug'] .
					$connection['name']
				) === false
			) {
				continue;
			}

			$backtrace = '';
			foreach( $connection['backtrace'] as $k => $v ) {
				$backtrace .= "{$v['file']}@{$v['line']}\n";
			}
			$this->buffer[] = [
				$connection['slug'],
				$connection['url'],
				$connection['time'],
				$connection['code'],
				$connection['msg'],
				$backtrace,
				$connection['name']
			];
		}
	}


	/**
	 * Search input.
	 */
	private function search( $string ) {

		// Case sensitivity
		if (! empty( $_REQUEST['c'] ) ) {
			$search = 'strpos';
		} else {
			$search = 'stripos';
		}

		return $search( $string, $_REQUEST['s'] );

	}

}
// =====================================================================
// EOF
