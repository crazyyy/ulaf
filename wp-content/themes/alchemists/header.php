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
 * @version   4.6.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php
/**
 * wp_body_open hook.
 *
 * @since WordPress 5.2
 */
wp_body_open();

/**
 * Hook: alchemists_before_body_content
 *
 * @hooked alchemists_page_preloader - 10
 */
do_action( 'alchemists_before_body_content' );
?>

<div class="site-wrapper">
	<div class="site-overlay"></div>

	<?php
	// Skip header for Canvas template
	if ( is_page_template( 'template-canvas.php' ) ) {
		do_action( 'alc_site_header_after' );
		return;
	}

	// Get theme options once
	$alchemists_data = get_option( 'alchemists_data', array() );

	// Get header layout with proper sanitization
	$header_layout = alchemists_get_header_layout( $alchemists_data );

	// Header variables (prepared but not used in this template - for child theme extensibility)
	$header_vars = array(
		'social'              => isset( $alchemists_data['alchemists__header-primary-social'] ) ? (bool) $alchemists_data['alchemists__header-primary-social'] : true,
		'social_position'     => isset( $alchemists_data['alchemists__header-social-position'] ) ? sanitize_key( $alchemists_data['alchemists__header-social-position'] ) : 'header_primary',
		'search_form'         => isset( $alchemists_data['alchemists__header-search-form'] ) ? (bool) $alchemists_data['alchemists__header-search-form'] : true,
		'search_form_position' => isset( $alchemists_data['alchemists__header-search-form-posiiton'] ) ? sanitize_key( $alchemists_data['alchemists__header-search-form-posiiton'] ) : 'header_secondary',
		'pushy_panel'         => isset( $alchemists_data['alchemists__header-pushy-panel'] ) ? (bool) $alchemists_data['alchemists__header-pushy-panel'] : true,
	);

	/**
	 * Filter header variables
	 *
	 * @since 4.6.0
	 * @param array $header_vars Header configuration variables
	 */
	$header_vars = apply_filters( 'alchemists_header_vars', $header_vars );

	// Header Mobile
	get_template_part( 'template-parts/header/header', 'mobile' );
	?>

	<!-- Header Desktop -->
	<header id="site-header" class="header header--<?php echo esc_attr( $header_layout ); ?>" role="banner">

		<?php
		/**
		 * Hook: alchemists_header_top
		 *
		 * @hooked alchemists_header_top_bar - 10
		 */
		do_action( 'alchemists_header_top' );

		// Header Top Bar
		get_template_part( 'template-parts/header/header', 'top-bar' );

		// Header Secondary (skip for layout-3)
		if ( 'layout-3' !== $header_layout ) {
			get_template_part( 'template-parts/header/header', 'secondary' );
		}

		// Header Primary
		get_template_part( 'template-parts/header/header', 'primary' );

		/**
		 * Hook: alchemists_header_bottom
		 *
		 * @since 4.6.0
		 */
		do_action( 'alchemists_header_bottom' );
		?>

	</header>
	<!-- Header / End -->

	<?php
	// Header Tertiary
	get_template_part( 'template-parts/header/header', 'tertiary' );

	// Pushy Panel
	if ( $header_vars['pushy_panel'] ) {
		get_template_part( 'template-parts/pushy', 'panel' );
	}

	/**
	 * Hook: alc_site_header_after
	 *
	 * @since 1.0.0
	 */
	do_action( 'alc_site_header_after' );