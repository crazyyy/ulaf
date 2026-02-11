<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php
$alchemists_data = get_option( 'alchemists_data' );
$header_social        = isset( $alchemists_data['alchemists__header-primary-social'] ) ? $alchemists_data['alchemists__header-primary-social'] : 1;
$header_social_pos    = isset( $alchemists_data['alchemists__header-social-position'] ) ? $alchemists_data['alchemists__header-social-position'] : 'header_primary';
$search_form          = isset( $alchemists_data['alchemists__header-search-form'] ) ? esc_html( $alchemists_data['alchemists__header-search-form'] ) : true;
$search_form_position = isset( $alchemists_data['alchemists__header-search-form-posiiton'] ) ? $alchemists_data['alchemists__header-search-form-posiiton'] : 'header_secondary';

// Header Layout
if ( isset( $_GET['header-layout'] ) && ! empty( $_GET['header-layout'] )) {
	$header_layout = $_GET['header-layout'];
} else {
	$header_layout = isset( $alchemists_data['alchemists__header-layout'] ) ? $alchemists_data['alchemists__header-layout'] : 'layout-1';
}
?>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php wp_body_open(); ?>

	<?php do_action( 'alchemists_before_body_content' ); ?>

	<div class="site-wrapper">
		<div class="site-overlay"></div>

		<?php
		// Don't show header if Canvas template used
		if ( ! is_page_template( 'template-canvas.php' ) ) :

			// Header Mobile
			include( locate_template( 'template-parts/header/header-mobile.php' ) );
			?>

			<!-- Header Desktop -->
			<header class="header header--<?php echo esc_attr( $header_layout ); ?>">

				<?php
				// Header Top Bar
				include( locate_template( 'template-parts/header/header-top-bar.php' ) );

				// Header Secondary
				if ( 'layout-3' != $header_layout ) {
					include( locate_template( 'template-parts/header/header-secondary.php' ) );
				}

				// Header Primary
				include( locate_template( 'template-parts/header/header-primary.php' ) );
				?>

			</header>
			<!-- Header / End -->

			<?php
			// Header Tertiary
			include( locate_template( 'template-parts/header/header-tertiary.php' ) );

			// Pushy Panel
			$pushy_panel = isset( $alchemists_data['alchemists__header-pushy-panel'] ) ? $alchemists_data['alchemists__header-pushy-panel'] : 1;

			if ( $pushy_panel ) {
				get_template_part( 'template-parts/pushy-panel');
			}

		endif;

		do_action( 'alc_site_header_after' );
