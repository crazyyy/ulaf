<?php
/*
Plugin Name: REST API Manager For ACF
Plugin URI:  https://github.com/bayzidMostafiz/ACF-REST-API-Manager
Description: Custom REST API endpoint to return ACF fields, post meta (selected keys), or Mixed data. Fully configurable from admin settings.
Version:     1.0.2
Author:      Md. Bayzid Mostafiz
Author URI:  https://www.linkedin.com/in/md-bayzid-mostafiz-152b80139/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: rest-api-manager-for-acf
Requires at least: 6.0
Tested up to: 6.8
Assets:
    banner: assets/banner-1544x500.png
    icon: assets/icon-256x256.png
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin settings page
 */
add_action('admin_menu', function() {
    add_options_page(
        esc_html__('REST API Manager For ACF', 'rest-api-manager-for-acf'),
        esc_html__('REST API Manager For ACF', 'rest-api-manager-for-acf'),
        'manage_options',
        'rest-api-manager-for-acf',
        'ramacf_settings_page'
    );
});

/**
 * Enqueue admin JS
 */
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'settings_page_rest-api-manager-for-acf') {
        return;
    }

    $plugin_url = plugin_dir_url(__FILE__);
    $version = '1.0.2';

    wp_register_script(
        'ramacf-admin-js',
        $plugin_url . 'assets/js/admin.js',
        array(),
        $version,
        true
    );

    wp_enqueue_script('ramacf-admin-js');
});

/**
 * Get all unique post meta keys safely
 */
function ramacf_get_meta_keys() {
    $keys = [];
    $posts = get_posts([
        'post_type'      => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    if ($posts) {
        foreach ($posts as $post_id) {
            $meta_keys = array_keys(get_post_meta($post_id));
            foreach ($meta_keys as $key) {
                if (strpos($key, '_') !== 0) {
                    $keys[] = $key;
                }
            }
        }
        $keys = array_unique($keys);
    }
    return $keys;
}

/**
 * Settings page HTML
 */
function ramacf_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('REST API Manager For ACF Settings', 'rest-api-manager-for-acf'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ramacf_options_group');
            do_settings_sections('ramacf_options_group');

            $base_url = get_option('ramacf_api_base', 'ramacf/v1');
            $data_type = get_option('ramacf_api_data_type', 'acf');
            $selected_keys = (array) get_option('ramacf_api_meta_keys', []);
            $all_keys = ramacf_get_meta_keys();
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('API Base URL', 'rest-api-manager-for-acf'); ?></th>
                    <td>
                        <input type="text" name="ramacf_api_base" value="<?php echo esc_attr($base_url); ?>" />
                        <p class="description"><?php echo esc_html__('Enter the base URL for the endpoint (e.g., ramacf/v1)', 'rest-api-manager-for-acf'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Data Type', 'rest-api-manager-for-acf'); ?></th>
                    <td>
                        <select name="ramacf_api_data_type" id="ramacf_api_data_type">
                            <option value="acf" <?php selected($data_type, 'acf'); ?>><?php echo esc_html__('ACF Fields Only', 'rest-api-manager-for-acf'); ?></option>
                            <option value="meta" <?php selected($data_type, 'meta'); ?>><?php echo esc_html__('Post Meta Only', 'rest-api-manager-for-acf'); ?></option>
                            <option value="mixed" <?php selected($data_type, 'mixed'); ?>><?php echo esc_html__('Mixed (ACF + Meta + Post Info)', 'rest-api-manager-for-acf'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('Choose which type of data should be returned by the API.', 'rest-api-manager-for-acf'); ?></p>
                    </td>
                </tr>
                <tr valign="top" id="ramacf_meta_keys_row" style="<?php echo in_array($data_type, ['meta','mixed']) ? '' : 'display:none;'; ?>">
                    <th scope="row"><?php echo esc_html__('Select Meta Keys', 'rest-api-manager-for-acf'); ?></th>
                    <td>
                        <select name="ramacf_api_meta_keys[]" multiple size="8" style="width:300px;">
                            <?php foreach($all_keys as $key): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php echo in_array($key,$selected_keys) ? 'selected':''; ?>>
                                    <?php echo esc_html($key); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html__('Choose which post meta keys to return. Leave empty to return all.', 'rest-api-manager-for-acf'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Sanitization function for array input
 */
function ramacf_sanitize_array($input) {
    if (!is_array($input)) return [];
    return array_map('sanitize_text_field', $input);
}

/**
 * Register settings with proper sanitization
 */
add_action('admin_init', function() {
    register_setting('ramacf_options_group', 'ramacf_api_base', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    register_setting('ramacf_options_group', 'ramacf_api_data_type', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    register_setting('ramacf_options_group', 'ramacf_api_meta_keys', [
        'sanitize_callback' => 'ramacf_sanitize_array',
        'type' => 'array',
    ]);
});

/**
 * Register REST API endpoint
 */
add_action('rest_api_init', function () {
    $base = get_option('ramacf_api_base', 'ramacf/v1');
    $data_type = get_option('ramacf_api_data_type', 'acf');
    $selected_keys = (array) get_option('ramacf_api_meta_keys', []);

    register_rest_route($base, '/page/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => function($data) use ($data_type, $selected_keys) {
            $post_id = $data['id'];
            $post = get_post($post_id);
            if (!$post) return ['error'=>'Post not found'];

            $acf_data = function_exists('get_fields') ? get_fields($post_id) : [];
            $meta_data_raw = get_post_meta($post_id);
            $meta_data = [];

            if (!empty($selected_keys)) {
                foreach ($selected_keys as $key) {
                    if(isset($meta_data_raw[$key])) $meta_data[$key] = $meta_data_raw[$key];
                }
            } else {
                $meta_data = $meta_data_raw;
            }

            switch($data_type) {
                case 'acf': return $acf_data;
                case 'meta': return $meta_data;
                case 'mixed': 
                    return [
                        'acf'=>$acf_data,
                        'meta'=>$meta_data,
                        'post'=>[
                            'ID'=>$post->ID,
                            'title'=>get_the_title($post),
                            'content'=>apply_filters('the_content',$post->post_content),
                            'excerpt'=>get_the_excerpt($post),
                            'author'=>get_the_author_meta('display_name',$post->post_author),
                            'featured_image'=>get_the_post_thumbnail_url($post,'full'),
                            'date'=>$post->post_date,
                        ]
                    ];
                default: return ['error'=>'Invalid data type'];
            }
        },
        'permission_callback' => function() {
            return current_user_can('edit_posts'); // safer for private data
        }
    ));
});
