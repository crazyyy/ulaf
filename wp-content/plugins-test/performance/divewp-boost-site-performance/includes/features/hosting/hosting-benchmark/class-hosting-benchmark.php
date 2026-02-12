<?php
/**
 * Hosting Benchmark Main Class
 *
 * Handles the main benchmark feature UI and orchestration.
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
 * DiveWP Hosting Benchmark Class
 */
class DiveWP_Hosting_Benchmark {

    /**
     * Test controllers
     */
    private $test_controllers = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->ensure_database_ready();
        $this->load_dependencies();
        $this->init_ajax_handlers();
    }

    /**
     * Ensure database tables are ready for benchmark feature
     */
    private function ensure_database_ready() {
        // Load database class if not already loaded
        if (!class_exists('DiveWP_Database')) {
            require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-database.php';
        }
        
        // Verify tables exist, create if needed (fallback for edge cases)
        if (!DiveWP_Database::verify_tables()) {
            DiveWP_Database::init_tables();
        }
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load AJAX handlers
        require_once __DIR__ . '/ajax-handlers.php';
        
        // Load test controllers
        require_once __DIR__ . '/tests/performance/class-performance-tests.php';
        require_once __DIR__ . '/tests/performance/scoring.php';
        
        // Initialize controllers
        $this->test_controllers['performance'] = new DiveWP_Benchmark_Performance_Tests();
    }

    /**
     * Initialize AJAX handlers
     */
    private function init_ajax_handlers() {
        // AJAX handlers are initialized in ajax-handlers.php
    }

    /**
     * Render the hosting benchmark UI
     */
    public function render() {
        // Generate nonce for AJAX requests
        $nonce = wp_create_nonce('divewp_benchmark_nonce');
        ?>
        <div class="divewp-section divewp-hosting-benchmark">
            <h4><span class="dashicons dashicons-performance" style="vertical-align: middle; margin-right: 5px;"></span><?php esc_html_e('Hosting Performance Benchmark', 'divewp-boost-site-performance'); ?></h4>

            <!-- Hero Section (initial page only) -->
            <div class="benchmark-hero">
                <div class="hero-content">
                    <p class="hero-title"><?php esc_html_e('Measure how your hosting handles your WordPress site! Do you need to upgrade or not?', 'divewp-boost-site-performance'); ?></p>
<p class="hero-description">
    <?php esc_html_e('This benchmark measures how your hosting handles your WordPress site. It runs for approximately 6 minutes. The tool is designed to evaluate if your site and its setup are performing optimally on your current server, not to compare different hosting providers.', 'divewp-boost-site-performance'); ?>
</p>
                    <div class="benchmark-controls hero-controls">
                        <button class="benchmark-launch-btn divewp-button">
                            <?php esc_html_e('Launch Benchmark', 'divewp-boost-site-performance'); ?>
                        </button>
                        <button class="benchmark-settings-toggle divewp-button">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <?php esc_html_e('Settings', 'divewp-boost-site-performance'); ?>
                        </button>
                    </div>

                    <div class="hero-hint"><?php esc_html_e('Typical duration: 4–6 minutes. You can continue working while it runs.', 'divewp-boost-site-performance'); ?></div>

                    <div class="benchmark-status" style="display: none;">
                        <span class="status-indicator"></span>
                        <span class="status-text"></span>
                    </div>
                </div>
            </div>

            <!-- Score legend & explanation -->
            <div class="benchmark-score-legend">
                <h5><?php esc_html_e('How the score works', 'divewp-boost-site-performance'); ?></h5>
                <p class="score-legend-desc"><?php esc_html_e('Scores range from 0–100 and reflect speed, reliability, and consistency across performance, database, resources, and concurrency tests.', 'divewp-boost-site-performance'); ?></p>
                <ul class="score-legend-list">
                    <li><span class="score-pill excellent"><?php esc_html_e('EXCELLENT', 'divewp-boost-site-performance'); ?></span><span class="score-range">90–100</span></li>
                    <li><span class="score-pill good"><?php esc_html_e('GOOD', 'divewp-boost-site-performance'); ?></span><span class="score-range">70–89</span></li>
                    <li><span class="score-pill fair"><?php esc_html_e('FAIR', 'divewp-boost-site-performance'); ?></span><span class="score-range">50–69</span></li>
                    <li><span class="score-pill poor"><?php esc_html_e('POOR', 'divewp-boost-site-performance'); ?></span><span class="score-range">30–49</span></li>
                    <li><span class="score-pill critical"><?php esc_html_e('CRITICAL', 'divewp-boost-site-performance'); ?></span><span class="score-range">0–29</span></li>
                </ul>
            </div>

            <div class="benchmark-container">
                <?php $this->render_settings_panel(); ?>

                <div class="benchmark-results">
                    <div class="results-placeholder">
                        <p><?php esc_html_e('Run the benchmark to see your hosting performance results.', 'divewp-boost-site-performance'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        /* Localize AJAX data */
        var divewp_ajax = {
            ajax_url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            nonce: '<?php echo esc_attr($nonce); ?>',
            plugin_url: '<?php echo esc_url(DIVEWP_PLUGIN_URL); ?>'
        };
        </script>
        <?php
    }

    /**
     * Render the saved benchmarks section (previous results)
     */
    public function render_saved_benchmarks() {
        ?>
        <div class="saved-benchmarks-section">
            <div class="saved-benchmarks-header">
                <h5>
                    <span class="dashicons dashicons-clock" style="vertical-align: middle; margin-right: 5px;"></span>
                    <?php esc_html_e('Previous Benchmark Results', 'divewp-boost-site-performance'); ?>
                </h5>
                <div class="saved-benchmarks-actions">
                    <button class="refresh-saved-benchmarks button" id="divewp-refresh-benchmarks">
                        <?php esc_html_e('Refresh', 'divewp-boost-site-performance'); ?>
                    </button>
                    <button class="delete-all-benchmarks button" id="divewp-delete-all-benchmarks">
                        <?php esc_html_e('Delete All', 'divewp-boost-site-performance'); ?>
                    </button>
                </div>
            </div>
            
            <div class="saved-benchmarks-list">
                <div class="loading-saved-benchmarks">
                    <span class="dashicons dashicons-update-alt" style="animation: rotation 1s infinite linear;"></span>
                    <?php esc_html_e('Loading saved benchmarks...', 'divewp-boost-site-performance'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the settings panel
     */
    private function render_settings_panel() {
        ?>
        <div class="benchmark-settings" style="display: none;">
            <div class="settings-header">
                <h3>
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Benchmark Configuration', 'divewp-boost-site-performance'); ?>
                </h3>
                <p class="settings-description">
                    <?php esc_html_e('Configure which tests to run and customize benchmark behavior. Only select the tests you need for faster results.', 'divewp-boost-site-performance'); ?>
                </p>
            </div>
            
            <div class="settings-content">
                <!-- Test Categories Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <h4>
                            <span class="dashicons dashicons-list-view"></span>
                            <?php esc_html_e('Test Categories', 'divewp-boost-site-performance'); ?>
                        </h4>
                        <p class="section-description">
                            <?php esc_html_e('Select which test categories to include in your benchmark run.', 'divewp-boost-site-performance'); ?>
                        </p>
                    </div>
                    
                    <div class="test-categories-grid">
                        <?php $this->render_test_category_cards(); ?>
                    </div>
                </div>

                <!-- Advanced Settings Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <h4>
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php esc_html_e('Advanced Settings', 'divewp-boost-site-performance'); ?>
                        </h4>
                        <p class="section-description">
                            <?php esc_html_e('Fine-tune benchmark behavior and performance parameters.', 'divewp-boost-site-performance'); ?>
                        </p>
                    </div>
                    
                    <div class="advanced-settings-grid">
                        <div class="setting-card">
                            <div class="setting-card-header">
                                <span class="dashicons dashicons-clock"></span>
                                <label for="test-delay"><?php esc_html_e('Test Interval', 'divewp-boost-site-performance'); ?></label>
                            </div>
                            <div class="setting-card-body">
                                <div class="input-group">
                                    <input type="number" id="test-delay" class="test-delay-setting" value="0" min="0" max="10">
                                    <span class="input-suffix"><?php esc_html_e('seconds', 'divewp-boost-site-performance'); ?></span>
                                </div>
                                <p class="setting-description">
                                    <?php esc_html_e('Time to wait after each individual sub‑test. Higher values reduce server load but increase total runtime.', 'divewp-boost-site-performance'); ?>
                                </p>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>

            <div class="settings-actions">
                <button class="divewp-button save-settings">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e('Apply Settings', 'divewp-boost-site-performance'); ?>
                </button>
                <button class="divewp-button reset-settings">
                    <span class="dashicons dashicons-undo"></span>
                    <?php esc_html_e('Reset to Defaults', 'divewp-boost-site-performance'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render test category cards with modern UI
     */
    private function render_test_category_cards() {
        $categories = array(
            'performance' => array(
                'name' => __('E‑commerce Performance', 'divewp-boost-site-performance'),
                'icon' => 'dashicons-performance',
                'status' => 'available',
                'description' => __('Test WooCommerce-style operations and processing speed', 'divewp-boost-site-performance'),
                'tests' => array(
                    'price_calculations' => array(
                        'name' => __('Price Calculations', 'divewp-boost-site-performance'),
                        'description' => __('Complex pricing logic and tax calculations', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'shipping_calculations' => array(
                        'name' => __('Shipping Calculations', 'divewp-boost-site-performance'),
                        'description' => __('Zone-based shipping and rate calculations', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'inventory_operations' => array(
                        'name' => __('Inventory Operations', 'divewp-boost-site-performance'),
                        'description' => __('Stock management and inventory updates', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    )
                )
            ),
            'database' => array(
                'name' => __('Database Tests', 'divewp-boost-site-performance'),
                'icon' => 'dashicons-database',
                'status' => 'available',
                'description' => __('Test database performance with INSERT, SELECT, UPDATE operations and MySQL functions', 'divewp-boost-site-performance'),
                'tests' => array(
                    'insert_operations' => array(
                        'name' => __('Data Creation (INSERT)', 'divewp-boost-site-performance'),
                        'description' => __('Adding new products and orders (500 records × 5 iterations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'select_operations' => array(
                        'name' => __('Data Retrieval (SELECT)', 'divewp-boost-site-performance'),
                        'description' => __('Product searches and listings (2,500 queries × 5 iterations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'update_operations' => array(
                        'name' => __('Data Updates (UPDATE)', 'divewp-boost-site-performance'),
                        'description' => __('Stock changes and modifications (10 updates × 5 iterations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'crypto_functions' => array(
                        'name' => __('Crypto Functions', 'divewp-boost-site-performance'),
                        'description' => __('Encryption and hash operations (1,000 operations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'math_functions' => array(
                        'name' => __('Math Functions', 'divewp-boost-site-performance'),
                        'description' => __('Mathematical calculations (5,000 operations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'string_functions' => array(
                        'name' => __('String Functions', 'divewp-boost-site-performance'),
                        'description' => __('Text processing operations (3,000 operations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'datetime_functions' => array(
                        'name' => __('DateTime Functions', 'divewp-boost-site-performance'),
                        'description' => __('Date and time operations (5,000 operations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'aggregate_functions' => array(
                        'name' => __('Aggregate Functions', 'divewp-boost-site-performance'),
                        'description' => __('SUM, COUNT, AVG operations (100 operations on 1,000 rows)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    )
                )
            ),
            'resources' => array(
                'name' => __('Resources Tests', 'divewp-boost-site-performance'),
                'icon' => 'dashicons-admin-tools',
                'status' => 'available',
                'description' => __('Test server resources: CPU, memory, I/O, and network', 'divewp-boost-site-performance'),
                'tests' => array(
                    'cpu_tests' => array(
                        'name' => __('CPU Tests', 'divewp-boost-site-performance'),
                        'description' => __('5 computational tests: prime generation, math operations, logic, strings, arrays', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'memory_tests' => array(
                        'name' => __('Memory Tests', 'divewp-boost-site-performance'),
                        'description' => __('Memory allocation efficiency with aggressive cleanup testing', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'file_io_tests' => array(
                        'name' => __('File I/O Tests', 'divewp-boost-site-performance'),
                        'description' => __('4 file operation types: small, medium, large, and concurrent files', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'network_tests' => array(
                        'name' => __('Network Tests', 'divewp-boost-site-performance'),
                        'description' => __('WordPress.org API connectivity and HTTP reliability testing', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'wordpress_tests' => array(
                        'name' => __('WordPress Tests', 'divewp-boost-site-performance'),
                        'description' => __('4 WordPress operations: shortcodes, hooks, caching, security functions', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    )
                )
            ),
            'concurrency' => array(
                'name' => __('Concurrency Tests', 'divewp-boost-site-performance'),
                'icon' => 'dashicons-networking',
                'status' => 'available',
                'description' => __('Test how your server handles multiple simultaneous operations', 'divewp-boost-site-performance'),
                'tests' => array(
                    'database_concurrency' => array(
                        'name' => __('Database Concurrency', 'divewp-boost-site-performance'),
                        'description' => __('Multiple database operations simultaneously (495 operations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'http_concurrency' => array(
                        'name' => __('HTTP Concurrency', 'divewp-boost-site-performance'),
                        'description' => __('Multiple HTTP requests handling (8 simultaneous requests)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'memory_concurrency' => array(
                        'name' => __('Memory Concurrency', 'divewp-boost-site-performance'),
                        'description' => __('Memory competition under load (96 memory processes)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    ),
                    'file_concurrency' => array(
                        'name' => __('File Concurrency', 'divewp-boost-site-performance'),
                        'description' => __('File system operations under load (320 file operations)', 'divewp-boost-site-performance'),
                        'status' => 'available'
                    )
                )
            )
        );

        foreach ($categories as $category_id => $category_data) {
            $is_available = $category_data['status'] === 'available';
            $card_class = 'test-category-card ' . ($is_available ? 'available' : 'unavailable');
            ?>
            <div class="<?php echo esc_attr($card_class); ?>" data-category="<?php echo esc_attr($category_id); ?>">
                <div class="category-card-header">
                    <div class="category-header-left">
                        <span class="category-icon <?php echo esc_attr($category_data['icon']); ?>"></span>
                        <div class="category-title-group">
                            <h5 class="category-title"><?php echo esc_html($category_data['name']); ?></h5>
                            <span class="category-status-badge status-<?php echo esc_attr($category_data['status']); ?>">
                                <?php echo $is_available ? esc_html__('Available', 'divewp-boost-site-performance') : esc_html__('Coming Soon', 'divewp-boost-site-performance'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="category-header-right">
                        <label class="category-toggle-wrapper">
                            <input type="checkbox" 
                                   class="category-master-toggle" 
                                   data-category="<?php echo esc_attr($category_id); ?>" 
                                   <?php checked($is_available); ?>
                                   <?php disabled(!$is_available); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="category-description">
                    <p><?php echo esc_html($category_data['description']); ?></p>
                </div>
                
                <div class="category-tests">
                    <div class="tests-header">
                        <span class="tests-count"><?php echo esc_html(count($category_data['tests'])); ?> <?php esc_html_e('tests', 'divewp-boost-site-performance'); ?></span>
                        <?php if ($is_available) : ?>
                            <button type="button" class="toggle-tests-detail" data-category="<?php echo esc_attr($category_id); ?>">
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <?php esc_html_e('Show Details', 'divewp-boost-site-performance'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_available) : ?>
                        <div class="tests-detail" id="tests-detail-<?php echo esc_attr($category_id); ?>" style="display: none;">
                            <?php foreach ($category_data['tests'] as $test_id => $test_data) : ?>
                                <label class="test-item">
                                    <input type="checkbox" 
                                           class="sub-test-toggle" 
                                           data-category="<?php echo esc_attr($category_id); ?>" 
                                           data-test="<?php echo esc_attr($test_id); ?>" 
                                           checked>
                                    <div class="test-info">
                                        <div class="test-name"><?php echo esc_html($test_data['name']); ?></div>
                                        <div class="test-description"><?php echo esc_html($test_data['description']); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="tests-preview">
                            <?php 
                            $test_names = array_column($category_data['tests'], 'name');
                            echo esc_html(implode(', ', array_slice($test_names, 0, 2)));
                            if (count($test_names) > 2) {
                                echo esc_html(' +' . (count($test_names) - 2) . ' more');
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
} 