<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfGroups;

use org\wplake\acf_views\AcfCptData;
use org\wplake\acf_views\AcfView\AcfViews;
use org\wplake\acf_views\AcfView\FieldMeta;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;

defined('ABSPATH') || exit;

class AcfViewData extends AcfCptData
{
    // to fix the group name in case class name changes
    const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'view';
    const LOCATION_RULES = [
        [
            'post_type == ' . AcfViews::NAME,
        ],
    ];
    const FIELD_MARKUP = 'markup';
    const FIELD_CSS_CODE = 'cssCode';
    const FIELD_JS_CODE = 'jsCode';
    const FIELD_CUSTOM_MARKUP = 'customMarkup';
    const FIELD_PHP_VARIABLES = 'phpVariables';
    const POST_FIELD_IS_HAS_GUTENBERG = 'post_mime_type';
    // keep the WP format 'image/jpg' to use WP_Query without issues
    const POST_VALUE_IS_HAS_GUTENBERG = 'block/block';

    /**
     * @a-type tab
     * @label Basic
     */
    public bool $general;
    /**
     * @a-type textarea
     * @label Description
     * @instructions Add a short description for your views’ purpose. Note : This description is only seen on the admin ACF Views list
     */
    public string $description;
    /**
     * @label CSS classes
     * @instructions Add a class name without a dot (e.g. “class-name”) or multiple classes with single space as a delimiter (e.g. “class-name1 class-name2”). These classes are added to the wrapping HTML element. <a target='_blank' href='https://www.w3schools.com/cssref/sel_class.asp'>Learn more about CSS Classes</a>
     */
    public string $cssClasses;
    /**
     * @label With Gutenberg Block
     * @instructions If checked, a separate gutenberg block for this view will be available. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/gutenberg-pro'>Read more</a>
     * @a-pro The field must be not required or have default value!
     * @a-acf-pro ACF PRO version is necessary for this feature
     */
    public bool $isHasGutenbergBlock;
    /**
     * @item \org\wplake\acf_views\AcfGroups\Item
     * @var Item[]
     * @label Fields
     * @instructions Assign Advanced Custom Fields (ACF) to your View. <br> Tip : hover mouse on the field number column and drag to reorder
     * @button_label Add Field
     * @a-no-tab 1
     */
    public array $items;

    /**
     * @a-type tab
     * @label Markup
     */
    public bool $markupTab;
    /**
     * @a-type textarea
     * @new_lines br
     * @label Markup Preview
     * @instructions Output preview of HTML markup generated. Important! Publish or Update your view to see the latest markup
     */
    public string $markup;
    /**
     * @a-type textarea
     * @label Custom Markup
     * @instructions Warning : for users familiar with HTML. Write your own HTML markup with full control over how your view will look. You can copy the Markup field output and make your changes. <br>Make sure you've kept all variables (like '&#36;name&#36;') and wrappers (like HTML comments), otherwise field values won't be inserted. <br>Important! This field will not be updated automatically when you add or remove fields, so you have to update this field manually to reflect the new changes (you could see the Markup field for assistance). <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/custom-markup-pro'>Read more</a>
     * @a-pro The field must be not required or have default value!
     */
    public string $customMarkup;
    /**
     * @a-type textarea
     * @label Custom Markup Variables
     * @instructions You can add custom variables to the custom markup using this PHP code snippet. <br>The snippet must return an associative array of values, where keys are variable names. These variables will be available in the custom markup within brackets, like '{VARIABLE_NAME}'.<br> In the snippet pre-defined following variables : '&#36;_objectId' (current data post), '&#36;_viewId' (current view id),'&#36;_fields' (an associative field values array, where keys are field identifiers). <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/custom-markup-variables-pro'>Read more</a>
     * @default_value <?php return [];
     * @a-pro The field must be not required or have default value!
     */
    public string $phpVariables;

