<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

use DateTime;
use org\wplake\acf_views\Acf;
use org\wplake\acf_views\AcfGroups\Field;

defined('ABSPATH') || exit;

class FieldMarkup
{
    protected Acf $acf;
    protected FieldMeta $fieldMeta;
    protected Field $field;
    protected $fieldValue;

    public function __construct(Acf $acf, FieldMeta $fieldMeta, Field $field, $fieldValue)
    {
        $this->acf = $acf;
        $this->fieldMeta = $fieldMeta;
        $this->field = $field;
        $this->fieldValue = $fieldValue;
    }

    // content types

    protected function getImageSizeAttributes($imageValue): string
    {
        $width = 0;
        $height = 0;

        switch ($this->fieldMeta->getReturnFormat()) {
            case 'id':
                $metadata = wp_get_attachment_metadata($imageValue);
                $width = $metadata['width'] ?? 0;
                $height = $metadata['height'] ?? 0;
                break;
            case 'array':
                $width = $imageValue['width'] ?? 0;
                $height = $imageValue['height'] ?? 0;
                break;
        }

        return sprintf("data-width='%s' data-height='%s'", esc_attr($width), esc_attr($height));
    }

    protected function getImageMarkup(
        $fieldValue,
        bool $isWithSize = false,
        bool $isWithFullSizeUrlInData = false,
        string $returnFormat = ''
    ): string {
        $imageUrl = '';
        $imageSize = $this->field->imageSize ?: 'full';
        $alt = '';
        $fullSizeUrl = '';

        $returnFormat = $returnFormat ?: $this->fieldMeta->getReturnFormat();

        switch ($returnFormat) {
            case 'id':
                $imageUrl = (string)wp_get_attachment_image_url($fieldValue, $imageSize);
                $alt = (string)get_post_meta($fieldValue, '_wp_attachment_image_alt', true);

                $fullSizeUrl = $isWithFullSizeUrlInData ?
                    (string)wp_get_attachment_image_url($fieldValue, 'full') :
                    '';
                break;
            case 'array':
                $sizes = (array)($fieldValue['sizes'] ?? []);
                $imageUrl = $sizes[$imageSize] ?? '';

                $imageUrl = !$imageUrl ?
                    ($fieldValue['url'] ?? '') :
                    $imageUrl;
                $alt = $fieldValue['alt'] ?? '';

                // full contains in the 'url' key
                $fullSizeUrl = $isWithFullSizeUrlInData ?
                    $fieldValue['url'] ?? '' :
                    '';
                break;
            case 'url':
                $imageUrl = $fieldValue;
                break;
        }

        // data-width/height
        $sizeData = $isWithSize ?
            ' ' . $this->getImageSizeAttributes($fieldValue) :
            '';

        // data-full
        $sizeData .= $isWithFullSizeUrlInData ?
            sprintf(" data-full='%s'", esc_attr($fullSizeUrl)) :
            '';

        return sprintf(
            "<img class='acf-view__image' src='%s' alt='%s' loading='lazy'%s>",
            esc_attr($imageUrl),
            esc_attr($alt),
            $sizeData
        );
    }

    protected function getFileMarkup(): string
    {
        $fileUrl = '';
        $fileTitle = '';

        switch ($this->fieldMeta->getReturnFormat()) {
            case 'id':
                $fileUrl = (string)wp_get_attachment_url($this->fieldValue);

                $fileTitle = get_post($this->fieldValue)->post_title ?? '';
                break;
            case 'array':
                $fileUrl = $this->fieldValue['url'] ?? '';
                $fileTitle = $this->fieldValue['title'] ?? '';
                break;
            case 'url':
                $fileUrl = $this->fieldValue;
                break;
        }

        // if the linkLabel is setup, use it instead of default
        $fileTitle = $this->field->linkLabel ?: $fileTitle;

        // use label in case there are no linkLabel and default
        $fileTitle = !$fileTitle ?
            $this->field->label :
            $fileTitle;

        // last attempt to fill the label. Use the url
        $fileTitle = !$fileTitle ?
            $fileUrl :
            $fileTitle;

        return "<a class='acf-view__link' href='" . esc_attr($fileUrl) . "'>" . esc_html($fileTitle) . "</a>";
    }

