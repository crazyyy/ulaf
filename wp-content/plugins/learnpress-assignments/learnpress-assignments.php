<?php
/*
Plugin Name: LearnPress - Assignments
Plugin URI: http://thimpress.com/learnpress
Description: Assignments add-on for LearnPress.
Author: ThimPress
Version: 3.1.4
Author URI: http://thimpress.com
Tags: learnpress, lms, assignment
Text Domain: learnpress-assignments
Domain Path: /languages/
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'LP_ADDON_ASSIGNMENT_FILE', __FILE__ );
define( 'LP_ADDON_ASSIGNMENT_PATH', dirname( __FILE__ ) );
define( 'LP_ADDON_ASSIGNMENT_INC_PATH', LP_ADDON_ASSIGNMENT_PATH . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR );
define( 'LP_ADDON_ASSIGNMENT_VER', '3.1.4' );
define( 'LP_ADDON_ASSIGNMENT_REQUIRE_VER', '3.0.9' );

/**
 * Class LP_Addon_Assignment_Preload
 */
class LP_Addon_Assignment_Preload {

	/**
	 * LP_Addon_Assignment_Preload constructor.
	 */
	public function __construct() {
		add_action( 'learn-press/ready', array( $this, 'load' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		LP_Addon::load( 'LP_Addon_Assignment', 'inc/load.php', __FILE__ );
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
						__( '<strong>%s</strong> addon version %s requires %s version %s or higher is <strong>installed</strong> and <strong>activated</strong>.', 'learnpress-assignments' ),
						__( 'LearnPress Assignment', 'learnpress-assignments' ),
						LP_ADDON_ASSIGNMENT_VER,
						sprintf( '<a href="%s" target="_blank"><strong>%s</strong></a>', admin_url( 'plugin-install.php?tab=search&type=term&s=learnpress' ), __( 'LearnPress', 'learnpress-assignments' ) ),
						LP_ADDON_ASSIGNMENT_REQUIRE_VER
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

new LP_Addon_Assignment_Preload();

register_activation_hook( __FILE__ , 'lp_assignment_install');
function lp_assignment_install() {

	$assignment_students_man_title = 'Assignment Students Manager';
	$assignment_students_man_name = 'assignment-students-manager';

	// the menu entry...
	delete_option("assignment_students_man_title");
	add_option("assignment_students_man_title", $assignment_students_man_title, '', 'yes');
	// the slug...
	delete_option("assignment_students_man_name");
	add_option("assignment_students_man_name", $assignment_students_man_name, '', 'yes');
	// the id...
	delete_option("assignment_students_man_page_id");
	add_option("assignment_students_man_page_id", '0', '', 'yes');

	$the_page = get_page_by_title( $assignment_students_man_title );

	if ( ! $the_page ) {

		// Create post object
		$_p = array();
		$_p['post_title'] = $assignment_students_man_title;
		$_p['post_content'] = "[assignment_students_manager]";
		$_p['post_status'] = 'publish';
		$_p['post_type'] = 'page';
		$_p['comment_status'] = 'closed';
		$_p['ping_status'] = 'closed';
		$_p['post_category'] = array(1); // the default 'Uncatrgorised'

		// Insert the post into the database
		$the_page_id = wp_insert_post( $_p );

	}
	else {

		//make sure the page is not trashed...
		$the_page->post_status = 'publish';
		$the_page_id = wp_update_post( $the_page );

	}

	delete_option( 'assignment_students_man_page_id' );
	add_option( 'assignment_students_man_page_id', $the_page_id );

	$assignment_evaluate_title = 'Assignment Evaluate';
	$assignment_evaluate_name = 'assignment-evaluate';

	// the menu entry...
	delete_option("assignment_evaluate_title");
	add_option("assignment_evaluate_title", $assignment_evaluate_title, '', 'yes');
	// the slug...
	delete_option("assignment_evaluate_name");
	add_option("assignment_evaluate_name", $assignment_evaluate_name, '', 'yes');
	// the id...
	delete_option("assignment_evaluate_page_id");
	add_option("assignment_evaluate_page_id", '0', '', 'yes');

	$eval_page = get_page_by_title( $assignment_evaluate_title );

	if ( ! $eval_page ) {

		// Create post object
		$_p_eval = array();
		$_p_eval['post_title'] = $assignment_evaluate_title;
		$_p_eval['post_content'] = "[assignment_evaluate_form]";
		$_p_eval['post_status'] = 'publish';
		$_p_eval['post_type'] = 'page';
		$_p_eval['comment_status'] = 'closed';
		$_p_eval['ping_status'] = 'closed';
		$_p_eval['post_category'] = array(1); // the default 'Uncatrgorised'

		// Insert the post into the database
		$eval_page_id = wp_insert_post( $_p_eval );

	}
	else {

		//make sure the page is not trashed...
		$eval_page->post_status = 'publish';
		$eval_page_id = wp_update_post( $eval_page );

	}

	delete_option( 'assignment_evaluate_page_id' );
	add_option( 'assignment_evaluate_page_id', $eval_page_id );

}

/* Runs on plugin deactivation */
register_deactivation_hook( __FILE__, 'lp_assignment_remove') ;
function lp_assignment_remove() {
	//  the id of our page...
	$the_page_id = get_option( 'assignment_students_man_page_id' );
	if( $the_page_id ) {

		wp_delete_post( $the_page_id ); // this will trash, not delete

	}

	delete_option("assignment_students_man_title");
	delete_option("assignment_students_man_name");
	delete_option("assignment_students_man_page_id");

	$eval_page_id = get_option( 'assignment_evaluate_page_id' );
	if( $eval_page_id ) {

		wp_delete_post( $eval_page_id ); // this will trash, not delete

	}

	delete_option("assignment_evaluate_title");
	delete_option("assignment_evaluate_name");
	delete_option("assignment_evaluate_page_id");
}