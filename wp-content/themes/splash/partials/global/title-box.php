<?php
$post_id = get_the_ID();
$current_page = get_queried_object();


if(!empty($current_page->ID) and $current_page->ID == get_option( 'page_for_posts' )) {
	$post_id = get_option('page_for_posts');
}

$title = '';
$show_breadcrumbs = true;

$is_shop = false;
$is_product = false;
$is_product_category = false;

if( function_exists( 'is_shop' ) && is_shop() ){
	$is_shop = true;
}

if( function_exists( 'is_product_category' ) && is_product_category() ){
	$is_product_category = true;
}

if( function_exists( 'is_product' ) && is_product() ){
	$is_product = true;
}

if(!class_exists('WooCommerce')) {
	$is_product_category = false;
	$is_product = false;
}

if( is_category() || is_search() || is_tag() || is_year() || is_month() || is_day() ){
	$post_id = get_option( 'page_for_posts' );
}

if( $is_shop or $is_product_category) {
	$post_id = get_option( 'woocommerce_shop_page_id' );
}

$site_pages_show_title = get_theme_mod('pages_show_title', true);
$site_pages_show_bc = get_theme_mod('pages_show_breadcrumbs', true);


$page_hide_title = get_post_meta($post_id, 'page_title', true);
$page_hide_bc = get_post_meta($post_id, 'page_breadcrumbs', true);


if($site_pages_show_title) {
	$title = get_the_title($post_id);
}

if(!empty($page_hide_title) and $page_hide_title == 'on' ) {
	$title = '';
}

if(get_post_type() == 'sp_player') {
	$player_title = get_theme_mod('player_title', true);
	if(splash_is_layout("bb") && $player_title) {
		$title = '';
	}elseif(splash_is_layout("af") || splash_is_layout("sccr")){
		$title = trim(preg_replace("/[0-9]{1,}/", "", $title));
	}elseif(splash_is_layout("baseball")){
		$number = preg_match("/[0-9]{1,}/", $title, $matches, PREG_OFFSET_CAPTURE);
		$title = trim(preg_replace("/[0-9]{1,}/", "", $title));
		$title = $title . "&nbsp;&nbsp;|&nbsp;&nbsp;" .esc_html__("#", "splash") . $matches[0][0];
	}
}

if(!$site_pages_show_bc) {
	$show_breadcrumbs = false;
}

if(!empty($page_hide_bc) and $page_hide_bc == 'on') {
	$show_breadcrumbs = false;
}

$transparent_header = get_post_meta($post_id, 'transparent_header', true);

$tr_header= '';
if(!empty($transparent_header) and $transparent_header == 'on') {
	$tr_header = 'transparent-header_on';
}

/*TITLE*/
if(!empty($current_page->name) and !$is_shop) {
	$title = $current_page->name;
}

if(!empty($current_page->label) and !$is_shop) {
	$title = $current_page->label;
}

if(is_search()) {
	$title = esc_html__('Search', 'splash');
}


$shop_archive = get_option('woocommerce_shop_page_id');
$style = "";
if(splash_is_af()) {
	if ($show_breadcrumbs) $style = "style='padding-top: 27px; padding-bottom: 0px;'";
	if (!empty($tr_header)) $style = "style='padding-top: 0 !important;'";
}

if(get_option( 'page_on_front' ) == get_the_ID()) {
    $style = "style='padding-top: 0; padding-bottom: 0px;'";
}

if(!empty($page_hide_title) and $page_hide_title == 'on' && splash_is_layout("baseball") ) {
	$style = "style='padding-top: 0; padding-bottom: 0px;'";
}


if($is_shop && !splash_is_layout('soccer_two')) {
	echo "<div class='stm-revslider-wrap'>";
		$show_slider = get_theme_mod( 'shop_slider' );
		$show_slider_slug = get_theme_mod( 'select_shop_slider' );
		if ( !empty( $show_slider ) && $show_slider == "show" ) {
			putRevSlider( $show_slider_slug );
		}
	echo "</div>";
}
$title_box_class = 'title_box-' . rand(0, 1000);
if(splash_is_layout('soccer_two')){
	$header_background = get_theme_mod('header_background');
	if(!$header_background){
		$header_background = '';
	}
	if($header_background){
		$title_styles = '<style>.' . $title_box_class . '{background: url(' . $header_background . ') no-repeat center; background-size: cover;}</style>';
		echo splash_sanitize_text_field($title_styles);
	}
}
echo '<div class="stm-title-box-unit '.$tr_header. ' ' . $title_box_class .'" ' . $style . '>';


