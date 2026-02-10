<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro;

use org\wplake\acf_views\AcfCptData;
use org\wplake\acf_views\AcfGroups\MountPoint;
use org\wplake\acf_views\Cache;
use org\wplake\acf_views\pro\AcfCard\AcfCards;
use org\wplake\acf_views\pro\AcfView\AcfViews;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Common class for both AcfViews and AcfCards
 */
class MountPoints
{
    private Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return MountPoint[]
     */
    protected function queryMountPointsData(string $currentPostType, int $currentPostId): array
    {
        global $wpdb;

        $mountPoints = [];

        $query       = $wpdb->prepare(
            "SELECT * from {$wpdb->posts} WHERE post_type IN (%s,%s) AND post_status = 'publish'
                      AND (FIND_IN_SET(%s,post_excerpt) > 0 OR FIND_IN_SET(%s,post_excerpt) > 0)",
            AcfViews::NAME,
            AcfCards::NAME,
            $currentPostType,
            $currentPostId
        );
        $sourcePosts = $wpdb->get_results($query);

        /**
         * @var WP_Post $sourcePost
         */
        foreach ($sourcePosts as $sourcePost) {
            // for some reasons it's string
            $sourcePostId = (int)$sourcePost->ID;

            /**
             * @var AcfCptData $acfCptData
             */
            $acfCptData = AcfViews::NAME === $sourcePost->post_type ?
                $this->cache->getAcfViewData($sourcePostId) :
                $this->cache->getAcfCardData($sourcePostId);

            // filter target mount points
            // as the query was rough and contained common data from all MountPoints of a source item

            foreach ($acfCptData->mountPoints as $mountPoint) {
                // without strict comparison, as in the posts array can be strings
                if (! in_array($currentPostType, $mountPoint->postTypes) &&
                    ! in_array($currentPostId, $mountPoint->posts)) {
                    continue;
                }

                if (! isset($mountPoints[$sourcePost->post_type])) {
                    $mountPoints[$sourcePost->post_type] = [];
                }
                if (! isset($mountPoints[$sourcePost->post_type][$sourcePostId])) {
                    $mountPoints[$sourcePost->post_type][$sourcePostId] = [];
                }

                // exactly array structure, as multiple mountPoints can exist for one ID
                $mountPoints[$sourcePost->post_type][$sourcePostId][] = $mountPoint;
            }
        }

        return $mountPoints;
    }

    /**
     * @param bool $isRunShortcode Can be false for tests
     */
    protected function mountPoint(
        string $sourcePostType,
        int $sourcePostId,
        MountPoint $mountPoint,
        string $content,
        bool $isRunShortcode = true
    ): string {
        $startOfMarkerIndex = $mountPoint->mountPoint ?
            strpos($content, $mountPoint->mountPoint) :
            0;

        // mountPoint not found (mistake), just skip
        if (false === $startOfMarkerIndex) {
            return $content;
        }

        $endOfMarkerIndex = $mountPoint->mountPoint ?
            $startOfMarkerIndex + strlen($mountPoint->mountPoint) :
            strlen($content);
        $markerLength     = $mountPoint->mountPoint ?
            strlen($mountPoint->mountPoint) :
            strlen($content);

        $shortcode     = '';
        $shortcodeArgs = $mountPoint->shortcodeArgs ?
            ' ' . $mountPoint->shortcodeArgs :
            '';

        if (AcfViews::NAME === $sourcePostType) {
            $shortcode = sprintf('[acf_views view-id="%s"%s]', $sourcePostId, $shortcodeArgs);
        } else {
            $shortcode = sprintf('[acf_cards card-id="%s"%s]', $sourcePostId, $shortcodeArgs);
        }

        $offset = 0;
        $length = 0;

        switch ($mountPoint->mountPosition) {
            case MountPoint::MOUNT_POSITION_BEFORE:
                $offset = $startOfMarkerIndex;
                break;
            case MountPoint::MOUNT_POSITION_AFTER:
                $offset = $endOfMarkerIndex;
                break;
            case MountPoint::MOUNT_POSITION_INSTEAD:
                $offset = $startOfMarkerIndex;
                $length = $markerLength;
                break;
        }

        if ($isRunShortcode) {
            $shortcode = do_shortcode($shortcode);
        }

        return substr_replace($content, $shortcode, $offset, $length);
    }

    public function setHooks(): void
    {
        add_filter('the_content', [$this, 'mount']);
    }

    // it's a filter, so in a theory content can have any type
    public function mount($content): string
    {
        $content = (string)$content;

        // make sure the_content inside the main loop
        // otherwise can be called within sidebars, etc, and there is no opportunity to check it
        // (as the filter has only 1 argument and global variables are untouched)
        // more https://developer.wordpress.org/reference/hooks/the_content/
        if (! is_singular() ||
            ! in_the_loop() ||
            ! is_main_query()) {
            return $content;
        }

        $queriedObject = get_queried_object();

        if (! $queriedObject instanceof WP_Post) {
            return $content;
        }

        $mountPointsData = $this->queryMountPointsData($queriedObject->post_type, $queriedObject->ID);

        foreach ($mountPointsData as $sourcePostType => $mountPointData) {
            foreach ($mountPointData as $sourcePostId => $mountPoints) {
                foreach ($mountPoints as $mountPoint) {
                    $content = $this->mountPoint($sourcePostType, $sourcePostId, $mountPoint, $content);
                }
            }
        }

        return $content;
    }
}