    /**
     * @a-type tab
     * @label Advanced
     */
    public bool $advancedTab;
    /**
     * @a-type textarea
     * @label CSS Code
     * @instructions Define your CSS style rules here or within your theme. Rules defined here will be added within &lt;style&gt;&lt;/style&gt; tags ONLY to pages that have this view. <br> Magic shortcuts are available : <br> '#view' will be replaced with '.acf-view--id--X'.<br> '#view__' will be replaced with '.acf-view--id--X .acf-view__'. It means you can use '#view__row' and it'll be replaced with '.acf-view--id--X .acf-view__row'
     */
    public string $cssCode;
    /**
     * @a-type textarea
     * @label JS Code
     * @instructions Add your own Javascript to your view. This will be added within &lt;script&gt;&lt;/script&gt; tags ONLY to pages that have this view and also will be wrapped into an anonymous function to avoid name conflicts. Don't use inline comments ('//') inside the code, otherwise it'll break the snippet.
     */
    public string $jsCode;

    /**
     * @a-type tab
     * @label Preview
     */
    public bool $previewTab;
    /**
     * @a-type post_object
     * @return_format 1
     * @allow_null 1
     * @label Preview Object
     * @instructions Select a data object (which field values will be used) and press the 'Update' button to see the final markup in the preview
     */
    public int $previewPost;
    /**
     * @label Preview
     * @instructions Here you can see the preview of the view and play with CSS rules. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/preview'>Read more</a><br>Important! Press the 'Update' button after changes to see the latest markup here. <br>Your changes to the preview won't be applied to the view automatically, if you want to keep them copy amended CSS to the 'CSS Code' field and press the 'Update' button. <br> Note: styles from your front page are included in the preview (some differences may appear)
     * @placeholder Loading... Please wait a few seconds
     * @disabled 1
     */
    public string $preview;

    private array $fieldsMeta;

    public function __construct(CreatorInterface $creator)
    {
        parent::__construct($creator);

        $this->fieldsMeta = [];
    }

    public static function getGroupInfo(): array
    {
        return array_merge(parent::getGroupInfo(), [
            'title' => __('View settings', 'acf-views'),
        ]);
    }

    public function setFieldsMeta(array $fieldsMeta = [], bool $isForce = false): void
    {
        if ($fieldsMeta ||
            $isForce) {
            $this->fieldsMeta = $fieldsMeta;

            return;
        }

        foreach ($this->items as $item) {
            $fieldId = $item->field->getAcfFieldId();
            $this->fieldsMeta[$fieldId] = new FieldMeta($fieldId);
        }
    }

    /**
     * @return FieldMeta[]
     */
    public function getFieldsMeta(): array
    {
        return $this->fieldsMeta;
    }

    public function getCssCode(bool $isMinify = true, bool $isPreview = false): string
    {
        $cssCode = $this->cssCode;

        if ($isMinify) {
            // remove all CSS comments
            $cssCode = preg_replace('|\/\*(.?)+\*\/|', '', $cssCode);

            // 'minify' CSS
            $cssCode = str_replace(["\t", "\n", "\r"], '', $cssCode);

            // magic shortcuts
            $cssCode = str_replace('#view ', sprintf('.acf-view--id--%s ', $this->getSource()), $cssCode);
            $cssCode = str_replace('#view{', sprintf('.acf-view--id--%s{', $this->getSource()), $cssCode);
            $cssCode = str_replace('#view__', sprintf('.acf-view--id--%s .acf-view__', $this->getSource()), $cssCode);

            $cssCode = trim($cssCode);
        } elseif ($isPreview) {
            $cssCode = str_replace('#view__', sprintf('#view .acf-view__', $this->getSource()), $cssCode);
        }

        return $cssCode;
    }

    public function saveToPostContent(array $postFields = [], bool $isSkipDefaults = false): bool
    {
        $isHasGutenberg = $this->isHasGutenbergBlock ?
            static::POST_VALUE_IS_HAS_GUTENBERG :
            '';

        $postFields = array_merge($postFields, [
            static::POST_FIELD_IS_HAS_GUTENBERG => $isHasGutenberg,
        ]);

        return parent::saveToPostContent($postFields, $isSkipDefaults);
    }
}
