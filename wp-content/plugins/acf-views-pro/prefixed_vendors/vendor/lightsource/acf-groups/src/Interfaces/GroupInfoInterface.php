<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces;

interface GroupInfoInterface extends FieldsInfoInterface
{
    public static function isLocalGroup() : bool;
    public static function getAcfGroupName() : string;
    public static function getAcfFieldName(string $fieldName) : string;
    // https://www.advancedcustomfields.com/resources/register-fields-via-php/
    public static function getGroupInfo() : array;
}
