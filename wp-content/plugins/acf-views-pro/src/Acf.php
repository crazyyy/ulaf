<?php

declare(strict_types=1);

namespace org\wplake\acf_views;

use org\wplake\acf_views\AcfGroups\AcfCardData;
use org\wplake\acf_views\AcfGroups\Field;
use org\wplake\acf_views\AcfGroups\Item;
use org\wplake\acf_views\AcfGroups\MetaField;
use org\wplake\acf_views\AcfGroups\MountPoint;
use org\wplake\acf_views\AcfGroups\RepeaterField;
use org\wplake\acf_views\AcfGroups\TaxField;
use org\wplake\acf_views\AcfView\Post;

defined('ABSPATH') || exit;

// integration with ACF to provide dynamic select options and similar
class Acf
{
    const GROUP_POST = '$post$';
    const GROUP_TAXONOMY = '$taxonomy$';
    // all fields have ids like 'field_x', so no conflicts possible
    // Post fields have '_post_' prefix
    const TAXONOMY_PREFIX = '_taxonomy_';

    protected function getGroupChoices(bool $isWithExtra = true): array
    {
        $groupChoices = [
            '' => __('Select', 'acf-views'),
        ];

        if ($isWithExtra) {
            $groupChoices[self::GROUP_POST] = __('$Post$', 'acf-views');
            $groupChoices[self::GROUP_TAXONOMY] = __('$Taxonomy$', 'acf-views');
        }

        $groups = $this->getGroups();
        foreach ($groups as $group) {
            $groupId = $group['key'];
            $groupChoices[$groupId] = $group['title'];
        }

        return $groupChoices;
    }

    protected function getFieldChoices(bool $isWithExtra = true, array $excludeTypes = []): array
    {
        $fieldChoices = [];

        if (!function_exists('acf_get_fields')) {
            return $fieldChoices;
        }

        $fieldChoices = [
            '' => 'Select',
        ];

        if ($isWithExtra) {
            $fieldChoices = array_merge($fieldChoices, [
                Field::createKey(self::GROUP_POST, Post::FIELD_TITLE) => __('Title', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_TITLE_LINK) => __('Title with link', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_EXCERPT) => __('Excerpt', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_THUMBNAIL) => __('Featured Image', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_THUMBNAIL_LINK) => __(
                    'Featured Image with link',
                    'acf-views'
                ),
                Field::createKey(self::GROUP_POST, Post::FIELD_AUTHOR) => __('Author', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_DATE) => __('Published date', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_MODIFIED) => __('Modified date', 'acf-views'),
                Field::createKey(self::GROUP_POST, Post::FIELD_LINK) => __('Link', 'acf-views'),
            ]);

            $taxonomies = get_taxonomies([], 'objects');
            if (!in_array(self::GROUP_TAXONOMY, $excludeTypes, true)) {
                foreach ($taxonomies as $taxonomy) {
                    $itemName = Field::createKey(
                        self::GROUP_TAXONOMY,
                        self::TAXONOMY_PREFIX . $taxonomy->name
                    );
                    $fieldChoices[$itemName] = $taxonomy->label;
                }
            }
        }

        $supportedFieldTypes = $this->getFieldTypes();

        $groups = $this->getGroups();
        foreach ($groups as $group) {
            $fields = acf_get_fields($group);

            foreach ($fields as $groupField) {
                if (!in_array($groupField['type'], $supportedFieldTypes, true) ||
                    ($excludeTypes && in_array($groupField['type'], $excludeTypes, true))) {
                    continue;
                }

                $fullFieldId = Field::createKey($group['key'], $groupField['key']);
                $fieldChoices[$fullFieldId] = $groupField['label'] . ' (' . $groupField['type'] . ')';
            }
        }

        return $fieldChoices;
    }

    protected function getSubFieldChoices(array $excludeTypes = []): array
    {
        $subFieldChoices = [
            '' => 'Select',
        ];

        $supportedFieldTypes = $this->getFieldTypes();

        $groups = $this->getGroups();
        foreach ($groups as $group) {
            $fields = acf_get_fields($group);

            foreach ($fields as $groupField) {
                $subFields = (array)($groupField['sub_fields'] ?? []);

                if (!in_array($groupField['type'], ['repeater', 'group',], true) ||
                    !$subFields) {
                    continue;
                }

                foreach ($subFields as $subField) {
                    // inner complex types, like repeater or group aren't allowed
                    if (!in_array($subField['type'], $supportedFieldTypes, true) ||
                        in_array($subField['type'], ['repeater', 'group',], true) ||
                        ($excludeTypes && in_array($subField['type'], $excludeTypes, true))) {
                        continue;
                    }

                    $fullFieldId = Field::createKey(
                        $group['key'],
                        $groupField['key'],
                        $subField['key']
                    );
                    $subFieldChoices[$fullFieldId] = $subField['label'] . ' (' . $subField['type'] . ')';
                }
            }
        }

        return $subFieldChoices;
    }

