<?php
/*
Plugin Name: LearnPress - Collections
Plugin URI: http://thimpress.com/learnpress
Description: Collecting related courses into one collection by administrator.
Author: ThimPress
Version: 3.0.3
Author URI: http://thimpress.com
Tags: learnpress, collections
Text Domain: learnpress-collections
Domain Path: /languages/
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'LP_ADDON_COLLECTIONS_FILE', __FILE__ );
define( 'LP_ADDON_COLLECTIONS_PATH', __DIR__ );
define( 'LP_ADDON_COLLECTIONS_VER', '3.0.3' );
define( 'LP_ADDON_COLLECTIONS_REQUIRE_VER', '3.0.0' );
define( 'LP_COLLECTIONS_VER', '3.0.3' );

/**
 * Class LP_Addon_Collections_Preload
 */
class LP_Addon_Collections_Preload {

	/**
	 * LP_Addon_Collections_Preload constructor.
	 */
	public function __construct() {
		add_action( 'learn-press/ready', array( $this, 'load' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		LP_Addon::load( 'LP_Addon_Collections', 'inc/load.php', __FILE__ );
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
						__( '<strong>%s</strong> addon version %s requires %s version %s or higher is <strong>installed</strong> and <strong>activated</strong>.', 'learnpress-collections' ),
						__( 'LearnPress Collections', 'learnpress-collections' ),
						LP_ADDON_COLLECTIONS_VER,
						sprintf( '<a href="%s" target="_blank"><strong>%s</strong></a>', admin_url( 'plugin-install.php?tab=search&type=term&s=learnpress' ), __( 'LearnPress', 'learnpress-collection' ) ),
						LP_ADDON_COLLECTIONS_REQUIRE_VER
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

new LP_Addon_Collections_Preload();