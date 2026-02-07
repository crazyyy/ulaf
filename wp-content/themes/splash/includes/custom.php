<?php
function stm_set_html_content_type() {
	return 'text/html';
}

// Add svg support
function splash_svg_mime( $mimes ) {
	$mimes['ico'] = 'image/icon';
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}

add_filter( 'upload_mimes', 'splash_svg_mime' );

function splash_sanitize_text_field( $text ) {
	return apply_filters( 'splash_sanitize_text_field', $text );
}

if ( ! function_exists( 'splash_socials' ) ) {
	function splash_socials( $socials_pos = 'top_bar_socials' ) {
		$socials_array = array();

		$header_socials_enable = explode( ',', get_theme_mod( $socials_pos ) );
		$socials               = get_theme_mod( 'socials_link' );
		$socials_values        = array();
		if ( ! empty( $socials ) ) {
			parse_str( $socials, $socials_values );
		}

		if ( $header_socials_enable ) {
			foreach ( $header_socials_enable as $social ) {
				if ( ! empty( $socials_values[ $social ] ) ) {
					$socials_array[ $social ] = $socials_values[ $social ];
				}
			}
		}

		return $socials_array;
	}
}


function splash_check_plugin_active( $slug ) {
	/*if just slug*/
	if ( strpos( $slug, '.php' ) === false ) {
		$slug = splash_get_plugin_main_path( $slug );
	}

	return in_array( $slug, (array) get_option( 'active_plugins', array() ), true ) || is_plugin_active_for_network( $slug );
}

function splash_activate_plugin( $slug ) {
	activate_plugin( splash_get_plugin_main_path( $slug ) );
}

function splash_get_plugin_main_path( $slug ) {
	$plugin_data = get_plugins( '/' . $slug );
	if ( ! empty( $plugin_data ) ) {
		$plugin_file = array_keys( $plugin_data );
		$plugin_path = $slug . '/' . $plugin_file[0];
	} else {
		$plugin_path = false;
	}

	return $plugin_path;
}


if ( ! function_exists( 'splash_generate_inline_style' ) ) {
	function splash_generate_inline_style( $styles ) {
		$return = '';
		if ( ! empty( $styles ) ) {
			$return = 'style="';
			foreach ( $styles as $style_name => $style_value ) {
				if ( ! empty( $style_value ) ) {
					$return .= $style_name . ':' . $style_value . ' !important;';
				}
			}
			$return .= '"';
		}

		return $return;
	}
}

if ( ! function_exists( 'splash_top_bar_styles' ) ) {
	function splash_top_bar_styles() {
		$color      = get_theme_mod( 'top_bar_text_color' );
		$custom_css = '';
		if ( ! empty( $color ) ) {
			$custom_css = "#stm-top-bar .heading-font, #stm-top-bar a {
				color: {$color};
			}";
		};
		wp_add_inline_style( 'stm-theme-style', $custom_css );
	}

	add_action( 'wp_enqueue_scripts', 'splash_top_bar_styles' );
}

if ( ! function_exists( 'splash_hex2rgb' ) ) {
	function splash_hex2rgb( $colour ) {
		if ( '#' === $colour[0] ) {
			$colour = substr( $colour, 1 );
		}
		if ( 6 === strlen( $colour ) ) {
			list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
		} elseif ( 3 === strlen( $colour ) ) {
			list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
		} else {
			return false;
		}
		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		return $r . ',' . $g . ',' . $b;
	}
}

if ( ! function_exists( 'splash_hex2rgba' ) ) {
	function splash_hex2rgba( $colour ) {
		if ( ! empty( $colour ) ) {
			if ( '#' === $colour[0] ) {
				$colour = substr( $colour, 1 );
			}
			if ( 6 === strlen( $colour ) ) {
				list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
			} elseif ( 3 === strlen( $colour ) ) {
				list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
			} else {
				return false;
			}
			$r = hexdec( $r );
			$g = hexdec( $g );
			$b = hexdec( $b );

			return $r . ',' . $g . ',' . $b . ', 1';
		}

		return '';
	}
}

if ( ! function_exists( 'splash_body_class' ) ) {
	function splash_body_class( $classes ) {

		global $wp_customize;

		if ( isset( $wp_customize ) ) {
			$classes[] = 'stm-customize-page';
		}

		$boxed    = get_theme_mod( 'site_boxed', false );
		$bg_image = get_theme_mod( 'bg_image', false );

		if ( $boxed ) {
			$classes[] = 'stm-boxed';
			if ( $bg_image ) {
				$classes[] = $bg_image;
			}
		}

		$shop_sidebar_id = get_theme_mod( 'shop_sidebar', 'primary_sidebar' );
		if ( 'no_sidebar' !== $shop_sidebar_id ) {
			$classes[] = 'stm-shop-sidebar';
		}

		return $classes;
	}
}

add_filter( 'body_class', 'splash_body_class' );


$preloader = get_theme_mod( 'preloader', false );

if ( ! empty( $preloader ) && $preloader ) {
	function preloader_body_class( $classes ) {
		$classes[] = 'preloader';

		return $classes;
	}

	add_filter( 'body_class', 'preloader_body_class' );
}


if ( ! function_exists( 'splash_get_thumbnail_url' ) ) {
	function splash_get_thumbnail_url( $post_id = 0, $image_id = 0, $image_size = 'stm-85-105' ) {
		$return = '';
		if ( ! $image_id ) {
			$image = get_post_thumbnail_id( $post_id );
		} else {
			$image = $image_id;
		}
		if ( ! empty( $image ) ) {
			$image = wp_get_attachment_image_src( $image, $image_size );
			if ( ! empty( $image[0] ) ) {
				$return = $image[0];
			}
		}

		return $return;
	}
}

if ( ! function_exists( 'splash_get_sportpress_points_system' ) ) {
	function splash_get_sportpress_points_system() {
		$points = 'points';
		if ( get_option( 'sportspress_primary_result' ) !== null ) {
			$points = get_option( 'sportspress_primary_result' );
		}

		return $points;
	}
}

