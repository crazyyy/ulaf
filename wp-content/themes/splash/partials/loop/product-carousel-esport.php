<?php
global $post;
$post_id = $post->ID;
if( class_exists( 'WooCommerce' ) ):
    $product = wc_get_product( $post_id );
    $image = splash_get_thumbnail_url( $post_id, 0, 'stm-445-400' );
    $salePrice = $product->get_sale_price();
    $price = $product->get_price_html()
    ?>

    <div class="stm-single-product-carousel">
        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="stm-product-link">
            <?php if( !empty( $image ) ): ?>
                <div class="image">
                    <img src="<?php echo esc_url( $image ) ?>" alt="<?php the_title(); ?>"/>
                    <?php
                    if( !empty( $price ) ): ?>
                        <div class="price heading-font">
                            <?php echo wp_kses_post($price); ?>
                        </div>
                    <?php
                    endif; ?>
                </div>
            <?php endif; ?>
            <div class="clearfix stm-product-meta">
                <div class="title heading-font">
                    <?php the_title(); ?>
                </div>
                <span
                    rel="nofollow"
                    data-quantity="1"
                    data-product_id="<?php echo intval( $post_id ); ?>"
                    data-product_sku=""
                    class="add_to_cart_button ajax_add_to_cart">
                        <i class="fa fa-shopping-cart"></i>
                </span>
            </div>
        </a>
    </div>
<?php endif; ?>