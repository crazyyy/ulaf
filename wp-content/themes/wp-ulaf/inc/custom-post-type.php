<?php
/**
  *  Author: Vitalii A | @knaipa
  *  URL: https://github.com/crazyyy/wp-framework
  *  Initialize Custom Post Types and Taxonomies
*/

// Adding a column for the ACF field 'category' in the list of entries for the post type 'team'
add_filter('manage_team_posts_columns', 'add_team_category_column');
function add_team_category_column($columns) {
    $columns['team_category'] = __('Category', 'text-domain');
    return $columns;
}

// Displaying the ACF value of the 'category' field in a column
add_action('manage_team_posts_custom_column', 'display_team_category_column', 10, 2);
function display_team_category_column($column, $post_id) {
    if ($column === 'team_category') {
        // Getting the value of the ACF field 'category'
        $category = get_field('category', $post_id);
        if ($category) {
            // If the field is a taxonomy
            if (is_array($category) || is_object($category)) {
                $terms = [];
                if (is_array($category)) {
                  echo '<strong>' . $category['label'] . '</strong>';
                } elseif (is_object($category)) {
                    echo '<strong>' . $category->value . '</strong>';
                }
            } else {
                // If the field is a simple text
                echo esc_html($category);
            }
        } else {
            echo '—';
        }
    }
}

// Adding sorting functionality for the column
add_filter('manage_edit-team_sortable_columns', 'make_team_category_column_sortable');
function make_team_category_column_sortable($columns) {
    $columns['team_category'] = 'team_category';
    return $columns;
}

// Processing sorting for ACF field
add_action('pre_get_posts', 'sort_team_category_column');
function sort_team_category_column($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') === 'team' && $query->get('orderby') === 'team_category') {
        $query->set('meta_key', 'category');
        $query->set('orderby', 'meta_value');
    }
}