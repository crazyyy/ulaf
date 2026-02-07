<?php
extract($atts);
if (empty($count)) {
    $count = '1';
}

splash_enqueue_modul_scripts_styles( 'stm_latest_results' );

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

if ($latest_results_query->have_posts()):
?>
<div class="stm-latest-results stm-latest-results--volleyball">
    <div class="stm-latest-results__wrapper">
        <h2 class="stm-latest-results__title"><?php echo esc_html($title); ?></h2>
        <div class="stm-latest-results__list">
        <?php 
        $prev_date = $prev_time = $prev_venue = '';
        while ($latest_results_query->have_posts()):
            $latest_results_query->the_post();
            $id = get_the_id();

            // getting place 
            $venue = wp_get_post_terms($id, 'sp_venue');
            $venue_name = '';
            if (!empty($venue) and !is_wp_error($venue)) {
                $venue_name = $venue[0]->name;
            }

            // getting date
            $date = get_the_date('F d, Y');
            
            //getting results
            $results = get_post_meta($id, 'sp_results');
            
            //getting teams
            $teams = array();

            if ( !empty( $results[0] ) ) :

                foreach($results[0] as $team_id => $team_results) {
                    $teams[] = array(
                        'id' => $team_id,
                        'name' => get_the_title($team_id),
                        'url' => get_permalink($team_id),
                        'logo' => splash_get_thumbnail_url($team_id, '', 'stm-200-200'),
                        'points' => $team_results['points'],
                        'win' => $team_results['outcome'][0] === "win"
                    );
                }
                ?>

                <div class="stm-latest-results__item">
                    <div class="stm-latest-results__info">
                        <span class="stm-latest-results__place"><?php echo esc_html($venue_name) ?></span>, <span class="stm-latest-results__date"><?php echo esc_html($date) ?></span>
                    </div>
                    <div class="stm-latest-results__teams">
                    <?php foreach($teams as $i => $team): ?>
                        <?php if ($i === 1): ?>
                        <div class="stm-latest-results__vs h3">vs</div>
                        <?php endif; ?>
                        <div class="stm-latest-results__team">
                            <div class="stm-latest-results__team__logo">
                                <img src="<?php echo esc_url($team['logo']) ?>" alt="<?php echo esc_attr($team['name']) ?>" class="stm-latest-results__team__logo-img" />
                            </div>
                            <div class="stm-latest-results__team__name heading-font"><?php echo esc_html($team['name']) ?></div>
                            <div class="stm-latest-results__team__result heading-font">
                                <div class="stm-latest-results__team__points"><?php echo esc_html($team['points']) ?></div>
                                <?php if ($team['win']): ?>
                                <div class="stm-latest-results__team__result-label">win</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
        </div>
    </div>
</div>
<?php endif; ?>