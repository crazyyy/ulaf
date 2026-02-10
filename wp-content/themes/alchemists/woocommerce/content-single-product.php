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

// Post Classes
$post_classes   = array();
$post_classes[] = 'products list products--list-lg';

$attributes   = array();

// Post Styling Options
$alchemists_data = get_option( 'alchemists_data' );
$product_gradient = isset( $alchemists_data['alchemists__product-gradient'] ) ? $alchemists_data['alchemists__product-gradient'] : 1;

if ( $product_gradient ) {
	if ( alchemists_sp_preset( 'soccer' ) ) {
		$product_color_1_default = '#ffffff';
		$product_color_2_default = '#ffffff';
	} else {
		$product_color_1_default = '#fe2b00';
		$product_color_2_default = '#f7d500';
	}
	$product_color_1   = get_field('product_grad_color_1') ? get_field('product_grad_color_1') : $product_color_1_default;
	$product_color_2   = get_field('product_grad_color_2') ? get_field('product_grad_color_2') : $product_color_2_default;

	$attributes[] = 'style="background-image: linear-gradient(to left top, ' . $product_color_1 . ', ' . $product_color_2 . ');"';
}

// Category Abbr
$product_cat_abbr = '';
$terms = wp_get_post_terms( $post->ID, 'product_cat' );
if ( !empty( $terms )) {
	$cat_id = $terms[0]->term_id;
	$product_cat_abbr = get_term_meta( $cat_id, 'alc_meta_abbr', true );
}

?>

<?php
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

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( $post_classes, $product ); ?>>
	<div class="product__item card">

		<div class="product__img" <?php echo implode( ' ', $attributes ); ?>>

			<?php if ( ! alchemists_sp_preset('soccer') ) : ?>
				<?php if ( $product_cat_abbr) : ?>
					<div class="product__bg-letters"><?php echo esc_html( $product_cat_abbr ); ?></div>
				<?php endif; ?>
			<?php endif; ?>

			<div class="product__img-holder">

				<?php
					/**
					 * Hook: woocommerce_before_single_product_summary.
					 *
					 * @hooked woocommerce_show_product_sale_flash - 10
					 * @hooked woocommerce_show_product_images - 20
					 */
					do_action( 'woocommerce_before_single_product_summary' );
				?>

			</div>
		</div>

		<div class="product__content card__content">

			<?php
				/**
				 * Hook: woocommerce_single_product_summary.
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
			?>

		</div><!-- .product__content -->

	</div>
</div><!-- #product-<?php the_ID(); ?> -->

<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
?>


<?php do_action( 'woocommerce_after_single_product' ); ?>
