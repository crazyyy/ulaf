<?php
add_action('wp_ajax_divewp_get_resource_results', function() {
    $upload_dir = wp_upload_dir();
    $results_file = $upload_dir['basedir'] . '/divewp-resource-test-results.json';
    if (file_exists($results_file)) {
        $json = file_get_contents($results_file);
        $data = json_decode($json, true);
        if ($data) {
            wp_send_json_success(['results' => $data]);
        } else {
            wp_send_json_error(['message' => 'Could not decode results JSON.']);
        }
    } else {
        wp_send_json_error(['message' => 'Results file not found.']);
    }
    wp_die();
}); 