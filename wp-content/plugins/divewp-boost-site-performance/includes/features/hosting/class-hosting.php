<?php
/**
 * Hosting Feature Class
 *
 * Main hosting feature that displays chose hosting information cards and hosting benchmark.
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
 * Main Hosting Feature Class
 */
class DiveWP_Hosting {

    /**
     * Content loader instance
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * Chose hosting instance
     * @var DiveWP_Chose_Hosting
     */
    private $chose_hosting;

    /**
     * Hosting benchmark instance
     * @var DiveWP_Hosting_Benchmark
     */
    private $hosting_benchmark;

    /**
     * Constructor
     */
    public function __construct() {
        $this->content_loader = new DiveWP_Content_Loader();
        $this->init_subfeatures();
    }

    /**
     * Initialize sub-features
     */
    private function init_subfeatures() {
        // Load chose hosting feature
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/chose-hosting/class-chose-hosting.php';
        $this->chose_hosting = new DiveWP_Chose_Hosting();

        // Load hosting benchmark feature
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/class-hosting-benchmark.php';
        $this->hosting_benchmark = new DiveWP_Hosting_Benchmark();
    }

    /**
     * Render the main hosting page
     */
    public function render() {
        // Add capability check
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        echo '<h3>' . esc_html__('Hosting', 'divewp-boost-site-performance') . '</h3>';

        // Tabs header
        echo '<div class="divewp-hosting-tabs" role="tablist" aria-label="Hosting Sections">';
        echo '<button type="button" class="divewp-hosting-tab" role="tab" aria-selected="true" aria-controls="hosting-tab-benchmark" id="hosting-tab-button-benchmark">' . esc_html__('Benchmark', 'divewp-boost-site-performance') . '</button>';
        echo '<button type="button" class="divewp-hosting-tab" role="tab" aria-selected="false" aria-controls="hosting-tab-previous" id="hosting-tab-button-previous">' . esc_html__('Previous Results', 'divewp-boost-site-performance') . '</button>';
        echo '<button type="button" class="divewp-hosting-tab" role="tab" aria-selected="false" aria-controls="hosting-tab-guide" id="hosting-tab-button-guide">' . esc_html__('Hosting Guide', 'divewp-boost-site-performance') . '</button>';
        echo '</div>';

        // Benchmark content (default visible)
        echo '<div id="hosting-tab-benchmark" class="divewp-hosting-tab-content active" role="tabpanel" aria-labelledby="hosting-tab-button-benchmark">';
        $this->hosting_benchmark->render();
        echo '</div>';

        // Previous results content
        echo '<div id="hosting-tab-previous" class="divewp-hosting-tab-content" role="tabpanel" aria-labelledby="hosting-tab-button-previous">';
        // Reuse benchmark class renderer for saved benchmarks
        if (method_exists($this->hosting_benchmark, 'render_saved_benchmarks')) {
            // Saved results list + a results container (so results can render here too)
            $this->hosting_benchmark->render_saved_benchmarks();
            echo '<div class="benchmark-results" style="margin-top:16px;">';
            echo '<div class="results-placeholder"><p>' . esc_html__('Select a previous benchmark to load results here.', 'divewp-boost-site-performance') . '</p></div>';
            echo '</div>';
        }
        echo '</div>';

        // Guide content
        echo '<div id="hosting-tab-guide" class="divewp-hosting-tab-content" role="tabpanel" aria-labelledby="hosting-tab-button-guide">';
        $this->chose_hosting->render();
        echo '</div>';

        // JS handler moved to divewp-admin.js for consistency
    }
} 