<?php
/**
 * Event Officials List
 *
 * @author    ThemeBoy
 * @package   SportsPress/Templates
 * @version   2.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$scrollable = get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false;
?>
<div class="card sp-template-event-officials sp-template-details">
	<header class="card__header">
		<h4 class="sp-table-caption"><?php esc_html_e( 'Officials', 'alchemists' ); ?></h4>
	</header>
	<div class="card__content">
		<div class="sp-list-wrapper">
			<ul class="sp-event-officials officials-list list-unstyled">
				<?php
				foreach ( $labels as $key => $label ) {
					echo '<li class="officials-list__item">';
						$appointed_officials = (array) sp_array_value( $data, $key, array() );
						if ( empty( $appointed_officials ) ) continue;

						echo '<div class="officials-list__duty">' . $label . '</div>';

						echo '<div class="officials-list__holder">';
							foreach ( $appointed_officials as $official_id => $official_name ) {
								if ( $link_officials && sp_post_exists( $official_id ) ) {
									$official_name = '<a href="' . get_post_permalink( $official_id ) . '">' . $official_name . '</a>';
								}
								echo '<h6 class="officials-list__name">' . $official_name . '</h6>';
							}
						echo '</div>';
					echo '</li>';
				}
				?>
			</ul>
		</div>
	</div>
</div>
