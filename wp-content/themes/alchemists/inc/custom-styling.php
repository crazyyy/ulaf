<?php
/**
 * Output Custom Styling from Redux Theme Options
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.6.0
 */


function alchemists_custom_styling() {

	$alchemists_data = get_option( 'alchemists_data' );
	$output = '';

	/**
	 * Logo Width
	 */
	$logo_width  = isset( $alchemists_data['alchemists__opt-logo-width']['width'] ) && ! empty( $alchemists_data['alchemists__opt-logo-width']['width'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-width']['width'] ) : 'px';

	if ( $logo_width != 'px' ) {
		$output    .= '.header-logo__img {max-width:' . $logo_width . '; width:' . $logo_width . ';}';
	}

	/**
	 * Logo Width on Mobile devices
	 */
	$logo_width_mobile  = isset( $alchemists_data['alchemists__opt-logo-width-mobile']['width'] ) && ! empty( $alchemists_data['alchemists__opt-logo-width-mobile']['width'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-width-mobile']['width'] ) : 'px';
	if ( $logo_width_mobile != 'px' ) {
		$output    .= '@media (max-width: 991px) {';
			$output    .= '.header-mobile__logo-img {max-width:' . $logo_width_mobile . '; width:' . $logo_width_mobile . ';}';
		$output    .= '}';
	}


	/**
	 * Logo Position Adjustments
	 */
	$logo_position_on  = isset( $alchemists_data['alchemists__opt-logo-position'] ) ? $alchemists_data['alchemists__opt-logo-position'] : 0;
	if ( $logo_position_on ) {

		$logo_position_desktop_ver = ! empty( $alchemists_data['alchemists__opt-logo-position-desktop']['bottom'] ) ? $alchemists_data['alchemists__opt-logo-position-desktop']['bottom'] : 0;
		$logo_position_desktop_hor = ! empty( $alchemists_data['alchemists__opt-logo-position-desktop']['right'] ) ? $alchemists_data['alchemists__opt-logo-position-desktop']['right'] : 0;

		$logo_position_mobile_ver = ! empty( $alchemists_data['alchemists__opt-logo-position-mobile']['bottom'] ) ? $alchemists_data['alchemists__opt-logo-position-mobile']['bottom'] : 0;
		$logo_position_mobile_hor = ! empty( $alchemists_data['alchemists__opt-logo-position-mobile']['right'] ) ? $alchemists_data['alchemists__opt-logo-position-mobile']['right'] : 0;

		$output    .= '@media (min-width: 992px) {';
			$output    .= '.header .header-logo { ';
				$output    .= '-webkit-transform: translate(' . $logo_position_desktop_hor . ', ' . $logo_position_desktop_ver . '); transform: translate(' . $logo_position_desktop_hor . ', ' . $logo_position_desktop_ver . ');';
			$output    .= '}';
		$output    .= '}';

		$output    .= '@media (max-width: 991px) {';
			$output    .= '.header-mobile__logo {';
				$output    .= 'margin-left: ' . $logo_position_mobile_hor . '; margin-top: ' . $logo_position_mobile_ver .  ';';
			$output    .= '}';
		$output    .= '}';
	}


	/**
	 * Header Paddings
	 */
	$page_heading_padding_custom  = isset( $alchemists_data['alchemists__opt-page-title-spacing-on'] ) ? $alchemists_data['alchemists__opt-page-title-spacing-on'] : 0;
	if ( $page_heading_padding_custom == 1) {

		$page_heading_padding_desktop = $alchemists_data['alchemists__opt-page-title-spacing-desktop'];
		$page_heading_padding_tablet  = $alchemists_data['alchemists__opt-page-title-spacing-tablet'];
		$page_heading_padding_mobile  = $alchemists_data['alchemists__opt-page-title-spacing-mobile'];

		$output    .= '.page-heading { padding-top:' . $page_heading_padding_mobile['padding-top'] . '; padding-bottom:' . $page_heading_padding_mobile['padding-bottom'] . '}';

		$output    .= '@media (min-width: 768px) {';
			$output    .= '.page-heading { padding-top:' . $page_heading_padding_tablet['padding-top'] . '; padding-bottom:' . $page_heading_padding_tablet['padding-bottom'] . '}';
		$output    .= '}';

		$output    .= '@media (min-width: 992px) {';
			$output    .= '.page-heading { padding-top:' . $page_heading_padding_desktop['padding-top'] . '; padding-bottom:' . $page_heading_padding_desktop['padding-bottom'] . '}';
		$output    .= '}';
	}


	/**
	 * Mobile Header
	 */
	$mobile_header_fixed  = isset( $alchemists_data['alchemists__mobile-header-fixed'] ) ? $alchemists_data['alchemists__mobile-header-fixed'] : 0;
	if ( $mobile_header_fixed == 1 ) {
		$mobile_header_height  = isset( $alchemists_data['alchemists__mobile-nav-height'] ) ? $alchemists_data['alchemists__mobile-nav-height'] . 'px' : 0;
		$output   .= '@media (max-width: 991px) {';
			$output   .= '.site-wrapper { padding-top: ' . $mobile_header_height . '; }';
			$output   .= '.header-mobile { position: fixed; }';
		$output   .= '}';
	}


	/**
	 * Nav Primary Item Paddings
	 */

	$nav_item_padding_custom  = isset( $alchemists_data['alchemists__header-primary-nav-padding'] ) ? $alchemists_data['alchemists__header-primary-nav-padding'] : 0;
	if ( $nav_item_padding_custom == 1) {

		$nav_item_padding_desktop = $alchemists_data['alchemists__header-primary-nav-padding-desktop'];
		$nav_item_padding_tablet  = $alchemists_data['alchemists__header-primary-nav-padding-tablet'];

		$output    .= '.main-nav__list > li > a { padding-left:' . $nav_item_padding_desktop['padding-left'] . '; padding-right:' . $nav_item_padding_desktop['padding-right'] . '}';

		$output    .= '@media (max-width: 1199px) and (min-width: 992px) {';
			$output    .= '.main-nav__list > li > a { padding-left:' . $nav_item_padding_tablet['padding-left'] . '; padding-right:' . $nav_item_padding_tablet['padding-right'] . '}';
		$output    .= '}';
	}


	/**
	 * Search Form Width
	 */
	$search_form_width_custom  = isset( $alchemists_data['alchemists__header-search-form-width'] ) ? $alchemists_data['alchemists__header-search-form-width'] : 0;
	if ( $search_form_width_custom == 1) {

		$search_form_width_desktop = $alchemists_data['alchemists__header-search-form-width-desktop'];
		$search_form_width_tablet  = $alchemists_data['alchemists__header-search-form-width-tablet'];

		$output    .= '@media (min-width: 992px) {';
			$output    .= '.header-search-form { width:' . $search_form_width_desktop . 'px ; }';
		$output    .= '}';

		$output    .= '@media (max-width: 1199px) and (min-width: 992px) {';
			$output    .= '.header-search-form { width:' . $search_form_width_tablet . 'px; }';
		$output    .= '}';
	}


	/**
	 * Pushy Panel Logo Width
	 */
	$logo_pushy_width  = isset( $alchemists_data['alchemists__opt-logo-pushy-width']['width'] ) && ! empty( $alchemists_data['alchemists__opt-logo-pushy-width']['width'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-pushy-width']['width'] ) : 'px';

	if ( $logo_pushy_width != 'px' ) {
		$output    .= '.pushy-panel__logo-img {max-width:' . $logo_pushy_width . '; width:' . $logo_pushy_width . ';}';
	}

	/**
	 * Page Heading - Custom Opacity
	 */
	$page_heading_title_opacity = isset( $alchemists_data['alchemists__opt-paget-title-overlay-opacity-on'] ) ? $alchemists_data['alchemists__opt-paget-title-overlay-opacity-on'] : 0;

	if ( $page_heading_title_opacity ) {
		$output .= '.page-heading::before { opacity: ' . $alchemists_data['alchemists__opt-paget-title-overlay-opacity'] / 100 . '}';
	}


	/**
	 * Breadcrumbs Separator
	 */
	$breadcrumbs = $alchemists_data['alchemists__opt-page-title-breadcrumbs'];
	$breadcrumbs_custom_color = $alchemists_data['alchemists__custom_breadcrumbs'];

	if ( $breadcrumbs == 1 && $breadcrumbs_custom_color == 1 ) {
		$output .= '.page-heading ul.trail-items>li::after { color: ' . $alchemists_data['alchemists__opt-page-title-breadcrumbs-sep-color'] . '}';
	}


	/**
	 * Hero Unit height (mobile)
	 */

	if ( isset( $alchemists_data['alchemists__opt-page-heading-hero-height-sm']) and is_array( $alchemists_data['alchemists__opt-page-heading-hero-height-sm'] )) {
		$hero_height_sm = $alchemists_data['alchemists__opt-page-heading-hero-height-sm']['height'];

		// var_dump($hero_height_sm);

		if ( !empty( $hero_height_sm ) and $hero_height_sm != 'px' ) {
			$output    .= '.hero-unit__container { height:' . $hero_height_sm . ';}';
		}
	}


	/**
	 * Hero Unit height
	 */

	if ( isset( $alchemists_data['alchemists__opt-page-heading-hero-height']) and is_array( $alchemists_data['alchemists__opt-page-heading-hero-height'] )) {
		$hero_height = $alchemists_data['alchemists__opt-page-heading-hero-height']['height'];

		if ( !empty( $hero_height ) and $hero_height != 'px' ) {
			$output    .= '@media (min-width: 1200px) {';
				$output    .= '.hero-unit__container { height:' . $hero_height . ';}';
			$output    .= '}';
		}
	}


	/**
	 * Hero Unit - Posts Slider height
	 */
	$hero_posts_slider_height  = isset( $alchemists_data['alchemists__hero-posts-height'] ) ? $alchemists_data['alchemists__hero-posts-height'] : 0;
	if ( $hero_posts_slider_height ) {

		$hero_posts_slider_height_lg  = isset( $alchemists_data['alchemists__hero-posts-height-desktop']['height'] ) && ! empty( $alchemists_data['alchemists__hero-posts-height-desktop']['height'] ) ? $alchemists_data['alchemists__hero-posts-height-desktop']['height'] : 'px';
		$hero_posts_slider_height_md  = isset( $alchemists_data['alchemists__hero-posts-height-tablet-landscape']['height'] ) && ! empty( $alchemists_data['alchemists__hero-posts-height-tablet-landscape']['height'] ) ? $alchemists_data['alchemists__hero-posts-height-tablet-landscape']['height'] : 'px';
		$hero_posts_slider_height_sm  = isset( $alchemists_data['alchemists__hero-posts-height-tablet-portrait']['height'] ) && ! empty( $alchemists_data['alchemists__hero-posts-height-tablet-portrait']['height'] ) ? $alchemists_data['alchemists__hero-posts-height-tablet-portrait']['height'] : 'px';
		$hero_posts_slider_height_xs  = isset( $alchemists_data['alchemists__hero-posts-height-mobile']['height'] ) && ! empty( $alchemists_data['alchemists__hero-posts-height-mobile']['height'] ) ? $alchemists_data['alchemists__hero-posts-height-mobile']['height'] : 'px';

		if ( $hero_posts_slider_height_xs != 'px' ) {
			$output    .= '.hero-slider, .hero-slider .hero-slider__item, .posts--slider-top-news .hero-slider__item { height:' . $hero_posts_slider_height_xs . ';}';
		}

		if ( $hero_posts_slider_height_sm != 'px' ) {
			$output    .= '@media (min-width: 768px) {';
				$output    .= '.hero-slider, .hero-slider .hero-slider__item, .posts--slider-top-news .hero-slider__item { height:' . $hero_posts_slider_height_sm . ';}';
			$output    .= '}';
		}

		if ( $hero_posts_slider_height_md != 'px' ) {
			$output    .= '@media (min-width: 992px) {';
				$output    .= '.hero-slider, .hero-slider .hero-slider__item, .posts--slider-top-news .hero-slider__item { height:' . $hero_posts_slider_height_md . ';}';
			$output    .= '}';
		}

		if ( $hero_posts_slider_height_lg != 'px' ) {
			$output    .= '@media (min-width: 1200px) {';
				$output    .= '.hero-slider, .hero-slider .hero-slider__item, .posts--slider-top-news .hero-slider__item { height:' . $hero_posts_slider_height_lg . ';}';
			$output    .= '}';
		}
	}


	/**
	 * Hero Unit - Posts Slider Overlay color
	 */
	$hero_posts_slider_overlay_color  = isset( $alchemists_data['alchemists__hero-posts-slider--overlay-color'] ) ? $alchemists_data['alchemists__hero-posts-slider--overlay-color'] : 0;
	if ( $hero_posts_slider_overlay_color ) {

		$hero_posts_slider_overlay_color_custom = isset( $alchemists_data['alchemists__hero-posts-slider--overlay-color-customize'] ) ? $alchemists_data['alchemists__hero-posts-slider--overlay-color-customize'] : '';

		if ( $hero_posts_slider_overlay_color_custom ) {
			$output .= '.hero-slider--overlay-on .hero-slider__item::before { opacity: ' . $hero_posts_slider_overlay_color_custom['alpha']. '; background-color:' . $hero_posts_slider_overlay_color_custom['color'] . ';}';
		}
	}


	/**
	 * Content Paddings
	 */
	$content_padding_on  = isset( $alchemists_data['alchemists__opt-content-padding-on'] ) ? $alchemists_data['alchemists__opt-content-padding-on'] : 0;
	if ( $content_padding_on == 1) {

		$content_padding_desktop = $alchemists_data['alchemists__opt-content-padding-desktop'];
		$content_padding_tablet  = $alchemists_data['alchemists__opt-content-padding-tablet'];
		$content_padding_mobile  = $alchemists_data['alchemists__opt-content-padding-mobile'];

		$output    .= '.site-content { padding-top:' . $content_padding_mobile['padding-top'] . '; padding-bottom:' . $content_padding_mobile['padding-bottom'] . '}';

		$output    .= '@media (min-width: 768px) {';
			$output    .= '.site-content { padding-top:' . $content_padding_tablet['padding-top'] . '; padding-bottom:' . $content_padding_tablet['padding-bottom'] . '}';
		$output    .= '}';

		$output    .= '@media (min-width: 992px) {';
			$output    .= '.site-content { padding-top:' . $content_padding_desktop['padding-top'] . '; padding-bottom:' . $content_padding_desktop['padding-bottom'] . '}';
		$output    .= '}';
	}


	/**
	 * Player Heading
	 */
	$player_heading_bg_on = isset( $alchemists_data['alchemists__player-title-custom'] ) ? $alchemists_data['alchemists__player-title-custom'] : 0;
	$player_team_logo_on  = isset( $alchemists_data['alchemists__player-team-logo'] ) ? $alchemists_data['alchemists__player-team-logo'] : 0;

	if ( $player_heading_bg_on == 1 ) {
		$player_heading_bg_custom_img      = $alchemists_data['alchemists__player-title-background']['background-image'];
		$player_heading_bg_overlay_opacity = isset( $alchemists_data['alchemists__player-title-overlay-opacity'] ) ? $alchemists_data['alchemists__player-title-overlay-opacity'] : null;

		if ( empty( $player_heading_bg_custom_img ) ) {
			$output .= '.player-heading { background-image: none; }';
			if ( $player_heading_bg_overlay_opacity ) {
				$output .= '.player-heading::after { opacity: ' . $player_heading_bg_overlay_opacity / 100 . '; }';
			}
		}
	}

	if ( $player_team_logo_on ) {
		$player_team_logo_opacity = $alchemists_data['alchemists__player-team-logo-opacity'];
		$output .= '.player-info__team-logo { opacity: ' . $player_team_logo_opacity / 100 . '; }';
	}


	/**
	 * Mobile Navigation Font Size
	 */
	$custom_nav_font = isset( $alchemists_data['alchemists__custom_nav-font'] ) ? $alchemists_data['alchemists__custom_nav-font'] : 0;

	if ( $custom_nav_font == 1 ) {

		$custom_nav_font_main       = $alchemists_data['alchemists__custom_nav-mobile-font']['font-size'];
		$custom_nav_font_submenu    = $alchemists_data['alchemists__custom_nav-mobile-font-submenu']['font-size'];
		$custom_nav_font_subsubmenu = $alchemists_data['alchemists__custom_nav-mobile-font-subsubmenu']['font-size'];

		$output    .= '@media (max-width: 991px) {';
			if ( ! empty( $custom_nav_font_main ) ) {
				$output    .= '.main-nav .main-nav__list > li > a, .main-nav .main-nav__item--shopping-cart .info-block__heading, .main-nav .main-nav__item--shopping-cart .info-block__cart-sum { font-size:' . $custom_nav_font_main . '; }';
			}
			if ( ! empty( $custom_nav_font_submenu ) ) {
				$output    .= '.no-mega-menu .main-nav__sub-0 li a, .main-nav__list .has-mega-menu .main-nav__sub-0 .main-nav__title { font-size:' . $custom_nav_font_submenu . '; }';
			}
			if ( ! empty( $custom_nav_font_subsubmenu ) ) {
				$output    .= '.main-nav__list .has-mega-menu .main-nav__sub-0 .main-nav__sub-1 > li > a, .main-nav__list .main-nav__sub-0 > li [class*="main-nav__sub-"] li a { font-size:' . $custom_nav_font_subsubmenu . '; }';
			}
		$output    .= '}';
	}


	/**
	 * Mobile Secondary Navigation Font Size
	 */
	$custom_nav_secondary_font = isset( $alchemists_data['alchemists__custom_nav-secondary-font'] ) ? $alchemists_data['alchemists__custom_nav-secondary-font'] : 0;

	if ( $custom_nav_secondary_font == 1 ) {

		// Heading
		$custom_nav_secondary_font_heading_mobile = $alchemists_data['alchemists__custom_nav-secondary-heading-mobile-font']['font-size'];
		if ( ! empty( $custom_nav_secondary_font_heading_mobile ) ) {
			$output    .= '@media (max-width: 991px) {';
				$output    .= '.secondary-nav__heading { font-size:' . $custom_nav_secondary_font_heading_mobile . '; }';
			$output    .= '}';
		}

		$custom_nav_secondary_font_main       = $alchemists_data['alchemists__custom_nav-secondary-mobile-font']['font-size'];
		$custom_nav_secondary_font_submenu    = $alchemists_data['alchemists__custom_nav-secondary-mobile-font-submenu']['font-size'];
		$custom_nav_secondary_font_subsubmenu = $alchemists_data['alchemists__custom_nav-secondary-mobile-font-subsubmenu']['font-size'];

		$output    .= '@media (max-width: 991px) {';
			if ( ! empty( $custom_nav_secondary_font_main ) ) {
				$output    .= '.secondary-nav .secondary-nav__list > li > a { font-size:' . $custom_nav_secondary_font_main . '; }';
			}
			if ( ! empty( $custom_nav_secondary_font_submenu ) ) {
				$output    .= '.secondary-nav__list .no-mega-menu .main-nav__sub-0 li a { font-size:' . $custom_nav_secondary_font_submenu . '; }';
			}
			if ( ! empty( $custom_nav_secondary_font_subsubmenu ) ) {
				$output    .= '.secondary-nav__list .main-nav__sub-0 > li [class*="main-nav__sub-"] li a { font-size:' . $custom_nav_secondary_font_subsubmenu . '; }';
			}
		$output    .= '}';
	}


	/**
	 * Team Colors
	 */
	if ( class_exists( 'SportsPress' ) ) {

		// Single Team
		if ( is_singular( 'sp_team' ) ) {
			$current_team_id       = get_the_ID();
			$team_color_primary    = get_field( 'team_color_primary', $current_team_id );
			$team_color_secondary  = get_field( 'team_color_secondary', $current_team_id );
			$team_color_heading    = get_field( 'team_color_heading', $current_team_id );

			if ( alchemists_sp_preset( 'soccer' ) ) {
				if ( $team_color_primary && $team_color_secondary ) {
					$output .= '.team-roster--card-compact .team-roster__item-holder .team-roster__content-wrapper::before {';
						$output .= 'background-image: linear-gradient(135deg, transparent, transparent 57%, ' . hex2rgba( $team_color_secondary, 0.82). ' 57%, ' . hex2rgba( $team_color_secondary, 0.82). ' 78%, transparent 78%, transparent), linear-gradient(135deg, transparent, transparent 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 57%, transparent 57%, transparent), linear-gradient(135deg, ' . hex2rgba($team_color_primary, 0.82) . ', ' . hex2rgba($team_color_primary, 0.82) . ' 47%, ' . $team_color_secondary . ' 47%, ' . $team_color_secondary . ')';
					$output .= '}';
				} elseif ( $team_color_primary ) {
					$output .= '.team-roster--card-compact .team-roster__item-holder .team-roster__content-wrapper::before {';
						$output .= 'background-image: linear-gradient(135deg, transparent, transparent 57%, #e2ff2f 57%, #e2ff2f 78%, transparent 78%, transparent), linear-gradient(135deg, transparent, transparent 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 57%, transparent 57%, transparent), linear-gradient(135deg, ' . hex2rgba($team_color_primary, 0.82) . ', ' . hex2rgba($team_color_primary, 0.82) . ' 47%, #c2ff1f 47%, #c2ff1f)';
					$output .= '}';
				} elseif ( $team_color_secondary ) {
					$output .= '.team-roster--card-compact .team-roster__item-holder .team-roster__content-wrapper::before {';
						$output .= 'background-image: linear-gradient(135deg, transparent, transparent 57%, ' . hex2rgba( $team_color_secondary, 0.82). ' 57%, ' . hex2rgba( $team_color_secondary, 0.82). ' 78%, transparent 78%, transparent), linear-gradient(135deg, transparent, transparent 33%, rgba(255, 255, 255, 0.15) 33%, rgba(255, 255, 255, 0.15) 57%, transparent 57%, transparent), linear-gradient(135deg, rgba(24, 146, 237, 0.82), rgba(24, 146, 237, 0.82) 47%, ' . $team_color_secondary . ' 47%, ' . $team_color_secondary . ')';
					$output .= '}';
				}
			}

			if ( $team_color_primary ) {
				if ( ! alchemists_sp_preset( 'football' ) ) {
					$output .= '.btn-fab,';
					$output .= '.gallery__item-inner:hover,';
					$output .= '.slick-dots li.slick-active button {';
						$output .= 'background-color: ' . $team_color_primary . ';';
					$output .= '}';
				}

				if ( alchemists_sp_preset( 'soccer' ) ) {
					$output .= '.team-roster--card-slider .btn-primary-inverse,';
					$output .= '.team-roster--card-slider .slick-prev:hover:not(.slick-disabled),';
					$output .= '.team-roster--card-slider .slick-next:hover:not(.slick-disabled),';
					$output .= '.team-roster--card-compact .slick-prev:hover:not(.slick-disabled),';
					$output .= '.team-roster--card-compact .slick-next:hover:not(.slick-disabled),';
					$output .= '.team-roster--card-compact .btn-primary-inverse,';
					$output .= '.widget-player--soccer .widget-player__number {';
						$output .= 'background-color: ' . $team_color_primary . ';';
					$output .= '}';

					$output .= '.team-roster--card .team-roster__player-shape {';
						$output .= 'background-color: ' . hex2rgba( $team_color_primary, 0.8) . ';';
					$output .= '}';

					$output .= '.team-roster--card .team-roster__player-last-name,';
					$output .= '.team-roster--card-compact .team-roster__item-holder .team-roster__player-last-name,';
					$output .= '.widget-player--soccer .widget-player__last-name {';
						$output .= 'color: ' . $team_color_primary . ';';
					$output .= '}';
				}

				if ( alchemists_sp_preset( 'basketball' ) ) {
					$output .= '.template-basketball .widget-player--alt .widget-player__inner,';
					$output .= '.widget-player__footer,';
					$output .= '.widget-player__footer-txt::before {';
						$output .= 'background-color: ' . $team_color_primary . ';';
					$output .= '}';

					$output .= '.template-basketball .widget-player--alt .widget-player__inner {';
						$output .= 'background-image: linear-gradient(to bottom, ' . $team_color_primary . ', ' . $team_color_secondary . ');';
					$output .= '}';

					$output .= '.template-basketball .widget-player--alt .widget-player__stat-number {';
						$output .= 'color: ' . $team_color_primary . ';';
					$output .= '}';
				}

				if ( alchemists_sp_preset( 'football' ) ) {
					$output .= '.widget-player--football .widget-player__inner::before {';
						$output .= 'background-color: ' . $team_color_primary . ';';
					$output .= '}';
				}

				$output .= '.team-roster--grid .btn-fab,';
				$output .= '.team-roster--grid .team-roster__holder:hover .team-roster__member-number,';
				$output .= '.team-roster--grid-sm .btn-fab,';
				$output .= '.team-roster--case-slider .slick-prev:hover,';
				$output .= '.team-roster--case-slider .slick-next:hover {';
					$output .= 'background-color: ' . $team_color_primary . ';';
				$output .= '}';

				$output .= 'table a:not([class]),';
				$output .= '.gallery__icon,';
				$output .= '.team-roster--grid .team-roster__member-last-name,';
				$output .= '.team-roster--slider .team-roster__player-last-name,';
				$output .= '.team-roster--grid-sm .team-roster__member-number {';
					$output .= 'color: ' . $team_color_primary . ';';
				$output .= '}';

				$output .= '.team-roster--slider .team-roster__img-ring-top::before,';
				$output .= '.team-roster--slider .team-roster__img-ring-bottom::before,';
				$output .= '.team-roster--slider .slick-prev:hover,';
				$output .= '.team-roster--slider .slick-next:hover {';
					$output .= 'border-color: ' . $team_color_primary . ';';
				$output .= '}';
			}

			if ( $team_color_secondary ) {
				if ( alchemists_sp_preset( 'football' ) ) {
					$output .= '.btn-fab--clean::before,';
					$output .= '.btn-fab--clean::after,';
					$output .= '.awards--slider .slick-dots li.slick-active button {';
						$output .= 'background-color: ' . $team_color_secondary . ';';
					$output .= '}';

					$output .= '.team-roster--case .team-roster__meta-value--accent,';
					$output .= '.widget-player--football .widget-player__last-name {';
						$output .= 'color: ' . $team_color_secondary . ';';
					$output .= '}';
				}

				if ( alchemists_sp_preset( 'soccer' ) ) {
					$output .= '.team-roster__player-stats .progress__bar,';
					$output .= '.team-roster--card-compact .progress__bar,';
					$output .= '.widget-player--soccer .progress__bar {';
						$output .= 'background-color: ' . $team_color_secondary . ';';
					$output .= '}';
				}
			}

			if ( $team_color_heading ) {
				$output .= '.card__header::before,';
				$output .= '.content-filter__link::before { ';
					$output .= 'background-color: ' . $team_color_heading . ';';
				$output .= '}';

				$output .= '.game-result__score-result--winner::before,';
				$output .= '.widget-results__score-winner:before {';
					$output .= 'border-left-color: ' . $team_color_heading . ';';
				$output .= '}';

				$output .= '.game-result__score-result--loser ~ .game-result__score-result--winner::after,';
				$output .= '.widget-results__score-loser ~ .widget-results__score-winner::after {';
					$output .= 'border-right-color: ' . $team_color_heading . ';';
				$output .= '}';
			}
		}

		// Single Player
		if ( is_singular( 'sp_player' ) ) {
			$player_id = get_the_ID();

			$current_teams = get_post_meta( $player_id, 'sp_current_team' );
			$current_team_id = '';
			if ( ! empty( $current_teams[0] ) ) {
				$current_team_id = $current_teams[0];
			}

			// get Team colors for player
			$team_color_primary    = get_field( 'team_color_primary', $current_team_id );
			$team_color_secondary  = get_field( 'team_color_secondary', $current_team_id );
			$team_color_heading    = get_field( 'team_color_heading', $current_team_id );

			// set custom Primary Team color for various elements
			if ( $team_color_primary ) {

				$player_last_name_color = '';
				if ( alchemists_sp_preset( 'soccer') || alchemists_sp_preset( 'football' ) ) {
					$player_last_name_color = $team_color_secondary;
				} else {
					$player_last_name_color = $team_color_primary;
				}
				$output .= '.player-info__last-name {';
					$output .= 'color: ' . $player_last_name_color . ';';
				$output .= '}';

				$output .= '.album__item-holder--color--primary {';
					$output .= 'background-color: ' . $team_color_primary . ';';
				$output .= '}';

				$output .= 'table a:not([class]),';
				$output .= '.album__item-icon {';
					$output .= 'color: ' . $team_color_primary . ';';
				$output .= '}';
			}

			// set custom Heading Team color for headings and filter
			if ( $team_color_heading ) {
				$output .= '.alc-current-team-id-'. $current_team_id . ' .card__header::before,';
				$output .= '.alc-current-team-id-'. $current_team_id . ' .content-filter__link::before { ';
					$output .= 'background-color: ' . $team_color_heading . ';';
				$output .= '}';
			}
		}
	}


	/**
	 * Body Font - Mobile
	 */
	$body_font_size_custom = isset( $alchemists_data['alchemists__custom_body_font_mobile'] ) ? $alchemists_data['alchemists__custom_body_font_mobile'] : 0;
	if ( $body_font_size_custom == 1 ) {

		$body_font_size_mobile   = isset( $alchemists_data['alchemists__typography-body-mobile']['font-size'] ) && ! empty( $alchemists_data['alchemists__typography-body-mobile']['font-size'] ) ? $alchemists_data['alchemists__typography-body-mobile']['font-size'] : '';
		$body_line_height_mobile = isset( $alchemists_data['alchemists__typography-body-mobile']['line-height'] ) && ! empty( $alchemists_data['alchemists__typography-body-mobile']['line-height'] ) ? $alchemists_data['alchemists__typography-body-mobile']['line-height'] : '';

		if ( $body_font_size_mobile ) {
			$output    .= '@media (max-width: 479px) {';
				$output    .= 'body { font-size:' . $body_font_size_mobile . '; }';
			$output    .= '}';
		}

		if ( $body_line_height_mobile ) {
			$output    .= '@media (max-width: 479px) {';
				$output    .= 'body { line-height:' . $body_line_height_mobile . '; }';
			$output    .= '}';
		}
	}

	/**
	 * Table Typography
	 */
	$table_font_on  = isset( $alchemists_data['alchemists__custom-table-on'] ) && ! empty( $alchemists_data['alchemists__custom-table-on'] ) ? esc_html( $alchemists_data['alchemists__custom-table-on'] ) : false;
	if ( $table_font_on ) {
		$table_font_size   = isset( $alchemists_data['alchemists__custom-table']['font-size'] ) && ! empty( $alchemists_data['alchemists__custom-table']['font-size'] ) ? $alchemists_data['alchemists__custom-table']['font-size'] : '';

		if ( $table_font_size ) {
			$output    .= '@media (min-width: 992px) {';
				$output    .= '.table, table, .table > thead > tr > th, table > thead > tr > td, table > tbody > tr > th, table > tbody > tr > td, table > tfoot > tr > th, table > tfoot > tr > td, .table > thead > tr > th, .table > thead > tr > td, .table > tbody > tr > th, .table > tbody > tr > td, .table > tfoot > tr > th, .table > tfoot > tr > td {';
					$output .= 'font-size:' . $table_font_size . ';';
			$output    .= '}';
		}
	}

	/**
	 * Sponsors
	 */

	// Opacity default
	$sponsors_opacity_default = isset( $alchemists_data['alchemists__footer-sponsors-images-opacity'] ) ? $alchemists_data['alchemists__footer-sponsors-images-opacity'] : false;

	if ( $sponsors_opacity_default ) {
		$output .= '.sponsors__item img { opacity: ' . $sponsors_opacity_default . '; }';
	}

	// Opacity on Hover
	$sponsors_opacity_hover = isset( $alchemists_data['alchemists__footer-sponsors-images-opacity-hover'] ) ? $alchemists_data['alchemists__footer-sponsors-images-opacity-hover'] : false;

	if ( $sponsors_opacity_hover ) {
		$output .= '.sponsors__item img:hover { opacity: ' . $sponsors_opacity_hover . '; }';
	}




	/**
	 * Custom CSS
	 */
	if ( isset( $alchemists_data['alchemists__custom-css'] ) ) {
		$custom_css = $alchemists_data['alchemists__custom-css'];
		if ($custom_css <> '') {
			$output .= $custom_css . "\n";
		}
	}


	/**
	 * Output
	 */
	if ($output <> '') {
		$output = "<!-- Custom CSS--><style type=\"text/css\">\n" . $output . "</style>\n";
		echo !empty( $output ) ? $output : '';
	}
}

add_action( 'wp_head', 'alchemists_custom_styling' );
