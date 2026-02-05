<?php

class bt_bb_css_post_grid extends BT_BB_Element {

	function __construct() {
		parent::__construct();
		add_action( 'wp_ajax_bt_bb_get_css_grid', array( __CLASS__, 'bt_bb_get_css_grid_callback' ) );
		add_action( 'wp_ajax_nopriv_bt_bb_get_css_grid', array( __CLASS__, 'bt_bb_get_css_grid_callback' ) );
	}
	
	static function bt_bb_get_css_grid_callback() {
		check_ajax_referer( 'bt-bb-css-post-grid-nonce', 'bt-bb-css-post-grid-nonce' );
		bt_bb_css_post_grid::dump_grid( 
				intval( $_POST['number'] ),
				intval( $_POST['offset'] ), 
				sanitize_text_field( urldecode( $_POST['category'] ) ),
				$_POST['show'],
				$_POST['show_superheadline'],
				$_POST['show_subheadline'], 
				$_POST['post-type'],
				$_POST['format']  
			);
		die();
	}

	static function dump_grid( $number, $offset, $category, $show, $show_superheadline, $show_subheadline, $post_type, $format ) {
		
		$show				= json_decode( urldecode( $show ), true );
		$show_superheadline = json_decode( urldecode( $show_superheadline ), true );
		$show_subheadline	= json_decode( urldecode( $show_subheadline ), true );

		$format_arr = explode( ',', $format );

		$output = '';

		$posts = bt_bb_get_posts( $number, $offset, $category, $post_type );
		
		$n = 0;

		foreach( $posts as $item ) { 
			$post_thumbnail_id = get_post_thumbnail_id( $item['ID'] ); 
			$img = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );
			$img_src = isset( $img[0] ) ? $img[0] : '';
			$hw = 0;
			if ( $img_src != '' ) {
				if ( isset($img[1]) && isset($img[2]) ){
					$hw = $img[2] / $img[1];
				}
			}
			$alt = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );
			$alt = $alt != '' ? $alt : $item['title'];

			if ( isset( $format_arr[ $n ] ) ) {
				$tile_format = 'bt_bb_tile_format';
				if ( $format_arr[ $n ] != '' ) {
					$tile_format .= '_' . esc_attr( $format_arr[ $n ] );
				} else {
					$tile_format .= '_11';
				}
			}

			$output .= '<div class="bt_bb_grid_item ' . $tile_format . '" data-hw="' . esc_attr( $hw ) . '" data-src="' . esc_url_raw( $img_src ) . '" data-alt="' . esc_attr( $alt ) . '" data-post-format="' . esc_attr( $item['format'] ) . '"><div class="bt_bb_grid_item_inner">';
				$output .= '<div class="bt_bb_grid_item_post_thumbnail"><a href="' . esc_url_raw( $item['permalink'] ) . '" title="' . esc_attr( $item['title'] ) . '"></a></div>';
				$output .= '<div class="bt_bb_grid_item_post_content">';

					if ( $show_superheadline['category'] || $show_superheadline['date'] || $show_superheadline['author'] || $show_superheadline['comments'] ) {
				
						$meta_output = '<div class="bt_bb_grid_item_meta bt_bb_grid_item_meta_superheadline">';

							if ( $show_superheadline['category'] && $item['category_list'] != '' ) {
								$meta_output .=  '<div class="bt_bb_grid_item_category bt_bb_grid_item_category_superheadline">';
									$meta_output .=  $item['category_list'];
								$meta_output .=  '</div>';
							}

							if ( $show_superheadline['date'] && $item['date'] != '' ) {
								$meta_output .= '<span class="bt_bb_grid_item_date">';
									$meta_output .= $item['date'];
								$meta_output .= '</span>';
							}

							if ( $show_superheadline['author'] && $item['author'] != '' ) {
								$meta_output .= '<span class="bt_bb_grid_item_item_author">';
									$meta_output .= esc_html__( 'by', 'campo' ) . ' ' . $item['author'];
								$meta_output .= '</span>';
							}

							if ( $show_superheadline['comments'] && $item['comments'] != '' ) {
								$meta_output .= '<span class="bt_bb_grid_item_item_comments">';
									$meta_output .= $item['comments'];
								$meta_output .= '</span>';
							}
				
						$meta_output .= '</div>';
		
						$output .= $meta_output;
		
					}

					$output .= '<h5 class="bt_bb_grid_item_post_title"><a href="' . esc_url_raw( $item['permalink'] ) . '" title="' . esc_attr( $item['title'] ) . '">' . $item['title'] . '</a></h5>';

