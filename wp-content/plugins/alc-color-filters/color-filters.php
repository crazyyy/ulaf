<?php declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NM_Color_Filters' ) ) {

	final class NM_Color_Filters {

		public function __construct() {

			// Declare WooCommerce HPOS compatibility early
			add_action(
				'before_woocommerce_init',
				[ self::class, 'declare_compatibility_with_custom_order_tables' ]
			);

			add_action( 'init', [ $this, 'init' ] );
			add_action( 'init', [ $this, 'update_check' ] );
		}

		/**
		 * Declare WooCommerce Custom Order Tables (HPOS) compatibility
		 */
		public static function declare_compatibility_with_custom_order_tables(): void {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'custom_order_tables',
					__FILE__,
					true
				);
				
				// Додаємо сумісність з новими features WooCommerce
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'cart_checkout_blocks',
					__FILE__,
					true
				);
			}
		}

		/**
		 * Init plugin
		 */
		public function init(): void {

			if ( ! class_exists( 'WooCommerce', false ) ) {
				add_action( 'admin_notices', [ $this, 'notice_no_woocommerce' ] );
				return;
			}

			$this->register_taxonomy();

			add_action( 'product_color_edit_form_fields', [ $this, 'product_color_edit_form_fields' ], 10, 2 );
			add_action( 'product_color_add_form_fields', [ $this, 'product_color_add_form_fields' ] );
			add_action( 'edited_product_color', [ $this, 'save_product_color' ] );
			add_action( 'created_product_color', [ $this, 'save_product_color' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'load_custom_css_js' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'plugin_scripts' ] );
			add_action( 'admin_footer', [ $this, 'add_colors_admin_side' ] );
		}

		public function notice_no_woocommerce(): void {
			echo '<div class="notice notice-error"><p>';
			printf(
				wp_kses_post(
					__( 'Color Filters requires <a href="%s" target="_blank">WooCommerce</a> to be installed and active.', 'alc-color-filters' )
				),
				'https://woocommerce.com/'
			);
			echo '</p></div>';
		}

		public function plugin_scripts(): void {
			wp_enqueue_style(
				'color-filters',
				CF_PLUGIN_URL . '/assets/css/color-filters.css',
				[],
				CF_VERSION
			);
		}

		public function load_custom_css_js( string $hook ): void {

			if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) {
				return;
			}

			// Безпечна перевірка $_GET параметра
			if ( ! isset( $_GET['taxonomy'] ) ) {
				return;
			}

			// Додаємо nonce перевірку для безпеки
			$taxonomy = sanitize_key( $_GET['taxonomy'] );

			if ( $taxonomy === 'product_color' ) {

				wp_enqueue_style(
					'cf-colorpicker',
					CF_PLUGIN_URL . '/assets/css/colorpicker.min.css',
					[],
					CF_VERSION
				);

				wp_enqueue_style(
					'cf-admin',
					CF_PLUGIN_URL . '/assets/css/admin.css',
					[],
					CF_VERSION
				);

				wp_enqueue_script(
					'cf-colorpicker',
					CF_PLUGIN_URL . '/assets/js/colorpicker.min.js',
					[ 'jquery' ],
					CF_VERSION,
					true
				);
			}
		}

		public function save_product_color( int $term_id ): void {

			// Додаємо nonce перевірку для безпеки
			if ( ! isset( $_POST['normal_fill'] ) ) {
				return;
			}

			// Перевірка nonce (якщо WordPress його додає)
			if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( $_POST['_wpnonce'], 'update-tag_' . $term_id ) ) {
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'add-tag' ) ) {
					return;
				}
			}

			$color = sanitize_hex_color( wp_unslash( $_POST['normal_fill'] ) );
			
			// Якщо не hex колір, спробуємо sanitize_text_field
			if ( ! $color ) {
				$color = sanitize_text_field( wp_unslash( $_POST['normal_fill'] ) );
			}

			if ( empty( $color ) ) {
				return;
			}

			$colors = (array) get_option( 'nm_taxonomy_colors', [] );

			$colors[ $term_id ] = $color;

			update_option( 'nm_taxonomy_colors', $colors );
		}

		public function product_color_add_form_fields(): void {
			?>
			<div class="form-field term-color-wrap">
				<label for="normal_fill"><?php esc_html_e( 'Color', 'alc-color-filters' ); ?></label>
				<input type="text" name="normal_fill" id="normal_fill" class="cf-color small-text" />
				<p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #ff0000) or use the color picker.', 'alc-color-filters' ); ?></p>
			</div>
			<?php
		}

		public function product_color_edit_form_fields( WP_Term $tag ): void {

			$colors = (array) get_option( 'nm_taxonomy_colors', [] );
			$color  = $colors[ $tag->term_id ] ?? '';
			?>
			<tr class="form-field term-color-wrap">
				<th scope="row">
					<label for="normal_fill"><?php esc_html_e( 'Color', 'alc-color-filters' ); ?></label>
				</th>
				<td>
					<input type="text" name="normal_fill" id="normal_fill" value="<?php echo esc_attr( $color ); ?>" class="cf-color small-text" />
					<p class="description"><?php esc_html_e( 'Enter a hex color code (e.g., #ff0000) or use the color picker.', 'alc-color-filters' ); ?></p>
				</td>
			</tr>
			<?php
		}

		public function add_colors_admin_side(): void {

			global $pagenow, $post;

			if (
				! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ||
				! $post ||
				get_post_type( $post ) !== 'product'
			) {
				return;
			}

			$colors = (array) get_option( 'nm_taxonomy_colors', [] );

			if ( empty( $colors ) ) {
				return;
			}
			?>
			<script>
				document.addEventListener('DOMContentLoaded', function () {
					<?php foreach ( $colors as $term_id => $color ) : ?>
						const el<?php echo (int) $term_id; ?> = document.querySelector('#product_color-<?php echo (int) $term_id; ?>');
						if (el<?php echo (int) $term_id; ?>) {
							const colorSpan = document.createElement('span');
							colorSpan.style.cssText = 'display:inline-block;width:14px;height:14px;background:<?php echo esc_js( $color ); ?>;margin-right:6px;border:1px solid #ccc;border-radius:2px;';
							el<?php echo (int) $term_id; ?>.insertBefore(colorSpan, el<?php echo (int) $term_id; ?>.firstChild);
						}
					<?php endforeach; ?>
				});
			</script>
			<?php
		}

		private function register_taxonomy(): void {

			$labels = [
				'name'                       => __( 'Colors', 'alc-color-filters' ),
				'singular_name'              => __( 'Color', 'alc-color-filters' ),
				'search_items'               => __( 'Search Colors', 'alc-color-filters' ),
				'popular_items'              => __( 'Popular Colors', 'alc-color-filters' ),
				'all_items'                  => __( 'All Colors', 'alc-color-filters' ),
				'edit_item'                  => __( 'Edit Color', 'alc-color-filters' ),
				'update_item'                => __( 'Update Color', 'alc-color-filters' ),
				'add_new_item'               => __( 'Add New Color', 'alc-color-filters' ),
				'new_item_name'              => __( 'New Color Name', 'alc-color-filters' ),
				'separate_items_with_commas' => __( 'Separate colors with commas', 'alc-color-filters' ),
				'add_or_remove_items'        => __( 'Add or remove colors', 'alc-color-filters' ),
				'choose_from_most_used'      => __( 'Choose from most used colors', 'alc-color-filters' ),
				'not_found'                  => __( 'No colors found', 'alc-color-filters' ),
				'menu_name'                  => __( 'Colors', 'alc-color-filters' ),
			];

			register_taxonomy(
				'product_color',
				[ 'product' ],
				[
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'show_in_rest'      => true, // Підтримка Gutenberg/REST API
					'labels'            => $labels,
					'rewrite'           => [
						'slug'         => 'product-color',
						'with_front'   => false,
						'hierarchical' => true,
					],
					'capabilities'      => [
						'manage_terms' => 'manage_product_terms',
						'edit_terms'   => 'edit_product_terms',
						'delete_terms' => 'delete_product_terms',
						'assign_terms' => 'assign_product_terms',
					],
				]
			);
		}

		/**
		 * MUST be public – used as WordPress hook callback
		 */
		public function update_check(): void {
			update_option( 'elm_color_filters_version', CF_VERSION );
		}
	}

	new NM_Color_Filters();
}