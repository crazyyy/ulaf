<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);
splash_enqueue_modul_scripts_styles('stm_players_tabs');
$count = $count ? $count : 4;
$i = 0;
$query = new WP_Query(array(
	'post_type' => 'sp_player',
	'post_status' => 'publish',
	'posts_per_page' => $count,
)); ?>
<div class="stm-players-tabs <?php echo esc_attr($view_style) ?>" data-count="<?php echo esc_attr($count); ?>">
<?php if ($view_style == "style_2"): ?>
	<div class="player-carousel">
		<?php
		$q = 0;
		foreach ($query->posts as $post):
			$q++;
			$player_id = $post->ID;
			$player = new SP_Player_List($player_id);

			$thumbnail_id = get_post_thumbnail_id($player_id);
			$img = wpb_getImageBySize(array(
				'attach_id' => $thumbnail_id,
				'thumb_size' => '100x100',
			));
			$player_data = $player->data()[$player_id];
			unset($player_data[0]);
			$position = '';
			$positions = wp_get_post_terms($player_id, 'sp_position');
			if ($positions) {
				$position = $positions[0]->name;
			}
		?>
			<div class="player-carousel__item">
				<a href="#" class="<?php echo esc_attr($q == 1) ? 'active' : ''; ?> player-slide-thumb-<?php echo esc_attr($q); ?>" data-slide="<?php echo esc_attr($q); ?>">
					<?php echo splash_sanitize_text_field($img['thumbnail']); ?>
					<div class="player-name">
						<b class="heading-font"><?php echo get_the_title($player_id); ?></b>
						<span><?php echo esc_html($position); ?></span>
					</div>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="stm-players-info">
	<?php foreach ($query->posts as $post):
		$i++;
		$player_id = $post->ID;
		$player = new SP_Player_List($player_id);

		$thumbnail_id = get_post_thumbnail_id($player_id);
		$img = wpb_getImageBySize(array(
			'attach_id' => $thumbnail_id,
			'thumb_size' => '310x403',
		));
		$player_data = $player->data()[$player_id];
		unset($player_data[0]);
		$player_info = array();
		$position = '';
		$positions = wp_get_post_terms($player_id, 'sp_position');
		if ($positions) {
			$position = $positions[0]->name;
		}
		$player_number = $player_data['number'];
		
		$player_info['height'] = $player_data['height'];
		$player_info['weight'] = $player_data['weight'];
		$player_info['age'] = $player_data['age'];
		// $player_info['goals'] = $player_data['goals'];
		// $player_info['assists'] = $player_data['assists'];
		
		if( $atts[ "league" ] != "" ) $league = $atts[ "league" ];
		if( $atts[ "season" ] != "" ) $season = $atts[ "season" ];

		$seasonName = get_term_by( 'id', $season, 'sp_season' );

		if( $seasonName ) {
			$seasonName = $seasonName->name;
		}
		else {
			$seasonName = "";
		}

		$playerData = $player->data( false, $league, $season );

		if( isset( $player_data ) ) :
			$statistics = $player_data;
		endif;
		$equations = array_keys( sp_get_var_equations( 'sp_statistic' ) );
	?>
		<div class="player-info-wrap <?php echo esc_attr($i == 1) ? esc_attr('active') : ''; ?> player-slide-<?php echo esc_attr($i); ?>" data-slide="<?php echo esc_attr($i); ?>">
			<div class="player-info">
				<div class="player-info__title">Players</div>
				<h3 class="player-info__name"><span class="player-info__number heading-font"><?php echo esc_html($player_number); ?></span> <?php echo get_the_title($player_id); ?></h3>
				<div class="player-info__stats">
					<div class="player-info__stats-col"> <!-- position -->
						<div class="player-info__stats-title heading-font"><?php echo esc_html($position) ?></div>
						<table class="player-info__stats-table normal-font">
						<?php foreach ($player_info as $info => $value): ?>
							<?php if($value && $value != '-'): ?>
								<tr>
									<td><?php echo esc_html($info); ?></td>
									<td><?php echo esc_html($value); ?></td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>

						</table>
					</div>
					<div class="player-info__stats-col"> <!-- stats -->
						<div class="player-info__stats-title heading-font"><?php esc_html_e( 'Quick stats', 'splash' ); ?> (<?php echo esc_html($seasonName) ?>)</div>
						<?php if( $statistics != null ): ?>
						<table class="player-info__stats-table normal-font">
						<?php
							foreach( $statistics as $k => $val ):
								if( in_array( $k, $equations ) ) {
									if( $val != 0 && $val != "" && $val != "-" && $k != "name" && $k != "team" ):
										?>
										<tr>
											<td><?php echo esc_html($k); ?></td>
											<td><?php echo esc_html($val); ?></td>
										</tr>
										<?php
									endif;
								}
							endforeach;
						?>				
						</table>
						<?php endif; ?>
					</div>
				</div>
				<div class="player-metrics">


				</div>
			</div>
			<div class="player-image">
				<a href="<?php echo get_permalink($player_id); ?>">
					<?php echo splash_sanitize_text_field($img['thumbnail']); ?>
				</a>
			</div>
		</div>
	<?php endforeach; ?>
	</div>