    protected function getPostTypeChoices(): array
    {
        return get_post_types();
    }

    protected function getImageSizes(): array
    {
        $imageSizeChoices = [];
        $imageSizes = get_intermediate_image_sizes();

        foreach ($imageSizes as $imageSize) {
            $imageSizeChoices[$imageSize] = ucfirst($imageSize);
        }

        $imageSizeChoices['full'] = __('Full', 'acf-views');

        return $imageSizeChoices;
    }

    protected function getPostStatusChoices(): array
    {
        return get_post_statuses();
    }

    protected function getTaxonomyChoices(): array
    {
        $taxChoices = [
            '' => __('Select', 'acf-views'),
        ];

        $taxonomies = get_taxonomies([], 'objects');

        foreach ($taxonomies as $taxonomy) {
            $taxChoices[$taxonomy->name] = $taxonomy->label;
        }

        return $taxChoices;
    }

    protected function getTermChoices(): array
    {
        $termChoices = [
            '' => __('Select', 'acf-views'),
            '$current$' => __('$current$ (archive and category pages)', 'acf-views'),
        ];

        $taxonomyNames = get_taxonomies([]);
        foreach ($taxonomyNames as $taxonomyName) {
            $terms = get_terms([
                'taxonomy' => $taxonomyName,
                'hide_empty' => false,
            ]);
            foreach ($terms as $term) {
                $fullTaxId = TaxField::createKey($taxonomyName, $term->term_id);
                $termChoices[$fullTaxId] = $term->name;
            }
        }

        return $termChoices;
    }

    // Important! Use this wrapper to avoid recursion
    protected function getGroups(): array
    {
        if (!function_exists('acf_get_field_groups')) {
            return [];
        }

        $acfGroups = acf_get_field_groups();

        // Important! To avoid recursion, otherwise within 'getChoices()' will be available the same group as the current
        // and this class will call 'acf_get_fields()' that will call 'getChoices()'
        $acfGroups = array_filter($acfGroups, function ($acfGroup) {
            $isPrivate = (bool)($acfGroup['private'] ?? false);
            $isOwn = 0 === strpos($acfGroup['key'], AcfGroup::GROUP_NAME_PREFIX);
            // don't check at all, as 'local' not presented only when json is disabled.
            // in other cases contains 'php' or 'json'
            // $isLocal = (bool)($acfGroup['local'] ?? false);

            return (!$isPrivate &&
                !$isOwn);
        });


        return array_values($acfGroups);
    }

    ////

    protected function setConditionalRulesForField(array $field, string $targetField, array $notEqualValues): array
    {
        // multiple calls of this method are allowed
        if (!isset($field['conditional_logic']) ||
            !is_array($field['conditional_logic'])) {
            $field['conditional_logic'] = [];
        }

        foreach ($notEqualValues as $notEqualValue) {
            // using exactly AND rule (so all rules in one array) and '!=' comparison,
            // otherwise if there are no such fields the field will be visible
            $field['conditional_logic'][] = [
                'field' => $targetField,
                'operator' => '!=',
                'value' => $notEqualValue,
            ];
        }

        return $field;
    }

