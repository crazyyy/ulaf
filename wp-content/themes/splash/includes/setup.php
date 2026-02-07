<?php

if ( ! isset( $content_width ) ) {
	$content_width = 1170;
}

/*
 * PARAMS
 * 		layoutName 	-> af -> american football
 * 					-> bb -> basketball
 * 					-> sccr -> soccer
 * 		fonts -> font-family
 * 			  -> font-weight
 *
 * */

function getThemeSettings() {

	$current_theme = get_option( 'splash_layout', 'basketball' );

	switch ( $current_theme ) {
		case 'americanfootball':
			$theme      = 'af';
			$body_class = 'splashAmericanFootball';
			$fonts      = array(
				array(
					'family' => 'Oswald',
					'weight' => '300,400,700',
					'onOff'  => _x( 'on', 'Oswald font: on or off', 'splash' ),
				),
				array(
					'family' => 'Roboto',
					'weight' => '500,400,300,400italic,700',
					'onOff'  => _x( 'on', 'Roboto font: on or off', 'splash' ),
				),
			);
			break;
		case 'soccer':
			$theme      = 'sccr';
			$body_class = 'splashSoccer';
			$fonts      = array(
				array(
					'family' => 'Oswald',
					'weight' => '100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic',
					'onOff'  => _x( 'on', 'Oswald font: on or off', 'splash' ),
				),
			);
			break;
		case 'baseball':
			$theme      = 'baseball';
			$body_class = 'splashBaseball';
			$fonts      = array(
				array(
					'family' => 'Fira+Sans+Condensed',
					'weight' => '400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
					'onOff'  => _x( 'on', 'Fira Sans Condensed font: on or off', 'splash' ),
				),
			);
			break;
		case 'magazine_one':
			$theme      = 'magazine_one';
			$body_class = 'splashMagazineOne';
			$fonts      = array(
				array(
					'family' => 'Fira+Sans',
					'weight' => '300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
					'onOff'  => _x( 'on', 'Fira Sans font: on or off', 'splash' ),
				),
				array(
					'family' => 'Source+Sans+Pro',
					'weight' => '300,300i,400,400i,600,600i,700,700i,900,900i',
					'onOff'  => _x( 'on', 'Source Sans Pro font: on or off', 'splash' ),
				),
			);
			break;
		case 'magazine_two':
			$theme      = 'magazine_two';
			$body_class = 'splashMagazineTwo';
			$fonts      = array(
				array(
					'family' => 'Roboto+Condensed',
					'weight' => '300,400,700',
					'onOff'  => _x( 'on', 'Roboto Condensed font: on or off', 'splash' ),
				),
				array(
					'family' => 'Open+Sans',
					'weight' => '300,300i,400,400i,600,600i,700,700i,900,900i',
					'onOff'  => _x( 'on', 'Open Sans font: on or off', 'splash' ),
				),
			);
			break;
		case 'soccer_two':
			$theme      = 'soccer_two';
			$body_class = 'splashSoccerTwo';
			$fonts      = array(
				array(
					'family' => 'Oswald',
					'weight' => '100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic',
					'onOff'  => _x( 'on', 'Oswald font: on or off', 'splash' ),
				),
				array(
					'family' => 'Rubik',
					'weight' => '400,700',
					'onOff'  => _x( 'on', 'Rubik font: on or off', 'splash' ),
				),
			);
			break;
		case 'soccer_news':
			$theme      = 'soccer_news';
			$body_class = 'soccer_news';
			$fonts      = array(
				array(
					'family' => 'Poppins',
					'weight' => '300,400,500,600,700',
					'onOff'  => _x( 'on', 'Poppins font: on or off', 'splash' ),
				),
				array(
					'family' => 'Open+Sans',
					'weight' => '300,300i,400,400i,600,600i,700,700i,900,900i',
					'onOff'  => _x( 'on', 'Open Sans font: on or off', 'splash' ),
				),
			);
			break;
		case 'basketball_two':
			$theme      = 'basketball_two';
			$body_class = 'basketball_two';
			$fonts      = array(
				array(
					'family' => 'Teko',
					'weight' => '400,500,600,700',
					'onOff'  => _x( 'on', 'Oswald font: on or off', 'splash' ),
				),
				array(
					'family' => 'Rubik',
					'weight' => '400,500,600,700',
					'onOff'  => _x( 'on', 'Rubik font: on or off', 'splash' ),
				),
			);
			break;
		case 'hockey':
			$theme      = 'hockey';
			$body_class = 'hockey';
			$fonts      = array(
				array(
					'family' => 'Poppins',
					'weight' => '300,400,500,600,700',
					'onOff'  => _x( 'on', 'Poppins font: on or off', 'splash' ),
				),
			);
			break;
		case 'esport':
			$theme      = 'esport';
			$body_class = 'esport';
			$fonts      = array(
				array(
					'family' => 'Montserrat',
					'weight' => '300,400,500,600,700',
					'onOff'  => _x( 'on', 'Montserrat font: on or off', 'splash' ),
				),
			);
			break;
		case 'volleyball':
			$theme      = 'volleyball';
			$body_class = 'volleyball';
			$fonts      = array(
				array(
					'family' => 'Montserrat',
					'weight' => '300,400,500,600,700,900',
					'onOff'  => _x( 'on', 'Montserrat font: on or off', 'splash' ),
				),
			);
			break;
		case 'rugby':
			$theme      = 'rugby';
			$body_class = 'splash_rugby';
			$fonts      = array(
				array(
					'family' => 'Montserrat',
					'weight' => '300,400,500,600,700,900',
					'onOff'  => _x( 'on', 'Montserrat font: on or off', 'splash' ),
				),
			);
			break;
		default:
			$theme      = 'bb';
			$body_class = 'splashBasketball';
			$fonts      = array(
				array(
					'family' => 'Roboto+Condensed',
					'weight' => '300,400,700',
					'onOff'  => _x( 'on', 'Roboto Condensed font: on or off', 'splash' ),
				),
				array(
					'family' => 'Roboto',
					'weight' => '500,400,300,400italic,700',
					'onOff'  => _x( 'on', 'Roboto font: on or off', 'splash' ),
				),
			);
			break;
	}

	$header_type = get_theme_mod( 'header_type', 'header_1' );

	$header_type = ( ! empty( $header_type ) ) ? $header_type : 'header_1';

	$body_class .= ' ' . $header_type;

	return array(
		'layoutName' => $theme,
		'bodyClass'  => $body_class,
		'fonts'      => $fonts,
	);
}

