<?php
/**
 * Template for Video Hero Section
 *
 * Reusable video hero component for feature explainer videos.
 *
 * @package     DiveWP
 * @subpackage  Templates
 * @since       2.2.0
 *
 * Variables available:
 * $title           - Hero title text
 * $description     - Hero description text
 * $video_id        - YouTube video ID (e.g., 'Qh84mRrkPiY')
 * $video_start     - Optional start time in seconds (e.g., 269)
 * $badge_text      - Optional badge text (defaults to 'Video Guide')
 * $features        - Optional array of feature highlights to display
 * $show_subscribe  - Optional boolean to show/hide subscribe button (defaults to true)
 * $channel_url     - Optional YouTube channel URL (defaults to DiveWP channel)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__( 'Direct access not permitted.', 'divewp-boost-site-performance' ) );
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template file with local variables only

// Ensure all variables are set with defaults
$title          = isset( $title ) ? $title : '';
$description    = isset( $description ) ? $description : '';
$video_id       = isset( $video_id ) ? $video_id : '';
$video_start    = isset( $video_start ) ? absint( $video_start ) : 0;
$badge_text     = isset( $badge_text ) ? $badge_text : __( 'Video Guide', 'divewp-boost-site-performance' );
$features       = isset( $features ) && is_array( $features ) ? $features : array();
$show_subscribe = isset( $show_subscribe ) ? $show_subscribe : true;
$channel_url    = isset( $channel_url ) ? $channel_url : 'https://www.youtube.com/@diveWPcom';

// Don't render if no video ID provided
if ( empty( $video_id ) ) {
    return;
}

// Normalize video ID and build embed URL.
$video_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $video_id );
$embed_url = 'https://www.youtube.com/embed/' . rawurlencode( $video_id );
if ( $video_start > 0 ) {
    $embed_url .= '?' . http_build_query(
        array(
            'start' => $video_start,
        ),
        '',
        '&',
        PHP_QUERY_RFC3986
    );
}
?>
<div class="divewp-video-hero">
    <div class="divewp-video-hero__content">
        <?php if ( ! empty( $badge_text ) ) : ?>
        <span class="divewp-video-hero__badge">
            <span class="dashicons dashicons-video-alt3"></span>
            <?php echo esc_html( $badge_text ); ?>
        </span>
        <?php endif; ?>

        <?php if ( ! empty( $title ) ) : ?>
        <h2 class="divewp-video-hero__title">
            <?php echo esc_html( $title ); ?>
        </h2>
        <?php endif; ?>

        <?php if ( ! empty( $description ) ) : ?>
        <p class="divewp-video-hero__description">
            <?php echo esc_html( $description ); ?>
        </p>
        <?php endif; ?>

        <?php if ( ! empty( $features ) ) : ?>
        <ul class="divewp-video-hero__features">
            <?php foreach ( $features as $feature ) : ?>
            <li>
                <span class="dashicons dashicons-yes-alt"></span>
                <?php echo esc_html( $feature ); ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ( $show_subscribe ) : ?>
        <a href="<?php echo esc_url( $channel_url . '?sub_confirmation=1' ); ?>" target="_blank" rel="noopener noreferrer" class="divewp-video-hero__subscribe">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            <?php esc_html_e( 'Subscribe to DiveWP', 'divewp-boost-site-performance' ); ?>
        </a>
        <?php endif; ?>
    </div>

    <div class="divewp-video-hero__video">
        <div class="divewp-video-hero__video-wrapper">
            <iframe 
                src="<?php echo esc_url( $embed_url ); ?>" 
                title="<?php echo esc_attr( $title ); ?>"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            ></iframe>
        </div>
    </div>
</div>

