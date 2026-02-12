<?php

namespace WPAdminify\Pro;

// no direct access allowed
if (!defined('ABSPATH'))  exit;

/**
 * WPAdminify
 * @package Admin Pages
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */


class AdminPages_Render extends AdminPagesModel
{

    public function __construct($post, $from_multisite = false)
    {
        if (!empty($post->remove_admin_notices)) {
            remove_all_actions('user_admin_notices');
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            remove_all_actions('network_admin_notices');
            // Output CSS to hide any notices that already rendered (like Freemius sticky notices)
            $this->hide_notices_css();
        }

        $this->init_admin_page_content_new($post, $from_multisite);
    }

    /**
     * Output CSS to forcefully hide admin notices
     */
    private function hide_notices_css()
    {
        ?>
        <style>
            .wrap > .notice,
            .wrap > .updated,
            .wrap > .error,
            .wrap > .warning,
            #wpbody-content > .notice,
            #wpbody-content > .updated,
            #wpbody-content > .error,
            #wpbody-content > .fs-notice,
            .fs-notice.fs-sticky,
            .fs-notice.updated,
            .fs-notice.success,
            div.fs-notice,
            [class*="fs-notice"] {
                display: none !important;
            }
        </style>
        <?php
    }

    public function init_admin_page_content_new($post, $from_multisite)
    {

        $remove_page_title  = $post->remove_page_title;
        $remove_page_margin = $post->remove_page_margin;

        $custom_css = $post->custom_css;

        $link = get_permalink($post);
        $link = add_query_arg('bknd', 1, $link);

        printf('<iframe class="wp-adminify--admin-page" src="%s"></iframe>', esc_url( $link ) );

    ?>

        <style>
            .wp-adminify #wpbody{
                overflow:hidden;
            }
            .wp-adminify #wpbody-content {
                position: relative;
                overflow: hidden;
            }

            iframe.wp-adminify--admin-page {
                width: 100%;
                height: 100%;
                position: relative;
            }

            <?php if ($remove_page_margin) : ?>#wpcontent {
                padding-left: 0;
            }

            .wrap {
                margin: 0;
            }

            <?php endif; ?>
        </style>
    <?php

    }
}
