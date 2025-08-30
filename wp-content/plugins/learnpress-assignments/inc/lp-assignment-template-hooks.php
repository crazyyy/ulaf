<?php
/**
 * All template hooks for LearnPress Assignment templates.
 *
 * @author  ThimPress
 * @package LearnPress/Assignments/Hooks
 * @version 3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

/**
 * @see learn_press_content_item_assignment_duration
 * @see learn_press_content_item_assignment_title
 * @see learn_press_content_item_assignment_intro
 * @see learn_press_content_item_assignment_buttons
 */
add_action( 'learn-press/before-content-item-summary/lp_assignment', 'learn_press_content_item_assignment_duration', 5 );
add_action( 'learn-press/before-content-item-summary/lp_assignment', 'learn_press_content_item_assignment_title', 10 );
add_action( 'learn-press/before-content-item-summary/lp_assignment', 'learn_press_content_item_assignment_intro', 15 );

/**
 * @see learn_press_content_item_assignment_buttons
 * @see learn_press_course_finish_button
 */
add_action( 'learn-press/after-content-item-summary/lp_assignment', 'learn_press_content_item_assignment_buttons', 15 );
add_action( 'learn-press/after-content-item-summary/lp_assignment', 'learn_press_course_finish_button', 15 );

/**
 * @see learn_press_content_item_assignment_content
 * @see learn_press_content_item_assignment_attachment
 */
add_action( 'learn-press/content-item-summary/lp_assignment', 'learn_press_content_item_assignment_content', 5 );
add_action( 'learn-press/content-item-summary/lp_assignment', 'learn_press_content_item_assignment_attachment', 10 );

/**
 * @see learn_press_assignment_nav_buttons
 * @see learn_press_assignment_start_button
 * @see learn_press_assignment_after_sent
 * @see learn_press_assignment_result
 * @see learn_press_assignment_retake
 */
add_action( 'learn-press/assignment-buttons', 'learn_press_assignment_nav_buttons', 5 );
add_action( 'learn-press/assignment-buttons', 'learn_press_assignment_start_button', 10 );
add_action( 'learn-press/assignment-buttons', 'learn_press_assignment_after_sent', 15 );
add_action( 'learn-press/assignment-buttons', 'learn_press_assignment_result', 15 );
add_action( 'learn-press/assignment-buttons', 'learn_press_assignment_retake', 20 );

/**
 * @see learn_press_assignment_fe_setting
 * @see learn_press_assignment_fe_fields
 * @see lp_assignments_setup_shortcode_page_content
 */
add_action( 'learn-press/frontend-editor/item-settings-after', 'learn_press_assignment_fe_setting' );
add_action( 'learn-press/frontend-editor/form-fields-after', 'learn_press_assignment_fe_fields' );
add_action( 'learn-press/frontend-editor/item-extra-action', 'learn_press_assignment_fe_manager_link' );
add_filter( 'the_content', 'lp_assignments_setup_shortcode_page_content' );

