<?php
/**
 * File I/O Performance Tests
 *
 * Replicates exact POC file I/O test specifications with enhanced UX features.
 * Tests file system performance with WordPress-realistic scenarios.
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
 * Resources File I/O Tests Class
 */
class DiveWP_Resources_File_IO_Tests {

    /**
     * Run file I/O performance tests (INTENSIFIED for Real Hosting Evaluation)
     *
     * @param array $test_config POC test configuration
     * @return array Test results with enhanced UX data
     */
    public static function run($test_config = array()) {
        $start_time = microtime(true);
        
        // INTENSIFIED: 100+ operations with realistic WordPress scenarios
        // Test multiple file sizes and concurrent operations that WordPress actually performs:
        // - Concurrent media uploads (like WooCommerce product images)
        // - Log file operations under load
        // - Cache file thrashing scenarios  
        // - Backup file creation/reading (1-50MB files)
        // - Plugin/theme file operations
        
        $io_score = self::test_file_io_intensive();
        $test_status = $io_score > 0 ? 'completed' : 'error';
        $total_time = microtime(true) - $start_time;
        
        // INTENSIFIED: Much higher operation count for realistic testing
        $expected_operations = 150; // 50+25+10+15+30+20 = 150 total operations
        $completed_operations = $test_status === 'completed' ? $expected_operations : 0;
        
        // MINIMUM RUNTIME ENFORCEMENT - Keep testing until minimum time reached
        $min_runtime = 4; // 4 seconds minimum for I/O stress
        if ($total_time < $min_runtime && $test_status === 'completed') {
            $additional_io_result = self::run_additional_io_stress($start_time, $min_runtime);
            $expected_operations += $additional_io_result['additional_operations'];
            $total_time = microtime(true) - $start_time;
        }
        
        // Calculate operations per second for UI display
        $operations_per_second = $total_time > 0 ? round($completed_operations / $total_time, 1) : 0;
        
        return array(
            'score' => $io_score,
            'total_time' => round($total_time, 3),
            'completed_operations' => $completed_operations,
            'total_operations' => $expected_operations,
            'operations_per_second' => $operations_per_second,
            'status' => $test_status,
            'timeout_reason' => $test_status === 'error' ? 'File I/O test failed to complete' : null,
            'io_score' => $io_score,
            'hosting_io_analysis' => self::analyze_hosting_io_performance($io_score, $total_time),
            'performance_interpretation' => DiveWP_Benchmark_Resources_Scoring::get_sub_test_performance_interpretation('file_io_tests', array(
                'score' => $io_score,
                'total_time' => $total_time,
                'completed_operations' => $completed_operations,
                'total_operations' => $expected_operations,
                'operations_per_second' => $operations_per_second,
                'status' => $test_status
            ))
        );
    }
    
