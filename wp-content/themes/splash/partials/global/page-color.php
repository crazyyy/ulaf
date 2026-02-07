<?php
$page_color = get_post_meta(get_the_id(), 'page_color', true);
if(!empty($page_color)): ?>
	<style type="text/css">
		#wrapper {
			background-color: <?php echo esc_attr($page_color); ?>;
		}
	</style>
<?php endif;