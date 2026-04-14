<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Value;

use Concrete\Core\Block\Manifest\BlockManifest;
use Symfony\Component\HttpFoundation\Request;

final class ValueFactory
{
    public function createStorageValueFromRequest(BlockManifest $manifest, Request $request): StorageValue
    {
        return $this->createStorageValueFromArray($manifest, $request->request->all());
    }

    public function createStorageValueFromArray(BlockManifest $manifest, array $requestArgs): StorageValue
    {
        $value = new StorageValue($manifest->getSchemaVersion());
        foreach ($manifest->getFields() as $field) {
            $fieldType = $field->getFieldType();
            $requestValue = $fieldType->extractValueFromRequest($requestArgs, $field);
            $value->addValue($field, $requestValue);
        }
        return $value;
    }

    /**
     * Similar to createFromArray, but storage stores meta, schemaversion, etc... and perhaps
     * some other fields
     *
     * @param BlockManifest $manifest
     * @param array $data
     * @return Value
     */
    public function createViewValue(BlockManifest $manifest, array $values): ViewValue
    {
        $value = new ViewValue();
        foreach ($manifest->getFields() as $field) {
            $fieldType = $field->getFieldType();
            $storageValueValue = $fieldType->extractValueFromStorage($values, $field);
            $value->addValue($field, $storageValueValue);
        }
        return $value;
    }

}