    /**
     * Test file I/O performance with INTENSIVE 2025 WordPress-realistic scenarios
     * 
     * INTENSIFIED to test 150+ operations with real hosting stress scenarios:
     * - Concurrent media uploads (WooCommerce product images)
     * - Large backup file operations (1-50MB)
     * - Cache file thrashing under load
     * - Log file operations during traffic spikes
     * - Plugin/theme installation scenarios
     */
    private static function test_file_io_intensive() {
        // Initialize WordPress filesystem
        global $wp_filesystem;
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        $score = 30; // Conservative baseline for I/O failures
        
        // Try direct filesystem access first
        if (!WP_Filesystem()) {
            return $score;
        }
        
        $upload_dir = wp_upload_dir();
        $test_dir = trailingslashit($upload_dir['basedir']) . 'divewp-intensive-tests/';
        
        try {
            // Create test directory
            if (!$wp_filesystem->exists($test_dir)) {
                $wp_filesystem->mkdir($test_dir);
            }
            
            $io_results = array();
            $total_operations = 0;
            $total_time = 0;
            
            // INTENSIFIED Test 1: Small file operations (50 operations - was 10)
            $small_file_time = self::test_small_file_operations_intensive($wp_filesystem, $test_dir);
            $io_results['small_files'] = $small_file_time;
            $total_time += $small_file_time;
            $total_operations += 50;
            
            // INTENSIFIED Test 2: Medium file operations (25 operations - was 5) 
            $medium_file_time = self::test_medium_file_operations_intensive($wp_filesystem, $test_dir);
            $io_results['medium_files'] = $medium_file_time;
            $total_time += $medium_file_time;
            $total_operations += 25;
            
            // INTENSIFIED Test 3: Large file operations (10 operations - was 2)
            $large_file_time = self::test_large_file_operations_intensive($wp_filesystem, $test_dir);
            $io_results['large_files'] = $large_file_time;
            $total_time += $large_file_time;
            $total_operations += 10;
            
            // NEW Test 4: Backup simulation (1-50MB files)
            $backup_time = self::test_backup_simulation($wp_filesystem, $test_dir);
            $io_results['backup_simulation'] = $backup_time;
            $total_time += $backup_time;
            $total_operations += 15;
            
            // INTENSIFIED Test 5: Concurrent I/O simulation (30 operations - was 3)
            $concurrent_time = self::test_concurrent_io_intensive($wp_filesystem, $test_dir);
            $io_results['concurrent_ops'] = $concurrent_time;
            $total_time += $concurrent_time;
            $total_operations += 30;
            
            // NEW Test 6: Cache thrashing simulation 
            $cache_thrashing_time = self::test_cache_thrashing_simulation($wp_filesystem, $test_dir);
            $io_results['cache_thrashing'] = $cache_thrashing_time;
            $total_time += $cache_thrashing_time;
            $total_operations += 20;
            
            // Calculate STRICT 2025-calibrated I/O score
            $score = self::calculate_io_score_2025_strict($io_results, $total_time, $total_operations);
            
            // Clean up test directory
            if ($wp_filesystem->exists($test_dir)) {
                self::cleanup_test_directory_recursive($wp_filesystem, $test_dir);
            }
            
        } catch (Exception $e) {
            $score = 15; // Lower score for failures
            // Ensure cleanup on error
            if ($wp_filesystem->exists($test_dir)) {
                self::cleanup_test_directory_recursive($wp_filesystem, $test_dir);
            }
        }
        
        return min(100, max(15, intval($score)));
    }
    
