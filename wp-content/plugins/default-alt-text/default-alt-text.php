<?php
/**
 * Plugin Name: Default Alt Text | SEO Plugin
 * Description: Displays default alt text for images that don’t have one set in the Media Library.
 * Author: MohammedYasar Khalifa
 * Author URI: https://myasark.wordpress.com/
 * Text Domain: default-alt-text
 * Version: 1.2
 * License: GPLv2
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

class YKDefaultAltText {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'yk_enqueue_scripts' ] );
    }
    
    public function yk_enqueue_scripts() {
        wp_enqueue_script(
            'default-alt-text-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/default-alt-text.js',
            [], 
            '1.0',
            true 
        );
    }

}


new YKDefaultAltText();