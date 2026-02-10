<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro\AcfCard;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\AcfView\FieldMeta;
use WP_Post;

defined('ABSPATH') || exit;

class QueryBuilder extends \org\wplake\acf_views\AcfCard\QueryBuilder
{
    protected function getMetaComparisonForSerializedField(array $subMetaField, string $value): array
    {
        // relationship & post_object (with multiple checkbox) can be found only in this manner
        // https://www.advancedcustomfields.com/resources/querying-relationship-fields/

        // equal and not equals won't work here. Only LIKE & NOT LIKE
        $subMetaField['compare'] = '=' === $subMetaField['compare'] ?
            'LIKE' :
            $subMetaField['compare'];
        $subMetaField['compare'] = '!=' === $subMetaField['compare'] ?
            'NOT LIKE' :
            $subMetaField['compare'];

        // add quotes to the value. To matches exactly "123", not just 123. This prevents a match for "1234" in 'a:1:{i:0;s:4:"1234";}'.
        $subMetaField['value'] = sprintf('"%s"', $value);

        return $subMetaField;
    }

    protected function convertItemToString($item): ?string
    {
        if (is_a($item, WP_Post::class)) {
            $item = $item->ID;
        }

        // convert possible int to string
        if (is_numeric($item)) {
            $item = (string)$item;
        }

        // some unknown data, array or another type of the object
        if (!is_string($item)) {
            return null;
        }

        return $item;
    }

    protected function convertArrayToStringList(array $items): array
    {
        $list = [];

        foreach ($items as $item) {
            $stringItem = $this->convertItemToString($item);

            if (null === $stringItem) {
                continue;
            }

            $list[] = $item;
        }

        return $list;
    }

    protected function getMetaArgsForSerializedField(array $subMetaField): array
    {
        $metaFilterValue = $subMetaField['value'];

        // plain text option
        if (!is_array($metaFilterValue)) {
            $metaFilterValue = $this->convertItemToString($metaFilterValue);

            return null !== $metaFilterValue ?
                $this->getMetaComparisonForSerializedField($subMetaField, $metaFilterValue) :
                [];
        }

        // meta value after magic replace. Contains response of the relationship field (with multiple options)
        // target: A, B, C
        // metaValue: C, D, E
        // we need to 'match' every item from the metaValue to find all related
        // (using WP inner meta, that allows 'relation' in 'relation')

        $idsList = $this->convertArrayToStringList($metaFilterValue);

        if (!$idsList) {
            return [];
        }

        $metaQuery = [
            'relation' => 'OR',
        ];

        foreach ($idsList as $id) {
            $metaQuery[] = $this->getMetaComparisonForSerializedField($subMetaField, $id);
        }

        return $metaQuery;
    }

    protected function getMetaArgsForArrayValue(array $subMetaField): array
    {
        $metaFilterValue = $subMetaField['value'];
        $idsList = $this->convertArrayToStringList($metaFilterValue);

        if (!$idsList) {
            return [];
        }

        $metaQuery = [
            'relation' => 'OR',
        ];

        foreach ($idsList as $id) {
            $metaQuery[] = array_merge($subMetaField, [
                'value' => $id,
            ]);
        }

        return $metaQuery;
    }

    /**
     * @return string|array
     */
    protected function getMetaValue(string $fieldValue, bool $isStringValue)
    {
        if (false === strpos($fieldValue, '$post$')) {
            return $fieldValue;
        }

        // in case user is set like '$post$ '
        $fieldValue = trim($fieldValue);

        global $post;
        // a) object-id in the current loop
        // b) the current page id
        $currentId = $post->ID ?? 0;
        $currentId = $currentId ?: get_queried_object_id();

        // magic inserting of '$post$'

        if (false === strpos($fieldValue, '$post$.')) {
            return (string)$currentId;
        }

        // magic inserting of '$post$.field-name'

        $metaFieldName = str_replace('$post$.', '', $fieldValue);
        $metaFieldValue = get_field($metaFieldName, $currentId);

        if (null === $metaFieldValue ||
            ($isStringValue && !is_string($metaFieldValue) && !is_numeric($metaFieldValue))) {
            return '';
        }

        return $isStringValue ?
            (string)$metaFieldValue :
            $metaFieldValue;
    }

