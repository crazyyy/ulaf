<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces;

use org\wplake\acf_views\vendors\LightSource\AcfGroups\FieldInfo;
use Exception;
interface FieldsInfoInterface
{
    /**
     * @return FieldInfo[]
     * @throws Exception
     */
    public static function getFieldsInfo() : array;
}
