<?php
/**
 * Event Officials Table
 *
 * @author    ThemeBoy
 * @package   SportsPress/Templates
 * @version   2.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$scrollable = get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false;
?>
<div class="card card--has-table sp-template sp-template-event-officials">
	<header class="card__header">
		<h4 class="sp-table-caption"><?php esc_html_e( 'Officials', 'alchemists' ); ?></h4>
	</header>
	<div class="card__content">
		<div class="sp-table-wrapper">
			<div class="table-responsive">
				<table class="sp-event-officials sp-data-table<?php if ( $scrollable ) { echo esc_attr( ' sp-scrollable-table' ); } ?>">
					<thead>
						<tr>
							<?php
							foreach ( $labels as $label ) {
								echo '<th class="data-name">' . $label . '</th>';
							}
							?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							foreach ( $data as $appointed_officials ) {
								foreach ( $appointed_officials as $official_id => $official_name ) {
									if ( $link_officials && sp_post_exists( $official_id ) ) {
										$appointed_officials[ $official_id ] = '<a href="' . get_post_permalink( $official_id ) . '">' . $official_name . '</a>';
									}
								}
								echo '<td class="data-name">' . implode( '<br>', $appointed_officials ) . '</td>';
							}
							?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
