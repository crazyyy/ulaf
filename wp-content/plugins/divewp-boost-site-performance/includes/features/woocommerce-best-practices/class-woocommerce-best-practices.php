<?php
/**
 * WooCommerce Best Practices functionality for DiveWP
 *
 * Provides analysis and recommendations for WooCommerce store optimization.
 * Checks cart fragments, session handling, order cleanup, and product revisions.
 *
 * @package     DiveWP
 * @subpackage  Features/WooCommerce
 * @since       1.0.4
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    /* translators: Error message shown when someone tries to access this file directly */
    die( esc_html__( 'Direct access not permitted.', 'divewp-boost-site-performance' ) );
}

/**
 * Class DiveWP_WooCommerce_Best_Practices
 *
 * @since 1.0.4
 */
class DiveWP_WooCommerce_Best_Practices {
    /**
     * Status constants
     */
    const STATUS_GOOD     = 'success';
    const STATUS_WARNING  = 'warning';
    const STATUS_CRITICAL = 'danger';
    const STATUS_INFO     = 'info';

    /**
     * Content loader instance
     *
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * Initialize the class
     *
     * @since 1.0.4
     */
    public function __construct() {
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-content-loader.php';
        $this->content_loader = new DiveWP_Content_Loader();
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue necessary assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets( $hook ) {
        if ( false === strpos( $hook, 'divewp' ) ) {
            return;
        }

        wp_enqueue_style(
            'divewp-woocommerce-best-practices',
            DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css',
            array(),
            DIVEWP_VERSION
        );
        
        wp_enqueue_script(
            'divewp-recommendations',
            DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js',
            array( 'jquery' ),
            DIVEWP_VERSION,
            true
        );

        wp_localize_script( 'divewp-recommendations', 'divewpAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'divewp_nonce' ),
            'version' => DIVEWP_VERSION,
        ) );
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool True if WooCommerce is active.
     */
    private function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Render the best practices interface
     */
    public function render() {
        if ( ! $this->is_woocommerce_active() ) {
            echo '<div class="divewp-notice divewp-notice-warning">';
            /* translators: Warning message shown when WooCommerce plugin is not active */
            echo '<p>' . esc_html__( 'WooCommerce is not active. Please install and activate WooCommerce to see best practices.', 'divewp-boost-site-performance' ) . '</p>';
            echo '</div>';
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            /* translators: Error message shown when a user doesn't have WooCommerce management permissions */
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance' ) );
        }

        ?>
        <h3><?php 
            /* translators: Title for the WooCommerce best practices section */
            echo esc_html_e( 'WooCommerce Best Practices', 'divewp-boost-site-performance' ); 
        ?></h3>
        
        <div class="recommendations-grid">
            <?php 
            $checks = array(
                'cart-fragments',
                'session-handler',
                'order-cleanup',
                'product-revisions',
            );

            foreach ( $checks as $check ) {
                $this->render_check( $check );
            }
            ?>
        </div>

        <div class="divewp-notice divewp-notice-info">
            <p>
                <?php
                /* translators: Label for an important note about WooCommerce recommendations */
                echo '<strong>' . esc_html__( 'Note:', 'divewp-boost-site-performance' ) . '</strong> ';
                /* translators: Explanation about WooCommerce recommendations being general guidelines */
                echo esc_html__( 'These recommendations are based on common WooCommerce best practices. Your specific needs may vary based on your store size and requirements.', 'divewp-boost-site-performance' );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render a specific check
     *
     * @param string $check Check identifier.
     */
    private function render_check( $check ) {
        try {
            if ( empty( $check ) ) {
                /* translators: Error message when a check identifier is missing or empty */
                throw new Exception( __( 'Invalid check identifier', 'divewp-boost-site-performance' ) );
            }

            $content = $this->content_loader->get_content( 'woocommerce-best-practices', $check );
            if ( empty( $content ) || ! is_array( $content ) ) {
                /* translators: %s: The identifier of the check that couldn't be found */
                throw new Exception( sprintf( __( "Content not found for check: %s", 'divewp-boost-site-performance' ), $check ) );
            }

            // Validate required content structure
            if ( ! isset( $content['messages'] ) || ! is_array( $content['messages'] ) ) {
                /* translators: Error message when the content structure is invalid */
                throw new Exception( __( 'Missing or invalid messages array', 'divewp-boost-site-performance' ) );
            }

            $check_data = $this->get_check_data( $check );
            if ( empty( $check_data ) || ! isset( $check_data['value'] ) ) {
                /* translators: Error message when check data is invalid or missing */
                throw new Exception( __( 'Invalid check data', 'divewp-boost-site-performance' ) );
            }

            $status = $this->get_check_status( $check_data['value'] );
            
            // Validate message type exists
            if ( ! isset( $content['messages'][$status] ) || ! is_array( $content['messages'][$status] ) ) {
                /* translators: %s: The status type that was invalid */
                throw new Exception( sprintf( __( "Invalid message type: %s", 'divewp-boost-site-performance' ), $status ) );
            }

            $message = $content['messages'][$status];

            // Process message content with translations
            $processed_message = array(
                'title'   => isset($message['title']) ? esc_html($message['title']) : '',
                'details' => isset($message['details']) ? strtr(esc_html($message['details']), array('{value}' => $check_data['value'])) : '',
                'steps'   => isset($message['steps']) ? array_map(function($step) {
                    return esc_html($step);
                }, $message['steps']) : array(),
            );

            // Prepare template variables with validation and translation
            $template_vars = array(
                'title'       => isset($content['title']) ? esc_html($content['title']) : '',
                'icon'        => $this->get_check_icon($check),
                'details'     => esc_html($processed_message['details']),
                'steps'       => array_map('esc_html', $processed_message['steps']),
                'status'      => $status,
                'status_text' => $check_data['value'],
                'check_name'  => esc_attr($check),
                'feature'     => 'woocommerce-best-practices'
            );

            // Process learn more content with translations
            if (isset($content['learn_more']) && is_array($content['learn_more'])) {
                $template_vars['learn_more'] = array(
                    'description'    => isset($content['learn_more']['description']) 
                        ? esc_html($content['learn_more']['description']) 
                        : '',
                    /* translators: Title for the benefits section of a recommendation */
                    'benefits_title' => esc_html__('Benefits:', 'divewp-boost-site-performance'),
                    'benefits'       => isset($content['learn_more']['benefits']) 
                        ? array_map(function($benefit) {
                            return esc_html($benefit);
                        }, $content['learn_more']['benefits']) 
                        : array(),
                );

                if (isset($content['learn_more']['recommended_plugins']) && is_array($content['learn_more']['recommended_plugins'])) {
                    /* translators: Title for the recommended plugins section */
                    $template_vars['learn_more']['plugins_title'] = esc_html__('Recommended Plugins:', 'divewp-boost-site-performance');
                    $template_vars['learn_more']['plugins'] = array_map(function($plugin) {
                        return array(
                            'name' => isset($plugin['name']) 
                                ? esc_html($plugin['name']) 
                                : '',
                            'type' => isset($plugin['description']) 
                                ? esc_html($plugin['description']) 
                                : '',
                        );
                    }, $content['learn_more']['recommended_plugins']);
                }
            } else {
                $template_vars['learn_more'] = array();
            }

            // Extract variables for template
            extract($template_vars);

            // Include the card template
            require DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';

        } catch ( Exception $e ) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %1$s: check type, %2$s: error message */
                    esc_html__('Error rendering WooCommerce check %1$s: %2$s', 'divewp-boost-site-performance'),
                    sanitize_text_field($check),
                    $e->getMessage()
                ), 'error');
            }
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                /* translators: Generic error message shown when a WooCommerce check fails */
                esc_html__('An error occurred while processing this check.', 'divewp-boost-site-performance')
            );
        }
    }

    /**
     * Get check data based on existing WooCommerce insights
     *
     * Analyzes various WooCommerce settings and data to provide optimization recommendations.
     *
     * @since  1.0.4
     * @param  string $check Check identifier.
     * @return array {
     *     Check result data
     *     
     *     @type string $value The current value or status of the check
     * }
     */
    private function get_check_data( $check ) {
        $data = array(
            'value' => '',
            'status' => self::STATUS_INFO
        );

        switch ($check) {
            case 'order-cleanup':
                $old_orders = $this->get_old_orders_count();
                return array(
                    'status' => $old_orders > 500 ? self::STATUS_WARNING : self::STATUS_GOOD,
                    /* translators: %1$d: Number of old orders found */
                    'value' => sprintf(
                        /* translators: %1$d: Number of old orders found */
                        _n(
                            '%1$d old order found',
                            '%1$d old orders found',
                            $old_orders,
                            'divewp-boost-site-performance'
                        ),
                        $old_orders
                    )
                );

            case 'product-revisions':
                $revision_count = $this->get_product_revisions_count();
                return array(
                    'status' => $revision_count > 1000 ? self::STATUS_WARNING : self::STATUS_GOOD,
                    /* translators: %1$d: Number of product revisions */
                    'value' => sprintf(
                        /* translators: %1$d: Number of product revisions */
                        _n(
                            '%1$d product revision',
                            '%1$d product revisions',
                            $revision_count,
                            'divewp-boost-site-performance'
                        ),
                        $revision_count
                    )
                );

            case 'cart-fragments':
                $fragment_count = $this->get_cart_fragments_count();
                return array(
                    'status' => $fragment_count > 100 ? self::STATUS_WARNING : self::STATUS_GOOD,
                    /* translators: %1$d: Number of cart fragments */
                    'value' => sprintf(
                        /* translators: %1$d: Number of cart fragments */
                        _n(
                            '%1$d cart fragment',
                            '%1$d cart fragments',
                            $fragment_count,
                            'divewp-boost-site-performance'
                        ),
                        $fragment_count
                    )
                );

            case 'session-handler':
                return $this->check_session_handler();

            default:
                return $data;
        }
    }

    /**
     * Get count of old orders
     * 
     * Note: Direct database query without caching is intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Order counts must be current for accurate monitoring
     * 3. Caching would provide outdated statistics
     * 4. Performance impact is minimal as this is only used in admin dashboard
     *
     * @since 1.0.4
     * @return int Number of old orders
     */
    private function get_old_orders_count() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only analytics query requiring real-time data
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = %s 
            AND post_status = %s 
            AND post_date < DATE_SUB(NOW(), INTERVAL %d DAY)",
            'shop_order',
            'wc-completed',
            30
        ));
    }

    /**
     * Get count of product revisions
     * 
     * Note: Direct database query without caching is intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Revision counts must be current for accurate monitoring
     * 3. Caching would provide outdated statistics
     * 4. Performance impact is minimal as this is only used in admin dashboard
     *
     * @since 1.0.4
     * @return int Number of product revisions
     */
    private function get_product_revisions_count() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only analytics query requiring real-time data
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = %s 
            AND post_parent IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = %s
            )",
            'revision',
            'product'
        ));
    }

    /**
     * Get count of cart fragments
     * 
     * Note: Direct database query without caching is intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Fragment counts must be current for accurate monitoring
     * 3. Caching would provide outdated statistics
     * 4. Performance impact is minimal as this is only used in admin dashboard
     * 5. We are specifically monitoring transients, so caching would defeat the purpose
     *
     * @since 1.0.4
     * @return int Number of cart fragments
     */
    private function get_cart_fragments_count() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only analytics query requiring real-time data
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} 
            WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_wc_cart_fragments_') . '%'
        ));
    }

    /**
     * Check session handler status
     * 
     * Note: Direct database query without caching is intentional because:
     * 1. This is an admin-only monitoring tool requiring real-time data
     * 2. Session handler status must be current for accurate monitoring
     * 3. Caching would provide outdated information
     * 4. This is a one-time check per page load
     *
     * @since 1.0.4
     * @return array Status and value of session handler check
     */
    private function check_session_handler() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_sessions';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only feature detection query
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        return array(
            'status' => $table_exists ? self::STATUS_GOOD : self::STATUS_WARNING,
            'value' => $table_exists 
                ? __('Optimized', 'divewp-boost-site-performance')
                : __('File based', 'divewp-boost-site-performance')
        );
    }

    /**
     * Get check status based on value
     *
     * Determines the status level (success/warning/error) based on check results.
     *
     * @since  1.0.4
     * @param  string $value Check value.
     * @return string Status constant.
     */
    private function get_check_status( $value ) {
        $value = wp_kses( $value, array() ); // Strip any HTML

        if ( empty( $value ) && $value !== '0' && $value !== 0 ) {
            return self::STATUS_CRITICAL;
        }

        // Handle zero cases
        if ( in_array( $value, array(
            '0 ' . __( 'old orders', 'divewp-boost-site-performance' ),
            '0 ' . __( 'revisions', 'divewp-boost-site-performance' )
        ), true ) ) {
            return self::STATUS_GOOD;
        }

        // Check optimized status
        if ( strpos( $value, __( 'Optimized', 'divewp-boost-site-performance' ) ) !== false || 
             $this->is_within_limits( $value ) ) {
            return self::STATUS_GOOD;
        }
        
        return self::STATUS_CRITICAL;
    }

    /**
     * Check if numeric value is within acceptable limits
     *
     * @since  1.0.4
     * @param  string $value Value to check
     * @return bool True if within limits
     */
    private function is_within_limits( $value ) {
        $numeric_value = (int) preg_replace( '/[^0-9]/', '', $value );
        return $numeric_value < 500;
    }

    /**
     * Get icon for a specific check
     *
     * @since  1.0.4
     * @param  string $check Check identifier.
     * @return string SVG icon markup.
     */
    private function get_check_icon( $check ) {
        $check = sanitize_key( $check );
        $icons = array(
            'cart-fragments'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>',
            'session-handler'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21h14.878a2 2 0 001.94-1.515L22 17"/>
                                    </svg>',
            'order-cleanup'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>',
            'product-revisions'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                    </svg>',
        );
        
        return isset( $icons[$check] ) ? $icons[$check] : '';
    }

    /**
     * Aggregate all WooCommerce best practice checks for Abilities/MCP.
     *
     * @since 2.1.0
     * @return array
     */
    public function get_all_checks() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return array();
        }

        $checks = array(
            'cart-fragments',
            'session-handler',
            'order-cleanup',
            'product-revisions',
        );

        $results = array();
        $summary = array(
            'total_checks' => 0,
            'passed'       => 0,
            'warnings'     => 0,
            'critical'     => 0,
        );
        $overall = self::STATUS_GOOD;

        foreach ( $checks as $check ) {
            $data = $this->get_check_data( $check );
            if ( empty( $data ) ) {
                continue;
            }

            // Ensure status exists
            $status = isset( $data['status'] ) ? $data['status'] : $this->get_check_status( isset( $data['value'] ) ? $data['value'] : '' );
            $data['status'] = $status;

            $results[ $check ] = $data;
            $summary['total_checks']++;

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
