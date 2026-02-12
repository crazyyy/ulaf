<?php
/**
 * Concurrency Tests Scoring Configuration
 *
 * Defines scoring logic, weights, and penalty calculations for concurrency tests.
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
 * Hosting Benchmark Concurrency Tests Scoring Class
 */
class DiveWP_Benchmark_Concurrency_Scoring {

    /**
     * Category weight in overall benchmark score
     * Concurrency tests contribute 25% to the total benchmark score
     */
    const CATEGORY_WEIGHT = 0.25;

    /**
     * Sub-test weights within the concurrency category
     * Total must equal 1.0 (100%)
     */
    const SUB_TEST_WEIGHTS = array(
        'database_concurrency' => 0.35,
        'http_concurrency'     => 0.25,
        'memory_concurrency'   => 0.25,
        'file_concurrency'     => 0.15
    );

    /**
     * Timeout/Kill penalties
     * Percentage of points deducted for timeout or process kill
     */
    const TIMEOUT_PENALTY = 0.6;  // 60% penalty
    const KILL_PENALTY = 0.8;     // 80% penalty

    /**
     * Performance thresholds for operations per second
     * Used to calculate scores based on performance levels
     */
    const PERFORMANCE_THRESHOLDS = array(
        'database_concurrency' => array(
            'excellent' => 80,    // 80+ ops/sec = 100 points
            'good'      => 60,    // 60+ ops/sec = 80 points
            'average'   => 40,    // 40+ ops/sec = 60 points
            'poor'      => 20,    // 20+ ops/sec = 40 points
            'critical'  => 0      // Below 20 ops/sec = 20 points
        ),
        'http_concurrency' => array(
            'excellent' => 6,     // 6+ requests/sec = 100 points
            'good'      => 4,     // 4+ requests/sec = 80 points
            'average'   => 2,     // 2+ requests/sec = 60 points
            'poor'      => 1,     // 1+ requests/sec = 40 points
            'critical'  => 0      // Below 1 requests/sec = 20 points
        ),
        'memory_concurrency' => array(
            'excellent' => 40,    // 40+ allocations/sec = 100 points
            'good'      => 30,    // 30+ allocations/sec = 80 points
            'average'   => 20,    // 20+ allocations/sec = 60 points
            'poor'      => 10,    // 10+ allocations/sec = 40 points
            'critical'  => 0      // Below 10 allocations/sec = 20 points
        ),
        'file_concurrency' => array(
            'excellent' => 60,    // 60+ file ops/sec = 100 points
            'good'      => 40,    // 40+ file ops/sec = 80 points
            'average'   => 25,    // 25+ file ops/sec = 60 points
            'poor'      => 15,    // 15+ file ops/sec = 40 points
            'critical'  => 0      // Below 15 file ops/sec = 20 points
        )
    );

    /**
     * Calculate score for a single sub-test
     *
     * @param string $test_name Test identifier
     * @param array  $result    Test result data
     * @return float Score from 0 to 100
     */
    public static function calculate_sub_test_score($test_name, $result) {
        // Check for timeout or kill
        if ($result['status'] === 'timeout') {
            return 100 * (1 - self::TIMEOUT_PENALTY);
        }
        
        if ($result['status'] === 'killed') {
            return 100 * (1 - self::KILL_PENALTY);
        }
        
        if ($result['status'] !== 'completed') {
            return 0; // Error or unknown status
        }

        // Throughput (ops/sec)
        $ops_per_second = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
        
        // Get thresholds for this test
        if (!isset(self::PERFORMANCE_THRESHOLDS[$test_name])) {
            return 0; // Unknown test
        }
        
        $thresholds = self::PERFORMANCE_THRESHOLDS[$test_name];

        // 1) Throughput points (0..40)
        $throughput_100 = self::calculate_performance_score($ops_per_second, $thresholds); // 0..100
        $throughput_points = ($throughput_100 / 100) * 40;

        // 2) Tail latency points (0..30) using response/batch times if present
        $tail_points = 30; // optimistic default
        $times = array();
        if (isset($result['response_times']) && is_array($result['response_times'])) {
            $times = $result['response_times'];
        } elseif (isset($result['batch_times']) && is_array($result['batch_times'])) {
            $times = $result['batch_times'];
        } elseif (isset($result['operation_times']) && is_array($result['operation_times'])) {
            $times = $result['operation_times'];
        }
        if (count($times) >= 5) {
            sort($times);
            $n = count($times);
            $p50 = $times[(int)floor(0.50 * ($n - 1))];
            $p95 = $times[(int)floor(0.95 * ($n - 1))];
            if ($p50 > 0) {
                $ratio = $p95 / $p50;
                if ($ratio <= 1.2) {
                    $tail_points = 30;
                } elseif ($ratio >= 1.6) {
                    $tail_points = 15;
                } else {
                    $tail_points = 30 - ((($ratio - 1.2) / (1.6 - 1.2)) * 15);
                }
            }
        }

        // 3) Success ratio points (0..20)
        $success_points = 20;
        $completed = $result['operations_completed'] ?? $result['requests_completed'] ?? $result['processes_completed'] ?? 0;
        $success   = $result['successful_operations'] ?? $result['successful_requests'] ?? $result['successful_allocations'] ?? 0;
        if ($completed > 0) {
            $sr = $success / $completed; // 0..1
            if ($sr >= 0.98) {
                $success_points = 20;
            } elseif ($sr <= 0.85) {
                $success_points = 10;
            } else {
                $success_points = 10 + ((($sr - 0.85) / (0.98 - 0.85)) * 10);
            }
        }

        // 4) Stability points (0..10) via coefficient of variation
        $stability_points = 10;
        if (count($times) >= 5) {
            $mean = array_sum($times) / count($times);
            if ($mean > 0) {
                $var = 0;
                foreach ($times as $t) { $var += ($t - $mean) * ($t - $mean); }
                $var /= count($times);
                $cv = sqrt($var) / $mean; // 0..∞
                if ($cv <= 0.10) {
                    $stability_points = 10;
                } elseif ($cv >= 0.35) {
                    $stability_points = 4;
                } else {
                    $stability_points = 10 - ((($cv - 0.10) / (0.35 - 0.10)) * 6);
                }
            }
        }

        // Sum
        $final_score = $throughput_points + $tail_points + $success_points + $stability_points; // 0..100

        // Timeout/kill/error floors already handled earlier via TIMEOUT/KILL paths
        return round(max(0, min(100, $final_score)), 2);
    }

