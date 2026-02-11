<?php
/**
 * Inventory Operations Test
 *
 * Tests stock level checking speed - simulates WooCommerce inventory operations.
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
 * Inventory Operations Test Implementation
 */
class DiveWP_Inventory_Operations_Test {

    /**
     * Run inventory operations test
     *
     * Purpose: Stock level checking speed
     * Details: 1,500 checks × 15 iterations
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    public static function run($config = array()) {
        $defaults = array(
            'iterations' => 15,
            'operations_per_iteration' => 1500
        );
        $config = array_merge($defaults, $config);

        $target_total_time = 4.5;
        $target_iteration_time = 0.35;
        $min_total_time = 2.2;
        $warmup_ops = 180;

        $start_time = microtime(true);
        $iteration_times = array();
        $operations_completed = 0;

        try {
            $warehouses = array(
                'main' => array('capacity' => 10000, 'utilization' => 0.7),
                'secondary' => array('capacity' => 5000, 'utilization' => 0.5),
                'dropship_1' => array('capacity' => 'unlimited', 'utilization' => 0),
                'dropship_2' => array('capacity' => 'unlimited', 'utilization' => 0)
            );
            $product_types = array('simple', 'variable', 'grouped', 'bundle');

            $warmup_start = microtime(true);
            for ($w = 0; $w < $warmup_ops; $w++) {
                self::execute_inventory_operation($warehouses, $product_types);
            }
            $warmup_elapsed = max(0.000001, microtime(true) - $warmup_start);
            $op_time = $warmup_elapsed / $warmup_ops;

            $computed_ops_per_iter = max(300, min(100000, (int)floor($target_iteration_time / $op_time)));
            $computed_iterations = max(6, min(25, (int)ceil($target_total_time / $target_iteration_time)));

            $config['operations_per_iteration'] = $computed_ops_per_iter;
            $config['iterations'] = $computed_iterations;

            for ($i = 0; $i < $config['iterations']; $i++) {
                $iteration_start = microtime(true);
                for ($j = 0; $j < $config['operations_per_iteration']; $j++) {
                    self::execute_inventory_operation($warehouses, $product_types);
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
                    self::execute_inventory_operation($warehouses, $product_types);
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
                'test_name' => 'Inventory Operations',
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
            $result['performance_interpretation'] = DiveWP_Benchmark_Performance_Scoring::get_sub_test_performance_interpretation('inventory_operations', $result);

            return $result;

        } catch (Exception $e) {
            return array(
                'test_name' => 'Inventory Operations',
                'status' => 'error',
                'message' => $e->getMessage(),
                'operations_completed' => $operations_completed,
                'timestamp' => current_time('mysql')
            );
        }
    }

    /**
     * Execute a single inventory operation
     */
    private static function execute_inventory_operation($warehouses, $product_types) {
        $product_type = $product_types[array_rand($product_types)];
        $stock_levels = array();
        foreach ($warehouses as $warehouse_id => $warehouse_data) {
            if ($warehouse_data['capacity'] === 'unlimited') {
                $stock_levels[$warehouse_id] = wp_rand(0, 1000);
            } else {
                $max_stock = (int) floor($warehouse_data['capacity'] * $warehouse_data['utilization']);
                $stock_levels[$warehouse_id] = wp_rand(0, $max_stock);
            }
        }
        $total_physical_stock = array_sum($stock_levels);
        $pending_orders = wp_rand(0, 20);
        $reserved_stock = 0;
        for ($k = 0; $k < $pending_orders; $k++) {
            $reserved_stock += wp_rand(1, 5);
        }
        $backorders_allowed = wp_rand(0, 10) > 7;
        $max_backorder_qty = $backorders_allowed ? wp_rand(10, 50) : 0;
        $buffer_stock = wp_rand(5, 20);
        $available_stock = $total_physical_stock - $reserved_stock - $buffer_stock;
        if ($available_stock < 0) $available_stock = 0;
        if ($product_type === 'variable') {
            $variations = wp_rand(2, 10);
            $variation_stock = array();
            for ($v = 0; $v < $variations; $v++) {
                $var_stock = wp_rand(0, 50);
                $var_reserved = wp_rand(0, 10);
                $variation_stock[$v] = max(0, $var_stock - $var_reserved);
            }
            $available_stock = min($variation_stock);
        }
        if ($product_type === 'bundle') {
            $bundle_items = wp_rand(2, 5);
            $bundle_stock = array();
            for ($b = 0; $b < $bundle_items; $b++) {
                $item_stock = wp_rand(10, 100);
                $item_qty_required = wp_rand(1, 3);
                $bundle_stock[$b] = floor($item_stock / $item_qty_required);
            }
            $available_stock = min($bundle_stock);
        }
        $incoming_shipments = wp_rand(0, 3);
        $incoming_stock = 0;
        $next_restock_date = null;
        if ($incoming_shipments > 0) {
            for ($s = 0; $s < $incoming_shipments; $s++) {
                $shipment_qty = wp_rand(50, 500);
                $days_until_arrival = wp_rand(1, 30);
                $incoming_stock += $shipment_qty;
                if ($next_restock_date === null || $days_until_arrival < $next_restock_date) {
                    $next_restock_date = $days_until_arrival;
                }
            }
        }
        $stock_status = 'in_stock';
        if ($available_stock <= 0) {
            if ($backorders_allowed) {
                $stock_status = 'on_backorder';
            } else {
                $stock_status = 'out_of_stock';
            }
        } elseif ($available_stock <= $buffer_stock) {
            $stock_status = 'low_stock';
        }
        $order_qty = wp_rand(1, 10);
        $can_fulfill = false;
        $fulfillment_method = 'none';
        if ($available_stock >= $order_qty) {
            $can_fulfill = true;
            $fulfillment_method = 'immediate';
        } elseif ($backorders_allowed && ($available_stock + $max_backorder_qty) >= $order_qty) {
            $can_fulfill = true;
            $fulfillment_method = 'backorder';
        } elseif ($incoming_stock > 0 && ($available_stock + $incoming_stock) >= $order_qty) {
            $can_fulfill = true;
            $fulfillment_method = 'pre_order';
        }
        if ($can_fulfill && $fulfillment_method === 'immediate') {
            $remaining_to_reduce = $order_qty;
            foreach ($stock_levels as $warehouse_id => &$warehouse_stock) {
                if ($remaining_to_reduce <= 0) break;
                $reduce_from_warehouse = min($warehouse_stock, $remaining_to_reduce);
                $warehouse_stock -= $reduce_from_warehouse;
                $remaining_to_reduce -= $reduce_from_warehouse;
            }
            unset($warehouse_stock);
        }
        $stock_history = array();
        for ($h = 0; $h < 7; $h++) {
            $stock_history[] = array(
                'date' => wp_date('Y-m-d', current_time('timestamp') - ($h * DAY_IN_SECONDS)),
                'stock_level' => $total_physical_stock + wp_rand(-20, 20),
                'sales' => wp_rand(0, 15)
            );
        }
        $total_sales = array_sum(array_column($stock_history, 'sales'));
        $avg_daily_sales = $total_sales / 7;
        $days_until_stockout = $avg_daily_sales > 0 ? floor($available_stock / $avg_daily_sales) : 999;
        return;
    }
} 