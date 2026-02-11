<?php

declare(strict_types=1);

namespace org\wplake\acf_views\AcfView;

defined('ABSPATH') || exit;

interface FieldMetaInterface
{
    public function isFieldExist(): bool;

    public function getFieldId(): string;

    public function getName(): string;

    public function getType(): string;

    public function getReturnFormat(): string;

    public function getDisplayFormat(): string;

    public function getChoices(): array;

    public function isMultiple(): bool;

    public function getAppearance(): string;
}
