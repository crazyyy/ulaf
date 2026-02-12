<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

defined('ABSPATH') || exit;

class RepeaterField extends Field
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'repeater-field';

    /**
     * @a-type tab
     * @label Field
     * @a-order 1
     */
    public bool $fieldTab;
    // override fields to change labels & instructions
    /**
     * @a-type select
     * @return_format value
     * @default_value
     * @allow_null 0
     * @required 1
     * @label Sub Field
     * @instructions This list contains fields for the selected repeater or group. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here. <a target='_blank' href='https://www.advancedcustomfields.com/resources/repeater/'>Learn more about Repeater Fields</a>
     * @a-order 1
     */
    public string $key;

    /**
     * @label Identifier
     * @instructions Used in the markup, leave empty to use chosen field name. Allowed symbols : letters, numbers, underline and dash. Important! Should be unique within the repeater
     * @a-order 6
     */
    public string $id;
}
