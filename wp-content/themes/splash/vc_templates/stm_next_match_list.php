<?php
$title = $show_games = $count = $pick_team = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

splash_enqueue_modul_scripts_styles('stm_next_match_list');

$style = $atts["view_type"];

$count = ($count > 0) ? $count : 2;

if ($style == 'style_3') $count = 3;

$next_match_args = array(
    'post_status'    => 'future',
    'posts_per_page' => intval($count),
    'post_type'      => 'sp_event',
    'order'          => 'ASC'
);

if(!empty($pick_team)) {
    $next_match_args['meta_query'][] = array(
        'key' => 'sp_team',
        'value' => intval($pick_team),
        'compare' => 'IN'
    );
}

$next_match_query = new WP_Query($next_match_args);
$follow_link = $atts['follow_link'];
$rand_id = 'stm-next-match';

?>

    <!--Looping through next matches-->
<?php
if($next_match_query->have_posts()): ?>
    <?php if($style == "style_one"): ?>
    <div class="<?php echo esc_attr($rand_id); ?>">
        <?php $now = new DateTime( current_time( 'mysql', 0 ) ); ?>
        <div class="stm-next-match-header">
            <h3 class="stm-next-match-title"><?php echo esc_attr($title); ?></h3>
            <span class="stm-next-match-upcoming">
                <a href="<?php echo esc_url($follow_link); ?>"><?php esc_html_e('upcoming events', 'splash'); ?></a>
            </span>
        </div>
        <div class="stm-next-match-units">
            <?php while($next_match_query->have_posts()):
                $next_match_query->the_post();
                /*Check if two team exist in derby*/
                $teams = get_post_meta(get_the_id(), 'sp_team', false);
                if(count($teams) > 1): ?>
                    <?php
                    /* Get league names */
                    $leagues = wp_get_post_terms(get_the_id(), 'sp_league');

                    $leagues_names = array();
                    if(!empty($leagues)) {
                        foreach($leagues as $league) {
                            $leagues_names[] = $league->name;
                        }
                    }

                    /*Get venue name*/
                    $venue = wp_get_post_terms(get_the_id(), 'sp_venue');
                    $venue_name = '';
                    if(!empty($venue) and !is_wp_error($venue)) {
                        $venue_name = $venue[0]->name;
                    }
                    ?>

                    <div class="stm-next-match-unit">
                        <?php
						$date = get_the_time( get_option('date_format'), get_the_ID() );
						$time = get_the_time(get_option('time_format'), get_the_ID());
						$date_show = $date . " " . $time;
                        ?>
                        <div class="stm-next-match-main-meta">
                            <?php if(!empty($images)): ?>
                                <div class="stm-next-matches_bg" style="background-image: url(<?php echo esc_url(splash_get_thumbnail_url(0, $images, 'full')); ?>);"></div>
                            <?php endif; ?>
                            <div class="stm-next-match-opponents-units">
                                <div class="stm-next-match-opponents">
                                    <?php
                                    /*Get teams meta*/
                                    $team_1_title = get_the_title($teams[0]);
                                    $team_1_image = splash_get_thumbnail_url($teams[0], "", 'gallery_thumbnail');
                                    $team_1_url = get_permalink($teams[0]);
                                    $venue_1 = wp_get_post_terms($teams[0], 'sp_venue');
                                    $team_1_home = $venue_1[0]->name;

                                    $team_2_title = get_the_title($teams[1]);
                                    $team_2_image = splash_get_thumbnail_url($teams[1], "", 'gallery_thumbnail');
                                    $team_2_url = get_permalink($teams[1]);
                                    $venue_2 = wp_get_post_terms($teams[1], 'sp_venue');
                                    $team_2_home = $venue_2[0]->name;

                                    ?>

                                    <div class="stm-command">
                                        <?php if(!empty($team_1_image)): ?>
                                            <div class="stm-command-logo">
                                                <img src="<?php echo esc_url($team_1_image); ?>" alt="<?php echo esc_attr($team_1_title); ?>"/>
                                            </div>
                                        <?php endif; ?>
                                        <div class="stm-command-title">
                                            <h5>
                                                <?php echo esc_html($team_1_home); ?>
                                            </h5>
                                            <h4>
                                                <?php echo esc_attr($team_1_title); ?>
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="stm-command-vs"><span><?php esc_html_e('vs', 'splash'); ?></span></div>

                                    <div class="stm-command stm-command-right">
                                        <div class="stm-command-title">
                                            <h5>
                                                <?php echo esc_html($team_2_home); ?>
                                            </h5>
                                            <h4>
                                                <?php echo esc_attr($team_2_title); ?>
                                            </h4>
                                        </div>
                                        <?php if(!empty($team_2_image)): ?>
                                            <div class="stm-command-logo">
                                                <img src="<?php echo esc_url($team_2_image); ?>" alt="<?php echo esc_attr($team_2_title); ?>" />
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="stm-next-match-info-wrapp">
                                <div class="stm-next-match-info  <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal-font"?>">
                                    <?php echo esc_attr($date_show) . ' <span class="vertical-divider"></span> <span class="brown">' . esc_html($venue_name) . '</span>'; ?>
                                </div>
                                <div class="stm-next-match-preview">
                                    <a href="<?php echo esc_url(get_the_permalink()); ?>"><img src="<?php echo esc_html(splash_getLocalImgUrl("ico_camera.svg")); ?>" /> Preview</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; /*Two team exists*/ ?>
            <?php endwhile; ?>
        </div>
    </div>
    <?php
    elseif ($style == 'style_two'):
        $output .= '<div class="vc_upcoming_fixtures">';
        if( $title ){
            $output .= '<div class="title"><h4>'.$title.'</h4></div>';
        }
        while($next_match_query->have_posts()){
            $next_match_query->the_post();
            $id = get_the_ID();

            $event = new SP_Event( $id );
            $data = $event->results();
            unset( $data[0] );
            $venues = get_the_terms( $id, 'sp_venue' );

            $output .= '<div class="commands">';

            $i=0; foreach($data as $team_id => $result) { $i++;

                $output .= '<div class="command">';
                $output .= '<h5><a href="'.esc_url( get_the_permalink($team_id) ) .'">'.get_the_title( $team_id ).'</a></h5>';
                $output .= '</div>';

                if($i == 1){
                    $output .= '<div class="command_vs heading-font"><span>-</span> '.__('VS', 'splash').' <span>-</span></div>';
                }

            }
            $output .= '</div>';
            $output .= '<a href="' . esc_url( get_the_permalink($id) ) . '">';
            $output .= '<div class="match_info">';
            $output .= get_the_time( get_option('date_format'), $id ).' | '.get_the_time( get_option('time_format'), $id ).'<br/>';
            if($venues){
                foreach( $venues as $venue ){
                    $output .= $venue->name;
                }
            }
            $output .= '</div>';
            $output .= '</a>';


        }
        $output .= '</div>';

        echo splash_sanitize_text_field($output);
    
    elseif ($style == 'style_3'):
        $classes = "upcoming-matches upcoming-matches--" . $style;
    ?>

    <div class="<?php echo esc_attr($classes) ?>">
        <div class="upcoming-matches__wrapper">
            <div class="upcoming-matches__header">
                <h2><?php echo esc_attr($title); ?></h2>
            </div>

            <div class="upcoming-matches__list row">
            <?php while($next_match_query->have_posts()):
                $next_match_query->the_post(); ?>
                <div class="upcoming-matches__item">
                    <?php
                    $id = get_the_id();
                    $teams = get_post_meta($id, 'sp_team', false);
                    $leagues = wp_get_post_terms($id, 'sp_league');
                    $leagues_names = array();
                    if(!empty($leagues)) {
                        foreach($leagues as $league) {
                            $leagues_names[] = $league->name;
                        }
                    }
                    $venue = wp_get_post_terms($id, 'sp_venue');
                    $venue_name = '';
                    if(!empty($venue) && !is_wp_error($venue)) {
                        $venue_name = $venue[0]->name;
                    }
                    $date = get_the_time( "F d, Y", $id);
                    ?>
                    <div class="upcoming-matches__teams">
                        <div class="upcoming-matches__team">
                            <div class="upcoming-matches__team__logo">
                                <img src="<?php echo splash_get_thumbnail_url($teams[0], "", 'gallery_thumbnail'); ?>" alt="<?php echo get_the_title($teams[0]); ?>" class="upcoming-matches__team__logo-img" />
                            </div>
                            <div class="upcoming-matches__team__name"><?php echo get_the_title($teams[0]); ?></div>
                        </div>
                        <div class="upcoming-matches__vs h3">vs</div>
                        <div class="upcoming-matches__team">
                            <div class="upcoming-matches__team__logo">
                                <img src="<?php echo splash_get_thumbnail_url($teams[1], "", 'gallery_thumbnail'); ?>" alt="<?php echo get_the_title($teams[1]); ?>" class="upcoming-matches__team__logo-img" />
                            </div>
                            <div class="upcoming-matches__team__name"><?php echo get_the_title($teams[1]); ?></div>
                        </div>
                    </div>
                    <div class="upcoming-matches__info">
                        <div class="upcoming-matches__league"><strong><?php echo implode(", ", $leagues_names) ?></strong></div>
                        <span class="upcoming-matches__place"><?php echo esc_html($venue_name) ?></span>, <span class="upcoming-matches__date"><?php echo esc_attr($date) ?></span>
                    </div>
                    <a href="<?php echo esc_url(get_post_permalink($id, false, true)); ?>" class="upcoming-matches__button"><?php esc_html_e('read more', 'splash'); ?></a>
                </div>
            <?php endwhile; ?>
            </div>

        </div>
    </div>

    <?php
    endif;
    ?>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>