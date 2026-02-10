<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfGroups\MountPoint;

defined('ABSPATH') || exit;

abstract class AcfCptData extends AcfGroup
{
    const POST_FIELD_MOUNT_POINTS = 'post_excerpt';

    // fields have 'a-order' is 2 to be after current fields (they have '1' by default)

    /**
     * @a-type tab
     * @label Mount Points
     * @a-order 2
     * @a-pro The field must be not required or have default value!
     */
    public bool $mountPointsTab;
    /**
     * @item \org\wplake\acf_views\AcfGroups\MountPoint
     * @var MountPoint[]
     * @label Mount Points
     * @instructions 'Mount' this View/Card to a location that doesn't support shortcodes. Mounting uses 'the_content' theme hook. <a target="_blank" href="https://docs.acfviews.com/guides/acf-views/features/mount-points-pro">Read more</a>
     * @button_label Add Mount Point
     * @a-no-tab 1
     * @a-order 2
     * @a-pro The field must be not required or have default value!
     */
    public array $mountPoints;

    public function saveToPostContent(array $postFields = [], bool $isSkipDefaults = false): bool
    {
        $commonMountPoints = [];

        foreach ($this->mountPoints as $mountPoint) {
            // both into one array, as IDs and postTypes are different and can't be mixed up
            $commonMountPoints = array_merge($commonMountPoints, $mountPoint->postTypes);
            $commonMountPoints = array_merge($commonMountPoints, $mountPoint->posts);
        }

        $commonMountPoints = array_values(array_unique($commonMountPoints));

        $postFields = array_merge($postFields, [
            static::POST_FIELD_MOUNT_POINTS => join(',', $commonMountPoints),
        ]);

        // skipDefaults. We won't need to save default values to the DB
        return parent::saveToPostContent($postFields, true);
    }
}