					if ( $show['excerpt'] ) {
						$output .= '<div class="bt_bb_grid_item_post_excerpt">' . $item['excerpt'] . '</div>';
					}

					if ( $show_subheadline['category'] || $show_subheadline['date'] || $show_subheadline['author'] || $show_subheadline['comments'] ) {
				
						$meta_output = '<div class="bt_bb_grid_item_meta bt_bb_grid_item_meta_subheadline">';

							if ( $show_subheadline['category'] && $item['category_list'] != '' ) {
								$meta_output .=  '<div class="bt_bb_grid_item_category bt_bb_grid_item_category_subheadline">';
									$meta_output .= $item['category_list'];
								$meta_output .=  '</div>';
							}

							if ( $show_subheadline['date'] && $item['date'] != '' ) {
								$meta_output .= '<span class="bt_bb_grid_item_date">';
									$meta_output .= $item['date'];
								$meta_output .= '</span>';
							}

							if ( $show_subheadline['author'] && $item['author'] != '' ) {
								$meta_output .= '<span class="bt_bb_grid_item_item_author">';
									$meta_output .= esc_html__( 'by', 'campo' ) . ' ' . $item['author'];
								$meta_output .= '</span>';
							}

							if ( $show_subheadline['comments'] && $item['comments'] != '' ) {
								$meta_output .= '<span class="bt_bb_grid_item_item_comments">';
									$meta_output .= $item['comments'];
								$meta_output .= '</span>';
							}
				
						$meta_output .= '</div>';
		
						$output .= $meta_output;
		
					}

					if ( $show['share'] ) {
						$output .= '<div class="bt_bb_grid_item_post_share">' . $item['share'] . '</div>';
					}

