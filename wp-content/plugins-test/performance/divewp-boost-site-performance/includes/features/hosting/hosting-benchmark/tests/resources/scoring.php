<?php
/**
 * Resources Tests Scoring Configuration
 *
 * Defines scoring logic, weights, and penalty calculations for resources tests.
 * Replicates exact POC specifications with enhanced UX features.
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
 * Resources Tests Scoring Class
 */
class DiveWP_Benchmark_Resources_Scoring {

    /**
     * Category weight in overall benchmark score
     * Resources tests contribute 25% to the total benchmark score
     */
    const CATEGORY_WEIGHT = 0.25;

    /**
     * Sub-test weights within the resources category (POC exact specification)
     * Total must equal 1.0 (100%)
     */
    const SUB_TEST_WEIGHTS = array(
        'cpu_tests'     => 0.30,  // 30% - Primary performance indicator
        'wordpress_tests' => 0.20,  // 20% - Core functionality performance
        'memory_tests'  => 0.15,  // 15% - Resource allocation efficiency
        'file_io_tests' => 0.25,  // 25% - Storage performance critical
        'network_tests' => 0.10   // 10% - Connectivity bonus
    );

    /**
     * Timeout/Kill penalties
     * Percentage of points deducted for timeout or process kill
     */
    const TIMEOUT_PENALTY = 0.6;  // 60% penalty
    const KILL_PENALTY = 0.8;     // 80% penalty

    /**
     * Performance thresholds for operations per second
     * Used to calculate scores based on performance levels (following Concurrency pattern)
     */
    const PERFORMANCE_THRESHOLDS = array(
        'cpu_tests' => array(
            'excellent' => 4,     // 4+ complex CPU operations/sec = 100 points (premium hosting)
            'good'      => 2.5,   // 2.5+ operations/sec = 80 points (good hosting)
            'average'   => 1.5,   // 1.5+ operations/sec = 60 points (average hosting)
            'poor'      => 0.8,   // 0.8+ operations/sec = 40 points (poor hosting)
            'critical'  => 0      // Below 0.8 operations/sec = 20 points (very poor)
        ),
        'wordpress_tests' => array(
            'excellent' => 2,     // 2+ operations/sec = 100 points
            'good'      => 1,     // 1+ operations/sec = 80 points
            'average'   => 0.5,   // 0.5+ operations/sec = 60 points
            'poor'      => 0.25,  // 0.25+ operations/sec = 40 points
            'critical'  => 0      // Below 0.25 operations/sec = 20 points
        ),
        'memory_tests' => array(
            'excellent' => 2.5,   // 2.5+ operations/sec = 100 points
            'good'      => 1.5,   // 1.5+ operations/sec = 80 points
            'average'   => 1,     // 1+ operations/sec = 60 points
            'poor'      => 0.5,   // 0.5+ operations/sec = 40 points
            'critical'  => 0      // Below 0.5 operations/sec = 20 points
        ),
        'file_io_tests' => array(
            'excellent' => 25,    // 25+ operations/sec = 100 points
            'good'      => 15,    // 15+ operations/sec = 80 points
            'average'   => 10,    // 10+ operations/sec = 60 points
            'poor'      => 5,     // 5+ operations/sec = 40 points
            'critical'  => 0      // Below 5 operations/sec = 20 points
        ),
        'network_tests' => array(
            'excellent' => 1,     // 1+ operations/sec = 100 points
            'good'      => 0.5,   // 0.5+ operations/sec = 80 points
            'average'   => 0.3,   // 0.3+ operations/sec = 60 points
            'poor'      => 0.1,   // 0.1+ operations/sec = 40 points
            'critical'  => 0      // Below 0.1 operations/sec = 20 points
        )
    );

    /**
     * Calculate score for a single sub-test (enhanced with UX features)
     *
     * @param string $test_name Test identifier
     * @param array  $result    Test result data
     * @return float Score from 0 to 100
     */
    public static function calculate_sub_test_score($test_name, $result) {
        // Check for timeout or kill (POC method)
        if (isset($result['status'])) {
            if ($result['status'] === 'timeout') {
                return 100 * (1 - self::TIMEOUT_PENALTY);
            }
            
            if ($result['status'] === 'killed') {
                return 100 * (1 - self::KILL_PENALTY);
            }
            
            if ($result['status'] === 'error') {
                return 0; // Error status
            }
        }

        // Calculate score based on test type (POC methods)
        switch ($test_name) {
            case 'cpu_tests':
                return self::calculate_cpu_score($result);
            case 'wordpress_tests':
                return self::calculate_wordpress_score($result);
            case 'memory_tests':
                return self::calculate_memory_score($result);
            case 'file_io_tests':
                return self::calculate_file_io_score($result);
            case 'network_tests':
                return self::calculate_network_score($result);
            default:
                return 0; // Unknown test
        }
    }

