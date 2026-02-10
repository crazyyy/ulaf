<?php
/**
 * OTFS Officials Template Loader
 *
 * @class       OTFS_Template_Loader
 * @version     1.0.0
 * @package     OTFS/Classes
 * @category    Class
 * @author      SavvasHa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * OTFS_Template_Loader
 */
class OTFS_Template_Loader {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'the_content', array( $this, 'officials_content' ) );
	}

	/**
	 * Add content based on type, position, and caption.
	 *
	 * @param string $content  The original content.
	 * @param string $type     The type of content.
	 * @param int    $position The position of the content.
	 * @param string $caption  The caption for the content.
	 *
	 * @return string Modified content.
	 */
	public function add_content( $content, $type, $position = 10, $caption = null ) {
		if ( ! defined( 'ABSPATH' ) ) {
			exit; // Exit if accessed directly.
		}
		if ( ! in_the_loop() ) {
			return; // Return if not in main loop.
		}

		// Return password form if required.
		if ( post_password_required() ) {
			echo esc_html( get_the_password_form() );
			return;
		}

		// Prepend caption to content if given.
		if ( $content ) {
			if ( $caption ) {
				$content = '<h3 class="sp-post-caption">' . $caption . '</h3>' . $content;
			}

			$content = '<div class="sp-post-content">' . $content . '</div>';
		}

		// Get layout setting.
		$layout = (array) get_option( 'sportspress_' . $type . '_template_order', array() );

		// Get templates.
		$templates = SP()->templates->$type;

		// Combine layout setting with available templates.
		$templates = array_merge( array_flip( $layout ), $templates );

		$templates = apply_filters( 'sportspress_' . $type . '_templates', $templates );

		// Split templates into sections and tabs.
		$slice = array_search( 'tabs', array_keys( $templates ), true );
		if ( $slice ) {
			$section_templates = array_slice( $templates, 0, $slice );
			$tab_templates     = array_slice( $templates, $slice );
		} else {
			$section_templates = $templates;
			$tab_templates     = array();
		}

		ob_start();

		// Before template hook.
		do_action( 'sportspress_before_single_' . $type );

		// Loop through sections.
		if ( ! empty( $section_templates ) ) {
			foreach ( $section_templates as $key => $template ) {
				// Ignore templates that are unavailable or that have been turned off.
				if ( ! is_array( $template ) ) {
					continue;
				}
				if ( ! isset( $template['option'] ) ) {
					continue;
				}
				if ( 'yes' !== get_option( $template['option'], sp_array_value( $template, 'default', 'yes' ) ) ) {
					continue;
				}

				// Render the template.
				echo '<div class="sp-section-content sp-section-content-' . esc_attr( $key ) . '">';
				if ( 'content' === $key ) {
					echo wp_kses_post( $content );
					// Template content hook.
					do_action( 'sportspress_single_' . $type . '_content' );
				} else {
					call_user_func( $template['action'] );
				}
				echo '</div>';
			}
		}

		// After template hook.
		do_action( 'sportspress_after_single_' . $type );

		$ob = ob_get_clean();

		$tabs = '';

		if ( ! empty( $tab_templates ) ) {
			$i           = 0;
			$tab_content = '';

			foreach ( $tab_templates as $key => $template ) {
				// Ignore templates that are unavailable or that have been turned off.
				if ( ! is_array( $template ) ) {
					continue;
				}
				if ( ! isset( $template['option'] ) ) {
					continue;
				}
				if ( 'yes' !== get_option( $template['option'], sp_array_value( $template, 'default', 'yes' ) ) ) {
					continue;
				}

				// Put tab content into buffer.
				ob_start();
				if ( 'content' === $key ) {
					echo wp_kses_post( $content );
				} else {
					call_user_func( $template['action'] );
				}
				$buffer = ob_get_clean();

				// Trim whitespace from buffer.
				$buffer = trim( $buffer );

				// Continue if tab content is empty.
				if ( empty( $buffer ) ) {
					continue;
				}

				// Get template label.
				$label = sp_array_value( $template, 'label', $template['title'] );

				// Add to tabs.
				$tabs .= '<li class="sp-tab-menu-item' . ( 0 === $i ? ' sp-tab-menu-item-active' : '' ) . '"><a href="#sp-tab-content-' . $key . '" data-sp-tab="' . $key . '">' . apply_filters( 'gettext', $label, $label, 'sportspress' ) . '</a></li>';

				// Render the template.
				$tab_content .= '<div class="sp-tab-content sp-tab-content-' . $key . '" id="sp-tab-content-' . $key . '"' . ( 0 === $i ? ' style="display: block;"' : '' ) . '>' . $buffer . '</div>';

				++$i;
			}

			$ob .= '<div class="sp-tab-group">';

			if ( ! empty( $tabs ) ) {
				$ob .= '<ul class="sp-tab-menu">' . $tabs . '</ul>';
			}

			$ob .= $tab_content;

			$ob .= '</div>';
		}

		return $ob;
	}

	/**
	 * Filter the content for single sp_official posts.
	 *
	 * This function modifies the content for single sp_official posts by adding
	 * specific content based on the SportsPress configuration and settings.
	 *
	 * @param string $content The original content.
	 * @return string Modified content for single sp_official posts.
	 */
	public function officials_content( $content ) {
		if ( is_singular( 'sp_official' ) ) {
			$content = self::add_content( $content, 'officials', apply_filters( 'sportspress_official_content_priority', 10 ) );
		}
		return $content;
	}
}
if ( get_option( 'sportspress_load_officials_module', 'no' ) === 'yes' ) {
	new OTFS_Template_Loader();
}
