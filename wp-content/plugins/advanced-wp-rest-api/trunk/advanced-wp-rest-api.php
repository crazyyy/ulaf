<?php
/**
 * Plugin Name: Advanced WP REST API
 * Plugin URI: https://wordpress.org/plugins/advanced-wp-rest-api/
 * Description: This plugin register multiple REST API endpoints
 * Version: 1.3
 * Author: galaxyweblinks
 * Author URI: https://profiles.wordpress.org/galaxyweblinks/#content-plugins
 * License: GPL3
 * Text Domain: advanced-wp-rest-api
 */

include_once 'apis/class-gwl-register-route-api.php';

add_action('admin_enqueue_scripts', 'awpr_callback_for_setting_up_scripts');
function awpr_callback_for_setting_up_scripts() {
    wp_register_style( 'awpr-custom-css', plugins_url('assets/css/custom.css', __FILE__), false, '1.0.0', 'all' );
    wp_enqueue_style( 'awpr-custom-css' );
}

function AWPR_register_options_page() {

    //create new setting
    add_options_page('AWPR Settings', 'Advanced WP REST API', 'manage_options', 'awpr_settings', 'AWPR_options_page');

    //call register settings function
	add_action( 'admin_init', 'register_awpr_plugin_settings' );
}
add_action('admin_menu', 'AWPR_register_options_page');

function register_awpr_plugin_settings() {
	//register our settings
	register_setting( 'awpr-plugin-settings-group', 'awpr_user_login_api' );
	register_setting( 'awpr-plugin-settings-group', 'awpr_post_api' );
	register_setting( 'awpr-plugin-settings-group', 'awpr_user_api' );
    register_setting( 'awpr-plugin-settings-group', 'awpr_product_api' );
}

function AWPR_options_page() {
?>
    <div class="wrap awpr_main">
        <h2><?php _e('Enable/Disable Routes', 'advanced-wp-rest-api'); ?></h2>
        
        <div class="notice awpr--notice">
            <div>
                <h3><?php _e('Advanced WP REST API', 'advanced-wp-rest-api'); ?></h3>
                <p>Here's a link to the documentation for the plugin. This will help you learn more about its features and how to use it.</p>
                <div class="e-notice__actions">
                    <a href="https://wp-plugins.galaxyweblinks.com/wp-plugins/advanced-wp-rest-api/doc/" class="e-button--cta cta-secondary" target="_blank"><span>Documentation</span></a>
                </div>
                <p class="e-note">For any feedback or queries regarding this plugin, please contact our <a href="https://wp-plugins.galaxyweblinks.com/contact/" target="_blank">Support team</a>.</p>
            </div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'awpr-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'awpr-plugin-settings-group' ); ?>
            <table>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_user_login_api"><?php _e('Login API', 'advanced-wp-rest-api'); ?></label></th>
                    <td>
                        <input type="checkbox" id="awpr_user_login_api" name="awpr_user_login_api" value="yes" <?php if( get_option('awpr_user_login_api') == 'yes' ){ echo "checked"; }  ?>/>
                        <p><?php _e('Please check if you want to enable the Login API', 'advanced-wp-rest-api'); ?></p>
                    </td>
                </tr>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_post_api"><?php _e('Post API', 'advanced-wp-rest-api'); ?></label></th>
                    <td>
                        <input type="checkbox" id="awpr_post_api" name="awpr_post_api" value="yes" <?php if( get_option('awpr_post_api') == 'yes' ){ echo "checked"; }  ?> />
                        <p><?php _e('Please check if you want to enable the Post API', 'advanced-wp-rest-api'); ?></p>
                    </td>
                </tr>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_user_api"><?php _e('User API', 'advanced-wp-rest-api'); ?></label></th>
                    <td>
                        <input type="checkbox" id="awpr_user_api" name="awpr_user_api" value="yes" <?php if( get_option('awpr_user_api') == 'yes' ){ echo "checked"; }  ?> />
                        <p><?php _e('Please check if you want to enable the User API', 'advanced-wp-rest-api'); ?></p>
                    </td>
                </tr>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_product_api"><?php _e('Product API', 'advanced-wp-rest-api'); ?></label></th>
                    <td>
                        <input type="checkbox" id="awpr_product_api" name="awpr_product_api" value="yes" <?php if( get_option('awpr_product_api') == 'yes' ){ echo "checked"; }  ?> />
                        <p><?php _e('Please check if you want to enable the Product API', 'advanced-wp-rest-api'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

/*
*  Plugin Setting Link 
*/
function awpr_settings_link( $links, $plugin_file ) {
    if( plugin_basename( __FILE__ ) == $plugin_file ){
        $settings_ui_links = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=awpr_settings' ), __( 'Settings', 'advanced-wp-rest-api' ) );
        array_unshift( $links, $settings_ui_links );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'awpr_settings_link', 10, 2 );

/**
 * You can use these filters to add custom links to your plugin row in the plugin list.
 * @param $links, $file
 * @return $links [array]
 */
function awpr_add_custom_plugin_links($links, $file) {
	if ($file === 'advanced-wp-rest-api/advanced-wp-rest-api.php') {
		$links[] = '<a href="https://wp-plugins.galaxyweblinks.com/wp-plugins/advanced-wp-rest-api/doc/" target="_blank">Documentation</a>';
		$links[] = '<a href="https://wp-plugins.galaxyweblinks.com/contact/" target="_blank">Contact Support</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'awpr_add_custom_plugin_links', 10, 2);