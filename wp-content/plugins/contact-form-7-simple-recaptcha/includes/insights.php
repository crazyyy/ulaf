<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cf7sr_add_submission_insights_to_mail($components) {
    if (empty($components['body']) || false === strpos($components['body'], '[cf7sr-insights]')) {
        return $components;
    }

    $ip = cf7sr_get_ip();

    $ua = 'Unknown';
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $ua = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
        $ua = substr($ua, 0, 255);
    }

    $form_url = 'Unknown';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $form_url = esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
    }

    $submission_time = current_time('mysql');

    $is_html = isset($components['additional_headers']) && false !== stripos($components['additional_headers'], 'Content-Type: text/html');

    $lb = $is_html ? "<br/>" : "\n";

    if ($is_html) {
        $insights = $lb . $lb . '--' . $lb;
        $insights .= 'Submission Time: ' . esc_html($submission_time) . $lb;
        $insights .= 'Sender IP: ' . esc_html($ip) . $lb;
        $insights .= 'Source Page: ' . esc_html($form_url) . $lb;
        $insights .= 'Device/Browser: ' . esc_html($ua) . $lb;
    } else {
        $insights = $lb . $lb . '--' . $lb;
        $insights .= 'Submission Time: ' . $submission_time . $lb;
        $insights .= 'Sender IP: ' . $ip . $lb;
        $insights .= 'Source Page: ' . $form_url . $lb;
        $insights .= 'Device/Browser: ' . $ua . $lb;
    }

    $components['body'] = str_replace('[cf7sr-insights]', $insights, $components['body']);

    return $components;
}

add_filter('wpcf7_mail_components', 'cf7sr_add_submission_insights_to_mail');

