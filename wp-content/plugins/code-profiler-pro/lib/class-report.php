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


class CodeProfilerPro_Report {

	private $last_slug;
	private $last_script;
	private $last_type;
	private $last_function;
	private $last_time		= 0;
	private $scripts			= [];
	private $themes			= [];
	private $plugins			= [];
	private $composer			= [];
	private $summary_list	= [];
	private $slug2name		= [];
	private $cx_buffer		= [];
	private $parsed_data		= 0;
	private $plugins_dir;
	private $themes_dir;
	private $mu_dir;
	public  $total_plugins;
	private $total_io;

	private $profile_name;
	private $microtime;
	private $tmp_iostats;
	private $tmp_summary;
	private $tmp_iolist;
	private $tmp_diskio;
	private $tmp_ticks;
	private $tmp_queries;
	private $tmp_connections;

	/**
	 * Initialize.
	 */
	public function __construct( $profile_name, $microtime ) {

		$this->tmp_summary		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_SUMMARY_LOG;
		$this->tmp_iostats		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_IOSTATS_LOG;
		$this->tmp_iolist			= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_IOLIST_LOG;
		$this->tmp_ticks			= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_TICKS_LOG;
		$this->tmp_diskio  		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_DISKIO_LOG;
		$this->tmp_queries		= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_QUERY_LOG;
		$this->tmp_connections	= CODE_PROFILER_PRO_UPLOAD_DIR ."/$microtime." .
											CODE_PROFILER_PRO_TMP_CONNECTIONS_LOG;
		$this->microtime   		= $microtime;

		// Make sure all files are there (except tmp_queries, see below)
		if (! file_exists( $this->tmp_ticks ) ) {
			$cp_error = 1;
		} elseif (! file_exists( $this->tmp_iostats ) ) {
			$cp_error = 2;
		} elseif (! file_exists( $this->tmp_iolist ) ) {
			$cp_error = 3;
		}
		if (! empty( $cp_error ) ) {
			$error = sprintf(
				esc_html__('Cannot create the report: the profiler did not '.
				'generate a data file (#%s). Make sure the following directory '.
				'is writable: %s. If you are using a caching plugin or an '.
				'opcode cache, try to disable it. You may also find more '.
				'details about the error in the "Log" tab.', 'code-profiler-pro'),
				$cp_error,
				CODE_PROFILER_PRO_UPLOAD_DIR .'/'
				);
			$this->return_error( $error );
		}

		// Total data analyzed
		$this->parsed_data += filesize( $this->tmp_iostats );
		$this->parsed_data += filesize( $this->tmp_iolist );
		$this->parsed_data += filesize( $this->tmp_ticks );
		if ( file_exists( $this->tmp_queries ) ) {
			$this->parsed_data += filesize( $this->tmp_queries );
		}
		if ( file_exists( $this->tmp_connections ) ) {
			$this->parsed_data += filesize( $this->tmp_connections );
		}
		$this->profile_name	= $profile_name;
		$this->plugins_dir	= preg_quote( realpath( WP_PLUGIN_DIR ) );
		$this->themes_dir		= preg_quote( realpath( WP_CONTENT_DIR .'/themes') );
		$this->mu_dir			= preg_quote( realpath( WPMU_PLUGIN_DIR ) );
	}


	/**
	 * Filter and save the profiler's data.
	 */
	public function prepare_report() {

		$this->parse_ticks();
		$this->get_plugins_theme_name();
		$this->merge_data();
		$this->save_iostats();

		// Get connections list before parsing data
		$this->save_connections();
		$this->save_data();

		$this->save_iolist();
		$this->save_diskio();
		$this->save_composer();
		$this->save_queries();

		code_profiler_pro_log_info( sprintf(
			__('Volume of code and data analyzed: %1$sMB (%2$s plugins and '.
			'1 theme) in %4$ss - Memory used: %3$sMB', 'code-profiler-pro'),
			number_format( $this->parsed_data / 1024 / 1024, 2 ),
			(int) $this->total_plugins,
			number_format( memory_get_peak_usage( false ) / 1024 / 1024, 2 ),
			number_format( microtime( true ) - $this->microtime, 2 )
		));

		return $this->microtime;
	}


	/**
	 * Return a json-encoded error for AJAX, write to log and quit.
	 */
	private function return_error( $error ) {

		$response['message'] = $error;
		$response['status'] = 'error';
		code_profiler_pro_log_error( $error );
		wp_send_json( $response );
	}


