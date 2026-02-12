<?php
/**
 * Price Calculations Test
 *
 * Tests how fast product prices can be calculated - simulates WooCommerce pricing operations.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.0.4
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Price Calculations Test Implementation
 */
class DiveWP_Price_Calculations_Test {

    /**
     * Run price calculations test
     *
     * Purpose: How fast product prices update
     * Details: 2,500 calculations × 15 iterations
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    public static function run($config = array()) {
        $defaults = array(
            'iterations' => 15,
            'operations_per_iteration' => 2500
        );
        $config = array_merge($defaults, $config);

        $target_total_time = 4.5;
        $target_iteration_time = 0.35;
        $min_total_time = 2.2; // Ensure each test runs at least ~2 seconds
        $warmup_ops = 300;

        $start_time = microtime(true);
        $iteration_times = array();
        $operations_completed = 0;

        try {
            $warmup_start = microtime(true);
            for ($w = 0; $w < $warmup_ops; $w++) {
                self::execute_price_operation();
            }
            $warmup_elapsed = max(0.000001, microtime(true) - $warmup_start);
            $op_time = $warmup_elapsed / $warmup_ops;

            $computed_ops_per_iter = max(500, min(120000, (int)floor($target_iteration_time / $op_time)));
            $computed_iterations = max(6, min(25, (int)ceil($target_total_time / $target_iteration_time)));

            $config['operations_per_iteration'] = $computed_ops_per_iter;
            $config['iterations'] = $computed_iterations;

            for ($i = 0; $i < $config['iterations']; $i++) {
                $iteration_start = microtime(true);
                for ($j = 0; $j < $config['operations_per_iteration']; $j++) {
                    self::execute_price_operation();
                    $operations_completed++;
                }
                $iteration_time = microtime(true) - $iteration_start;
                $iteration_times[] = $iteration_time;
            }

            // Ensure minimum total runtime by adding extra iterations if needed
            $total_time = microtime(true) - $start_time;
            $safety_cap_iterations = 50;
            while ($total_time < $min_total_time && count($iteration_times) < $safety_cap_iterations) {
                $iteration_start = microtime(true);
                for ($j = 0; $j < $config['operations_per_iteration']; $j++) {
                    self::execute_price_operation();
                    $operations_completed++;
                }
                $iteration_time = microtime(true) - $iteration_start;
                $iteration_times[] = $iteration_time;
                $total_time = microtime(true) - $start_time;
                $config['iterations']++;
            }

            $average_time = array_sum($iteration_times) / count($iteration_times);
            $min_time = min($iteration_times);
            $max_time = max($iteration_times);

            $result = array(
                'test_name' => 'Price Calculations',
                'status' => 'completed',
                'total_time' => $total_time,
                'average_iteration_time' => $average_time,
                'min_iteration_time' => $min_time,
                'max_iteration_time' => $max_time,
                'iterations' => $config['iterations'],
                'operations_per_iteration' => $config['operations_per_iteration'],
                'total_operations' => $operations_completed,
                'operations_per_second' => $operations_completed / $total_time,
                'iteration_times' => $iteration_times,
                'timestamp' => current_time('mysql')
            );

            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/performance/scoring.php';
            $result['performance_interpretation'] = DiveWP_Benchmark_Performance_Scoring::get_sub_test_performance_interpretation('price_calculations', $result);

            return $result;

        } catch (Exception $e) {
            return array(
                'test_name' => 'Price Calculations',
                'status' => 'error',
                'message' => $e->getMessage(),
                'operations_completed' => $operations_completed,
                'timestamp' => current_time('mysql')
            );
        }
    }

    /**
     * Execute a single price calculation operation
     */
    private static function execute_price_operation() {
        $base_price = wp_rand(1000, 100000) / 100;
        $quantity = wp_rand(1, 10);
        $country_tax = wp_rand(500, 2500) / 10000;
        $state_tax = wp_rand(0, 1000) / 10000;
        $city_tax = wp_rand(0, 500) / 10000;
        $product_discount = wp_rand(0, 5000) / 10000;
        $cart_discount = wp_rand(0, 2000) / 10000;
        $coupon_fixed = wp_rand(0, 2000) / 100;
        $weight = wp_rand(100, 5000) / 1000;
        $shipping_base = 5.00;
        $shipping_per_kg = 2.50;
        $subtotal = $base_price * $quantity;
        $discounted_price = $subtotal * (1 - $product_discount);
        if ($coupon_fixed > 0 && $coupon_fixed < $discounted_price) {
            $discounted_price -= $coupon_fixed;
        }
        $discounted_price *= (1 - $cart_discount);
        $tax_amount = $discounted_price * ($country_tax + $state_tax + $city_tax);
        $shipping_cost = $shipping_base + ($weight * $quantity * $shipping_per_kg);
        $final_price = $discounted_price + $tax_amount + $shipping_cost;
        $price_history = array();
        for ($k = 0; $k < 5; $k++) {
            $price_history[] = $final_price * (1 + (wp_rand(-10, 10) / 100));
        }
        $avg_historical = array_sum($price_history) / count($price_history);
        $competitor_prices = array();
        for ($k = 0; $k < 3; $k++) {
            $competitor_prices[] = $final_price * (1 + (wp_rand(-20, 20) / 100));
        }
        $cost_price = $base_price * 0.4;
        $min_viable_price = $cost_price * 1.2;
        return;
    }
} 