    protected function getGalleryRowMarkup($imageValue, string $imageMarkup, bool $isWithSize): string
    {
        return $imageMarkup;
    }

    protected function getGalleryMarkup(bool $isWithSize = false, bool $isWithFullSizeInData = false): string
    {
        $markup = '';
        $images = (array)($this->fieldValue ?: []);

        foreach ($images as $image) {
            $imageMarkup = $this->getImageMarkup($image, $isWithSize, $isWithFullSizeInData);
            $markup .= $this->getGalleryRowMarkup($image, $imageMarkup, $isWithSize);
        }

        return $markup;
    }

    // choice types

    protected function getSelectMarkup(): string
    {
        $fieldHTML = '';

        // select & checkbox could have multiple values
        $fieldValues = (is_array($this->fieldValue) &&
            !key_exists('label', $this->fieldValue)) ?
            $this->fieldValue :
            [$this->fieldValue];

        $fieldViews = [];
        foreach ($fieldValues as $fieldValue) {
            switch ($this->fieldMeta->getReturnFormat()) {
                case 'value':
                    $fieldViews[] = $this->fieldMeta->getChoices()[$fieldValue] ?? '';
                    break;
                case 'label':
                    $fieldViews[] = $fieldValue;
                    break;
                case 'array':
                    $fieldViews[] = $fieldValue['label'] ?? '';
                    break;
            }
        }

        // only one value is possible for them
        if (in_array($this->fieldMeta->getType(), ['radio', 'button_group',], true)) {
            return implode('', $fieldViews);
        }

        foreach ($fieldViews as $subFieldView) {
            if ($this->field->optionsDelimiter &&
                !!$fieldHTML) {
                $fieldHTML .= sprintf("<span class='acf-view__delimiter'>%s</span>", $this->field->optionsDelimiter);
            }

            $fieldHTML .= "<div class='acf-view__choice'>" . esc_html($subFieldView) . "</div>";
        }

        return $fieldHTML;
    }

    protected function getTrueFalseMarkup(): string
    {
        $state = $this->fieldValue ?
            'checked' :
            'unchecked';

        return "<div class='acf-view__true-false acf-view__true-false--state--" . esc_attr($state) . "'></div>";
    }

    // jquery types

    protected function getMapMarkup(): string
    {
        $mapMarkup = '';

        // always should be an array
        if (!is_array($this->fieldValue)) {
            return $mapMarkup;
        }

        $zoom = $this->fieldValue['zoom'] ?? '16';
        $lat = $this->fieldValue['lat'] ?? '';
        $lng = $this->fieldValue['lng'] ?? '';

        // zoom gets from every specific map (when admin zoom out and save a page, the zoom is also saved)
        $mapMarkup .= sprintf(
            '<div class="acf-views__map" style="width:100%%;height:400px;" data-zoom="%s">',
            $zoom
        );

        $mapMarkup .= sprintf(
            '<div class="acf-views__map-marker" data-lat="%s" data-lng="%s"></div>',
            esc_attr($lat),
            esc_attr($lng)
        );

        $mapMarkup .= '</div>';


        return $mapMarkup;
    }

    protected function getDatepickerMarkup(): string
    {
        $displayFormat = $this->fieldMeta->getDisplayFormat();
        // $returnFormat = $this->fieldMeta->getReturnFormat();

        if (!$this->fieldValue ||
            !$displayFormat) {
            return '';
        }

        // we got fieldValue without formatting (see '->getFieldValue()' for date/time fields)
        // so '$this->fieldValue' has the default ACF format

        $date = false;

        switch ($this->fieldMeta->getType()) {
            case 'date_picker':
                $date = DateTime::createFromFormat('Ymd', $this->fieldValue);
                break;
            case 'date_time_picker':
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->fieldValue);
                break;
            case 'time_picker':
                $date = DateTime::createFromFormat('H:i:s', $this->fieldValue);
                break;
        }

