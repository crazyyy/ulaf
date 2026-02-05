<?php

class bt_bb_post_slider extends BT_BB_Element {
	
	public $auto_play = '';
	
	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'post_type'					=> 'post',
			'number'					=> '',
			'category'					=> '',
			'height'    				=> '',
			'animation' 				=> '',
			'pause_on_hover'     		=> '',
			'slides_to_show' 			=> '',
			'gap' 						=> '',
			'auto_play' 				=> '',
			'size'						=> '',
			
			'navigation_position'		=> '',
			'arrows_size' 				=> '',
			'show_dots' 				=> '',
			'navigation_color_scheme'	=> '',
			'arrows_color_scheme'		=> '',
			'item_color_scheme'			=> '',			
			'shape'						=> '',
			'image_shape'     			=> '',
			
			'show_date'					=> '',
			'show_category'				=> '',
			'show_author'				=> '',
			'show_comments'				=> '',
			'show_excerpt'				=> '',
			'read_more'					=> '',
			'target'					=> ''
			
		) ), $atts, $this->shortcode ) );

		wp_enqueue_script( 
			'bt_bb_post_slider',
			get_template_directory_uri() . '/bold-page-builder/content_elements/bt_bb_post_slider/bt_bb_post_slider.js',
			array( 'jquery' )
		);
		
		$class = array( $this->shortcode );
		$data_override_class = array();
		$slider_class = array( 'slick-slider' );
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}
		
		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		if ( $gap != '' ) {
			$class[] = $this->prefix . 'gap' . '_' . $gap;
		}

		if ( $image_shape != '' ) {
			$class[] = $this->prefix . 'image_shape' . '_' . $image_shape;
		}		
		
		if ( $arrows_size != '' ) {
			$class[] = $this->prefix . 'arrows_size' . '_' . $arrows_size;
		}
		
		if ( $show_dots != '' ) {
			$class[] = $this->prefix . 'show_dots' . '_' . $show_dots;
		}

		if ( $navigation_position != '' ) {
			$class[] = $this->prefix . 'arrows_position' . '_' . $navigation_position;
		}
		
		if ( $height != '' ) {
			$class[] = $this->prefix . 'height' . '_' . $height;
		}

		if ( $animation != '' ) {
			$class[] = $this->prefix . 'animation' . '_' . $animation;
		}


		$item_color_scheme_id = NULL;
		if ( is_numeric ( $item_color_scheme ) ) {
			$item_color_scheme_id = $item_color_scheme;
		} else if ( $item_color_scheme != '' ) {
			$item_color_scheme_id = bt_bb_get_color_scheme_id( $item_color_scheme );
		}
		$item_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $item_color_scheme_id - 1 );
		if ( $item_color_scheme_colors ) $el_style .= '; --post-slider-item-primary-color:' . $item_color_scheme_colors[0] . '; --post-slider-item-secondary-color:' . $item_color_scheme_colors[1] . ';';
		if ( $item_color_scheme != '' ) $class[] = $this->prefix . 'item_color_scheme_' .  $item_color_scheme_id;


		$navigation_color_scheme_id = NULL;
		if ( is_numeric ( $navigation_color_scheme ) ) {
			$navigation_color_scheme_id = $navigation_color_scheme;
		} else if ( $navigation_color_scheme != '' ) {
			$navigation_color_scheme_id = bt_bb_get_color_scheme_id( $navigation_color_scheme );
		}
		$navigation_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $navigation_color_scheme_id - 1 );
		if ( $navigation_color_scheme_colors ) $el_style .= '; --navigation-primary-color:' . $navigation_color_scheme_colors[0] . '; --navigation-secondary-color:' . $navigation_color_scheme_colors[1] . ';';
		if ( $navigation_color_scheme != '' ) $class[] = $this->prefix . 'navigation_color_scheme_' .  $navigation_color_scheme_id;

		$arrows_color_scheme_id = NULL;
		if ( is_numeric ( $arrows_color_scheme ) ) {
			$arrows_color_scheme_id = $arrows_color_scheme;
		} else if ( $arrows_color_scheme != '' ) {
			$arrows_color_scheme_id = bt_bb_get_color_scheme_id( $arrows_color_scheme );
		}
		$arrows_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $arrows_color_scheme_id - 1 );
		if ( $arrows_color_scheme_colors ) $el_style .= '; --arrows-primary-color:' . $arrows_color_scheme_colors[0] . '; --arrows-secondary-color:' . $arrows_color_scheme_colors[1] . ';';
		if ( $arrows_color_scheme != '' ) $class[] = $this->prefix . 'arrows_color_scheme_' .  $arrows_color_scheme_id;

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		$data_slick  = ' data-slick=\'{ "lazyLoad": "progressive", "cssEase": "ease-out", "speed": "600"';
		
		if ( $animation == 'fade' ) {
			$data_slick .= ', "fade": true';
			$slider_class[] = 'fade';
			$slides_to_show = 1;
		}
		
		if ( $arrows_size != 'no_arrows' ) {
			$data_slick  .= ', "prevArrow": "&lt;button type=\"button\" class=\"slick-prev\" aria-label=\"' . esc_html__( 'Previous', 'campo' ) . '\" tabindex=\"0\" role=\"button\"&gt;&lt;/button&gt;", "nextArrow": "&lt;button type=\"button\" class=\"slick-next\" aria-label=\"' . esc_html__( 'Next', 'campo' ) . '\" tabindex=\"0\" role=\"button\"&gt;&lt;/button&gt;"';
		} else {
			$data_slick .= ', "arrows": false';
		}
		
		if ( $height != 'keep-height' ) {
			$data_slick .= ', "adaptiveHeight": true';
		}
		
		if ( $show_dots != 'hide' ) {
			$data_slick .= ', "dots": true' ;
		}
		
		if ( $slides_to_show > 1 ) {
			$data_slick .= ',"slidesToShow": ' . intval( $slides_to_show );
			$class[] = $this->prefix . 'multiple_slides';
		}
		
		if ( $auto_play != '' ) {
			$data_slick .= ',"autoplay": true, "autoplaySpeed": ' . intval( $auto_play );
		}
		
		if ( $pause_on_hover == 'no' ) {
			$data_slick .= ',"pauseOnHover": false';
		}

		if ( is_rtl() ) {
			$data_slick .= ', "rtl": true' ;
		}
		
		if ( $slides_to_show > 1 ) {
			$data_slick .= ', "responsive": [';
			if ( $slides_to_show > 1 ) {
				$data_slick .= '{ "breakpoint": 480, "settings": { "slidesToShow": 1, "slidesToScroll": 1 } }';	
			}
			if ( $slides_to_show > 2 ) {
				$data_slick .= ',{ "breakpoint": 768, "settings": { "slidesToShow": 2, "slidesToScroll": 2 } }';	
			}
			if ( $slides_to_show > 3 ) {
				$data_slick .= ',{ "breakpoint": 920, "settings": { "slidesToShow": 3, "slidesToScroll": 3 } }';	
			}
			if ( $slides_to_show > 4 ) {
				$data_slick .= ',{ "breakpoint": 1024, "settings": { "slidesToShow": 3, "slidesToScroll": 3 } }';	
			}
			$data_slick .= ']';
		}
		$data_slick = $data_slick . '}\' ';
		
		$class = apply_filters( $this->shortcode . '_class', $class, $atts );

		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		$show = array(
			'category' 		=> false,
			'date' 			=> false,
			'author' 		=> false,
			'comments' 		=> false,
			'excerpt' 		=> false, 
			'read_more' 	=> '',
			'target' 		=> '_self',
			'image' 		=> false,
			'size' 			=> 'large'
		);
		
		if ( $show_category == 'show_category' ) {
			$show['category'] = true;
		}
		if ( $show_excerpt == 'show_excerpt' ) {
			$show['excerpt'] = true;
		}
		if ( $show_date == 'show_date' ) {
			$show['date'] = true;
		}
		if ( $show_comments == 'show_comments' ) {
			$show['comments'] = true;
		}
		if ( $show_author == 'show_author' ) {
			$show['author'] = true;
		}
		if ( $read_more != '' ) {
			$show['read_more'] = $read_more;
		}
		if ( $size != '' ) {
			$show['size'] = $size;
		}
		if ( $target != '' ) {
			$show['target'] = $target;
		}
		
		if ( $number > 1000 || $number == '' ) {
			$number = 1000;
		} else if ( $number < 1 ) {
			$number = 1;
		}

		$posts = bt_bb_get_posts( $number, 0, $category, $post_type );

		$output = $this->slider_content( $posts, $show );
		
		$output = '<div' . $id_attr . ' class="' . implode( ' ', $class ) . '"' . $style_attr . ' data-slides="' . esc_attr( $slides_to_show ) . '" data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '"><div class="' . implode( ' ', $slider_class ) . '" ' . $data_slick .  '>' . $output . '</div></div>';
	
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );
		
		return $output;
	}
	
	static function slider_content( $posts, $show ) {

		$date_design_format         = 'j F';
		$date_design_format_day     = 'j';
		$date_design_format_month   = 'F';

		$output = '';
		$class	= array();
		
		$prefix_backend = 'bt_bb_';
		$shortcode = 'bt_bb_post_slider';

		foreach( $posts as $post_item ) {

			$post_thumbnail_id = get_post_thumbnail_id( $post_item['ID'] ); 
			$img = wp_get_attachment_image_src( $post_thumbnail_id, $show['size'] );
			$img_src = isset($img[0]) ? $img[0] : BoldThemes_Customize_Default::$data['post_image_default'];

			if ( $img_src ) {
				$style_attr = ' ';
				$hw = 0;
				if ( $img_src != '' ) {
					$hw = $img[2] / $img[1];
				}
			} else {
				$class[] = ' bt_bb_post_slider_item_inner_no_image';
				$style_attr = ' ';

				$post_thumbnail_id = attachment_url_to_postid( boldthemes_get_option( 'post_image_default' ) );
				if ( is_numeric( $post_thumbnail_id ) ) {
					$img = wp_get_attachment_image_src( $post_thumbnail_id, $show['size'] );
					$img_src = isset($img[0]) ? $img[0] : BoldThemes_Customize_Default::$data['post_image_default'];
				}
			}

			$alt = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );
			$alt = $alt != '' ? $alt : $post_item['title'];

			$output .= '<div class="' . esc_attr( $shortcode ) . '_item " ' . $style_attr . ' data-post-format="' . esc_attr( $post_item['format'] ) . '"><div class="' . esc_attr( $shortcode ) . '_item_inner">';

				// IMAGE
				if ( $post_thumbnail_id != '' ) {
					$output .= '<div class="' . esc_attr( $shortcode ) . '_item_image">';
						
						$output .= '<a href="' . esc_url_raw( $post_item['permalink'] ) . '" target="' . esc_attr( $show['target'] ) . '">';
							$output .= '<img src="' . esc_url_raw( $img_src ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $post_item['title'] ) . '">';
						$output .= '</a>';

					$output .= '</div>';
				}

				$output .= '<div class="' . esc_attr( $shortcode ) . '_item_content">';

					// META
					if ( $show['date'] || $show['author']  || $show['comments'] || $show['category'] ) {
						$meta_output = '<div class="' . esc_attr( $shortcode ) . '_item_meta">';

							if ( $show['date'] ) {

								$meta_output .= '<div class="' . esc_attr( $shortcode ) . '_item_date">';
									if ( $date_design_format_day != '' && $date_design_format_month != '' ) {
										$meta_output .= '<span class="' . esc_attr( $shortcode ) . '_item_date_day">';
											$meta_output .= get_the_date( $date_design_format_day, $post_item['ID'] );
										$meta_output .= '</span>';

										$meta_output .= '<span class="' . esc_attr( $shortcode ) . '_item_date_month">';
											$meta_output .= get_the_date( $date_design_format_month, $post_item['ID'] );
										$meta_output .= '</span>';
									} else {
										$meta_output .= get_the_date( $date_design_format, $post_item['ID'] );
									}
								$meta_output .= '</div>';
							}

							if ( $show['author'] ) {
								$meta_output .= '<span class="' . esc_attr( $shortcode ) . '_item_author">';
									$meta_output .= esc_html__( 'by ', 'campo' ) . ' ' . $post_item['author'];
								$meta_output .= '</span>';
							}

							if ( $show['comments'] && $post_item['comments'] != '' ) {
								$meta_output .= '<span class="' . esc_attr( $shortcode ) . '_item_comments">';
									$meta_output .= $post_item['comments'];
								$meta_output .= '</span>';
							}

							if ( $show['category'] ) {
								$meta_output .= '<div class="' . esc_attr( $shortcode ) . '_item_category">';
									$meta_output .= $post_item['category_list'];
								$meta_output .= '</div>';
							}

						$meta_output .= '</div>';
						$output .= $meta_output;
					}

					// TITLE
					$output .= '<h5 class="' . esc_attr( $shortcode ) . '_item_title">';
						$output .= '<a href="' . esc_url_raw( $post_item['permalink'] ) . '" target="' . esc_attr( $show['target'] ) . '">' . $post_item['title'] . '</a>';
					$output .= '</h5>';

					// EXCERPT
					if ( $show['excerpt'] ) {
						$output .= '<div class="' . esc_attr( $shortcode ) . '_item_excerpt">';
							$output .= $post_item['excerpt'];
						$output .= '</div>';
					}

					// READ MORE
					if ( $show['read_more'] ) {
						$output .= '<div class="bt_bb_post_slider_item_read_more bt_bb_button bt_bb_width_inline bt_bb_shape_inherit bt_bb_style_clean bt_bb_size_normal btWithLink btWithIcon bt_bb_icon_position_left">';
							$output .='<a href="' . esc_url_raw( $post_item['permalink'] ) . '" target="' . esc_attr( $show['target'] ) . '" class="bt_bb_link"><span class="bt_bb_button_text"> ' . esc_attr( $show['read_more'] ) . ' </span><span data-ico-dripicons="" class="bt_bb_icon_holder"></span></a>';
						$output .= '</div>';
					}
						
					$output .= '</div>';
			$output .= '</div></div>';

		}
		
		return $output;
	}
	
	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Post Slider', 'campo' ), 'description' => esc_html__( 'Slider with posts', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'post_type', 'type' => 'dropdown', 'preview' => true, 'heading' => esc_html__( 'Post Type', 'campo' ),
					'value' => array(
						esc_html__( 'Post', 'campo' ) => 'post',
						esc_html__( 'Portfolio', 'campo' ) => 'portfolio'
					)
				),
				array( 'param_name' => 'number', 'type' => 'textfield', 'heading' => esc_html__( 'Number of posts', 'campo' ), 'description' => esc_html__( 'Enter number of posts or leave empty to show all (up to 1000)', 'campo' ) ),
				array( 'param_name' => 'category', 'type' => 'textfield', 'heading' => esc_html__( 'Filter posts by category', 'campo' ), 'description' => esc_html__( 'Enter category slugs separated by "," or leave empty to show all', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'size', 'type' => 'dropdown', 'heading' => esc_html__( 'Image size', 'campo' ),
					'value' => bt_bb_get_image_sizes()
				),
				
				array( 'param_name' => 'slides_to_show', 'type' => 'textfield', 'default' => 1, 'group' => esc_html__( 'Slider', 'campo' ), 'heading' => esc_html__( 'Number of slides to show', 'campo' ), 'description' => esc_html__( 'Enter number of posts to be visible in the slider, e.g. 3', 'campo' ) ),
				array( 'param_name' => 'height', 'type' => 'dropdown', 'preview' => true, 'heading' => esc_html__( 'Slider height', 'campo' ),  'group' => esc_html__( 'Slider', 'campo' ),
					'value' => array(
						esc_html__( 'Auto', 'campo' ) 			=> 'auto',
						esc_html__( 'Keep height', 'campo' )		=> 'keep-height',
						esc_html__( 'Half screen', 'campo' )		=> 'half_screen',
						esc_html__( 'Full screen', 'campo' )		=> 'full_screen'
					)
				),
				array( 'param_name' => 'animation', 'type' => 'dropdown', 'heading' => esc_html__( 'Slider animation', 'campo' ), 'group' => esc_html__( 'Slider', 'campo' ), 'description' => esc_html__( 'If fade is selected, number of slides to show will be 1', 'campo' ),
					'value' => array(
						esc_html__( 'Default', 'campo' )		=> 'slide',
						esc_html__( 'Fade', 'campo' )			=> 'fade'
					)
				),
				array( 'param_name' => 'pause_on_hover', 'default' => 'yes', 'type' => 'dropdown', 'heading' => esc_html__( 'Pause slideshow on hover', 'campo' ), 'group' => esc_html__( 'Slider', 'campo' ),
					'value' => array(
						esc_html__( 'Yes', 'campo' )		=> 'yes',
						esc_html__( 'No', 'campo' )		=> 'no'
					)
				),
				
				array( 'param_name' => 'gap', 'type' => 'dropdown', 'heading' => esc_html__( 'Slider items gap', 'campo' ), 'group' => esc_html__( 'Slider', 'campo' ),
					'value' => array(
						esc_html__( 'No gap', 'campo' )		=> 'no_gap',
						esc_html__( 'Small', 'campo' )		=> 'small',
						esc_html__( 'Normal', 'campo' )		=> 'normal',
						esc_html__( 'Large', 'campo' )		=> 'large'
					)
				),
				array( 'param_name' => 'auto_play', 'type' => 'textfield', 'heading' => esc_html__( 'Slider autoplay interval (ms)', 'campo' ), 'group' => esc_html__( 'Slider', 'campo' ), 'description' => esc_html__( 'e.g. 2000', 'campo' ) ),
				array( 'param_name' => 'navigation_position', 'type' => 'dropdown', 'heading' => esc_html__( 'Arrows position', 'campo' ), 'group' => esc_html__( 'Slider', 'campo' ),
					'value' => array(
						esc_html__( 'On side', 'campo' ) 				=> '',
						esc_html__( 'Outside slider', 'campo' ) 		=> 'outside'
					)
				),
				array( 'param_name' => 'arrows_size', 'type' => 'dropdown', 'default' => 'normal', 'group' => esc_html__( 'Slider', 'campo' ), 'heading' => esc_html__( 'Arrows size', 'campo' ),
					'value' => array(
						esc_html__( 'No arrows', 'campo' ) 	=> 'no_arrows',
						esc_html__( 'Small', 'campo' ) 		=> 'small',
						esc_html__( 'Normal', 'campo' ) 		=> 'normal',
						esc_html__( 'Large', 'campo' ) 		=> 'large'
					)
				),
				array( 'param_name' => 'show_dots', 'type' => 'dropdown', 'heading' => esc_html__( 'Dots position', 'campo' ), 'group' => esc_html__( 'Slider', 'campo' ),
					'value' => array(
						esc_html__( 'Bottom', 'campo' ) 				=> 'bottom',
						esc_html__( 'Below', 'campo' ) 				=> 'below',
						esc_html__( 'Hide', 'campo' ) 				=> 'hide'
					)
				),
				array( 'param_name' => 'navigation_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Dots color scheme', 'campo' ), 'value' => $color_scheme_arr, 'description' => esc_html__( 'Choose navigation colors - Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Slider', 'campo' ) ),
				array( 'param_name' => 'arrows_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Arrows color scheme', 'campo' ), 'value' => $color_scheme_arr, 'description' => esc_html__( 'Choose navigation colors - Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Slider', 'campo' ) ),


				
				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Slider item shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 				=> 'square',
						esc_html__( 'Soft Rounded', 'campo' ) 		=> 'soft_rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 		=> 'hard_rounded'
					)
				),
				array( 'param_name' => 'image_shape', 'type' => 'dropdown', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Image shape', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> 'square',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'round'
					)
				),
				array( 'param_name' => 'item_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Slider item color scheme', 'campo' ), 'value' => $color_scheme_arr, 'description' => esc_html__( 'Choose item colors - Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ) ),

				
				array( 'param_name' => 'show_date', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_date' ), 'heading' => esc_html__( 'Show date', 'campo' ), 'group' => esc_html__( 'Post', 'campo' )
				),
				array( 'param_name' => 'show_category', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_category' ), 'heading' => esc_html__( 'Show category', 'campo' ), 'group' => esc_html__( 'Post', 'campo' )
				),
				array( 'param_name' => 'show_author', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_author' ), 'heading' => esc_html__( 'Show author', 'campo' ), 'group' => esc_html__( 'Post', 'campo' )
				),
				array( 'param_name' => 'show_comments', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_comments' ), 'heading' => esc_html__( 'Show number of comments', 'campo' ), 'group' => esc_html__( 'Post', 'campo' )
				),
				array( 'param_name' => 'show_excerpt', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_excerpt' ), 'heading' => esc_html__( 'Show excerpt', 'campo' ), 'group' => esc_html__( 'Post', 'campo' )
				),
				array( 'param_name' => 'read_more', 'type' => 'textfield', 'heading' => esc_html__( 'Read more text', 'campo' ), 'group' => esc_html__( 'Post', 'campo' ) 
				),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'heading' => esc_html__( 'Target', 'campo' ), 'group' => esc_html__( 'Post', 'campo' ),
					'value' => array(
						esc_html__('Self (open in same tab)', 'campo' )		=> '_self',
						esc_html__('Blank (open in new tab)', 'campo' )		=> '_blank',
					)
				),
			)
		) );
	}
}