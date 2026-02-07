<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<?php if(splash_is_af() || splash_is_layout("baseball")): ?>
	<div class="row">
		<div class="col-md-12 af-single-product-title">
			<?php woocommerce_template_single_title(); ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row">
		<?php
		$class_left = "col-md-6 col-sm-6";
		$class_right = "col-md-6 col-sm-6";
		if(splash_is_layout("af") || splash_is_layout("baseball")) { $class_left = "col-md-7 col-sm-6"; $class_right = "col-md-5 col-sm-6"; }
		elseif(splash_is_layout("sccr") || splash_is_layout('soccer_two')) { $class_left = "col-md-4 col-sm-6"; $class_right = "col-md-8 col-sm-6"; }

		?>
		<div class="<?php echo esc_attr($class_left); ?>">
			<div class="stm-badge-wrapper stm-thumb-num-<?php echo count(wc_get_product()->get_gallery_image_ids()); ?>">
				<?php
					/**
					 * woocommerce_before_single_product_summary hook.
					 *
					 * @hooked woocommerce_show_product_sale_flash - 10
					 * @hooked woocommerce_show_product_images - 20
					 */
					do_action( 'woocommerce_before_single_product_summary' );
				?>
			</div>
		</div>
		<div class="<?php echo esc_attr($class_right); ?>">
			<div class="summary entry-summary stm-single-product-content-right <?php if(splash_is_layout("bb")) echo "stm-bb-prod-cont-right"?>">

				<?php
					/**
					 * woocommerce_single_product_summary hook.
					 *
					 * @hooked woocommerce_template_single_title - 5
					 * @hooked woocommerce_template_single_rating - 10
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 * @hooked woocommerce_template_single_add_to_cart - 30
					 * @hooked woocommerce_template_single_meta - 40
					 * @hooked woocommerce_template_single_sharing - 50
                     * @hooked WC_Structured_Data::generate_product_data() - 60
					 */
					do_action( 'woocommerce_single_product_summary' );
				if((splash_is_layout("sccr") && !wp_is_mobile()) || (splash_is_layout('soccer_two') && !wp_is_mobile())) {
					remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
					remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
					do_action( 'woocommerce_after_single_product_summary' );
				}
				?>

			</div><!-- .summary -->
		</div>
	</div>

	<?php
		/**
		 * woocommerce_after_single_product_summary hook.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products  - 20
		 */
		if(splash_is_layout("sccr") || splash_is_layout('soccer_two')) {
			if(!wp_is_mobile()) {
				remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
			}
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}
		do_action( 'woocommerce_after_single_product_summary' );
	?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>