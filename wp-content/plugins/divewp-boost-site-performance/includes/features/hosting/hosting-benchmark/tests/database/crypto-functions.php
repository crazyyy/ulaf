<?php
/**
 * Database Crypto Functions Test
 *
 * Tests database performance for encryption and hash operations.
 * Performs 1,000 intensive cryptographic operations focusing on AES encryption/decryption.
 * Enhanced to match PoC intensity with CPU-intensive encryption operations.
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
 * Database Crypto Functions Test Class
 * 
 * Performs cryptographic operations performance tests using MySQL
 * encryption and hash functions with focus on intensive AES operations.
 */
class DiveWP_Crypto_Functions_Test {

    /**
     * Run the cryptographic functions performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'operations_count' => 1000
        );

        $start_time = microtime(true);
        $operations_completed = 0;
        $operation_times = array();
        $aes_success_count = 0;
        $encryption_failures = 0;

        try {
            // Test data for intensive crypto operations (larger data sets for CPU stress)
            $test_strings = array(
                'DiveWP Benchmark Test Data - This is a longer string to make encryption more CPU intensive and realistic for testing database cryptographic performance under heavy load conditions.',
                'WordPress Performance Testing - Advanced cryptographic operations using AES encryption and decryption with various key sizes and data lengths to simulate real-world usage patterns.',
                'MySQL Crypto Functions Benchmark - Comprehensive testing of database encryption capabilities including multiple rounds of encrypt/decrypt cycles to verify data integrity.',
                'Database Security Operations - Testing password hashing, data encryption, secure token generation, and cryptographic verification processes in high-volume scenarios.',
                'Hosting Performance Analysis - Evaluating server cryptographic performance under sustained load with complex encryption operations and key management scenarios.'
            );

            // Run intensive crypto operations
            for ($i = 0; $i < $config['operations_count']; $i++) {
                $op_start = microtime(true);
                $test_string = $test_strings[$i % count($test_strings)] . '_iteration_' . $i . '_' . wp_generate_password(50, false);

                // Focus heavily on AES encryption/decryption operations (like PoC)
                $encryption_key = 'divewp_benchmark_key_' . wp_generate_password(32, false) . '_' . $i;
                
                // Perform intensive AES encryption and decryption cycle
                // CRYPTO FUNCTIONS BENCHMARK - Direct query required for AES encryption performance measurement
                // WordPress has no equivalent for database cryptographic functions; essential for crypto performance testing
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $aes_encrypted = $wpdb->get_var($wpdb->prepare("SELECT AES_ENCRYPT(%s, %s)", $test_string, $encryption_key));
                
                if ($aes_encrypted) {
                    // Decrypt to verify integrity (critical for PoC-level intensity)
                    // CRYPTO FUNCTIONS BENCHMARK - Direct query required for AES decryption performance measurement
                    // WordPress abstractions would invalidate crypto integrity testing and timing accuracy
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $aes_decrypted = $wpdb->get_var($wpdb->prepare("SELECT AES_DECRYPT(%s, %s)", $aes_encrypted, $encryption_key));
                    
                    if ($aes_decrypted === $test_string) {
                        $aes_success_count++;
                        
                        // Additional encryption rounds for CPU stress (like PoC)
                        for ($round = 1; $round <= 3; $round++) {
                            $round_key = $encryption_key . '_round_' . $round;
                            // CRYPTO FUNCTIONS BENCHMARK - Direct query required for multi-round AES encryption stress testing
                            // WordPress abstractions would interfere with CPU stress measurement and crypto timing accuracy
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                            $round_encrypted = $wpdb->get_var($wpdb->prepare("SELECT AES_ENCRYPT(%s, %s)", $aes_decrypted, $round_key));
                            if ($round_encrypted) {
                                // CRYPTO FUNCTIONS BENCHMARK - Direct query required for multi-round AES decryption stress testing
                                // WordPress abstractions would distort crypto stress testing and performance measurement
                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                                $round_decrypted = $wpdb->get_var($wpdb->prepare("SELECT AES_DECRYPT(%s, %s)", $round_encrypted, $round_key));
                                if ($round_decrypted !== $aes_decrypted) {
                                    $encryption_failures++;
                                }
                            }
                        }
                    } else {
                        $encryption_failures++;
                    }
                } else {
                    $encryption_failures++;
                }

                // Additional intensive cryptographic operations per iteration
                $crypto_results = array();
                
                // Multiple hash operations with longer data
                // CRYPTO FUNCTIONS BENCHMARK - Direct queries required for hash function performance measurement
                // WordPress has no equivalent for database hash functions; essential for comprehensive crypto testing
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['md5'] = $wpdb->get_var($wpdb->prepare("SELECT MD5(%s)", $test_string));
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['sha1'] = $wpdb->get_var($wpdb->prepare("SELECT SHA1(%s)", $test_string));
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['sha2_256'] = $wpdb->get_var($wpdb->prepare("SELECT SHA2(%s, 256)", $test_string));
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['sha2_512'] = $wpdb->get_var($wpdb->prepare("SELECT SHA2(%s, 512)", $test_string));
                
                // Password-style hashing with multiple rounds
                // CRYPTO FUNCTIONS BENCHMARK - Direct query required for password hashing performance measurement
                // WordPress password functions inappropriate for database crypto performance testing
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $password_hash = $wpdb->get_var($wpdb->prepare("SELECT SHA2(CONCAT(%s, 'salt_string'), 256)", $test_string));
                for ($hash_round = 1; $hash_round <= 5; $hash_round++) {
                    // CRYPTO FUNCTIONS BENCHMARK - Direct query required for iterative password hashing stress testing
                    // WordPress abstractions would invalidate hash iteration timing and CPU stress measurement
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $password_hash = $wpdb->get_var($wpdb->prepare("SELECT SHA2(CONCAT(%s, %s), 256)", $password_hash, 'round_' . $hash_round));
                }
                $crypto_results['password_hash'] = $password_hash;

                // CRC32 and additional checksums
                // CRYPTO FUNCTIONS BENCHMARK - Direct queries required for checksum and compression performance testing
                // WordPress has no equivalent for database CRC32/COMPRESS functions; essential for crypto benchmarking
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['crc32'] = $wpdb->get_var($wpdb->prepare("SELECT CRC32(%s)", $test_string));
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['compressed'] = $wpdb->get_var($wpdb->prepare("SELECT COMPRESS(%s)", $test_string));
                
                // Random number generation for key derivation simulation
                // CRYPTO FUNCTIONS BENCHMARK - Direct queries required for database random number generation performance testing
                // WordPress wp_rand() functions would bypass database performance measurement for crypto operations
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['random1'] = $wpdb->get_var("SELECT RAND()");
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['random2'] = $wpdb->get_var("SELECT RAND()");
                
                // Binary operations for crypto key manipulation
                // CRYPTO FUNCTIONS BENCHMARK - Direct query required for binary operation performance measurement
                // WordPress has no equivalent for database binary functions; essential for crypto key manipulation testing
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $crypto_results['binary_ops'] = $wpdb->get_var($wpdb->prepare("SELECT BIN(CRC32(%s))", $test_string));

                $op_end = microtime(true);
                $operation_times[] = $op_end - $op_start;
                $operations_completed++;

                // Check time limit every 100 operations
                if ($i % 100 === 0 && (microtime(true) - $start_time) > 25) {
                    break;
                }
            }

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - minimal debug logging gated by WP_DEBUG for diagnosing DB errors
                error_log('DiveWP Crypto Functions Test Error: ' . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
        }

        $end_time = microtime(true);
        $total_time = $end_time - $start_time;

        // Calculate statistics
        $avg_operation_time = !empty($operation_times) ? array_sum($operation_times) / count($operation_times) : 0;
        $max_operation_time = !empty($operation_times) ? max($operation_times) : 0;
        $min_operation_time = !empty($operation_times) ? min($operation_times) : 0;

        $operations_per_second = ($operations_completed > 0) ? $operations_completed / $total_time : 0;
        $aes_success_rate = ($operations_completed > 0) ? ($aes_success_count / $operations_completed) * 100 : 0;
        
        // CRITICAL: Calculate score using the scoring system
        require_once __DIR__ . '/scoring.php';
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('crypto_functions', array(
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
            'test_name' => 'crypto_functions',
            'operations_completed' => $operations_completed,
            'operations_requested' => $config['operations_count'],
            'total_time' => round($total_time, 4),
            'avg_operation_time' => round($avg_operation_time * 1000, 4), // Convert to milliseconds
            'max_operation_time' => round($max_operation_time * 1000, 4),
            'min_operation_time' => round($min_operation_time * 1000, 4),
            'operations_per_second' => round($operations_per_second, 2),
            'aes_encryptions_successful' => $aes_success_count,
            'aes_success_rate' => round($aes_success_rate, 2),
            'encryption_failures' => $encryption_failures,
            'memory_used' => memory_get_usage(true),
            'timestamp' => current_time('mysql'),
            'score' => round($score, 1),
            'rating' => $rating,
            'interpretation' => sprintf(
                // translators: %1$s is the operations per second rate (e.g., "45", "123"), %2$d is the AES encryption success rate percentage
                __('Crypto functions completed at %1$s operations/second (%2$d%% AES success rate)', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0),
                round($aes_success_rate, 0)
            ),
            'status' => 'completed'
        );
        // ENHANCED UX: Add performance interpretation using scoring class (consistent with other DB sub-tests)
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('crypto_functions', $result);

        return $result;
    }
} 