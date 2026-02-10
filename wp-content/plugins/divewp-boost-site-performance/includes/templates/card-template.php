<?php
/**
 * Template for recommendation cards
 *
 * @package     DiveWP
 * @subpackage  Templates
 * @since       1.0.4
 *
 * Variables available:
 * $title          - Card title
 * $icon           - SVG icon markup
 * $details        - Main description text
 * $steps          - Array of steps to display
 * $status         - Status class (success, warning, danger, info)
 * $status_text    - Status text to display
 * $learn_more     - Array containing learn more content
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__( 'Direct access not permitted.', 'divewp-boost-site-performance' ) );
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template file with local variables only

// Ensure all variables are set with defaults
$title       = isset( $title ) ? $title : '';
$icon        = isset( $icon ) ? $icon : '';
$details     = isset( $details ) ? $details : '';
$steps       = isset( $steps ) && is_array( $steps ) ? $steps : array();
$status      = isset( $status ) ? $status : 'info';
$status_text = isset( $status_text ) ? $status_text : '';
$learn_more  = isset( $learn_more ) && is_array( $learn_more ) ? $learn_more : array();
?>
<div class="recommendation-card">
    <div class="recommendation-top">
        <div class="recommendation-header">
            <div class="recommendation-icon">
                <?php echo wp_kses( $icon, array(
                    'svg'  => array(
                        'width'   => true,
                        'height'  => true,
                        'viewBox' => true,
                        'fill'    => true,
                        'stroke'  => true,
                    ),
                    'path' => array(
                        'd'      => true,
                        'stroke' => true,
                        'fill'   => true,
                    ),
                ) ); ?>
            </div>
            <h4 class="recommendation-title"><?php echo esc_html( $title ); ?></h4>
        </div>
    </div>

    <div class="recommendation-middle">
        <div class="recommendation-content">
            <p><?php echo esc_html( $details ); ?></p>
            <?php if ( ! empty( $steps ) ) : ?>
                <ul class="recommendation-steps">
                    <?php foreach ( $steps as $step ) : ?>
                        <li><?php echo esc_html( $step ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="recommendation-bottom">
        <span class="status-pill status-pill-<?php echo esc_attr( $status ); ?>">
            <?php echo esc_html( $status_text ); ?>
        </span>
        <div class="recommendation-expand" 
             role="button" 
             tabindex="0"
             data-feature="<?php echo esc_attr( isset($feature) ? $feature : '' ); ?>"
             data-check="<?php echo esc_attr( isset($check_name) ? $check_name : '' ); ?>">
            <?php esc_html_e( 'Learn more', 'divewp-boost-site-performance' ); ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </div>
    </div>

    <?php if ( ! empty( $learn_more ) ) : ?>
        <div class="recommendation-details" style="display: none;">
            <div class="recommendation-details-content">
                <?php if ( ! empty( $learn_more['description'] ) ) : ?>
                    <p><?php echo esc_html( $learn_more['description'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $learn_more['benefits'] ) ) : ?>
                    <?php if ( isset( $learn_more['benefits_title'] ) ) : ?>
                        <p><strong><?php echo esc_html( $learn_more['benefits_title'] ); ?></strong></p>
                    <?php endif; ?>
                    <ul class="recommendation-steps">
                        <?php foreach ( $learn_more['benefits'] as $benefit ) : ?>
                            <li><?php echo esc_html( $benefit ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="recommendation-loading divewp-loading-container" style="display: none;">
                <div class="divewp-loader"></div>
                <p><?php esc_html_e('Loading details...', 'divewp-boost-site-performance'); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>