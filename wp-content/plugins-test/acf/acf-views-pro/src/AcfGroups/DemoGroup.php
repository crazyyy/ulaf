<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;

defined('ABSPATH') || exit;

class DemoGroup extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'demo-group';
    const LOCATION_RULES = [
        [
            'page == $id$',
        ],
        [
            'page == $id$',
        ],
        [
            'page == $id$',
        ],
    ];

    /**
     * @a-type select
     * @label Brand
     * @choices {"samsung":"Samsung","nokia":"Nokia","htc":"HTC","xiaomi":"Xiaomi"}
     */
    public string $brand;
    /**
     * @label Model
     */
    public string $model;
    /**
     * @label Price
     */
    public int $price;
    /**
     * @a-type link
     * @label Website link
     * @return_format array
     */
    public string $websiteLink;
}