if ( ! function_exists( 'splash_pagination' ) ) {
	function splash_pagination() {
		echo wp_kses_post(
			paginate_links(
				array(
					'type'      => 'list',
					'prev_text' => '<i class="fa fa-angle-left"></i>',
					'next_text' => '<i class="fa fa-angle-right"></i>',
				)
			)
		);
	}
}

if ( ! function_exists( 'splash_pages_pagination' ) ) {
	function splash_pages_pagination() {
		wp_link_pages(
			array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'splash' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'splash' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			)
		);
	}
}

//Sidebar layout
if ( ! function_exists( 'splash_sidebar_layout_mode' ) ) {
	function splash_sidebar_layout_mode( $position = 'left', $sidebar_id = false ) {
		$content_before = '';
		$content_after  = '';
		$sidebar_before = '';
		$sidebar_after  = '';
		$show_title     = '';
		$default_row    = '';
		$default_col    = '';

		if ( 'post' === get_post_type() ) {
			if ( ! empty( $_GET['show-title-box'] ) && 'hide' === $_GET['show-title-box'] ) {
				$blog_archive_id = get_option( 'page_for_posts' );
				if ( ! empty( $blog_archive_id ) ) {

					$get_the_title = get_the_title( $blog_archive_id );

					if ( ! empty( $get_the_title ) ) {
						$show_title = '<h2 class="stm-blog-main-title">' . $get_the_title . '</h2>';
					}
				}
			}
		}

		if ( ! $sidebar_id ) {
			$content_before .= '<div class="col-md-12">';

			$content_after .= '</div>';

			$default_row = 3;
			$default_col = 'col-md-4 col-sm-4 col-xs-12';
		} else {
			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$shop_archive = get_option( 'woocommerce_shop_page_id' );
				$post_id      = get_option( 'woocommerce_shop_page_id' );
			}

			$classAF   = ( ! splash_is_layout( 'bb' ) && ! splash_is_layout( 'hockey' ) ) ? 'af-margin-88' : '';
			$classShop = ( ! empty( $shop_archive ) && $shop_archive === $post_id ) ? 'af-margin-0' : '';
			$contentW  = ( splash_is_layout( 'magazine_one' ) || splash_is_layout( 'hockey' ) || splash_is_layout( 'esport' ) ) ? '8' : '9';
			$sidebarW  = ( splash_is_layout( 'magazine_one' ) || splash_is_layout( 'hockey' ) || splash_is_layout( 'esport' ) ) ? '4' : '3';

			if ( 'right' === $position ) {
				$content_before .= '<div class="col-md-' . $contentW . ' col-sm-12 col-xs-12"><div class="sidebar-margin-top clearfix"></div>';
				$sidebar_before .= '<div class="col-md-' . $sidebarW . ' hidden-sm hidden-xs ' . $classAF . ' ' . $classShop . '">';

				$sidebar_after .= '</div>';
				$content_after .= '</div>';
			} elseif ( 'left' === $position ) {
				$content_before .= '<div class="col-md-' . $contentW . ' col-md-push-' . $sidebarW . ' col-sm-12"><div class="sidebar-margin-top clearfix"></div>';
				$sidebar_before .= '<div class="col-md-' . $sidebarW . ' col-md-pull-' . $contentW . ' hidden-sm hidden-xs ' . $classAF . '">';

				$sidebar_after .= '</div>';
				$content_after .= '</div>';
			}
			$default_row = 2;
			$default_col = 'col-md-6 col-sm-6 col-xs-12';
		}

		$return                   = array();
		$return['content_before'] = $content_before;
		$return['content_after']  = $content_after;
		$return['sidebar_before'] = $sidebar_before;
		$return['sidebar_after']  = $sidebar_after;
		$return['show_title']     = $show_title;
		$return['default_row']    = $default_row;
		$return['default_col']    = $default_col;

		return $return;
	}
}

if ( ! function_exists( 'splash_get_sidebar_settings' ) ) {
	function splash_get_sidebar_settings( $sidebar = 'sidebar', $sidebar_pos = 'sidebar_position', $sidebar_default = 'primary_sidebar', $sidebar_pos_default = 'left' ) {
		$sidebar_id       = get_theme_mod( $sidebar, $sidebar_default );
		$sidebar_position = get_theme_mod( $sidebar_pos, $sidebar_pos_default );

		$blog_sidebar = 0;

		if ( ! empty( $_GET['sidebar-position'] ) && 'left' === $_GET['sidebar-position'] ) {
			$sidebar_position = 'left';
		}

		if ( ! empty( $_GET['sidebar-position'] ) && 'right' === $_GET['sidebar-position'] ) {
			$sidebar_position = 'right';
		}

		if ( ! empty( $_GET['sidebar-position'] ) && 'none' === $_GET['sidebar-position'] ) {
			$sidebar_id = false;
		}

		if ( 'no_sidebar' === $sidebar_id ) {
			$sidebar_id = false;
		}

		$view_type   = get_theme_mod( 'view_type', 'grid' );
		$grid_column = get_theme_mod( 'news_grid_columns', 3 );

		if ( ! empty( $_GET['view-type'] ) && 'grid' === $_GET['view-type'] ) {
			$view_type = 'grid';
		}

		if ( ! empty( $_GET['view-type'] ) && 'list' === $_GET['view-type'] ) {
			$view_type = 'list';
		}

		if ( ! empty( $sidebar_id ) ) {
			$blog_sidebar = get_post( $sidebar_id );
		}

		$response = array(
			'id'           => $sidebar_id,
			'position'     => $sidebar_position,
			'view_type'    => $view_type,
			'grid_column'  => $grid_column,
			'blog_sidebar' => $blog_sidebar,
		);

		return $response;
	}
}

function splash_categories_empty_title( $title = '', $instance = '', $base = '' ) {
	if ( 'categories' === $base ) {
		if ( isset( $instance['title'] ) && '' === trim( $instance['title'] ) ) {
			return '';
		}
	}

	return $title;
}

add_filter( 'widget_title', 'splash_categories_empty_title', 10, 3 );