<?php else: ?>
	<div class="slider-navs">
		<a href="#" class="prev"><i class="icon-arrow-left"></i></a>
		<a href="#" class="next"><i class="icon-arrow-right"></i></a>
	</div>

	<div class="stm-players-info">
	<?php foreach ($query->posts as $post):
		$i++;
		$player_id = $post->ID;
		$player = new SP_Player_List($player_id);

		$thumbnail_id = get_post_thumbnail_id($player_id);
		$img = wpb_getImageBySize(array(
			'attach_id' => $thumbnail_id,
			'thumb_size' => '386x450',
		));
		$player_data = $player->data()[$player_id];
		unset($player_data[0]);
		$player_info = array();
		$position = '';
		$positions = wp_get_post_terms($player_id, 'sp_position');
		if ($positions) {
			$position = $positions[0]->name;
		}
		$player_number = $player_data['number'];
		$winrate = $player_data['winratio'];
		$appearances = $player_data['appearances'];

		$player_info['height'] = $player_data['height'] . ' cm';
		$player_info['weight'] = $player_data['weight'] . ' kg';
		$player_info['age'] = $player_data['age'];
		$player_info['goals'] = $player_data['goals'];
		$player_info['assists'] = $player_data['assists'];
		$translated_info = array(
            'height' => esc_html__('height', 'splash'),
            'weight' => esc_html__('weight', 'splash'),
            'age' => esc_html__('age', 'splash'),
            'goals' => esc_html__('goals', 'splash'),
            'assists' => esc_html__('assists', 'splash'),
        );
	?>
		<div class="player-info-wrap <?php echo esc_attr($i == 1) ? esc_attr('active') : ''; ?> player-slide-<?php echo esc_attr($i); ?>" data-slide="<?php echo esc_attr($i); ?>">
			<div class="player-image">
				<a href="<?php echo get_permalink($player_id); ?>">
					<?php echo splash_sanitize_text_field($img['thumbnail']); ?>
				</a>
				<span class="player-number heading-font"><?php echo esc_html($player_number); ?></span>
			</div>
			<div class="player-info">
				<h3><?php echo get_the_title($player_id); ?></h3>
				<span class="position"><?php echo esc_html($position); ?></span>
				<div class="biography">
					<?php echo get_the_excerpt($player_id); ?>
				</div>
			</div>
			<div class="player-data">
				<div class="player-circle-section">
					<div class="player-circle-wrap">
						<div class="player-circle">
							<?php echo esc_html(intval($winrate)); ?>%
							<svg width="80" height="80">
								<circle stroke="red" class="outer" cx="40" cy="40" r="38"/>
							</svg>
						</div>
						<b><?php esc_html_e('Wins', 'splash'); ?></b>
					</div>
					<div class="player-circle-wrap">
						<div class="player-circle">
							<?php echo esc_html($appearances); ?>
							<svg width="80" height="80">
								<circle stroke="red" class="outer" cx="40" cy="40" r="38"/>
							</svg>
						</div>
						<b><?php esc_html_e('Appearances', 'splash'); ?></b><br>
					</div>
				</div>
				<div class="player-metrics">

					<?php foreach ($player_info as $info => $value): ?>
						<?php if($value && $value != '-'): ?>
							<div class="player-metrics__item">
								<span><?php echo esc_html($translated_info[$info]); ?></span>
								<b><?php echo esc_html($value); ?></b>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	</div>

	<div class="player-carousel">
		<?php
		$q = 0;
		foreach ($query->posts as $post):
		$q++;
		$player_id = $post->ID;
		$player = new SP_Player_List($player_id);

		$thumbnail_id = get_post_thumbnail_id($player_id);
		$img = wpb_getImageBySize(array(
			'attach_id' => $thumbnail_id,
			'thumb_size' => '75x75',
		));
		$player_data = $player->data()[$player_id];
		unset($player_data[0]);
		$position = '';
		$positions = wp_get_post_terms($player_id, 'sp_position');
		if ($positions) {
			$position = $positions[0]->name;
		}
		$player_number = $player_data['number'];
		?>
		<div class="player-carousel__item">
			<a href="player-slide-<?php echo esc_attr($q); ?>" class="<?php echo esc_attr($q == 1) ? 'active' : ''; ?> player-slide-thumb-<?php echo esc_attr($q); ?>" data-slide="<?php echo esc_attr($q); ?>">
				<?php echo splash_sanitize_text_field($img['thumbnail']); ?>
				<div class="player-name">
					<b><?php echo get_the_title($player_id); ?></b><br>
					<span><?php echo esc_html($position); ?></span>
				</div>
				<span class="player-number heading-font">
					<?php echo esc_html($player_number); ?>
				</span>
			</a>
		</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
</div>