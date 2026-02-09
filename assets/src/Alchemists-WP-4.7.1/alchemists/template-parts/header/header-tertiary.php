<?php
/**
 * Template part for Header Tertiary section.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.2.1
 * @version   4.2.1
 */

$header_tertiary         = isset( $alchemists_data['alchemists__header-tertiary'] ) ? $alchemists_data['alchemists__header-tertiary'] : 0;
$header_tertiary_heading = isset( $alchemists_data['alchemists__header-tertiary-heading'] ) ? $alchemists_data['alchemists__header-tertiary-heading'] : '';
$header_tertiary_toggle  = isset( $alchemists_data['alchemists__header-tertiary-toggle'] ) ? $alchemists_data['alchemists__header-tertiary-toggle'] : '';
$header_tertiary_classes = array( 'header-tertiary' );

if ( $header_tertiary ) : ?>
	<div class="<?php echo esc_attr( implode( ' ', $header_tertiary_classes ) ); ?>">
		<div class="container">
			<div class="header-tertiary__inner">

				<!-- Secondary Navigation -->
				<nav class="secondary-nav">
					<?php if ( $header_tertiary_heading ) : ?>
						<h3 class="secondary-nav__heading"><?php echo esc_html( $header_tertiary_heading ); ?></h3>
					<?php endif; ?>

					<?php if ( $header_tertiary_toggle ) : ?>
						<span class="secondary-nav__toggle" id="secondary-nav__toggle"><?php echo esc_html( $header_tertiary_toggle ); ?></span>
					<?php endif; ?>
					<?php
					// Secondary navigation
					if ( has_nav_menu( 'secondary' ) ) {
						wp_nav_menu(
							array(
								'theme_location'  => 'secondary',
								'container'       => false,
								'menu_class'      => 'secondary-nav__list',
								'echo'            => true,
								'fallback_cb'     => 'wp_page_menu',
								'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
								'depth'           => 0,
								'walker'          => new Alchemists_Nav_Menu()
							)
						);
					}
					?>

				</nav>
				<!-- Secondary Navigation / End -->
			</div>
		</div>
	</div>
<?php endif; ?>
