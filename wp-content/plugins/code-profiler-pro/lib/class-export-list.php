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

class CodeProfilerPro_ExportList {

	// CSV header line
	private $headers = [
		'Date',
		'Profile',
		'Items',
		'Time (s)',
		'Memory (MB)',
		'File I/O',
		'DB Queries',
		'HTTP Connections'
	];

	/**
	 * Initialize.
	 */
	public function __construct() {

		// Security nonce
		if ( wp_verify_nonce(
			$_REQUEST['_wpnonce'], 'code-profiler-download-csv') === false
		) {

			wp_nonce_ays('code-profiler-download-csv');
		}

		$this->export();
	}


	/**
	 * Export data in CSV format
	 */
	private function export() {

		require_once 'class-table-profiles.php';
		$CPTableProfiles = new CodeProfilerPro_Table_Profiles();
		$profile = $CPTableProfiles->fetch_profiles();
		$out = fopen('php://output', 'w');
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="profiles_list.csv"');
		fputcsv( $out, $this->headers );
		foreach( $profile as $key ) {
			if ( empty( $key ) ) {
				continue;
			}
			$field		= [];
			$fields[0]	= date('M d, Y \@ H:i', $key['date'] );
			$fields[1]	= $key['profile'];

			if ( $key['items'] == '-' ) {
				$fields[2] = '-';
			} else {
				$fields[2] = number_format( $key['items'] );
			}

			$fields[3] = $key['time'];

			if ( $key['mem'] == '-' ) {
				$fields[4] = '-';
			} else {
				$fields[4] = number_format( $key['mem'], 2);
			}

			if ( $key['io'] == '-' ) {
				$fields[5] = '-';
			} else {
				$fields[5] = number_format( $key['io'] );
			}

			if ( $key['queries'] == '-' ) {
				$fields[6] = '-';
			} else {
				$fields[6] = number_format( $key['queries'] );
			}

			if (
				! isset( $key['http'] ) || // CP <1.6
				$key['http'] == '-' )
			{
				$fields[7] = '-';
			} else {
				$fields[7] = number_format( $key['http'] );
			}

			fputcsv( $out, $fields );
		}
		fclose( $out );
		exit;
	}

}
// =====================================================================
// EOF