if ( ! function_exists( 'splash_theme_comment' ) ) {
	function splash_theme_comment( $comment, $args, $depth ) {
		if ( 'div' === $args['style'] ) {
			$tag       = 'div';
			$add_below = 'comment';
		} else {
			$tag       = 'li ';
			$add_below = 'div-comment';
		}
		?>
		<<?php echo wp_kses_post( $tag ); ?><?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
		<?php if ( 'div' !== $args['style'] ) : ?>
			<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
		<?php endif; ?>

		<div class="clearfix">

			<div class="comment-author-image">
				<?php
				if ( 0 !== $args['avatar_size'] ) {
					echo get_avatar( $comment, $args['avatar_size'] );
				}
				?>
			</div>

			<div class="comment-author vcard">
				<span
						class="comment-author heading-font"><?php echo wp_kses_post( get_comment_author_link() ); ?></span>
				<span class="comment-meta commentmetadata">
					<span class="date <?php echo ( splash_is_layout( 'bb' ) ) ? 'heading-font' : 'normal_font'; ?>"><?php echo esc_html( get_comment_date() ); ?></span>
				</span>
				<?php if ( '0' === $comment->comment_approved ) : ?>
					<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'splash' ); ?></em>
					<br/>
				<?php endif; ?>
				<?php comment_text(); ?>

				<div class="reply">
					<i class="fa fa-mail-reply"></i>
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => $add_below,
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
							)
						)
					);
					?>
				</div>
			</div>
		</div>


		<?php if ( 'div' !== $args['style'] ) : ?>
			</div>
			<?php
		endif;
	}
}

add_filter( 'comment_form_default_fields', 'splash_bootstrap3_comment_form_fields' );

if ( ! function_exists( 'splash_bootstrap3_comment_form_fields' ) ) {
	function splash_bootstrap3_comment_form_fields( $fields ) {
		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );
		$aria_req  = ( $req ? " aria-required='true'" : '' );
		$html5     = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;
		$fields    = array(
			'author' => '<div class="row stm-row-comments">
							<div class="col-md-4 col-sm-4 col-xs-12">
								<div class="form-group comment-form-author">
			            			<input placeholder="' . esc_attr__( 'Name', 'splash' ) . ( $req ? ' *' : '' ) . '" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />
		                        </div>
		                    </div>',
			'email'  => '<div class="col-md-4 col-sm-4 col-xs-12">
							<div class="form-group comment-form-email">
								<input placeholder="' . esc_attr__( 'Email', 'splash' ) . ( $req ? ' *' : '' ) . '" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />
							</div>
						</div>',
			'url'    => '<div class="col-md-4 col-sm-4 col-xs-12">
						<div class="form-group comment-form-url">
							<input placeholder="' . esc_attr__( 'Website', 'splash' ) . '" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />
						</div>
					</div></div>',
		);

		return $fields;
	}
}

add_filter( 'comment_form_defaults', 'splash_bootstrap3_comment_form' );

if ( ! function_exists( 'splash_bootstrap3_comment_form' ) ) {
	function splash_bootstrap3_comment_form( $args ) {
		$args['comment_field'] = '<div class="form-group comment-form-comment">
			<textarea placeholder="' . esc_attr_x( 'Comment', 'noun', 'splash' ) . ' *" name="comment" rows="9" aria-required="true"></textarea>
	    </div>';

		return $args;
	}
}

function splash_filter_comment_form_submit_button( $submit_button, $args ) {
	// make filter magic happen here...
	$submit_before = '';
	$submit_after  = '';
	if ( ! splash_is_layout( 'bb' ) && ! splash_is_layout( 'rugby' ) ) {
		$submit_before = '<span class="button btn-md">';
		$submit_after  = '</span>';
	}

	return $submit_before . $submit_button . $submit_after;
}


add_filter( 'comment_form_submit_button', 'splash_filter_comment_form_submit_button', 10, 2 );

if ( ! function_exists( 'splash_donors_text' ) ) {
	function splash_donors_text( $post_id, $getProcent = false ) {

		$raised = get_post_meta( $post_id, 'raised_money', true );
		$donors = get_post_meta( $post_id, 'donors', true );
		$goal   = get_post_meta( $post_id, 'goal', true );

		if ( ! $getProcent ) {

			if ( empty( $raised ) ) {
				$raised = '0';
			}

			if ( empty( $donors ) ) {
				$donors = '0';
			}

			if ( empty( $goal ) ) {
				$goal = '0';
			}

			$raised_label   = get_theme_mod( 'donation_raised', esc_html__( 'Raised', 'splash' ) );
			$donors_label   = get_theme_mod( 'donation_donors', esc_html__( 'Donors', 'splash' ) );
			$goal_label     = get_theme_mod( 'donation_goal', esc_html__( 'Goal', 'splash' ) );
			$currency_label = get_theme_mod( 'donation_currency', esc_html__( '$', 'splash' ) );

			$response  = '';
			$response .= '<div class="heading-font">';
			$response .= '<span class="stm-red">' . $raised_label . '</span> ' . $currency_label . $raised;
			$response .= '</div>';
			$response .= '<div class="heading-font">';
			$response .= '<span class="stm-red">' . $donors_label . '</span> ' . $donors;
			$response .= '</div>';
			$response .= '<div class="heading-font">';
			$response .= '<span class="stm-red">' . $goal_label . '</span> ' . $currency_label . $goal;
			$response .= '</div>';

			echo wp_kses_post( $response );
		} else {
			$procent = 0;
			if ( ! empty( $raised ) && ! empty( $goal ) ) {
				$total   = ( $raised * 100 ) / $goal;
				$procent = round( $total, 1 );
			}

			return $procent;
		}
	}
}

/*Show sportpress future events content for everyone*/
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() && $query->is_main_query() && in_array( $query->get( 'post_type' ), array( 'sp_event' ), true ) ) {
			$query->set( 'post_status', array( 'publish', 'future' ) );
		}
	}
);

