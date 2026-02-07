<?php

add_action('vc_before_init', 'splash_vc_set_as_theme');

function splash_vc_set_as_theme()
{
    vc_set_as_theme(true);
}

if (function_exists('vc_set_default_editor_post_types')) {
    vc_set_default_editor_post_types(array('page', 'post', 'donation', 'vc_sidebar', 'product', 'sp_calendar', 'sp_event', 'sp_player', 'sp_team', 'sp_list'));
}

add_action('init', 'splash_update_existing_shortcodes');

function splash_update_existing_shortcodes()
{
    if (function_exists('vc_remove_param')) {
        vc_remove_param('vc_cta_button2', 'h2');
        vc_remove_param('vc_cta_button2', 'content');
        vc_remove_param('vc_cta_button2', 'btn_style');
        vc_remove_param('vc_cta_button2', 'color');
        vc_remove_param('vc_cta_button2', 'size');
        vc_remove_param('vc_cta_button2', 'css_animation');

        //Accordion
        vc_remove_param('vc_tta_accordion', 'color');
        vc_remove_param('vc_tta_accordion', 'shape');
        vc_remove_param('vc_tta_accordion', 'style');
        vc_remove_param('vc_tta_accordion', 'spacing');
        vc_remove_param('vc_tta_accordion', 'c_align');
        vc_remove_param('vc_tta_accordion', 'c_position');
        vc_remove_param('vc_tta_accordion', 'gap');
        vc_remove_param('vc_tta_accordion', 'c_icon');

        //Tabs
        vc_remove_param('vc_tta_tabs', 'title');
        vc_remove_param('vc_tta_tabs', 'style');
        vc_remove_param('vc_tta_tabs', 'shape');
        vc_remove_param('vc_tta_tabs', 'color');
        vc_remove_param('vc_tta_tabs', 'spacing');
        vc_remove_param('vc_tta_tabs', 'gap');
        vc_remove_param('vc_tta_tabs', 'alignment');
        vc_remove_param('vc_tta_tabs', 'pagination_style');
        vc_remove_param('vc_tta_tabs', 'pagination_color');

        //Toggle
        vc_remove_param('vc_toggle', 'style');
        vc_remove_param('vc_toggle', 'color');
        vc_remove_param('vc_toggle', 'size');
    }

    if (function_exists('vc_remove_element')) {
        vc_remove_element("vc_gallery");
        //vc_remove_element( "vc_images_carousel" );
        vc_remove_element("vc_tta_tour");
        //vc_remove_element( "vc_btn" );
        vc_remove_element("vc_cta");
        vc_remove_element("vc_tta_pageable");
        vc_remove_element("vc_cta_button");
        vc_remove_element("vc_posts_slider");
        vc_remove_element("vc_icon");
        vc_remove_element("vc_pinterest");
        vc_remove_element("vc_googleplus");
        vc_remove_element("vc_facebook");
        vc_remove_element("vc_tweetmeme");
    }

}


if (function_exists('vc_map')) {
    add_action('init', 'splash_vc_elements');
}

