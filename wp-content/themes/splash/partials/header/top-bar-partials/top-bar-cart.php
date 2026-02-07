<?php
if( function_exists('WC')) {
	$woocommerce_shop_page_id = wc_get_cart_url();
	$top_bar_enable_cart = get_theme_mod('top_bar_enable_cart', true);
}
?>

<?php if(!empty($woocommerce_shop_page_id) and $top_bar_enable_cart): ?>
	<?php
    $items = WC()->cart->cart_contents_count;
    $totalPrice = WC()->cart->get_totals();
    ?>
	<!--Shop archive-->
    <?php if(splash_is_layout('magazine_one') || splash_is_layout('magazine_two') || splash_is_layout('soccer_news')) : ?>
		<div class="help-bar-shop normal_font">
			<a href="<?php echo esc_url($woocommerce_shop_page_id); ?>" title="<?php esc_html_e('Watch shop items', 'splash'); ?>">
				<div class="items-info-wrap">
					<span class="total-price heading-font"><?php echo wc_price($totalPrice['total']); ?></span>
					<span class="normal_font"><span class="stm-current-items-in-cart"><?php echo esc_attr($items)?></span> <?php echo esc_html__('items', 'splash'); ?></span>
				</div>
				<i class="icon-mg-icon-shoping-cart"></i>
			</a>
		</div>
    <?php else: ?>
		<div class="help-bar-shop normal_font stm-cart-af">
			<a href="<?php echo esc_url($woocommerce_shop_page_id); ?>" title="<?php esc_html_e('Watch shop items', 'splash'); ?>">
				<?php if (splash_is_layout("rugby")): ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="16.855" height="17.998" viewBox="0 0 16.855 17.998">
						<path id="prefix__Path_205" d="M8 1a1.958 1.958 0 0 1-.778-.16 1.994 1.994 0 0 1-.635-.425 2.062 2.062 0 0 1-.43-.635A1.907 1.907 0 0 1 6-1a1.9 1.9 0 0 1 .161-.781 2.142 2.142 0 0 1 .43-.635 1.954 1.954 0 0 1 .635-.43A1.958 1.958 0 0 1 8-3a1.947 1.947 0 0 1 .781.156 2.026 2.026 0 0 1 .635.43 2.026 2.026 0 0 1 .43.635A1.947 1.947 0 0 1 10-1a1.958 1.958 0 0 1-.156.776 1.954 1.954 0 0 1-.43.635 2.069 2.069 0 0 1-.635.429A1.947 1.947 0 0 1 8 1zm0-3a.959.959 0 0 0-.7.293A.97.97 0 0 0 7-1a.959.959 0 0 0 .293.7A.959.959 0 0 0 8 0a.97.97 0 0 0 .713-.293A.959.959 0 0 0 9-1a.97.97 0 0 0-.293-.713A.97.97 0 0 0 8-2zm7 3a1.958 1.958 0 0 1-.776-.16 2.053 2.053 0 0 1-.64-.425 1.954 1.954 0 0 1-.43-.635A1.958 1.958 0 0 1 13-1a1.947 1.947 0 0 1 .156-.781 2.026 2.026 0 0 1 .43-.635 2.011 2.011 0 0 1 .64-.43A1.958 1.958 0 0 1 15-3a1.958 1.958 0 0 1 .776.156 2.011 2.011 0 0 1 .64.43 2.026 2.026 0 0 1 .43.635A1.947 1.947 0 0 1 17-1a1.958 1.958 0 0 1-.156.776 1.954 1.954 0 0 1-.43.635 2.053 2.053 0 0 1-.64.425A1.958 1.958 0 0 1 15 1zm0-3a.959.959 0 0 0-.7.293A.97.97 0 0 0 14-1a.959.959 0 0 0 .293.7A.959.959 0 0 0 15 0a.959.959 0 0 0 .7-.293A.959.959 0 0 0 16-1a.97.97 0 0 0-.293-.713A.959.959 0 0 0 15-2zm2.539-12.529a1.35 1.35 0 0 1 .264.513 1.388 1.388 0 0 1 .029.591L17.158-9.4a1.744 1.744 0 0 1-.558 1 1.725 1.725 0 0 1-1.05.479l-9.961.869.241 1.486a.665.665 0 0 0 .239.4A.656.656 0 0 0 6.5-5h10a.479.479 0 0 1 .352.146A.479.479 0 0 1 17-4.5a.479.479 0 0 1-.146.352A.479.479 0 0 1 16.5-4h-10a1.632 1.632 0 0 1-1.08-.409 1.617 1.617 0 0 1-.576-.991l-1.67-10.03a.665.665 0 0 0-.239-.4A.656.656 0 0 0 2.5-16h-1a.491.491 0 0 1-.361-.146A.491.491 0 0 1 1-16.5a.479.479 0 0 1 .146-.352A.491.491 0 0 1 1.5-17h1a1.627 1.627 0 0 1 1.079.41 1.629 1.629 0 0 1 .581 1l.1.6H16.5a1.4 1.4 0 0 1 .576.122 1.244 1.244 0 0 1 .463.337zm-.693.938a.525.525 0 0 0 0-.161.293.293 0 0 0-.063-.132.31.31 0 0 0-.117-.088A.393.393 0 0 0 16.5-14H4.424l1 5.967 10.039-.879a.757.757 0 0 0 .459-.22.768.768 0 0 0 .254-.435z" data-name="Path 205" transform="translate(-.996 17.002)" style="fill:#707070"/>
					</svg>
				<?php else: ?>
					<i class="fa fa-shopping-cart"></i>
					<span class="list-label"><?php esc_html_e('Cart', 'splash'); ?></span>
					<span class="list-badge"><span class="stm-current-items-in-cart"><?php echo esc_attr($items); ?></span></span>
				<?php endif; ?>
			</a>
		</div>
    <?php endif; ?>
<?php endif; ?>