    /**
     * Calculate CPU score (standardized performance rating approach)
     */
    private static function calculate_cpu_score($result) {
        $total_time = $result['total_time'] ?? 0;
        $completed_operations = $result['completed_operations'] ?? 0;
        
        if ($total_time <= 0 || $completed_operations <= 0) {
            return 100;
        }
        
        // Calculate operations per second
        $ops_per_second = $completed_operations / $total_time;
        
        // Get thresholds for this test
        $thresholds = self::PERFORMANCE_THRESHOLDS['cpu_tests'];
        
        // Calculate base score from performance thresholds (following Concurrency pattern)
        return self::calculate_performance_score($ops_per_second, $thresholds);
    }

    /**
     * Calculate WordPress score (standardized performance rating approach)
     */
    private static function calculate_wordpress_score($result) {
        $total_time = $result['total_time'] ?? 0;
        $completed_operations = $result['completed_operations'] ?? 0;
        
        if ($total_time <= 0 || $completed_operations <= 0) {
            return 100;
        }
        
        // Calculate operations per second
        $ops_per_second = $completed_operations / $total_time;
        
        // Get thresholds for this test
        $thresholds = self::PERFORMANCE_THRESHOLDS['wordpress_tests'];
        
        // Calculate base score from performance thresholds (following Concurrency pattern)
        return self::calculate_performance_score($ops_per_second, $thresholds);
    }

    /**
     * Calculate memory score (standardized performance rating approach)
     */
    private static function calculate_memory_score($result) {
        $total_time = $result['total_time'] ?? 0;
        $completed_operations = $result['completed_operations'] ?? 1; // Usually 1 for memory test
        
        if ($total_time <= 0) {
            return 100;
        }
        
        // Calculate operations per second
        $ops_per_second = $completed_operations / $total_time;
        
        // Get thresholds for this test
        $thresholds = self::PERFORMANCE_THRESHOLDS['memory_tests'];
        
        // Calculate base score from performance thresholds (following Concurrency pattern)
        return self::calculate_performance_score($ops_per_second, $thresholds);
    }

    /**
     * Calculate file I/O score (standardized performance rating approach)
     */
    private static function calculate_file_io_score($result) {
        $total_time = $result['total_time'] ?? 0;
        $completed_operations = $result['completed_operations'] ?? 0;
        
        if ($total_time <= 0 || $completed_operations <= 0) {
            return 100;
        }
        
        // Calculate operations per second
        $ops_per_second = $completed_operations / $total_time;
        
        // Get thresholds for this test
        $thresholds = self::PERFORMANCE_THRESHOLDS['file_io_tests'];
        
        // Calculate base score from performance thresholds (following Concurrency pattern)
        return self::calculate_performance_score($ops_per_second, $thresholds);
    }

    /**
     * Calculate network score (standardized performance rating approach)
     */
    private static function calculate_network_score($result) {
        $total_time = $result['total_time'] ?? 0;
        $completed_operations = $result['completed_operations'] ?? 0;
        
        if ($total_time <= 0 || $completed_operations <= 0) {
            return 100;
        }
        
        // Calculate operations per second
        $ops_per_second = $completed_operations / $total_time;
        
        // Get thresholds for this test
        $thresholds = self::PERFORMANCE_THRESHOLDS['network_tests'];
        
        // Calculate base score from performance thresholds (following Concurrency pattern)
        return self::calculate_performance_score($ops_per_second, $thresholds);
    }

