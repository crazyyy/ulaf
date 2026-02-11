<?php
/**
 * Database Tests Scoring System
 *
 * Calculates and interprets database benchmark scores based on operations per second.
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
 * Database Benchmark Tests Scoring Class
 */
class DiveWP_Benchmark_Database_Scoring {

    /**
     * Performance thresholds for operations per second by test type
     * Used to calculate scores based on performance levels
     */
    const PERFORMANCE_THRESHOLDS = array(
        'insert_operations' => array(
            'excellent' => 2000,   // 2000+ ops/sec = 100 points
            'good'      => 1200,   // 1200+ ops/sec = 80 points
            'average'   => 600,    // 600+ ops/sec = 60 points
            'poor'      => 200,    // 200+ ops/sec = 40 points
            'critical'  => 0       // Below 200 ops/sec = 20 points
        ),
        'select_operations' => array(
            'excellent' => 1000,   // 1000+ complex queries/sec = 100 points (premium hosting)
            'good'      => 500,    // 500+ complex queries/sec = 80 points (good hosting)
            'average'   => 250,    // 250+ complex queries/sec = 60 points (average hosting)
            'poor'      => 100,    // 100+ complex queries/sec = 40 points (poor hosting)
            'critical'  => 0       // Below 100 ops/sec = 20 points (very poor)
        ),
        'update_operations' => array(
            'excellent' => 1500,   // 1500+ ops/sec = 100 points
            'good'      => 900,    // 900+ ops/sec = 80 points
            'average'   => 450,    // 450+ ops/sec = 60 points
            'poor'      => 150,    // 150+ ops/sec = 40 points
            'critical'  => 0       // Below 150 ops/sec = 20 points
        ),
        'crypto_functions' => array(
            'excellent' => 800,    // 800+ ops/sec = 100 points
            'good'      => 500,    // 500+ ops/sec = 80 points
            'average'   => 250,    // 250+ ops/sec = 60 points
            'poor'      => 100,    // 100+ ops/sec = 40 points
            'critical'  => 0       // Below 100 ops/sec = 20 points
        ),
        'math_functions' => array(
            'excellent' => 3000,   // 3000+ ops/sec = 100 points
            'good'      => 2000,   // 2000+ ops/sec = 80 points
            'average'   => 1000,   // 1000+ ops/sec = 60 points
            'poor'      => 300,    // 300+ ops/sec = 40 points
            'critical'  => 0       // Below 300 ops/sec = 20 points
        ),
        'string_functions' => array(
            'excellent' => 2500,   // 2500+ ops/sec = 100 points
            'good'      => 1500,   // 1500+ ops/sec = 80 points
            'average'   => 800,    // 800+ ops/sec = 60 points
            'poor'      => 250,    // 250+ ops/sec = 40 points
            'critical'  => 0       // Below 250 ops/sec = 20 points
        ),
        'datetime_functions' => array(
            'excellent' => 2000,   // 2000+ ops/sec = 100 points
            'good'      => 1200,   // 1200+ ops/sec = 80 points
            'average'   => 600,    // 600+ ops/sec = 60 points
            'poor'      => 200,    // 200+ ops/sec = 40 points
            'critical'  => 0       // Below 200 ops/sec = 20 points
        ),
        'aggregate_functions' => array(
            'excellent' => 500,    // 500+ complex aggregates/sec = 100 points (premium hosting)
            'good'      => 300,    // 300+ complex aggregates/sec = 80 points (good hosting)
            'average'   => 150,    // 150+ complex aggregates/sec = 60 points (average hosting)
            'poor'      => 50,     // 50+ complex aggregates/sec = 40 points (poor hosting)
            'critical'  => 0       // Below 50 ops/sec = 20 points (very poor)
        )
    );

    /**
     * Category weight in overall benchmark score
     * Database tests contribute 25% to the total benchmark score
     */
    const CATEGORY_WEIGHT = 0.25;

    /**
     * Sub-test weights within the database category
     * Based on real-world database operation frequency and business impact
     * Total must equal 1.0 (100%)
     */
    const SUB_TEST_WEIGHTS = array(
        'select_operations'   => 0.25,  // 25% - Most frequent operations, critical for page load times
        'insert_operations'   => 0.20,  // 20% - Important for content creation and user registrations
        'update_operations'   => 0.20,  // 20% - Critical for data modifications and integrity
        'aggregate_functions' => 0.15,  // 15% - Important for analytics and reporting
        'string_functions'    => 0.10,  // 10% - Utility operations in queries
        'datetime_functions'  => 0.05,  // 5% - Temporal operations and scheduling
        'math_functions'      => 0.03,  // 3% - Mathematical calculations
        'crypto_functions'    => 0.02   // 2% - Security operations (password hashing)
    );

