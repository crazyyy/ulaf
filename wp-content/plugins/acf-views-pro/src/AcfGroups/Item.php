<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;
use org\wplake\acf_views\AcfView\FieldMeta;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;

defined('ABSPATH') || exit;

class Item extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'item';
    const FIELD_GROUP = 'group';
    const FIELD_REPEATER_FIELDS_TAB = 'repeaterFieldsTab';
    const FIELD_FIELD = 'field';
    const FIELD_REPEATER_FIELDS = 'repeaterFields';

    /**
     * @a-type tab
     * @label Field
     * @a-order 2
     */
    public bool $fieldTab;
    /**
     * @a-type select
     * @return_format value
     * @required 1
     * @ui 1
     * @label Group
     * @instructions Select a target group. Use &#36;Post&#36; group to select built-in post fields, '&#36;Taxonomy&#36;' group to select taxonomies
     * @a-order 2
     */
    public string $group;
    /**
     * @display seamless
     * @a-order 2
     * @a-no-tab 1
     */
    public Field $field;

    /**
     * @a-type tab
     * @placement top
     * @label Sub Fields
     * @a-order 3
     * @a-pro 1
     */
    public bool $repeaterFieldsTab;
    /**
     * @item \org\wplake\acf_views\AcfGroups\RepeaterField
     * @var RepeaterField[]
     * @label Sub fields
     * @instructions Setup sub fields here
     * @button_label Add Sub Field
     * @layout block
     * @a-no-tab 1
     * @a-order 3
     * @a-pro The field must be not required or have default value!
     */
    public array $repeaterFields;

    private array $repeaterFieldsMeta;

    public function __construct(CreatorInterface $creator)
    {
        parent::__construct($creator);

        $this->repeaterFieldsMeta = [];
    }

    public function setSubFieldsMeta(array $repeaterFieldsMeta = [], bool $isForce = false): void
    {
        if ($repeaterFieldsMeta ||
            $isForce) {
            $this->repeaterFieldsMeta = $repeaterFieldsMeta;

            return;
        }

        foreach ($this->repeaterFields as $repeaterField) {
            $fieldId = $repeaterField->getAcfFieldId();
            $this->repeaterFieldsMeta[$fieldId] = new FieldMeta($fieldId);
        }
    }

    /**
     * @return FieldMeta[]
     */
    public function getSubFieldsMeta(bool $isInitWhenEmpty = false): array
    {
        if (!$this->repeaterFieldsMeta &&
            $isInitWhenEmpty) {
            $this->setSubFieldsMeta();
        }

        return $this->repeaterFieldsMeta;
    }
}