function splash_get_search_form( $form ) {
	$form  = '<form method="get" action="' . home_url( '/' ) . '">';
	$form .= '<div class="search-wrapper">';
	$form .= '<input ';
	$form .= 'placeholder="' . esc_attr__( 'Search', 'splash' ) . '" type="text"';
	$form .= ' class="search-input"';
	$form .= ' value="' . get_search_query() . '" name="s" />';
	$form .= '</div>';
	$form .= '<button type="submit" class="search-submit" ><i class="fa fa-search"></i></button>';
	$form .= '</form>';

	return $form;
}

add_action( 'get_search_form', 'splash_get_search_form' );


function splash_import_widgets( $widget_data ) {
	$json_data = $widget_data;
	$json_data = json_decode( $json_data, true );

	$sidebar_data = $json_data[0];
	$widget_data  = $json_data[1];

	$menu_object = wp_get_nav_menu_object( 'Widget menu' );

	if ( ! empty( $menu_object ) && ! empty( $menu_object->term_id ) && ! empty( $widget_data['nav_menu'] ) && ! empty( $widget_data['nav_menu'][2] ) && ! empty( $widget_data['nav_menu'][2]['nav_menu'] ) ) {
		$widget_data['nav_menu'][2]['nav_menu'] = $menu_object->term_id;
	}

	foreach ( $widget_data as $widget_data_title => $widget_data_value ) {
		$widgets[ $widget_data_title ] = array();
		foreach ( $widget_data_value as $widget_data_key => $widget_data_array ) {
			if ( is_int( $widget_data_key ) ) {
				$widgets[ $widget_data_title ][ $widget_data_key ] = 'on';
			}
		}
	}
	unset( $widgets[''] );

	foreach ( $sidebar_data as $title => $sidebar ) {
		$count = count( $sidebar );
		for ( $i = 0; $i < $count; $i ++ ) {
			$widget               = array();
			$widget['type']       = trim( substr( $sidebar[ $i ], 0, strrpos( $sidebar[ $i ], '-' ) ) );
			$widget['type-index'] = trim( substr( $sidebar[ $i ], strrpos( $sidebar[ $i ], '-' ) + 1 ) );
			if ( ! isset( $widgets[ $widget['type'] ][ $widget['type-index'] ] ) ) {
				unset( $sidebar_data[ $title ][ $i ] );
			}
		}
		$sidebar_data[ $title ] = array_values( $sidebar_data[ $title ] );
	}

	foreach ( $widgets as $widget_title => $widget_value ) {
		foreach ( $widget_value as $widget_key => $widget_value ) {
			$widgets[ $widget_title ][ $widget_key ] = $widget_data[ $widget_title ][ $widget_key ];
		}
	}

	$sidebar_data = array( array_filter( $sidebar_data ), $widgets );

	splash_widget_parse_import_data( $sidebar_data );
}

function splash_widget_parse_import_data( $import_array ) {
	global $wp_registered_sidebars;
	$sidebars_data    = $import_array[0];
	$widget_data      = $import_array[1];
	$current_sidebars = get_option( 'sidebars_widgets' );
	$new_widgets      = array();

	foreach ( $sidebars_data as $import_sidebar => $import_widgets ) :

		foreach ( $import_widgets as $import_widget ) :
			//if the sidebar exists
			if ( isset( $wp_registered_sidebars[ $import_sidebar ] ) ) :
				$title               = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
				$index               = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
				$current_widget_data = get_option( 'widget_' . $title );
				$new_widget_name     = splash_get_new_widget_name( $title, $index );
				$new_index           = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );

				if ( ! empty( $new_widgets[ $title ] ) && is_array( $new_widgets[ $title ] ) ) {
					while ( array_key_exists( $new_index, $new_widgets[ $title ] ) ) {
						$new_index ++;
					}
				}
				$current_sidebars[ $import_sidebar ][] = $title . '-' . $new_index;
				if ( array_key_exists( $title, $new_widgets ) ) {
					$new_widgets[ $title ][ $new_index ] = $widget_data[ $title ][ $index ];
					$multiwidget                         = $new_widgets[ $title ]['_multiwidget'];
					unset( $new_widgets[ $title ]['_multiwidget'] );
					$new_widgets[ $title ]['_multiwidget'] = $multiwidget;
				} else {
					$current_widget_data[ $new_index ] = $widget_data[ $title ][ $index ];
					$current_multiwidget               = isset( $current_widget_data['_multiwidget'] ) ? $current_widget_data['_multiwidget'] : false;
					$new_multiwidget                   = isset( $widget_data[ $title ]['_multiwidget'] ) ? $widget_data[ $title ]['_multiwidget'] : false;
					$multiwidget                       = ( $current_multiwidget !== $new_multiwidget ) ? $current_multiwidget : 1;
					unset( $current_widget_data['_multiwidget'] );
					$current_widget_data['_multiwidget'] = $multiwidget;
					$new_widgets[ $title ]               = $current_widget_data;
				}

			endif;
		endforeach;
	endforeach;

	if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
		update_option( 'sidebars_widgets', $current_sidebars );

		foreach ( $new_widgets as $title => $content ) {
			update_option( 'widget_' . $title, $content );
		}

		return true;
	}

	return false;
}

function splash_get_new_widget_name( $widget_name, $widget_index ) {
	$current_sidebars = get_option( 'sidebars_widgets' );
	$all_widget_array = array();
	foreach ( $current_sidebars as $sidebar => $widgets ) {
		if ( ! empty( $widgets ) && is_array( $widgets ) && 'wp_inactive_widgets' !== $sidebar ) {
			foreach ( $widgets as $widget ) {
				$all_widget_array[] = $widget;
			}
		}
	}
	while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array, true ) ) {
		$widget_index ++;
	}
	$new_widget_name = $widget_name . '-' . $widget_index;

	return $new_widget_name;
}

