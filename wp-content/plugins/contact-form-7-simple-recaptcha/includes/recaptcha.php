<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cf7sr_key    = get_option( 'cf7sr_key' );
$cf7sr_secret = get_option( 'cf7sr_secret' );

if ( empty( $cf7sr_key ) || empty( $cf7sr_secret ) || is_admin() ) {
    return;
}

function enqueue_cf7sr_recaptcha_script() {
    $cf7sr_script_url = 'https://www.google.com/recaptcha/api.js?onload=cf7srLoadRecaptcha&render=explicit';
    $language = get_option( 'cf7sr_language' );

    if ( ! empty( $language ) && defined('CF7SR_LANGUAGES') && isset( CF7SR_LANGUAGES[ $language ] ) ) {
        $cf7sr_script_url .= '&hl=' . $language;
    }

    $cf7sr_key = get_option( 'cf7sr_key' );

    $inline_js = "
        var recaptchaIds = [];
        
        window.cf7srLoadRecaptcha = function() {
            var widgets = document.querySelectorAll('.cf7sr-g-recaptcha');
            for (var i = 0; i < widgets.length; ++i) {
                var widget = widgets[i];
                if (typeof grecaptcha !== 'undefined') {
                    recaptchaIds.push(
                        grecaptcha.render(widget.id, {
                            'sitekey' : " . wp_json_encode( $cf7sr_key ) . "
                        })
                    );
                }
            }
        };

        window.cf7srResetRecaptcha = function() {
            for (var i = 0; i < recaptchaIds.length; i++) {
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset(recaptchaIds[i]);
                }
            }
        };

        document.addEventListener('wpcf7invalid', cf7srResetRecaptcha);
        document.addEventListener('wpcf7mailsent', cf7srResetRecaptcha);
        document.addEventListener('invalid.wpcf7', cf7srResetRecaptcha);
        document.addEventListener('mailsent.wpcf7', cf7srResetRecaptcha);
    ";

    wp_enqueue_script( 'cf7sr-recaptcha-api', $cf7sr_script_url, array(), null, true );
    wp_add_inline_script( 'cf7sr-recaptcha-api', $inline_js, 'before' );
}

function cf7sr_recaptcha_shortcode( $atts ) {
    enqueue_cf7sr_recaptcha_script();

    $cf7sr_key   = get_option( 'cf7sr_key' );
    $cf7sr_theme = ! empty( $atts['theme'] ) && 'dark' == $atts['theme'] ? 'dark' : 'light';
    $cf7sr_type  = ! empty( $atts['type'] ) && 'audio' == $atts['type'] ? 'audio' : 'image';
    $cf7sr_size  = ! empty( $atts['size'] ) && 'compact' == $atts['size'] ? 'compact' : 'normal';

    $cf7sr_id       = 'cf7sr-' . uniqid();

    return '<div id="' . $cf7sr_id . '" class="cf7sr-g-recaptcha" data-theme="' . esc_attr( $cf7sr_theme ) . '" data-type="'
            . esc_attr( $cf7sr_type ) . '" data-size="' . esc_attr( $cf7sr_size ) . '" data-sitekey="' . esc_attr( $cf7sr_key )
            . '"></div><span class="wpcf7-form-control-wrap cf7sr-recaptcha" data-name="cf7sr-recaptcha"><input type="hidden" name="cf7sr-recaptcha" value="" class="wpcf7-form-control"></span>';
}
add_shortcode( 'cf7sr-simple-recaptcha', 'cf7sr_recaptcha_shortcode' );
add_shortcode( 'cf7sr-recaptcha', 'cf7sr_recaptcha_shortcode' );

function cf7sr_verify_recaptcha( $result, $tags ) {
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
    if ( false === strpos( $form_body, '[cf7sr-recaptcha' ) && false === strpos( $form_body, '[cf7sr-simple-recaptcha' ) ) {
        return $result;
    }

    $submission = WPCF7_Submission::get_instance();
    $data       = $submission->get_posted_data();

    $message = get_option( 'cf7sr_message' );
    if ( empty( $message ) ) {
        $message = 'Invalid captcha';
    }

    if ( empty( $data['g-recaptcha-response'] ) ) {
        $result->invalidate( array( 'type' => 'captcha', 'name' => 'cf7sr-recaptcha' ), $message );
        if ( function_exists( 'cf7sr_record_spam_block' ) ) {
            cf7sr_record_spam_block( 'recaptcha' );
        }
        return $result;
    }

    $request = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
        'timeout' => 15,
        'body' => array(
            'secret'   => get_option( 'cf7sr_secret' ),
            'response' => sanitize_text_field( $data['g-recaptcha-response'] ),
            'remoteip' => cf7sr_get_ip()
        ),
    ) );

    if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
        return $result;
    }

    $response = json_decode( wp_remote_retrieve_body( $request ) );

    if ( empty( $response->success ) ) {
        $result->invalidate( array( 'type' => 'captcha', 'name' => 'cf7sr-recaptcha' ), $message );
        if ( function_exists( 'cf7sr_record_spam_block' ) ) {
            cf7sr_record_spam_block( 'recaptcha' );
        }
    }

    return $result;
}
add_filter( 'wpcf7_validate', 'cf7sr_verify_recaptcha', 20, 2 );
