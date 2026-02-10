<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

use org\wplake\acf_views\AcfGroups\AcfViewData;
use org\wplake\acf_views\AcfGroups\Field;
use org\wplake\acf_views\AcfGroups\Item;
use org\wplake\acf_views\Html;

defined('ABSPATH') || exit;

class ViewMarkup
{
    /**
     * @var AcfViewData[]
     */
    protected array $renderedViews;
    // cache
    protected array $markups;
    protected Html $html;
    protected bool $isWithGoogleMap;

    public function __construct(Html $html)
    {
        $this->html = $html;
        $this->renderedViews = [];
        $this->markups = [];
        $this->isWithGoogleMap = false;
    }

    protected function getRowMarkup(FieldMeta $fieldMeta, Item $item): string
    {
        // isn't supported in the basic version
        if (in_array($fieldMeta->getType(), ['repeater', 'group',], true)) {
            return '';
        }

        $fieldId = $item->field->id;

        return sprintf("\r\n\t<!--$%s$-->\r\n", esc_html($fieldId)) .
            $this->html->viewRow(
                'row',
                "\t",
                'acf-view__' . $item->field->id,
                $item->field->label,
                '$' . $item->field->id . '$',
                false
            ) .
            sprintf("\t<!--$%s$-->\r\n", esc_html($fieldId));
    }

    protected function getMarkupFromCache(AcfViewData $view, bool $isSkipCache): string
    {
        if (key_exists($view->getSource(), $this->markups) &&
            !$isSkipCache) {
            return $this->markups[$view->getSource()];
        }

        $fieldsMeta = $view->getFieldsMeta();
        // e.g. already filled for cache/tests
        if (!$fieldsMeta) {
            $view->setFieldsMeta();
            $fieldsMeta = $view->getFieldsMeta();
        }

        $content = '';
        foreach ($view->items as $item) {
            $content .= $this->getRowMarkup($fieldsMeta[$item->field->getAcfFieldId()], $item);
        }

        return $this->html->view($view->getSource(), $view->cssClasses, $content);
    }

    protected function markViewAsRendered(AcfViewData $view): void
    {
        $this->renderedViews[$view->getSource()] = $view;
    }

    /**
     * @return Field[]
     */
    protected function getFieldsByType(string $type, AcfViewData $view): array
    {
        $fieldsMeta = $view->getFieldsMeta();
        if (!$fieldsMeta) {
            $view->setFieldsMeta();
            $fieldsMeta = $view->getFieldsMeta();
        }

        $fitFields = [];

        foreach ($view->items as $item) {
            $isFit = $type === $fieldsMeta[$item->field->getAcfFieldId()]->getType();

            if (!$isFit) {
                continue;
            }

            $fitFields[] = $item->field;
        }

        return $fitFields;
    }

    protected function setIsWithGoogleMap(AcfViewData $view): void
    {
        if ($this->isWithGoogleMap) {
            return;
        }

        $mapFields = $this->getFieldsByType('google_map', $view);
        foreach ($mapFields as $mapField) {
            if ($mapField->isMapWithoutGoogleMap) {
                continue;
            }

            $this->isWithGoogleMap = true;
            return;
        }
    }

    public function getMarkup(
        AcfViewData $view,
        int         $pageId,
        string      $viewMarkup = '',
        bool        $isSkipCache = false
    ): string
    {
        $viewMarkup = $viewMarkup ?: $this->getMarkupFromCache($view, $isSkipCache);

        // don't make return in case $pageId = 0, it can be WooCommerce Shop Page
        // (otherwise won't be marked as rendered, and CSS won't be printed on the page
        /*  if (! $pageId) {
              return $viewMarkup;
          }*/

        if (!key_exists($view->getSource(), $this->renderedViews)) {
            $this->markViewAsRendered($view);
            $this->setIsWithGoogleMap($view);
        }

        $this->markups[$view->getSource()] = $viewMarkup;

        return $viewMarkup;
    }

    public function getRenderedViews(): array
    {
        return $this->renderedViews;
    }

    public function isWithGoogleMap(): bool
    {
        return $this->isWithGoogleMap;
    }
}