if ( ! function_exists( 'splash_skin_custom' ) ) {
	function splash_skin_custom() {
		$site_color = get_theme_mod( 'site_style', 'default' );

		if ( 'site_style_custom' === $site_color ) {
			global $wp_filesystem;

			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}

			$custom_style_css  = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/styles.css' );
			$custom_layout_css = '';

			$main_color_bb     = '#e21e22';
			$main_color_af     = '#06083b';
			$main_color_sccs   = '#00aaff';
			$second_color_sccs = '#039ce9';
			$second_color_bsbl = '#a10a0d';
			$def_hex_bb        = '226, 30, 34';
			$second_color_bb   = '#da9a29';
			$main_color_hc     = '#f53837';
			$second_color_hc   = '#1f3b79';
			$main_color        = $main_color_bb;
			$second_color      = $second_color_bb;
			$def_hex           = $def_hex_bb;

			if ( splash_is_layout( 'af' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/american_football_styles.css' );
				$main_color        = '#06083b';
				$second_color      = '#da9a29';
				$def_hex           = '6, 8, 59';
			} elseif ( splash_is_layout( 'sccr' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/soccer_styles.css' );
				$main_color        = '#00aaff';
				$second_color      = '#039ce9';
				$def_hex           = '0, 170, 255';
			} elseif ( splash_is_layout( 'baseball' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/baseball_styles.css' );
				$main_color        = '#e21e22';
				$second_color      = '#a10a0d';
				$def_hex           = '0, 170, 255';
			} elseif ( splash_is_layout( 'magazine_two' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/magazine_two_styles.css' );
				$main_color        = '#e21e22';
				$second_color      = '#d99a29';
			} elseif ( splash_is_layout( 'soccer_two' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/soccer_two_styles.css' );
				$main_color        = '#f20600';
				$second_color      = '#039ce9';
			} elseif ( splash_is_layout( 'soccer_news' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/soccer_news_styles.css' );
				$main_color        = '#e700fc';
				$second_color      = '#ebff00';
			} elseif ( splash_is_layout( 'basketball_two' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/basketball_two.css' );
				$main_color        = '#ffba00';
				$second_color      = '#552085';
				$def_hex           = '6, 8, 59';
			} elseif ( splash_is_layout( 'hockey' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/hockey.css' );
				$main_color        = '#f53837';
				$second_color      = '#1f3b79';
				$def_hex           = '6, 8, 59';
			} elseif ( splash_is_layout( 'esport' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/esport.css' );
				$main_color        = '#00a8ff';
				$second_color      = '#ffb400';
			} elseif ( splash_is_layout( 'volleyball' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/volleyball.css' );
				$main_color        = '#ff573b';
				$second_color      = '#142978';
				$def_hex           = '129,215,67';
			} elseif ( splash_is_layout( 'rugby' ) ) {
				$custom_layout_css = $wp_filesystem->get_contents( get_template_directory() . '/assets/css/rugby.css' );
				$main_color        = '#d50029';
				$second_color      = '#24293e';
			}

			$base_color      = get_theme_mod( 'site_style_base_color', $main_color );
			$secondary_color = get_theme_mod( 'site_style_secondary_color', $second_color );

			$colors_arr         = array();
			$colors_arr[]       = $base_color;
			$colors_differences = false;

			$custom_style_css = str_replace(
				array(
					$main_color_hc,
					$second_color_hc,
					$main_color_bb,
					$def_hex_bb,
					$main_color_bb,
					$second_color_bb,
					$second_color,
					'../',
					$main_color_af,
					$main_color_sccs,
					$second_color_sccs,
					$second_color_bsbl,
				),
				array(
					$base_color,
					$secondary_color,
					$base_color,
					splash_hex2rgb( $base_color ),
					$base_color,
					$secondary_color,
					$secondary_color,
					get_template_directory_uri() . '/assets/',
					$base_color,
					$base_color,
					$secondary_color,
					$secondary_color,
				),
				$custom_style_css
			);

			if ( ! empty( $custom_layout_css ) ) {
				$custom_layout_css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $custom_layout_css );
				$custom_layout_css = str_replace(
					array(
						$main_color,
						$def_hex,
						$second_color,
						'../',
					),
					array(
						$base_color,
						splash_hex2rgb( $base_color ),
						$secondary_color,
						get_template_directory_uri() . '/assets/',
					),
					$custom_layout_css
				);

			}

			$upload_dir = wp_upload_dir();

			if ( ! is_dir( $upload_dir['basedir'] . '/stm_uploads' ) ) {
				wp_mkdir_p( $upload_dir['basedir'] . '/stm_uploads' );
			}

			if ( $custom_style_css ) {
				$css_to_filter = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $custom_style_css );
				$css_to_filter = str_replace(
					array(
						'\r\n',
						'\r',
						'\n',
						'\t',
						'  ',
						'    ',
						'    ',
					),
					'',
					$css_to_filter
				);

				$custom_style_file = $upload_dir['basedir'] . '/stm_uploads/skin-custom.css';

				if ( $custom_style_file ) {
					$custom_style_content = $wp_filesystem->get_contents( $custom_style_file );

					if ( is_array( $colors_arr ) && ! empty( $colors_arr ) ) {
						foreach ( $colors_arr as $color ) {
							$color_find = strpos( $custom_style_content, $color );
							if ( ! $color_find && ! $colors_differences ) {
								$colors_differences = true;
							}
						}
					}

					if ( $colors_differences ) {
						$wp_filesystem->put_contents( $custom_style_file, $css_to_filter, FS_CHMOD_FILE );
					}
				} else {
					$wp_filesystem->put_contents( $custom_style_file, $css_to_filter, FS_CHMOD_FILE );
				}
			}

			if ( $custom_layout_css ) {
				$css_to_filter = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $custom_layout_css );
				$css_to_filter = str_replace(
					array(
						'\r\n',
						'\r',
						'\n',
						'\t',
						'  ',
						'    ',
						'    ',
					),
					'',
					$css_to_filter
				);

				$custom_style_file = $upload_dir['basedir'] . '/stm_uploads/skin-custom-layout.css';

				if ( $custom_style_file ) {
					$custom_style_content = $wp_filesystem->get_contents( $custom_style_file );

					if ( is_array( $colors_arr ) && ! empty( $colors_arr ) ) {
						foreach ( $colors_arr as $color ) {
							$color_find = strpos( $custom_style_content, $color );
							if ( ! $color_find && ! $colors_differences ) {
								$colors_differences = true;
							}
						}
					}

					if ( $colors_differences ) {
						$wp_filesystem->put_contents( $custom_style_file, $css_to_filter, FS_CHMOD_FILE );
					}
				} else {
					$wp_filesystem->put_contents( $custom_style_file, $css_to_filter, FS_CHMOD_FILE );
				}
			}
		}
	}
}

add_action( 'customize_save_after', 'splash_skin_custom', 20 );

if ( ! function_exists( 'splash_print_styles' ) ) {
	function splash_print_styles() {
		$front_css = '';

		/*Boxed BG*/
		$site_boxed        = get_theme_mod( 'site_boxed' );
		$bg_image          = get_theme_mod( 'bg_image' );
		$custom_bg_image   = get_theme_mod( 'custom_bg_image' );
		$custom_bg_pattern = get_theme_mod( 'custom_bg_pattern' );
		if ( $site_boxed ) {

			if ( empty( $custom_bg_image ) && empty( $custom_bg_pattern ) ) {
				$front_css .= '
					body.stm-boxed {
						background-image: url( ' . get_template_directory_uri() . '/assets/images/tmp/box_img_5.png );
					}
				';
			}

			if ( ! empty( $bg_image ) ) {
				$box_images = array(
					'5' => 'box_img_5.png',
					'1' => 'box_img_1.jpg',
					'2' => 'box_img_2.jpg',
					'3' => 'box_img_3.jpg',
					'4' => 'box_img_4.jpg',
				);

				if ( ! empty( $box_images[ $bg_image ] ) ) {
					$front_css .= '
						body.stm-boxed {
							background-image: url( ' . get_template_directory_uri() . '/assets/images/tmp/' . $box_images[ $bg_image ] . ' );
							background-attachment: fixed;
						}
					';
				}
			}

			if ( ! empty( $custom_bg_image ) ) {
				$front_css .= '
					body.stm-boxed {
						background-image: url( ' . esc_url( $custom_bg_image ) . ' );
						background-attachment: fixed;
						background-size:cover;
					}
				';
			} elseif ( ! empty( $custom_bg_pattern ) ) {
				$front_css .= '
					body.stm-boxed {
						background-image: url( ' . esc_url( $custom_bg_pattern ) . ' );
						background-repeat: repeat;
					}
				';
			}
		} else {
			if ( splash_is_layout( 'esport' ) ) {

				if ( ! empty( $custom_bg_pattern ) ) {
					$front_css .= '
					#wrapper {
						background: url( ' . esc_url( $custom_bg_pattern ) . ' ) no-repeat;
						background-size: cover;
						background-attachment: fixed;
					}
				';
				}
			}
		}

		/*Remove page bottom padding after content*/
		$no_page_padding = get_post_meta( get_the_ID(), 'no_page_padding', true );
		$style_opts      = array();
		if ( ! empty( $no_page_padding ) && 'on' === $no_page_padding ) {
			$front_css .= '
					#main {
						padding: 0 !important;
					}
				';
		}

		/*Custom CSS*/
		$custom_css = get_theme_mod( 'custom_css' );

		if ( ! empty( $custom_css ) ) {
			$front_css .= preg_replace( '/\s+/', ' ', $custom_css );
		}

		wp_add_inline_style( 'stm-theme-default-styles', $front_css );
	}
}

add_action( 'wp_enqueue_scripts', 'splash_print_styles' );

// Remove [...] from excerpt
add_filter( 'excerpt_more', 'splash_excerpt_more' );
function splash_excerpt_more( $more ) {
	return '...';
}

//Add empty gravatar
function splash_default_avatar( $avatar_defaults ) {
	$stm_avatar                     = get_template_directory_uri() . '/assets/images/gravataricon.png';
	$avatar_defaults[ $stm_avatar ] = esc_html__( 'Splash Default Avatar', 'splash' );

	return $avatar_defaults;
}

add_filter( 'avatar_defaults', 'splash_default_avatar' );

/* Display custom column */
function splash_display_posts_stickiness( $column, $post_id ) {
	if ( 'media_type' === $column ) {
		$media_type = get_post_meta( $post_id, 'media_type', true );
		if ( empty( $media_type ) ) {
			$media_type = 'image';
		}
		echo esc_attr( $media_type );
	}
}

add_action( 'manage_media_gallery_posts_custom_column', 'splash_display_posts_stickiness', 10, 2 );

/* Add custom column to post list */
function splash_add_sticky_column( $columns ) {
	return array_merge(
		$columns,
		array(
			'media_type' => esc_html__( 'Media type', 'splash' ),
		)
	);
}

add_filter( 'manage_media_gallery_posts_columns', 'splash_add_sticky_column' );

function splash_RegenerateThumbnails() {
	$id           = splash_get_placeholder();
	$fullsizepath = get_attached_file( $id );

	if ( false === $fullsizepath || ! file_exists( $fullsizepath ) ) {
		return;
	}

	if ( wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $fullsizepath ) ) ) {
		return true;
	} else {
		return false;
	}
}

function splash_get_placeholder() {
	$placeholder_id    = 0;
	$placeholder_array = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_attachment_image_alt',
			'meta_value'     => 'placeholder',
		)
	);
	if ( $placeholder_array ) {
		foreach ( $placeholder_array as $val ) {
			$placeholder_id = $val->ID;
		}
	}

	return $placeholder_id;
}

