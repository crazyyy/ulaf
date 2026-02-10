<?php
/*
Plugin Name: php snippet for theme designers
Plugin URI: http://plugin.php-web.net/wp/tema-dezaina-no-tame-no-phpsunipetto
Description: This plugin diplays a is_page()/is_category()/is_tag() snippet for page/category/tag list. You can go to the page/category/tag list in the admin area, and you can copy one (and paste it to a theme file).
Version: 5.0
Author: Fumito Mizuno
Author URI: http://php-web.net/
License: GPL ver.2 or later
 */


function psftd_i18n() {
    load_plugin_textdomain( 'psftd', false, 'php-snippets-for-theme-designer/lang' );
}
add_action( 'admin_init', 'psftd_i18n' );


require(plugin_dir_path( __FILE__ ) . 'js_css.php');
require(plugin_dir_path( __FILE__ ) . 'class.php');


function psftd_readonly_textarea( $data, $rows=3, $cols=30 ) {
    $data = esc_html( $data );
    $rows = intval( $rows );
    $cols = intval( $cols );
    return '<textarea readonly rows="' . $rows . '" cols="' . $cols . '" onclick="this.focus();this.select()">' . $data . '</textarea>';
}

function psftd_create_snippet_if( $condition, $id, $slug ) {
    $condition = esc_html( $condition );
    $id = esc_html( $id );
    $slug = esc_html( urldecode( $slug ) );
    $snippetformat = '&lt;?php if ( %1$s ( \'%2$s\' ) ): // %3$s  ?&gt; ' . "\n\n" . '&lt;?php endif; // %1$s( \'%2$s\' ) %3$s  ?&gt;';
    return sprintf($snippetformat, $condition, $id, $slug);
}

function psftd_create_snippet_url( $func_name, $id, $slug ) {
    $func_name = esc_html( $func_name );
    $id = esc_html( $id );
    $slug = esc_html( urldecode( $slug ) );
    $snippetformat = '&lt;?php echo esc_url( %1$s ( \'%2$s\' ) ); // %3$s  ?&gt;';
    return sprintf($snippetformat, $func_name, $id, $slug);
}

function psftd_add_snippet_column( $defaults ) {
    $defaults['linksnippet'] = __('URL', 'psftd') ;
    $defaults['ifsnippet'] = __('IF', 'psftd') ;
    return $defaults;
}
add_filter('manage_pages_columns', 'psftd_add_snippet_column');
add_filter('manage_posts_columns', 'psftd_add_snippet_column');

function psftd_add_categories_snippet_column( $defaults ) {
    $defaults['ifsnippet'] = __('IF', 'psftd') ;
    $defaults['linksnippet'] = __('LINK', 'psftd') ;
    return $defaults;
}
add_filter('manage_edit-category_columns', 'psftd_add_categories_snippet_column');
add_filter('manage_edit-post_tag_columns', 'psftd_add_categories_snippet_column');

function psftd_add_snippet_text($column_name, $id) {
    if( $column_name == 'ifsnippet' ) {
        $pageinfo = get_page( $id );
        $slug = get_post_type($pageinfo->ID) . ': ' . $pageinfo->post_name;
        $output = psftd_create_snippet_if( 'is_page', $pageinfo->ID, $slug );
        print psftd_readonly_textarea( $output );
    } elseif( $column_name == 'linksnippet' ) {
        $pageinfo = get_page( $id );
        $slug = get_post_type($pageinfo->ID) . ': ' . $pageinfo->post_name;
        $output = psftd_create_snippet_url( 'get_permalink', $pageinfo->ID, $slug );
        print psftd_readonly_textarea( $output );
    }

}
add_action('manage_pages_custom_column', 'psftd_add_snippet_text', 10, 2);

function psftd_add_post_snippet_text($column_name, $id) {
    if( $column_name == 'ifsnippet' ) {
        $pageinfo = get_page( $id );
        $slug = get_post_type($pageinfo->ID) . ': ' . $pageinfo->post_name;
        $output = psftd_create_snippet_if( 'is_single', $pageinfo->ID, $slug );
        print psftd_readonly_textarea( $output );
    } elseif( $column_name == 'linksnippet' ) {
        $pageinfo = get_page( $id );
        $slug = get_post_type($pageinfo->ID) . ': ' . $pageinfo->post_name;
        $output = psftd_create_snippet_url( 'get_permalink', $pageinfo->ID, $slug );
        print psftd_readonly_textarea( $output );
    }

}
add_action('manage_posts_custom_column', 'psftd_add_post_snippet_text', 10, 2);

function psftd_add_categories_snippet_text($null,$column_name, $id) {
    if( $column_name == 'ifsnippet' ) {
        $cat_name = get_cat_name($id);
        $output = psftd_create_snippet_if( 'is_category', $id, $cat_name );
        return psftd_readonly_textarea($output);
    } elseif( $column_name == 'linksnippet' ) {
        $cat_name = get_cat_name($id);
        $output = psftd_create_snippet_url( 'get_category_link', $id, $cat_name );
        print psftd_readonly_textarea( $output );
    }
}
add_filter('manage_category_custom_column', 'psftd_add_categories_snippet_text', 10, 3);

function psftd_add_post_tag_snippet_text($null,$column_name, $id) {
    if( $column_name == 'ifsnippet' ) {
        $tag_array = get_term_by('id',$id,'post_tag');
        $output = psftd_create_snippet_if( 'is_tag', $id, $tag_array->name );
        return psftd_readonly_textarea($output);
    } elseif( $column_name == 'linksnippet' ) {
        $tag_array = get_term_by('id',$id,'post_tag');
        $output = psftd_create_snippet_url( 'get_tag_link', $id, $tag_array->name );
        print psftd_readonly_textarea( $output );
    }
}
add_filter('manage_post_tag_custom_column', 'psftd_add_post_tag_snippet_text', 10, 3);
