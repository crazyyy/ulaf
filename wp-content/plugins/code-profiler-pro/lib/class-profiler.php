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

class CodeProfilerPro_Profiler {

	static $tick_list = '';
	static $connections_list = [];
	static $connections_start;
	static $buffer;
	static $metrics;
	private $tmp_iostats;
	private $tmp_summary;
	private $tmp_iolist;
	private $tmp_diskio;
	private $tmp_ticks;
	private $tmp_queries;
	private $tmp_connections;
	private $abspath;

	/**
	 * Initialize.
	 */
	public function __construct() {

		$this->abspath = rtrim( ABSPATH, '/\\');
		$microtime = sanitize_file_name( $_REQUEST['CODE_PROFILER_PRO_ON'] );

		$this->tmp_summary		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_SUMMARY_LOG;
		$this->tmp_iostats		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_IOSTATS_LOG;
		$this->tmp_iolist			= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_IOLIST_LOG;
		$this->tmp_ticks			= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_TICKS_LOG;
		$this->tmp_diskio			= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_DISKIO_LOG;
		$this->tmp_queries		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_QUERY_LOG;
		$this->tmp_connections	= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_CONNECTIONS_LOG;

		// Clear the temporary log because the buffer will be written
		// with the FILE_APPEND flag
		if ( file_exists( $this->tmp_ticks ) ) {
			unlink( $this->tmp_ticks );
		}

		if ( function_exists('hrtime') ) {
			// PHP >=7.3
			self::$metrics = 'hrtime';
		} else {
			self::$metrics = 'microtime';
		}

		// Enable queries debugging
		if (! defined('SAVEQUERIES') ) {
			define('SAVEQUERIES', true);
		}
		if (! defined('QM_DISABLED') ) {
			// Temporarily disable Query Monitor
			// so that we can fetch the DB queries
			define('QM_DISABLED', true);
		}

		$cp_options = get_option('code-profiler-pro');
		if ( empty( $cp_options['accuracy'] ) ) {
			define('CODE_PROFILER_PRO_TICKS', 1 );
		} else {
			define('CODE_PROFILER_PRO_TICKS', (int) $cp_options['accuracy'] );
		}
		define('CODE_PROFILER_PRO_LENGTH', 17 + strlen( (string) CODE_PROFILER_PRO_TICKS ) );

		if ( empty( $cp_options['backtrace_limit'] ) || $cp_options['backtrace_limit'] == 2 ) {
			define('CODE_PROFILER_PRO_DBLIMIT', 2);
		} else {
			define('CODE_PROFILER_PRO_DBLIMIT', (int) $cp_options['backtrace_limit'] );
		}

		if ( empty( $cp_options['buffer'] ) ||
			! preg_match('/^(?:[1-9]|10)$/', $cp_options['buffer'] ) ) {

			$recommended = code_profiler_pro_suggested_memory();
			self::$buffer = (int) $recommended * 1000000;
		} else {
			self::$buffer = $cp_options['buffer'] * 1000000;
		}
		code_profiler_pro_log_debug(
			sprintf(
				esc_html__('Setting size of memory buffer to %sMB', 'code-profiler-pro'),
				self::$buffer / 1000000
			)
		);

		add_filter('pre_http_request', [ $this, 'pre_http_request'], 10000, 3 );
		add_action('http_api_debug', [ $this, 'http_api_debug'], 10000, 5 );
		register_shutdown_function( [ $this, 'code_profiler_pro_shutdown'] );
		code_profiler_pro_log_debug(
			esc_html__('Starting profiler', 'code-profiler-pro')
		);
		require 'class-stream.php';
		CodeProfilerPro_Stream::start();
		register_tick_function( [ $this, 'code_profiler_pro_tick_handler'] );
	}


