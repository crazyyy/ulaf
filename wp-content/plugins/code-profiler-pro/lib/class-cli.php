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

if (! defined('ABSPATH') ) { die('Forbidden'); }

// =====================================================================

class CodeProfilerPro_CLI extends WP_CLI_Command {

	private $file;
	private $cmd_view = 'wp code-profiler-pro view';
	private $cmd_run  = 'wp code-profiler-pro run';
	private $cmd_lic  = 'wp code-profiler-pro license';

	/**
	 * Run the profiler.
	 *
	 */
	public function run( $args, $assoc_args ) {

		$this->is_enabled();

		$_POST['cp_nonce']	= wp_create_nonce('start_profiler_nonce');
		$_POST['profile']		= 'WP-CLI_' . time();
		$_POST['ua']			= 'Firefox';

		// Detect if we're authenticated or not
		if ( is_user_logged_in() === true ) {
			$_POST['user']		= 'authenticated';
		} else {
			$_POST['user']		= 'unauthenticated';
		}

		// Custom URI?
		if (! empty( $assoc_args['dest'] ) ) {
			$_POST['where']	= 'custom';
			$_POST['post']		= $assoc_args['dest'];
		} else {
			$_POST['where']	= 'frontend';
			$_POST['post']		= home_url( '/' );
		}

		// HTTP basic authentication
		if (! empty( $assoc_args['u'] ) ) {
			_e('Enter your HTTP basic authentication password:', 'code-profiler-pro');
			echo ' ';
			echo "\033[30;40m";
			$password = trim( stream_get_line( STDIN, 255, PHP_EOL) );
			echo "\033[0m";
			$_POST['Authorization'] = 'Basic '. base64_encode( "{$assoc_args['u']}:{$password}" );
		}

		WP_CLI::log( sprintf(
			__('Starting Code Profiler Pro v%s (profile: %s)', 'code-profiler-pro'),
			CODE_PROFILER_PRO_VERSION, $_POST['profile'] ) . "\n"
		);

		$progress = \WP_CLI\Utils\make_progress_bar( '', 3 );
		$progress->tick();

		// Run the profiler
		$response = json_decode( codeprofiler_pro_start_profiler(), true );
		if ( $response === false ) {
			$message = __('Unknown error returned by AJAX', 'code-profiler-pro');
			WP_CLI::error( $message );
			exit;
		}
		if ( $response['status'] == 'error') {
			WP_CLI::error( $response['message'] );
			exit;
		}

		$progress->tick();

		// All good, run the parser
		$_POST['microtime'] = $response['microtime'];
		$response = json_decode( codeprofiler_pro_prepare_report(), true );
		if ( $response === false ) {
			$message = __('Unknown error returned by AJAX', 'code-profiler-pro');
			WP_CLI::error( $message );
			exit;
		}
		if ( $response['status'] == 'error') {
			WP_CLI::error( $response['message'] );
			exit;
		}

		$progress->tick();
		$progress->finish();

		// Run the parser and show the results
		$this->view( $response['cp_profile'] );

		exit;
	}


	/**
	 * Display last created profile.
	 *
	 */
	public function view( $profile = '') {

		$this->is_enabled();

		// The profile was just created (`wp code-profiler-pro run`)...
		if (! empty( $profile ) ) {
			$this->file = code_profiler_pro_get_profile_path( $profile );
			if ( $this->file === false ) {
				$message = __('Cannot find the profile file', 'code-profiler-pro');
				WP_CLI::error( $message );
				exit;
			}
			$this->file .= '.slugs.profile';

		// ... or we show last created one  (`wp code-profiler-pro view`)
		} else {
			$this->file = $this->find_last_profile();
			if ( $this->file === false ) {
				$message =	sprintf(
					__('No profile found. Run the profiler at least once to create a profile: %s', 'code-profiler-pro'),
					$this->cmd_run
				);
				WP_CLI::error( $message );
				exit;
			}
		}

		// Fetch and display the profile's name only
		preg_match(
			'`'. CODE_PROFILER_PRO_UPLOAD_DIR .'/\d{10}\.\d+\.(.+?)\.slugs\.profile$`',
			$this->file,
			$match
		);
		$message = sprintf( __('Viewing: %s', 'code-profiler-pro'), $match[1] );
		$date = date('Y/m/d \@ H:i:s', filemtime( $this->file ) );
		echo WP_CLI::colorize("\n%Y$message ~ $date%n\n\n");

		// Display stats
		$summary_file = str_replace('.slugs.profile', '', $this->file );
		echo code_profiler_pro_getsummarystats( $summary_file, 'text');

		$slugs = $this->read_profile();

		$cp_options = get_option('code-profiler-pro');

		// Get the total time and the slowest item
		$total_time = 0;
		foreach( $slugs as $k => $v ) {
			$total_time += $v[1];
		}

		$coeff = number_format( $slugs[0][1] / $total_time * 100 );

		foreach( $slugs as $k => $v ) {

			// Display name, time and %
			if ( $cp_options['display_name'] == 'slug') {
				$name = $v[0];
			} else {
				$name = $v[2];
			}
			// Inform if it's the theme or a mu-plugin
			if ( $v[3] == 'theme' ) {
				$name .= ' (theme)';
			} elseif ( $v[3] == 'mu-plugin') {
				$name .= ' (mu-plugin)';
			}

			$time    = number_format( $v[1], 3                  );
			$percent = number_format( $v[1] / $total_time * 100 );
			$chars   = number_format( $percent * 80 / $coeff    );
			// We use `echo` instead of `WP_CLI::log` because the layout could
			// be all messed-up when some caching plugins such as LiteSpeed Cache
			// are activated.
			echo " $name | {$time}s | {$percent}%\n";
			if (! $percent ) {
				echo " \u{258C}\n\n";
			} else {
				$bar = '';
				for ( $i = 0; $i < $chars; $i++ ) {
					$bar .= ' ';
				}
				echo WP_CLI::colorize(" %8$bar%n\n\n");
			}
		}
		exit;
	}


