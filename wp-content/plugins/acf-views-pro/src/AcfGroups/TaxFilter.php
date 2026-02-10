<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;

defined('ABSPATH') || exit;

class TaxFilter extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'tax-filter';

    /**
     * @a-type select
     * @ui 1
     * @required 1
     * @label Relation
     * @instructions Controls how taxonomy rules will be joined within the taxonomy query
     * @choices {"AND":"AND","OR":"OR"}
     * @default_value AND
     * @conditional_logic [[{"field": "local_acf_views_tax-filter__rules","operator": ">","value": "1"}]]
     * @a-pro The field must be not required or have default value!
     */
    public string $relation;
    /**
     * @var TaxRule[]
     * @item \org\wplake\acf_views\AcfGroups\TaxRule
     * @label Rules
     * @instructions Rules for the taxonomy query. Multiple rules are supported. <a target='_blank' href='https://docs.acfviews.com/guides/acf-cards/features/taxonomy-filter-pro'>Read more</a> <br> If you want to see the query that was created by your input, please press the 'Update' button and have a look at the 'Query Preview' field in the 'Advanced' tab
     * @button_label Add Rule
     * @a-no-tab 1
     * @layout block
     * @a-pro The field must be not required or have default value!
     */
    public array $rules;
}