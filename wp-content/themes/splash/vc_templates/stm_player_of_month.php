<?php
$title = $post_categories = $number = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

splash_enqueue_modul_scripts_styles('stm_player_of_month');

/*
 * 'title' => string 'Plater of the Month' (length=19)
  'player_id' => string '1006' (length=4)
  'use_background_image' => string 'enable' (length=6)
  'background_image' => string '1527' (length=4)
'stat_paramms'
 * */

$player = new SP_Player( $player_id );
$metrics = $player->metrics();
$stat = $player->statistics();
$statFields = '';
$player = $player->post;
$stat_paramms = explode(',', $stat_paramms);

foreach ($stat as $k => $val) {
    $stat = (isset($val[-1])) ? $val[-1] : $val[0];
    $statFields = $val[0];
    break;
}


$bgImg = (!empty($use_background_image) && $use_background_image == 'enable' && !empty($background_image)) ? 'background-image: url(' . wp_get_attachment_image_url($background_image, 'full') . '); background-size: cover;' : 'background-image: url(' . get_the_post_thumbnail_url($player_id, 'full') . '); background-size: cover;';

?>
<div class="stm_player_month" style="<?php echo splash_sanitize_text_field($bgImg);?>">
    <a href="<?php echo get_the_permalink($player_id); ?>" >
        <div class="player-info">
            <div class="title normal_font"><?php echo esc_html($title);?></div>
            <div class="player-name heading-font"><?php echo splash_firstWordBold($player->post_title); ?></div>
            <ul class="player-stat">
                <?php foreach ($stat_paramms as $key): ?>
                    <li> <?php
                        if( !empty($stat[$key])) { ?>
                            <div class="stat-label heading-font">
                                <?php echo esc_html($stat[$key]); ?>
                            </div> <?php
                        }
                        if ( !empty($statFields[$key]) ) { ?>
                            <div class="stat-data normal_font">
                                <?php  echo esc_html($statFields[$key]); ?>
                            </div> <?php
                        } ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </a>
</div>