<?php

/**
 * MPlugins hooks
 *
 * @package AdminTweaks
 */

namespace ADTW;

class HooksPlugins
{
    /**
     * Check options and dispatch hooks
     * 
     * @param  array $options
     * @return void
     */
    public function __construct()
    {

        # DISABLE PLUGIN UPDATE NOTICES
        if (ADTW()->getOption('plugins_block_update_notice')) {
            add_filter(
                'pre_site_transient_update_plugins',
                '__return_null'
            );
            add_action('load-plugins.php', function () {
                add_action(
                    'pre_current_active_plugins',
                    [$this, 'warn_update_nag_deactivated'],
                    999
                );
            });
        }

        # DISABLE INACTIVE PLUGIN UPDATE NOTICES
        if (ADTW()->getOption('plugins_block_update_inactive_plugins') && !is_multisite()) {
            add_filter(
                'site_transient_update_plugins',
                [$this, 'remove_update_nag_for_deactivated']
            );
            add_action('load-plugins.php', function () {
                add_action(
                    'pre_current_active_plugins',
                    [$this, 'warn_update_nag_deactivated'],
                    999
                );
            });
        }

        # DISABLE EMAIL AUTO-UPDATE NOTICES
        if (ADTW()->getOption('plugins_block_emails_updates')) {
            add_filter(
                'auto_plugin_update_send_email',
                '__return_false'
            );
        }

        # FILTER BY 
        if (ADTW()->getOption('plugins_live_filter')) {
            add_action(
                'admin_print_footer_scripts-plugins.php',
                [$this, 'printFilterPlugins']
            );
        }

        # ADD LAST UPDATED INFORMATION
        if (ADTW()->getOption('plugins_add_last_updated')) {
            add_filter(
                'plugin_row_meta',
                [$this, 'lastUpdated'],
                10,
                4
            );
        }

        # ALL CSS and JS OPTIONS CHECKED INSIDE
        add_action(
            'admin_head-plugins.php',
            [$this, 'pluginsCSSJS']
        );
    }

    /**
     * CSS and JS for Filter By
     */
    public function printFilterPlugins()
    {
        $assets = ADTW_URL . '/assets';
        wp_register_style(
            'mtt-filterby',
            "$assets/filter-listings.css",
            [],
            ADTW()->cache('filter-listings.css')
        );
        wp_register_script(
            'mtt-filters',
            "$assets/filters-common.js",
            [],
            ADTW()->cache('filters-common.js')
        );
        wp_register_script(
            'mtt-filterby',
            "$assets/filter-plugins.js",
            ['mtt-filters', 'jquery'],
            ADTW()->cache('filter-plugins.js')
        );
        wp_enqueue_style('mtt-filterby');
        wp_enqueue_script('mtt-filterby');

        wp_add_inline_script(
            'mtt-filterby',
            'const ADTW = ' . json_encode([
                'html' => $this->_filtersHtml(),
                'plugin_users' => ADTW()->getOption('plugins_my_plugins_names'),
                'hide_row_actions' => ADTW()->getOption('plugins_live_description')
            ]),
            'before'
        );
    }

