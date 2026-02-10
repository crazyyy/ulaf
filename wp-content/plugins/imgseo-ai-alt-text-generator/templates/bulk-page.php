<?php
// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Bulk Generation', 'imgseo-ai-alt-text-generator'); ?></h1>
    <p class="description">
        <?php esc_html_e('This feature automatically processes alternative texts for all images on your site.', 'imgseo-ai-alt-text-generator'); ?>
    </p>

    <div class="imgseo-stats-container" style="display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0;">
        <?php
        // Get media library stats
        // ✅ FIX: Gestisci errori se wp_count_posts() fallisce
        $imgseo_count_obj = wp_count_posts('attachment');
        $imgseo_total_images = (isset($imgseo_count_obj->inherit)) ? (int)$imgseo_count_obj->inherit : 0;

        // Count images without alt text - Real-time calculation for accuracy
        // No cache used to ensure diagnostic data is always up to date
        global $wpdb;

        // Prima ottieni tutti gli ID candidati (solo immagini senza alt-text)
        $imgseo_candidate_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT p.ID FROM $wpdb->posts p
            LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = %s
            WHERE p.post_type = %s
            AND (p.post_mime_type LIKE %s)
            AND (pm.meta_value IS NULL OR pm.meta_value = %s)",
            '_wp_attachment_image_alt',
            'attachment',
            'image/%',
            ''
        ));

        $imgseo_valid_count = 0;
        
        if (is_array($imgseo_candidate_ids) && !empty($imgseo_candidate_ids)) {
            // Poi filtra con wp_attachment_is_image() come fa il batch processor
            foreach ($imgseo_candidate_ids as $imgseo_image_id) {
                if (wp_attachment_is_image($imgseo_image_id)) {
                    $imgseo_valid_count++;
                }
            }
        }

        $imgseo_images_without_alt = $imgseo_valid_count;
        $imgseo_total_candidates = is_array($imgseo_candidate_ids) ? count($imgseo_candidate_ids) : 0;
        $imgseo_orphaned_records = $imgseo_total_candidates - $imgseo_valid_count;

        // Get available credits
        $imgseo_api_key = get_option('imgseo_api_key', '');
        $imgseo_api_verified = !empty($imgseo_api_key) && get_option('imgseo_api_verified', false);
        $imgseo_credits_raw = get_option('imgseo_credits', 0);
        $imgseo_credits = is_numeric($imgseo_credits_raw) ? (float) $imgseo_credits_raw : 0.0;
        ?>

        <div class="stats-card" style="flex: 1; min-width: 200px; padding: 20px; background-color: #f0f6fc; border-left: 5px solid #2271b1; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-images-alt2" style="font-size: 30px; color: #2271b1;"></span>
                <div>
                    <h3 style="margin: 0; font-size: 16px; color: #555;"><?php esc_html_e('Total Images', 'imgseo-ai-alt-text-generator'); ?></h3>
                    <div style="font-size: 24px; font-weight: bold; margin-top: 5px;"><?php echo esc_html($imgseo_total_images); ?></div>
                </div>
            </div>
        </div>

        <div class="stats-card" style="flex: 1; min-width: 200px; padding: 20px; background-color: #fcf0f0; border-left: 5px solid #b72121; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-warning" style="font-size: 30px; color: #b72121;"></span>
                <div>
                    <h3 style="margin: 0; font-size: 16px; color: #555;"><?php esc_html_e('Images without alt text', 'imgseo-ai-alt-text-generator'); ?></h3>
                    <div style="font-size: 24px; font-weight: bold; margin-top: 5px;"><?php echo esc_html($imgseo_images_without_alt); ?></div>
                </div>
            </div>
        </div>

        <div class="stats-card" style="flex: 1; min-width: 200px; padding: 20px; background-color: #f0fcf0; border-left: 5px solid #1eb721; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-tickets-alt" style="font-size: 30px; color: #1eb721;"></span>
                <div>
                    <h3 style="margin: 0; font-size: 16px; color: #555;"><?php esc_html_e('Available Credits', 'imgseo-ai-alt-text-generator'); ?></h3>
                    <div style="font-size: 24px; font-weight: bold; margin-top: 5px;">
                        <?php
                        // ✅ FIX: Escape corretto anche per HTML inline
                        if ($imgseo_api_verified && $imgseo_credits > 0) {
                            echo esc_html($imgseo_credits);
                        } else {
                            echo '<span style="color: #b72121;">' . esc_html('0') . '</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Check if the user has a valid API key
    $imgseo_api_key = get_option('imgseo_api_key', '');
    $imgseo_api_verified = !empty($imgseo_api_key) && get_option('imgseo_api_verified', false);
    $imgseo_credits_raw = get_option('imgseo_credits', 0);
    $imgseo_credits = is_numeric($imgseo_credits_raw) ? (float) $imgseo_credits_raw : 0.0;
    $imgseo_api_missing = empty($imgseo_api_key) || !$imgseo_api_verified; // Allows starting even with insufficient credits

    if (empty($imgseo_api_key)) {
        echo '<div class="notice notice-error">';
        echo '<p><strong>' . esc_html__('API Key missing!', 'imgseo-ai-alt-text-generator') . '</strong> ';
        echo esc_html__('To use bulk generation, you must first configure the ImgSEO API key. ', 'imgseo-ai-alt-text-generator');
        echo '<a href="' . esc_url(admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=api')) . '" class="button btn-custom-primary">' . esc_html__('Configure API Key', 'imgseo-ai-alt-text-generator') . '</a>';
        echo '</p></div>';
    } elseif (!$imgseo_api_verified) {
        echo '<div class="notice notice-error">';
        echo '<p><strong>' . esc_html__('API Key not verified!', 'imgseo-ai-alt-text-generator') . '</strong> ';
        echo esc_html__('To use bulk generation, you need to verify the API key. ', 'imgseo-ai-alt-text-generator');
        echo '<a href="' . esc_url(admin_url('admin.php?page=imgseo-ai-alt-text-generator&tab=api')) . '" class="button btn-custom-primary">' . esc_html__('Verify API Key', 'imgseo-ai-alt-text-generator') . '</a>';
        echo '</p></div>';
    } elseif ($imgseo_credits <= 0) {
        echo '<div class="notice notice-error">';
        echo '<p><strong>' . esc_html__('Insufficient credits!', 'imgseo-ai-alt-text-generator') . '</strong> ';
        echo esc_html__('You have no available credits. Proceed only if you intend to purchase credits during processing. ', 'imgseo-ai-alt-text-generator');
        echo '<a href="https://dashboard.imgseo.net/" target="_blank" class="button btn-custom-primary">' . esc_html__('Purchase credits', 'imgseo-ai-alt-text-generator') . '</a>';
        echo '</p></div>';
    } elseif ($imgseo_credits < $imgseo_images_without_alt && $imgseo_credits > 0) {
        echo '<div class="notice notice-warning">';
        echo '<p><strong>' . esc_html__('Limited credits:', 'imgseo-ai-alt-text-generator') . '</strong></p>';
        echo sprintf(
            // translators: %1$s is the number of available credits, %2$s is the number of images without alt text
            esc_html__('You have %1$s available credits, but there are %2$s images without alt text. Processing will stop when credits are exhausted. ', 'imgseo-ai-alt-text-generator'),
            '<strong>' . esc_html($imgseo_credits) . '</strong>',
            '<strong>' . esc_html($imgseo_images_without_alt) . '</strong>'
        );
        echo '<a href="https://dashboard.imgseo.net/" target="_blank" class="button btn-custom-primary">' . esc_html__('Purchase more credits', 'imgseo-ai-alt-text-generator') . '</a>';
        echo '</p></div>';
    }

    ?>

    <form id="bulk-generate-form" method="post">
        <div class="bulk-options">
            <div class="option-group">
                <label style="display: block; margin: 1rem 0;">
                    <input type="checkbox" name="overwrite" value="1">
                    <span id="imgseo-overwrite-label-bulk"><?php esc_html_e('Overwrite Existing Alt Texts', 'imgseo-ai-alt-text-generator'); ?></span>
                </label>
            </div>

            <!-- Nuova sezione: generazione metadati AI nel bulk -->
            <div class="option-group" style="margin-top: 18px;">
                <h3><?php esc_html_e('AI metadata generation', 'imgseo-ai-alt-text-generator'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Enable AI to generate additional metadata for each image during bulk processing. These options update only the selected fields and do not rename files.', 'imgseo-ai-alt-text-generator'); ?>
                </p>
                <div style="margin-left: 15px; display:flex; flex-direction:column; gap: 15px; margin-top: 15px;">
                    <!-- Base alt text indicator (always included) -->
                    <label style="display: flex; align-items: center; gap: 10px; opacity: 0.8;">
                        <input type="checkbox" checked disabled>
                        <?php esc_html_e('Generate and update alt text', 'imgseo-ai-alt-text-generator'); ?>
                        <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">1.0 credit</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="update_title" value="1" class="imgseo-metadata-field">
                        <?php esc_html_e('Generate and missing image title', 'imgseo-ai-alt-text-generator'); ?>
                        <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">+0.5 credits</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="update_caption" value="1" class="imgseo-metadata-field">
                        <?php esc_html_e('Generate and missing image caption', 'imgseo-ai-alt-text-generator'); ?>
                        <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">+0.5 credits</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="update_description" value="1" class="imgseo-metadata-field">
                        <?php esc_html_e('Generate and missing image description', 'imgseo-ai-alt-text-generator'); ?>
                        <span style="background: #6330ED; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">+0.5 credits</span>
                    </label>
                </div>
                <div style="margin: 15px 0 0 15px; padding: 12px; background: #f8f9fa; border-left: 4px solid #6330ED; border-radius: 4px;">
                    <p style="margin: 0; font-weight: 600; color: #333;">
                        <span class="dashicons dashicons-info" style="color: #6330ED;"></span>
                        <?php esc_html_e('Cost per image:', 'imgseo-ai-alt-text-generator'); ?>
                        <strong id="imgseo-cost-per-image" style="color: #6330ED;">1.0</strong> <?php esc_html_e('credits', 'imgseo-ai-alt-text-generator'); ?>
                    </p>
                    <p style="margin: 8px 0 0 0; font-size: 13px; color: #666;">
                        <?php
                        printf(
                            /* translators: %1$s: opening link tag, %2$s: closing link tag */
                            esc_html__('Base cost: 1.0 credit (alt text). Each additional field adds +0.5 credits. %1$sLearn more about pricing%2$s', 'imgseo-ai-alt-text-generator'),
                            '<a href="https://imgseo.net/#prices" target="_blank" style="color: #6330ED; text-decoration: none;">',
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
            </div>

            <div class="option-group">
                <h3><?php esc_html_e('Processing Speed', 'imgseo-ai-alt-text-generator'); ?></h3>
                <p class="description"><?php esc_html_e('Choose processing speed. Higher speeds process multiple images in parallel with overlapping requests for faster completion.', 'imgseo-ai-alt-text-generator'); ?></p>

                <div style="margin-left: 15px;">
                    <?php
                    // Get current processing speed
                    $imgseo_current_speed = get_option('imgseo_processing_speed', 'normal');
                    ?>
                    <select name="processing_speed" id="processing_speed" class="regular-text">
                        <option value="slow" <?php selected($imgseo_current_speed, 'slow'); ?>><?php esc_html_e('Slow (4 parallel requests, 1s intervals)', 'imgseo-ai-alt-text-generator'); ?></option>
                        <option value="normal" <?php selected($imgseo_current_speed, 'normal'); ?>><?php esc_html_e('Normal (6 parallel requests, 0.7s intervals)', 'imgseo-ai-alt-text-generator'); ?></option>
                        <option value="fast" <?php selected($imgseo_current_speed, 'fast'); ?>><?php esc_html_e('Fast (8 parallel requests, 0.5s intervals)', 'imgseo-ai-alt-text-generator'); ?></option>
                        <option value="ultra" <?php selected($imgseo_current_speed, 'ultra'); ?>><?php esc_html_e('Ultra (12 parallel requests, 0.4s intervals)', 'imgseo-ai-alt-text-generator'); ?></option>
                        <option value="insane" <?php selected($imgseo_current_speed, 'insane'); ?>><?php esc_html_e('Insane (16 parallel requests, 0.2s intervals) ⚠️', 'imgseo-ai-alt-text-generator'); ?></option>
                    </select>
                    <p class="description" style="margin-top: 8px; font-style: italic; color: #666;">
                        <strong><?php esc_html_e('How it works:', 'imgseo-ai-alt-text-generator'); ?></strong> <?php esc_html_e('Multiple images are processed simultaneously with overlapping requests for maximum throughput.', 'imgseo-ai-alt-text-generator'); ?><br>
                        <strong><?php esc_html_e('Performance gains:', 'imgseo-ai-alt-text-generator'); ?></strong> <?php esc_html_e('Slow: 4x faster, Normal: 6x faster, Fast: 8x faster, Ultra: 12x faster, Insane: 16x faster than sequential processing.', 'imgseo-ai-alt-text-generator'); ?><br>
                        <strong><?php esc_html_e('Recommended:', 'imgseo-ai-alt-text-generator'); ?></strong> <?php esc_html_e('Normal speed for best balance. Use Fast/Ultra for powerful servers. Insane only for high-end infrastructure.', 'imgseo-ai-alt-text-generator'); ?>
                    </p>
                </div>
                <input type="hidden" name="processing_mode" value="async">
            </div>
        </div>

        <?php
        // Show the button, but disable it if the API key is not configured
        $imgseo_btn_attrs = array(
            'id' => 'imgseo-bulk-generate'
        );
        if ($imgseo_api_missing) {
            $imgseo_btn_attrs['disabled'] = 'disabled';
            $imgseo_btn_attrs['title'] = esc_attr__('Configure the API key first to enable this feature', 'imgseo-ai-alt-text-generator');
        }
        submit_button(esc_html__('Start Generation', 'imgseo-ai-alt-text-generator'), 'btn-custom-primary', 'submit', false, $imgseo_btn_attrs);
        ?>
    </form>

    <!-- Container for credit errors (shown below button) -->
    <div id="imgseo-credit-error-container" style="display: none; margin-top: 15px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
        <p id="imgseo-credit-error-message" style="margin: 0; color: #856404; font-weight: 500;"></p>
        <p style="margin: 10px 0 0 0;">
            <a href="https://dashboard.imgseo.net/subscription" target="_blank" class="button button-primary" style="background-color: #6330ED; border-color: #6330ED; text-decoration: none;">
                <?php esc_html_e('Purchase Credits', 'imgseo-ai-alt-text-generator'); ?> →
            </a>
        </p>
    </div>

    <!-- Container for notification messages -->
    <div id="imgseo-notification-container" style="margin: 20px 0; display: none;"></div>

    <div id="progress-container" style="display:none;">
        <h2><?php esc_html_e('Processing Status:', 'imgseo-ai-alt-text-generator'); ?></h2>

        <!-- Processing Statistics -->
        <div id="processing-statistics" style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 12px 15px; margin-bottom: 15px; display: none;">
            <p style="margin: 0; font-weight: 500;" id="stats-text"></p>
        </div>

        <div id="progress-bar" style="width: 100%; background-color: #9f9f9f;">
            <div id="progress-bar-fill" style="height: 20px; width: 0%; background-color: #4caf50;"></div>
        </div>
        <p id="progress-text"></p>
        <p class="description" id="progress-description"><?php esc_html_e('Please keep this page open until processing completes.', 'imgseo-ai-alt-text-generator'); ?></p>
        <div class="button-group" style="display:flex; gap: 20px; margin-top:15px">
            <button id="imgseo-cancel" class="button btn-custom-secondary"><?php esc_html_e('Hide Monitoring', 'imgseo-ai-alt-text-generator'); ?></button>
            <button id="imgseo-stop" class="button btn-custom-disconnect" style="margin-left: 10px; color: #fff; background-color: #d63638; border-color: #d63638;"><?php esc_html_e('Stop Processing', 'imgseo-ai-alt-text-generator'); ?></button>
        </div>
    </div>


    <!-- Separator between the form/progress section and the jobs section -->
    <hr style="margin: 40px 0 30px; border-top: 1px solid #ddd; border-bottom: 0;">

    <div id="job-status-list" class="job-status-list" style="background-color: #f9f9f9; padding: 20px; border-radius: 15px; box-shadow: 0 5px 10px rgba(0, 0, 0, .09); border: 1px solid #ccd0d4; <?php echo $imgseo_api_missing ? 'display:none;' : ''; ?>">
        <h2 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 10px; color: #23282d;">
            <span class="dashicons dashicons-list-view" style="vertical-align: middle; font-size: 24px; margin-right: 5px;"></span>
            <?php esc_html_e('Recent Jobs', 'imgseo-ai-alt-text-generator'); ?>
        </h2>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <p class="description"><?php esc_html_e('History of previously started bulk processes.', 'imgseo-ai-alt-text-generator'); ?></p>
            <button id="delete-all-jobs" class="button btn-custom-disconnect"><?php esc_html_e('Delete all jobs', 'imgseo-ai-alt-text-generator'); ?></button>
        </div>
        <?php
        global $wpdb;
        $imgseo_table_name = $wpdb->prefix . 'imgseo_jobs';

        // Verifica se la tabella esiste
        $imgseo_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $imgseo_table_name)) === $imgseo_table_name;

        if ($imgseo_table_exists) {
            // Table name is safe as it's constructed with $wpdb->prefix
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $imgseo_recent_jobs = $wpdb->get_results(
                "SELECT * FROM `$imgseo_table_name` ORDER BY updated_at DESC LIMIT 10"
            );

            if ($imgseo_recent_jobs) {
                echo '<table style="border-radius:5px;" class="wp-list-table widefat fixed striped">';
                echo '<thead><tr>';
                echo '<th>ID</th>';
                echo '<th>' . esc_html__('Status', 'imgseo-ai-alt-text-generator') . '</th>';
                echo '<th>' . esc_html__('Progress', 'imgseo-ai-alt-text-generator') . '</th>';
                echo '<th>' . esc_html__('Creation Date', 'imgseo-ai-alt-text-generator') . '</th>';
                echo '<th>' . esc_html__('Last Update', 'imgseo-ai-alt-text-generator') . '</th>';
                echo '<th>' . esc_html__('Actions', 'imgseo-ai-alt-text-generator') . '</th>';
                echo '</tr></thead>';
                echo '<tbody>';

                foreach ($imgseo_recent_jobs as $imgseo_job) {
                    // Check for accurate image count for stopped/completed jobs with zero processed images
                    $imgseo_processed_count = $imgseo_job->processed_images;

                    if (($imgseo_job->status == 'stopped' || $imgseo_job->status == 'completed') && $imgseo_processed_count == 0) {
                        // Try to get count from logs
                        $imgseo_log_table_name = $wpdb->prefix . 'imgseo_logs';
                        $imgseo_log_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $imgseo_log_table_name)) === $imgseo_log_table_name;

                        if ($imgseo_log_table_exists) {
                            // Table name is safe as it's constructed with $wpdb->prefix
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                            $imgseo_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM `$imgseo_log_table_name` WHERE job_id = %s",
                                $imgseo_job->job_id
                            ));

                            if ($imgseo_count && intval($imgseo_count) > 0) {
                                $imgseo_processed_count = intval($imgseo_count);

                                // Update the database record with correct count
                                $wpdb->update(
                                    $imgseo_table_name,
                                    ['processed_images' => $imgseo_processed_count],
                                    ['id' => $imgseo_job->id]
                                );

                                // Also update the object for current display
                                $imgseo_job->processed_images = $imgseo_processed_count;
                            }
                        }
                    }

                    // ✅ FIX: Gestisci valori null/invalidi per evitare errori
                    $imgseo_total_images_safe = isset($imgseo_job->total_images) ? (int)$imgseo_job->total_images : 0;
                    $imgseo_processed_count_safe = (int)$imgseo_processed_count;

                    $imgseo_progress = ($imgseo_total_images_safe > 0) ?
                        round(($imgseo_processed_count_safe / $imgseo_total_images_safe) * 100) : 0;

                    $imgseo_status_label = '';
                    $imgseo_status_class = '';

                    switch ($imgseo_job->status) {
                        case 'pending':
                            $imgseo_status_label = esc_html__('Pending', 'imgseo-ai-alt-text-generator');
                            $imgseo_status_class = 'status-pending';
                            break;
                        case 'processing':
                            $imgseo_status_label = esc_html__('Processing', 'imgseo-ai-alt-text-generator');
                            $imgseo_status_class = 'status-processing';
                            break;
                        case 'completed':
                            $imgseo_status_label = esc_html__('Completed', 'imgseo-ai-alt-text-generator');
                            $imgseo_status_class = 'status-completed';
                            break;
                        case 'stopped':
                            $imgseo_status_label = esc_html__('Stopped', 'imgseo-ai-alt-text-generator');
                            $imgseo_status_class = 'status-stopped';
                            break;
                        default:
                            $imgseo_status_label = $imgseo_job->status;
                            break;
                    }

                    // ✅ FIX: Valida tutti i valori prima di renderizzare
                    $imgseo_job_id_safe = isset($imgseo_job->job_id) ? $imgseo_job->job_id : 'N/A';
                    $imgseo_created_at_safe = isset($imgseo_job->created_at) ? $imgseo_job->created_at : 'N/A';
                    $imgseo_updated_at_safe = isset($imgseo_job->updated_at) ? $imgseo_job->updated_at : 'N/A';

                    echo '<tr>';
                    echo '<td>' . esc_html($imgseo_job_id_safe) . '</td>';
                    echo '<td><span class="' . esc_attr($imgseo_status_class) . '">' . esc_html($imgseo_status_label) . '</span></td>';
                    echo '<td>' . esc_html($imgseo_processed_count_safe) . '/' . esc_html($imgseo_total_images_safe) . ' (' . esc_html($imgseo_progress) . '%)</td>';
                    echo '<td>' . esc_html($imgseo_created_at_safe) . '</td>';
                    echo '<td>' . esc_html($imgseo_updated_at_safe) . '</td>';
                    echo '<td>';

                    // Mostra il pulsante di stop solo per i job pending o in elaborazione
                    // ✅ FIX: Valida job_id prima di usarlo negli attributi
                    if (isset($imgseo_job->status) && ($imgseo_job->status === 'pending' || $imgseo_job->status === 'processing')) {
                        // Add a unique ID for each stop button and force the data-job-id attribute
                        $imgseo_stop_btn_id = 'stop-job-' . esc_attr($imgseo_job_id_safe);
                        echo '<button type="button" id="' . esc_attr($imgseo_stop_btn_id) . '" class="button button-small stop-job-button" data-job-id="' . esc_attr($imgseo_job_id_safe) . '" style="color: #fff; background-color: #d63638; border-color: #d63638; margin-right: 5px;margin-bottom:10px" onclick="jQuery(this).data(\'job-id\', \'' . esc_js($imgseo_job_id_safe) . '\');">' . esc_html__('Stop', 'imgseo-ai-alt-text-generator') . '</button>';
                    }

                    // Add View Log button
                    echo '<button type="button" class="button button-small view-job-log-button" data-job-id="' . esc_attr($imgseo_job_id_safe) . '" style="margin-right: 5px; margin-bottom:10px;">' . esc_html__('View Log', 'imgseo-ai-alt-text-generator') . '</button>';

                    // Always add the Delete button
                    echo '<button type="button" class="button button-small btn-custom-disconnect delete-job-button" data-job-id="' . esc_attr($imgseo_job_id_safe) . '">' . esc_html__('Delete', 'imgseo-ai-alt-text-generator') . '</button>';
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            } else {
                echo '<p>' . esc_html__('No recent jobs found.', 'imgseo-ai-alt-text-generator') . '</p>';
            }
        } else {
            echo '<p>' . esc_html__('The jobs table does not exist yet. Deactivate and reactivate the plugin to create it.', 'imgseo-ai-alt-text-generator') . '</p>';
        }
        ?>
    </div>
