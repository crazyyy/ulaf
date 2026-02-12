<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;

defined('ABSPATH') || exit;

class TaxField extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'tax-field';
    const FIELD_TAXONOMY = 'taxonomy';
    const FIELD_TERM = 'term';

    /**
     * @a-type select
     * @return_format value
     * @required 1
     * @ui 1
     * @label Taxonomy
     * @instructions Select a target taxonomy
     */
    public string $taxonomy;
    /**
     * @a-type select
     * @ui 1
     * @required 1
     * @label Comparison
     * @instructions Controls how taxonomy will be compared
     * @choices {"IN":"Equal to","NOT IN":"Not Equal to","EXISTS":"Exists","NOT EXISTS":"Does Not Exist"}
     * @default_value IN
     */
    public string $comparison;
    /**
     * @a-type select
     * @return_format value
     * @required 1
     * @label Term
     * @instructions Term that will be compared
     * @conditional_logic [[{"field": "local_acf_views_tax-field__comparison","operator": "!=","value": "EXISTS"},{"field": "local_acf_views_tax-field__comparison","operator": "!=","value": "NOT EXISTS"}]]
     */
    public string $term;

    public static function createKey(string $taxonomyName, int $termId): string
    {
        return $taxonomyName . '|' . $termId;
    }

    public static function getTermIdByKey(string $key): int
    {
        $termId = explode('|', $key);

        return intval($termId[1]) ?? 0;
    }

    public function getTermId(): int
    {
        return self::getTermIdByKey($this->term);
    }
}
