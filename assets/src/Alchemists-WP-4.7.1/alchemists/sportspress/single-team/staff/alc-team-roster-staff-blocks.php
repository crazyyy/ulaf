<?php
/**
 * Staff Blocks
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
	'staff_heading' => 0,
	'link_posts' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
	'link_phone' => get_option( 'sportspress_staff_link_phone', 'yes' ) == 'yes' ? true : false,
	'link_email' => get_option( 'sportspress_staff_link_email', 'yes' ) == 'yes' ? true : false,
	'show_nationality' => get_option( 'sportspress_staff_show_nationality', 'yes' ) == 'yes' ? true : false,
	'show_nationality_flags' => get_option( 'sportspress_staff_show_flags', 'yes' ) == 'yes' ? true : false,
	'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
);

$staffs = array_filter( sp_array_between( (array)get_post_meta( $id, 'sp_staff', false ), 0, $index ) );

if ( ! $staffs ) return;

extract( $defaults, EXTR_SKIP );

$countries = SP()->countries->countries;
?>

<div class="card card--clean">
	<?php if ( $staff_heading ) : ?>
	<header class="card__header">
		<h4><?php esc_html_e( 'Staff', 'alchemists' ); ?></h4>
	</header>
	<?php endif; ?>
	<div class="card__content">
		<div class="sp-template sp-template-staff-gallery sp-template-gallery team-roster--grid-sm team-roster--grid-col-0">
			<?php
			foreach( $staffs as $staff_id ) :

				if ( ! $staff_id ) {
					continue;
				}

				if ( has_post_thumbnail( $staff_id ) ) {
					$thumbnail = get_the_post_thumbnail( $staff_id, 'alchemists_thumbnail-player-block' );
				} else {
					$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/placeholder-140x210.jpg' ) . '" alt="">';
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

				if ( isset( $staff->phone ) ) {
					$phone = $staff->phone;
				} else {
					$phone = '';
				}
				if ( isset( $staff->email ) ) {
					$email = $staff->email;
				} else {
					$email = '';
				}

				$staff_details = array();

				if ( $phone !== '' ):
					if ( $link_phone ) $phone = '<a href="tel:' . $phone . '">' . $phone . '</a>';
					$staff_details[ esc_html__( 'Phone:', 'alchemists' ) ] = $phone;
				endif;

				if ( $email !== '' ):
					if ( $link_email ) $email = '<a href="mailto:' . $email . '">' . $email . '</a>';
					$staff_details[ esc_html__( 'Email:', 'alchemists' ) ] = $email;
				endif;

				$staff_details = apply_filters( 'sportspress_staff_details', $staff_details, $staff_id );

				$nationalities = $staff->nationalities();
				if ( $show_nationality && $nationalities && is_array( $nationalities ) ):
					$values = array();
					foreach ( $nationalities as $nationality ):
						if ( 2 == strlen( $nationality ) ):
							$legacy = SP()->countries->legacy;
							$nationality = strtolower( $nationality );
							$nationality = sp_array_value( $legacy, $nationality, null );
						endif;
						$country_name = sp_array_value( $countries, $nationality, null );
						$values[] = $country_name ? ( $show_nationality_flags ? '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" alt="' . $nationality . '"> ' : '' ) . $country_name : '&mdash;';
					endforeach;
					$staff_details[ __( 'Nationality', 'alchemists' ) ] = implode( '<br>', $values );
				endif;
				?>

				<div class="team-roster__item">
					<div class="team-roster__holder">
						<figure class="team-roster__img">
							<?php echo wp_kses_post( $thumbnail ); ?>
						</figure>
						<div class="team-roster__content">
							<header class="team-roster__member-header">
								<h2 class="team-roster__member-name team-roster__member-name--no-link"><?php echo wp_kses_post( $name ); ?></h2>
							</header>
							<div class="team-roster__member-subheader">
								<div class="team-roster__member-position">
									<?php
									$roles = $staff->roles();
									if ( ! empty( $roles ) ):
										$roles = wp_list_pluck( $roles, 'name' );
										echo implode( ', ', $roles );
									else:
										esc_html_e( 'Staff', 'alchemists' );
									endif;
									?>
								</div>
							</div>
							<ul class="team-roster__member-details list-unstyled">
								<?php foreach( $staff_details as $label => $value ) : ?>
									<li class="team-roster__member-details-item">
										<span class="item-title"><?php echo wp_kses_post( $label ); ?></span>
										<span class="item-desc"><?php echo wp_kses_post( $value ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
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
