<?php
/**
 * Plugin Name: Alchemists SCSS Compiler
 * Plugin URI: https://github.com/danfisher85/alc-scss
 * Description: Compiles SCSS to CSS for Alchemists WP Theme.
 * Version: 4.5.0
 * Author: Dan Fisher
 * Author URI: https://themeforest.net/user/dan_fisher
 */


/*
 * 1. PLUGIN GLOBAL VARIABLES
 */

// Plugin Paths
if (!defined('DFSCSS_THEME_DIR'))
		define('DFSCSS_THEME_DIR', get_stylesheet_directory());

if (!defined('DFSCSS_PLUGIN_NAME'))
		define('DFSCSS_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('DFSCSS_PLUGIN_DIR'))
		define('DFSCSS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . DFSCSS_PLUGIN_NAME);

if (!defined('DFSCSS_PLUGIN_URL'))
		define('DFSCSS_PLUGIN_URL', WP_PLUGIN_URL . '/' . DFSCSS_PLUGIN_NAME);

// Plugin Version
if (!defined('DFSCSS_VERSION_KEY'))
		define('DFSCSS_VERSION_KEY', 'dfscss_version');

if (!defined('DFSCSS_VERSION_NUM'))
		define('DFSCSS_VERSION_NUM', '4.4.2');



/*
 * 2. REQUIRE DEPENDENCIES
 *
 *    scssphp - scss compiler
 */

include_once DFSCSS_PLUGIN_DIR . '/compiler/WP_SCSS_Compiler.php'; // SCSS Compiler (vendor)


/**
 * 3. ENQUEUE STYLES
 */

add_action( 'wp_enqueue_scripts', 'df_enqueue_styles', 98 );
function df_enqueue_styles() {

	$sport = 'basketball';

	if ( function_exists( 'alchemists_sp_preset' ) ) {
		if ( alchemists_sp_preset('soccer') ) {
			$sport = 'soccer';
		} elseif ( alchemists_sp_preset('football') ) {
			$sport = 'football';
		} elseif ( alchemists_sp_preset('esports') ) {
			$sport = 'esports';
		}
	}

	// Check if language is RTL
	$alchemists_dir = '';
	if ( is_rtl() ) {
		$alchemists_dir = '-rtl';
	}

	// Main styles
	wp_enqueue_style( 'df-compiled', get_template_directory_uri() . '/sass/style-skin-' . $sport . $alchemists_dir . '.scss', array( 'alchemists-style' ), DFSCSS_VERSION_NUM );

	if ( class_exists( 'woocommerce' ) ) {
		// Woocommerce styles
		wp_enqueue_style( 'df-compiled-woocommerce', get_template_directory_uri() . '/sass/woocommerce/woocommerce-skin-' . $sport . '.scss', array( 'woocommerce' ), DFSCSS_VERSION_NUM );
	}

	if ( class_exists( 'SportsPress' ) ) {
		// Sportspress styles
		wp_enqueue_style( 'df-compiled-sportspress', get_template_directory_uri() . '/sass/sportspress-skin-' . $sport . $alchemists_dir .'.scss', array( 'alchemists-sportspress' ), DFSCSS_VERSION_NUM );
	}
}



/**
 * 4. PASS VARIABLES INTO COMPILER
 */
add_filter( 'wp_scss_variables', 'df_scss_vars', 10, 2 );
function df_scss_vars( $vars, $handle ) {

	$alchemists_data = get_option('alchemists_data');

	if ( ! is_array( $vars ) ) {
		$vars = array();
	}

	// Colors
	if ( isset( $alchemists_data['color-primary'] ) && !empty( $alchemists_data['color-primary'] ) ) {
		$vars['color-primary']        = $alchemists_data['color-primary'];
	}
	if ( isset( $alchemists_data['color-primary-darken'] ) && !empty( $alchemists_data['color-primary-darken'] ) ) {
		$vars['color-primary-darken'] = $alchemists_data['color-primary-darken'];
	}
	if ( isset( $alchemists_data['color-dark'] ) && !empty( $alchemists_data['color-dark'] ) ) {
		$vars['color-dark'] = $alchemists_data['color-dark'];
	}
	if ( isset( $alchemists_data['color-dark-lighten'] ) && !empty( $alchemists_data['color-dark-lighten'] ) ) {
		$vars['color-dark-lighten']   = $alchemists_data['color-dark-lighten'];
	}
	if ( isset( $alchemists_data['color-gray'] ) && !empty( $alchemists_data['color-gray'] ) ) {
		$vars['color-gray']           = $alchemists_data['color-gray'];
	}
	if ( isset( $alchemists_data['color-2'] ) && !empty( $alchemists_data['color-2'] ) ) {
		$vars['color-2']              = $alchemists_data['color-2'];
	}
	if ( isset( $alchemists_data['color-dark-2'] ) && !empty( $alchemists_data['color-dark-2'] ) ) {
		$vars['color-dark-2']         = $alchemists_data['color-dark-2'];
	}
	if ( isset( $alchemists_data['color-3'] ) && !empty( $alchemists_data['color-3'] ) ) {
		$vars['color-3']              = $alchemists_data['color-3'];
	}
	if ( isset( $alchemists_data['color-4'] ) && !empty( $alchemists_data['color-4'] ) ) {
		$vars['color-4']              = $alchemists_data['color-4'];
	}
	if ( isset( $alchemists_data['color-4-darken'] ) && !empty( $alchemists_data['color-4-darken'] ) ) {
		$vars['color-4-darken']       = $alchemists_data['color-4-darken'];
	}

	// Soccer
	if ( function_exists( 'alchemists_sp_preset' ) ) {
		if ( alchemists_sp_preset('soccer') ) {
			if ( isset( $alchemists_data['color-4-darken'] ) && !empty( $alchemists_data['color-4-darken'] ) ) {
				$vars['color-4-darken'] = $alchemists_data['color-4-darken'];
			}
		}
	}


	// Card
	if ( isset( $alchemists_data['alchemists__card-bg'] ) && !empty( $alchemists_data['alchemists__card-bg'] ) ) {
		$vars['card-bg'] = $alchemists_data['alchemists__card-bg'];
	}
	if ( isset( $alchemists_data['alchemists__card-header-bg'] ) && !empty( $alchemists_data['alchemists__card-header-bg'] ) ) {
		$vars['card-header-bg'] = $alchemists_data['alchemists__card-header-bg'];
	}
	if ( isset( $alchemists_data['alchemists__card-subheader-bg'] ) && !empty( $alchemists_data['alchemists__card-subheader-bg'] ) ) {
		$vars['card-subheader-bg'] = $alchemists_data['alchemists__card-subheader-bg'];
	}
	if ( isset( $alchemists_data['alchemists__card-border-color'] ) && !empty( $alchemists_data['alchemists__card-border-color'] ) ) {
		$vars['card-border-color'] = $alchemists_data['alchemists__card-border-color'];
	}


	// Form
	if ( isset( $alchemists_data['alchemists__form-control']['regular'] ) && !empty( $alchemists_data['alchemists__form-control']['regular'] ) ) {
		$vars['input-bg'] = $alchemists_data['alchemists__form-control']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__form-control']['active'] ) && !empty( $alchemists_data['alchemists__form-control']['active'] ) ) {
		$vars['input-bg-focus'] = $alchemists_data['alchemists__form-control']['active'];
	}
	if ( isset( $alchemists_data['alchemists__form-control-border']['regular'] ) && !empty( $alchemists_data['alchemists__form-control-border']['regular'] ) ) {
		$vars['input-border'] = $alchemists_data['alchemists__form-control-border']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__form-control-border']['active'] ) && !empty( $alchemists_data['alchemists__form-control-border']['active'] ) ) {
		$vars['input-border-focus'] = $alchemists_data['alchemists__form-control-border']['active'];
	}
	if ( isset( $alchemists_data['alchemists__form-control-txt']['regular'] ) && !empty( $alchemists_data['alchemists__form-control-txt']['regular'] ) ) {
		$vars['input-color'] = $alchemists_data['alchemists__form-control-txt']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__form-control-txt']['active'] ) && !empty( $alchemists_data['alchemists__form-control-txt']['active'] ) ) {
		$vars['input-color-focus'] = $alchemists_data['alchemists__form-control-txt']['active'];
	}
	if ( isset( $alchemists_data['alchemists__form-control-placeholder'] ) && !empty( $alchemists_data['alchemists__form-control-placeholder'] ) ) {
		$vars['input-color-placeholder'] = $alchemists_data['alchemists__form-control-placeholder'];
	}


	// Table
	if ( isset( $alchemists_data['alchemists__table-bg'] ) && !empty( $alchemists_data['alchemists__table-bg'] ) ) {
		$vars['table-bg'] = $alchemists_data['alchemists__table-bg'];
	}
	if ( isset( $alchemists_data['alchemists__table-bg-hover'] ) && !empty( $alchemists_data['alchemists__table-bg-hover'] ) ) {
		$vars['table-bg-hover'] = $alchemists_data['alchemists__table-bg-hover'];
	}
	if ( isset( $alchemists_data['alchemists__table-bg-active'] ) && !empty( $alchemists_data['alchemists__table-bg-active'] ) ) {
		$vars['table-bg-active'] = $alchemists_data['alchemists__table-bg-active'];
	}
	if ( isset( $alchemists_data['alchemists__table-border-color'] ) && !empty( $alchemists_data['alchemists__table-border-color'] ) ) {
		$vars['table-border-color'] = $alchemists_data['alchemists__table-border-color'];
	}
	if ( isset( $alchemists_data['alchemists__table-thead-bg-color'] ) && !empty( $alchemists_data['alchemists__table-thead-bg-color'] ) ) {
		$vars['table-thead-bg-color'] = $alchemists_data['alchemists__table-thead-bg-color'];
	}
	if ( isset( $alchemists_data['alchemists__table-thead-color'] ) && !empty( $alchemists_data['alchemists__table-thead-color'] ) ) {
		$vars['table-thead-color'] = $alchemists_data['alchemists__table-thead-color'];
	}
	if ( isset( $alchemists_data['alchemists__table-highlight-color'] ) && !empty( $alchemists_data['alchemists__table-highlight-color'] ) ) {
		$vars['table-highlight'] = $alchemists_data['alchemists__table-highlight-color'];
	}


	// Progress Bars
	if ( isset( $alchemists_data['alchemists__progress-bars-track-color'] ) && !empty( $alchemists_data['alchemists__progress-bars-track-color'] ) ) {
		$vars['progress-bg'] = $alchemists_data['alchemists__progress-bars-track-color'];
	}


	// Header Primary Height
	if ( isset( $alchemists_data['alchemists__header-primary-height'] ) && !empty( $alchemists_data['alchemists__header-primary-height'] ) ) {
		$vars['nav-height'] = $alchemists_data['alchemists__header-primary-height'] . 'px';
	}


	// Header Tertiary Height (can be 0)
	if ( isset( $alchemists_data['alchemists__header-tertiary-height'] ) && $alchemists_data['alchemists__header-tertiary-height'] != '' ) {
		$vars['nav-secondary-height'] = $alchemists_data['alchemists__header-tertiary-height'] . 'px';
	}


	// Mobile Nav Width
	$nav_mobile_fullwidth = isset( $alchemists_data['alchemists__mobile-nav-fullwidth'] ) ? $alchemists_data['alchemists__mobile-nav-fullwidth'] : 0;

	if ( $nav_mobile_fullwidth == 1 ) {
		$vars['nav-mobile-width'] = '100%';
	} else {
		if ( isset( $alchemists_data['alchemists__mobile-nav-width'] ) && !empty( $alchemists_data['alchemists__mobile-nav-width'] ) ) {
			$vars['nav-mobile-width'] = $alchemists_data['alchemists__mobile-nav-width'] . 'px';
		}
	}

	// Mobile Nav Height
	if ( isset( $alchemists_data['alchemists__mobile-nav-height'] ) && !empty( $alchemists_data['alchemists__mobile-nav-height'] ) ) {
		$vars['header-mobile-height'] = $alchemists_data['alchemists__mobile-nav-height'] . 'px';
	}


	// Body Background
	if ( isset( $alchemists_data['alchemists__body-bg']['background-color'] ) && !empty( $alchemists_data['alchemists__body-bg']['background-color'] ) ) {
		$vars['body-bg-color'] = $alchemists_data['alchemists__body-bg']['background-color'];
	}


	// Links Color
	if ( isset( $alchemists_data['alchemists__link-color']['regular'] ) && !empty( $alchemists_data['alchemists__link-color']['regular'] ) ) {
		$vars['link-color'] = $alchemists_data['alchemists__link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__link-color']['hover'] ) && !empty( $alchemists_data['alchemists__link-color']['hover'] ) ) {
		$vars['link-color-hover'] = $alchemists_data['alchemists__link-color']['hover'];
	}


	// Outline Button Color
	if ( isset( $alchemists_data['alchemists__button_outline_txt_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_outline_txt_color']['regular'] ) ) {
		$vars['btn-o-default-color'] = $alchemists_data['alchemists__button_outline_txt_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_outline_txt_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_outline_txt_color']['hover'] ) ) {
		$vars['btn-o-default-color-hover'] = $alchemists_data['alchemists__button_outline_txt_color']['hover'];
	}

	// Outline Button Background Color
	if ( isset( $alchemists_data['alchemists__button_outline_bg_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_outline_bg_color']['regular'] ) ) {
		$vars['btn-o-default-bg'] = $alchemists_data['alchemists__button_outline_bg_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_outline_bg_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_outline_bg_color']['hover'] ) ) {
		$vars['btn-o-default-bg-hover'] = $alchemists_data['alchemists__button_outline_bg_color']['hover'];
	}

	// Outline Button Border Color
	if ( isset( $alchemists_data['alchemists__button_outline_border_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_outline_border_color']['regular'] ) ) {
		$vars['btn-o-default-border'] = $alchemists_data['alchemists__button_outline_border_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_outline_border_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_outline_border_color']['hover'] ) ) {
		$vars['btn-o-default-border-hover'] = $alchemists_data['alchemists__button_outline_border_color']['hover'];
	}

	// Default Button Color
	if ( isset( $alchemists_data['alchemists__button_default_txt_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_default_txt_color']['regular'] ) ) {
		$vars['btn-default-color'] = $alchemists_data['alchemists__button_default_txt_color']['regular'];
	}

	// Default Button Background Color
	if ( isset( $alchemists_data['alchemists__button_default_bg_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_default_bg_color']['regular'] ) ) {
		$vars['btn-default-bg'] = $alchemists_data['alchemists__button_default_bg_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_default_bg_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_default_bg_color']['hover'] ) ) {
		$vars['btn-default-hover'] = $alchemists_data['alchemists__button_default_bg_color']['hover'];
	}


	// Default Alt Button Color
	if ( isset( $alchemists_data['alchemists__button_default_alt_txt_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_default_alt_txt_color']['regular'] ) ) {
		$vars['btn-default-alt-color'] = $alchemists_data['alchemists__button_default_alt_txt_color']['regular'];
	}

	// Default Alt Button Background Color
	if ( isset( $alchemists_data['alchemists__button_default_alt_bg_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_default_alt_bg_color']['regular'] ) ) {
		$vars['btn-default-alt-bg'] = $alchemists_data['alchemists__button_default_alt_bg_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_default_alt_bg_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_default_alt_bg_color']['hover'] ) ) {
		$vars['btn-default-alt-hover'] = $alchemists_data['alchemists__button_default_alt_bg_color']['hover'];
	}


	// Button Primary Color
	if ( isset( $alchemists_data['alchemists__button_primary_txt_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_primary_txt_color']['regular'] ) ) {
		$vars['btn-primary-color'] = $alchemists_data['alchemists__button_primary_txt_color']['regular'];
	}

	// Button Primary Background Color
	if ( isset( $alchemists_data['alchemists__button_primary_bg_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_primary_bg_color']['regular'] ) ) {
		$vars['btn-primary-bg'] = $alchemists_data['alchemists__button_primary_bg_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_primary_bg_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_primary_bg_color']['hover'] ) ) {
		$vars['btn-primary-hover'] = $alchemists_data['alchemists__button_primary_bg_color']['hover'];
	}


	// Button Primary Inverse Color
	if ( isset( $alchemists_data['alchemists__button_primary_inverse_txt_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_primary_inverse_txt_color']['regular'] ) ) {
		$vars['btn-primary-inverse-color'] = $alchemists_data['alchemists__button_primary_inverse_txt_color']['regular'];
	}

	// Button Primary Inverse Background Color
	if ( isset( $alchemists_data['alchemists__button_primary_inverse_bg_color']['regular'] ) && !empty( $alchemists_data['alchemists__button_primary_inverse_bg_color']['regular'] ) ) {
		$vars['btn-primary-inverse-bg'] = $alchemists_data['alchemists__button_primary_inverse_bg_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__button_primary_inverse_bg_color']['hover'] ) && !empty( $alchemists_data['alchemists__button_primary_inverse_bg_color']['hover'] ) ) {
		$vars['btn-primary-inverse-hover'] = $alchemists_data['alchemists__button_primary_inverse_bg_color']['hover'];
	}


	// Top Bar
	if ( isset( $alchemists_data['alchemists__header-top-bar-bg'] ) && !empty( $alchemists_data['alchemists__header-top-bar-bg'] ) ) {
		$vars['header-top-bg'] = $alchemists_data['alchemists__header-top-bar-bg'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-link-color'] ) && !empty( $alchemists_data['alchemists__header-top-bar-link-color'] ) ) {
		$vars['top-bar-link-color'] = $alchemists_data['alchemists__header-top-bar-link-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-highlight'] ) && !empty( $alchemists_data['alchemists__header-top-bar-highlight'] ) ) {
		$vars['top-bar-highlight'] = $alchemists_data['alchemists__header-top-bar-highlight'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-text-color'] ) && !empty( $alchemists_data['alchemists__header-top-bar-text-color'] ) ) {
		$vars['top-bar-text-color'] = $alchemists_data['alchemists__header-top-bar-text-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-divider-color'] ) && !empty( $alchemists_data['alchemists__header-top-bar-divider-color'] ) ) {
		$vars['top-bar-divider-color'] = $alchemists_data['alchemists__header-top-bar-divider-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-dropdown-bg'] ) && !empty( $alchemists_data['alchemists__header-top-bar-dropdown-bg'] ) ) {
		$vars['top-bar-dropdown-bg'] = $alchemists_data['alchemists__header-top-bar-dropdown-bg'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-dropdown-border-color'] ) && !empty( $alchemists_data['alchemists__header-top-bar-dropdown-border-color'] ) ) {
		$vars['top-bar-dropdown-border'] = $alchemists_data['alchemists__header-top-bar-dropdown-border-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-dropdown-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-top-bar-dropdown-link-color']['regular'] ) ) {
		$vars['top-bar-dropdown-link-color'] = $alchemists_data['alchemists__header-top-bar-dropdown-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-dropdown-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-top-bar-dropdown-link-color']['hover'] ) ) {
		$vars['top-bar-dropdown-link-color-hover'] = $alchemists_data['alchemists__header-top-bar-dropdown-link-color']['hover'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-social-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-top-bar-social-link-color']['regular'] ) ) {
		$vars['top-bar-social-icon-color'] = $alchemists_data['alchemists__header-top-bar-social-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-social-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-top-bar-social-link-color']['hover'] ) ) {
		$vars['top-bar-social-icon-color-hover'] = $alchemists_data['alchemists__header-top-bar-social-link-color']['hover'];
	}
	if ( isset( $alchemists_data['alchemists__header-top-bar-social-link-opacity'] ) && !empty( $alchemists_data['alchemists__header-top-bar-social-link-opacity'] ) ) {
		$vars['top-bar-social-icon-opacity'] = $alchemists_data['alchemists__header-top-bar-social-link-opacity'];
	}


	// Header Secondary Background
	if ( isset( $alchemists_data['alchemists__header-secondary-bg'] ) && !empty( $alchemists_data['alchemists__header-secondary-bg'] ) ) {
		$vars['header-bg'] = $alchemists_data['alchemists__header-secondary-bg'];
	}
	if ( isset( $alchemists_data['alchemists__header-secondary-bg'] ) && !empty( $alchemists_data['alchemists__header-secondary-bg'] ) ) {
		$vars['header-secondary-bg'] = $alchemists_data['alchemists__header-secondary-bg'];
	}


	// Header Primary Background
	if ( isset( $alchemists_data['alchemists__header-primary-bg'] ) && !empty( $alchemists_data['alchemists__header-primary-bg'] ) ) {
		$vars['header-primary-bg'] = $alchemists_data['alchemists__header-primary-bg'];
	}

	if ( isset( $alchemists_data['alchemists__header-primary-bg'] ) && !empty( $alchemists_data['alchemists__header-primary-bg'] ) ) {
		$vars['header-primary-alt-bg'] = $alchemists_data['alchemists__header-primary-bg'];
	}

	// Header Primary Links Color
	if ( isset( $alchemists_data['alchemists__header-primary-font-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-primary-font-color']['regular'] ) ) {
		$vars['nav-font-color'] = $alchemists_data['alchemists__header-primary-font-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-primary-font-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-primary-font-color']['hover'] ) ) {
		$vars['nav-font-color-hover'] = $alchemists_data['alchemists__header-primary-font-color']['hover'];
	}

	// Header Primary Link Border Color
	if ( isset( $alchemists_data['alchemists__header-primary-border-color'] ) && !empty( $alchemists_data['alchemists__header-primary-border-color'] ) ) {
		$vars['nav-active-border-color'] = $alchemists_data['alchemists__header-primary-border-color'];
	}

	// Header Primary Divider Color
	if ( isset( $alchemists_data['alchemists__header-primary-divider-color'] ) && !empty( $alchemists_data['alchemists__header-primary-divider-color'] ) ) {
		$vars['nav-item-divider-color'] = $alchemists_data['alchemists__header-primary-divider-color'];
	}

	// Header Primary Link Border Height
	if ( isset( $alchemists_data['alchemists__header-primary-border-height']['height'] ) && !empty( $alchemists_data['alchemists__header-primary-border-height']['height'] ) ) {
		$vars['nav-active-border-height'] = $alchemists_data['alchemists__header-primary-border-height']['height'];
	}

	// Header Submenu Background Color
	if ( isset( $alchemists_data['alchemists__header-primary-submenu-bg'] ) && !empty( $alchemists_data['alchemists__header-primary-submenu-bg'] ) ) {
		$vars['nav-sub-bg'] = $alchemists_data['alchemists__header-primary-submenu-bg'];
	}

	// Header Submenu Border Color
	if ( isset( $alchemists_data['alchemists__header-primary-submenu-border-color'] ) && !empty( $alchemists_data['alchemists__header-primary-submenu-border-color'] ) ) {
		$vars['nav-sub-border-color'] = $alchemists_data['alchemists__header-primary-submenu-border-color'];
	}

	// Header Submenu Link Color
	if ( isset( $alchemists_data['alchemists__header-primary-submenu-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-primary-submenu-link-color']['regular'] ) ) {
		$vars['nav-sub-link-color'] = $alchemists_data['alchemists__header-primary-submenu-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-primary-submenu-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-primary-submenu-link-color']['hover'] ) ) {
		$vars['nav-sub-hover-link-color'] = $alchemists_data['alchemists__header-primary-submenu-link-color']['hover'];
	}

	// Header Submenu Background Color
	if ( isset( $alchemists_data['alchemists__header-primary-submenu-hover-bg-color'] ) && !empty( $alchemists_data['alchemists__header-primary-submenu-hover-bg-color'] ) ) {
		$vars['nav-sub-hover-bg-color'] = $alchemists_data['alchemists__header-primary-submenu-hover-bg-color'];
	}

	// Header Submenu Caret Color
	if ( isset( $alchemists_data['alchemists__header-primary-submenu-dropdown-caret-color'] ) && !empty( $alchemists_data['alchemists__header-primary-submenu-dropdown-caret-color'] ) ) {
		$vars['nav-sub-caret-color'] = $alchemists_data['alchemists__header-primary-submenu-dropdown-caret-color'];
	}

	// Megamenu Text Color
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-txt-color'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-txt-color'] ) ) {
		$vars['nav-sub-megamenu-txt-color'] = $alchemists_data['alchemists__header-primary-megamenu-txt-color'];
	}

	// Megamenu Link Color
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-link-color']['regular'] ) ) {
		$vars['nav-sub-megamenu-link-color'] = $alchemists_data['alchemists__header-primary-megamenu-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-link-color']['hover'] ) ) {
		$vars['nav-sub-megamenu-link-color-hover'] = $alchemists_data['alchemists__header-primary-megamenu-link-color']['hover'];
	}

	// Megamenu Widget Meta Links Color
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-widget-meta-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-widget-meta-color']['regular'] ) ) {
		$vars['nav-sub-megamenu-meta-color'] = $alchemists_data['alchemists__header-primary-megamenu-widget-meta-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-widget-meta-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-widget-meta-color']['hover'] ) ) {
		$vars['nav-sub-megamenu-meta-color-hover'] = $alchemists_data['alchemists__header-primary-megamenu-widget-meta-color']['hover'];
	}

	// Megamenu Title Color
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-title-color'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-title-color'] ) ) {
		$vars['nav-sub-megamenu-title-color'] = $alchemists_data['alchemists__header-primary-megamenu-title-color'];
	}
	
	// Megamenu Post Title Color
	if ( isset( $alchemists_data['alchemists__header-primary-megamenu-post-title-color'] ) && !empty( $alchemists_data['alchemists__header-primary-megamenu-post-title-color'] ) ) {
		$vars['nav-sub-megamenu-post-title-color'] = $alchemists_data['alchemists__header-primary-megamenu-post-title-color'];
	}

	// Social Link Color
	if ( isset( $alchemists_data['alchemists__header-primary-social-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-primary-social-link-color']['regular'] ) ) {
		$vars['header-social-link-color'] = $alchemists_data['alchemists__header-primary-social-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-primary-social-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-primary-social-link-color']['hover'] ) ) {
		$vars['header-social-link-color-hover'] = $alchemists_data['alchemists__header-primary-social-link-color']['hover'];
	}

	// Mobile Nav Background Color
	if ( isset( $alchemists_data['alchemists__header-primary-mobile-nav-bg'] ) && !empty( $alchemists_data['alchemists__header-primary-mobile-nav-bg'] ) ) {
		$vars['nav-mobile-bg'] = $alchemists_data['alchemists__header-primary-mobile-nav-bg'];
	}

	// Mobile Nav Burger Menu Color
	if ( isset( $alchemists_data['alchemists__header-primary-mobile-burger-icon-color'] ) && !empty( $alchemists_data['alchemists__header-primary-mobile-burger-icon-color'] ) ) {
		$vars['nav-mobile-burger-icon'] = $alchemists_data['alchemists__header-primary-mobile-burger-icon-color'];
	}


	// Header Mobile Background Color
	if ( isset( $alchemists_data['alchemists__mobile-header-bg'] ) && !empty( $alchemists_data['alchemists__mobile-header-bg'] ) ) {
		$vars['header-mobile-bg'] = $alchemists_data['alchemists__mobile-header-bg'];
	}

	// Mobile Nav Links Color
	if ( isset( $alchemists_data['alchemists__header-primary-mobile-link-color'] ) && !empty( $alchemists_data['alchemists__header-primary-mobile-link-color'] ) ) {
		$vars['nav-mobile-color'] = $alchemists_data['alchemists__header-primary-mobile-link-color'];
	}

	// Mobile Nav Border Color
	if ( isset( $alchemists_data['alchemists__header-primary-mobile-border-color'] ) && !empty( $alchemists_data['alchemists__header-primary-mobile-border-color'] ) ) {
		$vars['nav-mobile-border'] = $alchemists_data['alchemists__header-primary-mobile-border-color'];
	}

	// Mobile Nav Submenu Background Color
	if ( isset( $alchemists_data['alchemists__header-primary-mobile-sub-bg'] ) && !empty( $alchemists_data['alchemists__header-primary-mobile-sub-bg'] ) ) {
		$vars['nav-mobile-sub-bg'] = $alchemists_data['alchemists__header-primary-mobile-sub-bg'];
	}

	// Mobile Nav Submenu Links Color
	if ( isset( $alchemists_data['alchemists__header-primary-mobile-sub-link-color'] ) && !empty( $alchemists_data['alchemists__header-primary-mobile-sub-link-color'] ) ) {
		$vars['nav-mobile-sub-color'] = $alchemists_data['alchemists__header-primary-mobile-sub-link-color'];
	}


	// Header Info Block
	if ( isset( $alchemists_data['alchemists__header-info-block-color'] ) && !empty( $alchemists_data['alchemists__header-info-block-color'] ) ) {
		$vars['header-info-block-color'] = $alchemists_data['alchemists__header-info-block-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-block-cart-sum-color'] ) && !empty( $alchemists_data['alchemists__header-info-block-cart-sum-color'] ) ) {
		$vars['header-info-block-cart-sum-color'] = $alchemists_data['alchemists__header-info-block-cart-sum-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-block-title-color'] ) && !empty( $alchemists_data['alchemists__header-info-block-title-color'] ) ) {
		$vars['header-info-block-title-color'] = $alchemists_data['alchemists__header-info-block-title-color'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-block-cart-sum-color-mobile'] ) && !empty( $alchemists_data['alchemists__header-info-block-cart-sum-color-mobile'] ) ) {
		$vars['header-info-block-cart-sum-color-mobile'] = $alchemists_data['alchemists__header-info-block-cart-sum-color-mobile'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-info-link-color']['regular'] ) ) {
		$vars['header-info-block-link-color'] = $alchemists_data['alchemists__header-info-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-info-link-color']['hover'] ) ) {
		$vars['header-info-block-link-color-hover'] = $alchemists_data['alchemists__header-info-link-color']['hover'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-link-color-mobile']['regular'] ) && !empty( $alchemists_data['alchemists__header-info-link-color-mobile']['regular'] ) ) {
		$vars['header-info-block-link-color-mobile'] = $alchemists_data['alchemists__header-info-link-color-mobile']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-info-link-color-mobile']['hover'] ) && !empty( $alchemists_data['alchemists__header-info-link-color-mobile']['hover'] ) ) {
		$vars['header-info-block-link-color-mobile-hover'] = $alchemists_data['alchemists__header-info-link-color-mobile']['hover'];
	}


	// Header Tertiary Background
	if ( isset( $alchemists_data['alchemists__header-tertiary-bg'] ) && !empty( $alchemists_data['alchemists__header-tertiary-bg'] ) ) {
		$vars['header-tertiary-bg'] = $alchemists_data['alchemists__header-tertiary-bg'];
	}

	// Header Tertiary Heading Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-heading-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-heading-color'] ) ) {
		$vars['nav-secondary-heading'] = $alchemists_data['alchemists__header-tertiary-heading-color'];
	}

	// Header Tertiary Toggle Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-toggle-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-toggle-color'] ) ) {
		$vars['nav-secondary-mobile-toggle'] = $alchemists_data['alchemists__header-tertiary-toggle-color'];
	}

	// Header Tertiary Links Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-nav-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-tertiary-nav-link-color']['regular'] ) ) {
		$vars['nav-secondary-font-color'] = $alchemists_data['alchemists__header-tertiary-nav-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-tertiary-nav-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-tertiary-nav-link-color']['hover'] ) ) {
		$vars['nav-secondary-font-color-hover'] = $alchemists_data['alchemists__header-tertiary-nav-link-color']['hover'];
	}

	// Header Tertiary Link Border Height
	if ( isset( $alchemists_data['alchemists__header-tertiary-border-height']['height'] ) && !empty( $alchemists_data['alchemists__header-tertiary-border-height']['height'] ) ) {
		$vars['nav-secondary-active-border-height'] = $alchemists_data['alchemists__header-tertiary-border-height']['height'];
	}

	// Header Tertiary Link Border Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-border-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-border-color'] ) ) {
		$vars['nav-secondary-active-border-color'] = $alchemists_data['alchemists__header-tertiary-border-color'];
	}

	// Header Tertiary Link Background Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-background-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-background-color'] ) ) {
		$vars['nav-secondary-active-bg'] = $alchemists_data['alchemists__header-tertiary-background-color'];
	}

	// Header Tertiary Submenu Background Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-submenu-bg'] ) && !empty( $alchemists_data['alchemists__header-tertiary-submenu-bg'] ) ) {
		$vars['nav-secondary-sub-bg'] = $alchemists_data['alchemists__header-tertiary-submenu-bg'];
	}

	// Header Tertiary Submenu Border Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-submenu-border-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-submenu-border-color'] ) ) {
		$vars['nav-secondary-sub-border-color'] = $alchemists_data['alchemists__header-tertiary-submenu-border-color'];
	}

	// Header Submenu Link Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-submenu-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-tertiary-submenu-link-color']['regular'] ) ) {
		$vars['nav-secondary-sub-link-color'] = $alchemists_data['alchemists__header-tertiary-submenu-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-tertiary-submenu-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-tertiary-submenu-link-color']['hover'] ) ) {
		$vars['nav-secondary-sub-hover-link-color'] = $alchemists_data['alchemists__header-tertiary-submenu-link-color']['hover'];
	}

	// Mobile Nav Links Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-mobile-link-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-mobile-link-color'] ) ) {
		$vars['nav-secondary-mobile-color'] = $alchemists_data['alchemists__header-tertiary-mobile-link-color'];
	}

	// Mobile Nav Submenu Background Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-mobile-sub-bg'] ) && !empty( $alchemists_data['alchemists__header-tertiary-mobile-sub-bg'] ) ) {
		$vars['nav-secondary-mobile-sub-bg'] = $alchemists_data['alchemists__header-tertiary-mobile-sub-bg'];
	}

	// Mobile Nav Submenu Background Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-mobile-border'] ) && !empty( $alchemists_data['alchemists__header-tertiary-mobile-border'] ) ) {
		$vars['nav-secondary-mobile-border'] = $alchemists_data['alchemists__header-tertiary-mobile-border'];
	}

	// Mobile Nav Submenu Links Color
	if ( isset( $alchemists_data['alchemists__header-tertiary-mobile-sub-link-color'] ) && !empty( $alchemists_data['alchemists__header-tertiary-mobile-sub-link-color'] ) ) {
		$vars['nav-secondary-mobile-sub-color'] = $alchemists_data['alchemists__header-tertiary-mobile-sub-link-color'];
	}


	// Search Form
	// background color - desktop
	if ( isset( $alchemists_data['alchemists__header-search-form-desktop-bg']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-desktop-bg']['regular'] ) ) {
		$vars['header-search-input-bg'] = $alchemists_data['alchemists__header-search-form-desktop-bg']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-desktop-bg']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-desktop-bg']['active'] ) ) {
		$vars['header-search-input-bg-focus'] = $alchemists_data['alchemists__header-search-form-desktop-bg']['active'];
	}
	// border color - desktop
	if ( isset( $alchemists_data['alchemists__header-search-form-desktop-border']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-desktop-border']['regular'] ) ) {
		$vars['header-search-input-border'] = $alchemists_data['alchemists__header-search-form-desktop-border']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-desktop-border']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-desktop-border']['active'] ) ) {
		$vars['header-search-input-border-focus'] = $alchemists_data['alchemists__header-search-form-desktop-border']['active'];
	}
	// text color - desktop
	if ( isset( $alchemists_data['alchemists__header-search-form-desktop-txt']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-desktop-txt']['regular'] ) ) {
		$vars['header-search-input-txt'] = $alchemists_data['alchemists__header-search-form-desktop-txt']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-desktop-txt']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-desktop-txt']['active'] ) ) {
		$vars['header-search-input-txt-focus'] = $alchemists_data['alchemists__header-search-form-desktop-txt']['active'];
	}
	// submit icon color - desktop
	if ( isset( $alchemists_data['alchemists__header-search-form-submit-icon-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-submit-icon-color']['regular'] ) ) {
		$vars['header-search-icon'] = $alchemists_data['alchemists__header-search-form-submit-icon-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-submit-icon-color']['hover'] ) && !empty( $alchemists_data['alchemists__header-search-form-submit-icon-color']['hover'] ) ) {
		$vars['header-search-icon-hover'] = $alchemists_data['alchemists__header-search-form-submit-icon-color']['hover'];
	}
	// background color - mobile
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-bg']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-bg']['regular'] ) ) {
		$vars['header-search-input-bg-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-bg']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-bg']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-bg']['active'] ) ) {
		$vars['header-search-input-bg-focus-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-bg']['active'];
	}
	// border color - mobile
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-border']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-border']['regular'] ) ) {
		$vars['header-search-input-border-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-border']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-border']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-border']['active'] ) ) {
		$vars['header-search-input-border-focus-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-border']['active'];
	}
	// text color - mobile
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-txt']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-txt']['regular'] ) ) {
		$vars['header-search-input-txt-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-txt']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-txt']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-txt']['active'] ) ) {
		$vars['header-search-input-txt-mobile-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-txt']['active'];
	}
	// submit icon color - mobile
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-submit-icon-color']['regular'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-submit-icon-color']['regular'] ) ) {
		$vars['header-search-icon-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-submit-icon-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-submit-icon-color']['active'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-submit-icon-color']['active'] ) ) {
		$vars['header-search-icon-mobile-active'] = $alchemists_data['alchemists__header-search-form-mobile-submit-icon-color']['active'];
	}
	// submit trigger icon - color
	if ( isset( $alchemists_data['alchemists__header-search-form-mobile-submit-trigger-icon-color'] ) && !empty( $alchemists_data['alchemists__header-search-form-mobile-submit-trigger-icon-color'] ) ) {
		$vars['header-search-icon-trigger-mobile'] = $alchemists_data['alchemists__header-search-form-mobile-submit-trigger-icon-color'];
	}


	// Content Filter Colors
	if ( isset( $alchemists_data['alchemists__content-content-filter']['regular'] ) && !empty( $alchemists_data['alchemists__content-content-filter']['regular'] ) ) {
		$vars['content-filter-color'] = $alchemists_data['alchemists__content-content-filter']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__content-content-filter']['hover'] ) && !empty( $alchemists_data['alchemists__content-content-filter']['hover'] ) ) {
		$vars['content-filter-color-hover'] = $alchemists_data['alchemists__content-content-filter']['hover'];
	}
	if ( isset( $alchemists_data['alchemists__content-content-filter']['active'] ) && !empty( $alchemists_data['alchemists__content-content-filter']['active'] ) ) {
		$vars['content-filter-color-active'] = $alchemists_data['alchemists__content-content-filter']['active'];
	}


	// Blog Categories
	if ( isset( $alchemists_data['alchemists__blog-cat-group-1'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-1'] ) ) {
		$vars['post-category-1'] = $alchemists_data['alchemists__blog-cat-group-1'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-2'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-2'] ) ) {
		$vars['post-category-2'] = $alchemists_data['alchemists__blog-cat-group-2'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-3'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-3'] ) ) {
		$vars['post-category-3'] = $alchemists_data['alchemists__blog-cat-group-3'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-4'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-4'] ) ) {
		$vars['post-category-4'] = $alchemists_data['alchemists__blog-cat-group-4'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-5'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-5'] ) ) {
		$vars['post-category-5'] = $alchemists_data['alchemists__blog-cat-group-5'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-6'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-6'] ) ) {
		$vars['post-category-6'] = $alchemists_data['alchemists__blog-cat-group-6'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-7'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-7'] ) ) {
		$vars['post-category-7'] = $alchemists_data['alchemists__blog-cat-group-7'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-8'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-8'] ) ) {
		$vars['post-category-8'] = $alchemists_data['alchemists__blog-cat-group-8'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-9'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-9'] ) ) {
		$vars['post-category-9'] = $alchemists_data['alchemists__blog-cat-group-9'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-10'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-10'] ) ) {
		$vars['post-category-10'] = $alchemists_data['alchemists__blog-cat-group-10'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-11'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-11'] ) ) {
		$vars['post-category-11'] = $alchemists_data['alchemists__blog-cat-group-11'];
	}
	if ( isset( $alchemists_data['alchemists__blog-cat-group-12'] ) && !empty( $alchemists_data['alchemists__blog-cat-group-12'] ) ) {
		$vars['post-category-12'] = $alchemists_data['alchemists__blog-cat-group-12'];
	}


	// Footer Background
	if ( isset( $alchemists_data['alchemists__footer-widgets-bg'] ) && !empty( $alchemists_data['alchemists__footer-widgets-bg'] ) ) {
		$is_footer_bg_img   = isset( $alchemists_data['alchemists__footer-widgets-bg']['background-image'] ) && !empty( $alchemists_data['alchemists__footer-widgets-bg']['background-image'] ) ? true : false;
		$is_footer_bg_color = isset( $alchemists_data['alchemists__footer-widgets-bg']['background-color'] ) && !empty( $alchemists_data['alchemists__footer-widgets-bg']['background-color'] ) ? true : false;
		
		if ( $is_footer_bg_img && $is_footer_bg_color ) {
			$vars['footer-widgets-bg'] = 'url(' . $alchemists_data['alchemists__footer-widgets-bg']['background-image'] . ') ' . $alchemists_data['alchemists__footer-widgets-bg']['background-color'];
		} elseif ( $is_footer_bg_img && ! $is_footer_bg_color ) {
			$vars['footer-widgets-bg'] = 'url(' . $alchemists_data['alchemists__footer-widgets-bg']['background-image'] . ')';
		} elseif ( ! $is_footer_bg_img && $is_footer_bg_color ) {
			$vars['footer-widgets-bg'] = $alchemists_data['alchemists__footer-widgets-bg']['background-color'];
		} else {
			if ( alchemists_sp_preset('esports') ) {
				$vars['footer-widgets-bg'] = '#3b2f4c url("' . get_template_directory_uri() . '/assets/images/esports/samples/footer-bg.jpg") no-repeat center top/cover';
			}
		}
	} else {
		if ( alchemists_sp_preset('esports') ) {
			$vars['footer-widgets-bg'] = '#3b2f4c url("' . get_template_directory_uri() . '/assets/images/esports/samples/footer-bg.jpg") no-repeat center top/cover';
		}
	}

	if ( isset( $alchemists_data['alchemists__footer-widgets--overlay-color'] ) && !empty( $alchemists_data['alchemists__footer-widgets--overlay-color'] ) ) {
		if ( isset( $alchemists_data['alchemists__footer-widgets--overlay-color-customize']['color'] ) && !empty( $alchemists_data['alchemists__footer-widgets--overlay-color-customize']['color'] ) ) {
			$vars['footer-widgets-overlay'] = $alchemists_data['alchemists__footer-widgets--overlay-color-customize']['color'];
		}
		if ( isset( $alchemists_data['alchemists__footer-widgets--overlay-color-customize']['alpha'] ) && !empty( $alchemists_data['alchemists__footer-widgets--overlay-color-customize']['alpha'] ) ) {
			$vars['footer-widgets-overlay-opacity'] = $alchemists_data['alchemists__footer-widgets--overlay-color-customize']['alpha'];
		}
	}

	if ( isset( $alchemists_data['alchemists__footer-secondary-bg'] ) && !empty( $alchemists_data['alchemists__footer-secondary-bg'] ) ) {
		$vars['footer-secondary-bg'] = $alchemists_data['alchemists__footer-secondary-bg'];
	}
	if ( isset( $alchemists_data['alchemists__footer-info-bg'] ) && !empty( $alchemists_data['alchemists__footer-info-bg'] ) ) {
		$vars['footer-info-bg'] = $alchemists_data['alchemists__footer-info-bg'];
	}
	if ( isset( $alchemists_data['alchemists__footer-side-decoration-bg'] ) && !empty( $alchemists_data['alchemists__footer-side-decoration-bg'] ) ) {
		$vars['footer-secondary-side-bg'] = $alchemists_data['alchemists__footer-side-decoration-bg'];
	}
	if ( isset( $alchemists_data['alchemists__footer-secondary-border-color'] ) && !empty( $alchemists_data['alchemists__footer-secondary-border-color'] ) ) {
		$vars['footer-copyright-border-color'] = $alchemists_data['alchemists__footer-secondary-border-color'];
	}
	if ( isset( $alchemists_data['alchemists__footer-widgets-list-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__footer-widgets-list-link-color']['regular'] ) ) {
		$vars['footer-widget-link-color'] = $alchemists_data['alchemists__footer-widgets-list-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__footer-widgets-list-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__footer-widgets-list-link-color']['hover'] ) ) {
		$vars['footer-widget-link-color-hover'] = $alchemists_data['alchemists__footer-widgets-list-link-color']['hover'];
	}

	if ( isset( $alchemists_data['alchemists__footer-contact-info-social-link-color']['regular'] ) && !empty( $alchemists_data['alchemists__footer-contact-info-social-link-color']['regular'] ) ) {
		$vars['footer-contact-info-social-link-color'] = $alchemists_data['alchemists__footer-contact-info-social-link-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__footer-contact-info-social-link-color']['hover'] ) && !empty( $alchemists_data['alchemists__footer-contact-info-social-link-color']['hover'] ) ) {
		$vars['footer-contact-info-social-link-color-hover'] = $alchemists_data['alchemists__footer-contact-info-social-link-color']['hover'];
	}

	if ( isset( $alchemists_data['alchemists__footer-contact-info-social-icon-color']['regular'] ) && !empty( $alchemists_data['alchemists__footer-contact-info-social-icon-color']['regular'] ) ) {
		$vars['footer-contact-info-social-icon-color'] = $alchemists_data['alchemists__footer-contact-info-social-icon-color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__footer-contact-info-social-icon-color']['hover'] ) && !empty( $alchemists_data['alchemists__footer-contact-info-social-icon-color']['hover'] ) ) {
		$vars['footer-contact-info-social-icon-color-hover'] = $alchemists_data['alchemists__footer-contact-info-social-icon-color']['hover'];
	}
	
	
	// Typography

	// Body
	if ( $alchemists_data['alchemists__custom_body_font'] ) {
		if ( isset( $alchemists_data['alchemists__typography-body']['font-family'] ) && !empty( $alchemists_data['alchemists__typography-body']['font-family'] ) ) {
			$vars['font-family-base'] = $alchemists_data['alchemists__typography-body']['font-family'];
		}
		if ( isset( $alchemists_data['alchemists__typography-body']['font-size'] ) && !empty( $alchemists_data['alchemists__typography-body']['font-size'] ) ) {
			$vars['base-font-size'] = $alchemists_data['alchemists__typography-body']['font-size'];
		}
		if ( isset( $alchemists_data['alchemists__typography-body']['line-height'] ) && !empty( $alchemists_data['alchemists__typography-body']['line-height'] ) ) {
			$vars['base-line-height'] = $alchemists_data['alchemists__typography-body']['line-height'];
		}
		if ( isset( $alchemists_data['alchemists__typography-body']['font-weight'] ) && !empty( $alchemists_data['alchemists__typography-body']['font-weight'] ) ) {
			$vars['body-font-weight'] = $alchemists_data['alchemists__typography-body']['font-weight'];
		}
		if ( isset( $alchemists_data['alchemists__typography-body']['color'] ) && !empty( $alchemists_data['alchemists__typography-body']['color'] ) ) {
			$vars['body-font-color'] = $alchemists_data['alchemists__typography-body']['color'];
		}
	}

	// Heading Fonts
	if ( $alchemists_data['alchemists__custom_heading_font'] ) {
		// Font Family Accent
		if ( isset( $alchemists_data['font-family-accent']['font-family'] ) && !empty( $alchemists_data['font-family-accent']['font-family'] ) ) {
			$vars['font-family-accent'] = $alchemists_data['font-family-accent']['font-family'];
		}
		// Headings
		if ( isset( $alchemists_data['headings-typography']['font-family'] ) && !empty( $alchemists_data['headings-typography']['font-family'] ) ) {
			$vars['headings-font-family'] = $alchemists_data['headings-typography']['font-family'];
		}
		if ( isset( $alchemists_data['headings-typography']['color'] ) && !empty( $alchemists_data['headings-typography']['color'] ) ) {
			$vars['headings-color'] = $alchemists_data['headings-typography']['color'];
		}
	}

	// Heading Links
	if ( isset( $alchemists_data['alchemists__custom_heading_link_color']['regular'] ) && !empty( $alchemists_data['alchemists__custom_heading_link_color']['regular'] ) ) {
		$vars['post-title-color'] = $alchemists_data['alchemists__custom_heading_link_color']['regular'];
	}
	if ( isset( $alchemists_data['alchemists__custom_heading_link_color']['hover'] ) && !empty( $alchemists_data['alchemists__custom_heading_link_color']['hover'] ) ) {
		$vars['post-title-color-hover'] = $alchemists_data['alchemists__custom_heading_link_color']['hover'];
	}

	// Navigation
	if ( $alchemists_data['alchemists__custom_nav-font'] ) {

		if ( isset( $alchemists_data['alchemists__nav-font']['font-family'] ) && !empty( $alchemists_data['alchemists__nav-font']['font-family'] ) ) {
			$vars['nav-font-family'] = $alchemists_data['alchemists__nav-font']['font-family'];
		}
		if ( isset( $alchemists_data['alchemists__nav-font']['text-transform'] ) && !empty( $alchemists_data['alchemists__nav-font']['text-transform'] ) ) {
			$vars['nav-text-transform'] = $alchemists_data['alchemists__nav-font']['text-transform'];
		}
		if ( isset( $alchemists_data['alchemists__nav-font']['font-weight'] ) && !empty( $alchemists_data['alchemists__nav-font']['font-weight'] ) ) {
			$vars['nav-font-weight'] = $alchemists_data['alchemists__nav-font']['font-weight'];
		}
		$vars['nav-font-style'] = ( isset( $alchemists_data['alchemists__nav-font']['font-style'] ) && !empty( $alchemists_data['alchemists__nav-font']['font-style'] ) ) ? $alchemists_data['alchemists__nav-font']['font-style'] : 'normal';
		if ( isset( $alchemists_data['alchemists__nav-font']['font-size'] ) && !empty( $alchemists_data['alchemists__nav-font']['font-size'] ) ) {
			$vars['nav-font-size'] = $alchemists_data['alchemists__nav-font']['font-size'];
		}
		if ( isset( $alchemists_data['alchemists__nav-font-sub']['font-family'] ) && !empty( $alchemists_data['alchemists__nav-font-sub']['font-family'] ) ) {
			$vars['nav-sub-font-family'] = $alchemists_data['alchemists__nav-font-sub']['font-family'];
		}
		if ( isset( $alchemists_data['alchemists__nav-font-sub']['text-transform'] ) && !empty( $alchemists_data['alchemists__nav-font-sub']['text-transform'] ) ) {
			$vars['nav-sub-text-transform'] = $alchemists_data['alchemists__nav-font-sub']['text-transform'];
		}
		if ( isset( $alchemists_data['alchemists__nav-font-sub']['font-weight'] ) && !empty( $alchemists_data['alchemists__nav-font-sub']['font-weight'] ) ) {
			$vars['nav-sub-font-weight'] = $alchemists_data['alchemists__nav-font-sub']['font-weight'];
		}

		$vars['nav-sub-font-style'] = ( isset( $alchemists_data['alchemists__nav-font-sub']['font-style'] ) && !empty( $alchemists_data['alchemists__nav-font-sub']['font-style'] ) ) ? $alchemists_data['alchemists__nav-font-sub']['font-style'] : 'normal';

		$vars['nav-sub-font-size'] = ( isset( $alchemists_data['alchemists__nav-font-sub']['font-size'] ) && !empty( $alchemists_data['alchemists__nav-font-sub']['font-size'] ) ) ? $alchemists_data['alchemists__nav-font-sub']['font-size'] : '11px';
	}


	// Secondary Navigation
	$alchemists__custom_nav_secondary_font = ( isset( $alchemists_data['alchemists__custom_nav-secondary-font'] ) && !empty( $alchemists_data['alchemists__custom_nav-secondary-font'] ) ) ? $alchemists_data['alchemists__custom_nav-secondary-font'] : 0;
		
	if ( $alchemists__custom_nav_secondary_font ) {
		if ( isset( $alchemists_data['alchemists__nav-secondary-font']['font-family'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font']['font-family'] ) ) {
			$vars['nav-secondary-font-family'] = $alchemists_data['alchemists__nav-secondary-font']['font-family'];
		}
		if ( isset( $alchemists_data['alchemists__nav-secondary-font']['text-transform'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font']['text-transform'] ) ) {
			$vars['nav-secondary-text-transform'] = $alchemists_data['alchemists__nav-secondary-font']['text-transform'];
		}
		if ( isset( $alchemists_data['alchemists__nav-secondary-font']['font-weight'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font']['font-weight'] ) ) {
			$vars['nav-secondary-font-weight'] = $alchemists_data['alchemists__nav-secondary-font']['font-weight'];
		}
		$vars['nav-secondary-font-style'] = ( isset( $alchemists_data['alchemists__nav-secondary-font']['font-style'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font']['font-style'] ) ) ? $alchemists_data['alchemists__nav-secondary-font']['font-style'] : 'normal';

		if ( isset( $alchemists_data['alchemists__nav-secondary-font']['font-size'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font']['font-size'] ) ) {
			$vars['nav-secondary-font-size'] = $alchemists_data['alchemists__nav-secondary-font']['font-size'];
		}
		if ( isset( $alchemists_data['alchemists__nav-secondary-font-sub']['font-family'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font-sub']['font-family'] ) ) {
			$vars['nav-secondary-sub-font-family'] = $alchemists_data['alchemists__nav-secondary-font-sub']['font-family'];
		}
		if ( isset( $alchemists_data['alchemists__nav-secondary-font-sub']['text-transform'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font-sub']['text-transform'] ) ) {
			$vars['nav-secondary-sub-text-transform'] = $alchemists_data['alchemists__nav-secondary-font-sub']['text-transform'];
		}
		if ( isset( $alchemists_data['alchemists__nav-secondary-font-sub']['font-weight'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font-sub']['font-weight'] ) ) {
			$vars['nav-secondary-sub-font-weight'] = $alchemists_data['alchemists__nav-secondary-font-sub']['font-weight'];
		}
		$vars['nav-secondary-sub-font-style'] = ( isset( $alchemists_data['alchemists__nav-secondary-font-sub']['font-style'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font-sub']['font-style'] ) ) ? $alchemists_data['alchemists__nav-secondary-font-sub']['font-style'] : 'normal';
		if ( isset( $alchemists_data['alchemists__nav-secondary-font-sub']['font-size'] ) && !empty( $alchemists_data['alchemists__nav-secondary-font-sub']['font-size'] ) ) {
			$vars['nav-secondary-sub-font-size'] = $alchemists_data['alchemists__nav-secondary-font-sub']['font-size'];
		}
	}


	// Preloader
	if ( isset( $alchemists_data['alchemists__opt-preloader-bg'] ) && !empty( $alchemists_data['alchemists__opt-preloader-bg'] ) ) {
		$vars['preloader-bg'] = $alchemists_data['alchemists__opt-preloader-bg'];
	}

	if ( isset( $alchemists_data['alchemists__opt-preloader-size']['width'] ) && !empty( $alchemists_data['alchemists__opt-preloader-size']['width'] ) ) {
		$vars['preloader-size'] = $alchemists_data['alchemists__opt-preloader-size']['width'];
	}

	if ( isset( $alchemists_data['alchemists__opt-preloader-color'] ) && !empty( $alchemists_data['alchemists__opt-preloader-color'] ) ) {
		$vars['preloader-color'] = $alchemists_data['alchemists__opt-preloader-color'];
	}

	if ( isset( $alchemists_data['alchemists__opt-preloader-color-secondary'] ) && !empty( $alchemists_data['alchemists__opt-preloader-color-secondary'] ) ) {
		$vars['preloader-color-secondary'] = $alchemists_data['alchemists__opt-preloader-color-secondary'];
	}

	if ( isset( $alchemists_data['alchemists__opt-preloader-spin-duration'] ) && !empty( $alchemists_data['alchemists__opt-preloader-spin-duration'] ) ) {
		$vars['preloader-spin-duration'] = $alchemists_data['alchemists__opt-preloader-spin-duration'] . 's';
	}

	return $vars;
}
