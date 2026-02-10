<?php
/**
 * The template for displaying ALC: Team Stats cell (Soccer)
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

$stat_icon_classes = array(
	'team-stats__icon',
);

switch ( $stat_icon ) {
	case 'icon_2':
		array_push( $stat_icon_classes, 'team-stats__icon--circle', 'team-stats__icon--default', 'team-stats__icon--shots-ot' );
		$stat_icon_output = '<img src="' . get_template_directory_uri() . '/assets/images/soccer/icon-soccer-ball.svg" class="team-stats__icon-primary" alt="">';
		$stat_icon_output .= '<img src="' . get_template_directory_uri() . '/assets/images/soccer/icon-soccer-gate.svg" class="team-stats__icon-secondary" alt="">';
		break;
	case 'icon_3':
		array_push( $stat_icon_classes, 'team-stats__icon--circle', 'team-stats__icon--default', 'team-stats__icon--shots' );
		$stat_icon_output = '<img src="' . get_template_directory_uri() . '/assets/images/soccer/icon-soccer-ball.svg" class="team-stats__icon-primary" alt="">';
		$stat_icon_output .= '<img src="' . get_template_directory_uri() . '/assets/images/soccer/icon-soccer-shots.svg" class="team-stats__icon-secondary" alt="">';
		break;
	case 'icon_custom':
		array_push( $stat_icon_classes, 'team-stats__icon--circle', 'team-stats__icon--default', 'team-stats__icon--assists' );
		// icon symbol
		$stat_icon_symbol = $stat_item['stat_icon_symbol'];
		$stat_icon_output = '<img src="' . get_template_directory_uri() . '/assets/images/soccer/icon-soccer-ball.svg" class="team-stats__icon-primary" alt="">';
		$stat_icon_output .= '<span class="team-stats__icon-secondary">' .  $stat_item['stat_icon_symbol'] . '</span>';
		break;
	case 'icon_custom_font':
		$stat_icon_classes[] = 'team-stats__icon--icon-font';
		$icon_style          = array();
		$icon_styles_output  = array();

		if ( $icon_color ) {
			$icon_style[] = 'color: ' . $icon_color . ';';
		} elseif ( $team_color_primary ) {
			$icon_style[] = 'color: ' . $team_color_primary . ';';
		}
		if ( $icon_style ) {
			$icon_styles_output[] = 'style="' . implode( ' ', $icon_style ). '"';
		}
		$stat_icon_output = '<i class="' . $iconClass . '" ' . implode( ' ', $icon_styles_output ) . '></i>';

		break;
	case 'icon_custom_img':
		$stat_icon_classes[] = 'team-stats__icon--img';
		$icon_img_title      = get_the_title( $icon_img_id );
		$stat_icon_output    = '<img src="' . $iconImgUrl . '" class="team-stats__icon-img" alt="' . esc_attr( $icon_img_title ) . '">';
		break;
	default:
		array_push( $stat_icon_classes, 'team-stats__icon--circle', 'team-stats__icon--default' );
		$stat_icon_output = '<img src="' . get_template_directory_uri() . '/assets/images/soccer/icon-soccer-ball.svg" class="team-stats__icon-primary" alt="">';
}
?>

<li class="team-stats__item team-stats__item--clean">
	<div class="team-stats__icon <?php echo esc_attr( implode( ' ', $stat_icon_classes ) ); ?>">
		<?php echo wp_kses_post( $stat_icon_output ); ?>
	</div>
	<div class="team-stats__value"><?php echo esc_html( $stat_value ); ?></div>
	<div class="team-stats__label"><?php echo esc_html( $stat_label ); ?></div>
</li>
