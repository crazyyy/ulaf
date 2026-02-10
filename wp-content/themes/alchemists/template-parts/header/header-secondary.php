<?php
/**
 * Template part for Header Secondary section.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.2
 * @version   4.7.0
 */

// Banner
$header_secondary = isset( $alchemists_data['alchemists__header-secondary'] ) ? $alchemists_data['alchemists__header-secondary'] : 1;
$banner           = isset( $alchemists_data['alchemists__header-banner'] ) ? $alchemists_data['alchemists__header-banner'] : 0;
$banner_img       = isset( $alchemists_data['alchemists__header-banner-image']['url'] ) ? $alchemists_data['alchemists__header-banner-image']['url'] : '';
$banner_title     = !empty( $alchemists_data['alchemists__header-banner-image']['id'] ) ? get_the_title( $alchemists_data['alchemists__header-banner-image']['id'] ) : esc_html__( 'Header Banner', 'alchemists');
$banner_link      = isset( $alchemists_data['alchemists__header-banner-link'] ) ? $alchemists_data['alchemists__header-banner-link'] : '';
$banner_target    = isset( $alchemists_data['alchemists__header-banner-target'] ) ? $alchemists_data['alchemists__header-banner-target'] : 1;
?>

<?php if ( $header_secondary ) : ?>
	<div class="header__secondary">
		<div class="container">
			<div class="header__secondary-inner">

				<?php
				// display Logo here Layout 3
				if ( 'layout-4' == $header_layout ) {
					include( locate_template( 'template-parts/header/logo.php' ) );
				}

				// Header Search Form
				if ( $search_form && $search_form_position == 'header_secondary') {
					include( locate_template( 'template-parts/header/header-search-form.php' ) );
				}

				// Header Info Block
				if ( 'layout-3' != $header_layout ) {
					include( locate_template( 'template-parts/header/header-info-block.php' ) );
				}
				?>

				<?php if ( $banner ) : ?>
					<!-- Header Banner -->
					<div class="header-banner">
						<?php
						// display banner placeholder image if Banner displaying is enabled but not selected
						if ( empty( $banner_img ) ) {
							$banner_img = get_template_directory_uri() . '/assets/images/football/banner-420x60.jpg';
						}

						// prepare output
						$banner_ouput = '<img src="' . esc_url( $banner_img ) . '" alt="' . esc_attr( $banner_title ) . '">';

						// wrap with the link if set
						if ( $banner_link ) {
							$banner_target = $banner_target ? 'target="_blank"' : '';
							$banner_ouput = '<a href="' . esc_url( $banner_link ) . '" ' . $banner_target . '>' . $banner_ouput . '</a>';
						}

						// output banner
						echo wp_kses_post( $banner_ouput );
						?>
					</div>
					<!-- Header Banner / End -->
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>
