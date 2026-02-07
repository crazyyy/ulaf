<?php
$terms = get_terms( array(
	'taxonomy' => 'product_cat',
	'hide_empty' => false,
	'count' => true,
	'parent' => '0'
) );

$current_obj = get_queried_object();
$current_term_id = 0;

if(!empty($current_obj) and !empty($current_obj->term_id)) {
	$current_term_id = $current_obj->term_id;
}
if ( class_exists( 'WooCommerce' ) ):
	if(!empty($terms) && !splash_is_af()):
	?>
		<div class="stm-shop-categories heading-font">
			<?php foreach($terms as $term): ?>
				<?php
					$active = '';
					if($term->term_id == $current_term_id) {
						$active = 'active';
					}
				?>
				<div class="stm-shop-category">
					<a href="<?php echo esc_url(get_term_link($term)); ?>" class="<?php echo esc_attr($active); ?>">
						<span class="name"><?php echo esc_attr($term->name); ?></span>
						<span class="count">(<?php echo esc_attr($term->count); ?>)</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>