    /**
     * Calculate performance score based on operations per second
     *
     * @param float $ops_per_second Operations per second
     * @param array $thresholds Performance thresholds
     * @return float Base performance score
     */
    private static function calculate_performance_score($ops_per_second, $thresholds) {
        if ($ops_per_second >= $thresholds['excellent']) {
            return 100;
        } elseif ($ops_per_second >= $thresholds['good']) {
            // Linear interpolation between good and excellent
            $range = $thresholds['excellent'] - $thresholds['good'];
            $position = $ops_per_second - $thresholds['good'];
            return 80 + (20 * ($position / $range));
        } elseif ($ops_per_second >= $thresholds['average']) {
            // Linear interpolation between average and good
            $range = $thresholds['good'] - $thresholds['average'];
            $position = $ops_per_second - $thresholds['average'];
            return 60 + (20 * ($position / $range));
        } elseif ($ops_per_second >= $thresholds['poor']) {
            // Linear interpolation between poor and average
            $range = $thresholds['average'] - $thresholds['poor'];
            $position = $ops_per_second - $thresholds['poor'];
            return 40 + (20 * ($position / $range));
        } else {
            // Linear interpolation between critical and poor
            $range = $thresholds['poor'] - $thresholds['critical'];
            if ($range > 0) {
                $position = $ops_per_second - $thresholds['critical'];
                return 20 + (20 * ($position / $range));
            }
            return 20; // Minimum score
        }
    }

    /**
     * Calculate efficiency factor based on test type
     *
     * @param string $test_name Test identifier
     * @param array $result Test results
     * @return float Efficiency factor (0.8 to 1.2)
     */
    private static function calculate_efficiency_factor($test_name, $result) {
        switch ($test_name) {
            case 'database_concurrency':
                $efficiency = isset($result['concurrent_efficiency']) ? $result['concurrent_efficiency'] : 0;
                break;
            case 'http_concurrency':
                $efficiency = isset($result['concurrent_efficiency']) ? $result['concurrent_efficiency'] : 0;
                break;
            case 'memory_concurrency':
                $efficiency = isset($result['memory_efficiency']) ? $result['memory_efficiency'] : 0;
                break;
            case 'file_concurrency':
                $efficiency = isset($result['file_efficiency']) ? $result['file_efficiency'] : 0;
                break;
            default:
                $efficiency = 100;
        }
        
        // Convert efficiency percentage to factor (80% = 0.8x, 100% = 1.0x, 120% = 1.2x)
        return max(0.8, min(1.2, $efficiency / 100));
    }

    /**
     * Calculate success factor based on successful operations
     *
     * @param array $result Test results
     * @return float Success factor (0.5 to 1.0)
     */
    private static function calculate_success_factor($result) {
        $total_operations = 0;
        $successful_operations = 0;
        
        // Determine total and successful operations based on test type
        if (isset($result['operations_completed'])) {
            $total_operations = $result['operations_completed'];
        }
        
        if (isset($result['successful_requests'])) {
            $successful_operations = $result['successful_requests'];
        } elseif (isset($result['successful_allocations'])) {
            $successful_operations = $result['successful_allocations'];
        } elseif (isset($result['successful_operations'])) {
            $successful_operations = $result['successful_operations'];
        }
        
        if ($total_operations <= 0) {
            return 0.5; // Minimum factor if no operations
        }
        
        $success_rate = $successful_operations / $total_operations;
        
        // Scale success rate to factor (50% success = 0.5x, 100% success = 1.0x)
        return max(0.5, $success_rate);
    }

    /**
     * Calculate error penalty
     *
     * @param array $result Test results
     * @return float Error penalty points (0 to 30)
     */
    private static function calculate_error_penalty($result) {
        $error_count = 0;
        
        if (isset($result['error_count'])) {
            $error_count = $result['error_count'];
        } elseif (isset($result['failed_requests'])) {
            $error_count = $result['failed_requests'];
        } elseif (isset($result['failed_allocations'])) {
            $error_count = $result['failed_allocations'];
        } elseif (isset($result['failed_operations'])) {
            $error_count = $result['failed_operations'];
        }
        
        // Each error deducts 2 points, max 30 points penalty
        return min(30, $error_count * 2);
    }

