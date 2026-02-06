<?php
/**
 * Staff Gallery
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.3.0
 * @version   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => get_the_ID(),
	'index' => 0,
	'link_posts' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
	'staff_heading' => 0
);

$staffs = array_filter( sp_array_between( (array)get_post_meta( $id, 'sp_staff', false ), 0, $index ) );

if ( ! $staffs ) return;

extract( $defaults, EXTR_SKIP );
?>
<div class="card card--clean">
	<?php if ( $staff_heading ) : ?>
	<header class="card__header">
		<h4><?php esc_html_e( 'Staff', 'alchemists' ); ?></h4>
	</header>
	<?php endif; ?>
	<div class="card__content">
		<div class="team-roster team-roster--grid team-roster--grid-col-3 sp-player-gallery-wrapper sp-gallery-wrapper">
			<?php
			foreach( $staffs as $staff_id ) :

				if ( ! $staff_id ) {
					continue;
				}

				if ( has_post_thumbnail( $staff_id ) ) {
					$thumbnail = get_the_post_thumbnail( $staff_id, 'alchemists_thumbnail-player-lg' );
				} else {
					$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-380x570.jpg' ) . '" alt="">';
				}

				if ( $link_posts ):
					$permalink = get_post_permalink( $staff_id );
					$thumbnail = '<a href="' . $permalink . '">' . $thumbnail . '</a>';
				endif;

				$name = get_the_title( $staff_id );

				if ( ! $name ) {
					continue;
				}

				$staff = new SP_Staff( $staff_id );
				?>

				<div class="team-roster__item">
					<div class="team-roster__holder">
						<figure class="team-roster__img">
							<?php echo wp_kses_post( $thumbnail ); ?>
						</figure>
						<div class="team-roster__content">
							<div class="team-roster__content-inner">
								<div class="team-roster__member-info">
									<h2 class="team-roster__member-name team-roster__member-name--has-link"><?php echo wp_kses_post( $name ); ?></h2>
									<span class="team-roster__member-position">
										<?php
										$roles = $staff->roles();
										if ( ! empty( $roles ) ):
											$roles = wp_list_pluck( $roles, 'name' );
											echo implode( ', ', $roles );
										else:
											esc_html_e( 'Staff', 'alchemists' );
										endif;
										?>
									</span>
								</div>
							</div>
						</div>

						<?php if ( $link_posts ) : ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="btn-fab"></a>
						<?php endif; ?>

					</div>

				</div>

			<?php endforeach; ?>
		</div>
	</div>
</div>
