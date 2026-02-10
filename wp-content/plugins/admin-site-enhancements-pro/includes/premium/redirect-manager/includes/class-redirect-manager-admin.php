<?php
/**
 * Redirect Manager Admin
 *
 * Handles admin menu registration
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for managing admin menu
 */
class ASENHA_Redirect_Manager_Admin {

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_filter( 'parent_file', array( $this, 'set_parent_file' ) );
		add_filter( 'submenu_file', array( $this, 'set_submenu_file' ) );
		add_action( 'admin_head', array( $this, 'add_view_all_redirects_button' ) );
	}

	/**
	 * Add admin menu item under Tools
	 *
	 * @since 8.1.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Redirect Manager', 'admin-site-enhancements' ),
			__( 'Redirect Manager', 'admin-site-enhancements' ),
			'manage_options',
			'edit.php?post_type=asenha_redirect'
		);
	}

	/**
	 * Set parent file to highlight Tools menu on redirect CPT screens
	 *
	 * @since 8.1.0
	 * @param string $parent_file The parent file
	 * @return string Modified parent file
	 */
	public function set_parent_file( $parent_file ) {
		global $typenow;

		if ( 'asenha_redirect' === $typenow ) {
			$parent_file = 'tools.php';
		}

		return $parent_file;
	}

	/**
	 * Set submenu file to highlight Redirect Manager submenu on redirect CPT screens
	 *
	 * @since 8.1.0
	 * @param string $submenu_file The submenu file
	 * @return string Modified submenu file
	 */
	public function set_submenu_file( $submenu_file ) {
		global $typenow;

		if ( 'asenha_redirect' === $typenow ) {
			$submenu_file = 'edit.php?post_type=asenha_redirect';
		}

		return $submenu_file;
	}

	/**
	 * Add "View All Redirects" button on add/edit redirect screens
	 *
	 * @since 8.1.0
	 */
	public function add_view_all_redirects_button() {
		global $pagenow, $typenow;

		if ( 'asenha_redirect' !== $typenow ) {
			return;
		}

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$view_all_url = admin_url( 'edit.php?post_type=asenha_redirect' );
		$button_text  = __( 'View All Redirects', 'admin-site-enhancements' );

		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var viewAllBtn = '<a href="<?php echo esc_url( $view_all_url ); ?>" class="page-title-action"><?php echo esc_js( $button_text ); ?></a>';
			var $pageTitleAction = $('.wrap .page-title-action');

			if ($pageTitleAction.length > 0) {
				// Edit screen - insert after "Add New Redirect" button
				$pageTitleAction.after(viewAllBtn);
			} else {
				// Add new screen - insert after heading
				$('.wrap h1').first().after(viewAllBtn);
			}
		});
		</script>
		<?php
	}
}

