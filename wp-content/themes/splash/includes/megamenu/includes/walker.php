<?php
add_filter( 'nav_menu_css_class', 'stm_nav_menu_css_class', 10, 4);

function stm_nav_menu_css_class( $classes, $item, $args, $depth = 0 ) {

	$id = $item->ID;
	//MEGAMENU ONLY ON FIRST LVL
	if(!$depth) {
		$mega = get_post_meta($id, stm_menu_meta('stm_mega'), true);
		if(!empty($mega) and $mega != 'disabled') {
			$classes[] = 'stm_megamenu stm_megamenu__' . $mega;

			$mega_cols = get_post_meta($id, stm_menu_meta('stm_mega_cols'), true);
			if(!empty($mega_cols)) {
				$classes[] = 'stm_megamenu_' . $mega_cols;
			}
		}

		$menuUseLogo = get_post_meta($id, '_menu_item_stm_menu_logo', true);
		if($menuUseLogo == "checked") {
			$classes[] = ' stm_menu_item_logo';
		}
	}
	elseif($depth == 1) {
		$mega_col_width = get_post_meta($id, stm_menu_meta('stm_mega_col_width'), true);
		if(!empty($mega_col_width)) {
			$classes[] = 'stm_col_width_' . $mega_col_width;
		}

		$mega_col_width_inside = get_post_meta($id, stm_menu_meta('stm_mega_cols_inside'), true);
		if(!empty($mega_col_width_inside)) {
			$classes[] = 'stm_mega_cols_inside_' . $mega_col_width_inside;
		}
	}
	elseif($depth == 2) {
		$mega_second_col_width = get_post_meta($id, stm_menu_meta('stm_mega_second_col_width'), true);
		if(!empty($mega_second_col_width)) {
			$classes[] = 'stm_mega_second_col_width_' . $mega_second_col_width;
		}

		$image = get_post_meta($id, stm_menu_meta('stm_menu_image'), true);
		$menuTextData = get_post_meta($id, '_menu_item_stm_menu_text_repeater');
		$textarea = get_post_meta($id, stm_menu_meta('stm_mega_textarea'), true);
		if(!empty($image) || $menuTextData != null || !empty($textarea)) {
			$classes[] = 'stm_mega_second_col_width_' . $mega_second_col_width . ' stm_mega_has_info';
		}
	}

    return $classes;
}

add_filter( 'nav_menu_item_title', 'stm_nav_menu_item_title', 10, 4);

function stm_nav_menu_item_title($title, $item, $args, $depth) {
    $id = $item->ID;

    //MEGAMENU ONLY ON 2 AND 3
	$menuUseLogo = get_post_meta($id, '_menu_item_stm_menu_logo', true);
    if(!$depth && $menuUseLogo == "") return $title;

    /*IMAGE BANNER THIRD LVL ONLY*/
    $image = get_post_meta($id, stm_menu_meta('stm_menu_image'), true);
    if($depth == 1 || $depth == 2) {
        if(!empty($image)) {
            $img = '';
            $image = wp_get_attachment_image_src($image, 'full');

            if(!empty($image[0])) {
                $img = '<img alt="' . $title . '" src="' . $image[0] . '" />';
                $title = $img;
            }
        } else {
			if($depth == 2){
				$menuIconData = get_post_meta($id, '_menu_item_stm_menu_icon_repeater');
				if($menuIconData != null) $menuIconData = json_decode($menuIconData[0]);

				$menuTextData = get_post_meta($id, '_menu_item_stm_menu_text_repeater');
				if($menuTextData != null) $menuTextData = json_decode($menuTextData[0]);

				if($menuTextData != null && count($menuTextData) > 0) {
					$title = "";
				}
			}
		}
    }

    if($depth == 2) {
        /*Text field*/
        $textarea = get_post_meta($id, stm_menu_meta('stm_mega_textarea'), true);
        if(!empty($textarea)) {
            $textarea = '<span class="stm_mega_textarea">'.$textarea.'</span>';
            if(!empty($image)) {
            $title = $title . $textarea;
            } else {
                $title = $textarea;
            }
        }

		$menuIconData = get_post_meta($id, '_menu_item_stm_menu_icon_repeater');
        if($menuIconData != null) $menuIconData = json_decode($menuIconData[0]);

		$menuTextData = get_post_meta($id, '_menu_item_stm_menu_text_repeater');
		if($menuTextData != null) $menuTextData = json_decode($menuTextData[0]);

		if($menuTextData != null && count($menuTextData) > 0) {
			$classLi = "normal_font";
			$list = "<ul class='mm-list'>";
			for($q=0;$q<count($menuTextData);$q++) {
				if($menuTextData[$q] != "") {
					$list .= "<li class='" . $classLi . "'><i class='" . $menuIconData[$q] . "'></i>" . $menuTextData[$q] . "</li>";
				}
			}
			$list .= "</ul>";
			$title .= $list;
		}
    }

    /*Icon on both 2 and 3 lvls and not on images*/
    if(empty($image)) {
        $icon = get_post_meta($id, stm_menu_meta('stm_menu_icon'), true);
        if (!empty($icon)) {
            $icon = '<i class="stm_megaicon ' . $icon . '"></i>';
            $title = $icon . $title;
        }
    }

	if($depth == 0 && $menuUseLogo == "checked") {
		$logo_main = get_theme_mod('logo', '');
		$output = '<div class="logo-main kos-header-logo">';
		if(empty($logo_main)):
			$output .= '<h1>' . esc_attr(get_bloginfo('name')) . '</h1>';
		else:
			$output .= '<img
                            src="' . esc_url( $logo_main ) . '"
                            style="width: ' . get_theme_mod( 'logo_width', '157' ) . 'px;"
                            title="' . esc_html_e('Home', 'splash') . '"
                            alt="' . esc_html_e('Logo', 'splash') . '"
								/>';
		endif;
		$output .= '</div>';
		$title = $output;
	}

    return $title;
}

add_filter( 'nav_menu_link_attributes', 'stm_nav_menu_link_attributes', 10, 4);

function stm_nav_menu_link_attributes($atts, $item, $args, $depth) {
    /*ONLY LVL 0*/
    if (!$depth) {
        $id = $item->ID;

        $bg = get_post_meta($id, stm_menu_meta('stm_menu_bg'), true);

        if(!empty($bg)) {
            $bg = wp_get_attachment_image_src($bg, 'full');
            if(!empty($bg[0])) {
                $atts['data-megabg'] = esc_url($bg[0]);
            }
        }
    }
    return $atts;
}

function stm_menu_meta($name) {
    return '_menu_item_' . $name;
}
