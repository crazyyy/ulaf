<?php

namespace BoltAudit\App\Repositories;

defined( 'ABSPATH' ) || exit;

use WC_Admin_Status;

class WooCommerceRepository {
        protected static ?array $summary_cache = null;
        protected static ?array $insights_cache = null;

        protected static function count_posts( string $post_type ): int {
                $count_obj = wp_count_posts( $post_type );
                if ( ! $count_obj ) {
                        return 0;
                }
                return array_sum( (array) $count_obj );
        }

        protected static function count_terms( string $taxonomy ): int {
                $count = wp_count_terms( $taxonomy );
                return is_wp_error( $count ) ? 0 : (int) $count;
        }

        public static function get_summary(): array {
                if ( null !== self::$summary_cache ) {
                        return self::$summary_cache;
                }

                if ( ! class_exists( 'WooCommerce' ) ) {
                        return [];
                }

                $product_count   = self::count_posts( 'product' );
                $variation_count = self::count_posts( 'product_variation' );
                $order_count     = self::count_posts( 'shop_order' );
                $coupon_count    = self::count_posts( 'shop_coupon' );
                $category_count  = self::count_terms( 'product_cat' );

                $attr_taxonomies = wc_get_attribute_taxonomy_names();
                $attr_terms      = 0;
                foreach ( $attr_taxonomies as $tax ) {
                        $attr_terms += self::count_terms( $tax );
                }

                self::$summary_cache = [
                        'products'      => $product_count,
                        'variations'    => $variation_count,
                        'orders'        => $order_count,
                        'coupons'       => $coupon_count,
                        'categories'    => $category_count,
                        'attribute_terms' => $attr_terms,
                ];

                return self::$summary_cache;
        }

        public static function get_insights(): array {
                if ( null !== self::$insights_cache ) {
                        return self::$insights_cache;
                }

                if ( ! class_exists( 'WooCommerce' ) ) {
                        return [];
                }

                global $wpdb;

                // Cart fragments enabled check
                $cart_fragments = apply_filters( 'woocommerce_cart_fragments_enabled', true );

                // Woo scripts/styles globally enqueued
                $woo_styles = apply_filters( 'woocommerce_enqueue_styles', [] );
                $woo_scripts = apply_filters( 'woocommerce_enqueue_scripts', true );
                $styles_global = ! empty( $woo_styles );
                $scripts_global = (bool) $woo_scripts;

                $variation_count = self::count_posts( 'product_variation' );
                $high_variations = $variation_count > 2000;

                $outdated_templates = [];
                if ( class_exists( 'WC_Admin_Status' ) && method_exists( WC_Admin_Status::class, 'get_theme_template_overrides' ) ) {
                        $outdated_templates = WC_Admin_Status::get_theme_template_overrides();
                }

                $scheduled_actions = 0;
                if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . 'actionscheduler_actions' ) ) ) {
                        $scheduled_actions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions" );
                }

                // Unused product tags
                $unused_tags = (int) $wpdb->get_var( "SELECT COUNT(t.term_id) FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id AND tt.taxonomy = 'product_tag' LEFT JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.object_id IS NULL" );

                // WooCommerce transients
                $wc_transients = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_%'" );

                // High Performance Order Storage
                $hpos_enabled = 'yes' === get_option( 'woocommerce_custom_orders_table_enabled', 'no' );

                self::$insights_cache = [
                        'cart_fragments_sitewide' => $cart_fragments,
                        'styles_js_global'       => $styles_global && $scripts_global,
                        'high_variation_count'   => $high_variations,
                        'outdated_templates'     => $outdated_templates,
                        'scheduled_actions'      => $scheduled_actions,
                        'unused_product_tags'    => $unused_tags,
                        'wc_transients'          => $wc_transients,
                        'hpos_enabled'           => $hpos_enabled,
                ];

                return self::$insights_cache;
        }

        public static function get_all(): array {
                return [
                        'summary' => self::get_summary(),
                        'insights' => self::get_insights(),
                ];
        }
}
