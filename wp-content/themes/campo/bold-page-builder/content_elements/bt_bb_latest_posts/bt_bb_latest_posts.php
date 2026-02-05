<?php

class bt_bb_latest_posts extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'rows'            			=> '',
			'columns'         			=> '',
			'gap'             			=> '',
			'category'        			=> '',
			'target'          			=> '',
			'image_shape'     			=> '',
			'show_category'   			=> '',
			'show_date'       			=> '',
			'show_author'     			=> '',
			'show_comments'   			=> '',
			'show_excerpt'    			=> '',
			'read_more'					=> '',
			'lazy_load'  				=> 'no',
			'shape'						=> '',
			'title_text_transform'		=> '',
			'title_size'				=> '',
			'title_color_scheme' 		=> '',
			'color_scheme' 				=> '',
			'border'					=> '',
			'border_color'				=> '',
			'title_lines'				=> ''

		) ), $atts, $this->shortcode ) );
		
		$class = array( $this->shortcode );
		$data_override_class = array();
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}	
		
		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}
		
		if ( $columns != '' ) {
			$class[] = $this->prefix . 'columns' . '_' . $columns;
		}
		
		if ( $gap != '' ) {
			$class[] = $this->prefix . 'gap' . '_' . $gap;
		}

		$date_design_format         = 'j F';
		$date_design_format_day     = 'j';
		$date_design_format_month   = 'F';
		
		if ( $image_shape != '' ) {
			$class[] = $this->prefix . 'image_shape' . '_' . $image_shape;
		}

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $title_text_transform != '' ) {
			$class[] = $this->prefix . 'title_text_transform' . '_' . $title_text_transform;
		}

		if ( $title_size != '' ) {
			$class[] = $this->prefix . 'title_size' . '_' . $title_size;
		}

		if ( $show_excerpt == '' ) {
			$class[] = 'bt_bb_hide_excerpt';
		}

		if ( $title_lines != '' ) {
			$class[] = $this->prefix . 'title_lines' . '_' . $title_lines;
		}

		$title_color_scheme_id = NULL;
		if ( is_numeric ( $title_color_scheme ) ) {
			$title_color_scheme_id = $title_color_scheme;
		} else if ( $title_color_scheme != '' ) {
			$title_color_scheme_id = bt_bb_get_color_scheme_id( $title_color_scheme );
		}
		$title_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $title_color_scheme_id - 1 );
		if ( $title_color_scheme_colors ) $el_style .= '; --post-title-primary-color:' . $title_color_scheme_colors[0] . '; --post-title-secondary-color:' . $title_color_scheme_colors[1] . ';';
		if ( $title_color_scheme != '' ) $class[] = $this->prefix . 'title_color_scheme_' .  $title_color_scheme_id;


		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --latest-post-primary-color:' . $color_scheme_colors[0] . '; --latest-post-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;


		if ( $border != '' ) {
			$class[] = $this->prefix . 'border' . '_' . $border;
		}

		if ( $border_color != '' ) {
			$class[] = $this->prefix . 'border_color' . '_' . $border_color;
		}


		$style_attr = '';
		$el_style = apply_filters( $this->shortcode . '_style', $el_style, $atts );
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}


		do_action( $this->shortcode . '_before_extra_responsive_param' );
		foreach ( $this->extra_responsive_data_override_param as $p ) {
			if ( ! is_array( $atts ) || ! array_key_exists( $p, $atts ) ) continue;
			$this->responsive_data_override_class(
				$class, $data_override_class,
				apply_filters( $this->shortcode . '_responsive_data_override', array(
					'prefix' => $this->prefix,
					'param' => $p,
					'value' => $atts[ $p ],
				) )
			);
		}
		
		$class = apply_filters( $this->shortcode . '_class', $class, $atts );
		
		$number = $rows * $columns;
		
		$posts = bt_bb_get_posts( $number, 0, $category );
		
		$output = '';

		foreach( $posts as $post_item ) {

			$output .= '<div class="' . esc_attr( $this->shortcode ) . '_item">';

				// IMAGE
				$post_thumbnail_id = get_post_thumbnail_id( $post_item['ID'] );

				// POST FORMAT
				$post_format_type = get_post_format( $post_item['ID'] );

				if ( $post_thumbnail_id != '' ) {
					$img = wp_get_attachment_image_src( $post_thumbnail_id, $size = 'medium' );
					$img_src = BT_BB_Root::$path . 'img/blank.gif';
					$data_img = '';
					if ( $lazy_load == 'yes' ) {
						$img_class = 'btLazyLoadImage';
						if ( $img ) {
							$data_img = ' data-image_src="' . esc_attr( $img[0] ) . '"';
						}
					} else {
						if ( $img ) {
							$img_src = $img[0];
						}
						$img_class = '';
					}
					$output .= '<div class="' . esc_attr( $this->shortcode ) . '_item_image">';

						$output .= '<a href="' . esc_url_raw( $post_item['permalink'] ) . '" target="' . esc_attr( $target ) . '">';

							if ( $post_format_type == 'video' ) {
								$output .= '<div class="' . esc_attr( $this->shortcode ) . '_item_format">' . do_shortcode( '[bt_bb_icon icon="remixiconsmedia_e969" size="large" color_scheme="light-accent" style="borderless" shape="square" ignore_fe_editor="true"]' ) . '</div>';
							}

							$output .= '<img src="' . esc_url_raw( $img_src ) . '" alt="' . esc_attr( $post_item['title'] ) . '" title="' . esc_attr( $post_item['title'] ) . '" class="' . esc_attr( $img_class ) . '" ' . $data_img .  '>';

						$output .= '</a>';

					$output .= '</div>';
				}

				$output .= '<div class="' . esc_attr( $this->shortcode ) . '_item_content">';

					if ( $show_date == 'show_date' || $show_author == 'show_author' || $show_author == 'show_comments' || $show_category == 'show_category' ) {
				
						// META
						$meta_output = '<div class="' . esc_attr( $this->shortcode ) . '_item_meta">';

							// DATE
							if ( $show_date == 'show_date' ) {
								$meta_output .= '<span class="' . esc_attr( $this->shortcode ) . '_item_date">';
									
									if ( $date_design_format_day != '' && $date_design_format_month != '' ) {
										$meta_output .= '<span class="' . esc_attr( $this->shortcode ) . '_item_date_day">';
											$meta_output .= get_the_date( $date_design_format_day, $post_item['ID'] );
										$meta_output .= '</span>';

										$meta_output .= '<span class="' . esc_attr( $this->shortcode ) . '_item_date_month">';
											$meta_output .= get_the_date( $date_design_format_month, $post_item['ID'] );
										$meta_output .= '</span>';
									} else {
										$meta_output .= get_the_date( $date_design_format, $post_item['ID'] );
									}  

								$meta_output .= '</span>';
							}

							if ( $show_author == 'show_author' ) {
								$meta_output .= '<span class="' . esc_attr( $this->shortcode ) . '_item_author">';
									$meta_output .= esc_html__( 'by', 'campo' ) . ' ' . $post_item['author'];
								$meta_output .= '</span>';
							}

							if ( $show_comments == 'show_comments' && $post_item['comments'] != '' ) {
								$meta_output .= '<span class="' . esc_attr( $this->shortcode ) . '_item_comments">';
									$meta_output .= $post_item['comments'];
								$meta_output .= '</span>';
							}

							if ( $show_category == 'show_category' && $post_item['comments'] != '' ) {
								$meta_output .= '<div class="' . esc_attr( $this->shortcode ) . '_item_category">';
									$meta_output .= $post_item['category_list'];
								$meta_output .= '</div>';
							}
				
						$meta_output .= '</div>';
		
						$output .= $meta_output;
					}

					// TITLE
					$output .= '<h5 class="' . esc_attr( $this->shortcode ) . '_item_title">';
						$output .= '<a href="' . esc_url_raw( $post_item['permalink'] ) . '" target="' . esc_attr( $target ) . '">' . $post_item['title'] . '</a>';
					$output .= '</h5>';
					
					// EXCERPT
					if ( $show_excerpt == 'show_excerpt' ) {
						$output .= '<div class="' . esc_attr( $this->shortcode ) . '_item_excerpt">';
							$output .= $post_item['excerpt'];
						$output .= '</div>';
					}

					// READ MORE
					if ( $read_more != '' ) {
						$output .= '<div class="bt_bb_latest_post_item_read_more bt_bb_button bt_bb_width_inline bt_bb_shape_inherit bt_bb_style_clean bt_bb_size_normal btWithLink btWithIcon bt_bb_icon_position_left">';
							$output .='<a href="' . esc_url_raw( $post_item['permalink'] ) . '" target="_self" class="bt_bb_link"><span class="bt_bb_button_text"> ' . ( $read_more ) . ' </span><span data-ico-dripicons="" class="bt_bb_icon_holder"></span></a>';
						$output .= '</div>';
					}

				$output .= '</div>';
				
			$output .= '</div>';
		}

		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . ' data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">' . $output . '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Latest Posts', 'campo' ), 'description' => esc_html__( 'List of latest posts', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'rows', 'type' => 'textfield', 'value' => '1', 'heading' => esc_html__( 'Rows', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'columns', 'type' => 'dropdown', 'value' => '3', 'heading' => esc_html__( 'Columns', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( '1', 'campo' ) => '1',
						esc_html__( '2', 'campo' ) => '2',
						esc_html__( '3', 'campo' ) => '3',
						esc_html__( '4', 'campo' ) => '4',
						esc_html__( '6', 'campo' ) => '6'
					)
				),
				array( 'param_name' => 'gap', 'type' => 'dropdown', 'heading' => esc_html__( 'Gap', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( 'Normal', 'campo' ) 	=> 'normal',
						esc_html__( 'No gap', 'campo' ) 	=> 'no_gap',
						esc_html__( 'Small', 'campo' ) 	=> 'small',
						esc_html__( 'Large', 'campo' ) 	=> 'large'
					)
				),				
				array( 'param_name' => 'category', 'type' => 'textfield', 'heading' => esc_html__( 'Category', 'campo' ), 'description' => esc_html__( 'Enter category slug or leave empty to show all', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'heading' => esc_html__( 'Target', 'campo' ),
					'value' => array(
						esc_html__( 'Self (open in same tab)', 'campo' ) 	=> '_self',
						esc_html__( 'Blank (open in new tab)', 'campo' ) 	=> '_blank',
					)
				),
				array( 'param_name' => 'show_category', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_category' ), 'heading' => esc_html__( 'Show category', 'campo' ), 'preview' => true
				),
				array( 'param_name' => 'show_date', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_date' ), 'heading' => esc_html__( 'Show date', 'campo' ), 'preview' => true
				),
				array( 'param_name' => 'show_author', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_author' ), 'heading' => esc_html__( 'Show author', 'campo' ), 'preview' => true
				),
				array( 'param_name' => 'show_comments', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_comments' ), 'heading' => esc_html__( 'Show number of comments', 'campo' ), 'preview' => true
				),
				array( 'param_name' => 'show_excerpt', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_excerpt' ), 'heading' => esc_html__( 'Show excerpt', 'campo' ), 'preview' => true
				),
				array( 'param_name' => 'read_more', 'type' => 'textfield', 'heading' => esc_html__( 'Read more text', 'campo' ) 
				),
				array( 'param_name' => 'lazy_load', 'type' => 'dropdown', 'default' => 'yes', 'heading' => esc_html__( 'Lazy load images', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 		=> 'no',
						esc_html__( 'Yes', 'campo' ) 	=> 'yes'
					)
				),



				array( 'param_name' => 'image_shape', 'type' => 'dropdown', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Image shape', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> 'square',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'round'
					)
				),
				array( 'param_name' => 'title_text_transform', 'type' => 'dropdown', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Title text transform', 'campo' ), 
					'value' => array(
						esc_html__( 'Default', 'campo' ) 			=> '',
						esc_html__( 'UPPERCASE', 'campo' ) 		=> 'uppercase',
						esc_html__( 'Capitalize', 'campo' ) 		=> 'capitalize',
						esc_html__( 'lowercase', 'campo' ) 		=> 'lowercase'
					)
				),
				array( 'param_name' => 'title_size', 'type' => 'dropdown', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Title size', 'campo' ), 
					'value' => array(
						esc_html__( 'Small', 'campo' ) 			=> '',
						esc_html__( 'Medium', 'campo' ) 			=> 'medium',
						esc_html__( 'Large', 'campo' ) 			=> 'large'
					)
				),
				array( 'param_name' => 'title_color_scheme', 'type' => 'dropdown', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Title color scheme', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Latest item shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> '',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft_rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard_rounded'
					)
				),
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Latest item color scheme', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),

				array( 'param_name' => 'border', 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Border', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 			=> '',
						esc_html__( 'Yes', 'campo' ) 			=> 'show'
					)
				),
				array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Gray color', 'campo' ) 			=> 'gray',
						esc_html__( 'Very light gray color', 'campo' ) => 'very_light_gray',
						esc_html__( 'Accent color', 'campo' ) 		=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 			=> 'light',
						esc_html__( 'Dark color', 'campo' ) 			=> 'dark'
					)
				),
				array( 'param_name' => 'title_lines', 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Title lines', 'campo' ), 'description' => esc_html__( 'Limit and show 1, 2, 3 or 4 lines of post title', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'All', 'campo' )		=> '',
						esc_html__( '4', 'campo' )		=> '4',
						esc_html__( '3', 'campo' )		=> '3',
						esc_html__( '2', 'campo' )		=> '2',
						esc_html__( '1', 'campo' )		=> '1'
					)
				),
			)
		) );
	} 
}