	/**
	 * Save all data to TSV files.
	 */
	private function save_data() {

		$slugs_buffer						= '';
		$scripts_buffer					= '';
		$functions_buffer					= '';
		// Flush buffer if >= 2MB
		$max_size							= 2000000;
		$this->summary_list['time']	= 0;

		$slugs_tsv     = CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.".
								"{$this->profile_name}.slugs.profile";
		if ( file_exists( $slugs_tsv ) ) { unlink( $slugs_tsv ); }

		$scripts_tsv   = CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.".
								"{$this->profile_name}.scripts.profile";
		if ( file_exists( $scripts_tsv ) ) { unlink( $scripts_tsv ); }

		$functions_tsv = CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.".
								"{$this->profile_name}.functions.profile";
		if ( file_exists( $functions_tsv ) ) { unlink( $functions_tsv ); }

		$summary_json	= CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.".
								"{$this->profile_name}.summary.profile";
		if ( file_exists( $summary_json ) ) { unlink( $summary_json ); }

		foreach( $this->plugins as $content => $content_array ) {
			$this->total_plugins++;
			// Slugs
			$this->summary_list['time'] += $content_array['time'];

			if ( isset( $this->cx_buffer['plugin'][ $content ] ) ) {
				// Additional connection(s) time
				$time = $this->convert_2_seconds(
					$content_array['time'] + $this->cx_buffer['plugin'][ $content ]
				);
				$this->summary_list['time'] += $this->cx_buffer['plugin'][ $content ];
			} else {
				$time = $this->convert_2_seconds( $content_array['time'] );
			}

			if ( empty( $content_array['name'] ) ) {
				// E.g., a plugin without a folder
				$content_array['name'] = $content;
			}
			if ( isset( $content_array['mu'] ) ) {
				$plugin = 'mu-plugin';
			} else {
				$plugin = 'plugin';
			}
			$slugs_buffer .= "$content\t$time\t{$content_array['name']}\t$plugin\n";

			foreach( $content_array as $slug_k => $slug_v ) {
				if (! in_array( $slug_k, ['name', 'time', 'mu'] ) ) {
					// Scripts
					$time = $this->convert_2_seconds( $content_array[$slug_k]['time'] );
					$scripts_buffer .= "$content\t$slug_k\t$time\t{$content_array['name']}\t$plugin\n";
					if ( strlen( $scripts_buffer ) > $max_size ) {
						file_put_contents( $scripts_tsv, $scripts_buffer, FILE_APPEND );
						$scripts_buffer = '';
					}
					foreach( $slug_v as $script_k => $script_v ) {
						if ( $script_k == 'functions' ) {
							foreach( $script_v as $function_k => $function_v ) {
								// Class/methods & functions
								if ( in_array( strtolower( $function_k ),  ['include', 'include_once', 'require', 'require_once'] ) ) {
									// Look for multiple copies of composer, to warn user
									if (! empty( $function_v['caller'] ) && $function_k == 'include' ) {
										// We check the first element only with key()
										if ( preg_match( "`^{$this->plugins_dir}[\\\/]([^\\\/]+)[\\\/].+?[\\\/]composer[\\\/]ClassLoader\.php@`", key( $function_v['caller'] ), $match ) ) {
											// It is a call from another plugin or theme?
											if ( $match[1] != $content ) {
												if ( isset( $this->plugins[ $match[1] ] ) ) {
													$this->composer[ $match[1] ] = $this->plugins[ $match[1] ]['name'];
												} else {
													$this->composer[ $match[1] ] = '';
												}
												$this->composer[ $content ] = $this->plugins[ $content ]['name'];
											}
										}
									}
									$function_k = '{main}';
								}
								$time = $this->convert_2_seconds( $function_v['time'] );
								if (! empty( $function_v['caller'] ) ) {
									$caller = serialize( $function_v['caller'] );
								} else {
									$caller = 'N/A';
								}
								$functions_buffer .= "$content\t$function_k\t$time\t0\t$slug_k\t$caller\t{$content_array['name']}\t$plugin\n";
								if ( strlen( $functions_buffer ) > $max_size ) {
									file_put_contents( $functions_tsv, $functions_buffer, FILE_APPEND );
									$functions_buffer = '';
								}
							}
						}
					}
				}
			}
		}
		foreach( $this->themes as $content => $content_array ) {

			// Slugs
			$this->summary_list['time'] += $content_array['time'];

			if ( isset( $this->cx_buffer['theme'][ $content ] ) ) {
				// Additional connection(s) time
				$time = $this->convert_2_seconds(
					$content_array['time'] + $this->cx_buffer['theme'][ $content ]
				);
				$this->summary_list['time'] += $this->cx_buffer['theme'][ $content ];
			} else {
				$time = $this->convert_2_seconds( $content_array['time'] );
			}

			$slugs_buffer .= "$content\t$time\t{$content_array['name']}\ttheme\n";

			foreach( $content_array as $slug_k => $slug_v ) {
				if ( $slug_k != 'name' && $slug_k != 'time' ) {
					// Scripts
					$time = $this->convert_2_seconds( $content_array[$slug_k]['time'] );
					$scripts_buffer .= "$content\t$slug_k\t$time\t{$content_array['name']}\ttheme\n";
					foreach( $slug_v as $script_k => $script_v ) {
						if ( $script_k == 'functions' ) {
							foreach( $script_v as $function_k => $function_v ) {
								// Class/methods & functions
								if ( in_array( strtolower( $function_k ),  ['include', 'include_once', 'require', 'require_once'] ) ) {
									// Look for multiple copies of composer, to warn user
									if (! empty( $function_v['caller'] ) && $function_k == 'include' ) {
										// We check the first element only with key()
										if ( preg_match( "`^{$this->plugins_dir}[\\\/]([^\\\/]+)[\\\/].+?[\\\/]composer[\\\/]ClassLoader\.php@`", key( $function_v['caller'] ), $match ) ) {
											// It is a call from another plugin?
											if ( isset( $this->plugins[ $match[1] ] ) ) {
												$this->composer[ $match[1] ] = $this->plugins[ $match[1] ]['name'];
											} else {
												$this->composer[ $match[1] ] = '';
											}
											$this->composer[ $content ] = $this->themes[ $content ]['name'];
										}
									}
									$function_k = '{main}';
								}
								$time = $this->convert_2_seconds( $function_v['time'] );
								if (! empty( $function_v['caller'] ) ) {
									$caller = serialize( $function_v['caller'] );
								} else {
									$caller = 'N/A';
								}
								$functions_buffer .= "$content\t$function_k\t$time\t0\t$slug_k\t$caller\t{$content_array['name']}\ttheme\n";
							}
						}
					}
				}
			}
		}
		$error = '';
		if ( empty( $slugs_buffer ) ) {
			$error = esc_html__('Data is empty: no plugins or themes found', 'code-profiler-pro');
		} elseif ( empty( $scripts_buffer ) ) {
			$error = esc_html__('Data is empty: no running scripts found', 'code-profiler-pro');
		} elseif ( empty( $functions_buffer ) ) {
			$error = esc_html__('Data is empty: no class/methods or functions found', 'code-profiler-pro');
		}
		if ( $error ) {
			$this->return_error( $error );
		}

		// Save data
		file_put_contents( $slugs_tsv, $slugs_buffer );
		if (! empty( $scripts_buffer ) ) {
			file_put_contents( $scripts_tsv, $scripts_buffer, FILE_APPEND );
		}
		if (! empty( $functions_buffer ) ) {
			file_put_contents( $functions_tsv, $functions_buffer, FILE_APPEND );
		}

		// Summary
		$s = json_decode( file_get_contents( $this->tmp_summary ), true );
		unlink( $this->tmp_summary );
		if ( empty( $s['memory'] ) ) {
			$this->summary_list['memory'] = 0;
		} else {
			$this->summary_list['memory'] = $s['memory'] / 1024 / 1024;
		}
		if ( empty( $s['queries'] ) ) {
			$this->summary_list['queries']= 0;
		} else {
			$this->summary_list['queries']= $s['queries'];
		}
		if ( empty( $s['connections'] ) ) {
			$this->summary_list['connections'] = 0;
		} else {
			$this->summary_list['connections'] = (int) $s['connections'];
		}
		if ( empty( $this->total_io ) ) {
			$this->summary_list['io']	= 0;
		} else {
			$this->summary_list['io']	= $this->total_io;
		}
		$this->summary_list['items']	= $this->total_plugins + 1; // Add the theme
		$this->summary_list['time']	= $this->convert_2_seconds( $this->summary_list['time'] );
		$this->summary_list['time']	= number_format( $this->summary_list['time'], 4 );

		// Record if the backtrace was enabled
		$cp_options = get_option('code-profiler-pro');
		if (! empty( $cp_options['backtrace_limit'] ) && $cp_options['backtrace_limit'] > 2 ) {
			$this->summary_list['backtrace'] = 1;
		}

		// Save Code Profiler, PHP and WordPress' versions
		global $wp_version;
		$this->summary_list['versions']	= [
			'wp'	=> $wp_version,
			'cp'	=> CODE_PROFILER_PRO_VERSION,
			'php'	=> PHP_VERSION
		];

		file_put_contents( $summary_json, json_encode( $this->summary_list ) );
	}


