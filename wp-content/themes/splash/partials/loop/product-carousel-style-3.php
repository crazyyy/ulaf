<?php
global $post;
$post_id = $post->ID;
if( class_exists( 'WooCommerce' ) ):
    $product = wc_get_product( $post_id );
    $image = splash_get_thumbnail_url( $post_id, 0, 'stm-270-270' );
    $salePrice = $product->get_sale_price();
	$price = $product->get_price_html();
	$category = get_the_terms( $post_id, 'product_cat' )[0]->name;
    ?>

	<div class="stm-products-carousel__product">
		<div class="stm-products-carousel__product-image">
		<?php if( !empty( $image ) ): ?>
			<a href="<?php echo get_the_permalink() ?>">
				<img src="<?php echo esc_url( $image ) ?>" alt="<?php the_title(); ?>"/>
			</a>
		<?php endif; ?>
		</div>

		<div class="stm-products-carousel__product-content">
			<a href="<?php echo get_the_permalink() ?>" class="stm-products-carousel__product-name heading-font"><?php the_title(); ?></a>
			<div class="stm-products-carousel__product-category"><?php echo wp_kses_post($category) ?></div>
		</div>
		
		<div class="stm-products-carousel__product-footer">

			<?php if( !empty( $price ) ): ?>
			<div class="stm-products-carousel__product-price heading-font"><?php echo wp_kses_post($price); ?></div>
			<?php endif; ?>

			<div class="stm-products-carousel__product-actions heading-font">
				<a href="#" class="stm-products-carousel__product-add add_to_cart_button ajax_add_to_cart"
					data-quantity="1"
					data-product_id="<?php echo intval($post_id); ?>"
					data-product_sku="">
					+ <?php esc_html_e('Add to cart', 'splash'); ?>
				</a>
			</div>

		</div>
	</div>
<?php endif; ?>