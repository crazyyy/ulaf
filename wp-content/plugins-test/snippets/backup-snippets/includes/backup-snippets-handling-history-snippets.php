<?php

/**
 * @package Backup_Snippets\Functions
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Handling history
if(!function_exists('backup_snippets_handling_history_snippets')) {
    function backup_snippets_handling_history_snippets() {
        global $pagenow, $wpdb, $table_prefix;

        $main_table = $table_prefix . BACKUP_SNIPPETS_MAIN_TABLE;
        $page = get_current_screen()->base;
        $id = (isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '');

        // check current page
        if ($pagenow != 'admin.php') {
            return;
        }

        // check partial page
        if ($page != 'snippets_page_edit-snippet' ) {
            return;
        }

        $all_backup_snippets = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $main_table WHERE id_snippet = %d ORDER BY modified DESC LIMIT %d OFFSET 1", $id, BACKUP_SNIPPETS_LIMIT_HISTORY_SNIPPETS)
        );

        // html
        $btn_html = '<button id="seeHistory" type="button" class="button button-small button-primary">See History</button>';
        $overlay_html = '<div class="bucs-overlay"></div>
        <div class="bucs-notification">Write a message here..</div>';
        $main_html = '<div class="bucs-wrapper">
            <div id="hideHistory">&#10006;</div>
            <h2>Backup Snippets</h2>';

        if(count($all_backup_snippets) > 0) {
            $i = 1;

            $main_html .= '<h3>Revisions</h3>';
            $main_html .= '<select id="bucs-navtab-editor">';

            foreach($all_backup_snippets as $snippet) {
                $main_html .= '<option value="bucs-codemirror' . $snippet->id . '">#' . $i .  ' ' . date('M d, Y H:i A', strtotime($snippet->modified)) . '</option>';

                $i++;
            }

            $main_html .= '</select>';
        }
        
        if(count($all_backup_snippets) > 0) {
            $j = 1;

            $main_html .= '<h3>Code</h3>';

            foreach($all_backup_snippets as $snippet) {
                $main_html .= '<div class="bucs-tab-editor" data-id="bucs-codemirror' . $snippet->id . '" data-hidden="' . ($j === 1 ? '' : 'hidden') . '">';
                $main_html .= '<textarea class="bucs-codemirror" id="bucs-codemirror' . $snippet->id . '">';
                $main_html .= $snippet->code;
                $main_html .= '</textarea>';
                $main_html .= '<button type="button" class="button button-small bucs-hide-history">Close</button>';

                // Pending feature
                // $main_html .= '<button data-id="bucs-codemirror' . $snippet->id . '" type="button" class="button button-small button-primary bucs-copy-to-clipboard">Copy to Clipboard</button>';
                
                $main_html .= '</div>';

                $j++;
            }
        }

        $main_html .= '</div>';

        wp_enqueue_style('codemirror-css', BACKUP_SNIPPETS_PLUGIN_URL . 'admin/css/codemirror.min.css', array(), '6.65.7', 'all');
        wp_enqueue_style('backup-snippets-handling-history-snippets-css', BACKUP_SNIPPETS_PLUGIN_URL . 'admin/css/handling-history-snippets.css', array(), filemtime(BACKUP_SNIPPETS_PLUGIN_DIR . 'admin/css/handling-history-snippets.css'), 'all');

        wp_enqueue_script('codemirror-js', BACKUP_SNIPPETS_PLUGIN_URL . 'admin/js/codemirror.min.js', array('jquery'), '6.65.7', true);
        wp_enqueue_script('backup-snippets-handling-history-snippets-js', BACKUP_SNIPPETS_PLUGIN_URL . 'admin/js/handling-history-snippets.js', array('jquery'), filemtime(BACKUP_SNIPPETS_PLUGIN_DIR . 'admin/js/handling-history-snippets.js'), true);

        wp_localize_script(
            'backup-snippets-handling-history-snippets-js',
            'ajaxObj',
            array(
                'btn_html' => esc_html($btn_html),
                'overlay_html' => esc_html($overlay_html),
                'main_html' => esc_html($main_html),
            )
        );
    }
    add_action( 'admin_enqueue_scripts', 'backup_snippets_handling_history_snippets' );
}