<?php
/**
 * Player Gallery
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version   2.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$html5 = current_theme_supports( 'html5', 'gallery' );
$defaults = array(
	'id' => get_the_ID(),
	'title' => false,
	'number' => -1,
	'grouptag' => 'h4',
	'columns' => null,
	'grouping' => null,
	'orderby' => 'default',
	'order' => 'ASC',
	'itemtag' => 'dl',
	'icontag' => 'dt',
	'captiontag' => 'dd',
	'grouptag' => 'h4',
	'columns' => 3,
	'size' => 'sportspress-crop-medium',
	'show_all_players_link' => false,
	'show_title' => get_option('sportspress_list_show_title', 'yes') == 'yes' ? true : false,
	'show_player_photo' => get_option('sportspress_list_show_photos', 'no') == 'yes' ? true : false,
	'show_player_flag' => get_option('sportspress_list_show_flags', 'no') == 'yes' ? true : false,
	'link_posts' => get_option('sportspress_link_players', 'yes') == 'yes' ? true : false,
	'link_teams' => get_option('sportspress_link_teams', 'no') == 'yes' ? true : false,
	'abbreviate_teams' => get_option('sportspress_abbreviate_teams', 'yes') === 'yes' ? true : false,
	'sortable' => get_option('sportspress_enable_sortable_tables', 'yes') == 'yes' ? true : false,
	'scrollable' => get_option('sportspress_enable_scrollable_tables', 'yes') == 'yes' ? true : false,
	'paginated' => get_option('sportspress_list_paginated', 'yes') == 'yes' ? true : false,
	'rows' => get_option('sportspress_list_rows', 10),
);

extract($defaults, EXTR_SKIP);

// Backward compatibility
if (isset($performance)) {
	$columns = $performance;
}

// Explode into array
if (null !== $columns && !is_array($columns)) {
	$columns = explode(',', $columns);
}

$list = new SP_Player_List($id);
if (isset($columns) && null !== $columns):
	$list->columns = $columns;
endif;

$itemtag = tag_escape( $itemtag );
$captiontag = tag_escape( $captiontag );
$icontag = tag_escape( $icontag );
$valid_tags = wp_kses_allowed_html( 'post' );
if ( ! isset( $valid_tags[ $itemtag ] ) )
	$itemtag = 'dl';
if ( ! isset( $valid_tags[ $captiontag ] ) )
	$captiontag = 'dd';
if ( ! isset( $valid_tags[ $icontag ] ) )
	$icontag = 'dt';

$columns = intval( $columns );
$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
$size = $size;
$float = is_rtl() ? 'right' : 'left';

$selector = 'sp-player-gallery-' . $id;

$list = new SP_Player_List( $id );
$data = $list->data();

// The first row should be column labels
$labels = $data[0];

// Remove the first row to leave us with the actual data
unset($data[0]);
?>
<div class="container">
	<div class="stm-players stm-players-inline">
		<?php foreach ($data as $player_id => $player): ?>

			<?php if (!empty($player_id)):

			    $metric_labels = (array)sp_get_var_labels( 'sp_metric' );
			    $player_number = get_post_meta($player_id, 'sp_number', true);
//				$positions = wp_get_post_terms($player_id, 'sp_position');
//				$position = false;
//				if ($positions) {
//					$position = $positions[0]->name;
//				}
				$position = sp_array_value( $player, 'position', null );
				if ( null === $position || ! $position ):
					$positions = wp_strip_all_tags( get_the_term_list( $player_id, 'sp_position', '', ', ' ) );
				else:
					$position_term = get_term_by( 'id', $position, 'sp_position', ARRAY_A );
					$positions = sp_array_value( $position_term, 'name', '&mdash;' );
				endif;

				$thumbnailSize = (!splash_is_layout("baseball")) ? 'stm-270-370' : 'stm-255-255';

				$image = splash_get_thumbnail_url($player_id, 0, $thumbnailSize);
				$image = (!empty($image)) ? $image : 'http://www.gravatar.com/avatar/?s=150&d=mm&f=y';

				if (!empty($image)): ?>
					<div class="stm-list-single-player">
                        <a href="<?php echo esc_url(get_the_permalink($player_id)); ?>"
						   title="<?php echo esc_attr(get_the_title($player_id)); ?>">
                            <?php if(!splash_is_layout("baseball")) : ?>
                                <?php if(splash_is_layout('hockey')) echo("<div class="."'over'>"); ?>
                                    <img src="<?php echo esc_url($image); ?>"/>
                                <?php if(splash_is_layout('hockey')) echo("</div>") ?>
                                <div class="stm-list-single-player-info">
                                    <div class="inner heading-font">
                                        <div class="player-number">
                                            <?php if(splash_is_layout('hockey')) echo("<div class="."'before_'>"); ?>
                                                <?php echo esc_attr($player_number); ?>
                                            <?php if(splash_is_layout('hockey')) echo("</div>") ?>
                                        </div>
                                        <div class="player-title"><?php echo esc_attr(get_the_title($player_id)); ?></div>
                                        <?php if( $labels['position']): ?>
                                            <div class="player-position"><?php echo esc_attr($positions); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="stm-bsbl-player-wrap">
                                    <div class="stm-player-name-position heading-font">
                                        <div class="player-title"><?php echo esc_attr(get_the_title($player_id)); ?></div>
                                        <div class="player-number"><?php echo esc_html__("#", "splash") . esc_attr($player_number); ?></div>
                                    </div>
                                    <div class="stm-player-info-wrap">
                                        <div class="stm-player-img">
                                            <img src="<?php echo esc_url($image); ?>"/>
                                        </div>
                                        <div class="stm-player-info">
                                            <div class="player-position heading-font"><?php echo esc_attr($position); ?></div>
                                            <?php foreach($metric_labels as $key => $val) : ?> <!--start parse metrics-->
                                                <div class="player-metric normal_font">
                                                    <span><?php echo esc_html($val . ":"); ?></span>
                                                    <span><?php echo esc_html($player[$key]); ?></span>
                                                </div>
                                            <?php endforeach; ?><!--end parse metrics -->
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>