function splash_vc_elements()
{
    $order_by_values = array(
        '',
        esc_html__('Date', 'splash') => 'date',
        esc_html__('ID', 'splash') => 'ID',
        esc_html__('Author', 'splash') => 'author',
        esc_html__('Title', 'splash') => 'title',
        esc_html__('Modified', 'splash') => 'modified',
        esc_html__('Random', 'splash') => 'rand',
        esc_html__('Comment count', 'splash') => 'comment_count',
        esc_html__('Menu order', 'splash') => 'menu_order',
    );

    $order_way_values = array(
        '',
        esc_html__('Descending', 'splash') => 'DESC',
        esc_html__('Ascending', 'splash') => 'ASC',
    );

    //post format list

    $postFormat = array('All' => 'all');
    $pFormats = get_theme_support('post-formats');
    if (!empty($pFormats) && count($pFormats) > 0) {
        foreach ($pFormats[0] as $k => $val) {
            $postFormat[$val] = strtoupper($val);
        }
    }
    /*Scheduled Events*/
    $events = get_posts(array('post_type' => 'sp_event', 'posts_per_page' => 9999, 'post_status' => 'future'));
    $events_array = array();

    if ($events) {
        foreach ($events as $event) {
            $events_array[$event->post_title . " (" . $event->post_date . ")"] = $event->ID;
        }
    }

    /*Teams*/
    $teams = get_posts(array('post_type' => 'sp_team', 'posts_per_page' => 9999));
    $teams_array = array(esc_html__('All', 'splash') => 0);
    if ($teams) {
        foreach ($teams as $team) {
            $teams_array[$team->post_title] = $team->ID;
        }
    }

    /*Players list*/
    $player_lists = get_posts(array('post_type' => 'sp_list', 'posts_per_page' => 9999));
    $lists_array = array();
    if ($player_lists) {
        foreach ($player_lists as $list) {
            $lists_array[$list->post_title] = $list->ID;
        }
    }

    /*Players*/
    $players = get_posts(array('post_type' => 'sp_player', 'posts_per_page' => 9999));
    $players_array = array();
    if ($players) {
        foreach ($players as $player) {
            $players_array[] = array('label' => $player->post_title, 'value' => $player->ID);
        }
    }

    /*Tables*/
    $tables = get_posts(array('posts_per_page' => 9999, 'post_type' => 'sp_table'));
    $tables_array = array('0' => esc_html__('Empty', 'splash'));
    if ($tables) {
        $tables_array = array();
        foreach ($tables as $table) {
            $tables_array[$table->post_title] = $table->ID;
        }
    }

    /*Performance player*/
    $statistics = get_posts(array('post_type' => 'sp_statistic', 'posts_per_page' => 9999));
    $statistics_array = array();
    if ($statistics) {
        foreach ($statistics as $statistic) {
            $statistics_array[] = array('label' => $statistic->post_title, 'value' => $statistic->ID);
        }
    }

    /*Performance player*/
    $statistics_array_chb = array();
    if ($statistics) {
        foreach ($statistics as $statistic) {
            $statistics_array_chb[$statistic->post_title] = $statistic->post_name;
        }
    }

    $posts_categories = get_terms('category');
    $post_categories_arr = array();

    foreach ($posts_categories as $posts_category) {
        $post_categories_arr[] = array('label' => $posts_category->name, 'value' => $posts_category->slug);
    }

    /*Product categories*/
    $product_categories = get_terms('product_cat');
    $product_categories_arr = array();

    if (!empty($product_categories) and !is_wp_error($product_categories)) {
        foreach ($product_categories as $product_category) {
            $product_categories_arr[] = array('label' => $product_category->name, 'value' => $product_category->slug);
        }
    }

    /*Leagues categories*/
    $leagues = get_terms('sp_league');
    $leagues_array = array();

    if (!empty($leagues) and !is_wp_error($leagues)) {
        foreach ($leagues as $league) {
            $leagues_array[] = array('label' => $league->name, 'value' => $league->term_id);
        }
    }

    /*Season categories*/
    $seasons = get_terms('sp_season');
    $seasons_array = array();

    if (!empty($seasons) and !is_wp_error($seasons)) {
        foreach ($seasons as $season) {
            $seasons_array[] = array('label' => $season->name, 'value' => $season->term_id);
        }
    }

    /*Stm sidebars*/
    $stm_sidebars_array = get_posts(array('post_type' => 'vc_sidebar', 'posts_per_page' => -1));
    $stm_sidebars = array(__('Select', 'splash') => 0);
    if ($stm_sidebars_array) {
        foreach ($stm_sidebars_array as $val) {
            $stm_sidebars[get_the_title($val)] = $val->ID;
        }
    }

    /*Category list*/

    $catList = array();
    $getCats = get_categories();

    if ($getCats != null) {
        foreach ($getCats as $val) {
            $catList[esc_attr($val->name)] = $val->term_id;
        }
    }

    /*=======================================================B============================================================*/
    vc_map(array(
        'name' => esc_html__('Button', 'splash'),
        'base' => 'stm_button',
        'icon' => 'stm_button',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'vc_link',
                'heading' => esc_html__('Link', 'splash'),
                'param_name' => 'link'
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button type', 'splash'),
                'param_name' => 'button_type',
                'value' => array(
                    esc_html__('Primary', 'splash') => 'primary',
                    esc_html__('Secondary', 'splash') => 'secondary',
                ),
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button size', 'splash'),
                'param_name' => 'button_size',
                'value' => array(
                    esc_html__('Normal', 'splash') => 'btn-sm',
                    esc_html__('Medium', 'splash') => 'btn-md',
                    esc_html__('Large', 'splash') => 'btn-lg',
                ),
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button color style', 'splash'),
                'param_name' => 'button_color_style',
                'value' => array(
                    esc_html__('Style 1', 'splash') => 'style-1',
                    esc_html__('Style 2', 'splash') => 'style-2',
                    esc_html__('Style 3', 'splash') => 'style-3',
                    esc_html__('Style 4', 'splash') => 'style-4',
                    esc_html__('Style 5', 'splash') => 'style-5',
                    esc_html__('Style 6', 'splash') => 'style-6',
                ),
            )
        )
    ));


    vc_map(array(
        "name" => esc_html__("Blockquote", 'splash'),
        "base" => "stm_block_quote",
        "class" => "stm_block_quote",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Set style", 'splash'),
                "param_name" => "blockquote_style",
                "value" => array(
                    "Style 1" => "style_1",
                    "Style 2" => "style_2",
                    "Style 3" => "style_3",
                    "Style 4" => "style_4"
                )
            ),
            array(
                "type" => "textarea",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Quote text", 'splash'),
                "param_name" => "bq_text",
            ),
            array(
                "type" => "textarea",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Author", 'splash'),
                "param_name" => "author",
                'dependency' => array(
                    'element' => 'blockquote_style',
                    'value' => array(
                        'style_3',
                        'style_4'
                    )
                )
            ),
            array(
                'type' => 'iconpicker',
                'heading' => esc_html__('Icon', 'splash'),
                'param_name' => 'bq_icon',
                'dependency' => array(
                    'element' => 'blockquote_style',
                    'value' => 'style_3'
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Blockquote with image", 'splash'),
        "base" => "stm_coach_excerption",
        "class" => "stm_coach_excerption",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Name', 'splash'),
                'param_name' => 'name',
            ),
            array(
                "type" => "textarea",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Content", 'splash'),
                "param_name" => "excerption",
            ),
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Photo', 'splash'),
                'param_name' => 'photo'
            ),
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Signature', 'splash'),
                'param_name' => 'signature'
            ),
        )
    ));

    /*==============================================================C==================================================================*/
    vc_map(array(
        'name' => esc_html__('Call to action', 'splash'),
        'base' => 'stm_call_to_action',
        'icon' => 'stm_call_to_action',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Call to action Text', 'splash'),
                'param_name' => 'call_to_action_label',
            ),
            array(
                'type' => 'colorpicker',
                'heading' => esc_html__('Text color', 'splash'),
                'param_name' => 'text_color',
            ),
            array(
                'type' => 'vc_link',
                'heading' => esc_html__('Link', 'splash'),
                'param_name' => 'link'
            ),
            /*Button style*/
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button type', 'splash'),
                'param_name' => 'button_type',
                'value' => array(
                    esc_html__('Primary', 'splash') => 'primary',
                    esc_html__('Secondary', 'splash') => 'secondary',
                ),
                'group' => esc_html__('Button style', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button size', 'splash'),
                'param_name' => 'button_size',
                'value' => array(
                    esc_html__('Normal', 'splash') => 'btn-sm',
                    esc_html__('Medium', 'splash') => 'btn-md',
                    esc_html__('Large', 'splash') => 'btn-lg',
                ),
                'group' => esc_html__('Button style', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button color style', 'splash'),
                'param_name' => 'button_color_style',
                'value' => array(
                    esc_html__('Style 1', 'splash') => 'style-1',
                    esc_html__('Style 2', 'splash') => 'style-2',
                    esc_html__('Style 3', 'splash') => 'style-3',
                    esc_html__('Style 4', 'splash') => 'style-4',
                ),
                'group' => esc_html__('Button style', 'splash')
            )
        )
    ));


    vc_map(array(
        'name' => esc_html__('Carousel', 'splash'),
        'base' => 'stm_carousel',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'attach_images',
                'heading' => esc_html__('Images', 'splash'),
                'param_name' => 'images'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Image size', 'splash'),
                'param_name' => 'image_size',
                'value' => '160x60',
                'description' => esc_html__('Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'disable_controlls',
                'value' => array(
                    esc_html__('Disable controlls', 'splash') => 'disable'
                )
            ),
            array(
                'type' => 'exploded_textarea_safe',
                'heading' => __('Custom links', 'splash'),
                'param_name' => 'custom_links',
                'description' => __('Enter links for each slide (Note: divide links with linebreaks (Enter)).', 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'heading' => esc_html__('Disable grayscale', 'splash'),
                'param_name' => 'disable_grayscale',
                'value' => array(
                    esc_html__('Yes', 'splash') => 'yes'
                ),
            ),
        ),
    ));

    vc_map(array(
        'name' => esc_html__('Carousel With Data', 'splash'),
        'base' => 'stm_carousel_with_data',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'attach_images',
                'heading' => esc_html__('Images', 'splash'),
                'param_name' => 'images'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Location', 'splash'),
                'param_name' => 'location'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Capacity', 'splash'),
                'param_name' => 'capacity'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Surface', 'splash'),
                'param_name' => 'surface'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Opened', 'splash'),
                'param_name' => 'opened'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Renovated', 'splash'),
                'param_name' => 'renovated'
            )
        ),
    ));

    vc_map(array(
        'name' => esc_html__('Carousel Image With Title', 'splash'),
        'base' => 'stm_carousel_image_title',
        'category' => esc_html__("STM", 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Visible items', 'splash'),
                'param_name' => 'visible_item',
                'value' => 4
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'disable_controlls',
                'value' => array(
                    esc_html__('Disable controlls', 'splash') => 'disable'
                )
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Image and Title', 'splash'),
                'param_name' => 'img_title',
                'value' => urlencode(json_encode(array(
                    array(
                        'image' => '',
                        'title' => '',
                    )
                ))),
                'params' => array(
                    array(
                        'type' => 'attach_image',
                        'heading' => esc_html__('Image', 'splash'),
                        'param_name' => 'image'
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Title", 'splash'),
                        "param_name" => "title",
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => esc_html__('Title align', 'splash'),
                        'param_name' => 'align',
                        'value' => array(
                            esc_html__('Left', 'splash') => 'left',
                            esc_html__('Center', 'splash') => 'center',
                            esc_html__('Right', 'splash') => 'right',
                        )
                    ),
                ),
            ),
        ),
    ));

    vc_map(array(
        'name' => esc_html__('Contact Info', 'splash'),
        'base' => 'stm_contact_info',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'attach_images',
                'heading' => esc_html__('Image', 'splash'),
                'param_name' => 'image'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Subtitle', 'splash'),
                'param_name' => 'subtitle',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Address', 'splash'),
                'param_name' => 'address',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Phone', 'splash'),
                'param_name' => 'phone',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Fax', 'splash'),
                'param_name' => 'fax',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Email', 'splash'),
                'param_name' => 'email',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('URL', 'splash'),
                'param_name' => 'url',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Schedule', 'splash'),
                'param_name' => 'schedule',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Image size', 'splash'),
                'param_name' => 'image_size',
                'value' => '370x150',
                'description' => esc_html__('Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'splash'),
            ),
        ),
    ));

    vc_map(array(
        'name' => esc_html__('Contact Manager Info', 'splash'),
        'base' => 'stm_contact_manager',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Subtitle', 'splash'),
                'param_name' => 'subtitle',
            ),
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Avatar', 'splash'),
                'param_name' => 'avatar'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Name', 'splash'),
                'param_name' => 'name_lname',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Position', 'splash'),
                'param_name' => 'position',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Phone 1', 'splash'),
                'param_name' => 'phone_one',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Phone 2', 'splash'),
                'param_name' => 'phone_two',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Phone 3', 'splash'),
                'param_name' => 'phone_three',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Email', 'splash'),
                'param_name' => 'email',
            ),
        ),
    ));


    vc_map(array(
        "name" => esc_html__("Countdown", 'splash'),
        "base" => "stm_countdown",
        "class" => "stm_countdown",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Date', 'splash'),
                'param_name' => 'countdown_date',
                'description' => 'Format 01-01-2016'
            )
        )
    ));

    /*============================================G============================================*/
    vc_map(array(
        'name' => esc_html__('Google Map', 'splash'),
        'base' => 'stm_gmap',
        'icon' => 'stm_gmap',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Marker', 'splash'),
                'param_name' => 'image'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Map Width', 'splash'),
                'param_name' => 'map_width',
                'value' => '100%',
                'description' => esc_html__('Enter map width in px or %', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Map Height', 'splash'),
                'param_name' => 'map_height',
                'value' => '460px',
                'description' => esc_html__('Enter map height in px', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Latitude', 'splash'),
                'param_name' => 'lat',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Longitude', 'splash'),
                'param_name' => 'lng',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Map Zoom', 'splash'),
                'param_name' => 'map_zoom',
                'value' => 18
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('InfoWindow text', 'splash'),
                'param_name' => 'infowindow_text',
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'disable_mouse_whell',
                'value' => array(
                    esc_html__('Disable map zoom on mouse wheel scroll', 'splash') => 'disable'
                )
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Extra class name', 'splash'),
                'param_name' => 'el_class',
                'description' => esc_html__('Style particular content element differently - add a class name and refer to it in custom CSS.', 'splash')
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    vc_map(array(
        'name' => esc_html__('Google Map With Info Blocks', 'splash'),
        'base' => 'stm_gmap_with_info_blocks',
        'as_parent' => array('only' => 'stm_gmap_info_block'),
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Marker', 'splash'),
                'param_name' => 'image'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Map Width', 'splash'),
                'param_name' => 'map_width',
                'value' => '100%',
                'description' => esc_html__('Enter map width in px or %', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Map Height', 'splash'),
                'param_name' => 'map_height',
                'value' => '460px',
                'description' => esc_html__('Enter map height in px', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Latitude', 'splash'),
                'param_name' => 'lat',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Longitude', 'splash'),
                'param_name' => 'lng',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Map Zoom', 'splash'),
                'param_name' => 'map_zoom',
                'value' => 18
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('InfoWindow text', 'splash'),
                'param_name' => 'infowindow_text',
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'disable_mouse_whell',
                'value' => array(
                    esc_html__('Disable map zoom on mouse wheel scroll', 'splash') => 'disable'
                )
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Extra class name', 'splash'),
                'param_name' => 'el_class',
                'description' => esc_html__('Style particular content element differently - add a class name and refer to it in custom CSS.', 'splash')
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        ),
        'js_view' => 'VcColumnView'
    ));

    vc_map(array(
        'name' => esc_html__('Google Map Info Block', 'splash'),
        'base' => 'stm_gmap_info_block',
        'as_child' => array('only' => 'stm_gmap_with_info_blocks'),
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Block Title", 'splash'),
                "param_name" => "block_title",
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Info blocks', 'splash'),
                'param_name' => 'info_blocks',
                'value' => urlencode(json_encode(array(
                    array(
                        'is_link' => '',
                        'info_icon' => '',
                        'info_content' => '',
                        'info_link' => ''
                    )
                ))),
                'params' => array(
                    array(
                        'type' => 'checkbox',
                        'param_name' => 'is_link',
                        'value' => array(
                            esc_html__('Is Link', 'splash') => 'enable'
                        )
                    ),
                    array(
                        'type' => 'iconpicker',
                        'heading' => esc_html__('Icon', 'splash'),
                        'param_name' => 'info_icon',
                        'dependency' => array('element' => 'list_type', 'value' => 'font'),
                    ),
                    array(
                        'type' => 'textarea',
                        'heading' => esc_html__('Info text', 'splash'),
                        'param_name' => 'info_content'
                    ),
                    array(
                        'type' => 'textarea',
                        'heading' => esc_html__('Info link', 'splash'),
                        'param_name' => 'info_link',
                        'dependency' => array(
                            'element' => 'is_link',
                            'value' => 'enable'
                        )
                    )
                ),
                'group' => esc_html__('Info Blocks', 'splash')
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));


    /*=============================================H=============================================*/
    vc_map(array(
        'name' => esc_html__('Heading with icon', 'splash'),
        'base' => 'stm_heading_with_icon',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'iconpicker',
                'heading' => esc_html__('Icon', 'splash'),
                'param_name' => 'icon_title',
            ),
            array(
                'type' => 'textarea_html',
                'heading' => esc_html__('Content', 'splash'),
                'param_name' => 'content'
            )
        )
    ));

    /*=============================================I=============================================*/
    vc_map(array(
        'name' => esc_html__('Icon list', 'splash'),
        'base' => 'stm_icon_list',
        'icon' => 'stm_icon_list',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('List type', 'splash'),
                'param_name' => 'list_type',
                'value' => array(
                    esc_html__('Marked', 'splash') => 'marked',
                    esc_html__('Numeric', 'splash') => 'numeric',
                    esc_html__('Font icon', 'splash') => 'font',
                ),
                'holder' => 'div'
            ),
            array(
                'type' => 'iconpicker',
                'heading' => esc_html__('Icon', 'splash'),
                'param_name' => 'title',
                'dependency' => array('element' => 'list_type', 'value' => 'font'),
            ),
            array(
                'type' => 'checkbox',
                'heading' => esc_html__('Circle bg', 'splash'),
                'param_name' => 'circle_bg',
                'value' => array(
                    esc_html__('Enable circle bg for icon', 'splash') => 'enable'
                ),
                'dependency' => array('element' => 'list_type', 'value' => 'font'),
            ),
            array(
                'type' => 'textarea_html',
                'heading' => esc_html__('Text', 'splash'),
                'param_name' => 'content'
            ),
        )
    ));

    vc_map(array(
        'name' => esc_html__('Images Grid', 'splash'),
        'base' => 'stm_images_grid',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'attach_images',
                'heading' => esc_html__('Images', 'splash'),
                'param_name' => 'images'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Image size', 'splash'),
                'param_name' => 'image_size',
                'value' => '270x250',
                'description' => esc_html__('Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'splash'),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Columns", 'splash'),
                "param_name" => "columns",
                "value" => array(
                    '6' => '6',
                    '4' => '4',
                    '3' => '3',
                    '2' => '2',
                ),
                'std' => '4'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Load by', 'splash'),
                'param_name' => 'load_by',
                'value' => '12',
                'description' => esc_html__('Images to show by. Default: 12', 'splash'),
            ),
            /*Button style*/
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button type', 'splash'),
                'param_name' => 'button_type',
                'value' => array(
                    esc_html__('Primary', 'splash') => 'primary',
                    esc_html__('Secondary', 'splash') => 'secondary',
                ),
                'group' => esc_html__('Button style', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button size', 'splash'),
                'param_name' => 'button_size',
                'value' => array(
                    esc_html__('Normal', 'splash') => 'btn-sm',
                    esc_html__('Medium', 'splash') => 'btn-md',
                    esc_html__('Large', 'splash') => 'btn-lg',
                ),
                'group' => esc_html__('Button style', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Button color style', 'splash'),
                'param_name' => 'button_color_style',
                'value' => array(
                    esc_html__('Style 1', 'splash') => 'style-1',
                    esc_html__('Style 2', 'splash') => 'style-2',
                    esc_html__('Style 3', 'splash') => 'style-3',
                    esc_html__('Style 4', 'splash') => 'style-4',
                ),
                'group' => esc_html__('Button style', 'splash')
            )
        ),
    ));

    /*=====================================================L=====================================================*/
    vc_map(array(
        "name" => esc_html__("Latest Results", 'splash'),
        "base" => "stm_latest_results",
        "class" => "stm_latest_results",
        "controls" => "full",
        'category' => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Latest Results", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Show", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "show_games",
                "value" => array(
                    esc_html__('Certain number', 'splash') => 'number',
                    esc_html__('All games', 'splash') => 'all'
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Fixture Link", 'splash'),
                "param_name" => "link_bind",
                "value" => array(
                    esc_html__('Link teams', 'splash') => 'teams',
                    esc_html__('Link event', 'splash') => 'event'
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Count", 'splash'),
                "param_name" => "count",
                "value" => 3,
                "min" => 1,
                "dependency" => array(
                    "element" => "show_games",
                    "value" => array("number"),
                ),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a team", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "pick_team",
                "value" => $teams_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("View Style", 'splash'),
                "param_name" => "lr_view_style",
                "value" => array(
                    "Default" => "default",
                    "Baseball" => "baseball",
                    "Voleyball" => "volleyball"
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Latest Results Carousel", 'splash'),
        "base" => "stm_latest_results_carousel",
        "class" => "stm_latest_results_carousel",
        "controls" => "full",
        'category' => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Latest Results", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Fixture Link", 'splash'),
                "param_name" => "link_bind",
                "value" => array(
                    esc_html__('Link teams', 'splash') => 'teams',
                    esc_html__('Link event', 'splash') => 'event'
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Count", 'splash'),
                "param_name" => "count",
                "value" => 6,
                "min" => 1,
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Slides count", 'splash'),
                "param_name" => "slide_count",
                "value" => 5,
                "min" => 1,
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a team", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "pick_team",
                "value" => $teams_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("View Style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    esc_html__('Default', 'splash') => 'default',
                    esc_html__('Style 1', 'splash') => 'style_1',
                    esc_html__('Style 2', 'splash') => 'style_2'
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Results Table With Ajax", 'splash'),
        "base" => "stm_results_table_ajax",
        "class" => "stm_results_table_ajax",
        "controls" => "full",
        'category' => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Results", 'splash'),
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Show Games", 'splash'),
                "param_name" => "count",
                "value" => 3,
                "min" => 1,
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Set Type", 'splash'),
                "param_name" => "results_type",
                "value" => array(
                    esc_html__('Latest', 'splash') => 'publish',
                    esc_html__('Upcoming', 'splash') => 'future',
                )
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("One Event", 'splash'),
        "base" => "stm_af_latest_result",
        "class" => "stm_af_latest_result",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Select event type", 'splash'),
                "param_name" => "event_type",
                "value" => array(
                    esc_html__('Latest', 'splash') => "latest",
                    esc_html__('Upcoming', 'splash') => "upcoming"
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a team", 'splash'),
                "param_name" => "pick_team",
                "value" => $teams_array
            ),
            array(
                'type' => 'dropdown',
                'heading' => 'Block style',
                'param_name' => 'block_style',
                'value' => array(
                    esc_html__('Football style', 'splash') => 'football_style',
                    esc_html__('Soccer style', 'splash') => 'soccer_style',
                    esc_html__('Baseball style', 'splash') => 'baseball_style',
                    esc_html__('Basketball Two style', 'splash') => 'basketball_two_style'
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Left Helms Type', 'splash'),
                'param_name' => 'left_helms',
                'value' => array(
                    esc_html__('Black', 'splash') => 'black',
                    esc_html__('Blue', 'splash') => 'blue',
                    esc_html__('Brown', 'splash') => 'brown',
                    esc_html__('Green', 'splash') => 'green',
                    esc_html__('Orange', 'splash') => 'orange',
                    esc_html__('Purple', 'splash') => 'purple',
                    esc_html__('Red', 'splash') => 'red',
                    esc_html__('Turquoise', 'splash') => 'turquoise',
                    esc_html__('White', 'splash') => 'white',
                    esc_html__('Yellow', 'splash') => 'yellow'
                ),
                'holder' => 'div',
                'dependency' => array(
                    'element' => 'block_style',
                    'value' => array('football_style')
                )
            ),
            array(
                'type' => 'attach_image',
                'param_name' => 'custom_left_helm',
                'heading' => esc_html__('Custom Left Helms', 'splash'),
                'dependency' => array(
                    'element' => 'block_style',
                    'value' => array('football_style')
                )
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Right Helms Type', 'splash'),
                'param_name' => 'right_helms',
                'value' => array(
                    esc_html__('Black', 'splash') => 'black',
                    esc_html__('Blue', 'splash') => 'blue',
                    esc_html__('Brown', 'splash') => 'brown',
                    esc_html__('Green', 'splash') => 'green',
                    esc_html__('Orange', 'splash') => 'orange',
                    esc_html__('Purple', 'splash') => 'purple',
                    esc_html__('Red', 'splash') => 'red',
                    esc_html__('Turquoise', 'splash') => 'turquoise',
                    esc_html__('White', 'splash') => 'white',
                    esc_html__('Yellow', 'splash') => 'yellow'
                ),
                'holder' => 'div',
                'dependency' => array(
                    'element' => 'block_style',
                    'value' => array('football_style')
                )
            ),
            array(
                'type' => 'attach_image',
                'param_name' => 'custom_right_helm',
                'heading' => esc_html__('Custom Right Helms', 'splash'),
                'dependency' => array(
                    'element' => 'block_style',
                    'value' => array('football_style')
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'use_default_team_helm',
                'value' => array(
                    esc_html__('Use Default Team Helm', 'splash') => 'enable'
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'show_btn_get_tickets',
                'default' => false,
                'value' => array(
                    esc_html__('Show button "GET TICKETS"', 'splash') => 'enable'
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("GET TICKETS Button link", 'splash'),
                "param_name" => "get_tickets_btn_link",
                "value" => esc_html__("http://", 'splash'),
                'dependency' => array(
                    'element' => 'show_btn_get_tickets',
                    'value' => array('enable')
                )
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Latest News Grid Tabs", 'splash'),
        "base" => "stm_latest_news",
        "class" => "stm_latest_news",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Latest news", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'include_all_news',
                'value' => array(
                    esc_html__('Show tab all news', 'splash') => 'enable'
                )
            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Include Category', 'splash'),
                'param_name' => 'post_categories',
                'description' => esc_html__('Add Category. If not added show all category', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $post_categories_arr
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of news to display (each tab)", 'splash'),
                "param_name" => "number",
                "value" => esc_html__("4", 'splash'),
                "dependency" => array(
                    "element" => "view_style",
                    "value" => array("style_1", "style_4")
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Select View Style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    esc_html__("style 1", 'splash') => "style_1",
                    esc_html__("style 2", 'splash') => "style_2",
                    esc_html__("style 3", 'splash') => "style_3",
                    esc_html__("style 4", 'splash') => "style_4",
                    esc_html__("style 5", 'splash') => "style_5"
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Latest News Most Styles", 'splash'),
        "base" => "stm_latest_news_most_styles",
        "class" => "stm_latest_news_most_styles",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Latest news", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Select View Style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    'With Image' => 'with_image',
                    'WithOut Image' => 'without_image',
                    'Grid' => 'grid',
                    'Simple post list' => 'list',
                    'Masonry grid' => 'masonry',
                    'Masonry grid 2' => 'masonry_2'
                )
            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Include Category', 'splash'),
                'param_name' => 'post_categories',
                'description' => esc_html__('Add Category. If not added show all category', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $post_categories_arr
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'order_by_popular',
                'value' => array(
                    esc_html__('Order by Popular', 'splash') => 'enable'
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'show_load_more_btn',
                'value' => array(
                    esc_html__('Show load more button', 'splash') => 'enable'
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Button Title", 'splash'),
                "param_name" => "load_mpre_btn_title",
                "value" => esc_html__("Load more", 'splash'),
                "dependency" => array(
                    "element" => "show_load_more_btn",
                    "value" => "enable"
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("News page url", 'splash'),
                "param_name" => "news_page_url",
                "value" => esc_html__("/blog", 'splash'),
                "dependency" => array(
                    "element" => "view_style",
                    "value" => 'masonry'
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Number of columns", 'splash'),
                "param_name" => "number_columns",
                "value" => array(
                    '1' => '1',
                    '2' => '2',
                ),
                "dependency" => array(
                    "element" => "view_style",
                    "value" => array('with_image', 'without_image')
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of news to display", 'splash'),
                "param_name" => "number",
                "value" => esc_html__("4", 'splash'),
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));


    vc_map(array(
        "name" => esc_html__("Latest tweets", 'splash'),
        "base" => "stm_latest_tweets",
        "class" => "stm_latest_tweets",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'latest_tweets_title',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Twitter user name', 'splash'),
                'param_name' => 'latest_tweets_name',
            ),
            array(
                'type' => 'checkbox',
                'heading' => esc_html__('Enable carousel', 'splash'),
                'param_name' => 'carousel',
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("League Tables", 'splash'),
        "description" => esc_html__("Place League Table", 'splash'),
        "base" => "stm_league_table",
        "class" => "stm_league_table",
        "controls" => "full",
        "icon" => 'stm_league_table',
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Select Table", 'splash'),
                "param_name" => "id",
                "value" => $tables_array
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Table_style", 'splash'),
                "param_name" => "table_style",
                "value" => array(
                    esc_html__("Style 1", 'splash') => "style_one",
                    esc_html__("Style 2", 'splash') => "style_two",
                    esc_html__("Style 3", 'splash') => "style_3",
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Table title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Points Table", 'splash'),
                "dependency" => array(
                    "element" => "table_style",
                    "value" => array("style_one", "style_3")
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Count", 'splash'),
                "param_name" => "count",
                "value" => 7,
                "min" => 1
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("League Table With Ajax", 'splash'),
        "base" => "stm_league_table_with_ajax",
        "class" => "stm_league_table_with_ajax",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Table title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Statistics Table", 'splash')
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Table rows count", 'splash'),
                "param_name" => "count",
                "value" => esc_html__("5", 'splash')
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    /*=====================================================M=====================================================*/
    vc_map(array(
        "name" => esc_html__("Media Archive", 'splash'),
        "base" => "stm_media_archive",
        "class" => "stm_media_archive",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Set style", 'splash'),
                "param_name" => "media_archive_style",
                "value" => array(
                    "Style 2x3" => "style_2_3",
                    "Style 3x3" => "style_3_3",
                    "Style 5x3" => "style_5_3",
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'disable_masonry',
                'value' => array(
                    esc_html__('Disable masonry mode', 'splash') => 'disable'
                )
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Media tabs", 'splash'),
        "base" => "stm_media_tabs",
        "class" => "stm_media_tabs",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Media", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Set style", 'splash'),
                "param_name" => "media_style",
                "value" => array(
                    "Style 2x3" => "style_2_3",
                    "Style 3x3" => "style_3_3",
                    "Style 5x3" => "style_5_3"
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'disable_masonry',
                'value' => array(
                    esc_html__('Disable masonry mode', 'splash') => 'disable'
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of medias to display (each tab)", 'splash'),
                "param_name" => "number",
                "value" => esc_html__("6", 'splash')
            ),
        )
    ));

    /*=====================================================N=====================================================*/
    vc_map(array(
        "name" => esc_html__("Next Matches", 'splash'),
        "base" => "stm_next_match",
        "class" => "stm_next_match",
        "controls" => "full",
        'category' => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Next Match", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "holder" => "div",
                "heading" => "View type",
                "param_name" => "view_type",
                "value" => array(
                    esc_html__("Slider", 'splash') => "slider",
                    esc_html__("Blocks", 'splash') => "blocks",
                    esc_html__("Single block", 'splash') => "single"
                )
            ),
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Background image', 'splash'),
                'param_name' => 'images',
                'dependency' => array(
                    "element" => "view_type",
                    "value" => array("slider", "single")
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Show", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "show_games",
                "value" => array(
                    esc_html__('Certain number', 'splash') => 'number',
                    esc_html__('All games', 'splash') => 'all'
                ),
                'dependency' => array(
                    "element" => "view_type",
                    "value" => array("slider", "blocks")
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Count", 'splash'),
                "param_name" => "count",
                "value" => 3,
                "min" => 1,
                "dependency" => array(
                    "element" => "show_games",
                    "value" => array("number"),
                ),
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Button text", 'splash'),
                "param_name" => "button_text",
                "value" => 'Read more',
                "dependency" => array(
                    "element" => "view_type",
                    "value" => array("single"),
                ),
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Button link", 'splash'),
                "param_name" => "button_link",
                "dependency" => array(
                    "element" => "view_type",
                    "value" => array("single"),
                ),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a team", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "pick_team",
                "value" => $teams_array
            ),
            array(
                'type' => 'colorpicker',
                'param_name' => 'text_color',
                'heading' => esc_html__('Text color', 'splash'),
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("News tabs", 'splash'),
        "base" => "stm_news_tabs",
        "class" => "stm_news_tabs",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("News", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Include Category', 'splash'),
                'param_name' => 'post_categories',
                'description' => esc_html__('Add Category. If not added show all category', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $post_categories_arr
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of news to display (each tab)", 'splash'),
                "param_name" => "number",
                "value" => esc_html__("3", 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'holder' => "div",
                'class' => "stm_show_load_more_btn",
                'param_name' => 'stm_show_load_more',
                'value' => array(esc_attr__("Show load more button", 'splash') => 'enable')
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'news_tabs_style',
                'heading' => esc_html__('View Style', 'splash'),
                'value' => array(
                    esc_html__("Default", 'splash') => 'style_basketball',
                    esc_html__("Baseball", 'splash') => 'style_baseball'
                ),
            ),
        )
    ));

    /*============================================P============================================*/
    vc_map(array(
        "name" => esc_html__("Posts Ticker", 'splash'),
        "base" => "stm_posts_ticker",
        "class" => "stm_posts_ticker",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'checkbox',
                'holder' => "div",
                'class' => "stm_excl_cats_wrapper",
                'param_name' => 'exclude_categories',
                'value' => array(esc_attr__("Exclude categories", 'splash') => 'enable')
            ),
            array(
                'type' => 'checkbox',
                'holder' => "div",
                'class' => "stm_excl_cats_wrapper",
                'param_name' => 'checked_exclude_categories',
                'heading' => esc_html__("Exclude categories", 'splash'),
                'value' => $catList,
                'dependency' => array(
                    'element' => 'exclude_categories',
                    'value' => 'enable'
                )
            ),
            array(
                'type' => 'textfield',
                'param_name' => 'total_posts',
                'heading' => esc_html__('Total number of posts', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'link_target',
                'heading' => esc_html__('Link target', 'splash'),
                'value' => array(
                    esc_html__("Same window", 'splash') => 'same_window',
                    esc_html__("New window", 'splash') => 'new_window'
                ),
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'show_post_date',
                'heading' => esc_html__('Show post date', 'splash'),
                'value' => array(esc_html__('Show', 'splash') => 'enable')
            ),
            array(
                'type' => 'textfield',
                'param_name' => 'date_format',
                'heading' => esc_html__('Date format', 'splash'),
                'dependency' => array(
                    'element' => 'show_post_date',
                    'value' => 'enable'
                )
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'styles',
                'heading' => esc_html__('Styles', 'splash'),
                'value' => array(
                    esc_html__("Default", 'splash') => 'default',
                    esc_html__("Style 1", 'splash') => 'style_1',
                ),
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'show_ticker_title',
                'heading' => esc_html__('Show ticker title', 'splash'),
                'value' => array(esc_html__('Show', 'splash') => 'enable'),
                'group' => esc_html__('Header settings', 'splash'),
                'dependency' => array(
                    'element' => 'styles',
                    'value' => 'default'
                ),
            ),
            array(
                'type' => 'colorpicker',
                'param_name' => 'ticker_title_color',
                'heading' => esc_html__('Title color', 'splash'),
                'dependency' => array(
                    'element' => 'show_ticker_title',
                    'value' => 'enable'
                ),
                'group' => esc_html__('Header settings', 'splash')
            ),
            array(
                'type' => 'colorpicker',
                'param_name' => 'ticker_first_word_color',
                'heading' => esc_html__('Color for the first word', 'splash'),
                'dependency' => array(
                    'element' => 'show_ticker_title',
                    'value' => 'enable'
                ),
                'group' => esc_html__('Header settings', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'ticker_title_position',
                'heading' => esc_html__('Ticker title position', 'splash'),
                'value' => array(
                    esc_html__("Left", 'splash') => 'left',
                    esc_html__("Right", 'splash') => 'right',
                ),
                'dependency' => array(
                    'element' => 'show_ticker_title',
                    'value' => 'enable'
                ),
                'group' => esc_html__('Header settings', 'splash')
            ),
            array(
                'type' => 'textfield',
                'param_name' => 'ticker_title_text',
                'heading' => esc_html__('Ticker title', 'splash'),
                'dependency' => array(
                    'element' => 'show_ticker_title',
                    'value' => 'enable'
                ),
                'group' => esc_html__('Header settings', 'splash')
            ),
            array(
                'type' => 'colorpicker',
                'param_name' => 'ticker_bg_color',
                'heading' => esc_html__('Background color', 'splash'),
                'group' => esc_html__('Header settings', 'splash'),
                'dependency' => array(
                    'element' => 'styles',
                    'value' => 'default'
                ),
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'ticker_direction',
                'heading' => esc_html__("Ticker direction", 'splash'),
                'value' => array(
                    esc_html__('Up', 'splash') => 'up',
                    esc_html__('Down', 'splash') => 'down',
                    esc_html__('Left to Right', 'splash') => 'left_right',
                ),
                'group' => esc_html__('Ticker settings', 'splash')
            ),
            array(
                'type' => 'textfield',
                'param_name' => 'ticker_auto_play_speed',
                'heading' => esc_html__('Ticker auto play speed', 'splash'),
                'value' => 3000,
                'description' => esc_html__('Set speed value in milliseconds', 'splash'),
                'group' => esc_html__('Ticker settings', 'splash')
            ),
            array(
                'type' => 'textfield',
                'param_name' => 'ticker_animate_speed',
                'heading' => esc_html__('Ticker animate speed', 'splash'),
                'value' => 700,
                'description' => esc_html__('Set speed value in milliseconds', 'splash'),
                'group' => esc_html__('Ticker settings', 'splash')
            ),
            array(
                'type' => 'iconpicker',
                'heading' => esc_html__('Ticker post icon', 'splash'),
                'param_name' => 'ticker_icon',
                'group' => esc_html__('Ticker settings', 'splash'),
                'dependency' => array(
                    'element' => 'styles',
                    'value' => 'default'
                ),
            ),
        )
    ));

    vc_map(array(
        'name' => esc_html__('Player Quick Stats', 'splash'),
        'base' => 'stm_player_quick_stats',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Quick Stats (2016)", 'splash'),
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Params', 'splash'),
                'param_name' => 'stm_pl_qk_sts',
                'value' => urlencode(json_encode(array(
                    array(
                        'label' => '',
                        'value' => '',
                    )
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Label", 'splash'),
                        "param_name" => "label",
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Data", 'splash'),
                        "param_name" => "data",
                    )
                ),
            ),
        ),
    ));


    vc_map(array(
        "name" => esc_html__("Players Carousel", 'splash'),
        "base" => "stm_players_carousel",
        "class" => "stm_players_carousel",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Players", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "heading" => esc_html__("Player per row", 'splash'),
                "param_name" => "per_row",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "heading" => esc_html__("Image Size", 'splash'),
                "param_name" => "player_image_size",
                "description" => esc_html__("Default 270x370.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Player Lists", 'splash'),
                "param_name" => "player_list",
                "value" => $lists_array,
                "admin_label" => true
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Enable carousel", 'splash'),
                "param_name" => "enable_carousel",
                "value" => array(
                    esc_html__('Yes', 'splash') => 'yes',
                    esc_html__('No', 'splash') => 'no'
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Select view style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    esc_html__('Style 1', 'splash') => 'style_1',
                    esc_html__('Style 2', 'splash') => 'style_2'
                )
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Products carousel", 'splash'),
        "base" => "stm_products_carousel",
        "class" => "stm_products_carousel",
        "description" => esc_html__('Carousel of recent products', 'splash'),
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Official Store", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Include Category', 'splash'),
                'param_name' => 'post_categories',
                'description' => esc_html__('Add Category. If not added show all category', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $product_categories_arr
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Visible items", 'splash'),
                "param_name" => "visible_items",
                "value" => esc_html__("4", 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'stretch_row',
                'value' => array(
                    esc_html__('Disable stretch row', 'splash') => 'disable'
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of products to display", 'splash'),
                "param_name" => "number",
                "value" => esc_html__("6", 'splash'),
            ),
            array(
                "type" => "dropdown",
                "param_name" => "change_style",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Change style", 'splash'),
                "value" => array(
                    esc_html__('default', 'splash') => 'default',
                    esc_html__("Style 1", 'splash') => 'style_1',
                    esc_html__("Style 2", 'splash') => 'style_2',
                    esc_html__("Style 3", 'splash') => 'style_3'
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Posts carousel", 'splash'),
        "base" => "stm_posts_carousel",
        "class" => "stm_posts_carousel",
        "description" => esc_html__('Carousel of posts', 'splash'),
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("", 'splash'),
            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Include Category', 'splash'),
                'param_name' => 'post_categories',
                'description' => esc_html__('Add Category. If not added show all category', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $post_categories_arr
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Visible items", 'splash'),
                "param_name" => "visible_items",
                "value" => esc_html__("4", 'splash'),
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number Of Posts", 'splash'),
                "param_name" => "number_posts",
                "value" => esc_html__("8", 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'stretch_row',
                'value' => array(
                    esc_html__('Disable stretch row', 'splash') => 'disable'
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Video Posts Carousel", 'splash'),
        "base" => "stm_video_posts_carousel",
        "class" => "stm_video_posts_carousel",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number Of Posts", 'splash'),
                "param_name" => "number_posts",
                "value" => esc_html__("8", 'splash'),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Style", 'splash'),
                "param_name" => "style",
                "value" => array(
                    esc_html__("Slider view", 'splash') => 'slider',
                    esc_html__("Carousel view", 'splash') => 'carousel'
                ),
                'std' => 'slider'
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Video Posts List", 'splash'),
        "base" => "stm_video_posts_list",
        "class" => "stm_video_posts_list",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number Of Posts", 'splash'),
                "param_name" => "number_posts",
                "value" => esc_html__("4", 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'heading' => esc_html__("First Image Big", 'splash'),
                'param_name' => 'first_big_img',
                'value' => array('Enable' => 'enable'),
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Player of the Month", 'splash'),
        "base" => "stm_player_of_month",
        "class" => "stm_player_of_month",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Plater of the Month", 'splash'),
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Player', 'splash'),
                'param_name' => 'player_id',
                'value' => $players_array,
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'stat_paramms',
                'value' => $statistics_array_chb
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'use_background_image',
                'value' => array(
                    esc_html__('Use background Image', 'splash') => 'enable'
                )
            ),
            array(
                'type' => 'attach_image',
                'param_name' => 'background_image',
                'heading' => esc_html__('Background Image', 'splash'),
                'dependency' => array(
                    'element' => 'use_background_image',
                    'value' => 'enable'
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Player statistics", 'splash'),
        "base" => "stm_player_statistic",
        "class" => "stm_player_statistic",
        "description" => esc_html__('Carousel of players', 'splash'),
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Preseason Stats", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Sub Title", 'splash'),
                "param_name" => "sub_title",
                "value" => esc_html__("Preseason Leaders", 'splash'),
            ),
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Background image', 'splash'),
                'param_name' => 'images'
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose League", 'splash'),
                "param_name" => "league",
                "value" => $leagues_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose Season", 'splash'),
                "param_name" => "season",
                "value" => $seasons_array
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Items', 'splash'),
                'param_name' => 'items',
                'value' => urlencode(json_encode(array(
                    array(
                        'label' => esc_html__('Choose Statistic', 'splash'),
                        'value' => '',
                    ),
                    array(
                        'label' => esc_html__('Statistic Title', 'splash'),
                        'value' => '',
                    ),
                    array(
                        'label' => esc_html__('Choose Players', 'splash'),
                        'value' => '',
                    ),
                ))),
                'params' => array(
                    array(
                        "type" => "dropdown",
                        "class" => "",
                        "heading" => esc_html__("Choose statistic to show", 'splash'),
                        "param_name" => "statistic",
                        "value" => $statistics_array,
                        "holder" => 'div'
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Statistic title", 'splash'),
                        "param_name" => "statistic_title",
                        "description" => esc_html__("Enter text which will be used as widget title. Leave blank, title will be generated from statistic title", 'splash')
                    ),
                    array(
                        'type' => 'autocomplete',
                        'heading' => esc_html__('Players', 'splash'),
                        'param_name' => 'players',
                        'description' => esc_html__('Choose players to show', 'splash'),
                        'settings' => array(
                            'multiple' => true,
                            'min_length' => 1,
                            'no_hide' => true,
                            'values' => $players_array,
                        )
                    ),
                ),
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Player statistic details", 'splash'),
        "base" => "stm_af_player_statistic",
        "class" => "stm_af_player_statistic",
        "description" => esc_html__('Player statistic details', 'splash'),
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Preseason Stats", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose League", 'splash'),
                "param_name" => "league",
                "value" => $leagues_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose Season", 'splash'),
                "param_name" => "season",
                "value" => $seasons_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Set view by", 'splash'),
                "param_name" => "set_view_by",
                "value" => array(
                    esc_html__('Player lists', 'splash') => 'player_lists',
                    esc_html__('Players', 'splash') => 'players'
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose player list to show", 'splash'),
                "param_name" => "player_lists",
                "value" => $lists_array,
                "dependency" => array(
                    "element" => "set_view_by",
                    "value" => array("player_lists"),
                ),

            ),
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Choose player to show', 'splash'),
                'param_name' => 'players',
                'description' => esc_html__('Choose players to show', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'values' => $players_array,
                ),
                "dependency" => array(
                    "element" => "set_view_by",
                    "value" => array("players"),
                ),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose View Type", 'splash'),
                "param_name" => "af_ps_view_style",
                "value" => array(
                    esc_html__("Default", 'splash') => "default",
                    esc_html__("BaseBall", 'splash') => "baseball",
                    esc_html__("Basketball Two", 'splash') => "basketball-two",
                    esc_html__("Hockey", 'splash') => "hockey",
                    esc_html__("Esport", 'splash') => "esport",
                    esc_html__("Volleyball", 'splash') => "volleyball"
                ),

            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'show_all_season',
                'value' => array(
                    esc_html__('Show all seasons', 'splash') => 'show_all_seasons'
                ),
                'dependency' => array(
                    'element' => 'af_ps_view_style',
                    'value' => 'baseball'
                )
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Price plan", 'splash'),
        "base" => "stm_price_plan",
        "class" => "stm_price_plan",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Plan title", 'splash'),
                "param_name" => "title",
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Plan badge", 'splash'),
                "param_name" => "badge",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Plan price", 'splash'),
                "param_name" => "price",
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Plan price label", 'splash'),
                "param_name" => "price_label",
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Features', 'splash'),
                'param_name' => 'feature',
                'value' => urlencode(json_encode(array(
                    array(
                        'label' => esc_html__('Feature', 'splash'),
                        'value' => '',
                    ),
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Feature", 'splash'),
                        "param_name" => "feature_item",
                    ),
                ),
            ),
            array(
                'type' => 'vc_link',
                'heading' => esc_html__('Link', 'splash'),
                'param_name' => 'link'
            ),
        )
    ));

    /*===================================================R===============================================*/
    vc_map(array(
        "name" => esc_html__("Reviews Carousel", 'splash'),
        "base" => "stm_reviews_carousel",
        "class" => "stm_reviews_carousel",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Reviews", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of review to display", 'splash'),
                "param_name" => "number",
                "value" => esc_html__("3", 'splash'),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Set style", 'splash'),
                "param_name" => "review_view_style",
                "value" => array(
                    esc_html__("Review style 1", 'splash') => "review_style_one",
                    esc_html__("Review style 2", 'splash') => "review_style_two",
                    esc_html__("Review style 3", 'splash') => "review_style_three",
                    esc_html__("Review style 4", 'splash') => "review_style_four",
                    esc_html__("Review style 5", 'splash') => "review_style_5",
                    esc_html__("Review style 6", 'splash') => "review_style_6",
                )
            )
        )
    ));


    /*==============================================S=========================================*/

    vc_map(array(
        'name' => esc_html__('Slider', 'splash'),
        'base' => 'stm_slider',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'attach_images',
                'heading' => esc_html__('Images', 'splash'),
                'param_name' => 'images'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Image size', 'splash'),
                'param_name' => 'image_size',
                'value' => '1170x650',
                'description' => esc_html__('Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'splash'),
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'enable_thumbnails',
                'value' => array(
                    esc_html__('Enable Thumbs', 'splash') => 'enable'
                )
            )
        ),
    ));

    vc_map(array(
        'name' => esc_html__('STM Trophy', 'splash'),
        'base' => 'stm_trophy',
        'as_child' => array('only' => 'stm_trophies'),
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Image', 'splash'),
                'param_name' => 'image'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Image size', 'splash'),
                'param_name' => 'image_size',
                'value' => '170x259',
                'description' => esc_html__('Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'splash'),
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Year', 'splash'),
                'param_name' => 'year',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Trophy title', 'splash'),
                'param_name' => 'title',
            ),
        ),
    ));


    vc_map(array(
        "name" => esc_html__("Statistics Counter", 'splash'),
        "base" => "stm_stats_count",
        "class" => "stm_stats_count",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Icon', 'splash'),
                'param_name' => 'stat_icon',
            ), array(
                'type' => 'textfield',
                'heading' => esc_html__('Points', 'splash'),
                'param_name' => 'stat_points',
            ), array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'stat_title',
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Style", 'splash'),
                "description" => esc_html__("Change style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    esc_html__("Default", 'splash') => "default",
                    esc_html__("Style 1", 'splash') => "style_1",
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Social Counter (Work with plugin AccessPress Social Counter)", 'splash'),
        "base" => "stm_social_counter",
        "class" => "stm_social_counter",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'social_title',
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Stm Socials", 'splash'),
        "base" => "stm_socials",
        "class" => "stm_socials",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'dropdown',
                'heading'    => esc_html__('change style', 'splash'),
                'param_name' => 'style',
                'value' => array(
                    esc_html__('default','splash') => 'default',
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Share this (Works with plugin AddToAny Share Buttons)", 'splash'),
        "base" => "stm_share_this",
        "class" => "stm_share_this",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Style', 'splash'),
                'param_name' => 'share_style',
                'value' => array(
                    esc_html__('List', 'splash') => 'list',
                    esc_html__('List Fade Effect', 'splash') => 'list_fade',
                    esc_html__('Dropdown', 'splash') => 'dropdown'
                ),
            ),
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Position', 'splash'),
                'param_name' => 'position',
                'value' => array(
                    esc_html__('Right', 'splash') => 'right',
                    esc_html__('Left', 'splash') => 'left'
                ),
                'holder' => 'div'
            )
        )
    ));


    vc_map(array(
        'name' => esc_html__('STM Sidebar', 'splash'),
        'base' => 'stm_sidebar',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'dropdown',
                'heading' => esc_html__('Choose sidebar', 'splash'),
                'param_name' => 'sidebar',
                'value' => $stm_sidebars
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        )
    ));

    vc_map(array(
        'name' => esc_html__('STM Popular Video', 'splash'),
        'base' => 'stm_popular_video',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Popular video", 'splash'),
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Video', 'splash'),
                'param_name' => 'video_item',
                'value' => urlencode(json_encode(array(
                    array(
                        'video_title' => '',
                        'video_sub_title' => '',
                        'video_img' => '',
                        'video_embed_url' => '',
                    )
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video title", 'splash'),
                        "param_name" => "video_title",
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video subtitle", 'splash'),
                        "param_name" => "video_sub_title",
                    ),
                    array(
                        "type" => "attach_image",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video poster", 'splash'),
                        "param_name" => "video_img",
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video embed url", 'splash'),
                        "param_name" => "video_embed_url",
                    ),
                ),
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Style", 'splash'),
                "description" => esc_html__("Change style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    esc_html__("Default", 'splash') => "default",
                    esc_html__("Style 2", 'splash') => "style_2",
                )
            ),
        ),
    ));
    vc_map(array(
        'name' => esc_html__('Single Video', 'splash'),
        'base' => 'stm_single_video',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Single video", 'splash'),
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Video', 'splash'),
                'param_name' => 'video_item',
                'value' => urlencode(json_encode(array(
                    array(
                        'video_title' => '',
                        'video_sub_title' => '',
                        'video_img' => '',
                        'video_embed_url' => '',
                    )
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video subtitle", 'splash'),
                        "param_name" => "video_sub_title",
                    ),
                    array(
                        "type" => "attach_image",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video poster", 'splash'),
                        "param_name" => "video_img",
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Video embed url", 'splash'),
                        "param_name" => "video_embed_url",
                    ),
                ),
            ),
        ),
    ));

    vc_map(array(
        'name' => esc_html__('STM Features', 'splash'),
        'base' => 'stm_features',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "attach_image",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Background image", 'splash'),
                "param_name" => "features_bg_img",
            ),
            array(
                'type' => 'colorpicker',
                'param_name' => 'features_bg_color',
                'heading' => esc_html__('Background color', 'splash')
            ),
            array(
                'type' => 'colorpicker',
                'param_name' => 'features_line_color',
                'heading' => esc_html__('Bottom line color', 'splash')
            ),
            array(
                "type" => "attach_image",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Icon", 'splash'),
                "param_name" => "features_icon",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "features_title",
            ),
            array(
                'type' => 'textarea',
                'heading' => esc_html__('Text', 'splash'),
                'param_name' => 'features_content'
            ),
        )
    ));

    vc_map(array(
        'name' => esc_html__("STM Advertisment", 'splash'),
        'base' => 'stm_advertisment',
        'category' => esc_html__("STM", 'splash'),
        'params' => array(
            array(
                "type" => "attach_image",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Background image", 'splash'),
                "param_name" => "addvertisment_bg_img",
            ),
            array(
                "type" => "attach_image",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Logo", 'splash'),
                "param_name" => "addvertisment_logo",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "addvertisment_title",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("SubTitle", 'splash'),
                "param_name" => "addvertisment_sub_title",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Button text", 'splash'),
                "param_name" => "addvertisment_btn_text",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Link", 'splash'),
                "param_name" => "addvertisment_btn_link",
            )
        )
    ));

    vc_map(array(
        'name' => esc_html__('STM Tabs', 'splash'),
        'base' => 'stm_tabs',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "tabs_title",
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Tabs', 'splash'),
                'param_name' => 'tab_item',
                'value' => urlencode(json_encode(array(
                    array(
                        'tab_title' => '',
                        'tab_content' => ''
                    )
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Tab Title", 'splash'),
                        "param_name" => "tab_title",
                    ),
                    array(
                        'type' => 'textarea',
                        'heading' => esc_html__('Content', 'splash'),
                        'param_name' => 'tab_content'
                    )
                ),
            ),
        ),
    ));

    vc_map(array(
        'name' => esc_html__('STM Info Table', 'splash'),
        'base' => 'stm_info_table',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "info_table_title",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Columns", 'splash'),
                "value" => 3,
                "param_name" => "info_table_columns",
            ),
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Info', 'splash'),
                'param_name' => 'info_item',
                'value' => urlencode(json_encode(array(
                    array(
                        'info_title' => '',
                        'info_content' => '',
                        'info_link' => ''
                    )
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Title", 'splash'),
                        "param_name" => "info_title",
                    ),
                    array(
                        'type' => 'textarea',
                        'heading' => esc_html__('Content', 'splash'),
                        'param_name' => 'info_content'
                    ),
                    array(
                        'type' => 'checkbox',
                        'param_name' => 'show_info_link',
                        'value' => array(esc_html__('Show', 'splash') => 'enable'),
                        'heading' => esc_html__('Show link', 'splash')
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => esc_html__('Link Title', 'splash'),
                        'param_name' => 'info_link_title',
                        'dependency' => array(
                            'element' => 'show_info_link',
                            'value' => 'enable'
                        )
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => esc_html__('Link', 'splash'),
                        'param_name' => 'info_link',
                        'dependency' => array(
                            'element' => 'show_info_link',
                            'value' => 'enable'
                        )
                    )
                ),
            ),
        ),
    ));

    //tfnm - tickets_for_next_match
    vc_map(array(
        'name' => esc_html__('STM Tickets For Next Match', 'splash'),
        'base' => 'stm_tickets_for_next_match',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "tfnm_title",
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a event", 'splash'),
                "description" => esc_html__("This event will be displayed", 'splash'),
                "param_name" => "tfnm_event",
                "value" => $events_array
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Button title", 'splash'),
                "param_name" => "tfnm_btn_title",
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Button link", 'splash'),
                "param_name" => "tfnm_btn_link",
            ),
        )
    ));

    /*==============================================T=========================================*/
    vc_map(array(
        'name' => esc_html__('Trophies', 'splash'),
        'base' => 'stm_trophies',
        'as_parent' => array('only' => 'stm_trophy'),
        'icon' => 'stm_image_links',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'splash'),
                'param_name' => 'title',
                'value' => esc_html__('Awards', 'splash')
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'styles',
                'heading' => esc_html__('Styles', 'splash'),
                'value' => array(
                    esc_html__("Default", 'splash') => 'default',
                    esc_html__("Style 1", 'splash') => 'style_1',
                    esc_html__("Style 2", 'splash') => 'style_2',
                    esc_html__("Style 3", 'splash') => 'style_3',
                ),
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Trophies per row', 'splash'),
                'description' => esc_html__('Only numeric value', 'splash'),
                'param_name' => 'per_row',
                'value' => ''
            ),
        ),
        'js_view' => 'VcColumnView'
    ));

    vc_map(array(
        'name' => esc_html__('Team History', 'splash'),
        'base' => 'stm_team_history',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'param_group',
                'heading' => esc_html__('Year description', 'splash'),
                'param_name' => 'feature',
                'value' => urlencode(json_encode(array(
                    array(
                        'label' => esc_html__('Year', 'splash'),
                        'value' => '',
                    ),
                    array(
                        'label' => esc_html__('Title', 'splash'),
                        'value' => '',
                    ),
                    array(
                        'label' => esc_html__('Content', 'splash'),
                        'value' => '',
                    ),
                ))),
                'params' => array(
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Year", 'splash'),
                        "param_name" => "year",
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Title", 'splash'),
                        "param_name" => "title",
                    ),
                    array(
                        "type" => "textarea",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_html__("Content", 'splash'),
                        "param_name" => "content",
                    ),
                ),
            ),
        ),
    ));

    /*=============================================U=============================================*/
    vc_map(array(
        "name" => esc_html__("Upcoming fixtures", 'splash'),
        "base" => "stm_next_match_list",
        "class" => "stm_next_match_list",
        "controls" => "full",
        'category' => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Next Match", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "dropdown",
                "heading" => "View type",
                "param_name" => "view_type",
                "value" => array(
                    esc_html__("Style 1", 'splash') => "style_one",
                    esc_html__("Style 2", 'splash') => "style_two",
                    esc_html__("Style 3", 'splash') => "style_3",
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Follow link", 'splash'),
                "param_name" => "follow_link",
                "value" => esc_html__("Page link", 'splash'),
                "dependency" => array(
                    "element" => "view_type",
                    "value" => "style_one"
                )
            ),
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Background image', 'splash'),
                'param_name' => 'images',
                "dependency" => array(
                    "element" => "view_type",
                    "value" => "style_one"
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Show", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "show_games",
                "value" => array(
                    esc_html__('Certain number', 'splash') => 'number',
                    esc_html__('All games', 'splash') => 'all'
                ),
                "dependency" => array(
                    "element" => "view_type",
                    "value" => "style_two"
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Count", 'splash'),
                "param_name" => "count",
                "value" => 2,
                "dependency" => array(
                    "element" => "view_type",
                    "value" => "style_two"
                )
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a team", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "pick_team",
                "value" => $teams_array
            ),
        )
    ));

//next matches carousel

    vc_map(array(
        "name" => esc_html__("Next matches carousel", 'splash'),
        "base" => "stm_next_match_carousel",
        "class" => "stm_next_match_carousel",
        "controls" => "full",
        'category' => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Title", 'splash'),
                "param_name" => "title",
                "value" => esc_html__("Next Match", 'splash'),
                "description" => esc_html__("Enter text which will be used as widget title. Leave blank if no title is needed.", 'splash')
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Count", 'splash'),
                "param_name" => "count",
                "value" => 7,
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Pick a team", 'splash'),
                "description" => esc_html__("Fixtures by only this team will be displayed", 'splash'),
                "param_name" => "pick_team",
                "value" => $teams_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Style", 'splash'),
                "description" => esc_html__("Change style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    esc_html__("Style 1", 'splash') => "style_1",
                    esc_html__("Style 2", 'splash') => "style_2",
                    esc_html__("Style 3", 'splash') => "style_3",
                    esc_html__("Style 4", 'splash') => "style_4"
                )
            ),
            array(
                "type" => "textfield",
                "class" => "",
                "heading" => esc_html__("Slides count", 'splash'),
                "param_name" => "slide_count",
                "value" => 3,
                "min" => 1,
                "dependency" => array(
                    "element" => "view_style",
                    "value" => array(
                        "style_2",
                        "style_3"
                    )
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'navs_',
                'value' => array(
                    esc_html__('Enable navs', 'splash') => 'enable'
                ),
                "dependency" => array(
                    "element" => "view_style",
                    "value" => array(
                        "style_2",
                        "style_3"
                    )
                )
            ),
            array(
                'type' => 'checkbox',
                'param_name' => 'dots_',
                'value' => array(
                    esc_html__('Enable dots', 'splash') => 'enable'
                ),
                "dependency" => array(
                    "element" => "view_style",
                    "value" => array(
                        "style_2",
                        "style_3"
                    )
                )
            ),
        )
    ));

    //Timeline
    vc_map(array(
        'name' => esc_html__('STM Timeline', 'splash'),
        'base' => 'stm_timeline',
        'category' => esc_html__('STM', 'splash'),
        "as_parent" => array('only' => 'stm_timeline_item'),
        "is_container" => true,
        "content_element" => true,
        "show_settings_on_create" => false,
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Slider Width', 'splash'),
                'param_name' => 'slider_width'
            ),
            array(
                'type' => 'css_editor',
                'heading' => esc_html__('Css', 'splash'),
                'param_name' => 'css',
                'group' => esc_html__('Design options', 'splash')
            )
        ),
        "js_view" => 'VcColumnView'
    ));

    // Item
    vc_map(array(
        "name" => esc_html__('Item', 'splash'),
        "base" => "stm_timeline_item",
        "content_element" => true,
        "as_child" => array('only' => 'stm_timeline'),
        "params" => array(
            array(
                'type' => 'attach_image',
                'heading' => esc_html__('Image', 'splash'),
                'param_name' => 'img_id'
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Size', 'splash'),
                'param_name' => 'img_size',
                'description' => esc_html__('Image size. Example: 400x500', 'splash')
            ),
            array(
                "type" => 'textfield',
                "heading" => esc_html__('Title', 'splash'),
                "param_name" => 'title',
                'holder' => 'div'
            ),
            array(
                "type" => 'textarea_html',
                "heading" => esc_html__('Text', 'splash'),
                "param_name" => 'content'
            )
        )
    ));

//Post slider

    vc_map(array(
        "name" => esc_html__("Posts Slider", 'splash'),
        "base" => "stm_post_slider",
        "class" => "stm_post_slider",
        "description" => esc_html__('Posts Slider', 'splash'),
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                'type' => 'autocomplete',
                'heading' => esc_html__('Include Category', 'splash'),
                'param_name' => 'post_categories',
                'description' => esc_html__('Add Category. If not added show all category', 'splash'),
                'settings' => array(
                    'multiple' => true,
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $post_categories_arr
                )
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number Of Posts", 'splash'),
                "param_name" => "number_posts",
                "value" => esc_html__("4", 'splash'),
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("Single video post", 'splash'),
        "base" => "stm_video_post",
        "class" => "stm_video_post",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Post id #", 'splash'),
                "param_name" => "post_id",
            )
        )
    ));

    vc_map(array(
        "name" => esc_html__("STM Players Tabs", 'splash'),
        "base" => "stm_players_tabs",
        "class" => "stm_players_tabs",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_html__("Number of Players", 'splash'),
                "param_name" => "count",
                "value" => '4'
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose League", 'splash'),
                "param_name" => "league",
                "value" => $leagues_array
            ),
            array(
                "type" => "dropdown",
                "class" => "",
                "heading" => esc_html__("Choose Season", 'splash'),
                "param_name" => "season",
                "value" => $seasons_array
            ),
            array(
                "type" => "dropdown",
                "heading" => esc_html__("Style", 'splash'),
                "param_name" => "view_style",
                "value" => array(
                    "Style 1" => "style_1",
                    "Style 2" => "style_2"
                )
            ),
        )
    ));

    vc_map(array(
        "name" => esc_html__("Single post tags", 'splash'),
        "base" => "stm_post_tags",
        "class" => "stm_post_tags",
        "controls" => "full",
        "category" => esc_html__('STM', 'splash'),
    ));

    vc_map(array(
        'name' => esc_html__('Spacing', 'splash'),
        'base' => 'stm_spacing',
        'category' => esc_html__('STM', 'splash'),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Large Screen', 'splash'),
                'param_name' => 'lg_spacing',
                'description' => esc_html__('Only number value', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Medium Screen', 'splash'),
                'param_name' => 'md_spacing',
                'description' => esc_html__('Only number value', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Small Screen', 'splash'),
                'param_name' => 'sm_spacing',
                'description' => esc_html__('Only number value', 'splash')
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Extra Small Screen', 'splash'),
                'param_name' => 'xs_spacing',
                'description' => esc_html__('Only number value', 'splash')
            )
        )
    ));

}