</div>

<!-- Modal dialog for viewing logs -->
<div id="view-log-modal" class="imgseo-modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="imgseo-modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 800px; height: 80%; display: flex; flex-direction: column; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h3 style="margin: 0; color: #23282d;">
                <span class="dashicons dashicons-media-text" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php esc_html_e('Job Log Details', 'imgseo-ai-alt-text-generator'); ?>
            </h3>
            <button id="view-log-close-button" class="button button-small"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <div id="view-log-content" style="flex: 1; overflow-y: auto; background: #f0f0f1; padding: 10px; font-family: monospace; white-space: pre-wrap; border: 1px solid #ddd;">
            <?php esc_html_e('Loading...', 'imgseo-ai-alt-text-generator'); ?>
        </div>
    </div>
</div>

<!-- Modal dialog for insufficient credits confirmation -->
<div id="credits-confirmation-modal" class="imgseo-modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="imgseo-modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #d63638;">
            <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
            <?php esc_html_e('Insufficient credits for all images', 'imgseo-ai-alt-text-generator'); ?>
        </h3>
        <p id="credits-confirmation-message"></p>
        <div style="text-align: right; margin-top: 20px;">
            <button id="credits-cancel-button" class="button btn-custom-secondary"><?php esc_html_e('Cancel', 'imgseo-ai-alt-text-generator'); ?></button>
            <button id="credits-confirm-button" class="button btn-custom-primary" style="margin-left: 10px;"><?php esc_html_e('Continue anyway', 'imgseo-ai-alt-text-generator'); ?></button>
        </div>
    </div>
