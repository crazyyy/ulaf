<?php

/**
 * AJAX action handler for fetching and validating RSS feeds.
 * Similar to the CSV parse action, this verifies feeds before generation.
 *
 * @package AIPKit
 * @since NEXT_VERSION
 */

namespace WPAICG\Lib\ContentWriter\Ajax\Actions;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Base_Ajax_Action;
use WPAICG\Lib\ContentWriter\AIPKit_Rss_Feed_Parser;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles RSS feed fetching/verification requests.
 */
class AIPKit_Content_Writer_Fetch_Rss_Action extends AIPKit_Content_Writer_Base_Ajax_Action
{
    /**
     * Handle the AJAX request.
     *
     * @return void
     */
    public function handle()
    {
        // Permission check
        if (!\WPAICG\AIPKit_Role_Manager::user_can_access_module('content-writer') && !\WPAICG\AIPKit_Role_Manager::user_can_access_module('autogpt')) {
            $this->send_wp_error(new WP_Error('permission_denied', __('You do not have permission to use this feature.', 'gpt3-ai-content-generator'), ['status' => 403]));
            return;
        }

        // Flexible nonce check (supports both content writer and autogpt contexts)
        $nonce = isset($_POST['_ajax_nonce']) ? sanitize_key(wp_unslash($_POST['_ajax_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'aipkit_content_writer_nonce') && !wp_verify_nonce($nonce, 'aipkit_nonce') && !wp_verify_nonce($nonce, 'aipkit_automated_tasks_manage_nonce')) {
            $this->send_wp_error(new WP_Error('nonce_failure', __('Security check failed.', 'gpt3-ai-content-generator'), ['status' => 403]));
            return;
        }

        // Get RSS feed URLs
        $feeds_raw = isset($_POST['rss_feeds']) ? sanitize_textarea_field(wp_unslash($_POST['rss_feeds'])) : '';

        if (empty($feeds_raw)) {
            $this->send_wp_error(new WP_Error('missing_feeds', __('Please enter at least one RSS feed URL.', 'gpt3-ai-content-generator')), 400);
            return;
        }

        // Optional keyword filters (for count accuracy)
        $include_keywords = isset($_POST['rss_include_keywords']) ? sanitize_text_field(wp_unslash($_POST['rss_include_keywords'])) : '';
        $exclude_keywords = isset($_POST['rss_exclude_keywords']) ? sanitize_text_field(wp_unslash($_POST['rss_exclude_keywords'])) : '';

        // Parse and count feed URLs
        $feed_urls = array_filter(array_map('trim', explode("\n", $feeds_raw)));
        $valid_feed_urls = array_filter($feed_urls, function ($url) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        });

        if (empty($valid_feed_urls)) {
            $this->send_wp_error(new WP_Error('invalid_urls', __('No valid RSS feed URLs found. Please check your input.', 'gpt3-ai-content-generator')), 400);
            return;
        }

        // Check if parser class exists
        if (!class_exists('\WPAICG\Lib\ContentWriter\AIPKit_Rss_Feed_Parser')) {
            $this->send_wp_error(new WP_Error('rss_parser_missing', __('RSS parser component is missing.', 'gpt3-ai-content-generator')), 500);
            return;
        }

        try {
            $rss_parser = new AIPKit_Rss_Feed_Parser();

            // Get all items (pass null for last_checked to get all recent items)
            $items = $rss_parser->get_latest_items($feeds_raw, null);

            // Apply keyword filters if provided
            $filtered_items = $this->apply_keyword_filters($items, $include_keywords, $exclude_keywords);

            $total_feeds = count($valid_feed_urls);
            $total_items = count($filtered_items);

            // Track which feeds had items
            $feeds_with_items = [];
            foreach ($filtered_items as $item) {
                // Items don't have feed info, so we just count items
            }

            wp_send_json_success([
                'feeds_found' => $total_feeds,
                'items_found' => $total_items,
                'message'     => $this->build_success_message($total_feeds, $total_items),
            ]);
        } catch (\Exception $e) {
            $this->send_wp_error(new WP_Error('rss_fetch_failed', $e->getMessage()), 400);
        }
    }

    /**
     * Apply include/exclude keyword filters to items.
     *
     * @param array  $items            Array of RSS items.
     * @param string $include_keywords Comma-separated keywords to include.
     * @param string $exclude_keywords Comma-separated keywords to exclude.
     * @return array Filtered items.
     */
    private function apply_keyword_filters(array $items, string $include_keywords, string $exclude_keywords): array
    {
        if (empty($include_keywords) && empty($exclude_keywords)) {
            return $items;
        }

        $include_list = array_filter(array_map('trim', explode(',', $include_keywords)));
        $exclude_list = array_filter(array_map('trim', explode(',', $exclude_keywords)));

        return array_filter($items, function ($item) use ($include_list, $exclude_list) {
            $title = strtolower($item['title'] ?? '');

            // Check exclude first
            foreach ($exclude_list as $keyword) {
                if (strpos($title, strtolower($keyword)) !== false) {
                    return false;
                }
            }

            // Check include (if any specified, title must contain at least one)
            if (!empty($include_list)) {
                foreach ($include_list as $keyword) {
                    if (strpos($title, strtolower($keyword)) !== false) {
                        return true;
                    }
                }
                return false;
            }

            return true;
        });
    }

    /**
     * Build a user-friendly success message.
     *
     * @param int $feeds_count Number of valid feeds.
     * @param int $items_count Number of items found.
     * @return string The message.
     */
    private function build_success_message(int $feeds_count, int $items_count): string
    {
        $feeds_label = $feeds_count === 1
            ? __('feed', 'gpt3-ai-content-generator')
            : __('feeds', 'gpt3-ai-content-generator');

        $items_label = $items_count === 1
            ? __('item', 'gpt3-ai-content-generator')
            : __('items', 'gpt3-ai-content-generator');

        if ($items_count > 0) {
            /* translators: 1: items count, 2: items label, 3: feeds count, 4: feeds label */
            return sprintf(
                __('Found %1$d %2$s from %3$d %4$s. Ready to generate.', 'gpt3-ai-content-generator'),
                $items_count,
                $items_label,
                $feeds_count,
                $feeds_label
            );
        }

        /* translators: 1: feeds count, 2: feeds label */
        return sprintf(
            __('Connected to %1$d %2$s but no new items found.', 'gpt3-ai-content-generator'),
            $feeds_count,
            $feeds_label
        );
    }
}
