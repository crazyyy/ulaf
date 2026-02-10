<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'sweeppress_fs' ) ) {
    // Create a helper function for easy SDK access.
    function sweeppress_fs() {
        global $sweeppress_fs;
        if ( !isset( $sweeppress_fs ) ) {
            $sweeppress_fs = fs_dynamic_init( array(
                'id'             => '16226',
                'slug'           => 'sweeppress',
                'premium_slug'   => 'sweeppress-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_d03d8e4d58cec379213dd2b6e19d8',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_paid_plans' => true,
                'has_addons'     => false,
                'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'    => 'sweeppress-dashboard',
                    'contact' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $sweeppress_fs;
    }

    // Init Freemius.
    sweeppress_fs();
    // Signal that SDK was initiated.
    do_action( 'sweeppress_fs_loaded' );
    function sweeppress_premium_support_forum_url(  $wp_org_support_forum_url  ) : string {
        return 'https://support.dev4press.com/forums/forum/plugins/sweeppress/';
    }

    if ( sweeppress_fs()->is_premium() ) {
        sweeppress_fs()->add_filter( 'support_forum_url', 'sweeppress_premium_support_forum_url' );
    }
    sweeppress_fs()->add_filter( 'pricing/disable_single_package', '__return_true' );
    sweeppress_fs()->add_filter( 'pricing/show_annual_in_monthly', '__return_false' );
    sweeppress_fs()->add_filter( 'checkout/parameters', function () {
        return array(
            'show_refund_badge'      => true,
            'show_reviews'           => true,
            'billing_cycle_selector' => 'dropdown',
        );
    } );
}