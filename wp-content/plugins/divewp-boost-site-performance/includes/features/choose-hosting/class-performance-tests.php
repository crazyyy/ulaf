<?php
/**
 * DiveWP Performance Tests Class
 * 
 * Handles comprehensive performance testing for hosting evaluation
 * Tests WooCommerce-like operations including price calculations, shipping, and inventory checks
 * 
 * @package DiveWP_Boost_Site_Performance
 * @since 1.0.6
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DiveWP_Performance_Tests
 * 
 * Dedicated class for hosting performance testing
 * Separated from main class for better organization and maintainability
 */
class DiveWP_Performance_Tests {
    
    /**
     * Run comprehensive performance tests
     * Tests WooCommerce-like operations with intensive calculations
     * 
     * @param array $test_config Test configuration from main class
     * @return array Performance test results with scores and timings
     */
    public function run_performance_tests($test_config) {
        // Add rate limiting (one test per PHP time limit per user)
        $rate_limit_key = 'divewp_performance_test_rate_limit_' . get_current_user_id();
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $rate_limit_duration = $php_max_execution_time > 0 ? $php_max_execution_time : 30;
        
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another performance test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, $rate_limit_duration);
        
        $results = array();
        $test_iterations = 15; // Original intensive testing
        $price_calc_times = array();
        $shipping_calc_times = array();
        $inventory_check_times = array();
        
        // Track timeout information
        $timed_out_tests = array();
        $total_score_deduction = 0;
        
        // Component weights for score deductions
        $score_weights = array(
            'price' => 50,      // Most critical for WooCommerce
            'shipping' => 30,   // Important for checkout
            'inventory' => 20   // Less critical
        );
        
        // Temporarily disable cache addition during tests
        wp_suspend_cache_addition(true);
        
        // Use full PHP execution time (80% for safety) 
        $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 40;
        $component_timeout_limit = $max_test_time / 3; // Divide time among 3 components
        
        $start_time = microtime(true);
        
        // Simulate WooCommerce price calculations for 2500 products
        $products_count = 2500;
        $iterations = 10; // Simulate 10 concurrent users
        
        // Run price calculation test multiple times with timeout protection
        $price_calc_timed_out = false;
        $price_calc_test_start = microtime(true);
        
        for ($test_run = 0; $test_run < $test_iterations && !$price_calc_timed_out; $test_run++) {
            $price_calc_start = microtime(true);
            
            for ($iteration = 0; $iteration < $iterations; $iteration++) {
                if ($iteration === 0) {
                }
                
                // Check for timeout before each iteration
                if ((microtime(true) - $price_calc_test_start) > $component_timeout_limit) {
                    $price_calc_timed_out = true;
                    break;
                }
                
                $cart_total = 0;
                $tax_total = 0;
                $discount_total = 0;
                
                for ($i = 0; $i < $products_count; $i++) {
                    // Variable product pricing
                    $base_price = mt_rand(1000, 50000) / 100;
                    $quantity = mt_rand(1, 5);
                    
                    // Apply bulk discounts
                    $discount_percent = 0;
                    if ($quantity >= 5) $discount_percent = 10;
                    if ($quantity >= 10) $discount_percent = 15;
                    
                    $discounted_price = $base_price * (1 - $discount_percent / 100);
                    
                    // Tax calculations with different rates
                    $tax_rates = array(
                        'standard' => 10,
                        'reduced' => 5,
                        'zero' => 0
                    );
                    
                    $tax_class = array_rand($tax_rates);
                    $tax_rate = $tax_rates[$tax_class];
                    
                    // Calculate line items
                    $line_subtotal = $base_price * $quantity;
                    $line_total = $discounted_price * $quantity;
                    $line_tax = ($line_total * $tax_rate) / 100;
                    $line_discount = $line_subtotal - $line_total;
                    
                    // Add to totals
                    $cart_total += $line_total;
                    $tax_total += $line_tax;
                    $discount_total += $line_discount;
                    
                    // Format with WordPress functions
                    $formatted_price = number_format_i18n($line_total, 2);
                    $formatted_tax = number_format_i18n($line_tax, 2);
                }
                
                // Coupon validation simulation
                $coupon_codes = array('SAVE10', 'FREESHIP', 'WELCOME20');
                foreach ($coupon_codes as $code) {
                    // Simulate coupon validation logic
                    $is_valid = (mt_rand(0, 10) > 5);
                    if ($is_valid) {
                        $coupon_discount = $cart_total * 0.1; // 10% discount
                        $cart_total -= $coupon_discount;
                        $discount_total += $coupon_discount;
                    }
                }
                
                // Currency conversion simulation
                $exchange_rates = array(
                    'EUR' => 0.85,
                    'GBP' => 0.73,
                    'JPY' => 110.50
                );
                
                foreach ($exchange_rates as $currency => $rate) {
                    $converted = $cart_total * $rate;
                    $formatted = number_format_i18n($converted, 2);
                }
            }
            
            if (!$price_calc_timed_out) {
                $price_calc_times[] = microtime(true) - $price_calc_start;
            }
        }
        
