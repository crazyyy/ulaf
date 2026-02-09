<?php
/**
 * Staff Gallery
 *
 * @author 		ThemeBoy
 * @package 	SportsPress_Staff_Directories
 * @version     2.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$html5 = current_theme_supports( 'html5', 'gallery' );
$defaults = array(
	'id' => get_the_ID(),
	'title' => false,
	'number' => -1,
	'itemtag' => 'div',
	'icontag' => 'figure',
	'captiontag' => 'div',
	'grouptag' => 'h4',
	'columns' => 3,
	'size' => 'sportspress-crop-medium',
	'show_all_staff_link' => false,
	'link_posts' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

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

$selector = 'sp-staff-gallery-' . $id;

$directory = new SP_Staff_Directory( $id );
$data = $directory->data();

// Remove the first row to leave us with the actual data
unset( $data[0] );

$gallery_style = $gallery_div = '';
$size_class = sanitize_html_class( $size );
$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";
echo apply_filters( 'gallery_style', $gallery_style . "\n\t\t" );
?>
<?php echo wp_kses_post( $gallery_div ); ?>
	<div class="card card--clean">
		<?php if ( $title ) {

			$card_header_btn_classes = array(
				'btn',
				'btn-xs',
				'btn-default',
				'card-header__button',
			);
			// make it outline
			if ( alchemists_sp_preset( 'basketball' ) || alchemists_sp_preset( 'soccer' ) ) {
				$card_header_btn_classes[] = 'btn-outline';
			}

			echo '<header class="card__header">'
				. '<h4 class="sp-table-caption">' . $title . '</h4>';

				if ( $show_all_staff_link ) {
					echo '<a class="sp-staff-gallery-link sp-gallery-link ' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '" href="' . get_permalink( $id ) . '">' . __( 'View all staff', 'alchemists' ) . '</a>';
				}
			echo '</header>';
		} ?>
		<div class="card__content">
			<div class="sp-template sp-template-staff-gallery sp-template-gallery team-roster--grid-sm team-roster--grid-col-<?php echo esc_attr( $columns ); ?>">
				<?php
				if ( is_int( $number ) && $number > 0 )
					$limit = $number;

				$i = 0;

				foreach( $data as $staff_id => $row ):

					if ( isset( $limit ) && $i >= $limit ) continue;

					$caption = get_the_title( $staff_id );
					$caption = trim( $caption );

						sp_get_template( 'staff-gallery-thumbnail.php', array(
							'id' => $staff_id,
							'row' => $row,
							'itemtag' => $itemtag,
							'icontag' => $icontag,
							'captiontag' => $captiontag,
							'caption' => $caption,
							'size' => $size,
							'link_posts' => $link_posts,
						), '', SP_STAFF_DIRECTORIES_DIR . 'templates/' );

					$i++;

				endforeach; ?>
			</div>
		</div>
	</div>
</div>
