<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups;

use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\FieldInfoInterface;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\FieldsInfoInterface;
use ReflectionProperty;
use Exception;
class FieldsInfo implements FieldsInfoInterface
{
    protected static function getFieldInfoInstance(ReflectionProperty $property) : FieldInfoInterface
    {
        return new FieldInfo($property);
    }
    /**
     * @throws Exception
     */
    protected static function getFieldInfo(string $fieldName) : ?FieldInfoInterface
    {
        try {
            $property = new ReflectionProperty(static::class, $fieldName);
        } catch (Exception $ex) {
            throw new Exception('Fail to create ReflectionProperty, reason : ' . $ex->getMessage());
        }
        if (!$property->isPublic() || $property->isStatic()) {
            return null;
        }
        $fieldInfo = static::getFieldInfoInstance($property);
        // only with supported types
        return $fieldInfo->getType() ? $fieldInfo : null;
    }
    /**
     * @throws Exception
     */
    protected static function readFieldsInfo() : array
    {
        $fieldNames = \array_keys(\get_class_vars(static::class));
        $fieldsByOrder = [];
        $fieldsInfo = [];
        foreach ($fieldNames as $fieldName) {
            $fieldInfo = static::getFieldInfo($fieldName);
            // only public with a supported type
            if (\is_null($fieldInfo)) {
                continue;
            }
            $order = \intval($fieldInfo->getArguments()['a-order']);
            if (!isset($fieldsByOrder[$order])) {
                $fieldsByOrder[$order] = [];
            }
            $fieldsByOrder[$order][] = $fieldInfo;
        }
        \ksort($fieldsByOrder);
        foreach ($fieldsByOrder as $fields) {
            $fieldsInfo = \array_merge($fieldsInfo, $fields);
        }
        return $fieldsInfo;
    }
    /**
     * @return FieldInfo[]
     * @throws Exception
     */
    public static function getFieldsInfo() : array
    {
        return static::readFieldsInfo();
    }
}