function splash_updatePostMeta() {
	$processed_posts = get_transient( 'processed_posts' );
	$processed_terms = get_transient( 'processed_terms' );

	$players = new WP_Query(
		array(
			'post_type'   => 'sp_player',
			'post_status' => 'publish',
		)
	);

	foreach ( $players->posts as $k => $val ) {
		$postMetaLeague  = get_post_meta( $val->ID, 'sp_leagues', true );
		$postMetaStat    = get_post_meta( $val->ID, 'sp_statistics', true );
		$newPostMeta     = array();
		$newPostMetaStat = array();

		foreach ( $postMetaLeague as $leagueId => $leagueData ) {
			$newLeagueData = array();
			foreach ( $leagueData as $year => $yearData ) {
				if ( 0 !== $year ) {
					$newLeagueData[ $processed_terms[ $year ] ] = $processed_posts[ $yearData ];
				} else {
					$newLeagueData[0] = $processed_posts[ $yearData ];
				}
			}

			if ( 0 === $leagueId ) {
				$newPostMeta[0] = $newLeagueData;
			} else {
				$newPostMeta[ $processed_terms[ $leagueId ] ] = $newLeagueData;
			}

			update_post_meta( $val->ID, 'sp_leagues', $newPostMeta );
		}

		foreach ( $postMetaStat as $stat => $statData ) {
			$newStatData = array();
			foreach ( $statData as $year => $yearData ) {
				if ( 0 !== $year ) {
					$newStatData[ $processed_terms[ $year ] ] = $yearData;
				} else {
					$newStatData[0] = $yearData;
				}
			}
			if ( 0 === $stat ) {
				$newPostMetaStat[0] = $newStatData;
			} else {
				$newPostMetaStat[ $processed_terms[ $stat ] ] = $newStatData;
			}
			update_post_meta( $val->ID, 'sp_statistics', $newPostMetaStat );
		}
	}
}


