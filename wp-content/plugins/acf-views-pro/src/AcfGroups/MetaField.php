<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;

defined('ABSPATH') || exit;

class MetaField extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'meta-field';
    const FIELD_GROUP = 'group';
    const FIELD_FIELD_KEY = 'fieldKey';

    /**
     * @a-type select
     * @return_format value
     * @required 1
     * @ui 1
     * @label Group
     * @instructions Select a target group
     */
    public string $group;
    /**
     * @a-type select
     * @return_format value
     * @required 1
     * @label Field
     * @instructions Select a target field. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here
     */
    public string $fieldKey;
    /**
     * @a-type select
     * @ui 1
     * @required 1
     * @label Comparison
     * @instructions Controls how field value will be compared
     * @choices {"=":"Equal to","!=":"Not Equal to",">":"Bigger than",">=":"Bigger than or Equal to","<":"Less than","<=":"Less than or Equal to","LIKE":"Contains","NOT LIKE":"Does Not Contain","EXISTS":"Exists","NOT EXISTS":"Does Not Exist"}
     * @default_value =
     */
    public string $comparison;
    // not required, as it's user should be able to select != ''
    /**
     * @label Value2
     * @instructions Value that will be compared.<br>Can be empty, in case you want to compare with empty string.<br>Use <strong>&#36;post&#36;</strong> to pick up the actual ID or <strong>&#36;post&#36;.field-name</strong> to pick up field value dynamically
     * @conditional_logic [[{"field": "local_acf_views_meta-field__comparison","operator": "!=","value": "EXISTS"},{"field": "local_acf_views_meta-field__comparison","operator": "!=","value": "NOT EXISTS"}]]
     */
    public string $value;

    public function getAcfFieldId(): string
    {
        return Field::getAcfFieldIdByKey($this->fieldKey);
    }
}
