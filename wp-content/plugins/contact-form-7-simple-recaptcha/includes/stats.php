<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cf7sr_record_spam_block( $captcha_type = 'recaptcha' ) {
    $stats = get_option( 'cf7sr_spam_stats', array(
        'total_blocked' => 0,
        'blocked_today' => 0,
        'blocked_this_month' => 0,
        'last_reset_day' => current_time( 'Y-m-d' ),
        'last_reset_month' => current_time( 'Y-m' ),
        'by_type' => array(
            'recaptcha' => 0,
            'recaptcha-v3' => 0,
            'hcaptcha' => 0,
            'turnstile' => 0
        )
    ) );

    $current_date = current_time( 'Y-m-d' );
    $current_month = current_time( 'Y-m' );

    if ( $stats['last_reset_day'] !== $current_date ) {
        $stats['blocked_today'] = 0;
        $stats['last_reset_day'] = $current_date;
    }

    if ( $stats['last_reset_month'] !== $current_month ) {
        $stats['blocked_this_month'] = 0;
        $stats['last_reset_month'] = $current_month;
    }

    $stats['total_blocked']++;
    $stats['blocked_today']++;
    $stats['blocked_this_month']++;

    if ( isset( $stats['by_type'][$captcha_type] ) ) {
        $stats['by_type'][$captcha_type]++;
    }

    update_option( 'cf7sr_spam_stats', $stats );
}

function cf7sr_get_spam_stats() {
    $stats = get_option( 'cf7sr_spam_stats', array(
        'total_blocked' => 0,
        'blocked_today' => 0,
        'blocked_this_month' => 0,
        'last_reset_day' => current_time( 'Y-m-d' ),
        'last_reset_month' => current_time( 'Y-m' ),
        'by_type' => array(
            'recaptcha' => 0,
            'recaptcha-v3' => 0,
            'hcaptcha' => 0,
            'turnstile' => 0
        )
    ) );

    $current_date = current_time( 'Y-m-d' );
    $current_month = current_time( 'Y-m' );

    if ( $stats['last_reset_day'] !== $current_date ) {
        $stats['blocked_today'] = 0;
    }

    if ( $stats['last_reset_month'] !== $current_month ) {
        $stats['blocked_this_month'] = 0;
    }

    return $stats;
}

function cf7sr_reset_spam_stats() {
    delete_option( 'cf7sr_spam_stats' );
}

function cf7sr_handle_stats_reset() {
    if ( ! isset( $_POST['cf7sr_reset_stats'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! isset( $_POST['cf7sr_stats_nonce'] ) || ! wp_verify_nonce( $_POST['cf7sr_stats_nonce'], 'cf7sr_reset_stats' ) ) {
        return;
    }

    cf7sr_reset_spam_stats();

    add_settings_error(
        'cf7sr_messages',
        'cf7sr_message',
        __( 'Statistics have been reset successfully.', 'contact-form-7-simple-recaptcha' ),
        'updated'
    );
}
add_action( 'admin_init', 'cf7sr_handle_stats_reset' );