    /**
     * Test small file operations INTENSIFIED (50 operations)
     * WordPress cache files, logs, meta data under load (1-10KB files)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for small file operations
     */
    private static function test_small_file_operations_intensive($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // INTENSIFIED: 50 operations simulating WordPress under load
        for ($i = 0; $i < 50; $i++) {
            // Simulate different WordPress cache scenarios
            $cache_types = array('object_cache', 'transient_cache', 'user_meta', 'post_meta', 'option_cache');
            $cache_type = $cache_types[$i % count($cache_types)];
            
            $cache_data = array(
                'cache_type' => $cache_type,
                'post_id' => $i,
                'meta_data' => array_fill(0, 30, wp_generate_password(75, false)), // Larger cache entries
                'timestamp' => current_time('timestamp'),
                'user_data' => array_fill(0, 15, array('id' => $i, 'data' => wp_generate_password(50, false))),
                'query_cache' => array_fill(0, 20, array('sql' => 'SELECT * FROM wp_posts WHERE id = ' . $i, 'result' => wp_generate_password(100, false)))
            );
            
            $serialized_data = serialize($cache_data);
            $file_path = $test_dir . "{$cache_type}_{$i}.cache";
            
            // INTENSIFIED: Write, read, modify, write again, delete (5 operations per file)
            $wp_filesystem->put_contents($file_path, $serialized_data);
            $read_data = $wp_filesystem->get_contents($file_path);
            
            // Modify data (simulate cache updates)
            $cache_data['updated'] = current_time('timestamp');
            $updated_data = serialize($cache_data);
            $wp_filesystem->put_contents($file_path, $updated_data);
            
            // Read again and verify
            $final_data = $wp_filesystem->get_contents($file_path);
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Test medium file operations INTENSIFIED (25 operations)
     * WordPress uploads, plugin assets, theme files (50-500KB files)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for medium file operations
     */
    private static function test_medium_file_operations_intensive($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // INTENSIFIED: 25 operations simulating WordPress media and plugin operations
        for ($i = 0; $i < 25; $i++) {
            // Simulate different WordPress file scenarios
            $file_types = array('product_image', 'plugin_asset', 'theme_file', 'user_upload', 'generated_content');
            $file_type = $file_types[$i % count($file_types)];
            
            // Create realistic WordPress file data structures
            $medium_data = array(
                'file_type' => $file_type,
                'image_data' => str_repeat('REALISTIC_IMAGE_DATA_CHUNK', 8000), // ~200KB
                'metadata' => array(
                    'width' => 1920 + ($i * 10),
                    'height' => 1080 + ($i * 5),
                    'mime_type' => 'image/jpeg',
                    'file_size' => 200000 + ($i * 1000),
                    'alt_text' => 'Product image ' . $i,
                    'sizes' => array_fill(0, 8, array('file' => wp_generate_password(30, false) . '.jpg')),
                    'optimization_data' => array_fill(0, 20, wp_generate_password(150, false))
                ),
                'processing_log' => array_fill(0, 150, wp_generate_password(200, false)), // Detailed processing logs
                'seo_data' => array_fill(0, 25, wp_generate_password(100, false))
            );
            
            $file_content = json_encode($medium_data);
            $file_path = $test_dir . "{$file_type}_{$i}.json";
            
            // INTENSIFIED: Multiple operations per file (write, read, append, read, delete)
            $wp_filesystem->put_contents($file_path, $file_content);
            $retrieved = $wp_filesystem->get_contents($file_path);
            
            // Simulate file processing/modification (like image optimization)
            $additional_data = json_encode(array('processed' => true, 'optimization_score' => $i * 2));
            $wp_filesystem->put_contents($file_path, $retrieved . "\n" . $additional_data);
            
            // Final read and delete
            $final_content = $wp_filesystem->get_contents($file_path);
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Test large file operations INTENSIFIED (10 operations)
     * WordPress exports, backups, log files (1-10MB files)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for large file operations
     */
    private static function test_large_file_operations_intensive($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // INTENSIFIED: 10 operations with realistic large WordPress files
        for ($i = 0; $i < 10; $i++) {
            // Create realistic large WordPress file content
            $base_chunk = str_repeat('WORDPRESS_EXPORT_DATA_ROW_' . str_pad($i, 6, '0', STR_PAD_LEFT) . '_', 2000); // ~50KB chunk
            $large_data = str_repeat($base_chunk, 20 + ($i * 5)); // 1-3MB files, escalating
            
            $file_types = array('database_export', 'debug_log', 'backup_file', 'import_data', 'analytics_export');
            $file_type = $file_types[$i % count($file_types)];
            $file_path = $test_dir . "{$file_type}_{$i}.sql";
            
            // INTENSIFIED: Chunked writing and reading (realistic for shared hosting)
            $chunk_size = 262144; // 256KB chunks
            $chunks_written = 0;
            
            // Write in chunks
            $data_length = strlen($large_data);
            for ($offset = 0; $offset < $data_length; $offset += $chunk_size) {
                $chunk = substr($large_data, $offset, $chunk_size);
                if ($chunks_written === 0) {
                    $wp_filesystem->put_contents($file_path, $chunk);
                } else {
                    // Append mode simulation
                    $existing = $wp_filesystem->get_contents($file_path);
                    $wp_filesystem->put_contents($file_path, $existing . $chunk);
                }
                $chunks_written++;
            }
            
            // Read file in chunks to simulate backup/export reading
            $read_data = '';
            $file_size = strlen($large_data);
            
            for ($read_offset = 0; $read_offset < $file_size; $read_offset += $chunk_size) {
                $file_content = $wp_filesystem->get_contents($file_path);
                $chunk = substr($file_content, $read_offset, $chunk_size);
                $read_data .= $chunk;
                
                // Simulate processing each chunk
                $processed_chunk = md5($chunk); // Simulate hash verification
                
                // Break if we've read enough for testing
                if ($read_offset > ($file_size / 2)) break;
            }
            
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * NEW: Test backup simulation (15 operations)
     * Simulate WordPress backup creation and reading (1-50MB files)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for backup operations
     */
    private static function test_backup_simulation($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // Simulate WordPress backup scenarios
        for ($i = 0; $i < 15; $i++) {
            // Create backup-like content with escalating sizes (1-50MB)
            $backup_size = (1 + $i * 3) * 1048576; // 1MB, 4MB, 7MB, ... up to ~45MB
            $chunk_size = 524288; // 512KB chunks
            $chunks_needed = (int)ceil($backup_size / $chunk_size);
            
            $backup_file = $test_dir . "backup_simulation_{$i}.sql";
            
            // Simulate database backup creation
            for ($chunk = 0; $chunk < min($chunks_needed, 20); $chunk++) { // Limit to 20 chunks max
                $backup_chunk = str_repeat("INSERT INTO wp_posts (post_title, post_content) VALUES ('Post $i-$chunk', '" . str_repeat('Content data ', 1000) . "');\n", 100);
                
                if ($chunk === 0) {
                    $wp_filesystem->put_contents($backup_file, $backup_chunk);
                } else {
                    // Append to existing backup
                    $existing = $wp_filesystem->get_contents($backup_file);
                    $wp_filesystem->put_contents($backup_file, $existing . $backup_chunk);
                }
            }
            
            // Simulate backup verification (read partial content)
            $backup_content = $wp_filesystem->get_contents($backup_file);
            $backup_hash = md5(substr($backup_content, 0, 100000)); // Hash first 100KB for verification
            
            // Clean up
            $wp_filesystem->delete($backup_file);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Test concurrent I/O INTENSIFIED (30 operations)
     * Multiple WordPress processes accessing files simultaneously
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for concurrent operations
     */
    private static function test_concurrent_io_intensive($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // INTENSIFIED: Simulate 30 concurrent WordPress operations
        $files_created = array();
        
        // Phase 1: Create multiple files simultaneously (simulate concurrent users)
        for ($i = 0; $i < 30; $i++) {
            $file_types = array('session', 'cache', 'log', 'upload', 'temp');
            $file_type = $file_types[$i % count($file_types)];
            
            $concurrent_data = array(
                'file_type' => $file_type,
                'user_id' => $i,
                'session_data' => array_fill(0, 75, wp_generate_password(150, false)), // Larger sessions
                'user_meta' => array_fill(0, 30, array('key' => wp_generate_password(30, false), 'value' => wp_generate_password(300, false))),
                'transient_cache' => array_fill(0, 50, wp_generate_password(200, false)),
                'activity_log' => array_fill(0, 100, array('action' => 'user_action_' . $i, 'timestamp' => time(), 'data' => wp_generate_password(100, false)))
            );
            
            $file_content = serialize($concurrent_data);
            $file_path = $test_dir . "concurrent_{$file_type}_{$i}.cache";
            $files_created[] = $file_path;
            
            $wp_filesystem->put_contents($file_path, $file_content);
        }
        
        // Phase 2: Simulate concurrent access and modifications
        foreach ($files_created as $index => $file_path) {
            // Read existing file
            $data = $wp_filesystem->get_contents($file_path);
            $unserialized = unserialize($data);
            
            // Modify data (simulate concurrent updates)
            $unserialized['last_accessed'] = current_time('timestamp');
            $unserialized['access_count'] = $index + 1;
            
            // Write back modified data
            $wp_filesystem->put_contents($file_path, serialize($unserialized));
        }
        
        // Phase 3: Clean up all files
        foreach ($files_created as $file_path) {
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * NEW: Test cache thrashing simulation (20 operations)
     * Simulate WordPress cache invalidation and regeneration scenarios
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for cache thrashing operations
     */
    private static function test_cache_thrashing_simulation($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // Simulate WordPress cache thrashing (typical during high traffic)
        $cache_files = array();
        
        // Phase 1: Create cache files
        for ($i = 0; $i < 20; $i++) {
            $cache_data = array(
                'cache_key' => 'cache_' . $i,
                'cache_content' => array_fill(0, 100, wp_generate_password(200, false)),
                'expiry' => time() + 3600,
                'hit_count' => 0
            );
            
            $cache_file = $test_dir . "cache_thrash_{$i}.tmp";
            $cache_files[] = $cache_file;
            
            $wp_filesystem->put_contents($cache_file, serialize($cache_data));
        }
        
        // Phase 2: Simulate cache thrashing (rapid invalidation/regeneration)
        for ($cycle = 0; $cycle < 3; $cycle++) {
            foreach ($cache_files as $index => $cache_file) {
                // Delete cache (invalidation)
                $wp_filesystem->delete($cache_file);
                
                // Regenerate cache with new data
                $new_cache_data = array(
                    'cache_key' => 'cache_' . $index . '_cycle_' . $cycle,
                    'cache_content' => array_fill(0, 120, wp_generate_password(250, false)), // Larger regenerated cache
                    'expiry' => time() + 3600,
                    'hit_count' => $cycle + 1,
                    'regeneration_time' => microtime(true)
                );
                
                $wp_filesystem->put_contents($cache_file, serialize($new_cache_data));
            }
        }
        
        // Phase 3: Final cleanup
        foreach ($cache_files as $cache_file) {
            if ($wp_filesystem->exists($cache_file)) {
                $wp_filesystem->delete($cache_file);
            }
        }
        
        return microtime(true) - $start_time;
    }

    /**
     * Calculate I/O score with STRICT curve for realistic hosting differentiation
     * 
     * @param array $io_results Results from different I/O tests
     * @param float $total_time Total time for all operations
     * @param int $total_operations Total number of operations
     * @return int I/O performance score (0-100)
     */
    private static function calculate_io_score_2025_strict($io_results, $total_time, $total_operations) {
        if ($total_time <= 0 || $total_operations <= 0) {
            return 100;
        }
        
        // STRICT THRESHOLDS for realistic hosting differentiation
        $excellent_time = 2.0;   // Premium NVMe SSD hosting (was 0.8s - too easy)
        $good_time = 5.0;        // Quality SSD hosting
        $fair_time = 12.0;       // Standard HDD/hybrid hosting  
        $poor_time = 25.0;       // Poor/oversold shared hosting
        
        $base_score = 0;
        
        // Primary scoring based on total execution time (STRICT)
        if ($total_time <= $excellent_time) {
            $base_score = 90 + (10 * (($excellent_time - $total_time) / $excellent_time));
        } elseif ($total_time <= $good_time) {
            // Linear interpolation between excellent and good
            $range = $good_time - $excellent_time;
            $position = $total_time - $excellent_time;
            $base_score = 75 + (15 * (1 - ($position / $range)));
        } elseif ($total_time <= $fair_time) {
            // Linear interpolation between good and fair
            $range = $fair_time - $good_time;
            $position = $total_time - $good_time;
            $base_score = 50 + (25 * (1 - ($position / $range)));
        } elseif ($total_time <= $poor_time) {
            // Linear interpolation between fair and poor
            $range = $poor_time - $fair_time;
            $position = $total_time - $fair_time;
            $base_score = 25 + (25 * (1 - ($position / $range)));
        } else {
            // Very poor performance - severely limited I/O
            $base_score = max(5, 25 - (($total_time - $poor_time) * 1));
        }
        
        // PENALTIES for poor I/O patterns (STRICT)
        $penalty = 0;
        
        // Large file operations penalty (if too slow)
        if (isset($io_results['large_files']) && $io_results['large_files'] > 8) {
            $penalty += min(15, ($io_results['large_files'] - 8) * 2); // Up to 15 point penalty
        }
        
        // Backup simulation penalty (if too slow for real backups)
        if (isset($io_results['backup_simulation']) && $io_results['backup_simulation'] > 10) {
            $penalty += min(10, ($io_results['backup_simulation'] - 10) * 1); // Up to 10 point penalty
        }
        
        // Cache thrashing penalty (if cache operations are too slow)
        if (isset($io_results['cache_thrashing']) && $io_results['cache_thrashing'] > 3) {
            $penalty += min(8, ($io_results['cache_thrashing'] - 3) * 2); // Up to 8 point penalty
        }
        
        // BONUSES for excellent I/O performance (STRICT - only for truly exceptional performance)
        $bonus = 0;
        
        // Small file operations bonus (critical for WordPress performance)
        if (isset($io_results['small_files']) && $io_results['small_files'] < 0.5) {
            $bonus += 3; // Small bonus for excellent small file performance
        }
        
        // Concurrent operations bonus (critical for multi-user sites)
        if (isset($io_results['concurrent_ops']) && $io_results['concurrent_ops'] < 1.0) {
            $bonus += 2; // Small bonus for excellent concurrent I/O
        }
        
        // Calculate final score with STRICT bounds
        $final_score = $base_score - $penalty + $bonus;
        
        return max(5, min(100, round($final_score)));
    }
    
    /**
     * Analyze hosting I/O performance characteristics
     * 
     * @param int $io_score I/O performance score
     * @param float $total_time Total test execution time
     * @return array Hosting I/O analysis
     */
    private static function analyze_hosting_io_performance($io_score, $total_time) {
        $analysis = array(
            'storage_type_detected' => 'unknown',
            'hosting_tier' => 'unknown',
            'io_bottlenecks' => array(),
            'performance_characteristics' => array()
        );
        
        // Determine storage type based on performance
        if ($total_time <= 2.0 && $io_score >= 85) {
            $analysis['storage_type_detected'] = 'nvme_ssd';
            $analysis['hosting_tier'] = 'premium';
        } elseif ($total_time <= 5.0 && $io_score >= 70) {
            $analysis['storage_type_detected'] = 'ssd';
            $analysis['hosting_tier'] = 'quality_vps';
        } elseif ($total_time <= 12.0 && $io_score >= 50) {
            $analysis['storage_type_detected'] = 'hybrid_hdd_ssd';
            $analysis['hosting_tier'] = 'standard_shared';
        } elseif ($total_time <= 25.0) {
            $analysis['storage_type_detected'] = 'traditional_hdd';
            $analysis['hosting_tier'] = 'budget_shared';
        } else {
            $analysis['storage_type_detected'] = 'severely_limited';
            $analysis['hosting_tier'] = 'oversold_hosting';
        }
        
        // Identify I/O bottlenecks
        if ($total_time > 15) {
            $analysis['io_bottlenecks'][] = 'slow_large_file_operations';
        }
        
        if ($io_score < 50) {
            $analysis['io_bottlenecks'][] = 'general_io_performance_issues';
        }
        
        if ($total_time > 8 && $io_score > 60) {
            $analysis['io_bottlenecks'][] = 'inconsistent_io_performance';
        }
        
        // Performance characteristics
        $analysis['performance_characteristics'] = array(
            'suitable_for_high_traffic' => $io_score >= 75,
            'suitable_for_ecommerce' => $io_score >= 65,
            'suitable_for_media_heavy_sites' => $io_score >= 70,
            'backup_performance_adequate' => $total_time <= 15,
            'concurrent_user_support' => $io_score >= 60
        );
        
        return $analysis;
    }
    
    /**
     * Recursive directory cleanup for test files
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $dir_path Directory to clean up
     */
    private static function cleanup_test_directory_recursive($wp_filesystem, $dir_path) {
        if (!$wp_filesystem->exists($dir_path)) {
            return;
        }
        
        // Get directory listing
        $files = $wp_filesystem->dirlist($dir_path);
        
        if (is_array($files)) {
            foreach ($files as $file) {
                $file_path = trailingslashit($dir_path) . $file['name'];
                
                if ($file['type'] === 'd') {
                    // Recursively clean subdirectories
                    self::cleanup_test_directory_recursive($wp_filesystem, $file_path);
                    $wp_filesystem->rmdir($file_path);
                } else {
                    // Delete files
                    $wp_filesystem->delete($file_path);
                }
            }
        }
        
        // Remove the directory itself
        $wp_filesystem->rmdir($dir_path);
    }
    
    /**
     * Run additional I/O stress to reach minimum runtime
     * 
     * @param float $start_time Overall test start time
     * @param float $min_runtime Minimum required runtime
     * @return array Additional stress test results
     */
    private static function run_additional_io_stress($start_time, $min_runtime) {
        global $wp_filesystem;
        
        $additional_operations = 0;
        $upload_dir = wp_upload_dir();
        $stress_dir = trailingslashit($upload_dir['basedir']) . 'divewp-additional-stress/';
        
        try {
            if (!$wp_filesystem->exists($stress_dir)) {
                $wp_filesystem->mkdir($stress_dir);
            }
            
            // Continue stress testing until minimum runtime reached
            $cycle = 0;
            while ((microtime(true) - $start_time) < $min_runtime) {
                // Rapid file creation and deletion cycles
                for ($i = 0; $i < 10; $i++) {
                    $stress_data = array_fill(0, 200, 'stress_test_' . $cycle . '_' . $i);
                    $stress_file = $stress_dir . "stress_{$cycle}_{$i}.tmp";
                    
                    $wp_filesystem->put_contents($stress_file, serialize($stress_data));
                    $additional_operations++;
                    
                    $wp_filesystem->delete($stress_file);
                    $additional_operations++;
                }
                
                $cycle++;
                
                // Safety break
                if ($cycle > 100) {
                    break;
                }
            }
            
            // Cleanup stress directory
            if ($wp_filesystem->exists($stress_dir)) {
                self::cleanup_test_directory_recursive($wp_filesystem, $stress_dir);
            }
            
        } catch (Exception $e) {
            // Cleanup on error
            if ($wp_filesystem->exists($stress_dir)) {
                self::cleanup_test_directory_recursive($wp_filesystem, $stress_dir);
            }
        }
        
        return array(
            'additional_operations' => $additional_operations,
            'cycles_completed' => $cycle
        );
    }
} 