    /**
     * Timeout/Kill penalties
     * Percentage of points deducted for timeout or process kill
     */
    const TIMEOUT_PENALTY = 0.5;  // 50% penalty
    const KILL_PENALTY = 0.7;     // 70% penalty

    /**
     * Calculate score for a single sub-test based on operations per second
     *
     * @param string $test_name Test identifier
     * @param array  $result    Test result data
     * @return float Score from 0 to 100
     */
    public static function calculate_sub_test_score($test_name, $result) {
        // Check for timeout or kill
        if (isset($result['status'])) {
            if ($result['status'] === 'timeout') {
                return 100 * (1 - self::TIMEOUT_PENALTY);
            }
            
            if ($result['status'] === 'killed') {
                return 100 * (1 - self::KILL_PENALTY);
            }
            
            if ($result['status'] !== 'completed') {
                return 0; // Error or unknown status
            }
        }

        // Get operations per second from test result
        $ops_per_second = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
        
        // Get thresholds for this test type
        if (!isset(self::PERFORMANCE_THRESHOLDS[$test_name])) {
            return 0; // Unknown test type
        }
        
        $thresholds = self::PERFORMANCE_THRESHOLDS[$test_name];
        
        // Calculate score based on performance thresholds
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
     * Calculate the overall database category score
     *
     * @param array $results Test results array
     * @return array Score data with interpretation and sub_scores
     */
    public static function calculate_category_score($results) {
        if (empty($results)) {
            return array(
                'score' => 0,
                'rating' => 'poor',
                'interpretation' => __('No database test results available', 'divewp-boost-site-performance'),
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

        // Calculate score for each sub-test using defined weights
        foreach ($results as $test_name => $result) {
            $sub_score = self::calculate_sub_test_score($test_name, $result);
            $weight = self::SUB_TEST_WEIGHTS[$test_name] ?? 0.125; // Fallback to equal weight if not defined
            $weighted_score = $sub_score * $weight;
            
            $sub_scores[$test_name] = array(
                'raw_score' => $sub_score,
                'weighted_score' => $weighted_score,
                'weight' => $weight,
                'status' => isset($result['status']) ? $result['status'] : 'unknown',
                'score_factors' => isset($result['performance_interpretation']['score_factors'])
                    ? $result['performance_interpretation']['score_factors']
                    : ''
            );

            $total_score += $weighted_score;
            
            if (isset($result['status']) && $result['status'] === 'completed') {
                $tests_completed++;
            } else {
                $tests_failed++;
            }
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
            return __('Excellent database performance - optimal for high-traffic sites', 'divewp-boost-site-performance');
        } elseif ($score >= 70) {
            return __('Good database performance - suitable for most applications', 'divewp-boost-site-performance');
        } elseif ($score >= 50) {
            return __('Fair database performance - may need optimization for busy sites', 'divewp-boost-site-performance');
        } elseif ($score >= 30) {
            return __('Poor database performance - optimization required', 'divewp-boost-site-performance');
        } else {
            return __('Critical database performance - severe limitations detected', 'divewp-boost-site-performance');
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
            $weight = $score_data['weight'] ?? 0;
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
            'Insert Operations' => __('Add database indexes, optimize INSERT queries, use bulk operations', 'divewp-boost-site-performance'),
            'Select Operations' => __('Optimize WHERE clauses, add proper indexes, use query caching', 'divewp-boost-site-performance'),
            'Update Operations' => __('Add indexes on WHERE columns, batch UPDATE operations, optimize queries', 'divewp-boost-site-performance'),
            'Crypto Functions' => __('Use hardware acceleration, optimize hashing algorithms', 'divewp-boost-site-performance'),
            'Math Functions' => __('Database engine optimization, consider application-level calculation', 'divewp-boost-site-performance'),
            'String Functions' => __('Optimize text processing, use appropriate data types and indexes', 'divewp-boost-site-performance'),
            'Datetime Functions' => __('Optimize date queries, use proper datetime indexes', 'divewp-boost-site-performance'),
            'Aggregate Functions' => __('Add indexes for GROUP BY/ORDER BY, optimize aggregate queries', 'divewp-boost-site-performance')
        );

        return $strategies[$test_name] ?? __('General database optimization needed', 'divewp-boost-site-performance');
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
                    $interpretation['score_factors'] = __('Test timed out - unable to complete database analysis', 'divewp-boost-site-performance');
                    return $interpretation;

                case 'killed':
                    $interpretation['rating'] = 'killed';
                    $interpretation['rating_label'] = __('Terminated', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = self::get_kill_explanation($test_name, $result);
                    $interpretation['hosting_quality'] = __('Severe hosting limitations detected', 'divewp-boost-site-performance');
                    // Add score factors even for killed results
                    $interpretation['score_factors'] = __('Test terminated - unable to complete database analysis', 'divewp-boost-site-performance');
                    return $interpretation;

                case 'error':
                    $interpretation['rating'] = 'error';
                    $interpretation['rating_label'] = __('Error', 'divewp-boost-site-performance');
                    $interpretation['explanation'] = __('Test failed to complete due to system errors.', 'divewp-boost-site-performance');
                    $interpretation['hosting_quality'] = __('System instability detected', 'divewp-boost-site-performance');
                    // Add score factors even for error results
                    $interpretation['score_factors'] = __('Test failed - unable to calculate database factors', 'divewp-boost-site-performance');
                    return $interpretation;
            }
        }

        // Calculate operations per second for performance analysis
        $ops_per_second = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
        $total_time = isset($result['total_time']) ? $result['total_time'] : 0;
        $operations_completed = isset($result['operations_completed']) ? $result['operations_completed'] : 0;

        // Get performance rating and context
        $thresholds = self::PERFORMANCE_THRESHOLDS[$test_name] ?? array();
        $performance_rating = self::get_performance_rating($ops_per_second, $thresholds);

        // Add simple database factors explanation for score breakdown
        $interpretation['score_factors'] = self::get_database_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds);

        $interpretation['rating'] = $performance_rating;
        $interpretation['rating_label'] = self::get_rating_label($performance_rating);
        $interpretation['performance_context'] = self::get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time);
        $interpretation['explanation'] = self::get_performance_explanation($test_name, $performance_rating, $ops_per_second);
        $interpretation['hosting_quality'] = self::get_hosting_quality_assessment($test_name, $performance_rating);

        return $interpretation;
    }

