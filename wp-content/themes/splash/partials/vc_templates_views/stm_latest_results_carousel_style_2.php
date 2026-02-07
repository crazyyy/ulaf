<?php
extract($atts);
splash_enqueue_modul_scripts_styles('stm_latest_results_carousel_' . $view_style);

if (empty($slide_count)) {
    $slide_count = 10;
}
wp_localize_script('stm_latest_results_carousel', 'splash_slides', array('items' => $slide_count));
if (empty($count)) {
    $count = '3';
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

$fixture_link = false;
$unqiueClass = 'stm-latest-results-carousel-'.rand(0,9999);

if ($latest_results_query->have_posts()):
?>
<div class="stm-latest-results-carousel stm-latest-results-carousel--<?php echo esc_attr($view_style) . " " . esc_attr($unqiueClass) ?>">
    <div class="stm-latest-results-carousel__wrapper">
        <div class="stm-latest-results-carousel__header">
            <h2 class="stm-latest-results-carousel__title"><?php echo esc_attr($title); ?></h2>
        </div>
        <div class="stm-latest-results-carousel__list">
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
            foreach($results[0] as $team_id => $team_results) {
                $teams[] = array(
                    'id' => $team_id,
                    'name' => get_the_title($team_id),
                    'url' => get_permalink($team_id),
                    'logo' => splash_get_thumbnail_url($team_id, '', 'full'),
                    'points' => $team_results['points'],
                    'win' => $team_results['outcome'][0] === "win"
                );
            }
            ?>

            <div class="stm-latest-results-carousel__item">
                <div class="stm-latest-results-carousel__info">
                    <span class="stm-latest-results-carousel__place"><?php echo esc_html($venue_name) ?></span>, <span class="stm-latest-results-carousel__date"><?php echo esc_html($date) ?></span>
                </div>
                <div class="stm-latest-results-carousel__teams">
                <?php foreach($teams as $i => $team): ?>
                    <?php if ($i === 1): ?>    
                    <div class="stm-latest-results-carousel__vs h3">vs</div>
                    <?php endif; ?>
                    <div class="stm-latest-results-carousel__team">
                        <div class="stm-latest-results-carousel__team__logo">
                            <img src="<?php echo esc_url($team['logo']) ?>" alt="<?php echo esc_attr($team['name']) ?>" class="stm-latest-results-carousel__team__logo-img" />
                        </div>
                        <div class="stm-latest-results-carousel__team__name heading-font"><?php echo esc_html($team['name']) ?></div>
                        <div class="stm-latest-results-carousel__team__result heading-font">
                            <div class="stm-latest-results-carousel__team__points"><?php echo esc_html($team['points']) ?></div>
                            <?php if ($team['win']): ?>
                            <div class="stm-latest-results-carousel__team__result-label">win</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div> 
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        (function ($) {
            $(document).ready(function () {
                var blockId = ".<?php echo esc_js($unqiueClass) ?>";
                var items = <?php echo esc_js($slide_count) ?>;
                var lrc = $('.stm-latest-results-carousel__list', $(blockId));
                lrc.owlCarousel({
                    items: items,
                    nav: false,
                    dots: true,
                    loop: true,
                    margin: 30,
                    responsive: {
                        0: {
                            items: 1
                        },
                        520: {
                            items: items > 2 ? 2 : items
                        },
                        1024: {
                            items: items > 3 ? 3 : items
                        },
                        1440: {
                            items: items
                        }
                    }
                });
            });
        })(jQuery);
    </script>
</div>
<?php endif; ?>