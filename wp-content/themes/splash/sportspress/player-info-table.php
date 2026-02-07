<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$defaults = array(
	'id'                    => get_the_ID(),
	'title'                 => false,
	'number'                => - 1,
	'grouptag'              => 'h4',
	'columns'               => null,
	'grouping'              => null,
	'orderby'               => 'default',
	'order'                 => 'ASC',
	'show_all_players_link' => false,
	'show_title'            => get_option( 'sportspress_list_show_title', 'yes' ) == 'yes' ? true : false,
	'show_player_photo'     => get_option( 'sportspress_list_show_photos', 'no' ) == 'yes' ? true : false,
	'show_player_flag'      => get_option( 'sportspress_list_show_flags', 'no' ) == 'yes' ? true : false,
	'link_posts'            => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'link_teams'            => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'abbreviate_teams'      => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes' ? true : false,
	'sortable'              => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
	'scrollable'            => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
	'paginated'             => get_option( 'sportspress_list_paginated', 'yes' ) == 'yes' ? true : false,
	'rows'                  => get_option( 'sportspress_list_rows', 10 ),
);

extract( $defaults, EXTR_SKIP );

// Backward compatibility
if ( isset( $performance ) ) {
	$columns = $performance;
}

// Explode into array
if ( null !== $columns && ! is_array( $columns ) ) {
	$columns = explode( ',', $columns );
}

$list = new SP_Player_List( $id );
if ( isset( $columns ) && null !== $columns ):
	$list->columns = $columns;
endif;
$data = $list->data();

// The first row should be column labels
$labels = $data[0];

// Remove the first row to leave us with the actual data
unset( $data[0] );

?>
<div class="container">
	<div class="stm-players stm-players-inline">
		<?php foreach ($data as $player_id => $player): ?>
			
			<?php if (!empty($player_id)):
				$player_number = get_post_meta($player_id, 'sp_number', true);
				$positions = wp_get_post_terms($player_id, 'sp_position');
				$position = false;

				if ($positions) {
					$position = $positions[0]->name;
				}
				$image = splash_get_thumbnail_url($player_id, 0, 'stm-270-370');
                $image = (!empty($image)) ? $image : 'http://www.gravatar.com/avatar/?s=150&d=mm&f=y';
				if (!empty($image)): ?>
					
					<div class="stm-list-single-player">
						<a href="<?php echo esc_url(get_the_permalink($player_id)); ?>"
						   title="<?php echo esc_attr(get_the_title($player_id)); ?>">
							<img src="<?php echo esc_url($image); ?>"/>
							
							<div class="stm-list-single-player-info">
								<div class="inner heading-font">
									<div class="player-number"><?php echo esc_attr($player_number); ?></div>
									<div class="player-title"><?php echo esc_attr(get_the_title($player_id)); ?></div>
									<div class="player-position"><?php echo esc_attr($position); ?></div>
								</div>
							</div>
						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>