<?php
/**
 * Player Photo
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     1.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
	<?php
	if ( get_option( 'sportspress_player_show_photo', 'yes' ) === 'no' ) return;

	if ( ! isset( $id ) )
		$id = get_the_ID();

	if ( has_post_thumbnail( $id ) ):
		?>
		<div class="sp-template sp-template-player-photo sp-template-photo sp-player-photo">
			<?php
			$size = 'stm-270-370';
			if(splash_is_layout("af") || splash_is_layout('baseball') || splash_is_layout('rugby')) $size = 'stm-445-400';
			elseif (splash_is_layout("sccr") || splash_is_layout("esport")) $size = 'player_photo';
			elseif (splash_is_layout('soccer_two')) $size = 'post-370-420';

			echo get_the_post_thumbnail( $id, $size );
			?>
			
			<?php

			$playerNumber = sp_get_player_number($id);
			if(splash_is_layout("sccr") && $playerNumber != ""){
                echo '<div class="number"><i class="icon-soccer_ico_tshirt"></i>' . esc_html( $playerNumber ). '</div>';
            }
            if((splash_is_layout("hockey") || splash_is_layout("esport")) && $playerNumber != ""){
                echo '<div class="player-number-photo"><div class="before_">' . esc_html( $playerNumber ). '</div></div>';
            }
			?>
		</div>
		<?php
	endif;
	$socials = array( 'facebook', 'twitter', 'instagram', 'dribbble' );
	if((splash_is_layout("sccr") || splash_is_layout("soccer_two") || splash_is_layout("hockey")) && count($socials) > 0) : ?>
		<ul class="player-socials">
			<?php foreach($socials as $social): ?>
				<?php
				$soc = get_post_meta($id, $social, true);
				if(!empty($soc)): ?>
					<li class="<?php echo esc_attr($social) ?>">
						<a href="<?php echo esc_url($soc); ?>" target="_blank">
							<i class="fa fa-<?php echo esc_attr($social); ?>"></i>
						</a>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>