<?php
/**
 * Event Details
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version   2.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( get_option( 'sportspress_event_show_details', 'yes' ) === 'no' ) return;

if ( ! isset( $id ) )
	$id = get_the_ID();

$scrollable = get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false;

$data = array();

if ( 'yes' === get_option( 'sportspress_event_show_date', 'yes' ) ) {
	$date = get_the_time( get_option('date_format'), $id );
	$data[ esc_html__( 'Date', 'alchemists' ) ] = $date;
}

if ( 'yes' === get_option( 'sportspress_event_show_time', 'yes' ) ) {
	$time = get_the_time( get_option('time_format'), $id );
	$data[ esc_html__( 'Time', 'alchemists' ) ] = apply_filters( 'sportspress_event_time', $time, $id );
}

$taxonomies = apply_filters( 'sportspress_event_taxonomies', array( 'sp_league' => null, 'sp_season' => null ) );

foreach ( $taxonomies as $taxonomy => $post_type ):
	$terms = get_the_terms( $id, $taxonomy );
	if ( $terms ):
		$obj = get_taxonomy( $taxonomy );
		$term = array_shift( $terms );
		$data[ $obj->labels->singular_name ] = $term->name;
	endif;
endforeach;

if ( 'yes' === get_option( 'sportspress_event_show_day', 'yes' ) ) {
	$day = get_post_meta( $id, 'sp_day', true );
	if ( '' !== $day ) {
		$data[ esc_html__( 'Match Day', 'alchemists' ) ] = $day;
	}
}

if ( 'yes' === get_option( 'sportspress_event_show_full_time', 'yes' ) ) {
	$full_time = get_post_meta( $id, 'sp_minutes', true );
	if ( '' === $full_time ) {
		$full_time = get_option( 'sportspress_event_minutes', 90 );
	}
	$data[ esc_html__( 'Full Time', 'alchemists' ) ] = $full_time . '\'';
}

$data = apply_filters( 'sportspress_event_details', $data, $id );

if ( ! sizeof( $data ) ) return;
?>
<div class="card card--has-table sp-template sp-template-event-details">
	<header class="card__header">
		<h4 class="sp-table-caption"><?php esc_html_e( 'Details', 'alchemists' ); ?></h4>
	</header>
	<div class="sp-table-wrapper">
		<div class="table-responsive">
			<table class="table sp-event-details sp-data-table<?php if ( $scrollable ) { ?> sp-scrollable-table<?php } ?>">
				<thead>
					<tr>
						<?php $i = 0; foreach( $data as $label => $value ):	?>
							<th><?php echo esc_html( $label ); ?></th>
						<?php $i++; endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr class="odd">
						<?php $i = 0; foreach( $data as $value ):	?>
							<td><?php echo esc_html( $value ); ?></td>
						<?php $i++; endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