        if (false === $date) {
            return '';
        }

        // date_i18n() unlike the '$date->format($displayFormat)' supports different languages
        return date_i18n($displayFormat, $date->getTimestamp());
    }

    protected function getColorPickerMarkup(): string
    {
        if ('array' !== $this->fieldMeta->getReturnFormat() ||
            !is_array($this->fieldValue)) {
            return $this->fieldValue;
        }

        $red = (string)($this->fieldValue['red'] ?? '');
        $green = (string)($this->fieldValue['green'] ?? '');
        $blue = (string)($this->fieldValue['blue'] ?? '');
        $alpha = (string)($this->fieldValue['alpha'] ?? '');

        return sprintf('rgba(%s;%s;%s;%s)', esc_html($red), esc_html($green), esc_html($blue), esc_html($alpha));
    }

    // relation types

    protected function getLinkMarkup($fieldValue, string $returnFormat): string
    {
        $linkUrl = '';
        $linkTitle = '';

        switch ($returnFormat) {
            case 'array':
                $linkUrl = $fieldValue['url'] ?? '';
                $linkTitle = $fieldValue['title'] ?? '';
                break;
            case 'url':
                $linkUrl = (string)$fieldValue;
                break;
        }

        $target = (isset($fieldValue['target']) && $fieldValue['target']) ?
            '_blank' :
            '_self';

        // use linkLabel if exists
        $linkTitle = $this->field->linkLabel ?: $linkTitle;

        // use label if title is empty
        $linkTitle = !$linkTitle ?
            $this->field->label :
            $linkTitle;

        // use url if title is empty
        $linkTitle = !$linkTitle ?
            $linkUrl :
            $linkTitle;

        return "<a target='" . esc_attr($target) . "' class='acf-view__link' href='" . esc_attr($linkUrl) . "'>" .
            esc_html($linkTitle) . "</a>";
    }

    protected function getImageLinkMarkup(): string
    {
        if (!is_array($this->fieldValue) ||
            !key_exists('image_id', $this->fieldValue) ||
            !key_exists('permalink', $this->fieldValue)) {
            return '';
        }

        return "<a target='_self' class='acf-view__link' href='" . esc_attr($this->fieldValue['permalink']) . "'>" .
            $this->getImageMarkup($this->fieldValue['image_id'], false, false, 'id') .
            "</a>";
    }

    protected function displayPostObject(int $postId): string
    {
        $linkLabel = $this->field->linkLabel ?: get_the_title($postId);

        return $this->getLinkMarkup([
            'url' => (string)get_the_permalink($postId),
            'title' => $linkLabel,
        ],
            'array');
    }

    protected function getPostObjectMarkup(): string
    {
        $postObjectMarkup = '';

        $posts = is_array($this->fieldValue) ?
            $this->fieldValue :
            [$this->fieldValue];

        foreach ($posts as $post) {
            if ($this->field->optionsDelimiter &&
                !!$postObjectMarkup) {
                $postObjectMarkup .= sprintf(
                    "<span class='acf-view__delimiter'>%s</span>",
                    $this->field->optionsDelimiter
                );
            }

            $postId = 'object' === $this->fieldMeta->getReturnFormat() ?
                (int)($post->ID ?? 0) :
                (int)$post;

            $postObjectMarkup .= $this->displayPostObject($postId);
        }

        return $postObjectMarkup;
    }

    protected function getPageLinkMarkup(): string
    {
        $markup = '';

        $links = is_array($this->fieldValue) ?
            $this->fieldValue :
            [$this->fieldValue];

        foreach ($links as $linkUrl) {
            if ($this->field->optionsDelimiter &&
                !!$markup) {
                $markup .= sprintf("<span class='acf-view__delimiter'>%s</span>", $this->field->optionsDelimiter);
            }

            $linkTitle = $this->field->linkLabel ?: $this->field->label;
            $linkTitle = !$linkTitle ?
                $linkUrl :
                $linkTitle;

            $markup .= $this->getLinkMarkup([
                'url' => $linkUrl,
                'title' => $linkTitle,
            ],
                'array');
        }

        return $markup;
    }

    protected function getTaxonomyMarkup(): string
    {
        $taxonomyMarkup = '';

        $fieldValue = $this->fieldValue;

        // if the single option and the value is set, then convert to the multiple format
        if (!in_array($this->fieldMeta->getAppearance(), ['checkbox', 'multi_select',], true) &&
            !!$fieldValue) {
            $fieldValue = [$fieldValue,];
        }

        // skip if it's NULL or the array is empty
        if (!$fieldValue) {
            return $taxonomyMarkup;
        }

        foreach ($fieldValue as $term) {
            if ($this->field->optionsDelimiter &&
                !!$taxonomyMarkup) {
                $taxonomyMarkup .= sprintf(
                    "<span class='acf-view__delimiter'>%s</span>",
                    $this->field->optionsDelimiter
                );
            }

            $term = 'id' === $this->fieldMeta->getReturnFormat() ?
                get_term($term) :
                $term;

            $linkLabel = '';
            $linkUrl = '';

            // don't use simple 'continue' as markup needs for tests
            if ($term) {
                $linkLabel = $this->field->linkLabel ?: $term->name;
                $linkUrl = (string)get_term_link($term);
            }

            $taxonomyMarkup .= $this->getLinkMarkup([
                'url' => $linkUrl,
                'title' => $linkLabel,
            ],
                'array');
        }

        return $taxonomyMarkup;
    }

    protected function getUserMarkup(): string
    {
        $userMarkup = '';

        // multiple option is available
        $users = (is_array($this->fieldValue) &&
            !key_exists('display_name', $this->fieldValue)) ?
            $this->fieldValue :
            [$this->fieldValue,];

        foreach ($users as $user) {
            if ($this->field->optionsDelimiter &&
                !!$userMarkup) {
                $userMarkup .= sprintf(
                    "<span class='acf-view__delimiter'>%s</span>",
                    $this->field->optionsDelimiter
                );
            }

            $linkLabel = '';
            $linkUrl = '';

            switch ($this->fieldMeta->getReturnFormat()) {
                case 'object':
                    $linkLabel = $user->display_name ?? '';
                    $linkUrl = get_author_posts_url($user->ID ?? 0);
                    break;
                case 'id':
                    $userObject = get_user_by('ID', $user);

                    $linkLabel = $userObject->display_name ?? '';
                    $linkUrl = get_author_posts_url($userObject->ID ?? 0);
                    break;
                case 'array':
                    $linkLabel = $user['display_name'] ?? '';
                    $linkUrl = get_author_posts_url($user['ID'] ?? 0);
                    break;
            }

            // use linkLabel if available
            $linkLabel = $this->field->linkLabel ?: $linkLabel;

            $userMarkup .= $this->getLinkMarkup([
                'url' => $linkUrl,
                'title' => $linkLabel,
            ],
                'array');
        }

        return $userMarkup;
    }

    // markup

    public function getMarkup(int $viewId): string
    {
        $isSupportedFieldType = in_array($this->fieldMeta->getType(), $this->acf->getFieldTypes(), true);
        $isCustomType = $this->fieldMeta->isCustomType();

        // field could be removed or file type could be changed after a view has been saved
        if (!$this->fieldMeta->isFieldExist() ||
            (!$isSupportedFieldType && !$isCustomType)) {
            return '';
        }

        $this->fieldValue = apply_filters(
            'acf_views/view/field_value',
            $this->fieldValue,
            $this->fieldMeta,
            $viewId
        );

        if (!$isSupportedFieldType) {
            $this->fieldValue = apply_filters(
                'acf_views/view/field_value/type=' . $this->fieldMeta->getType(),
                $this->fieldValue,
                $this->fieldMeta,
                $viewId
            );
        }

        $this->fieldValue = apply_filters(
            'acf_views/view/field_value/name=' . $this->fieldMeta->getName(),
            $this->fieldValue,
            $this->fieldMeta,
            $viewId
        );
        $this->fieldValue = apply_filters(
            'acf_views/view/field_value/view_id=' . $viewId,
            $this->fieldValue,
            $this->fieldMeta,
            $viewId
        );

        if ('true_false' !== $this->fieldMeta->getType() &&
            !$this->fieldValue) {
            return '';
        }

        $fieldHTML = $this->fieldValue ?: '';

        // transformation for certain types
        switch ($this->fieldMeta->getType()) {
            //// content types
            case 'image':
                $fieldHTML = $this->getImageMarkup($this->fieldValue);
                break;
            case 'file':
                $fieldHTML = $this->getFileMarkup();
                break;
            case 'gallery':
                $fieldHTML = $this->getGalleryMarkup();
                break;

            //// choice types
            case 'select':
            case 'checkbox':
            case 'radio':
            case 'button_group':
                $fieldHTML = $this->getSelectMarkup();
                break;
            case 'true_false':
                $fieldHTML = $this->getTrueFalseMarkup();
                break;

            //// relational
            case 'link':
                $fieldHTML = $this->getLinkMarkup($this->fieldValue, $this->fieldMeta->getReturnFormat());
                break;
            case 'page_link':
                $fieldHTML = $this->getPageLinkMarkup();
                break;
            case 'post_object':
            case 'relationship':
                $fieldHTML = $this->getPostObjectMarkup();
                break;
            case 'taxonomy':
                $fieldHTML = $this->getTaxonomyMarkup();
                break;
            case 'user':
                $fieldHTML = $this->getUserMarkup();
                break;

            //// jquery types
            case 'google_map':
                $fieldHTML = $this->getMapMarkup();
                break;
            case 'date_picker':
            case 'date_time_picker':
            case 'time_picker':
                $fieldHTML = $this->getDatepickerMarkup();
                break;
            case 'color_picker':
                $fieldHTML = $this->getColorPickerMarkup();
                break;

            //// custom types (missing in ACF)
            case '_image_link':
                $fieldHTML = $this->getImageLinkMarkup();
                break;

            //// others
            default:
                // convert all types (int, float, string) to string
                $fieldHTML = (string)$fieldHTML;

                // don't escape output of wysiwyg, as HTML is expected here
                if (in_array($this->fieldMeta->getType(), ['wysiwyg', 'oembed',], true)) {
                    break;
                }

                $fieldHTML = esc_html($fieldHTML);

                // convert new lines to tags, as within Divs by default '\n' is ignored
                if ('textarea' === $this->fieldMeta->getType()) {
                    $fieldHTML = str_replace("\n", "<br>", $fieldHTML);
                }
                break;
        }

        $fieldHTML = (string)apply_filters(
            'acf_views/view/field_markup',
            $fieldHTML,
            $this->fieldMeta,
            $this->fieldValue,
            $viewId
        );
        $fieldHTML = (string)apply_filters(
            'acf_views/view/field_markup/name=' . $this->fieldMeta->getName(),
            $fieldHTML,
            $this->fieldMeta,
            $this->fieldValue,
            $viewId
        );

        if (!$isCustomType) {
            $fieldHTML = (string)apply_filters(
                'acf_views/view/field_markup/type=' . $this->fieldMeta->getType(),
                $fieldHTML,
                $this->fieldMeta,
                $this->fieldValue,
                $viewId
            );
        }

        $fieldHTML = (string)apply_filters(
            'acf_views/view/field_markup/view_id=' . $viewId,
            $fieldHTML,
            $this->fieldMeta,
            $this->fieldValue,
            $viewId
        );

        return $fieldHTML;
    }
}