	/**
	 * Merge scripts, functions and their corresponding slug.
	 */
	private function merge_data() {

		foreach( $this->scripts as $script => $v ) {
			if ( preg_match("`^{$this->plugins_dir}[\\\/]([^\\\/]+)[\\\/]`", $script, $match ) ) {
				if ( isset( $this->plugins[ $match[1] ]['time'] ) ) {
					$this->plugins[ $match[1] ][$script] = $v;
					unset( $this->scripts[ $script ] );
				}
			} elseif ( preg_match("`^{$this->themes_dir}[\\\/]([^\\\/]+)[\\\/]`", $script, $match ) ) {
				if ( isset( $this->themes[ $match[1] ]['time'] ) ) {
					$this->themes[ $match[1] ][$script] = $v;
					unset( $this->scripts[ $script ] );
				}
			} elseif ( preg_match("`^{$this->mu_dir}[\\\/]([^\\\/]+\.php)$`", $script, $match ) ) {
				if ( isset( $this->plugins[ $match[1] ]['time'] ) ) {
					$this->plugins[ $match[1] ][$script] = $v;
					unset( $this->scripts[ $script ] );
				}
			}
		}
	}


	/**
	 * Convert microtime (PHP<7.3) or hrtime (PHP>=7.3) to seconds.
	 */
	private function convert_2_seconds( $time ) {

		if ( $time < 0 || empty( $time ) ) {
			return '0';
		}
		if ( strpos( $time, '.' ) !== false ) {
			// Looks like microtime
			$time = number_format( $time, 6 );
		} else {
			// hrtime
			$time = number_format( $time / 1000000000, 6 );
		}
		return $time;
	}


