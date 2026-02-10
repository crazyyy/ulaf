<?php
/**
 * Database Math Functions Test
 *
 * Tests database performance for mathematical calculations.
 * Performs 5,000 advanced mathematical operations using MySQL functions.
 * Enhanced with complex trigonometry, logarithms, and advanced mathematical operations.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.0.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database Math Functions Test Class
 * 
 * Performs mathematical operations performance tests using MySQL
 * mathematical functions with advanced trigonometry and complex calculations.
 */
class DiveWP_Math_Functions_Test {

    /**
     * Run the mathematical functions performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'operations_count' => 5000
        );

        $start_time = microtime(true);
        $operations_completed = 0;
        $operation_times = array();

        try {
            // Run advanced mathematical operations
            for ($i = 0; $i < $config['operations_count']; $i++) {
                $op_start = microtime(true);

                // Generate test values with more variety for complex calculations
                $num1 = ($i % 1000) + 1;
                $num2 = ($i % 100) + 1;
                $num3 = ($i % 50) + 1;
                $angle = ($i % 360) * (M_PI / 180); // Convert to radians for trigonometry
                $small_num = ($i % 10) + 0.1; // For logarithms and exponentials

                // Perform intensive mathematical operations in a single complex query (like PoC)
                // MATH FUNCTIONS BENCHMARK - Direct query required for mathematical performance measurement
                // WordPress abstractions would distort timing results and defeat mathematical testing purpose
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $query = $wpdb->prepare("
                    SELECT 
                        -- Basic arithmetic and rounding
                        ABS(%f) as abs_val,
                        CEILING(%f) as ceil_val,
                        FLOOR(%f) as floor_val,
                        ROUND(%f, 4) as round_val,
                        TRUNCATE(%f, 2) as trunc_val,
                        
                        -- Advanced arithmetic operations
                        SQRT(%d) as sqrt_val,
                        POW(%d, 3) as power_val,
                        POW(%f, 0.5) as fractional_power,
                        MOD(%d, %d) as mod_val,
                        
                        -- Trigonometric functions (intensive CPU operations)
                        SIN(%f) as sin_val,
                        COS(%f) as cos_val,
                        TAN(%f) as tan_val,
                        ASIN(0.5) as asin_val,
                        ACOS(0.5) as acos_val,
                        ATAN(%f) as atan_val,
                        ATAN2(%f, %f) as atan2_val,
                        
                        -- Angle conversion functions
                        DEGREES(%f) as degrees_val,
                        RADIANS(%d) as radians_val,
                        
                        -- Logarithmic and exponential functions
                        LOG(%d) as log_natural,
                        LOG10(%d) as log_base10,
                        LOG2(%d) as log_base2,
                        EXP(%f) as exp_val,
                        
                        -- Complex mathematical combinations
                        SQRT(POW(%d, 2) + POW(%d, 2)) as pythagorean,
                        SIN(%f) * COS(%f) + TAN(%f) as trig_combination,
                        LOG(EXP(%f)) as log_exp_identity,
                        POW(SQRT(%d), 2) as power_sqrt_identity,
                        
                        -- Statistical and comparison functions
                        GREATEST(%d, %d, %d) as greatest_val,
                        LEAST(%d, %d, %d) as least_val,
                        SIGN(%f) as sign_val,
                        
                        -- Random number generation (CPU intensive)
                        RAND() as random_val,
                        RAND() * %d as scaled_random,
                        
                        -- Advanced mathematical formulas
                        PI() as pi_constant,
                        EXP(1) as e_constant,
                        SQRT(2 * PI()) as sqrt_2pi
                ", 
                    -$num1,                          // ABS
                    $num1 + 0.7,                     // CEILING
                    $num1 + 0.7,                     // FLOOR
                    $num1 + 0.123456789,             // ROUND
                    $num1 + 0.987654321,             // TRUNCATE
                    
                    $num1,                           // SQRT
                    $num2,                           // POW (integer)
                    $num1,                           // POW (fractional)
                    $num1, $num2,                    // MOD
                    
                    $angle,                          // SIN
                    $angle,                          // COS
                    $angle,                          // TAN
                    $angle / 10,                     // ATAN
                    $num1 / 100, $num2 / 100,       // ATAN2
                    
                    $angle,                          // DEGREES
                    $num2,                           // RADIANS
                    
                    $num1,                           // LOG (natural)
                    $num1,                           // LOG10
                    $num1,                           // LOG2
                    $small_num,                      // EXP
                    
                    $num2, $num3,                    // Pythagorean theorem
                    $angle, $angle, $angle,          // Trigonometric combination
                    $small_num,                      // LOG(EXP(x)) identity
                    $num1,                           // POW(SQRT(x), 2) identity
                    
                    $num1, $num2, $num3,             // GREATEST
                    $num1, $num2, $num3,             // LEAST
                    $num1 - 500,                     // SIGN (can be negative)
                    
                    $num1                            // Scaled random
                );

                // MATH FUNCTIONS BENCHMARK - Direct query execution required for mathematical performance measurement
                // WordPress abstractions would invalidate mathematical function timing accuracy
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability within tight loop
                $results = $wpdb->get_row($query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

                // Additional complex mathematical calculations every 10 iterations
                if ($i % 10 === 0) {
                    // Advanced trigonometric and hyperbolic calculations
                    // MATH FUNCTIONS BENCHMARK - Direct query required for advanced mathematical performance testing
                    // WordPress abstractions would interfere with trigonometric and logarithmic timing accuracy
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $advanced_query = $wpdb->prepare("
                        SELECT 
                            -- Complex trigonometric identities
                            SIN(%f) * SIN(%f) + COS(%f) * COS(%f) as trig_identity1,
                            (SIN(%f) + COS(%f)) * (SIN(%f) - COS(%f)) as trig_identity2,
                            TAN(%f) / (1 + POW(TAN(%f), 2)) as tan_identity,
                            
                            -- Logarithmic calculations
                            LOG(%d) + LOG(%d) - LOG(%d * %d) as log_property,
                            EXP(LOG(%d)) - %d as exp_log_identity,
                            
                            -- Complex power calculations
                            POW(%d, LOG(%d)) as power_log_combo,
                            SQRT(SQRT(%d)) - POW(%d, 0.25) as nested_roots,
                            
                            -- Mathematical series approximations
                            (POW(%f, 1) / 1) - (POW(%f, 3) / 6) + (POW(%f, 5) / 120) as sin_series,
                            1 - (POW(%f, 2) / 2) + (POW(%f, 4) / 24) as cos_series,
                            
                            -- Statistical calculations
                            SQRT((%d - %f) * (%d - %f)) as variance_component,
                            ABS(%d - %d) / GREATEST(%d, %d) as relative_difference
                    ", 
                        $angle, $angle, $angle, $angle,     // Trig identity 1 (sin²+cos²=1)
                        $angle, $angle, $angle, $angle,     // Trig identity 2 (difference of squares)
                        $angle, $angle,                     // Tan identity
                        
                        $num2, $num3, $num2, $num3,         // LOG(a) + LOG(b) = LOG(a*b)
                        $num2, $num2,                       // EXP(LOG(x)) = x
                        
                        $num2, $num3,                       // Power-log combination
                        $num1, $num1,                       // Nested roots identity
                        
                        $angle / 10, $angle / 10, $angle / 10,  // Sin Taylor series
                        $angle / 10, $angle / 10,               // Cos Taylor series
                        
                        $num1, $num1 * 1.1, $num1, $num1 * 1.1,    // Variance component
                        $num1, $num2, $num1, $num2                   // Relative difference
                    );
                    
                    // MATH FUNCTIONS BENCHMARK - Direct query execution required for advanced mathematical performance measurement
                    // WordPress abstractions would distort advanced mathematical function timing accuracy
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability
                    $advanced_results = $wpdb->get_row($advanced_query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                }

                // Intensive mathematical operations every 50 iterations for CPU stress
                if ($i % 50 === 0) {
                    // Financial and engineering calculations
                    // MATH FUNCTIONS BENCHMARK - Direct query required for financial/engineering mathematical performance testing
                    // WordPress abstractions would invalidate complex mathematical formula timing accuracy
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $financial_query = $wpdb->prepare("
                        SELECT 
                            -- Compound interest formula: A = P(1 + r/n)^(nt)
                            %d * POW(1 + %f / 12, 12 * %d) as compound_interest,
                            
                            -- Distance formula in 3D space
                            SQRT(POW(%d - %d, 2) + POW(%d - %d, 2) + POW(%d - %d, 2)) as distance_3d,
                            
                            -- Quadratic formula components
                            (-1 * %d + SQRT(POW(%d, 2) - 4 * %d * %d)) / (2 * %d) as quadratic_root1,
                            (-1 * %d - SQRT(POW(%d, 2) - 4 * %d * %d)) / (2 * %d) as quadratic_root2,
                            
                            -- Geometric series sum: a(1-r^n)/(1-r)
                            %d * (1 - POW(%f, %d)) / (1 - %f) as geometric_series,
                            
                            -- Normal distribution approximation
                            EXP(-0.5 * POW((%d - %d) / %d, 2)) / (%d * SQRT(2 * PI())) as normal_dist
                    ",
                        1000, 0.05, 10,                         // Compound interest ($1000, 5% APR, 10 years)
                        $num1, $num2, $num2, $num3, $num3, $num1,   // 3D distance
                        $num2, $num2, $num3, $num1, $num3,          // Quadratic formula +
                        $num2, $num2, $num3, $num1, $num3,          // Quadratic formula -
                        $num2, 0.8, $num3 % 10, 0.8,               // Geometric series
                        $num1, $num2, $num3 % 10, $num3 % 10       // Normal distribution
                    );
                    
                    // MATH FUNCTIONS BENCHMARK - Direct query execution required for financial/engineering performance measurement
                    // WordPress abstractions would distort complex mathematical formula timing accuracy
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability
                    $financial_results = $wpdb->get_row($financial_query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                }

                $op_end = microtime(true);
                $operation_times[] = $op_end - $op_start;
                $operations_completed++;

                // Check time limit every 500 operations
                if ($i % 500 === 0 && (microtime(true) - $start_time) > 25) {
                    break;
                }
            }

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG for diagnosing DB errors
                error_log('DiveWP Math Functions Test Error: ' . $e->getMessage());
            }
        }

        $end_time = microtime(true);
        $total_time = $end_time - $start_time;

        // Calculate statistics
        $avg_operation_time = !empty($operation_times) ? array_sum($operation_times) / count($operation_times) : 0;
        $max_operation_time = !empty($operation_times) ? max($operation_times) : 0;
        $min_operation_time = !empty($operation_times) ? min($operation_times) : 0;

        $operations_per_second = ($operations_completed > 0) ? $operations_completed / $total_time : 0;
        
        // CRITICAL: Calculate score using the scoring system
        require_once __DIR__ . '/scoring.php';
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('math_functions', array(
            'status' => 'completed',
            'operations_per_second' => $operations_per_second
        ));
        
        $rating = 'unknown';
        if ($score >= 85) {
            $rating = 'excellent';
        } elseif ($score >= 70) {
            $rating = 'good';
        } elseif ($score >= 50) {
            $rating = 'fair';
        } else {
            $rating = 'poor';
        }

        $result = array(
            'test_name' => 'math_functions',
            'operations_completed' => $operations_completed,
            'operations_requested' => $config['operations_count'],
            'total_time' => round($total_time, 4),
            'avg_operation_time' => round($avg_operation_time * 1000, 4), // Convert to milliseconds
            'max_operation_time' => round($max_operation_time * 1000, 4),
            'min_operation_time' => round($min_operation_time * 1000, 4),
            'operations_per_second' => round($operations_per_second, 2),
            'score' => round($score, 1),
            'rating' => $rating,
            'interpretation' => sprintf(
                // translators: %1$s is the operations per second rate (e.g., "45", "123") for database mathematical function performance
                __('Advanced math functions completed at %1$s operations/second (trigonometry, logarithms, complex calculations)', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0)
            ),
            'status' => 'completed',
            'memory_used' => memory_get_usage(true),
            'timestamp' => current_time('mysql')
        );
        // ENHANCED UX: Add performance interpretation using scoring class (consistent with other DB sub-tests)
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('math_functions', $result);

        return $result;
    }
} 