    protected function setGroupSelectChoices(): void
    {
        add_filter(
            'acf/load_field/name=' . Item::getAcfFieldName(Item::FIELD_GROUP),
            function (array $field) {
                $field['choices'] = $this->getGroupChoices();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . AcfCardData::getAcfFieldName(AcfCardData::FIELD_ORDER_BY_META_FIELD_GROUP),
            function (array $field) {
                $field['choices'] = $this->getGroupChoices(false);

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . MetaField::getAcfFieldName(MetaField::FIELD_GROUP),
            function (array $field) {
                $field['choices'] = $this->getGroupChoices(false);

                return $field;
            }
        );
    }

    protected function setFieldSelectChoices(): void
    {
        add_filter(
            'acf/load_field/name=' . Field::getAcfFieldName(Field::FIELD_KEY),
            function (array $field) {
                $field['choices'] = $this->getFieldChoices();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . RepeaterField::getAcfFieldName(RepeaterField::FIELD_KEY),
            function (array $field) {
                $field['choices'] = $this->getSubFieldChoices();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . AcfCardData::getAcfFieldName(AcfCardData::FIELD_ORDER_BY_META_FIELD_KEY),
            function (array $field) {
                $field['choices'] = $this->getFieldChoices(false);

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . MetaField::getAcfFieldName(MetaField::FIELD_FIELD_KEY),
            function (array $field) {
                $field['choices'] = $this->getFieldChoices(false);

                return $field;
            }
        );
    }

    protected function addConditionalFilter(
        string $fieldName,
        array $notFieldTypes,
        bool $isSubField = false,
        array $includeFields = []
    ): void {
        $acfFieldName = !$isSubField ?
            Field::getAcfFieldName($fieldName) :
            RepeaterField::getAcfFieldName($fieldName);
        $acfKey = !$isSubField ?
            Field::getAcfFieldName(Field::FIELD_KEY) :
            RepeaterField::getAcfFieldName(RepeaterField::FIELD_KEY);

        add_filter(
            'acf/load_field/name=' . $acfFieldName,
            function (array $field) use ($acfKey, $notFieldTypes, $includeFields, $isSubField) {
                // using exactly the negative (excludeTypes) filter,
                // otherwise if there are no such fields the field will be visible
                $notRightFields = !$isSubField ?
                    $this->getFieldChoices(true, $notFieldTypes) :
                    $this->getSubFieldChoices($notFieldTypes);

                foreach ($includeFields as $includeField) {
                    unset($notRightFields[$includeField]);
                }

                return $this->setConditionalRulesForField(
                    $field,
                    $acfKey,
                    array_keys($notRightFields)
                );
            }
        );
    }

    protected function setViewConditionalRules(): void
    {
        //// linkLabel

        $linkLabelTypes = [
            'link',
            'page_link',
            'file',
            'post_object',
            'relationship',
            'taxonomy',
            'user',
        ];
        $this->addConditionalFilter(Field::FIELD_LINK_LABEL, $linkLabelTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_LINK_LABEL, $linkLabelTypes, true);

        //// imageSize

        $imageSizeTypes = ['image', 'gallery',];

        $this->addConditionalFilter(Field::FIELD_IMAGE_SIZE, $imageSizeTypes, false, [
            // post thumbnail should have this setting too
            Field::createKey(self::GROUP_POST, Post::FIELD_THUMBNAIL),
            Field::createKey(self::GROUP_POST, Post::FIELD_THUMBNAIL_LINK),
        ]);
        $this->addConditionalFilter(RepeaterField::FIELD_IMAGE_SIZE, $imageSizeTypes, true);

        //// acfViewId

        $acfViewIdTypes = ['post_object', 'relationship',];
        $this->addConditionalFilter(Field::FIELD_ACF_VIEW_ID, $acfViewIdTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_ACF_VIEW_ID, $acfViewIdTypes, true);

        //// GalleryType & GalleryWithLightBox

        $galleryTypes = ['gallery',];
        $this->addConditionalFilter(Field::FIELD_GALLERY_TYPE, $galleryTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_GALLERY_TYPE, $galleryTypes, true);
        $this->addConditionalFilter(Field::FIELD_GALLERY_WITH_LIGHT_BOX, $galleryTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_GALLERY_WITH_LIGHT_BOX, $galleryTypes, true);

        //// Map fields

        $mapTypes = ['google_map',];
        $this->addConditionalFilter(Field::FIELD_MAP_ADDRESS_FORMAT, $mapTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_MAP_ADDRESS_FORMAT, $mapTypes, true);
        $this->addConditionalFilter(Field::FIELD_IS_MAP_WITHOUT_GOOGLE_MAP, $mapTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_IS_MAP_WITHOUT_GOOGLE_MAP, $mapTypes, true);

        //// Masonry fields

        $masonryFields = [
            Field::FIELD_MASONRY_ROW_MIN_HEIGHT,
            Field::FIELD_MASONRY_GUTTER,
            Field::FIELD_MASONRY_MOBILE_GUTTER,
        ];

        foreach ($masonryFields as $masonryField) {
            add_filter(
                'acf/load_field/name=' . Field::getAcfFieldName($masonryField),
                function (array $field) {
                    return $this->setConditionalRulesForField(
                        $field,
                        Field::getAcfFieldName(Field::FIELD_GALLERY_TYPE),
                        ['', 'plain',],
                    );
                }
            );
        }

        $masonryRepeaterFields = [
            RepeaterField::FIELD_MASONRY_ROW_MIN_HEIGHT,
            RepeaterField::FIELD_MASONRY_GUTTER,
            RepeaterField::FIELD_MASONRY_MOBILE_GUTTER,
        ];

        foreach ($masonryRepeaterFields as $masonryRepeaterField) {
            add_filter(
                'acf/load_field/name=' . RepeaterField::getAcfFieldName($masonryRepeaterField),
                function (array $field) {
                    return $this->setConditionalRulesForField(
                        $field,
                        RepeaterField::getAcfFieldName(RepeaterField::FIELD_GALLERY_TYPE),
                        ['', 'plain',],
                    );
                }
            );
        }

        //// Options delimiter

        $multiSelectTypes = [
            'select',
            'post_object',
            'page_link',
            'relationship',
            'taxonomy',
            'user',
            self::GROUP_TAXONOMY,
        ];
        $this->addConditionalFilter(Field::FIELD_OPTIONS_DELIMITER, $multiSelectTypes);
        $this->addConditionalFilter(RepeaterField::FIELD_OPTIONS_DELIMITER, $multiSelectTypes, true);

        //// repeaterFields tab ('repeater' + 'group')

        add_filter(
            'acf/load_field/name=' . Item::getAcfFieldName(Item::FIELD_REPEATER_FIELDS_TAB),
            function (array $field) {
                // using exactly the negative (excludeTypes) filter,
                // otherwise if there are no such fields the field will be visible
                $notRepeaterFields = $this->getFieldChoices(true, ['repeater', 'group',]);

                return $this->setConditionalRulesForField(
                    $field,
                    Field::getAcfFieldName(Field::FIELD_KEY),
                    array_keys($notRepeaterFields)
                );
            }
        );
    }

    protected function setCardChoices(): void
    {
        add_filter(
            'acf/load_field/name=' . AcfCardData::getAcfFieldName(AcfCardData::FIELD_POST_TYPES),
            function (array $field) {
                $field['choices'] = $this->getPostTypeChoices();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . AcfCardData::getAcfFieldName(AcfCardData::FIELD_POST_STATUSES),
            function (array $field) {
                $field['choices'] = $this->getPostStatusChoices();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . TaxField::getAcfFieldName(TaxField::FIELD_TAXONOMY),
            function (array $field) {
                $field['choices'] = $this->getTaxonomyChoices();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . TaxField::getAcfFieldName(TaxField::FIELD_TERM),
            function (array $field) {
                $field['choices'] = $this->getTermChoices();

                return $field;
            }
        );
    }

    protected function setViewChoices(): void
    {
        add_filter(
            'acf/load_field/name=' . Field::getAcfFieldName(Field::FIELD_IMAGE_SIZE),
            function (array $field) {
                $field['choices'] = $this->getImageSizes();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . RepeaterField::getAcfFieldName(RepeaterField::FIELD_IMAGE_SIZE),
            function (array $field) {
                $field['choices'] = $this->getImageSizes();

                return $field;
            }
        );

        add_filter(
            'acf/load_field/name=' . MountPoint::getAcfFieldName(MountPoint::FIELD_POST_TYPES),
            function (array $field) {
                $field['choices'] = $this->getPostTypeChoices();

                return $field;
            }
        );
    }

    ////

    public function setHooks(): void
    {
        $this->setGroupSelectChoices();
        $this->setFieldSelectChoices();
        $this->setViewConditionalRules();
        $this->setViewChoices();
        $this->setCardChoices();
    }

    public function getGroupedFieldTypes(): array
    {
        return [
            'basic' => [
                'text',
                'textarea',
                'number',
                'range',
                'email',
                'url',
                'password',
            ],
            'content' => [
                'image',
                'file',
                'wysiwyg',
                'oembed',
                'gallery',
            ],
            'choice' => [
                'select',
                'checkbox',
                'radio',
                'button_group',
                'true_false',
            ],
            'relational' => [
                'link',
                'post_object',
                'page_link',
                'relationship',
                'taxonomy',
                'user',
            ],
            'jquery' => [
                'google_map',
                'date_picker',
                'date_time_picker',
                'time_picker',
                'color_picker',
            ],
            'layout' => [
                'repeater',
                'group',
            ],
        ];
    }

    public function getFieldTypes(): array
    {
        $fieldTypes = [];
        $groupedFieldTypes = $this->getGroupedFieldTypes();
        foreach ($groupedFieldTypes as $group => $fields) {
            $fieldTypes = array_merge($fieldTypes, $fields);
        }

        return $fieldTypes;
    }
}