    private function _filtersHtml()
    {
        $strings = [
            'icon_title'         => 'by ' . AdminTweaks::NAME,
            'desc_show'          => esc_html__('Show descriptions', 'mtt'),
            'desc_hide'          => esc_html__('Hide descriptions', 'mtt'),
            'desc_label'         => esc_html__('Description', 'mtt'),
            'show_all'           => esc_html__('Show all', 'mtt'),
            'active_show'        => esc_html__('Show active', 'mtt'),
            'active_label'       => esc_html__('Active', 'mtt'),
            'inactive_show'      => esc_html__('Show inactive', 'mtt'),
            'inactive_label'     => esc_html__('Inactive', 'mtt'),
            'filter_placeholder' => esc_html__('filter by keyword', 'mtt'),
            'filter_title'       => esc_html__('enter a string to filter the list', 'mtt'),
            'mine_show'          => esc_html__('Show mine', 'mtt'),
            'mine_label'         => esc_html__('Mine', 'mtt'),
        ];

        // Build the mine button conditionally
        $mine_button = '';
        if (!empty(ADTW()->getOption('plugins_my_plugins_bg_color')) && !empty(ADTW()->getOption('plugins_my_plugins_names'))) {
            $mine_button = sprintf(
                '<button id="hide-mine" class="button b5f-button b5f-btn-status" 
                    title="%1$s" 
                    data-title-hide="%2$s" 
                    data-title-show="%1$s">
                %3$s</button>',
                $strings['mine_show'],
                $strings['show_all'],
                $strings['mine_label']
            );
        }

        return sprintf(
            '<div class="mysearch-wrapper">
                <span class="dashicons dashicons-image-filter b5f-icon" title="%1$s"></span> 
                
                <!-- Description Toggle -->
                <button id="hide-desc" class="button b5f-button" 
                    title="%2$s" 
                    data-title-hide="%2$s" 
                    data-title-show="%3$s">
                    %4$s
                </button> 
                
                <!-- Status Filters -->
                <button id="hide-active" class="button b5f-button b5f-btn-status" 
                    title="%5$s" 
                    data-title-hide="%6$s" 
                    data-title-show="%5$s">
                    %7$s
                </button> 
                
                <button id="hide-inactive" class="button b5f-button b5f-btn-status" 
                    title="%8$s" 
                    data-title-hide="%6$s" 
                    data-title-show="%8$s">
                    %9$s
                </button>
                
                %10$s
                
                <!-- Search Box -->
                <input type="text" id="b5f-plugins-filter" class="mysearch-box" 
                    name="focus" value="" placeholder="%11$s" 
                    title="%12$s" />
                <button class="close-icon" type="reset"></button>
            </div>',
            // Parameters
            $strings['icon_title'],          // 1
            $strings['desc_show'],           // 2
            $strings['desc_hide'],           // 3
            $strings['desc_label'],          // 4
            $strings['active_show'],         // 5
            $strings['show_all'],            // 6
            $strings['active_label'],        // 7
            $strings['inactive_show'],       // 8
            $strings['inactive_label'],      // 9
            $mine_button,                    // 10
            $strings['filter_placeholder'],  // 11
            $strings['filter_title']         // 12
        );
    }

    private function _filtersHtmlOLD()
    {
        return sprintf(
            '<div class="mysearch-wrapper">
            <span class="dashicons dashicons-image-filter b5f-icon" 
                title="%1$s">
            </span> 
            <button id="hide-desc" class="button b5f-button" 
                title="%2$s" 
                data-title-hide="%2$s" 
                data-title-show="%3$s">
            %4$s</button> 
            <button id="hide-active" class="button b5f-button b5f-btn-status" 
                title="%6$s" 
                data-title-hide="%5$s" 
                data-title-show="%6$s">
            %7$s</button> 
            <button id="hide-inactive" class="button b5f-button b5f-btn-status" 
                title="%8$s" 
                data-title-hide="%5$s" 
                data-title-show="%8$s">
            %9$s</button>
            <button id="hide-mine" class="button b5f-button b5f-btn-status" 
                title="%12$s" 
                data-title-hide="%5$s" 
                data-title-show="%12$s">
            %13$s</button>
            <input type="text" id="b5f-plugins-filter" class="mysearch-box" 
                name="focus" value="" placeholder="%10$s" 
                title="%11$s" />
            <button class="close-icon" type="reset"></button>
            </div>',
            'by ' . AdminTweaks::NAME,                #1
            esc_html__('Show descriptions', 'mtt'), #2
            esc_html__('Hide descriptions', 'mtt'), #3
            esc_html__('Description', 'mtt'), #4
            esc_html__('Show all', 'mtt'), #5
            esc_html__('Show active', 'mtt'), #6
            esc_html__('Active', 'mtt'), #7
            esc_html__('Show inactive', 'mtt'), #9
            esc_html__('Inactive', 'mtt'),
            esc_html__('filter by keyword', 'mtt'),
            esc_html__('enter a string to filter the list', 'mtt'),
            esc_html__('Show mine', 'mtt'),
            esc_html__('Mine', 'mtt'),
        );
    }

    public function warn_update_nag_deactivated()
    {
        $setts = sprintf(
            '<a href="%s">(%s)</a>',
            admin_url('admin.php?page=admintweaks&tab=8'),
            __('settings', 'mtt')
        );
        if (ADTW()->getOption('plugins_block_update_inactive_plugins')) {
            # deactivated only
            $base = __('UPDATES NOT SHOWING for disabled plugins', 'mtt');
        } else if (ADTW()->getOption('plugins_block_update_notice')) {
            # all plugins
            $base = __('UPDATES NOT SHOWING for all plugins', 'mtt');
        }
        echo "<div class='notice notice-warning inline  is-dismissible'><p>$base $setts</p></div>";
    }

    /**
     * Remove update notice for desactived plugins
     * Tip via: https://wordpress.stackexchange.com/a/77155/12615
     * 
     * @param type $value
     * @return type
     */
    public function remove_update_nag_for_deactivated($value)
    {
        if (empty($value) || empty($value->response))
            return $value;
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        foreach ($value->response as $key => $val) {
            if (!\is_plugin_active($val->plugin))
                unset($value->response[$key]);
        }
        return $value;
    }


