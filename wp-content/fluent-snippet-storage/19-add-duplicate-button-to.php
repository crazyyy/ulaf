<?php
// <Internal Doc Start>
/*
*
* @description: The following snippet will add a "Duplicate" post button to the posts table.


* @tags: 
* @group: 
* @name: Add duplicate button to post management table
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:54:16
* @updated_at: 2026-02-13 23:54:16
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: all
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
/*
 * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
 */
function rd_duplicate_post_as_draft()
{
    if (!current_user_can("edit_posts")) {
        return;
    }
    
    /*
     * Nonce verification
     */
    if (
        !isset($_GET["duplicate_nonce"]) ||
        !wp_verify_nonce($_GET["duplicate_nonce"], basename(__FILE__))
    ) {
        return;
    }
    
    global $wpdb;
    
    if (
        !(
            isset($_GET["post"]) ||
            isset($_POST["post"]) ||
            (isset($_REQUEST["action"]) &&
                "rd_duplicate_post_as_draft" == $_REQUEST["action"])
        )
    ) {
        wp_die("No post to duplicate has been supplied!");
    }



    /*
     * get the original post id
     */
    $post_id = isset($_GET["post"])
        ? absint($_GET["post"])
        : absint($_POST["post"]);
    /*
     * and all the original post data then
     */
    $post = get_post($post_id);

    /*
     * if you don't want current user to be the new post author,
     * then change next couple of lines to this: $new_post_author = $post->post_author;
     */
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    /*
     * if post data exists, create the post duplicate
     */
    if (isset($post) && $post != null) {
        /*
         * new post data array
         */
        $args = [
            "comment_status" => $post->comment_status,
            "ping_status" => $post->ping_status,
            "post_author" => $new_post_author,
            "post_content" => $post->post_content,
            "post_excerpt" => $post->post_excerpt,
            "post_name" => $post->post_name,
            "post_parent" => $post->post_parent,
            "post_password" => $post->post_password,
            "post_status" => "draft",
            "post_title" => $post->post_title,
            "post_type" => $post->post_type,
            "to_ping" => $post->to_ping,
            "menu_order" => $post->menu_order,
        ];

        /*
         * insert the post by wp_insert_post() function
         */
        $new_post_id = wp_insert_post($args);

        /*
         * get all current post terms ad set them to the new post draft
         */
        $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, [
                "fields" => "slugs",
            ]);
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        /*
         * duplicate all post meta just in two SQL queries
         */
        $post_meta_infos = $wpdb->get_results(
            "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id"
        );
        if (count($post_meta_infos) != 0) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                if ($meta_key == "_wp_old_slug") {
                    continue;
                }
                $meta_value = addslashes($meta_info->meta_value);
                $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }
            $sql_query .= implode(" UNION ALL ", $sql_query_sel);
            $wpdb->query($sql_query);
        }

        /*
         * finally, redirect to the edit post screen for the new draft
         */

        // wp_safe_redirect( wp_get_referer() ); // Option to not get redirected to new post edit page, but stay where one was.
        // wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) ); // replace this line with the following
        wp_safe_redirect(
            admin_url("post.php?action=edit&post=" . $new_post_id)
        ); // Security related edit: @link https://developer.wordpress.org/reference/functions/wp_safe_redirect/
        exit();
    } else {
        wp_die(
            "Post creation failed, could not find original post: " . $post_id
        );
    }
}
add_action(
    "admin_action_rd_duplicate_post_as_draft",
    "rd_duplicate_post_as_draft"
);

/*
 * Add the duplicate link to action list for post_row_actions
 */
function rd_duplicate_post_link($actions, $post)
{
    if (current_user_can("edit_posts")) {
        $actions["duplicate"] =
            '<a href="' .
            wp_nonce_url(
                "admin.php?action=rd_duplicate_post_as_draft&post=" . $post->ID,
                basename(__FILE__),
                "duplicate_nonce"
            ) .
            '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    }
    return $actions;
}

add_filter("post_row_actions", "rd_duplicate_post_link", 10, 2);
add_filter("page_row_actions", "rd_duplicate_post_link", 10, 2); // add the link to pages too