				$output .= '</div></div>';
			$output .= '</div>';
			$n++;
		}
		
		$allowed = array(
			'a' => array(
				'class'       => true,
				'data-ico-fontawesome6brands' => true,
				'data-ico-fa' => true,
				'href'        => true,
				'rel'         => true,
				'title'       => true,
				'target'      => true,
			),
			'div' => array(
				'class'    => true,
				'data-hw'  => true,
				'data-src' => true,
				'data-alt' => true,
				'data-post-format' => true,
				'style'    => true,
			),
			'span' => array(
				'class' => true,
			),
			'img' => array(
				'src' => true,
				'alt' => true,
			),
			'h1' => array(
				
			),
			'h2' => array(
				
			),
			'h3' => array(
				
			),
			'h4' => array(
				
			),
			'h5' => array(
				'class' => true,
			),
			'h6' => array(
				
			),
			'ul' => array(
				'class' => true,
			),
			'li' => array(
				
			)
		);
		echo wp_kses( $output, $allowed );
	}


	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'post_type'					=> 'post',
			'initial_items_number'		=> '6',
			'auto_loading'				=> '',
			'columns'     				=> '3',
			'format'      				=> '',
			'gap'         				=> '',
			'shape'						=> 'inherit',
			'category'					=> '',
			'category_filter'			=> '',
			'title_html_tag'			=> '',
			'show_in_superheadline'		=> '',
			'show_in_subheadline'		=> '',
			'show_excerpt'				=> '',
			'show_share'				=> ''
		) ), $atts, $this->shortcode ) );

		wp_enqueue_script( 'jquery-masonry' );
		

		wp_enqueue_script( 
			'bt_bb_css_post_grid',
			get_template_directory_uri() . '/bold-page-builder/content_elements/bt_bb_css_post_grid/bt_bb_css_post_grid.js',
			array( 'jquery' )
		);
		
		wp_localize_script( 'bt_bb_css_post_grid', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		$class = array( $this->shortcode, 'bt_bb_grid_container' );
		$data_override_class = array();


		if ( $el_class != '' ) {
			$class[] = $el_class;
		}	

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		$style_attr = '';
		$el_style = apply_filters( $this->shortcode . '_style', $el_style, $atts );
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		if ( $columns != '' ) {
			$class[] = $this->prefix . 'columns' . '_' . $columns;
		}
		
		if ( $gap != '' ) {
			$class[] = $this->prefix . 'gap' . '_' . $gap;
		}

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $initial_items_number > 1000 || $initial_items_number == '' ) {
			$initial_items_number = 1000;
		} else if ( $initial_items_number < 1 ) {
			$initial_items_number = 1;
		}		

		$category = str_replace( ' ', '', $category );
		
		$show_in_superheadline = explode( ' ', $show_in_superheadline );
		$show_superheadline = array( 'category' => false, 'date' => false, 'author' => false, 'comments' => false );
		foreach ( $show_in_superheadline as $show_item ){
			$show_superheadline[$show_item] = true;
		}

		$show_in_subheadline = explode( ' ', $show_in_subheadline );
		$show_subheadline = array( 'category' => false, 'date' => false, 'author' => false, 'comments' => false );
		foreach ( $show_in_subheadline as $show_item ){
			$show_subheadline[$show_item] = true;
		}

		$show = array( 'excerpt' => false, 'share' => false );
		if ( $show_excerpt == 'show_excerpt' ) {
			$show['excerpt'] = true;
		}
		if ( $show_share == 'show_share' ) {
			$show['share'] = true;
		}
		
		$output = '';

		if ( $category_filter == 'yes' ) {
			if ( $post_type == 'post' ) {
				$cat_arr = get_categories();
				$cats = array();
				if ( $category != '' ) {
					$cat_slug_arr = explode( ',', $category );
					$cat_id_arr = get_terms( array( 'taxonomy' => 'category',  'fields' => 'ids' , 'slug' => $cat_slug_arr)  );
					foreach ( $cat_arr as $cat ) {
						if ( in_array( $cat->slug, $cat_slug_arr ) || in_array( $cat->parent, $cat_id_arr ) ) {
							$cats[] = $cat;
						}
					}
				} else {
					$cats = $cat_arr;
				}
			} else if ( $post_type == 'portfolio' ) {
				$cat_arr = get_terms( 'portfolio_category' );
				$cats = array();
				if ( ! is_wp_error( $cat_arr ) ) {
					if ( $category != '' ) {
						$cat_slug_arr = explode( ',', $category );
						foreach ( $cat_arr as $cat ) {
							if ( in_array( $cat->slug, $cat_slug_arr ) ) {
								$cats[] = $cat;
							}
						}
					} else {
						$cats = $cat_arr;
					}
				} else {
					$output .= $cat_arr->get_error_message();
				}
			}

			if ( ! is_wp_error( $cats ) ) {
				if ( count( $cats ) > 0 ) {
					$output .= '<div class="bt_bb_css_post_grid_filter">';
						$output .= '<span class="bt_bb_css_post_grid_filter_item active" data-category="' . esc_attr( $category ) . '">' . esc_html__( 'All', 'campo' ) . '</span>';
							foreach ( $cats as $cat ) {
								$output .= '<span class="bt_bb_css_post_grid_filter_item" data-category="' . esc_attr( $cat->slug ) . '">' . $cat->name . '</span>';
							}
					$output .= '</div>';
				}
			} else {
				$output .= $cats->get_error_message();
			}
		}

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

		$bt_bb_css_post_grid_nonce = wp_create_nonce( 'bt-bb-css-post-grid-nonce' );

		$output .= '<div class="bt_bb_css_post_grid_content bt_bb_grid_hide" data-bt-bb-css-post-grid-nonce="' . esc_attr( $bt_bb_css_post_grid_nonce ) . '" data-number="' . esc_attr( $initial_items_number ) . '" data-category="' . esc_attr( $category ) . '" data-show="' . esc_attr( urlencode( json_encode( $show ) ) ) . '" data-show-superheadline="' . esc_attr( urlencode( json_encode( $show_superheadline ) ) ) . '" data-show-subheadline="' . esc_attr( urlencode( json_encode( $show_subheadline ) ) ) . '" data-format="' . esc_attr( $format ) . '" data-post-type="' . esc_attr( $post_type ) . '" data-auto-loading="' . esc_attr( $auto_loading ) . '" data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">
		</div>';

		$output .= '<div class="bt_bb_post_grid_loader"></div>';

		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . ' data-columns="' . esc_attr( $columns ) . '" data-offset="0">' . $output . '</div>';

		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;
	}

	function map_shortcode() {

		$array = array();
		
		if ( post_type_exists( 'portfolio' ) ) {
			$array = array( array( 'param_name' => 'post_type', 'type' => 'dropdown', 'heading' => esc_html__( 'Post Type', 'campo' ), 'preview' => true,
				'value' => array(
					esc_html__( 'Post', 'campo' ) 		=> 'post',
					esc_html__( 'Portfolio', 'campo' ) 	=> 'portfolio',
				)
			) );
		}

		$array = array_merge( $array, array(
			array( 'param_name' => 'initial_items_number', 'type' => 'textfield', 'heading' => esc_html__( 'Inital number of items', 'campo' ), 'description' => esc_html__( 'Enter initial number of items or leave empty to show all (up to 1000)', 'campo' ), 'default' => '6', 'preview' => true ),
			array( 'param_name' => 'auto_loading', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'auto_loading' ), 'heading' => esc_html__( 'Load more items on scroll', 'campo' ), 'preview' => true
			),
			array( 'param_name' => 'columns', 'type' => 'dropdown', 'heading' => esc_html__( 'Columns', 'campo' ), 'default' => '3', 'preview' => true,
				'value' => array(
					esc_html__( '1', 'campo' ) 	=> '1',
					esc_html__( '2', 'campo' ) 	=> '2',
					esc_html__( '3', 'campo' ) 	=> '3',
					esc_html__( '4', 'campo' ) 	=> '4',
					esc_html__( '5', 'campo' ) 	=> '5',
					esc_html__( '6', 'campo' ) 	=> '6'
				)
			),
			array( 'param_name' => 'gap', 'type' => 'dropdown', 'heading' => esc_html__( 'Gap', 'campo' ),
				'value' => array(
					esc_html__( 'No gap', 'campo' )	 	=> 'no_gap',
					esc_html__( 'Small', 'campo' ) 		=> 'small',
					esc_html__( 'Normal', 'campo' ) 		=> 'normal',
					esc_html__( 'Large', 'campo' ) 		=> 'large'
				)
			),
			array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 			=> 'inherit',
						esc_html__( 'Square', 'campo' ) 			=> 'square',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'round'
					)
				),
			array( 'param_name' => 'format', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Tiles format', 'campo' ), 'description' => esc_html__( 'e.g. 21, 11, 11', 'campo' ) ),
			array( 'param_name' => 'category', 'type' => 'textfield', 'heading' => esc_html__( 'Category', 'campo' ), 'description' => esc_html__( 'Enter category slug or leave empty to show all', 'campo' ), 'preview' => true ),
			array( 'param_name' => 'category_filter', 'type' => 'dropdown', 'heading' => esc_html__( 'Category filter', 'campo' ),
				'value' => array(
					esc_html__( 'No', 'campo' ) 		=> 'no',
					esc_html__( 'Yes', 'campo' ) 		=> 'yes'
				)
			),
			array( 'param_name' => 'title_html_tag', 'type' => 'dropdown', 'heading' => esc_html__( 'Title HTML tag', 'campo' ), 'preview' => true,
				'value' => array(
					esc_html__( 'h1', 'campo' ) 	=> 'h1',
					esc_html__( 'h2', 'campo' ) 	=> 'h2',
					esc_html__( 'h3', 'campo' ) 	=> 'h3',
					esc_html__( 'h4', 'campo' ) 	=> 'h4',
					esc_html__( 'h5', 'campo' ) 	=> 'h5',
					esc_html__( 'h6', 'campo' ) 	=> 'h6'
			) ),
			array( 'param_name' => 'show_in_superheadline', 'type' => 'checkbox_group', 'heading' => esc_html__( 'Show in superheadline', 'campo' ),  'preview' => true,
				'value' => array(
					esc_html__( 'Category', 'campo' ) 	=> 'category',
					esc_html__( 'Date', 'campo' ) 		=> 'date',
					esc_html__( 'Author', 'campo' ) 		=> 'author',
					esc_html__( 'Comments', 'campo' ) 	=> 'comments'
				)
			),
			array( 'param_name' => 'show_in_subheadline', 'type' => 'checkbox_group', 'heading' => esc_html__( 'Show in subheadline', 'campo' ),  'preview' => true,
				'value' => array(
					esc_html__( 'Category', 'campo' ) 	=> 'category',
					esc_html__( 'Date', 'campo' ) 		=> 'date',
					esc_html__( 'Author', 'campo' ) 		=> 'author',
					esc_html__( 'Comments', 'campo' ) 	=> 'comments'
				)
			),
			array( 'param_name' => 'show_excerpt', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_excerpt' ), 'heading' => esc_html__( 'Show excerpt', 'campo' ), 'preview' => true
			),
			array( 'param_name' => 'show_share', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'show_share' ), 'heading' => esc_html__( 'Show share icons', 'campo' ), 'preview' => true 
			)
		) );

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'CSS Post Grid', 'campo' ), 'description' => esc_html__( 'CSS post grid with images', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => $array
		) );

	}
}