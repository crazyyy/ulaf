<?php
/**
 * Chose Hosting Class
 *
 * Handles the hosting information cards section.
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
 * Chose Hosting Class
 */
class DiveWP_Chose_Hosting {

    /**
     * Content loader instance
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * Constructor
     */
    public function __construct() {
        $this->content_loader = new DiveWP_Content_Loader();
    }

    /**
     * Render the chose hosting information section
     */
    public function render() {
        // Render introductory text
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-info" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Why Your Host Matters', 'divewp-boost-site-performance') . '</h4>';
        echo '<p>' . esc_html__("Your hosting provider is the foundation of your website. It significantly impacts your site's speed, security, reliability, and ability to handle traffic. Choosing the right host is crucial for success.", 'divewp-boost-site-performance') . '</p>';
        echo '<p>' . esc_html__("This guide explains key hosting terms and provides questions to ask potential hosts.", 'divewp-boost-site-performance') . '</p>';
        echo '</div>';

        // --- Section: Key Hosting Factors Explained (Using Cards) ---
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-book" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Key Hosting Factors Explained', 'divewp-boost-site-performance') . '</h4>';
        // Add instruction to check other tabs
        echo '<p>' . esc_html__("Consider how your current site performs based on the details in other DiveWP tabs (like Server Insights, DB Insights, and Performance Checks) when evaluating these factors.", 'divewp-boost-site-performance') . '</p>';
        echo '<div class="recommendations-grid">';

        // Render all the factor cards
        $this->render_factor_card('hosting-types');
        $this->render_factor_card('cpu-ram');
        $this->render_factor_card('storage');
        $this->render_factor_card('inodes');
        $this->render_factor_card('bandwidth');
        $this->render_factor_card('php-db');
        $this->render_factor_card('backups-support');
        $this->render_factor_card('ssl-location');

        echo '</div>'; // Close recommendations-grid
        echo '</div>';

        // --- Section: Where to Find Details ---
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-search" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Where to Find Hosting Details', 'divewp-boost-site-performance') . '</h4>';
        echo '<ul style="list-style: disc; padding-left: 20px;">';
        echo '<li>' . esc_html__("Hosting Provider's Website: Check plan comparison pages and knowledge base articles.", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Hosting Control Panel: Log in to your cPanel/Plesk/etc. to check resource usage like disk space.", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Contact Support/Sales: Ask the hosting provider directly for specifics not listed online.", 'divewp-boost-site-performance') . '</li>';
        echo '</ul>';
        echo '</div>';

        // --- Section: Questions to Ask ---
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-editor-help" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Questions to Ask Potential Hosts', 'divewp-boost-site-performance') . '</h4>';
        echo '<ul style="list-style: disc; padding-left: 20px;">';
        echo '<li>' . esc_html__("What are the specific CPU & RAM resources allocated (dedicated or shared)?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("What is the inode limit?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Do you use SSD storage for files AND databases?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Which PHP versions are supported? What is the default `memory_limit`?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Which MySQL/MariaDB versions are available?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Is a free SSL certificate included and auto-renewed?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("How often are backups performed, where are they stored, and how long are they kept?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("What support channels and hours are available?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Where are your data centers located?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("(Optional) Do you offer server-level caching (LiteSpeed, Redis, etc.)?", 'divewp-boost-site-performance') . '</li>';
        echo '</ul>';
        echo '</div>';

        // --- Section: Disclaimer ---
        echo '<div class="divewp-notice divewp-notice-info">';
        echo '<p><strong>' . esc_html__('Disclaimer:', 'divewp-boost-site-performance') . '</strong> ';
        echo esc_html__("DiveWP provides informational guidance based on general best practices and available data. We cannot guarantee the suitability or performance of any specific hosting provider. Please conduct thorough research before choosing a host.", 'divewp-boost-site-performance') . '</p>';
        echo '</div>';
    }

    /**
     * Renders a generic hosting factor card.
     */
    private function render_factor_card($check_type) {
        $content = $this->content_loader->get_content('choose-hosting', $check_type);

        if (!$content) {
            echo '<p>Error loading content for ' . esc_html($check_type) . '.</p>';
            return;
        }

        $messages = isset($content['messages']['info']) ? $content['messages']['info'] : [];
        $learn_more = isset($content['learn_more']) ? $content['learn_more'] : [];

        $args = array(
            'title'       => isset($messages['title']) ? esc_html($messages['title']) : ucfirst(str_replace('-', ' ', $check_type)),
            'icon'        => $this->get_icon($check_type),
            'details'     => isset($messages['details']) ? esc_html($messages['details']) : '',
            'steps'       => array(),
            'status'      => 'info',
            'status_text' => esc_html__('Informational', 'divewp-boost-site-performance'),
            'learn_more'  => $learn_more,
            'feature'     => 'choose-hosting',
            'check_name'  => $check_type
        );

        $this->render_card($args);
    }

    /**
     * Helper method to render a card using the template.
     */
    private function render_card($args) {
        $defaults = array(
            'title' => '',
            'icon' => '',
            'details' => '',
            'steps' => array(),
            'status' => 'info',
            'status_text' => esc_html__('Information', 'divewp-boost-site-performance'),
            'learn_more' => array(),
            'feature' => '',
            'check_name' => ''
        );
        $args = wp_parse_args($args, $defaults);
        extract($args);
        $template_path = DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p>Error: Card template not found.</p>';
        }
    }

    /**
     * Get icon for a specific check type.
     */
    private function get_icon($type) {
        // Return SVG markup directly, matching card-template.php wp_kses expectations
        // Reusing SVGs from class-server-insights-new.php where applicable
        $svg_markup = '';
        switch ($type) {
            case 'hosting-types': // Networking
            case 'bandwidth':     // Chart Area / Networking
            case 'ssl-location':  // Admin Site Alt / Networking
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>'; // Reused External Connections icon
                break;
            case 'cpu-ram': // Performance
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 4v4"/><path d="M15 4v4"/><path d="M9 16v4"/><path d="M15 16v4"/><path d="M4 9h4"/><path d="M16 9h4"/><path d="M4 15h4"/><path d="M16 15h4"/></svg>'; // Reused Memory Limit icon
                break;
            case 'storage': // Database
            case 'php-db':  // Admin Settings / Database
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 5c0 1.1-3.582 2-8 2s-8-.9-8-2 3.582-2 8-2 8 .9 8 2"/><path d="M3 5v14c0 1.1 3.582 2 8 2s8-.9 8-2V5"/></svg>'; // Reused Database Version icon
                break;
            case 'inodes': // Media Default / File Icon Placeholder
                 $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>'; // Simple file icon
                break;
            case 'backups-support': // Shield Alt
                 $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'; // Shield icon
                break;
            default: // Default info icon
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="8"/></svg>';
                break;
        }

        // No need for esc_attr here as wp_kses in card-template handles sanitization
        return $svg_markup;
    }
} 