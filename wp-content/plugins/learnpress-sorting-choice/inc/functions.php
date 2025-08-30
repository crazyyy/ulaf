<?php
/**
 * LearnPress Sorting Choice Functions
 *
 * Define common functions for both front-end and back-end
 *
 * @author   ThimPress
 * @package  LearnPress/Sorting-Choice/Functions
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'learn_press_sorting_choice_get_template' ) ) {
	/**
	 * Get template.
	 *
	 * @param $template_name
	 * @param array $args
	 */
	function learn_press_sorting_choice_get_template( $template_name, $args = array() ) {
		learn_press_get_template( $template_name, $args, learn_press_template_path() . '/addons/sorting-choice/', LP_QUESTION_SORTING_CHOICE_TEMPLATE );
	}
}