function getHTag() {
	return ( ! splash_is_layout( 'bb' ) ) ? 'h2' : 'h3';
}

add_action( 'after_setup_theme', 'splash_local_theme_setup' );
function splash_local_theme_setup() {

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'sportspress' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		)
	);

	//single post video
	add_image_size( 'stm-1140-666', 1140, 666, true );

	//bb
	add_image_size( 'stm-1170-650', 1170, 650, true );
	add_image_size( 'stm-570-350', 570, 350, true );
	add_image_size( 'stm-570-250', 570, 250, true );
	add_image_size( 'stm-270-370', 270, 370, true );
	add_image_size( 'stm-540-500', 540, 500, array( 'center', 'top' ) );
	add_image_size( 'stm-270-530', 270, 530, true );
	add_image_size( 'stm-200-200', 200, 200, true );
	add_image_size( 'stm-85-105', 85, 105, true );

	//af
	add_image_size( 'stm-255-255', 255, 255, array( 'center', 'top' ) );
	add_image_size( 'stm-360-240', 360, 240, true );
	add_image_size( 'stm-360-495', 360, 495, true );
	add_image_size( 'stm-445-400', 445, 400, true );
	add_image_size( 'stm-735-240', 735, 240, true );
	add_image_size( 'stm-255-183', 255, 183, true );
	add_image_size( 'stm-350-250', 350, 250, true );

	//soccer
	add_image_size( 'blog_list', 270, 220, true );
	add_image_size( 'blog_list_medium', 390, 345, true );
	add_image_size( 'gallery_thumbnail', 80, 80, true );
	add_image_size( 'gallery_image', 560, 367, true );
	add_image_size( 'gallery_image_mini', 143, 116, true );
	add_image_size( 'player_photo', 740, 740, true );
	add_image_size( 'team_logo', 98, 98, false );

	//bsbl
	add_image_size( 'player_stat_ava', 550, 580, true );

	//magazine
	add_image_size( 'post-350-220', 350, 220, true );
	add_image_size( 'post-275-142', 275, 142, true );
	add_image_size( 'post-160-120', 160, 120, true );
	add_image_size( 'post-110-70', 110, 70, true );

	//soccer two
	add_image_size( 'post-770-450', 770, 450, true );
	add_image_size( 'post-370-210', 370, 210, true );
	add_image_size( 'post-457-470', 457, 470, true );
	add_image_size( 'post-370-420', 370, 420, true );

	//Basketball two
	add_image_size( 'stm-445-445', 445, 445, true );

	//hockey
	add_image_size( 'stm-555-460', 555, 460, true );
	add_image_size( 'stm-350-450', 350, 450, true );
	add_image_size( 'stm-720-440', 720, 440, true );

	//rugby
	add_image_size( 'stm-270-270', 270, 270, true );
	add_image_size( 'stm-760-450', 760, 450, true );

	load_theme_textdomain( 'splash', get_template_directory() . '/languages' );

	register_nav_menus(
		array(
			'primary'      => esc_html__( 'Header menu', 'splash' ),
			'bottom_menu'  => esc_html__( 'Bottom Widget menu', 'splash' ),
			'sidebar_menu' => esc_html__( 'Sidebar menu', 'splash' ),
		)
	);

	add_theme_support( 'post-formats', array( 'video', 'audio', 'image' ) );
}

add_action( 'widgets_init', 'splash_register_sidebars' );
function splash_register_sidebars() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Primary Sidebar', 'splash' ),
			'id'            => 'default',
			'description'   => esc_html__( 'Main sidebar that appears on the right or left.', 'splash' ),
			'before_widget' => '<aside id="%1$s" class="widget widget-default %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<div class="widget-title"><h4>',
			'after_title'   => '</h4></div>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer', 'splash' ),
			'id'            => 'footer',
			'description'   => esc_html__( 'Footer Widgets Area', 'splash' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
			'after_widget'  => '</div></aside>',
			'before_title'  => '<div class="widget-title"><h6>',
			'after_title'   => '</h6></div>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'SportsPress', 'splash' ),
			'id'            => 'sportspress',
			'description'   => esc_html__( 'SportsPress Widgets Area', 'splash' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
			'after_widget'  => '</div></aside>',
			'before_title'  => '<div class="widget-title"><h6>',
			'after_title'   => '</h6></div>',
		)
	);

	if ( class_exists( 'WooCommerce' ) ) {
		register_sidebar(
			array(
				'name'          => esc_html__( 'Shop', 'splash' ),
				'id'            => 'shop',
				'description'   => esc_html__( 'Woocommerce pages sidebar', 'splash' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<div class="widget_title"><h3>',
				'after_title'   => '</h3></div>',
			)
		);
	}
}
