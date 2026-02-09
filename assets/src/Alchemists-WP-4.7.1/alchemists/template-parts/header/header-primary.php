<?php
/**
 * Template part for Header Primary section.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

$pushy_panel   = isset( $alchemists_data['alchemists__header-pushy-panel'] ) ? $alchemists_data['alchemists__header-pushy-panel'] : 1;

$header_primary_classes = array( 'header__primary' );
if ( 'layout-4' == $header_layout ) {
	$header_primary_classes[] = 'header__primary--center';
}
?>

<div class="<?php echo esc_attr( implode( ' ', $header_primary_classes ) ); ?>">
	<div class="container">
		<div class="header__primary-inner">

			<?php
			// display Logo here for all layout except Layout 3
			if ( 'layout-4' != $header_layout ) {
				include( locate_template( 'template-parts/header/logo.php' ) );
			}
			?>

			<!-- Main Navigation -->
			<nav class="main-nav">
				<?php
				// Primary navigation
				if ( has_nav_menu('primary') ) {
					wp_nav_menu(
						array(
							'theme_location'  => 'primary',
							'container'       => false,
							'menu_class'      => 'main-nav__list',
							'echo'            => true,
							'fallback_cb'     => 'wp_page_menu',
							'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							'depth'           => 0,
							'walker'          => new Alchemists_Nav_Menu()
						)
					);
				}
				?>

				<?php
				// Social Links
				if ( $header_social && 'header_primary' == $header_social_pos ) {
					include( locate_template( 'template-parts/header/social-links.php' ) );
				}

				// Search Form
				if ( $search_form && 'header_primary' == $search_form_position ) {
					include( locate_template( 'template-parts/header/header-search-form.php' ) );
				}
				?>

				<?php if ( $pushy_panel == 1 ) : ?>
				<!-- Pushy Panel Toggle -->
				<a href="#" class="pushy-panel__toggle">
					<span class="pushy-panel__line"></span>
				</a>
				<!-- Pushy Panel Toggle / Eng -->
				<?php endif; ?>

			</nav>
			<!-- Main Navigation / End -->

			<?php if ( 'layout-3' == $header_layout ) : ?>
			<div class="header__primary-spacer"></div>
			<?php endif; ?>

			<?php
			// Search Form
			if ( $search_form && 'layout-3' == $header_layout ) {
				include( locate_template( 'template-parts/header/header-search-form.php' ) );
			}

			// Info Block
			if ( 'layout-3' == $header_layout ) {
				include( locate_template( 'template-parts/header/header-info-block.php' ) );
			}
			?>
		</div>
	</div>
</div>