if ($show_breadcrumbs && (!splash_is_layout("bb") && !splash_is_layout("hockey")) && !splash_is_layout('soccer_news') && get_theme_mod('header_type', 'header_1') != "header_4") {
	/*Breadcrumbs*/
	if ( $is_shop || $is_product || $is_product_category ) {
		woocommerce_breadcrumb();
	} else {
		if ( function_exists( 'bcn_display' ) ) { ?>
			<div class="stm-breadcrumbs-unit normal_font">
				<div class="container">
					<div class="navxtBreads">
						<?php bcn_display(); ?>
					</div>
				</div>
			</div>
		<?php }
	}
}

if(!splash_is_layout('magazine_two') && !splash_is_layout('soccer_news') && !splash_is_layout('hockey')):
if(!empty($title)): ?>
	<div class="stm-page-title">
		<div class="container">
			<div class="clearfix stm-title-box-title-wrapper">
				<h1 class="stm-main-title-unit"><?php echo sanitize_text_field($title); ?></h1>
				<?php if(!splash_is_af() && !empty($shop_archive) and $shop_archive == $post_id or $is_product_category) {
					get_template_part('partials/global/shop-cats');
				} ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php elseif(splash_is_layout('hockey') ):
    if(!empty($title)): ?>
    <div class="stm-page-title">
        <div class="container">
            <div class="clearfix stm-title-box-title-wrapper">
                <h1 class="stm-main-title-unit white"><?php echo sanitize_text_field($title); ?></h1>
                <?php if(!splash_is_af() && !empty($shop_archive) and $shop_archive == $post_id or $is_product_category) {
                    get_template_part('partials/global/shop-cats');
                } ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php else: ?>
	<?php if(!empty($title)): ?>
		<div class="stm-page-title">
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						<div class="clearfix stm-title-box-title-wrapper">
                            <?php if(is_single()): ?>
							<span class="stm-title-box__cat">
								<?php
								$category = get_the_category(get_the_id());
								if($category) echo esc_html($category[0]->name);
								?>
							</span>
                            <?php endif; ?>
							<h1 class="stm-main-title-unit"><?php echo sanitize_text_field($title); ?></h1>
							<?php if(!splash_is_af() && !empty($shop_archive) and $shop_archive == $post_id or $is_product_category) {
								get_template_part('partials/global/shop-cats');
							} ?>
						</div>
					</div>
				</div>

			</div>
		</div>
	<?php endif; ?>
	<div class="stm-title-box__bottom container">

		<div class="stm_author_box clearfix">
			<div class="author_avatar">
				<?php echo get_avatar( get_the_author_meta( 'email' ), 54 ); ?>
			</div>
			<div class="author_info">
				<div class="author_name">
					<p><?php esc_html_e( 'by:', 'splash' ); ?>
						<?php $author_id = get_post_field ('post_author', get_the_id()); ?>
						<span class="stm-author-name "><?php the_author_meta('nickname', $author_id); ?></span>
					</p>
				</div>
				<div class="post_date">
					<?php echo get_the_date(); ?>
				</div>
			</div>
		</div>
		<div class="stm-title-box__bottom__data">
			<div class="stm-post__data">
				<?php if(function_exists('pvc_get_post_views')): ?>
					<span class="stm-views">
					<i class="fa fa-eye"></i>
						<?php echo pvc_get_post_views(get_the_id()) ? pvc_get_post_views(get_the_id()) : '0'; ?>
				</span>
				<?php endif; ?>
				<span class="stm-comments">
				<i class="icon-mg-comments"></i>
					<?php echo get_comments_number(get_the_id()); ?>
			</span>
			</div>
			<div class="stm-share-this-wrapp">
			<span class="stm-share-btn-wrapp">
				<?php if(function_exists('A2A_SHARE_SAVE_pre_get_posts')) echo A2A_SHARE_SAVE_add_to_content(""); ?>
			</span>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php if ($show_breadcrumbs && (splash_is_layout("bb") || splash_is_layout("hockey")) && get_theme_mod('header_type', 'header_1') != "header_4") {
	/*Breadcrumbs*/
	if ( $is_shop || $is_product || $is_product_category ) {
		woocommerce_breadcrumb();
	} else {
		if ( function_exists( 'bcn_display' ) ) { ?>
			<div class="stm-breadcrumbs-unit heading-font">
				<div class="container">
					<div class="navxtBreads">
						<?php bcn_display(); ?>
					</div>
				</div>
			</div>
		<?php }
	}
}

echo '</div>';