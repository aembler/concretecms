<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field;

use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Block\Manifest\View\FieldViewValue;
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

    public function extractValueFromStorage(array $payload, FieldDefinition $field)
    {
        $section = EnvelopeSectionResolver::resolve($field);
        $fieldId = $field->getId();
        if (isset($payload[$section]) && is_array($payload[$section]) && array_key_exists($fieldId, $payload[$section])) {
            return $payload[$section][$fieldId];
        }

        $definition = $field->getDefinition();

        return $definition['default'] ?? null;
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

    public function createViewValue($storedValue, FieldDefinition $field): FieldViewValue
    {
        return new FieldViewValue($field, $storedValue);
    }

    protected function extractScalarValueFromRequest(array $requestArgs, FieldDefinition $field)
    {
        $fieldId = $field->getId();
        // Normalize the fieldId for request - PHP request variables cannot include
        // periods so change them to underscores.
        $fieldId = str_replace('.', '_', $fieldId);
        if (array_key_exists($fieldId, $requestArgs)) {
            return $requestArgs[$fieldId];
        }

        $definition = $field->getDefinition();

        return $definition['default'] ?? null;
    }
}