    /**
     * Calculate performance score based on operations per second (copied from Concurrency pattern)
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
     * Calculate the overall resources category score
     *
     * @param array $results Test results array
     * @return array Score data with interpretation and sub_scores
     */
    public static function calculate_category_score($results) {
        if (empty($results)) {
            return array(
                'score' => 0,
                'rating' => 'poor',
                'interpretation' => __('No resources test results available', 'divewp-boost-site-performance'),
                'sub_scores' => array(),
                'tests_completed' => 0,
                'tests_failed' => 0,
                'category_weight' => self::CATEGORY_WEIGHT,
                'weighted_score' => 0
            );
        }

        $total_score = 0;
        $sub_scores = array();
        $tests_completed = 0;
        $tests_failed = 0;

        // Calculate each sub-test score
        foreach (self::SUB_TEST_WEIGHTS as $test_name => $weight) {
            if (!isset($results[$test_name])) {
                // Test was disabled or not run
                continue;
            }

            $sub_score = self::calculate_sub_test_score($test_name, $results[$test_name]);
            $weighted_score = $sub_score * $weight;
            
            $sub_scores[$test_name] = array(
                'raw_score' => $sub_score,
                'weighted_score' => $weighted_score,
                'weight' => $weight,
                'status' => isset($results[$test_name]['status']) ? $results[$test_name]['status'] : 'unknown',
                'score_factors' => isset($results[$test_name]['performance_interpretation']['score_factors'])
                    ? $results[$test_name]['performance_interpretation']['score_factors']
                    : ''
            );

            $total_score += $weighted_score;
            
            if (isset($results[$test_name]['status']) && $results[$test_name]['status'] === 'completed') {
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
            'interpretation' => self::get_score_interpretation($total_score),
            'sub_scores' => $sub_scores,
            'tests_completed' => $tests_completed,
            'tests_failed' => $tests_failed,
            'category_weight' => self::CATEGORY_WEIGHT,
            'weighted_score' => round($total_score * self::CATEGORY_WEIGHT, 2)
        );
    }

    /**
     * Get performance rating based on score (lowercase for JavaScript)
     *
     * @param float $score Score from 0 to 100
     * @return string Rating label
     */
    public static function get_rating($score) {
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
     * Get human-readable score interpretation
     *
     * @param float $score Score value
     * @return string Score interpretation
     */
    private static function get_score_interpretation($score) {
        if ($score >= 90) {
            return __('Excellent server resources - high-performance hosting', 'divewp-boost-site-performance');
        } elseif ($score >= 70) {
            return __('Good server resources - solid hosting performance', 'divewp-boost-site-performance');
        } elseif ($score >= 50) {
            return __('Fair server resources - may experience limitations under load', 'divewp-boost-site-performance');
        } elseif ($score >= 30) {
            return __('Poor server resources - upgrade recommended', 'divewp-boost-site-performance');
        } else {
            return __('Critical server resources - severe limitations detected', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get score impact analysis for enhanced UX
     *
     * @param array $sub_scores Sub-test scores with weights
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
                } elseif ($score_data['status'] === 'killed') {
                    $contribution['impact_reason'] = __('Process terminated', 'divewp-boost-site-performance');
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
            $potential_gain = (75 - $contribution['raw_score']) * $contribution['weight'];
            if ($potential_gain > 0) {
                $potential_improvements[] = array(
                    'test_name' => $contribution['test_name'],
                    'current_score' => $contribution['raw_score'],
                    'potential_gain' => round($potential_gain, 1),
                    'improvement_strategy' => self::get_improvement_strategy($contribution['test_name'])
                );
                $total_potential_gain += $potential_gain;
            }
        }

        return array(
            'total_potential_gain' => round($total_potential_gain, 1),
            'individual_improvements' => $potential_improvements
        );
    }

    /**
     * Get improvement strategy for specific test
     *
     * @param string $test_name Test name
     * @return string Improvement strategy
     */
    private static function get_improvement_strategy($test_name) {
        $strategies = array(
            'Cpu Tests' => __('Upgrade CPU cores, optimize server configuration, reduce background processes', 'divewp-boost-site-performance'),
            'Memory Tests' => __('Increase available RAM, optimize memory allocation, enable server-side caching', 'divewp-boost-site-performance'),
            'File Io Tests' => __('Upgrade to SSD storage, optimize file system, enable caching mechanisms', 'divewp-boost-site-performance'),
            'Network Tests' => __('Optimize network configuration, use CDN, upgrade hosting network infrastructure', 'divewp-boost-site-performance'),
            'Wordpress Tests' => __('Optimize WordPress configuration, update plugins/themes, enable object caching', 'divewp-boost-site-performance')
        );

        return $strategies[$test_name] ?? __('General server optimization needed', 'divewp-boost-site-performance');
    }

    /**
     * Get sub-test performance interpretation for enhanced UX
     *
     * @param string $test_name Test identifier
     * @param array $result Test result data
     * @return array Performance interpretation
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
        if (isset($result['status'])) {
            switch ($result['status']) {
                case 'timeout':
                    $interpretation['rating'] = 'timeout';
                    $interpretation['rating_label'] = __('Timed Out', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = self::get_timeout_explanation($test_name, $result);
                    $interpretation['hosting_quality'] = __('Server resource limitations detected', 'divewp-boost-site-performance');
                    // Add score factors even for timeout results
                    $interpretation['score_factors'] = __('Test timed out - unable to complete resource analysis', 'divewp-boost-site-performance');
                    return $interpretation;

                case 'error':
                    $interpretation['rating'] = 'error';
                    $interpretation['rating_label'] = __('Error', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = __('Test failed to complete due to system errors.', 'divewp-boost-site-performance');
                    $interpretation['hosting_quality'] = __('System instability detected', 'divewp-boost-site-performance');
                    // Add score factors even for error results
                    $interpretation['score_factors'] = __('Test failed - unable to calculate resource factors', 'divewp-boost-site-performance');
                    return $interpretation;
            }
        }

        // Calculate operations per second for performance analysis
        $ops_per_second = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
        $total_time = isset($result['total_time']) ? $result['total_time'] : 0;
        $operations_completed = isset($result['completed_operations']) ? $result['completed_operations'] : 0;

        // Calculate ops_per_second if not provided directly
        if ($ops_per_second == 0 && $total_time > 0 && $operations_completed > 0) {
            $ops_per_second = $operations_completed / $total_time;
        }

        // Get performance rating and context
        $thresholds = self::PERFORMANCE_THRESHOLDS[$test_name] ?? array();
        $performance_rating = self::get_performance_rating($ops_per_second, $thresholds);

        // Add simple resources factors explanation for score breakdown
        $interpretation['score_factors'] = self::get_resources_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds);

        $interpretation['rating'] = $performance_rating;
        $interpretation['rating_label'] = self::get_rating_label($performance_rating);
        $interpretation['performance_context'] = self::get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time);
        $interpretation['explanation'] = self::get_performance_explanation($test_name, $performance_rating, $ops_per_second);
        $interpretation['hosting_quality'] = self::get_hosting_quality_assessment($test_name, $performance_rating);

        return $interpretation;
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
            'killed' => __('Terminated', 'divewp-boost-site-performance'),
            'error' => __('Error', 'divewp-boost-site-performance'),
            'unknown' => __('Unknown', 'divewp-boost-site-performance')
        );

        return $labels[$rating] ?? $labels['unknown'];
    }

    /**
     * Get performance context string (e.g., "5,000 ops/sec")
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @return string Performance context
     */
    private static function get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time) {
        // DEBUG: Log performance context calculation (debug/admin only)
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            error_log("DiveWP Performance Context Debug - Test: {$test_name}, Ops/sec: {$ops_per_second}, Completed: {$operations_completed}, Time: {$total_time}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        }
        
        switch ($test_name) {
            case 'cpu_tests':
                $formatted_ops = number_format(round($ops_per_second, 1), 1);
                $result = sprintf(
                    // translators: %1$s is the formatted number of CPU operations per second (e.g., "1,500.0", "2,300.5")
                    __('%1$s CPU ops/sec', 'divewp-boost-site-performance'), 
                    $formatted_ops
                );
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
                    error_log("DiveWP Performance Context Final - Test: {$test_name}, Raw: {$ops_per_second}, Rounded: " . round($ops_per_second, 1) . ", Formatted: {$formatted_ops}, Final: {$result}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                }
                return $result;
                
            case 'memory_tests':
                return sprintf(
                    // translators: %1$s is the formatted number of memory operations per second (e.g., "1,500.0", "2,300.5")
                    __('%1$s memory ops/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second, 1), 1)
                );
                
            case 'file_io_tests':
                return sprintf(
                    // translators: %1$s is the formatted number of file I/O operations per second (e.g., "1,500.0", "2,300.5")
                    __('%1$s I/O ops/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second, 1), 1)
                );
                
            case 'network_tests':
                return sprintf(
                    // translators: %1$s is the formatted number of network operations per second (e.g., "1,500.0", "2,300.5")
                    __('%1$s network ops/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second, 1), 1)
                );
                
            case 'wordpress_tests':
                return sprintf(
                    // translators: %1$s is the formatted number of WordPress operations per second (e.g., "1,500.0", "2,300.5")
                    __('%1$s WP ops/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second, 1), 1)
                );
                
            default:
                return sprintf(
                    // translators: %1$s is the formatted number of operations per second (e.g., "1,500.0", "2,300.5")
                    __('%1$s ops/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second, 1), 1)
                );
        }
    }

    /**
     * Get detailed explanation for performance rating
     *
     * @param string $test_name Test identifier
     * @param string $rating Performance rating
     * @param float $ops_per_second Operations per second
     * @return string Performance explanation
     */
    private static function get_performance_explanation($test_name, $rating, $ops_per_second) {
        switch ($test_name) {
            case 'cpu_tests':
                return self::get_cpu_explanation($rating, $ops_per_second);
                
            case 'memory_tests':
                return self::get_memory_explanation($rating, $ops_per_second);
                
            case 'file_io_tests':
                return self::get_file_io_explanation($rating, $ops_per_second);
                
            case 'network_tests':
                return self::get_network_explanation($rating, $ops_per_second);
                
            case 'wordpress_tests':
                return self::get_wordpress_explanation($rating, $ops_per_second);
                
            default:
                return __('Performance analysis not available for this test type.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get CPU tests explanation
     */
    private static function get_cpu_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding CPU performance! Your server handles computational tasks extremely efficiently, perfect for high-traffic sites and complex operations.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good CPU performance. Your server processes computational tasks efficiently, suitable for most WordPress sites and moderate traffic loads.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average CPU performance. May experience delays during high traffic periods or with computationally intensive operations.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor CPU performance. Computational tasks are slow, affecting page generation speeds and overall site responsiveness.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical CPU performance issues. Server struggles with basic computational tasks, significantly impacting site performance and user experience.', 'divewp-boost-site-performance');
                
            default:
                return __('CPU performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get Memory tests explanation
     */
    private static function get_memory_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent memory performance! Your server efficiently manages memory allocation and deallocation, enabling smooth operation under load.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good memory management. Your server handles memory operations efficiently, suitable for typical WordPress memory requirements.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average memory performance. May experience memory pressure during peak usage or with memory-intensive plugins and themes.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor memory performance. Memory operations are slow, potentially leading to out-of-memory errors or degraded performance.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical memory issues. Server struggles with memory management, likely causing frequent errors and poor site stability.', 'divewp-boost-site-performance');
                
            default:
                return __('Memory performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get File I/O tests explanation
     */
    private static function get_file_io_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding file I/O performance! Your storage system handles file operations extremely efficiently, perfect for content-heavy sites and media uploads.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good file I/O performance. Your storage handles file operations efficiently, suitable for typical WordPress file management needs.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average file I/O performance. May experience delays with large file uploads, theme installations, or backup operations.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor file I/O performance. File operations are slow, affecting media uploads, plugin installations, and content management.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical file I/O issues. Storage operations are severely slow, significantly impacting content management and site functionality.', 'divewp-boost-site-performance');
                
            default:
                return __('File I/O performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get Network tests explanation
     */
    private static function get_network_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent network performance! Your server provides fast network connectivity, ensuring quick external API calls and remote resource access.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good network performance. Your server handles network operations efficiently, suitable for most external service integrations.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average network performance. External API calls and remote services may experience moderate delays during peak usage.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor network performance. External services and API integrations are slow, affecting third-party plugin functionality.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical network issues. Network operations are severely slow, significantly impacting external service integrations and remote content delivery.', 'divewp-boost-site-performance');
                
            default:
                return __('Network performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get WordPress tests explanation
     */
    private static function get_wordpress_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding WordPress performance! Your server executes WordPress-specific operations extremely efficiently, perfect for complex sites and multiple simultaneous users.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good WordPress performance. Your server handles typical WordPress operations efficiently, suitable for most site configurations and traffic levels.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average WordPress performance. Core WordPress operations may experience delays during high traffic or with resource-intensive plugins.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor WordPress performance. Core operations are slow, affecting admin panel responsiveness and frontend loading times.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical WordPress performance issues. Basic WordPress operations are severely slow, significantly impacting site usability and management.', 'divewp-boost-site-performance');
                
            default:
                return __('WordPress performance could not be properly assessed.', 'divewp-boost-site-performance');
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
        switch ($rating) {
            case 'excellent':
                return __('Premium server resources performance', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Solid server resources performance', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Adequate server resources for basic needs', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Server resource limitations affecting performance', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Serious server resource performance issues', 'divewp-boost-site-performance');
                
            default:
                return __('Server resource quality assessment unavailable', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get timeout explanation for specific test
     *
     * @param string $test_name Test identifier
     * @param array $result Test result data
     * @return string Timeout explanation
     */
    private static function get_timeout_explanation($test_name, $result) {
        $base_message = sprintf(
            // translators: %1$s is the name of the resources test that timed out (e.g., "CPU tests", "memory tests")
            __('The %1$s test exceeded time limits and was stopped by the system.', 'divewp-boost-site-performance'),
            str_replace('_', ' ', $test_name)
        );

        $operations_completed = isset($result['operations_completed']) ? $result['operations_completed'] : 0;
        if ($operations_completed > 0) {
            $base_message .= ' ' . sprintf(
                // translators: %1$d is the number of resource operations completed before the test timed out
                __('Completed %1$d operations before timeout, indicating server resource constraints.', 'divewp-boost-site-performance'),
                $operations_completed
            );
        }

        return $base_message;
    }

    /**
     * Get kill explanation for specific test
     *
     * @param string $test_name Test identifier
     * @param array $result Test result data
     * @return string Kill explanation
     */
    private static function get_kill_explanation($test_name, $result) {
        return sprintf(
            // translators: %1$s is the name of the resources test that was terminated (e.g., "CPU tests", "memory tests")
            __('The %1$s test was forcibly terminated by your hosting provider, indicating severe server resource limitations or security restrictions.', 'divewp-boost-site-performance'),
            str_replace('_', ' ', $test_name)
        );
    }

    /**
     * Get detailed component breakdown for resources analysis
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @param array $thresholds Performance thresholds
     * @param array $result Full test result data
     * @return array Component breakdown data
     */
    private static function get_resources_component_breakdown($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds, $result) {
        $breakdown = array();

        // Raw Performance component
        $formatted_ops = number_format($ops_per_second, 1);
        $speed_rating = self::get_performance_rating($ops_per_second, $thresholds);
        $speed_threshold_text = '';

        if ($speed_rating === 'critical') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "0.8")
                __('below poor threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['poor'], 1)
            );
        } elseif ($speed_rating === 'poor') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "1.5")
                __('below average threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['average'], 1)
            );
        } elseif ($speed_rating === 'fair') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "2.5")
                __('below good threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['good'], 1)
            );
        } elseif ($speed_rating === 'good') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "4.0")
                __('below excellent threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['excellent'], 1)
            );
        } else {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "4.0")
                __('above excellent threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['excellent'], 1)
            );
        }

        $breakdown['performance'] = array(
            'label' => __('Raw Performance', 'divewp-boost-site-performance'),
            'value' => sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "3.2")
                __('%1$s ops/sec', 'divewp-boost-site-performance'),
                $formatted_ops
            ),
            'points' => round($ops_per_second, 1),
            'max_points' => $thresholds['excellent'] ?? 100,
            'analysis' => sprintf(
                // translators: %1$s is the formatted operations per second, %2$s is the threshold comparison text
                __('%1$s ops/sec (%2$s)', 'divewp-boost-site-performance'),
                $formatted_ops, $speed_threshold_text
            ),
            'rating' => $speed_rating
        );

        // Test Completion component
        $expected_operations = isset($result['total_operations']) ? $result['total_operations'] : max($operations_completed, 1);
        $completion_rate = $expected_operations > 0 ? ($operations_completed / $expected_operations) * 100 : 100;

        $breakdown['completion'] = array(
            'label' => __('Test Completion', 'divewp-boost-site-performance'),
            'value' => sprintf(
                // translators: %1$d is completed operations, %2$d is total expected operations (e.g., "95/100")
                __('%1$d/%2$d operations', 'divewp-boost-site-performance'),
                $operations_completed, $expected_operations
            ),
            'points' => round($completion_rate, 1),
            'max_points' => 100,
            'analysis' => sprintf(
                // translators: %1$s is the formatted completion percentage (e.g., "95.0")
                __('Completed %1$s%% of expected operations', 'divewp-boost-site-performance'),
                number_format($completion_rate, 1)
            ),
            'rating' => $completion_rate >= 90 ? 'excellent' : ($completion_rate >= 75 ? 'good' : ($completion_rate >= 50 ? 'fair' : 'poor'))
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
            'max_points' => 30, // Max expected time
            'analysis' => sprintf(
                // translators: %1$s is the formatted total test duration in seconds
                __('Total test duration: %1$s seconds', 'divewp-boost-site-performance'),
                number_format($total_time, 2)
            ),
            'rating' => $total_time <= 5 ? 'excellent' : ($total_time <= 10 ? 'good' : ($total_time <= 20 ? 'fair' : 'poor'))
        );

        return $breakdown;
    }

    /**
     * Get simple resources factors explanation for score breakdown
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @param array $thresholds Performance thresholds
     * @return string Simple explanation text
     */
    private static function get_resources_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds) {
        $explanation = '';

        // Performance factor
        $performance_rating = self::get_performance_rating($ops_per_second, $thresholds);
        $formatted_ops = number_format($ops_per_second, 1);

        if ($performance_rating === 'excellent') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "4.5")
                __('Performance: %1$s ops/sec (excellent)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } elseif ($performance_rating === 'good') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "3.2")
                __('Performance: %1$s ops/sec (good)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } elseif ($performance_rating === 'fair') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "2.1")
                __('Performance: %1$s ops/sec (moderate)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } else {
            $explanation .= sprintf(
                // translators: %1$s is the formatted operations per second value (e.g., "1.2")
                __('Performance: %1$s ops/sec (slow)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        }

        // Completion factor
        $expected_operations = max($operations_completed, 1);
        $completion_rate = min(($operations_completed / $expected_operations) * 100, 100);

        if ($completion_rate >= 95) {
            $explanation .= __(' + Full completion', 'divewp-boost-site-performance');
        } elseif ($completion_rate >= 80) {
            $explanation .= __(' + Good completion', 'divewp-boost-site-performance');
        } elseif ($completion_rate >= 60) {
            $explanation .= __(' + Partial completion', 'divewp-boost-site-performance');
        } else {
            $explanation .= __(' + Limited completion', 'divewp-boost-site-performance');
        }

        // Time factor
        if ($total_time <= 5) {
            $explanation .= __(' + Fast execution', 'divewp-boost-site-performance');
        } elseif ($total_time <= 10) {
            $explanation .= __(' + Moderate execution time', 'divewp-boost-site-performance');
        } elseif ($total_time <= 20) {
            $explanation .= __(' + Slow execution', 'divewp-boost-site-performance');
        } else {
            $explanation .= __(' + Very slow execution', 'divewp-boost-site-performance');
        }

        // Resources tests are typically scored as 100 if they complete successfully
        $explanation .= __(' = 100 points (completed successfully)', 'divewp-boost-site-performance');

        return $explanation;
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
                    case 'cpu_tests':
                        $recommendations[] = __('CPU performance is limited. Consider upgrading to a higher CPU tier or optimizing server processes.', 'divewp-boost-site-performance');
                        break;
                    case 'memory_tests':
                        $recommendations[] = __('Memory operations are slow. Increase available RAM or enable memory optimization features.', 'divewp-boost-site-performance');
                        break;
                    case 'file_io_tests':
                        $recommendations[] = __('File I/O performance needs improvement. Consider upgrading to SSD storage or optimizing file operations.', 'divewp-boost-site-performance');
                        break;
                    case 'network_tests':
                        $recommendations[] = __('Network performance is limited. Consider using a CDN or upgrading network infrastructure.', 'divewp-boost-site-performance');
                        break;
                    case 'wordpress_tests':
                        $recommendations[] = __('WordPress operations are slow. Optimize your WordPress configuration and consider enabling object caching.', 'divewp-boost-site-performance');
                        break;
                }
            }

            if ($score_data['status'] === 'timeout' || $score_data['status'] === 'killed') {
                $recommendations[] = sprintf(
                    // translators: %1$s is the name of the resources test that was terminated by the hosting provider
                    __('The %1$s test was terminated by your hosting provider. This indicates severe resource limitations.', 'divewp-boost-site-performance'),
                    str_replace('_', ' ', $test_name)
                );
            }
        }

        return $recommendations;
    }
} 