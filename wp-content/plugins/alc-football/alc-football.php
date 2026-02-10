<?php
/*
 * Plugin Name: Alchemists American Football for SportsPress
 * Plugin URI: https://themeforest.net/user/dan_fisher
 * Description: A suite of American Football features for the Alchemists theme.
 * Author: Dan Fisher
 * Author URI: https://themeforest.net/user/dan_fisher
 * Version: 0.2.0
 *
 * Text Domain: alc-football
 * Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alchemists_SportsPress_Football' ) ) :

/**
 * Main SportsPress American Football Class
 *
 * @class Alchemists_SportsPress_Football
 * @version 0.1.0
 */
class Alchemists_SportsPress_Football {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Define constants
		$this->define_constants();
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		// Define default sport
		add_filter( 'sportspress_default_sport', array( $this, 'default_sport' ) );
	}

	/**
	 * Install.
	*/
	public static function install() {

		// Event Results
		$event_results = array(
			'poss' => array(
				'post_title'   => 'Possession',
				'post_name'    => 'poss',
				'post_type'    => 'sp_result',
				'post_excerpt' => 'Possession',
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
		);

		// Insert our fields as posts
		foreach ( $event_results as $event_result => $event_result_slug ) {
			
			// don't insert a post if it already exists
			if ( get_page_by_path( $event_result_slug['post_name'], OBJECT, 'sp_result' ) ) continue;

			// insert post
			$event_post_id = wp_insert_post( $event_result_slug );  

			// update labels
			update_post_meta( $event_post_id, 'sp_singular', $event_result_slug['post_title'] );
		}



		// Player Performance fields
		$posts = array(
			'fga' => array(
				'post_title' => 'FG Att',
				'post_name' => 'fga',
				'post_type' => 'sp_performance',
				'post_excerpt' => 'Field Goal Attempts',
				'menu_order' => 21,
				'post_status' => 'publish',
			),
			'fgm' => array(
				'post_title' => 'FGM',
				'post_name' => 'fgm',
				'post_type' => 'sp_performance',
				'post_excerpt' => 'Field Goal Made',
				'menu_order' => 22,
				'post_status' => 'publish',
			),
		);

		// Insert our fields as posts
		foreach ( $posts as $post => $post_slug ) {

			// don't insert a post if it already exists
			if ( get_page_by_path( $post_slug['post_name'], OBJECT, 'sp_performance' ) ) continue;

			// insert post
			$id = wp_insert_post( $post_slug );

			// update labels
			update_post_meta( $id, 'sp_singular', $post_slug['post_title'] );
			update_post_meta( $id, 'sp_timed', 0 );
		}



		// Player Statistics
		$player_stats = array(
			'eventsplayedpercent' => array(
				'post_title'   => 'G%',
				'post_name'    => 'eventsplayedpercent',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => 'Played percentage',
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
			'cmppercent' => array(
				'post_title'   => 'CMP%',
				'post_name'    => 'cmppercent',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => 'Completion percentage',
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
			'tdavg' => array(
				'post_title'   => 'TD Avg',
				'post_name'    => 'tdavg',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => 'Touchdowns average',
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
			'tdpercent' => array(
				'post_title'   => 'TD%',
				'post_name'    => 'tdpercent',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => 'Touchdowns percentage',
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
			'fgpercent' => array(
				'post_title'   => 'FG%',
				'post_name'    => 'fgpercent',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => 'Field Goal percentage',
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
		);

		// Insert our fields as posts
		foreach ( $player_stats as $player_stat => $player_stat_slug ) {
			
			// don't insert a post if it already exists
			if ( get_page_by_path( $player_stat_slug['post_name'], OBJECT, 'sp_statistic' ) ) continue;

			// insert post
			$player_stat_id = wp_insert_post( $player_stat_slug );

			// Played percentage
			if ( $player_stat_slug['post_name'] == 'eventsplayedpercent' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$eventsplayed / $eventsattended * 100' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Field Goal percentage
			if ( $player_stat_slug['post_name'] == 'fgpercent' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$fgm / $fga * 100' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Completion percentage
			if ( $player_stat_slug['post_name'] == 'cmppercent' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$comp / $att * 100' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Touchdowns average
			if ( $player_stat_slug['post_name'] == 'tdavg' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$td / $eventsplayed' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 2 );
			}

			// Touchdowns percentage
			if ( $player_stat_slug['post_name'] == 'tdpercent' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$td / $att * 100' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// update labels
			update_post_meta( $player_stat_id, 'sp_singular', $player_stat_slug['post_title'] );
		}
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'ALC_FOOTBALL_VERSION' ) ) {
			define( 'ALC_FOOTBALL_VERSION', '0.2.0' );
		}

		if ( !defined( 'ALC_FOOTBALL_URL' ) ) {
			define( 'ALC_FOOTBALL_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( !defined( 'ALC_FOOTBALL_DIR' ) ) {
			define( 'ALC_FOOTBALL_DIR', plugin_dir_path( __FILE__ ) );
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'alc-football' );
		
		// Global + Frontend Locale
		load_textdomain( 'alc-football', WP_LANG_DIR . "/alc-football/alc-football-$locale.mo" );
		load_plugin_textdomain( 'alc-football', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
	}

	/**
	 * Enqueue styles.
	 */
	public static function admin_enqueue_scripts() {

		wp_enqueue_style( 'sportspress-football-admin', ALC_FOOTBALL_URL . 'css/admin.css', array( 'sportspress-admin-menu-styles' ), '0.2.0' );
	}

	/**
	 * Require SportsPress core.
	*/
	public static function require_core() {
		$plugins = array(
			array(
				'name'        => 'SportsPress',
				'slug'        => 'sportspress',
				'required'    => true,
				'version'     => '2.5.5',
				'is_callable' => array( 'SportsPress', 'instance' ),
			),
		);

		$config = array(
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,
			'dismissable'  => true,
			'is_automatic' => true,
			'message'      => '',
			'strings'      => array(
				'nag_type' => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}

	/**
	 * Define default sport.
	*/
	public function default_sport() {
		return 'football';
	}
}

endif;

new Alchemists_SportsPress_Football();
