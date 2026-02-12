<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfGroup;

defined('ABSPATH') || exit;

class Field extends AcfGroup
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'field';
    const FIELD_KEY = 'key';
    const FIELD_ID = 'id';
    const FIELD_LINK_LABEL = 'linkLabel';
    const FIELD_IMAGE_SIZE = 'imageSize';
    const FIELD_ACF_VIEW_ID = 'acfViewId';
    const FIELD_GALLERY_TYPE = 'galleryType';
    const FIELD_MASONRY_ROW_MIN_HEIGHT = 'masonryRowMinHeight';
    const FIELD_MASONRY_GUTTER = 'masonryGutter';
    const FIELD_MASONRY_MOBILE_GUTTER = 'masonryMobileGutter';
    const FIELD_GALLERY_WITH_LIGHT_BOX = 'galleryWithLightBox';
    const FIELD_MAP_ADDRESS_FORMAT = 'mapAddressFormat';
    const FIELD_IS_MAP_WITHOUT_GOOGLE_MAP = 'isMapWithoutGoogleMap';
    const FIELD_OPTIONS_DELIMITER = 'optionsDelimiter';

    /**
     * @a-type select
     * @return_format value
     * @required 1
     * @label Field
     * @instructions Select a target field. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here
     * @a-order 2
     */
    public string $key;
    /**
     * @label Label
     * @instructions If filled will be added to the markup as a prefix label of the field above
     * @a-order 2
     */
    public string $label;
    /**
     * @label Link Label
     * @instructions You can set the link label here. Leave empty to use the default
     * @a-order 2
     */
    public string $linkLabel;
    /**
     * @label Image Size
     * @instructions Controls the size of the image, it changes the image src
     * @a-type select
     * @default_value full
     * @a-order 2
     */
    public string $imageSize;
    /**
     * @a-type post_object
     * @post_type ["acf_views"]
     * @return_format id
     * @allow_null 1
     * @label ACF View
     * @instructions If filled then Posts within this field will be displayed using the selected View. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/display-fields-from-a-related-post-pro'>Read more</a>
     * @a-order 2
     * @a-pro The field must be not required or have default value!
     */
    public int $acfViewId;
    /**
     * @a-type select
     * @label Gallery Layout
     * @instructions Select the gallery layout type. If Masonry is chosen see 'Field Options' for more settings. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/fields/gallery'>Read more</a>
     * @choices {"plain":"Default","masonry":"Masonry"}
     * @default_value plain
     * @a-order 2
     * @a-pro The field must be not required or have default value!
     */
    public string $galleryType;
    /**
     * @label Image Lightbox
     * @instructions If enabled images will include a zoom icon on hover and when clicked popup with a large image
     * @a-order 2
     * @a-pro The field must be not required or have default value!
     */
    public bool $galleryWithLightBox;

    /**
     * @a-type tab
     * @label Field Options
     * @a-order 4
     */
    public bool $advancedTab;
    /**
     * @label Identifier
     * @instructions Used in the markup, leave empty to use chosen field name. Allowed symbols : letters, numbers, underline and dash. Important! Should be unique within the group
     * @a-order 6
     */
    public string $id;
    /**
     * @label Default Value
     * @instructions Set up default value, only used when the field is empty
     * @a-order 6
     */
    public string $defaultValue;
    /**
     * @label Show When Empty
     * @instructions By default, empty fields are hidden. Turn on to show even when field has no value
     * @a-order 6
     */
    public bool $isVisibleWhenEmpty;
    /**
     * @label Masonry: Row Min Height
     * @instructions Minimum height of a row in px
     * @default_value 180
     * @a-order 6
     * @a-pro The field must be not required or have default value!
     */
    public int $masonryRowMinHeight;
    /**
     * @label Masonry: Gutter
     * @instructions Margin between items in px
     * @default_value 20
     * @a-order 6
     * @a-pro The field must be not required or have default value!
     */
    public int $masonryGutter;
    /**
     * @label Masonry: Mobile Gutter
     * @instructions Margin between items on mobile in px
     * @default_value 10
     * @a-order 6
     * @a-pro The field must be not required or have default value!
     */
    public int $masonryMobileGutter;
    /**
     * @label Hide Google Map
     * @instructions The Map is shown by default. Turn this on to hide the map
     * @a-order 6
     * @a-pro The field must be not required or have default value!
     */
    public bool $isMapWithoutGoogleMap;
    /**
     * @label Map address format
     * @instructions Use these variables to format your map address: <br> &#36;street_number&#36;, &#36;street_name&#36;, &#36;city&#36;, &#36;state&#36;, &#36;post_code&#36;, &#36;country&#36; <br> HTML is also supported. If left empty the address is not shown.
     * @a-order 6
     * @a-pro The field must be not required or have default value!
     */
    public string $mapAddressFormat;
    /**
     * @label Values delimiter
     * @instructions If multiple values are chosen, you can define their delimiter here. HTML is supported
     * @a-order 6
     */
    public string $optionsDelimiter;

    public static function getAcfFieldIdByKey(string $key): string
    {
        $fieldId = explode('|', $key);

        // group, field, [subField]
        return 3 === count($fieldId) ?
            $fieldId[2] :
            ($fieldId[1] ?? '');
    }

    public static function createKey(string $group, string $field, string $subField = ''): string
    {
        $fullFieldId = $group . '|' . $field;

        $fullFieldId .= $subField ?
            '|' . $subField :
            '';

        return $fullFieldId;
    }

    public function getAcfFieldId(): string
    {
        return self::getAcfFieldIdByKey($this->key);
    }
}
