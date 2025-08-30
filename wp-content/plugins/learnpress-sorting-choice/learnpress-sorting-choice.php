<?php
/*
Plugin Name: LearnPress - Sorting Choice Question
Plugin URI: http://thimpress.com/learnpress
Description: Sorting Choice provide ability to sorting the options of a question to the right order.
Author: ThimPress
Version: 3.0.1
Author URI: http://thimpress.com
Tags: learnpress, lms, add-on, sorting-choice
Text Domain: learnpress-sorting-choice
Domain Path: /languages/
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'LP_ADDON_SORTING_CHOICE_FILE', __FILE__ );
define( 'LP_ADDON_SORTING_CHOICE_VER', '3.0.1' );
define( 'LP_ADDON_SORTING_CHOICE_REQUIRE_VER', '3.0.0' );
define( 'LP_QUESTION_SORTING_CHOICE_VER', '3.0.1' );

/**
 * Class LP_Addon_Sorting_Choice_Preload
 */
class LP_Addon_Sorting_Choice_Preload {

	/**
	 * LP_Addon_Sorting_Choice_Preload constructor.
	 */
	public function __construct() {
		add_action( 'learn-press/ready', array( $this, 'load' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		LP_Addon::load( 'LP_Addon_Sorting_Choice', 'inc/load.php', __FILE__ );
		remove_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notice
	 */
	public function admin_notices() {
		?>
        <div class="error">
            <p><?php echo wp_kses(
					sprintf(
						__( '<strong>%s</strong> addon version %s requires %s version %s or higher is <strong>installed</strong> and <strong>activated</strong>.', 'learnpress-sorting-choice' ),
						__( 'LearnPress Sorting Choice', 'learnpress-sorting-choice' ),
						LP_ADDON_SORTING_CHOICE_VER,
						sprintf( '<a href="%s" target="_blank"><strong>%s</strong></a>', admin_url( 'plugin-install.php?tab=search&type=term&s=learnpress' ), __( 'LearnPress', 'learnpress-sorting-choice' ) ),
						LP_ADDON_SORTING_CHOICE_REQUIRE_VER
					),
					array(
						'a'      => array(
							'href'  => array(),
							'blank' => array()
						),
						'strong' => array()
					)
				); ?>
            </p>
        </div>
		<?php
	}
}

new LP_Addon_Sorting_Choice_Preload();