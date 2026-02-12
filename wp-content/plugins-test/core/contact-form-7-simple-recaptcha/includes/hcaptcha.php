<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cf7sr_hc_key    = get_option( 'cf7sr_hc_key' );
$cf7sr_hc_secret = get_option( 'cf7sr_hc_secret' );

if ( empty( $cf7sr_hc_key ) || empty( $cf7sr_hc_secret ) || is_admin() ) {
    return;
}

function enqueue_cf7sr_hcaptcha_script() {
    $cf7sr_script_url = 'https://js.hcaptcha.com/1/api.js?onload=cf7srLoadHcaptcha&render=explicit';
    $language = get_option( 'cf7sr_hc_language' );

    if ( ! empty( $language ) && defined('CF7SR_LANGUAGES') && isset( CF7SR_LANGUAGES[ $language ] ) ) {
        $cf7sr_script_url .= '&hl=' . $language;
    }

    $cf7sr_hc_key = get_option( 'cf7sr_hc_key' );

    $inline_js = "
        var hcaptchaIds = [];
        window.cf7srLoadHcaptcha = function() {
            var widgets = document.querySelectorAll('.cf7sr-g-hcaptcha');
            for (var i = 0; i < widgets.length; ++i) {
                var widget = widgets[i];
                if (typeof hcaptcha !== 'undefined') {
                    hcaptchaIds.push(
                        hcaptcha.render(widget.id, {
                            'sitekey' : " . wp_json_encode( $cf7sr_hc_key ) . "
                        })
                    );
                }
            }
        };

        window.cf7srResetHcaptcha = function() {
            for (var i = 0; i < hcaptchaIds.length; i++) {
                if (typeof hcaptcha !== 'undefined') {
                    hcaptcha.reset(hcaptchaIds[i]);
                }
            }
        };

        document.addEventListener('wpcf7invalid', cf7srResetHcaptcha);
        document.addEventListener('wpcf7mailsent', cf7srResetHcaptcha);
        document.addEventListener('invalid.wpcf7', cf7srResetHcaptcha);
        document.addEventListener('mailsent.wpcf7', cf7srResetHcaptcha);
    ";

    wp_enqueue_script( 'cf7sr-hcaptcha-api', $cf7sr_script_url, array(), null, true );
    wp_add_inline_script( 'cf7sr-hcaptcha-api', $inline_js, 'before' );
}

function cf7sr_hcaptcha_shortcode( $atts ) {
    enqueue_cf7sr_hcaptcha_script();

    $cf7sr_hc_key   = get_option( 'cf7sr_hc_key' );
    $cf7sr_theme = ! empty( $atts['theme'] ) && 'dark' == $atts['theme'] ? 'dark' : 'light';
    $cf7sr_size  = ! empty( $atts['size'] ) && 'compact' == $atts['size'] ? 'compact' : 'normal';

    $cf7sr_id       = 'cf7sr-' . uniqid();

    return '<div id="' . $cf7sr_id . '" class="cf7sr-g-hcaptcha" data-theme="' . esc_attr( $cf7sr_theme ) . '" data-size="' . esc_attr( $cf7sr_size ) . '" data-sitekey="' . esc_attr( $cf7sr_hc_key )
        . '"></div><span class="wpcf7-form-control-wrap cf7sr-hcaptcha" data-name="cf7sr-hcaptcha"><input type="hidden" name="cf7sr-hcaptcha" value="" class="wpcf7-form-control"></span>';
}
add_shortcode( 'cf7sr-hcaptcha', 'cf7sr_hcaptcha_shortcode' );

function cf7sr_verify_hcaptcha( $result, $tags ) {
    if ( ! class_exists( 'WPCF7_Submission' ) ) {
        return $result;
    }

    $_wpcf7 = ! empty( $_POST['_wpcf7'] ) ? absint( $_POST['_wpcf7'] ) : 0;
    if ( empty( $_wpcf7 ) ) {
        return $result;
    }

    $contact_form = wpcf7_contact_form( $_wpcf7 );
    if ( ! $contact_form ) {
        return $result;
    }

    $form_body = $contact_form->prop( 'form' );
    if ( false === strpos( $form_body, '[cf7sr-hcaptcha' ) ) {
        return $result;
    }

    $submission = WPCF7_Submission::get_instance();
    $data       = $submission->get_posted_data();

    $message = get_option( 'cf7sr_hc_message' );
    if ( empty( $message ) ) {
        $message = 'Invalid captcha';
    }

    if ( empty( $data['h-captcha-response'] ) ) {
        $result->invalidate( array( 'type' => 'captcha', 'name' => 'cf7sr-hcaptcha' ), $message );
        if ( function_exists( 'cf7sr_record_spam_block' ) ) {
            cf7sr_record_spam_block( 'hcaptcha' );
        }
        return $result;
    }

    $request = wp_remote_post( 'https://api.hcaptcha.com/siteverify', array(
        'method'    => 'POST',
        'timeout'   => 15,
        'body'      => array(
            'secret'   => get_option( 'cf7sr_hc_secret' ),
            'response' => sanitize_text_field( $data['h-captcha-response'] ),
            'remoteip' => cf7sr_get_ip()
        ),
    ) );

    if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
        return $result;
    }

    $body     = wp_remote_retrieve_body( $request );
    $response = json_decode( $body );

    if ( empty( $response->success ) ) {
        $result->invalidate( array( 'type' => 'captcha', 'name' => 'cf7sr-hcaptcha' ), $message );
        if ( function_exists( 'cf7sr_record_spam_block' ) ) {
            cf7sr_record_spam_block( 'hcaptcha' );
        }
    }

    return $result;
}
add_filter( 'wpcf7_validate', 'cf7sr_verify_hcaptcha', 30, 2 );
