<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;

defined('ABSPATH') || exit;

class MountPoint extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'mount-point';

    const FIELD_POST_TYPES = 'postTypes';
    const MOUNT_POSITION_BEFORE = 'before';
    const MOUNT_POSITION_AFTER = 'after';
    const MOUNT_POSITION_INSTEAD = 'instead';

    /**
     * @a-type post_object
     * @return_format id
     * @label Specific posts
     * @multiple 1
     * @instructions Limit the mount point to only specific posts. Leave empty and use the 'Post Types' field to limit to specific post types
     */
    public array $posts;
    /**
     * @a-type select
     * @multiple 1
     * @ui 1
     * @label Post Types
     * @instructions Specific post types, to all items of which the shortcode should be mounted. Leave empty if you want to add to specific items only and use the 'Specific posts' field
     */
    public array $postTypes;
    /**
     * @label Mount Point
     * @instructions To which unique Word, String or HTML piece to Mount to. Together with the 'Mount Position' controls the placement. If left empty all the content will be used as a mount point
     */
    public string $mountPoint;
    /**
     * @a-type select
     * @required 1
     * @label Mount Position
     * @instructions Where the shortcode should be mounted
     * @choices {"before":"Before","after":"After","instead":"Instead (replace)"}
     * @default_value after
     */
    public string $mountPosition;
    /**
     * @label Shortcode Arguments
     * @instructions Add arguments to the shortcode, e.g. 'user-with-roles'. Only the view/card 'id' argument is filled by default
     */
    public string $shortcodeArgs;
}
