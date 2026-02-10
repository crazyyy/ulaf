<?php
/**
 * File Concurrency Test
 *
 * Tests filesystem I/O performance under concurrent load by performing
 * 320 file operations simultaneously to stress the file system.
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
 * File Concurrency Test Class
 */
class DiveWP_File_Concurrency_Test {

    /**
     * Test configuration
     */
    const FILE_OPERATIONS = 320;
    const FILE_SIZE = 8192; // 8KB per file
    const MAX_TEST_TIME = 35; // 35 seconds max
    const BATCH_SIZE = 16; // Process files in batches
    const MAX_DISK_USAGE = 10485760; // 10MB max disk usage

    /**
     * Test directory path
     * @var string
     */
    private static $test_dir = null;

    /**
     * Run the file concurrency test
     *
     * @return array Test results
     */
    public static function run() {
        $start_time = microtime(true);
        $test_name = 'File Concurrency';
        
        $result = array(
            'status' => 'completed',
            'test_name' => $test_name,
            'total_time' => 0,
            'operations_completed' => 0,
            'successful_operations' => 0,
            'failed_operations' => 0,
            'write_operations' => 0,
            'read_operations' => 0,
            'delete_operations' => 0,
            'avg_operation_time' => 0,
            'operations_per_second' => 0,
            'file_efficiency' => 0,
            'score' => 0,
            'rating' => 'unknown',
            'interpretation' => '',
            'error_details' => array(),
            'timestamp' => current_time('mysql')
        );

        try {
            // Set appropriate time limit
            $time_limit = get_transient('divewp_benchmark_time_limit') ?: 54;
            // BENCHMARK REQUIREMENT - Extended time limit needed for server stress testing
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
            set_time_limit($time_limit);

            // Setup test directory
            self::setup_test_directory();
            
            // BENCHMARK REQUIREMENT - Direct filesystem writability check for I/O benchmark
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
            if (!self::$test_dir || !is_writable(self::$test_dir)) {
                throw new Exception(esc_html__('Cannot create writable test directory for file concurrency test', 'divewp-boost-site-performance'));
            }

            // TRUE PARALLEL: REST worker file units via curl_multi
            require_once __DIR__ . '/helpers.php';
            $token = wp_generate_password(16, false, false);
            set_transient('divewp_concurrency_worker_token', $token, MINUTE_IN_SECONDS);
            $parallel = 10;
            $runtime = 8.0;
            $pool = DiveWP_Concurrency_MultiRunner::run('file', $parallel, $runtime, $token);
            delete_transient('divewp_concurrency_worker_token');

            // Map results to existing structure
            $successful_operations = $pool['success_count'];
            $failed_operations = $pool['fail_count'];
            $avg_operation_time = !empty($pool['durations']) ? array_sum($pool['durations']) / count($pool['durations']) : 0;

            $test_result = array(
                'operations_completed' => $successful_operations + $failed_operations,
                'successful_operations' => $successful_operations,
                'failed_operations' => $failed_operations,
                'write_operations' => 0,
                'read_operations' => 0,
                'delete_operations' => 0,
                'avg_operation_time' => round($avg_operation_time, 6),
                'operation_times' => $pool['durations'],
                'error_details' => $pool['errors'],
                'created_files_count' => 0,
                'total_disk_usage' => 0
            );
            
            // Merge test results
            $result = array_merge($result, $test_result);
            
            // Calculate final metrics
            $total_time = microtime(true) - $start_time;
            $result['total_time'] = $total_time;
            
            if ($total_time > 0 && $result['operations_completed'] > 0) {
                $result['operations_per_second'] = round($result['operations_completed'] / $total_time, 2);
                $result['file_efficiency'] = round(($result['successful_operations'] / max(1, $result['operations_completed'])) * 100, 2);
            }

            // Calculate score and rating
            $result['score'] = self::calculate_score($result);
            $result['rating'] = self::get_rating($result['score']);
            $result['interpretation'] = self::get_interpretation($result);
            
            // Add status fields for UX enhancement
            $result['test_status'] = ($result['operations_completed'] >= self::FILE_OPERATIONS) ? 'completed' : 'partial';
            $result['completed_operations'] = $result['operations_completed'];
            $result['total_operations'] = self::FILE_OPERATIONS;
            
            if ($result['test_status'] === 'partial') {
                $result['timeout_reason'] = esc_html__('Performance degraded under concurrent load.', 'divewp-boost-site-performance');
            } elseif ($result['failed_operations'] > 0) {
                $result['timeout_reason'] = sprintf(
                    // translators: %1$d is the number of file operations that failed during testing
                    esc_html__('%1$d file operations failed due to permissions or hosting limits.', 'divewp-boost-site-performance'),
                    absint($result['failed_operations'])
                );
            } else {
                $result['timeout_reason'] = '';
            }

            // ENHANCED UX: Add performance interpretation data using scoring class
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/scoring.php';
            $result['performance_interpretation'] = DiveWP_Benchmark_Concurrency_Scoring::get_sub_test_performance_interpretation('file_concurrency', $result);

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("DiveWP File Concurrency Error: " . $e->getMessage());
            }
            
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
            $result['total_time'] = microtime(true) - $start_time;
            $result['score'] = 0;
            $result['rating'] = 'error';
            $result['interpretation'] = sprintf(
                // translators: %1$s is the specific error message explaining why the file concurrency test failed
                esc_html__('File concurrency test failed: %1$s', 'divewp-boost-site-performance'), 
                esc_html($e->getMessage())
            );
            
            // Add status fields for error case
            $result['test_status'] = 'error';
            $result['completed_operations'] = $result['operations_completed'] ?? 0;
            $result['total_operations'] = self::FILE_OPERATIONS;
            $result['timeout_reason'] = sprintf(
                // translators: %1$s is the specific error message explaining why the test failed
                esc_html__('Test failed with error: %1$s', 'divewp-boost-site-performance'), 
                esc_html($e->getMessage())
            );
        }

