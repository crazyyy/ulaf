<?php
$title = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);
$eventType = ($event_type == "latest") ? "publish" : "future";
$eventSort = ($event_type == "latest") ? "DESC" : "ASC";

$latest_results_args = array(
    'post_status' => $eventType,
    'posts_per_page' => 1,
    'post_type' => 'sp_event',
    'order' => $eventSort,
);

if ($atts["pick_team"] != "0") {
    $latest_results_args['meta_query'] = array(
        array(
            'key' => 'sp_team',
            'value' => $atts["pick_team"],
            'compare' => '='
        )
    );
}

$latest_results_query = new WP_Query($latest_results_args);
if ($latest_results_query->post_count > 0) {
    ?>
    <div class="stm-one-event-wrapper">
        <?php

        $id = $latest_results_query->post->ID;
        $sp_league = wp_get_post_terms($latest_results_query->post->ID, 'sp_league');
        if (empty($link_bind)) {
            $link_bind = 'teams';
        }

        $team_1_full_link = $team_2_full_link = '';

        if ($atts["block_style"] == "football_style") {
            ?>
            <div class="stm_af_latest_results_wrapper">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <?php if (!empty($sp_league[0]->name)) {
                    echo '<h3>' . esc_html($sp_league[0]->name) . '</h3>';
                } ?>
                <div class="stm-latest-results-units">
                    <?php $prev_date = $prev_venue = ''; ?>
                    <?php while ($latest_results_query->have_posts()):
                        $latest_results_query->the_post();
                        /*Check if two team exist in derby*/
                        $teams = get_post_meta(get_the_ID(), 'sp_team', false);

                        $point_system = splash_get_sportpress_points_system();

                        if (count($teams) > 1):
                            $team_1_id = $teams[0];
                            $team_2_id = $teams[1];
                            $city_1 = wp_get_post_terms($team_1_id, 'sp_venue');
                            $city_2 = wp_get_post_terms($team_2_id, 'sp_venue');

                            $team_results = get_post_meta(get_the_ID(), 'sp_results', false);

                            /*GET TOPS*/
                            $players = get_post_meta(get_the_ID(), 'sp_players', false);
                            $topData = array();
                            $topDataOpponent = array();

                            if (count($players[0]) > 0) {
                                if ($players[0][$team_1_id] != "") {
                                    foreach ($players[0][$team_1_id] as $k => $val) {
                                        if ($k != 0) {
                                            if (isset($val["position"])) {

                                                $term = get_term($val["position"][0]);

                                                if (!isset($topData[$term->name])) {
                                                    if (count($topData) == 0) $topData = splash_buildTopPlayerArray($k, $val, $city_1[0]->name);
                                                    else $topData += splash_buildTopPlayerArray($k, $val, $city_1[0]->name);
                                                } else {
                                                    if ($term->name == "Wide receiver" && $topData[$term->name][0]['rec'] < $val['rec']) {
                                                        $topData = splash_buildTopPlayerArray($k, $val, $city_1[0]->name);
                                                    } elseif ($term->name == "Running Back" && $topData[$term->name][0]['yds'] < $val['yds']) {
                                                        $topData = splash_buildTopPlayerArray($k, $val, $city_1[0]->name);
                                                    } elseif ($term->name == "Quarterback" && $topData[$term->name][0]['yds'] < $val['yds']) {
                                                        $topData = splash_buildTopPlayerArray($k, $val, $city_1[0]->name);
                                                    }
                                                }

                                                /*$topData[$term->name][0]['city_code'] = strtoupper(splash_getSTMShortCityCode($city_1[0]->name));
                                                $topData[$term->name][0]['position_id'] = $val["position"][0];
                                                $topData[$term->name][0]['position_name'] = $term->name;
                                                $topData[$term->name][0]['player_name'] = get_the_title($k);
                                                $topData[$term->name][0]['yds'] = $val["yds"];
                                                $topData[$term->name][0]['rec'] = $val["rec"];
                                                if ($val["td"] == "") $topData[$term->name][0]['td'] = 0;
                                                else $topData[$term->name][0]['td'] = $val["td"];*/
                                            }
                                        }
                                    }
                                }

                                if ($players[0][$team_2_id] != "") {
                                    foreach ($players[0][$team_2_id] as $k => $val) {
                                        if ($k != 0) {
                                            if (isset($val["position"])) {
                                                $term = get_term($val["position"][0]);
                                                if (!isset($topDataOpponent[$term->name])) {
                                                    if (count($topDataOpponent) == 0) $topDataOpponent = splash_buildTopPlayerArray($k, $val, $city_2[0]->name);
                                                    else $topDataOpponent += splash_buildTopPlayerArray($k, $val, $city_2[0]->name);
                                                } else {
                                                    if ($term->name == "Wide receiver" && $topDataOpponent[$term->name][0]['rec'] < $val['rec']) {
                                                        $topDataOpponent = splash_buildTopPlayerArray($k, $val, $city_2[0]->name);
                                                    } elseif ($term->name == "Running Back" && $topDataOpponent[$term->name][0]['yds'] < $val['yds']) {
                                                        $topDataOpponent = splash_buildTopPlayerArray($k, $val, $city_2[0]->name);
                                                    } elseif ($term->name == "Quarterback" && $topDataOpponent[$term->name][0]['yds'] < $val['yds']) {
                                                        $topDataOpponent = splash_buildTopPlayerArray($k, $val, $city_2[0]->name);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /*Get teams meta*/
                            $team_1_title = get_the_title($team_1_id);
                            $team_2_title = get_the_title($team_2_id);

                            $teamHelm1 = $atts['custom_left_helm'];
                            $teamHelm2 = $atts['custom_right_helm'];

                            if ($use_default_team_helm) {
                                $teamHelm1 = get_post_meta($team_1_id, 'team_helm_image', true);
                                $teamHelm2 = get_post_meta($team_2_id, 'team_helm_image', true);
                            }

                            $team_1_url = get_permalink();
                            $team_2_url = get_permalink();

                            /*Link bind*/
                            if ($link_bind == 'teams') {
                                $team_1_full_link = '<a href="' . esc_url($team_1_url) . '">' . esc_attr($team_1_title) . '</a>';
                                $team_2_full_link = '<a href="' . esc_url($team_2_url) . '">' . esc_attr($team_2_title) . '</a>';
                            } else {
                                $team_1_full_link = '<span>' . esc_attr($team_1_title) . '</span>';
                                $team_2_full_link = '<span>' . esc_attr($team_2_title) . '</span>';
                            }
                            ?>
                            <div class="stm-mobile-teams-name">
                                <div class="row">
                                    <div class="col-sm-6 col-xs-6">
                                        <div class="stm-team-name">
                                            <div class="teamNameLeftWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_1[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_1_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-6">
                                        <div class="stm-team-name">
                                            <div class="teamNameRightWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_2[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_2_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="stm-custom-row">
                                <div class="col-20">
                                    <a href="<?php echo esc_url($team_1_url); ?>">
                                        <div class="stmLeftHelmsWrapp">
                                            <?php
                                            $attachImg = wp_get_attachment_image_src($teamHelm1, 'full');
                                            if ( !empty($attachImg[0]) ){
                                                $leftHelm = $attachImg[0];
                                            } else {
                                                $leftHelm = splash_getLocalImgUrl("helms/" . $atts["left_helms"] . ".png");
                                            }
                                            ?>
                                            <img src="<?php echo esc_url($leftHelm); ?>"/>
                                            <?php echo sp_get_logo($team_1_id, 'full', array('class' => 'stm-team-logo-left')); ?>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-60">
                                    <div class="stm-custom-row">
                                        <div class="<?php
                                        if ($eventType == "publish") {
                                            echo esc_attr('col-35');
                                        } else {
                                            echo esc_attr('col-40');
                                        }
                                        ?>">
                                            <div class="teamNameLeftWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_1[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_1_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($eventType == "publish"): ?>
                                            <div class="col-15">
                                                <a href="<?php echo esc_url($team_1_url); ?>">
                                                    <div class="stmPointsWrapp heading-font">
                                                        <?php if (!empty($team_results[0])): ?>
                                                            <?php if (!empty($team_results[0][$team_1_id])): ?>
                                                                <?php if (isset($team_results[0][$team_1_id]['outcome']) and !empty($team_results[0][$team_1_id]['outcome'][0]) and $team_results[0][$team_1_id]['outcome'][0] == 'win'): ?>
                                                                    <div class="stmPointWin">
                                                                        <?php echo esc_attr($team_results[0][$team_1_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-win-label"><?php esc_html_e('win', 'splash') ?></div>
                                                                <?php else: ?>
                                                                    <div class="stmPointLose">
                                                                        <?php echo esc_attr($team_results[0][$team_1_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-lose-label"><?php echo (!empty($team_results[0][$team_1_id]['outcome'][0]))?esc_html($team_results[0][$team_1_id]['outcome'][0]):''; ?></div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-15">
                                                <a href="<?php echo esc_url($team_1_url); ?>">
                                                    <div class="stmPointsWrapp heading-font">
                                                        <?php if (!empty($team_results[0])): ?>
                                                            <?php if (!empty($team_results[0][$team_2_id])): ?>
                                                                <?php if (isset($team_results[0][$team_2_id]['outcome']) and !empty($team_results[0][$team_2_id]['outcome'][0]) and $team_results[0][$team_2_id]['outcome'][0] == 'win'): ?>
                                                                    <div class="stmPointWin">
                                                                        <?php echo esc_attr($team_results[0][$team_2_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-win-label"><?php esc_html_e('win', 'splash') ?></div>
                                                                <?php else: ?>
                                                                    <div class="stmPointLose">
                                                                        <?php echo esc_attr($team_results[0][$team_2_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-lose-label"><?php echo (!empty($team_results[0][$team_2_id]['outcome'][0]))?esc_html($team_results[0][$team_2_id]['outcome'][0]):''; ?></div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php else : ?>
                                            <div class="col-20">
                                                <div class="stmVSWrapp heading-font">
                                                    <?php echo esc_html__("VS", "splash"); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="<?php
                                        if ($eventType == "publish") {
                                            echo esc_attr('col-35');
                                        } else {
                                            echo esc_attr('col-40');
                                        }
                                        ?>">
                                            <div class="teamNameRightWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_2[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_2_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row stm-mobile-hide">
                                        <div class="col-md-12">
                                            <div class="stmOrangeDivider"></div>
                                        </div>
                                    </div>
                                    <div class="row stm-mobile-hide">
                                        <?php if (splash_is_layout("af")) : ?>
                                            <div class="col-md-4">
                                                <div class="stmTopWrapper">
                                                    <span class="stmTopTitle heading-font"><?php esc_html_e('Top Passer', 'splash') ?></span>
                                                    <span class="stmFirstTopPlayer normal_font">
											<?php if (isset($topData['quarterback']) && $topData['quarterback'] != null) echo esc_html($topData['quarterback'][0]["city_code"] . ": " . $topData['quarterback'][0]["player_name"] . " " . $topData['quarterback'][0]["yds"] . " YDS " . $topData['quarterback'][0]["td"] . " TD") ?>
										</span>
                                                    <span class="stmSecondTopPlayer normal_font">
											<?php if (isset($topDataOpponent['quarterback']) && $topDataOpponent['quarterback'] != null) echo esc_html($topDataOpponent['quarterback'][0]["city_code"] . ": " . $topDataOpponent['quarterback'][0]["player_name"] . " " . $topDataOpponent['quarterback'][0]["yds"] . " YDS " . $topDataOpponent['quarterback'][0]["td"] . " TD") ?>
										</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="stmTopWrapper">
                                                    <span class="stmTopTitle heading-font"><?php esc_html_e('Top Rusher', 'splash') ?></span>
                                                    <span class="stmFirstTopPlayer normal_font">
											<?php if (isset($topData['running back']) && $topData['running back'] != null) echo esc_html($topData['running back'][0]["city_code"] . ": " . $topData['running back'][0]["player_name"] . " " . $topData['running back'][0]["yds"] . " YDS " . $topData['running back'][0]["td"] . " TD") ?>
										</span>
                                                    <span class="stmSecondTopPlayer normal_font">
											<?php if (isset($topDataOpponent['running back']) && $topDataOpponent['running back'] != null) echo esc_html($topDataOpponent['running back'][0]["city_code"] . ": " . $topDataOpponent['running back'][0]["player_name"] . " " . $topDataOpponent['running back'][0]["yds"] . " YDS " . $topDataOpponent['running back'][0]["td"] . " TD") ?>
										</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="stmTopWrapper">
                                                    <span class="stmTopTitle heading-font"><?php esc_html_e('Top Receiver', 'splash') ?></span>
                                                    <span class="stmFirstTopPlayer normal_font">
											<?php if (isset($topData['wide receiver']) && $topData['wide receiver'] != null) echo esc_html($topData['wide receiver'][0]["city_code"] . ": " . $topData['wide receiver'][0]["player_name"] . " " . $topData['wide receiver'][0]["rec"] . " YDS " . $topData['wide receiver'][0]["td"] . " TD") ?>
										</span>
                                                    <span class="stmSecondTopPlayer normal_font">
											<?php if (isset($topDataOpponent['wide receiver']) && $topDataOpponent['wide receiver'] != null) echo esc_html($topDataOpponent['wide receiver'][0]["city_code"] . ": " . $topDataOpponent['wide receiver'][0]["player_name"] . " " . $topDataOpponent['wide receiver'][0]["rec"] . " YDS " . $topDataOpponent['wide receiver'][0]["td"] . " TD") ?>
										</span>
                                                </div>
                                            </div>
                                        <?php elseif (splash_is_layout("sccr")) : ?>
                                            <?php
                                            $event = new SP_Event(get_the_ID());
                                            $data = $event->results();
                                            $performance = $event->performance();

                                            unset($data[0]);
                                            unset($performance[0]);

                                            if ($performance) {
                                                foreach ($performance as $key => $val) {
                                                    unset($performance[$key][0]);
                                                }
                                            }

                                            if (!empty($data)) {

                                                $sportspress_primary_result = get_option('sportspress_primary_result', null);
                                                if (!empty($sportspress_primary_result))
                                                    $goals = $sportspress_primary_result;
                                                else
                                                    $goals = "goals";

                                                $i = 0;
                                                foreach ($data as $team_id => $result) {
                                                    $i++;

                                                    $wr_class = 'col-md-6';

                                                    $output .= '<div class="' . esc_attr($wr_class) . '">';

                                                    if ($performance) {
                                                        $output .= '<ul class="players">';
                                                        foreach ($performance[$team_id] as $player_id => $player) {
                                                            if (isset($player[$goals])) {
                                                                if ($player[$goals] >= 1) {
                                                                    $output .= '<li>' . esc_html(get_the_title($player_id)) . ' - <span>' . esc_html($player[$goals]) . ' ' . esc_html__('goal(s)', 'splash') . '</span></li>';
                                                                }
                                                            }
                                                        }
                                                        $output .= '</ul>';
                                                    }
                                                    $output .= '</div>';
                                                }

                                                echo splash_sanitize_text_field($output);
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-20">
                                    <a href="<?php echo esc_url($team_1_url); ?>">
                                        <div class="stmRightHelmsWrapp">
                                            <?php
                                            $attachImg = wp_get_attachment_image_src($teamHelm2, 'full');
                                            if ( !empty($attachImg[0]) ){
                                                $rightHelm = $attachImg[0];
                                            } else {
                                                $rightHelm = splash_getLocalImgUrl("helms/" . $atts["right_helms"] . ".png");
                                            }
                                            ?>
                                            <img class="stm-helm" src="<?php echo esc_url($rightHelm); ?>"/>
                                            <?php echo sp_get_logo($team_2_id, 'full', array('class' => 'stm-team-logo-right')); ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="stm-mobile-show">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="stmOrangeDivider"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="stmTopWrapper">
                                            <span class="stmTopTitle heading-font"><?php esc_html_e('Top Passer', 'splash') ?></span>
                                            <span class="stmFirstTopPlayer normal_font">
										<?php echo esc_html($topData['Quarterback'][0]["city_code"] . ": " . $topData['Quarterback'][0]["player_name"] . " " . $topData['Quarterback'][0]["yds"] . " YDS " . $topData['Quarterback'][0]["td"] . " TD") ?>
									</span>
                                            <span class="stmSecondTopPlayer normal_font">
										<?php echo esc_html($topDataOpponent['Quarterback'][0]["city_code"] . ": " . $topDataOpponent['Quarterback'][0]["player_name"] . " " . $topDataOpponent['Quarterback'][0]["yds"] . " YDS " . $topDataOpponent['Quarterback'][0]["td"] . " TD") ?>
									</span>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="stmTopWrapper">
                                            <span class="stmTopTitle heading-font"><?php esc_html_e('Top Rusher', 'splash') ?></span>
                                            <span class="stmFirstTopPlayer normal_font">
										<?php echo esc_html($topData['Running Back'][0]["city_code"] . ": " . $topData['Running Back'][1]["player_name"] . " " . $topData['Running Back'][1]["yds"] . " YDS " . $topData['Running Back'][1]["td"] . " TD") ?>
									</span>
                                            <span class="stmSecondTopPlayer normal_font">
										<?php echo esc_html($topDataOpponent['Running Back'][0]["city_code"] . ": " . $topDataOpponent['Running Back'][0]["player_name"] . " " . $topDataOpponent['Running Back'][0]["yds"] . " YDS " . $topDataOpponent['Running Back'][0]["td"] . " TD") ?>
									</span>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="stmTopWrapper">
                                            <span class="stmTopTitle heading-font"><?php esc_html_e('Top Receiver', 'splash') ?></span>
                                            <span class="stmFirstTopPlayer normal_font">
										<?php echo esc_html($topData['Wide receiver'][0]["city_code"] . ": " . $topData['Wide receiver'][1]["player_name"] . " " . $topData['Wide receiver'][1]["rec"] . " YDS " . $topData['Wide receiver'][1]["td"] . " TD") ?>
									</span>
                                            <span class="stmSecondTopPlayer normal_font">
										<?php echo esc_html($topDataOpponent['Wide receiver'][0]["city_code"] . ": " . $topDataOpponent['Wide receiver'][0]["player_name"] . " " . $topDataOpponent['Wide receiver'][0]["rec"] . " YDS " . $topDataOpponent['Wide receiver'][0]["td"] . " TD") ?>
									</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; /*Two team exists*/ ?>
                    <?php endwhile; ?>
                </div>

                <?php wp_reset_postdata(); ?>
            </div>
            <?php
        } elseif ($atts["block_style"] == "soccer_style") { //block style SOCCER

            $event = new SP_Event($id);
            $data = $event->results();
            $performance = $event->performance();
            $date = get_the_time(get_option('date_format'), $id);
            $time = get_the_time(get_option('time_format'), $id);
            $venues = get_the_terms($id, 'sp_venue');
            unset($data[0]);
            unset($performance[0]);

            if ($performance) {
                foreach ($performance as $key => $val) {
                    unset($performance[$key][0]);
                }
            }
            if (!empty($data)) {

                $sportspress_primary_result = get_option('sportspress_primary_result', null);

                if (!empty($sportspress_primary_result))
                    $goals = $sportspress_primary_result;
                else
                    $goals = "goals";

                $futureClass = ($eventType == "publish") ? "" : "stm-soccer-show-vs";


                $output .= '<div class="vc_latest_result"><div class="fixture_detail clearfix ' . $futureClass . '">';
                $output .= '<h2>' . esc_html($title) . '</h2>';
                if (!empty($sp_league[0]->name)) {
                    $output .= '<h3>' . esc_html($sp_league[0]->name) . '</h3>';
                }
                $i = 0;
                foreach ($data as $team_id => $result) {
                    $i++;

                    $wr_class = ($i == 2) ? 'command_right' : 'command_left';
                    $w_42 = (!isset($result[$goals])) ? "w-42" : "";

                    if ($wr_class == 'command_right' && $eventType == "future") {
                        $output .= "<div class='stm-lr-vs'>" . esc_html__("VS", "splash") . "</div>";
                    }
                    $output .= '<div class="' . esc_attr($wr_class) . ' ' . $w_42 . '">';
                    $output .= '<div class="command_info">';
                    $output .= '<div class="logo"><a href="' . esc_url(get_the_permalink($team_id)) . '">' . get_the_post_thumbnail($team_id, "team_logo") . '</a></div>';
                    if (isset($result[$goals])) {
                        $output .= '<div class="score heading-font">' . esc_html($result[$goals]) . '</div>';
                    }
                    $output .= '</div>';
                    $output .= '<div class="goals">';
                    $output .= '<h2><a href="' . esc_url(get_the_permalink($team_id)) . '">' . esc_html(get_the_title($team_id)) . '</a></h2>';
                    if (isset($result['outcome'][0])) {
                        $outcome = get_page_by_path($result['outcome'][0], OBJECT, 'sp_outcome');
                        $output .= '<h4>' . get_the_title($outcome->ID) . '</h4>';
                    }
                    if ($performance) {
                        $output .= '<ul class="players">';
                        foreach ($performance[$team_id] as $player_id => $player) {
                            if (splash_is_layout("af")) {
                                $goalsList = 'td';
                                $title = esc_html__('touchdown(s)', 'splash');
                            } else {
                                $goalsList = $goals;
                                $title = esc_html__('goal(s)', 'splash');
                            }

                            if (isset($player[$goalsList])) {
                                if ($player[$goalsList] >= 1) {
                                    $output .= '<li>' . esc_html(get_the_title($player_id)) . ' - <span>' . esc_html($player[$goalsList]) . ' ' . $title . '</span></li>';
                                }
                            }
                        }
                        $output .= '</ul>';
                    }
                    $output .= '</div>';
                    $output .= '</div>';
                }
                $output .= '<div class="fixture_info">';
                $output .= '<div class="date_time">' . esc_html($date . '  | ' . $time) . '</div>';

                if ($venues != "") {

                    $output .= '<div class="venue">' . esc_html($venues[0]->name) . '</div>';

                }
                $output .= '<a class="button button-default" href="' . esc_url(get_the_permalink($id)) . '"><span>' . esc_html__('read more', 'splash') . '</span></a>';
                $output .= '</div>';
                $output .= '</div></div>';

                echo splash_sanitize_text_field($output);
            }

        } elseif ($atts["block_style"] == "baseball_style") {

            $date = get_the_time('l, F d, Y', $id);
            $time = get_the_time(get_option('time_format'), $id);
            $venues = get_the_terms($id, 'sp_venue');
            ?>
            <div class="stm_bsb_latest_results_wrapper">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <?php if(!empty($sp_league[0]->name)){
                    echo '<h4>'.esc_html($sp_league[0]->name).'</h4>';
                }?>
                <div class="stm-latest-results-units">
                    <?php $prev_date = $prev_venue = ''; ?>
                    <?php while ($latest_results_query->have_posts()):
                        $latest_results_query->the_post();
                        /*Check if two team exist in derby*/
                        $teams = get_post_meta(get_the_ID(), 'sp_team', false);

                        $point_system = splash_get_sportpress_points_system();

                        if (count($teams) > 1):
                            $team_1_id = $teams[0];
                            $team_2_id = $teams[1];
                            $city_1 = wp_get_post_terms($team_1_id, 'sp_venue');
                            $city_2 = wp_get_post_terms($team_2_id, 'sp_venue');

                            $team_results = get_post_meta(get_the_ID(), 'sp_results', false);

                            /*GET TOPS*/
                            $players = get_post_meta(get_the_ID(), 'sp_players', false);
                            $topData;

                            if (count($players[0]) > 0) {
                                if ($players[0][$team_1_id] != "") {
                                    foreach ($players[0][$team_1_id] as $k => $val) {
                                        if ($k != 0) {
                                            if (isset($val["position"])) {
                                                $term = get_term($val["position"][0]);

                                                $topData[$term->name][0]['city_code'] = strtoupper(splash_getSTMShortCityCode($city_1[0]->name));
                                                $topData[$term->name][0]['position_id'] = $val["position"][0];
                                                $topData[$term->name][0]['position_name'] = $term->name;
                                                $topData[$term->name][0]['player_name'] = get_the_title($k);
                                                $topData[$term->name][0]['yds'] = $val["yds"];
                                                if ($val["td"] == "") $topData[$term->name][0]['td'] = 0;
                                                else $topData[$term->name][0]['td'] = $val["td"];
                                            }
                                        }
                                    }
                                }

                                if ($players[0][$team_2_id] != "") {
                                    foreach ($players[0][$team_2_id] as $k => $val) {
                                        if ($k != 0) {
                                            if (isset($val["position"])) {
                                                $term = get_term($val["position"][0]);

                                                $topData[$term->name][1]['city_code'] = strtoupper(splash_getSTMShortCityCode($city_2[0]->name));
                                                $topData[$term->name][1]['position_id'] = $val["position"][0];
                                                $topData[$term->name][1]['position_name'] = $term->name;
                                                $topData[$term->name][1]['player_name'] = get_the_title($k);
                                                $topData[$term->name][1]['yds'] = $val["yds"];
                                                if ($val["td"] == "") $topData[$term->name][1]['td'] = 0;
                                                else $topData[$term->name][1]['td'] = $val["td"];
                                            }
                                        }
                                    }
                                }
                            }

                            /*Get teams meta*/
                            $team_1_title = get_the_title($team_1_id);
                            $team_2_title = get_the_title($team_2_id);

                            $team_1_url = get_permalink();
                            $team_2_url = get_permalink();

                            /*Link bind*/
                            if ($link_bind == 'teams') {
                                $team_1_full_link = '<a href="' . esc_url($team_1_url) . '">' . esc_attr($team_1_title) . '</a>';
                                $team_2_full_link = '<a href="' . esc_url($team_2_url) . '">' . esc_attr($team_2_title) . '</a>';
                            } else {
                                $team_1_full_link = '<span>' . esc_attr($team_1_title) . '</span>';
                                $team_2_full_link = '<span>' . esc_attr($team_2_title) . '</span>';
                            }
                            ?>
                            <div class="stm-mobile-teams-name">
                                <div class="row">
                                    <div class="col-sm-6 col-xs-6">
                                        <div class="stm-team-name">
                                            <div class="teamNameLeftWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_1[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_1_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-6">
                                        <div class="stm-team-name">
                                            <div class="teamNameRightWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_2[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_2_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="stm-teams-info-wrap">
                                <div class="stmLeftLogoWrapp">
                                    <a href="<?php echo esc_url($team_1_url); ?>">
                                        <?php echo sp_get_logo($team_1_id, 'full', array('class' => 'stm-team-logo-left')); ?>
                                    </a>
                                </div>
                                <div class="stm-info">
                                    <div class="stm-event-date">
                                        <div class="stm-date normal-font"><?php echo esc_html($date . '  | ' . $time); ?></div>
                                    </div>
                                    <div class="stm-custom-row">
                                        <div class="<?php
                                        if ($eventType == "future") {
                                            echo esc_attr('col-40');
                                        } else {
                                            echo esc_attr('col-35');
                                        }
                                        ?>">
                                            <div class="teamNameLeftWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_1[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_1_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="<?php
                                        if ($eventType == "future") {
                                            echo esc_attr('col-20');
                                        } else {
                                            echo esc_attr('col-30');
                                        }
                                        ?>">
                                            <div class="stmVSWrapp heading-font">
                                                <?php
                                                if ($eventType == "future"):
                                                    echo esc_html__("VS", "splash");
                                                else :
                                                    echo esc_attr($team_results[0][$team_1_id][$point_system]) . " : " . esc_attr($team_results[0][$team_2_id][$point_system]);
                                                endif;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="<?php
                                        if ($eventType == "future") {
                                            echo esc_attr('col-40');
                                        } else echo esc_attr('col-35');
                                        ?>">
                                            <div class="teamNameRightWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_2[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_2_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="stm-event-venue">
                                        <div class="stm-venue">
                                            <?php echo (!empty($venues[0]->name))?esc_html($venues[0]->name):''; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="stmRightLogoWrapp">
                                    <a href="<?php echo esc_url($team_1_url); ?>">
                                        <?php echo sp_get_logo($team_2_id, 'full', array('class' => 'stm-team-logo-right')); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; /*Two team exists*/ ?>
                    <?php endwhile; ?>
                </div>

                <?php wp_reset_postdata(); ?>
            </div>
            <?php
        } elseif ($atts["block_style"] == "basketball_two_style") {
            $date = get_the_time(get_option('date_format'), $id);
            $time = get_the_time(get_option('time_format'), $id);
            $venues = get_the_terms($id, 'sp_venue');
            ?>
            <div class="stm_af_latest_results_wrapper">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <h3><?php echo esc_html($sp_league[0]->name); ?></h3>
                <div class="stm-latest-results-units">
                    <?php $prev_date = $prev_venue = ''; ?>
                    <?php while ($latest_results_query->have_posts()):
                        $latest_results_query->the_post();
                        /*Check if two team exist in derby*/
                        $teams = get_post_meta(get_the_ID(), 'sp_team', false);

                        $point_system = splash_get_sportpress_points_system();

                        if (count($teams) > 1):
                            $team_1_id = $teams[0];
                            $team_2_id = $teams[1];
                            $city_1 = wp_get_post_terms($team_1_id, 'sp_venue');
                            $city_2 = wp_get_post_terms($team_2_id, 'sp_venue');

                            $team_results = get_post_meta(get_the_ID(), 'sp_results', false);

                            /*Get teams meta*/
                            $team_1_title = get_the_title($team_1_id);
                            $team_2_title = get_the_title($team_2_id);

                            $teamHelm1 = $atts['custom_left_helm'];
                            $teamHelm2 = $atts['custom_right_helm'];

                            if ($use_default_team_helm) {
                                $teamHelm1 = get_post_meta($team_1_id, 'team_helm_image', true);
                                $teamHelm2 = get_post_meta($team_2_id, 'team_helm_image', true);
                            }

                            $team_1_url = get_permalink();
                            $team_2_url = get_permalink();

                            /*Link bind*/
                            if ($link_bind == 'teams') {
                                $team_1_full_link = '<a href="' . esc_url($team_1_url) . '">' . esc_attr($team_1_title) . '</a>';
                                $team_2_full_link = '<a href="' . esc_url($team_2_url) . '">' . esc_attr($team_2_title) . '</a>';
                            } else {
                                $team_1_full_link = '<span>' . esc_attr($team_1_title) . '</span>';
                                $team_2_full_link = '<span>' . esc_attr($team_2_title) . '</span>';
                            }
                            ?>

                            <div class="stm-custom-row">
                                <div class="col-20">
                                    <a href="<?php echo esc_url($team_1_url); ?>">
                                        <div class="stmLeftHelmsWrapp">
                                            <?php echo sp_get_logo($team_1_id, 'full', array('class' => 'stm-team-logo-left')); ?>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-60">
                                    <div class="stm-custom-row">
                                        <div class="<?php
                                        if ($eventType == "publish") {
                                            echo esc_attr('col-30');
                                        } else {
                                            echo esc_attr('col-35');
                                        }
                                        ?>">
                                            <div class="teamNameLeftWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_1[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_1_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($eventType == "publish"): ?>
                                            <div class="col-20 left">
                                                <a href="<?php echo esc_url($team_1_url); ?>">
                                                    <div class="stmPointsWrapp heading-font">
                                                        <?php if (!empty($team_results[0])): ?>
                                                            <?php if (!empty($team_results[0][$team_1_id])): ?>
                                                                <?php if (isset($team_results[0][$team_1_id]['outcome']) and !empty($team_results[0][$team_1_id]['outcome'][0]) and $team_results[0][$team_1_id]['outcome'][0] == 'win'): ?>
                                                                    <div class="stmPointWin">
                                                                        <?php echo esc_attr($team_results[0][$team_1_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-win-label"><?php esc_html_e('win', 'splash') ?></div>
                                                                <?php else: ?>
                                                                    <div class="stmPointLose">
                                                                        <?php echo esc_attr($team_results[0][$team_1_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-lose-label"><?php echo esc_html($team_results[0][$team_1_id]['outcome'][0]); ?></div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-20 right">
                                                <a href="<?php echo esc_url($team_1_url); ?>">
                                                    <div class="stmPointsWrapp heading-font">
                                                        <?php if (!empty($team_results[0])): ?>
                                                            <?php if (!empty($team_results[0][$team_2_id])): ?>
                                                                <?php if (isset($team_results[0][$team_2_id]['outcome']) and !empty($team_results[0][$team_2_id]['outcome'][0]) and $team_results[0][$team_2_id]['outcome'][0] == 'win'): ?>
                                                                    <div class="stmPointWin">
                                                                        <?php echo esc_attr($team_results[0][$team_2_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-win-label"><?php esc_html_e('win', 'splash') ?></div>
                                                                <?php else: ?>
                                                                    <div class="stmPointLose">
                                                                        <?php echo esc_attr($team_results[0][$team_2_id][$point_system]); ?>
                                                                    </div>
                                                                    <div class="stm-latest-result-lose-label"><?php echo esc_html($team_results[0][$team_2_id]['outcome'][0]); ?></div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php else : ?>
                                            <div class="col-20">
                                                <div class="stmVSWrapp heading-font">
                                                    <?php echo esc_html__("VS", "splash"); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="<?php
                                        if ($eventType == "publish") {
                                            echo esc_attr('col-30');
                                        } else {
                                            echo esc_attr('col-35');
                                        }
                                        ?>">
                                            <div class="teamNameRightWrapper heading-font">
                                                <span class="teamHome"><?php echo esc_html($city_2[0]->name) ?></span>
                                                <div class="stm-latest-result-team">
                                                    <?php echo wp_kses_post($team_2_full_link); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="stmOrangeDivider"></div>
                                        </div>
                                    </div>
                                    <div class="latest-match-info normal_font">
                                        <?php
                                        echo esc_html( $date );
                                        if ($venues != "") echo  '<span class="divider"> | </span>' . esc_html( $venues[0]->name ) ;
                                        ?>
                                    </div>

                                </div>
                                <div class="col-20">
                                    <a href="<?php echo esc_url($team_1_url); ?>">
                                        <div class="stmRightHelmsWrapp">
                                            <?php echo sp_get_logo($team_2_id, 'full', array('class' => 'stm-team-logo-right')); ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; /*Two team exists*/ ?>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            </div>
            <?php
        }

        if ($show_btn_get_tickets == "enable") {
            ?>
            <div class="stm-get-tickets-btn">
                <a href="<?php echo esc_url($get_tickets_btn_link) ?>" class="button only_border">
                    <i class="fa fa-ticket fa-5" aria-hidden="true"></i>
                    <?php echo esc_html__("GET TICKETS", "splash") ?>
                </a>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}
?>
