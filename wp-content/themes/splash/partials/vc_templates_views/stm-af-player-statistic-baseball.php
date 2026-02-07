<?php
/**
 * Created by PhpStorm.
 * Date: 28.08.2017
 * Time: 16:14
 */
$title = $league = $season = $playerList = '';
$players = array();

extract($atts);

if ($atts["league"] != "") $league = $atts["league"];
if ($atts["season"] != "") $season = $atts["season"];
$players = ( $atts["players"] != "" ) ? explode( ', ', $atts['players'] ) : array();
$seasonName = get_term_by('id', $season, 'sp_season');
if(!empty($seasonName)){
    $seasonName = $seasonName->name;
}
if ( $atts["player_lists"] != "" && $atts["set_view_by"] == 'player_lists' ) {
    $playerList = new SP_Player_List($atts['player_lists']);
    $i = 0;
    foreach ($playerList->data(false) as $k => $val) {
        if ($k != 0) {
            $players[$i] = $k;
            $i++;
        }
    }
}

?>
<div class="stm-media-tabs stm-statistic-tabs stm-player-statistic-baseball">
    <?php if (!empty($title)): ?>
        <div class="stm-title-float">
            <h2 class="stm-main-title-unit white"><?php echo esc_attr($title); ?></h2>
        </div>
    <?php endif; ?>
    <div class="stm-player-statistic-unit stm-player-statistic-unit-baseball">
        <?php if (!empty($players)):
            $equations = array_keys(sp_get_var_equations('sp_statistic'));

            foreach ($players as $post_id):
                if ($post_id != 0):
                    $player = new SP_Player($post_id);
                    if ($player != null) :
                        $playerData = $player->data($league);
                        $params = $playerData[0];

                        /*IMAGE*/
                        $player_image_id = get_post_meta($post_id, 'player_image', true);
                        if (!empty($player_image_id)) {
                            $image = (splash_is_layout("baseball")) ? splash_get_thumbnail_url(0, $player_image_id, 'player_stat_ava') : splash_get_thumbnail_url(0, $player_image_id, 'full');
                        } else {
                            $image = '';
                        }

                        /*TITLE*/
                        $title = get_the_title($post_id);
                        $player_url = get_the_permalink($post_id);

                        /*POSITION*/
                        $positions = wp_get_post_terms($post_id, 'sp_position');
                        $position = false;
                        if ($positions) {
                            $position = $positions[0]->name;
                        }

                        /*NUMBER*/
                        $player_number = get_post_meta($post_id, 'sp_number', true);

                        if (isset($playerData[$season]) && $show_all_season == "") : //single season statistic
                            $statistics = $playerData[$season];
                            $metrics = $player->metrics();
                            //$metrics = (array)get_post_meta( $post_id, 'sp_metrics', true );

                            if ($statistics != null):
                                ?>
                                <div class="stm-single-player-vc_stats clearfix">
                                    <div class="stm-player-img">
                                        <?php if (!empty($image)): ?>
                                            <div class="image">
                                                <a href="<?php echo esc_url($player_url); ?>">
                                                    <img src="<?php echo esc_url($image); ?>"
                                                         alt="<?php echo esc_attr($title); ?>"/>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="stm-player-stat">
                                        <div class="stm-statistic-meta">
                                            <div class="player-meta-name_number heading-font">
                                                <?php if (!empty($title)): ?>
                                                    <span class="title">
                                                                <a href="<?php echo esc_url($player_url); ?>">
                                                                    <?php echo esc_html( $title ); ?>
                                                                </a>
                                                            </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="stm-custom-row">
                                                <div class="number_position_wrapp">
                                                    <?php if (!empty($player_number)): ?>
                                                        <span class="number stm-red heading-font">
                                                                <?php echo esc_html($player_number); ?>
                                                            </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($position)): ?>
                                                        <span class="position heading-font">
                                                            <?php echo esc_html($position); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="stm-metric-wrapp">
                                                    <ul class="stm-metric">
                                                        <?php
                                                        if ($metrics != null):
                                                            foreach ($metrics as $k => $val):
                                                                ?>
                                                                <li>
                                                                    <span class="stm-metric-data header-font"><?php echo esc_html($val); ?></span>
                                                                </li>
                                                            <?php
                                                            endforeach;
                                                        endif;
                                                        ?>
                                                    </ul>
                                                </div>
                                                <div class="stm-metric-wrapp">
                                                    <table class="stm-metric">
                                                        <thead>
                                                        <tr>
                                                            <?php
                                                            if ($statistics != null):
                                                                foreach ($statistics as $k => $val):
                                                                    if (in_array($k, $equations)) :
                                                                        if ($val != 0 && $val != "" && $val != "-" && $k != "name" && $k != "team"):
                                                                            ?>
                                                                            <td>
                                                                                <span class="stm-metric-title normal_font"><?php echo esc_html($params[$k]); ?></span>
                                                                            </td>
                                                                        <?php
                                                                        endif;
                                                                    endif;
                                                                endforeach;
                                                            endif;
                                                            ?>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <?php
                                                            if ($statistics != null):
                                                                foreach ($statistics as $k => $val):
                                                                    if (in_array($k, $equations)) {
                                                                        if ($val != 0 && $val != "" && $val != "-" && $k != "name" && $k != "team"):
                                                                            ?>
                                                                            <td>
                                                                                <span class="stm-metric-data normal_font"><?php echo esc_html($val); ?></span>
                                                                            </td>
                                                                        <?php
                                                                        endif;
                                                                    }
                                                                endforeach;
                                                            endif;
                                                            ?>

                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <a href="<?php echo esc_url($player_url); ?>"
                                                   class="button stm-mobile-hide only_border">
                                                    <?php esc_html_e('View profile', 'splash'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php
                        else: //all seasons statistic
                            unset($playerData[-1]);
                            unset($playerData[0]);

                            if (count($playerData) > 0) :

                                $metrics = $player->metrics();
                                ?>
                                <div class="stm-single-player-vc_stats clearfix">
                                    <div class="stm-player-img">
                                        <?php if (!empty($image)): ?>
                                            <div class="image">
                                                <a href="<?php echo esc_url($player_url); ?>">
                                                    <img src="<?php echo esc_url($image); ?>"
                                                         alt="<?php echo esc_attr($title); ?>"/>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="stm-player-stat">
                                        <div class="stm-statistic-meta">
                                            <div class="player-meta-name_number heading-font">
                                                <?php if (!empty($title)): ?>
                                                    <h4 class="title">
                                                        <a href="<?php echo esc_url($player_url); ?>">
                                                            <?php echo esc_html($title); ?>
                                                        </a>
                                                    </h4>
                                                <?php endif; ?>
                                                <?php if (!empty($player_number)): ?>
                                                    <span class="number h4">
                                                            <?php echo esc_html("#" . $player_number); ?>
                                                        </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="stm-metric-wrapp">
                                                <ul class="stm-metric">
                                                    <li>
                                                        <?php if (!empty($position)): ?>
                                                            <span class="position stm-metric-data normal_font">
                                                                    <?php echo esc_html($position); ?>
                                                                </span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <?php
                                                    $metrics = apply_filters( 'sportspress_player_details', $metrics, $post_id );
                                                    if ($metrics != null):
                                                        foreach ($metrics as $k => $val):
                                                            ?>
                                                            <li>
                                                                <span class="stm-metric-data heading-font">
                                                                    <?php
                                                                    if ((strtolower($k) == "age")) {
                                                                        if ($val) echo esc_html($k . ": " . $val);
                                                                    }
                                                                    else
                                                                        echo esc_html($val);
                                                                    ?>
                                                                </span>
                                                            </li>
                                                        <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </ul>
                                            </div>
                                            <div class="stm-stat-table-wrapp">
                                                <table class="stm-metric">
                                                    <thead>
                                                    <tr>
                                                        <td>
                                                            <span class="stm-metric-title normal_font"><?php echo esc_html__("Year", "splash"); ?></span>
                                                        </td>
                                                        <?php foreach ($equations as $k => $val): ?>
                                                            <td>
                                                                <span class="stm-metric-title normal_font"><?php echo esc_html($params[$val]); ?></span>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    foreach ($playerData as $k => $val) :
                                                        $statistics = $playerData[$k];
                                                        if ($statistics != null):
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <span class="stm-metric-data heading-font"><?php echo esc_html($statistics["name"]); ?></span>
                                                                </td>
                                                                <?php
                                                                if ($statistics != null):
                                                                    foreach ($equations as $k => $val):
                                                                        //if(in_array($k, $equations)) :
                                                                        //if ($val != "-" && $k != "name" && $k != "team"):

                                                                        ?>
                                                                        <td>
                                                                            <span class="normal_font"><?php echo (isset($statistics[$val]) && $statistics[$val] != 0 && $statistics[$val] != "") ? esc_html($statistics[$val]) : 0; ?></span>
                                                                        </td>
                                                                    <?php
                                                                        //endif;
                                                                        //endif;
                                                                    endforeach;
                                                                endif;
                                                                ?>

                                                            </tr>

                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <a href="<?php echo esc_url($player_url); ?>"
                                               class="button stm-mobile-hide only_border">
                                                <?php esc_html_e('View profile', 'splash'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    (function ($) {
        "use strict";

        var owl = $('.stm-player-statistic-unit');
        $(document).ready(function () {
            owl.owlCarousel({
                items: 1,
                dots: true,
                autoplay: false,
                slideBy: 1,
                nav: false,
                navigation: false,
                loop: false,
                margin: 600
            });
        });
    })(jQuery);
</script>