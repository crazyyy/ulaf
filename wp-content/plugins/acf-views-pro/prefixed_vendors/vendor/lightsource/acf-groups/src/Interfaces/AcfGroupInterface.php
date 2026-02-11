<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces;

use Exception;
interface AcfGroupInterface extends GroupInfoInterface
{
    public static function convertRepeaterFieldValues(string $repeaterFieldName, array $rows, bool $isFromAcfFormat = \true) : array;
    public static function convertCloneField(string $cloneFieldName, array $fields, bool $isFromAcfFormat = \true) : array;
    /**
     * @param false|string|int $source
     *
     * @throws Exception
     */
    public function load($source = \false, string $clonePrefix = '', ?array $externalData = null) : bool;
    public function loadFromPostContent(int $postId) : bool;
    public function isExternalSource() : bool;
    public function isLoaded() : bool;
    public function isHasChanges() : bool;
    public function getRepeaterFieldNames() : array;
    public function getCloneFieldNames() : array;
    public function refreshFieldValuesCache() : void;
    public function save(bool $isForce = \false) : bool;
    /**
     * @param array $postFields Can be used to update other post fields (in the same query)
     *
     * @return bool
     */
    public function saveToPostContent(array $postFields = []) : bool;
    public function getFieldValues(string $clonePrefix = '') : array;
    public function getSource();
    public function getExternalData() : ?array;
    public function getClonePrefix() : string;
    public function setClonePrefix(string $clonePrefix) : void;
    public function getDeepClone() : AcfGroupInterface;
}