    /**
     * Calculate overall concurrency category score
     *
     * @param array $sub_test_results Array of sub-test results
     * @return array Category score data
     */
    public static function calculate_category_score($sub_test_results) {
        $total_score = 0;
        $sub_scores = array();
        $tests_completed = 0;
        $tests_failed = 0;

        // Calculate each sub-test score
        foreach (self::SUB_TEST_WEIGHTS as $test_name => $weight) {
            if (!isset($sub_test_results[$test_name])) {
                // Test was disabled or not run
                continue;
            }

            $sub_score = self::calculate_sub_test_score($test_name, $sub_test_results[$test_name]);
            $weighted_score = $sub_score * $weight;
            
            $sub_scores[$test_name] = array(
                'raw_score' => $sub_score,
                'weighted_score' => $weighted_score,
                'weight' => $weight,
                'status' => $sub_test_results[$test_name]['status'],
                'score_factors' => isset($sub_test_results[$test_name]['performance_interpretation']['score_factors'])
                    ? $sub_test_results[$test_name]['performance_interpretation']['score_factors']
                    : ''
            );

            $total_score += $weighted_score;
            
            if ($sub_test_results[$test_name]['status'] === 'completed') {
                $tests_completed++;
            } else {
                $tests_failed++;
            }
        }

        // Normalize score if not all tests were run
        $total_weight = array_sum(array_column($sub_scores, 'weight'));
        if ($total_weight > 0 && $total_weight < 1.0) {
            $total_score = $total_score / $total_weight;
        }

        // Determine rating
        $rating = self::get_rating($total_score);

        return array(
            'score' => round($total_score, 2),
            'rating' => $rating,
            'sub_scores' => $sub_scores,
            'tests_completed' => $tests_completed,
            'tests_failed' => $tests_failed,
            'category_weight' => self::CATEGORY_WEIGHT,
            'weighted_score' => round($total_score * self::CATEGORY_WEIGHT, 2),
            'interpretation' => self::get_interpretation($total_score)
        );
    }