</div>

<!-- Modal dialog for job interruption confirmation -->
<div id="stop-job-confirmation-modal" class="imgseo-modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="imgseo-modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #d63638;">
            <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
            <?php esc_html_e('Stop processing', 'imgseo-ai-alt-text-generator'); ?>
        </h3>
        <p id="stop-job-confirmation-message"></p>
        <div style="text-align: right; margin-top: 20px;">
            <button id="stop-job-cancel-button" class="button btn-custom-secondary"><?php esc_html_e('Cancel', 'imgseo-ai-alt-text-generator'); ?></button>
            <button id="stop-job-confirm-button" class="button btn-custom-primary" style="margin-left: 10px;"><?php esc_html_e('Stop', 'imgseo-ai-alt-text-generator'); ?></button>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Dynamic overwrite label update based on selected fields
    function updateOverwriteLabel() {
        var fields = [];

        // Alt text is always included
        fields.push('<?php echo esc_js(__('Alt Texts', 'imgseo-ai-alt-text-generator')); ?>');

        // Check if other fields are selected
        if ($('input[name="update_title"]').is(':checked')) {
            fields.push('<?php echo esc_js(__('Titles', 'imgseo-ai-alt-text-generator')); ?>');
        }
        if ($('input[name="update_caption"]').is(':checked')) {
            fields.push('<?php echo esc_js(__('Captions', 'imgseo-ai-alt-text-generator')); ?>');
        }
        if ($('input[name="update_description"]').is(':checked')) {
            fields.push('<?php echo esc_js(__('Descriptions', 'imgseo-ai-alt-text-generator')); ?>');
        }

        // Build the label text
        var labelText = '<?php echo esc_js(__('Overwrite existing:', 'imgseo-ai-alt-text-generator')); ?> ' + fields.join(', ');

        // Update the label
        $('#imgseo-overwrite-label-bulk').text(labelText);
    }

    // Initialize label on page load with delay to ensure DOM is ready
    setTimeout(function() {
        updateOverwriteLabel();

        // Update label when metadata checkboxes change
        $('.imgseo-metadata-field').on('change', function() {
            updateOverwriteLabel();
        });
    }, 100);

    // Function to show notifications instead of alerts
    function showNotification(message, type) {
        var $container = $('#imgseo-notification-container');
        var noticeClass = 'notice-info';

        if (type === 'error') {
            noticeClass = 'notice-error';
        } else if (type === 'warning') {
            noticeClass = 'notice-warning';
        } else if (type === 'success') {
            noticeClass = 'notice-success';
        }

        var $notice = $('<div class="notice ' + noticeClass + '" style="padding: 10px; margin: 10px 0;"><p>' + message + '</p></div>');

        // Add close button
        var $dismissBtn = $('<button type="button" class="notice-dismiss"></button>');
        $dismissBtn.on('click', function() {
            $notice.fadeOut(300, function() { $(this).remove(); });
        });

        $notice.append($dismissBtn);
        $container.empty().append($notice).show();

        // Scroll the page to the notification
        $('html, body').animate({
            scrollTop: $container.offset().top - 50
        }, 500);
    }
    // Global variables to store form parameters
    var formData = {};

    // Function to start image processing
    function startProcessing(isConfirmed) {
        // Hide any previous credit error messages
        $('#imgseo-credit-error-container').hide();

        // Prepare UI
        $('#progress-bar-fill').css('width', '0%');
        $('#progress-text').text("<?php esc_html_e('Starting processing...', 'imgseo-ai-alt-text-generator'); ?>");
        $('#progress-container').show();

        // Show the cron status container
        $('#cron-status-container').show();

        // Set the descriptive text based on the processing mode
        $('#progress-description').text("<?php esc_html_e('Processing takes place in real-time. Keep this page open until completion.', 'imgseo-ai-alt-text-generator'); ?>");

        // Disable the submit button
        $('#imgseo-bulk-generate').prop('disabled', true).text("<?php esc_html_e('Processing...', 'imgseo-ai-alt-text-generator'); ?>");

        // If it's a confirmation of insufficient credits, add a notification message
        if (isConfirmed) {
            showNotification('<?php esc_html_e('Processing will continue until available credits are exhausted.', 'imgseo-ai-alt-text-generator'); ?>', 'warning');
        }

        // Start the process
        $.ajax({
            url: ImgSEO.ajax_url,
            type: 'POST',
            data: {
                action: 'imgseo_start_bulk',
                overwrite: formData.overwrite,
                processing_mode: formData.processingMode,
                update_title: formData.updateTitle,
                update_description: formData.updateDescription,
                update_caption: formData.updateCaption,
                processing_speed: formData.processingSpeed,
                security: ImgSEO.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    var jobId = response.data.job_id;
                    var imageIds = response.data.image_ids;
                    $('#progress-text').text(response.data.message);

                    // Update the stop button with the job ID
                    $('#imgseo-stop').data('job-id', jobId);

                    // Asynchronous process: process directly
                    var originalTotal = response.data.original_total_images || imageIds.length;
                    var creditLimited = response.data.credit_limited || false;
                    processAsyncBatch(jobId, imageIds, originalTotal, creditLimited);
                } else {
                    var errorMessage = response.data ? response.data.message : '<?php esc_html_e('Error starting the process', 'imgseo-ai-alt-text-generator'); ?>';

                    // Check if it's a credit error - show inline warning instead of popup
                    if (response.data && (
                        response.data.phase === 'insufficient_credits_for_single_image' ||
                        response.data.phase === 'zero_images_after_limiting' ||
                        response.data.phase === 'preliminary_check_api' ||
                        response.data.phase === 'preliminary_check_local_fallback'
                    )) {
                        // Show inline credit error below button
                        $('#imgseo-credit-error-message').html(errorMessage);
                        $('#imgseo-credit-error-container').slideDown(300);

                        // Scroll to error message
                        $('html, body').animate({
                            scrollTop: $('#imgseo-credit-error-container').offset().top - 100
                        }, 500);
                    } else {
                        // For other errors, use notification system
                        showNotification(errorMessage, 'error');
                    }

                    // Se c'è un URL di reindirizzamento, reindirizza l'utente alla pagina di configurazione
                    if (response.data && response.data.redirect_url) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 1000);
                        return;
                    }

                    $('#imgseo-bulk-generate').prop('disabled', false).text("<?php esc_html_e('Start Generation', 'imgseo-ai-alt-text-generator'); ?>");
                    $('#progress-container').hide();
                }
            },
            error: function() {
                showNotification('<?php esc_html_e('Error starting the process. Please try again later.', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                $('#imgseo-bulk-generate').prop('disabled', false).text("<?php esc_html_e('Start Generation', 'imgseo-ai-alt-text-generator'); ?>");
                $('#progress-container').hide();
            }
        });
    }

    // Handle confirmation modal for insufficient credits
    $('#credits-cancel-button').on('click', function() {
        // Nascondi il modale
        $('#credits-confirmation-modal').hide();

        // Riabilita il pulsante di submit
        $('#imgseo-bulk-generate').prop('disabled', false).text("<?php esc_html_e('Start Generation', 'imgseo-ai-alt-text-generator'); ?>");
    });

    $('#credits-confirm-button').on('click', function() {
        // Nascondi il modale
        $('#credits-confirmation-modal').hide();

        // Start processing with confirmation
        startProcessing(true);
    });

    // Handle bulk generation start form
    $('#bulk-generate-form').on('submit', function(e) {
        e.preventDefault();

        // Ottieni i dati del form
        formData = {
            overwrite: $('input[name="overwrite"]').is(':checked') ? 1 : 0,
            processingMode: $('input[name="processing_mode"]:checked').val() || 'background',
            updateTitle: $('input[name="update_title"]').is(':checked') ? 1 : 0,
            updateCaption: $('input[name="update_caption"]').is(':checked') ? 1 : 0,
            updateDescription: $('input[name="update_description"]').is(':checked') ? 1 : 0,
            processingSpeed: $('select[name="processing_speed"]').val() || 'normal'
        };

        // Controlla i crediti disponibili e le immagini da elaborare
        var availableCredits = <?php echo esc_js($imgseo_credits); ?>;
        var imagesWithoutAlt = <?php echo esc_js($imgseo_images_without_alt); ?>;

        // Se non ci sono immagini da elaborare, mostra un messaggio e interrompi
        if (imagesWithoutAlt <= 0) {
            showNotification('<?php esc_html_e('No images to process. All images already have alt text.', 'imgseo-ai-alt-text-generator'); ?>', 'warning');
            return;
        }

        // If credits are insufficient but greater than zero, show confirmation dialog
        if (availableCredits > 0 && availableCredits < imagesWithoutAlt) {
            // Update confirmation message
            var message = '<?php esc_html_e('You have <strong>{credits}</strong> available credits, but there are <strong>{images}</strong> images without alt text. Only the first {credits} images will be processed and then processing will stop. Do you want to continue?', 'imgseo-ai-alt-text-generator'); ?>';
            message = message.replace('{credits}', availableCredits).replace('{images}', imagesWithoutAlt).replace('{credits}', availableCredits);
            $('#credits-confirmation-message').html(message);

            // Mostra la finestra di dialogo
            $('#credits-confirmation-modal').show();
        } else if (availableCredits <= 0) {
            // If credits are zero, show confirmation dialog
            var message = '<?php esc_html_e('You have no available credits. Processing cannot start until you purchase credits. Do you want to continue anyway?', 'imgseo-ai-alt-text-generator'); ?>';
            $('#credits-confirmation-message').html(message);

            // Mostra la finestra di dialogo
            $('#credits-confirmation-modal').show();
        } else {
            // Se i crediti sono sufficienti, avvia l'elaborazione direttamente
            startProcessing(false);
        }
    });

    // Pulsante per forzare l'esecuzione del cron
    $('#force-cron-button').on('click', function() {
        var $button = $(this);
        var $spinner = $('#cron-spinner');

        $button.prop('disabled', true);
        $spinner.css('visibility', 'visible');

        $.ajax({
            url: ImgSEO.ajax_url,
            type: 'POST',
            data: {
                action: 'imgseo_force_cron',
                security: ImgSEO.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#cron-status-text').text('<?php esc_html_e('Status: Processing started manually', 'imgseo-ai-alt-text-generator'); ?>');
                    $('#last-cron-run').text('<?php esc_html_e('Last update:', 'imgseo-ai-alt-text-generator'); ?> ' + response.data.last_run + ' (' + response.data.time_ago + ')');

                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    $('#cron-status-text').html('<span style="color: red;"><?php esc_html_e('Status: Error starting the process', 'imgseo-ai-alt-text-generator'); ?></span>');
                    showNotification('<?php esc_html_e('An error occurred while starting the process.', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                }
            },
            error: function() {
                $('#cron-status-text').html('<span style="color: red;"><?php esc_html_e('Status: Connection error', 'imgseo-ai-alt-text-generator'); ?></span>');
                showNotification('<?php esc_html_e('A connection error occurred.', 'imgseo-ai-alt-text-generator'); ?>', 'error');
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.css('visibility', 'hidden');
            }
        });
    });

    // Pulsante per interrompere l'elaborazione con gestione sicura per prevenire doppi clic
    var isStopInProgress = false;

    // Dialog modale per l'interruzione del job
    var $stopJobButton;
    var stopJobId;

    function showStopJobModal(jobId, $button) {
        stopJobId = jobId;
        $stopJobButton = $button;

        var message = '<?php esc_html_e('Are you sure you want to stop this job? This action cannot be undone.', 'imgseo-ai-alt-text-generator'); ?>';
        $('#stop-job-confirmation-message').html(message);
        $('#stop-job-confirmation-modal').show();
    }

    $('#stop-job-cancel-button').on('click', function() {
        $('#stop-job-confirmation-modal').hide();
    });

    $('#stop-job-confirm-button').on('click', function() {
        $('#stop-job-confirmation-modal').hide();

        isStopInProgress = true;

        // Disabilita tutti i pulsanti di stop
        $('#imgseo-stop, .stop-job-button').prop('disabled', true);
        $stopJobButton.text('<?php esc_html_e('Stopping...', 'imgseo-ai-alt-text-generator'); ?>');

        // Aggiorna il testo di stato
        $('#progress-text').text("<?php esc_html_e('Stopping in progress, please wait...', 'imgseo-ai-alt-text-generator'); ?>");

        // Rimuovi flag operazioni intensive quando si ferma il job
        setBulkOperationFlag(false);

        $.ajax({
            url: ImgSEO.ajax_url,
            type: 'POST',
            data: {
                action: 'imgseo_stop_job',
                job_id: stopJobId,
                security: ImgSEO.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Aggiorna il testo dello stato
                    var processedCount = response.data.processed_images || 0;
                    $('#progress-text').text('<?php esc_html_e('Processing stopped:', 'imgseo-ai-alt-text-generator'); ?> ' + processedCount + ' <?php esc_html_e('images processed', 'imgseo-ai-alt-text-generator'); ?>');

                    // Reload page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    isStopInProgress = false;
                    $('#imgseo-stop, .stop-job-button').prop('disabled', false);
                    $stopJobButton.text('<?php esc_html_e('Stop Processing', 'imgseo-ai-alt-text-generator'); ?>');
                    showNotification(response.data ? response.data.message : '<?php esc_html_e('Error stopping the job', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                }
            },
            error: function() {
                isStopInProgress = false;
                $('#imgseo-stop, .stop-job-button').prop('disabled', false);
                $stopJobButton.text('<?php esc_html_e('Stop Processing', 'imgseo-ai-alt-text-generator'); ?>');
                showNotification('<?php esc_html_e('Connection error while stopping the job', 'imgseo-ai-alt-text-generator'); ?>', 'error');
            }
        });
    });

    $('#imgseo-stop, .stop-job-button').on('click', function() {
        // Previeni clic multipli
        if (isStopInProgress) {
            return;
        }

        var $button = $(this);
        var jobId = $button.data('job-id');

        if (!jobId) {
            return;
        }

        // IMMEDIATE STOP - No confirmation modal
        isStopInProgress = true;

        // Set stop flag immediately to prevent new requests
        if (typeof isStopped !== 'undefined') {
            isStopped = true;
        }

        // Disabilita tutti i pulsanti di stop
        $('#imgseo-stop, .stop-job-button').prop('disabled', true);
        $button.text('<?php esc_html_e('Stopping...', 'imgseo-ai-alt-text-generator'); ?>');

        // Aggiorna il testo di stato
        $('#progress-text').text("<?php esc_html_e('Stopping...', 'imgseo-ai-alt-text-generator'); ?>");

        // Rimuovi flag operazioni intensive quando si ferma il job
        setBulkOperationFlag(false);

        $.ajax({
            url: ImgSEO.ajax_url,
            type: 'POST',
            data: {
                action: 'imgseo_stop_job',
                job_id: jobId,
                security: ImgSEO.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Aggiorna il testo dello stato
                    var processedCount = response.data.processed_images || 0;
                    $('#progress-text').text('<?php esc_html_e('Processing stopped:', 'imgseo-ai-alt-text-generator'); ?> ' + processedCount + ' <?php esc_html_e('images processed', 'imgseo-ai-alt-text-generator'); ?>');
                    showNotification('<?php esc_html_e('Processing stopped successfully', 'imgseo-ai-alt-text-generator'); ?>', 'success');

                    // Fast reload - reduced delay
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                } else {
                    isStopInProgress = false;
                    if (typeof isStopped !== 'undefined') {
                        isStopped = false;
                    }
                    $('#imgseo-stop, .stop-job-button').prop('disabled', false);
                    $button.text('<?php esc_html_e('Stop Processing', 'imgseo-ai-alt-text-generator'); ?>');
                    showNotification(response.data ? response.data.message : '<?php esc_html_e('Error stopping the job', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                }
            },
            error: function() {
                isStopInProgress = false;
                if (typeof isStopped !== 'undefined') {
                    isStopped = false;
                }
                $('#imgseo-stop, .stop-job-button').prop('disabled', false);
                $button.text('<?php esc_html_e('Stop Processing', 'imgseo-ai-alt-text-generator'); ?>');
                showNotification('<?php esc_html_e('Connection error while stopping the job', 'imgseo-ai-alt-text-generator'); ?>', 'error');
            }
        });
    });

    // Button to delete a job
    $('.delete-job-button').on('click', function() {
        var jobId = $(this).data('job-id');

        if (!jobId) {
            return;
        }

        if (confirm('<?php esc_html_e('Are you sure you want to delete this job?', 'imgseo-ai-alt-text-generator'); ?>')) {
            var $button = $(this);
            $button.prop('disabled', true).text('<?php esc_html_e('Deleting...', 'imgseo-ai-alt-text-generator'); ?>');

            $.ajax({
                url: ImgSEO.ajax_url,
                type: 'POST',
                data: {
                    action: 'imgseo_delete_job',
                    job_id: jobId,
                    security: ImgSEO.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        $button.prop('disabled', false).text('<?php esc_html_e('Delete', 'imgseo-ai-alt-text-generator'); ?>');
                        showNotification(response.data ? response.data.message : '<?php esc_html_e('Error deleting the job', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                    }
                },
                error: function() {
                    $button.prop('disabled', false).text('<?php esc_html_e('Delete', 'imgseo-ai-alt-text-generator'); ?>');
                    showNotification('<?php esc_html_e('Connection error while deleting the job', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                }
            });
        }
    });

    // Button to delete all jobs
    $('#delete-all-jobs').on('click', function() {
        if (confirm('<?php esc_html_e('Are you sure you want to delete all jobs?', 'imgseo-ai-alt-text-generator'); ?>')) {
            var $button = $(this);
            $button.prop('disabled', true).text('<?php esc_html_e('Deleting in progress...', 'imgseo-ai-alt-text-generator'); ?>');

            $.ajax({
                url: ImgSEO.ajax_url,
                type: 'POST',
                data: {
                    action: 'imgseo_delete_all_jobs',
                    security: ImgSEO.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        $button.prop('disabled', false).text('<?php esc_html_e('Delete all jobs', 'imgseo-ai-alt-text-generator'); ?>');
                        showNotification('<?php esc_html_e('Error deleting jobs', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                    }
                },
                error: function() {
                    $button.prop('disabled', false).text('<?php esc_html_e('Delete all jobs', 'imgseo-ai-alt-text-generator'); ?>');
                    showNotification('<?php esc_html_e('Connection error while deleting jobs', 'imgseo-ai-alt-text-generator'); ?>', 'error');
                }
            });
        }
    });

    // Button to view job log
    $('.view-job-log-button').on('click', function() {
        var jobId = $(this).data('job-id');
        if (!jobId) return;

        $('#view-log-content').text('<?php esc_html_e('Loading...', 'imgseo-ai-alt-text-generator'); ?>');
        $('#view-log-modal').show();

        $.ajax({
            url: ImgSEO.ajax_url,
            type: 'POST',
            data: {
                action: 'imgseo_view_job_log',
                job_id: jobId,
                security: ImgSEO.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#view-log-content').text(response.data.log_content);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php esc_html_e('Error loading log', 'imgseo-ai-alt-text-generator'); ?>';
                    $('#view-log-content').text(errorMsg);
                }
            },
            error: function() {
                $('#view-log-content').text('<?php esc_html_e('Connection error', 'imgseo-ai-alt-text-generator'); ?>');
            }
        });
    });

    $('#view-log-close-button').on('click', function() {
        $('#view-log-modal').hide();
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#view-log-modal')) {
            $('#view-log-modal').hide();
        }
    });

    // Funzione per monitorare lo stato del job con polling frequente
    function monitorJobStatusWithPolling(jobId) {
        var $progressBar = $('#progress-bar-fill');
        var $progressText = $('#progress-text');
        var $cronStatusText = $('#cron-status-text');
        var lastLogId = 0;
        var pollingInterval;

        // Contenitore per i log se non esiste
        if ($('#processing-logs-container').length === 0) {
            $('#progress-container').after('<div id="processing-logs-container" class="log-container"><h3>Real-time Processing Log</h3><div id="processing-logs" class="log-entries"></div></div>');
        }
        var $logsContainer = $('#processing-logs');

        function checkJobStatus() {
            $.ajax({
                url: ImgSEO.ajax_url,
                type: 'POST',
                data: {
                    action: 'imgseo_check_job_status',
                    job_id: jobId,
                    last_log_id: lastLogId,
                    security: ImgSEO.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Aggiorna la barra di progresso
                        $progressBar.css('width', response.data.progress + '%');
                        $progressText.text(response.data.message);

                        // Aggiorna lo stato del cron
                        var status = response.data.status;
                        switch(status) {
                            case 'pending':
                                $cronStatusText.text('<?php esc_html_e('Status: Waiting', 'imgseo-ai-alt-text-generator'); ?>');
                                break;
                            case 'processing':
                                $cronStatusText.text('<?php esc_html_e('Status: Processing', 'imgseo-ai-alt-text-generator'); ?>');
                                break;
                            case 'completed':
                                $cronStatusText.text('<?php esc_html_e('Status: Completed', 'imgseo-ai-alt-text-generator'); ?>');
                                break;
                            case 'stopped':
                                $cronStatusText.text('<?php esc_html_e('Status: Stopped', 'imgseo-ai-alt-text-generator'); ?>');
                                break;
                            default:
                                $cronStatusText.text('<?php esc_html_e('Status:', 'imgseo-ai-alt-text-generator'); ?> ' + status);
                        }

                        // Aggiorna i log se presenti
                        if (response.data.logs && response.data.logs.length > 0) {
                            response.data.logs.forEach(function(log) {
                                var statusClass = log.status === 'success' ? 'log-success' : 'log-error';
                                var logText = log.alt_text;

                                // Add metadata fields to log text if present
                                var metadataLines = [];
                                if (log.title) {
                                    metadataLines.push('Title: ' + log.title);
                                }
                                if (log.caption) {
                                    metadataLines.push('Caption: ' + log.caption);
                                }
                                if (log.description) {
                                    metadataLines.push('Description: ' + log.description);
                                }

                                if (metadataLines.length > 0) {
                                    logText += ' | ' + metadataLines.join(' | ');
                                }

                                var logEntry =
                                    '<div class="log-entry ' + statusClass + '">' +
                                    '<span class="log-time">' + formatTime(log.time) + '</span>' +
                                    '<span class="log-filename">' + log.filename + '</span>' +
                                    '<span class="log-text" title="' + logText.replace(/"/g, '&quot;') + '">' +
                                    logText + '</span>' +
                                    '</div>';
                                $logsContainer.append(logEntry);
                            });

                            // Scrolla in fondo
                            $logsContainer.scrollTop($logsContainer[0].scrollHeight);

                            // Aggiorna l'ultimo ID di log
                            if (response.data.logs.length > 0) {
                                var lastLog = response.data.logs[response.data.logs.length - 1];
                                lastLogId = lastLog.id;
                            }
                        }

                        // Controlla se il job è completo
                        if (status === 'completed' || status === 'stopped') {
                            clearInterval(pollingInterval);

                            // Mostra una notifica
                            var notificationType = status === 'completed' ? 'success' : 'warning';
                            var notificationMessage = status === 'completed' ?
                                '<?php esc_html_e('Processing completed successfully!', 'imgseo-ai-alt-text-generator'); ?>' :
                                '<?php esc_html_e('Processing stopped by user.', 'imgseo-ai-alt-text-generator'); ?>';

                            setTimeout(function() {
                                showNotification(notificationMessage, notificationType);
                            }, 1000);

                            if (status === 'completed') {
                                $progressBar.css('width', '100%');
                            }
                        }
                    }
                },
                error: function() {
                    // Gestisci errori in modo silenzioso
                    console.log('Connection error while checking job status');
                }
            });
        }

        // Avvia il polling con intervalli intelligenti
        checkJobStatus(); // Prima chiamata immediata
        // Intervallo ottimizzato per prestazioni
        pollingInterval = setInterval(checkJobStatus, 6000); // Ogni 6 secondi
    }

    // Segnala l'inizio di operazioni bulk per evitare conflitti SEO
    function setBulkOperationFlag(active) {
        $.ajax({
            url: ImgSEO.ajax_url,
            type: 'POST',
            data: {
                action: 'imgseo_set_bulk_flag',
                active: active ? 1 : 0,
                security: ImgSEO.nonce
            },
            async: true // Non bloccare l'esecuzione
        });
    }

    // Funzione per elaborare il batch asincrono
    function processAsyncBatch(jobId, imageIds, originalTotalImages, creditLimited) {
        // Segnala l'inizio delle operazioni intensive
        setBulkOperationFlag(true);
        var $progressBar = $('#progress-bar-fill');
        var $progressText = $('#progress-text');

        // Contenitore per i log se non esiste
        if ($('#processing-logs-container').length === 0) {
            $('#progress-container').after('<div id="processing-logs-container" class="log-container"><h3>Real-time Processing Log</h3><div id="processing-logs" class="log-entries"></div></div>');
        }
        var $logsContainer = $('#processing-logs');

        var totalImages = imageIds.length;
        var originalTotal = originalTotalImages || totalImages;
        var isLimited = creditLimited || false;
        var processedCount = 0;
        var activeRequests = 0;
        var currentIndex = 0;
        var isStopped = false;
        
        // Imposta la velocità di elaborazione
        var speed = formData.processingSpeed;
        var parallelRequests = 6; // Default normal
        var interval = 700; // Default normal

        switch (speed) {
            case 'slow':
                parallelRequests = 4;
                interval = 1000;
                break;
            case 'fast':
                parallelRequests = 8;
                interval = 500;
                break;
            case 'ultra':
                parallelRequests = 12;
                interval = 400;
                break;
            case 'insane':
                parallelRequests = 16;
                interval = 200;
                break;
        }

        // Gestione errori globale per assicurarsi che il flag venga sempre pulito
        function cleanupBulkOperations() {
            setBulkOperationFlag(false);
        }

        // Cleanup automatico in caso di errori o chiusura pagina
        window.addEventListener('beforeunload', cleanupBulkOperations);
        window.addEventListener('error', cleanupBulkOperations);

        // Funzione per processare un singolo batch
        function processNext() {
            if (isStopped || currentIndex >= totalImages) {
                if (activeRequests === 0 && !isStopped) {
                    // Tutto completato
                    completeJob();
                }
                return;
            }

            // Lancia nuovi worker finché non raggiungiamo il limite parallelo
            while (activeRequests < parallelRequests && currentIndex < totalImages && !isStopped) {
                var imageInfo = imageIds[currentIndex];
                
                // Robust ID extraction to handle both object and direct ID formats
                var imageId = imageInfo;
                if (typeof imageInfo === 'object' && imageInfo !== null) {
                    // Try to extract ID from common properties
                    imageId = imageInfo.id || imageInfo.ID;
                }
                
                // Skip invalid IDs or if we still have an object (failed extraction)
                if (!imageId || typeof imageId === 'object') {
                    console.error('ImgSEO: Invalid image ID found:', imageInfo);
                    
                    // Add log entry for invalid ID so user knows something was skipped
                    var logEntry =
                        '<div class="log-entry log-error">' +
                        '<span class="log-time">' + formatTime(new Date().getTime() / 1000) + '</span>' +
                        '<span class="log-filename">Error</span>' +
                        '<span class="log-text">Skipped invalid image data</span>' +
                        '</div>';
                    $logsContainer.append(logEntry);
                    
                    currentIndex++;
                    continue;
                }
                
                processImage(imageId, currentIndex);
                currentIndex++;
                activeRequests++;
            }
        }

        function processImage(imageId, index) {
            $.ajax({
                url: ImgSEO.ajax_url,
                type: 'POST',
                data: {
                    action: 'imgseo_process_single_image',
                    image_id: imageId,
                    job_id: jobId,
                    // Pass current settings to ensure they are used
                    update_title: formData.updateTitle,
                    update_caption: formData.updateCaption,
                    update_description: formData.updateDescription,
                    overwrite: formData.overwrite,
                    security: ImgSEO.nonce
                },
                success: function(response) {
                    activeRequests--;
                    processedCount++;

                    // CRITICAL: Check credits from API response and auto-stop if exhausted
                    if (response.data && response.data.credits_remaining !== undefined) {
                        var creditsRemaining = parseFloat(response.data.credits_remaining);

                        // Auto-stop if credits are insufficient for next image (using cost_per_image calculation)
                        var costPerImage = 1.0;
                        if (formData.updateTitle) costPerImage += 0.5;
                        if (formData.updateCaption) costPerImage += 0.5;
                        if (formData.updateDescription) costPerImage += 0.5;

                        if (creditsRemaining < costPerImage && currentIndex < totalImages) {
                            isStopped = true;
                            $progressText.text('<?php esc_html_e('Processing stopped: Insufficient credits remaining.', 'imgseo-ai-alt-text-generator'); ?>');
                            showNotification('Processing stopped automatically - insufficient credits remaining (' + creditsRemaining.toFixed(1) + ' credits left, ' + costPerImage.toFixed(1) + ' needed per image)', 'warning');
                            completeJob(); // Mark as completed with current count
                            return;
                        }
                    }

                    // Check for insufficient_credits error
                    if (!response.success && response.data && response.data.error_type === 'insufficient_credits') {
                        isStopped = true;
                        $progressText.text('<?php esc_html_e('Processing stopped: Insufficient credits.', 'imgseo-ai-alt-text-generator'); ?>');
                        showNotification('Processing stopped - ' + response.data.message, 'error');
                        completeJob();
                        return;
                    }

                    // Aggiorna UI
                    var percent = Math.round((processedCount / totalImages) * 100);
                    $progressBar.css('width', percent + '%');

                    // Build progress message with limitation info if applicable
                    var progressMsg = '<?php esc_html_e('Processing:', 'imgseo-ai-alt-text-generator'); ?> ' + processedCount + '/' + totalImages;
                    if (isLimited) {
                        progressMsg += ' (limited due to insufficient credits - total: ' + originalTotal + ' images)';
                    }
                    progressMsg += ' (' + percent + '%)';
                    $progressText.text(progressMsg);

                    // Log
                    if (response.success && response.data) {
                        var statusClass = response.data.status === 'success' ? 'log-success' : 'log-error';
                        var logText = response.data.alt_text;

                        // Add metadata info to log if generated
                        var metadataLines = [];
                        if (response.data.title) {
                            metadataLines.push('Title: ' + response.data.title);
                        }
                        if (response.data.caption) {
                            metadataLines.push('Caption: ' + response.data.caption);
                        }
                        if (response.data.description) {
                            metadataLines.push('Description: ' + response.data.description);
                        }

                        if (metadataLines.length > 0) {
                            logText += ' | ' + metadataLines.join(' | ');
                        }

                        var logEntry =
                            '<div class="log-entry ' + statusClass + '">' +
                            '<span class="log-time">' + formatTime(new Date().getTime() / 1000) + '</span>' +
                            '<span class="log-filename">' + (response.data.filename || 'ID: ' + imageId) + '</span>' +
                            '<span class="log-text" title="' + logText.replace(/"/g, '&quot;') + '">' +
                            logText + '</span>' +
                            '</div>';
                        $logsContainer.append(logEntry);
                        $logsContainer.scrollTop($logsContainer[0].scrollHeight);
                    } else {
                        // Log errore
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        var logEntry =
                            '<div class="log-entry log-error">' +
                            '<span class="log-time">' + formatTime(new Date().getTime() / 1000) + '</span>' +
                            '<span class="log-filename">ID: ' + imageId + '</span>' +
                            '<span class="log-text">' + errorMsg + '</span>' +
                            '</div>';
                        $logsContainer.append(logEntry);
                    }

                    // Check stop status periodically (every 10 images)
                    if (processedCount % 10 === 0) {
                        checkStopStatus();
                    } else {
                        // Continue processing
                        setTimeout(processNext, interval);
                    }
                },
                error: function() {
                    activeRequests--;
                    processedCount++;
                    
                    var logEntry =
                        '<div class="log-entry log-error">' +
                        '<span class="log-time">' + formatTime(new Date().getTime() / 1000) + '</span>' +
                        '<span class="log-filename">ID: ' + imageId + '</span>' +
                        '<span class="log-text">Connection error</span>' +
                        '</div>';
                    $logsContainer.append(logEntry);
                    
                    setTimeout(processNext, interval);
                }
            });
        }

        function checkStopStatus() {
            $.ajax({
                url: ImgSEO.ajax_url,
                type: 'POST',
                data: {
                    action: 'imgseo_check_job_status',
                    job_id: jobId,
                    last_log_id: 0,
                    security: ImgSEO.nonce
                },
                success: function(statusResponse) {
                    if (statusResponse.success && statusResponse.data) {
                        if (statusResponse.data.status === 'stopped') {
                            isStopped = true;
                            $progressText.text('<?php esc_html_e('Processing stopped by user.', 'imgseo-ai-alt-text-generator'); ?>');
                            showNotification('<?php esc_html_e('Processing stopped by user.', 'imgseo-ai-alt-text-generator'); ?>', 'warning');
                            $('#imgseo-bulk-generate').prop('disabled', false).text('<?php esc_html_e('Start Generation', 'imgseo-ai-alt-text-generator'); ?>');
                            return;
                        }
                    }
                    // Continue processing
                    setTimeout(processNext, interval);
                },
                error: function() {
                    // Continue anyway on error
                    setTimeout(processNext, interval);
                }
            });
        }

        function completeJob() {
            // Segna il job come completato nel DB
            $.ajax({
                url: ImgSEO.ajax_url,
                type: 'POST',
                data: {
                    action: 'imgseo_stop_job', // Usa stop_job ma con status completed
                    job_id: jobId,
                    completion_status: 'completed',
                    processed_count: processedCount,
                    security: ImgSEO.nonce
                },
                success: function() {
                    $progressBar.css('width', '100%');

                    // Build completion message with limitation info if applicable
                    var completionMsg = '<?php esc_html_e('Processing completed successfully!', 'imgseo-ai-alt-text-generator'); ?>';
                    if (isLimited) {
                        completionMsg += ' Processed ' + processedCount + ' of ' + originalTotal + ' images (limited by available credits).';
                    }

                    $progressText.text(completionMsg);
                    showNotification(completionMsg, 'success');

                    // Rimuovi flag operazioni intensive
                    setBulkOperationFlag(false);

                    // Reload after short delay
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            });
            
            $('#imgseo-bulk-generate').prop('disabled', false).text('<?php esc_html_e('Start Generation', 'imgseo-ai-alt-text-generator'); ?>');
        }

        // Start processing
        processNext();
    }

    // Funzione per formattare il tempo
    function formatTime(timestamp) {
        var date = new Date(timestamp * 1000);
        var hours = date.getHours().toString().padStart(2, '0');
        var minutes = date.getMinutes().toString().padStart(2, '0');
        var seconds = date.getSeconds().toString().padStart(2, '0');
        return hours + ':' + minutes + ':' + seconds;
    }

    // Funzione per troncare il testo
    function truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }

    // Pulsante per nascondere il monitoraggio
    $('#imgseo-cancel').on('click', function() {
        $('#progress-container').hide();
        $('#cron-status-container').hide();
        $('#processing-logs-container').hide();
    });

    // Calcolo dinamico del costo per immagine basato sui campi selezionati
    function updateCostPerImage() {
        var baseCost = 1.0; // Alt text (sempre incluso)
        var extraFieldsCost = 0.0;

        // Conta i campi aggiuntivi selezionati
        $('.imgseo-metadata-field:checked').each(function() {
            extraFieldsCost += 0.5;
        });

        var totalCost = baseCost + extraFieldsCost;
        $('#imgseo-cost-per-image').text(totalCost.toFixed(1));
    }

    // Aggiorna il costo quando i checkbox cambiano
    $('.imgseo-metadata-field').on('change', updateCostPerImage);

    // Inizializza il costo al caricamento della pagina
    updateCostPerImage();

});
</script>
</div>
