<?php
/*
 * Plugin Name: Alchemists League of Legends for SportsPress
 * Plugin URI: https://themeforest.net/user/dan_fisher
 * Description: A suite of League of Legends features for the Alchemists theme.
 * Author: Dan Fisher
 * Author URI: http://dan-fisher.com
 * Version: 1.0.0
 *
 * Text Domain: alc-lol
 * Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alchemists_SportsPress_LOL' ) ) :

/**
 * Main SportsPress League of Legends Class
 *
 * @class Alchemists_SportsPress_LOL
 * @version 1.0.0
 */
class Alchemists_SportsPress_LOL {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue_scripts' ) );
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );
		// Define default sport
		add_filter( 'sportspress_default_sport', array( $this, 'default_sport' ) );

		// Update SportsPress icons
		add_filter( 'sportspress_icons', array( $this, 'alc_sportspress_icons_esports' ) );
	}

	/**
	 * Install.
	*/
	public static function install() {

		// Event Results
		$event_results = array(
			'score' => array(
				'post_title'   => esc_html__( 'Score', 'alc-lol' ),
				'post_name'    => 'score',
				'post_type'    => 'sp_result',
				'post_excerpt' => esc_html__( 'Score', 'alc-lol' ),
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
			'cs' => array(
				'post_title' => 'CS',
				'post_name' => 'cs',
				'post_type' => 'sp_performance',
				'post_excerpt' => esc_html__( 'Creep Score', 'alc-lol' ),
				'menu_order' => 21,
				'post_status' => 'publish',
				'sp_icon' => 'alc-skull',
				'sp_color' => '#ffffff'
			),
			'dmg' => array(
				'post_title' => 'Damage',
				'post_name' => 'dmg',
				'post_type' => 'sp_performance',
				'post_excerpt' => esc_html__( 'Damage Dealt', 'alc-lol' ),
				'menu_order' => 22,
				'post_status' => 'publish',
				'sp_icon' => 'alc-explosion',
				'sp_color' => '#ffffff'
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
			update_post_meta( $id, 'sp_icon', $post_slug['sp_icon'] );
			update_post_meta( $id, 'sp_color', $post_slug['sp_color'] );
		}


		// Set Performances to update
		$posts_to_update = array(
			'deaths' => array(
				'post_name' => 'deaths',
				'sp_icon' => 'alc-dead-face',
				'sp_color' => '#ffffff'
			),
			'kills' => array(
				'post_name' => 'kills',
				'sp_icon' => 'alc-crosshair',
				'sp_color' => '#ffffff'
			),
			'assists' => array(
				'post_name' => 'assists',
				'sp_icon' => 'alc-thumbs-up',
				'sp_color' => '#ffffff'
			),
			'gold' => array(
				'post_name' => 'gold',
				'sp_icon' => 'alc-coin',
				'sp_color' => '#ffffff'
			),
		);

		foreach ( $posts_to_update as $post => $post_slug ) {
			$id = 0;
			if ( $post = get_page_by_path( $post_slug['post_name'], OBJECT, 'sp_performance' ) ) {
				$id = $post->ID;
			}
			// update labels
			update_post_meta( $id, 'sp_icon', $post_slug['sp_icon'] );
			update_post_meta( $id, 'sp_color', $post_slug['sp_color'] );
		}



		// Player Statistics
		$player_stats = array(
			'avgkills' => array(
				'post_title'   => esc_html__( 'Avg. Kills', 'alc-lol' ),
				'post_name'    => 'avgkills',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => esc_html__( 'Average Total Kills', 'alc-lol' ),
				'menu_order'   => 210,
				'post_status'  => 'publish',
				'sp_icon'      => 'alc-crosshair',
				'sp_color'     => '#ffffff'
			),
			'avgdeaths' => array(
				'post_title'   => esc_html__( 'Avg. Deaths', 'alc-lol' ),
				'post_name'    => 'avgdeaths',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => esc_html__( 'Average Total Deaths', 'alc-lol' ),
				'menu_order'   => 220,
				'post_status'  => 'publish',
				'sp_icon'      => 'alc-dead-face',
				'sp_color'     => '#ffffff'
			),
			'avgassists' => array(
				'post_title'   => esc_html__( 'Avg. Assists', 'alc-lol' ),
				'post_name'    => 'avgassists',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => esc_html__( 'Average Total Assists', 'alc-lol' ),
				'menu_order'   => 230,
				'post_status'  => 'publish',
				'sp_icon'      => 'alc-thumbs-up',
				'sp_color'     => '#ffffff'
			),
			'avgdmg' => array(
				'post_title'   => esc_html__( 'Avg. Damage', 'alc-lol' ),
				'post_name'    => 'avgdmg',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => esc_html__( 'Average Total Damage', 'alc-lol' ),
				'menu_order'   => 240,
				'post_status'  => 'publish',
				'sp_icon'      => 'alc-explosion',
				'sp_color'     => '#ffffff'
			),
			'killsp' => array(
				'post_title'   => esc_html__( 'Kills P.', 'alc-lol' ),
				'post_name'    => 'killsp',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => esc_html__( 'Kills Participation', 'alc-lol' ),
				'menu_order'   => 250,
				'post_status'  => 'publish',
				'sp_icon'      => 'alc-drop',
				'sp_color'     => '#ffffff'
			),
			'winrate' => array(
				'post_title'   => esc_html__( 'Win Rate', 'alc-lol' ),
				'post_name'    => 'winrate',
				'post_type'    => 'sp_statistic',
				'post_excerpt' => esc_html__( 'Win Rate', 'alc-lol' ),
				'menu_order'   => 260,
				'post_status'  => 'publish',
				'sp_icon'      => 'alc-trophy',
				'sp_color'     => '#ffffff'
			),
		);

		// Insert our fields as posts
		foreach ( $player_stats as $player_stat => $player_stat_slug ) {
			
			// don't insert a post if it already exists
			if ( get_page_by_path( $player_stat_slug['post_name'], OBJECT, 'sp_statistic' ) ) continue;

			// insert post
			$player_stat_id = wp_insert_post( $player_stat_slug );

			// Kills per minute
			if ( $player_stat_slug['post_name'] == 'avgkills' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$kills / $eventsplayed' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Deaths per minute
			if ( $player_stat_slug['post_name'] == 'avgdeaths' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$deaths / $eventsplayed' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Assists per minute
			if ( $player_stat_slug['post_name'] == 'avgassists' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$assists / $eventsplayed' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Damage per minute
			if ( $player_stat_slug['post_name'] == 'avgdmg' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$dmg / $eventsplayed' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// Kills participation
			if ( $player_stat_slug['post_name'] == 'killsp' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '( $kills + $assists ) / $killsfor * 100' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 0 );
			}

			// Win rate
			if ( $player_stat_slug['post_name'] == 'winrate' ) {
				update_post_meta( $player_stat_id, 'sp_format', 'number' );
				update_post_meta( $player_stat_id, 'sp_equation', '$win / $eventsplayed * 100' );
				update_post_meta( $player_stat_id, 'sp_type', 'average' );
				update_post_meta( $player_stat_id, 'sp_precision', 1 );
			}

			// update labels
			update_post_meta( $player_stat_id, 'sp_singular', $player_stat_slug['post_title'] );
			update_post_meta( $player_stat_id, 'sp_icon', $player_stat_slug['sp_icon'] );
			update_post_meta( $player_stat_id, 'sp_color', $player_stat_slug['sp_color'] );
		}


		// Player Metrics
		$player_metrics = array(
			'fname' => array(
				'post_title'   => esc_html__( 'Name', 'alc-lol' ),
				'post_name'    => 'fname',
				'post_type'    => 'sp_metric',
				'post_excerpt' => esc_html__( 'First Name', 'alc-lol' ),
				'menu_order'   => 200,
				'post_status'  => 'publish',
			),
			'lname' => array(
				'post_title'   => esc_html__( 'Surname', 'alc-lol' ),
				'post_name'    => 'lname',
				'post_type'    => 'sp_metric',
				'post_excerpt' => esc_html__( 'Last Name', 'alc-lol' ),
				'menu_order'   => 210,
				'post_status'  => 'publish',
			),
			'role' => array(
				'post_title'   => esc_html__( 'Role', 'alc-lol' ),
				'post_name'    => 'lname',
				'post_type'    => 'sp_metric',
				'post_excerpt' => esc_html__( 'Role', 'alc-lol' ),
				'menu_order'   => 220,
				'post_status'  => 'publish',
			),
		);

		// Insert our fields as posts
		foreach ( $player_metrics as $player_metric => $player_metric_slug ) {
			
			// don't insert a post if it already exists
			if ( get_page_by_path( $player_metric_slug['post_name'], OBJECT, 'sp_metric' ) ) continue;

			// insert post
			$player_metric_id = wp_insert_post( $player_metric_slug );

			// update labels
			update_post_meta( $player_metric_id, 'sp_singular', $player_metric_slug['post_title'] );
		}



		// Table Columns
		$table_columns = array(
			'kills' => array(
				'post_title'   => esc_html__( 'Kills', 'alc-lol' ),
				'post_name'    => 'kills',
				'post_type'    => 'sp_column',
				'post_excerpt' => esc_html__( 'Total Kills', 'alc-lol' ),
				'menu_order'   => 210,
				'post_status'  => 'publish',
			),
			'deaths' => array(
				'post_title'   => esc_html__( 'Deaths', 'alc-lol' ),
				'post_name'    => 'deaths',
				'post_type'    => 'sp_column',
				'post_excerpt' => esc_html__( 'Total Deaths', 'alc-lol' ),
				'menu_order'   => 220,
				'post_status'  => 'publish',
			),
			'assists' => array(
				'post_title'   => esc_html__( 'Assists', 'alc-lol' ),
				'post_name'    => 'assists',
				'post_type'    => 'sp_column',
				'post_excerpt' => esc_html__( 'Total Assists', 'alc-lol' ),
				'menu_order'   => 230,
				'post_status'  => 'publish',
			),
			'avgkdaratio' => array(
				'post_title'   => esc_html__( 'KDA.R', 'alc-lol' ),
				'post_name'    => 'avgkdaratio',
				'post_type'    => 'sp_column',
				'post_excerpt' => esc_html__( 'Average kills/deaths/assists ratio', 'alc-lol' ),
				'menu_order'   => 240,
				'post_status'  => 'publish',
			),
			'goldpermin' => array(
				'post_title'   => esc_html__( 'GPM', 'alc-lol' ),
				'post_name'    => 'goldpermin',
				'post_type'    => 'sp_column',
				'post_excerpt' => esc_html__( 'Gold per minute', 'alc-lol' ),
				'menu_order'   => 250,
				'post_status'  => 'publish',
			),
			'winrate' => array(
				'post_title'   => esc_html__( 'Win %', 'alc-lol' ),
				'post_name'    => 'winrate',
				'post_type'    => 'sp_column',
				'post_excerpt' => esc_html__( 'Win Rate', 'alc-lol' ),
				'menu_order'   => 260,
				'post_status'  => 'publish',
			),
		);


		// Insert our fields as posts
		foreach ( $table_columns as $table_column => $table_column_slug ) {
			
			// don't insert a post if it already exists
			if ( get_page_by_path( $table_column_slug['post_name'], OBJECT, 'sp_column' ) ) continue;

			// insert post
			$table_column_id = wp_insert_post( $table_column_slug );

			// Kills
			if ( $table_column_slug['post_name'] == 'kills' ) {
				update_post_meta( $table_column_id, 'sp_equation', '$killsfor' );
				update_post_meta( $table_column_id, 'sp_precision', 0 );
			}

			// Deaths
			if ( $table_column_slug['post_name'] == 'deaths' ) {
				update_post_meta( $table_column_id, 'sp_equation', '$deathsfor' );
				update_post_meta( $table_column_id, 'sp_precision', 0 );
			}

			// Assists
			if ( $table_column_slug['post_name'] == 'assists' ) {
				update_post_meta( $table_column_id, 'sp_equation', '$assistsfor' );
				update_post_meta( $table_column_id, 'sp_precision', 0 );
			}

			// KDA.R
			if ( $table_column_slug['post_name'] == 'avgkdaratio' ) {
				update_post_meta( $table_column_id, 'sp_equation', '( $killsfor + $assistsfor ) / $deathsfor' );
				update_post_meta( $table_column_id, 'sp_precision', 1 );
			}

			// GPM
			if ( $table_column_slug['post_name'] == 'goldpermin' ) {
				update_post_meta( $table_column_id, 'sp_equation', '$goldfor / $eventminutes' );
				update_post_meta( $table_column_id, 'sp_precision', 1 );
			}

			// Win rate
			if ( $table_column_slug['post_name'] == 'winrate' ) {
				update_post_meta( $table_column_id, 'sp_equation', '$win / $eventsplayed * 100' );
				update_post_meta( $table_column_id, 'sp_precision', 1 );
			}

			// update labels
			update_post_meta( $table_column_id, 'sp_singular', $table_column_slug['post_title'] );
		}
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'ALC_LOL_VERSION' ) ) {
			define( 'ALC_LOL_VERSION', '1.0.0' );
		}

		if ( !defined( 'ALC_LOL_URL' ) ) {
			define( 'ALC_LOL_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( !defined( 'ALC_LOL_DIR' ) ) {
			define( 'ALC_LOL_DIR', plugin_dir_path( __FILE__ ) );
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'alc-lol' );
		
		// Global + Frontend Locale
		load_textdomain( 'alc-lol', WP_LANG_DIR . "/alc-lol/alc-lol-$locale.mo" );
		load_plugin_textdomain( 'alc-lol', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
	}

	/**
	 * Enqueue admin styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'alc-lol-admin', ALC_LOL_URL . 'assets/css/admin.css', array( 'sportspress-admin-menu-styles' ), ALC_LOL_VERSION );
	}

	/**
	 * Enqueue front styles.
	 */
	public static function front_enqueue_scripts() {
		wp_enqueue_style( 'alc-lol-front', ALC_LOL_URL . 'assets/css/alc-lol.css', array( 'sportspress-icons' ), ALC_LOL_VERSION );
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
	 * Text filter.
	 */
	public function gettext( $translated_text, $untranslated_text, $domain ) {
		if ( $domain == 'sportspress' ) {
			switch ( $untranslated_text ) {
				case 'Positions':
					$translated_text = __( 'Characters', 'alc-lol' );
					break;
				case 'Position':
					$translated_text = __( 'Character', 'alc-lol' );
					break;
				case 'Edit Position':
					$translated_text = __( 'Edit Character', 'alc-lol' );
					break;
			}
		}
		
		return $translated_text;
	}

	/**
	 * Define default sport.
	*/
	public function default_sport() {
		return 'lol';
	}

	/**
	 * Update SportsPress icons.
	*/
	public function alc_sportspress_icons_esports() {
		$icons = array(
			'soccerball',
			'soccerball-alt',
			'baseball',
			'baseball-alt',
			'basketball',
			'golfball',
			'cricketball',
			'bowling',
			'ice-hockey',
			'football',
			'poolball',
			'table-tennis',
			'tennis',
			'racing-flag',
			'shoe',
			'card',
			'league',
			'shield',
			'tshirt',
			'whistle',
			'time',
			'friendly',
			'sub',
			'update',
			'undo',
			'redo',
			'marker',
			'no',
			'heart',
			'star-filled',
			'alc-medic',
			'alc-dead-face',
			'alc-crosshair',
			'alc-coin',
			'alc-plier',
			'alc-skull',
			'alc-trophy',
			'alc-stopwatch2',
			'alc-thumbs-up',
			'alc-shield',
			'alc-fire',
			'alc-explosion',
			'alc-stopwatch',
			'alc-drop',
			'alc-gamepad',
		);

		return $icons;
	}

}

endif;

new Alchemists_SportsPress_LOL();
