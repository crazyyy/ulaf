<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

use org\wplake\acf_views\Acf;
use org\wplake\acf_views\AcfGroups\AcfViewData;
use org\wplake\acf_views\AcfGroups\Field;
use org\wplake\acf_views\AcfGroups\Item;

defined('ABSPATH') || exit;

class AcfView
{
    protected Acf $acf;
    protected AcfViewData $view;
    protected Post $dataPost;
    protected int $pageId;
    protected string $html;

    public function __construct(ACF $acf, AcfViewData $view, Post $dataPost, int $pageId, string $markup)
    {
        $this->acf = $acf;
        $this->view = $view;
        $this->dataPost = $dataPost;
        $this->pageId = $pageId;
        $this->html = $markup;
    }

    protected function getFieldMarkup(FieldMeta $fieldMeta, Field $field, $fieldValue): FieldMarkup
    {
        return new FieldMarkup($this->acf, $fieldMeta, $field, $fieldValue);
    }

    protected function injectFieldInMarkup(
        string $fieldId,
        string $fieldMarkup,
        string $markup,
        bool $isRemoveWhenEmpty
    ): string {
        $regExpFieldId = str_replace('$', '\$', $fieldId);
        $regExp = "/<!--{$regExpFieldId}-->([\s\S]*)<!--{$regExpFieldId}-->/mi";
        preg_match_all($regExp, $markup, $originItemMatch, PREG_SET_ORDER);
        $originItemMatch = $originItemMatch[0] ?? [];

        if (2 !== count($originItemMatch)) {
            return $markup;
        }

        $itemMarkup = !$isRemoveWhenEmpty ?
            str_replace($fieldId, $fieldMarkup, $originItemMatch[1]) :
            '';

        return str_replace($originItemMatch[0], $itemMarkup, $markup);
    }

    protected function insertField(
        FieldMeta $fieldMeta,
        Item $item,
        $fieldValue
    ): void {
        // isn't supported in the basic version
        if (in_array($fieldMeta->getType(), ['repeater', 'group',], true)) {
            return;
        }

        $fieldMarkup = '';
        $isRemoveWhenEmpty = true;

        if (!!$fieldValue ||
            $item->field->isVisibleWhenEmpty ||
            'true_false' === $fieldMeta->getType()) {
            $fieldMarkup = $this->getFieldMarkup($fieldMeta, $item->field, $fieldValue);
            $fieldMarkup = $fieldMarkup->getMarkup($this->view->getSource());
            $isRemoveWhenEmpty = false;
        }

        $this->html = $this->injectFieldInMarkup(
            '$' . $item->field->id . '$',
            $fieldMarkup,
            $this->html,
            $isRemoveWhenEmpty
        );
    }

    protected function getFieldValue(Item $item, FieldMeta $fieldMeta)
    {
        // we need to get timestamp for date/time fields. Otherwise, won't be able to display date in different languages
        $isWithoutFormatting = in_array(
            $fieldMeta->getType(),
            ['date_picker', 'date_time_picker', 'time_picker',],
            true
        );

        return $this->dataPost->getFieldValue($fieldMeta->getFieldId(), $isWithoutFormatting);
    }

    public function insertFields(bool $isMinifyMarkup = true): array
    {
        if ($isMinifyMarkup) {
            // remove special symbols that used in the markup for a preview
            // exactly here, before the fields are inserted, to avoid affecting them
            $this->html = str_replace(["\t", "\n", "\r"], '', $this->html);
        }

        $fieldValues = [];

        $fieldsMeta = $this->view->getFieldsMeta();
        // e.g. already filled for cache/tests
        if (!$fieldsMeta) {
            $this->view->setFieldsMeta();
            $fieldsMeta = $this->view->getFieldsMeta();
        }

        $dateFieldTypes = ['date_picker', 'date_time_picker', 'time_picker',];

        foreach ($this->view->items as $item) {
            $fieldMeta = $fieldsMeta[$item->field->getAcfFieldId()];

            // for IDE
            if (!$fieldMeta instanceof FieldMeta) {
                continue;
            }

            $fieldValue = $this->getFieldValue($item, $fieldMeta);
            $fieldValue = $fieldValue ?: $item->field->defaultValue;

            $fieldValues[$item->field->id] = $fieldValue;

            $this->insertField($fieldMeta, $item, $fieldValue);
        }

        // internal variables
        $this->html = str_replace('{object-id}', strval($this->dataPost->getId()), $this->html);

        return $fieldValues;
    }

    public function getHTML(): string
    {
        return $this->html;
    }
}