    /**
     * Get detailed component breakdown for database analysis
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @param array $thresholds Performance thresholds
     * @param array $result Full test result data
     * @return array Component breakdown data
     */
    private static function get_database_component_breakdown($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds, $result) {
        $breakdown = array();

        // Query Performance component
        $formatted_ops = number_format($ops_per_second, 1);
        $speed_rating = self::get_performance_rating($ops_per_second, $thresholds);
        $speed_threshold_text = '';

        if ($speed_rating === 'critical') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "100")
                __('below poor threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['poor'], 1)
            );
        } elseif ($speed_rating === 'poor') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "250")
                __('below average threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['average'], 1)
            );
        } elseif ($speed_rating === 'fair') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "500")
                __('below good threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['good'], 1)
            );
        } elseif ($speed_rating === 'good') {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "1,000")
                __('below excellent threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['excellent'], 1)
            );
        } else {
            $speed_threshold_text = sprintf(
                // translators: %1$s is the formatted threshold value (e.g., "1,000")
                __('above excellent threshold of %1$s', 'divewp-boost-site-performance'),
                number_format($thresholds['excellent'], 1)
            );
        }

        $breakdown['performance'] = array(
            'label' => __('Query Performance', 'divewp-boost-site-performance'),
            'value' => sprintf(
                // translators: %1$s is the formatted queries per second value (e.g., "1,200")
                __('%1$s queries/sec', 'divewp-boost-site-performance'),
                $formatted_ops
            ),
            'points' => round($ops_per_second, 1),
            'max_points' => $thresholds['excellent'] ?? 100,
            'analysis' => sprintf(
                // translators: %1$s is the formatted queries per second, %2$s is the threshold comparison text
                __('%1$s queries/sec (%2$s)', 'divewp-boost-site-performance'),
                $formatted_ops, $speed_threshold_text
            ),
            'rating' => $speed_rating
        );

        // Query Complexity component
        $complexity_label = '';
        $complexity_rating = 'good';

        switch ($test_name) {
            case 'select_operations':
                $complexity_label = __('Simple SELECT queries', 'divewp-boost-site-performance');
                break;
            case 'insert_operations':
                $complexity_label = __('INSERT operations', 'divewp-boost-site-performance');
                break;
            case 'update_operations':
                $complexity_label = __('UPDATE operations', 'divewp-boost-site-performance');
                break;
            case 'string_functions':
                $complexity_label = __('String function queries', 'divewp-boost-site-performance');
                $complexity_rating = 'fair';
                break;
            case 'math_functions':
                $complexity_label = __('Math function queries', 'divewp-boost-site-performance');
                $complexity_rating = 'fair';
                break;
            case 'datetime_functions':
                $complexity_label = __('Date/time function queries', 'divewp-boost-site-performance');
                $complexity_rating = 'fair';
                break;
            case 'aggregate_functions':
                $complexity_label = __('Aggregate function queries (COUNT, SUM, etc.)', 'divewp-boost-site-performance');
                $complexity_rating = 'poor';
                break;
            case 'crypto_functions':
                $complexity_label = __('Cryptographic function queries', 'divewp-boost-site-performance');
                $complexity_rating = 'poor';
                break;
            default:
                $complexity_label = __('Database queries', 'divewp-boost-site-performance');
        }

        $breakdown['complexity'] = array(
            'label' => __('Query Type', 'divewp-boost-site-performance'),
            'value' => $complexity_label,
            'points' => 100, // All queries are valid
            'max_points' => 100,
            'analysis' => sprintf(
                // translators: %1$s is the type of database query being tested (e.g., "Simple SELECT queries")
                __('Testing %1$s', 'divewp-boost-site-performance'),
                $complexity_label
            ),
            'rating' => $complexity_rating
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
                __('Total test duration: %1$s seconds', 'divewp-boost-site-performance'),
                number_format($total_time, 2)
            ),
            'rating' => $total_time <= 5 ? 'excellent' : ($total_time <= 10 ? 'good' : ($total_time <= 20 ? 'fair' : 'poor'))
        );

        return $breakdown;
    }

    /**
     * Get simple database factors explanation for score breakdown
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @param array $thresholds Performance thresholds
     * @return string Simple explanation text
     */
    private static function get_database_factors_explanation($test_name, $ops_per_second, $operations_completed, $total_time, $thresholds) {
        $explanation = '';

        // Query performance factor
        $performance_rating = self::get_performance_rating($ops_per_second, $thresholds);
        $formatted_ops = number_format($ops_per_second, 1);

        if ($performance_rating === 'excellent') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted queries per second value (e.g., "1,500")
                __('Query speed: %1$s queries/sec (excellent)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } elseif ($performance_rating === 'good') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted queries per second value (e.g., "1,200")
                __('Query speed: %1$s queries/sec (good)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } elseif ($performance_rating === 'fair') {
            $explanation .= sprintf(
                // translators: %1$s is the formatted queries per second value (e.g., "800")
                __('Query speed: %1$s queries/sec (moderate)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        } else {
            $explanation .= sprintf(
                // translators: %1$s is the formatted queries per second value (e.g., "400")
                __('Query speed: %1$s queries/sec (slow)', 'divewp-boost-site-performance'),
                $formatted_ops
            );
        }

        // Query complexity factor
        $complexity_description = '';
        switch ($test_name) {
            case 'select_operations':
                $complexity_description = __('simple SELECT queries', 'divewp-boost-site-performance');
                break;
            case 'insert_operations':
                $complexity_description = __('INSERT operations', 'divewp-boost-site-performance');
                break;
            case 'update_operations':
                $complexity_description = __('UPDATE operations', 'divewp-boost-site-performance');
                break;
            case 'string_functions':
                $complexity_description = __('string function queries', 'divewp-boost-site-performance');
                break;
            case 'math_functions':
                $complexity_description = __('math function queries', 'divewp-boost-site-performance');
                break;
            case 'datetime_functions':
                $complexity_description = __('date/time function queries', 'divewp-boost-site-performance');
                break;
            case 'aggregate_functions':
                $complexity_description = __('aggregate function queries', 'divewp-boost-site-performance');
                break;
            case 'crypto_functions':
                $complexity_description = __('cryptographic function queries', 'divewp-boost-site-performance');
                break;
            default:
                $complexity_description = __('database queries', 'divewp-boost-site-performance');
        }

        $explanation .= sprintf(
            // translators: %1$s is the type of database query being tested (e.g., "simple SELECT queries")
            __(' + Testing %1$s', 'divewp-boost-site-performance'),
            $complexity_description
        );

        // Execution time factor
        if ($total_time <= 5) {
            $explanation .= __(' + Fast execution', 'divewp-boost-site-performance');
        } elseif ($total_time <= 10) {
            $explanation .= __(' + Moderate execution time', 'divewp-boost-site-performance');
        } elseif ($total_time <= 20) {
            $explanation .= __(' + Slow execution', 'divewp-boost-site-performance');
        } else {
            $explanation .= __(' + Very slow execution', 'divewp-boost-site-performance');
        }

        // Database tests are scored based on completion and performance
        $explanation .= __(' = Scored based on query efficiency', 'divewp-boost-site-performance');

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
            'killed' => __('Terminated', 'divewp-boost-site-performance'),
            'error' => __('Error', 'divewp-boost-site-performance'),
            'unknown' => __('Unknown', 'divewp-boost-site-performance')
        );

        return $labels[$rating] ?? $labels['unknown'];
    }

    /**
     * Get performance context string (e.g., "1,500 ops/sec")
     *
     * @param string $test_name Test identifier
     * @param float $ops_per_second Operations per second
     * @param int $operations_completed Total operations completed
     * @param float $total_time Total time taken
     * @return string Performance context
     */
    private static function get_performance_context($test_name, $ops_per_second, $operations_completed, $total_time) {
        switch ($test_name) {
            case 'insert_operations':
                return sprintf(
                    // translators: %1$s is the formatted number of database insert operations per second (e.g., "1,500", "2,300")
                    __('%1$s inserts/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'select_operations':
                return sprintf(
                    // translators: %1$s is the formatted number of database select queries per second (e.g., "1,500", "2,300")
                    __('%1$s queries/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'update_operations':
                return sprintf(
                    // translators: %1$s is the formatted number of database update operations per second (e.g., "1,500", "2,300")
                    __('%1$s updates/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'crypto_functions':
                return sprintf(
                    // translators: %1$s is the formatted number of cryptographic operations per second (e.g., "1,500", "2,300")
                    __('%1$s crypto/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'math_functions':
                return sprintf(
                    // translators: %1$s is the formatted number of mathematical operations per second (e.g., "1,500", "2,300")
                    __('%1$s math/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'string_functions':
                return sprintf(
                    // translators: %1$s is the formatted number of string operations per second (e.g., "1,500", "2,300")
                    __('%1$s strings/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'datetime_functions':
                return sprintf(
                    // translators: %1$s is the formatted number of datetime operations per second (e.g., "1,500", "2,300")
                    __('%1$s dates/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            case 'aggregate_functions':
                return sprintf(
                    // translators: %1$s is the formatted number of aggregate function operations per second (e.g., "1,500", "2,300")
                    __('%1$s aggregates/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
                );
                
            default:
                return sprintf(
                    // translators: %1$s is the formatted number of operations per second (e.g., "1,500", "2,300")
                    __('%1$s ops/sec', 'divewp-boost-site-performance'), 
                    number_format(round($ops_per_second))
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
            case 'insert_operations':
                return self::get_insert_explanation($rating, $ops_per_second);
                
            case 'select_operations':
                return self::get_select_explanation($rating, $ops_per_second);
                
            case 'update_operations':
                return self::get_update_explanation($rating, $ops_per_second);
                
            case 'crypto_functions':
                return self::get_crypto_explanation($rating, $ops_per_second);
                
            case 'math_functions':
                return self::get_math_explanation($rating, $ops_per_second);
                
            case 'string_functions':
                return self::get_string_explanation($rating, $ops_per_second);
                
            case 'datetime_functions':
                return self::get_datetime_explanation($rating, $ops_per_second);
                
            case 'aggregate_functions':
                return self::get_aggregate_explanation($rating, $ops_per_second);
                
            default:
                return __('Performance analysis not available for this test type.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get INSERT operations explanation
     */
    private static function get_insert_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding INSERT performance! Your database handles new record creation extremely efficiently, perfect for high-traffic sites with frequent content additions.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good INSERT operation speed. Your database processes new records efficiently, suitable for most WordPress sites with regular content updates.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average INSERT performance. May experience delays when adding multiple records simultaneously or during high traffic periods.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor INSERT operation performance. Adding new posts, comments, or user registrations may be slow, affecting user experience.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical INSERT performance issues. New content creation is severely slow, significantly impacting site functionality and user experience.', 'divewp-boost-site-performance');
                
            default:
                return __('INSERT operation performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get SELECT operations explanation
     */
    private static function get_select_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent SELECT query performance! Your database retrieves data extremely quickly, ensuring fast page loads and optimal user experience.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good SELECT operation speed. Your database handles data retrieval efficiently, suitable for most WordPress sites and plugin queries.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average SELECT performance. Complex queries or high traffic may cause slower page loads and reduced responsiveness.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor SELECT query performance. Page loads are slow, and users may experience delays when browsing your site or accessing content.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical SELECT performance issues. Database queries are severely slow, causing significant page load delays and poor user experience.', 'divewp-boost-site-performance');
                
            default:
                return __('SELECT operation performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get UPDATE operations explanation
     */
    private static function get_update_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding UPDATE performance! Your database handles record modifications extremely efficiently, perfect for dynamic content and user interactions.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good UPDATE operation speed. Your database processes record changes efficiently, suitable for most WordPress administrative tasks and user interactions.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average UPDATE performance. May experience delays when editing content, updating user profiles, or processing form submissions.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor UPDATE operation performance. Content editing and user profile updates are slow, affecting administrative efficiency and user experience.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical UPDATE performance issues. Record modifications are severely slow, significantly impacting content management and user interactions.', 'divewp-boost-site-performance');
                
            default:
                return __('UPDATE operation performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get crypto functions explanation
     */
    private static function get_crypto_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent cryptographic performance! Your database handles password hashing and security functions very efficiently, ensuring strong security without performance impact.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good cryptographic function performance. Security operations like password verification and data encryption process efficiently.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average cryptographic performance. Password operations and security functions may cause slight delays during user authentication.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor cryptographic function performance. User login and password operations are slow, affecting authentication speed and user experience.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical cryptographic performance issues. Security operations are severely slow, significantly impacting user authentication and login processes.', 'divewp-boost-site-performance');
                
            default:
                return __('Cryptographic function performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get math functions explanation
     */
    private static function get_math_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding mathematical function performance! Your database handles numerical calculations extremely efficiently, perfect for analytics and statistical processing.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good mathematical function performance. Numerical calculations and statistical operations process efficiently in database queries.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average mathematical performance. Complex calculations in reports or analytics may experience moderate processing delays.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor mathematical function performance. Numerical calculations in database queries are slow, affecting reporting and analytics functionality.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical mathematical performance issues. Database calculations are severely slow, significantly impacting analytical queries and numerical processing.', 'divewp-boost-site-performance');
                
            default:
                return __('Mathematical function performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get string functions explanation
     */
    private static function get_string_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent string processing performance! Your database handles text operations extremely efficiently, perfect for content-heavy sites and search functionality.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good string function performance. Text processing operations like search queries and content manipulation execute efficiently.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average string processing performance. Text-heavy operations like content search may experience moderate processing delays.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor string function performance. Text processing operations are slow, affecting search functionality and content manipulation features.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical string processing issues. Text operations are severely slow, significantly impacting search functionality and content processing.', 'divewp-boost-site-performance');
                
            default:
                return __('String function performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get datetime functions explanation
     */
    private static function get_datetime_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Outstanding date/time performance! Your database handles temporal operations extremely efficiently, perfect for scheduling and time-based queries.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good date/time function performance. Temporal operations like date filtering and time-based queries execute efficiently.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average date/time performance. Time-based queries and date operations may experience moderate processing delays.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor date/time function performance. Temporal operations are slow, affecting date-based filtering and scheduling functionality.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical date/time performance issues. Temporal operations are severely slow, significantly impacting time-based queries and scheduling features.', 'divewp-boost-site-performance');
                
            default:
                return __('Date/time function performance could not be properly assessed.', 'divewp-boost-site-performance');
        }
    }

    /**
     * Get aggregate functions explanation
     */
    private static function get_aggregate_explanation($rating, $ops_per_second) {
        switch ($rating) {
            case 'excellent':
                return __('Excellent aggregate function performance! Your database handles complex summaries and grouping operations extremely efficiently, perfect for analytics and reporting.', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Good aggregate function performance. Complex queries with COUNT, SUM, and GROUP BY operations execute efficiently.', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Average aggregate performance. Complex reporting queries with grouping and calculations may experience moderate processing delays.', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Poor aggregate function performance. Complex queries with grouping and calculations are slow, affecting reporting and analytics functionality.', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Critical aggregate performance issues. Complex queries are severely slow, significantly impacting reporting capabilities and analytical operations.', 'divewp-boost-site-performance');
                
            default:
                return __('Aggregate function performance could not be properly assessed.', 'divewp-boost-site-performance');
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
                return __('Premium database hosting performance', 'divewp-boost-site-performance');
                
            case 'good':
                return __('Solid database hosting performance', 'divewp-boost-site-performance');
                
            case 'average':
                return __('Adequate database hosting for basic needs', 'divewp-boost-site-performance');
                
            case 'poor':
                return __('Database hosting limitations affecting performance', 'divewp-boost-site-performance');
                
            case 'critical':
                return __('Serious database hosting performance issues', 'divewp-boost-site-performance');
                
            default:
                return __('Database hosting quality assessment unavailable', 'divewp-boost-site-performance');
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
            // translators: %1$s is the name of the database test that timed out (e.g., "insert operations", "select operations")
            __('The %1$s test exceeded time limits and was stopped by the system.', 'divewp-boost-site-performance'),
            str_replace('_', ' ', $test_name)
        );

        $operations_completed = isset($result['operations_completed']) ? $result['operations_completed'] : 0;
        if ($operations_completed > 0) {
            $base_message .= ' ' . sprintf(
                // translators: %1$d is the number of database operations completed before the test timed out
                __('Completed %1$d operations before timeout, indicating database resource constraints.', 'divewp-boost-site-performance'),
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
            // translators: %1$s is the name of the database test that was terminated (e.g., "insert operations", "select operations")
            __('The %1$s test was forcibly terminated by your hosting provider, indicating severe database resource limitations or security restrictions.', 'divewp-boost-site-performance'),
            str_replace('_', ' ', $test_name)
        );
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
                    case 'insert_operations':
                        $recommendations[] = __('INSERT operations are slow. Add database indexes and consider bulk insert strategies.', 'divewp-boost-site-performance');
                        break;
                    case 'select_operations':
                        $recommendations[] = __('SELECT queries need optimization. Review slow query log and add appropriate indexes.', 'divewp-boost-site-performance');
                        break;
                    case 'update_operations':
                        $recommendations[] = __('UPDATE operations are sluggish. Optimize WHERE clauses and add indexes on updated columns.', 'divewp-boost-site-performance');
                        break;
                    case 'crypto_functions':
                        $recommendations[] = __('Cryptographic functions are slow. Consider database engine optimization or hardware acceleration.', 'divewp-boost-site-performance');
                        break;
                    case 'math_functions':
                        $recommendations[] = __('Mathematical functions need optimization. Consider moving complex calculations to application level.', 'divewp-boost-site-performance');
                        break;
                    case 'string_functions':
                        $recommendations[] = __('String processing is slow. Optimize text queries and consider full-text search indexes.', 'divewp-boost-site-performance');
                        break;
                    case 'datetime_functions':
                        $recommendations[] = __('Date/time operations need optimization. Add indexes on date columns and optimize temporal queries.', 'divewp-boost-site-performance');
                        break;
                    case 'aggregate_functions':
                        $recommendations[] = __('Aggregate queries are slow. Add indexes for GROUP BY clauses and optimize summary operations.', 'divewp-boost-site-performance');
                        break;
                }
            }

            if ($score_data['status'] === 'timeout' || $score_data['status'] === 'killed') {
                $recommendations[] = sprintf(
                    // translators: %1$s is the name of the database test that was terminated by the hosting provider
                    __('The %1$s test was terminated by your hosting provider. This indicates severe database resource limitations.', 'divewp-boost-site-performance'),
                    str_replace('_', ' ', $test_name)
                );
            }
        }

        return $recommendations;
    }
} 