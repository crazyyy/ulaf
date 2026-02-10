<?php $ajaxloader   = WAPT_PLUGIN_URL . '/admin/assets/img/ajax-loader-line.gif';
$apt_unsplash_nonce = wp_create_nonce( 'apt_api' );

if ( $apt_unsplash_key = WAPT_Plugin::app()->getPopulateOption( 'unsplash-apikey' ) ) {
	?>
	<script>
		window.wapt_no_hits = '<?php echo __( 'No hits', 'apt' ); ?>';
		window.wapt_download_svg = '<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/download.svg'; ?>';
	</script>
	<script type="text/javascript">

		function call_api(query, page = 1) {
			findImages('unsplash', 'apt_api_unsplash', '<?php echo $apt_unsplash_nonce; ?>', query, page, {
				orient: orient
			}, function () {
				jQuery('#loader_flex-unsplash').hide();
			});
		}

		function do_submit() {
			jQuery('#loader_flex-unsplash').show();
			q = jQuery('#query', form).val();

			orient = jQuery('#filter_align', form).val();
			if (orient === '') {
				orient = 'all';
			}
			p = jQuery('#page_num', form).val();

			jQuery('#unsplash_results').html('');
			call_api(q, p);
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
			form = jQuery('#unsaplash_images_form');

			form.submit(function (e) {
				e.preventDefault();
				do_submit();
			});
		});

		//загрузка в медиабиблиотеку
		jQuery(document).on('click', '#tab-unsplash .upload_unsplash', function (e) {
			if (jQuery(e.target).is('a')) return;
			//jQuery(document).off('click', '.upload_unsplash');
			// loading animation
			var downdiv = jQuery(this);
			downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg'; ?>" style="height:80px !important">');

			downloadMedia(
					'unsplash',
					jQuery(this).data('url'),
					jQuery("#query").val(),
					window.parent.window.apt.postid,
					jQuery(this).data('title'),
					'<a href="' + jQuery(this).data('link') + '" target="_blank">' + jQuery(this).data('title') + '</a> @ <a href="' + jQuery(this).data('link') + '" target="_blank">Unsplash</a>',
					'<?php echo esc_attr( $apt_unsplash_nonce ); ?>',
					jQuery(this)
			);

			return false;
		});
	</script>

	<div style="padding:10px 15px 25px">
		<form id="unsaplash_images_form" style="margin:0">
			<div class="divform">
				<input id="query" type="text" value="" class="input_query" autofocus
				       placeholder="<?php echo __( 'Search...', 'apt' ); ?>">
				<input id="page_num" type="hidden" value="1">
				<button type="submit" class="submit_button" title="<?php echo __( 'Search', 'apt' ); ?>"><img
							src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/search.png'; ?>"></button>
			</div>
			<div style="margin:1em 0;padding-left:2px;line-height:2">
				<label style="margin-right:15px;white-space:nowrap">
					<select name="filter_align" id="filter_align">
						<option value="all"><?php echo __( 'All', 'apt' ); ?></option>
						<option value="landscape"><?php echo __( 'Horizontal', 'apt' ); ?></option>
						<option value="portrait"><?php echo __( 'Vertical', 'apt' ); ?></option>
						<option value="squarish"><?php echo __( 'Square', 'apt' ); ?></option>
					</select>
				</label>
			</div>
		</form>
		<div id="loader_flex-unsplash" style="display: none;"><img src='<?php echo $ajaxloader; ?>' width='100px'
		                                                           alt=''></div>
		<div id="unsplash_results" class="flex-images"></div>
		<div class="apt_pages">
			<button id="prev_page" style="display: none;"><span
						class="dashicons dashicons-arrow-left-alt"></span> <?php echo __( 'Prev', 'aptp' ); ?>
			</button>
			<div id="page_num_div" style="display: none;"></div>
			<button id="next_page" style="display: none;"><?php echo __( 'Next', 'aptp' ); ?> <span
						class="dashicons dashicons-arrow-right-alt"></span>
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
