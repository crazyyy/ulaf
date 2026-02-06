<?php
/*
 * Plugin Name: Alchemists Soccer for SportsPress
 * Plugin URI: https://themeforest.net/user/dan_fisher
 * Description: A suite of Soccer features for Alchemists.
 * Author: Dan Fisher
 * Author URI: https://themeforest.net/user/dan_fisher
 * Version: 0.1.1
 *
 * Text Domain: alc-soccer
 * Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alchemists_SportsPress_Soccer' ) ) :

/**
 * Main SportsPress Soccer Class
 *
 * @class Alchemists_SportsPress_Soccer
 * @version	0.1.1
 */
class Alchemists_SportsPress_Soccer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Define constants
		$this->define_constants();
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		add_filter( 'sportspress_text', array( $this, 'add_text_options' ) );
		add_filter( 'sportspress_event_box_score_labels', array( $this, 'box_score_labels' ), 10, 3 );
		add_filter( 'sportspress_match_stats_labels', array( $this, 'stats_labels' ) );
		add_filter( 'sportspress_event_performance_players', array( $this, 'players' ), 10, 4 );

		// Define default sport
		add_filter( 'sportspress_default_sport', array( $this, 'default_sport' ) );
	}

	/**
	 * Install.
	*/
	public static function install() {
    
    // Player Performance fields
    $posts = array(
      'min' => array(
        'post_title' => 'MIN',
        'post_name' => 'min',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Minutes',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'sh' => array(
        'post_title' => 'SH',
        'post_name' => 'sh',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Shots',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'sog' => array(
        'post_title' => 'SOG',
        'post_name' => 'sog',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Shots on Goal',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'shpercent' => array(
        'post_title' => 'SH%',
        'post_name' => 'shpercent',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Shot Accuracy',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'pka' => array(
        'post_title' => 'PKA',
        'post_name' => 'pka',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Penalty Kick Attempts',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'pkg' => array(
        'post_title' => 'PKG',
        'post_name' => 'pkg',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Penalty Kick Goals',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'pa' => array(
        'post_title' => 'PA',
        'post_name' => 'pa',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Pass Attempts',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'ps' => array(
        'post_title' => 'PS',
        'post_name' => 'ps',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Pass Success',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'keyp' => array(
        'post_title' => 'KP',
        'post_name' => 'kp',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Key Pass',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'passpercent' => array(
        'post_title' => 'P%',
        'post_name' => 'passpercent',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Pass Accuracy',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'drb' => array(
        'post_title' => 'DRB',
        'post_name' => 'drb',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Dribbles',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      's' => array(
        'post_title' => 'S',
        'post_name' => 's',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Saves',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'f' => array(
        'post_title' => 'F',
        'post_name' => 'f',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Fouls',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'off' => array(
        'post_title' => 'OFF',
        'post_name' => 'off',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Offsides',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'ck' => array(
        'post_title' => 'CK',
        'post_name' => 'ck',
        'post_type' => 'sp_performance',
        'post_excerpt' => 'Corner Kick',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
    );

    // Insert our fields as posts
    foreach ( $posts as $post => $post_slug ) {

      // don't insert a post if it already exists
      if ( get_page_by_path( $post_slug['post_name'], OBJECT, 'sp_performance' ) ) continue;

      // insert post
      $id = wp_insert_post( $post_slug );

      // Shot Accuracy
      if ( $post_slug['post_name'] == 'shpercent' ) {
        update_post_meta( $id, 'sp_format', 'equation' );
        update_post_meta( $id, 'sp_equation', '( $sog / $sh ) * 100' );
        update_post_meta( $id, 'sp_precision', 1 );
      }

      // Pass Accuracy
      if ( $post_slug['post_name'] == 'passpercent' ) {
        update_post_meta( $id, 'sp_format', 'equation' );
        update_post_meta( $id, 'sp_equation', '( $ps / $pa ) * 100' );
        update_post_meta( $id, 'sp_precision', 1 );
      }

      // update labels
      update_post_meta( $id, 'sp_singular', $post_slug['post_title'] );
      update_post_meta( $id, 'sp_timed', 0 );
    }


    // Event Results
    $event_results = array(
      'poss' => array(
        'post_title' => 'Possession',
        'post_name' => 'poss',
        'post_type' => 'sp_result',
        'post_excerpt' => 'Possession',
        'menu_order' => 200,
        'post_status' => 'publish',
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


    // Player Statistics
    $player_stats = array(
      'gpg' => array(
        'post_title' => 'GPG',
        'post_name' => 'gpg',
        'post_type' => 'sp_statistic',
        'post_excerpt' => 'Goals per Game',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'apg' => array(
        'post_title' => 'APG',
        'post_name' => 'apg',
        'post_type' => 'sp_statistic',
        'post_excerpt' => 'Assists per Game',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'shpercent' => array(
        'post_title' => 'SH%',
        'post_name' => 'shpercent',
        'post_type' => 'sp_statistic',
        'post_excerpt' => 'Shot Accuracy',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'passpercent' => array(
        'post_title' => 'P%',
        'post_name' => 'passpercent',
        'post_type' => 'sp_statistic',
        'post_excerpt' => 'Pass Accuracy',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'perf' => array(
        'post_title' => 'Perf',
        'post_name' => 'perf',
        'post_type' => 'sp_statistic',
        'post_excerpt' => 'Performance',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
      'penpercent' => array(
        'post_title' => 'Pen%',
        'post_name' => 'penpercent',
        'post_type' => 'sp_statistic',
        'post_excerpt' => 'Penalty Kick Accuracy',
        'menu_order' => 200,
        'post_status' => 'publish',
      ),
    );

    // Insert our fields as posts
    foreach ( $player_stats as $player_stat => $player_stat_slug ) {
      
      // don't insert a post if it already exists
      if ( get_page_by_path( $player_stat_slug['post_name'], OBJECT, 'sp_statistic' ) ) continue;

      // insert post
      $player_stat_id = wp_insert_post( $player_stat_slug );  


      // Goals per Game
      if ( $player_stat_slug['post_name'] == 'gpg' ) {
        update_post_meta( $player_stat_id, 'sp_format', 'number' );
        update_post_meta( $player_stat_id, 'sp_equation', '$goals / $eventsplayed' );
        update_post_meta( $player_stat_id, 'sp_type', 'average' );
        update_post_meta( $player_stat_id, 'sp_precision', 2 );
      }

      // Assists per Game
      if ( $player_stat_slug['post_name'] == 'apg' ) {
        update_post_meta( $player_stat_id, 'sp_format', 'number' );
        update_post_meta( $player_stat_id, 'sp_equation', '$assists / $eventsplayed' );
        update_post_meta( $player_stat_id, 'sp_type', 'average' );
        update_post_meta( $player_stat_id, 'sp_precision', 2 );
      }

      // Shot Accuracy
      if ( $player_stat_slug['post_name'] == 'shpercent' ) {
        update_post_meta( $player_stat_id, 'sp_format', 'number' );
        update_post_meta( $player_stat_id, 'sp_equation', '$sog / $sh * 100' );
        update_post_meta( $player_stat_id, 'sp_type', 'average' );
        update_post_meta( $player_stat_id, 'sp_precision', 0 );
      }

      // Pass Accuracy
      if ( $player_stat_slug['post_name'] == 'passpercent' ) {
        update_post_meta( $player_stat_id, 'sp_format', 'number' );
        update_post_meta( $player_stat_id, 'sp_equation', '$ps / $pa * 100' );
        update_post_meta( $player_stat_id, 'sp_type', 'average' );
        update_post_meta( $player_stat_id, 'sp_precision', 0 );
      }

      // Performace
      if ( $player_stat_slug['post_name'] == 'perf' ) {
        update_post_meta( $player_stat_id, 'sp_format', 'number' );
        update_post_meta( $player_stat_id, 'sp_equation', '( ( $sog / $sh ) + ( $ps / $pa ) ) * 100 / 2 ' );
        update_post_meta( $player_stat_id, 'sp_type', 'average' );
        update_post_meta( $player_stat_id, 'sp_precision', 0 );
      }

      // Penalty Accuracy
      if ( $player_stat_slug['post_name'] == 'penpercent' ) {
        update_post_meta( $player_stat_id, 'sp_format', 'number' );
        update_post_meta( $player_stat_id, 'sp_equation', '$pkg / $pka * 100 ' );
        update_post_meta( $player_stat_id, 'sp_type', 'average' );
        update_post_meta( $player_stat_id, 'sp_precision', 0 );
      }

      // update labels
      update_post_meta( $player_stat_id, 'sp_singular', $player_stat_slug['post_title'] );
    }
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'ALC_SOCCER_VERSION' ) )
			define( 'ALC_SOCCER_VERSION', '0.1.1' );

		if ( !defined( 'ALC_SOCCER_URL' ) )
			define( 'ALC_SOCCER_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'ALC_SOCCER_DIR' ) )
			define( 'ALC_SOCCER_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'alc-soccer' );
		
		// Global + Frontend Locale
		load_textdomain( 'alc-soccer', WP_LANG_DIR . "/alc-soccer/alc-soccer-$locale.mo" );
		load_plugin_textdomain( 'alc-soccer', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
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
				'version'     => '2.3',
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
	 * Add text options 
	 */
	public function add_text_options( $options = array() ) {
		return array_merge( $options, array(
			__( 'MINS', 'sportspress' ),
		) );
	}

	/**
	 * Hide own goals from box score.
	*/
	public function box_score_labels( $labels = array(), $event = null, $mode = 'values' ) {
		if ( 'values' == $mode ) {
			unset( $labels['mins'] );
		}
		return $labels;
	}

	/**
	 * Hide own goals from match stats.
	*/
	public function stats_labels( $labels = array() ) {
		unset( $labels['mins'] );
		return $labels;
	}

	/**
	 * Append own goals to box score.
	*/
	public function players( $data = array(), $lineups = array(), $subs = array(), $mode = 'values' ) {
		if ( 'icons' == $mode ) return $data;

		foreach ( $data as $id => $performance ) {
			$mins = sp_array_value( $performance, 'mins', 0 );
			if ( $mins ) {
				$option = sp_get_main_performance_option();
				$goals = sp_array_value( $performance, $option, 0 );
				if ( $goals ) {
					$data[ $id ][ $option ] = $goals . ', ' . $mins . ' ' . __( 'MINS', 'alc-soccer' );
				} else {
					$data[ $id ][ $option ] = $mins . ' ' . __( 'MINS', 'alc-soccer' );
				}
			}
		}

		return $data;
	}

	/**
	 * Define default sport.
	*/
	public function default_sport() {
		return 'soccer';
	}
}

endif;

new Alchemists_SportsPress_Soccer();