    protected function getMetaArgsForRelationship(array $subMetaField, FieldMeta $fieldMeta): array
    {
        // target field is a multiple (serialized) value

        if ('relationship' === $fieldMeta->getType() ||
            $fieldMeta->isMultiple()) {
            return $this->getMetaArgsForSerializedField($subMetaField);
        }

        // target field is a single value

        if (is_array($subMetaField['value'])) {
            return $this->getMetaArgsForArrayValue($subMetaField);
        }

        $value = $this->convertItemToString($subMetaField['value']);

        if (null !== $value) {
            $subMetaField['value'] = $value;
        } else {
            $subMetaField = [];
        }

        return $subMetaField;
    }

    protected function getMetaQueryArgs(AcfCardData $acfCardData): array
    {
        $metaQuery = [
            // can be empty in case hidden
            'relation' => $acfCardData->metaFilter->relation ?: 'AND',
        ];

        foreach ($acfCardData->metaFilter->rules as $metaRule) {
            $subMeta = [
                // can be empty in case hidden
                'relation' => $metaRule->relation ?: 'AND',
            ];
            foreach ($metaRule->fields as $field) {
                $fieldMeta = new FieldMeta($field->getAcfFieldId());

                if (!$fieldMeta->isFieldExist()) {
                    continue;
                }

                $subMetaField = [
                    'key' => $fieldMeta->getName(),
                    'compare' => $field->comparison,
                ];

                if (!in_array($field->comparison, ['EXISTS', 'NOT EXISTS',], true)) {
                    $isRelationshipOrPostObject = 'relationship' === $fieldMeta->getType() ||
                        'post_object' === $fieldMeta->getType();
                    $isStringValue = !$isRelationshipOrPostObject;

                    $subMetaField['value'] = $this->getMetaValue($field->value, $isStringValue);

                    if ($isRelationshipOrPostObject) {
                        $subMetaField = $this->getMetaArgsForRelationship($subMetaField, $fieldMeta);
                    }
                }

                // can be an empty array
                if ($subMetaField) {
                    $subMeta[] = $subMetaField;
                }
            }

            $metaQuery[] = $subMeta;
        }

        return $metaQuery;
    }

    protected function getTaxQueryArgs(AcfCardData $acfCardData): array
    {
        $taxQuery = [
            // can be empty in case hidden
            'relation' => $acfCardData->taxFilter->relation ?: 'AND',
        ];

        foreach ($acfCardData->taxFilter->rules as $taxRule) {
            $subTax = [
                // can be empty in case hidden
                'relation' => $taxRule->relation ?: 'AND',
            ];
            foreach ($taxRule->taxonomies as $taxonomy) {
                $subTerm = [
                    'taxonomy' => $taxonomy->taxonomy,
                    // it's necessary to use term_id, wp has a bug when 'field' => 'slug'
                    'field' => 'term_id',
                    'operator' => $taxonomy->comparison,
                ];

                if (!in_array($taxonomy->comparison, ['EXISTS', 'NOT EXISTS',], true)) {
                    // magic inserting
                    $termIds = '$current$' === $taxonomy->term ?
                        [get_queried_object_id(),] :
                        [$taxonomy->getTermId(),];

                    $subTerm['terms'] = $termIds;
                }

                $subTax[] = $subTerm;
            }

            $taxQuery[] = $subTax;
        }

        return $taxQuery;
    }

    public function getQueryArgs(AcfCardData $acfCardData, int $pageNumber): array
    {
        $args = parent::getQueryArgs($acfCardData, $pageNumber);

        if ($acfCardData->metaFilter->rules) {
            $args['meta_query'] = $this->getMetaQueryArgs($acfCardData);
        }

        if ($acfCardData->taxFilter->rules) {
            $args['tax_query'] = $this->getTaxQueryArgs($acfCardData);
        }

        if ($acfCardData->isWithPagination) {
            $args = array_merge($args, [
                'posts_per_page' => $acfCardData->paginationPerPage,
                'offset' => ($pageNumber - 1) * $acfCardData->paginationPerPage,
            ]);

            if (-1 !== $acfCardData->limit) {
                $overAmount = ($acfCardData->paginationPerPage * $pageNumber) - $acfCardData->limit;

                if ($overAmount > 0) {
                    $postsPerPage = $acfCardData->paginationPerPage - $overAmount;
                    $postsPerPage = max($postsPerPage, 0);
                    $args['posts_per_page'] = $postsPerPage;
                }
            }
        }

        $cardId = $acfCardData->getSource();

        $args = (array)apply_filters(
            'acf_views/card/query_args',
            $args,
            $cardId,
            $pageNumber
        );
        $args = (array)apply_filters(
            'acf_views/card/query_args/id=' . $cardId,
            $args,
            $cardId,
            $pageNumber
        );

        return $args;
    }
}
