<?php
extract($atts);
splash_enqueue_modul_scripts_styles('stm_latest_results_carousel');

if (empty($slide_count)) {
    $slide_count = 5;
}
wp_localize_script('stm_latest_results_carousel', 'splash_slides', array('items' => $slide_count));
if (empty($count)) {
    $count = '1';
}

$latest_results_args = array(
    'post_status' => 'publish',
    'posts_per_page' => intval($count),
    'post_type' => 'sp_event',
    'order' => 'DESC'
);

if (!empty($pick_team)) {
    $latest_results_args['meta_query'][] = array(
        'key' => 'sp_team',
        'value' => intval($pick_team),
        'compare' => 'LIKE'
    );
}

$latest_results_query = new WP_Query($latest_results_args);

if (empty($link_bind)) {
    $link_bind = 'teams';
}

$team_1_full_link = $team_2_full_link = '';

$fixture_link = false;
$venue_name = '';

if ($latest_results_query->have_posts()):
    ?>
    <div class="stm-latest-results-units stm-latest-results-units-carousel">
        <div class="stm_latest_results_carousel">
            <?php $prev_date = $prev_time = $prev_venue = ''; ?>
            <?php while ($latest_results_query->have_posts()):
                $latest_results_query->the_post();
                /*Check if two team exist in derby*/
                $teams = get_post_meta(get_the_id(), 'sp_team', false);

                $point_system = splash_get_sportpress_points_system();

                if (count($teams) > 1):
                    $team_1_id = $teams[0];
                    $team_2_id = $teams[1];

                    $team_1_url = get_permalink($team_1_id);
                    $team_2_url = get_permalink($team_2_id);
                    $team_1_img_url = splash_get_thumbnail_url($team_1_id, '', 'thumbnail');
                    $team_2_img_url = splash_get_thumbnail_url($team_2_id, '', 'thumbnail');

                    $team_results = get_post_meta(get_the_ID(), 'sp_results', false);


                    /*Get venue name*/
                    $venue = wp_get_post_terms(get_the_id(), 'sp_venue');
                    $venue_name = '';
                    if (!empty($venue) and !is_wp_error($venue)) {
                        $venue_name = $venue[0]->name;
                    }

                    /*Get teams meta*/
                    $team_1_title = get_the_title($team_1_id);
                    $team_2_title = get_the_title($team_2_id);

                    $team_1_url = get_permalink($team_1_id);
                    $team_2_url = get_permalink($team_2_id);

                    $date = new DateTime(get_the_time('Y/m/d H:i:s'));
                    $time = new DateTime(get_the_time('H:i'));
                    if ($date) {
                        $date_show = get_post_time(get_option('date_format'), false, get_the_ID(), true);
                        $time_show = get_post_time(get_option('time_format'), false, get_the_ID(), true);
                    }

                    /*Link bind*/
                    if ($link_bind == 'teams') {
                        $team_1_full_link = '<a href="' . esc_url($team_1_url) . '">' . esc_attr($team_1_title) . '</a>';
                        $team_2_full_link = '<a href="' . esc_url($team_2_url) . '">' . esc_attr($team_2_title) . '</a>';
                    } else {
                        $team_1_full_link = '<span>' . esc_attr($team_1_title) . '</span>';
                        $team_2_full_link = '<span>' . esc_attr($team_2_title) . '</span>';
                        $fixture_link = true;
                    }
                    ?>
                    <div class="stm-latest-results-item">
                    <?php if ($prev_date != $date_show || $prev_time != $time_show || $prev_venue != $venue_name): ?>
                    <div class="stm-latest-results-meta heading-font">
                        <div class="venue"><?php echo esc_attr($venue_name) . ", "; ?></div>
                        <div class="date"><?php echo esc_attr($date_show); ?></div>
                    </div>
                <?php endif; ?>
                    <?php if ($fixture_link): ?>
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="stm-no-decoration" title="<?php the_title(); ?>">
                    <?php endif; ?>
                    <div class="stm-latest-results-info heading-font">
                        <div class="stm-latest-result">
                            <div class="stm-latest-result-team">
                                <span href="<?php echo esc_url($team_1_url); ?>">
                                    <img src="<?php echo esc_url($team_1_img_url); ?>"
                                         alt="<?php echo esc_attr($team_1_title); ?>"
                                        style="max-width: 55px;">
                                </span>
                                <?php echo wp_kses_post($team_1_full_link); ?>
                                <div class="info_inline">
                                    <?php if (!empty($team_results[0])): ?>
                                        <?php if (!empty($team_results[0][$team_1_id]) and !empty($team_results[0][$team_2_id])): ?>
                                            <?php if (isset($team_results[0][$team_1_id][$point_system]) and isset($team_results[0][$team_2_id][$point_system])): ?>
                                                <div class="stm-latest-result_result">
                                                    <?php echo esc_attr($team_results[0][$team_1_id][$point_system]); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($team_results[0])): ?>
                                        <?php if (!empty($team_results[0][$team_1_id])): ?>
                                            <?php if (isset($team_results[0][$team_1_id]['outcome']) and !empty($team_results[0][$team_1_id]['outcome'][0]) and $team_results[0][$team_1_id]['outcome'][0] == 'win'): ?>
                                                <div class="stm-latest-result-win-label <?php echo (splash_is_layout("af")) ? "heading-font" : "normal-font" ?>"><?php esc_html_e('win', 'splash') ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="stm-latest-result stm-latest-result-right">
                            <div class="stm-latest-result-team">
                                <span href="<?php echo esc_url($team_2_url); ?>">
                                    <img src="<?php echo esc_url($team_2_img_url); ?>"
                                         alt="<?php echo esc_attr($team_2_title); ?>"
                                        style="max-width: 55px;">
                                </span>
                                <?php echo wp_kses_post($team_2_full_link); ?>
                                <div class="info_inline">
                                    <?php if (!empty($team_results[0])): ?>
                                        <?php if (!empty($team_results[0][$team_1_id]) and !empty($team_results[0][$team_2_id])): ?>
                                            <?php if (isset($team_results[0][$team_1_id][$point_system]) and isset($team_results[0][$team_2_id][$point_system])): ?>
                                                <div class="stm-latest-result_result"><?php echo esc_attr($team_results[0][$team_2_id][$point_system]); ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($team_results[0])): ?>
                                        <?php if (!empty($team_results[0][$team_2_id])): ?>
                                            <?php if (isset($team_results[0][$team_2_id]['outcome']) and !empty($team_results[0][$team_2_id]['outcome'][0]) and $team_results[0][$team_2_id]['outcome'][0] == 'win'): ?>
                                                <div class="stm-latest-result-win-label <?php echo (splash_is_layout("af")) ? "heading-font" : "normal-font" ?>"><?php esc_html_e('win', 'splash') ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($fixture_link): ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    $prev_date = $date_show;
                    $prev_time = $time_show;
                    $prev_venue = $venue_name;
                    ?>
                <?php endif; /*Two team exists*/ ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endif; ?>