    /**
     * Get performance rating based on score
     *
     * @param float $score Score from 0 to 100
     * @return string Rating label
     */
    private static function get_rating($score) {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 70) {
            return 'good';
        } elseif ($score >= 50) {
            return 'fair';
        } elseif ($score >= 30) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Get interpretation text for a score
     *
     * @param float $score Category score
     * @return string Interpretation text
     */
    public static function get_interpretation($score) {
        if ($score >= 90) {
            return __('Excellent concurrency handling! Your hosting performs exceptionally well under concurrent load with multiple users, database operations, and file activities running simultaneously.', 'divewp-boost-site-performance');
        } elseif ($score >= 70) {
            return __('Good concurrency performance. Your hosting handles multiple simultaneous operations well and should provide good user experience during traffic spikes.', 'divewp-boost-site-performance');
        } elseif ($score >= 50) {
            return __('Average concurrency handling. Suitable for moderate traffic but may struggle during peak loads or with many concurrent users.', 'divewp-boost-site-performance');
        } elseif ($score >= 30) {
            return __('Below average concurrency performance. Your hosting may struggle with multiple users accessing your site simultaneously. Consider optimization or upgrade.', 'divewp-boost-site-performance');
        } else {
            return __('Poor concurrency handling. Significant limitations in handling multiple simultaneous operations. Upgrade strongly recommended for production sites.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get recommendations based on sub-test results
     *
     * @param array $sub_scores Sub-test scores
     * @return array Recommendations
     */
    public static function get_recommendations($sub_scores) {
        $recommendations = array();

        foreach ($sub_scores as $test_name => $score_data) {
            if ($score_data['raw_score'] < 60) {
                switch ($test_name) {
                    case 'database_concurrency':
                        $recommendations[] = __('Database concurrency is poor. Consider implementing database connection pooling, query optimization, and potentially upgrading your database plan.', 'divewp-boost-site-performance');
                        break;
                    case 'http_concurrency':
                        $recommendations[] = __('HTTP concurrency needs improvement. Consider implementing caching, CDN, and optimizing server configuration for concurrent connections.', 'divewp-boost-site-performance');
                        break;
                    case 'memory_concurrency':
                        $recommendations[] = __('Memory handling under load is suboptimal. Consider increasing memory limits, optimizing code for memory efficiency, or upgrading hosting plan.', 'divewp-boost-site-performance');
                        break;
                    case 'file_concurrency':
                        $recommendations[] = __('File I/O performance under load is poor. Consider implementing file caching, optimizing file operations, or using SSD storage.', 'divewp-boost-site-performance');
                        break;
                }
            }

            if ($score_data['status'] === 'timeout' || $score_data['status'] === 'killed') {
                $recommendations[] = sprintf(
                    // translators: %1$s is the name of the test that was terminated (e.g., "database concurrency", "http concurrency", "memory concurrency")
                    __('The %1$s test was terminated by your hosting provider. This indicates severe resource limitations that will affect real-world performance.', 'divewp-boost-site-performance'),
                    str_replace('_', ' ', $test_name)
                );
            }
        }

        // Overall recommendations based on category performance
        if (count($recommendations) >= 3) {
            $recommendations[] = __('Multiple concurrency issues detected. Consider upgrading to a higher-tier hosting plan with better resource allocation and performance guarantees.', 'divewp-boost-site-performance');
        }

        return $recommendations;
    }

    /**
     * Get business impact analysis based on scores
     *
     * @param array $sub_scores Sub-test scores
     * @return array Business impact analysis
     */
    public static function get_business_impact($sub_scores) {
        $impact = array();
        
        $avg_score = array_sum(array_column($sub_scores, 'raw_score')) / count($sub_scores);
        
        if ($avg_score < 60) {
            $impact[] = __('User Experience: Visitors may experience slow loading times during peak traffic periods.', 'divewp-boost-site-performance');
            $impact[] = __('E-commerce Impact: Checkout processes and product browsing may be sluggish with multiple users.', 'divewp-boost-site-performance');
            $impact[] = __('SEO Impact: Poor concurrency performance can negatively affect search engine rankings.', 'divewp-boost-site-performance');
        }
        
        if (isset($sub_scores['database_concurrency']) && $sub_scores['database_concurrency']['raw_score'] < 50) {
            $impact[] = __('Database Issues: Multiple users may experience conflicts when accessing or modifying content simultaneously.', 'divewp-boost-site-performance');
        }
        
        if (isset($sub_scores['http_concurrency']) && $sub_scores['http_concurrency']['raw_score'] < 50) {
            $impact[] = __('Traffic Handling: Your site may become unavailable or very slow during traffic spikes or viral content.', 'divewp-boost-site-performance');
        }
        
        return $impact;
    }

    /**
     * Get performance interpretation for individual sub-test
     *
     * @param string $test_name Test identifier
     * @param array $result Test result data
     * @return array Performance interpretation data
     */
    public static function get_sub_test_performance_interpretation($test_name, $result) {
        $interpretation = array(
            'rating' => 'unknown',
            'rating_label' => __('Unknown', 'divewp-boost-site-performance'),
            'performance_context' => '',
            'explanation' => '',
            'hosting_quality' => ''
        );

        // Check for timeout or error states first
        if (!empty($result['test_status'])) {
            switch ($result['test_status']) {
                case 'timeout':
                    $interpretation['rating'] = 'timeout';
                    $interpretation['rating_label'] = __('Timed Out', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = self::get_timeout_explanation($test_name, $result);
                    $interpretation['hosting_quality'] = __('Server resource limitations detected', 'divewp-boost-site-performance');
                    // Add score factors even for timeout results
                    $interpretation['score_factors'] = __('Test timed out - unable to complete performance analysis', 'divewp-boost-site-performance');
                    return $interpretation;
                    
                case 'partial':
                    // Calculate ops/sec even for partial results to show performance pill
                    $ops_per_second = 0;
                    $total_time = isset($result['total_time']) ? $result['total_time'] : 0;
                    $operations_completed = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
                    
                    if ($total_time > 0 && $operations_completed > 0) {
                        $ops_per_second = $operations_completed / $total_time;
                    }

                    // Map partial to standard rating based on computed score context
                    $interpretation['rating'] = 'poor';
                    $interpretation['rating_label'] = __('Poor', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = __('Performance degraded under concurrent load.', 'divewp-boost-site-performance');
                    $interpretation['hosting_quality'] = __('Performance degradation under load', 'divewp-boost-site-performance');
                    $interpretation['performance_context'] = self::get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time);
                    // Add score factors even for partial results
                    $interpretation['score_factors'] = self::get_concurrency_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, array());
                    return $interpretation;
                    
                case 'error':
                    $interpretation['rating'] = 'error';
                    $interpretation['rating_label'] = __('Error', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = __('Test failed to complete due to system errors.', 'divewp-boost-site-performance');
                    $interpretation['hosting_quality'] = __('System instability detected', 'divewp-boost-site-performance');
                    // Add score factors even for error results
                    $interpretation['score_factors'] = __('Test failed - unable to calculate performance factors', 'divewp-boost-site-performance');
                    return $interpretation;
            }
        }

        // Calculate operations per second for performance analysis
        $ops_per_second = 0;
        $total_time = isset($result['total_time']) ? $result['total_time'] : 0;
        $operations_completed = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
        
        if ($total_time > 0 && $operations_completed > 0) {
            $ops_per_second = $operations_completed / $total_time;
        }

        // Get performance rating and context
        $thresholds = self::PERFORMANCE_THRESHOLDS[$test_name] ?? array();
        $performance_rating = self::get_performance_rating($ops_per_second, $thresholds);

        // Add simple concurrency factors explanation for score breakdown
        $interpretation['score_factors'] = self::get_concurrency_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds);

        $interpretation['rating'] = $performance_rating;
        $interpretation['rating_label'] = self::get_rating_label($performance_rating);
        $interpretation['performance_context'] = self::get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time);
        $interpretation['explanation'] = self::get_performance_explanation($test_name, $performance_rating, $ops_per_second);
        $interpretation['hosting_quality'] = self::get_hosting_quality_assessment($test_name, $performance_rating);

        return $interpretation;
    }

    /**
     * Get detailed component breakdown for concurrency analysis
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @param array $thresholds Performance thresholds
     * @param array $result Full test result data
     * @return array Component breakdown data
     */
    private static function get_concurrency_component_breakdown($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds, $result) {
        $breakdown = array();

        // Concurrent Performance component
        $formatted_ops = number_format($ops_per_second, 1);
        $speed_rating = self::get_performance_rating($ops_per_second, $thresholds);
        $speed_threshold_text = '';

        if ($speed_rating === 'critical') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted performance threshold value (e.g., "20.0")
                __('below poor threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['poor'], 1)
            );
        } elseif ($speed_rating === 'poor') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted performance threshold value (e.g., "40.0")
                __('below average threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['average'], 1)
            );
        } elseif ($speed_rating === 'average') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted performance threshold value (e.g., "60.0")
                __('below good threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['good'], 1)
            );
        } elseif ($speed_rating === 'good') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted performance threshold value (e.g., "80.0")
                __('below excellent threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['excellent'], 1)
            );
        } else {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted performance threshold value (e.g., "80.0")
                __('above excellent threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['excellent'], 1)
            );
        }

        $breakdown['performance'] = array(
            'label' => __('Concurrent Performance', 'divewp-boost-site-performance'),
            'value' => sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "123.4")
                __('%1$s ops/sec under load', 'divewp-boost-site-performance'),
                $formatted_ops
            ),
            'points' => round($ops_per_second, 1),
            'max_points' => $thresholds['excellent'] ?? 100,
            'analysis' => sprintf(
                // translators: %1$s is the formatted operations per second, %2$s is the threshold comparison text
                __('%1$s ops/sec under concurrent load (%2$s)', 'divewp-boost-site-performance'),
                $formatted_ops, $speed_threshold_text
            ),
            'rating' => $speed_rating
        );

        // Concurrent Load component
        $concurrent_users = isset($result['concurrent_users']) ? $result['concurrent_users'] : 10; // Default assumption
        $load_rating = $concurrent_users >= 50 ? 'excellent' : ($concurrent_users >= 25 ? 'good' : ($concurrent_users >= 10 ? 'average' : 'poor'));

        $breakdown['concurrency'] = array(
            'label' => __('Concurrent Load', 'divewp-boost-site-performance'),
            'value' => sprintf(
                // translators: %1$d is the number of simultaneous connections/users tested
                __('%1$d simultaneous connections', 'divewp-boost-site-performance'),
                $concurrent_users
            ),
            'points' => $concurrent_users,
            'max_points' => 100, // Arbitrary scale
            'analysis' => sprintf(
                // translators: %1$d is the number of concurrent connections/users being tested
                __('Testing with %1$d concurrent connections/users', 'divewp-boost-site-performance'),
                $concurrent_users
            ),
            'rating' => $load_rating
        );

        // Execution Time component
        $breakdown['execution_time'] = array(
            'label' => __('Execution Time', 'divewp-boost-site-performance'),
            'value' => sprintf(
                // translators: %1$s is the formatted execution time in seconds (e.g., "12.34")
                __('%1$s seconds', 'divewp-boost-site-performance'),
                number_format($total_time, 2)
            ),
            'points' => round($total_time, 2),
            'max_points' => 30,
            'analysis' => sprintf(
                // translators: %1$s is the formatted total test duration in seconds
                __('Total test duration under concurrent load: %1$s seconds', 'divewp-boost-site-performance'),
                number_format($total_time, 2)
            ),
            'rating' => $total_time <= 5 ? 'excellent' : ($total_time <= 10 ? 'good' : ($total_time <= 20 ? 'average' : 'poor'))
        );

        return $breakdown;
    }

    /**
     * Get simple concurrency factors explanation for score breakdown
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @param array $thresholds Performance thresholds
     * @return string Simple explanation text
     */
    private static function get_concurrency_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds) {
        $explanation = '';

        // Concurrent performance factor
        $performance_rating = self::get_performance_rating($ops_per_second, $thresholds);
        $formatted_ops = number_format($ops_per_second, 1);

        if ($performance_rating === 'excellent') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "123.4")
                __('Concurrent performance: %1$s ops/sec (excellent)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } elseif ($performance_rating === 'good') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "123.4")
                __('Concurrent performance: %1$s ops/sec (good)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } elseif ($performance_rating === 'average') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "123.4")
                __('Concurrent performance: %1$s ops/sec (moderate)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } else {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "123.4")
                __('Concurrent performance: %1$s ops/sec (slow)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        }

        // Load level factor (simplified - we don't have exact concurrent users data)
        $explanation .= __(' + Under concurrent load', 'divewp-boost-site-performance');

        // Execution time factor
        if ($total_time <= 5) {
            $explanation .= __(' + Fast concurrent execution', 'divewp-boost-site-performance');
        } elseif ($total_time <= 10) {
            $explanation .= __(' + Moderate concurrent execution time', 'divewp-boost-site-performance');
        } elseif ($total_time <= 20) {
            $explanation .= __(' + Slow concurrent execution', 'divewp-boost-site-performance');
        } else {
            $explanation .= __(' + Very slow concurrent execution', 'divewp-boost-site-performance');
        }

        // Concurrency tests are scored based on performance under load
        $explanation .= __(' = Scored based on concurrent load handling', 'divewp-boost-site-performance');

        return $explanation;
    }

    /**
     * Get performance rating based on operations per second
     *
     * @param float $ops_per_second Operations per second
     * @param array $thresholds Performance thresholds
     * @return string Performance rating
     */
    private static function get_performance_rating($ops_per_second, $thresholds) {
        if (empty($thresholds)) {
            return 'unknown';
        }

        if ($ops_per_second >= $thresholds['excellent']) {
            return 'excellent';
        } elseif ($ops_per_second >= $thresholds['good']) {
            return 'good';
        } elseif ($ops_per_second >= $thresholds['average']) {
            return 'average';
        } elseif ($ops_per_second >= $thresholds['poor']) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Get human-readable rating label
     *
     * @param string $rating Performance rating
     * @return string Human-readable label
     */
    private static function get_rating_label($rating) {
        $labels = array(
            'excellent' => __('Excellent', 'divewp-boost-site-performance'),
            'good' => __('Good', 'divewp-boost-site-performance'),
            'fair' => __('Fair', 'divewp-boost-site-performance'),
            'poor' => __('Poor', 'divewp-boost-site-performance'),
            'critical' => __('Critical', 'divewp-boost-site-performance'),
            'timeout' => __('Timed Out', 'divewp-boost-site-performance'),
            'partial' => __('Partial', 'divewp-boost-site-performance'),
            'error' => __('Error', 'divewp-boost-site-performance'),
            'unknown' => __('Unknown', 'divewp-boost-site-performance')
        );

        return $labels[$rating] ?? $labels['unknown'];
    }

    /**
     * Get performance context string (e.g., "198 ops/sec")
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @return string Performance context
     */
    private static function get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time) {
        switch ($test_name) {
            case 'database_concurrency':
                return sprintf(
                    // translators: %1$d is the number of database operations performed per second (e.g., "150", "89")
                    __('%1$d ops/sec', 'divewp-boost-site-performance'),
                    round($ops_per_second)
                );

            case 'http_concurrency':
                return sprintf(
                    // translators: %1$d is the number of HTTP requests processed per second (e.g., "12", "5")
                    __('%1$d requests/sec', 'divewp-boost-site-performance'),
                    round($ops_per_second)
                );

            case 'memory_concurrency':
                return sprintf(
                    // translators: %1$d is the number of memory allocations performed per second (e.g., "45", "23")
                    __('%1$d allocations/sec', 'divewp-boost-site-performance'),
                    round($ops_per_second)
                );

            case 'file_concurrency':
                return sprintf(
                    // translators: %1$d is the number of file operations performed per second (e.g., "78", "34")
                    __('%1$d file ops/sec', 'divewp-boost-site-performance'),
                    round($ops_per_second)
                );

            default:
                return sprintf(
                    // translators: %1$d is the number of operations performed per second (e.g., "67", "45")
                    __('%1$d ops/sec', 'divewp-boost-site-performance'),
                    round($ops_per_second)
                );
        }
    }

    /**
     * Get detailed performance explanation
     *
     * @param string $test_name Test identifier
     * @param string $rating Performance rating
     * @param float $ops_per_second Operations per second
     * @return string Detailed explanation
     */
    private static function get_performance_explanation($test_name, $rating, $ops_per_second) {
        switch ($test_name) {
            case 'database_concurrency':
                return self::get_database_explanation($rating, $ops_per_second);
                
            case 'http_concurrency':
                return self::get_http_explanation($rating, $ops_per_second);
                
            case 'memory_concurrency':
                return self::get_memory_explanation($rating, $ops_per_second);
                
            case 'file_concurrency':
                return self::get_file_explanation($rating, $ops_per_second);
                
            default:
                return __('Performance analysis not available for this test type.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get database concurrency explanation
     */
    private static function get_database_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding database performance! Your hosting handles concurrent database operations exceptionally well, indicating optimized MySQL configuration and likely SSD storage.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good database concurrency. Your hosting handles multiple database operations well, suitable for most WordPress sites with moderate traffic.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average database performance under load. May experience slowdowns during peak traffic or with heavy database operations.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor database concurrency performance. Multiple users may experience slow page loads and database timeouts during busy periods.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical database performance issues. Concurrent database operations are severely limited, affecting user experience and site stability.', 'divewp-boost-site-performance');
                
            default:
                return __('Database performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get HTTP concurrency explanation
     */
    private static function get_http_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent HTTP handling! Your server processes concurrent requests very efficiently, providing fast response times even under load.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good HTTP concurrency performance. Your server handles multiple simultaneous requests well, suitable for most traffic patterns.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average HTTP performance under concurrent load. May experience slowdowns during traffic spikes or viral content.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor HTTP concurrency handling. Users may experience slow page loads or connection timeouts when multiple visitors access your site.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical HTTP performance issues. Your server struggles with concurrent connections, leading to poor user experience and potential downtime.', 'divewp-boost-site-performance');
                
            default:
                return __('HTTP performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get memory concurrency explanation
     */
    private static function get_memory_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent memory management under load! Your hosting efficiently handles concurrent memory operations with minimal performance impact.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good memory handling during concurrent operations. Your hosting manages memory allocation well under typical load conditions.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average memory performance under concurrent load. May experience memory pressure during peak usage or with memory-intensive plugins.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor memory management under load. Concurrent operations may trigger memory exhaustion errors or significant performance degradation.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical memory performance issues. Severe memory limitations affecting site stability and concurrent user handling capability.', 'divewp-boost-site-performance');
                
            default:
                return __('Memory performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get file concurrency explanation
     */
    private static function get_file_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent file I/O performance! Your hosting handles concurrent file operations very efficiently, indicating fast storage (likely SSD).', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good file system performance under concurrent load. Your hosting handles multiple file operations well for typical WordPress usage.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average file I/O performance during concurrent operations. May experience slower file access during high-traffic periods.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor file system performance under load. Concurrent file operations may cause significant delays in content delivery and uploads.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical file I/O performance issues. Severe limitations in concurrent file handling affecting content management and user uploads.', 'divewp-boost-site-performance');
                
            default:
                return __('File performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get hosting quality assessment
     *
     * @param string $test_name Test identifier
     * @param string $rating Performance rating
     * @return string Hosting quality assessment
     */
    private static function get_hosting_quality_assessment($test_name, $rating) {
        $quality_indicators = array(
            'excellent' => array(
                'database_concurrency' => __('Premium database configuration with SSD storage', 'divewp-boost-site-performance'),
                'http_concurrency' => __('High-performance web server with optimized configuration', 'divewp-boost-site-performance'),
                'memory_concurrency' => __('Generous memory allocation with efficient management', 'divewp-boost-site-performance'),
                'file_concurrency' => __('Fast SSD storage with optimized I/O handling', 'divewp-boost-site-performance')
            ),
            'good' => array(
                'database_concurrency' => __('Well-configured database with good resource allocation', 'divewp-boost-site-performance'),
                'http_concurrency' => __('Properly configured web server for concurrent handling', 'divewp-boost-site-performance'),
                'memory_concurrency' => __('Adequate memory allocation for concurrent operations', 'divewp-boost-site-performance'),
                'file_concurrency' => __('Good storage performance, possibly SSD', 'divewp-boost-site-performance')
            ),
            'average' => array(
                'database_concurrency' => __('Standard database configuration with basic optimization', 'divewp-boost-site-performance'),
                'http_concurrency' => __('Basic web server setup with standard configuration', 'divewp-boost-site-performance'),
                'memory_concurrency' => __('Limited memory allocation affecting concurrent performance', 'divewp-boost-site-performance'),
                'file_concurrency' => __('Standard storage, possibly hybrid SSD/HDD', 'divewp-boost-site-performance')
            ),
            'poor' => array(
                'database_concurrency' => __('Poorly optimized database or insufficient resources', 'divewp-boost-site-performance'),
                'http_concurrency' => __('Web server limitations affecting concurrent request handling', 'divewp-boost-site-performance'),
                'memory_concurrency' => __('Insufficient memory allocation for concurrent operations', 'divewp-boost-site-performance'),
                'file_concurrency' => __('Slow storage system, likely traditional HDD', 'divewp-boost-site-performance')
            ),
            'critical' => array(
                'database_concurrency' => __('Severe database limitations or resource restrictions', 'divewp-boost-site-performance'),
                'http_concurrency' => __('Critical web server performance issues', 'divewp-boost-site-performance'),
                'memory_concurrency' => __('Critical memory limitations affecting site stability', 'divewp-boost-site-performance'),
                'file_concurrency' => __('Critical storage performance issues', 'divewp-boost-site-performance')
            )
        );

        return $quality_indicators[$rating][$test_name] ?? __('Hosting quality assessment not available', 'divewp-boost-site-performance');
    }

    /**
     * Get timeout explanation
     *
     * @param string $test_name Test identifier
     * @param array $result Test result data
     * @return string Timeout explanation
     */
    private static function get_timeout_explanation($test_name, $result) {
        $completed = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
        $total = isset($result['total_operations']) ? $result['total_operations'] : 0;
        
        $base_message = sprintf(
            // translators: %1$d is the number of operations completed before timeout, %2$d is the total number of operations planned (e.g., "45 of 100")
            __('Test timed out after completing %1$d of %2$d operations. ', 'divewp-boost-site-performance'),
            $completed,
            $total
        );

        switch ($test_name) {
            case 'database_concurrency':
                return $base_message . __('This indicates database performance issues or resource limitations that will significantly impact user experience during peak traffic.', 'divewp-boost-site-performance');
                
            case 'http_concurrency':
                return $base_message . __('This suggests server overload or network connectivity issues that will cause slow page loads and potential downtime during traffic spikes.', 'divewp-boost-site-performance');
                
            case 'memory_concurrency':
                return $base_message . __('This indicates memory allocation problems that will cause errors and crashes when multiple users access your site simultaneously.', 'divewp-boost-site-performance');
                
            case 'file_concurrency':
                return $base_message . __('This suggests slow file system performance that will affect content delivery, uploads, and overall site responsiveness.', 'divewp-boost-site-performance');
                
            default:
                return $base_message . __('This indicates performance limitations that may affect your site\'s ability to handle multiple users effectively.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get partial completion explanation
     *
     * @param string $test_name Test identifier
     * @param array $result Test result data
     * @return string Partial completion explanation
     */
    private static function get_partial_explanation($test_name, $result) {
        return __('Performance degraded under concurrent load.', 'divewp-boost-site-performance');
    }

    /**
     * Get baseline comparison data for all concurrency tests
     *
     * @return array Baseline comparison data
     */
    public static function get_baseline_comparison_data() {
        return array(
            'database_concurrency' => array(
                'test_type' => __('Database Operations', 'divewp-boost-site-performance'),
                'unit' => __('495 operations', 'divewp-boost-site-performance'),
                'thresholds' => array(
                    'excellent' => __('< 6s', 'divewp-boost-site-performance'),
                    'good' => __('6-12s', 'divewp-boost-site-performance'),
                    'poor' => __('> 25s', 'divewp-boost-site-performance')
                ),
                'indicators' => array(
                    'excellent' => __('SSD storage, optimized MySQL', 'divewp-boost-site-performance'),
                    'good' => __('Good database configuration', 'divewp-boost-site-performance'),
                    'poor' => __('Resource limitations, HDD storage', 'divewp-boost-site-performance')
                )
            ),
            'http_concurrency' => array(
                'test_type' => __('HTTP Requests', 'divewp-boost-site-performance'),
                'unit' => __('8 requests', 'divewp-boost-site-performance'),
                'thresholds' => array(
                    'excellent' => __('< 2s', 'divewp-boost-site-performance'),
                    'good' => __('2-4s', 'divewp-boost-site-performance'),
                    'poor' => __('> 8s', 'divewp-boost-site-performance')
                ),
                'indicators' => array(
                    'excellent' => __('High-performance web server', 'divewp-boost-site-performance'),
                    'good' => __('Well-configured hosting', 'divewp-boost-site-performance'),
                    'poor' => __('Server overload, poor connectivity', 'divewp-boost-site-performance')
                )
            ),
            'memory_concurrency' => array(
                'test_type' => __('Memory Operations', 'divewp-boost-site-performance'),
                'unit' => __('96 processes', 'divewp-boost-site-performance'),
                'thresholds' => array(
                    'excellent' => __('< 2.5s', 'divewp-boost-site-performance'),
                    'good' => __('2.5-5s', 'divewp-boost-site-performance'),
                    'poor' => __('> 10s', 'divewp-boost-site-performance')
                ),
                'indicators' => array(
                    'excellent' => __('Generous memory allocation', 'divewp-boost-site-performance'),
                    'good' => __('Adequate memory limits', 'divewp-boost-site-performance'),
                    'poor' => __('Memory limitations, shared hosting', 'divewp-boost-site-performance')
                )
            ),
            'file_concurrency' => array(
                'test_type' => __('File Operations', 'divewp-boost-site-performance'),
                'unit' => __('320 operations', 'divewp-boost-site-performance'),
                'thresholds' => array(
                    'excellent' => __('< 5s', 'divewp-boost-site-performance'),
                    'good' => __('5-15s', 'divewp-boost-site-performance'),
                    'poor' => __('> 30s', 'divewp-boost-site-performance')
                ),
                'indicators' => array(
                    'excellent' => __('Fast SSD storage', 'divewp-boost-site-performance'),
                    'good' => __('Reasonable I/O performance', 'divewp-boost-site-performance'),
                    'poor' => __('Slow HDD storage, I/O limitations', 'divewp-boost-site-performance')
                )
            )
        );
    }

    /**
     * Get score impact analysis for category
     *
     * @param array $sub_scores Sub-test scores
     * @return array Score impact analysis
     */
    public static function get_score_impact_analysis($sub_scores) {
        $impact_analysis = array();
        $total_weighted_score = 0;
        $positive_contributions = array();
        $negative_contributions = array();

        foreach ($sub_scores as $test_name => $score_data) {
            $weight = self::SUB_TEST_WEIGHTS[$test_name] ?? 0;
            $weighted_score = $score_data['weighted_score'];
            $total_weighted_score += $weighted_score;

            $contribution = array(
                'test_name' => str_replace('_', ' ', ucwords($test_name)),
                'raw_score' => $score_data['raw_score'],
                'weight' => $weight,
                'weighted_score' => $weighted_score,
                'contribution_points' => round($weighted_score, 1),
                'status' => $score_data['status'],
                'score_factors' => $score_data['score_factors'] ?? ''
            );

            // Determine if this is a positive or negative contribution
            if ($score_data['raw_score'] >= 75) {
                $contribution['impact_type'] = 'positive';
                $contribution['impact_reason'] = __('Excellent performance', 'divewp-boost-site-performance');
                $positive_contributions[] = $contribution;
            } elseif ($score_data['raw_score'] >= 60) {
                $contribution['impact_type'] = 'neutral';
                $contribution['impact_reason'] = __('Good performance', 'divewp-boost-site-performance');
                $positive_contributions[] = $contribution;
            } else {
                $contribution['impact_type'] = 'negative';
                if ($score_data['status'] === 'timeout') {
                    $contribution['impact_reason'] = __('Timeout penalty', 'divewp-boost-site-performance');
                } elseif ($score_data['status'] === 'partial') {
                    $contribution['impact_reason'] = __('Partial completion', 'divewp-boost-site-performance');
                } elseif ($score_data['status'] === 'error') {
                    $contribution['impact_reason'] = __('Test error', 'divewp-boost-site-performance');
                } else {
                    $contribution['impact_reason'] = __('Poor performance', 'divewp-boost-site-performance');
                }
                $negative_contributions[] = $contribution;
            }
        }

        return array(
            'total_score' => round($total_weighted_score, 2),
            'positive_contributions' => $positive_contributions,
            'negative_contributions' => $negative_contributions,
            'improvement_potential' => self::calculate_improvement_potential($negative_contributions)
        );
    }

    /**
     * Calculate improvement potential from negative contributions
     *
     * @param array $negative_contributions Negative score contributions
     * @return array Improvement potential analysis
     */
    private static function calculate_improvement_potential($negative_contributions) {
        $potential_improvements = array();
        $total_potential_gain = 0;

        foreach ($negative_contributions as $contribution) {
            // Calculate potential gain if this test performed at "good" level (75 points)
            $current_weighted = $contribution['weighted_score'];
            $potential_weighted = 75 * $contribution['weight'];
            $potential_gain = $potential_weighted - $current_weighted;
            
            $total_potential_gain += $potential_gain;

            $potential_improvements[] = array(
                'test_name' => $contribution['test_name'],
                'current_score' => $contribution['raw_score'],
                'potential_score' => 75,
                'potential_gain' => round($potential_gain, 1),
                'priority' => $contribution['weight'] > 0.25 ? 'high' : 'medium'
            );
        }

        return array(
            'total_potential_gain' => round($total_potential_gain, 1),
            'individual_improvements' => $potential_improvements
        );
    }
} 