<?php
global $post;
$post_id = $post->ID;
if( class_exists( 'WooCommerce' ) ):
    $product = wc_get_product( $post_id );
    $image = splash_get_thumbnail_url( $post_id, 0, 'stm-445-400' );
    $salePrice = $product->get_sale_price();
    $price = $product->get_price_html()
    ?>

	<div class="stm-products-carousel__product">
		<a href="<?php the_permalink(); ?>" class="stm-products-carousel__product-image">
		<?php if( !empty( $image ) ): ?>
             <img src="<?php echo esc_url( $image ) ?>" alt="<?php the_title(); ?>"/>
		<?php endif; ?>
        </a>
		<div class="stm-products-carousel__product-info">
			<div class="stm-products-carousel__product-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>

			<?php if( !empty( $price ) ): ?>
			<div class="stm-products-carousel__product-price heading-font"><?php echo wp_kses_post($price); ?></div>
			<?php endif; ?>

			<div class="stm-products-carousel__product-actions">
				<a href="#" class="stm-products-carousel__product-add add_to_cart_button ajax_add_to_cart heading-font"
					data-quantity="1"
					data-product_id="<?php echo intval($post_id); ?>"
					data-product_sku="">
					+ <?php esc_html_e('Add to cart', 'splash'); ?>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>