        // Track price calculation timeout
        if ($price_calc_timed_out) {
            $timed_out_tests['price'] = true;
            $total_score_deduction += $score_weights['price'];
        }
        
        $price_calc_time = count($price_calc_times) > 0 ? array_sum($price_calc_times) / count($price_calc_times) : 0;
        
        // Run shipping calculation test multiple times with timeout protection
        $shipping_calc_timed_out = false;
        $shipping_calc_test_start = microtime(true);
        
        for ($test_run = 0; $test_run < $test_iterations && !$shipping_calc_timed_out; $test_run++) {
            $shipping_calc_start = microtime(true);
            
            // Check for timeout before each test run
            if ((microtime(true) - $shipping_calc_test_start) > $component_timeout_limit) {
                $shipping_calc_timed_out = true;
                break;
            }
            
            for ($i = 0; $i < 1250; $i++) {
                // Simulate shipping zones and methods
                $zones = array('domestic', 'international', 'express');
                $total_weight = mt_rand(1000, 10000) / 1000; // kg
                
                foreach ($zones as $zone) {
                    $base_rate = mt_rand(500, 2000) / 100;
                    $weight_rate = $total_weight * 2;
                    $shipping_cost = $base_rate + $weight_rate;
                    
                    // Apply shipping classes
                    if ($zone === 'express') {
                        $shipping_cost *= 1.5;
                    }
                    
                    $formatted_shipping = number_format_i18n($shipping_cost, 2);
                }
            }
            
            if (!$shipping_calc_timed_out) {
                $shipping_calc_times[] = microtime(true) - $shipping_calc_start;
            }
        }
        
        // Track shipping calculation timeout
        if ($shipping_calc_timed_out) {
            $timed_out_tests['shipping'] = true;
            $total_score_deduction += $score_weights['shipping'];
        }
        
        $shipping_calc_time = count($shipping_calc_times) > 0 ? array_sum($shipping_calc_times) / count($shipping_calc_times) : 0;
        
        // Run inventory check simulation multiple times with timeout protection
        $inventory_check_timed_out = false;
        $inventory_check_test_start = microtime(true);
        
        for ($test_run = 0; $test_run < $test_iterations && !$inventory_check_timed_out; $test_run++) {
            $inventory_check_start = microtime(true);
            
            // Check for timeout before each test run
            if ((microtime(true) - $inventory_check_test_start) > $component_timeout_limit) {
                $inventory_check_timed_out = true;
                break;
            }
            
            for ($i = 0; $i < 1500; $i++) {
                $stock_levels = array();
                for ($j = 0; $j < 50; $j++) {
                    $stock_levels[$j] = mt_rand(0, 100);
                    $is_in_stock = $stock_levels[$j] > 0;
                    $is_low_stock = $stock_levels[$j] < 5 && $stock_levels[$j] > 0;
                }
            }
            
            if (!$inventory_check_timed_out) {
                $inventory_check_times[] = microtime(true) - $inventory_check_start;
            }
        }
        
        // Track inventory check timeout
        if ($inventory_check_timed_out) {
            $timed_out_tests['inventory'] = true;
            $total_score_deduction += $score_weights['inventory'];
        }
        
        $inventory_check_time = count($inventory_check_times) > 0 ? array_sum($inventory_check_times) / count($inventory_check_times) : 0;
        
        // Re-enable cache addition
        wp_suspend_cache_addition(false);
        
        // Calculate total time
        $total_time = $price_calc_time + $shipping_calc_time + $inventory_check_time;
        
        // Test for PHP opcode caching (server-level performance feature)
        $opcache_enabled = false;
        $opcache_stats = array();
        
        if (function_exists('opcache_get_status')) {
            $opcache_status = @opcache_get_status(false);
            if ($opcache_status && isset($opcache_status['opcache_enabled']) && $opcache_status['opcache_enabled']) {
                $opcache_enabled = true;
                $opcache_stats = array(
                    'memory_usage' => isset($opcache_status['memory_usage']) ? $opcache_status['memory_usage'] : array(),
                    'statistics' => isset($opcache_status['opcache_statistics']) ? $opcache_status['opcache_statistics'] : array()
                );
            }
        }
        
