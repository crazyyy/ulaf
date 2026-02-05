<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 */
 
boldthemes_set_override();
boldthemes_header_init();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); do_action( 'boldthemes_body_data' ); ?> style="<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--page-primary-color', 'css_var2' => '--page-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'template_color_scheme' ) ] ) ); ?><?php do_action( 'boldthemes_css_vars' ); ?>">
<?php wp_body_open(); ?>
<?php get_template_part( 'framework/template-parts/preloader' ); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'campo' ); ?></a>
	<?php get_template_part( 'assets/template-parts/header-menu' ); ?>
	<?php get_template_part( 'framework/template-parts/default-headline' ); ?>
	<?php if ( BoldThemesFramework::$page_for_header_id != '' && ! is_search() ) { ?>
		<?php
		$content = get_post( BoldThemesFramework::$page_for_header_id );
		if ( ! is_null( $content ) && $content != '' ) {
			$top_content = $content->post_content;
			if ( $top_content != '' ) {
				$top_content = do_shortcode($top_content);
				$top_content = preg_replace( '/data-edit_url="(.*?)"/s', 'data-edit_url="' . get_edit_post_link( BoldThemesFramework::$page_for_header_id, '' ) . '"', $top_content );
				echo '<div class="bt-blog-header-content">' . str_replace( ']]>', ']]&gt;', $top_content ) . '</div>';
			}
		}
	} ?>	
	<div id="content" class="site-content">
