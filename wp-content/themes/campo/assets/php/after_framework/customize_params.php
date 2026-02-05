<?php



/* /* GENERAL SECTION
-------------------------------------------------------------- */

// PAGE SHAPE
BoldThemes_Customize_Default::$data['page_shape'] = 'square';

if ( ! function_exists( 'boldthemes_customize_page_shape' ) ) {
	function boldthemes_customize_page_shape( $wp_customize ) {
		
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[page_shape]', array(
			'default'           => BoldThemes_Customize_Default::$data['page_shape'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_select'
		));
		$wp_customize->add_control( 'page_shape', array(
			'label'    => esc_html__( 'Page shape', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_general_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[page_shape]',
			'priority'  		=> 10,
			'type'      		=> 'select',
			'choices'   => array(
				'square' 	=> esc_html__( 'Square', 'campo' ),
				'rounded' 	=> esc_html__( 'Rounded', 'campo' )
			)
		));	
	}
}
add_action( 'customize_register', 'boldthemes_customize_page_shape' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_page_shape' );

BoldThemesFramework::$customize_body_class[] = 'page_shape';



/* HEADER 
-------------------------------------------------------------- */

// CREST LOGO
if ( ! function_exists( 'boldthemes_customize_crest_logo' ) ) {
	function boldthemes_customize_crest_logo( $wp_customize ) {

		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[crest_logo]', array(
			'default'           => BoldThemes_Customize_Default::$data['crest_logo'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_image',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'crest_logo', array(
			'label'    		=> esc_html__( 'Crest logo', 'campo' ),
			'description'   => esc_html__( 'Crest logo is visible when sticky header is not active', 'campo' ),
			'section'  		=> BoldThemesFramework::$pfx . '_header_section',
			'settings' 		=> BoldThemesFramework::$pfx . '_theme_options[crest_logo]',
			'priority' 		=> 35,
		)));
	}
}
add_action( 'customize_register', 'boldthemes_customize_crest_logo' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_crest_logo' );


// BLUR HEADER
BoldThemes_Customize_Default::$data['blur'] = '';

if ( ! function_exists( 'boldthemes_customize_blur' ) ) {
	function boldthemes_customize_blur( $wp_customize ) {
		
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blur]', array(
			'default'           => BoldThemes_Customize_Default::$data['blur'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox'
		));
		$wp_customize->add_control( 'blur', array(
			'label'    => esc_html__( 'Blur header background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[blur]',
			'priority' => 61,
			'type'     => 'checkbox'
		));	
	}
}
add_action( 'customize_register', 'boldthemes_customize_blur' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blur' );

BoldThemesFramework::$customize_body_class[] = 'blur';


// BLUR STICKY HEADER
BoldThemes_Customize_Default::$data['blur_sticky'] = '';

if ( ! function_exists( 'boldthemes_customize_blur_sticky' ) ) {
	function boldthemes_customize_blur_sticky( $wp_customize ) {
		
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[blur_sticky]', array(
			'default'           => BoldThemes_Customize_Default::$data['blur_sticky'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox'
		));
		$wp_customize->add_control( 'blur_sticky', array(
			'label'    => esc_html__( 'Blur sticky header background', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_responsive_header_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[blur_sticky]',
			'priority' => 241,
			'type'     => 'checkbox'
		));	
	}
}
add_action( 'customize_register', 'boldthemes_customize_blur_sticky' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_blur_sticky' );

BoldThemesFramework::$customize_body_class[] = 'blur_sticky';


/* TYPO 
-------------------------------------------------------------- */

// UPPERCASE NUMBERS
BoldThemes_Customize_Default::$data['uppercase_numbers'] = '';

if ( ! function_exists( 'boldthemes_customize_uppercase_numbers' ) ) {
	function boldthemes_customize_uppercase_numbers( $wp_customize ) {
		
		$wp_customize->add_setting( BoldThemesFramework::$pfx . '_theme_options[uppercase_numbers]', array(
			'default'           => BoldThemes_Customize_Default::$data['uppercase_numbers'],
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'boldthemes_sanitize_checkbox'
		));
		$wp_customize->add_control( 'uppercase_numbers', array(
			'label'    => esc_html__( 'Show numbers in uppercase', 'campo' ),
			'section'  => BoldThemesFramework::$pfx . '_typo_section',
			'settings' => BoldThemesFramework::$pfx . '_theme_options[uppercase_numbers]',
			'priority' => 61,
			'type'     => 'checkbox'
		));	
	}
}
add_action( 'customize_register', 'boldthemes_customize_uppercase_numbers' );
add_action( 'boldthemes_customize_register', 'boldthemes_customize_uppercase_numbers' );

BoldThemesFramework::$customize_body_class[] = 'uppercase_numbers';