        // Check for alternative PHP accelerators
        $apc_enabled = function_exists('apc_cache_info') && @apc_cache_info();
        $xcache_enabled = function_exists('xcache_get');
        
        // Adjust scoring based on opcode caching
        $cache_bonus = 0;
        if ($opcache_enabled || $apc_enabled || $xcache_enabled) {
            $cache_bonus = 10; // Bonus points for having opcode caching
        }
        
        // Realistic scoring for WooCommerce operations with precise continuous scoring - Extended for slow servers
        if ($total_time <= 0) {
            $performance_score = 100;
            $rating = 'excellent';
        } elseif ($total_time <= 0.4) {
            // Excellent range: 100-92 (linear scale)
            $performance_score = round(100 - ($total_time * 20.0));
            $rating = 'excellent';
        } elseif ($total_time <= 1.2) {
            // Very good range: 92-72 (linear scale)
            $performance_score = round(92 - (($total_time - 0.4) * 25.0));
            $rating = ($performance_score >= 85) ? 'excellent' : 'good';
        } elseif ($total_time <= 2.5) {
            // Good range: 72-52 (linear scale)
            $performance_score = round(72 - (($total_time - 1.2) * 15.38));
            $rating = ($performance_score >= 70) ? 'good' : 'fair';
        } elseif ($total_time <= 5.0) {
            // Fair range: 52-32 (linear scale)
            $performance_score = round(52 - (($total_time - 2.5) * 8.0));
            $rating = ($performance_score >= 50) ? 'fair' : 'poor';
        } elseif ($total_time <= 10.0) {
            // Extended range: 5s to 10s gets 32-15 points
            $performance_score = round(32 - (($total_time - 5.0) * 3.4));
            $rating = 'poor';
        } elseif ($total_time <= 20.0) {
            // Very slow range: 10s to 20s gets 15-5 points
            $performance_score = round(15 - (($total_time - 10.0) * 1.0));
            $rating = 'poor';
        } else {
            // Extremely slow: 20s+ gets minimum 3 points (not 0)
            $performance_score = max(3, round(5 - (($total_time - 20.0) * 0.1)));
            $rating = 'critical';
        }
        
        // Apply cache bonus (before timeout deductions)
        $performance_score = min(100, $performance_score + $cache_bonus);
        
        // Apply score deduction for timed out components
        $performance_score = max(0, $performance_score - $total_score_deduction);
        
        // Update rating based on final score after deductions
        if ($performance_score >= 85) {
            $rating = 'excellent';
        } elseif ($performance_score >= 70) {
            $rating = 'good';
        } elseif ($performance_score >= 50) {
            $rating = 'fair';
        } elseif ($performance_score >= 30) {
            $rating = 'poor';
        } else {
            $rating = 'critical';
        }
        
        $results = array(
            'price_calc_time' => round($price_calc_time * 1000, 2),
            'shipping_calc_time' => round($shipping_calc_time * 1000, 2),
            'inventory_check_time' => round($inventory_check_time * 1000, 2),
            'total_time' => round($total_time * 1000, 2),
            'products_calculated' => $products_count * $iterations,
            'test_iterations' => $test_iterations,
            'opcache_enabled' => $opcache_enabled,
            'apc_enabled' => $apc_enabled,
            'xcache_enabled' => $xcache_enabled,
            'score' => $performance_score,
            'rating' => $rating,
            'interpretation' => $this->get_performance_interpretation($total_time),
            'timed_out_tests' => $timed_out_tests,
            'score_deduction' => $total_score_deduction,
            'component_scores' => array(
                'price_calc' => !isset($timed_out_tests['price']),
                'shipping_calc' => !isset($timed_out_tests['shipping']),
                'inventory_check' => !isset($timed_out_tests['inventory'])
            ),
            'cache_bonus' => $cache_bonus
        );
        
        // Save raw performance test results to file for debugging
        $upload_dir = wp_upload_dir();
        $results_file = $upload_dir['basedir'] . '/divewp-performance-test-results.json';
        