if (class_exists('WPBakeryShortCodesContainer')) {
    class WPBakeryShortCode_Stm_Trophies extends WPBakeryShortCodesContainer
    {
    }

    class WPBakeryShortCode_Stm_Gmap_With_Info_Blocks extends WPBakeryShortCodesContainer
    {
    }

    class WPBakeryShortCode_Stm_Timeline extends WPBakeryShortCodesContainer
    {

    }
}

if (class_exists('WPBakeryShortCode')) {
    class WPBakeryShortCode_Stm_Spacing extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Icon_List extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Players_Tabs extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Next_Match_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Post_Tags extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Video_Post extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Button extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Socials extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Call_To_Action extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Next_Match extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Next_Match_List extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Latest_Results extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Latest_Results_carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Results_Table_Ajax extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Af_Latest_Result extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Player_Quick_Stats extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Players_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Reviews_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Media_Tabs extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_News_Tabs extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Latest_News extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Latest_News_Most_Styles extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Products_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Posts_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Video_Posts_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Video_Posts_List extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Player_Of_Month extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Player_Statistic extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Af_Player_Statistic extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_League_Table extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_League_Table_With_Ajax extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Trophy extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Carousel extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Carousel_With_Data extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Slider extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Images_Grid extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Price_Plan extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Contact_Info extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_STM_Contact_Manager extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Gmap extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Team_History extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Media_Archive extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Stats_Count extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Social_Counter extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Latest_Tweets extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Countdown extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Coach_Excerption extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Share_This extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Block_Quote extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Sidebar extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Posts_Ticker extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Popular_Video extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Single_Video extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Features extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Advertisment extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Tabs extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Info_Table extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Tickets_For_Next_Match extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Carousel_Image_Title extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Gmap_Info_Block extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Heading_With_Icon extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Timeline_Item extends WPBakeryShortCode
    {
    }

    class WPBakeryShortCode_Stm_Post_Slider extends WPBakeryShortCode
    {
    }
}
