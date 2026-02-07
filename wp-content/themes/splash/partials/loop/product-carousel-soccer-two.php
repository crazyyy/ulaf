<?php
global $post;
$post_id = $post->ID;
if ( class_exists( 'WooCommerce' ) ):
	$product = wc_get_product( $post_id );

	$image = splash_get_thumbnail_url($post_id, 0, 'post-370-420');
	if(splash_is_layout("baseball")) $image = get_the_post_thumbnail_url( $post->ID, 'shop_thumbnail' );
	$currency = get_woocommerce_currency_symbol();

	$salePrice = $product->get_sale_price();
	$price = $product->get_price();
	?>

	<div class="stm-single-product-carousel">
		<?php
		if(splash_is_layout("baseball")) {
			if ($product->is_on_sale()) {
				echo apply_filters( 'woocommerce_sale_flash', '<span class="onsale normal_font">' . esc_html__( 'Sale!', 'splash' ) . '</span>', $post, $product );
			}
		}
		?>
		<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="stm-product-link">
			<?php if(!empty($image)): ?>
				<div class="image">
					<img src="<?php echo esc_url($image) ?>" alt="<?php the_title(); ?>"/>
					<?php if(!splash_is_layout("baseball")) : ?>
						<div class="stm-add-to-cart">
							<?php if(splash_is_af()): ?><span class="button btn-secondary btn-style-4"><?php endif; ?>
								<span
									rel="nofollow"
									data-quantity="1"
									data-product_id="<?php echo intval($post_id); ?>"
									data-product_sku=""
									class="<?php if(!splash_is_af()) echo "button btn-only-border"; ?> add_to_cart_button ajax_add_to_cart">
								<?php esc_html_e('Add to cart', 'splash'); ?>
							</span>
								<?php if(splash_is_af()): ?></span><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>


			<div class="clearfix stm-product-meta">
				<div class="title">
					<?php the_title(); ?>
				</div>
				<?php
				if(!splash_is_layout("baseball")) :
					if(!empty($price)): ?>
						<div class="price heading-font">
							<?php echo wc_price($price); ?>
						</div>
					<?php
					endif;
				else: ?>
					<div class="stm-bsb-price-add-to-cart">
						<?php if(!empty($price)): ?>
							<div class="price heading-font">
								<?php echo splash_sanitize_text_field($product->get_price_html()); ?>
							</div>
						<?php endif; ?>

						<span rel="nofollow" data-quantity="1" data-product_id="<?php echo intval($post_id); ?>" data-product_sku="" class="add_to_cart_button normal_font ajax_add_to_cart">
                            <?php //esc_html_e('Add to cart', 'splash'); ?>
                        </span>
					</div>
				<?php endif; ?>
			</div>
		</a>
	</div>
<?php endif; ?>