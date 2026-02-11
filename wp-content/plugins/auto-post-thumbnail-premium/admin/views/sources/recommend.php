<div><?php echo __( 'Plugin analyzes the text of the article with a neural network and gives you the result in the form of recommended images connected with the text of the topic of your article! You just need to choose the right image!', 'aptp' ); ?></div>

<?php

$ajaxloader = WAPT_PLUGIN_URL . "/admin/assets/img/ajax-loader-line.gif";
$nonce      = wp_create_nonce( 'apt_api' );


$watson_apikey   = WAPT_Plugin::app()->getPopulateOption( 'ibm-watson-apikey' );
$watson_endpoint = WAPT_Plugin::app()->getPopulateOption( 'ibm-watson-endpoint' );

if ( isset( $_REQUEST['post'] ) ) {
	$pid = $_REQUEST['post'];
} else {
	$pid = 0;
}

if ( $watson_apikey && $watson_endpoint ) {
	$google_apikey = WAPT_Plugin::app()->getPopulateOption( 'google_apikey' );
	$google_cse    = WAPT_Plugin::app()->getPopulateOption( 'google_cse' );

	$google          = ! empty( $google_apikey ) && ! empty( $google_cse );
	$pixabay_apikey  = WAPT_Plugin::app()->getPopulateOption( 'pixabay-apikey' );
	$pixabay         = ! empty( $pixabay_apikey );
	$unsplash_apikey = WAPT_Plugin::app()->getPopulateOption( 'unsplash-apikey' );
	$unsplash        = ! empty( $unsplash_apikey );

	$post_id = isset( $_GET['post_id'] ) ? $_GET['post_id'] : ( isset( $_POST['post_id'] ) ? $_POST['post_id'] : - 1 );
	$post    = get_post( $post_id, OBJECT );

	$key        = WAPT_Plugin::app()->getPrefix() . 'watson_categories';
	$meta_key   = WAPT_Plugin::app()->getPrefix() . 'watson_categories';
	$categories = get_post_meta( $post->ID, $meta_key, true );

	$post_content = strip_tags( $post->post_content );
	$watson       = true;
	if ( empty( $categories ) && strlen( $post_content ) > 550 ) {
		$categories = ( new WAPT_IBMWatson( $post_content ) )->categories()->analyze();
		if ( ! isset( $categories['categories'] ) ) {
			$watson = false;
			if ( isset( $categories['error'] ) ) {
				//echo $categories['error'];
			} else {
				//echo 'Internal error on IBM Watson';
			}
			//die();
		}

		$categories = isset( $categories['categories'] ) ? $categories['categories'] : [];
		update_post_meta( $post->ID, $meta_key, $categories );
	}

	$popular_category = isset( $categories[0]['label'] ) ? $categories[0]['label'] : '';
	//$founded = ( new WAPT_GoogleImages() )->search( $categories[0], 1 );

	/**
	 * @var array $categories = [
	 *     [
	 *          'label' => string,
	 *          'score' => float,
	 *     ]
	 * ]
	 */

	?>
	<style>
		.watson-categories ul li
		{
			display: block;
		}
	</style>
	<?php /*
    <div style="padding:10px 15px 25px">
        <div class="watson-categories">
            <?php if(is_array($categories)): ?>
                <ul>
                    <?php foreach(array_slice($categories, 0, 3) as $category): ?>
                        <li>
                            <a href="#" class="watson-cat-search" data-label="<?php echo trim($category['label'], '/')?>">
                                <?php echo trim($category['label'], '/')?> (<?php echo round($category['score'], 2) * 100?>%)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <a href="#" id=""><?php echo __( 'Upload possible categories', 'aptp' )?></a>
            <?php endif; ?>
        </div>
    </div>
    */
	?>

	<div id="google">
		<h3>Google</h3>
		<div id="google_loader_flex" style="display: none;"><img src='<?php echo $ajaxloader ?>' width='100px' alt=''>
		</div>
		<div id="google_results" class="flex-images"></div>
	</div>

	<div id="pixabay">
		<h3>Pixabay</h3>
		<div id="pixabay_loader_flex" style="display: none;"><img src='<?php echo $ajaxloader ?>' width='100px' alt=''>
		</div>
		<div id="pixabay_results" class="flex-images"></div>
	</div>

	<div id="unsplash">
		<h3>Unsplash</h3>
		<div id="unsplash_loader_flex" style="display: none;"><img src='<?php echo $ajaxloader ?>' width='100px' alt=''>
		</div>
		<div id="unsplash_results" class="flex-images"></div>
	</div>

	<script src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/js/search-page.js' ?>"></script>
	<script>
		window.wapt_no_hits = '<?php echo __( 'No hits', 'apt' )?>';
		window.wapt_download_svg = '<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/download.svg' ?>';
	</script>
	<script>
		jQuery("#upd-categories").on('click', function () {
			jQuery.post(ajaxurl, {
				action: 'remove_post_watson_categories',
				nonce: '<?php echo wp_create_nonce( 'remove_post_watson_categories' )?>',
				post_id: <?php echo $post->ID?>
			}, function () {
				jQuery("#tabs-1").click();
			});
		});

		<?php
		$nonce = wp_create_nonce( 'apt_api' );
		?>
		jQuery(document).ready(function () {
			jQuery(document).on('click', '#tab-recommend .upload_google', function (e) {
				if (jQuery(e.target).is('a')) return;
				//jQuery(document).off('click', '.upload_google');
				// loading animation
				var downdiv = jQuery(this);
				downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg' ?>" style="height:80px !important">');
				downloadMedia(
						'google',
						jQuery(this).data('url'),
						'<?php echo $popular_category?>',
						window.parent.window.apt.postid,
						jQuery(this).data('title'),
						'<a href="' + jQuery(this).data('link') + '" target="_blank">' + jQuery(this).data('title') + '</a>',
						'<?php echo $nonce?>',
						jQuery(this)
				);
			});

			jQuery(document).on('click', '#tab-recommend .upload_pixabay', function () {
				var downdiv = jQuery(this);
				downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg' ?>" style="height:80px !important">');
				downloadMedia(
						'pixabay',
						jQuery(this).data('url'),
						'<?php echo $popular_category?>',
						window.parent.window.apt.postid,
						jQuery(this).data('title'),
						'<a href="' + jQuery(this).data('userlink') + '" target="_blank">' + jQuery(this).data('user') + '</a> @ <a href="' + jQuery(this).data('link') + '" target="_blank">Pixabay</a>',
						'<?php echo $nonce?>',
						jQuery(this)
				);
			})

			jQuery(document).on('click', '#tab-recommend .upload_unsplash', function () {
				var downdiv = jQuery(this);
				downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg' ?>" style="height:80px !important">');
				downloadMedia(
						'unsplash',
						jQuery(this).data('url'),
						'<?php echo $popular_category?>',
						window.parent.window.apt.postid,
						jQuery(this).data('title'),
						'<a href="' + jQuery(this).data('userlink') + '" target="_blank">' + jQuery(this).data('user') + '</a> @ <a href="' + jQuery(this).data('link') + '" target="_blank">Unsplash</a>',
						'<?php echo $nonce?>',
						jQuery(this)
				);
			})

			findImages('google', 'apt_api_google', '<?php echo $nonce?>', '<?php echo $popular_category?>', 1, {
				post_id: window.parent.window.apt.postid,
				limit: 5,
				watson: <?php echo (int) $watson;?>
			});

			findImages('pixabay', 'apt_api_pixabay', '<?php echo $nonce?>', '<?php echo str_replace( '/', ' ', $popular_category )?>', 1, {
				post_id: window.parent.window.apt.postid,
				limit: 5,
				watson: <?php echo (int) $watson;?>
			})

			findImages('unsplash', 'apt_api_unsplash', '<?php echo $nonce?>', '<?php echo $popular_category?>', 1, {
				post_id: window.parent.window.apt.postid,
				limit: 5,
				watson: <?php echo (int) $watson;?>
			})
		});

		jQuery(".watson-cat-search").on('click', function () {
			var tab = document.getElementById("search-engine");
			tab = tab.options[tab.selectedIndex].value;

			window.search_query = jQuery(this).attr('data-label');

			console.log(window.search_query);
			console.log(tab);

			switch (tab) {
				case 'google':
					jQuery("#tabs-2").click();
					break;

				case 'pixabay':
					jQuery("#tabs-3").click();
					break;

				case 'unsplash':
					jQuery("#tabs-4").click();
					break;
			}
		});
	</script>
	<?php
} else {
	?>
	<div><?php echo __( 'IBM Watson API key or endpoint url is missing. Add it in APT settings', 'aptp' ); ?> ->
		<a href="<?php echo admin_url( 'admin.php?page=wapt_settings-wbcr_apt&apt_tab=api' ); ?>"
		   target="_blank">here</a>
	</div>
	<?php
} ?>
