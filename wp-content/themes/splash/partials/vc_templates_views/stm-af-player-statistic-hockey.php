<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28.08.2017
 * Time: 16:14
 */
$title = $league = $season = $playerList = '';
$players = array();
extract($atts);
if (empty($players)) $players = array();
if ($atts["league"] != "") $league = $atts["league"];
if ($atts["season"] != "") $season = $atts["season"];
if (!empty($atts["players"])) $players = explode(', ', $atts['players']);
$seasonName = get_term_by('id', $season, 'sp_season');
if ($seasonName) {
    $seasonName = $seasonName->name;
} else {
    $seasonName = "";
}
if (!empty($atts["player_lists"])) {
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
<div class="stm-media-tabs stm-statistic-tabs stm-player-statistic-football <?php echo esc_attr(($af_ps_view_style == 'hockey') ? $af_ps_view_style : ''); ?>">
    <div class="clearfix">
        <?php if (!empty($title)): ?>
            <div class="stm-title-center">
                <h2 class="stm-main-title-unit white"><?php echo esc_attr($title); ?></h2>
            </div>
        <?php endif; ?>
    </div>
    <div class="stm-player-statistic-unit stm-player-statistic-unit-football">
        <?php if (!empty($players)):
            $equations = array_keys(sp_get_var_equations('sp_statistic'));
            foreach ($players as $post_id):
                if ($post_id != 0):
                    $player = new SP_Player($post_id);
                    if ($player != null) :
                        $playerData = $player->data($league);
                        $params = $playerData[0];
                        if (isset($playerData[$season])) :
                            $statistics = $playerData[$season];

//                            $metric = $player->metrics();
                            $metrics = (array)get_post_meta($post_id, 'sp_metrics', true);
                            if ($statistics != null):
                                /*IMAGE*/
                                $player_image_id = get_post_meta($post_id, 'player_image', true);
                                if (!empty($player_image_id)) {
                                    $image = splash_get_thumbnail_url(0, $player_image_id, 'stm-350-450');
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
                                ?>
                                <div class="stm-single-player-vc_stats clearfix">
                                    <div class="before_"></div>
                                    <div class="row">
                                        <div class="col-xs-5 col-sm-5 col-md-5">
                                            <?php if (!empty($image)): ?>
                                                <div class="image">
                                                    <a href="<?php echo esc_url($player_url); ?>">
                                                        <img src="<?php echo esc_url($image); ?>"
                                                             alt="<?php echo esc_attr($title); ?>"/>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-xs-7 col-sm-7 col-md-7">
                                            <div class="stm-statistic-meta">
                                                <div class="player-meta-name_number heading-font">
                                                    <?php if (!empty($title)): ?>
                                                        <span class="title">
                                                                    <a href="<?php echo esc_url($player_url); ?>">
                                                                        <?php echo esc_attr($title); ?>
                                                                    </a>
                                                                </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="stm-custom-row">
                                                    <div class="col-39">
                                                        <div class="number_position_wrapp">
                                                            <?php if (!empty($player_number)): ?>
                                                                <span class="number heading-font">
                                                                        <?php echo esc_attr($player_number); ?>
                                                                    </span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($position)): ?>
                                                                <div class="position heading-font">
                                                                    <?php echo esc_attr($position); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="stm-metric-wrapp">
                                                            <table class="stm-metric">
                                                                <tbody>
                                                                <?php
                                                                if ($metrics != null):
                                                                    $q = 0;
                                                                    foreach ($metrics as $k => $val):
                                                                        ?>
                                                                        <tr class="<?php if ($q % 2 == 0) echo "odd"; else echo "even"; ?>">
                                                                            <td>
                                                                                <span class="stm-metric-title normal_font"><?php echo esc_html($k); ?></span>
                                                                            </td>
                                                                            <td>
                                                                                <span class="stm-metric-data normal_font"><?php echo esc_html($val); ?></span>
                                                                            </td>
                                                                        </tr>
                                                                        <?php
                                                                        $q++;
                                                                    endforeach;
                                                                endif;
                                                                ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <a href="<?php echo esc_url($player_url); ?>"
                                                           class="btn stm-mobile-hide">
                                                            <?php esc_html_e('View profile', 'splash'); ?>
                                                        </a>
                                                    </div>
                                                    <div class="col-39">
                                                        <div class="stm-quick-sts heading-font">
                                                            <?php esc_html_e('Quick stats', 'splash') . " (" . esc_html($seasonName) . ")"; ?>
                                                        </div>
                                                        <div class="stm-metric-wrapp">
                                                            <table class="stm-metric">
                                                                <tbody>
                                                                <?php
                                                                if ($statistics != null):
                                                                    $q = 0;
                                                                    foreach ($statistics as $k => $val):

                                                                        if (in_array($k, $equations)) {
                                                                            if ($val != 0 && $val != "" && $val != "-" && $k != "name" && $k != "team"):
                                                                                if (empty($params[$k])) continue;
                                                                                ?>
                                                                                <tr class="<?php if ($q % 2 == 0) echo "odd"; else echo "even"; ?>">
                                                                                    <td>
                                                                                        <span class="stm-metric-title normal_font"><?php echo esc_html($params[$k]); ?></span>
                                                                                    </td>
                                                                                    <td>
                                                                                        <span class="stm-metric-data normal_font"><?php echo esc_html($val); ?></span>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php
                                                                                $q++;
                                                                            endif;
                                                                        }
                                                                    endforeach;
                                                                endif;
                                                                ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
                dots: false,
                autoplay: false,
                slideBy: 1,
                smartSpeed: 700,
                sliderSpeed: 900,
                nav: true,
                navText: ''
            });
        });
    })(jQuery);
</script>