if ( ! function_exists( 'splash_sportspress_side_posts' ) ) {
	function splash_sportspress_side_posts() {
		$post_types_content = array(
			'sp_calendar'   => array(
				'class'    => 'stm-single-sp_calendar stm-calendar-page',
				'template' => 'calendar-content',
			),
			'sp_event'      => array(
				'class'    => 'stm-single-sp_event stm-event-page',
				'template' => 'event-content',
			),
			'sp_table'      => array(
				'class'    => 'stm-single-sp_table-league stm-table-league-page',
				'template' => 'event-content',
			),
			'sp_player'     => array(
				'class'    => 'stm-single-sp_player stm-player-page',
				'template' => 'player-content',
			),
			'sp_staff'      => array(
				'class'    => 'stm-single-sp_staff stm-player-page',
				'template' => 'staff-content',
			),
			'sp_team'       => array(
				'class'    => 'stm-single-sp_team stm-team-page',
				'template' => 'team-content',
			),
			'sp_list'       => array(
				'class'    => 'stm-single-sp_list stm-list-page',
				'template' => 'team-content',
			),
			'sp_tournament' => array(
				'class'    => 'stm-single-sp_tournament stm-tournament-page',
				'template' => 'tournament-content',
			),
		);

		return $post_types_content;
	}
}

if ( ! function_exists( 'splash_display_sidebar' ) ) {
	function splash_display_sidebar( $sidebar_id, $before, $after, $settings ) {
		if ( ! empty( $sidebar_id ) ) :
			echo wp_kses_post( $before );
			if ( 'primary_sidebar' !== $sidebar_id ) {
				echo wp_kses_post( apply_filters( 'the_content', $settings->post_content ) );
				?>
				<style type="text/css">
					<?php echo wp_kses_post( get_post_meta( $sidebar_id, '_wpb_shortcodes_custom_css', true ) ); ?>
				</style>
				<?php
			} elseif ( 'primary_sidebar' === $sidebar_id ) {
				get_sidebar();
			}
			echo wp_kses_post( $after );
		endif;
	}
}

if ( ! function_exists( 'splash_get_body_class' ) ) {
	function splash_get_body_class() {
		$bodyClass = getThemeSettings();

		return $bodyClass['bodyClass'];
	}
}

if ( ! function_exists( 'splash_getSTMShortCityCode' ) ) {
	function splash_getSTMShortCityCode( $city ) {
		$words = str_word_count( $city, 1 );
		if ( count( $words ) > 1 ) {
			$str        = '';
			$word_count = count( $words );
			for ( $q = 0; $q < $word_count; $q ++ ) {
				$str = $str . substr( $words[ $q ], 0, 1 );
			}

			return $str;
		} else {
			return substr( $city, 0, 2 );
		}
	}
}

function splash_getLocalImgUrl( $imgName ) {
	return get_template_directory_uri() . '/assets/images/' . $imgName;
}


function splash_is_af() {
	$layoutName = get_option( 'splash_layout', 'basketball' );

	return 'americanfootball' === $layoutName;
}

function splash_is_layout( $layout_ident ) {
	$currentThemeName = get_option( 'splash_layout', 'basketball' );

	$layouts = array(
		'americanfootball' => 'af',
		'soccer'           => 'sccr',
		'baseball'         => 'baseball',
		'magazine_one'     => 'magazine_one',
		'magazine_two'     => 'magazine_two',
		'soccer_two'       => 'soccer_two',
		'soccer_news'      => 'soccer_news',
		'basketball_two'   => 'basketball_two',
		'hockey'           => 'hockey',
		'basketball'       => 'bb',
		'esport'           => 'esport',
		'volleyball'       => 'volleyball',
		'rugby'            => 'rugby',
	);

	return $layouts[ $currentThemeName ] === $layout_ident;
}


function splash_get_layout_name() {
	return get_option( 'splash_layout', 'basketball' );
}

//Add icons
add_filter( 'vc_iconpicker-type-fontawesome', 'splash_vc_stm_icons' );

if ( ! function_exists( 'splash_vc_stm_icons' ) ) {
	function splash_vc_stm_icons( $fonts ) {

		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$service_icons = json_decode( $wp_filesystem->get_contents( get_template_directory() . '/assets/js/selection.json' ), true );

		foreach ( $service_icons['icons'] as $icon ) {
			$fonts['Service Icons'][] = array(
				'icon-' . $icon['properties']['name'] => 'STM ' . $icon['properties']['name'],
			);
		}

		return $fonts;
	}
}

