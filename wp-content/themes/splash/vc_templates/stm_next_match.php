<?php
$title = $show_games = $count = $pick_team = '';
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);
splash_enqueue_modul_scripts_styles('stm_next_match');

if (empty($count) || $atts["view_type"] == 'single') {
    $count = '1';
}

$next_match_args = array(
    'post_status' => 'future',
    'posts_per_page' => intval($count),
    'post_type' => 'sp_event',
    'order' => 'ASC'
);

if (!empty($pick_team)) {
    $next_match_args['meta_query'][] = array(
        'key' => 'sp_team',
        'value' => intval($pick_team),
        'compare' => 'IN'
    );
}
$next_match_query = new WP_Query($next_match_args);

$rand_id = 'stm-next-match-' . rand(0, 9999);
?>
<!--Looping through next matches-->
<?php
if ($next_match_query->have_posts()): ?>
    <?php if ($atts["view_type"] == "slider"): ?>
        <div class="<?php echo esc_attr($rand_id); ?> stm-next-match-wrapper">
            <h3 class="stm-next-match-title"><?php echo esc_attr($title); ?></h3>
            <?php if ($next_match_query->found_posts > 1 && !splash_is_layout('hockey')): ?>
                <div class="stm-next-match-controls">
                    <div class="stm-next-match-prev disabled"><i class="fa fa-angle-left"></i></div>
                    <div class="stm-next-match-pagination heading-font"><span
                                class="current">1</span>/<?php echo esc_attr($count); ?></div>
                    <div class="stm-next-match-next"><i class="fa fa-angle-right"></i></div>
                </div>
            <?php endif; ?>
            <?php if ($next_match_query->found_posts > 1 && splash_is_layout('hockey')): ?>
                <div class="stm-carousel-controls-right stm-thophies-controls">
                    <div class="stm-carousel-control-prev stm-next-match-prev"><i class="fa fa-angle-left"></i></div>
                    <div class="stm-carousel-control-next stm-next-match-next"><i class="fa fa-angle-right"></i></div>
                </div>
            <?php endif; ?>
            <div class="stm-next-match-units">
                <?php while ($next_match_query->have_posts()):
                    $next_match_query->the_post();
                    /*Check if two team exist in derby*/
                    $teams = get_post_meta(get_the_id(), 'sp_team', false);
                    if (count($teams) > 1): ?>
                        <?php
                        /* Get league names */
                        $leagues = wp_get_post_terms(get_the_id(), 'sp_league');

                        $leagues_names = array();
                        if (!empty($leagues)) {
                            foreach ($leagues as $league) {
                                $leagues_names[] = $league->name;
                            }
                        }

                        /*Get venue name*/
                        $venue = wp_get_post_terms(get_the_id(), 'sp_venue');
                        $venue_name = '';
                        if (!empty($venue) and !is_wp_error($venue)) {
                            $venue_name = $venue[0]->name;
                        }
                        ?>

                        <div class="stm-next-match-unit">
                            <?php if (!splash_is_layout('hockey')): ?>
                            <a href="<?php echo esc_url(get_the_permalink()); ?>" class="stm-no-decoration">
                                <?php endif; ?>
                                <div class="stm-next-match-time">
                                    <?php

                                    $date = new DateTime(get_the_time('Y/m/d H:i:s'));
                                    if ($date) {

                                        $date_show = get_post_time(sp_date_format() . ' - ' . sp_time_format(), false, get_the_ID(), true);
                                        $date = $date->format('Y-m-d H:i:s');
                                    }
                                    $d = date('Y-m-d H:i:s', strtotime(splash_add_timezone() . ' minute', strtotime(get_the_date("Y-m-d H:s:i"))));
                                    $post = get_post(get_the_ID());
                                    ?>
                                    <time class="heading-font" datetime="<?php echo esc_attr($post->post_date); ?>"
                                          data-countdown="<?php echo esc_attr(str_replace('-', '/', get_gmt_from_date($post->post_date))) ?>"></time>
                                </div>

                                <div class="stm-next-match-main-meta">
                                    <?php if (!empty($images)): ?>
                                        <div class="stm-next-matches_bg"
                                             style="background-image: url(<?php echo esc_url(splash_get_thumbnail_url(0, $images, 'full')); ?>);"></div>
                                    <?php endif; ?>
                                    <div class="stm-next-match-opponents-units">
                                        <?php if (splash_is_layout('hockey')): ?>
                                            <?php if (!empty($venue_name)): ?>
                                                <div class="stm-next-match-venue heading-font">
                                                    <?php echo esc_attr($venue_name); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="stm-next-match-info heading-font">
                                                <?php echo esc_attr(implode(',', $leagues_names) . ' ' . $date_show); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="stm-next-match-opponents">
                                            <?php
                                            $size = (splash_is_layout('magazine_one')) ? 'full' : 'stm-200-200';

                                            /*Get teams meta*/
                                            $team_1_title = get_the_title($teams[0]);
                                            $team_1_image = splash_get_thumbnail_url($teams[0], '', $size);
                                            $team_1_url = get_permalink($teams[0]);

                                            $team_2_title = get_the_title($teams[1]);
                                            $team_2_image = splash_get_thumbnail_url($teams[1], '', $size);
                                            $team_2_url = get_permalink($teams[1]);

                                            ?>

                                            <div class="stm-command">
                                                <?php if (!empty($team_1_image)): ?>
                                                    <div class="stm-command-logo">
                                                        <!--<a href="<?php /*echo esc_url($team_1_url); */ ?>">-->
                                                        <img src="<?php echo esc_url($team_1_image); ?>"
                                                             alt="<?php echo esc_attr($team_1_title); ?>"/>
                                                        <!--</a>-->
                                                    </div>
                                                <?php endif; ?>
                                                <div class="stm-command-title">
                                                    <h4>
                                                        <!--<a href="<?php /*echo esc_url($team_1_url); */ ?>">-->
                                                        <?php echo esc_attr($team_1_title); ?>
                                                        <!--</a>-->
                                                    </h4>
                                                </div>
                                            </div>

                                            <div class="stm-command-vs">
                                                <span><?php esc_html_e('vs', 'splash'); ?></span>
                                            </div>

                                            <div class="stm-command stm-command-right">
                                                <div class="stm-command-title">
                                                    <h4>
                                                        <!--<a href="<?php /*echo esc_url($team_1_url); */ ?>">-->
                                                        <?php echo esc_attr($team_2_title); ?>
                                                        <!--</a>-->
                                                    </h4>
                                                </div>
                                                <?php if (!empty($team_2_image)): ?>
                                                    <div class="stm-command-logo">
                                                        <!--<a href="<?php /*echo esc_url($team_2_url); */ ?>">-->
                                                        <img src="<?php echo esc_url($team_2_image); ?>"
                                                             alt="<?php echo esc_attr($team_2_title); ?>"/>
                                                        <!--</a>-->
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (splash_is_layout('hockey')): ?>
                                        <div class="link_btn">
                                            <a class="button btn-primary btn-md btn-style-5"
                                               href="<?php echo esc_url(get_the_permalink()); ?>"
                                               title="JOIN US"><?php echo esc_html__('Learn more', 'splash'); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!splash_is_layout('hockey')): ?>
                                        <div class="stm-next-match-info heading-font">
                                            <?php echo esc_attr(implode(',', $leagues_names) . ' ' . $date_show); ?>
                                        </div>

                                        <?php if (!empty($venue_name)): ?>
                                            <div class="stm-next-match-venue heading-font">
                                                <?php echo esc_attr($venue_name); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!splash_is_layout('hockey')): ?>
                            </a>
                        <?php endif; ?>

                        </div>
                    <?php endif; /*Two team exists*/ ?>
                <?php endwhile; ?>
            </div>
        </div>

        <script type="text/javascript">
            (function ($) {
                "use strict";

                var unique_class = "<?php echo esc_js($rand_id); ?>";

                var owl = $('.' + unique_class + ' .stm-next-match-units');

                $(document).ready(function () {
                    owl.owlCarousel({
                        items: 1,
                        autoplay: false,
                        slideBy: 1
                    });

                    $('.' + unique_class + ' .stm-next-match-prev').on('click', function () {
                        owl.trigger('prev.owl.carousel');
                    });

                    $('.' + unique_class + ' .stm-next-match-next').on('click', function () {
                        owl.trigger('next.owl.carousel');
                    });

                    owl.on('changed.owl.carousel', function (event) {

                        var current = parseInt(event.item.index + 1);
                        var total = parseInt(event.item.count);


                        if (current == 1) {
                            $('.' + unique_class + ' .stm-next-match-prev').addClass('disabled');
                        } else {
                            $('.' + unique_class + ' .stm-next-match-prev').removeClass('disabled');
                        }

                        if (current === total) {
                            $('.' + unique_class + ' .stm-next-match-next').addClass('disabled');
                        } else {
                            $('.' + unique_class + ' .stm-next-match-next').removeClass('disabled');
                        }

                        $('.stm-next-match-pagination .current').text(current);
                    })

                });
            })(jQuery);
        </script>
    <?php
    elseif ($atts["view_type"] == "blocks"):
        $output .= '<div class="vc_next_match style_blocks">';
        if ($title) {
            $output .= '<div class="title"><h4>' . $title . '</h4></div>';
        }

        while ($next_match_query->have_posts()) {
            $next_match_query->the_post();
            $id = get_the_ID();
            $event = new SP_Event($id);
            $data = $event->results();
            unset($data[0]);
            $venues = get_the_terms($id, 'sp_venue');

            $now = new DateTime(get_the_time('Y/m/d H:i:s'));
            $date = new DateTime(get_the_date("Y-m-d H:s:i"));
            $interval = date_diff($now, $date);


            $startTime = date("Y-m-d H:i:s");

            $d = date('Y-m-d H:i:s', strtotime(splash_add_timezone() . ' minute', strtotime(get_the_date("Y-m-d H:s:i"))));


            $days = $interval->invert ? 0 : $interval->days;
            $h = $interval->invert ? 0 : $interval->h;
            $i = $interval->invert ? 0 : $interval->i;
            $s = $interval->invert ? 0 : $interval->s;


            if ($days >= 10) {
                $output .= '<p class="countdown sp-countdown long-countdown clear">';
            } else {
                $output .= '<p class="countdown sp-countdown clear">';
            }
            $post = get_post($id);
            $output .= '<time datetime="' . esc_attr($post->post_date) . '"  data-countdown="' . esc_attr(str_replace('-', '/', get_gmt_from_date($post->post_date))) . '">';
            $output .= '<span>' . sprintf('%02s', $days) . ' <small>' . __('days', 'splash') . '</small></span>';
            $output .= '<span>' . sprintf('%02s', $h) . ' <small>' . __('hrs', 'splash') . '</small></span>';
            $output .= '<span>' . sprintf('%02s', $i) . ' <small>' . __('mins', 'splash') . '</small></span>';
            $output .= '<span>' . sprintf('%02s', $s) . ' <small>' . __('secs', 'splash') . '</small></span>';
            $output .= '</time>';
            $output .= '</p>';

            $output .= '<div class="commands">';

            $i = 0;
            foreach ($data as $team_id => $result) {
                $i++;
                if($i > 2) continue;
                $output .= '<div class="command">';
                $output .= '<div class="logo"><a href="' . esc_url(get_the_permalink($team_id)) . '">' . get_the_post_thumbnail($team_id, 'team_logo') . '</a></div>';
                $output .= '<h5><a href="' . esc_url(get_the_permalink($team_id)) . '">' . get_the_title($team_id) . '</a></h5>';
                $output .= '</div>';

                if ($i == 1) {
                    $output .= '<div class="command_vs heading-font"><span>-</span> ' . __('VS', 'splash') . ' <span>-</span></div>';
                }

            }
            $output .= '</div>';

            $output .= "<a href='" . esc_url(get_the_permalink($id)) . "'>";
            $output .= '<div class="match_info">';
            $output .= get_the_time(get_option('date_format'), $id) . '<br/>';
            if ($venues) {
                foreach ($venues as $venue) {
                    $output .= '<span class="venue">' . $venue->name . '</span>';
                }
            }
            $output .= '</div>';
            $output .= '</a>';
        }
        $output .= '</div>';


        echo splash_sanitize_text_field($output);
        ?>
    <?php
    elseif ($atts["view_type"] == "single"):
        $output .= '<div class="vc_next_match single-view" style="background-image: url( ' . esc_url(splash_get_thumbnail_url(0, $images, 'full')) . ');">';
        $output .= '<div class="vc_next_match__container">';
        while ($next_match_query->have_posts()) {
            $next_match_query->the_post();
            $id = get_the_ID();
            $event = new SP_Event($id);
            $data = $event->results();
            unset($data[0]);
            $venues = get_the_terms($id, 'sp_venue');

            $now = new DateTime(get_the_time('Y/m/d H:i:s'));
            $date = new DateTime(get_the_date("Y-m-d H:s:i"));
            $interval = date_diff($now, $date);

            $days = $interval->invert ? 0 : $interval->days;
            $h = $interval->invert ? 0 : $interval->h;
            $i = $interval->invert ? 0 : $interval->i;
            $s = $interval->invert ? 0 : $interval->s;
            $d = date('Y-m-d H:i:s', strtotime(splash_add_timezone() . ' minute', strtotime(get_the_date("Y-m-d H:s:i"))));
            if ($days >= 10) {
                $output .= '<p class="countdown sp-countdown long-countdown clear">';
            } else {
                $output .= '<p class="countdown sp-countdown clear">';
            }
            $post = get_post($id);
            $output .= '<time datetime="' . $post->post_date . '"  data-countdown="' . str_replace('-', '/', get_gmt_from_date($post->post_date)) . '">';
            $output .= '<span class="heading-font">' . sprintf('%02s', $days) . ' <small class="normal-font">' . __('days', 'splash') . '</small></span>';
            $output .= '<span class="heading-font">' . sprintf('%02s', $h) . ' <small class="normal-font">' . __('hrs', 'splash') . '</small></span>';
            $output .= '<span class="heading-font">' . sprintf('%02s', $i) . ' <small class="normal-font">' . __('mins', 'splash') . '</small></span>';
            $output .= '<span class="heading-font">' . sprintf('%02s', $s) . ' <small class="normal-font">' . __('secs', 'splash') . '</small></span>';
            $output .= '</time>';
            $output .= '</p>';

            $output .= '<div class="commands">';

            $i = 0;
            foreach ($data as $team_id => $result) {
                $i++;

                $output .= '<div class="command">';
                $output .= '<div class="logo"><a href="' . esc_url(get_the_permalink($team_id)) . '">' . get_the_post_thumbnail($team_id, 'thumbnail') . '</a></div>';
                $output .= '</div>';

                if ($i == 1) {
                    if ($title) {
                        $output .= '<div class="title"><h4>' . $title . '</h4><div class="command_vs heading-font"><span>-</span> ' . __('VS', 'splash') . ' <span>-</span></div></div>';
                    } else {
                        $output .= '<div class="command_vs heading-font"><span>-</span> ' . __('VS', 'splash') . ' <span>-</span></div>';
                    }

                }

            }
            $output .= '</div>';

            $output .= "<a href='" . esc_url(get_the_permalink($id)) . "' class='button btn-only-border'>";
            $output .= esc_html('Read more', 'splash');
            $output .= '<i class="icon-arrow-right"></i>';
            $output .= '</a>';
        }
        $output .= '</div>';
        $output .= '</div>';
        echo splash_sanitize_text_field($output);
    endif; ?>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>

