<?php
/**
 * Performance Checks functionality for DiveWP
 *
 * This class provides performance optimization checks and recommendations.
 *
 * @package     DiveWP
 * @subpackage  Features/Performance
 * @author      Oleg Petrov
 * @version     1.0.4
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class DiveWP_Performance_Checks {
    /**
     * Status constants
     * 
     * @since 1.0.4
     * @var string
     */
    const STATUS_GOOD = 'success';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO = 'info';

    /**
     * Content loader instance
     * 
     * @since 1.0.4
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * AJAX actions
     * 
     * @since 1.0.4
     * @var array
     */
    private $ajax_actions = array(
        'divewp_performance_check',
        'divewp_refresh_checks'
    );

    /**
     * Initialize the class
     *
     * @since 1.0.4
     * @return void
     */
    public function __construct() {
        if (!defined('ABSPATH')) {
            exit;
        }
        
        // Verify user capabilities early
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $this->content_loader = new DiveWP_Content_Loader();
        
        // Initialize AJAX handlers
        $this->init();
        
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_footer', array($this, 'add_nonce_fields'));
    }

    /**
     * Add nonce fields for AJAX calls
     *
     * @since 1.0.4
     * @return void
     */
    public function add_nonce_fields() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple page check, not processing form submission
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        
        // Only proceed for the divewp page
        if ($page !== 'divewp' || !current_user_can('manage_options')) {
            return;
        }

        foreach ($this->ajax_actions as $action) {
            wp_nonce_field($action . '_nonce', '_wpnonce_' . $action);
        }
    }

    /**
     * Initialize AJAX handlers
     *
     * @since 1.0.4
     * @return void
     */
    public function init() {
        foreach ($this->ajax_actions as $action) {
            add_action('wp_ajax_' . $action, array($this, 'handle_' . $action));
        }
    }

    /**
     * Verify AJAX request
     *
     * @since 1.0.4
     * @param string $action The AJAX action to verify
     * @return bool Whether the request is valid
     */
    private function verify_ajax_request($action) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $nonce_key = '_wpnonce_' . $action;
        check_ajax_referer($action . '_nonce', $nonce_key);

        return true;
    }

    /**
     * Handle performance check AJAX request
     *
     * @since 1.0.4
     * @return void
     */
    public function handle_divewp_performance_check() {
        try {
            // Verify nonce and capabilities
            if (!$this->verify_ajax_request('divewp_performance_check')) {
                throw new Exception(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            }

            // Verify nonce specifically for POST data
            check_ajax_referer('divewp_performance_check_nonce', '_wpnonce_divewp_performance_check');

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified in check_ajax_referer above
            if (!isset($_POST['check_type'])) {
                throw new Exception(esc_html__('Missing check type', 'divewp-boost-site-performance'));
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified in check_ajax_referer above
            $check_type = sanitize_text_field(wp_unslash($_POST['check_type']));
            if (empty($check_type)) {
                throw new Exception(esc_html__('Missing check type', 'divewp-boost-site-performance'));
            }

            $method = 'check_' . str_replace('-', '_', $check_type);
            if (!method_exists($this, $method)) {
                throw new Exception(esc_html__('Invalid check type', 'divewp-boost-site-performance'));
            }

            $result = $this->$method();
            wp_send_json_success($result);

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => esc_html($e->getMessage())
            ));
        }
    }

    /**
     * Handle refresh checks AJAX request
     *
     * Note: Security is handled through verify_ajax_request() which:
     * 1. Verifies administrator capabilities with current_user_can('manage_options')
     * 2. Verifies nonce through check_ajax_referer()
     * 3. Uses unique nonce keys for each action
     * 4. Nonce fields are added through add_nonce_fields()
     *
     * @since 1.0.4
     * @return void
     */
    public function handle_divewp_refresh_checks() {
        try {
            if (!$this->verify_ajax_request('divewp_refresh_checks')) {
                throw new Exception(esc_html__('Security verification failed', 'divewp-boost-site-performance'));
            }

            $results = array();
            $checks = array(
                'caching',
                'minification',
                'deferred_js',
                'image_optimization',
                'lazy_loading',
                'object_cache'
            );

            foreach ($checks as $check) {
                $method = 'check_' . $check;
                if (method_exists($this, $method)) {
                    $results[$check] = $this->$method();
                }
            }

            wp_send_json_success($results);

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => esc_html($e->getMessage())
            ));
        }
    }

    /**
     * Enqueue necessary assets
     *
     * @since 1.0.4
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_assets($hook) {
        if (false === strpos($hook, 'divewp')) {
            return;
        }

        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        $version = defined('DIVEWP_VERSION') ? DIVEWP_VERSION : '1.0.4';
        
        wp_enqueue_style(
            'divewp-performance-checks',
            esc_url(DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css'),
            array(),
            esc_attr($version)
        );
        
        wp_enqueue_script(
            'divewp-recommendations',
            esc_url(DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js'),
            array('jquery'),
            esc_attr($version),
            true
        );

        wp_localize_script(
            'divewp-recommendations', 
            'divewpAdmin', 
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('divewp_performance_nonce'),
                'version' => esc_attr($version),
                'i18n' => array(
                    'error' => esc_html__('An error occurred', 'divewp-boost-site-performance'),
                    'success' => esc_html__('Check completed successfully', 'divewp-boost-site-performance')
                )
            )
        );
    }

    /**
     * Render the performance checks interface
     *
     * @since 1.0.4
     * @return void
     */
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }
        ?>
        <h3><?php esc_html_e('Performance Optimization Recommendations', 'divewp-boost-site-performance'); ?></h3>
        
        <?php 
        // Add nonce fields for each action
        foreach ($this->ajax_actions as $action) {
            wp_nonce_field($action . '_nonce', '_wpnonce_' . $action);
        }
        ?>
        
        <div class="recommendations-grid">
            <?php 
            $checks = array(
                'caching',
                'minification',
                'deferred_js',
                'image_optimization',
                'lazy_loading',
                'object_cache'
            );

            foreach ($checks as $check) {
                $method = "render_{$check}_check";
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
            ?>
        </div>

        <div class="divewp-notice divewp-notice-warning">
            <p>
                <strong><?php esc_html_e('Important:', 'divewp-boost-site-performance'); ?></strong> 
                <?php esc_html_e('Test your site after implementing optimizations. Some changes might require compatibility testing with your themes and plugins.', 'divewp-boost-site-performance'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Generic method to render a check card
     *
     * @since 1.0.4
     * @param string $check_type Type of check to render.
     * @param array  $check_result Results of the check.
     * @return void
     */
    private function render_check($check_type, $check_result) {
        if (!is_string($check_type) || !is_array($check_result)) {
            $this->log_error('Invalid parameters', 'Render Check');
            return;
        }

        $content = $this->content_loader->get_content('performance-checks', $check_type);
        
        if (!$content || !$this->validate_check_content($content)) {
            $this->log_error('Invalid content', 'Render Check');
            return;
        }

        try {
            // Map status to message type
            switch ($check_result['status']) {
                case self::STATUS_GOOD:
                    $message_type = 'success';
                    break;
                case self::STATUS_WARNING:
                    $message_type = 'warning';
                    break;
                default:
                    $message_type = 'error';
            }

            // Verify message type exists in content
            if (!isset($content['messages'][$message_type])) {
                // Fallback to error if warning doesn't exist
                $message_type = $message_type === 'warning' ? 'error' : $message_type;
            }

            $messages = $content['messages'][$message_type];

            // Prepare details text with escaping
            $details = '';
            if (isset($messages['details']) && is_string($messages['details'])) {
                $details = esc_html($messages['details']);
                if (isset($check_result['detected_plugins']) && strpos($details, '{plugin_name}') !== false) {
                    $details = str_replace(
                        '{plugin_name}',
                        esc_html(implode(', ', array_map('sanitize_text_field', $check_result['detected_plugins']))),
                        $details
                    );
                }
            }

            // Prepare learn more content with translations
            $learn_more = array();
            
            // Add description if exists with translation
            if (!empty($content['learn_more']['description']) && is_string($content['learn_more']['description'])) {
                $learn_more['description'] = wp_kses_post(esc_html($content['learn_more']['description']));
            }
            
            // Add benefits title with translation
            $learn_more['benefits_title'] = sprintf(
                /* translators: %s: check type name */
                esc_html__('Benefits of %s:', 'divewp-boost-site-performance'), 
                esc_html(str_replace('-', ' ', $check_type))
            );

            // Add benefits with translations
            $learn_more['benefits'] = array();
            if (!empty($content['learn_more']['benefits']) && is_array($content['learn_more']['benefits'])) {
                foreach ($content['learn_more']['benefits'] as $benefit) {
                    if (is_string($benefit)) {
                        $learn_more['benefits'][] = wp_kses_post(esc_html($benefit));
                    }
                }
            }

            // Add recommended plugins if needed with translations
            if ($check_result['status'] !== self::STATUS_GOOD && 
                !empty($content['learn_more']['recommended_plugins']) && 
                is_array($content['learn_more']['recommended_plugins'])) {
                $learn_more['plugins_title'] = esc_html__('Recommended plugins:', 'divewp-boost-site-performance');
                $learn_more['plugins'] = array();
                foreach ($content['learn_more']['recommended_plugins'] as $plugin) {
                    if (is_string($plugin)) {
                        $learn_more['plugins'][] = wp_kses_post(esc_html($plugin));
                    }
                }
            }

            // Prepare steps with translations
            $steps = array();
            if (!empty($messages['steps']) && is_array($messages['steps'])) {
                foreach ($messages['steps'] as $step) {
                    if (is_string($step)) {
                        $steps[] = wp_kses_post(esc_html($step));
                    }
                }
            }

            // Get title with translation
            $title = '';
            if (isset($messages['title']) && is_string($messages['title'])) {
                $title = esc_html($messages['title']);
            }

            // Render the card with translated content
            $this->render_card(array(
                'title' => $title,
                'icon' => $this->get_icon($check_type),
                'details' => $details,
                'steps' => $steps,
                'status' => $check_result['status'],
                'status_text' => $this->get_status_text($check_result['status']),
                'learn_more' => $learn_more
            ));
        } catch (Exception $e) {
            $this->log_error(
                sprintf(
                    'Error rendering check: %s',
                    $e->getMessage()
                ),
                'Render Check',
                array(
                    'check_type' => $check_type,
                    'error' => $e->getMessage()
                )
            );
        }
    }

    /**
     * Render individual check cards using the generic render method
     */
    private function render_caching_check() {
        $this->render_check('caching', $this->check_caching());
    }

    private function render_minification_check() {
        $this->render_check('minification', $this->check_minification());
    }

    private function render_deferred_js_check() {
        $this->render_check('deferred-js', $this->check_deferred_js());
    }

    private function render_image_optimization_check() {
        $this->render_check('image-optimization', $this->check_image_optimization());
    }

    private function render_lazy_loading_check() {
        $this->render_check('lazy-loading', $this->check_lazy_loading());
    }

    private function render_object_cache_check() {
        $this->render_check('object-cache', $this->check_object_cache());
    }

    /**
     * Check caching plugin status
     *
     * @since 1.0.4
     * @return array Array containing status and detected plugins.
     */
    protected function check_caching() {
        $caching_plugins = array(
            'wp-super-cache/wp-cache.php' => 'WP Super Cache',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
            'wp-rocket/wp-rocket.php' => 'WP Rocket',
            'cache-enabler/cache-enabler.php' => 'Cache Enabler',
            'sg-cachepress/sg-cachepress.php' => 'SG Optimizer',
            'breeze/breeze.php' => 'Breeze',
            'swift-performance-lite/performance.php' => 'Swift Performance',
            'wp-optimize/wp-optimize.php' => 'WP-Optimize',
            'nitropack/main.php' => 'NitroPack',
            'flying-press/flying-press.php' => 'FlyingPress'
        );

        $detected_plugins = array();
        foreach ($caching_plugins as $plugin_path => $plugin_name) {
            if ($this->validate_plugin_status($plugin_path)) {
                $detected_plugins[] = sanitize_text_field($plugin_name);
            }
        }

        return array(
            'status' => !empty($detected_plugins) ? self::STATUS_GOOD : self::STATUS_WARNING,
            'detected_plugins' => array_map('sanitize_text_field', $detected_plugins)
        );
    }

    /**
     * Check minification plugin status
     *
     * @since 1.0.4
     * @return array Array containing status and detected plugins.
     */
    protected function check_minification() {
        $minification_plugins = array(
            'autoptimize/autoptimize.php' => 'Autoptimize',
            'wp-rocket/wp-rocket.php' => 'WP Rocket',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
            'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
            'swift-performance-lite/performance.php' => 'Swift Performance',
            'hummingbird-performance/wp-hummingbird.php' => 'Hummingbird',
            'sg-cachepress/sg-cachepress.php' => 'SG Optimizer'
        );

        $detected_plugins = array();
        foreach ($minification_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $detected_plugins[] = sanitize_text_field($plugin_name);
            }
        }

        return array(
            'status' => !empty($detected_plugins) ? self::STATUS_GOOD : self::STATUS_WARNING,
            'detected_plugins' => array_map('sanitize_text_field', $detected_plugins)
        );
    }

    /**
     * Check deferred JavaScript plugin status
     *
     * @since 1.0.4
     * @return array Array containing status and detected plugins.
     */
    protected function check_deferred_js() {
        $deferred_js_plugins = array(
            'wp-rocket/wp-rocket.php' => 'WP Rocket',
            'flying-scripts/flying-scripts.php' => 'Flying Scripts',
            'async-javascript/async-javascript.php' => 'Async JavaScript',
            'autoptimize/autoptimize.php' => 'Autoptimize',
            'sg-cachepress/sg-cachepress.php' => 'SG Optimizer',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
            'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
            'swift-performance-lite/performance.php' => 'Swift Performance'
        );

        $detected_plugins = array();
        foreach ($deferred_js_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $detected_plugins[] = sanitize_text_field($plugin_name);
            }
        }

        return array(
            'status' => !empty($detected_plugins) ? self::STATUS_GOOD : self::STATUS_WARNING,
            'detected_plugins' => array_map('sanitize_text_field', $detected_plugins)
        );
    }

    /**
     * Check image optimization plugin status
     *
     * @since 1.0.4
     * @return array Array containing status and detected plugins.
     */
    protected function check_image_optimization() {
        $image_optimization_plugins = array(
            'webp-express/webp-express.php' => 'WebP Express',
            'imagify/imagify.php' => 'Imagify',
            'wp-smushit/wp-smush.php' => 'Smush',
            'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel',
            'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
            'optimus/optimus.php' => 'Optimus',
            'tiny-compress-images/tiny-compress-images.php' => 'TinyPNG',
            'compress-jpeg-png-images/compress-jpeg-png-images.php' => 'Compress JPEG & PNG',
            'wp-compress/wp-compress.php' => 'WP Compress',
            'imagerecycle-pdf-image-compression/wp-image-recycle.php' => 'ImageRecycle'
        );

        $detected_plugins = array();
        foreach ($image_optimization_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $detected_plugins[] = sanitize_text_field($plugin_name);
            }
        }

        return array(
            'status' => !empty($detected_plugins) ? self::STATUS_GOOD : self::STATUS_WARNING,
            'detected_plugins' => array_map('sanitize_text_field', $detected_plugins)
        );
    }

    /**
     * Check lazy loading plugin status
     *
     * @since 1.0.4
     * @return array Array containing status and detected plugins.
     */
    protected function check_lazy_loading() {
        $detected_plugins = array();
        
        // Extended list of plugins that provide lazy loading functionality
        $lazy_loading_plugins = array(
            // Dedicated lazy loading plugins
            'a3-lazy-load/a3-lazy-load.php' => 'a3 Lazy Load',
            'rocket-lazy-load/rocket-lazy-load.php' => 'Rocket Lazy Load',
            
            // Caching plugins with lazy loading features
            'wp-rocket/wp-rocket.php' => 'WP Rocket',
            'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            
            // Optimization plugins with lazy loading
            'autoptimize/autoptimize.php' => 'Autoptimize',
            'sg-cachepress/sg-cachepress.php' => 'SG Optimizer',
            'flying-press/flying-press.php' => 'FlyingPress',
            
            // Image optimization plugins with lazy loading
            'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
            'wp-smushit/wp-smush.php' => 'Smush',
            'imagify/imagify.php' => 'Imagify'
        );
        
        // Check for plugins that offer lazy loading
        foreach ($lazy_loading_plugins as $plugin_path => $plugin_name) {
            if ($this->validate_plugin_status($plugin_path)) {
                $detected_plugins[] = sanitize_text_field($plugin_name);
            }
        }
        
        // Check for WordPress native lazy loading
        $wp_lazy_loading = wp_lazy_loading_enabled('img', 'the_content');
        if ($wp_lazy_loading) {
            $detected_plugins[] = sanitize_text_field('WordPress Native Lazy Loading');
        }

        // If we have plugins or native lazy loading, return detailed info
        if (!empty($detected_plugins) || $wp_lazy_loading) {
            return array(
                'status' => (!empty($detected_plugins) || $wp_lazy_loading) ? self::STATUS_GOOD : self::STATUS_WARNING,
                'detected_plugins' => array_map('sanitize_text_field', array_unique($detected_plugins))
            );
        }

        // If no lazy loading detected, return simple warning status
        return array(
            'status' => self::STATUS_WARNING,
            'detected_plugins' => array()
        );
    }

    /**
     * Checks object cache implementation and status
     *
     * Verifies the presence and configuration of:
     * - Redis
     * - Memcached
     * - APCu
     * - WordPress drop-ins
     * - Caching plugins
     *
     * @since 1.0.4
     * @return array {
     *     @type string $status            Check status (success/warning/error)
     *     @type array  $detected_plugins  List of detected caching solutions
     *     @type bool   $using_ext_object_cache Whether external object cache is active
     * }
     */
    protected function check_object_cache() {
        $detected_plugins = array();
        $using_ext_object_cache = wp_using_ext_object_cache();
        
        // Check for specific object cache plugins/implementations
        $object_cache_plugins = array(
            // Redis implementations
            'redis-cache/redis-cache.php' => 'Redis Object Cache',
            'redis-cache-pro/redis-cache-pro.php' => 'Redis Object Cache Pro',
            'wp-redis/wp-redis.php' => 'WP Redis',
            
            // Memcached implementations
            'wp-memcached/wp-memcached.php' => 'WP Memcached',
            'memcached-cloud/memcached-cloud.php' => 'Memcached Cloud',
            'memcached-redux/memcached-redux.php' => 'Memcached Redux',
            
            // Full caching solutions with object cache support
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache'
        );

        foreach ($object_cache_plugins as $plugin_path => $plugin_name) {
            if (is_plugin_active($plugin_path)) {
                $detected_plugins[] = sanitize_text_field($plugin_name);
            }
        }

        // Check for specific object cache drop-ins
        $dropin_path = WP_CONTENT_DIR . '/object-cache.php';
        if (file_exists($dropin_path)) {
            $dropin_data = get_plugin_data($dropin_path, false, false);
            if (!empty($dropin_data['Name'])) {
                // Only add if not already detected through plugin
                if (!in_array($dropin_data['Name'], $detected_plugins, true)) {
                    $detected_plugins[] = sanitize_text_field($dropin_data['Name']);
                }
            }
        }

        // Check for APCu
        if (function_exists('apcu_enabled') && apcu_enabled()) {
            // Additional check for actual APCu availability
            if (function_exists('apcu_sma_info') && apcu_sma_info(true)) {
                $detected_plugins[] = 'APCu';
            }
        }

        // Check for Memcached
        if (class_exists('Memcached')) {
            if ($this->check_memcached_support()) {
                $detected_plugins[] = 'Memcached';
            }
        } elseif (class_exists('Memcache')) {
            if ($this->check_memcache_support()) {
                $detected_plugins[] = 'Memcache';
            }
        }

        return array(
            'status' => $using_ext_object_cache ? self::STATUS_GOOD : self::STATUS_WARNING,
            'detected_plugins' => array_map('sanitize_text_field', array_unique($detected_plugins)),
            'using_ext_object_cache' => (bool) $using_ext_object_cache
        );
    }

    /**
     * Helper method to render a recommendation card
     *
     * @since 1.0.4
     * @param array $args {
     *     Arguments for rendering the card.
     *     @type string $title          Card title
     *     @type string $icon           SVG icon markup
     *     @type string $details        Main description text
     *     @type array  $steps          Array of steps to display
     *     @type string $status         Status class (success, warning, danger, info)
     *     @type string $status_text    Status text to display
     *     @type array  $learn_more     Learn more content array
     * }
     * @return void
     */
    private function render_card($args) {
        // Default values
        $defaults = array(
            'title' => '',
            'icon' => '',
            'details' => '',
            'steps' => array(),
            'status' => self::STATUS_INFO,
            'status_text' => esc_html__('Information', 'divewp-boost-site-performance'),
            'learn_more' => array()
        );

        // Merge with defaults
        $args = wp_parse_args($args, $defaults);

        // Sanitize SVG icon
        $allowed_svg = array(
            'svg' => array(
                'class' => true,
                'width' => true,
                'height' => true,
                'viewBox' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true
            ),
            'path' => array(
                'd' => true,
                'stroke' => true,
                'stroke-width' => true
            ),
            'rect' => array(
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true
            ),
            'circle' => array(
                'cx' => true,
                'cy' => true,
                'r' => true
            )
        );

        // Extract and sanitize individual items
        $title = esc_html($args['title']);
        $icon = wp_kses($args['icon'], $allowed_svg);
        $details = wp_kses_post($args['details']);
        $status = sanitize_html_class($args['status']);
        $status_text = esc_html($args['status_text']);

        // Handle arrays separately
        $steps = array();
        if (!empty($args['steps']) && is_array($args['steps'])) {
            foreach ($args['steps'] as $step) {
                if (is_string($step)) {
                    $steps[] = $step; // Already sanitized in render_check
                }
            }
        }

        $learn_more = array();
        if (!empty($args['learn_more']) && is_array($args['learn_more'])) {
            foreach ($args['learn_more'] as $key => $value) {
                if (is_string($value)) {
                    $learn_more[$key] = $value; // Already sanitized in render_check
                } elseif (is_array($value)) {
                    $learn_more[$key] = array_filter($value, 'is_string'); // Only keep string values
                }
            }
        }

        // Include the template
        include DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';
    }

    /**
     * Get SVG icon markup for a specific check type
     *
     * @param string $type Check type
     * @return string SVG markup
     */
    private function get_icon($type) {
        $icons = array(
            'caching' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h18a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
                            <path d="M12 7v10"/>
                            <path d="M7 12h10"/>
                            <path d="M3 9h18"/>
                        </svg>',
            'minification' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M8 8h8"/>
                            <path d="M8 12h4"/>
                            <path d="M8 16h6"/>
                        </svg>',
            'deferred-js' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M12 8v8"/>
                            <path d="M8 12l4 4"/>
                            <path d="M16 12l-4 4"/>
                        </svg>',
            'image-optimization' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>',
            'lazy-loading' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M8 12h8"/>
                            <path d="M12 16V8"/>
                            <path d="m9 15 3-3 3 3"/>
                        </svg>',
            'object-cache' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M12 7v10"/>
                            <path d="M7 12h10"/>
                            <circle cx="12" cy="12" r="4"/>
                        </svg>'
        );

        return isset($icons[$type]) ? $icons[$type] : $icons['caching'];
    }

    /**
     * Get status text based on status
     *
     * @param string $status Status constant
     * @return string Status text
     */
    private function get_status_text($status) {
        switch ($status) {
            case self::STATUS_GOOD:
                return __('Available', 'divewp-boost-site-performance');
            case self::STATUS_WARNING:
                return __('Needs Attention', 'divewp-boost-site-performance');
            case self::STATUS_CRITICAL:
                return __('Not Configured', 'divewp-boost-site-performance');
            case self::STATUS_INFO:
                return __('Information', 'divewp-boost-site-performance');
            default:
                return __('Unknown', 'divewp-boost-site-performance');
        }
    }

    /**
     * Enhanced error logging for performance checks
     *
     * Logs errors when DIVEWP_DEBUG is enabled, with sanitized output
     * and proper error context. Only logs in debug mode and when user
     * has appropriate permissions.
     *
     * @since 1.0.4
     * @param string $message Error message
     * @param string $context Error context
     * @param array  $data    Optional. Additional error data
     * @return void
     */
    private function log_error($message, $context = '', $data = array()) {
        if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
            return;
        }

        $error_data = array(
            'message' => esc_html($message),
            'context' => esc_html($context),
            'timestamp' => current_time('mysql'),
            'check_type' => isset($data['check_type']) ? sanitize_key($data['check_type']) : '',
            'user_id' => get_current_user_id()
        );
        
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            if (function_exists('wp_debug_log')) {
                wp_debug_log(sprintf(
                    '[DiveWP Performance Check] %s - Context: %s - Data: %s',
                    $error_data['message'],
                    $error_data['context'],
                    wp_json_encode($error_data)
                ));
            }
        }
    }

    /**
     * Validate check content safely
     *
     * @param array $content Content to validate
     * @return bool Whether content is valid
     */
    private function validate_check_content($content) {
        if (!is_array($content)) {
            $this->log_error('Invalid content format', 'Content Validation');
            return false;
        }

        $required_keys = array('messages', 'learn_more');
        foreach ($required_keys as $key) {
            if (!isset($content[$key]) || !is_array($content[$key])) {
                $this->log_error("Missing or invalid key: {$key}", 'Content Validation');
                return false;
            }
        }

        return true;
    }

    /**
     * Validate plugin status safely
     *
     * Checks if a plugin exists and is active, with proper path validation.
     *
     * @since 1.0.4
     * @param string $plugin_path Path to the plugin file
     * @return bool True if plugin is active, false otherwise
     */
    private function validate_plugin_status($plugin_path) {
        if (validate_file($plugin_path) !== 0) {
            $this->log_error(
                sprintf(
                    /* translators: %s: Plugin path */
                    esc_html__('Invalid plugin path: %s', 'divewp-boost-site-performance'),
                    esc_html($plugin_path)
                ),
                'Plugin Validation'
            );
            return false;
        }
        
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_path)) {
            return false;
        }
        
        return is_plugin_active($plugin_path);
    }

    /**
     * Check for Memcached support
     *
     * @return bool Whether Memcached is available
     */
    private function check_memcached_support() {
        if (class_exists('Memcached')) {
            try {
                $memcached = new Memcached();
                if (@$memcached->addServer('127.0.0.1', 11211)) {
                    $version = $memcached->getVersion();
                    if (!empty($version)) {
                        return true;
                    }
                }
            } catch (Exception $e) {
                $this->log_error(
                    'Memcached check failed',
                    'Memcached Support',
                    array('error' => $e->getMessage())
                );
            }
        }
        return false;
    }

    /**
     * Check for Memcache support
     *
     * @return bool Whether Memcache is available
     */
    private function check_memcache_support() {
        if (class_exists('Memcache')) {
            try {
                $memcache = new Memcache();
                if (@$memcache->connect('127.0.0.1', 11211)) {
                    return true;
                }
            } catch (Exception $e) {
                $this->log_error(
                    'Memcache check failed',
                    'Memcache Support',
                    array('error' => $e->getMessage())
                );
            }
        }
        return false;
    }

    /**
     * Aggregate all performance checks for Abilities/MCP.
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $checks = array(
            'caching',
            'minification',
            'deferred_js',
            'image_optimization',
            'lazy_loading',
            'object_cache',
        );

        $results  = array();
        $warnings = 0;
        $critical = 0;
        $passed   = 0;

        foreach ( $checks as $check ) {
            $method = 'check_' . $check;
            if ( method_exists( $this, $method ) ) {
                $result = $this->$method();
                $results[ $check ] = $result;
                if ( isset( $result['status'] ) ) {
                    if ( 'success' === $result['status'] ) {
                        $passed++;
                    } elseif ( 'warning' === $result['status'] ) {
                        $warnings++;
                    } else {
                        $critical++;
                    }
                }
            }
        }

        $overall = 'success';
        if ( $critical > 0 ) {
            $overall = 'danger';
        } elseif ( $warnings > 0 ) {
            $overall = 'warning';
        }

        return array(
            'status'  => $overall,
            'checks'  => $results,
            'summary' => array(
                'total_checks' => count( $results ),
                'passed'       => $passed,
                'warnings'     => $warnings,
                'critical'     => $critical,
            ),
        );
    }
} 