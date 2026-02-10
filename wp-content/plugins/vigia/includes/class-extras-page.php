<?php
/**
 * Extras page class
 *
 * Handles the Extras submenu page with robots.txt, blocking, alerts, and llms.txt.
 *
 * @package VigIA
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Extras page class
 */
class VigIA_Extras_Page {

    /**
     * Register admin submenu
     */
    public static function register_menu() {
        add_submenu_page(
            'vigia',
            __( 'VigIA Extras', 'vigia' ),
            __( 'Extras', 'vigia' ),
            'manage_options',
            'vigia-extras',
            array( __CLASS__, 'render_page' )
        );
    }

    /**
     * Render the extras page
     */
    public static function render_page() {
        // Get current tab.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only, no data processing.
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'robots';

        $tabs = array(
            'robots'  => __( 'Disallow & Blocking', 'vigia' ),
            'llms'    => __( 'LLMs.txt Generator', 'vigia' ),
            'alerts'  => __( 'Email Alerts', 'vigia' ),
        );

        ?>
        <div class="wrap vigia-wrap vigia-extras-wrap">
            <h1>
                <span class="vigia-title-icon">
                    <img src="<?php echo esc_url( VIGIA_PLUGIN_URL . 'assets/images/icon-header-color.png' ); ?>" alt="" width="32" height="32">
                </span>
                <?php echo esc_html__( 'VigIA Extras', 'vigia' ); ?>
            </h1>

            <div class="vigia-extras-layout">
                <div class="vigia-extras-main">
                    <nav class="nav-tab-wrapper vigia-nav-tabs">
                        <?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=vigia-extras&tab=' . $tab_id ) ); ?>" 
                               class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                                <?php echo esc_html( $tab_name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>

                    <div class="vigia-extras-content">
                        <?php
                        switch ( $current_tab ) {
                            case 'llms':
                                self::render_llms_tab();
                                break;
                            case 'alerts':
                                self::render_alerts_tab();
                                break;
                            default:
                                self::render_robots_tab();
                                break;
                        }
                        ?>
                    </div>
                </div>

                <aside class="vigia-extras-sidebar">
                    <?php self::render_sidebar_promos(); ?>
                </aside>
            </div>
        </div>
        <?php
    }

    /**
     * Render robots.txt and blocking tab
     */
    private static function render_robots_tab() {
        $blocked_crawlers = VigIA_Blocker::get_blocked_crawlers();
        $robots_rules     = VigIA_Robots_Manager::get_ai_rules();
        $compliance       = VigIA_Robots_Manager::get_compliance_data();
        $all_crawlers     = VigIA_Crawler_Detector::get_all_crawlers();
        $category_labels  = VigIA_Crawler_Detector::get_category_labels();
        $category_colors  = VigIA_Crawler_Detector::get_category_colors();

        // Detect if physical robots.txt exists or use virtual.
        $physical_robots = ABSPATH . 'robots.txt';
        $robots_url      = file_exists( $physical_robots ) ? home_url( '/robots.txt' ) : home_url( '/?robots=1' );
        ?>

        <div class="vigia-extras-section">
            <h2><span class="dashicons dashicons-admin-site-alt3"></span> <?php esc_html_e( 'Robots.txt Disallow', 'vigia' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Manage which AI crawlers can access your site via robots.txt. Note: robots.txt is advisory only - crawlers may choose to ignore it.', 'vigia' ); ?>
            </p>

            <div class="vigia-robots-container">
                <!-- Current robots.txt preview -->
                <div class="vigia-robots-preview">
                    <h3><?php esc_html_e( 'Current robots.txt preview', 'vigia' ); ?></h3>
                    <pre id="vigia-robots-preview"><?php echo esc_html( VigIA_Robots_Manager::get_preview() ); ?></pre>
                    <p class="description">
                        <a href="<?php echo esc_url( $robots_url ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e( 'View live robots.txt', 'vigia' ); ?> <span class="dashicons dashicons-external"></span>
                        </a>
                        <?php if ( ! file_exists( $physical_robots ) ) : ?>
                            <span class="vigia-robots-type">(<?php esc_html_e( 'virtual', 'vigia' ); ?>)</span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Disallow rules -->
                <div class="vigia-robots-rules">
                    <h3><?php esc_html_e( 'AI Crawler rules', 'vigia' ); ?></h3>
                    
                    <?php if ( ! empty( $robots_rules['disallow'] ) ) : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Crawler', 'vigia' ); ?></th>
                                    <th><?php esc_html_e( 'Status', 'vigia' ); ?></th>
                                    <th><?php esc_html_e( 'Actions', 'vigia' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $robots_rules['disallow'] as $crawler ) : ?>
                                    <tr>
                                        <td><?php echo esc_html( $crawler ); ?></td>
                                        <td>
                                            <span class="vigia-status vigia-status-disallow"><?php esc_html_e( 'Disallow', 'vigia' ); ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small vigia-remove-robots-rule" 
                                                    data-crawler="<?php echo esc_attr( $crawler ); ?>" data-action="disallow">
                                                <?php esc_html_e( 'Remove', 'vigia' ); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p class="vigia-no-rules"><?php esc_html_e( 'No robots.txt rules configured for AI crawlers.', 'vigia' ); ?></p>
                    <?php endif; ?>

                    <!-- Add rule form -->
                    <div class="vigia-add-rule-form">
                        <h4><?php esc_html_e( 'Add robots.txt rule', 'vigia' ); ?></h4>
                        <div class="vigia-form-row">
                            <select id="vigia-robots-crawler">
                                <option value=""><?php esc_html_e( 'Select crawler...', 'vigia' ); ?></option>
                                <?php foreach ( $all_crawlers as $pattern => $crawler ) : ?>
                                    <?php if ( ! in_array( $crawler['name'], $robots_rules['disallow'], true ) ) : ?>
                                        <option value="<?php echo esc_attr( $crawler['name'] ); ?>">
                                            <?php echo esc_html( $crawler['name'] . ' (' . $crawler['company'] . ')' ); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="vigia-add-disallow" class="button button-secondary">
                                <?php esc_html_e( 'Add Disallow', 'vigia' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compliance panel -->
            <?php if ( ! empty( $robots_rules['disallow'] ) ) : ?>
            <div class="vigia-compliance-panel">
                <h3><span class="dashicons dashicons-shield"></span> <?php esc_html_e( 'Compliance check', 'vigia' ); ?></h3>
                <p class="description"><?php esc_html_e( 'Crawlers that visited your site in the last 30 days despite being in your Disallow list:', 'vigia' ); ?></p>
                
                <?php if ( ! empty( $compliance['non_compliant'] ) ) : ?>
                    <table class="wp-list-table widefat fixed striped vigia-non-compliant">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Crawler', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Visits', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Last visit', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'vigia' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $compliance['non_compliant'] as $crawler => $data ) : ?>
                                <tr class="vigia-row-warning">
                                    <td>
                                        <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                                        <?php echo esc_html( $crawler ); ?>
                                    </td>
                                    <td><?php echo esc_html( number_format_i18n( $data['visits'] ) ); ?></td>
                                    <td><?php echo esc_html( $data['last_visit'] ); ?></td>
                                    <td>
                                        <?php if ( ! VigIA_Blocker::is_blocked( $crawler ) ) : ?>
                                            <button type="button" class="button button-small vigia-block-php" 
                                                    data-crawler="<?php echo esc_attr( $crawler ); ?>">
                                                <?php esc_html_e( 'Block via PHP', 'vigia' ); ?>
                                            </button>
                                        <?php else : ?>
                                            <span class="vigia-already-blocked"><?php esc_html_e( 'Already blocked via PHP', 'vigia' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="vigia-compliance-ok">
                        <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                        <?php esc_html_e( 'All crawlers in your Disallow list are respecting your robots.txt (no visits in the last 30 days).', 'vigia' ); ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <hr class="vigia-section-divider">

        <div class="vigia-extras-section">
            <h2><span class="dashicons dashicons-dismiss"></span> <?php esc_html_e( 'PHP Blocking', 'vigia' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Block crawlers at PHP level by User-Agent or IP address. This is more effective than robots.txt as it returns a 403 Forbidden response.', 'vigia' ); ?>
            </p>

            <div class="vigia-notice vigia-notice-warning">
                <p>
                    <strong><?php esc_html_e( 'Warning:', 'vigia' ); ?></strong>
                    <?php esc_html_e( 'PHP blocking will completely prevent blocked crawlers from accessing your site. Make sure you understand the implications before blocking.', 'vigia' ); ?>
                </p>
            </div>

            <!-- User-Agent blocks -->
            <div class="vigia-blocking-subsection">
                <h3><?php esc_html_e( 'Blocked User-Agents', 'vigia' ); ?></h3>
                <?php
                $ua_blocks = VigIA_Blocker::get_blocked_by_type( 'useragent' );
                if ( ! empty( $ua_blocks ) ) :
                    ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Name', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'User-Agent pattern', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Blocked since', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'vigia' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $ua_blocks as $block ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $block['name'] ); ?></td>
                                    <td><code><?php echo esc_html( $block['pattern'] ); ?></code></td>
                                    <td><?php echo esc_html( $block['blocked_at'] ); ?></td>
                                    <td>
                                        <button type="button" class="button button-small button-link-delete vigia-unblock" 
                                                data-id="<?php echo esc_attr( $block['id'] ); ?>" data-type="useragent">
                                            <?php esc_html_e( 'Unblock', 'vigia' ); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="vigia-no-blocked"><?php esc_html_e( 'No User-Agent blocks configured.', 'vigia' ); ?></p>
                <?php endif; ?>

                <!-- Add User-Agent block form -->
                <div class="vigia-add-block-form">
                    <h4><?php esc_html_e( 'Block by User-Agent', 'vigia' ); ?></h4>
                    <div class="vigia-form-row">
                        <select id="vigia-block-crawler">
                            <option value=""><?php esc_html_e( 'Select crawler...', 'vigia' ); ?></option>
                            <?php foreach ( $all_crawlers as $pattern => $crawler ) : ?>
                                <?php if ( ! VigIA_Blocker::is_useragent_blocked( $pattern ) ) : ?>
                                    <option value="<?php echo esc_attr( $crawler['name'] ); ?>" data-useragent="<?php echo esc_attr( $pattern ); ?>">
                                        <?php echo esc_html( $crawler['name'] . ' (' . $crawler['company'] . ')' ); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="vigia-add-block-ua" class="button button-secondary">
                            <?php esc_html_e( 'Block', 'vigia' ); ?>
                        </button>
                    </div>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e( 'Or add custom User-Agent:', 'vigia' ); ?>
                    </p>
                    <div class="vigia-form-row" style="margin-top: 8px;">
                        <input type="text" id="vigia-custom-ua-name" placeholder="<?php esc_attr_e( 'Name (e.g., CustomBot)', 'vigia' ); ?>" style="width: 150px;">
                        <input type="text" id="vigia-custom-ua-pattern" placeholder="<?php esc_attr_e( 'Pattern (e.g., CustomBot/1.0)', 'vigia' ); ?>" style="width: 200px;">
                        <button type="button" id="vigia-add-custom-block-ua" class="button button-secondary">
                            <?php esc_html_e( 'Block', 'vigia' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- IP blocks -->
            <div class="vigia-blocking-subsection" style="margin-top: 25px;">
                <h3><?php esc_html_e( 'Blocked IP Addresses', 'vigia' ); ?></h3>
                <?php
                $ip_blocks = VigIA_Blocker::get_blocked_by_type( 'ip' );
                if ( ! empty( $ip_blocks ) ) :
                    ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Name/Note', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'IP Address', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Blocked since', 'vigia' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'vigia' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $ip_blocks as $block ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $block['name'] ); ?></td>
                                    <td><code><?php echo esc_html( $block['pattern'] ); ?></code></td>
                                    <td><?php echo esc_html( $block['blocked_at'] ); ?></td>
                                    <td>
                                        <button type="button" class="button button-small button-link-delete vigia-unblock" 
                                                data-id="<?php echo esc_attr( $block['id'] ); ?>" data-type="ip">
                                            <?php esc_html_e( 'Unblock', 'vigia' ); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="vigia-no-blocked"><?php esc_html_e( 'No IP blocks configured.', 'vigia' ); ?></p>
                <?php endif; ?>

                <!-- Add IP block form -->
                <div class="vigia-add-block-form">
                    <h4><?php esc_html_e( 'Block by IP Address', 'vigia' ); ?></h4>
                    <div class="vigia-form-row">
                        <input type="text" id="vigia-block-ip-name" placeholder="<?php esc_attr_e( 'Name/Note (optional)', 'vigia' ); ?>" style="width: 150px;">
                        <input type="text" id="vigia-block-ip" placeholder="<?php esc_attr_e( 'IP Address (e.g., 192.168.1.1)', 'vigia' ); ?>" style="width: 180px;">
                        <button type="button" id="vigia-add-block-ip" class="button button-secondary">
                            <?php esc_html_e( 'Block IP', 'vigia' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render email alerts tab
     */
    private static function render_alerts_tab() {
        $settings           = VigIA_Email_Alerts::get_settings();
        $frequency_options  = VigIA_Email_Alerts::get_frequency_options();
        $level_options      = VigIA_Email_Alerts::get_level_options();
        ?>

        <div class="vigia-extras-section">
            <h2><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Email Alerts', 'vigia' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Receive periodic reports about AI crawler activity on your site.', 'vigia' ); ?>
            </p>

            <div class="vigia-email-settings">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable alerts', 'vigia' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="vigia-email-enabled" <?php checked( $settings['enabled'] ); ?>>
                                <?php esc_html_e( 'Send periodic email reports', 'vigia' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vigia-email-address"><?php esc_html_e( 'Email address', 'vigia' ); ?></label></th>
                        <td>
                            <input type="email" id="vigia-email-address" class="regular-text" value="<?php echo esc_attr( $settings['email'] ); ?>">
                            <p class="description"><?php esc_html_e( 'Leave empty to use admin email.', 'vigia' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vigia-email-frequency"><?php esc_html_e( 'Frequency', 'vigia' ); ?></label></th>
                        <td>
                            <select id="vigia-email-frequency">
                                <?php foreach ( $frequency_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['frequency'], $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vigia-email-level"><?php esc_html_e( 'Detail level', 'vigia' ); ?></label></th>
                        <td>
                            <select id="vigia-email-level">
                                <?php foreach ( $level_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['level'], $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="vigia-email-actions">
                    <button type="button" id="vigia-save-email-settings" class="button button-primary">
                        <?php esc_html_e( 'Save settings', 'vigia' ); ?>
                    </button>
                    <button type="button" id="vigia-test-email" class="button button-secondary">
                        <?php esc_html_e( 'Send test email', 'vigia' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render LLMs.txt tab (v1.2.0 - completely rewritten)
     */
    private static function render_llms_tab() {
        $settings         = VigIA_LLMS_Generator::get_settings();
        $llms_exists      = VigIA_LLMS_Generator::llms_exists();
        $llms_full_exists = VigIA_LLMS_Generator::llms_full_exists();
        $llms_info        = VigIA_LLMS_Generator::get_file_info( 'llms.txt' );
        $llms_full_info   = VigIA_LLMS_Generator::get_file_info( 'llms-full.txt' );
        $post_types       = VigIA_LLMS_Generator::get_public_post_types();
        $seo_plugin       = VigIA_LLMS_Generator::detect_seo_plugin();
        ?>

        <div class="vigia-extras-section">
            <h2><span class="dashicons dashicons-media-text"></span> <?php esc_html_e( 'LLMs.txt Generator', 'vigia' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Generate llms.txt and llms-full.txt files to help AI systems understand your site structure and content.', 'vigia' ); ?>
                <a href="<?php
                    /* translators: URL to llms.txt documentation article. Replace with localized version if available. */
                    echo esc_url( __( 'https://ayudawp-com.translate.goog/llms-txt-llms-full-txt/?_x_tr_sl=es&_x_tr_tl=en&_x_tr_hl=es&_x_tr_pto=wapp', 'vigia' ) );
                ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Learn more about llms.txt', 'vigia' ); ?> <span class="dashicons dashicons-external"></span>
                </a>
            </p>

            <!-- Current files status -->
            <div class="vigia-llms-status">
                <h3><?php esc_html_e( 'Current files', 'vigia' ); ?></h3>
                <div class="vigia-files-grid">
                    <div class="vigia-file-card <?php echo $llms_exists ? 'vigia-file-exists' : ''; ?>">
                        <span class="dashicons dashicons-<?php echo $llms_exists ? 'yes-alt' : 'minus'; ?>"></span>
                        <strong>llms.txt</strong>
                        <?php if ( $llms_info ) : ?>
                            <span class="vigia-file-size"><?php echo esc_html( size_format( $llms_info['size'] ) ); ?></span>
                            <a href="<?php echo esc_url( $llms_info['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="vigia-file-link">
                                <?php esc_html_e( 'View', 'vigia' ); ?>
                            </a>
                        <?php else : ?>
                            <span class="vigia-file-missing"><?php esc_html_e( 'Not generated', 'vigia' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="vigia-file-card <?php echo $llms_full_exists ? 'vigia-file-exists' : ''; ?>">
                        <span class="dashicons dashicons-<?php echo $llms_full_exists ? 'yes-alt' : 'minus'; ?>"></span>
                        <strong>llms-full.txt</strong>
                        <?php if ( $llms_full_info ) : ?>
                            <span class="vigia-file-size"><?php echo esc_html( size_format( $llms_full_info['size'] ) ); ?></span>
                            <a href="<?php echo esc_url( $llms_full_info['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="vigia-file-link">
                                <?php esc_html_e( 'View', 'vigia' ); ?>
                            </a>
                        <?php else : ?>
                            <span class="vigia-file-missing"><?php esc_html_e( 'Not generated', 'vigia' ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ( $llms_exists || $llms_full_exists ) : ?>
                    <p class="vigia-last-generated">
                        <?php
                        /* translators: %s: formatted date/time */
                        printf( esc_html__( 'Last generated: %s', 'vigia' ), esc_html( VigIA_LLMS_Generator::get_last_generated_formatted() ) );
                        ?>
                    </p>
                    <div class="vigia-delete-buttons">
                        <?php if ( $llms_exists ) : ?>
                            <button type="button" class="vigia-delete-llms-file button button-link-delete" data-file="llms.txt">
                                <?php esc_html_e( 'Delete llms.txt', 'vigia' ); ?>
                            </button>
                        <?php endif; ?>
                        <?php if ( $llms_full_exists ) : ?>
                            <button type="button" class="vigia-delete-llms-file button button-link-delete" data-file="llms-full.txt">
                                <?php esc_html_e( 'Delete llms-full.txt', 'vigia' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Generator form -->
            <div class="vigia-llms-generator">
                <h3><?php esc_html_e( 'Generate files', 'vigia' ); ?></h3>

                <!-- Site info -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Site information', 'vigia' ); ?></h4>
                    <table class="form-table vigia-compact-table">
                        <tr>
                            <th scope="row"><label for="vigia-llms-site-name"><?php esc_html_e( 'Site name', 'vigia' ); ?></label></th>
                            <td>
                                <input type="text" id="vigia-llms-site-name" class="regular-text" value="<?php echo esc_attr( $settings['site_name'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="vigia-llms-description"><?php esc_html_e( 'Site description', 'vigia' ); ?></label></th>
                            <td>
                                <textarea id="vigia-llms-description" class="large-text" rows="2"><?php echo esc_textarea( $settings['site_description'] ); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Content selection by post type -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Include by content type', 'vigia' ); ?></h4>
                    <p class="description"><?php esc_html_e( 'Select which content types to include in their entirety.', 'vigia' ); ?></p>
                    
                    <div class="vigia-post-types-grid" id="vigia-post-types">
                        <?php foreach ( $post_types as $pt ) : ?>
                            <label class="vigia-post-type-item">
                                <input type="checkbox" name="vigia_post_types[]" value="<?php echo esc_attr( $pt['name'] ); ?>"
                                    data-count="<?php echo esc_attr( $pt['count'] ); ?>"
                                    <?php checked( in_array( $pt['name'], $settings['post_types'], true ) ); ?>>
                                <span class="vigia-pt-label"><?php echo esc_html( $pt['label'] ); ?></span>
                                <span class="vigia-pt-count">(<?php echo esc_html( number_format_i18n( $pt['count'] ) ); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Taxonomy filters (dynamic) -->
                    <div class="vigia-taxonomy-filters" id="vigia-taxonomy-filters" style="display: none;">
                        <h5><?php esc_html_e( 'Filter by taxonomy', 'vigia' ); ?></h5>
                        <p class="description"><?php esc_html_e( 'Optionally filter selected content types by their taxonomies. Leave empty to include all.', 'vigia' ); ?></p>
                        <div id="vigia-taxonomy-selectors"></div>
                    </div>
                </div>

                <!-- Manual includes -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Additional content (manual)', 'vigia' ); ?></h4>
                    <p class="description"><?php esc_html_e( 'Search and add specific content not covered by the post type selection above.', 'vigia' ); ?></p>
                    
                    <div class="vigia-manual-selector">
                        <div class="vigia-search-input-wrap">
                            <input type="text" id="vigia-include-search" class="vigia-ajax-search" 
                                   placeholder="<?php esc_attr_e( 'Search content to add...', 'vigia' ); ?>" autocomplete="off">
                            <div class="vigia-search-results" id="vigia-include-results"></div>
                        </div>
                        <div class="vigia-selected-items" id="vigia-manual-includes">
                            <?php if ( ! empty( $settings['manual_includes'] ) ) : ?>
                                <?php foreach ( $settings['manual_includes'] as $post_id ) : ?>
                                    <?php
                                    $post = get_post( $post_id );
                                    if ( ! $post ) {
                                        continue;
                                    }
                                    ?>
                                    <span class="vigia-selected-item" data-id="<?php echo esc_attr( $post_id ); ?>">
                                        <?php echo esc_html( get_the_title( $post ) ); ?>
                                        <button type="button" class="vigia-remove-item">&times;</button>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Manual excludes -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Exclude content', 'vigia' ); ?></h4>
                    <p class="description"><?php esc_html_e( 'Exclude specific content from the generated files.', 'vigia' ); ?></p>
                    
                    <div class="vigia-manual-selector">
                        <div class="vigia-search-input-wrap">
                            <input type="text" id="vigia-exclude-search" class="vigia-ajax-search" 
                                   placeholder="<?php esc_attr_e( 'Search content to exclude...', 'vigia' ); ?>" autocomplete="off">
                            <div class="vigia-search-results" id="vigia-exclude-results"></div>
                        </div>
                        <div class="vigia-selected-items" id="vigia-manual-excludes">
                            <?php if ( ! empty( $settings['manual_excludes'] ) ) : ?>
                                <?php foreach ( $settings['manual_excludes'] as $post_id ) : ?>
                                    <?php
                                    $post = get_post( $post_id );
                                    if ( ! $post ) {
                                        continue;
                                    }
                                    ?>
                                    <span class="vigia-selected-item" data-id="<?php echo esc_attr( $post_id ); ?>">
                                        <?php echo esc_html( get_the_title( $post ) ); ?>
                                        <button type="button" class="vigia-remove-item">&times;</button>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="vigia-pattern-excludes">
                        <label for="vigia-exclude-patterns"><?php esc_html_e( 'Exclude by URL pattern', 'vigia' ); ?></label>
                        <textarea id="vigia-exclude-patterns" class="large-text" rows="3" 
                                  placeholder="<?php esc_attr_e( "*/thank-you/*\n*/landing/*\n*-demo", 'vigia' ); ?>"><?php echo esc_textarea( $settings['exclude_patterns'] ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'One pattern per line. Use * as wildcard. Example: */thank-you/* excludes all URLs containing /thank-you/', 'vigia' ); ?></p>
                    </div>
                </div>

                <!-- SEO integration -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'SEO integration', 'vigia' ); ?></h4>
                    <label class="vigia-checkbox-label">
                        <input type="checkbox" id="vigia-exclude-noindex" <?php checked( $settings['exclude_noindex'] ); ?>>
                        <?php esc_html_e( 'Exclude noindex content', 'vigia' ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'Automatically excludes posts marked as noindex in your SEO plugin.', 'vigia' ); ?>
                        <?php if ( $seo_plugin ) : ?>
                            <span class="vigia-seo-detected">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php
                                /* translators: %s: SEO plugin name */
                                printf( esc_html__( 'Detected: %s', 'vigia' ), esc_html( $seo_plugin['name'] ) );
                                ?>
                            </span>
                        <?php else : ?>
                            <span class="vigia-seo-not-detected">
                                <?php esc_html_e( 'No supported SEO plugin detected.', 'vigia' ); ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Full content options -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Full content file', 'vigia' ); ?></h4>
                    <label class="vigia-checkbox-label">
                        <input type="checkbox" id="vigia-generate-full" <?php checked( $settings['generate_full'] ); ?>>
                        <?php esc_html_e( 'Also generate llms-full.txt with complete content', 'vigia' ); ?>
                    </label>
                    
                    <div class="vigia-full-options" id="vigia-full-options" style="<?php echo $settings['generate_full'] ? '' : 'display:none;'; ?>">
                        <p>
                            <label>
                                <input type="radio" name="vigia_full_mode" value="full" <?php checked( $settings['full_mode'], 'full' ); ?>>
                                <?php esc_html_e( 'Full content', 'vigia' ); ?>
                            </label>
                            <label style="margin-left: 20px;">
                                <input type="radio" name="vigia_full_mode" value="excerpt" <?php checked( $settings['full_mode'], 'excerpt' ); ?>>
                                <?php esc_html_e( 'Excerpt only (smaller file)', 'vigia' ); ?>
                            </label>
                        </p>
                    </div>
                </div>

                <!-- Auto-regeneration -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Regeneration', 'vigia' ); ?></h4>
                    <div class="vigia-regen-options">
                        <label>
                            <input type="radio" name="vigia_auto_regenerate" value="manual" <?php checked( $settings['auto_regenerate'], 'manual' ); ?>>
                            <?php esc_html_e( 'Manual only', 'vigia' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="vigia_auto_regenerate" value="daily" <?php checked( $settings['auto_regenerate'], 'daily' ); ?>>
                            <?php esc_html_e( 'Daily', 'vigia' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="vigia_auto_regenerate" value="weekly" <?php checked( $settings['auto_regenerate'], 'weekly' ); ?>>
                            <?php esc_html_e( 'Weekly', 'vigia' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="vigia_auto_regenerate" value="monthly" <?php checked( $settings['auto_regenerate'], 'monthly' ); ?>>
                            <?php esc_html_e( 'Monthly', 'vigia' ); ?>
                        </label>
                    </div>
                    <?php
                    $next_regen = VigIA_LLMS_Generator::get_next_regeneration();
                    if ( 'manual' !== $settings['auto_regenerate'] ) :
                        ?>
                        <p class="vigia-next-regen">
                            <?php
                            /* translators: %s: next scheduled date/time */
                            printf( esc_html__( 'Next scheduled: %s', 'vigia' ), esc_html( $next_regen ) );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Robots.txt integration -->
                <div class="vigia-llms-section">
                    <h4><?php esc_html_e( 'Robots.txt integration', 'vigia' ); ?></h4>
                    <label class="vigia-checkbox-label">
                        <input type="checkbox" id="vigia-robots-llms" <?php checked( $settings['robots_llms'] ); ?>>
                        <?php esc_html_e( 'Add llms.txt reference to robots.txt', 'vigia' ); ?>
                    </label>
                    <br>
                    <label class="vigia-checkbox-label">
                        <input type="checkbox" id="vigia-robots-llms-full" <?php checked( $settings['robots_llms_full'] ); ?>>
                        <?php esc_html_e( 'Add llms-full.txt reference to robots.txt', 'vigia' ); ?>
                    </label>
                </div>

                <!-- Content summary -->
                <div class="vigia-content-summary" id="vigia-content-summary">
                    <span class="dashicons dashicons-info-outline"></span>
                    <span id="vigia-summary-text"><?php esc_html_e( 'Select content types to see estimated count.', 'vigia' ); ?></span>
                </div>

                <!-- Action buttons -->
                <div class="vigia-llms-actions">
                    <button type="button" id="vigia-generate-llms" class="button button-primary">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e( 'Save and generate', 'vigia' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <script>
        /* Pass saved taxonomy filters to JS */
        var vigiaSavedTaxonomyFilters = <?php echo wp_json_encode( ! empty( $settings['taxonomy_filters'] ) ? $settings['taxonomy_filters'] : new stdClass() ); ?>;
        </script>
        <?php
    }

    /**
     * Render sidebar promotional boxes
     */
    private static function render_sidebar_promos() {
        // Add Thickbox support for plugin install popups.
        add_thickbox();

        // Render promotional banner with random plugins and services.
        $promo_banner = new Vigia_Promo_Banner( 'vigia', 'vigia', 'vigia' );
        $promo_banner->render( 'vertical' );
    }
}