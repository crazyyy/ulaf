<?php
/**
 * AI Capabilities Feature Class
 *
 * Provides a step-by-step guide for Abilities API and MCP integration.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.2.0
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * AI Capabilities Feature Class
 */
class DiveWP_AI_Capabilities {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue feature assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook) {
        if ('toplevel_page_divewp' !== $hook) {
            return;
        }

        // Enqueue video hero CSS (reusable template).
        wp_enqueue_style(
            'divewp-video-hero',
            DIVEWP_PLUGIN_URL . 'assets/css/video-hero.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        // Enqueue specific CSS
        wp_enqueue_style(
            'divewp-ai-capabilities',
            DIVEWP_PLUGIN_URL . 'assets/css/features/ai-capabilities.css',
            array('divewp-style', 'divewp-global', 'divewp-video-hero'),
            DIVEWP_VERSION
        );
    }

    /**
     * Render the AI Capabilities page
     */
    public function render() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        ?>
        <div class="divewp-ai-capabilities-dashboard">
            <h3><?php esc_html_e('AI Capabilities & API Integration', 'divewp-boost-site-performance'); ?></h3>
            
            <p class="divewp-ai-intro">
                <?php esc_html_e('Connect your WordPress site to AI assistants like Cursor, Claude, or ChatGPT using the Model Context Protocol (MCP) and WordPress Abilities API.', 'divewp-boost-site-performance'); ?>
            </p>

            <?php $this->render_video_hero(); ?>

            <?php $this->render_hero(); ?>

            <?php $this->render_mcp_explanation(); ?>

            <div class="divewp-grid divewp-grid-3">
                <?php $this->render_step_cards(); ?>
            </div>