function parseTeamName( $teamName ) {
	$teamParse = explode( ' ', $teamName );

	$output = '';

	if ( 1 === count( $teamParse ) ) {
		$output = '<span class="team_name">' . esc_html( $teamName ) . '</span>';
	} else {
		$output          = '<span class="team_venue">' . esc_html( $teamParse[0] ) . '</span>';
		$output         .= '<span class="team_name">';
		$teamParseCount  = count( $teamParse );
		for ( $q = 1; $q < $teamParseCount; $q ++ ) {
			$output .= esc_html( $teamParse[ $q ] );
			if ( ( $teamParseCount - 1 ) !== $q ) {
				$output .= ' ';
			}
		}
		$output .= '</span>';
	}

	return $output;
}


if ( ! function_exists( 'splash_woocommerce_template_loop_product_title' ) ) {

	/**
	 * Show the product title in the product loop. By default this is an H2.
	 */
	function splash_woocommerce_template_loop_product_title() {
		echo ( splash_is_layout( 'bb' ) ) ? '<h3 class="woocommerce-loop-product__title">' . esc_html( get_the_title() ) . '</h3>' : '<h2 class="woocommerce-loop-product__title">' . esc_html( get_the_title() ) . '</h2>';
	}
}

add_action( 'stm_woocommerce_shop_loop_item_title', 'splash_woocommerce_template_loop_product_title', 10 );

// Stm menu export pars
add_action( 'init', 'splash_menu_export_pars' );
function splash_menu_export_pars() {
	if ( current_user_can( 'manage_options' ) ) {
		if ( ! empty( $_GET['stm_menu_export'] ) ) {
			$r      = array();
			$menu   = wp_get_nav_menu_items( 'Header menu' );
			$fields = mytheme_menu_item_additional_fields( array() );

			foreach ( $menu as $menu_item ) {
				$id               = $menu_item->ID;
				$menu_item_config = array();
				foreach ( $fields as $field_key => $field_value ) {
					if ( 'stm_mega_text_repeater' === $field_key ) {
						$meta     = get_post_meta( $id, '_menu_item_stm_menu_text_repeater', true );
						$metaIcon = get_post_meta( $id, '_menu_item_stm_menu_icon_repeater', true );
					} else {
						$meta = get_post_meta( $id, '_menu_item_' . $field_key, true );
					}

					if ( ! empty( $meta ) ) {
						if ( 'stm_mega_text_repeater' === $field_key ) {
							$menu_item_config['stm_menu_text_repeater'] = html_entity_decode( $meta );
							$menu_item_config['stm_menu_icon_repeater'] = html_entity_decode( $metaIcon );
						} else {
							$menu_item_config[ $field_key ] = html_entity_decode( $meta );
						}
					}
				}

				$r[ $menu_item->title ] = $menu_item_config;
			}
			// phpcs:ignore
			var_export( $r );

			die();

		}
	}
}

function splash_buildTopPlayerArray( $k, $val, $city ) {

	$term = get_term( $val['position'][0] );

	$topDataOpponent[ strtolower( $term->name ) ][0]['city_code']     = strtoupper( splash_getSTMShortCityCode( $city ) );
	$topDataOpponent[ strtolower( $term->name ) ][0]['position_id']   = $val['position'][0];
	$topDataOpponent[ strtolower( $term->name ) ][0]['position_name'] = $term->name;
	$topDataOpponent[ strtolower( $term->name ) ][0]['player_name']   = get_the_title( $k );
	$topDataOpponent[ strtolower( $term->name ) ][0]['yds']           = $val['yds'];
	$topDataOpponent[ strtolower( $term->name ) ][0]['rec']           = $val['rec'];
	if ( '' === $val['td'] ) {
		$topDataOpponent[ strtolower( $term->name ) ][0]['td'] = 0;
	} else {
		$topDataOpponent[ strtolower( $term->name ) ][0]['td'] = $val['td'];
	}

	return $topDataOpponent;
}

function splash_firstWordBold( $string ) {
	$words = explode( ' ', $string );
	$first = $words[0];

	$rebuld = str_replace( $first, '<span class="customs">' . $first . '</span>', $string );
	if ( splash_is_layout( 'soccer_two' ) ) {
		return $string;
	} else {
		return $rebuld;
	}
}

if ( splash_is_layout( 'magazine_one' ) ) {
	require_once 'add_color_picker_to_category.php';
}

function splash_getPostViewsCountHtml( $postId ) {

	if ( function_exists( 'pvc_get_post_views' ) && ! empty( pvc_get_post_views( $postId ) ) ) {
		return '<span class="stm-post-views"><i class="icon-mg-icon-fire"></i>' . pvc_get_post_views( $postId ) . '</span>';
	}

	return '';
}

function splash_update_post_meta_counter( $id ) {
	update_post_meta( $id, 'post_views_counter', pvc_get_post_views( $id ) );
}

if ( class_exists( 'Post_Views_Counter_Counter' ) ) {
	add_action( 'pvc_after_count_visit', 'splash_update_post_meta_counter' );
}
function stm_get_sub_str( $string, $length ) {
	if ( strlen( $string ) > $length ) {
		return substr( $string, 0, $length ) . '...';
	} else {
		return $string;
	}
}

// Comments

function splash_comment_meta_box( $comment ) {
	wp_nonce_field( 'splash_comment_fields_update', 'splash_comment_fields_update', false );
	$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
	if ( ! $rating ) {
		?>
		<p>
			<label for="rating"><?php esc_html__( 'Rating', 'splash' ); ?></label>
			<input type="text" name="rating" value="<?php echo esc_attr( $phone_number ); ?>" class="widefat"/>
		</p>
		<?php
	}
}

add_action( 'edit_comment', 'splash_comment_edit_meta_fields' );
function splash_comment_edit_meta_fields( $comment_id ) {
	if ( ! isset( $_POST['splash_comment_fields_update'] ) || ! wp_verify_nonce( $_POST['splash_comment_fields_update'], 'splash_comment_fields_update' ) ) {
		return;
	}

	if ( ( isset( $_POST['rating'] ) ) && ( '' !== $_POST['rating'] ) ) {
		$phone_number = wp_filter_nohtml_kses( $_POST['rating'] );
		update_comment_meta( $comment_id, 'rating', $phone_number );
	} else {
		delete_comment_meta( $comment_id, 'rating' );
	}
}