	/**
	 * Check if a slug is from a plugin or theme.
	 */
	private function plugin_or_theme( $script ) {

		$res = ['theme' => '', 'plugin' => '', 'script' => ''];

		if ( preg_match("`^{$this->plugins_dir}[\\\/](?:(.+?)[\\\/]|([^\\\/]+\.php)$)`", $script, $slug ) ) {
			if (! empty( $slug[1] ) ) {
				$res['plugin'] = $slug[1];
				$res['script'] = $script;
			} elseif (! empty( $slug[2] ) ) {
				$res['plugin'] = $slug[2];
				$res['script'] = $script;
			}

		} elseif ( preg_match("`^{$this->themes_dir}[\\\/]([^\\\/]+)[\\\/]`", $script, $slug ) ) {
			$res['theme']  = $slug[1];
			$res['script'] = $script;

		} elseif ( preg_match("`^{$this->mu_dir}[\\\/]([^\\\/]+\.php)$`", $script, $slug ) ) {
			$res['plugin']	= $slug[1];
			$res['script'] = $script;
		}
		return $res;
	}


	/**
	 * Retrieve the full name for all plugins and themes.
	 */
	private function get_plugins_theme_name() {

		// Get installed plugins
		if ( ! function_exists('get_plugins') ) {
			require_once ABSPATH .'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();
		foreach( $installed_plugins as $k => $v ) {
			if ( preg_match('`^([^\\\/]+)[\\\/]`', $k, $match ) ) {
				if ( isset( $this->plugins[ $match[1] ]['time'] ) ) {
					$this->plugins[ $match[1] ]['name'] = $v['Name'];
					// Save it for later use too (queries)
					$this->slug2name[ $match[1] ] = $v['Name'];
				}
			}
		}
		// Get installed themes
		if ( ! function_exists('wp_get_themes') ) {
			require_once ABSPATH .'wp-includes/theme.php';
		}
		$installed_themes = wp_get_themes();
		foreach( $installed_themes as $k => $v ) {
			if ( isset( $this->themes[ $k ]['time'] ) ) {
				$this->themes[ $k ]['name'] = $v->Name;
				// Save it for later use too (queries)
				$this->slug2name[ $k ] = $v->Name;
			}
		}
		// MU plugins
		$mu_plugins = get_mu_plugins();
		foreach( $mu_plugins as $k => $v ) {
			if ( isset( $this->plugins[ $k ]['time'] ) ) {
				$this->plugins[ $k ]['name'] = $v['Name'];
				$this->plugins[ $k ]['mu'] = 1;
				// Save it for later use too (queries)
				$this->slug2name[ $k ] = $v['Name'];
			}
		}
	}


	/**
	 * Parse, filter and sort the data.
	 */
	private function parse_ticks() {

		$fh = fopen( $this->tmp_ticks, 'rb');
		if ( $fh === false ) {
			$error = sprintf(
				esc_html__('Cannot open file for reading: %s', 'code-profiler-pro'),
				$this->tmp_ticks
			);
			$this->return_error( $error );
		}
		while(! feof( $fh ) ) {
			$line = fgets( $fh );
			if ( preg_match( '/^(.+?)\t(\d+)\t(.+?)\t(.+?)\t(.+?)\t(.+)\t(.+)$/', $line, $match ) ) {

				$caller = $match[1]; $line_num = $match[2];
				$function = $match[3]; $callee = $match[4];
				$start = $match[5]; $stop = $match[6];
				$f_trace = $match[7];

				// Backtrace didn't return anything
				if ( $caller == '-') {
					$this->last_time = $stop;
					continue;
				}

				// Check if we have a plugin or theme
				$slug = $this->plugin_or_theme( $callee );
				if ( empty( $slug['theme'] ) && empty( $slug['plugin'] ) ) {
					$slug = $this->plugin_or_theme( $caller );
				}

				// Elapsed time since last tick
				if (! empty( $this->last_time ) && ! empty( $start ) ) {
					$elapse = $start - $this->last_time;
				} else {
					$elapse = 0;
				}

				// Plugin: update/create stats (time)

				// We have a slug
				if (! empty( $slug['theme'] ) || ! empty( $slug['plugin'] ) ) {
					// It's a theme
					if (! empty( $slug['theme'] ) ) {
						// Same theme as previous record, update its stats
						if ( $this->last_slug == $slug['theme'] ) {
							if ( isset( $this->themes[ $this->last_slug ]['time'] ) ) {
								$this->themes[ $this->last_slug ]['time'] += $elapse;
							}
						// Update the old record and create the new one if it doesn't exist
						} else {
							if (! empty( $this->last_slug ) ) {
								if ( isset( $this->themes[ $this->last_slug ]['time'] ) ) {
									$this->themes[ $this->last_slug ]['time'] += $elapse;
								}
							}
							if (! isset( $this->themes[ $slug['theme'] ]['time'] ) ) {
								$this->themes[ $slug['theme'] ]['time'] = 0;
							}
						}
					// It's a plugin
					} elseif (! empty( $slug['plugin'] ) ) {
						// Same plugin as previous record, update its stats
						if ( $this->last_slug == $slug['plugin'] ) {
							if ( isset( $this->plugins[ $this->last_slug ]['time'] ) ) {
								$this->plugins[ $this->last_slug ]['time'] += $elapse;
							}
						// Update the old one and create the new one if it doesn't exist
						} else {
							if (! empty( $this->last_slug ) ) {
								if ( isset( $this->plugins[ $this->last_slug ]['time'] ) ) {
									$this->plugins[ $this->last_slug ]['time'] += $elapse;
								}
							}
							if (! isset( $this->plugins[ $slug['plugin'] ]['time'] ) ) {
								$this->plugins[ $slug['plugin'] ]['time'] = 0;
							}
						}
					}

				// We don't have a slug: if there's an old record, update it
				} else {
					if (! empty( $this->last_type ) && ! empty( $this->last_slug ) ) {
						if ( $this->last_type == 'plugin' ) {
							if ( isset( $this->plugins[ $this->last_slug ]['time'] ) ) {
								$this->plugins[ $this->last_slug ]['time'] += $elapse;
							}
						} elseif ( $this->last_type == 'theme' ) {
							if ( isset( $this->themes[ $this->last_slug ]['time'] ) ) {
								$this->themes[ $this->last_slug ]['time'] += $elapse;
							}
						}
					}
				}

				// Script (callee): update/create stats (time)

				// We have a slug
				if (! empty( $slug['theme'] ) || ! empty( $slug['plugin'] ) ) {
					// Same script as previous record, update its stats
					if ( $this->last_script == $slug['script'] ) {
						if ( isset( $this->scripts[ $this->last_script ]['time'] ) ) {
							$this->scripts[ $this->last_script ]['time'] += $elapse;
						} else {
							$this->scripts[ $this->last_script ]['time'] = 0;
						}
					// Update old one and create a new one
					} else {
						if (! empty( $this->last_script ) ) {
							if ( isset( $this->scripts[ $this->last_script ]['time'] ) ) {
								$this->scripts[ $this->last_script ]['time'] += $elapse;
							}
						}
						if (! isset( $this->scripts[ $slug['script'] ]['time']) ) {
							$this->scripts[ $slug['script'] ]['time'] = 0;
						}
					}

				// We don't have a slug: if there's an old script record, update it
				} else {
					if (! empty( $this->last_script ) ) {
						if ( isset( $this->scripts[ $this->last_script ]['time'] ) ) {
							$this->scripts[ $this->last_script ]['time'] += $elapse;
						}
					}
				}

				// Function: update stats

				// We have a slug
				if (! empty( $slug['theme'] ) || ! empty( $slug['plugin'] ) ) {
					// Same function as previous record, update its stats
					if ( $this->last_function == $function ) {
						if ( isset( $this->scripts[ $slug['script'] ]['functions'][ $this->last_function ]['time'] ) ) {
							$this->scripts[ $slug['script'] ]['functions'][ $this->last_function ]['time'] += $elapse;
						} else {
							$this->scripts[ $slug['script'] ]['functions'][ $this->last_function ]['time'] = 0;
						}
					// Update old one and create new one
					} else {
						if (! empty( $this->last_function ) ) {
							if ( isset( $this->scripts[ $this->last_script ]['functions'][ $this->last_function ]['time'] ) ) {
								$this->scripts[ $this->last_script ]['functions'][ $this->last_function ]['time'] += $elapse;
							}
						}

						// If it exists, update caller
						if ( isset( $this->scripts[ $slug['script'] ]['functions'][ $function ]['caller'] ) ) {

							if ( in_array( strtolower( $function ),  ['include', 'include_once', 'require', 'require_once'] ) ) {
								// There's only one call, but we need to catch the caller
								$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][0] = 1;

								if ( empty( $this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][1]) ) {
									$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][1] = $f_trace;
								}

							} else {
								if ( isset( $this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"] ) ) {
									$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][0]++;
								} else {
									$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][0] = 1;
								}

								if ( empty( $this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][1]) ) {
									$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][1] = $f_trace;
								}
							}

						// Or create it
						} else {
							$this->scripts[ $slug['script'] ]['functions'][ $function ]['time'] = 0;
							// Record caller and line number
							$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][0] = 1;
							$this->scripts[ $slug['script'] ]['functions'][ $function ]['caller']["{$caller}@{$line_num}"][1] = $f_trace;
						}
					}
				// We don't have a slug: if there's an old function record, update it
				} else {
					if (! empty( $this->last_function ) ) {
						if ( isset( $this->scripts[ $this->last_script ]['functions'][ $this->last_function ]['time'] ) ) {
							$this->scripts[ $this->last_script ]['functions'][ $this->last_function ]['time'] += $elapse;
						}
					}
				}

				// Update all last records
				$this->last_script = $slug['script'];
				if (! empty( $slug['theme'] ) ) {
					$this->last_type = 'theme';
					$this->last_slug = $slug['theme'];
				} elseif (! empty( $slug['plugin'] ) ) {
					$this->last_type = 'plugin';
					$this->last_slug = $slug['plugin'];
				} else {
					$this->last_type = '';
					$this->last_slug = '';
				}
				$this->last_function = $function;
				$this->last_time = $stop;
			}
		}

		fclose( $fh );
		unlink( $this->tmp_ticks );
	}


	/**
	 * Parse and save DB queries.
	 */
	private function save_queries() {

		if (! file_exists( $this->tmp_queries ) ) {
			return;
		}
		$outfile = CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.".
						"{$this->profile_name}.queries.profile";
		$queries = unserialize( file_get_contents( $this->tmp_queries ) );
		unlink( $this->tmp_queries );

		if ( empty( $queries ) ) {
			return;
		}

		$exclusion = [
			/* "require('wp-blog-header.php')",
			"require_once('wp-load.php')",
			"require_once('wp-config.php')",
			"require_once('wp-settings.php')",
			"require_once('wp-includes/template-loader.php')",
			"do_action('plugins_loaded')",
			"do_action('init')",
			"WP_Hook->do_action",
			"WP_Hook->apply_filters",
			"get_site_transient",
			"get_site_option",
			"get_network_option",
			"get_option", */
			// We ignore Query Monitor otherwise
			// we'd blame it for most DB queries.
			"QM_DB->query"
		];

		foreach( $queries as $index => $query ) {

			$stack	= explode(', ', $query[2] );
			$buffer	= [];
			unset( $queries[ $index ][3] );
			unset( $queries[ $index ][4] );
			if ( isset( $queries[ $index ]['trace'] ) ) {
				unset( $queries[ $index ]['trace'] );
			}

			foreach( $stack as $value ) {

				if ( in_array( $value, $exclusion ) ) {
					continue;
				}

				// require/include
				if ( preg_match('/^(?:require|include)(?:_once)?\(\'([^\']+)\'\)$/', $value, $match ) ) {
					if ( $match[1][0] == '/') {
						if ( file_exists( WP_CONTENT_DIR . $match[1] ) ) {
							$buffer[]['n'] = [
								'n' => WP_CONTENT_DIR . $match[1],
								'f' => WP_CONTENT_DIR . $match[1],
								'l' => 1
							];
							continue;
						}
					// If the value doesn't start with a /, we only need
					// to prepend the ABSPATH
					} elseif ( file_exists( ABSPATH . $match[1] ) ) {
						$buffer[]['n'] = [
							'n' => ABSPATH . $match[1],
							'f' => ABSPATH . $match[1],
							'l' => 1
						];
						continue;
					}
					$buffer[]['n'] = $value;
					continue;
				}

				// action, filter etc
				if ( strpos( $value, '(') !== false ) {
					$buffer[]['n'] = $value;
					continue;
				}

				// Class, method and function
				$class		= '';
				$method		= '';
				$function	= '';
				$file			= '';
				$line			= 0;

				try {
					if ( strpos( $value, '->') !== false ) {
						list( $class, $function ) = explode('->', $value, 2 );

					} elseif ( strpos( $value, '::') !== false ) {
						list( $class, $function ) = explode('::', $value, 2 );

					} else {
						$function = $value;
					}

					if (! empty( $class ) ) {
						if (! class_exists( $class, false ) || ! method_exists( $class, $function ) ) {
							continue;
						}
						$rm	= new ReflectionMethod( $class, $function );
						$file	= $rm->getFileName();
						$line	= $rm->getStartLine();

					} else {
						if (! function_exists( $function ) ) {
							continue;
						}
						$rm	= new ReflectionFunction( $function );
						$file	= $rm->getFileName();
						$line	= $rm->getStartLine();
					}

				} catch ( ReflectionException $e ) {
					// nothing to do
				}

				if (! $file || ! $line ) {
					// We don't know what it is
					$buffer[]['n'] = $value;
					continue;
				}
				// We only care about plugins and the theme
				$type = $this->plugin_or_theme( $file );

				if (! empty( $type['theme'] ) ) {
					if ( empty( $queries[ $index ][3] ) ) {
						$queries[ $index ][3] = $type['theme'];
					}

				} elseif (! empty( $type['plugin'] ) ) {
					if ( empty( $queries[ $index ][3] ) ) {
						$queries[ $index ][3] = $type['plugin'];
					}
				}

				$buffer[]['n'] = [
					'n' => $value,
					'f' => $file,
					'l' => $line
				];
			}

			if ( empty( $queries[ $index ][3] ) ) {
				unset( $queries[ $index ] );

			} else {

				// Try to get the plugin/theme full name
				if ( isset( $this->slug2name[ $queries[ $index ][3] ] ) ) {
					$queries[ $index ][4] = $this->slug2name[ $queries[ $index ][3] ];
				} else {
					$queries[ $index ][4] = $queries[ $index ][3];
				}

				$queries[ $index ][2] = serialize( $buffer );
			}
		}

		file_put_contents( $outfile, serialize( $queries ) );
	}


	/**
	 * Parse and save IO list is TSV format.
	 */

	private function save_iolist() {

		$buffer = '';

		$fh = fopen( $this->tmp_iolist, 'rb');
		if ( $fh === false ) {
			$error = sprintf(
				esc_html__('Cannot open file for reading: %s', 'code-profiler-pro'),
				$this->tmp_iolist
			);
			$this->return_error( $error );
		}

		$count = 0;
		$rw = [
			'r'  => 'Read',
			'r+' => 'Read/Write',
			'w'  => 'Write',
			'w+' => 'Read/Write',
			'a'  => 'Write',
			'a+' => 'Read/Write',
			'x'  => 'Write',
			'x+' => 'Read/Write',
			'c'  => 'Write',
			'c+' => 'Read/Write',
			'e'  => 'close-on-exec'
		];
		while(! feof( $fh ) ) {
			$line = fgets( $fh );
			if ( preg_match( '`^\[(.+?)\]\[(.+?)\]$`', $line, $match ) ) {

				/* Text/plain CSV strings shouldn't be escaped */
				if ( $match[2] == 'unlink') {
					$mode = __('Delete file', 'code-profiler-pro');

				} elseif ( $match[2] == 'rename') {
					$mode = __('Rename file', 'code-profiler-pro');

				} elseif ( $match[2] == 'rmdir') {
					$mode = __('Remove directory', 'code-profiler-pro');

				} elseif ( $match[2] == 'opendir') {
					$mode = __('Open directory', 'code-profiler-pro');

				} elseif ( preg_match('`^(?:chmod|chgrp|chown|touch|mkdir)`', $match[2] ) ) {
					$metadata = explode(':', $match[2], 2 );

					if ( $metadata[0] == 'chmod') {
						$mode = sprintf(
							__('Change mode [chmod %s]', 'code-profiler-pro'),
							sprintf ("%04o", $metadata[1] & 0777)
						);
					} elseif ( $metadata[0] == 'mkdir') {
						$mode = sprintf(
							__('Make directory [%s]', 'code-profiler-pro'),
							sprintf ("%04o", $metadata[1] & 0777)
						);
					} elseif ( $metadata[0] == 'chgrp') {
						$mode = sprintf(
							__('Change group [chgrp %s]', 'code-profiler-pro'),
							$metadata[1]
						);
					} elseif ( $metadata[0] == 'chown') {
						$mode = sprintf(
							__('Change owner [chown %s]', 'code-profiler-pro'),
							$metadata[1]
						);
					} else { // touch
						$mode = __('Change timestamps', 'code-profiler-pro');
						if (! empty( $metadata[1] ) ) {
							$mode .= "[ {$metadata[1]} ]";
						}
					}

				} else { // fopen
					// Remove binary/text flag
					$match[2] = str_replace( ['t', 'b' ], '', $match[2] );
					if ( isset( $rw[ $match[2] ] ) ) {
						$mode = sprintf(
							__('Open file: %s [%s]', 'code-profiler-pro'),
							$rw[ $match[2] ],
							$match[2]
						);
					} else {
						$mode = sprintf(
							__('Open file [%s]', 'code-profiler-pro'),
							$match[2]
						);
					}
				}

				$file = $match[1];
				$buffer .= ++$count ."\t$file\t$mode\n";
			}
		}

		fclose( $fh );

		// Save content
		if ( empty( $buffer ) ){
			$error = sprintf(
				esc_html__('Buffer is empty, no data to save (%s)', 'code-profiler-pro'),
				'CODE_PROFILER_PRO_TMP_IOLIST_LOG'
			);
			$this->return_error( $error );
		}
		$res = file_put_contents(
			CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.iolist.profile",
			$buffer
		);
		if ( $res === false ) {
			$error = sprintf(
				esc_html__('Cannot open file for writting: %s', 'code-profiler-pro'),
				$this->tmp_iolist
			);
			$this->return_error( $error );
		}
		unlink( $this->tmp_iolist );
	}


	/**
	 * Save IO stats.
	 */
	private function save_iostats() {

		$buffer = json_decode( file_get_contents( $this->tmp_iostats ), true );

		if ( empty( $buffer ) ) {
			$error = sprintf(
				esc_html__('Cannot decode JSON-encode file (%s)', 'code-profiler-pro'),
				'CODE_PROFILER_PRO_TMP_IOSTATS_LOG'
			);
			$this->return_error( $error );
		}

		$lines = '';
		foreach( $buffer as $key => $value ) {
			$lines .= "$key\t$value\n";
			$this->total_io += $value;
		}

		file_put_contents(
			CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.iostats.profile",
			$lines
		);

		unlink( $this->tmp_iostats );
	}


	/**
	 * Save Read/Write stats.
	 */
	private function save_diskio() {

		rename(
			$this->tmp_diskio,
			CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.diskio.profile"
		);

	}


	 /**
	  * Save list of plugins/themes using composer.
	  */
	private function save_composer() {

		if (! empty( $this->composer ) ) {
			file_put_contents(
				CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.{$this->profile_name}.composer.profile",
				json_encode( $this->composer )
			);
		}
	}


	/**
	 * Save the list of external connections.
	 */
	private function save_connections() {

		if (! file_exists( $this->tmp_connections ) ) {
			return;
		}

		$outfile = CODE_PROFILER_PRO_UPLOAD_DIR ."/{$this->microtime}.".
					"{$this->profile_name}.connections.profile";

		$connections = json_decode( file_get_contents( $this->tmp_connections ), true );
		unlink( $this->tmp_connections );

		if ( empty( $connections ) ) {
			$error = sprintf(
				esc_html__('Cannot decode JSON-encode file (%s)', 'code-profiler-pro'),
				'CODE_PROFILER_PRO_TMP_CONNECTIONS_LOG'
			);
			$this->return_error( $error );
		}

		$buffer		= [];
		$index_cn	= 0;
		$index_bt	= 0;

		foreach( $connections as $connection => $array ) {
			$found = 0;
			$buffer[ $index_cn ]['time']	= $this->convert_2_seconds( $array['stop'] - $connection );
			$buffer[ $index_cn ]['url']	= $array['url'];
			$buffer[ $index_cn ]['code']	= $array['code'];
			$buffer[ $index_cn ]['msg']	= $array['msg'];

			foreach( $array['bt'] as $k =>$v ) {
				if ( isset( $v['file'] ) && isset( $v['function'] ) && isset( $v['line'] ) ) {
					if ( ! $found &&
						in_array( $v['function'], [
							'wp_remote_get',
							'wp_safe_remote_get',
							'wp_remote_post',
							'wp_safe_remote_post'
						] )
					) {
						$found = 1;

						$type = $this->plugin_or_theme( $v['file'] );
						if (! empty( $type['theme'] ) ) {
							$buffer[ $index_cn ]['slug'] = $type['theme'];
							// Save it for later use
							$this->cx_buffer['theme'][ $type['theme'] ] = $array['stop'] - $connection;

						} elseif (! empty( $type['plugin'] ) ) {
							$buffer[ $index_cn ]['slug'] = $type['plugin'];
							// Save it for later use
							$this->cx_buffer['plugin'][ $type['plugin'] ] = $array['stop'] - $connection;

						} else {
							$buffer[ $index_cn ]['slug'] = '';
						}

						if ( isset( $this->slug2name[ $buffer[ $index_cn ]['slug'] ] ) ) {
							$buffer[ $index_cn ]['name'] = $this->slug2name[ $buffer[ $index_cn ]['slug'] ];
						} else {
							$buffer[ $index_cn ]['name'] = '';
						}

					}
					if ( $found ) {
						$buffer[ $index_cn ]['backtrace'][ $index_bt ]['file'] = $v['file'];
						$buffer[ $index_cn ]['backtrace'][ $index_bt ]['line'] = $v['line'];
						if ( isset( $v['class'] ) ) {
							$buffer[ $index_cn ]['backtrace'][ $index_bt ]['function'] = "{$v['class']}{$v['type']}{$v['function']}";
						} elseif( isset( $v['function'] ) ) {
							$buffer[ $index_cn ]['backtrace'][ $index_bt ]['function'] = $v['function'];
						}
						$index_bt++;
					}
				}
			}
			$index_cn++;
		}

		if (! empty( $buffer ) ) {
			file_put_contents( $outfile, json_encode( $buffer ) );
		}
	}


}

// =====================================================================
// EOF