            <?php $this->render_tools_section(); ?>
        </div>
        <?php
    }

    /**
     * Render hero section
     */
    private function render_hero() {
        $is_available = DiveWP_Abilities::is_abilities_api_available();
        ?>
        <div class="divewp-ai-hero">
            <div class="divewp-ai-hero__content">
                <div class="divewp-ai-hero__icon">
                    <span class="dashicons dashicons-rest-api"></span>
                </div>
                <div class="divewp-ai-hero__text">
                    <h4><?php esc_html_e('The Bridge Between Your Site and AI', 'divewp-boost-site-performance'); ?></h4>
                    <p><?php esc_html_e('DiveWP exposes your site\'s diagnostics and optimization tools directly to AI agents. Follow these 3 simple steps to enable autonomous site management.', 'divewp-boost-site-performance'); ?></p>
                </div>
            </div>
            <div class="divewp-ai-hero__status">
                <div class="divewp-status-pill <?php echo $is_available ? 'divewp-status-pill--success' : 'divewp-status-pill--warning'; ?>">
                    <span class="dashicons <?php echo $is_available ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                    <?php echo $is_available ? esc_html__('Abilities API: Ready', 'divewp-boost-site-performance') : esc_html__('Abilities API: Missing', 'divewp-boost-site-performance'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render video explainer hero section using reusable template.
     *
     * @since 2.2.0
     * @return void
     */
    private function render_video_hero() {
        // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables passed to included template
        $title       = __( 'WordPress Abilities API + MCP: Connect your site to AI assistants (securely)', 'divewp-boost-site-performance' );
        $description = __( 'This explainer covers the two building blocks behind AI-to-WordPress integrations: the Model Context Protocol (MCP) and the WordPress Abilities API. Learn how capabilities become discoverable tools, how the MCP Adapter bridges them, and why this approach enables secure, multi-step, context-aware workflows for assistants like Cursor and Claude.', 'divewp-boost-site-performance' );
        $video_id    = 'BH2Lvm8RL_c';
        $video_start = 269;
        $badge_text  = __( 'Video Explainer', 'divewp-boost-site-performance' );
        $features    = array(
            __( 'Abilities API: machine-readable capability registry', 'divewp-boost-site-performance' ),
            __( 'MCP Adapter: turns abilities into tools/resources/prompts', 'divewp-boost-site-performance' ),
        );
        // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

        include DIVEWP_PLUGIN_DIR . 'includes/templates/video-hero-template.php';
    }

    /**
     * Render a simple explanation of what MCP is
     */
    private function render_mcp_explanation() {
        ?>
        <div class="divewp-mcp-explanation">
            <div class="divewp-mcp-explanation__header">
                <span class="dashicons dashicons-info"></span>
                <h5><?php esc_html_e('What is MCP?', 'divewp-boost-site-performance'); ?></h5>
            </div>
            <div class="divewp-mcp-explanation__content">
                <p><?php esc_html_e('Model Context Protocol (MCP) is like a "universal connector" for AI. It allows your AI assistant (like Cursor, Claude, or ChatGPT) to securely "plug in" to your website. Instead of you manually copying and pasting logs or server info, MCP lets the AI directly use DiveWP\'s diagnostic tools to see what\'s happening and help you fix things instantly.', 'divewp-boost-site-performance'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render step-by-step cards
     */
    private function render_step_cards() {
        $steps = array(
            array(
                'number' => '1',
                'title'  => __('Install MCP Adapter', 'divewp-boost-site-performance'),
                'icon'   => 'admin-plugins',
                'desc'   => __('Download and install the official WordPress MCP Adapter plugin (v0.4.1+) to create the communication bridge. This plugin allows AI agents to discover the capabilities exposed by DiveWP.', 'divewp-boost-site-performance'),
                'link'   => 'https://github.com/WordPress/mcp-adapter/releases',
                'label'  => __('Download Adapter', 'divewp-boost-site-performance')
            ),
            array(
                'number' => '2',
                'title'  => __('Create App Password', 'divewp-boost-site-performance'),
                'icon'   => 'lock',
                'desc'   => __('Go to Users → Profile and create a new "Application Password". Copy it securely; you will need this unique password to grant your AI assistant authenticated access to your site\'s diagnostics.', 'divewp-boost-site-performance'),
                'link'   => admin_url('profile.php#application-passwords-section'),
                'label'  => __('Go to Profile', 'divewp-boost-site-performance')
            ),
            array(
                'number' => '3',
                'title'  => __('Configure Your AI', 'divewp-boost-site-performance'),
                'icon'   => 'welcome-learn-more',
                'desc'   => __('Add your site URL and credentials to your AI assistant. For Cursor, edit your mcp.json; for Claude Desktop, update your config. This connects the AI to the tools listed below, allowing it to perform analysis on your behalf.', 'divewp-boost-site-performance'),
                'link'   => '', // No button for this card
                'label'  => ''
            ),
        );

        foreach ($steps as $step) {
            ?>
            <div class="recommendation-card divewp-ai-step-card">
                <div class="recommendation-top">
                    <div class="recommendation-header">
                        <div class="recommendation-icon">
                            <span class="dashicons dashicons-<?php echo esc_attr($step['icon']); ?>"></span>
                        </div>
                        <h4 class="recommendation-title"><?php echo esc_html($step['title']); ?></h4>
                    </div>
                </div>

                <div class="recommendation-middle">
                    <div class="recommendation-content">
                        <p><?php echo esc_html($step['desc']); ?></p>
                    </div>
                </div>

                <div class="recommendation-bottom">
                    <span class="status-pill status-pill-info">
                        <?php
                        /* translators: %s: Step number (1, 2, or 3) */
                        printf(esc_html__('Step %s', 'divewp-boost-site-performance'), esc_html($step['number']));
                        ?>
                    </span>
                    <?php if (!empty($step['link'])) : ?>
                    <a href="<?php echo esc_url($step['link']); ?>" class="divewp-button" <?php echo isset($step['target']) ? 'target="' . esc_attr($step['target']) . '"' : ''; ?>>
                        <?php echo esc_html($step['label']); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render available tools section
     */
    private function render_tools_section() {
        $tools = array(
            'divewp/server-insights'            => __('Full server health & config check', 'divewp-boost-site-performance'),
            'divewp/performance-checks'         => __('Caching & optimization discovery', 'divewp-boost-site-performance'),
            'divewp/db-insights'                => __('Database size & optimization status', 'divewp-boost-site-performance'),
            'divewp/security-insights'          => __('Vulnerability & configuration audit', 'divewp-boost-site-performance'),
            'divewp/theme-builder-insights'     => __('Theme and page builder health check', 'divewp-boost-site-performance'),
            'divewp/woocommerce-best-practices' => __('WooCommerce optimization analysis', 'divewp-boost-site-performance'),
            'divewp/seo-optimization'           => __('SEO configuration & visibility audit', 'divewp-boost-site-performance'),
            'divewp/email-communications'       => __('Email delivery & SMTP status', 'divewp-boost-site-performance'),
            'divewp/hosting-benchmark-latest'   => __('Latest hosting performance results', 'divewp-boost-site-performance'),
            'divewp/cron-insights'              => __('Monitor background tasks & overdue jobs', 'divewp-boost-site-performance'),
        );
        ?>
        <div class="divewp-ai-tools" id="divewp-ai-tools">
            <h4><?php esc_html_e('Available AI Tools (Abilities)', 'divewp-boost-site-performance'); ?></h4>
            <p><?php esc_html_e('Once connected, you can ask your AI assistant to run these specific tools on your site:', 'divewp-boost-site-performance'); ?></p>
            
            <div class="divewp-tools-list">
                <?php foreach ($tools as $id => $desc) : ?>
                    <div class="divewp-tool-item">
                        <code class="divewp-tool-id"><?php echo esc_html($id); ?></code>
                        <span class="divewp-tool-desc"><?php echo esc_html($desc); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="divewp-ai-usage-examples">
                <h4><?php esc_html_e('How to use with ChatGPT or Claude', 'divewp-boost-site-performance'); ?></h4>
                <p><?php esc_html_e('If your chat client supports MCP, you can use these prompts to interact with your site:', 'divewp-boost-site-performance'); ?></p>
                
                <div class="divewp-prompts-grid">
                    <div class="divewp-prompt-item">
                        <span class="dashicons dashicons-editor-quote"></span>
                        <p><?php esc_html_e('“Analyze my site\'s cron jobs and tell me if any are overdue.”', 'divewp-boost-site-performance'); ?></p>
                    </div>
                    <div class="divewp-prompt-item">
                        <span class="dashicons dashicons-editor-quote"></span>
                        <p><?php esc_html_e('“Check my server configuration for any performance bottlenecks.”', 'divewp-boost-site-performance'); ?></p>
                    </div>
                    <div class="divewp-prompt-item">
                        <span class="dashicons dashicons-editor-quote"></span>
                        <p><?php esc_html_e('“Audit my site\'s security and list any critical vulnerabilities.”', 'divewp-boost-site-performance'); ?></p>
                    </div>
                    <div class="divewp-prompt-item">
                        <span class="dashicons dashicons-editor-quote"></span>
                        <p><?php esc_html_e('“Provide a summary of my latest hosting benchmark results.”', 'divewp-boost-site-performance'); ?></p>
                    </div>
                </div>
            </div>

            <div class="divewp-ai-config-example">
                <h5><?php esc_html_e('Example Cursor Config (mcp.json)', 'divewp-boost-site-performance'); ?></h5>
                <pre><code>{
  "mcpServers": {
    "divewp": {
      "command": "npx",
      "args": ["-y", "@automattic/mcp-wordpress-remote@latest"],
      "env": {
        "WP_API_URL": "<?php echo esc_url(rest_url('mcp/mcp-adapter-default-server')); ?>",
        "WP_API_USERNAME": "your-username",
        "WP_API_PASSWORD": "your-app-password"
      }
    }
  }
}</code></pre>
            </div>
        </div>
        <?php
    }
}
