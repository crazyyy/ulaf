<?php
/**
 * Shipping Calculations Test
 *
 * Tests the speed of shipping cost calculations - simulates WooCommerce shipping operations.
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
 * Shipping Calculations Test Implementation
 */
class DiveWP_Shipping_Calculations_Test {

    /**
     * Run shipping calculations test
     *
     * Purpose: Speed of shipping cost calculations
     * Details: 1,250 calculations × 15 iterations
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    public static function run($config = array()) {
        $defaults = array(
            'iterations' => 15,
            'operations_per_iteration' => 1250
        );
        $config = array_merge($defaults, $config);

        $target_total_time = 4.5;
        $target_iteration_time = 0.35;
        $min_total_time = 2.2;
        $warmup_ops = 200;

        $start_time = microtime(true);
        $iteration_times = array();
        $operations_completed = 0;

        try {
            $shipping_zones = array(
                'domestic' => array('min_distance' => 0, 'max_distance' => 500, 'base_rate' => 5.00),
                'regional' => array('min_distance' => 500, 'max_distance' => 2000, 'base_rate' => 15.00),
                'international' => array('min_distance' => 2000, 'max_distance' => 20000, 'base_rate' => 35.00)
            );
            $shipping_classes = array(
                'standard' => array('multiplier' => 1.0, 'days' => '5-7'),
                'express' => array('multiplier' => 1.5, 'days' => '2-3'),
                'overnight' => array('multiplier' => 2.5, 'days' => '1')
            );

            $warmup_start = microtime(true);
            for ($w = 0; $w < $warmup_ops; $w++) {
                self::execute_shipping_operation($shipping_zones, $shipping_classes);
            }
            $warmup_elapsed = max(0.000001, microtime(true) - $warmup_start);
            $op_time = $warmup_elapsed / $warmup_ops;

            $computed_ops_per_iter = max(300, min(80000, (int)floor($target_iteration_time / $op_time)));
            $computed_iterations = max(6, min(25, (int)ceil($target_total_time / $target_iteration_time)));

            $config['operations_per_iteration'] = $computed_ops_per_iter;
            $config['iterations'] = $computed_iterations;

            for ($i = 0; $i < $config['iterations']; $i++) {
                $iteration_start = microtime(true);
                for ($j = 0; $j < $config['operations_per_iteration']; $j++) {
                    self::execute_shipping_operation($shipping_zones, $shipping_classes);
                    $operations_completed++;
                }
                $iteration_time = microtime(true) - $iteration_start;
                $iteration_times[] = $iteration_time;
            }

            // Ensure minimum total runtime
            $total_time = microtime(true) - $start_time;
            $safety_cap_iterations = 50;
            while ($total_time < $min_total_time && count($iteration_times) < $safety_cap_iterations) {
                $iteration_start = microtime(true);
                for ($j = 0; $j < $config['operations_per_iteration']; $j++) {
                    self::execute_shipping_operation($shipping_zones, $shipping_classes);
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
                'test_name' => 'Shipping Calculations',
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
            $result['performance_interpretation'] = DiveWP_Benchmark_Performance_Scoring::get_sub_test_performance_interpretation('shipping_calculations', $result);

            return $result;

        } catch (Exception $e) {
            return array(
                'test_name' => 'Shipping Calculations',
                'status' => 'error',
                'message' => $e->getMessage(),
                'operations_completed' => $operations_completed,
                'timestamp' => current_time('mysql')
            );
        }
    }

    /**
     * Execute a single shipping calculation operation
     */
    private static function execute_shipping_operation($shipping_zones, $shipping_classes) {
        $cart_items = wp_rand(1, 10);
        $total_weight = 0;
        $total_volume = 0;
        $fragile_items = 0;
        $hazardous_items = 0;
        for ($k = 0; $k < $cart_items; $k++) {
            $item_weight = wp_rand(100, 10000) / 1000;
            $item_length = wp_rand(10, 100);
            $item_width = wp_rand(10, 100);
            $item_height = wp_rand(10, 100);
            $total_weight += $item_weight;
            $total_volume += ($item_length * $item_width * $item_height) / 1000000;
            if (wp_rand(1, 10) > 8) $fragile_items++;
            if (wp_rand(1, 20) > 18) $hazardous_items++;
        }
        $origin_lat = wp_rand(-90000, 90000) / 1000;
        $origin_lon = wp_rand(-180000, 180000) / 1000;
        $dest_lat = wp_rand(-90000, 90000) / 1000;
        $dest_lon = wp_rand(-180000, 180000) / 1000;
        $distance = sqrt(pow($dest_lat - $origin_lat, 2) + pow($dest_lon - $origin_lon, 2)) * 111;
        $zone = 'domestic';
        foreach ($shipping_zones as $zone_name => $zone_data) {
            if ($distance >= $zone_data['min_distance'] && $distance < $zone_data['max_distance']) {
                $zone = $zone_name;
                break;
            }
        }
        $shipping_costs = array();
        foreach ($shipping_classes as $class_name => $class_data) {
            $base_cost = $shipping_zones[$zone]['base_rate'];
            $weight_cost = $total_weight * 2.50;
            $dim_weight = $total_volume * 200;
            if ($dim_weight > $total_weight) {
                $weight_cost = $dim_weight * 2.50;
            }
            $distance_cost = $distance * 0.05;
            $fragile_cost = $fragile_items * 5.00;
            $hazardous_cost = $hazardous_items * 25.00;
            $subtotal = $base_cost + $weight_cost + $distance_cost + $fragile_cost + $hazardous_cost;
            $total_cost = $subtotal * $class_data['multiplier'];
            $fuel_surcharge = $total_cost * (0.05 + ($distance / 10000));
            $total_cost += $fuel_surcharge;
            if ((int) wp_date('w') === 0 || (int) wp_date('w') === 6) {
                $total_cost *= 1.15;
            }
            if (wp_rand(1, 10) > 7) {
                $total_cost += 10.00;
            }
            $shipping_costs[$class_name] = array(
                'cost' => $total_cost,
                'delivery_days' => $class_data['days'],
                'zone' => $zone,
                'distance' => round($distance, 2)
            );
        }
        $cheapest = min(array_column($shipping_costs, 'cost'));
        $fastest = 'overnight';
        $lead_time = wp_rand(1, 3);
        $delivery_date = wp_date('Y-m-d', current_time('timestamp') + ($lead_time * DAY_IN_SECONDS));
        return;
    }
} 