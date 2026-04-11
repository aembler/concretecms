<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field;

use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Http\Request;

abstract class AbstractFieldType implements FieldInterface
{
    public function getComponentProps(): array
    {
        return [];
    }

    public function jsonSerialize(): array
    {
        return [
            'handle' => $this->getHandle(),
            'component' => $this->getComponent(),
            'componentProps' => $this->getComponentProps(),
        ];
    }

    public function normalizeDefinition(array $definition): array
    {
        return $definition;
    }

    public function extractValueFromRequest(array $requestArgs, FieldDefinition $field, ?Request $request = null)
    {
        return $this->extractScalarValueFromRequest($requestArgs, $field);
    }

    /**
     * @param mixed $submittedValue
     *
     * @return mixed
     */
    public function serializeValue($submittedValue, array $definition, ?Request $request = null)
    {
        return $submittedValue;
    }

    protected function extractScalarValueFromRequest(array $requestArgs, FieldDefinition $field)
    {
        $fieldId = $field->getId();
        if (array_key_exists($fieldId, $requestArgs)) {
            return $requestArgs[$fieldId];
        }

        $definition = $field->getDefinition();

        return $definition['default'] ?? null;
    }
}
