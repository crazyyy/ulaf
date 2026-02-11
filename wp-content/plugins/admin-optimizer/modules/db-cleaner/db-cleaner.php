<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB Optimization Class
 */
class DB_Cleaner {
	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var DB_Cleaner_Settings
	 */
	protected $settings;

	const OPTION_NAME = 'adminoptim_db_cleaner';

	const MENU_SLUG = 'adminoptimizer-db-cleaner';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'db-cleaner/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'db-cleaner/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		$this->settings = new DB_Cleaner_Settings( $this->options );

		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );

		add_action( 'wp_ajax_adminoptim_clean_db_now', [ $this, 'ajax_clean_db_now' ] );

		add_action( 'init', [ $this, 'schedule_database_cleanup' ] );
		add_action( 'adminoptim_database_cleanup', [ $this, 'clean_db_on_schedule' ] );
		add_action( 'adminoptim_database_remnant_cleanup', [ $this, 'clean_remnant' ] );

		add_action( 'adminoptim_deactivate_plugin', [ $this, 'remove_schedule_on_deactivation' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Database cleaner', 'admin-optimizer' ),
			__( 'Database Cleaner', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Schedule database optimization
	 *
	 * @return void
	 */
	public function schedule_database_cleanup() {
		if ( ! empty( $this->options['enable'] ) ) {
			$day       = $this->options['enable_day'] ?? 'sunday';
			$hour      = $this->options['enable_time_hour'] ?? '3'; // set default time as 3am.
			$min      = $this->options['enable_time_min'] ?? '0'; // set default time as 3am.
			$timestamp = strtotime( "this $day $hour hour $min minutes" );
			if ( $timestamp < time() ) {
				$timestamp = strtotime( "next $day $hour hour $min minutes" );
			}
			if ( ! as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
				as_schedule_recurring_action( $timestamp, WEEK_IN_SECONDS, 'adminoptim_database_cleanup', [], '', true );
			}
		} elseif ( as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
			as_unschedule_all_actions( 'adminoptim_database_cleanup' );
		}
	}

	/**
	 * Deactivate schedule
	 *
	 * @return void
	 */
	public function remove_schedule_on_deactivation(): void {
		if ( as_has_scheduled_action( 'adminoptim_database_cleanup' ) ) {
			as_unschedule_action( 'adminoptim_database_cleanup' );
		}
	}

	/**
	 * Clean database on schedule
	 *
	 * @return void
	 */
	public function clean_db_on_schedule() {
		set_time_limit( 300 );  // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged

		$log         = [ 'start' => time() ];
		$log_message = [];
		$cleaner     = new DB_Queries();
		if ( ! empty( $this->options['delete_revisions'] ) ) {
			$response = $cleaner->clean_revisions();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' revisions deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_auto_draft'] ) ) {
			$response = $cleaner->clean_auto_draft();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' Auto draft deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_trashed_posts'] ) ) {
			$response = $cleaner->clean_trashed_post();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' trashed posts deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_orphaned_postmeta'] ) ) {
			$response = $cleaner->clean_orphaned_postmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' orphaned post meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_duplicate_postmeta'] ) ) {
			$response = $cleaner->clean_duplicate_postmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' duplicate post meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_empty_postmeta'] ) ) {
			$response = $cleaner->clean_empty_postmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' empty post meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_oembed_cache'] ) ) {
			$response = $cleaner->clean_oembed_cache();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' oEmbed cache deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_unapproved_comments'] ) ) {
			$response = $cleaner->clean_comments();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' unapproved comments deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_spam_comments'] ) ) {
			$response = $cleaner->clean_comments( 'spam' );
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' spam comments deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_trashed_comments'] ) ) {
			$response = $cleaner->clean_comments( 'trash' );
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' trashed comments deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_duplicate_commentmeta'] ) ) {
			$response = $cleaner->clean_duplicate_commentmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' duplicate comment meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_orphaned_commentmeta'] ) ) {
			$response = $cleaner->clean_orphaned_commentmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' orphaned comment meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_empty_commmentmeta'] ) ) {
			$response = $cleaner->clean_empty_commentmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' empty comment meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_pingbacks'] ) ) {
			$response = $cleaner->clean_pingbacks();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' pingbacks deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_unused_terms'] ) ) {
			$response = $cleaner->clean_unused_terms();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' unused terms deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_duplicate_termmeta'] ) ) {
			$response = $cleaner->clean_duplicate_termmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' duplicate term meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_orphaned_termmeta'] ) ) {
			$response = $cleaner->clean_orphaned_termmeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' orphaned term meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_orphaned_term_rs'] ) ) {
			$response = $cleaner->clean_orphaned_term_rs();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' orphaned term relationship deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_duplicate_usermeta'] ) ) {
			$response = $cleaner->clean_duplicate_usermeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' duplicate user meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}
		if ( ! empty( $this->options['delete_orphaned_usermeta'] ) ) {
			$response = $cleaner->clean_orphaned_usermeta();
			if ( 'success' === $response['status'] ) {
				$log_message[] = esc_html( $response['count'] . __( ' orphaned user meta deleted', 'admin-optimizer' ) );
			} else {
				$log_message[] = esc_html( $response['message'] );
			}
		}

		$response = $cleaner->optimize_database();
		if ( 'success' === $response['status'] ) {
			$log_message[] = esc_html__( 'Optimized all tables in database', 'admin-optimizer' );
		} else {
			$log_message[] = $response['message'];
		}

		$log['end']       = time();
		$log['clean_log'] = implode( '%%', $log_message );
		if ( ! empty( $this->options['enable_logging'] ) ) {
			$db_log   = get_option( 'adminoptim_db_log', [] );
			$db_log[] = $log;
			if ( 5 < count( $db_log ) ) {
				array_shift( $db_log );
			}
			update_option( 'adminoptim_db_log', $db_log, false );
		}
	}

	/**
	 * Ajax function to clean db
	 *
	 * @return void
	 */
	public function ajax_clean_db_now() {
		check_ajax_referer( 'adminoptim-db-cleanup', 'nonce' );
		do_action( 'adminoptim_database_cleanup' );
		$response = [ 'message' => __( 'Database cleanup completed. Check the logs for more details.', 'admin-optimizer' ) ];
		wp_send_json_success( $response );
	}
}
