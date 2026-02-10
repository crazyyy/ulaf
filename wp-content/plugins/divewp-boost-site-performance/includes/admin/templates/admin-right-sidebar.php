<?php
/**
 * Admin Right Sidebar Template
 *
 * Displays the right sidebar content including:
 * - Beta notice
 * - Status legend
 * - Helpful links loaded from JSON
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template file with local variables only

// Load and validate helpful links from JSON
$helpful_links = [];
$json_file = DIVEWP_PLUGIN_DIR . 'content/sidebar-content/helpful-links.json';

try {
    if (!file_exists($json_file)) {
        throw new Exception(esc_html__('Helpful links file not found', 'divewp-boost-site-performance'));
    }
    
    $json_content = file_get_contents($json_file);
    if ($json_content === false) {
        throw new Exception(esc_html__('Could not read helpful links file', 'divewp-boost-site-performance'));
    }
    
    $helpful_links = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(esc_html__('Invalid helpful links JSON format', 'divewp-boost-site-performance'));
    }
} catch (Exception $e) {
    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
        divewp_debug_log('Sidebar Error: ' . $e->getMessage(), 'error');
    }
    echo '<div class="notice notice-error"><p>' . esc_html__('Error loading sidebar content.', 'divewp-boost-site-performance') . '</p></div>';
    $helpful_links = array(
        'title' => esc_html__('Helpful Links', 'divewp-boost-site-performance'),
        'links' => array()
    );
}
?>
<div class="divewp-sidebar no-print">
    

    <div class="sidebar-section beta-notice" style="background: linear-gradient(135deg, #FDF2E9 0%, #FAE5D3 100%); border-radius: 8px; padding: 12px; margin-bottom: 10px;">
        <h3 style="color: #c05621; margin-top: 0; font-size: 14px;">
            <?php esc_html_e('🧪 Message from the developer', 'divewp-boost-site-performance'); ?>
        </h3>
        <p style="color: #c05621; font-size: 11px; line-height: 1.4; margin-bottom: 0;">
            <?php esc_html_e('Currently, some features like SEO and Performance checks are designed to detect specific popular plugins and configurations. If you\'re using alternative solutions, these items might show as "not detected". If you notice any mismatches, please share your feedback to help us improve our detection system.', 'divewp-boost-site-performance'); ?>
        </p>
    </div>

<!-- YouTube Subscribe Section -->
<div class="sidebar-section divewp-yt-subscribe" style="background: linear-gradient(135deg, #F0FFF4 0%, #DCFCE7 100%); border-radius: 8px; padding: 12px; margin-bottom: 10px;">
        <div class="divewp-yt-subscribe__icon">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="#FF0000">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
        </div>
        <h3><?php esc_html_e('DiveWP on YouTube', 'divewp-boost-site-performance'); ?></h3>
        <p class="divewp-yt-subscribe__desc">
            <?php esc_html_e('Watch simple, non-techie tutorials about WordPress performance, security, and optimization.', 'divewp-boost-site-performance'); ?>
        </p>
        <a href="https://www.youtube.com/@diveWPcom?sub_confirmation=1"
           target="_blank"
           rel="noopener noreferrer"
           class="divewp-yt-subscribe__btn">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            <?php esc_html_e('Subscribe', 'divewp-boost-site-performance'); ?>
        </a>
    </div>
    <div class="sidebar-section">
        <h3><?php echo esc_html($helpful_links['title']); ?></h3>
        <ul>
            <?php foreach ($helpful_links['links'] as $link): ?>
                <li>
                    <a href="<?php echo esc_url($link['url']); ?>" 
                       target="_blank" 
                       title="<?php echo esc_attr($link['description']); ?>">
                        <?php echo esc_html($link['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