	/**
	 * Save data and check for potential PHP errors.
	 */
	public function code_profiler_pro_shutdown() {

		CodeProfilerPro_Stream::stop();

		global $wpdb;
		if (! empty( $this->tmp_queries ) ) {
			// Prevent a "Serialization of Closure is not allowed"
			// potential error message
			try {
				file_put_contents( $this->tmp_queries , serialize( $wpdb->queries ) );
			} catch ( Exception $e ) {
				code_profiler_pro_log_error(
					esc_html('Cannot serialize DB queries object, is that a closure?', 'code-profiler-pro')
				);
			}
		} else{
			code_profiler_pro_log_warn(
				esc_html('DB queries object is empty.', 'code-profiler-pro')
			);
		}

		// Restore wp-content/db.php
		if ( file_exists( WP_CONTENT_DIR .'/db.php.code-profiler') ) {
			rename( WP_CONTENT_DIR .'/db.php.code-profiler', WP_CONTENT_DIR .'/db.php');
			code_profiler_pro_log_info(
				sprintf(
					/* Translators: Restoring wp-content/db.php */
					esc_html__('Restoring %s', 'code-profiler-pro'),
					WP_CONTENT_DIR .'/db.php'
				)
			);
		}

		$summary['memory']		= memory_get_peak_usage();
		$summary['queries']		= get_num_queries() - 2;
		$summary['connections']	= count( self::$connections_list );
		file_put_contents( $this->tmp_summary, json_encode( $summary ) );

		// Catch potential error
		$e = error_get_last();
		if ( isset( $e['type'] ) && $e['type'] === E_ERROR ) {
			$err = str_replace( "\n", ' - ', $e['message'] );
			code_profiler_pro_log_error( sprintf(
			/* Translators: Error message, line number and script */
				esc_html__('Error: E_ERROR (%s - line %s in %s)', 'code-profiler-pro'),
				esc_html( $err ),
				(int) $e['line'],
				esc_html( $e['file'] )
			));
		}

		$err_msg = esc_html__('Cannot open file for writting: %s', 'code-profiler-pro');
		$res = file_put_contents( $this->tmp_iostats, json_encode( CodeProfilerPro_Stream::$io_stats ) );
		if ( $res === false ) {
			$msg = sprintf( $err_msg, $this->tmp_iostats );
			code_profiler_pro_log_error( $msg );
		}

		$res = file_put_contents( $this->tmp_iolist, CodeProfilerPro_Stream::$io_list );
		if ( $res === false ) {
			$msg = sprintf( $err_msg, $this->tmp_iolist );
			code_profiler_pro_log_error( $msg );
		}

		$res = file_put_contents( $this->tmp_ticks, self::$tick_list, FILE_APPEND );
		if ( $res === false ) {
			$msg = sprintf( $err_msg, $this->tmp_ticks );
			code_profiler_pro_log_error( $msg );
		}
		$res = file_put_contents(
			$this->tmp_diskio, "read\t". CodeProfilerPro_Stream::$io_read ."\nwrite\t".
			CodeProfilerPro_Stream::$io_write
		);

		if (! empty( self::$connections_list ) ) {
			file_put_contents( $this->tmp_connections, json_encode( self::$connections_list) );
		}
	}


	/**
	 * Our tick handler.
	 */
	public function code_profiler_pro_tick_handler() {

		$start = $this->time();

		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, CODE_PROFILER_PRO_DBLIMIT );

		if ( isset( $backtrace[1]['function'] ) && empty( $backtrace[1]['line'] ) &&
			isset( $backtrace[0]['function'] ) && $backtrace[0]['function'] = __FUNCTION__ &&
			isset( $backtrace[0]['file'] ) && isset( $backtrace[0]['line'] ) ) {

			if ( isset( $backtrace[1]['class'] ) &&  isset( $backtrace[1]['type'] ) ) {
				if ( method_exists( $backtrace[1]['class'], $backtrace[1]['function'] ) ) {
					$rm	= new ReflectionMethod( $backtrace[1]['class'], $backtrace[1]['function'] );
					$backtrace[1]['line']	= $rm->getStartLine();
					$backtrace[1]['file']	= $backtrace[0]['file'];
				}
			} elseif ( function_exists( $backtrace[1]['function'] ) ) {
					$rm	= new ReflectionFunction( $backtrace[1]['function'] );
					$backtrace[1]['line']	= $rm->getStartLine();
					$backtrace[1]['file']	= $backtrace[0]['file'];

			}
			if ( empty( $backtrace[1]['line'] ) && strpos( $backtrace[1]['function'], '{closure}') !== false ) {
				$backtrace[1]['line'] = $backtrace[0]['line'];
				$backtrace[1]['file'] = $backtrace[0]['file'];
			}
		}