        try {
            @file_put_contents($results_file, json_encode($results, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
                            // Failed to save results file
        }
        
        return $results;
    }
    
    /**
     * Get performance interpretation based on total test time
     * 
     * @param float $total_time Total time in seconds
     * @return string Human-readable interpretation
     */
    private function get_performance_interpretation($total_time) {
        if ($total_time < 0.5) {
            return esc_html__('Excellent speed! Can handle high-traffic sales and complex calculations.', 'divewp-boost-site-performance');
        } elseif ($total_time < 1.5) {
            return esc_html__('Good performance. Suitable for most WooCommerce stores.', 'divewp-boost-site-performance');
        } elseif ($total_time < 3.0) {
            return esc_html__('Fair speed. May slow down during peak traffic or sales.', 'divewp-boost-site-performance');
        } elseif ($total_time < 10.0) {
            return esc_html__('Poor performance. Will struggle with WooCommerce operations.', 'divewp-boost-site-performance');
        } else {
            return esc_html__('Critical performance issues. Server is too slow for WooCommerce.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Run price calculation test independently
     *
     * @since 2.0.4
     * @return array|WP_Error Price calculation test results or error
     */
    public function run_price_calculation_test() {
        // Check rate limiting
        $rate_limit_key = 'divewp_performance_price_test_rate_limit_' . get_current_user_id();
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $rate_limit_duration = $php_max_execution_time > 0 ? $php_max_execution_time : 30;
        
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another price calculation test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, $rate_limit_duration);
        
        try {
            $start_time = microtime(true);
            $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 20;
            
            // Temporarily disable cache addition during tests
            wp_suspend_cache_addition(true);
            
            $test_iterations = 15;
            $price_calc_times = array();
            $products_count = 2500;
            $iterations = 10;
            $timed_out = false;
            
            for ($test_run = 0; $test_run < $test_iterations && !$timed_out; $test_run++) {
                $price_calc_start = microtime(true);
                
                for ($iteration = 0; $iteration < $iterations; $iteration++) {
                    // Check for timeout
                    if ((microtime(true) - $start_time) > $max_test_time) {
                        $timed_out = true;
                        break;
                    }
                    
                    $cart_total = 0;
                    $tax_total = 0;
                    $discount_total = 0;
                    
                    for ($i = 0; $i < $products_count; $i++) {
                        // Variable product pricing
                        $base_price = mt_rand(1000, 50000) / 100;
                        $quantity = mt_rand(1, 5);
                        
                        // Apply bulk discounts
                        $discount_percent = 0;
                        if ($quantity >= 5) $discount_percent = 10;
                        if ($quantity >= 10) $discount_percent = 15;
                        
                        $discounted_price = $base_price * (1 - $discount_percent / 100);
                        
                        // Tax calculations with different rates
                        $tax_rates = array(
                            'standard' => 10,
                            'reduced' => 5,
                            'zero' => 0
                        );
                        
                        $tax_class = array_rand($tax_rates);
                        $tax_rate = $tax_rates[$tax_class];
                        
                        // Calculate line items
                        $line_subtotal = $base_price * $quantity;
                        $line_total = $discounted_price * $quantity;
                        $line_tax = ($line_total * $tax_rate) / 100;
                        $line_discount = $line_subtotal - $line_total;
                        
                        // Add to totals
                        $cart_total += $line_total;
                        $tax_total += $line_tax;
                        $discount_total += $line_discount;
                        
                        // Format with WordPress functions
                        $formatted_price = number_format_i18n($line_total, 2);
                        $formatted_tax = number_format_i18n($line_tax, 2);
                    }
                    
                    // Coupon validation simulation
                    $coupon_codes = array('SAVE10', 'FREESHIP', 'WELCOME20');
                    foreach ($coupon_codes as $code) {
                        $is_valid = (mt_rand(0, 10) > 5);
                        if ($is_valid) {
                            $coupon_discount = $cart_total * 0.1;
                            $cart_total -= $coupon_discount;
                            $discount_total += $coupon_discount;
                        }
                    }
                    
                    // Currency conversion simulation
                    $exchange_rates = array('EUR' => 0.85, 'GBP' => 0.73, 'JPY' => 110.50);
                    foreach ($exchange_rates as $currency => $rate) {
                        $converted = $cart_total * $rate;
                        $formatted = number_format_i18n($converted, 2);
                    }
                }
                
                if (!$timed_out) {
                    $price_calc_times[] = microtime(true) - $price_calc_start;
                }
            }
            
            wp_suspend_cache_addition(false);
            
            $avg_time = count($price_calc_times) > 0 ? array_sum($price_calc_times) / count($price_calc_times) : 0;
            
            return array(
                'price_calc_time' => round($avg_time * 1000, 2),
                'products_calculated' => $products_count * $iterations,
                'test_iterations' => count($price_calc_times),
                'timed_out' => $timed_out,
                'test_type' => 'price_calculation'
            );
            
        } catch (Exception $e) {
            wp_suspend_cache_addition(false);
            delete_transient($rate_limit_key);
            return new WP_Error('price_calc_failed', esc_html($e->getMessage()));
        }
    }

    /**
     * Run shipping calculation test independently
     *
     * @since 2.0.4
     * @return array|WP_Error Shipping calculation test results or error
     */
    public function run_shipping_calculation_test() {
        // Check rate limiting
        $rate_limit_key = 'divewp_performance_shipping_test_rate_limit_' . get_current_user_id();
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $rate_limit_duration = $php_max_execution_time > 0 ? $php_max_execution_time : 30;
        
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another shipping calculation test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, $rate_limit_duration);
        
        try {
            $start_time = microtime(true);
            $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 20;
            
            wp_suspend_cache_addition(true);
            
            $test_iterations = 15;
            $shipping_calc_times = array();
            $timed_out = false;
            
            for ($test_run = 0; $test_run < $test_iterations && !$timed_out; $test_run++) {
                $shipping_calc_start = microtime(true);
                
                // Check for timeout
                if ((microtime(true) - $start_time) > $max_test_time) {
                    $timed_out = true;
                    break;
                }
                
                for ($i = 0; $i < 1250; $i++) {
                    $zones = array('domestic', 'international', 'express');
                    $total_weight = mt_rand(1000, 10000) / 1000;
                    
                    foreach ($zones as $zone) {
                        $base_rate = mt_rand(500, 2000) / 100;
                        $weight_rate = $total_weight * 2;
                        $shipping_cost = $base_rate + $weight_rate;
                        
                        if ($zone === 'express') {
                            $shipping_cost *= 1.5;
                        }
                        
                        $formatted_shipping = number_format_i18n($shipping_cost, 2);
                    }
                }
                
                if (!$timed_out) {
                    $shipping_calc_times[] = microtime(true) - $shipping_calc_start;
                }
            }
            
            wp_suspend_cache_addition(false);
            
            $avg_time = count($shipping_calc_times) > 0 ? array_sum($shipping_calc_times) / count($shipping_calc_times) : 0;
            
            return array(
                'shipping_calc_time' => round($avg_time * 1000, 2),
                'calculations_performed' => 1250,
                'test_iterations' => count($shipping_calc_times),
                'timed_out' => $timed_out,
                'test_type' => 'shipping_calculation'
            );
            
        } catch (Exception $e) {
            wp_suspend_cache_addition(false);
            delete_transient($rate_limit_key);
            return new WP_Error('shipping_calc_failed', esc_html($e->getMessage()));
        }
    }

    /**
     * Run inventory check test independently
     *
     * @since 2.0.4
     * @return array|WP_Error Inventory check test results or error
     */
    public function run_inventory_check_test() {
        // Check rate limiting
        $rate_limit_key = 'divewp_performance_inventory_test_rate_limit_' . get_current_user_id();
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $rate_limit_duration = $php_max_execution_time > 0 ? $php_max_execution_time : 30;
        
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another inventory check test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, $rate_limit_duration);
        
        try {
            $start_time = microtime(true);
            $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 20;
            
            wp_suspend_cache_addition(true);
            
            $test_iterations = 15;
            $inventory_check_times = array();
            $timed_out = false;
            
            for ($test_run = 0; $test_run < $test_iterations && !$timed_out; $test_run++) {
                $inventory_check_start = microtime(true);
                
                // Check for timeout
                if ((microtime(true) - $start_time) > $max_test_time) {
                    $timed_out = true;
                    break;
                }
                
                for ($i = 0; $i < 1500; $i++) {
                    $stock_levels = array();
                    for ($j = 0; $j < 50; $j++) {
                        $stock_levels[$j] = mt_rand(0, 100);
                        $is_in_stock = $stock_levels[$j] > 0;
                        $is_low_stock = $stock_levels[$j] < 5 && $stock_levels[$j] > 0;
                    }
                }
                
                if (!$timed_out) {
                    $inventory_check_times[] = microtime(true) - $inventory_check_start;
                }
            }
            
            wp_suspend_cache_addition(false);
            
            $avg_time = count($inventory_check_times) > 0 ? array_sum($inventory_check_times) / count($inventory_check_times) : 0;
            
            return array(
                'inventory_check_time' => round($avg_time * 1000, 2),
                'checks_performed' => 1500,
                'test_iterations' => count($inventory_check_times),
                'timed_out' => $timed_out,
                'test_type' => 'inventory_check'
            );
            
        } catch (Exception $e) {
            wp_suspend_cache_addition(false);
            delete_transient($rate_limit_key);
            return new WP_Error('inventory_check_failed', esc_html($e->getMessage()));
        }
    }
} 