<?php
/**
 * The template for displaying Single Team
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$gallery_type     = isset( $alchemists_data['alchemists__team-gallery-type'] ) ? $alchemists_data['alchemists__team-gallery-type'] : 'img_top';

$team_gallery_albums = get_field( 'team_gallery_albums' );
?>

<?php if ( $team_gallery_albums ): ?>

	<!-- Team Gallery -->
	<div class="gallery row">

		<?php
		foreach( $team_gallery_albums as $post) :
			include( locate_template( 'sportspress/single-team/gallery/gallery-' . $gallery_type . '.php' ) );
		endforeach;
		?>

	</div>
	<?php wp_reset_postdata(); ?>
	<!-- Team Gallery / End -->

<?php endif;
