<?php

declare (strict_types=1);
namespace org\wplake\acf_views\vendors\LightSource\AcfGroups;

use Exception;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\AcfGroupInterface;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;
use org\wplake\acf_views\vendors\LightSource\AcfGroups\Interfaces\FieldInfoInterface;
abstract class AcfGroup extends GroupInfo implements AcfGroupInterface
{
    /**
     * @var int|string|false
     */
    private $source;
    private string $clonePrefix;
    private array $fieldsInfo;
    private array $originalFieldValues;
    private bool $isLoaded;
    private CreatorInterface $creator;
    private ?array $externalData;
    /**
     * @throws Exception
     */
    public function __construct(CreatorInterface $creator)
    {
        $this->creator = $creator;
        $this->source = \false;
        $this->clonePrefix = '';
        $this->originalFieldValues = [];
        $this->isLoaded = \false;
        $this->externalData = null;
        $this->fieldsInfo = static::getFieldsInfo();
        $this->setDefaultValuesForFields();
    }
    /**
     * 'getFieldValues()' method is designed to work with update_field() (for repeaters)
     * if you want to use (replace) values from 'acf/pre_load_value' or 'acf/pre_update_value' hooks you have to use this method
     * 'pre_update_value' = before saving, to get the AcfGroup (current class) format, which means after you can pass the array into the 'load()' method
     * 'pre_load_value' = before loading, mark '$isFromAcfFormat=false', it'll convert the AcfGroup (current class) format to ACF, and ACF (UI and others) will understand it
     */
    public static function convertRepeaterFieldValues(string $repeaterFieldName, array $rows, bool $isFromAcfFormat = \true, bool $isSkipIndexUpdate = \false) : array
    {
        $newValue = [];
        $prefix = $repeaterFieldName . '_item_';
        foreach ($rows as $index => $row) {
            $newRow = [];
            // can be plain field instead of array (in case the function called from the 'convertCloneField()' method)
            if (!\is_array($row)) {
                $newValue[$index] = $row;
                continue;
            }
            foreach ($row as $itemFieldName => $itemFieldValue) {
                $newItemFieldName = $isFromAcfFormat ? \substr($itemFieldName, \strlen($prefix)) : $prefix . $itemFieldName;
                $fieldNameToFixArray = $isFromAcfFormat ? $newItemFieldName : $itemFieldName;
                $newRow[$newItemFieldName] = \is_array($itemFieldValue) ? static::convertRepeaterFieldValues($fieldNameToFixArray, $itemFieldValue, $isFromAcfFormat) : $itemFieldValue;
            }
            if (!$isSkipIndexUpdate) {
                $newIndex = $isFromAcfFormat ? \str_replace('row-', '', $index) : 'row-' . $index;
            } else {
                $newIndex = $index;
            }
            $newValue[$newIndex] = $newRow;
        }
        return $newValue;
    }
    /**
     * 'getFieldValues()' method is designed to work with update_field() (for repeaters)
     * if you want to use (replace) values from 'acf/pre_load_value' or 'acf/pre_update_value' hooks you have to use this method
     */
    public static function convertCloneField(string $cloneFieldName, array $fields, bool $isFromAcfFormat = \true) : array
    {
        if (!$isFromAcfFormat) {
            return static::convertRepeaterFieldValues($cloneFieldName, $fields, $isFromAcfFormat);
        }
        $newFields = [];
        $prefix = $cloneFieldName . '_';
        foreach ($fields as $cloneSubFieldName => $cloneFieldValue) {
            $newCloneFieldName = \substr($cloneSubFieldName, \strlen($prefix));
            // can be string field
            if (!\is_array($cloneFieldValue)) {
                $newFields[$newCloneFieldName] = $cloneFieldValue;
                continue;
            }
            $newFields[$cloneSubFieldName] = static::convertRepeaterFieldValues($newCloneFieldName, $cloneFieldValue, $isFromAcfFormat, \true);
        }
        return $newFields;
    }
    protected function getCreator() : CreatorInterface
    {
        return $this->creator;
    }
    /**
     * @throws Exception
     */
    protected function getDefaultValue(FieldInfoInterface $fieldInfo)
    {
        $fieldValue = null;
        $fieldName = $fieldInfo->getName();
        switch ($fieldInfo->getType()) {
            case 'bool':
                $fieldValue = \false;
                break;
            case 'int':
            case 'float':
                $fieldValue = 0;
                break;
            case 'string':
                $fieldValue = '';
                break;
            case 'array':
                $fieldValue = [];
                break;
            default:
                $itemClass = $fieldInfo->getType();
                $fieldValue = $this->creator->create($itemClass);
                $fieldValue->setClonePrefix($this->getAcfFieldNameWithClonePrefix($fieldName) . '_');
                break;
        }
        return $fieldValue;
    }
    /**
     * @throws Exception
     */
    protected function isDefaultValue(FieldInfoInterface $fieldInfo, $fieldValue) : bool
    {
        if (isset($fieldInfo->getArguments()['default_value'])) {
            return $fieldInfo->getArguments()['default_value'] === $fieldValue;
        }
        return $fieldValue === $this->getDefaultValue($fieldInfo);
    }
    /**
     * @throws Exception
     */
    protected function setDefaultValueForField(FieldInfoInterface $fieldInfo) : void
    {
        $fieldName = $fieldInfo->getName();
        $this->{$fieldName} = $this->getDefaultValue($fieldInfo);
        // null, because we don't know what is in DB
        $this->originalFieldValues[$fieldName] = null;
    }
    /**
     * @throws Exception
     */
    protected function setDefaultValuesForFields() : void
    {
        foreach ($this->fieldsInfo as $fieldInfo) {
            $this->setDefaultValueForField($fieldInfo);
        }
    }
    /**
     * @return mixed
     */
    protected function getAcfFieldValue(FieldInfoInterface $fieldInfo)
    {
        $acfFieldName = $this->getAcfFieldNameWithClonePrefix($fieldInfo->getName());
        if ($this->isExternalSource()) {
            if (isset($this->externalData[$acfFieldName])) {
                return $this->externalData[$acfFieldName];
            }
            return $fieldInfo->getArguments()['default_value'] ?? null;
        }
        return \function_exists('get_field') ? \get_field($acfFieldName, $this->source) : null;
    }
    protected function setAcfFieldValue(string $acfFieldName, $value) : void
    {
        if (!\function_exists('update_field')) {
            return;
        }
        \update_field($acfFieldName, $value, $this->source);
    }
    protected function getAcfFieldNameWithClonePrefix(string $fieldName) : string
    {
        return $this->clonePrefix . $this->getAcfFieldName($fieldName);
    }
    /**
     * @throws Exception
     */
    protected function loadRepeaterField(FieldInfoInterface $fieldInfo) : array
    {
        $itemClass = $fieldInfo->getArguments()['item'] ?? '';
        if (!$itemClass) {
            throw new Exception('Array field must have the "item" php-doc attribute, class :' . \get_class($this));
        }
        // to make sure the class is right
        // (so if the class is wrong exception will be always, not only when there are data in a field)
        $this->creator->create($itemClass);
        $acfFieldName = $this->getAcfFieldNameWithClonePrefix($fieldInfo->getName());
        $acfFieldValue = $this->getAcfFieldValue($fieldInfo);
        // don't use (array)$this->getAcfFieldValue() because it'll create not empty array in a 'false' case
        $items = \is_array($acfFieldValue) ? $acfFieldValue : [];
        $fieldValue = [];
        //  foreach instead of for, as identifier can have 'string' type instead of 'int' (unique id)
        $i = 0;
        foreach ($items as $row) {
            $item = $this->creator->create($itemClass);
            if ($this->isExternalSource()) {
                // with ->isExternalSource() clone prefix is not needed, as fields are without a prefix in the sub array
                $item->load($this->source, '', $row);
            } else {
                $itemPrefix = $acfFieldName . '_' . $i . '_';
                $item->load($this->source, $itemPrefix);
            }
            $fieldValue[] = $item;
            $i++;
        }
        return $fieldValue;
    }
    /**
     * @throws Exception
     */
    protected function loadCloneField(FieldInfoInterface $fieldInfo) : AcfGroupInterface
    {
        $acfFieldName = $this->getAcfFieldNameWithClonePrefix($fieldInfo->getName());
        $fieldValue = $this->creator->create($fieldInfo->getType());
        // even in case ->isExternalSource() send the whole $externalData,
        // as clone fields are merged into the same group, not like repeater (array in array), but like ordinary fields
        $fieldValue->load($this->source, $acfFieldName . '_', $this->externalData);
        return $fieldValue;
    }
    /**
     * @throws Exception
     */
    protected function loadField(FieldInfoInterface $fieldInfo) : void
    {
        $fieldName = $fieldInfo->getName();
        $acfFieldName = $this->getAcfFieldNameWithClonePrefix($fieldInfo->getName());
        $fieldValue = null;
        switch ($fieldInfo->getType()) {
            case 'bool':
                $fieldValue = (bool) $this->getAcfFieldValue($fieldInfo);
                break;
            case 'int':
                $fieldValue = (int) $this->getAcfFieldValue($fieldInfo);
                break;
            case 'float':
                $fieldValue = (float) $this->getAcfFieldValue($fieldInfo);
                break;
            case 'string':
                $fieldValue = (string) $this->getAcfFieldValue($fieldInfo);
                break;
            case 'array':
                if ($this->isRepeaterField($fieldName)) {
                    $fieldValue = $this->loadRepeaterField($fieldInfo);
                } else {
                    $fieldValue = $this->getAcfFieldValue($fieldInfo);
                    // don't use (array)get_field() it gives wrong results for false and null
                    $fieldValue = \is_array($fieldValue) ? $fieldValue : [];
                }
                break;
            default:
                $fieldValue = $this->loadCloneField($fieldInfo);
                break;
        }
        $this->{$fieldName} = $fieldValue;
        // will be used for comparison in the save method to avoid unnecessary db requests
        $this->originalFieldValues[$fieldName] = $fieldValue;
    }
    protected function isRepeaterField(string $fieldName) : bool
    {
        foreach ($this->fieldsInfo as $fieldInfo) {
            if ($fieldInfo->getName() !== $fieldName) {
                continue;
            }
            return $fieldInfo->isRepeater();
        }
        return \false;
    }
    /**
     * @param string $acfFieldName
     * @param AcfGroupInterface[] $newFieldValue
     * @param AcfGroupInterface[] $originalFieldValue
     * @param bool $isForce
     *
     * @return bool
     */
    protected function saveRepeater(string $acfFieldName, array &$newFieldValue, ?array $originalFieldValue, bool $isForce) : bool
    {
        $countOfItems = \count($newFieldValue);
        $countOfOriginalItems = $originalFieldValue ? \count($originalFieldValue) : 0;
        $isRepeaterHasChanges = $countOfItems !== $countOfOriginalItems || $isForce;
        for ($i = 0; $i < $countOfItems && !$isRepeaterHasChanges; $i++) {
            $cloneObject = $newFieldValue[$i];
            if (!$cloneObject->isHasChanges()) {
                continue;
            }
            $isRepeaterHasChanges = \true;
        }
        if (!$isRepeaterHasChanges) {
            return \false;
        }
        $dataArray = [];
        for ($i = 0; $i < $countOfItems; $i++) {
            // set up the new right prefix ($newFieldValue argument accepted by a link, so it'll be updated)
            $newFieldValue[$i]->setClonePrefix($acfFieldName . '_' . $i . '_');
            // the values will be saved once for all items below,
            // so item->isHasChanges() should give 'false' down the line
            $newFieldValue[$i]->refreshFieldValuesCache();
            $dataArray[] = $newFieldValue[$i]->getFieldValues();
        }
        $this->setAcfFieldValue($acfFieldName, $dataArray);
        return \true;
    }
    /**
     * @param false|string|int $source
     * @param string $clonePrefix
     * @param null|array $externalData Can be output of the 'getFieldValues()' method
     *
     * @throws Exception
     */
    public function load($source = \false, string $clonePrefix = '', ?array $externalData = null) : bool
    {
        $this->source = $source;
        $this->clonePrefix = $clonePrefix;
        $this->externalData = $externalData;
        foreach ($this->fieldsInfo as $fieldInfo) {
            $this->loadField($fieldInfo);
        }
        $this->isLoaded = \true;
        return \true;
    }
    public function loadFromPostContent(int $postId) : bool
    {
        global $wpdb;
        if (!$wpdb) {
            return \false;
        }
        // don't use 'get_post($id)->post_content' to avoid the kses issue https://core.trac.wordpress.org/ticket/38715
        $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d LIMIT 1", $postId));
        $content = $post->post_content ?? '';
        $content = \json_decode($content, \true) ?: [];
        return $this->load($postId, '', $content);
    }
    public function isExternalSource() : bool
    {
        return \is_array($this->externalData);
    }
    public function isLoaded() : bool
    {
        return $this->isLoaded;
    }
    public function isHasChanges() : bool
    {
        foreach ($this->originalFieldValues as $fieldName => $originalFieldValue) {
            $newFieldValue = $this->{$fieldName};
            if ($this->isRepeaterField($fieldName)) {
                /**
                 * @var AcfGroupInterface $cloneObject
                 */
                foreach ($newFieldValue as $cloneObject) {
                    if ($cloneObject->isHasChanges()) {
                        return \true;
                    }
                }
            }
            if ($newFieldValue instanceof AcfGroupInterface && $newFieldValue->isHasChanges()) {
                return \true;
            }
            if ($originalFieldValue !== $newFieldValue) {
                return \true;
            }
        }
        return \false;
    }
    // can be used to recognize fields which need to apply 'convertRepeaterFieldValues()' method,
    // as not every array field is a repeater (can be plain field with return-type = array)
    public function getRepeaterFieldNames() : array
    {
        $repeaterFieldNames = [];
        foreach ($this->fieldsInfo as $fieldInfo) {
            if (!$fieldInfo->isRepeater()) {
                continue;
            }
            $repeaterFieldNames[] = $this->getAcfFieldName($fieldInfo->getName());
        }
        return $repeaterFieldNames;
    }
    // can be used to recognize fields which need to apply 'convertCloneField()' method
    public function getCloneFieldNames() : array
    {
        $cloneFieldNames = [];
        foreach ($this->fieldsInfo as $fieldInfo) {
            $fieldName = $fieldInfo->getName();
            $fieldValue = $this->{$fieldName};
            if (!$fieldValue instanceof AcfGroupInterface) {
                continue;
            }
            $cloneFieldNames[] = $this->getAcfFieldName($fieldName);
        }
        return $cloneFieldNames;
    }
    public function refreshFieldValuesCache() : void
    {
        foreach ($this->originalFieldValues as $fieldName => $originalFieldValue) {
            $this->originalFieldValues[$fieldName] = $this->{$fieldName};
        }
    }
    public function save(bool $isForce = \false) : bool
    {
        $isHasChangedFields = \false;
        foreach ($this->originalFieldValues as $fieldName => $originalFieldValue) {
            $newFieldValue = $this->{$fieldName};
            $acfFieldName = $this->getAcfFieldNameWithClonePrefix($fieldName);
            if ($this->isRepeaterField($fieldName)) {
                if ($this->saveRepeater($acfFieldName, $newFieldValue, $originalFieldValue, $isForce)) {
                    // update, because e.g. indexes could be changed
                    $this->{$fieldName} = $newFieldValue;
                    $this->originalFieldValues[$fieldName] = $newFieldValue;
                    $isHasChangedFields = \true;
                }
                continue;
            }
            if ($newFieldValue instanceof AcfGroupInterface) {
                if ($newFieldValue->save($isForce)) {
                    $this->originalFieldValues[$fieldName] = $newFieldValue;
                    $isHasChangedFields = \true;
                }
                continue;
            }
            if (!$isForce && $originalFieldValue === $newFieldValue) {
                continue;
            }
            $isHasChangedFields = \true;
            $this->originalFieldValues[$fieldName] = $newFieldValue;
            $this->setAcfFieldValue($acfFieldName, $newFieldValue);
        }
        return $isHasChangedFields;
    }
    /**
     * @param array $postFields Can be used to update other post fields (in the same query)
     * @param bool $isSkipDefaults
     *
     * @return bool
     * @throws Exception
     */
    public function saveToPostContent(array $postFields = [], bool $isSkipDefaults = \false) : bool
    {
        global $wpdb;
        if (!$wpdb) {
            return \false;
        }
        // don't escape slashes and line terminators
        $json = \json_encode($this->getFieldValues('', $isSkipDefaults), \JSON_HEX_APOS | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_LINE_TERMINATORS);
        $postFields = \array_merge($postFields, ['post_content' => $json]);
        // don't use 'wp_update_post' to avoid the kses issue https://core.trac.wordpress.org/ticket/38715
        $wpdb->update($wpdb->posts, $postFields, ['ID' => $this->getSource()]);
        return \true;
    }
    /**
     * Designed to work with update_field() (for repeaters)
     * @throws Exception
     */
    public function getFieldValues(string $clonePrefix = '', bool $isSkipDefaults = \false) : array
    {
        $fieldValues = [];
        foreach ($this->fieldsInfo as $fieldInfo) {
            $fieldName = $fieldInfo->getName();
            $acfFieldName = $this->getAcfFieldName($fieldName);
            $fieldValue = $this->{$fieldName};
            // ordinary field
            if (!$fieldValue instanceof AcfGroupInterface && !$this->isRepeaterField($fieldName)) {
                if ($isSkipDefaults && $this->isDefaultValue($fieldInfo, $fieldValue)) {
                    continue;
                }
                $fieldValues[$clonePrefix . $acfFieldName] = $fieldValue;
                continue;
            }
            // clone
            if ($fieldValue instanceof AcfGroupInterface) {
                $fieldValue = $fieldValue->getFieldValues($acfFieldName . '_', $isSkipDefaults);
                // merge with fields, because it's a clone, and his fields are added to this group
                // (not like an array with sub fields as it within a repeater)
                $fieldValues = \array_merge($fieldValues, $fieldValue);
                continue;
            }
            // repeater of clones
            $value = [];
            /**
             * @var AcfGroupInterface $item
             */
            foreach ($fieldValue as $item) {
                $itemFieldValues = $item->getFieldValues('', $isSkipDefaults);
                if ($isSkipDefaults && !$itemFieldValues) {
                    continue;
                }
                $value[] = $itemFieldValues;
            }
            if ($isSkipDefaults && !$value) {
                continue;
            }
            // todo it works for isExternalSource() case,
            // if it doesn't work for direct ACF fields add 'if' and don't use $clonePrefix for direct ACF (like was before)
            $fieldValues[$clonePrefix . $acfFieldName] = $value;
        }
        return $fieldValues;
    }
    /**
     * @return int|string|false
     */
    public function getSource()
    {
        return $this->source;
    }
    public function getExternalData() : ?array
    {
        return $this->externalData;
    }
    public function getClonePrefix() : string
    {
        return $this->clonePrefix;
    }
    public function setClonePrefix(string $clonePrefix) : void
    {
        $this->clonePrefix = $clonePrefix;
    }
    /**
     * get deeps clone unlike the std 'clone'
     * @return static
     */
    public function getDeepClone() : AcfGroupInterface
    {
        $clone = clone $this;
        foreach ($this->fieldsInfo as $fieldInfo) {
            $fieldName = $fieldInfo->getName();
            if (!$clone->{$fieldName} instanceof AcfGroupInterface) {
                continue;
            }
            $clone->{$fieldName} = $clone->{$fieldName}->getDeepClone();
        }
        return $clone;
    }
    /**
     * This method for tests only! Isn't declared in the Interface and shouldn't be used in code
     *
     * @param false|string|int $source
     *
     * @return void
     */
    public function setSource($source) : void
    {
        $this->source = $source;
    }
}
