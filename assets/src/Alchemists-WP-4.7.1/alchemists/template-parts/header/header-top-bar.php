<?php
/**
 * Template part for Header Top Bar.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$top_bar         = isset( $alchemists_data['alchemists__header-top-bar'] ) ? $alchemists_data['alchemists__header-top-bar'] : true;
$top_bar_links   = isset( $alchemists_data['alchemists__header-top-bar-links'] ) ? $alchemists_data['alchemists__header-top-bar-links'] : true;
$top_bar_divider = isset( $alchemists_data['alchemists__header-top-bar-divider-type'] ) ? $alchemists_data['alchemists__header-top-bar-divider-type'] : 'slash';

// Menu Class
$menu_class = array( 'nav-account' );
$menu_class[] = 'nav-account__divider-' . $top_bar_divider;
$menu_class = implode( ' ', $menu_class );
?>

<?php if ( $top_bar ) : ?>
<div class="header__top-bar clearfix">
	<div class="container">
		<div class="header__top-bar-inner">
			<?php
			// Social Links
			if ( $header_social && 'top_bar' == $header_social_pos ) {
				include( locate_template( 'template-parts/header/social-links.php' ) );
			}

			// Top Menu
			if ( $top_bar_links ) {
				if ( has_nav_menu( 'top_menu' ) ) {
					wp_nav_menu(
						array(
							'theme_location'  => 'top_menu',
							'container'       => false,
							'menu_class'      => $menu_class,
							'echo'            => true,
							'fallback_cb'     => false,
							'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							'depth'           => 2,
							'walker'          => new Alchemists_Top_Menu_Walker
						)
					);
				}
			}
			?>
		</div>

	</div>
</div>
<?php endif; ?>
