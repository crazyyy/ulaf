<?php $ajaxloader  = WAPT_PLUGIN_URL . "/admin/assets/img/ajax-loader-line.gif";
$apt_pixabay_nonce = wp_create_nonce( 'apt_api' );


$apt_pixabay_key = WAPT_Plugin::app()->getPopulateOption( 'pixabay-apikey' );

if ( isset( $_REQUEST['post'] ) ) {
	$pid = $_REQUEST['post'];
} else {
	$pid = 0;
}

if ( $apt_pixabay_key ) {
	?>
	<script>
		window.wapt_no_hits = '<?php echo __( 'No hits', 'apt' )?>';
		window.wapt_download_svg = '<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/download.svg' ?>';
	</script>
	<script src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/js/search-page.js' ?>"></script>
	<script type="text/javascript">

		function call_api(query, page = 1, params) {
			findImages('pixabay', 'apt_api_pixabay', '<?php echo $apt_pixabay_nonce?>', query, page, params);
		}

		function do_submit() {
			jQuery('#loader_flex-pixabay').show();
			q = jQuery('#query', form).val();
			image_type = jQuery('#filter_photos', form).val();
			if (image_type === '') {
				image_type = 'all';
			}
			if (jQuery('#filter_horizontal', form).is(':checked') && !jQuery('#filter_vertical', form).is(':checked')) {
				orient = 'horizontal';
			} else if (!jQuery('#filter_horizontal', form).is(':checked') && jQuery('#filter_vertical', form).is(':checked')) {
				orient = 'vertical';
			} else {
				orient = 'all';
			}
			p = jQuery('#page_num', form).val();

			params = {
				query: q,
				orient: orient,
				image_type: image_type,
			};
			jQuery('#pixabay_results').html('');
			call_api(q, p, params);
		}

		jQuery('#prev_page').click(function (e) {
			jQuery('#page_num', form).val(parseInt(jQuery('#page_num', form).val(), 10) - 1);
			do_submit();
		});
		jQuery('#next_page').click(function (e) {
			jQuery('#page_num', form).val(parseInt(jQuery('#page_num', form).val(), 10) + 1);
			do_submit();
		});

		//Кнопка поиска
		jQuery(document).ready(function () {
			form = jQuery('#pixabay_images_form');

			form.submit(function (e) {
				e.preventDefault();
				do_submit();
			});
		});

		//загрузка в медиабиблиотеку
		jQuery(document).on('click', '#tab-pixabay .upload_pixabay', function (e) {
			if (jQuery(e.target).is('a')) {
				return;
			}
			//jQuery(document).off('click', '.upload_pixabay');
			// loading animation
			var downdiv = jQuery(this);
			downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg' ?>" style="height:80px !important">');

			downloadMedia(
					jQuery(this).data('service'),
					jQuery(this).data('url'),
					jQuery('#query').val(),
					window.parent.window.apt.postid,
					jQuery(this).data('title'),
					'<a href="https://pixabay.com/users/' + jQuery(this).data('title') + '/" target="_blank">' + jQuery(this).data('title') + '</a> @ Pixabay',
					'<?php echo esc_attr( $apt_pixabay_nonce ); ?>',
					jQuery(this)
			);
			return false;
		});
	</script>
	<div style="padding:10px 15px 25px">
		<form id="pixabay_images_form" style="margin:0">
			<div class="divform">
				<input id="query" type="text" value="" class="input_query" autofocus
				       placeholder="<?php echo __( 'Search...', 'apt' ); ?>">
				<input id="page_num" type="hidden" value="1">
				<button type="submit" class="submit_button" title="<?php echo __( 'Search', 'apt' ); ?>"><img
							src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/search.png' ?>"></button>
			</div>
			<div style="margin:1em 0;padding-left:2px;line-height:2">
				<label style="margin-right:15px;white-space:nowrap">
					<select name="filter_photos" id="filter_photos">
						<option value="all"><?php echo __( 'All', 'apt' ); ?></option>
						<option value="photo"><?php echo __( 'Photo', 'apt' ); ?></option>
						<option value="illustration"><?php echo __( 'Illustration', 'apt' ); ?></option>
						<option value="vector"><?php echo __( 'Vector', 'apt' ); ?></option>
					</select>
				</label>
				<label style="margin-right:15px;white-space:nowrap">
					<input type="checkbox" id="filter_horizontal"><?php echo __( 'Horizontal', 'apt' ); ?>
				</label>
				<label style="margin-right:25px;white-space:nowrap">
					<input type="checkbox" id="filter_vertical"><?php echo __( 'Vertical', 'apt' ); ?>
				</label>
			</div>
		</form>
		<div id="loader_flex-pixabay" style="display: none;"><img src='<?php echo $ajaxloader; ?>' width='100px' alt=''>
		</div>
		<div id="pixabay_results" class="flex-images"></div>
		<div class="apt_pages">
			<button id="prev_page" style="display: none;">
				<span class="dashicons dashicons-arrow-left-alt"></span> <?php echo __( 'Prev', 'aptp' ); ?>
			</button>
			<div id="page_num_div" style="display: none;"></div>
			<button id="next_page" style="display: none;"><?php echo __( 'Next', 'aptp' ); ?>
				<span class="dashicons dashicons-arrow-right-alt"></span>
			</button>
		</div>
	</div>
	<?php
} else {
	?>
	<div><?php echo __( 'API key is missing. Add it in APT settings', 'aptp' ); ?> ->
		<a href="<?php echo admin_url( 'admin.php?page=wapt_settings-wbcr_apt' ); ?>" target="_blank">here</a></div>
	<?php
} ?>
