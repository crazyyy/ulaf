<?php
define( 'STM_THEME_VERSION', ( WP_DEBUG ) ? time() : wp_get_theme()->get( 'Version' ) );
if ( ! is_admin() ) {
	add_action( 'wp_enqueue_scripts', 'splash_enqueue_scripts_styles', 5 );
}

function splash_enqueue_scripts_styles() {
	$template_path = get_template_directory_uri();
	$typography_body_font_family    = get_theme_mod( 'typography_body_font_family' );
	$typography_heading_font_family = get_theme_mod( 'typography_heading_font_family' );
	if ( empty( $typography_body_font_family ) or empty($typography_heading_font_family)) {
		wp_enqueue_style(
			'stm_default_google_font',
			splash_default_google_fonts_enqueue(),
			null,
			STM_THEME_VERSION,
			'all'
		);
	}

	// Styles
	wp_enqueue_style( 'boostrap', $template_path . '/assets/css/bootstrap.min.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'select2', $template_path . '/assets/css/select2.min.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'owl-carousel', $template_path . '/assets/css/owl.carousel.css', null, STM_THEME_VERSION, 'all' );
	// wp_enqueue_style( 'stm-font-awesome', $template_path . '/assets/css/font-awesome.min.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'stm-font-awesome-5', $template_path . '/assets/css/fontawesome.min.css', null, STM_THEME_VERSION, 'all' );
	// wp_enqueue_style( 'vc_font_awesome_5' );
	wp_enqueue_style( 'fancybox', $template_path . '/assets/css/jquery.fancybox.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'lightbox', $template_path . '/assets/css/lightbox.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'stm-theme-animate', $template_path.'/assets/css/animate.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'stm-theme-icons', $template_path.'/assets/css/splash-icons.css', null, STM_THEME_VERSION, 'all' );

	wp_enqueue_style( 'perfect-scrollbar', $template_path . '/assets/css/perfect-scrollbar.css', null, STM_THEME_VERSION, 'all' );

	if ( get_theme_mod( 'frontend_customizer' ) ) {
		wp_enqueue_style( 'stm-frontend_customizer', $template_path . '/assets/css/frontend_customizer.css', null, STM_THEME_VERSION, 'all' );
	}

	$upload_dir = wp_upload_dir();
	$stm_upload_dir = $upload_dir['baseurl'] . '/stm_uploads';
	$stm_upload_basedir = $upload_dir['basedir'] . '/stm_uploads';
	$skin = get_theme_mod('site_style', 'default');
	if($skin == 'default' or $skin == 'site_style_custom') {
		if ( is_dir( $upload_dir['basedir'] . '/stm_uploads' ) and $skin != 'default' ) {
			wp_enqueue_style( 'stm-skin-custom', $stm_upload_dir . '/skin-custom.css?t=' . time(), null, STM_THEME_VERSION, 'all' );

			if ( file_exists( $stm_upload_basedir . '/skin-custom-layout.css' ) ) {
                wp_enqueue_style('stm-skin-custom-layout', $stm_upload_dir . '/skin-custom-layout.css?t=' . time(), null, STM_THEME_VERSION, 'all');
            }
		} else {
            wp_enqueue_style('stm-theme-style', $template_path . '/assets/css/styles.css', null, STM_THEME_VERSION, 'all');
            if(splash_is_layout("af")) wp_enqueue_style('stm-theme-style-af', $template_path . '/assets/css/american_football_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("sccr")) wp_enqueue_style('stm-theme-style-sccr', $template_path . '/assets/css/soccer_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("baseball")) wp_enqueue_style('stm-theme-style-baseball', $template_path . '/assets/css/baseball_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("magazine_one")) wp_enqueue_style('stm-theme-style-magazine-one', $template_path . '/assets/css/magazine_one_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("magazine_two")) wp_enqueue_style('stm-theme-style-magazine-two', $template_path . '/assets/css/magazine_two_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("soccer_two")) wp_enqueue_style('stm-theme-style-soccer-two', $template_path . '/assets/css/soccer_two_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("soccer_news")) wp_enqueue_style('stm-theme-style-soccer_news', $template_path . '/assets/css/soccer_news_styles.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("basketball_two")) wp_enqueue_style('stm-theme-style-basketball_two', $template_path . '/assets/css/basketball_two.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("hockey")) wp_enqueue_style('stm-theme-style-hockey', $template_path . '/assets/css/hockey.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("esport")) wp_enqueue_style('stm-theme-style-esport', $template_path . '/assets/css/esport.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("volleyball")) wp_enqueue_style('stm-theme-style-volleyball', $template_path . '/assets/css/volleyball.css', null, STM_THEME_VERSION, 'all');
            else if(splash_is_layout("rugby")) wp_enqueue_style('stm-theme-style-rugby', $template_path . '/assets/css/rugby.css', null, STM_THEME_VERSION, 'all');
        }
	} else {
		wp_enqueue_style( 'stm-theme-style-prepackaged', $template_path . '/assets/css/skins/skin-custom-' . $skin . '.css', null, STM_THEME_VERSION, 'all' );
	}


	wp_enqueue_style( 'stm-theme-default-styles', $template_path.'/style.css', null, STM_THEME_VERSION, 'all' );


	// THEME SCRIPTS
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$google_api_key = get_theme_mod( 'google_api_key', '' );
	if( !empty($google_api_key) ) {
		$google_api_map = 'https://maps.googleapis.com/maps/api/js?key='.$google_api_key.'&';
	} else {
		$google_api_map = 'https://maps.googleapis.com/maps/api/js?';
	}
	wp_register_script( 'stm_gmap', $google_api_map, array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'stm_gmap' );

	wp_enqueue_script( 'perfect-scrollbar', $template_path . '/assets/js/perfect-scrollbar.min.js', array( 'jquery' ), STM_THEME_VERSION, true );

	wp_register_script( 'stm-theme-ticker', $template_path . '/assets/js/ticker.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'bootstrap', $template_path . '/assets/js/bootstrap.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'select2', $template_path . '/assets/js/select2.full.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'owl-carousel', $template_path . '/assets/js/owl.carousel.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'fancybox', $template_path . '/assets/js/jquery.fancybox.pack.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'imagesloaded', $template_path . '/assets/js/imagesloaded.pkgd.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'isotope', $template_path . '/assets/js/isotope.pkgd.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_register_script( 'cloud_carousel', $template_path . '/assets/js/jquery.cloud9carousel.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'waypoint', $template_path . '/assets/js/waypoints.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'counterup', $template_path . '/assets/js/jquery.counterup.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'lightbox', $template_path . '/assets/js/lightbox.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'stm-theme-scripts', $template_path . '/assets/js/splash.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'stm-theme-scripts-header', $template_path . '/assets/js/header.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'stm-ticker-posts', $template_path . '/assets/js/ticker_posts.js', array( 'jquery' ), STM_THEME_VERSION, true );
	wp_enqueue_script( 'ajax-submit', $template_path . '/assets/js/ajax.submit.js', array( 'jquery' ), STM_THEME_VERSION, true );

	$smooth_scroll = get_theme_mod( 'smooth_scroll', false );
	if( !empty($smooth_scroll) and $smooth_scroll) {
		wp_enqueue_script( 'stm-smooth-scroll', $template_path . '/assets/js/smoothScroll.js', array( 'jquery' ), STM_THEME_VERSION, true );
	}

	if(is_singular('product') && !splash_is_layout("sccr")) {
		wp_enqueue_script("stm-slick", $template_path . '/assets/js/slick.min.js', array( 'jquery' ), STM_THEME_VERSION, true );
	}

	if(splash_is_layout('magazine_one')) {
        wp_enqueue_script("reading-time", $template_path . '/assets/js/reading-text/reading-time.js', array( 'jquery' ), STM_THEME_VERSION, true );
    }
}

if(!function_exists('splash_enqueue_modul_scripts_styles')) {
    function splash_enqueue_modul_scripts_styles($fileName) {

        if(file_exists(get_theme_file_path('/assets/css/vc_components/' . $fileName . '.css'))) {
            wp_enqueue_style($fileName, get_theme_file_uri('/assets/css/vc_components/' . $fileName . '.css'), null, STM_THEME_VERSION, 'all');
        }

        if(file_exists(get_theme_file_path('/assets/js/vc_components/' . $fileName . '.js'))) {
            wp_enqueue_script( $fileName, get_theme_file_uri('/assets/js/vc_components/' . $fileName . '.js'), 'jquery', STM_THEME_VERSION, true );
        }
    }
}

function splash_admin_styles() {
	$template_path = get_template_directory_uri();

	wp_enqueue_style( 'stm-theme-splash-icons-styles', $template_path . '/assets/css/splash-icons.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'stm-theme-admin-styles', $template_path . '/assets/css/admin.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'splash-gutenberg-styles', $template_path . '/assets/css/gutenberg.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'splash-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:400,700|Roboto:400,500,700', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'select2', $template_path . '/assets/css/select2.min.css', null, STM_THEME_VERSION, 'all' );
	wp_enqueue_script( 'jquery.multiselect', $template_path . '/assets/js/jquery.multi-select.js', 'jquery', STM_THEME_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'splash_admin_styles' );


// Default Google fonts enqueue
if( !function_exists('splash_default_google_fonts_enqueue')) {
	function splash_default_google_fonts_enqueue() {

		$font_families = array();
		$themeSettings = getThemeSettings();

		foreach ($themeSettings["fonts"] as $k => $val) {
			if($val['onOff'] !== 'off') {
				$font_families[$k] = $val['family'] . ':' . $val['weight'];
			}
		}

		$query_args = array(
			'family' =>  implode( '|', $font_families )
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

		return esc_url( $fonts_url );
	}
}
