<?php
/**
 * Product Color Filters widget.
 *
 * @package Alchemists_Color_Filters
 * @since   1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NM_Color_Filters_Widget' ) ) {

	/**
	 * WooCommerce Color Filters Widget
	 */
	class NM_Color_Filters_Widget extends WP_Widget {

		/**
		 * Register widget with WordPress
		 */
		public function __construct() {
			$widget_ops = array(
				'classname'                   => 'nm_color_filters_widget',
				'description'                 => __( 'Display WooCommerce product color filters.', 'alc-color-filters' ),
				'customize_selective_refresh' => true,
				'show_instance_in_rest'       => true,
			);

			parent::__construct(
				'nm_color_filters',
				__( 'WooCommerce Color Filters', 'alc-color-filters' ),
				$widget_ops
			);
		}

		/**
		 * Front-end display of widget
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			// Bail early if not a shop page
			if ( ! function_exists( 'is_shop' ) && ! function_exists( 'is_product_taxonomy' ) ) {
				return;
			}

			$title       = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$title       = apply_filters( 'widget_title', $title, $instance, $this->id_base );
			$hide_empty  = ! empty( $instance['hide_empty'] );
			$layout      = ! empty( $instance['layout'] ) ? $instance['layout'] : 'color_and_text';
			$show_count  = ! empty( $instance['product_count'] );

			// Get terms
			$term_args = apply_filters(
				'elm_cf_get_terms_args',
				array(
					'taxonomy'   => 'product_color',
					'hide_empty' => $hide_empty,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

			$terms = get_terms( $term_args );

			// Bail if no terms found
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return;
			}

			// Get saved colors
			$saved_colors = (array) get_option( 'nm_taxonomy_colors', array() );

			// Output
			echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$this->render_color_filters( $terms, $saved_colors, $layout, $show_count );

			echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Render color filters
		 *
		 * @param array  $terms        Terms array.
		 * @param array  $saved_colors Saved colors array.
		 * @param string $layout       Layout type.
		 * @param bool   $show_count   Whether to show product count.
		 */
		private function render_color_filters( $terms, $saved_colors, $layout, $show_count ) {
			?>
			<div class="color-filters-wrap">
				<?php
				foreach ( $terms as $term ) {
					$this->render_color_item( $term, $saved_colors, $layout, $show_count );
				}
				?>
			</div>
			<?php
		}

		/**
		 * Render single color item
		 *
		 * @param WP_Term $term         Term object.
		 * @param array   $saved_colors Saved colors array.
		 * @param string  $layout       Layout type.
		 * @param bool    $show_count   Whether to show product count.
		 */
		private function render_color_item( $term, $saved_colors, $layout, $show_count ) {
			$color     = isset( $saved_colors[ $term->term_id ] ) ? $saved_colors[ $term->term_id ] : '';
			$term_link = get_term_link( $term );

			if ( is_wp_error( $term_link ) ) {
				return;
			}

			// Color style attribute
			$color_style = '';
			if ( ! empty( $color ) ) {
				$color_style = apply_filters(
					'elm_cf_color_style_attribute',
					sprintf( 'background: %s;', esc_attr( $color ) ),
					$color,
					$term
				);
			}

			// Item inline CSS
			$item_style = '';
			if ( 'color' === $layout ) {
				$item_style = apply_filters( 'elm_cf_color_item_inline_css', 'width: 20%;', $layout );
			}

			// Current term check
			$current_class = '';
			if ( is_tax( 'product_color', $term->slug ) ) {
				$current_class = ' current-color';
			}
			?>
			<div class="color-item<?php echo esc_attr( $current_class ); ?>" <?php echo $item_style ? 'style="' . esc_attr( $item_style ) . '"' : ''; ?>>
				<?php
				switch ( $layout ) {
					case 'color_and_text':
						$this->render_color_and_text( $term, $term_link, $color_style, $show_count );
						break;

					case 'color':
						$this->render_color_only( $term, $term_link, $color_style );
						break;

					case 'text':
						$this->render_text_only( $term, $term_link, $show_count );
						break;
				}
				?>
			</div>
			<?php
		}

		/**
		 * Render color and text layout
		 *
		 * @param WP_Term $term        Term object.
		 * @param string  $term_link   Term link URL.
		 * @param string  $color_style Color style attribute.
		 * @param bool    $show_count  Whether to show product count.
		 */
		private function render_color_and_text( $term, $term_link, $color_style, $show_count ) {
			?>
			<div class="color-wrap">
				<div class="rcorners" <?php echo $color_style ? 'style="' . esc_attr( $color_style ) . '"' : ''; ?>>
					<a href="<?php echo esc_url( $term_link ); ?>" 
					   title="<?php echo esc_attr( sprintf( __( 'View products in %s', 'alc-color-filters' ), $term->name ) ); ?>"
					   aria-label="<?php echo esc_attr( $term->name ); ?>">
						<span class="screen-reader-text"><?php echo esc_html( $term->name ); ?></span>
					</a>
				</div>
			</div>
			<span class="color-link color_and_text_link">
				<a href="<?php echo esc_url( $term_link ); ?>">
					<?php
					echo esc_html( $term->name );
					if ( $show_count ) {
						echo ' <span class="count">(' . absint( $term->count ) . ')</span>';
					}
					?>
				</a>
			</span>
			<?php
		}

		/**
		 * Render color only layout
		 *
		 * @param WP_Term $term        Term object.
		 * @param string  $term_link   Term link URL.
		 * @param string  $color_style Color style attribute.
		 */
		private function render_color_only( $term, $term_link, $color_style ) {
			?>
			<div class="color-wrap">
				<div class="rcorners" <?php echo $color_style ? 'style="' . esc_attr( $color_style ) . '"' : ''; ?>>
					<a href="<?php echo esc_url( $term_link ); ?>" 
					   title="<?php echo esc_attr( sprintf( __( 'View products in %s', 'alc-color-filters' ), $term->name ) ); ?>"
					   aria-label="<?php echo esc_attr( $term->name ); ?>">
						<span class="screen-reader-text"><?php echo esc_html( $term->name ); ?></span>
					</a>
				</div>
			</div>
			<?php
		}

		/**
		 * Render text only layout
		 *
		 * @param WP_Term $term       Term object.
		 * @param string  $term_link  Term link URL.
		 * @param bool    $show_count Whether to show product count.
		 */
		private function render_text_only( $term, $term_link, $show_count ) {
			?>
			<span class="color-link">
				<a href="<?php echo esc_url( $term_link ); ?>">
					<?php
					echo esc_html( $term->name );
					if ( $show_count ) {
						echo ' <span class="count">(' . absint( $term->count ) . ')</span>';
					}
					?>
				</a>
			</span>
			<?php
		}

		/**
		 * Back-end widget form
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$title         = isset( $instance['title'] ) ? $instance['title'] : __( 'Color Filters', 'alc-color-filters' );
			$layout        = isset( $instance['layout'] ) ? $instance['layout'] : 'color_and_text';
			$hide_empty    = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : false;
			$product_count = isset( $instance['product_count'] ) ? (bool) $instance['product_count'] : false;
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php esc_html_e( 'Title:', 'alc-color-filters' ); ?>
				</label>
				<input class="widefat" 
					   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
					   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
					   type="text" 
					   value="<?php echo esc_attr( $title ); ?>" />
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>">
					<?php esc_html_e( 'Layout:', 'alc-color-filters' ); ?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>" 
						class="widefat">
					<?php
					$options = array(
						'color_and_text' => __( 'Color and text', 'alc-color-filters' ),
						'color'          => __( 'Color', 'alc-color-filters' ),
						'text'           => __( 'Text', 'alc-color-filters' ),
					);

					foreach ( $options as $key => $value ) {
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $key ),
							selected( $layout, $key, false ),
							esc_html( $value )
						);
					}
					?>
				</select>
			</p>

			<p>
				<input type="checkbox" 
					   class="checkbox" 
					   id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" 
					   name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" 
					   value="1" 
					   <?php checked( $hide_empty ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>">
					<?php esc_html_e( 'Hide empty', 'alc-color-filters' ); ?>
				</label>
			</p>

			<p>
				<input type="checkbox" 
					   class="checkbox" 
					   id="<?php echo esc_attr( $this->get_field_id( 'product_count' ) ); ?>" 
					   name="<?php echo esc_attr( $this->get_field_name( 'product_count' ) ); ?>" 
					   value="1" 
					   <?php checked( $product_count ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'product_count' ) ); ?>">
					<?php esc_html_e( 'Include the number of assigned products', 'alc-color-filters' ); ?>
				</label>
			</p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();

			$instance['title']         = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
			$instance['layout']        = ! empty( $new_instance['layout'] ) ? sanitize_key( $new_instance['layout'] ) : 'color_and_text';
			$instance['hide_empty']    = ! empty( $new_instance['hide_empty'] ) ? 1 : 0;
			$instance['product_count'] = ! empty( $new_instance['product_count'] ) ? 1 : 0;

			// Validate layout option
			$valid_layouts = array( 'color_and_text', 'color', 'text' );
			if ( ! in_array( $instance['layout'], $valid_layouts, true ) ) {
				$instance['layout'] = 'color_and_text';
			}

			return $instance;
		}
	}

	/**
	 * Register the widget
	 */
	if ( ! function_exists( 'nm_register_color_filters_widget' ) ) {
		function nm_register_color_filters_widget() {
			register_widget( 'NM_Color_Filters_Widget' );
		}
	}
	add_action( 'widgets_init', 'nm_register_color_filters_widget' );
}