    /**
     * Remove Action Links
     * 
     * @return empty
     */
    public function remove_action_links()
    {
        return;
    }


    /**
     * Add Last Updated information to the Meta row (author, plugin url)
     * 
     * @param string $plugin_meta
     * @param type $plugin_file
     * @return string
     */
    public function lastUpdated($plugin_meta, $pluginfile, $plugin_data, $status)
    {
        // If Multisite, only show in network admin
        if (is_multisite() && !is_network_admin())
            return $plugin_meta;

        list($slug) = explode('/', $pluginfile);

        $slug_hash = md5($slug);
        $last_updated = get_transient("range_plu_{$slug_hash}");
        if (false === $last_updated) {
            $last_updated = $this->get_last_updated($slug);
            set_transient("range_plu_{$slug_hash}", $last_updated, 86400);
        }

        if ($last_updated)
            $plugin_meta['last_updated'] = '<br>' . esc_html__('Last Updated', 'mtt')
                . esc_html(': ' . $last_updated);

        return $plugin_meta;
    }


    /**
     * Custom CSS for Plugins page
     * 
     * @return string Echo 
     */
    public function pluginsCSSJS()
    {
        $display_count = ADTW()->getOption('plugins_my_plugins_count');

        // GENERAL OUTPUT
        $output = '';

        // UPDATE NOTICE
        if (ADTW()->getOption('plugins_remove_plugin_notice'))
            $output .= '.update-message{display:none;} ';

        // INACTIVE
        if (ADTW()->getOption('plugins_inactive_bg_color'))
            $output .= 'tr.inactive {background-color:' . ADTW()->getOption('plugins_inactive_bg_color') . ' !important;}';

        if (!empty($output)) {
            echo '<style type="text/css">' . $output . ' </style>' . "\r\n";
        }
        // YOUR PLUGINS COLOR
        if (
            ADTW()->getOption('plugins_my_plugins_bg_color')
            && ADTW()->getOption('plugins_my_plugins_names')
            && ADTW()->getOption('plugins_my_plugins_color')
        ) {
            $authors = explode(',', ADTW()->getOption('plugins_my_plugins_names'));

            $jq = array();
            foreach ($authors as $author) {
                $jq[] = "tr td.column-description:Contains('{$author}')";
            }
            $jq_ok = implode(',', $jq);
            $by_author = esc_html__('by selected author(s)', 'mtt');
?>
            <script type="text/javascript">
                // https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
                jQuery.expr[':'].Contains = function(a, i, m) {
                    return jQuery(a).text().toUpperCase()
                        .indexOf(m[3].toUpperCase()) >= 0;
                };
                jQuery(document).ready(function($) {
                    <?php if ($display_count): ?>
                        // Display author count
                        var atual = $('.displaying-num').html();
                        $('.displaying-num').html(atual + ' : ' + $("#the-list").find("<?php echo $jq_ok; ?>").length + ' ' + '<?php echo $by_author; ?>');
                    <?php endif; ?>

                    // Modify the plugin rows background
                    $("<?php echo $jq_ok; ?>").each(function() {
                        var parent = $(this).parent();
                        if (parent.hasClass('inactive'))
                            opac = '0.6';
                        else
                            opac = '1';
                        //$(this).removeClass('inactive');
                        parent.find('td,th').css('background-color', '<?php echo ADTW()->getOption('plugins_my_plugins_color'); ?>');
                        parent.css('opacity', opac);
                    });
                });
            </script>
<?php
        }
    }


    /**
     * Query WP API
     * from the plugin https://wordpress.org/plugins/plugin-last-updated/
     * 
     * @param type $slug
     * @return boolean|string
     */
    private function get_last_updated($slug)
    {
        $request = wp_remote_post(
            'https://api.wordpress.org/plugins/info/1.0/',
            array(
                'body' => array(
                    'action'     => 'plugin_information',
                    'request'     => serialize(
                        (object) array(
                            'slug'     => $slug,
                            'fields' => array('last_updated' => true)
                        )
                    )
                )
            )
        );
        if (200 != wp_remote_retrieve_response_code($request))
            return false;

        $response = unserialize(wp_remote_retrieve_body($request));
        // Return an empty but cachable response if the plugin isn't in the .org repo
        if (empty($response))
            return '';
        if (isset($response->last_updated))
            return sanitize_text_field($response->last_updated);

        return false;
    }
}
