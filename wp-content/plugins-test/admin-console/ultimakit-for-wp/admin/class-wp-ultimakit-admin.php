<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    UltimaKit
 * @link       https://xwpankit.com
 * @since      1.0.0
 *
 * @subpackage UltimaKit/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    UltimaKit
 * @subpackage UltimaKit/admin
 * @author     Ankit Panchal <developer@wpultimakit.com>
 */
class UltimaKit_Admin extends UltimaKit_Module_Manager {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    private $helpers;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name       The name of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->helpers = new UltimaKit_Helpers();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in UltimaKit_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The UltimaKit_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ( strpos( ULTIMAKIT_FOR_WP_CURRENT_PAGE, 'ultimakit' ) > 0 ) {
            wp_enqueue_style(
                'select2-css',
                plugin_dir_url( __FILE__ ) . 'css/select2.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'ultimakit_bootstrap_main',
                plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'ultimakit_bootstrap_rtl',
                plugin_dir_url( __FILE__ ) . 'css/bootstrap.rtl.min.css',
                array(),
                $this->version,
                'all'
            );
            // Enqueue toastr CSS.
            wp_enqueue_style(
                'toastr-css',
                plugin_dir_url( __FILE__ ) . 'css/toastr.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'freemius-addon-custom-css',
                // Handle
                plugin_dir_url( __FILE__ ) . 'css/addons-style.css',
                // Path to your CSS file
                array(),
                // Dependencies
                '1.0.0'
            );
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/main.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in UltimaKit_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The UltimaKit_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ( strpos( ULTIMAKIT_FOR_WP_CURRENT_PAGE, 'ultimakit' ) > 0 ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script(
                'ultimakit_bootstrap_bundle',
                plugin_dir_url( __FILE__ ) . 'js/bootstrap.bundle.min.js',
                array('jquery'),
                $this->version,
                false
            );
            // Enqueue toastr.js.
            wp_enqueue_script(
                'toastr-js',
                plugin_dir_url( __FILE__ ) . 'js/toastr.min.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_enqueue_script(
                'select2-js',
                plugin_dir_url( __FILE__ ) . 'js/select2.min.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/wp-ultimakit-admin.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_localize_script( $this->plugin_name, 'ultimakit_ajax', array(
                'url'               => admin_url( 'admin-ajax.php' ),
                'rest_settings_url' => esc_url_raw( rest_url( 'ultimakit/v1/settings' ) ),
                'nonce'             => wp_create_nonce( 'ultimakit_nonce' ),
                'positionClass'     => 'toast-top-right',
                'timeOut'           => 3000,
            ) );
        }
    }

    public function ultimakit_premium_upgrade_notice() {
        if ( !$this->ultimakit_asset_condition() ) {
            return;
        }
        $migration_completed = get_option( 'ultimakit_migration_completed' );
        if ( !$migration_completed ) {
            $style = 'style="background: #fff; border: 5px solid #6610F2;max-width: 98%;"';
            ?>
			<div class="container-fluid p-0">
				<div class="alert alert-info alert-dismissible fade show mt-3" <?php 
            echo $style;
            ?> role="alert">
					<h5 class="alert-heading">
						<strong>Ultimakit Plugin Notice:</strong>
					</h5>
					<p>We have updated the settings structure. Please click the button below to migrate your settings.</p>
					
					<div class="mt-3">
						<a href="javascript:void(0)" class="btn btn-primary" id="ultimakit_migrate_settings" style="border-radius:20px;" target="_blank">Migrate Settings Now</a>
					</div>
					<button type="button" class="btn-close ultimakit-notice-dismiss" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
			<?php 
        }
        if ( current_user_can( 'manage_options' ) && !ufw_fs()->is__premium_only() && !get_transient( 'ultimakit_premium_notice_hidden' ) ) {
            $style = 'style="background: #fff; border: 5px solid #6610F2;max-width: 98%;"';
            ?>
			<div class="container-fluid p-0">
				<div class="alert alert-info alert-dismissible fade show mt-3" <?php 
            echo $style;
            ?> role="alert">
					<h5 class="alert-heading">
						🚀 Upgrade to <strong>UltimaKit For WP</strong> PRO!
					</h5>
					<p>
						Unlock powerful new modules for <strong>WooCommerce</strong> and <strong>WordPress</strong>! Enhance your site with advanced features like:
					</p>
					<ul>
						<li>🛒 <strong>WooCommerce Add-Ons:</strong> Streamline your store management, improve conversions, and access exclusive tools.</li>
						<li>🔧 <strong>Advanced WordPress Modules:</strong> Take control of customizations, security, and performance with our premium features.</li>
					</ul>
					<div class="mt-3">
						<a href="https://xwpstack.com" class="btn btn-outline-secondary" style="border-radius:20px;" target="_blank">Learn More</a>
						<a href="https://xwpstack.com/pricing/" class="btn btn-primary" style="border-radius:20px;" target="_blank">Upgrade Now</a>
					</div>
					<button type="button" class="btn-close ultimakit-notice-dismiss" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
			<script type="text/javascript">
				document.querySelector('.ultimakit-notice-dismiss').addEventListener('click', function() {
					// Make an AJAX request to set the transient
					fetch(ajaxurl + '?action=ultimakit_dismiss_notice');
				});
			</script>
			<?php 
        }
    }

    // Handle the AJAX request to set the transient
    public function ultimakit_dismiss_notice() {
        if ( current_user_can( 'manage_options' ) ) {
            set_transient( 'ultimakit_premium_notice_hidden', true, 15 * DAY_IN_SECONDS );
        }
        wp_die();
        // Terminate the request properly
    }

    /**
     * Adds an admin menu page for WP UltimaKit.
     *
     * This function registers a new menu page in the WordPress dashboard
     * under the specified menu title, position, and with the specified capabilities.
     *
     * @return void
     */
    public function ultimakit_admin_menu() {
        // Ensure the current user has the 'manage_options' capability.
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        // Parameters are: page_title, menu_title, capability, menu_slug, function, icon_url, position.
        add_menu_page(
            __( 'UltimaKit Dashboard', 'ultimakit-for-wp' ),
            // Page Title.
            __( 'UltimaKit For WP', 'ultimakit-for-wp' ),
            // Menu Title.
            'manage_options',
            // Capability (only admins can access).
            'wp-ultimakit-dashboard',
            // Menu Slug.
            array($this, 'ultimakit_render_dashboard_page'),
            // The function to render the page content.
            'dashicons-superhero',
            100
        );
        // Add submenu page
        // add_submenu_page(
        // 	'wp-ultimakit-dashboard',                        // Parent slug
        // 	__( 'Settings', 'ultimakit-for-wp' ),              // Page title
        // 	__( 'Settings', 'ultimakit-for-wp' ),              // Menu title
        // 	'manage_options',                                // Capability
        // 	'wp-ultimakit-settings',                         // Menu slug
        // 	array( $this, 'ultimakit_render_settings_page' )   // Function to display the page
        // );
    }

    public function ultimakit_render_settings_page() {
        ?>
		<div class="wrap">
			<?php 
        $this->ultimakit_get_header();
        ?>
			<?php 
        $this->ultimakit_get_settings();
        ?>
		</div>
		<?php 
    }

    /**
     * Renders the settings page for the plugin/theme.
     *
     * This function outputs the HTML for the settings page of the plugin or theme.
     * It should be hooked into the WordPress admin menu system via add_options_page()
     * or a similar function. The function checks for user permissions, outputs the
     * settings form, and handles the submission of form data for updating plugin/theme
     * settings.
     */
    public function ultimakit_render_dashboard_page() {
        ?>
		<div class="wrap">
			<?php 
        $this->ultimakit_get_header();
        ?>
			<?php 
        $this->ultimakit_get_modules();
        ?>
		</div>
		<?php 
    }

    public function ultimakit_get_settings() {
        ?>
		<div class="container-fluid module-container">
			<div class="row">
				<div class="col-6">
					<form id="ultimakit_form" method="post" enctype="multipart/form-data">
						<?php 
        $uninstall_status = get_option( 'ultimakit_uninstall_settings', true );
        ?>
						<!-- <div class="mb-3 form-check form-switch p-0">
							<label><?php 
        echo esc_html_e( 'Remove all plugin data upon uninstallation.', 'ultimakit-for-wp' );
        ?></label>
							<input class="form-check-input ultimakit_settings_action" type="checkbox" id="ultimakit_uninstall_settings" 
							<?php 
        if ( 'on' === $uninstall_status ) {
            echo 'checked';
        }
        ?>
							>
							<label class="form-check-label switch-label" for="ultimakit_uninstall_settings">Toggle me</label>
						</div> -->

						<div class="mb-3">
							<label for="formFile" class="form-label"><?php 
        echo esc_html_e( 'Import Settings', 'ultimakit-for-wp' );
        ?></label>
							<input class="form-control" type="file" name="ultimakit_import_settings" id="ultimakit_import_settings" accept=".json">
							<small><?php 
        echo esc_html_e( 'Only valid JSON is accepted.', 'ultimakit-for-wp' );
        ?></small>
						</div>

						<div class="mb-5">
							<button class="btn btn-primary" id="ultimakit_export_settings"><?php 
        echo esc_html_e( 'Export Settings', 'ultimakit-for-wp' );
        ?></button>
						</div>
					
					</form>	

				</div>
			</div>
		</div>
		<?php 
    }

    /**
     * Retrieves a list of available modules/components for the theme or plugin.
     *
     * This function compiles and returns an array of modules or components that are
     * available within the theme or plugin. These could be features, extensions,
     * widgets, or any other type of modular functionality that can be dynamically
     * managed or utilized within the project. The function can be used to check
     * for the availability of certain modules, to dynamically include them in the
     * project, or to provide options in the admin settings for enabling/disabling
     * specific modules.
     */
    public function ultimakit_get_modules() {
        $admin = new UltimaKit_Module_Manager();
        $helper = new UltimaKit_Helpers();
        $moduleCount = $helper->get_modules_count( $admin );
        $catList = $admin->getAllCategoriesList();
        $category_types = array('WordPress', 'WooCommerce');
        $gravity_modules = $admin->getAllModules( 'Gravity Forms' );
        $gravity_slug = $helper->string_to_slug( 'Gravity Forms' );
        ?>
		<div class="wrap ultimakit-modern-dashboard">
			<!-- Header Section -->
			<div class="ultimakit-dashboard-header">
				<div class="header-content">
					<h1><?php 
        echo esc_html_e( 'UltimaKit Control Center', 'ultimakit-for-wp' );
        ?></h1>
				</div>
				<div class="dashboard-header-actions">
					<div class="search-box">
						<input type="text" id="ultimakit_search_module" placeholder="<?php 
        echo esc_attr_e( 'Search modules...', 'ultimakit-for-wp' );
        ?>">
						<span class="search-icon dashicons dashicons-search"></span>
					</div>
					<div class="view-toggle">
						<button type="button" class="btn btn-sm" id="ultimakit_small_screen" title="<?php 
        echo esc_attr_e( 'Compact View', 'ultimakit-for-wp' );
        ?>">
							<span class="dashicons dashicons-editor-contract"></span>
						</button>
						<button type="button" class="btn btn-sm" id="ultimakit_full_screen" title="<?php 
        echo esc_attr_e( 'Full View', 'ultimakit-for-wp' );
        ?>">
							<span class="dashicons dashicons-editor-expand"></span>
						</button>
					</div>
					<div class="settings-actions">
						<button type="button" class="btn btn-sm btn-primary" id="ultimakit_export_settings" title="<?php 
        echo esc_attr_e( 'Export Settings', 'ultimakit-for-wp' );
        ?>">
							<span class="dashicons dashicons-download"></span>
						</button>
						<label for="ultimakit_import_settings" class="btn btn-sm btn-secondary" style="cursor: pointer; margin: 0;" title="<?php 
        echo esc_attr_e( 'Import Settings', 'ultimakit-for-wp' );
        ?>">
							<span class="dashicons dashicons-upload"></span>
						</label>
						<input type="file" id="ultimakit_import_settings" accept=".json" style="display: none;">
					</div>
				</div>
			</div>

			<!-- Main Dashboard Layout -->
			<div class="ultimakit-dashboard-layout">
				<!-- Sidebar -->
				<div class="ultimakit-sidebar">

					<div class="sidebar-categories">
						<h4><?php 
        echo esc_html_e( 'Categories', 'ultimakit-for-wp' );
        ?></h4>
						<ul class="category-list">
							<li class="category-item active" data-category="all">
								<span class="category-name"><?php 
        echo esc_html_e( 'All Categories', 'ultimakit-for-wp' );
        ?></span>
								<span class="category-count"><?php 
        echo esc_html( $moduleCount );
        ?></span>
							</li>
							<?php 
        if ( !empty( $gravity_modules ) ) {
            ?>
								<li class="category-item" data-category="<?php 
            echo esc_attr( $gravity_slug );
            ?>">
									<span class="category-name"><?php 
            echo esc_html_e( 'Gravity Forms', 'ultimakit-for-wp' );
            ?></span>
									<span class="category-count"><?php 
            echo esc_html( count( $gravity_modules ) );
            ?></span>
								</li>
							<?php 
        }
        ?>
							<?php 
        if ( !empty( $catList ) ) {
            foreach ( $catList as $category ) {
                if ( 'WooCommerce' === $category && !$this->helpers->is_woocommerce_active() ) {
                    continue;
                }
                $moduleCount = $helper->get_modules_count_by_category( $admin, $category, $category_types );
                $category_slug = $helper->string_to_slug( $category );
                ?>
									<li class="category-item" data-category="<?php 
                echo esc_attr( $category_slug );
                ?>">
										<span class="category-name"><?php 
                echo esc_html( ( $category === 'Content Management' ? 'Content Management & SEO' : $category ) );
                ?></span>
										<span class="category-count"><?php 
                echo esc_html( $moduleCount );
                ?></span>
									</li>
									<?php 
            }
        }
        ?>
						</ul>
					</div>
				</div>

				<!-- Main Content -->
				<div class="ultimakit-main-content">
					<div class="modules-grid" id="modules-grid">
						<?php 
        // Show all modules initially
        $all_modules = $admin->getAllModules();
        usort( $all_modules, function ( $a, $b ) {
            return $b['is_active'] - $a['is_active'];
        } );
        $helper->get_module_block( $all_modules );
        ?>
					</div>

					<!-- Hidden category-specific content for AJAX loading -->
					<?php 
        if ( !empty( $catList ) ) {
            foreach ( $catList as $category ) {
                if ( 'WooCommerce' === $category && !$this->helpers->is_woocommerce_active() ) {
                    continue;
                }
                $category_slug = $helper->string_to_slug( $category );
                ?>
							<div class="category-content" id="category-<?php 
                echo esc_attr( $category_slug );
                ?>" style="display: none;">
								<div class="modules-grid">
									<?php 
                $category_modules = $admin->getAllModulesByCategory( $category, $category_types );
                usort( $category_modules, function ( $a, $b ) {
                    return $b['is_active'] - $a['is_active'];
                } );
                $helper->get_module_block( $category_modules );
                ?>
								</div>
							</div>
							<?php 
            }
        }
        if ( !empty( $gravity_modules ) ) {
            usort( $gravity_modules, function ( $a, $b ) {
                return $b['is_active'] - $a['is_active'];
            } );
            ?>
						<div class="category-content" id="category-<?php 
            echo esc_attr( $gravity_slug );
            ?>" style="display: none;">
							<div class="modules-grid">
								<?php 
            $helper->get_module_block( $gravity_modules );
            ?>
							</div>
						</div>
						<?php 
        }
        ?>
				</div>
			</div>
		</div>
		<?php 
    }

    public function ultimakit_admin_head() {
        echo '<style>';
        $view_mode = get_option( 'ultimakit_modules_list_view', true );
        if ( 'small' === $view_mode ) {
            echo '.module-description{display:none;}';
            echo '.module-box{height:100px;}';
        }
        echo '</style>';
    }

    public function ultimakit_migrate_settings() {
        // Verify user capability
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions', 403 );
        }
        // Verify the nonce
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ultimakit_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Unauthorized', 'ultimakit-for-wp' ),
            ), 401 );
        }
        $helper = new UltimaKit_Helpers();
        // Run migration function
        if ( $helper->ultimakit_check_and_migrate_settings() ) {
            // Mark migration as complete
            update_option( 'ultimakit_migration_completed', true );
            wp_send_json_success();
        } else {
            wp_send_json_error( 'Migration failed.' );
        }
    }

}
