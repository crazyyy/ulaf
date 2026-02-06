<?php
/**
 * Event Staff
 *
 * @author    ThemeBoy
 * @package   SportsPress/Templates
 * @version   2.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => get_the_ID(),
	'index' => 0,
	'link_posts' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
);

$staffs = array_filter( sp_array_between( (array)get_post_meta( $id, 'sp_staff', false ), 0, $index ) );

if ( ! $staffs ) return;

extract( $defaults, EXTR_SKIP );
?>
<div class="card sp-template sp-template-event-staff">
	<header class="card__header">
		<h4><?php esc_html_e( 'Staff', 'alchemists' ); ?></h4>
	</header>
	<div class="card__content">
		<ul class="sp-event-staff list-unstyled">
			<?php
			foreach( $staffs as $staff_id ):

				echo '<li class="sp-event-staff__item">';

					if ( ! $staff_id )
						continue;

					$name = get_the_title( $staff_id );

					if ( ! $name )
						continue;

					$staff = new SP_Staff( $staff_id );

					$roles = $staff->roles();
					if ( ! empty( $roles ) ):
						$roles = wp_list_pluck( $roles, 'name' );
						echo implode( '<span class="sp-staff-role-delimiter">/</span>', $roles );
					else:
						esc_html_e( 'Staff', 'alchemists' );
					endif;

					echo ': ';

					if ( $link_posts ):
						$permalink = get_post_permalink( $staff_id );
						$name =  '<a href="' . $permalink . '">' . $name . '</a>';
					endif;

					echo '<span class="sp-event-staff__name">' . $name . '</span>';

				echo '</li>';

			endforeach;
			?>
		</ul>
	</div>
</div>
