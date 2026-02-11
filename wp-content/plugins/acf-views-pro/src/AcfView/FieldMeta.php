<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

use org\wplake\acf_views\Acf;

defined('ABSPATH') || exit;

class FieldMeta implements FieldMetaInterface
{
    private string $fieldId;
    private string $name;
    private string $type;
    private string $returnFormat;
    private array $choices;
    private array $fieldData;
    private bool $isFieldExist;
    private string $displayFormat;
    private bool $isMultiple;
    private string $appearance;

    public function __construct(string $fieldId, array $fieldData = [])
    {
        $this->fieldId = $fieldId;
        $this->name = '';
        $this->type = '';
        $this->returnFormat = '';
        $this->choices = [];
        $this->fieldData = $fieldData;
        $this->isFieldExist = false;
        $this->displayFormat = '';
        $this->isMultiple = false;
        $this->appearance = '';

        $this->read();
    }

    protected function getPostFieldData(): array
    {
        $fieldData = [
            // it's needed for markup
            'name' => $this->fieldId,
        ];

        switch ($this->fieldId) {
            case Post::FIELD_TITLE:
            case Post::FIELD_DATE:
            case Post::FIELD_MODIFIED:
                $fieldData['type'] = 'text';
                break;
            case Post::FIELD_EXCERPT:
                // HTML is expected for excerpt
                $fieldData['type'] = 'wysiwyg';
                break;
            case Post::FIELD_TITLE_LINK:
            case Post::FIELD_AUTHOR:
                $fieldData['type'] = 'link';
                $fieldData['return_format'] = 'array';
                break;
            case Post::FIELD_THUMBNAIL:
                $fieldData['type'] = 'image';
                $fieldData['return_format'] = 'id';
                break;
            case Post::FIELD_LINK:
                $fieldData['type'] = 'link';
                $fieldData['return_format'] = 'url';
                break;
            case Post::FIELD_THUMBNAIL_LINK:
                // introducing new type, missing in ACF
                $fieldData['type'] = '_image_link';
                break;
        }

        return $fieldData;
    }

    protected function getFieldData(): array
    {
        if ($this->fieldData) {
            return $this->fieldData;
        }

        if (in_array($this->fieldId, Post::getFields(), true)) {
            return $this->getPostFieldData();
        }

        if (0 === strpos($this->fieldId, Acf::TAXONOMY_PREFIX)) {
            return [
                // it's needed for markup
                'name' => str_replace(Acf::TAXONOMY_PREFIX, '', $this->fieldId),
                'type' => 'taxonomy',
                'return_format' => 'id',
                // multiple values are supposed
                'field_type' => 'checkbox',
            ];
        }

        if (!function_exists('get_field_object')) {
            return $this->fieldData;
        }

        $fieldData = get_field_object($this->fieldId);

        return $fieldData ?
            (array)$fieldData :
            [];
    }

    protected function read(): void
    {
        $fieldData = $this->getFieldData();
        $this->name = (string)($fieldData['name'] ?? '');
        $this->type = (string)($fieldData['type'] ?? '');
        $this->returnFormat = (string)($fieldData['return_format'] ?? '');
        $this->choices = (array)($fieldData['choices'] ?? '');
        $this->displayFormat = (string)($fieldData['display_format'] ?? '');
        $this->isMultiple = (bool)($fieldData['multiple'] ?? false);
        $this->appearance = (string)($fieldData['field_type'] ?? '');

        $this->isFieldExist = !!$this->type;
    }

    public function isFieldExist(): bool
    {
        return $this->isFieldExist;
    }

    public function getFieldId(): string
    {
        return $this->fieldId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isCustomType(): bool
    {
        return 0 === strpos($this->type, '_');
    }

    public function getReturnFormat(): string
    {
        return $this->returnFormat;
    }

    public function getDisplayFormat(): string
    {
        return $this->displayFormat;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    public function getAppearance(): string
    {
        return $this->appearance;
    }
}
