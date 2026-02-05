<?php

/**
 * Color schemes
 */

if ( ! function_exists( 'campo_color_schemes' ) ) {
	function campo_color_schemes( $color_scheme_arr ) {

		$theme_color_schemes = array();
		
		$theme_color_schemes[] = 'accent-light;Accent color, Light color;var(--accent-color);var(--light-color)';
		$theme_color_schemes[] = 'accent-dark;Accent color, Dark color;var(--accent-color);var(--dark-color)';
		
		$theme_color_schemes[] = 'light-accent;Light color, Accent color;var(--light-color);var(--accent-color)';
		$theme_color_schemes[] = 'dark-accent;Dark color, Accent color;var(--dark-color);var(--accent-color)';
		
		$theme_color_schemes[] = 'alternate-light;Alternate color, Light color;var(--alternate-color);var(--light-color)';
		$theme_color_schemes[] = 'alternate-dark;Alternate color, Dark color;var(--alternate-color);var(--dark-color)';
		
		$theme_color_schemes[] = 'light-alternate;Light color, Alternate color;var(--light-color);var(--alternate-color)';
		$theme_color_schemes[] = 'dark-alternate;Dark color, Alternate color;var(--dark-color);var(--alternate-color)';
		
		$theme_color_schemes[] = 'light-dark;Light color, Dark color;var(--light-color);var(--dark-color)';
		$theme_color_schemes[] = 'dark-light;Dark color, Light color;var(--dark-color);var(--light-color)';
		
		$theme_color_schemes[] = 'light-transparent;Light color, Transparent;var(--light-color);var(--transparent-color)';
		$theme_color_schemes[] = 'dark-transparent;Dark color, Transparent;var(--dark-color);var(--transparent-color)';

		$theme_color_schemes[] = 'accent-alternate;Accent color, Alternate color;var(--accent-color);var(--alternate-color)';
		$theme_color_schemes[] = 'alternate-accent;Alternate color, Accent color;var(--alternate-color);var(--accent-color)';

		$theme_color_schemes[] = 'beige-dark;Beige color, Dark color;var(--beige-color);var(--dark-color)';
		$theme_color_schemes[] = 'dark-beige;Dark color, Beige color;var(--dark-color);var(--beige-color)';

		$theme_color_schemes[] = 'beige-accent;Beige color, Accent color;var(--beige-color);var(--accent-color)';
		$theme_color_schemes[] = 'accent-beige;Accent color, Beige color;var(--accent-color);var(--beige-color)';

		$theme_color_schemes[] = 'beige-alternate;Beige color, Alternate color;var(--beige-color);var(--alternate-color)';
		$theme_color_schemes[] = 'alternate-beige;Alternate color, Beige color;var(--alternate-color);var(--beige-color)';

		$theme_color_schemes[] = 'light-gray-dark;Light gray color, Dark color;var(--light-gray-color);var(--dark-color)';
		$theme_color_schemes[] = 'dark-light-gray;Dark color, Light gray color;var(--dark-color);var(--light-gray-color)';

		$theme_color_schemes[] = 'light-gray-light;Light gray color, Light color;var(--light-gray-color);var(--light-color)';
		$theme_color_schemes[] = 'light-light-gray;Light color, Light gray color;var(--light-color);var(--light-gray-color)';

		$theme_color_schemes[] = 'dark-very-light-gray;Dark color, Very light gray color;var(--dark-color);var(--very-light-color)';

		$theme_color_schemes[] = 'accent-transparent;Accent color, Transparent;var(--accent-color);var(--transparent-color)';
		$theme_color_schemes[] = 'alternate-transparent;Alternate color, Transparent;var(--alternate-color);var(--transparent-color)';
		$theme_color_schemes[] = 'beige-transparent;Beige color, Transparent;var(--beige-color);var(--transparent-color)';

		$theme_color_schemes[] = 'dark-dark;All dark color;var(--dark-color);var(--dark-color)';
		$theme_color_schemes[] = 'alternate-alternate;All alternate color;var(--alternate-color);var(--alternate-color)';

		$theme_color_schemes[] = 'light-semi-dark;Light color, Semi dark color;var(--light-color);var(--semi-dark-60-color)';
		$theme_color_schemes[] = 'beige-semi-dark;Beige color, Semi dark color;var(--beige-color);var(--semi-dark-60-color)';

		return array_merge( $theme_color_schemes, $color_scheme_arr );
	}
}

add_filter( 'bt_bb_color_scheme_arr', 'campo_color_schemes' );