        // Always cleanup test directory
        self::cleanup_test_directory();

        return $result;
    }

    /**
     * Setup test directory for file operations
     */
    private static function setup_test_directory() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        
        // BENCHMARK REQUIREMENT - Direct filesystem writability check for I/O benchmark
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
        if (!$base_dir || !is_writable($base_dir)) {
            // Fallback to temp directory
            $base_dir = get_temp_dir();
        }
        
        self::$test_dir = $base_dir . '/divewp_file_concurrency_' . uniqid();
        
        if (!wp_mkdir_p(self::$test_dir)) {
            throw new Exception(esc_html__('Failed to create test directory', 'divewp-boost-site-performance'));
        }
        
        // Verify directory is writable
        // BENCHMARK REQUIREMENT - Direct filesystem writability check for I/O benchmark
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
        if (!is_writable(self::$test_dir)) {
            throw new Exception(esc_html__('Test directory is not writable', 'divewp-boost-site-performance'));
        }
    }

    /**
     * Execute concurrent file operations
     *
     * @param float $start_time Test start time
     * @return array Operation results
     */
    private static function execute_concurrent_file_operations($start_time) {
        $successful_operations = 0;
        $failed_operations = 0;
        $write_operations = 0;
        $read_operations = 0;
        $delete_operations = 0;
        $operation_times = array();
        $error_details = array();
        $created_files = array();
        $total_disk_usage = 0;
        
        $total_batches = ceil(self::FILE_OPERATIONS / self::BATCH_SIZE);
        
        for ($batch = 0; $batch < $total_batches; $batch++) {
            // Check timeout
            if ((microtime(true) - $start_time) > self::MAX_TEST_TIME) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("DiveWP File Concurrency: Timeout reached, processed {$successful_operations} operations");
                }
                break;
            }
            
            // Check disk usage
            if ($total_disk_usage > self::MAX_DISK_USAGE) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("DiveWP File Concurrency: Disk usage limit reached, stopping test");
                }
                break;
            }
            
            $batch_operations = min(self::BATCH_SIZE, self::FILE_OPERATIONS - ($batch * self::BATCH_SIZE));
            
            // Execute batch of file operations
            $batch_result = self::execute_file_batch(
                $batch_operations, 
                $batch, 
                $operation_times, 
                $created_files,
                $total_disk_usage
            );
            
            $successful_operations += $batch_result['successful'];
            $failed_operations += $batch_result['failed'];
            $write_operations += $batch_result['writes'];
            $read_operations += $batch_result['reads'];
            $delete_operations += $batch_result['deletes'];
            $error_details = array_merge($error_details, $batch_result['errors']);
            
            // Brief pause between batches
            usleep(25000); // 25ms
        }
        
        // Calculate operation statistics
        $avg_operation_time = 0;
        if (!empty($operation_times)) {
            $avg_operation_time = array_sum($operation_times) / count($operation_times);
        }
        
        return array(
            'operations_completed' => $successful_operations + $failed_operations,
            'successful_operations' => $successful_operations,
            'failed_operations' => $failed_operations,
            'write_operations' => $write_operations,
            'read_operations' => $read_operations,
            'delete_operations' => $delete_operations,
            'avg_operation_time' => round($avg_operation_time, 6),
            'operation_times' => $operation_times,
            'error_details' => $error_details,
            'created_files_count' => count($created_files),
            'total_disk_usage' => $total_disk_usage
        );
    }

    /**
     * Execute a batch of file operations
     *
     * @param int $batch_size Number of operations in this batch
     * @param int $batch_number Batch identifier
     * @param array &$operation_times Reference to operation times array
     * @param array &$created_files Reference to created files array
     * @param int &$total_disk_usage Reference to disk usage counter
     * @return array Batch results
     */
    private static function execute_file_batch($batch_size, $batch_number, &$operation_times, &$created_files, &$total_disk_usage) {
        $successful = 0;
        $failed = 0;
        $writes = 0;
        $reads = 0;
        $deletes = 0;
        $errors = array();
        
        for ($i = 0; $i < $batch_size; $i++) {
            $operation_number = ($batch_number * self::BATCH_SIZE) + $i;
            $operation_start = microtime(true);
            
            try {
                // Determine operation type based on file lifecycle
                $operation_type = self::determine_operation_type($operation_number, $created_files);
                
                switch ($operation_type) {
                    case 'write':
                        $success = self::execute_write_operation($operation_number, $created_files, $total_disk_usage);
                        if ($success) $writes++;
                        break;
                        
                    case 'read':
                        $success = self::execute_read_operation($created_files);
                        if ($success) $reads++;
                        break;
                        
                    case 'delete':
                        $success = self::execute_delete_operation($created_files, $total_disk_usage);
                        if ($success) $deletes++;
                        break;
                        
                    default:
                        $success = false;
                }
                
                if ($success) {
                    $successful++;
                    $operation_times[] = microtime(true) - $operation_start;
                } else {
                    $failed++;
                }
                
            } catch (Exception $e) {
                $failed++;
                $errors[] = array(
                    'operation' => $operation_number,
                    'type' => $operation_type ?? 'unknown',
                    'error' => $e->getMessage()
                );
            }
        }
        
        return array(
            'successful' => $successful,
            'failed' => $failed,
            'writes' => $writes,
            'reads' => $reads,
            'deletes' => $deletes,
            'errors' => $errors
        );
    }

    /**
     * Determine what type of operation to perform
     *
     * @param int $operation_number Current operation number
     * @param array $created_files Array of created files
     * @return string Operation type
     */
    private static function determine_operation_type($operation_number, $created_files) {
        $file_count = count($created_files);
        
        // Create files for first 50% of operations
        if ($operation_number < self::FILE_OPERATIONS * 0.5) {
            return 'write';
        }
        
        // Read operations for middle 30% of operations
        if ($operation_number < self::FILE_OPERATIONS * 0.8 && $file_count > 0) {
            return 'read';
        }
        
        // Delete operations for last 20% of operations
        if ($file_count > 0) {
            return 'delete';
        }
        
        // Fallback to write if no files exist
        return 'write';
    }

    /**
     * Execute file write operation
     *
     * @param int $operation_number Operation identifier
     * @param array &$created_files Reference to created files array
     * @param int &$total_disk_usage Reference to disk usage counter
     * @return bool Success status
     */
    private static function execute_write_operation($operation_number, &$created_files, &$total_disk_usage) {
        $filename = self::$test_dir . "/test_file_{$operation_number}_" . uniqid() . ".tmp";
        
        // Generate test data
        $test_data = self::generate_test_data($operation_number);
        
        // Write file
        // BENCHMARK REQUIREMENT - Direct file write used for precise I/O performance measurement
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_write_file_put_contents
        $bytes_written = file_put_contents($filename, $test_data, LOCK_EX);
        
        if ($bytes_written !== false && $bytes_written > 0) {
            $created_files[] = $filename;
            $total_disk_usage += $bytes_written;
            return true;
        }
        
        return false;
    }

    /**
     * Execute file read operation
     *
     * @param array $created_files Array of created files
     * @return bool Success status
     */
    private static function execute_read_operation($created_files) {
        if (empty($created_files)) {
            return false;
        }
        
        // Select random file to read
        $filename_index = wp_rand(0, count($created_files) - 1);
        $filename = $created_files[$filename_index];
        
        // BENCHMARK REQUIREMENT - Direct filesystem check for I/O benchmark
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_exists
        if (!file_exists($filename)) {
            return false;
        }
        
        // Read file contents
        // BENCHMARK REQUIREMENT - Direct file read used for precise I/O performance measurement
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents
        $contents = file_get_contents($filename);
        
        if ($contents !== false) {
            // Verify content integrity
            return strpos($contents, 'DIVEWP_FILE_TEST') !== false;
        }
        
        return false;
    }

    /**
     * Execute file delete operation
     *
     * @param array &$created_files Reference to created files array
     * @param int &$total_disk_usage Reference to disk usage counter
     * @return bool Success status
     */
    private static function execute_delete_operation(&$created_files, &$total_disk_usage) {
        if (empty($created_files)) {
            return false;
        }
        
        // Select random file to delete
        $index = wp_rand(0, count($created_files) - 1);
        $filename = $created_files[$index];
        
        // BENCHMARK REQUIREMENT - Direct filesystem check for I/O benchmark
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_exists
        if (!file_exists($filename)) {
            // Remove from array even if file doesn't exist
            unset($created_files[$index]);
            $created_files = array_values($created_files); // Reindex array
            return false;
        }
        
        // Get file size before deletion
        // BENCHMARK REQUIREMENT - Direct filesystem metadata access for I/O benchmark
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_filesize
        $file_size = filesize($filename);
        
        // Delete file
        // BENCHMARK REQUIREMENT - Direct file delete used for precise I/O performance measurement
        // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
        $deleted = unlink($filename);
        
        if ($deleted) {
            // Remove from created files array
            unset($created_files[$index]);
            $created_files = array_values($created_files); // Reindex array
            $total_disk_usage = max(0, $total_disk_usage - $file_size);
            return true;
        }
        
        return false;
    }

    /**
     * Generate test data for file operations
     *
     * @param int $operation_number Operation identifier
     * @return string Test data
     */
    private static function generate_test_data($operation_number) {
        $header = "DIVEWP_FILE_TEST_OPERATION_{$operation_number}\n";
        $timestamp = "CREATED_AT_" . gmdate('Y-m-d_H-i-s') . "\n";
        $padding_size = max(0, self::FILE_SIZE - strlen($header) - strlen($timestamp) - 1);
        $padding = str_repeat('X', $padding_size) . "\n";
        
        return $header . $timestamp . $padding;
    }

    /**
     * Cleanup test directory and all files
     */
    private static function cleanup_test_directory() {
        if (!self::$test_dir || !is_dir(self::$test_dir)) {
            return;
        }
        
        try {
            // Remove all files in test directory
            // BENCHMARK REQUIREMENT - Direct filesystem listing for cleanup of I/O benchmark artifacts
            // phpcs:ignore WordPress.WP.AlternativeFunctions.directory_glob
            $files = glob(self::$test_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    // BENCHMARK REQUIREMENT - Direct file delete used for cleanup after benchmark
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
                    unlink($file);
                }
            }
            
            // Remove the directory
            // BENCHMARK REQUIREMENT - Direct directory removal for cleanup after benchmark
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
            rmdir(self::$test_dir);
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("DiveWP File Cleanup Error: " . $e->getMessage());
            }
        }
        
        self::$test_dir = null;
    }

    /**
     * Calculate performance score
     *
     * @param array $result Test results
     * @return float Score from 0 to 100
     */
    private static function calculate_score($result) {
        if ($result['status'] !== 'completed' || $result['operations_completed'] === 0) {
            return 0;
        }
        
        $success_rate = ($result['successful_operations'] / $result['operations_completed']) * 100;
        $ops_per_second = $result['operations_per_second'];
        $efficiency = $result['file_efficiency'];
        
        // Base score from success rate (60% weight)
        $success_score = $success_rate * 0.6;
        
        // Operations per second score (25% weight)
        $speed_score = 0;
        if ($ops_per_second >= 80) {
            $speed_score = 25; // Excellent
        } elseif ($ops_per_second >= 60) {
            $speed_score = 20; // Good
        } elseif ($ops_per_second >= 40) {
            $speed_score = 15; // Fair
        } elseif ($ops_per_second >= 20) {
            $speed_score = 10; // Poor
        } else {
            $speed_score = 5; // Critical
        }
        
        // Efficiency bonus (15% weight)
        $efficiency_score = ($efficiency / 100) * 15;
        
        $final_score = $success_score + $speed_score + $efficiency_score;
        
        // Penalty for errors
        if ($result['failed_operations'] > 0) {
            $error_penalty = ($result['failed_operations'] / $result['operations_completed']) * 20;
            $final_score = max(0, $final_score - $error_penalty);
        }
        
        return round(max(0, min(100, $final_score)), 2);
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
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 60) {
            return 'fair';
        } elseif ($score >= 40) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Get interpretation text for the result
     *
     * @param array $result Test results
     * @return string Interpretation text
     */
    private static function get_interpretation($result) {
        if ($result['status'] !== 'completed') {
            return esc_html__('File concurrency test could not be completed.', 'divewp-boost-site-performance');
        }
        
        $score = $result['score'];
        $ops_per_second = $result['operations_per_second'];
        $success_rate = ($result['successful_operations'] / $result['operations_completed']) * 100;
        
        if ($score >= 90) {
            return sprintf(
                // translators: %1$d is the number of successful file operations, %2$.1f is operations per second performance metric, %3$.1f is the success rate percentage
                esc_html__('Excellent filesystem performance! Completed %1$d file operations at %2$.1f ops/sec with %3$.1f%% success rate.', 'divewp-boost-site-performance'),
                absint($result['successful_operations']),
                number_format_i18n($ops_per_second, 1),
                number_format_i18n($success_rate, 1)
            );
        } elseif ($score >= 75) {
            return sprintf(
                // translators: %1$.1f is operations per second performance metric, %2$d is write operations count, %3$d is read operations count, %4$d is delete operations count
                esc_html__('Good file I/O performance under load. %1$.1f ops/sec with %2$d writes, %3$d reads, %4$d deletes.', 'divewp-boost-site-performance'),
                number_format_i18n($ops_per_second, 1),
                absint($result['write_operations']),
                absint($result['read_operations']),
                absint($result['delete_operations'])
            );
        } elseif ($score >= 60) {
            return sprintf(
                // translators: %1$d is the number of successful operations completed, %2$.1f is the low operations per second performance metric
                esc_html__('Fair filesystem concurrency. %1$d operations completed but only %2$.1f ops/sec. Consider I/O optimization.', 'divewp-boost-site-performance'),
                absint($result['successful_operations']),
                number_format_i18n($ops_per_second, 1)
            );
        } elseif ($score >= 40) {
            return sprintf(
                // translators: %1$d is the number of failed file operations, %2$d is the total operations completed during testing
                esc_html__('Poor file I/O performance. %1$d/%2$d operations failed. Filesystem may struggle under concurrent load.', 'divewp-boost-site-performance'),
                absint($result['failed_operations']),
                absint($result['operations_completed'])
            );
        } else {
            return sprintf(
                // translators: %1$.1f is the very low operations per second metric indicating critical filesystem performance issues
                esc_html__('Critical filesystem issues. Very low performance (%1$.1f ops/sec) indicates severe I/O limitations.', 'divewp-boost-site-performance'),
                number_format_i18n($ops_per_second, 1)
            );
        }
    }
} 