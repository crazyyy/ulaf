<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cf7sr_key_v3    = get_option( 'cf7sr_key_v3' );
$cf7sr_secret_v3 = get_option( 'cf7sr_secret_v3' );

if ( empty( $cf7sr_key_v3 ) || empty( $cf7sr_secret_v3 ) || is_admin() ) {
    return;
}

function enqueue_cf7sr_recaptcha_v3_script() {
    $cf7sr_key_v3    = get_option( 'cf7sr_key_v3' );

    $cf7sr_script_url = 'https://www.google.com/recaptcha/api.js?render=' . $cf7sr_key_v3;

    $inline_js = "
            (function() {
                var cf7sr_v3_retry_count = 0;
                var getToken = function() {
                    if (typeof grecaptcha === 'undefined' || typeof grecaptcha.execute === 'undefined') {
                        if (cf7sr_v3_retry_count < 10) {
                            cf7sr_v3_retry_count++;
                            setTimeout(getToken, 500);
                        }
                        return;
                    }
                    grecaptcha.ready(function() {
                        grecaptcha.execute('" . esc_js($cf7sr_key_v3) . "', {action: 'contact_form'}).then(function(token) {
                            var inputs = document.querySelectorAll('input[name=\"cf7sr-v3-token\"]');
                            for (var i = 0; i < inputs.length; i++) {
                                inputs[i].value = token;
                            }
                        });
                    });
                };
                getToken();
                setInterval(getToken, 90000);
            })();";

    wp_enqueue_script('cf7sr-recaptcha-v3-api', $cf7sr_script_url, array(), null, true);
    wp_add_inline_script('cf7sr-recaptcha-v3-api', $inline_js);
}

function cf7sr_recaptcha_v3_shortcode( $atts ) {
    $cf7sr_id = 'cf7sr-v3-' . uniqid();

    enqueue_cf7sr_recaptcha_v3_script();

    return '<div id="' . esc_attr($cf7sr_id) . '"><input type="hidden" name="cf7sr-v3-token" value=""></div>';
}
add_shortcode( 'cf7sr-v3-recaptcha', 'cf7sr_recaptcha_v3_shortcode' );

function cf7sr_verify_recaptcha_v3( $result, $tags ) {
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
    if ( false === strpos( $form_body, '[cf7sr-v3-recaptcha' ) ) {
        return $result;
    }

    $submission = WPCF7_Submission::get_instance();
    $data       = $submission->get_posted_data();

    $threshold = (float) get_option( 'cf7sr_score_v3' );

    $message = get_option( 'cf7sr_message_v3' );
    if ( empty( $message ) ) {
        $message = 'Invalid captcha';
    }

    if (empty($data['cf7sr-v3-token'])) {
        cf7sr_trigger_spam($message);
        cf7sr_record_spam_block( 'recaptcha-v3' );
        return $result;
    }

    $request = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
        'timeout' => 15,
        'body' => array(
            'secret'   => get_option( 'cf7sr_secret_v3' ),
            'response' => sanitize_text_field($data['cf7sr-v3-token']),
            'remoteip' => cf7sr_get_ip()
        ),
    ));

    if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
        return $result;
    }

    $response = json_decode( wp_remote_retrieve_body( $request ) );

    if ( empty($response->success) || $response->score < $threshold ) {
        cf7sr_trigger_spam($message);
        cf7sr_record_spam_block( 'recaptcha-v3' );
    }

    return $result;
}
add_filter( 'wpcf7_validate', 'cf7sr_verify_recaptcha_v3', 20, 2 );
