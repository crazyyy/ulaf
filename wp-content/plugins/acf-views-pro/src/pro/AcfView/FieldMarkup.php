<?php

declare(strict_types=1);

namespace org\wplake\acf_views\pro\AcfView;

use org\wplake\acf_views\Plugin;

defined('ABSPATH') || exit;

class FieldMarkup extends \org\wplake\acf_views\AcfView\FieldMarkup
{
    protected function displayPostObject(int $postId): string
    {
        if (!$this->field->acfViewId) {
            return parent::displayPostObject($postId);
        }

        // checking for a recursion is built-in in the shortcode
        $shortcode = sprintf(
            "[%s view-id='%s' object-id='%s']",
            Plugin::SHORTCODE,
            $this->field->acfViewId,
            $postId
        );


        // run shortcode to display
        // (it's necessary, as common 'do_shortcode' was called (ViewMarkup.php) before for the whole markup,
        // it means the field wasn't inserted yet)

        return do_shortcode($shortcode);
    }

    protected function getGalleryRowMarkup($imageValue, string $imageMarkup, bool $isWithSize): string
    {
        if (!$this->field->galleryWithLightBox) {
            return $imageMarkup;
        }

        $sizeData = $isWithSize ?
            ' ' . $this->getImageSizeAttributes($imageValue) :
            '';

        $rowMarkup = sprintf("<div class='acf-view__image-outer'%s>", $sizeData);
        $rowMarkup .= $imageMarkup;
        $rowMarkup .= "<div class='acf-view__image-inner'> <svg class='acf-view__image-zoom-icon'> <use xlink:href='#acf-views-zoom-icon'></svg></div>";
        $rowMarkup .= "</div>";

        return $rowMarkup;
    }

    protected function getGalleryMarkup(bool $isWithSize = false, bool $isWithFullSizeInData = false): string
    {
        if ('masonry' === $this->field->galleryType) {
            $isWithSize = true;
        }

        if ($this->field->galleryWithLightBox) {
            $isWithFullSizeInData = true;
        }

        return parent::getGalleryMarkup($isWithSize, $isWithFullSizeInData);
    }

    protected function getMapMarkup(): string
    {
        $mapMarkup = '';

        // always should be an array
        if (!is_array($this->fieldValue)) {
            return $mapMarkup;
        }

        if ($this->field->mapAddressFormat) {
            $addressFields = ['street_number', 'street_name', 'city', 'state', 'post_code', 'country',];

            $address = $this->field->mapAddressFormat;
            foreach ($addressFields as $addressField) {
                $addressFieldValue = $this->fieldValue[$addressField] ?? '';
                $address = str_replace('$' . $addressField . '$', $addressFieldValue, $address);
            }

            $mapMarkup = '<div class="acf-view__map-address">' .
                $address .
                '</div>';
        }

        if (!$this->field->isMapWithoutGoogleMap) {
            $mapMarkup .= parent::getMapMarkup();
        }

        return $mapMarkup;
    }
}