	/**
	 * Retrieve the full path/name to the last created profile
	 *
	 */
	private function find_last_profile() {

		$profiles = glob( CODE_PROFILER_PRO_UPLOAD_DIR .'/*.slugs.profile');

		array_multisort(
			array_map('filectime', $profiles ),
			SORT_NUMERIC,
			SORT_DESC,
			$profiles
		);

		if (! empty( $profiles[0] ) ) {
			return $profiles[0];
		}

		return false;
	 }


	/**
	 * Parse and return the profile's data
	 *
	 */
	private function read_profile() {

		$profile = str_replace('.slugs.profile', '', $this->file );

		$res = code_profiler_pro_get_profile_data( $profile );
		if ( isset( $res['error'] ) ) {
			WP_CLI::error( $res['error'] );
			exit;
		}

		// Sort data (slowest plugin/theme first)
		usort( $res, function( $a, $b ) {
			return $b[1] <=> $a[1];
		} );

		return $res;
	}


	/**
	 * Verify wether WP CLI integration is enabled or not
	 *
	 */
	private function is_enabled() {

		$cp_options = get_option('code-profiler-pro');
		if ( empty( $cp_options['enable_wpcli'] ) ) {
			$message = __('WP-CLI integration is disabled. To enable it, log in to your admin dashboard and go to the Code Profiler settings page.', 'code-profiler-pro');
			WP_CLI::error( $message );
			exit;
		}
	}


	/**
	 * Save the license.
	 *
	 */

	public function license() {

		$this->is_enabled();

		_e('Enter your license:', 'code-profiler-pro');
		echo ' ';
		$_POST['cppro-license'] = trim( stream_get_line( STDIN, 200, PHP_EOL) );

		$res = code_profiler_pro_check_license();
		if (! empty( $res['error'] ) ) {
			WP_CLI::error( $res['error'] );
		} else {
			WP_CLI::success( $res['message'] );
		}
		exit;
	}


	/**
	 * Display help screen and quit.
	 *
	 */
	public function help() {

		$this->is_enabled();

		WP_CLI::log("\nCode Profiler Pro v". CODE_PROFILER_PRO_VERSION .
			" (c)". date('Y') ." Jerome Bruandet & NinTechNet Limited ~ https://code-profiler.com/\n\n".
			"  {$this->cmd_view}        ". __('View last created profile', 'code-profiler-pro') ."\n".
			"  {$this->cmd_run}         ". __('Run the profiler', 'code-profiler-pro') ."\n".
			"  {$this->cmd_lic}     ". __('Enter your license', 'code-profiler-pro') ."\n\n".
			__('GLOBAL PARAMETERS', 'code-profiler-pro') ."\n\n".
			"  --dest=<URL to profile>\n".
			"      ". __('Path to the WordPress page or post to profile. If missing, profile the frontend.', 'code-profiler-pro') ."\n\n".
			"  --user=<id|login|email>\n".
			"      ". __('Run the profiler as the corresponding WordPress user. If missing, run as an unauthenticated user.', 'code-profiler-pro') ."\n\n".
			"  --u=<username>\n".
			"      ". __('HTTP Basic authentication username. You will be prompted to enter your password.', 'code-profiler-pro') ."\n\n" );
		exit;
	}

}

WP_CLI::add_command('code-profiler-pro', 'CodeProfilerPro_CLI', ['shortdesc' => __('Profile your blog with Code Profiler Pro.', 'code-profiler-pro') ] );

// =====================================================================
// EOF
