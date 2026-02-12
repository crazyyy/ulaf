<?php
/**
 * SEO Optimization Class
 *
 * Provides SEO optimization recommendations and checks for WordPress sites.
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_SEO_Optimization {
    // Required status constants
    const STATUS_GOOD = 'success';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO = 'info';

    // Required property for content loading
    private $content_loader;

    /**
     * Constructor
     */
    public function __construct() {
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-content-loader.php';
        $this->content_loader = new DiveWP_Content_Loader();
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue required assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'divewp') === false || !current_user_can('manage_options')) {
            return;
        }

        wp_enqueue_style(
            'divewp-seo-optimization',
            DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css',
            array(),
            DIVEWP_VERSION
        );
        
        wp_enqueue_script(
            'divewp-recommendations',
            DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );

        // Add nonce for AJAX operations
        wp_localize_script('divewp-recommendations', 'divewpSEO', array(
            'nonce' => wp_create_nonce('divewp_seo_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    /**
     * Render the best practices interface
     */
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            /* translators: Error message shown when a user doesn't have sufficient permissions to access the SEO optimization page */
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance' ) );
        }
        
        ?>
        <h3><?php 
            /* translators: Title for the SEO optimization recommendations section in the admin interface */
            echo esc_html_e('SEO Optimization Recommendations', 'divewp-boost-site-performance'); 
        ?></h3>
        
        <div class="recommendations-grid">
            <?php 
            $this->render_seo_plugins_check();
            $this->render_meta_description_check();
            $this->render_sitemap_check();
            $this->render_robots_txt_check();
            $this->render_permalink_structure_check();
            $this->render_search_visibility_check();
            ?>
        </div>

        <div class="divewp-notice divewp-notice-warning">
            <p>
                <?php 
                /* translators: Label for an important note about SEO recommendations */
                echo '<strong>' . esc_html__('Important:', 'divewp-boost-site-performance') . '</strong> ';
                /* translators: Warning message about testing SEO changes before implementing them */
                echo esc_html__('Test your site after implementing SEO optimizations. Some changes might require compatibility testing with your themes and plugins.', 'divewp-boost-site-performance');
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Generic method to render a check card
     *
     * @param string $check_type Type of check
     * @param array  $check_result Check result data
     */
    private function render_check($check_type, $check_result) {
        try {
            if (empty($check_type) || !is_array($check_result)) {
                throw new Exception(esc_html__('Invalid check parameters', 'divewp-boost-site-performance'));
            }

            $content = $this->content_loader->get_content('seo-optimization', $check_type);
            if (empty($content) || !is_array($content)) {
                throw new Exception(sprintf(
                    /* translators: %1$s: check type for which content was not found or is invalid */
                    esc_html__('Content not found or invalid for check type: %1$s', 'divewp-boost-site-performance'),
                    $check_type
                ));
            }

            // Validate required content structure
            if ( ! isset( $content['messages'] ) || ! is_array( $content['messages'] ) ) {
                /* translators: Error message when the content structure is invalid */
                throw new Exception( esc_html__('Missing or invalid messages array', 'divewp-boost-site-performance') );
            }

            // Map status to message type with validation
            $status = isset( $check_result['status'] ) ? $check_result['status'] : self::STATUS_INFO;
            switch ( $status ) {
                case self::STATUS_GOOD:
                    $message_type = 'success';
                    break;
                case self::STATUS_WARNING:
                    $message_type = 'warning';
                    break;
                case self::STATUS_CRITICAL:
                    $message_type = 'danger';
                    break;
                default:
                    $message_type = 'danger';
            }

            // Validate message type exists
            if ( ! isset( $content['messages'][$message_type] ) || ! is_array( $content['messages'][$message_type] ) ) {
                /* translators: %1$s: The message type that was invalid */
                throw new Exception( sprintf( esc_html__('Missing or invalid message type: %1$s', 'divewp-boost-site-performance'), $message_type ) );
            }

            $messages = $content['messages'][$message_type];

            // Process message content with translations
            $processed_message = array(
                'title'   => isset($messages['title']) ? esc_html($messages['title']) : '',
                'details' => isset($messages['details']) ? strtr(esc_html($messages['details']), array('{value}' => isset($check_result['value']) ? $check_result['value'] : '')) : '',
                'steps'   => isset($messages['steps']) ? array_map(function($step) {
                    return esc_html($step);
                }, $messages['steps']) : array()
            );

            // Prepare template variables with validation and translation
            $template_vars = array(
                'title'       => isset($content['title']) ? esc_html($content['title']) : '',
                'icon'        => $this->get_icon($check_type),
                'details'     => esc_html($processed_message['details']),
                'steps'       => array_map('esc_html', $processed_message['steps']),
                'status'      => $status,
                'status_text' => $this->get_status_text($status),
                'check_name'  => esc_attr($check_type),
                'feature'     => 'seo-optimization',
                'tooltip'     => isset($check_result['tooltip']) ? esc_html($check_result['tooltip']) : ''
            );

            // Process learn more content with translations
            if (isset($content['learn_more']) && is_array($content['learn_more'])) {
                $template_vars['learn_more'] = array(
                    'description'    => isset($content['learn_more']['description']) 
                        ? esc_html($content['learn_more']['description']) 
                        : '',
                    /* translators: Title for the benefits section of a SEO recommendation */
                    'benefits_title' => esc_html__('Benefits:', 'divewp-boost-site-performance'),
                    'benefits'       => isset($content['learn_more']['benefits']) 
                        ? array_map(function($benefit) {
                            return esc_html($benefit);
                        }, $content['learn_more']['benefits']) 
                        : array()
                );

                if (isset($content['learn_more']['recommended_plugins']) && is_array($content['learn_more']['recommended_plugins'])) {
                    /* translators: Title for the recommended plugins section in SEO checks */
                    $template_vars['learn_more']['plugins_title'] = esc_html__('Recommended Plugins:', 'divewp-boost-site-performance');
                    $template_vars['learn_more']['plugins'] = array_map(function($plugin) {
                        return array(
                            /* translators: Name of a recommended SEO plugin */
                            'name' => isset($plugin['name']) 
                                ? esc_html($plugin['name']) 
                                : '',
                            /* translators: Description of a recommended SEO plugin */
                            'type' => isset($plugin['description']) 
                                ? esc_html($plugin['description']) 
                                : ''
                        );
                    }, $content['learn_more']['recommended_plugins']);
                }
            }

            // Extract variables for template
            extract($template_vars);

            // Include the card template
            require DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';

        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(
                    sprintf(
                        /* translators: %1$s: check type, %2$s: error message */
                        esc_html__('Error rendering SEO check %1$s: %2$s', 'divewp-boost-site-performance'),
                        sanitize_text_field($check_type),
                        $e->getMessage()
                    ),
                    'error'
                );
            }
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__('An error occurred while processing this check.', 'divewp-boost-site-performance')
            );
        }
    }

    /**
     * Render individual check cards using the generic render method
     */
    private function render_seo_plugins_check() {
        $this->render_check('seo-plugins', $this->check_meta_tags());
    }

    private function render_meta_description_check() {
        $this->render_check('meta-description', $this->check_meta_description());
    }

    private function render_sitemap_check() {
        $this->render_check('sitemap', $this->check_sitemap());
    }

    private function render_robots_txt_check() {
        $this->render_check('robots-txt', $this->check_robots_txt());
    }

    private function render_permalink_structure_check() {
        $this->render_check('permalink-structure', $this->check_permalink_structure());
    }

    /**
     * Render search visibility check
     */
    private function render_search_visibility_check() {
        try {
            $check_result = $this->check_search_visibility();
            if (!is_array($check_result)) {
                throw new Exception('Invalid check result');
            }

            // Get check status
            $check_status = isset($check_result['status']) ? $check_result['status'] : self::STATUS_INFO;
            $message_type = $check_status === self::STATUS_GOOD ? 'success' : 'error';

            // Prepare minimal data if content file is missing
            $default_data = [
                'title' => __('Search Engine Visibility', 'divewp-boost-site-performance'),
                'messages' => [
                    'success' => [
                        'details' => __('Your site is visible to search engines', 'divewp-boost-site-performance'),
                        'steps' => []
                    ],
                    'error' => [
                        'details' => __('Your site is hidden from search engines', 'divewp-boost-site-performance'),
                        'steps' => [
                            __('Go to Settings > Reading', 'divewp-boost-site-performance'),
                            __('Uncheck "Discourage search engines from indexing this site"', 'divewp-boost-site-performance'),
                            __('Save changes', 'divewp-boost-site-performance')
                        ]
                    ]
                ]
            ];

            // Try to get content from file, fallback to defaults if needed
            $learn_more = $this->get_check_data('search-visibility');
            if (empty($learn_more) || !is_array($learn_more)) {
                $learn_more = $default_data;
            }

            $this->render_check('search-visibility', [
                'status' => $check_status,
                'value' => isset($check_result['value']) ? $check_result['value'] : '',
                'details' => isset($check_result['details']) ? $check_result['details'] : '',
                'tooltip' => isset($check_result['tooltip']) ? $check_result['tooltip'] : '',
            ]);

        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    'DiveWP: Error rendering search visibility check: %s',
                    $e->getMessage()
                ), 'error');
            }
        }
    }

    /**
     * Get SVG icon markup for a specific check type
     *
     * @param string $type Check type
     * @return string SVG markup
     */
    private function get_icon($type) {
        $check = sanitize_key($type);
        return $this->get_check_icon($check);
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
                /* translators: Status text shown when a SEO check is optimal */
                return __('Optimal', 'divewp-boost-site-performance');
            case self::STATUS_WARNING:
                /* translators: Status text shown when a SEO check needs improvement */
                return __('Needs Attention', 'divewp-boost-site-performance');
            case self::STATUS_CRITICAL:
                /* translators: Status text shown when a SEO check needs immediate attention */
                return __('Needs Attention', 'divewp-boost-site-performance');
            case self::STATUS_INFO:
                /* translators: Status text shown when a SEO check is informational */
                return __('Information', 'divewp-boost-site-performance');
            default:
                /* translators: Status text shown when a SEO check status is unknown */
                return __('Unknown', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get icon for a specific check
     */
    private function get_check_icon($check) {
        $icons = array(
            'meta-description' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                                <path d="M16 13H8"/>
                                <path d="M16 17H8"/>
                                <path d="M10 9H8"/>
                            </svg>',
            'seo-plugins' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7.75 6.5a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z"/>
                                <path fill-rule="evenodd" d="M2.5 1A1.5 1.5 0 001 2.5v8.44c0 .397.158.779.44 1.06l10.25 10.25a1.5 1.5 0 002.12 0l8.44-8.44a1.5 1.5 0 000-2.12L12 1.44A1.5 1.5 0 0010.94 1H2.5z"/>
                            </svg>',
            'sitemap' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path fill-rule="evenodd" d="M12.292 2.06a.75.75 0 00-.584 0L.458 6.81a.75.75 0 000 1.38L11.708 13a.75.75 0 00.584 0L23.542 8.19a.75.75 0 000-1.38L12.292 2.06z"/>
                    </svg>',
            'robots-txt' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path fill-rule="evenodd" d="M3 3a2 2 0 012-2h9.982a2 2 0 011.414.586l4.018 4.018A2 2 0 0121 7.018V21a2 2 0 01-2 2H5a2 2 0 01-2-2V3z"/>
                    </svg>',
            'permalink-structure' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path fill-rule="evenodd" d="M14.78 3.653a3.936 3.936 0 115.567 5.567l-3.627 3.627a3.936 3.936 0 01-5.88-.353.75.75 0 00-1.18.928 5.436 5.436 0 008.12.486l3.628-3.628a5.436 5.436 0 10-7.688-7.688l-3 3a.75.75 0 001.06 1.061l3-3z"/>
                            </svg>',
            'search-visibility' => '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path fill-rule="evenodd" d="M12 2l-5.5 9h11L12 2z"/>
                                <path d="M12 22l-5.5-9h11L12 22z"/>
                            </svg>'
        );
        
        return isset($icons[$check]) ? $icons[$check] : '';
    }

    /**
     * Get data for a specific check
     */
    private function get_check_data($check) {
        $data = array(
            'value' => '',
            'status' => self::STATUS_INFO
        );

        switch ($check) {
            case 'seo-plugins':
                $data['value'] = $this->check_meta_tags();
                break;
            case 'sitemap':
                $data['value'] = $this->check_sitemap();
                break;
            case 'robots-txt':
                $data['value'] = $this->check_robots_txt();
                break;
            case 'permalink-structure':
                $data['value'] = $this->check_permalink_structure();
                break;
            case 'search-visibility':
                $data['value'] = $this->check_search_visibility();
                break;
        }

        return $data;
    }

    /**
     * Get status based on check value
     */
    private function get_check_status($value) {
        // Implementation will vary based on check type
        return self::STATUS_INFO;
    }

    /**
     * Check meta tags implementation
     *
     * @return array Status and details
     */
    private function check_meta_tags() {
        try {
            if (!function_exists('is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $seo_plugins = array(
                'wordpress-seo/wp-seo.php' => 'Yoast SEO',
                'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
                'seo-by-rank-math/rank-math.php' => 'Rank Math SEO',
                'slim-seo/slim-seo.php' => 'Slim SEO',
                'wp-meta-seo/wp-meta-seo.php' => 'WP Meta SEO'
            );

            $detected_plugins = array();
            foreach ($seo_plugins as $plugin_path => $plugin_name) {
                if (is_plugin_active($plugin_path)) {
                    $detected_plugins[] = esc_html($plugin_name);
                }
            }

            if (empty($detected_plugins)) {
                return array(
                    'status' => self::STATUS_WARNING,
                    'status_text' => esc_html__('Not Detected', 'divewp-boost-site-performance'),
                    'details' => esc_html__('No recommended SEO plugin detected', 'divewp-boost-site-performance'),
                    'detected_plugins' => array(),
                    'value' => esc_html__('No recommended plugin detected', 'divewp-boost-site-performance')
                );
            }

            return array(
                'status' => self::STATUS_GOOD,
                'status_text' => esc_html__('Active', 'divewp-boost-site-performance'),
                'value' => implode(', ', $detected_plugins),
                'details' => sprintf(
                    /* translators: %s: Comma-separated list of active SEO plugin names */
                    _n(
                        'Using %s',
                        'Using multiple plugins: %s',
                        count($detected_plugins),
                        'divewp-boost-site-performance'
                    ),
                    implode(', ', $detected_plugins)
                ),
                'detected_plugins' => $detected_plugins
            );
        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(
                    sprintf(
                        'DiveWP SEO Plugin Check Error: %s',
                        $e->getMessage()
                    ),
                    'error'
                );
            }
            return array(
                'status' => self::STATUS_WARNING,
                'status_text' => esc_html__('Error', 'divewp-boost-site-performance'),
                'details' => esc_html__('Could not check SEO plugins', 'divewp-boost-site-performance'),
                'detected_plugins' => array()
            );
        }
    }

    /**
     * Check XML sitemap implementation
     *
     * @return array Status and details
     */
    private function check_sitemap() {
        try {
            $active_plugin = $this->check_meta_tags();
            
            if (empty($active_plugin['detected_plugins'])) {
                if (get_option('permalink_structure')) {
                    return array(
                        'status' => self::STATUS_WARNING,
                        'status_text' => esc_html__('Basic', 'divewp-boost-site-performance'),
                        'details' => esc_html__('WordPress default sitemap', 'divewp-boost-site-performance'),
                        /* translators: Explains why default WordPress sitemap might not be sufficient */
                        'tooltip' => esc_html__('Default WordPress sitemap lacks advanced features like custom post types and image sitemaps. Installing a dedicated SEO plugin is recommended for better search engine optimization.', 'divewp-boost-site-performance')
                    );
                }
                return array(
                    'status' => self::STATUS_CRITICAL,
                    'status_text' => esc_html__('Missing', 'divewp-boost-site-performance'),
                    'details' => esc_html__('No sitemap detected', 'divewp-boost-site-performance'),
                    /* translators: Suggests solutions for missing sitemap */
                    'tooltip' => esc_html__('No sitemap found. Consider enabling permalinks or installing an SEO plugin.', 'divewp-boost-site-performance')
                );
            }

            $plugin_count = count($active_plugin['detected_plugins']);
            
            return array(
                'status' => self::STATUS_GOOD,
                'status_text' => esc_html__('Optimal', 'divewp-boost-site-performance'),
                'details' => sprintf(
                    /* translators: %s: Comma-separated list of SEO plugin names */
                    _n(
                        'Managed by %s',
                        'Managed by multiple plugins: %s',
                        $plugin_count,
                        'divewp-boost-site-performance'
                    ),
                    implode(', ', array_map('esc_html', $active_plugin['detected_plugins']))
                ),
                'tooltip' => sprintf(
                    /* translators: %s: Comma-separated list of SEO plugin names */
                    _n(
                        'Sitemap is being managed by %s. Check plugin settings for customization options.',
                        'Sitemap is being managed by multiple plugins: %s. Check plugin settings for customization options.',
                        $plugin_count,
                        'divewp-boost-site-performance'
                    ),
                    implode(', ', array_map('esc_html', $active_plugin['detected_plugins']))
                )
            );
        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(
                    sprintf(
                        'DiveWP Sitemap Check Error: %s',
                        $e->getMessage()
                    ),
                    'error'
                );
            }
            return array(
                'status' => self::STATUS_WARNING,
                'status_text' => esc_html__('Error', 'divewp-boost-site-performance'),
                'details' => esc_html__('Could not check sitemap', 'divewp-boost-site-performance')
            );
        }
    }

    /**
     * Check robots.txt file
     *
     * @return array Status and message about robots.txt
     */
    private function check_robots_txt() {
        $site_url = get_site_url();
        $robots_url = trailingslashit($site_url) . 'robots.txt';
        
        // Initialize wp_remote_get arguments
        $args = array(
            'timeout' => 5,
            'sslverify' => false, // Disable SSL verification for local/development environments
            'headers' => array(
                'Cache-Control' => 'no-cache',
            ),
        );

        try {
            $response = wp_remote_get($robots_url, $args);

            if (is_wp_error($response)) {
                return array(
                    'status' => 'warning',
                    'message' => sprintf(
                        /* translators: %s: Error message */
                        esc_html__('Could not check robots.txt: %s', 'divewp-boost-site-performance'),
                        $response->get_error_message()
                    )
                );
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $content = wp_remote_retrieve_body($response);

            // Check response code
            if ($response_code !== 200) {
                return array(
                    'status' => 'warning',
                    'message' => sprintf(
                        /* translators: %d: HTTP response code */
                        esc_html__('robots.txt returned HTTP code %d', 'divewp-boost-site-performance'),
                        $response_code
                    )
                );
            }

            // Check if content is empty
            if (empty(trim($content))) {
                return array(
                    'status' => 'warning',
                    'message' => esc_html__('robots.txt file exists but is empty. Consider adding content to control search engine crawling.', 'divewp-boost-site-performance')
                );
            }

            // Basic validation of robots.txt content
            $valid_content = false;
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && preg_match('/^(User-agent:|Disallow:|Allow:|Sitemap:)/i', $line)) {
                    $valid_content = true;
                    break;
                }
            }

            if (!$valid_content) {
                return array(
                    'status' => 'warning',
                    'message' => esc_html__('robots.txt exists but may not contain valid directives.', 'divewp-boost-site-performance')
                );
            }

            return array(
                'status' => 'success',
                'message' => esc_html__('robots.txt file exists and contains valid directives.', 'divewp-boost-site-performance')
            );

        } catch (Exception $e) {
            return array(
                'status' => 'warning',
                'message' => sprintf(
                    /* translators: %s: Error message */
                    esc_html__('Error checking robots.txt: %s', 'divewp-boost-site-performance'),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Check permalink structure
     *
     * @return array Status and details
     */
    private function check_permalink_structure() {
        $structure = get_option('permalink_structure');
        
        if (empty($structure)) {
            return array(
                'status' => self::STATUS_CRITICAL,
                /* translators: Status message shown when the site uses basic permalinks (not SEO friendly) */
                'details' => __('Plain (Not SEO Friendly)', 'divewp-boost-site-performance'),
                /* translators: Tooltip explaining why plain permalinks are not good for SEO */
                'tooltip' => __('Your current permalink structure is not SEO friendly. Change it to a more descriptive format.', 'divewp-boost-site-performance')
            );
        }
        
        if (strpos($structure, '%postname%') !== false) {
            return array(
                'status' => self::STATUS_GOOD,
                /* translators: Status message shown when the site uses SEO-friendly permalinks */
                'details' => __('SEO Friendly', 'divewp-boost-site-performance'),
                /* translators: Tooltip confirming that the current permalink structure is good for SEO */
                'tooltip' => __('Your permalink structure is optimized for SEO.', 'divewp-boost-site-performance')
            );
        }
        
        return array(
            'status' => self::STATUS_WARNING,
            /* translators: Status message shown when the site uses a custom permalink structure */
            'details' => __('Custom Structure', 'divewp-boost-site-performance'),
            /* translators: Tooltip suggesting to use post name in permalinks for better SEO */
            'tooltip' => __('You are using a custom permalink structure. Consider using post name for better SEO.', 'divewp-boost-site-performance')
        );
    }

    /**
     * Check search engine visibility
     *
     * @return array Status and details
     */
    private function check_search_visibility() {
        $blog_public = get_option('blog_public');
        
        if (!$blog_public) {
            return array(
                'status' => self::STATUS_CRITICAL,
                /* translators: Status message shown when the site is hidden from search engines */
                'details' => __('Hidden - Search engines discouraged', 'divewp-boost-site-performance'),
                /* translators: Tooltip explaining the impact of hiding the site from search engines */
                'tooltip' => __('Your site is currently hidden from search engines. This severely impacts your site\'s visibility in search results.', 'divewp-boost-site-performance')
            );
        }

        $active_plugin = $this->check_meta_tags();
        $plugin_count = count($active_plugin['detected_plugins']);

        if (!empty($active_plugin['detected_plugins'])) {
            return array(
                'status' => self::STATUS_GOOD,
                /* translators: Status message shown when the site is visible to search engines and has SEO plugins */
                'details' => __('Visible and optimized', 'divewp-boost-site-performance'),
                /* translators: %s: Comma-separated list of SEO plugin names */
                'tooltip' => sprintf(
                    /* translators: %s: Comma-separated list of SEO plugin names */
                    _n(
                        'Search engines can index your site, and %s provides additional SEO controls.',
                        'Search engines can index your site, and multiple plugins (%s) provide additional SEO controls.',
                        $plugin_count,
                        'divewp-boost-site-performance'
                    ),
                    implode(', ', $active_plugin['detected_plugins'])
                )
            );
        }
        
        return array(
            'status' => self::STATUS_GOOD,
            /* translators: Status message shown when the site is visible to search engines */
            'details' => __('Visible', 'divewp-boost-site-performance'),
            /* translators: Tooltip suggesting to install an SEO plugin for better control */
            'tooltip' => __('Search engines are allowed to index your site. Consider installing an SEO plugin for more control over indexing.', 'divewp-boost-site-performance')
        );
    }

    /**
     * Check meta description implementation
     * Uses direct integration with SEO plugins and WordPress core
     *
     * @return array Status and details
     */
    private function check_meta_description() {
        // Get content from JSON file for messages
        $content = $this->content_loader->get_content('seo-optimization', 'meta-description');

        // Check Yoast SEO
        if (defined('WPSEO_VERSION')) {
            if (class_exists('WPSEO_Meta')) {
                $meta_description = \WPSEO_Meta::get_value('metadesc');
                return $this->evaluate_meta_description($meta_description, $content, 'Yoast SEO');
            }
        }

        // Check Rank Math
        if (class_exists('RankMath\Helper')) {
            $meta_description = \RankMath\Helper::get_post_meta('description');
            return $this->evaluate_meta_description($meta_description, $content, 'Rank Math');
        }

        // Check All in One SEO
        if (class_exists('AIOSEO\Plugin\Common\Models\Post')) {
            $meta_description = aioseo()->meta->description->getDescription();
            return $this->evaluate_meta_description($meta_description, $content, 'All in One SEO');
        }

        // If no SEO plugin, check WordPress native meta description
        $front_page_id = get_option('page_on_front');
        if ($front_page_id) {
            // Try to get meta description from post meta
            $meta_description = get_post_meta($front_page_id, 'meta_description', true);
            if (!empty($meta_description)) {
                return $this->evaluate_meta_description($meta_description, $content, 'WordPress');
            }
            
            // Use excerpt as fallback
            $post = get_post($front_page_id);
            if ($post && !empty($post->post_excerpt)) {
                return $this->evaluate_meta_description($post->post_excerpt, $content, 'WordPress Excerpt');
            }
        } else {
            // Check bloginfo description
            $meta_description = get_bloginfo('description');
            if (!empty($meta_description)) {
                return $this->evaluate_meta_description($meta_description, $content, 'WordPress Site Description');
            }
        }

        // Check theme support as last resort
        $theme_support = current_theme_supports('meta-description');
        if ($theme_support) {
            return array(
                'status' => self::STATUS_WARNING,
                'details' => __('Theme-based meta description', 'divewp-boost-site-performance'),
                'status_text' => __('Missing', 'divewp-boost-site-performance'),
                'tooltip' => __('Your theme supports meta descriptions but none found on homepage.', 'divewp-boost-site-performance'),
                'steps' => $content['messages']['warning']['steps']
            );
        }

        return array(
            'status' => self::STATUS_CRITICAL,
            'details' => __('No meta description found', 'divewp-boost-site-performance'),
            'status_text' => __('Missing', 'divewp-boost-site-performance'),
            'tooltip' => __('No meta description found on your homepage.', 'divewp-boost-site-performance'),
            'steps' => $content['messages']['danger']['steps']
        );
    }

    /**
     * Evaluate meta description quality
     *
     * @param string $meta_description The meta description to evaluate
     * @param array $content Content messages
     * @param string $source Source of the meta description
     * @return array Status and details
     */
    private function evaluate_meta_description($meta_description, $content, $source) {
        $source_display = $this->get_friendly_source_name($source);

        if (empty($meta_description)) {
            return array(
                'status' => self::STATUS_WARNING,
                'value' => $source_display,
                /* translators: %s: Source of the meta description (e.g., "Yoast SEO plugin") */
                'details' => sprintf(
                    /* translators: %s: Source of the meta description (e.g., "Yoast SEO plugin") */
                    __('Empty meta description found in %s', 'divewp-boost-site-performance'),
                    $source_display
                ),
                /* translators: Status text shown when meta description is empty */
                'status_text' => __('Empty', 'divewp-boost-site-performance'),
                /* translators: Tooltip explaining why meta description is important */
                'tooltip' => __('Your homepage has no meta description. This is important for search results.', 'divewp-boost-site-performance'),
                'steps' => array(
                    /* translators: Step 1 for adding meta description */
                    __('Open your SEO plugin settings in WordPress dashboard', 'divewp-boost-site-performance'),
                    /* translators: Step 2 for adding meta description */
                    __('Navigate to Homepage or Search Appearance settings', 'divewp-boost-site-performance'),
                    /* translators: Step 3 for adding meta description */
                    __('Add a meta description between 120-160 characters', 'divewp-boost-site-performance'),
                    /* translators: Step 4 for adding meta description */
                    __('Check plugin documentation for detailed instructions', 'divewp-boost-site-performance')
                )
            );
        }

        $length = strlen($meta_description);
        if ($length < 120) {
            return array(
                'status' => self::STATUS_WARNING,
                'value' => sprintf(
                    /* translators: %d: Number of characters in the meta description */
                    _n('%d character', '%d characters', $length, 'divewp-boost-site-performance'),
                    $length
                ),
                /* translators: %1$d: number of characters in the meta description, %2$s: source of the meta description (e.g., "Yoast SEO plugin") */
                'details' => sprintf(
                    /* translators: %1$d: number of characters in the meta description, %2$s: source of the meta description (e.g., "Yoast SEO plugin") */
                    _n(
                        'Meta description is too short (%1$d character) in %2$s',
                        'Meta description is too short (%1$d characters) in %2$s',
                        $length,
                        'divewp-boost-site-performance'
                    ),
                    $length,
                    $source_display
                ),
                'status_text' => __('Too Short', 'divewp-boost-site-performance'),
                'tooltip' => __('Your meta description is too short. Aim for 120-160 characters for better visibility.', 'divewp-boost-site-performance'),
                'steps' => array(
                    __('Open your SEO plugin settings', 'divewp-boost-site-performance'),
                    __('Find the Homepage meta description field', 'divewp-boost-site-performance'),
                    __('Expand your description to 120-160 characters', 'divewp-boost-site-performance'),
                    __('Include key information about your website', 'divewp-boost-site-performance')
                )
            );
        }

        if ($length > 160) {
            return array(
                'status' => self::STATUS_WARNING,
                /* translators: %1$d: number of characters in the meta description, %2$s: source of the meta description (e.g., "Yoast SEO plugin") */
                'details' => sprintf(
                    /* translators: %1$d: number of characters in the meta description, %2$s: source of the meta description (e.g., "Yoast SEO plugin") */
                    _n(
                        'Meta description is too long (%1$d character) in %2$s',
                        'Meta description is too long (%1$d characters) in %2$s',
                        $length,
                        'divewp-boost-site-performance'
                    ),
                    $length,
                    $source_display
                ),
                'status_text' => __('Too Long', 'divewp-boost-site-performance'),
                'tooltip' => __('Your meta description is too long. Keep it between 120-160 characters.', 'divewp-boost-site-performance'),
                'steps' => array(
                    __('Open your SEO plugin settings', 'divewp-boost-site-performance'),
                    __('Locate the Homepage meta description', 'divewp-boost-site-performance'),
                    __('Shorten your description to 120-160 characters', 'divewp-boost-site-performance'),
                    __('Focus on the most important information', 'divewp-boost-site-performance')
                )
            );
        }

        // Success case
        return array(
            'status' => self::STATUS_GOOD,
            'value' => sprintf(
                /* translators: %d: Number of characters in the meta description */
                _n('%d character', '%d characters', $length, 'divewp-boost-site-performance'),
                $length
            ),
            'details' => sprintf(
                /* translators: 1: Number of characters, 2: Source of meta description (e.g., "Yoast SEO plugin") */
                _n(
                    'Meta description is properly configured (%1$d character) in %2$s',
                    'Meta description is properly configured (%1$d characters) in %2$s',
                    $length,
                    'divewp-boost-site-performance'
                ),
                $length,
                $source_display
            ),
            'status_text' => __('Optimal', 'divewp-boost-site-performance'),
            'tooltip' => __('Your meta description is optimized for search results.', 'divewp-boost-site-performance'),
            'steps' => array(
                __('Keep monitoring your meta description', 'divewp-boost-site-performance'),
                __('Update it when your homepage content changes', 'divewp-boost-site-performance'),
                __('Maintain the optimal length of 120-160 characters', 'divewp-boost-site-performance')
            )
        );
    }

    /**
     * Get user-friendly source name
     *
     * @param string $source Original source name
     * @return string Friendly source name
     */
    private function get_friendly_source_name($source) {
        switch ($source) {
            case 'Yoast SEO':
                return 'Yoast SEO plugin';
            case 'Rank Math':
                return 'Rank Math plugin';
            case 'All in One SEO':
                return 'All in One SEO plugin';
            case 'WordPress':
                return 'WordPress settings';
            case 'WordPress Excerpt':
                return 'page excerpt';
            case 'WordPress Site Description':
                return 'site tagline';
            default:
                return $source;
        }
    }

    /**
     * Get source location path
     *
     * @param string $source Original source name
     * @return string Location path
     */
    private function get_source_location($source) {
        switch ($source) {
            case 'Yoast SEO':
                return 'SEO → Search Appearance → Homepage';
            case 'Rank Math':
                return 'Rank Math → Titles & Meta → Homepage';
            case 'All in One SEO':
                return 'All in One SEO → Search Appearance → Homepage';
            case 'WordPress':
                return 'Settings → Reading';
            case 'WordPress Excerpt':
                return 'Pages → Homepage → Excerpt';
            case 'WordPress Site Description':
                return 'Settings → General → Tagline';
            default:
                return $source . ' settings';
        }
    }

    /**
     * Handle SEO check errors
     *
     * @param string $context Error context
     * @param string $message Error message
     */
    private function handle_seo_error($context, $message) {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log(
                sprintf(
                    '[SEO Optimization] %s: %s',
                    sanitize_text_field($context),
                    sanitize_text_field($message)
                ),
                'error'
            );
        }
    }

    /**
     * Aggregate all SEO checks for Abilities/MCP.
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $checks = array(
            'seo-plugins'         => array( $this, 'check_meta_tags' ),
            'meta-description'    => array( $this, 'check_meta_description' ),
            'sitemap'             => array( $this, 'check_sitemap' ),
            'robots-txt'          => array( $this, 'check_robots_txt' ),
            'permalink-structure' => array( $this, 'check_permalink_structure' ),
            'search-visibility'   => array( $this, 'check_search_visibility' ),
        );

        $results = array();
        $summary = array(
            'total_checks' => 0,
            'passed'       => 0,
            'warnings'     => 0,
            'critical'     => 0,
        );
        $overall = self::STATUS_GOOD;

        foreach ( $checks as $key => $callback ) {
            $result = is_callable( $callback ) ? call_user_func( $callback ) : array();
            $results[ $key ] = $result;
            $summary['total_checks']++;

            $status = isset( $result['status'] ) ? $result['status'] : self::STATUS_INFO;
            if ( self::STATUS_CRITICAL === $status ) {
                $summary['critical']++;
                $overall = self::STATUS_CRITICAL;
            } elseif ( self::STATUS_WARNING === $status ) {
                $summary['warnings']++;
                if ( self::STATUS_GOOD === $overall ) {
                    $overall = self::STATUS_WARNING;
                }
            } else {
                $summary['passed']++;
            }
        }

        return array(
            'status'  => $overall,
            'checks'  => $results,
            'summary' => $summary,
        );
    }
}
