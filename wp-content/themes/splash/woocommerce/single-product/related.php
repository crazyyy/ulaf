<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $woocommerce_loop;

if ( sizeof( $related_products ) === 0 ) return;

$woocommerce_loop['columns'] = $columns;

$id = 'stm-product-carousel-init-'.rand(0,9999);

$id_controls = 'stm-product-carousel-controls-'.rand(0,9999);

if ( $related_products ) : ?>

	<div class="stm-fullwidth-row-js related-products-list">
		<div class="container">
			<div class="clearfix">
				<div class="stm-title-left">
					<h2 class="stm-main-title-unit"><?php esc_html_e('Related Products', 'splash'); ?></h2>
				</div>
                <?php if(!splash_is_layout('baseball')): ?>
                    <div class="stm-carousel-controls-right <?php echo esc_attr($id_controls); ?>">
                        <div class="stm-carousel-control-prev"><i class="fa fa-angle-left"></i></div>
                        <div class="stm-carousel-control-next"><i class="fa fa-angle-right"></i></div>
                    </div>
                <?php endif; ?>
			</div>
		</div>

		<div class="clearfix"></div>

		<div class="container">
			<div class="stm-products-carousel-unit-wrapper">
				<div class="stm-products-carousel-unit">
					<div class="stm-products-carousel-init <?php echo esc_attr($id); ?>">
                        <?php foreach ( $related_products as $related_product ) : ?>
							<?php
                            $post_object = get_post( $related_product->get_id() );
                            setup_postdata( $GLOBALS['post'] =& $post_object );

							if(!splash_is_layout("sccr")) get_template_part('partials/loop/product-carousel');
							else get_template_part('partials/loop/product-carousel-soccer');
							?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>



	<script type="text/javascript">
		(function($) {
			"use strict";

			var unique_class = "<?php echo esc_js($id); ?>";

			var unique_class_controls = "<?php echo esc_js($id_controls); ?>";

			var owl = $('.' + unique_class);
			var items = 2;
			var slide = 2;
			<?php if(splash_is_layout("af") || splash_is_layout("baseball")) : ?>
                items = 3;
                slide = 3;
			<?php elseif(splash_is_layout("sccr")) : ?>
                items = 4;
                slide = 4;
			<?php elseif(splash_is_layout("soccer_two")) : ?>
				items = 3;
				slide = 3;
                <?php if(wp_is_mobile()): ?>
                    items = 3;
                    slide = 3;
                <?php endif; ?>
			<?php endif; ?>

            var margin = 0;
            <?php if(splash_is_layout("baseball")) : ?>
                margin = 10;
            <?php endif; ?>

			$(window).load(function () {
				owl.owlCarousel({
					items: 2,
					dots: true,
					autoplay: false,
					loop: true,
                    margin: margin,
					slideBy: 2,
					responsive:{
						0:{
							items:1,
							slideBy:1
						},
						440:{
							items:2,
							slideBy:2
						},
						768:{
							items: items,
							slideBy: slide
						}
					}
				});

				$('.' + unique_class_controls + ' .stm-carousel-control-prev').on('click', function(){
					owl.trigger('prev.owl.carousel');
				});

				$('.' + unique_class_controls + ' .stm-carousel-control-next').on('click', function(){
					owl.trigger('next.owl.carousel');
				});
			});
		})(jQuery);
	</script>

<?php endif;

wp_reset_postdata();