		if ( isset( $backtrace[1] ) ) {
			$el		= $backtrace[1];
			$dbindex	= 2;

		} else {
			$el		= $backtrace[0];
			$dbindex	= 1;
		}

		if ( empty( $el['file'] ) || empty( $el['line'] ) ) {
			self::$tick_list .= "-\t0\t-\t-\t-\t". $this->time() ."\ts:0:\"\"\n";
			return;
		}

		self::$tick_list .= "{$el['file']}\t{$el['line']}\t";
		self::$tick_list .= $this->function_or_method( $el ) ."\t";

		if ( isset( $backtrace[0]['file'][0] ) ) {
			self::$tick_list .= $backtrace[0]['file'];
		} else {
			self::$tick_list .= '-';
		}

		$trace = [];
		while ( isset( $backtrace[ $dbindex ]['file'] ) ) {

			$trace[] = $this->function_or_method( $backtrace[ $dbindex ] ) .'@-@'.
						ltrim( str_replace( $this->abspath, '', $backtrace[ $dbindex ]['file'] ), '/\\').
						"@-@{$backtrace[ $dbindex ]['line']}";
			$dbindex++;
		}
		$backtrace	= null;
		$ser_trace	= serialize( $trace );
		$trace		= null;

		// Buffer can grow *very* big (several hundred megabytes),
		// hence we flush it every 10MB by default
		if ( strlen( self::$tick_list ) > self::$buffer ) {
			CodeProfilerPro_Stream::stop();
			file_put_contents(
				$this->tmp_ticks,
				self::$tick_list ."\t{$start}\t". $this->time() ."\t$ser_trace\n",
				FILE_APPEND
			);
			CodeProfilerPro_Stream::start();
			self::$tick_list = '';

		} else {
			self::$tick_list .="\t{$start}\t". $this->time() ."\t$ser_trace\n";
		}
	}


	/**
	 * HTTP API request.
	 */
	public function pre_http_request( $preempt, $r, $url ) {

		// We must have a URL to log
		if ( empty( $url ) ) {
			return false;
		}
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		self::$connections_start = $this->time();
		self::$connections_list[ self::$connections_start ]['url']	= $url;
		self::$connections_list[ self::$connections_start ]['bt']	= $backtrace;

		return false;
	}


	/**
	 * HTTP API response.
	 */
	public function http_api_debug( $response, $context, $class, $parsed_args, $url ) {

		self::$connections_list[ self::$connections_start ]['stop'] = $this->time();

		if (! is_wp_error( $response ) ) {
			self::$connections_list[ self::$connections_start ]['code']	= $response['response']['code'];
			self::$connections_list[ self::$connections_start ]['msg']	= $response['response']['message'];
			return;
		}
		self::$connections_list[ self::$connections_start ]['code']	= 'ERROR';
		self::$connections_list[ self::$connections_start ]['msg']	= $response->get_error_message();
	}


	/**
	 * Retrieves the function or class/method from a stack frame.
	 */
	private function function_or_method( $frame ) {

		$res = '';
		if ( isset( $frame['class'] ) ) {
			$res .= "{$frame['class']}";
			if ( isset( $frame['type'] ) ) {
				$res .= "{$frame['type']}";
			} else {
				$res .= '->';
			}
		}
		if ( isset( $frame['function'] ) ) {
			$res .= "{$frame['function']}";
		}
		return $res;
	}


	/**
	 * Returns time.
	 */
	private function time() {

		if ( self::$metrics == 'hrtime') {
			return hrtime( true );
		} else {
			return microtime( true );
		}
	}

}

new CodeProfilerPro_Profiler();

// =====================================================================
// EOF
